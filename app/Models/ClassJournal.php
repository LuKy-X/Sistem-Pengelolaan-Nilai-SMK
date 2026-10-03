<?php

namespace App\Models;

use App\Enums\AttendanceStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\Relation;

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

    /**
     * Jumlah siswa yang tercatat tidak hadir pada sesi ini.
     */
    public function absenceCount(AttendanceStatus $status): int
    {
        return $this->attendances->where('status', $status)->count();
    }

    /**
     * Jumlah siswa aktif di kelas pada saat jurnal ini dibuat.
     */
    public function activeStudentCount(): int
    {
        return $this->teachingAssignment?->schoolClass?->activeStudentCount() ?? 0;
    }

    public function getSakitCountAttribute(): int
    {
<<<<<<< HEAD
        return $this->absenceCount(AttendanceStatus::Sick);
=======
        $count = $this->relationLoaded('attendances')
            ? $this->attendances->where('status', AttendanceStatus::Sick)->count()
            : 0;

        if ($count === 0 && preg_match('/Sakit:\s*(\d+)/i', $this->notes ?? '', $m)) {
            return (int) $m[1];
        }

        return $count;
>>>>>>> 20ced0b66372a12f543f8ad88845821ac4f25b47
    }

    public function getIzinCountAttribute(): int
    {
<<<<<<< HEAD
        return $this->absenceCount(AttendanceStatus::Permit);
=======
        $count = $this->relationLoaded('attendances')
            ? $this->attendances->where('status', AttendanceStatus::Permit)->count()
            : 0;

        if ($count === 0 && preg_match('/Izin:\s*(\d+)/i', $this->notes ?? '', $m)) {
            return (int) $m[1];
        }

        return $count;
>>>>>>> 20ced0b66372a12f543f8ad88845821ac4f25b47
    }

    public function getAlphaCountAttribute(): int
    {
<<<<<<< HEAD
        return $this->absenceCount(AttendanceStatus::Absent);
=======
        $count = $this->relationLoaded('attendances')
            ? $this->attendances->where('status', AttendanceStatus::Absent)->count()
            : 0;

        if ($count === 0 && preg_match('/Alpha:\s*(\d+)/i', $this->notes ?? '', $m)) {
            return (int) $m[1];
        }

        return $count;
>>>>>>> 20ced0b66372a12f543f8ad88845821ac4f25b47
    }

    /**
     * Jumlah siswa hadir dihitung dari data nyata: siswa aktif di kelas dikurangi yang
     * tercatat sakit, izin, atau alpha.
     *
     * Ringkasan "Hadir: N" di kolom notes hanya dipakai sebagai cadangan terakhir karena
     * kolom tersebut adalah ringkasan bebas yang bisa basi bila data siswa berubah.
     */
    public function getHadirCountAttribute(): int
    {
<<<<<<< HEAD
        $totalStudents = $this->activeStudentCount();

        if ($totalStudents > 0) {
            return max(0, $totalStudents - ($this->sakit_count + $this->izin_count + $this->alpha_count));
        }

        $presentRows = $this->attendances->where('status', AttendanceStatus::Present)->count();

        if ($presentRows > 0) {
            return $presentRows;
        }

        if (preg_match('/Hadir:\s*(\d+)/i', $this->notes ?? '', $matches)) {
            return (int) $matches[1];
        }

        return 0;
    }

    /**
     * Eager load jumlah siswa aktif per kelas supaya accessor rekap tidak melakukan query per jurnal.
     *
     * @param  Builder<static>  $query
     * @return Builder<static>
     */
    public function scopeWithActiveClassStudentCount(Builder $query): Builder
    {
        return $query->with([
            'teachingAssignment.schoolClass' => fn (Relation $classRelation) => $classRelation
                ->withCount([
                    'enrollments as active_enrollments_count' => fn (Builder $enrollmentQuery) => $enrollmentQuery
                        ->where('status', 'ACTIVE'),
                ]),
        ]);
=======
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
>>>>>>> 20ced0b66372a12f543f8ad88845821ac4f25b47
    }
}
