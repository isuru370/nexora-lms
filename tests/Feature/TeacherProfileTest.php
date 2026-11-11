<?php

namespace Tests\Feature;

use App\Models\Teacher;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\Concerns\BuildsTenant;
use Tests\TestCase;

class TeacherProfileTest extends TestCase
{
    use BuildsTenant, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');
    }

    /**
     * @return array<string, mixed>
     */
    private function settingsForm(array $overrides = []): array
    {
        return array_merge([
            'display_name' => 'Kasun Perera',
            'subject_line' => 'A/L Physics',
            'bio' => 'Ten years of A/L Physics.',
            'whatsapp' => '077 555 1234',
            'brand_color' => '#2563EB',
        ], $overrides);
    }

    public function test_super_admin_can_add_photo_logo_and_bio_when_creating_a_teacher(): void
    {
        $this->actingAs($this->makeSuperAdmin())
            ->post($this->centralUrl('admin/teachers'), [
                'display_name' => 'Kasun Perera',
                'slug' => 'kasun',
                'bio' => 'A/L Physics teacher in Colombo.',
                'owner_name' => 'Kasun Perera',
                'phone' => '0771234567',
                'password' => 'temp-pass-123',
                'photo' => UploadedFile::fake()->image('me.jpg', 900, 600),
                'logo' => UploadedFile::fake()->image('logo.png', 1200, 400),
            ])
            ->assertRedirect($this->centralUrl('admin/teachers'));

        $teacher = Teacher::query()->where('slug', 'kasun')->firstOrFail();

        $this->assertSame('A/L Physics teacher in Colombo.', $teacher->bio);
        Storage::disk('public')->assertExists($teacher->photo_path);
        Storage::disk('public')->assertExists($teacher->logo_path);

        // Photo is cropped to a 512 square; logo fits inside 512 keeping its shape.
        [$pw, $ph] = getimagesizefromstring(Storage::disk('public')->get($teacher->photo_path));
        [$lw, $lh] = getimagesizefromstring(Storage::disk('public')->get($teacher->logo_path));
        $this->assertSame([512, 512], [$pw, $ph]);
        $this->assertSame(512, $lw);
        $this->assertLessThan(512, $lh);

        $this->get($this->tenantUrl('kasun'))
            ->assertOk()
            ->assertSee('A/L Physics teacher in Colombo.')
            ->assertSee('storage/'.$teacher->photo_path, false);
    }

    public function test_non_images_are_rejected(): void
    {
        $this->actingAs($this->makeSuperAdmin())
            ->post($this->centralUrl('admin/teachers'), [
                'display_name' => 'Kasun Perera',
                'slug' => 'kasun',
                'owner_name' => 'Kasun Perera',
                'phone' => '0771234567',
                'password' => 'temp-pass-123',
                'photo' => UploadedFile::fake()->create('virus.pdf', 10, 'application/pdf'),
            ])
            ->assertSessionHasErrors('photo');

        $this->assertSame(0, Teacher::query()->count());
    }

    public function test_teacher_can_update_settings_and_images(): void
    {
        $kasun = $this->makeTeacher('kasun');

        $this->actingAs($this->ownerOf($kasun))
            ->get($this->tenantUrl('kasun', 'manage/settings'))
            ->assertOk();

        $this->actingAs($this->ownerOf($kasun))
            ->put($this->tenantUrl('kasun', 'manage/settings'), $this->settingsForm([
                'photo' => UploadedFile::fake()->image('me.jpg', 600, 600),
            ]))
            ->assertRedirect($this->tenantUrl('kasun', 'manage/settings'))
            ->assertSessionHasNoErrors();

        $kasun->refresh();
        $this->assertSame('Ten years of A/L Physics.', $kasun->bio);
        $this->assertSame('+94775551234', $kasun->whatsapp);
        $this->assertSame('#2563eb', $kasun->brand_color);
        Storage::disk('public')->assertExists($kasun->photo_path);
    }

    public function test_replacing_and_removing_a_photo_deletes_the_old_file(): void
    {
        $kasun = $this->makeTeacher('kasun');
        $owner = $this->ownerOf($kasun);

        $this->actingAs($owner)->put($this->tenantUrl('kasun', 'manage/settings'), $this->settingsForm([
            'photo' => UploadedFile::fake()->image('a.jpg', 300, 300),
        ]));
        $first = $kasun->refresh()->photo_path;

        $this->actingAs($owner)->put($this->tenantUrl('kasun', 'manage/settings'), $this->settingsForm([
            'photo' => UploadedFile::fake()->image('b.jpg', 300, 300),
        ]));
        $second = $kasun->refresh()->photo_path;

        $this->assertNotSame($first, $second);
        Storage::disk('public')->assertMissing($first);

        $this->actingAs($owner)->put($this->tenantUrl('kasun', 'manage/settings'), $this->settingsForm([
            'remove_photo' => '1',
        ]));

        $this->assertNull($kasun->refresh()->photo_path);
        Storage::disk('public')->assertMissing($second);
    }

    public function test_assistants_and_students_cannot_open_settings(): void
    {
        $kasun = $this->makeTeacher('kasun');
        $assistant = User::factory()->assistant($kasun)->create();
        $student = $this->makeStudent($kasun);

        $this->actingAs($assistant)->get($this->tenantUrl('kasun', 'manage/settings'))->assertForbidden();
        $this->actingAs($student)->get($this->tenantUrl('kasun', 'manage/settings'))->assertForbidden();
        $this->actingAs($assistant)->put($this->tenantUrl('kasun', 'manage/settings'), $this->settingsForm())->assertForbidden();
    }

    public function test_a_teacher_can_never_change_another_teachers_settings(): void
    {
        $kasun = $this->makeTeacher('kasun', ownerPhone: '+94770000001');
        $nimal = $this->makeTeacher('nimal', ownerPhone: '+94770000002');

        // Even if Nimal's owner were somehow authenticated on Kasun's site.
        $this->actingAs($this->ownerOf($nimal))
            ->put($this->tenantUrl('kasun', 'manage/settings'), $this->settingsForm(['display_name' => 'Hacked']))
            ->assertForbidden();

        $this->assertNotSame('Hacked', $kasun->refresh()->display_name);
    }

    public function test_invalid_colour_and_whatsapp_are_rejected(): void
    {
        $kasun = $this->makeTeacher('kasun');

        $this->actingAs($this->ownerOf($kasun))
            ->put($this->tenantUrl('kasun', 'manage/settings'), $this->settingsForm([
                'brand_color' => 'red; background:url(x)',
                'whatsapp' => '123',
            ]))
            ->assertSessionHasErrors(['brand_color', 'whatsapp']);
    }
}
