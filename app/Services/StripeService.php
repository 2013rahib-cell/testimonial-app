<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\User;
use Stripe\BillingPortal\Session as PortalSession;
use Stripe\Checkout\Session as CheckoutSession;
use Stripe\Customer;
use Stripe\Stripe;
use Stripe\StripeClient;
use Stripe\Subscription;

class StripeService
{
    private StripeClient $client;

    public function __construct()
    {
        $secretKey = (string) config('services.stripe.secret', '');
        Stripe::setApiKey($secretKey);
        $this->client = new StripeClient($secretKey);
    }

    /**
     * Get or create a Stripe Customer for the given user.
     */
    public function createOrGetCustomer(User $user): Customer
    {
        if ($user->stripe_customer_id) {
            /** @var Customer $customer */
            $customer = $this->client->customers->retrieve($user->stripe_customer_id, []);

            return $customer;
        }

        $customer = $this->client->customers->create([
            'email' => $user->email,
            'name' => $user->name,
            'metadata' => [
                'user_id' => (string) $user->id,
            ],
        ]);

        $user->forceFill([
            'stripe_customer_id' => $customer->id,
        ])->save();

        return $customer;
    }

    /**
     * Create a Stripe Checkout Session for upgrading to the $9.99/month Pro plan.
     */
    public function createProCheckoutSession(
        User $user,
        ?string $successUrl = null,
        ?string $cancelUrl = null
    ): CheckoutSession {
        $priceId = config('services.stripe.price_id');

        $lineItems = [];

        if (! empty($priceId)) {
            $lineItems[] = [
                'price' => (string) $priceId,
                'quantity' => 1,
            ];
        } else {
            // Default ad-hoc price of $9.99/month USD
            $lineItems[] = [
                'price_data' => [
                    'currency' => 'usd',
                    'unit_amount' => 999, // $9.99
                    'recurring' => [
                        'interval' => 'month',
                    ],
                    'product_data' => [
                        'name' => 'Testimonial Pro Plan',
                        'description' => '$9.99/month - Up to 25 spaces and 1,000 testimonials per space',
                    ],
                ],
                'quantity' => 1,
            ];
        }

        $payload = [
            'mode' => 'subscription',
            'line_items' => $lineItems,
            'client_reference_id' => (string) $user->id,
            'metadata' => [
                'user_id' => (string) $user->id,
            ],
            'subscription_data' => [
                'metadata' => [
                    'user_id' => (string) $user->id,
                ],
            ],
            'success_url' => $successUrl ?? route('settings.billing', ['checkout' => 'success']),
            'cancel_url' => $cancelUrl ?? route('settings.billing', ['checkout' => 'cancelled']),
        ];

        if ($user->stripe_customer_id) {
            $payload['customer'] = $user->stripe_customer_id;
        } else {
            $payload['customer_email'] = $user->email;
        }

        return $this->client->checkout->sessions->create($payload);
    }

    /**
     * Create a Stripe Customer Portal session.
     */
    public function createBillingPortalSession(User $user, ?string $returnUrl = null): PortalSession
    {
        $customer = $this->createOrGetCustomer($user);

        return $this->client->billingPortal->sessions->create([
            'customer' => $customer->id,
            'return_url' => $returnUrl ?? route('settings.billing'),
        ]);
    }

    /**
     * Cancel the user's active subscription.
     */
    public function cancelSubscription(User $user): ?Subscription
    {
        if (! $user->stripe_subscription_id) {
            return null;
        }

        return $this->client->subscriptions->cancel($user->stripe_subscription_id, []);
    }
}
