<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Enrolment;
use App\Models\LessonView;
use App\Models\Offering;
use App\Support\CurrentStudent;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $student = CurrentStudent::get();

        $enrolments = Enrolment::query()
            ->where('student_id', $student?->id)
            ->whereHas('offering')
            ->with('offering')
            ->latest('enrolled_at')
            ->get();

        return view('student.dashboard', [
            'user' => $request->user(),
            'student' => $student,
            'enrolments' => $enrolments->take(6),
            'available' => Offering::query()->published()
                ->whereNotIn('id', $enrolments->pluck('offering_id'))
                ->orderBy('subject')->orderBy('sort')
                ->get(),
            // Links only; the lesson page re-checks access when opened.
            'recent' => LessonView::query()
                ->where('student_id', $student?->id)
                ->whereHas('lesson', fn ($q) => $q->where('is_published', true))
                ->with('lesson.offering')
                ->latest('last_viewed_at')
                ->limit(3)
                ->get(),
        ]);
    }
}
