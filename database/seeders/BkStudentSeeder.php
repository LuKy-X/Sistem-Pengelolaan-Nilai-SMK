<?php

namespace Database\Seeders;

use App\Models\AcademicYear;
use App\Models\ClassEnrollment;
use App\Models\Department;
use App\Models\GradeLevel;
use App\Models\Role;
use App\Models\SchoolClass;
use App\Models\StudentProfile;
use App\Models\TeacherProfile;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Seeder siswa tambahan khusus skenario BK.
 * Membuat 10 siswa di 3 kelas (X, XI, XII) dengan beragam profil
 * untuk memperkaya data demo modul disiplin, izin keluar, dan konseling.
 */
class BkStudentSeeder extends Seeder
{
    public function run(): void
    {
        $studentRole = Role::where('code', 'STUDENT')->first();
        $year = AcademicYear::where('is_active', true)->first() ?? AcademicYear::first();
        $rpl = Department::where('code', 'RPL')->first();
        $level10 = GradeLevel::where('code', 'X')->first();
        $level11 = GradeLevel::where('code', 'XI')->first();
        $level12 = GradeLevel::where('code', 'XII')->first();
        $teacherAgus = TeacherProfile::where('nip', '198001012005011001')->first();
        $defaultPassword = Hash::make('password123');

        if (! $studentRole || ! $year || ! $rpl) {
            $this->command->error('Data master (role/tahun ajaran/jurusan) belum lengkap. Jalankan seeder utama terlebih dahulu.');

            return;
        }

        // ─── Pastikan kelas X RPL 1 tersedia ─────────────────────────────────
        $class10 = null;

        if ($level10) {
            $class10 = SchoolClass::firstOrCreate(
                ['academic_year_id' => $year->id, 'code' => 'X-RPL-1'],
                [
                    'department_id' => $rpl->id,
                    'grade_level_id' => $level10->id,
                    'homeroom_teacher_id' => $teacherAgus?->id,
                    'name' => 'X Rekayasa Perangkat Lunak 1',
                    'is_active' => true,
                ]
            );
        }

        // Ambil kelas XI dan XII yang sudah dibuat SampleClassSeeder
        $class11 = $level11 ? SchoolClass::where('code', 'XI-RPL-1')->first() : null;
        $class12 = $level12 ? SchoolClass::where('code', 'XII-RPL-1')->first() : null;

        // ─── Data siswa tambahan ──────────────────────────────────────────────
        $students = [
            // Kelas XII
            [
                'user' => ['username' => 'siswa.fatur', 'name' => 'Faturrahman Hidayat', 'email' => 'fatur@smk.test'],
                'profile' => ['nis' => '10004', 'nisn' => '0071234564', 'full_name' => 'Faturrahman Hidayat',
                    'gender' => 'MALE', 'birth_place' => 'Karanganyar', 'birth_date' => '2007-03-12',
                    'phone' => '085712345674', 'address' => 'Jl. Raya Karanganyar No. 5', 'entry_date' => '2024-07-15', 'status' => 'ACTIVE'],
                'class' => $class12,
            ],
            [
                'user' => ['username' => 'siswa.rena', 'name' => 'Rena Fitriani', 'email' => 'rena@smk.test'],
                'profile' => ['nis' => '10005', 'nisn' => '0071234565', 'full_name' => 'Rena Fitriani',
                    'gender' => 'FEMALE', 'birth_place' => 'Solo', 'birth_date' => '2007-07-20',
                    'phone' => '085712345675', 'address' => 'Jl. Adi Sucipto No. 12', 'entry_date' => '2024-07-15', 'status' => 'ACTIVE'],
                'class' => $class12,
            ],
            [
                'user' => ['username' => 'siswa.bagas', 'name' => 'Bagas Kurniawan', 'email' => 'bagas@smk.test'],
                'profile' => ['nis' => '10006', 'nisn' => '0071234566', 'full_name' => 'Bagas Kurniawan',
                    'gender' => 'MALE', 'birth_place' => 'Sragen', 'birth_date' => '2007-01-05',
                    'phone' => '085712345676', 'address' => 'Jl. Diponegoro No. 88', 'entry_date' => '2024-07-15', 'status' => 'ACTIVE'],
                'class' => $class12,
            ],
            // Kelas XI
            [
                'user' => ['username' => 'siswa.dinda', 'name' => 'Dinda Permata Sari', 'email' => 'dinda@smk.test'],
                'profile' => ['nis' => '10007', 'nisn' => '0071234567', 'full_name' => 'Dinda Permata Sari',
                    'gender' => 'FEMALE', 'birth_place' => 'Wonogiri', 'birth_date' => '2008-04-17',
                    'phone' => '085712345677', 'address' => 'Desa Mojo Rt.03/02', 'entry_date' => '2025-07-14', 'status' => 'ACTIVE'],
                'class' => $class11,
            ],
            [
                'user' => ['username' => 'siswa.iqbal', 'name' => 'Iqbal Maulana', 'email' => 'iqbal@smk.test'],
                'profile' => ['nis' => '10008', 'nisn' => '0071234568', 'full_name' => 'Iqbal Maulana',
                    'gender' => 'MALE', 'birth_place' => 'Boyolali', 'birth_date' => '2008-09-30',
                    'phone' => '085712345678', 'address' => 'Jl. Pemuda No. 7', 'entry_date' => '2025-07-14', 'status' => 'ACTIVE'],
                'class' => $class11,
            ],
            [
                'user' => ['username' => 'siswa.tari', 'name' => 'Sartika Dewi', 'email' => 'tari@smk.test'],
                'profile' => ['nis' => '10009', 'nisn' => '0071234569', 'full_name' => 'Sartika Dewi',
                    'gender' => 'FEMALE', 'birth_place' => 'Klaten', 'birth_date' => '2008-11-11',
                    'phone' => '085712345679', 'address' => 'Jl. Matahari No. 3', 'entry_date' => '2025-07-14', 'status' => 'ACTIVE'],
                'class' => $class11,
            ],
            // Kelas X
            [
                'user' => ['username' => 'siswa.aldi', 'name' => 'Aldian Putra', 'email' => 'aldi@smk.test'],
                'profile' => ['nis' => '10010', 'nisn' => '0071234570', 'full_name' => 'Aldian Putra',
                    'gender' => 'MALE', 'birth_place' => 'Karanganyar', 'birth_date' => '2009-02-22',
                    'phone' => '085712345680', 'address' => 'Perum Griya Indah Blok B/5', 'entry_date' => '2026-07-14', 'status' => 'ACTIVE'],
                'class' => $class10,
            ],
            [
                'user' => ['username' => 'siswa.nisa', 'name' => 'Annisa Ramadhani', 'email' => 'nisa@smk.test'],
                'profile' => ['nis' => '10011', 'nisn' => '0071234571', 'full_name' => 'Annisa Ramadhani',
                    'gender' => 'FEMALE', 'birth_place' => 'Solo', 'birth_date' => '2009-06-08',
                    'phone' => '085712345681', 'address' => 'Jl. Teratai No. 19', 'entry_date' => '2026-07-14', 'status' => 'ACTIVE'],
                'class' => $class10,
            ],
            [
                'user' => ['username' => 'siswa.hendra', 'name' => 'Hendra Saputra', 'email' => 'hendra@smk.test'],
                'profile' => ['nis' => '10012', 'nisn' => '0071234572', 'full_name' => 'Hendra Saputra',
                    'gender' => 'MALE', 'birth_place' => 'Sukoharjo', 'birth_date' => '2009-10-03',
                    'phone' => '085712345682', 'address' => 'Jl. Kenanga No. 44', 'entry_date' => '2026-07-14', 'status' => 'ACTIVE'],
                'class' => $class10,
            ],
            [
                'user' => ['username' => 'siswa.vita', 'name' => 'Novita Anggraini', 'email' => 'vita@smk.test'],
                'profile' => ['nis' => '10013', 'nisn' => '0071234573', 'full_name' => 'Novita Anggraini',
                    'gender' => 'FEMALE', 'birth_place' => 'Karanganyar', 'birth_date' => '2009-12-25',
                    'phone' => '085712345683', 'address' => 'Jl. Mawar No. 2', 'entry_date' => '2026-07-14', 'status' => 'ACTIVE'],
                'class' => $class10,
            ],
        ];

        $created = 0;

        foreach ($students as $data) {
            $user = User::firstOrCreate(
                ['username' => $data['user']['username']],
                array_merge($data['user'], [
                    'password' => $defaultPassword,
                    'is_active' => true,
                ])
            );

            if ($studentRole) {
                $user->roles()->syncWithoutDetaching([$studentRole->id]);
            }

            $profile = StudentProfile::firstOrCreate(
                ['nis' => $data['profile']['nis']],
                array_merge($data['profile'], ['user_id' => $user->id])
            );

            if ($data['class']) {
                ClassEnrollment::firstOrCreate(
                    ['class_id' => $data['class']->id, 'student_id' => $profile->id],
                    ['start_date' => $data['profile']['entry_date'], 'status' => 'ACTIVE']
                );
            }

            $created++;
        }

        $this->command->info("BkStudentSeeder: {$created} siswa BK berhasil dibuat.");
    }
}
