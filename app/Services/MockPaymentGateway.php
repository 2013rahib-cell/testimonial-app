<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\SubscriptionStatus;
use App\Enums\UserPlan;
use App\Models\User;
use Carbon\CarbonImmutable;

/**
 * Demo payment gateway used until real Stripe credentials are configured.
 *
 * - Never charges anything and never stores card data (only the last 4 digits are returned).
 * - Mirrors Stripe's well-known test cards so the flow can be tested end to end:
 *     4242 4242 4242 4242 → success
 *     4000 0000 0000 0002 → declined
 *     4000 0000 0000 9995 → insufficient funds
 */
final class MockPaymentGateway
{
    public const PRO_PRICE_CENTS = 999;

    /** Numeric-string keys become int keys in PHP; lookups by digit string still work. @var array<int, string> */
    private const DECLINE_CARDS = [
        '4000000000000002' => 'Your card was declined. Please try a different card.',
        '4000000000009995' => 'Your card has insufficient funds.',
    ];

    /**
     * Whether the demo checkout should be used instead of Stripe.
     */
    public static function isEnabled(): bool
    {
        return (bool) config('services.billing.mock', true) && ! StripeService::isConfigured();
    }

    public static function digitsOnly(string $value): string
    {
        return (string) preg_replace('/\D+/', '', $value);
    }

    /**
     * Luhn checksum – rejects mistyped card numbers.
     */
    public static function passesLuhn(string $number): bool
    {
        $digits = self::digitsOnly($number);
        $length = strlen($digits);

        if ($length < 12 || $length > 19) {
            return false;
        }

        $sum = 0;
        for ($i = 0; $i < $length; $i++) {
            $digit = (int) $digits[$length - 1 - $i];
            if ($i % 2 === 1) {
                $digit *= 2;
                if ($digit > 9) {
                    $digit -= 9;
                }
            }
            $sum += $digit;
        }

        return $sum % 10 === 0;
    }

    /**
     * Validate an expiry in MM/YY format and ensure it is not in the past.
     */
    public static function isValidExpiry(string $expiry, ?CarbonImmutable $now = null): bool
    {
        if (preg_match('#^\s*(\d{2})\s*/\s*(\d{2})\s*$#', $expiry, $m) !== 1) {
            return false;
        }

        $month = (int) $m[1];
        $year = 2000 + (int) $m[2];

        if ($month < 1 || $month > 12) {
            return false;
        }

        $now ??= CarbonImmutable::now();
        $endOfExpiryMonth = CarbonImmutable::create($year, $month, 1)?->endOfMonth();

        return $endOfExpiryMonth !== null && $endOfExpiryMonth->greaterThanOrEqualTo($now);
    }

    /**
     * Detect the card brand from the number prefix (display only).
     */
    public static function brand(string $number): string
    {
        $digits = self::digitsOnly($number);

        return match (true) {
            str_starts_with($digits, '4') => 'Visa',
            preg_match('/^(5[1-5]|2[2-7])/', $digits) === 1 => 'Mastercard',
            preg_match('/^3[47]/', $digits) === 1 => 'American Express',
            preg_match('/^(6011|65)/', $digits) === 1 => 'Discover',
            default => 'Card',
        };
    }

    /**
     * "Charge" the card. Returns an error message for a declined test card, or null on success.
     * Assumes the inputs were already validated.
     */
    public function declineReason(string $cardNumber): ?string
    {
        return self::DECLINE_CARDS[self::digitsOnly($cardNumber)] ?? null;
    }

    /**
     * Activate the Pro plan for one month (demo subscription).
     */
    public function activatePro(User $user): void
    {
        $user->plan = UserPlan::Pro;
        $user->subscription_status = SubscriptionStatus::Active;
        $user->subscription_ends_at = CarbonImmutable::now()->addMonth();
        $user->save();
    }

    /**
     * Cancel the demo subscription and return the user to the Free plan.
     */
    public function cancel(User $user): void
    {
        $user->plan = UserPlan::Free;
        $user->subscription_status = SubscriptionStatus::Canceled;
        $user->subscription_ends_at = CarbonImmutable::now();
        $user->save();
    }
}
