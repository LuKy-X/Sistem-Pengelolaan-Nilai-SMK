<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'code',
    'name',
    'short_name',
    'description',
    'vision',
    'mission',
    'career_prospects',
    'is_active',
])]
class Department extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function classes(): HasMany
    {
        return $this->hasMany(SchoolClass::class);
    }

    public function subjects(): HasMany
    {
        return $this->hasMany(Subject::class);
    }

    public function competencies(): HasMany
    {
        return $this->hasMany(DepartmentCompetency::class);
    }

    public function facilities(): HasMany
    {
        return $this->hasMany(DepartmentFacility::class);
    }

    public function studentProducts(): HasMany
    {
        return $this->hasMany(StudentProduct::class);
    }

    /**
     * Cover image URL, resolved from the presentation fallback map in
     * `config/public_site.php` because departments have no image column.
     */
    public function coverImageUrl(): string
    {
        $covers = config('public_site.department_covers');
        $key = strtoupper((string) $this->short_name) ?: strtoupper((string) $this->code);

        $path = $covers[$key] ?? $covers[$this->code] ?? $covers['default'];

        return asset($path);
    }
}
