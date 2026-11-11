<?php

namespace App\Http\Controllers\Manage;

use App\Enums\Role;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Notifications\PanelPasswordReset;
use App\Services\Auth\PasswordLinks;
use App\Support\AuditLogger;
use App\Tenancy\CurrentTeacher;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Exceptions\InvalidSignatureException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

/**
 * Set a password from an invite link, and "Forgot password?" for the panel.
 */
class PasswordController extends Controller
{
    public function __construct(private PasswordLinks $links, private CurrentTeacher $current) {}

    public function request(): View
    {
        return view('manage.auth.forgot-password');
    }

    public function email(Request $request): RedirectResponse
    {
        $data = $request->validate(['email' => ['required', 'email']]);
        $email = mb_strtolower($data['email']);
        $key = 'panel-reset|'.$request->getHost().'|'.$request->ip();

        if (RateLimiter::tooManyAttempts($key, 5)) {
            return back()->withErrors(['email' => __('Too many attempts. Please try again in :seconds seconds.', [
                'seconds' => RateLimiter::availableIn($key),
            ])]);
        }

        RateLimiter::hit($key, 300);

        $user = User::query()
            ->where('teacher_id', $this->current->id())
            ->where('email', $email)
            ->whereIn('role', [Role::Teacher->value, Role::Assistant->value])
            ->where('status', 'active')
            ->first();

        $user?->notify(new PanelPasswordReset($this->links->reset($user)));

        // Same answer whether or not the email exists.
        return back()->with('status', __('If that email belongs to this site, a reset link is on its way.'));
    }

    public function edit(Request $request, User $user, string $fingerprint): View|RedirectResponse
    {
        $this->guard($user, $fingerprint);

        return view('manage.auth.set-password', [
            'user' => $user,
            'action' => $request->fullUrl(),
        ]);
    }

    public function update(Request $request, User $user, string $fingerprint): RedirectResponse
    {
        $this->guard($user, $fingerprint);

        $data = $request->validate([
            'password' => ['required', 'confirmed', Password::min(8)],
        ]);

        $user->forceFill([
            'password' => $data['password'],
            'remember_token' => null,
        ])->save();

        AuditLogger::log('user.password_set', $user);

        Auth::login($user);
        $request->session()->regenerate();
        $user->forceFill(['last_login_at' => now()])->save();

        return redirect()->route('manage.dashboard')->with('status', __('Password saved. Welcome to your teacher panel!'));
    }

    /**
     * The signature is checked by the "signed" middleware; here we check
     * the link belongs to this site and has not been used.
     */
    private function guard(User $user, string $fingerprint): void
    {
        $belongsHere = $user->teacher_id === $this->current->id()
            && $user->hasRole(Role::Teacher, Role::Assistant)
            && $user->isActive();

        if (! $belongsHere || ! $this->links->stillValid($user, $fingerprint)) {
            throw new InvalidSignatureException;
        }
    }
}
