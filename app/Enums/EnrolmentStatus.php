<?php

namespace App\Enums;

enum EnrolmentStatus: string
{
    /** Course joined, waiting for payment. */
    case Pending = 'pending';
    case Active = 'active';
    case Suspended = 'suspended';
    case Cancelled = 'cancelled';
}
