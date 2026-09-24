<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'academic_year_id',
    'title',
    'registration_start',
    'registration_end',
    'description',
    'status',
])]
class AdmissionPeriod extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'registration_start' => 'date',
            'registration_end' => 'date',
        ];
    }

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function scheduleItems(): HasMany
    {
        return $this->hasMany(AdmissionScheduleItem::class)->orderBy('step_number');
    }

    public function paths(): HasMany
    {
        return $this->hasMany(AdmissionPath::class)->orderBy('sort_order');
    }

    public function requirements(): HasMany
    {
        return $this->hasMany(AdmissionRequirement::class)->orderBy('sort_order');
    }

    public function feeItems(): HasMany
    {
        return $this->hasMany(AdmissionFeeItem::class)->orderBy('sort_order');
    }
}
