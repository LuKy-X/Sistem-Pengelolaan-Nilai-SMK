<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'teacher_id',
    'subject_id',
    'class_id',
    'semester_id',
    'weekly_hours',
    'is_active',
])]
class TeachingAssignment extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'weekly_hours' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(TeacherProfile::class, 'teacher_id');
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class, 'subject_id');
    }

    public function schoolClass(): BelongsTo
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }

    public function semester(): BelongsTo
    {
        return $this->belongsTo(Semester::class, 'semester_id');
    }

    public function schedules(): HasMany
    {
        return $this->hasMany(TeachingSchedule::class);
    }

    public function gradebooks(): HasMany
    {
        return $this->hasMany(Gradebook::class);
    }

    public function journals(): HasMany
    {
        return $this->hasMany(ClassJournal::class);
    }

    public function assessments(): HasMany
    {
        return $this->hasMany(Assessment::class);
    }
}
