<?php

namespace App\Models;

use App\Enums\LessonVisibility;
use App\Support\YouTube;
use App\Tenancy\BelongsToTeacher;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * A video lesson. video_ref (the YouTube id) must only ever reach a
 * student through AccessService.
 */
#[Fillable([
    'teacher_id', 'offering_id', 'title', 'description', 'month', 'video_provider', 'video_ref',
    'duration_minutes', 'visibility', 'available_until', 'is_published', 'sort', 'published_at',
])]
#[Hidden(['video_ref'])]
class Lesson extends Model
{
    use BelongsToTeacher, SoftDeletes;

    protected function casts(): array
    {
        return [
            'visibility' => LessonVisibility::class,
            'available_until' => 'date',
            'is_published' => 'boolean',
            'published_at' => 'datetime',
            'duration_minutes' => 'integer',
        ];
    }

    /**
     * @return BelongsTo<Offering, $this>
     */
    public function offering(): BelongsTo
    {
        return $this->belongsTo(Offering::class);
    }

    /**
     * @param  Builder<Lesson>  $query
     */
    public function scopePublished(Builder $query): void
    {
        $query->where('is_published', true);
    }

    /**
     * @param  Builder<Lesson>  $query
     */
    public function scopeOrdered(Builder $query): void
    {
        $query->orderByDesc('month')->orderBy('sort')->orderBy('id');
    }

    /**
     * Only call after an access check (or in the teacher panel).
     */
    public function thumbnailUrl(): string
    {
        return YouTube::thumbnail($this->video_ref);
    }

    /**
     * Only call after an access check (or in the teacher panel).
     */
    public function embedUrl(): string
    {
        return YouTube::embedUrl($this->video_ref);
    }
}
