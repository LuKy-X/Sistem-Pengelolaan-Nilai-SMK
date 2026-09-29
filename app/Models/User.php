<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable([
    'username',
    'name',
    'email',
    'password',
    'is_active',
    'last_login_at',
])]
#[Hidden([
    'password',
    'remember_token',
])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_login_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'role_user');
    }

    public function teacherProfile(): HasOne
    {
        return $this->hasOne(TeacherProfile::class);
    }

    public function studentProfile(): HasOne
    {
        return $this->hasOne(StudentProfile::class);
    }

    public function staffProfile(): HasOne
    {
        return $this->hasOne(StaffProfile::class);
    }

    public function hasRole(string|array $roles): bool
    {
        $roles = (array) $roles;
        $aliasMap = [
            'GURU' => 'TEACHER',
            'TEACHER' => 'GURU',
            'SISWA' => 'STUDENT',
            'STUDENT' => 'SISWA',
            'BK' => 'COUNSELOR',
            'COUNSELOR' => 'BK',
        ];

        $expandedRoles = [];
        foreach ($roles as $r) {
            $upper = strtoupper($r);
            $expandedRoles[] = $upper;
            if (isset($aliasMap[$upper])) {
                $expandedRoles[] = $aliasMap[$upper];
            }
        }

        return $this->roles->contains(function ($role) use ($expandedRoles) {
            return in_array(strtoupper($role->code), $expandedRoles, true);
        });
    }

    public function isAdmin(): bool
    {
        return $this->hasRole('ADMIN');
    }

    public function isTeacher(): bool
    {
        return $this->hasRole('TEACHER');
    }

    public function isStudent(): bool
    {
        return $this->hasRole('STUDENT');
    }

    public function isCounselor(): bool
    {
        return $this->hasRole('COUNSELOR');
    }

    /**
     * Named route the user should land on after signing in.
     */
    public function dashboardRouteName(): string
    {
        return match (true) {
            $this->isAdmin() => 'admin.dashboard',
            $this->isTeacher() => 'teacher.dashboard',
            $this->isCounselor() => 'counselor.dashboard',
            $this->isStudent() => 'student.dashboard',
            default => 'public.home',
        };
    }
}
