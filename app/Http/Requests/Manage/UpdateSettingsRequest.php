<?php

namespace App\Http\Requests\Manage;

use App\Enums\Role;
use App\Support\Phone;
use Illuminate\Foundation\Http\FormRequest;

class UpdateSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasRole(Role::Teacher) ?? false;
    }

    protected function prepareForValidation(): void
    {
        $whatsapp = $this->input('whatsapp');

        $this->merge([
            'whatsapp' => blank($whatsapp) ? null : (Phone::normalize($whatsapp) ?? $whatsapp),
            'brand_color' => blank($this->input('brand_color')) ? null : strtolower((string) $this->input('brand_color')),
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
            'whatsapp' => ['nullable', 'string', 'regex:/^\+94\d{9}$/'],
            'brand_color' => ['nullable', 'regex:/^#[0-9a-f]{6}$/'],
            'photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048', 'dimensions:min_width=128,min_height=128'],
            'logo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048', 'dimensions:min_width=64,min_height=64'],
            'remove_photo' => ['nullable', 'boolean'],
            'remove_logo' => ['nullable', 'boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'whatsapp.regex' => __('Enter a valid mobile number, e.g. 077 123 4567.'),
            'brand_color.regex' => __('Pick a colour.'),
            'photo.dimensions' => __('The photo must be at least 128 × 128 pixels.'),
            'logo.dimensions' => __('The logo must be at least 64 × 64 pixels.'),
        ];
    }
}
