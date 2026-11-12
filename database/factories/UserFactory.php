<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\SubscriptionStatus;
use App\Enums\UserPlan;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'plan' => UserPlan::Free,
            'stripe_customer_id' => null,
            'stripe_subscription_id' => null,
            'subscription_status' => SubscriptionStatus::Inactive,
            'trial_ends_at' => null,
            'subscription_ends_at' => null,
            'remember_token' => Str::random(10),
            /* @chisel-2fa */
            'two_factor_secret' => null,
            'two_factor_recovery_codes' => null,
            'two_factor_confirmed_at' => null,
            /* @end-chisel-2fa */
        ];
    }

    /**
     * Indicate that the user is on the Pro plan.
     */
    public function pro(): static
    {
        return $this->state(fn (array $attributes) => [
            'plan' => UserPlan::Pro,
            'stripe_customer_id' => 'cus_'.Str::random(14),
            'stripe_subscription_id' => 'sub_'.Str::random(14),
            'subscription_status' => SubscriptionStatus::Active,
            'subscription_ends_at' => now()->addMonth(),
        ]);
    }

    /**
     * Indicate that the user is trialing.
     */
    public function trialing(): static
    {
        return $this->state(fn (array $attributes) => [
            'plan' => UserPlan::Pro,
            'subscription_status' => SubscriptionStatus::Trialing,
            'trial_ends_at' => now()->addDays(14),
        ]);
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    /* @chisel-2fa */
    /**
     * Indicate that the model has two-factor authentication configured.
     */
    public function withTwoFactor(): static
    {
        return $this->state(fn (array $attributes) => [
            'two_factor_secret' => encrypt('secret'),
            'two_factor_recovery_codes' => encrypt(json_encode(['recovery-code-1'])),
            'two_factor_confirmed_at' => now(),
        ]);
    }
    /* @end-chisel-2fa */
}
