<?php

use App\Models\Role;
use App\Models\TeacherProfile;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Sinkronkan seluruh User yang memiliki role COUNSELOR atau BK ke teacher_profiles
        $counselorRoleIds = Role::whereIn('code', ['COUNSELOR', 'counselor', 'BK', 'bk'])->pluck('id')->all();

        if (! empty($counselorRoleIds)) {
            $counselorUsers = User::whereHas('roles', function ($q) use ($counselorRoleIds) {
                $q->whereIn('roles.id', $counselorRoleIds);
            })->with('staffProfile')->get();

            foreach ($counselorUsers as $user) {
                $existingTeacher = TeacherProfile::where('user_id', $user->id)->first();
                if (! $existingTeacher) {
                    $staff = $user->staffProfile;
                    $nip = $staff?->employee_number;
                    if (! $nip) {
                        $nip = 'BK'.str_pad((string) $user->id, 6, '0', STR_PAD_LEFT);
                    }

                    // Pastikan NIP unik jika sudah dipakai
                    if (TeacherProfile::where('nip', $nip)->exists()) {
                        $nip = 'BK-'.$user->id.'-'.time();
                    }

                    TeacherProfile::create([
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
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Tidak perlu menghapus profil agar data penugasan tidak putus
    }
};
