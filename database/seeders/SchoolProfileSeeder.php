<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\SchoolProfile;
use App\Models\TeacherGradeSetting;
use App\Models\TeacherProfile;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SchoolProfileSeeder extends Seeder
{
    public function run(): void
    {
        $profile = SchoolProfile::first();

        $data = [
            'school_name' => 'SMK Negeri 2 Karanganyar',
            'npsn' => '20312071',
            'principal_name' => 'Sukidi, S.Pd., M.Pd.',
            'address' => 'Jl. Laksda Yos Sudarso, Bejen, Kec. Karanganyar, Kab. Karanganyar, Jawa Tengah 57716',
            'phone' => '0271-494549',
            'email' => 'smkn2kra97@gmail.com',
            'website' => 'https://smkn2kra.sch.id',
            'description' => 'SMK Negeri 2 Karanganyar membekali siswa dengan kurikulum berbasis industri, sertifikasi kompetensi, dan jaringan kerja sama dunia usaha untuk masa depan karier yang nyata.',
            'vision' => 'Terwujudnya SMK Negeri 2 Karanganyar yang berkarakter, unggul, berprestasi, berdaya saing global, berwawasan lingkungan, dan berjiwa wirausaha.',
            'mission' => "1. Menyelenggarakan pendidikan kejuruan yang berorientasi pada kebutuhan dunia usaha dan dunia industri (DUDI).\n2. Membentuk peserta didik yang beriman, bertakwa, berakhlak mulia, dan berkarakter Profil Pelajar Pancasila.\n3. Mengembangkan kompetensi keahlian dan sertifikasi bertaraf nasional maupun internasional.\n4. Menumbuhkan jiwa kewirausahaan (entrepreneurship) dan kreativitas peserta didik.\n5. Menerapkan budaya kerja industri dan budaya peduli lingkungan hidup di sekolah.",
            'history' => 'SMK Negeri 2 Karanganyar berdiri pada tahun 1997 di atas tanah seluas 27.720 m² dan diresmikan pada 18 November 1997 oleh Menteri Pendidikan Nasional Prof. Dr. Ing. Wardiman Djojonegoro dengan satu program studi, Teknik Mesin. Sekolah pertama kali dipimpin Drs. Surip Sunamto pada Tahun Pelajaran 1997/1998 hingga 2005/2006. Program Teknologi Tekstil dibuka pada Tahun Pelajaran 2004/2005. Pada Tahun Pelajaran 2006/2007, Drs. Sugiyarso HS, S.Pd., S.H., M.Ag. ditugaskan sebagai pelaksana tugas kepala sekolah. Pada Tahun Pelajaran 2007/2008, Drs. Wahyu Widodo, M.T. ditetapkan sebagai kepala sekolah. Pada Tahun Pelajaran 2008/2009, sekolah membuka program Teknik Otomotif Elektronik dan Rekayasa Perangkat Lunak.',
        ];

        if ($profile) {
            $profile->update($data);
        } else {
            SchoolProfile::create($data);
        }

        // Kepala sekolah didaftarkan sebagai Guru (posisi non-mengajar) agar memiliki NIP untuk keperluan laporan/ekspor
        $teacherRole = Role::where('code', 'TEACHER')->first();
        if ($teacherRole && ! app()->isProduction()) {
            $userSukidi = User::firstOrCreate(
                ['username' => 'kepsek.sukidi'],
                [
                    'name' => 'Sukidi, S.Pd., M.Pd.',
                    'email' => 'sukidi@smk.test',
                    'password' => Hash::make('password123'),
                    'is_active' => true,
                ]
            );
            $userSukidi->roles()->syncWithoutDetaching([$teacherRole->id]);

            $teacherSukidi = TeacherProfile::updateOrCreate(
                ['user_id' => $userSukidi->id],
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
        }
    }
}
