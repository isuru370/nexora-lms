<?php

namespace Database\Factories;

use App\Enums\Role;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'teacher_id' => null,
            'role' => Role::Student,
            'name' => fake()->name(),
            'phone' => '+947'.fake()->unique()->numerify('########'),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'status' => 'active',
            'remember_token' => Str::random(10),
        ];
    }

    public function superAdmin(): static
    {
        return $this->state(fn () => ['teacher_id' => null, 'role' => Role::SuperAdmin]);
    }

    public function teacherOwner(Teacher $teacher): static
    {
        return $this->state(fn () => ['teacher_id' => $teacher->id, 'role' => Role::Teacher]);
    }

    public function assistant(Teacher $teacher): static
    {
        return $this->state(fn () => ['teacher_id' => $teacher->id, 'role' => Role::Assistant, 'permissions' => []]);
    }

    public function student(Teacher $teacher): static
    {
        return $this->state(fn () => ['teacher_id' => $teacher->id, 'role' => Role::Student]);
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }
}
