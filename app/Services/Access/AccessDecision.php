<?php

namespace App\Services\Access;

use App\Support\Month;

final class AccessDecision
{
    public const NOT_PUBLISHED = 'not_published';

    public const EXPIRED = 'expired';

    public const NOT_ENROLLED = 'not_enrolled';

    public const PENDING_PAYMENT = 'pending_payment';

    public const BLOCKED = 'blocked';

    public const UNPAID_MONTH = 'unpaid_month';

    public const CLASS_ENDED = 'class_ended';

    private function __construct(
        public readonly bool $allowed,
        public readonly ?string $reason = null,
        public readonly ?string $month = null,
    ) {}

    public static function allow(): self
    {
        return new self(true);
    }

    public static function deny(string $reason, ?string $month = null): self
    {
        return new self(false, $reason, $month);
    }

    public function denied(): bool
    {
        return ! $this->allowed;
    }

    /**
     * Short message for the student.
     */
    public function message(): ?string
    {
        if ($this->allowed) {
            return null;
        }

        return match ($this->reason) {
            self::NOT_ENROLLED => __('Join this class to watch.'),
            self::PENDING_PAYMENT => __('Complete the payment to open this course.'),
            self::BLOCKED => __('Your access was paused by the teacher. Please contact them.'),
            self::UNPAID_MONTH => __('Pay for :month to watch.', ['month' => $this->month ? Month::label($this->month) : __('this month')]),
            self::CLASS_ENDED => __('This class has ended.'),
            self::EXPIRED => __('This lesson is no longer available.'),
            default => __('This lesson is not available.'),
        };
    }
}
