<?php

namespace App\Http\Controllers\BK\Concerns;

use App\Models\ClassEnrollment;
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
     * Apakah Guru BK berwenang menangani data siswa ini.
     *
     * Admin tidak dibatasi karena selalu punya akses ke seluruh sekolah.
     */
    protected function counselorManagesStudent(int|StudentProfile $student): bool
    {
        $user = auth()->user();

        if ($user === null) {
            return false;
        }

        if ($user->isAdmin()) {
            return true;
        }

        $studentId = $student instanceof StudentProfile ? $student->getKey() : $student;
        $classIds = $this->counselorClassIds();

        if ($classIds === []) {
            return false;
        }

        return ClassEnrollment::query()
            ->where('student_id', $studentId)
            ->where('status', 'ACTIVE')
            ->whereIn('class_id', $classIds)
            ->exists();
    }

    /**
     * Batasi akses ke data siswa milik kelas bounty; 403 bila di luar cakupan.
     */
    protected function authorizeCounselorStudent(int|StudentProfile $student, string $message = 'Anda tidak memiliki akses ke data siswa ini.'): void
    {
        abort_unless($this->counselorManagesStudent($student), 403, $message);
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
