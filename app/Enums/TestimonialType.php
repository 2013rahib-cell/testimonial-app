<?php

declare(strict_types=1);

namespace App\Enums;

enum TestimonialType: string
{
    case Text = 'text';
    case Video = 'video';

    public function label(): string
    {
        return match ($this) {
            self::Text => 'Text',
            self::Video => 'Video',
        };
    }
}
