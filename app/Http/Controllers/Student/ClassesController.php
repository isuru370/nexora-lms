<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Enrolment;
use App\Support\CurrentStudent;
use Illuminate\View\View;

class ClassesController extends Controller
{
    public function index(): View
    {
        $student = CurrentStudent::get();

        $enrolments = Enrolment::query()
            ->where('student_id', $student?->id)
            ->whereHas('offering')
            ->with(['offering' => fn ($q) => $q->withCount(['lessons' => fn ($l) => $l->where('is_published', true)])])
            ->latest('enrolled_at')
            ->get();

        return view('student.classes', compact('enrolments'));
    }
}
