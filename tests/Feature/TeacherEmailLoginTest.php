<?php

namespace Tests\Feature;

use App\Models\Teacher;
use App\Models\User;
use App\Notifications\PanelPasswordReset;
use App\Notifications\TeacherInvitation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Tests\Concerns\BuildsTenant;
use Tests\TestCase;

class TeacherEmailLoginTest extends TestCase
{
    use BuildsTenant, RefreshDatabase;

    private function createTeacherAsAdmin(): Teacher
    {
        $this->actingAs($this->makeSuperAdmin())
            ->post($this->centralUrl('admin/teachers'), [
                'display_name' => 'Kasun Perera',
                'slug' => 'kasun',
                'owner_name' => 'Kasun Perera',
                'phone' => '077 123 4567',
                'email' => 'Kasun@Example.com',
            ])
            ->assertRedirect($this->centralUrl('admin/teachers'));

        auth()->logout();

        return Teacher::query()->where('slug', 'kasun')->firstOrFail();
    }

    private function inviteLinkFor(User $owner): string
    {
        $link = null;

        Notification::assertSentTo($owner, TeacherInvitation::class, function (TeacherInvitation $n) use (&$link) {
            $link = $n->link;

            return true;
        });

        return $link;
    }

    public function test_invite_email_contains_the_site_and_a_working_set_password_link(): void
    {
        Notification::fake();

        $teacher = $this->createTeacherAsAdmin();
        $owner = $teacher->owner()->firstOrFail();
        $link = $this->inviteLinkFor($owner);

        $this->assertSame('kasun@example.com', $owner->email);
        $this->assertStringStartsWith($this->tenantUrl('kasun', 'manage/set-password/'), $link);

        $mail = (new TeacherInvitation($teacher, $link))->toMail($owner)->render();
        $this->assertStringContainsString($teacher->url(), (string) $mail);

        // Open the link and set a password -> logged in to the panel.
        $this->get($link)->assertOk()->assertSee('Set your password')->assertSee('kasun@example.com');

        $this->post($link, ['password' => 'new-secret-1', 'password_confirmation' => 'new-secret-1'])
            ->assertRedirect($this->tenantUrl('kasun', 'manage'));

        $this->assertAuthenticatedAs($owner);
    }

    public function test_the_invite_link_works_only_once(): void
    {
        Notification::fake();

        $owner = $this->createTeacherAsAdmin()->owner()->firstOrFail();
        $link = $this->inviteLinkFor($owner);

        $this->post($link, ['password' => 'new-secret-1', 'password_confirmation' => 'new-secret-1']);
        auth()->logout();

        $this->get($link)->assertForbidden()->assertSee('no longer valid');
        $this->post($link, ['password' => 'other-pass-2', 'password_confirmation' => 'other-pass-2'])->assertForbidden();
    }

    public function test_a_link_for_one_teacher_site_does_not_work_on_another(): void
    {
        Notification::fake();
        $this->makeTeacher('nimal', ownerPhone: '+94770000002');

        $owner = $this->createTeacherAsAdmin()->owner()->firstOrFail();
        $link = $this->inviteLinkFor($owner);

        $foreign = str_replace('//kasun.', '//nimal.', $link);

        $this->get($foreign)->assertForbidden();
    }

    public function test_tampered_or_expired_links_are_rejected(): void
    {
        Notification::fake();

        $owner = $this->createTeacherAsAdmin()->owner()->firstOrFail();
        $link = $this->inviteLinkFor($owner);

        $this->get($link.'x')->assertForbidden();

        $this->travel(49)->hours();
        $this->get($link)->assertForbidden();
    }

    public function test_teacher_logs_in_with_email_or_phone(): void
    {
        $kasun = $this->makeTeacher('kasun');
        $owner = $this->ownerOf($kasun);
        $owner->update(['email' => 'kasun@example.com']);

        $this->post($this->tenantUrl('kasun', 'manage/login'), ['login' => 'KASUN@example.com', 'password' => 'password'])
            ->assertRedirect($this->tenantUrl('kasun', 'manage'));
        $this->assertAuthenticatedAs($owner);

        auth()->logout();

        $this->post($this->tenantUrl('kasun', 'manage/login'), ['login' => '077 000 0001', 'password' => 'password'])
            ->assertRedirect($this->tenantUrl('kasun', 'manage'));
        $this->assertAuthenticatedAs($owner);
    }

    public function test_email_login_is_limited_to_the_current_site(): void
    {
        $kasun = $this->makeTeacher('kasun', ownerPhone: '+94770000001');
        $this->makeTeacher('nimal', ownerPhone: '+94770000002');
        $this->ownerOf($kasun)->update(['email' => 'kasun@example.com']);

        $this->post($this->tenantUrl('nimal', 'manage/login'), ['login' => 'kasun@example.com', 'password' => 'password'])
            ->assertSessionHasErrors('login');

        $this->assertGuest();
    }

    public function test_forgot_password_sends_a_reset_link_only_to_real_panel_users(): void
    {
        Notification::fake();

        $kasun = $this->makeTeacher('kasun');
        $owner = $this->ownerOf($kasun);
        $owner->update(['email' => 'kasun@example.com']);

        $this->post($this->tenantUrl('kasun', 'manage/forgot-password'), ['email' => 'kasun@example.com'])
            ->assertSessionHas('status');
        Notification::assertSentTo($owner, PanelPasswordReset::class);

        // Unknown email: same answer, nothing sent.
        $this->post($this->tenantUrl('kasun', 'manage/forgot-password'), ['email' => 'nobody@example.com'])
            ->assertSessionHas('status');
        Notification::assertSentTimes(PanelPasswordReset::class, 1);
    }

    public function test_reset_link_sets_a_new_password(): void
    {
        Notification::fake();

        $kasun = $this->makeTeacher('kasun');
        $owner = $this->ownerOf($kasun);
        $owner->update(['email' => 'kasun@example.com']);

        $this->post($this->tenantUrl('kasun', 'manage/forgot-password'), ['email' => 'kasun@example.com']);

        $link = null;
        Notification::assertSentTo($owner, PanelPasswordReset::class, function ($n) use (&$link) {
            $link = $n->link;

            return true;
        });

        $this->post($link, ['password' => 'brand-new-9', 'password_confirmation' => 'brand-new-9'])
            ->assertRedirect($this->tenantUrl('kasun', 'manage'));

        auth()->logout();
        $this->post($this->tenantUrl('kasun', 'manage/login'), ['login' => 'kasun@example.com', 'password' => 'brand-new-9'])
            ->assertRedirect($this->tenantUrl('kasun', 'manage'));
    }

    public function test_admin_can_resend_the_invite(): void
    {
        Notification::fake();

        $kasun = $this->makeTeacher('kasun');
        $owner = $this->ownerOf($kasun);

        $this->actingAs($this->makeSuperAdmin())
            ->post($this->centralUrl("admin/teachers/{$kasun->id}/invite"))
            ->assertSessionHas('status');

        Notification::assertSentTo($owner, TeacherInvitation::class);
    }

    public function test_teacher_is_still_created_when_the_email_fails(): void
    {
        Mail::shouldReceive('mailer')->andThrow(new \RuntimeException('SMTP down'));
        config(['mail.default' => 'smtp']);

        $this->actingAs($this->makeSuperAdmin())
            ->post($this->centralUrl('admin/teachers'), [
                'display_name' => 'Kasun Perera',
                'slug' => 'kasun',
                'owner_name' => 'Kasun Perera',
                'phone' => '077 123 4567',
                'email' => 'kasun@example.com',
            ])
            ->assertRedirect($this->centralUrl('admin/teachers'))
            ->assertSessionHas('status', fn ($status) => str_contains($status, 'could not be sent'));

        $this->assertTrue(Teacher::query()->where('slug', 'kasun')->exists());
    }
}
