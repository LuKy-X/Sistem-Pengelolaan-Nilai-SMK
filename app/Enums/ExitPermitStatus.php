<?php

namespace App\Enums;

enum ExitPermitStatus: string
{
    case Pending = 'PENDING';
    case Approved = 'APPROVED';
    case Rejected = 'REJECTED';
    case Completed = 'COMPLETED';
    case Late = 'LATE';
    case Cancelled = 'CANCELLED';
}
