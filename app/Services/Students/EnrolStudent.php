<?php

namespace App\Services\Students;

use App\Enums\EnrolmentStatus;
use App\Models\Enrolment;
use App\Models\Offering;
use App\Models\Student;
use App\Support\AuditLogger;
use App\Support\Month;

class EnrolStudent
{
    /**
     * Monthly class: active at once (each month is opened by paying for it).
     * Paid course: pending until paid (Phase 2). Free course: active at once.
     */
    public function handle(Student $student, Offering $offering): Enrolment
    {
        $existing = Enrolment::query()
            ->where('student_id', $student->id)
            ->where('offering_id', $offering->id)
            ->first();

        if ($existing) {
            return $existing;
        }

        $paidCourse = $offering->isCourse() && (float) $offering->fee > 0;

        $enrolment = Enrolment::create([
            'student_id' => $student->id,
            'offering_id' => $offering->id,
            'status' => $paidCourse ? EnrolmentStatus::Pending : EnrolmentStatus::Active,
            'enrolled_at' => now(),
            'access_until' => $offering->isCourse() && ! $paidCourse && $offering->access_days
                ? Month::today()->addDays($offering->access_days)->toDateString()
                : null,
        ]);

        AuditLogger::log('enrolment.created', $enrolment, ['offering' => $offering->id]);

        return $enrolment;
    }
}
