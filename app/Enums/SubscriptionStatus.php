<?php

declare(strict_types=1);

namespace App\Enums;

enum SubscriptionStatus: string
{
    case Active = 'active';
    case Inactive = 'inactive';
    case Trialing = 'trialing';
    case PastDue = 'past_due';
    case Canceled = 'canceled';

    public function label(): string
    {
        return match ($this) {
            self::Active => 'Active',
            self::Inactive => 'Inactive',
            self::Trialing => 'Trialing',
            self::PastDue => 'Past Due',
            self::Canceled => 'Canceled',
        };
    }
}
