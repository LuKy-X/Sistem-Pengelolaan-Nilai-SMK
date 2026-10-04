<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\StaffProfile;
use App\Models\StudentProfile;
use App\Models\TeacherGradeSetting;
use App\Models\TeacherProfile;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $adminRole = Role::where('code', 'ADMIN')->first();
        $teacherRole = Role::where('code', 'TEACHER')->first();
        $studentRole = Role::where('code', 'STUDENT')->first();
        $counselorRole = Role::where('code', 'COUNSELOR')->first();

        $defaultPassword = Hash::make('password123');

        // 1. ADMIN USER
        $adminUser = User::firstOrCreate(
            ['username' => 'admin'],
            [
                'name' => 'Super Administrator',
                'email' => 'admin@smk.test',
                'password' => $defaultPassword,
                'is_active' => true,
            ]
        );
        $adminUser->roles()->syncWithoutDetaching([$adminRole->id]);

        // 2. GURU AGUS
        $guruAgus = User::firstOrCreate(
            ['username' => 'guru.agus'],
            [
                'name' => 'Agus Prasetyo, S.Kom., M.T.',
                'email' => 'agus@smk.test',
                'password' => $defaultPassword,
                'is_active' => true,
            ]
        );
        $guruAgus->roles()->syncWithoutDetaching([$teacherRole->id]);

        $teacherAgus = TeacherProfile::firstOrCreate(
            ['user_id' => $guruAgus->id],
            [
                'nip' => '198001012005011001',
                'full_name' => 'Agus Prasetyo, S.Kom., M.T.',
                'gender' => 'MALE',
                'phone' => '081234567890',
                'status' => 'ACTIVE',
            ]
        );

        TeacherGradeSetting::firstOrCreate(
            ['teacher_id' => $teacherAgus->id],
            [
                'default_late_enabled' => true,
                'default_reduction_type' => 'PERCENTAGE',
                'default_reduction_value' => 5.00,
                'default_interval' => 60,
                'default_grace_minutes' => 15,
                'default_min_max_score' => 50.00,
            ]
        );

        // 3. GURU BUDI
        $guruBudi = User::firstOrCreate(
            ['username' => 'guru.budi'],
            [
                'name' => 'Budi Santoso, S.Pd.',
                'email' => 'budi@smk.test',
                'password' => $defaultPassword,
                'is_active' => true,
            ]
        );
        $guruBudi->roles()->syncWithoutDetaching([$teacherRole->id]);

        $teacherBudi = TeacherProfile::firstOrCreate(
            ['user_id' => $guruBudi->id],
            [
                'nip' => '198202022006021002',
                'full_name' => 'Budi Santoso, S.Pd.',
                'gender' => 'MALE',
                'phone' => '081234567891',
                'status' => 'ACTIVE',
            ]
        );

        TeacherGradeSetting::firstOrCreate(
            ['teacher_id' => $teacherBudi->id],
            [
                'default_late_enabled' => true,
                'default_reduction_type' => 'FIXED_POINTS',
                'default_reduction_value' => 10.00,
                'default_interval' => 60,
                'default_grace_minutes' => 15,
                'default_min_max_score' => 60.00,
            ]
        );

        // 4. KEPALA SEKOLAH (Guru Non-Mengajar) - Sukidi, S.Pd., M.Pd.
        $guruSukidi = User::firstOrCreate(
            ['username' => 'kepsek.sukidi'],
            [
                'name' => 'Sukidi, S.Pd., M.Pd.',
                'email' => 'sukidi@smk.test',
                'password' => $defaultPassword,
                'is_active' => true,
            ]
        );
        $guruSukidi->roles()->syncWithoutDetaching([$teacherRole->id]);

        $teacherSukidi = TeacherProfile::firstOrCreate(
            ['user_id' => $guruSukidi->id],
            [
                'nip' => '19700310 199702 1 004',
                'full_name' => 'Sukidi, S.Pd., M.Pd.',
                'gender' => 'MALE',
                'phone' => '0271-494549',
                'status' => 'ACTIVE',
            ]
        );

        TeacherGradeSetting::firstOrCreate(
            ['teacher_id' => $teacherSukidi->id],
            [
                'default_late_enabled' => true,
                'default_reduction_type' => 'FIXED_POINTS',
                'default_reduction_value' => 10.00,
                'default_interval' => 60,
                'default_grace_minutes' => 15,
                'default_min_max_score' => 60.00,
            ]
        );

        // 5. GURU BK DEWI
        $bkDewi = User::firstOrCreate(
            ['username' => 'bk.dewi'],
            [
                'name' => 'Dewi Lestari, S.Psi.',
                'email' => 'dewi@smk.test',
                'password' => $defaultPassword,
                'is_active' => true,
            ]
        );
        $bkDewi->roles()->syncWithoutDetaching([$counselorRole->id]);

        StaffProfile::firstOrCreate(
            ['user_id' => $bkDewi->id],
            [
                'employee_number' => '198505052010012003',
                'full_name' => 'Dewi Lestari, S.Psi.',
                'phone' => '081234567892',
            ]
        );

        // 5. SISWA AHMAD
        $siswaAhmad = User::firstOrCreate(
            ['username' => 'siswa.ahmad'],
            [
                'name' => 'Ahmad Fajar',
                'email' => 'ahmad@smk.test',
                'password' => $defaultPassword,
                'is_active' => true,
            ]
        );
        $siswaAhmad->roles()->syncWithoutDetaching([$studentRole->id]);

        StudentProfile::firstOrCreate(
            ['user_id' => $siswaAhmad->id],
            [
                'nis' => '10001',
                'nisn' => '0071234561',
                'full_name' => 'Ahmad Fajar',
                'gender' => 'MALE',
                'birth_place' => 'Jakarta',
                'birth_date' => '2008-05-10',
                'phone' => '085712345671',
                'address' => 'Jl. Merdeka No. 10',
                'entry_date' => '2024-07-15',
                'status' => 'ACTIVE',
            ]
        );

        // 6. SISWA SITI
        $siswaSiti = User::firstOrCreate(
            ['username' => 'siswa.siti'],
            [
                'name' => 'Siti Nurhaliza',
                'email' => 'siti@smk.test',
                'password' => $defaultPassword,
                'is_active' => true,
            ]
        );
        $siswaSiti->roles()->syncWithoutDetaching([$studentRole->id]);

        StudentProfile::firstOrCreate(
            ['user_id' => $siswaSiti->id],
            [
                'nis' => '10002',
                'nisn' => '0071234562',
                'full_name' => 'Siti Nurhaliza',
                'gender' => 'FEMALE',
                'birth_place' => 'Bandung',
                'birth_date' => '2008-08-15',
                'phone' => '085712345672',
                'address' => 'Jl. Anggrek No. 25',
                'entry_date' => '2024-07-15',
                'status' => 'ACTIVE',
            ]
        );

        // 7. SISWA RIZKY
        $siswaRizky = User::firstOrCreate(
            ['username' => 'siswa.rizky'],
            [
                'name' => 'Rizky Pratama',
                'email' => 'rizky@smk.test',
                'password' => $defaultPassword,
                'is_active' => true,
            ]
        );
        $siswaRizky->roles()->syncWithoutDetaching([$studentRole->id]);

        StudentProfile::firstOrCreate(
            ['user_id' => $siswaRizky->id],
            [
                'nis' => '10003',
                'nisn' => '0071234563',
                'full_name' => 'Rizky Pratama',
                'gender' => 'MALE',
                'birth_place' => 'Surabaya',
                'birth_date' => '2008-12-01',
                'phone' => '085712345673',
                'address' => 'Jl. Pahlawan No. 44',
                'entry_date' => '2024-07-15',
                'status' => 'ACTIVE',
            ]
        );

        $this->call(TeacherAccountSeeder::class);
    }
}
