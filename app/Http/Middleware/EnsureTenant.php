<?php

namespace App\Http\Middleware;

use App\Tenancy\CurrentTeacher;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Route is only for a teacher's subdomain.
 */
class EnsureTenant
{
    public function __construct(private CurrentTeacher $current) {}

    public function handle(Request $request, Closure $next): Response
    {
        if (! $this->current->check()) {
            abort(404);
        }

        return $next($request);
    }
}
