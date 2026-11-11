<?php

namespace App\Services\Auth;

use App\Enums\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;

/**
 * One login routine for all three doors (admin, manage, student).
 * The user lookup is already limited to the current tenant by
 * TenantUserProvider; here we check the role and status.
 */
class LoginService
{
    private const MAX_ATTEMPTS = 5;

    private const DECAY_SECONDS = 60;

    /**
     * @param  'email'|'phone'  $field
     * @param  array<int, Role>  $roles
     *
     * @throws ValidationException
     */
    public function attempt(Request $request, string $field, string $value, string $password, array $roles, bool $remember = false): User
    {
        $key = 'login|'.$request->getHost().'|'.mb_strtolower($value).'|'.$request->ip();

        if (RateLimiter::tooManyAttempts($key, self::MAX_ATTEMPTS)) {
            throw ValidationException::withMessages([
                $field => __('Too many attempts. Please try again in :seconds seconds.', [
                    'seconds' => RateLimiter::availableIn($key),
                ]),
            ]);
        }

        if (! Auth::attempt([$field => $value, 'password' => $password], $remember)) {
            $this->fail($key, $field);
        }

        /** @var User $user */
        $user = Auth::user();

        if (! $user->hasRole(...$roles) || ! $user->isActive()) {
            Auth::logout();
            $this->fail($key, $field);
        }

        RateLimiter::clear($key);
        $request->session()->regenerate();
        $user->forceFill(['last_login_at' => now()])->save();

        return $user;
    }

    public function logout(Request $request): void
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
    }

    /**
     * @throws ValidationException
     */
    private function fail(string $key, string $field): never
    {
        RateLimiter::hit($key, self::DECAY_SECONDS);

        throw ValidationException::withMessages([
            $field => __('These details do not match our records.'),
        ]);
    }
}
