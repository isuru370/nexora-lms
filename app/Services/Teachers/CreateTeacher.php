<?php

namespace App\Services\Teachers;

use App\Enums\Role;
use App\Enums\TeacherStatus;
use App\Models\Teacher;
use App\Models\User;
use App\Support\AuditLogger;
use Illuminate\Support\Facades\DB;

/**
 * Creates a tenant (teacher site) and its owner login, on trial.
 */
class CreateTeacher
{
    /**
     * @param  array{slug: string, display_name: string, subject_line?: ?string, owner_name: string, phone: string, email?: ?string, password: string}  $data
     */
    public function handle(array $data): Teacher
    {
        return DB::transaction(function () use ($data): Teacher {
            $teacher = Teacher::create([
                'slug' => $data['slug'],
                'display_name' => $data['display_name'],
                'subject_line' => $data['subject_line'] ?? null,
                'phone' => $data['phone'],
                'whatsapp' => $data['phone'],
                'status' => TeacherStatus::Trial,
                'trial_ends_on' => now(config('nexora.display_timezone'))
                    ->addDays((int) config('nexora.trial_days'))
                    ->toDateString(),
            ]);

            User::create([
                'teacher_id' => $teacher->id,
                'role' => Role::Teacher,
                'name' => $data['owner_name'],
                'phone' => $data['phone'],
                'email' => $data['email'] ?? null,
                'password' => $data['password'],
            ]);

            AuditLogger::log('teacher.created', $teacher, ['slug' => $teacher->slug], teacherId: null);

            return $teacher;
        });
    }
}
