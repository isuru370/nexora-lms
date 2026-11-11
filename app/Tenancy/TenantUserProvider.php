<?php

namespace App\Tenancy;

use Illuminate\Auth\EloquentUserProvider;
use Illuminate\Database\Eloquent\Builder;

/**
 * Users are looked up only inside the current tenant.
 *
 * On a teacher's subdomain only that teacher's users exist; on the central
 * site only users without a teacher (Super Admins) exist. A session or
 * "remember me" cookie from one tenant can never load a user on another.
 */
class TenantUserProvider extends EloquentUserProvider
{
    protected function newModelQuery($model = null): Builder
    {
        $query = parent::newModelQuery($model);
        $current = app(CurrentTeacher::class);

        if ($current->check()) {
            $query->where('teacher_id', $current->id());
        } else {
            $query->whereNull('teacher_id');
        }

        return $query;
    }
}
