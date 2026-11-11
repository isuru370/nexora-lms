<?php

namespace App\Http\Middleware;

use App\Enums\Role;
use App\Tenancy\CurrentTeacher;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Usage: ->middleware('role:teacher,assistant')
 */
class EnsureRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();
        $allowed = array_map(fn (string $role) => Role::from($role), $roles);

        if ($user === null || ! in_array($user->role, $allowed, true)) {
            abort(403);
        }

        // Defence in depth: the user must belong to this site's teacher
        // (or to no teacher on the central site).
        $tenantId = app(CurrentTeacher::class)->id();
        $userTenantId = $user->teacher_id === null ? null : (int) $user->teacher_id;

        if ($userTenantId !== $tenantId) {
            abort(403);
        }

        return $next($request);
    }
}
