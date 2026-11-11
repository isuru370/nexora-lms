<?php

namespace App\Http\Requests\Student;

use App\Support\Phone;
use App\Tenancy\CurrentTeacher;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'phone' => Phone::normalize($this->input('phone')) ?? $this->input('phone'),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $teacherId = app(CurrentTeacher::class)->id();

        return [
            'name' => ['required', 'string', 'max:100'],
            'phone' => [
                'required', 'string', 'regex:/^\+94\d{9}$/',
                Rule::unique('users', 'phone')->where('teacher_id', $teacherId),
            ],
            'school' => ['nullable', 'string', 'max:150'],
            'grade' => ['nullable', 'string', 'max:30'],
            'password' => ['required', 'confirmed', Password::min(8)],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'phone.regex' => __('Enter a valid mobile number, e.g. 077 123 4567.'),
            'phone.unique' => __('This number is already registered. Please log in.'),
        ];
    }
}
