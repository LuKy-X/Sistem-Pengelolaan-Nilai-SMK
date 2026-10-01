<?php

namespace App\Enums;

enum CareerOpportunityType: string
{
    case Job = 'JOB';
    case Internship = 'INTERNSHIP';

    public function label(): string
    {
        return match ($this) {
            self::Job => 'Lowongan Kerja',
            self::Internship => 'Magang',
        };
    }
}
