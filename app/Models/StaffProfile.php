<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'user_id',
    'employee_number',
    'full_name',
    'phone',
    'photo',
])]
class StaffProfile extends Model
{
    use HasFactory;

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function approvedExitPermits(): HasMany
    {
        return $this->hasMany(ExitPermit::class, 'approved_by');
    }

    public function decidedAppeals(): HasMany
    {
        return $this->hasMany(ExitPermitAppeal::class, 'decided_by');
    }
}
