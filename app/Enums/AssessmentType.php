<?php

namespace App\Enums;

enum AssessmentType: string
{
    case Task = 'TASK';
    case Quiz = 'QUIZ';
    case Project = 'PROJECT';
    case Exam = 'EXAM';
    case Remedial = 'REMEDIAL';
    case Other = 'OTHER';
}
