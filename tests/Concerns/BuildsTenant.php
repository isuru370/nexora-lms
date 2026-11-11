<?php

namespace Tests\Concerns;

use App\Enums\TeacherStatus;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\User;
use App\Services\Students\RegisterStudent;
use App\Tenancy\CurrentTeacher;
use Illuminate\Database\Eloquent\Collection;

/**
 * Builds teachers (tenants), their owners and students for tests.
 * Every password is "password".
 */
trait BuildsTenant
{
    protected function makeTeacher(string $slug, TeacherStatus $status = TeacherStatus::Trial, string $ownerPhone = '+94770000001'): Teacher
    {
        $teacher = Teacher::factory()->slug($slug)->status($status)->create();

        User::factory()->teacherOwner($teacher)->create(['phone' => $ownerPhone]);

        return $teacher;
    }

    protected function ownerOf(Teacher $teacher): User
    {
        return $teacher->owner()->firstOrFail();
    }

    protected function makeStudent(Teacher $teacher, string $phone = '+94710000001', string $name = 'Test Student'): User
    {
        return app(CurrentTeacher::class)->runAs($teacher, fn () => app(RegisterStudent::class)->handle([
            'name' => $name,
            'phone' => $phone,
            'password' => 'password',
            'grade' => '2027 A/L',
        ]));
    }

    protected function makeSuperAdmin(string $email = 'admin@nexoralms.test'): User
    {
        return User::factory()->superAdmin()->create(['email' => $email, 'phone' => null]);
    }

    /**
     * Students of a teacher, read the way the app reads them (scoped).
     *
     * @return Collection<int, Student>
     */
    protected function studentsAs(Teacher $teacher)
    {
        return app(CurrentTeacher::class)->runAs($teacher, fn () => Student::query()->get());
    }
}
