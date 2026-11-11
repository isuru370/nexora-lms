<?php

namespace Database\Factories;

use App\Enums\TeacherStatus;
use App\Models\Teacher;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Teacher>
 */
class TeacherFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->firstName();

        return [
            'slug' => strtolower($name).fake()->unique()->numberBetween(10, 9999),
            'display_name' => $name.' Sir',
            'subject_line' => 'A/L Physics',
            'phone' => '+947'.fake()->unique()->numerify('########'),
            'status' => TeacherStatus::Trial,
            'trial_ends_on' => now()->addDays(30)->toDateString(),
        ];
    }

    public function slug(string $slug): static
    {
        return $this->state(fn () => ['slug' => $slug]);
    }

    public function status(TeacherStatus $status): static
    {
        return $this->state(fn () => ['status' => $status]);
    }
}
