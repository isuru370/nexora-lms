<?php

namespace App\Support;

use App\Models\AuditLog;
use App\Tenancy\CurrentTeacher;
use Illuminate\Database\Eloquent\Model;

final class AuditLogger
{
    /**
     * @param  array<string, mixed>  $changes
     */
    public static function log(string $action, ?Model $subject = null, array $changes = [], ?int $teacherId = null): AuditLog
    {
        return AuditLog::create([
            'teacher_id' => $teacherId ?? app(CurrentTeacher::class)->id(),
            'user_id' => auth()->id(),
            'action' => $action,
            'subject_type' => $subject?->getMorphClass(),
            'subject_id' => $subject?->getKey(),
            'changes' => $changes ?: null,
            'ip' => request()->ip(),
        ]);
    }
}
