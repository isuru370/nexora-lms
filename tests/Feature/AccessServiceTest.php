<?php

namespace Tests\Feature;

use App\Enums\EnrolmentStatus;
use App\Enums\OfferingType;
use App\Models\AccessOverride;
use App\Models\Lesson;
use App\Models\Offering;
use App\Models\PaymentPeriod;
use App\Models\Teacher;
use App\Models\User;
use App\Services\Access\AccessDecision;
use App\Services\Access\AccessService;
use App\Tenancy\CurrentTeacher;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\BuildsTenant;
use Tests\TestCase;

/**
 * Every teacher setting in foundation doc §7.2, one test each.
 * "Today" is fixed to 2026-11-04 (Asia/Colombo).
 */
class AccessServiceTest extends TestCase
{
    use BuildsTenant, RefreshDatabase;

    private Teacher $teacher;

    private User $studentUser;

    private Offering $class;

    protected function setUp(): void
    {
        parent::setUp();

        $this->travelTo(now('Asia/Colombo')->setDate(2026, 11, 4)->setTime(10, 0));

        $this->teacher = $this->makeTeacher('kasun');
        $this->studentUser = $this->makeStudent($this->teacher);
        $this->class = $this->makeOffering($this->teacher);
    }

    private function decide(Lesson $lesson): AccessDecision
    {
        return app(CurrentTeacher::class)->runAs($this->teacher, fn () => app(AccessService::class)
            ->canWatch($this->studentUser->student()->first(), $lesson->fresh()));
    }

    private function lesson(string $month = '2026-11', array $attributes = [], ?Offering $offering = null): Lesson
    {
        return $this->makeLesson($offering ?? $this->class, ['month' => $month] + $attributes);
    }

    public function test_not_enrolled_cannot_watch_paid_lessons(): void
    {
        $this->assertSame(AccessDecision::NOT_ENROLLED, $this->decide($this->lesson())->reason);
    }

    public function test_public_lessons_are_open_to_everyone(): void
    {
        $this->assertTrue($this->decide($this->lesson('2026-11', ['visibility' => 'public']))->allowed);
    }

    public function test_enrolled_visibility_needs_only_an_enrolment(): void
    {
        $lesson = $this->lesson('2026-11', ['visibility' => 'enrolled']);
        $this->assertFalse($this->decide($lesson)->allowed);

        $this->enrol($this->studentUser, $this->class);
        $this->assertTrue($this->decide($lesson)->allowed);
    }

    public function test_paying_a_month_opens_only_that_month(): void
    {
        $enrolment = $this->enrol($this->studentUser, $this->class);
        $this->payMonth($enrolment, '2026-11');

        $this->assertTrue($this->decide($this->lesson('2026-11'))->allowed);

        $october = $this->decide($this->lesson('2026-10'));
        $this->assertSame(AccessDecision::UNPAID_MONTH, $october->reason);
        $this->assertSame('2026-10', $october->month);

        $this->assertFalse($this->decide($this->lesson('2026-12'))->allowed);
    }

    public function test_free_month_and_manual_payment_also_open_the_month(): void
    {
        $enrolment = $this->enrol($this->studentUser, $this->class);
        $this->payMonth($enrolment, '2026-10', PaymentPeriod::SOURCE_FREE);
        $this->payMonth($enrolment, '2026-11', PaymentPeriod::SOURCE_MANUAL);

        $this->assertTrue($this->decide($this->lesson('2026-10'))->allowed);
        $this->assertTrue($this->decide($this->lesson('2026-11'))->allowed);
    }

    public function test_past_lessons_access_opens_the_backlog(): void
    {
        $enrolment = $this->enrol($this->studentUser, $this->class);
        $this->payMonth($enrolment, '2026-11');
        $september = $this->lesson('2026-09');

        $this->assertFalse($this->decide($september)->allowed);

        $this->class->update(['past_lessons_access' => true]);
        $this->assertTrue($this->decide($september)->allowed);
        $this->assertFalse($this->decide($this->lesson('2026-12'))->allowed, 'never opens future months');
    }

    public function test_old_paid_months_close_when_the_class_ends_if_teacher_says_so(): void
    {
        $enrolment = $this->enrol($this->studentUser, $this->class);
        $this->payMonth($enrolment, '2026-10');
        $october = $this->lesson('2026-10');

        $this->class->update(['ends_on' => '2026-10-31']);
        $this->assertTrue($this->decide($october)->allowed, 'default is forever');

        $this->class->update(['old_lessons_access' => 'until_class_end']);
        $this->assertSame(AccessDecision::CLASS_ENDED, $this->decide($october)->reason);
    }

    public function test_grace_days_open_the_new_month_for_last_months_payers(): void
    {
        $enrolment = $this->enrol($this->studentUser, $this->class);
        $november = $this->lesson('2026-11');

        $this->payMonth($enrolment, '2026-10');
        $this->assertFalse($this->decide($november)->allowed);

        $this->class->update(['grace_days' => 5]); // today is the 4th
        $this->assertTrue($this->decide($november)->allowed);

        $this->class->update(['grace_days' => 3]);
        $this->assertFalse($this->decide($november)->allowed);
    }

    public function test_grace_days_do_not_help_students_who_did_not_pay_last_month(): void
    {
        $this->enrol($this->studentUser, $this->class);
        $this->class->update(['grace_days' => 7]);

        $this->assertFalse($this->decide($this->lesson('2026-11'))->allowed);
    }

    public function test_temporary_grant_opens_current_and_earlier_months_until_its_date(): void
    {
        $enrolment = $this->enrol($this->studentUser, $this->class);
        $this->override($enrolment, AccessOverride::GRANT, '2026-11-10');

        $this->assertTrue($this->decide($this->lesson('2026-11'))->allowed);
        $this->assertTrue($this->decide($this->lesson('2026-10'))->allowed);
        $this->assertFalse($this->decide($this->lesson('2026-12'))->allowed);

        $this->travelTo(now('Asia/Colombo')->setDate(2026, 11, 11));
        $this->assertFalse($this->decide($this->lesson('2026-11'))->allowed, 'grant expired');
    }

    public function test_block_wins_over_payment(): void
    {
        $enrolment = $this->enrol($this->studentUser, $this->class);
        $this->payMonth($enrolment, '2026-11');
        $override = $this->override($enrolment, AccessOverride::BLOCK);
        $lesson = $this->lesson('2026-11');

        $this->assertSame(AccessDecision::BLOCKED, $this->decide($lesson)->reason);

        $override->forceFill(['revoked_at' => now()])->saveQuietly();
        $this->assertTrue($this->decide($lesson)->allowed);
    }

    public function test_suspended_enrolment_has_no_access(): void
    {
        $enrolment = $this->enrol($this->studentUser, $this->class, EnrolmentStatus::Suspended);
        $this->payMonth($enrolment, '2026-11');

        $this->assertSame(AccessDecision::NOT_ENROLLED, $this->decide($this->lesson('2026-11'))->reason);
    }

    public function test_available_until_locks_a_lesson_for_everyone(): void
    {
        $enrolment = $this->enrol($this->studentUser, $this->class);
        $this->payMonth($enrolment, '2026-11');

        $this->assertTrue($this->decide($this->lesson('2026-11', ['available_until' => '2026-11-04']))->allowed);
        $this->assertSame(AccessDecision::EXPIRED, $this->decide($this->lesson('2026-11', ['available_until' => '2026-11-03']))->reason);
        $this->assertSame(AccessDecision::EXPIRED, $this->decide($this->lesson('2026-11', ['visibility' => 'public', 'available_until' => '2026-11-03']))->reason);
    }

    public function test_hidden_lessons_and_unpublished_classes_are_closed(): void
    {
        $enrolment = $this->enrol($this->studentUser, $this->class);
        $this->payMonth($enrolment, '2026-11');

        $this->assertSame(AccessDecision::NOT_PUBLISHED, $this->decide($this->lesson('2026-11', ['is_published' => false]))->reason);

        $lesson = $this->lesson('2026-11', ['visibility' => 'public']);
        $this->class->update(['is_published' => false]);
        $this->assertSame(AccessDecision::NOT_PUBLISHED, $this->decide($lesson)->reason);
    }

    public function test_paid_course_waits_for_payment_then_respects_access_until(): void
    {
        $course = $this->makeOffering($this->teacher, ['type' => OfferingType::Course, 'title' => 'MS Office', 'fee' => 5000]);
        $lesson = $this->makeLesson($course, ['month' => null]);

        $enrolment = $this->enrol($this->studentUser, $course, EnrolmentStatus::Pending);
        $this->assertSame(AccessDecision::PENDING_PAYMENT, $this->decide($lesson)->reason);

        $enrolment->update(['status' => EnrolmentStatus::Active, 'access_until' => '2026-12-01']);
        $this->assertTrue($this->decide($lesson)->allowed);

        $enrolment->update(['access_until' => '2026-11-01']);
        $this->assertSame(AccessDecision::EXPIRED, $this->decide($lesson)->reason);
    }

    public function test_free_class_is_open_to_every_enrolled_student(): void
    {
        $free = $this->makeOffering($this->teacher, ['title' => 'Free seminar', 'fee' => 0]);
        $lesson = $this->makeLesson($free, ['month' => '2026-11']);

        $this->assertFalse($this->decide($lesson)->allowed);
        $this->enrol($this->studentUser, $free);
        $this->assertTrue($this->decide($lesson)->allowed);
    }
}
