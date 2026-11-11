<?php

namespace App\Services\Access;

use App\Enums\EnrolmentStatus;
use App\Enums\LessonVisibility;
use App\Enums\OldLessonsAccess;
use App\Models\AccessOverride;
use App\Models\Enrolment;
use App\Models\Lesson;
use App\Models\Offering;
use App\Models\Student;
use App\Support\Month;
use Illuminate\Support\Collection;

/**
 * THE place that decides who may watch what. Web pages, the API and
 * the Zoom join link all ask here; nothing else checks access.
 *
 * Teacher settings it follows (see foundation doc §7.2):
 *  - lesson visibility: paid | enrolled | public, and available_until
 *  - class: old_lessons_access, past_lessons_access, grace_days
 *  - per student: payment periods (payment / manual / free), grant, block
 */
class AccessService
{
    public function canWatch(?Student $student, Lesson $lesson): AccessDecision
    {
        $offering = $lesson->relationLoaded('offering') ? $lesson->offering : $lesson->offering()->firstOrFail();

        return $this->decide($this->enrolmentFor($student, $offering), $lesson, $offering);
    }

    /**
     * Load an enrolment with everything decide() needs, in one go.
     * Use it when checking many lessons of one offering.
     */
    public function enrolmentFor(?Student $student, Offering $offering): ?Enrolment
    {
        if ($student === null) {
            return null;
        }

        return Enrolment::query()
            ->where('student_id', $student->getKey())
            ->where('offering_id', $offering->getKey())
            ->with(['periods:id,enrolment_id,month', 'overrides'])
            ->first();
    }

    public function decide(?Enrolment $enrolment, Lesson $lesson, Offering $offering): AccessDecision
    {
        $today = Month::today()->toDateString();

        if (! $lesson->is_published || ! $offering->is_published) {
            return AccessDecision::deny(AccessDecision::NOT_PUBLISHED);
        }

        if ($lesson->available_until !== null && $lesson->available_until->toDateString() < $today) {
            return AccessDecision::deny(AccessDecision::EXPIRED);
        }

        if ($lesson->visibility === LessonVisibility::Public) {
            return AccessDecision::allow();
        }

        if ($enrolment === null) {
            return AccessDecision::deny(AccessDecision::NOT_ENROLLED);
        }

        if ($enrolment->status === EnrolmentStatus::Pending) {
            return AccessDecision::deny(AccessDecision::PENDING_PAYMENT);
        }

        if (! $enrolment->isActive()) {
            return AccessDecision::deny(AccessDecision::NOT_ENROLLED);
        }

        if ($this->activeOverride($enrolment, AccessOverride::BLOCK)) {
            return AccessDecision::deny(AccessDecision::BLOCKED);
        }

        // Enrolled-only lessons, and anything in a free class or course.
        if ($lesson->visibility === LessonVisibility::Enrolled || (float) $offering->fee <= 0) {
            return AccessDecision::allow();
        }

        if ($offering->isCourse()) {
            return $enrolment->access_until === null || $enrolment->access_until->toDateString() >= $today
                ? AccessDecision::allow()
                : AccessDecision::deny(AccessDecision::EXPIRED);
        }

        // Monthly class
        $month = $lesson->month ?? Month::current();

        if ($offering->old_lessons_access === OldLessonsAccess::UntilClassEnds && $offering->hasEnded()) {
            return AccessDecision::deny(AccessDecision::CLASS_ENDED);
        }

        if ($this->covers($enrolment, $offering, $month)) {
            return AccessDecision::allow();
        }

        if ($offering->past_lessons_access && $this->paidMonths($enrolment)->contains(fn (string $paid) => $paid >= $month)) {
            return AccessDecision::allow();
        }

        return AccessDecision::deny(AccessDecision::UNPAID_MONTH, $month);
    }

    /**
     * Is this month open for the student?
     *  - a payment period exists (paid online, marked paid, or given free), or
     *  - an active temporary grant, for this or an earlier month, or
     *  - grace days at the start of the current month, if last month was covered.
     */
    public function covers(Enrolment $enrolment, Offering $offering, string $month): bool
    {
        if ($this->paidMonths($enrolment)->contains($month)) {
            return true;
        }

        $current = Month::current();

        if ($month <= $current && $this->activeOverride($enrolment, AccessOverride::GRANT)) {
            return true;
        }

        return $month === $current
            && $offering->grace_days > 0
            && Month::today()->day <= $offering->grace_days
            && $this->paidMonths($enrolment)->contains(Month::previous($current));
    }

    /**
     * @return Collection<int, string>
     */
    private function paidMonths(Enrolment $enrolment)
    {
        return $enrolment->periods->pluck('month');
    }

    private function activeOverride(Enrolment $enrolment, string $type): bool
    {
        return $enrolment->overrides->contains(fn (AccessOverride $o) => $o->type === $type && $o->isActive());
    }
}
