<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
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

    public function getFormattedStartTimeAttribute(): string
    {
        return $this->start_time ? substr((string) $this->start_time, 0, 5) : '';
    }

    public function getFormattedEndTimeAttribute(): string
    {
        return $this->end_time ? substr((string) $this->end_time, 0, 5) : '';
    }

    public function getNameAttribute(): string
    {
        if (! empty($this->attributes['name'] ?? null)) {
            return $this->attributes['name'];
        }

        if ($this->is_break) {
            $cleaned = preg_replace('/\s*\(.*\)/', '', $this->label ?? '');

            return ! empty($cleaned) ? trim($cleaned) : 'Istirahat';
        }

        return "Jam Ke-{$this->period_number}";
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
