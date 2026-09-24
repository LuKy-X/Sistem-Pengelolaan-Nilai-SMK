<?php

namespace App\Enums;

enum AttendanceStatus: string
{
    case Present = 'PRESENT';
    case Sick = 'SICK';
    case Permit = 'PERMIT';
    case Absent = 'ABSENT';
}
