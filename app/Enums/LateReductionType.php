<?php

namespace App\Enums;

enum LateReductionType: string
{
    case Percentage = 'PERCENTAGE';
    case FixedPoints = 'FIXED_POINTS';
}
