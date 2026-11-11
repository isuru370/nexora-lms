<?php

namespace App\Http\Middleware;

use App\Enums\TeacherStatus;
use App\Models\Teacher;
use App\Support\Domains;
use App\Tenancy\CurrentTeacher;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Symfony\Component\HttpFoundation\Response;

/**
 * Finds the teacher from the subdomain: kasun.<base> -> teachers.slug = kasun.
 * The central host (app.<base>) and the bare base domain have no tenant.
 */
class ResolveTenant
{
    public function __construct(private CurrentTeacher $current) {}

    public function handle(Request $request, Closure $next): Response
    {
        $this->current->clear();

        $host = strtolower($request->getHost());
        $base = Domains::base();

        if ($host === Domains::central() || $host === $base) {
            return $next($request);
        }

        $suffix = '.'.$base;

        if (! str_ends_with($host, $suffix)) {
            abort(404);
        }

        $slug = substr($host, 0, -strlen($suffix));

        if ($slug === '' || str_contains($slug, '.')) {
            abort(404);
        }

        $teacher = Teacher::query()->where('slug', $slug)->first();

        if ($teacher === null) {
            abort(404);
        }

        $this->current->set($teacher);
        View::share('currentTeacher', $teacher);

        if ($teacher->status === TeacherStatus::Locked && ! $request->is('manage', 'manage/*')) {
            return response()->view('tenant.unavailable', ['teacher' => $teacher], 403);
        }

        return $next($request);
    }
}
