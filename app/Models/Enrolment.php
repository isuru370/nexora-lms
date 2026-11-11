<?php

namespace App\Models;

use App\Enums\EnrolmentStatus;
use App\Tenancy\BelongsToTeacher;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['teacher_id', 'student_id', 'offering_id', 'status', 'enrolled_at', 'access_until'])]
class Enrolment extends Model
{
    use BelongsToTeacher;

    protected function casts(): array
    {
        return [
            'status' => EnrolmentStatus::class,
            'enrolled_at' => 'datetime',
            'access_until' => 'date',
        ];
    }

    /**
     * @return BelongsTo<Student, $this>
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    /**
     * @return BelongsTo<Offering, $this>
     */
    public function offering(): BelongsTo
    {
        return $this->belongsTo(Offering::class);
    }

    /**
     * @return HasMany<PaymentPeriod, $this>
     */
    public function periods(): HasMany
    {
        return $this->hasMany(PaymentPeriod::class);
    }

    /**
     * @return HasMany<AccessOverride, $this>
     */
    public function overrides(): HasMany
    {
        return $this->hasMany(AccessOverride::class);
    }

    public function isActive(): bool
    {
        return $this->status === EnrolmentStatus::Active;
    }
}
