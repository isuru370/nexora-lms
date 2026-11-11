<?php

namespace App\Enums;

enum TeacherStatus: string
{
    case Trial = 'trial';
    case Active = 'active';
    case Grace = 'grace';
    case Locked = 'locked';

    public function label(): string
    {
        return match ($this) {
            self::Trial => __('Trial'),
            self::Active => __('Active'),
            self::Grace => __('Grace'),
            self::Locked => __('Locked'),
        };
    }

    /**
     * Badge colour used in the UI kit.
     */
    public function color(): string
    {
        return match ($this) {
            self::Trial => 'blue',
            self::Active => 'green',
            self::Grace => 'amber',
            self::Locked => 'red',
        };
    }
}
