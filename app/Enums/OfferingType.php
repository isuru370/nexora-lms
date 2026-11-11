<?php

namespace App\Enums;

enum OfferingType: string
{
    case MonthlyClass = 'class';
    case Course = 'course';

    public function label(): string
    {
        return match ($this) {
            self::MonthlyClass => __('Monthly class'),
            self::Course => __('Course'),
        };
    }
}
