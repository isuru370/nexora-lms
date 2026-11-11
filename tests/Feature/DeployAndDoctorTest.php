<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\BuildsTenant;
use Tests\TestCase;

class DeployAndDoctorTest extends TestCase
{
    use BuildsTenant, RefreshDatabase;

    public function test_doctor_passes_locally_when_the_basics_are_in_place(): void
    {
        $this->makeSuperAdmin();

        $this->artisan('nexora:doctor')
            ->expectsOutputToContain('Database connection')
            ->expectsOutputToContain('Ready (local)')
            ->assertSuccessful();
    }

    public function test_doctor_fails_without_a_super_admin(): void
    {
        $this->artisan('nexora:doctor')
            ->expectsOutputToContain('nexora:create-super-admin')
            ->expectsOutputToContain('NOT READY')
            ->assertFailed();
    }

    public function test_doctor_fails_when_session_cookies_are_shared_across_subdomains(): void
    {
        $this->makeSuperAdmin();
        config(['session.domain' => '.nexoralms.lk']);

        $this->artisan('nexora:doctor')
            ->expectsOutputToContain('SESSION_DOMAIN must be empty')
            ->assertFailed();
    }

    public function test_production_rules_fail_a_local_setup(): void
    {
        $this->makeSuperAdmin();
        config(['app.debug' => true]);

        $this->artisan('nexora:doctor', ['--production' => true])
            ->expectsOutputToContain('Set APP_DEBUG=false')
            ->expectsOutputToContain('NOT READY')
            ->assertFailed();
    }

    public function test_deploy_runs_every_step_then_the_doctor(): void
    {
        $this->makeSuperAdmin();
        $linkExisted = file_exists(public_path('storage'));

        try {
            $this->artisan('nexora:deploy')
                ->expectsOutputToContain('Database migrations')
                ->expectsOutputToContain('Platform data')
                ->expectsOutputToContain('Restart queue workers')
                ->expectsOutputToContain('Ready (local)')
                ->assertSuccessful();
        } finally {
            if (! $linkExisted && is_link(public_path('storage'))) {
                unlink(public_path('storage'));
            }
        }
    }
}
