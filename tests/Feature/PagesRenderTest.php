<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\BuildsTenant;
use Tests\TestCase;

/**
 * Every Phase 0 screen renders without errors.
 */
class PagesRenderTest extends TestCase
{
    use BuildsTenant, RefreshDatabase;

    public function test_guest_pages_render(): void
    {
        $this->makeTeacher('kasun');

        $this->get($this->centralUrl('admin/login'))->assertOk()->assertSee('Admin login');
        $this->get($this->tenantUrl('kasun', 'login'))->assertOk()->assertSee('Welcome back');
        $this->get($this->tenantUrl('kasun', 'register'))->assertOk()->assertSee('Create your account');
        $this->get($this->tenantUrl('kasun', 'manage/login'))->assertOk()->assertSee('Teacher panel');
    }

    public function test_signed_in_pages_render(): void
    {
        $kasun = $this->makeTeacher('kasun');
        $student = $this->makeStudent($kasun, '+94712345678', 'Amaya Silva');

        $this->actingAs($student)
            ->get($this->tenantUrl('kasun', 'dashboard'))
            ->assertOk()
            ->assertSee('Amaya Silva')
            ->assertSee('KAS-00001');

        $this->actingAs($this->ownerOf($kasun))
            ->get($this->tenantUrl('kasun', 'manage'))
            ->assertOk()
            ->assertSee('days left in your free trial');

        $this->actingAs($this->makeSuperAdmin())
            ->get($this->centralUrl('admin'))
            ->assertOk()
            ->assertSee('Latest teachers');
    }

    public function test_logged_in_users_skip_the_login_page(): void
    {
        $kasun = $this->makeTeacher('kasun');
        $student = $this->makeStudent($kasun);

        $this->actingAs($student)
            ->get($this->tenantUrl('kasun', 'login'))
            ->assertRedirect($this->tenantUrl('kasun', 'dashboard'));
    }
}
