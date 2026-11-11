<?php

namespace App\Tenancy;

use App\Models\Teacher;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Every tenant model uses this trait:
 *  - queries are limited to the current teacher (fail closed),
 *  - teacher_id is filled automatically on create.
 */
trait BelongsToTeacher
{
    public static function bootBelongsToTeacher(): void
    {
        static::addGlobalScope(new TenantScope);

        static::creating(function (Model $model): void {
            if ($model->getAttribute('teacher_id') === null) {
                $model->setAttribute('teacher_id', app(CurrentTeacher::class)->require()->getKey());
            }
        });
    }

    /**
     * @return BelongsTo<Teacher, $this>
     */
    public function teacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class);
    }
}
