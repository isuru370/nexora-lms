<?php

namespace App\Models;

use App\Tenancy\BelongsToTeacher;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/**
 * Per-teacher sequences (e.g. student codes). Never use max()+1.
 */
#[Fillable(['teacher_id', 'key', 'value'])]
class TeacherCounter extends Model
{
    use BelongsToTeacher;

    public $timestamps = false;

    protected function casts(): array
    {
        return ['value' => 'integer'];
    }

    public static function next(string $key): int
    {
        return DB::transaction(function () use ($key): int {
            static::query()->createOrFirst(['key' => $key], ['value' => 0]);

            /** @var static $counter */
            $counter = static::query()->where('key', $key)->lockForUpdate()->firstOrFail();
            $counter->value++;
            $counter->save();

            return $counter->value;
        });
    }
}
