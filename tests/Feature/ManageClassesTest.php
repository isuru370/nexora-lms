<?php

namespace Tests\Feature;

use App\Models\Lesson;
use App\Models\Offering;
use App\Models\Teacher;
use App\Models\User;
use App\Tenancy\CurrentTeacher;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\Concerns\BuildsTenant;
use Tests\TestCase;

class ManageClassesTest extends TestCase
{
    use BuildsTenant, RefreshDatabase;

    private Teacher $kasun;

    private User $owner;

    protected function setUp(): void
    {
        parent::setUp();

        $this->kasun = $this->makeTeacher('kasun');
        $this->owner = $this->ownerOf($this->kasun);

        Http::fake([
            'www.youtube.com/oembed*' => Http::response(['title' => 'Kinematics – Part 1']),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function classForm(array $overrides = []): array
    {
        return array_merge([
            'type' => 'class',
            'title' => '2027 A/L Physics Theory',
            'subject' => 'Physics',
            'grade' => '2027 A/L',
            'medium' => 'sinhala',
            'fee' => '2,500',
            'old_lessons_access' => 'forever',
            'past_lessons_access' => '1',
            'grace_days' => '3',
            'is_published' => '1',
        ], $overrides);
    }

    private function as(callable $callback): mixed
    {
        return app(CurrentTeacher::class)->runAs($this->kasun, $callback);
    }

    public function test_teacher_creates_a_monthly_class_with_access_settings(): void
    {
        $this->actingAs($this->owner)->get($this->tenantUrl('kasun', 'manage/classes/create'))->assertOk();

        $response = $this->actingAs($this->owner)->post($this->tenantUrl('kasun', 'manage/classes'), $this->classForm());

        $offering = $this->as(fn () => Offering::query()->firstOrFail());
        $response->assertRedirect($this->tenantUrl('kasun', "manage/classes/{$offering->id}"));

        $this->assertSame('2027-al-physics-theory', $offering->slug);
        $this->assertSame('2500.00', $offering->fee);
        $this->assertTrue($offering->past_lessons_access);
        $this->assertSame(3, $offering->grace_days);

        $this->actingAs($this->owner)->get($this->tenantUrl('kasun', 'manage/classes'))->assertOk()->assertSee('2027 A/L Physics Theory');
        $this->actingAs($this->owner)->get($this->tenantUrl('kasun', "manage/classes/{$offering->id}"))->assertOk()->assertSee('No lessons yet');
    }

    public function test_course_ignores_month_settings(): void
    {
        $this->actingAs($this->owner)->post($this->tenantUrl('kasun', 'manage/classes'), $this->classForm([
            'type' => 'course', 'title' => 'MS Office', 'access_days' => '90',
        ]));

        $course = $this->as(fn () => Offering::query()->firstOrFail());
        $this->assertTrue($course->isCourse());
        $this->assertSame(90, $course->access_days);
        $this->assertFalse($course->past_lessons_access);
        $this->assertSame(0, $course->grace_days);
    }

    public function test_teacher_adds_many_youtube_lessons_at_once(): void
    {
        $class = $this->makeOffering($this->kasun);

        $this->actingAs($this->owner)->get($this->tenantUrl('kasun', "manage/classes/{$class->id}/lessons/create"))->assertOk();

        $this->actingAs($this->owner)->post($this->tenantUrl('kasun', "manage/classes/{$class->id}/lessons"), [
            'links' => "https://youtu.be/AAAAAAAAAA1\nhttps://www.youtube.com/watch?v=BBBBBBBBBB2&t=10\n\nhttps://youtu.be/AAAAAAAAAA1",
            'month' => '2026-11',
            'visibility' => 'paid',
            'is_published' => '1',
        ])->assertRedirect($this->tenantUrl('kasun', "manage/classes/{$class->id}"));

        $lessons = $this->as(fn () => Lesson::query()->orderBy('sort')->get());
        $this->assertSame(['AAAAAAAAAA1', 'BBBBBBBBBB2'], $lessons->pluck('video_ref')->all(), 'duplicates removed');
        $this->assertSame('Kinematics – Part 1', $lessons[0]->title);
        $this->assertSame('2026-11', $lessons[0]->month);
    }

    public function test_bad_links_are_rejected(): void
    {
        $class = $this->makeOffering($this->kasun);

        $this->actingAs($this->owner)->post($this->tenantUrl('kasun', "manage/classes/{$class->id}/lessons"), [
            'links' => "https://vimeo.com/123\nhello",
            'month' => '2026-11',
            'visibility' => 'paid',
            'is_published' => '1',
        ])->assertSessionHasErrors('links');
    }

    public function test_teacher_edits_and_deletes_a_lesson(): void
    {
        $class = $this->makeOffering($this->kasun);
        $lesson = $this->makeLesson($class);

        $this->actingAs($this->owner)->get($this->tenantUrl('kasun', "manage/classes/{$class->id}/lessons/{$lesson->id}/edit"))->assertOk();

        $this->actingAs($this->owner)->put($this->tenantUrl('kasun', "manage/classes/{$class->id}/lessons/{$lesson->id}"), [
            'title' => 'Renamed',
            'video' => 'https://youtu.be/CCCCCCCCCC3',
            'month' => '2026-12',
            'visibility' => 'enrolled',
            'is_published' => '1',
        ])->assertRedirect();

        $lesson = $this->as(fn () => $lesson->fresh());
        $this->assertSame('Renamed', $lesson->title);
        $this->assertSame('CCCCCCCCCC3', $lesson->video_ref);
        $this->assertSame('2026-12', $lesson->month);

        $this->actingAs($this->owner)->delete($this->tenantUrl('kasun', "manage/classes/{$class->id}/lessons/{$lesson->id}"))->assertRedirect();
        $this->assertSoftDeleted('lessons', ['id' => $lesson->id]);
    }

    public function test_a_lesson_must_belong_to_the_class_in_the_url(): void
    {
        $a = $this->makeOffering($this->kasun, ['title' => 'A']);
        $b = $this->makeOffering($this->kasun, ['title' => 'B']);
        $lessonOfB = $this->makeLesson($b);

        $this->actingAs($this->owner)
            ->get($this->tenantUrl('kasun', "manage/classes/{$a->id}/lessons/{$lessonOfB->id}/edit"))
            ->assertNotFound();
    }

    public function test_class_with_students_cannot_be_deleted(): void
    {
        $class = $this->makeOffering($this->kasun);
        $this->enrol($this->makeStudent($this->kasun), $class);

        $this->actingAs($this->owner)->delete($this->tenantUrl('kasun', "manage/classes/{$class->id}"))->assertSessionHasErrors('offering');
        $this->assertNotSoftDeleted('offerings', ['id' => $class->id]);
    }

    public function test_teacher_cannot_touch_another_teachers_class(): void
    {
        $nimal = $this->makeTeacher('nimal', ownerPhone: '+94770000002');
        $foreign = $this->makeOffering($nimal);

        $this->actingAs($this->owner)->get($this->tenantUrl('kasun', "manage/classes/{$foreign->id}"))->assertNotFound();
        $this->actingAs($this->owner)->put($this->tenantUrl('kasun', "manage/classes/{$foreign->id}"), $this->classForm(['type' => null]))->assertNotFound();
    }

    public function test_assistant_needs_the_lessons_permission(): void
    {
        $without = User::factory()->assistant($this->kasun)->create(['permissions' => []]);
        $with = User::factory()->assistant($this->kasun)->create(['permissions' => ['lessons' => true]]);

        $this->actingAs($without)->get($this->tenantUrl('kasun', 'manage/classes'))->assertForbidden();
        $this->actingAs($with)->get($this->tenantUrl('kasun', 'manage/classes'))->assertOk();
    }

    public function test_students_cannot_open_the_class_manager(): void
    {
        $this->actingAs($this->makeStudent($this->kasun))->get($this->tenantUrl('kasun', 'manage/classes'))->assertForbidden();
    }

    public function test_youtube_lookup_returns_title_and_thumbnail(): void
    {
        $this->actingAs($this->owner)
            ->getJson($this->tenantUrl('kasun', 'manage/youtube/lookup?url='.urlencode('https://youtu.be/AAAAAAAAAA1')))
            ->assertOk()
            ->assertJson(['ok' => true, 'id' => 'AAAAAAAAAA1', 'title' => 'Kinematics – Part 1']);

        $this->actingAs($this->owner)->getJson($this->tenantUrl('kasun', 'manage/youtube/lookup?url=nope'))->assertStatus(422);
    }
}
