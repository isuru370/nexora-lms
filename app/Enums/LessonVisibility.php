<?php

namespace App\Enums;

enum LessonVisibility: string
{
    /** Students who paid for the lesson's month (classes) or bought the course. */
    case Paid = 'paid';

    /** Every enrolled student, paid or not. */
    case Enrolled = 'enrolled';

    /** Anyone, even without an account (free preview). */
    case Public = 'public';

    public function label(): string
    {
        return match ($this) {
            self::Paid => __('Paid students'),
            self::Enrolled => __('All enrolled students'),
            self::Public => __('Free for everyone'),
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Paid => 'slate',
            self::Enrolled => 'blue',
            self::Public => 'green',
        };
    }
}
