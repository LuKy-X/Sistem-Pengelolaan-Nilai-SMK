<?php

namespace App\Enums;

enum AppealDecision: string
{
    case Pending = 'PENDING';
    case Accepted = 'ACCEPTED';
    case Rejected = 'REJECTED';
}
