<?php

namespace App\Services\Students;

use App\Enums\Role;
use App\Models\Student;
use App\Models\TeacherCounter;
use App\Models\User;
use App\Tenancy\CurrentTeacher;
use Illuminate\Support\Facades\DB;

/**
 * Registers a student under the current teacher.
 */
class RegisterStudent
{
    public function __construct(private CurrentTeacher $current) {}

    /**
     * @param  array{name: string, phone: string, password: string, email?: ?string, school?: ?string, grade?: ?string}  $data
     */
    public function handle(array $data): User
    {
        $teacher = $this->current->require();

        return DB::transaction(function () use ($data, $teacher): User {
            $user = User::create([
                'teacher_id' => $teacher->id,
                'role' => Role::Student,
                'name' => $data['name'],
                'phone' => $data['phone'],
                'email' => $data['email'] ?? null,
                'password' => $data['password'],
            ]);

            Student::create([
                'user_id' => $user->id,
                'student_code' => $this->nextCode($teacher->slug),
                'school' => $data['school'] ?? null,
                'grade' => $data['grade'] ?? null,
            ]);

            return $user;
        });
    }

    /**
     * kasun -> KAS-00001
     */
    private function nextCode(string $slug): string
    {
        $letters = preg_replace('/[^a-z]/', '', $slug) ?: 'stu';
        $prefix = strtoupper(substr(str_pad($letters, 3, 'x'), 0, 3));

        return $prefix.'-'.str_pad((string) TeacherCounter::next('student_code'), 5, '0', STR_PAD_LEFT);
    }
}
