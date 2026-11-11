<?php

use App\Enums\Role;
use App\Http\Middleware\EnsureCentral;
use App\Http\Middleware\EnsureRole;
use App\Http\Middleware\EnsureTenant;
use App\Http\Middleware\ResolveTenant;
use App\Tenancy\CurrentTeacher;
use Illuminate\Contracts\Auth\Middleware\AuthenticatesRequests;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Find the teacher from the subdomain on every web request,
        // before any route middleware (auth, role) runs.
        $middleware->web(append: [ResolveTenant::class]);

        // Laravel sorts some middleware (auth, bindings) by priority. The
        // tenant must be known before the user is loaded from the session,
        // because users are looked up inside the current tenant.
        $middleware->prependToPriorityList(
            before: AuthenticatesRequests::class,
            prepend: ResolveTenant::class,
        );

        $middleware->alias([
            'tenant' => EnsureTenant::class,
            'central' => EnsureCentral::class,
            'role' => EnsureRole::class,
        ]);

        // Where guests are sent when a page needs login.
        $middleware->redirectGuestsTo(function (Request $request): string {
            if (! app(CurrentTeacher::class)->check()) {
                return route('admin.login');
            }

            return $request->is('manage', 'manage/*') ? route('manage.login') : route('student.login');
        });

        // Where logged-in users are sent from login/register pages.
        $middleware->redirectUsersTo(function (Request $request): string {
            return match ($request->user()?->role) {
                Role::SuperAdmin => route('admin.dashboard'),
                Role::Teacher, Role::Assistant => route('manage.dashboard'),
                Role::Student => route('student.dashboard'),
                default => '/',
            };
        });
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
