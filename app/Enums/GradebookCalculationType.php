<?php

namespace App\Enums;

enum GradebookCalculationType: string
{
    case Average = 'AVERAGE';
    case Sum = 'SUM';
    case WeightedAverage = 'WEIGHTED_AVERAGE';
}
