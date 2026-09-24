<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

#[Fillable([
    'user_id',
    'nis',
    'nisn',
    'full_name',
    'gender',
    'birth_place',
    'birth_date',
    'phone',
    'address',
    'entry_date',
    'graduation_date',
    'status',
])]
class StudentProfile extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'birth_date' => 'date',
            'entry_date' => 'date',
            'graduation_date' => 'date',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function classEnrollments(): HasMany
    {
        return $this->hasMany(ClassEnrollment::class, 'student_id');
    }

    public function currentEnrollment(): HasOne
    {
        return $this->hasOne(ClassEnrollment::class, 'student_id')->where('status', 'ACTIVE')->latestOfMany();
    }

    public function gradebookMemberships(): HasMany
    {
        return $this->hasMany(GradebookStudent::class, 'student_id');
    }

    public function scores(): HasMany
    {
        return $this->hasMany(GradebookScore::class, 'student_id');
    }

    public function submissions(): HasMany
    {
        return $this->hasMany(AssessmentSubmission::class, 'student_id');
    }

    public function journalAttendances(): HasMany
    {
        return $this->hasMany(JournalAttendance::class, 'student_id');
    }

    public function exitPermits(): HasMany
    {
        return $this->hasMany(ExitPermit::class, 'student_id');
    }

    public function disciplineRecords(): HasMany
    {
        return $this->hasMany(DisciplineRecord::class, 'student_id');
    }

    public function disciplinaryLetters(): HasMany
    {
        return $this->hasMany(DisciplinaryLetter::class, 'student_id');
    }

    public function alumniProfile(): HasOne
    {
        return $this->hasOne(AlumniProfile::class, 'student_id');
    }

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(StudentProduct::class, 'product_students', 'student_id', 'product_id');
    }

    public function careerApplications(): HasMany
    {
        return $this->hasMany(CareerApplication::class, 'student_id');
    }

    public function achievements(): BelongsToMany
    {
        return $this->belongsToMany(Achievement::class, 'achievement_participants', 'student_id', 'achievement_id')
            ->withPivot(['role', 'description']);
    }
}
