<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'achievement_id',
    'student_id',
    'role',
    'description',
])]
class AchievementParticipant extends Model
{
    use HasFactory;

    public $timestamps = false;

    public function achievement(): BelongsTo
    {
        return $this->belongsTo(Achievement::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(StudentProfile::class, 'student_id');
    }
}
