<?php

namespace App\Support;

/**
 * Sri Lankan phone numbers in one format (E.164): +94XXXXXXXXX.
 *
 * "0771234567", "771234567", "94771234567", "+94 77 123 4567" are all the
 * same number and become "+94771234567".
 */
final class Phone
{
    public static function normalize(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $digits = preg_replace('/\D+/', '', $value) ?? '';

        if (strlen($digits) === 11 && str_starts_with($digits, '94')) {
            $digits = substr($digits, 2);
        } elseif (strlen($digits) === 10 && str_starts_with($digits, '0')) {
            $digits = substr($digits, 1);
        }

        if (strlen($digits) !== 9 || str_starts_with($digits, '0')) {
            return null;
        }

        return '+94'.$digits;
    }

    /**
     * "+94771234567" -> "077 123 4567" for display.
     */
    public static function display(?string $e164): string
    {
        if ($e164 === null || ! str_starts_with($e164, '+94') || strlen($e164) !== 12) {
            return (string) $e164;
        }

        $local = '0'.substr($e164, 3);

        return substr($local, 0, 3).' '.substr($local, 3, 3).' '.substr($local, 6);
    }
}
