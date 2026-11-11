<?php

namespace App\Support;

final class Money
{
    /**
     * 2000 -> "LKR 2,000", 1500.5 -> "LKR 1,500.50"
     */
    public static function lkr(string|int|float|null $amount): string
    {
        $value = (float) ($amount ?? 0);
        $decimals = fmod($value, 1.0) === 0.0 ? 0 : 2;

        return 'LKR '.number_format($value, $decimals);
    }
}
