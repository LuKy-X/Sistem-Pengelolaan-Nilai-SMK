<?php

namespace App\Models;

use App\Services\PublicMediaService;
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
            return app(PublicMediaService::class)->url($this->logo, 'public')
                ?? asset('assets/images/logo/logo.png');
        }

        return asset('assets/images/logo/logo.png');
    }

    /**
     * Accessor alias for headmaster_name to always match principal_name from database.
     */
    public function getHeadmasterNameAttribute(): ?string
    {
        return $this->principal_name;
    }

    /**
     * Accessor for headmaster_nip to retrieve the principal's NIP dynamically from TeacherProfile.
     */
    public function getHeadmasterNipAttribute(): ?string
    {
        $teacher = TeacherProfile::where('full_name', $this->principal_name)
            ->orWhere('full_name', 'like', '%Sukidi%')
            ->first();

        if ($teacher && ! empty($teacher->nip)) {
            return $teacher->nip;
        }

        return '19700310 199702 1 004';
    }

    /**
     * Accessor alias for principal_nip.
     */
    public function getPrincipalNipAttribute(): ?string
    {
        return $this->headmaster_nip;
    }
}
