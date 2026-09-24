<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'rubric_id',
    'criterion',
    'description',
    'max_points',
    'sort_order',
])]
class RubricCriterion extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'max_points' => 'decimal:2',
            'sort_order' => 'integer',
        ];
    }

    public function rubric(): BelongsTo
    {
        return $this->belongsTo(Rubric::class);
    }

    public function scores(): HasMany
    {
        return $this->hasMany(RubricScore::class, 'rubric_criterion_id');
    }
}
