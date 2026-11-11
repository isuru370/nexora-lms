<?php

namespace Database\Seeders;

use App\Enums\Role;
use App\Models\Teacher;
use App\Models\User;
use App\Services\Students\RegisterStudent;
use App\Services\Teachers\CreateTeacher;
use App\Tenancy\CurrentTeacher;
use Illuminate\Database\Seeder;

/**
 * Local demo data. Every password is "password".
 *
 *   Super Admin  app.<base>/admin          admin@nexoralms.test
 *   Teacher      kasun.<base>/manage       077 000 0001
 *   Teacher      nimal.<base>/manage       077 000 0002
 *   Student      kasun.<base>/login        071 000 0001
 *   Student      nimal.<base>/login        071 000 0001  (same number, separate account)
 */
class DatabaseSeeder extends Seeder
{
    public function run(CreateTeacher $createTeacher, RegisterStudent $registerStudent, CurrentTeacher $current): void
    {
        if (app()->isProduction()) {
            $this->command?->warn('Demo seeder skipped in production.');

            return;
        }

        $this->call(PlatformSeeder::class);

        User::query()->firstOrCreate(
            ['email' => 'admin@nexoralms.test', 'teacher_id' => null],
            ['name' => 'Nexora Admin', 'role' => Role::SuperAdmin, 'password' => 'password'],
        );

        $demo = [
            ['slug' => 'kasun', 'display_name' => 'Kasun Perera', 'subject_line' => 'A/L Physics · Sinhala medium', 'owner_name' => 'Kasun Perera', 'phone' => '+94770000001'],
            ['slug' => 'nimal', 'display_name' => 'Nimal Silva', 'subject_line' => 'O/L Maths · English medium', 'owner_name' => 'Nimal Silva', 'phone' => '+94770000002'],
        ];

        foreach ($demo as $data) {
            $teacher = Teacher::query()->where('slug', $data['slug'])->first()
                ?? $createTeacher->handle($data + ['email' => null, 'password' => 'password']);

            $current->runAs($teacher, function () use ($teacher, $registerStudent) {
                $exists = User::query()
                    ->where('teacher_id', $teacher->id)
                    ->where('phone', '+94710000001')
                    ->exists();

                if (! $exists) {
                    $registerStudent->handle([
                        'name' => 'Demo Student',
                        'phone' => '+94710000001',
                        'password' => 'password',
                        'school' => 'Royal College',
                        'grade' => '2027 A/L',
                    ]);
                }
            });
        }
    }
}
