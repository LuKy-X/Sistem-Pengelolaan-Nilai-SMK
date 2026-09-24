<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'student_id',
    'graduation_year',
    'current_occupation',
    'current_company',
    'city',
    'social_link',
    'is_featured',
])]
class AlumniProfile extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'graduation_year' => 'integer',
            'is_featured' => 'boolean',
        ];
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(StudentProfile::class, 'student_id');
    }

    public function stories(): HasMany
    {
        return $this->hasMany(AlumniStory::class, 'alumni_profile_id');
    }
}
