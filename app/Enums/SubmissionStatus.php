<?php

namespace App\Enums;

enum SubmissionStatus: string
{
    case Draft = 'DRAFT';
    case Submitted = 'SUBMITTED';
    case Reviewed = 'REVIEWED';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Draf',
            self::Submitted => 'Dikumpulkan',
            self::Reviewed => 'Sudah Dinilai',
        };
    }
}
