<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\SubscriptionStatus;
use App\Enums\UserPlan;
use Carbon\CarbonImmutable;
use Database\Factories\UserFactory;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Laravel\Fortify\Contracts\PasskeyUser;
use Laravel\Fortify\PasskeyAuthenticatable;
use Laravel\Fortify\TwoFactorAuthenticatable;

/**
 * @property int $id
 * @property string $name
 * @property string $email
 * @property CarbonImmutable|null $email_verified_at
 * @property string $password
 * @property UserPlan $plan
 * @property string|null $stripe_customer_id
 * @property string|null $stripe_subscription_id
 * @property SubscriptionStatus $subscription_status
 * @property CarbonImmutable|null $trial_ends_at
 * @property CarbonImmutable|null $subscription_ends_at
 * @property string|null $two_factor_secret
 * @property string|null $two_factor_recovery_codes
 * @property CarbonImmutable|null $two_factor_confirmed_at
 * @property string|null $remember_token
 * @property CarbonImmutable|null $created_at
 * @property CarbonImmutable|null $updated_at
 */
#[Fillable([
    'name',
    'email',
    'password',
])]
#[Hidden(['password', 'two_factor_secret', 'two_factor_recovery_codes', 'remember_token'])]
class User extends Authenticatable implements MustVerifyEmail, PasskeyUser
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, PasskeyAuthenticatable, TwoFactorAuthenticatable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * Defaults mirroring the DB column defaults, so a freshly created model
     * (not yet refreshed from the database) always has a plan and status.
     *
     * @var array<string, mixed>
     */
    protected $attributes = [
        'plan' => 'free',
        'subscription_status' => 'inactive',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'plan' => UserPlan::class,
            'subscription_status' => SubscriptionStatus::class,
            'trial_ends_at' => 'datetime',
            'subscription_ends_at' => 'datetime',
        ];
    }

    /**
     * A user owns many spaces.
     *
     * @return HasMany<Space, $this>
     */
    public function spaces(): HasMany
    {
        return $this->hasMany(Space::class);
    }

    /**
     * A user has many testimonials through spaces.
     *
     * @return HasManyThrough<Testimonial, Space, $this>
     */
    public function testimonials(): HasManyThrough
    {
        return $this->hasManyThrough(Testimonial::class, Space::class);
    }

    /**
     * Determine if the user has an active Pro plan.
     */
    public function isProPlan(): bool
    {
        return $this->plan === UserPlan::Pro;
    }

    /**
     * Maximum spaces allowed for the user's plan.
     */
    public function maxSpaces(): int
    {
        return $this->plan->maxSpaces();
    }

    /**
     * Maximum testimonials allowed per space for the user's plan.
     */
    public function maxTestimonialsPerSpace(): int
    {
        return $this->plan->maxTestimonialsPerSpace();
    }

    /**
     * Get the user's initials.
     */
    public function initials(): string
    {
        $initials = Str::initials($this->name, true);

        return Str::length($initials) > 1
            ? Str::substr($initials, 0, 1).Str::substr($initials, -1)
            : $initials;
    }
}
