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
        return $this->absenceCount(AttendanceStatus::Sick);
    }

    public function getIzinCountAttribute(): int
    {
        return $this->absenceCount(AttendanceStatus::Permit);
    }

    public function getAlphaCountAttribute(): int
    {
        return $this->absenceCount(AttendanceStatus::Absent);
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
    }
}
