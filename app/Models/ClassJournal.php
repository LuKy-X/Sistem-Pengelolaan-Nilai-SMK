<?php

namespace App\Models;

use App\Enums\AttendanceStatus;
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

    public function getSakitCountAttribute(): int
    {
        $count = $this->relationLoaded('attendances')
            ? $this->attendances->where('status', AttendanceStatus::Sick)->count()
            : 0;

        if ($count === 0 && preg_match('/Sakit:\s*(\d+)/i', $this->notes ?? '', $m)) {
            return (int) $m[1];
        }

        return $count;
    }

    public function getIzinCountAttribute(): int
    {
        $count = $this->relationLoaded('attendances')
            ? $this->attendances->where('status', AttendanceStatus::Permit)->count()
            : 0;

        if ($count === 0 && preg_match('/Izin:\s*(\d+)/i', $this->notes ?? '', $m)) {
            return (int) $m[1];
        }

        return $count;
    }

    public function getAlphaCountAttribute(): int
    {
        $count = $this->relationLoaded('attendances')
            ? $this->attendances->where('status', AttendanceStatus::Absent)->count()
            : 0;

        if ($count === 0 && preg_match('/Alpha:\s*(\d+)/i', $this->notes ?? '', $m)) {
            return (int) $m[1];
        }

        return $count;
    }

    public function getHadirCountAttribute(): int
    {
        $present = $this->relationLoaded('attendances')
            ? $this->attendances->where('status', AttendanceStatus::Present)->count()
            : 0;

        if ($present > 0) {
            return $present;
        }

        // Determine true total active students enrolled in this class from database
        $total = 0;
        if ($this->relationLoaded('teachingAssignment') && $this->teachingAssignment?->relationLoaded('schoolClass')) {
            $class = $this->teachingAssignment->schoolClass;
            if ($class?->relationLoaded('enrollments')) {
                $total = $class->enrollments->where('status', 'ACTIVE')->count();
            } else {
                $total = $class->enrollments()->where('status', 'ACTIVE')->count();
            }
        } elseif ($this->teaching_assignment_id) {
            $total = ClassEnrollment::where('class_id', $this->teachingAssignment?->class_id)
                ->where('status', 'ACTIVE')
                ->count();
        }

        $nonPresent = $this->sakit_count + $this->izin_count + $this->alpha_count;

        if ($total > 0) {
            return max(0, $total - $nonPresent);
        }

        // Fallback for tests or legacy notes if no enrollments exist in database
        if (preg_match('/Hadir:\s*(\d+)/i', $this->notes ?? '', $m)) {
            return (int) $m[1];
        }

        return 0;
    }
}
