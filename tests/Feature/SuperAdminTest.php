<?php

namespace Tests\Feature;

use App\Enums\Role;
use App\Enums\TeacherStatus;
use App\Models\AuditLog;
use App\Models\Teacher;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\BuildsTenant;
use Tests\TestCase;

class SuperAdminTest extends TestCase
{
    use BuildsTenant, RefreshDatabase;

    /**
     * @return array<string, string>
     */
    private function teacherForm(array $overrides = []): array
    {
        return array_merge([
            'display_name' => 'Kasun Perera',
            'subject_line' => 'A/L Physics',
            'slug' => 'kasun',
            'owner_name' => 'Kasun Perera',
            'phone' => '077 123 4567',
            'email' => 'kasun@example.com',
            'password' => 'temp-pass-123',
        ], $overrides);
    }

    public function test_super_admin_can_log_in(): void
    {
        $admin = $this->makeSuperAdmin();

        $this->post($this->centralUrl('admin/login'), [
            'email' => 'admin@nexoralms.test',
            'password' => 'password',
        ])->assertRedirect($this->centralUrl('admin'));

        $this->assertAuthenticatedAs($admin);
        $this->get($this->centralUrl('admin'))->assertOk();
    }

    public function test_teacher_users_cannot_log_in_to_admin(): void
    {
        $teacher = $this->makeTeacher('kasun');
        $this->ownerOf($teacher)->update(['email' => 'owner@example.com']);

        $this->post($this->centralUrl('admin/login'), [
            'email' => 'owner@example.com',
            'password' => 'password',
        ])->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_super_admin_creates_a_teacher_on_trial_with_an_owner_login(): void
    {
        $this->actingAs($this->makeSuperAdmin())
            ->post($this->centralUrl('admin/teachers'), $this->teacherForm())
            ->assertRedirect($this->centralUrl('admin/teachers'))
            ->assertSessionHas('status');

        $teacher = Teacher::query()->where('slug', 'kasun')->firstOrFail();
        $owner = $teacher->owner()->firstOrFail();

        $this->assertSame(TeacherStatus::Trial, $teacher->status);
        $this->assertNotNull($teacher->trial_ends_on);
        $this->assertSame(Role::Teacher, $owner->role);
        $this->assertSame('+94771234567', $owner->phone);
        $this->assertTrue(AuditLog::query()->where('action', 'teacher.created')->exists());

        // The new teacher can log in on their own site.
        auth()->logout();
        $this->post($this->tenantUrl('kasun', 'manage/login'), [
            'phone' => '0771234567',
            'password' => 'temp-pass-123',
        ])->assertRedirect($this->tenantUrl('kasun', 'manage'));
    }

    public function test_reserved_and_duplicate_site_names_are_rejected(): void
    {
        $admin = $this->makeSuperAdmin();
        $this->makeTeacher('kasun');

        $this->actingAs($admin)
            ->post($this->centralUrl('admin/teachers'), $this->teacherForm(['slug' => 'admin']))
            ->assertSessionHasErrors('slug');

        $this->actingAs($admin)
            ->post($this->centralUrl('admin/teachers'), $this->teacherForm(['slug' => 'kasun']))
            ->assertSessionHasErrors('slug');

        $this->actingAs($admin)
            ->post($this->centralUrl('admin/teachers'), $this->teacherForm(['slug' => 'Bad Name!']))
            ->assertSessionHasErrors('slug');
    }

    public function test_teachers_list_and_create_pages_render(): void
    {
        $admin = $this->makeSuperAdmin();
        $this->makeTeacher('kasun');

        $this->actingAs($admin)->get($this->centralUrl('admin/teachers'))->assertOk()->assertSee('kasun');
        $this->actingAs($admin)->get($this->centralUrl('admin/teachers/create'))->assertOk();
    }

    public function test_guests_cannot_create_teachers(): void
    {
        $this->post($this->centralUrl('admin/teachers'), $this->teacherForm())
            ->assertRedirect($this->centralUrl('admin/login'));

        $this->assertSame(0, Teacher::query()->count());
    }
}
