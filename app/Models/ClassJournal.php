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
        $count = $this->attendances->where('status', AttendanceStatus::Sick)->count();
        if ($count === 0 && preg_match('/Sakit:\s*(\d+)/i', $this->notes ?? '', $m)) {
            return (int) $m[1];
        }

        return $count;
    }

    public function getIzinCountAttribute(): int
    {
        $count = $this->attendances->where('status', AttendanceStatus::Permit)->count();
        if ($count === 0 && preg_match('/Izin:\s*(\d+)/i', $this->notes ?? '', $m)) {
            return (int) $m[1];
        }

        return $count;
    }

    public function getAlphaCountAttribute(): int
    {
        $count = $this->attendances->where('status', AttendanceStatus::Absent)->count();
        if ($count === 0 && preg_match('/Alpha:\s*(\d+)/i', $this->notes ?? '', $m)) {
            return (int) $m[1];
        }

        return $count;
    }

    public function getHadirCountAttribute(): int
    {
        if (preg_match('/Hadir:\s*(\d+)/i', $this->notes ?? '', $m)) {
            return (int) $m[1];
        }
        $present = $this->attendances->where('status', AttendanceStatus::Present)->count();
        if ($present > 0) {
            return $present;
        }
        $total = $this->teachingAssignment?->schoolClass?->enrollments()->where('status', 'ACTIVE')->count()
            ?? $this->teachingAssignment?->schoolClass?->students_count
            ?? 36;

        return max(0, $total - ($this->sakit_count + $this->izin_count + $this->alpha_count));
    }
}
