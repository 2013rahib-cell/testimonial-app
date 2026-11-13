<?php

declare(strict_types=1);

namespace App\Livewire\Settings;

use App\Enums\SubscriptionStatus;
use App\Enums\UserPlan;
use App\Models\User;
use App\Services\MockPaymentGateway;
use App\Services\StripeService;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;

/**
 * Livewire #[Computed] properties:
 *
 * @property-read User $user
 * @property-read bool $isPro
 * @property-read UserPlan $currentPlan
 * @property-read SubscriptionStatus $subscriptionStatus
 */
#[Layout('layouts.app')]
class Billing extends Component
{
    #[Url(as: 'checkout')]
    public ?string $checkoutStatus = null;

    public ?string $errorMessage = null;

    public function mount(): void
    {
        // Surface errors flashed by BillingCheckoutController redirects.
        $flashed = session('error');
        if (is_string($flashed) && $flashed !== '') {
            $this->errorMessage = $flashed;
        }
    }

    #[Computed]
    public function user(): User
    {
        /** @var User $user */
        $user = Auth::user();

        return $user;
    }

    #[Computed]
    public function isPro(): bool
    {
        return $this->user->isProPlan();
    }

    #[Computed]
    public function currentPlan(): UserPlan
    {
        return $this->user->plan ?? UserPlan::Free;
    }

    #[Computed]
    public function subscriptionStatus(): SubscriptionStatus
    {
        return $this->user->subscription_status ?? SubscriptionStatus::Inactive;
    }

    public function checkout(StripeService $stripeService): mixed
    {
        $this->errorMessage = null;

        // No Stripe keys yet: use the built-in demo checkout.
        if (MockPaymentGateway::isEnabled()) {
            return $this->redirectRoute('billing.demo-checkout', navigate: true);
        }

        if (! StripeService::isConfigured()) {
            $this->errorMessage = 'Payments are not configured yet. Add STRIPE_SECRET (and STRIPE_WEBHOOK_SECRET) to your .env file to enable upgrades.';

            return null;
        }

        try {
            $session = $stripeService->createProCheckoutSession(
                user: $this->user,
                successUrl: route('settings.billing', ['checkout' => 'success']),
                cancelUrl: route('settings.billing', ['checkout' => 'cancelled']),
            );

            if ($session->url) {
                return redirect()->away($session->url);
            }

            $this->errorMessage = 'Failed to generate checkout URL. Please try again.';
        } catch (\Throwable $e) {
            report($e);
            $this->errorMessage = 'The payment service is temporarily unavailable. Please try again in a moment.';
        }

        return null;
    }

    public function manageBilling(StripeService $stripeService): mixed
    {
        $this->errorMessage = null;

        if (! StripeService::isConfigured()) {
            $this->errorMessage = 'Payments are not configured yet. Add STRIPE_SECRET to your .env file to manage billing.';

            return null;
        }

        try {
            $session = $stripeService->createBillingPortalSession(
                user: $this->user,
                returnUrl: route('settings.billing'),
            );

            if ($session->url) {
                return redirect()->away($session->url);
            }

            $this->errorMessage = 'Failed to open customer portal. Please try again.';
        } catch (\Throwable $e) {
            report($e);
            $this->errorMessage = 'The billing portal is temporarily unavailable. Please try again in a moment.';
        }

        return null;
    }

    /**
     * Demo mode only: cancel the demo subscription (stands in for the Stripe Billing Portal).
     */
    public function cancelDemoSubscription(MockPaymentGateway $gateway): void
    {
        if (! MockPaymentGateway::isEnabled() || ! $this->user->isProPlan()) {
            return;
        }

        $gateway->cancel($this->user);
        $this->checkoutStatus = null;
        unset($this->isPro, $this->currentPlan, $this->subscriptionStatus);

        session()->flash('status', 'Your Pro subscription was cancelled. You are back on the Free plan.');
    }

    public function render(): View
    {
        return view('livewire.settings.billing', [
            'demoMode' => MockPaymentGateway::isEnabled(),
        ]);
    }
}
