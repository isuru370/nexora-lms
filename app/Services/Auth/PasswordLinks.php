<?php

namespace App\Services\Auth;

use App\Models\Teacher;
use App\Models\User;
use Illuminate\Support\Facades\URL;

/**
 * Signed "set your password" links for teacher-panel users.
 *
 * - Signed for the teacher's own host, so a link for kasun.<base> does
 *   not work on nimal.<base>.
 * - Carries a fingerprint of the current password hash, so the link stops
 *   working as soon as the password is changed (single use).
 * - No token table needed.
 */
class PasswordLinks
{
    public const INVITE_HOURS = 48;

    public const RESET_MINUTES = 60;

    public function invite(User $user): string
    {
        return $this->make($user, now()->addHours(self::INVITE_HOURS));
    }

    public function reset(User $user): string
    {
        return $this->make($user, now()->addMinutes(self::RESET_MINUTES));
    }

    /**
     * Does the fingerprint in the link still match the user's password?
     */
    public function stillValid(User $user, string $fingerprint): bool
    {
        return hash_equals($this->fingerprint($user), $fingerprint);
    }

    private function make(User $user, \DateTimeInterface $expires): string
    {
        /** @var Teacher $teacher */
        $teacher = $user->teacher()->firstOrFail();

        // Sign the URL for the teacher's host, whatever host we are on now.
        URL::forceRootUrl($teacher->url());

        try {
            return URL::temporarySignedRoute('manage.password.edit', $expires, [
                'user' => $user->getKey(),
                'fingerprint' => $this->fingerprint($user),
            ]);
        } finally {
            URL::forceRootUrl(null);
        }
    }

    private function fingerprint(User $user): string
    {
        return substr(hash_hmac('sha256', (string) $user->getAuthPassword(), (string) config('app.key')), 0, 24);
    }
}
