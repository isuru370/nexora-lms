<?php

namespace App\Tenancy;

use App\Models\Teacher;

/**
 * The only source of "which teacher (tenant) is this request for".
 *
 * Set by the ResolveTenant middleware. Tenant models read it through
 * TenantScope; if it is empty they fail closed (TenantNotResolved).
 */
final class CurrentTeacher
{
    private ?Teacher $teacher = null;

    private int $bypassDepth = 0;

    public function set(Teacher $teacher): void
    {
        $this->teacher = $teacher;
    }

    public function clear(): void
    {
        $this->teacher = null;
    }

    public function get(): ?Teacher
    {
        return $this->teacher;
    }

    public function check(): bool
    {
        return $this->teacher !== null;
    }

    public function id(): ?int
    {
        return $this->teacher?->getKey();
    }

    public function require(): Teacher
    {
        return $this->teacher ?? throw new TenantNotResolved;
    }

    /**
     * Run code that is allowed to see every tenant's rows (Super Admin,
     * platform jobs). Must always be explicit.
     *
     * @template T
     *
     * @param  callable(): T  $callback
     * @return T
     */
    public function withoutTenancy(callable $callback): mixed
    {
        $this->bypassDepth++;

        try {
            return $callback();
        } finally {
            $this->bypassDepth--;
        }
    }

    public function bypassing(): bool
    {
        return $this->bypassDepth > 0;
    }

    /**
     * Run code as a given teacher (jobs, commands, tests).
     *
     * @template T
     *
     * @param  callable(): T  $callback
     * @return T
     */
    public function runAs(Teacher $teacher, callable $callback): mixed
    {
        $previous = $this->teacher;
        $this->teacher = $teacher;

        try {
            return $callback();
        } finally {
            $this->teacher = $previous;
        }
    }
}
