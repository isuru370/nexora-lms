<?php

namespace App\Models;

use App\Tenancy\BelongsToTeacher;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['teacher_id', 'lesson_id', 'student_id', 'first_viewed_at', 'last_viewed_at', 'views', 'progress_seconds', 'completed_at'])]
class LessonView extends Model
{
    use BelongsToTeacher;

    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'first_viewed_at' => 'datetime',
            'last_viewed_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<Lesson, $this>
     */
    public function lesson(): BelongsTo
    {
        return $this->belongsTo(Lesson::class);
    }
}
