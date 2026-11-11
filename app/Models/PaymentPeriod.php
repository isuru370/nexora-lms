<?php

namespace App\Models;

use App\Tenancy\BelongsToTeacher;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * "This month is covered" for a class enrolment.
 * source: payment (paid online), manual (teacher marked paid), free (teacher gave it).
 */
#[Fillable(['teacher_id', 'enrolment_id', 'month', 'source', 'payment_id', 'granted_by'])]
class PaymentPeriod extends Model
{
    use BelongsToTeacher;

    public const SOURCE_PAYMENT = 'payment';

    public const SOURCE_MANUAL = 'manual';

    public const SOURCE_FREE = 'free';

    /**
     * @return BelongsTo<Enrolment, $this>
     */
    public function enrolment(): BelongsTo
    {
        return $this->belongsTo(Enrolment::class);
    }
}
