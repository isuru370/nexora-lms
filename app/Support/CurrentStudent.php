<?php

namespace App\Support;

use App\Enums\Role;
use App\Models\Student;
use App\Tenancy\CurrentTeacher;

/**
 * The signed-in student of the current site, or null (guest, teacher, etc.).
 */
final class CurrentStudent
{
    public static function get(): ?Student
    {
        $user = auth()->user();
        $teacherId = app(CurrentTeacher::class)->id();

        if ($user === null || $user->role !== Role::Student || $user->teacher_id !== $teacherId) {
            return null;
        }

        return $user->student;
    }
}
