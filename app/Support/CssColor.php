<?php

declare(strict_types=1);

namespace App\Support;

/**
 * Whitelists user-supplied colours before they are placed inside inline CSS
 * (e.g. the Wall of Love `?bg=` parameter) to prevent CSS injection.
 */
final class CssColor
{
    /**
     * Return the colour if it is a hex value (#rgb, #rgba, #rrggbb, #rrggbbaa)
     * or "transparent"; otherwise return the fallback.
     */
    public static function sanitize(?string $value, string $fallback = '#ffffff'): string
    {
        $value = strtolower(trim((string) $value));

        if ($value === 'transparent') {
            return $value;
        }

        if (preg_match('/^#(?:[0-9a-f]{3,4}|[0-9a-f]{6}|[0-9a-f]{8})$/', $value) === 1) {
            return $value;
        }

        return $fallback;
    }

    public static function isValid(?string $value): bool
    {
        return self::sanitize($value, '') !== '';
    }
}
