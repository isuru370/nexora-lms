<?php

namespace App\Http\Requests\Manage;

use App\Enums\LessonVisibility;
use App\Models\Offering;
use App\Support\Month;
use App\Support\YouTube;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreLessonsRequest extends FormRequest
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
            'links' => ['required', 'string', 'max:5000'],
            'month' => [$offering->isClass() ? 'required' : 'nullable', 'date_format:Y-m'],
            'visibility' => ['required', Rule::enum(LessonVisibility::class)],
            'available_until' => ['nullable', 'date', 'after_or_equal:today'],
            'is_published' => ['required', 'boolean'],
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator) {
                if ($validator->errors()->has('links')) {
                    return;
                }

                $lines = $this->lines();
                $bad = array_filter($lines, fn (string $line) => YouTube::parseId($line) === null);

                if ($lines === []) {
                    $validator->errors()->add('links', __('Paste at least one YouTube link.'));
                } elseif ($bad !== []) {
                    $validator->errors()->add('links', __('These are not YouTube links: :lines', ['lines' => implode(', ', array_slice($bad, 0, 3))]));
                } elseif (count($lines) > 50) {
                    $validator->errors()->add('links', __('Add up to 50 links at a time.'));
                }

                if ($this->filled('month') && ! Month::isValid($this->input('month'))) {
                    $validator->errors()->add('month', __('Pick a month.'));
                }
            },
        ];
    }

    /**
     * @return list<string>
     */
    public function lines(): array
    {
        $lines = preg_split('/\R/', (string) $this->input('links')) ?: [];

        return array_values(array_filter(array_map('trim', $lines), fn (string $l) => $l !== ''));
    }

    /**
     * Unique video ids in the order pasted.
     *
     * @return list<string>
     */
    public function videoIds(): array
    {
        return array_values(array_unique(array_map(fn ($l) => (string) YouTube::parseId($l), $this->lines())));
    }
}
