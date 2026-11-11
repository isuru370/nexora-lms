<?php

namespace Tests\Feature;

use App\Models\Enrolment;
use App\Models\Teacher;
use App\Models\User;
use App\Support\Month;
use App\Tenancy\CurrentTeacher;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\BuildsTenant;
use Tests\TestCase;

/**
 * Phase 1 "done when": a student without access never gets a video id.
 */
class LessonPagesTest extends TestCase
{
    use BuildsTenant, RefreshDatabase;

    private Teacher $kasun;

    private User $student;

    protected function setUp(): void
    {
        parent::setUp();

        $this->kasun = $this->makeTeacher('kasun');
        $this->student = $this->makeStudent($this->kasun, '+94711111111', 'Amaya Silva');
    }

    public function test_locked_lessons_never_reveal_the_video_id_anywhere(): void
    {
        $class = $this->makeOffering($this->kasun, ['slug' => 'physics']);
        $lesson = $this->makeLesson($class, ['title' => 'Motion part 1', 'video_ref' => 'SECRETvid01']);

        // Guest
        $this->get($this->tenantUrl('kasun', 'classes/physics'))->assertOk()->assertSee('Motion part 1')->assertDontSee('SECRETvid01');
        $this->get($this->tenantUrl('kasun', "lessons/{$lesson->id}"))->assertRedirect($this->tenantUrl('kasun', 'login'));

        // Enrolled but unpaid
        $this->enrol($this->student, $class);
        $this->actingAs($this->student)->get($this->tenantUrl('kasun', 'classes/physics'))
            ->assertOk()->assertDontSee('SECRETvid01')->assertSee('Pay for');
        $this->actingAs($this->student)->get($this->tenantUrl('kasun', "lessons/{$lesson->id}"))
            ->assertRedirect()->assertSessionHasErrors('lesson');
        $this->actingAs($this->student)->get($this->tenantUrl('kasun', 'dashboard'))->assertOk()->assertDontSee('SECRETvid01');
        $this->actingAs($this->student)->get($this->tenantUrl('kasun', 'my-classes'))->assertOk()->assertDontSee('SECRETvid01');
    }

    public function test_paid_student_watches_with_a_watermark_and_the_view_is_recorded(): void
    {
        $class = $this->makeOffering($this->kasun, ['slug' => 'physics']);
        $lesson = $this->makeLesson($class, ['video_ref' => 'OPENvideo01']);
        $this->payMonth($this->enrol($this->student, $class), Month::current());

        $this->actingAs($this->student)->get($this->tenantUrl('kasun', 'classes/physics'))->assertOk()->assertSee('OPENvideo01');

        $this->actingAs($this->student)->get($this->tenantUrl('kasun', "lessons/{$lesson->id}"))
            ->assertOk()
            ->assertSee('youtube-nocookie.com/embed/OPENvideo01', false)
            ->assertSee('Amaya Silva')        // watermark
            ->assertSee('071 111 1111');

        $this->assertDatabaseHas('lesson_views', ['lesson_id' => $lesson->id, 'views' => 1]);

        $this->actingAs($this->student)->get($this->tenantUrl('kasun', "lessons/{$lesson->id}"));
        $this->assertDatabaseHas('lesson_views', ['lesson_id' => $lesson->id, 'views' => 2]);
    }

    public function test_free_preview_lessons_play_for_guests(): void
    {
        $class = $this->makeOffering($this->kasun, ['slug' => 'physics']);
        $lesson = $this->makeLesson($class, ['visibility' => 'public', 'video_ref' => 'PREVIEWvid1']);

        $this->get($this->tenantUrl('kasun', "lessons/{$lesson->id}"))->assertOk()->assertSee('PREVIEWvid1');
    }

    public function test_student_joins_a_class_from_its_page(): void
    {
        $class = $this->makeOffering($this->kasun, ['slug' => 'physics']);

        $this->actingAs($this->student)->get($this->tenantUrl('kasun', 'classes/physics'))->assertSee('Join class');

        $this->actingAs($this->student)->post($this->tenantUrl('kasun', 'classes/physics/enrol'))
            ->assertRedirect($this->tenantUrl('kasun', 'classes/physics'));

        $this->assertSame(1, $this->countEnrolments());

        // Joining twice does not duplicate.
        $this->actingAs($this->student)->post($this->tenantUrl('kasun', 'classes/physics/enrol'));
        $this->assertSame(1, $this->countEnrolments());

        $this->actingAs($this->student)->get($this->tenantUrl('kasun', 'my-classes'))->assertSee($class->title);
    }

    public function test_paid_course_enrolment_waits_for_payment(): void
    {
        $this->makeOffering($this->kasun, ['slug' => 'office', 'type' => 'course', 'fee' => 5000]);

        $this->actingAs($this->student)->post($this->tenantUrl('kasun', 'classes/office/enrol'));

        $this->assertSame('pending', Teacher::withoutTenancy(fn () => Enrolment::query()->value('status')->value));
    }

    public function test_landing_shows_published_classes_only(): void
    {
        $this->makeOffering($this->kasun, ['title' => 'Visible Physics']);
        $this->makeOffering($this->kasun, ['title' => 'Secret Draft', 'is_published' => false]);

        $this->get($this->tenantUrl('kasun'))->assertOk()->assertSee('Visible Physics')->assertDontSee('Secret Draft');
    }

    public function test_draft_class_page_is_404(): void
    {
        $this->makeOffering($this->kasun, ['slug' => 'draft', 'is_published' => false]);

        $this->get($this->tenantUrl('kasun', 'classes/draft'))->assertNotFound();
    }

    public function test_other_teachers_classes_and_lessons_do_not_exist_here(): void
    {
        $nimal = $this->makeTeacher('nimal', ownerPhone: '+94770000002');
        $class = $this->makeOffering($nimal, ['slug' => 'maths']);
        $lesson = $this->makeLesson($class, ['visibility' => 'public']);

        $this->get($this->tenantUrl('kasun', 'classes/maths'))->assertNotFound();
        $this->get($this->tenantUrl('kasun', "lessons/{$lesson->id}"))->assertNotFound();
        $this->actingAs($this->student)->post($this->tenantUrl('kasun', 'classes/maths/enrol'))->assertNotFound();
    }

    private function countEnrolments(): int
    {
        return app(CurrentTeacher::class)->runAs($this->kasun, fn () => Enrolment::query()->count());
    }
}
