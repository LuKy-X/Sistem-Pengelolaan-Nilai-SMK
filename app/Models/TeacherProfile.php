<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

#[Fillable([
    'user_id',
    'nip',
    'full_name',
    'gender',
    'phone',
    'photo',
    'status',
])]
class TeacherProfile extends Model
{
    use HasFactory;

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function teachingAssignments(): HasMany
    {
        return $this->hasMany(TeachingAssignment::class, 'teacher_id');
    }

    public function gradeSettings(): HasOne
    {
        return $this->hasOne(TeacherGradeSetting::class, 'teacher_id');
    }

    public function homeroomClasses(): HasMany
    {
        return $this->hasMany(SchoolClass::class, 'homeroom_teacher_id');
    }

    public function rubrics(): HasMany
    {
        return $this->hasMany(Rubric::class, 'created_by');
    }

    public function assessments(): HasMany
    {
        return $this->hasMany(Assessment::class, 'created_by');
    }

    public function journals(): HasMany
    {
        return $this->hasMany(ClassJournal::class, 'created_by');
    }

    public function isCounselor(): bool
    {
        return $this->user?->hasRole(['COUNSELOR', 'BK']) ?? false;
    }

    /**
     * Pastikan semua akun dengan role COUNSELOR / BK memiliki TeacherProfile aktif.
     */
    public static function ensureCounselorProfiles(): void
    {
        $counselorRoleIds = Role::whereIn('code', ['COUNSELOR', 'counselor', 'BK', 'bk'])->pluck('id')->all();
        if (empty($counselorRoleIds)) {
            return;
        }

        $counselorUsers = User::whereHas('roles', function ($q) use ($counselorRoleIds) {
            $q->whereIn('roles.id', $counselorRoleIds);
        })->whereDoesntHave('teacherProfile')->with('staffProfile')->get();

        foreach ($counselorUsers as $user) {
            $staff = $user->staffProfile;
            $nip = $staff?->employee_number ?: 'BK'.str_pad((string) $user->id, 6, '0', STR_PAD_LEFT);
            if (self::where('nip', $nip)->exists()) {
                $nip = 'BK-'.$user->id.'-'.time();
            }

            self::create([
                'user_id' => $user->id,
                'nip' => $nip,
                'full_name' => $staff?->full_name ?? $user->name,
                'gender' => 'MALE',
                'phone' => $staff?->phone,
                'status' => 'ACTIVE',
            ]);
        }
    }
}
