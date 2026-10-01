<?php

namespace App\Services;

use App\Models\Department;
use App\Models\SchoolProfile;
use Illuminate\Support\Collection;
use Throwable;

/**
 * Shared read-only context for the public (unauthenticated) school website.
 *
 * The school profile and the active department list are rendered on every public
 * page through the navbar, footer and hero, so they are memoised per request to
 * avoid repeating the same queries across nested views.
 *
 * Results are intentionally not cached across requests: the CMS content is
 * edited by administrators and there is no reliable invalidation hook, so a
 * long-lived cache would serve stale school data. A single indexed `LIMIT 1`
 * query per request is not worth that trade-off.
 *
 * Every method is defensive: the public site must still render (with an empty
 * state) when the database is unavailable, otherwise the error pages themselves
 * would fail to render.
 */
class PublicSiteService
{
    private ?SchoolProfile $profile = null;

    private bool $profileResolved = false;

    private ?Collection $activeDepartments = null;

    /**
     * The single `school_profile` record, or null when the CMS has not been filled in yet.
     */
    public function profile(): ?SchoolProfile
    {
        if ($this->profileResolved) {
            return $this->profile;
        }

        $this->profileResolved = true;

        try {
            return $this->profile = SchoolProfile::query()->first();
        } catch (Throwable) {
            return $this->profile = null;
        }
    }

    /**
     * Active departments (kompetensi keahlian) used by the public navigation and landing page.
     *
     * @return Collection<int, Department>
     */
    public function activeDepartments(): Collection
    {
        if ($this->activeDepartments !== null) {
            return $this->activeDepartments;
        }

        try {
            return $this->activeDepartments = Department::query()
                ->withCount('competencies')
                ->where('is_active', true)
                ->orderBy('name')
                ->get();
        } catch (Throwable) {
            return $this->activeDepartments = new Collection;
        }
    }

    /**
     * Resolve the display name used across the public site.
     */
    public function schoolName(): string
    {
        return $this->profile()?->school_name
            ?? config('app.name');
    }
}
