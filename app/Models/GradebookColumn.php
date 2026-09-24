<?php

namespace App\Models;

use App\Enums\GradebookCalculationType;
use App\Enums\GradebookColumnType;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'gradebook_id',
    'category_id',
    'name',
    'code',
    'column_type',
    'calculation_type',
    'max_score',
    'weight',
    'sort_order',
    'is_visible',
    'is_included_in_average',
])]
class GradebookColumn extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'column_type' => GradebookColumnType::class,
            'calculation_type' => GradebookCalculationType::class,
            'max_score' => 'decimal:2',
            'weight' => 'decimal:2',
            'sort_order' => 'integer',
            'is_visible' => 'boolean',
            'is_included_in_average' => 'boolean',
        ];
    }

    public function gradebook(): BelongsTo
    {
        return $this->belongsTo(Gradebook::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(GradebookCategory::class, 'category_id');
    }

    public function assessments(): HasMany
    {
        return $this->hasMany(Assessment::class, 'gradebook_column_id');
    }

    public function scores(): HasMany
    {
        return $this->hasMany(GradebookScore::class, 'gradebook_column_id');
    }

    public function summarySources(): HasMany
    {
        return $this->hasMany(GradebookColumnSource::class, 'summary_column_id');
    }

    public function usedInSummaries(): HasMany
    {
        return $this->hasMany(GradebookColumnSource::class, 'source_column_id');
    }
}
