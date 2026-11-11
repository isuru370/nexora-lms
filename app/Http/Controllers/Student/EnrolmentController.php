<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Offering;
use App\Services\Students\EnrolStudent;
use App\Support\CurrentStudent;
use Illuminate\Http\RedirectResponse;

class EnrolmentController extends Controller
{
    public function store(Offering $offering, EnrolStudent $enrol): RedirectResponse
    {
        abort_unless($offering->is_published, 404);

        $student = CurrentStudent::get();
        abort_if($student === null, 403);

        $enrolment = $enrol->handle($student, $offering);

        $message = $enrolment->isActive()
            ? __('You joined :title.', ['title' => $offering->title])
            : __('You joined :title. Complete the payment to open the lessons.', ['title' => $offering->title]);

        return redirect()->route('tenant.offerings.show', $offering)->with('status', $message);
    }
}
