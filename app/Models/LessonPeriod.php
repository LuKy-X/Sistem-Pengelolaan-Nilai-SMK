<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'period_number',
    'start_time',
    'end_time',
    'label',
])]
class LessonPeriod extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'period_number' => 'integer',
        ];
    }

    public function schedulesAsStart(): HasMany
    {
        return $this->hasMany(TeachingSchedule::class, 'start_period_id');
    }

    public function schedulesAsEnd(): HasMany
    {
        return $this->hasMany(TeachingSchedule::class, 'end_period_id');
    }
}
