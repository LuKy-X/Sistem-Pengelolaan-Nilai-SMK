<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'teaching_assignment_id',
    'day_of_week',
    'start_period_id',
    'end_period_id',
    'room',
])]
class TeachingSchedule extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'day_of_week' => 'integer',
        ];
    }

    public function teachingAssignment(): BelongsTo
    {
        return $this->belongsTo(TeachingAssignment::class);
    }

    public function startPeriod(): BelongsTo
    {
        return $this->belongsTo(LessonPeriod::class, 'start_period_id');
    }

    public function endPeriod(): BelongsTo
    {
        return $this->belongsTo(LessonPeriod::class, 'end_period_id');
    }

    public function journals(): HasMany
    {
        return $this->hasMany(ClassJournal::class, 'schedule_id');
    }
}
