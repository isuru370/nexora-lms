<?php

namespace Tests\Feature;

use App\Enums\TeacherStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\BuildsTenant;
use Tests\TestCase;

class TenantResolutionTest extends TestCase
{
    use BuildsTenant, RefreshDatabase;

    public function test_central_site_loads(): void
    {
        $this->get($this->centralUrl())->assertOk()->assertSee('NexoraLMS');
    }

    public function test_bare_base_domain_redirects_to_central_site(): void
    {
        $this->get('http://nexoralms.test/')->assertRedirect($this->centralUrl());
    }

    public function test_teacher_site_shows_that_teacher(): void
    {
        $kasun = $this->makeTeacher('kasun');
        $nimal = $this->makeTeacher('nimal', ownerPhone: '+94770000002');

        $this->get($this->tenantUrl('kasun'))
            ->assertOk()
            ->assertSee($kasun->display_name)
            ->assertDontSee($nimal->display_name);
    }

    public function test_unknown_subdomain_is_404(): void
    {
        $this->get($this->tenantUrl('nobody'))->assertNotFound();
    }

    public function test_foreign_host_is_404(): void
    {
        $this->get('http://example.com/')->assertNotFound();
    }

    public function test_tenant_pages_do_not_exist_on_central_site(): void
    {
        $this->get($this->centralUrl('login'))->assertNotFound();
        $this->get($this->centralUrl('manage/login'))->assertNotFound();
    }

    public function test_admin_pages_do_not_exist_on_teacher_site(): void
    {
        $this->makeTeacher('kasun');

        $this->get($this->tenantUrl('kasun', 'admin/login'))->assertNotFound();
    }

    public function test_locked_teacher_site_is_unavailable_but_panel_login_works(): void
    {
        $this->makeTeacher('kasun', TeacherStatus::Locked);

        $this->get($this->tenantUrl('kasun'))
            ->assertForbidden()
            ->assertSee('temporarily unavailable');

        $this->get($this->tenantUrl('kasun', 'manage/login'))->assertOk();
    }
}
