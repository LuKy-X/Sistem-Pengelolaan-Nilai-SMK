<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

#[Fillable([
    'user_id',
    'nip',
    'full_name',
    'gender',
    'phone',
    'photo',
    'status',
])]
class TeacherProfile extends Model
{
    use HasFactory;

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function teachingAssignments(): HasMany
    {
        return $this->hasMany(TeachingAssignment::class, 'teacher_id');
    }

    public function gradeSettings(): HasOne
    {
        return $this->hasOne(TeacherGradeSetting::class, 'teacher_id');
    }

    public function homeroomClasses(): HasMany
    {
        return $this->hasMany(SchoolClass::class, 'homeroom_teacher_id');
    }

    public function rubrics(): HasMany
    {
        return $this->hasMany(Rubric::class, 'created_by');
    }

    public function assessments(): HasMany
    {
        return $this->hasMany(Assessment::class, 'created_by');
    }

    public function journals(): HasMany
    {
        return $this->hasMany(ClassJournal::class, 'created_by');
    }
}
