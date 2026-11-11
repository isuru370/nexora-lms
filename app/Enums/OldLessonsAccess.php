<?php

namespace App\Enums;

enum OldLessonsAccess: string
{
    case Forever = 'forever';
    case UntilClassEnds = 'until_class_end';

    public function label(): string
    {
        return match ($this) {
            self::Forever => __('Always'),
            self::UntilClassEnds => __('Until the class ends'),
        };
    }
}
