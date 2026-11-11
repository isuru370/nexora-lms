<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Lesson;
use App\Models\LessonView;
use App\Services\Access\AccessService;
use App\Support\CurrentStudent;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class LessonController extends Controller
{
    public function show(Lesson $lesson, AccessService $access): View|RedirectResponse
    {
        $offering = $lesson->offering()->firstOrFail();
        $student = CurrentStudent::get();
        $enrolment = $access->enrolmentFor($student, $offering);
        $decision = $access->decide($enrolment, $lesson, $offering);

        if ($decision->denied()) {
            if (! auth()->check()) {
                return redirect()->guest(route('student.login'));
            }

            return redirect()
                ->route('tenant.offerings.show', ['offering' => $offering, 'month' => $lesson->month])
                ->withErrors(['lesson' => $decision->message()]);
        }

        if ($student) {
            $this->recordView($lesson, $student->id);
        }

        // Up next: other lessons in the same month (or course) the student can open.
        $siblings = $offering->lessons()->published()->ordered()
            ->when($offering->isClass(), fn ($q) => $q->where('month', $lesson->month))
            ->get()
            ->map(fn (Lesson $l) => ['lesson' => $l, 'open' => $access->decide($enrolment, $l, $offering)->allowed]);

        return view('tenant.lesson', [
            'lesson' => $lesson,
            'offering' => $offering,
            'siblings' => $siblings,
            'viewer' => auth()->user(),
        ]);
    }

    private function recordView(Lesson $lesson, int $studentId): void
    {
        $view = LessonView::query()->firstOrNew(['lesson_id' => $lesson->id, 'student_id' => $studentId]);

        if ($view->exists) {
            $view->views++;
        } else {
            $view->first_viewed_at = now();
        }

        $view->last_viewed_at = now();
        $view->save();
    }
}
