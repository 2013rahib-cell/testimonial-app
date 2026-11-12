<?php

declare(strict_types=1);

namespace App\Livewire\Settings;

use App\Enums\SubscriptionStatus;
use App\Enums\UserPlan;
use App\Models\User;
use App\Services\StripeService;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;

#[Layout('layouts.app')]
class Billing extends Component
{
    #[Url(as: 'checkout')]
    public ?string $checkoutStatus = null;

    public ?string $errorMessage = null;

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
            $this->errorMessage = 'Payment service unavailable: '.$e->getMessage();
        }

        return null;
    }

    public function manageBilling(StripeService $stripeService): mixed
    {
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
            $this->errorMessage = 'Billing portal unavailable: '.$e->getMessage();
        }

        return null;
    }

    public function render(): View
    {
        return view('livewire.settings.billing');
    }
}
