<?php

namespace App\Models;

use App\Support\Month;
use App\Tenancy\BelongsToTeacher;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Teacher decision for one student in one class:
 *  grant -> temporary access until a date (e.g. "pay by the 10th")
 *  block -> no access even if paid
 */
#[Fillable(['teacher_id', 'enrolment_id', 'type', 'until', 'reason', 'created_by', 'revoked_at'])]
class AccessOverride extends Model
{
    use BelongsToTeacher;

    public const GRANT = 'grant';

    public const BLOCK = 'block';

    protected function casts(): array
    {
        return [
            'until' => 'date',
            'revoked_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<Enrolment, $this>
     */
    public function enrolment(): BelongsTo
    {
        return $this->belongsTo(Enrolment::class);
    }

    public function isActive(): bool
    {
        if ($this->revoked_at !== null) {
            return false;
        }

        return $this->until === null || $this->until->toDateString() >= Month::today()->toDateString();
    }
}
