<?php

namespace App\Support;

use Carbon\CarbonImmutable;

/**
 * Months are "YYYY-MM" strings. "Now" is always Sri Lanka time.
 */
final class Month
{
    public static function current(): string
    {
        return self::today()->format('Y-m');
    }

    public static function today(): CarbonImmutable
    {
        return CarbonImmutable::now(config('nexora.display_timezone'))->startOfDay();
    }

    public static function isValid(?string $month): bool
    {
        return is_string($month) && preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $month) === 1;
    }

    public static function previous(string $month): string
    {
        return CarbonImmutable::createFromFormat('!Y-m', $month)->subMonthNoOverflow()->format('Y-m');
    }

    /**
     * "2026-11" -> "November 2026"
     */
    public static function label(string $month): string
    {
        return CarbonImmutable::createFromFormat('!Y-m', $month)->translatedFormat('F Y');
    }

    /**
     * "2026-11" -> "November"
     */
    public static function name(string $month): string
    {
        return CarbonImmutable::createFromFormat('!Y-m', $month)->translatedFormat('F');
    }
}
