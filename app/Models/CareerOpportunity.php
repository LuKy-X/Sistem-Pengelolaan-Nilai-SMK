<?php

namespace App\Models;

use App\Enums\CareerOpportunityStatus;
use App\Enums\CareerOpportunityType;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'company_id',
    'type',
    'title',
    'description',
    'requirements',
    'location',
    'open_date',
    'close_date',
    'application_link',
    'status',
])]
class CareerOpportunity extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'type' => CareerOpportunityType::class,
            'status' => CareerOpportunityStatus::class,
            'open_date' => 'date',
            'close_date' => 'date',
        ];
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(CareerCompany::class, 'company_id');
    }

    public function applications(): HasMany
    {
        return $this->hasMany(CareerApplication::class, 'opportunity_id');
    }
}
