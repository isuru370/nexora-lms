<?php

namespace App\Enums;

enum Role: string
{
    case SuperAdmin = 'super_admin';
    case Teacher = 'teacher';
    case Assistant = 'assistant';
    case Student = 'student';

    public function label(): string
    {
        return match ($this) {
            self::SuperAdmin => __('Super Admin'),
            self::Teacher => __('Teacher'),
            self::Assistant => __('Assistant'),
            self::Student => __('Student'),
        };
    }
}
