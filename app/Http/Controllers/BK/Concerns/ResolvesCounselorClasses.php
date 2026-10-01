<?php

namespace App\Http\Controllers\BK\Concerns;

use App\Models\SchoolClass;
use App\Models\StudentProfile;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

trait ResolvesCounselorClasses
{
    /**
     * Kelas binaan Guru BK yang sedang masuk.
     *
     * @return Collection<int, SchoolClass>
     */
    protected function counselorClasses(): Collection
    {
        $user = auth()->user();

        if ($user === null || ! $user->isCounselor()) {
            return collect();
        }

        return $user->counseledClasses()
            ->orderBy('classes.name')
            ->get();
    }

    /**
     * @return list<int>
     */
    protected function counselorClassIds(): array
    {
        return $this->counselorClasses()->pluck('id')->all();
    }

    /**
     * Siswa aktif yang berada di kelas binaan Guru BK.
     *
     * @return Collection<int, StudentProfile>
     */
    protected function counselorStudentOptions(): Collection
    {
        $classIds = $this->counselorClassIds();

        return StudentProfile::query()
            ->with('currentEnrollment.schoolClass')
            ->where('status', 'ACTIVE')
            ->whereHas('currentEnrollment', fn (Builder $query) => $query->whereIn('class_id', $classIds === [] ? [0] : $classIds))
            ->orderBy('full_name')
            ->get();
    }

    /**
     * Batasi query agar hanya memuat siswa kelas binaan Guru BK.
     *
     * @param  Builder<*>  $query
     */
    protected function scopeToCounselorStudents(Builder $query): void
    {
        $classIds = $this->counselorClassIds();

        $query->whereHas('student.currentEnrollment', fn (Builder $inner) => $inner->whereIn('class_id', $classIds === [] ? [0] : $classIds));
    }
}
