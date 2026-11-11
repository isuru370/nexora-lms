<?php

namespace App\Http\Controllers\Admin;

use App\Enums\Role;
use App\Enums\TeacherStatus;
use App\Http\Controllers\Controller;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $byStatus = Teacher::query()
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        return view('admin.dashboard', [
            'stats' => [
                'teachers' => (int) $byStatus->sum(),
                'trial' => (int) ($byStatus[TeacherStatus::Trial->value] ?? 0),
                'active' => (int) ($byStatus[TeacherStatus::Active->value] ?? 0),
                'locked' => (int) ($byStatus[TeacherStatus::Locked->value] ?? 0),
                'students' => User::query()->where('role', Role::Student->value)->count(),
            ],
            'latestTeachers' => Teacher::query()->latest()->limit(5)->get(),
        ]);
    }
}
