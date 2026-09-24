<?php

namespace App\Models;

use App\Enums\LateReductionType;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'assessment_id',
    'enabled',
    'reduction_type',
    'reduction_value',
    'interval',
    'grace_period_minutes',
    'minimum_max_score',
])]
class AssessmentLatePolicy extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'enabled' => 'boolean',
            'reduction_type' => LateReductionType::class,
            'reduction_value' => 'decimal:2',
            'interval' => 'integer',
            'grace_period_minutes' => 'integer',
            'minimum_max_score' => 'decimal:2',
        ];
    }

    public function assessment(): BelongsTo
    {
        return $this->belongsTo(Assessment::class);
    }
}
