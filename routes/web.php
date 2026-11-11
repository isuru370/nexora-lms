<?php

use App\Http\Controllers\Admin;
use App\Http\Controllers\Central;
use App\Http\Controllers\Manage;
use App\Http\Controllers\Student;
use App\Http\Controllers\Tenant;
use App\Support\Domains;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Central site: app.<base>
|--------------------------------------------------------------------------
*/

Route::domain(Domains::central())->middleware('central')->group(function () {
    Route::get('/', Central\HomeController::class)->name('central.home');

    Route::prefix('admin')->name('admin.')->group(function () {
        Route::middleware('guest')->group(function () {
            Route::get('login', [Admin\AuthController::class, 'create'])->name('login');
            Route::post('login', [Admin\AuthController::class, 'store'])->name('login.store');
        });

        Route::middleware(['auth', 'role:super_admin'])->group(function () {
            Route::post('logout', [Admin\AuthController::class, 'destroy'])->name('logout');
            Route::get('/', Admin\DashboardController::class)->name('dashboard');
            Route::get('teachers', [Admin\TeacherController::class, 'index'])->name('teachers.index');
            Route::get('teachers/create', [Admin\TeacherController::class, 'create'])->name('teachers.create');
            Route::post('teachers', [Admin\TeacherController::class, 'store'])->name('teachers.store');
            Route::post('teachers/{teacher}/invite', [Admin\TeacherController::class, 'invite'])->name('teachers.invite');
        });
    });
});

// Bare base domain -> central site.
Route::domain(Domains::base())->get('/', fn () => redirect()->away(Domains::centralUrl()));

/*
|--------------------------------------------------------------------------
| Teacher sites: <slug>.<base>
|--------------------------------------------------------------------------
|
| No domain constraint here: ResolveTenant has already found the teacher
| from the host, and the "tenant" middleware returns 404 everywhere else.
|
*/

Route::middleware('tenant')->group(function () {
    // Teacher panel
    Route::prefix('manage')->name('manage.')->group(function () {
        Route::middleware('guest')->group(function () {
            Route::get('login', [Manage\AuthController::class, 'create'])->name('login');
            Route::post('login', [Manage\AuthController::class, 'store'])->name('login.store');
            Route::get('forgot-password', [Manage\PasswordController::class, 'request'])->name('password.request');
            Route::post('forgot-password', [Manage\PasswordController::class, 'email'])->name('password.email');
        });

        // Invite / reset links (signed, single use)
        Route::middleware('signed')->group(function () {
            Route::get('set-password/{user}/{fingerprint}', [Manage\PasswordController::class, 'edit'])->name('password.edit');
            Route::post('set-password/{user}/{fingerprint}', [Manage\PasswordController::class, 'update'])->name('password.update');
        });

        Route::middleware(['auth', 'role:teacher,assistant'])->group(function () {
            Route::post('logout', [Manage\AuthController::class, 'destroy'])->name('logout');
            Route::get('/', Manage\DashboardController::class)->name('dashboard');
        });

        // Classes, courses and lessons (owner, or assistants with "lessons" permission)
        Route::middleware(['auth', 'role:teacher,assistant', 'permission:lessons'])->group(function () {
            Route::get('youtube/lookup', [Manage\LessonController::class, 'lookup'])->name('youtube.lookup');
            Route::resource('classes', Manage\OfferingController::class)->parameters(['classes' => 'offering']);

            Route::scopeBindings()->group(function () {
                Route::get('classes/{offering}/lessons/create', [Manage\LessonController::class, 'create'])->name('lessons.create');
                Route::post('classes/{offering}/lessons', [Manage\LessonController::class, 'store'])->name('lessons.store');
                Route::get('classes/{offering}/lessons/{lesson}/edit', [Manage\LessonController::class, 'edit'])->name('lessons.edit');
                Route::put('classes/{offering}/lessons/{lesson}', [Manage\LessonController::class, 'update'])->name('lessons.update');
                Route::delete('classes/{offering}/lessons/{lesson}', [Manage\LessonController::class, 'destroy'])->name('lessons.destroy');
            });
        });

        // Owner only
        Route::middleware(['auth', 'role:teacher'])->group(function () {
            Route::get('settings', [Manage\SettingsController::class, 'edit'])->name('settings');
            Route::put('settings', [Manage\SettingsController::class, 'update'])->name('settings.update');
        });
    });

    // Public + student portal
    Route::get('/', Tenant\LandingController::class)->name('tenant.home');
    Route::get('classes/{offering:slug}', [Tenant\OfferingController::class, 'show'])->name('tenant.offerings.show');
    Route::get('lessons/{lesson}', [Tenant\LessonController::class, 'show'])->name('tenant.lessons.show');

    Route::middleware('guest')->group(function () {
        Route::get('login', [Student\AuthController::class, 'create'])->name('student.login');
        Route::post('login', [Student\AuthController::class, 'store'])->name('student.login.store');
        Route::get('register', [Student\AuthController::class, 'showRegister'])->name('student.register');
        Route::post('register', [Student\AuthController::class, 'register'])->name('student.register.store');
    });

    Route::middleware(['auth', 'role:student'])->group(function () {
        Route::post('logout', [Student\AuthController::class, 'destroy'])->name('student.logout');
        Route::get('dashboard', Student\DashboardController::class)->name('student.dashboard');
        Route::get('my-classes', [Student\ClassesController::class, 'index'])->name('student.classes');
        Route::post('classes/{offering:slug}/enrol', [Student\EnrolmentController::class, 'store'])->name('student.enrol');
    });
});
