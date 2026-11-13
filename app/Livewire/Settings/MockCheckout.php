<?php

declare(strict_types=1);

namespace App\Livewire\Settings;

use App\Models\User;
use App\Services\MockPaymentGateway;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;

/**
 * Demo checkout page (Pro plan, $9.99/month) shown while Stripe is not configured.
 * No real payment is made and no card data is stored.
 *
 * @property-read User $user
 */
#[Layout('layouts.app')]
class MockCheckout extends Component
{
    public string $cardholderName = '';

    public string $email = '';

    public string $cardNumber = '';

    public string $expiry = '';

    public string $cvc = '';

    public string $country = 'Bangladesh';

    public string $postalCode = '';

    public ?string $paymentError = null;

    public function mount(): void
    {
        // When real Stripe keys exist, the real checkout must be used instead.
        if (! MockPaymentGateway::isEnabled()) {
            $this->redirectRoute('settings.billing', navigate: true);

            return;
        }

        if ($this->user->isProPlan()) {
            session()->flash('status', 'You are already on the Pro plan.');
            $this->redirectRoute('settings.billing', navigate: true);

            return;
        }

        $this->cardholderName = $this->user->name;
        $this->email = $this->user->email;
    }

    #[Computed]
    public function user(): User
    {
        /** @var User $user */
        $user = Auth::user();

        return $user;
    }

    public function pay(MockPaymentGateway $gateway): mixed
    {
        $this->paymentError = null;

        if (! MockPaymentGateway::isEnabled()) {
            return $this->redirectRoute('settings.billing', navigate: true);
        }

        $this->validate([
            'cardholderName' => ['required', 'string', 'min:2', 'max:100'],
            'email' => ['required', 'email', 'max:255'],
            'cardNumber' => ['required', 'string', function (string $attribute, mixed $value, \Closure $fail): void {
                if (! MockPaymentGateway::passesLuhn((string) $value)) {
                    $fail('Your card number is invalid.');
                }
            }],
            'expiry' => ['required', 'string', function (string $attribute, mixed $value, \Closure $fail): void {
                if (! MockPaymentGateway::isValidExpiry((string) $value)) {
                    $fail('Your card\'s expiration date is invalid or in the past.');
                }
            }],
            'cvc' => ['required', 'regex:/^\d{3,4}$/'],
            'country' => ['required', 'string', 'max:100'],
            'postalCode' => ['nullable', 'string', 'max:20'],
        ], [
            'cvc.regex' => 'Your card\'s security code is invalid.',
        ], [
            'cardholderName' => 'name on card',
            'cardNumber' => 'card number',
            'cvc' => 'CVC',
            'postalCode' => 'postal code',
        ]);

        $last4 = substr(MockPaymentGateway::digitsOnly($this->cardNumber), -4);
        $brand = MockPaymentGateway::brand($this->cardNumber);
        $decline = $gateway->declineReason($this->cardNumber);

        // Never keep card data around longer than this request.
        $this->reset(['cardNumber', 'expiry', 'cvc']);

        if ($decline !== null) {
            $this->paymentError = $decline;

            return null;
        }

        $gateway->activatePro($this->user);

        session()->flash('status', "Payment successful – {$brand} ending in {$last4} was charged \$9.99 (demo). Welcome to Pro!");

        return $this->redirectRoute('settings.billing', ['checkout' => 'success'], navigate: true);
    }

    public function render(): View
    {
        return view('livewire.settings.mock-checkout')->title(__('Checkout'));
    }
}
