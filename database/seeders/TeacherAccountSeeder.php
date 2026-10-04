<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\TeacherProfile;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class TeacherAccountSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $teacherRole = Role::query()->where('code', 'TEACHER')->firstOrFail();
        $defaultPassword = Hash::make('password123');

        foreach (require database_path('seeders/data/teacher_accounts.php') as $teacher) {
            $username = 'guru'.$teacher['source_id'];

            $user = User::query()->firstOrCreate(
                ['username' => $username],
                [
                    'name' => $teacher['full_name'],
                    'email' => $username.'@smk.test',
                    'password' => $defaultPassword,
                    'is_active' => true,
                ]
            );

            $user->roles()->syncWithoutDetaching([$teacherRole->id]);

            TeacherProfile::query()->firstOrCreate(
                ['user_id' => $user->id],
                [
                    'nip' => 'DIR-'.$teacher['source_id'],
                    'full_name' => $teacher['full_name'],
                    'gender' => $teacher['gender'],
                    'status' => 'ACTIVE',
                ]
            );
        }
    }
}
