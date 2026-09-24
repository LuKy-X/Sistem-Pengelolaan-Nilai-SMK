<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'summary_column_id',
    'source_column_id',
    'weight',
])]
class GradebookColumnSource extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'weight' => 'decimal:2',
        ];
    }

    public function summaryColumn(): BelongsTo
    {
        return $this->belongsTo(GradebookColumn::class, 'summary_column_id');
    }

    public function sourceColumn(): BelongsTo
    {
        return $this->belongsTo(GradebookColumn::class, 'source_column_id');
    }
}
