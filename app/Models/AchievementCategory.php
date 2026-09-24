<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'name',
    'slug',
])]
class AchievementCategory extends Model
{
    use HasFactory;

    public function achievements(): HasMany
    {
        return $this->hasMany(Achievement::class, 'achievement_category_id');
    }
}
