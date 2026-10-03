<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'academic_year_id',
    'department_id',
    'grade_level_id',
    'homeroom_teacher_id',
    'code',
    'name',
    'is_active',
])]
class SchoolClass extends Model
{
    use HasFactory;

    protected $table = 'classes';

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function gradeLevel(): BelongsTo
    {
        return $this->belongsTo(GradeLevel::class);
    }

    public function homeroomTeacher(): BelongsTo
    {
        return $this->belongsTo(TeacherProfile::class, 'homeroom_teacher_id');
    }

    public function enrollments(): HasMany
    {
        return $this->hasMany(ClassEnrollment::class, 'class_id');
    }

    public function students(): BelongsToMany
    {
        return $this->belongsToMany(StudentProfile::class, 'class_enrollments', 'class_id', 'student_id')
            ->withPivot(['start_date', 'end_date', 'status']);
    }

    public function teachingAssignments(): HasMany
    {
        return $this->hasMany(TeachingAssignment::class, 'class_id');
    }

    /**
     * Guru BK yang membina kelas ini.
     */
    public function counselors(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'counselor_class', 'class_id', 'user_id')
            ->withTimestamps();
    }

    /**
     * Jumlah siswa aktif di kelas ini.
     *
     * Memakai hasil eager load `withCount(['enrollments as active_enrollments_count' => ...])`
     * bila tersedia agar daftar jurnal tidak memicu query per baris.
     */
    public function activeStudentCount(): int
    {
        if (array_key_exists('active_enrollments_count', $this->attributes)) {
            return (int) $this->attributes['active_enrollments_count'];
        }

        return $this->enrollments()->where('status', 'ACTIVE')->count();
    }
}
