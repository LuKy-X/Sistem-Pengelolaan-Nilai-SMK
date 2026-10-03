<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'sort_order',
    'period_number',
    'start_time',
    'end_time',
    'label',
    'is_break',
])]
class LessonPeriod extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
            'period_number' => 'integer',
            'is_break' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (LessonPeriod $period) {
            if (empty($period->sort_order)) {
                $maxOrder = static::max('sort_order') ?? 0;
                $period->sort_order = $maxOrder + 1;
            }
        });
    }

    public function schedulesAsStart(): HasMany
    {
        return $this->hasMany(TeachingSchedule::class, 'start_period_id');
    }

    public function schedulesAsEnd(): HasMany
    {
        return $this->hasMany(TeachingSchedule::class, 'end_period_id');
    }

    /**
     * Jam pelajaran reguler saja, tanpa jam istirahat, urut dari jam pertama.
     * Dipakai di mana pun siswa memilih jam, misalnya pengajuan izin keluar.
     */
    public function scopeRegular(Builder $query): Builder
    {
        return $query->where('is_break', false)
            ->whereNotNull('period_number')
            ->orderBy('sort_order')
            ->orderBy('period_number');
    }

    /**
     * Label yang tampil ke pengguna, selalu memuat jam mulai dan jam akhir
     * supaya tidak ambigu saat siswa memilih jam keluar maupun jam kembali.
     *
     * Label yang tersimpan di database sengaja tidak dipakai apa adanya karena
     * resequence() dapat menimpanya menjadi "Jam Ke-N" tanpa jam.
     */
    public function displayLabel(): string
    {
        if ($this->is_break || $this->period_number === null) {
            return $this->label;
        }

        $start = $this->startTimeForDisplay();
        $end = $this->endTimeForDisplay();

        return 'Jam Ke-'.$this->period_number." ($start - $end)";
    }

    /**
     * Jam pelajaran reguler yang jam mulainya sudah lewat.
     *
     * Jam lessons disimpan sebagai jam dinding tanpa zona, jadi harus dipasang
     * ke tanggal hari ini lewat setTimeFromTimeString() supaya bisa dibandingkan
     * dengan now() pada kerangka waktu yang sama.
     */
    public function hasAlreadyStarted(): bool
    {
        if ($this->period_number === null || $this->start_time === null) {
            return false;
        }

        return now()->setTimeFromTimeString($this->startTimeForDisplay())->isPast();
    }

    public function startTimeForDisplay(): string
    {
        return substr((string) $this->start_time, 0, 5);
    }

    public function endTimeForDisplay(): string
    {
        return substr((string) $this->end_time, 0, 5);
    }

    /**
     * Urutkan ulang nomor jam pelajaran dan sort_order.
     * Jam istirahat TIDAK memengaruhi urutan nomor jam pelajaran reguler.
     * Semua jam istirahat memiliki label 'Istirahat' dan period_number null.
     */
    public static function resequence(): void
    {
        $all = static::orderBy('sort_order')
            ->orderBy('start_time')
            ->orderBy('id')
            ->get();

        $lessonNumber = 0;
        foreach ($all as $idx => $period) {
            $period->sort_order = $idx + 1;
            if ($period->is_break) {
                $period->period_number = null;
                if (empty($period->label) || str_starts_with($period->label, 'Jam Ke-') || str_starts_with($period->label, 'Istirahat Ke-')) {
                    $period->label = 'Istirahat';
                }
            } else {
                $lessonNumber++;
                $period->period_number = $lessonNumber;
                if (empty($period->label) || str_starts_with($period->label, 'Jam Ke-') || str_starts_with($period->label, 'Istirahat')) {
                    $period->label = "Jam Ke-{$lessonNumber}";
                }
            }
            $period->saveQuietly();
        }
    }
}
