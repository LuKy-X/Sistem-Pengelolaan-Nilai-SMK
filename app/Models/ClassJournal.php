<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'teaching_assignment_id',
    'schedule_id',
    'journal_date',
    'start_period_id',
    'end_period_id',
    'material',
    'notes',
    'created_by',
])]
class ClassJournal extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'journal_date' => 'date',
        ];
    }

    public function teachingAssignment(): BelongsTo
    {
        return $this->belongsTo(TeachingAssignment::class);
    }

    public function schedule(): BelongsTo
    {
        return $this->belongsTo(TeachingSchedule::class, 'schedule_id');
    }

    public function startPeriod(): BelongsTo
    {
        return $this->belongsTo(LessonPeriod::class, 'start_period_id');
    }

    public function endPeriod(): BelongsTo
    {
        return $this->belongsTo(LessonPeriod::class, 'end_period_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(TeacherProfile::class, 'created_by');
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(JournalAttendance::class, 'journal_id');
    }
}
