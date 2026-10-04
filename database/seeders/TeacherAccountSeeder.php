<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\SchoolClass;
use App\Models\StaffProfile;
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
        $counselorRole = Role::query()->where('code', 'COUNSELOR')->first();
        $defaultPassword = Hash::make('password123');

        $counselorNames = [
            'Endah Dwi Sayekti',
            'Fitriyah Maimun Thofiah',
            'Puput Sinta Dewi',
        ];

        foreach (require database_path('seeders/data/teacher_accounts.php') as $teacher) {
            $username = 'guru'.$teacher['source_id'];
            $isCounselor = in_array($teacher['full_name'], $counselorNames, true)
                || ($teacher['competency'] ?? '') === 'Bimbingan Konseling';

            $user = User::query()->firstOrCreate(
                ['username' => $username],
                [
                    'name' => $teacher['full_name'],
                    'email' => $username.'@smk.test',
                    'password' => $defaultPassword,
                    'is_active' => true,
                ]
            );

            $rolesToSync = [$teacherRole->id];
            if ($isCounselor && $counselorRole) {
                $rolesToSync[] = $counselorRole->id;
            }

            $user->roles()->syncWithoutDetaching($rolesToSync);

            TeacherProfile::query()->firstOrCreate(
                ['user_id' => $user->id],
                [
                    'nip' => 'DIR-'.$teacher['source_id'],
                    'full_name' => $teacher['full_name'],
                    'gender' => $teacher['gender'],
                    'status' => 'ACTIVE',
                ]
            );

            if ($isCounselor) {
                StaffProfile::query()->firstOrCreate(
                    ['user_id' => $user->id],
                    [
                        'employee_number' => 'DIR-'.$teacher['source_id'],
                        'full_name' => $teacher['full_name'],
                        'phone' => '0812'.str_pad((string) $teacher['source_id'], 8, '0', STR_PAD_LEFT),
                    ]
                );

                $classIds = SchoolClass::query()->pluck('id')->all();
                if (! empty($classIds)) {
                    $user->counseledClasses()->syncWithoutDetaching($classIds);
                }
            }
        }
    }
}
