<?php

namespace App\Models;

use App\Enums\ExitPermitStatus;
use Carbon\Carbon;
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
    'requested_at',
    'planned_exit_at',
    'planned_return_at',
    'approved_at',
    'approved_by',
    'approved_exit_at',
    'approved_return_at',
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
            'approved_exit_at' => 'datetime',
            'approved_return_at' => 'datetime',
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

    /**
     * Jam keluar yang berlaku: jam yang disetujui BK bila ada, jika tidak memakai rencana siswa.
     */
    public function effectiveExitAt(): Carbon
    {
        return $this->approved_exit_at ?? $this->planned_exit_at;
    }

    /**
     * Jam kembali yang berlaku: jam yang disetujui BK bila ada, jika tidak memakai rencana siswa.
     * Batas countdown dan deteksi keterlambatan selalu memakai nilai ini.
     */
    public function effectiveReturnAt(): Carbon
    {
        return $this->approved_return_at ?? $this->planned_return_at;
    }

    /**
     * Apakah BK menetapkan jam keluar/kembali sendiri di luar rencana pengajuan.
     */
    public function hasCustomApprovedTime(): bool
    {
        return $this->approved_exit_at !== null || $this->approved_return_at !== null;
    }

    public function getMinutesRemainingAttribute(): int
    {
        if ($this->status !== ExitPermitStatus::Approved && $this->status !== ExitPermitStatus::Late) {
            return 0;
        }

        return (int) now()->diffInMinutes($this->effectiveReturnAt(), false);
    }

    public function isOverdue(): bool
    {
        if ($this->status === ExitPermitStatus::Completed || $this->status === ExitPermitStatus::Rejected || $this->status === ExitPermitStatus::Cancelled) {
            return false;
        }

        return now()->isAfter($this->effectiveReturnAt());
    }
}
