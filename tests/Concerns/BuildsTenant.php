<?php

namespace Tests\Concerns;

use App\Enums\EnrolmentStatus;
use App\Enums\OfferingType;
use App\Enums\TeacherStatus;
use App\Models\AccessOverride;
use App\Models\Enrolment;
use App\Models\Lesson;
use App\Models\Offering;
use App\Models\PaymentPeriod;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\User;
use App\Services\Students\RegisterStudent;
use App\Support\Month;
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

    /**
     * @param  array<string, mixed>  $attributes
     */
    protected function makeOffering(Teacher $teacher, array $attributes = []): Offering
    {
        return app(CurrentTeacher::class)->runAs($teacher, fn () => Offering::create(array_merge([
            'type' => OfferingType::MonthlyClass,
            'title' => '2027 A/L Physics',
            'slug' => 'physics-'.uniqid(),
            'subject' => 'Physics',
            'fee' => 2000,
            'is_published' => true,
        ], $attributes)));
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    protected function makeLesson(Offering $offering, array $attributes = []): Lesson
    {
        return app(CurrentTeacher::class)->runAs($offering->teacher()->first(), fn () => $offering->lessons()->create(array_merge([
            'title' => 'Lesson '.uniqid(),
            'month' => Month::current(),
            'video_ref' => substr(str_replace(['+', '/', '='], ['A', 'B', ''], base64_encode(random_bytes(9))), 0, 11),
            'visibility' => 'paid',
            'is_published' => true,
        ], $attributes)));
    }

    protected function enrol(User $studentUser, Offering $offering, EnrolmentStatus $status = EnrolmentStatus::Active): Enrolment
    {
        return app(CurrentTeacher::class)->runAs($offering->teacher()->first(), fn () => Enrolment::create([
            'student_id' => $studentUser->student()->first()->id,
            'offering_id' => $offering->id,
            'status' => $status,
            'enrolled_at' => now(),
        ]));
    }

    protected function payMonth(Enrolment $enrolment, string $month, string $source = PaymentPeriod::SOURCE_PAYMENT): PaymentPeriod
    {
        return app(CurrentTeacher::class)->runAs($enrolment->teacher()->first(), fn () => PaymentPeriod::create([
            'enrolment_id' => $enrolment->id,
            'month' => $month,
            'source' => $source,
        ]));
    }

    protected function override(Enrolment $enrolment, string $type, ?string $until = null): AccessOverride
    {
        return app(CurrentTeacher::class)->runAs($enrolment->teacher()->first(), fn () => AccessOverride::create([
            'enrolment_id' => $enrolment->id,
            'type' => $type,
            'until' => $until,
        ]));
    }
}
