<?php

namespace App\Http\Controllers\Manage;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Tenancy\CurrentTeacher;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(CurrentTeacher $current): View
    {
        $teacher = $current->require();

        return view('manage.dashboard', [
            'teacher' => $teacher,
            'studentsCount' => Student::query()->count(),
            'latestStudents' => Student::query()->with('user')->latest()->limit(5)->get(),
            'trialDaysLeft' => $teacher->trial_ends_on
                ? max(0, (int) now(config('nexora.display_timezone'))->startOfDay()->diffInDays($teacher->trial_ends_on, false))
                : null,
        ]);
    }
}
