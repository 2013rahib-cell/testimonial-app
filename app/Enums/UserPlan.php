<?php

declare(strict_types=1);

namespace App\Enums;

enum UserPlan: string
{
    case Free = 'free';
    case Pro = 'pro';

    public function label(): string
    {
        return match ($this) {
            self::Free => 'Free',
            self::Pro => 'Pro',
        };
    }

    public function maxSpaces(): int
    {
        return match ($this) {
            self::Free => 3,
            self::Pro => 25,
        };
    }

    public function maxTestimonialsPerSpace(): int
    {
        return match ($this) {
            self::Free => 100,
            self::Pro => 1000,
        };
    }
}
