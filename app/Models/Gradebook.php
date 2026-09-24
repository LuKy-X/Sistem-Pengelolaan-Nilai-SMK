<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'teaching_assignment_id',
    'name',
    'description',
    'is_active',
])]
class Gradebook extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function teachingAssignment(): BelongsTo
    {
        return $this->belongsTo(TeachingAssignment::class);
    }

    public function categories(): HasMany
    {
        return $this->hasMany(GradebookCategory::class)->orderBy('sort_order');
    }

    public function columns(): HasMany
    {
        return $this->hasMany(GradebookColumn::class)->orderBy('sort_order');
    }

    public function students(): HasMany
    {
        return $this->hasMany(GradebookStudent::class);
    }
}
