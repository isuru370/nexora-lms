<?php

namespace App\Http\Requests\Auth;

use App\Support\Phone;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Teacher panel login: one box that takes an email or a mobile number.
 */
class PanelLoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'login' => ['required', 'string', 'max:150'],
            'password' => ['required', 'string'],
            'remember' => ['nullable', 'boolean'],
        ];
    }

    /**
     * ['email', 'kasun@example.com'] or ['phone', '+94771234567'].
     *
     * @return array{0: 'email'|'phone', 1: string}
     */
    public function credential(): array
    {
        $login = trim((string) $this->validated('login'));

        if (str_contains($login, '@')) {
            return ['email', mb_strtolower($login)];
        }

        return ['phone', Phone::normalize($login) ?? $login];
    }
}
