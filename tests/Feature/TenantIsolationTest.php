<?php

namespace Tests\Feature;

use App\Models\Student;
use App\Models\Teacher;
use App\Tenancy\CurrentTeacher;
use App\Tenancy\TenantNotResolved;
use Illuminate\Auth\SessionGuard;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\BuildsTenant;
use Tests\TestCase;

/**
 * "A user of teacher A can never see anything of teacher B."
 */
class TenantIsolationTest extends TestCase
{
    use BuildsTenant, RefreshDatabase;

    private Teacher $kasun;

    private Teacher $nimal;

    protected function setUp(): void
    {
        parent::setUp();

        $this->kasun = $this->makeTeacher('kasun', ownerPhone: '+94770000001');
        $this->nimal = $this->makeTeacher('nimal', ownerPhone: '+94770000002');
    }

    public function test_tenant_models_only_return_the_current_teachers_rows(): void
    {
        $this->makeStudent($this->kasun, '+94710000001', 'Kasun Student');
        $this->makeStudent($this->nimal, '+94710000002', 'Nimal Student');

        $kasunStudents = $this->studentsAs($this->kasun);
        $nimalStudents = $this->studentsAs($this->nimal);

        $this->assertCount(1, $kasunStudents);
        $this->assertCount(1, $nimalStudents);
        $this->assertTrue($kasunStudents->every(fn (Student $s) => $s->teacher_id === $this->kasun->id));
        $this->assertTrue($nimalStudents->every(fn (Student $s) => $s->teacher_id === $this->nimal->id));
    }

    public function test_querying_a_tenant_model_without_a_tenant_fails_closed(): void
    {
        $this->expectException(TenantNotResolved::class);

        Student::query()->get();
    }

    public function test_creating_a_tenant_model_without_a_tenant_fails_closed(): void
    {
        $this->expectException(TenantNotResolved::class);

        Student::create(['user_id' => 1, 'student_code' => 'X-1']);
    }

    public function test_without_tenancy_sees_every_teacher_explicitly(): void
    {
        $this->makeStudent($this->kasun, '+94710000001');
        $this->makeStudent($this->nimal, '+94710000002');

        $this->assertSame(2, Teacher::withoutTenancy(fn () => Student::query()->count()));
    }

    public function test_teacher_cannot_log_in_to_another_teachers_panel(): void
    {
        // Kasun's owner phone + password, tried on Nimal's site.
        $this->post($this->tenantUrl('nimal', 'manage/login'), [
            'phone' => '0770000001',
            'password' => 'password',
        ])->assertSessionHasErrors('phone');

        $this->assertGuest();
    }

    public function test_student_cannot_log_in_on_another_teachers_site(): void
    {
        $this->makeStudent($this->kasun, '+94710000001');

        $this->post($this->tenantUrl('nimal', 'login'), [
            'phone' => '0710000001',
            'password' => 'password',
        ])->assertSessionHasErrors('phone');

        $this->assertGuest();
    }

    public function test_a_session_from_one_teacher_site_is_not_valid_on_another(): void
    {
        $owner = $this->ownerOf($this->kasun);
        $sessionKey = 'login_web_'.sha1(SessionGuard::class);

        // Same session cookie value replayed on Nimal's subdomain.
        $this->withSession([$sessionKey => $owner->id])
            ->get($this->tenantUrl('nimal', 'manage'))
            ->assertRedirect($this->tenantUrl('nimal', 'manage/login'));

        // ...while it works on Kasun's own site.
        app(CurrentTeacher::class)->clear();
        $this->withSession([$sessionKey => $owner->id])
            ->get($this->tenantUrl('kasun', 'manage'))
            ->assertOk();
    }

    public function test_same_phone_can_be_a_student_of_two_teachers_as_separate_accounts(): void
    {
        $a = $this->makeStudent($this->kasun, '+94710000009');
        $b = $this->makeStudent($this->nimal, '+94710000009');

        $this->assertNotSame($a->id, $b->id);

        $this->post($this->tenantUrl('nimal', 'login'), [
            'phone' => '0710000009',
            'password' => 'password',
        ])->assertRedirect($this->tenantUrl('nimal', 'dashboard'));

        $this->assertAuthenticatedAs($b);
    }

    public function test_teacher_dashboard_lists_only_own_students(): void
    {
        $this->makeStudent($this->kasun, '+94710000001', 'Amaya From Kasun');
        $this->makeStudent($this->nimal, '+94710000002', 'Bimal From Nimal');

        $this->actingAs($this->ownerOf($this->kasun))
            ->get($this->tenantUrl('kasun', 'manage'))
            ->assertOk()
            ->assertSee('Amaya From Kasun')
            ->assertDontSee('Bimal From Nimal');
    }
}
