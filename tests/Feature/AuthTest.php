<?php

namespace Tests\Feature;

use App\Enums\Role;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\BuildsTenant;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use BuildsTenant, RefreshDatabase;

    private Teacher $kasun;

    protected function setUp(): void
    {
        parent::setUp();

        $this->kasun = $this->makeTeacher('kasun');
    }

    public function test_student_can_register_and_gets_a_student_code(): void
    {
        $this->post($this->tenantUrl('kasun', 'register'), [
            'name' => 'Amaya Silva',
            'phone' => '071 234 5678',
            'school' => 'Visakha Vidyalaya',
            'grade' => '2027 A/L',
            'password' => 'secret-pass',
            'password_confirmation' => 'secret-pass',
        ])->assertRedirect($this->tenantUrl('kasun', 'dashboard'));

        $user = User::query()->where('phone', '+94712345678')->firstOrFail();

        $this->assertSame($this->kasun->id, $user->teacher_id);
        $this->assertSame(Role::Student, $user->role);
        $this->assertAuthenticatedAs($user);
        $this->assertSame('KAS-00001', Teacher::withoutTenancy(fn () => Student::query()->where('user_id', $user->id)->value('student_code')));
    }

    public function test_student_codes_increase_per_teacher(): void
    {
        $nimal = $this->makeTeacher('nimal', ownerPhone: '+94770000002');

        $this->makeStudent($this->kasun, '+94710000001');
        $this->makeStudent($this->kasun, '+94710000002');
        $this->makeStudent($nimal, '+94710000003');

        $this->assertEqualsCanonicalizing(['KAS-00001', 'KAS-00002'], $this->studentsAs($this->kasun)->pluck('student_code')->all());
        $this->assertSame(['NIM-00001'], $this->studentsAs($nimal)->pluck('student_code')->all());
    }

    public function test_registering_the_same_phone_twice_on_one_site_fails(): void
    {
        $this->makeStudent($this->kasun, '+94712345678');

        $this->post($this->tenantUrl('kasun', 'register'), [
            'name' => 'Someone Else',
            'phone' => '0712345678',
            'password' => 'secret-pass',
            'password_confirmation' => 'secret-pass',
        ])->assertSessionHasErrors('phone');
    }

    public function test_student_can_log_in_with_any_phone_format(): void
    {
        $student = $this->makeStudent($this->kasun, '+94712345678');

        $this->post($this->tenantUrl('kasun', 'login'), [
            'phone' => '71 234 5678',
            'password' => 'password',
        ])->assertRedirect($this->tenantUrl('kasun', 'dashboard'));

        $this->assertAuthenticatedAs($student);
    }

    public function test_wrong_password_is_rejected(): void
    {
        $this->makeStudent($this->kasun, '+94712345678');

        $this->post($this->tenantUrl('kasun', 'login'), [
            'phone' => '0712345678',
            'password' => 'wrong',
        ])->assertSessionHasErrors('phone');

        $this->assertGuest();
    }

    public function test_login_is_rate_limited(): void
    {
        $this->makeStudent($this->kasun, '+94712345678');

        foreach (range(1, 5) as $ignored) {
            $this->post($this->tenantUrl('kasun', 'login'), ['phone' => '0712345678', 'password' => 'wrong']);
        }

        $this->post($this->tenantUrl('kasun', 'login'), ['phone' => '0712345678', 'password' => 'password'])
            ->assertSessionHasErrors('phone');

        $this->assertGuest();
    }

    public function test_student_cannot_use_the_teacher_panel_login(): void
    {
        $this->makeStudent($this->kasun, '+94712345678');

        $this->post($this->tenantUrl('kasun', 'manage/login'), [
            'phone' => '0712345678',
            'password' => 'password',
        ])->assertSessionHasErrors('phone');

        $this->assertGuest();
    }

    public function test_student_cannot_open_the_teacher_panel(): void
    {
        $student = $this->makeStudent($this->kasun, '+94712345678');

        $this->actingAs($student)
            ->get($this->tenantUrl('kasun', 'manage'))
            ->assertForbidden();
    }

    public function test_teacher_can_log_in_to_the_panel(): void
    {
        $this->post($this->tenantUrl('kasun', 'manage/login'), [
            'phone' => '077 000 0001',
            'password' => 'password',
        ])->assertRedirect($this->tenantUrl('kasun', 'manage'));

        $this->assertAuthenticatedAs($this->ownerOf($this->kasun));

        $this->get($this->tenantUrl('kasun', 'manage'))->assertOk()->assertSee($this->kasun->display_name);
    }

    public function test_teacher_cannot_open_the_student_dashboard(): void
    {
        $this->actingAs($this->ownerOf($this->kasun))
            ->get($this->tenantUrl('kasun', 'dashboard'))
            ->assertForbidden();
    }

    public function test_guests_are_sent_to_the_right_login_page(): void
    {
        $this->get($this->tenantUrl('kasun', 'manage'))->assertRedirect($this->tenantUrl('kasun', 'manage/login'));
        $this->get($this->tenantUrl('kasun', 'dashboard'))->assertRedirect($this->tenantUrl('kasun', 'login'));
        $this->get($this->centralUrl('admin'))->assertRedirect($this->centralUrl('admin/login'));
    }

    public function test_suspended_user_cannot_log_in(): void
    {
        $student = $this->makeStudent($this->kasun, '+94712345678');
        $student->update(['status' => 'suspended']);

        $this->post($this->tenantUrl('kasun', 'login'), [
            'phone' => '0712345678',
            'password' => 'password',
        ])->assertSessionHasErrors('phone');

        $this->assertGuest();
    }

    public function test_student_can_log_out(): void
    {
        $student = $this->makeStudent($this->kasun, '+94712345678');

        $this->actingAs($student)
            ->post($this->tenantUrl('kasun', 'logout'))
            ->assertRedirect($this->tenantUrl('kasun'));

        $this->assertGuest();
    }
}
