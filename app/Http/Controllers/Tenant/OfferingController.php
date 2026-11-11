<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Offering;
use App\Services\Access\AccessService;
use App\Support\CurrentStudent;
use App\Support\Month;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Public class page. Students also use it as "my lessons" for the class.
 * Lesson ids are linked only when AccessService allows; video ids never
 * appear on this page.
 */
class OfferingController extends Controller
{
    public function show(Request $request, Offering $offering, AccessService $access): View
    {
        abort_unless($offering->is_published, 404);

        $student = CurrentStudent::get();
        $enrolment = $access->enrolmentFor($student, $offering);

        $lessons = $offering->lessons()->published()->ordered()->get();
        $months = $offering->isClass() ? $lessons->pluck('month')->filter()->unique()->values() : collect();

        $month = $request->query('month');
        if ($offering->isClass() && ! $months->contains($month)) {
            $month = $months->contains(Month::current()) ? Month::current() : $months->first();
        }

        $shown = $offering->isClass() ? $lessons->where('month', $month)->values() : $lessons;

        return view('tenant.offering', [
            'offering' => $offering,
            'student' => $student,
            'enrolment' => $enrolment,
            'months' => $months,
            'month' => $month,
            'rows' => $shown->map(fn ($lesson) => [
                'lesson' => $lesson,
                'access' => $access->decide($enrolment, $lesson, $offering),
            ]),
            'lessonsCount' => $lessons->count(),
        ]);
    }
}
