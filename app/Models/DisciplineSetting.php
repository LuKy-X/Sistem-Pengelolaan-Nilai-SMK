<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'academic_year_id',
    'initial_points',
    'minimum_points',
    'warning_threshold',
    'sp1_threshold',
    'sp2_threshold',
    'sp3_threshold',
])]
class DisciplineSetting extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'initial_points' => 'integer',
            'minimum_points' => 'integer',
            'warning_threshold' => 'integer',
            'sp1_threshold' => 'integer',
            'sp2_threshold' => 'integer',
            'sp3_threshold' => 'integer',
        ];
    }

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }
}
