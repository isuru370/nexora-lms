<?php

namespace App\Http\Requests\Manage;

use App\Enums\LessonVisibility;
use App\Models\Offering;
use App\Support\YouTube;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class UpdateLessonRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        /** @var Offering $offering */
        $offering = $this->route('offering');

        return [
            'title' => ['required', 'string', 'max:200'],
            'description' => ['nullable', 'string', 'max:2000'],
            'video' => ['required', 'string', 'max:300'],
            'month' => [$offering->isClass() ? 'required' : 'nullable', 'date_format:Y-m'],
            'visibility' => ['required', Rule::enum(LessonVisibility::class)],
            'available_until' => ['nullable', 'date'],
            'duration_minutes' => ['nullable', 'integer', 'min:1', 'max:1000'],
            'is_published' => ['required', 'boolean'],
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator) {
                if (! $validator->errors()->has('video') && YouTube::parseId($this->input('video')) === null) {
                    $validator->errors()->add('video', __('Paste a YouTube link.'));
                }
            },
        ];
    }

    public function videoId(): string
    {
        return (string) YouTube::parseId($this->input('video'));
    }
}
