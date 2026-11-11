<?php

namespace App\Models;

use App\Enums\OfferingType;
use App\Enums\OldLessonsAccess;
use App\Support\Money;
use App\Support\Month;
use App\Tenancy\BelongsToTeacher;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

/**
 * A monthly class or a one-off course.
 */
#[Fillable([
    'teacher_id', 'type', 'title', 'slug', 'description', 'cover_path', 'subject', 'grade', 'medium',
    'fee', 'access_days', 'old_lessons_access', 'past_lessons_access', 'grace_days',
    'is_published', 'starts_on', 'ends_on', 'sort',
])]
class Offering extends Model
{
    use BelongsToTeacher, SoftDeletes;

    public const MEDIUMS = ['sinhala' => 'Sinhala', 'english' => 'English', 'tamil' => 'Tamil'];

    protected function casts(): array
    {
        return [
            'type' => OfferingType::class,
            'old_lessons_access' => OldLessonsAccess::class,
            'past_lessons_access' => 'boolean',
            'grace_days' => 'integer',
            'access_days' => 'integer',
            'is_published' => 'boolean',
            'fee' => 'decimal:2',
            'starts_on' => 'date',
            'ends_on' => 'date',
        ];
    }

    /**
     * @return HasMany<Lesson, $this>
     */
    public function lessons(): HasMany
    {
        return $this->hasMany(Lesson::class);
    }

    /**
     * @return HasMany<Enrolment, $this>
     */
    public function enrolments(): HasMany
    {
        return $this->hasMany(Enrolment::class);
    }

    /**
     * @param  Builder<Offering>  $query
     */
    public function scopePublished(Builder $query): void
    {
        $query->where('is_published', true);
    }

    public function isClass(): bool
    {
        return $this->type === OfferingType::MonthlyClass;
    }

    public function isCourse(): bool
    {
        return $this->type === OfferingType::Course;
    }

    public function hasEnded(): bool
    {
        return $this->ends_on !== null && $this->ends_on->toDateString() < Month::today()->toDateString();
    }

    public function feeLabel(): string
    {
        if ((float) $this->fee <= 0) {
            return __('Free');
        }

        return Money::lkr($this->fee).($this->isClass() ? ' / '.__('month') : '');
    }

    public function mediumLabel(): ?string
    {
        return $this->medium ? (self::MEDIUMS[$this->medium] ?? ucfirst($this->medium)) : null;
    }

    /**
     * A unique slug inside the current teacher's site.
     */
    public static function uniqueSlug(string $title, ?int $ignoreId = null): string
    {
        $base = Str::slug($title) ?: 'class';
        $base = Str::limit($base, 100, '');
        $slug = $base;
        $i = 2;

        while (static::withTrashed()->where('slug', $slug)->when($ignoreId, fn ($q) => $q->whereKeyNot($ignoreId))->exists()) {
            $slug = $base.'-'.$i++;
        }

        return $slug;
    }
}
