<?php

namespace App\Models;

use App\Enums\LateReductionType;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'teacher_id',
    'default_late_enabled',
    'default_reduction_type',
    'default_reduction_value',
    'default_interval',
    'default_grace_minutes',
    'default_min_max_score',
])]
class TeacherGradeSetting extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'default_late_enabled' => 'boolean',
            'default_reduction_type' => LateReductionType::class,
            'default_reduction_value' => 'decimal:2',
            'default_interval' => 'integer',
            'default_grace_minutes' => 'integer',
            'default_min_max_score' => 'decimal:2',
        ];
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(TeacherProfile::class, 'teacher_id');
    }
}
