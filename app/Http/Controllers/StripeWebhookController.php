<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\SubscriptionStatus;
use App\Enums\UserPlan;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Stripe\Event;
use Stripe\Exception\SignatureVerificationException;
use Stripe\Webhook;

class StripeWebhookController extends Controller
{
    /**
     * Handle an incoming Stripe webhook event.
     */
    public function handle(Request $request): JsonResponse
    {
        $payload = (string) $request->getContent();
        $sigHeader = (string) $request->header('Stripe-Signature', '');
        $webhookSecret = (string) config('services.stripe.webhook_secret', '');

        if ($webhookSecret !== '') {
            try {
                $event = Webhook::constructEvent($payload, $sigHeader, $webhookSecret);
            } catch (SignatureVerificationException $e) {
                Log::warning('Stripe webhook signature verification failed: '.$e->getMessage());

                return response()->json(['error' => 'Invalid signature'], 400);
            } catch (\UnexpectedValueException $e) {
                Log::warning('Stripe webhook invalid payload: '.$e->getMessage());

                return response()->json(['error' => 'Invalid payload'], 400);
            }
        } else {
            /** @var array<string, mixed>|null $data */
            $data = json_decode($payload, true);
            if (! is_array($data)) {
                return response()->json(['error' => 'Invalid JSON'], 400);
            }
            $event = Event::constructFrom($data);
        }

        try {
            match ($event->type) {
                'checkout.session.completed' => $this->handleCheckoutSessionCompleted($event->data->object),
                'customer.subscription.created',
                'customer.subscription.updated' => $this->handleSubscriptionUpdated($event->data->object),
                'customer.subscription.deleted' => $this->handleSubscriptionDeleted($event->data->object),
                'invoice.payment_succeeded' => $this->handleInvoicePaymentSucceeded($event->data->object),
                'invoice.payment_failed' => $this->handleInvoicePaymentFailed($event->data->object),
                default => null,
            };
        } catch (\Throwable $e) {
            Log::error('Error processing Stripe webhook: '.$e->getMessage(), [
                'type' => $event->type,
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json(['error' => 'Webhook processing failed'], 500);
        }

        return response()->json(['status' => 'success', 'event' => $event->type]);
    }

    /**
     * Handle checkout.session.completed.
     */
    protected function handleCheckoutSessionCompleted(object $session): void
    {
        $userId = $session->client_reference_id
            ?? ($session->metadata->user_id ?? null);

        $customerEmail = $session->customer_details->email
            ?? ($session->customer_email ?? null);

        $customerId = $session->customer ?? null;
        $subscriptionId = $session->subscription ?? null;

        $user = null;

        if ($userId) {
            $user = User::find((int) $userId);
        }

        if (! $user && $customerId) {
            $user = User::where('stripe_customer_id', (string) $customerId)->first();
        }

        if (! $user && $customerEmail) {
            $user = User::where('email', (string) $customerEmail)->first();
        }

        if ($user) {
            $user->plan = UserPlan::Pro;
            $user->subscription_status = SubscriptionStatus::Active;

            if ($customerId) {
                $user->stripe_customer_id = (string) $customerId;
            }

            if ($subscriptionId) {
                $user->stripe_subscription_id = (string) $subscriptionId;
            }

            $user->save();
        }
    }

    /**
     * Handle customer.subscription.created and customer.subscription.updated.
     */
    protected function handleSubscriptionUpdated(object $subscription): void
    {
        $user = $this->findUserBySubscription($subscription);

        if (! $user) {
            return;
        }

        $stripeStatus = (string) ($subscription->status ?? 'inactive');
        $status = match ($stripeStatus) {
            'active' => SubscriptionStatus::Active,
            'trialing' => SubscriptionStatus::Trialing,
            'past_due' => SubscriptionStatus::PastDue,
            'canceled' => SubscriptionStatus::Canceled,
            'unpaid' => SubscriptionStatus::PastDue,
            default => SubscriptionStatus::Inactive,
        };

        $user->subscription_status = $status;

        if (in_array($status, [SubscriptionStatus::Active, SubscriptionStatus::Trialing], true)) {
            $user->plan = UserPlan::Pro;
        } elseif ($status === SubscriptionStatus::Canceled) {
            $user->plan = UserPlan::Free;
        }

        if (! empty($subscription->id)) {
            $user->stripe_subscription_id = (string) $subscription->id;
        }

        if (! empty($subscription->customer)) {
            $user->stripe_customer_id = (string) $subscription->customer;
        }

        if (! empty($subscription->current_period_end)) {
            $user->subscription_ends_at = Carbon::createFromTimestamp((int) $subscription->current_period_end);
        }

        if (! empty($subscription->trial_end)) {
            $user->trial_ends_at = Carbon::createFromTimestamp((int) $subscription->trial_end);
        }

        $user->save();
    }

    /**
     * Handle customer.subscription.deleted.
     */
    protected function handleSubscriptionDeleted(object $subscription): void
    {
        $user = $this->findUserBySubscription($subscription);

        if (! $user) {
            return;
        }

        $user->plan = UserPlan::Free;
        $user->subscription_status = SubscriptionStatus::Canceled;
        $user->subscription_ends_at = now();
        $user->save();
    }

    /**
     * Handle invoice.payment_succeeded.
     */
    protected function handleInvoicePaymentSucceeded(object $invoice): void
    {
        $customerId = $invoice->customer ?? null;
        $subscriptionId = $invoice->subscription ?? null;

        $user = null;

        if ($subscriptionId) {
            $user = User::where('stripe_subscription_id', (string) $subscriptionId)->first();
        }

        if (! $user && $customerId) {
            $user = User::where('stripe_customer_id', (string) $customerId)->first();
        }

        if ($user) {
            $user->plan = UserPlan::Pro;
            $user->subscription_status = SubscriptionStatus::Active;
            $user->save();
        }
    }

    /**
     * Handle invoice.payment_failed.
     */
    protected function handleInvoicePaymentFailed(object $invoice): void
    {
        $customerId = $invoice->customer ?? null;
        $subscriptionId = $invoice->subscription ?? null;

        $user = null;

        if ($subscriptionId) {
            $user = User::where('stripe_subscription_id', (string) $subscriptionId)->first();
        }

        if (! $user && $customerId) {
            $user = User::where('stripe_customer_id', (string) $customerId)->first();
        }

        if ($user) {
            $user->subscription_status = SubscriptionStatus::PastDue;
            $user->save();
        }
    }

    /**
     * Find a user given a subscription payload.
     */
    protected function findUserBySubscription(object $subscription): ?User
    {
        $userId = $subscription->metadata->user_id ?? null;

        if ($userId) {
            $user = User::find((int) $userId);
            if ($user) {
                return $user;
            }
        }

        if (! empty($subscription->id)) {
            $user = User::where('stripe_subscription_id', (string) $subscription->id)->first();
            if ($user) {
                return $user;
            }
        }

        if (! empty($subscription->customer)) {
            $user = User::where('stripe_customer_id', (string) $subscription->customer)->first();
            if ($user) {
                return $user;
            }
        }

        return null;
    }
}
