<?php

namespace App\Http\Requests\Admin;

use App\Enums\Role;
use App\Support\Phone;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTeacherRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasRole(Role::SuperAdmin) ?? false;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'slug' => strtolower(trim((string) $this->input('slug'))),
            'email' => strtolower(trim((string) $this->input('email'))),
            'phone' => Phone::normalize($this->input('phone')) ?? $this->input('phone'),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'display_name' => ['required', 'string', 'max:100'],
            'subject_line' => ['nullable', 'string', 'max:150'],
            'bio' => ['nullable', 'string', 'max:300'],
            'photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048', 'dimensions:min_width=128,min_height=128'],
            'logo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048', 'dimensions:min_width=64,min_height=64'],
            'slug' => [
                'required', 'string', 'min:3', 'max:30',
                'regex:/^[a-z0-9](?:[a-z0-9-]*[a-z0-9])?$/',
                Rule::notIn(config('nexora.reserved_slugs')),
                Rule::unique('teachers', 'slug'),
            ],
            'owner_name' => ['required', 'string', 'max:100'],
            'phone' => ['required', 'string', 'regex:/^\+94\d{9}$/'],
            'email' => ['required', 'email', 'max:150'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'slug.regex' => __('Use lowercase letters, numbers and dashes only.'),
            'slug.not_in' => __('This site name is reserved.'),
            'slug.unique' => __('This site name is already taken.'),
            'phone.regex' => __('Enter a valid mobile number, e.g. 077 123 4567.'),
            'photo.dimensions' => __('The photo must be at least 128 × 128 pixels.'),
            'logo.dimensions' => __('The logo must be at least 64 × 64 pixels.'),
        ];
    }
}
