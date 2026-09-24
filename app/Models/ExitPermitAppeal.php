<?php

namespace App\Models;

use App\Enums\AppealDecision;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'exit_permit_id',
    'submitted_at',
    'reason',
    'decision',
    'decision_note',
    'decided_by',
    'decided_at',
])]
class ExitPermitAppeal extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'decision' => AppealDecision::class,
            'submitted_at' => 'datetime',
            'decided_at' => 'datetime',
        ];
    }

    public function exitPermit(): BelongsTo
    {
        return $this->belongsTo(ExitPermit::class);
    }

    public function decider(): BelongsTo
    {
        return $this->belongsTo(StaffProfile::class, 'decided_by');
    }
}
