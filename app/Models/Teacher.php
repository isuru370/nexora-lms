<?php

namespace App\Models;

use App\Enums\Role;
use App\Enums\TeacherStatus;
use App\Support\Domains;
use App\Tenancy\CurrentTeacher;
use Database\Factories\TeacherFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * The tenant. Not scoped itself.
 */
#[Fillable([
    'slug', 'display_name', 'subject_line', 'logo_path', 'brand_color',
    'phone', 'whatsapp', 'status', 'trial_ends_on', 'features', 'custom_domain',
])]
class Teacher extends Model
{
    /** @use HasFactory<TeacherFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'status' => TeacherStatus::class,
            'trial_ends_on' => 'date',
            'features' => 'array',
        ];
    }

    /**
     * @return HasMany<User, $this>
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    /**
     * The tenant owner (role = teacher).
     *
     * @return HasOne<User, $this>
     */
    public function owner(): HasOne
    {
        return $this->hasOne(User::class)->where('role', Role::Teacher->value);
    }

    public function url(string $path = '/'): string
    {
        return Domains::tenantUrl($this->slug, $path);
    }

    public function brandColor(): string
    {
        return $this->brand_color ?: '#c9a227';
    }

    public function initials(): string
    {
        $words = preg_split('/\s+/', trim($this->display_name)) ?: [];
        $letters = array_map(fn (string $word) => mb_substr($word, 0, 1), array_slice($words, 0, 2));

        return mb_strtoupper(implode('', $letters));
    }

    public function hasFeature(string $feature): bool
    {
        return (bool) ($this->features[$feature] ?? false);
    }

    /**
     * See every tenant's rows inside the callback (Super Admin, platform jobs).
     *
     * @template T
     *
     * @param  callable(): T  $callback
     * @return T
     */
    public static function withoutTenancy(callable $callback): mixed
    {
        return app(CurrentTeacher::class)->withoutTenancy($callback);
    }
}
