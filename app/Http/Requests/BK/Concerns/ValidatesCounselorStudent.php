<?php

namespace App\Http\Requests\BK\Concerns;

use Illuminate\Database\Query\Builder;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Exists;

trait ValidatesCounselorStudent
{
    /**
     * Aturan student_id yang hanya menerima siswa aktif di kelas binaan Guru BK.
     */
    protected function counselorStudentRule(): Exists
    {
        $classIds = $this->user()?->counseledClassIds() ?? [];

        return Rule::exists('student_profiles', 'id')->where(function (Builder $query) use ($classIds) {
            $query->where('status', 'ACTIVE')
                ->whereIn('id', function (Builder $sub) use ($classIds) {
                    $sub->select('student_id')
                        ->from('class_enrollments')
                        ->where('status', 'ACTIVE')
                        ->whereIn('class_id', $classIds === [] ? [0] : $classIds);
                });
        });
    }
}
