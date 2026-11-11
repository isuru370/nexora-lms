<?php

namespace App\Http\Requests\Manage;

use App\Enums\OfferingType;
use App\Enums\OldLessonsAccess;
use App\Models\Offering;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class OfferingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // route middleware checks role + permission
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'fee' => str_replace([',', ' '], '', (string) $this->input('fee')),
            'access_days' => blank($this->input('access_days')) ? null : $this->input('access_days'),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $creating = $this->route('offering') === null;

        return [
            'type' => [$creating ? 'required' : 'prohibited', Rule::enum(OfferingType::class)],
            'title' => ['required', 'string', 'max:120'],
            'subject' => ['required', 'string', 'max:80'],
            'grade' => ['nullable', 'string', 'max:40'],
            'medium' => ['nullable', Rule::in(array_keys(Offering::MEDIUMS))],
            'fee' => ['required', 'numeric', 'min:0', 'max:1000000'],
            'description' => ['nullable', 'string', 'max:2000'],
            'access_days' => ['nullable', 'integer', 'min:1', 'max:3650'],
            'starts_on' => ['nullable', 'date'],
            'ends_on' => ['nullable', 'date', 'after_or_equal:starts_on'],
            'old_lessons_access' => ['required', Rule::enum(OldLessonsAccess::class)],
            'past_lessons_access' => ['required', 'boolean'],
            'grace_days' => ['required', 'integer', 'min:0', 'max:7'],
            'is_published' => ['required', 'boolean'],
        ];
    }
}
