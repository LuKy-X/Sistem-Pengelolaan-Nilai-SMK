<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'school_name',
    'npsn',
    'principal_name',
    'address',
    'phone',
    'email',
    'website',
    'description',
    'vision',
    'mission',
    'history',
    'logo',
    'hero_image',
])]
class SchoolProfile extends Model
{
    use HasFactory;

    protected $table = 'school_profile';

    /**
     * Get school logo URL, falling back to the official SMK Negeri 2 Karanganyar logo.
     */
    public function getLogoUrlAttribute(): string
    {
        if (! empty($this->logo)) {
            if (str_starts_with($this->logo, 'http')) {
                return $this->logo;
            }

            return asset('storage/'.$this->logo);
        }

        return asset('assets/images/logo/logo.png');
    }
}
