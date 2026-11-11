<?php

use App\Enums\Role;
use App\Models\User;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Validator;

use function Laravel\Prompts\password;
use function Laravel\Prompts\text;

Artisan::command('nexora:create-super-admin', function () {
    $name = text('Name', required: true);
    $email = text('Email', required: true, validate: fn (string $value) => Validator::make(
        ['email' => $value],
        ['email' => ['email']],
    )->fails() ? 'Enter a valid email.' : null);
    $secret = password('Password (min 8 characters)', required: true, validate: fn (string $value) => strlen($value) < 8 ? 'At least 8 characters.' : null);

    $exists = User::query()->whereNull('teacher_id')->where('email', $email)->exists();

    if ($exists) {
        $this->error('A platform user with this email already exists.');

        return 1;
    }

    User::create([
        'teacher_id' => null,
        'role' => Role::SuperAdmin,
        'name' => $name,
        'email' => $email,
        'password' => $secret,
    ]);

    $this->info("Super Admin {$email} created. Log in at ".route('admin.login'));

    return 0;
})->purpose('Create a NexoraLMS Super Admin');
