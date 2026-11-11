<?php

namespace App\Tenancy;

use RuntimeException;

final class TenantNotResolved extends RuntimeException
{
    public function __construct()
    {
        parent::__construct(
            'A tenant model was used without a current teacher. '
            .'Resolve the tenant first, or wrap the code in Teacher::withoutTenancy().'
        );
    }
}
