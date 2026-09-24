<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'gradebook_id',
    'name',
    'code',
    'weight',
    'sort_order',
    'is_included_in_average',
    'is_active',
])]
class GradebookCategory extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'weight' => 'decimal:2',
            'sort_order' => 'integer',
            'is_included_in_average' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function gradebook(): BelongsTo
    {
        return $this->belongsTo(Gradebook::class);
    }

    public function columns(): HasMany
    {
        return $this->hasMany(GradebookColumn::class, 'category_id')->orderBy('sort_order');
    }
}
