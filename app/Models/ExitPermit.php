<?php

namespace App\Models;

use App\Enums\ExitPermitStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;

#[Fillable([
    'student_id',
    'reason_id',
    'reason_detail',
    'exit_period_id',
    'return_period_id',
    'requested_at',
    'planned_exit_at',
    'planned_return_at',
    'approved_at',
    'approved_by',
    'actual_exit_at',
    'actual_return_at',
    'status',
    'approval_note',
    'rejection_note',
])]
class ExitPermit extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'status' => ExitPermitStatus::class,
            'requested_at' => 'datetime',
            'planned_exit_at' => 'datetime',
            'planned_return_at' => 'datetime',
            'approved_at' => 'datetime',
            'actual_exit_at' => 'datetime',
            'actual_return_at' => 'datetime',
        ];
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(StudentProfile::class, 'student_id');
    }

    public function reason(): BelongsTo
    {
        return $this->belongsTo(ExitPermitReason::class, 'reason_id');
    }

    /**
     * Jam pelajaran saat siswa keluar. Nullable karena izin lama belum punya
     * rujukan jam pelajaran.
     */
    public function exitPeriod(): BelongsTo
    {
        return $this->belongsTo(LessonPeriod::class, 'exit_period_id');
    }

    /**
     * Jam pelajaran paling akhir siswa harus sudah kembali.
     */
    public function returnPeriod(): BelongsTo
    {
        return $this->belongsTo(LessonPeriod::class, 'return_period_id');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(StaffProfile::class, 'approved_by');
    }

    public function appeal(): HasOne
    {
        return $this->hasOne(ExitPermitAppeal::class, 'exit_permit_id');
    }

    public function media(): MorphMany
    {
        return $this->morphMany(Media::class, 'mediable');
    }

    public function getMinutesRemainingAttribute(): int
    {
        if ($this->status !== ExitPermitStatus::Approved && $this->status !== ExitPermitStatus::Late) {
            return 0;
        }

        return (int) now()->diffInMinutes($this->planned_return_at, false);
    }

    public function isOverdue(): bool
    {
        if ($this->status === ExitPermitStatus::Completed || $this->status === ExitPermitStatus::Rejected || $this->status === ExitPermitStatus::Cancelled) {
            return false;
        }

        return now()->isAfter($this->planned_return_at);
    }

    /**
     * Ringkasan jam pelajaran untuk ditampilkan, misal "Jam Ke-7 (12.30 - 13.15)".
     * Bernull untuk izin lama yang tidak menyimpan rujukan jam pelajaran.
     */
    public function periodSummary(): ?string
    {
        if ($this->exitPeriod === null || $this->returnPeriod === null) {
            return null;
        }

        return sprintf(
            '%s keluar, harus sudah kembali di %s',
            $this->exitPeriod->displayLabel(),
            $this->returnPeriod->displayLabel(),
        );
    }
}
