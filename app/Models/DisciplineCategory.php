<?php

namespace App\Models;

use App\Enums\DisciplineCategoryType;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'name',
    'type',
    'default_points',
    'description',
    'is_active',
])]
class DisciplineCategory extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'type' => DisciplineCategoryType::class,
            'default_points' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function records(): HasMany
    {
        return $this->hasMany(DisciplineRecord::class, 'category_id');
    }
}
