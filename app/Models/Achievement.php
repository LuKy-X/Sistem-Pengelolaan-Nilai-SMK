<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

#[Fillable([
    'achievement_category_id',
    'title',
    'scope',
    'level',
    'achievement_date',
    'organizer',
    'rank',
    'description',
    'is_featured',
])]
class Achievement extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'achievement_date' => 'date',
            'is_featured' => 'boolean',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(AchievementCategory::class, 'achievement_category_id');
    }

    public function participants(): HasMany
    {
        return $this->hasMany(AchievementParticipant::class);
    }

    public function students(): BelongsToMany
    {
        return $this->belongsToMany(StudentProfile::class, 'achievement_participants', 'achievement_id', 'student_id')
            ->withPivot(['role', 'description']);
    }

    public function media(): MorphMany
    {
        return $this->morphMany(Media::class, 'mediable');
    }
}
