<?php

namespace Database\Seeders;

use App\Models\AlumniProfile;
use App\Models\AlumniStory;
use App\Models\StudentProfile;
use Illuminate\Database\Seeder;

class AlumniSeeder extends Seeder
{
    public function run(): void
    {
        $alumni = [
            [
                'nis' => '09001',
                'nisn' => '0060000001',
                'full_name' => 'Ayu Lestari',
                'gender' => 'FEMALE',
                'graduation_year' => 2025,
                'current_occupation' => 'Frontend Developer',
                'current_company' => 'PT Karya Digital Nusantara',
                'city' => 'Kota Industri',
                'is_featured' => true,
                'story' => [
                    'title' => 'Dari Kelas RPL ke Dua Layar',
                    'quote' => 'Portofolio yang saya buat di sekolah justru dipakai saat wawancara kerja.',
                    'story' => 'Ayu lulus dari program keahlian RPL dan kini bekerja sebagai frontend developer di salah satu mitra industri sekolah.',
                    'career_story' => 'Ia mulai magang pada semester lima dan langsung diteruskan menjadi kerja penuh waktu.',
                ],
            ],
            [
                'nis' => '09002',
                'nisn' => '0060000002',
                'full_name' => 'Bimo Raharjo',
                'gender' => 'MALE',
                'graduation_year' => 2024,
                'current_occupation' => 'Teknisi Otomotif',
                'current_company' => 'PT Otomotif Sejahtera',
                'city' => 'Kota Industri',
                'is_featured' => true,
                'story' => [
                    'title' => 'Juru Kunci yang Terlatih',
                    'quote' => 'Sertifikasi kompetensi membuat saya tidak perlu belajar lagi dari nol.',
                    'story' => 'Bimo bekerja sebagai teknisi servis di bengkel mitra industri sekolah.',
                    'career_story' => 'Pengalaman PKL selama empat bulan memberinya bekal untuk bekerja di bengkel mitra.',
                ],
            ],
            [
                'nis' => '09003',
                'nisn' => '0060000003',
                'full_name' => 'Citra Anindya',
                'gender' => 'FEMALE',
                'graduation_year' => 2025,
                'current_occupation' => 'Desainer Grafis',
                'current_company' => 'PT Kreatif Grafika',
                'city' => 'Kota Industri',
                'is_featured' => true,
                'story' => [
                    'title' => 'Desain yang Dimulai dari Tugas Kelas',
                    'quote' => 'Setiap tugas sekolah saya simpan sebagai bahan portofolio.',
                    'story' => 'Citra bekerja sebagai desainer grafis di perusahaan mitra sekolah.',
                    'career_story' => 'Portofolio dari sekolah membawanya lolos seleksi kerja.',
                ],
            ],
            [
                'nis' => '09004',
                'nisn' => '0060000004',
                'full_name' => 'Dimas Prakoso',
                'gender' => 'MALE',
                'graduation_year' => 2023,
                'current_occupation' => 'Operator CNC',
                'current_company' => 'PT Presisi Mekanik',
                'city' => 'Kota Industri',
                'is_featured' => false,
                'story' => [
                    'title' => 'Presisi yang Teruji',
                    'quote' => 'Teliti mengukur sama pentingnya dengan teliti mengerjakan.',
                    'story' => 'Dimas bekerja sebagai operator mesin CNC di industri manufaktur.',
                    'career_story' => 'Ia mengikuti pelatihan CNC di sekolah sebelum bekerja.',
                ],
            ],
        ];

        foreach ($alumni as $item) {
            $story = $item['story'];
            unset($item['story']);

            $student = StudentProfile::firstOrCreate(
                ['nis' => $item['nis']],
                [
                    'user_id' => null,
                    'nisn' => $item['nisn'],
                    'full_name' => $item['full_name'],
                    'gender' => $item['gender'],
                    'birth_place' => null,
                    'birth_date' => null,
                    'phone' => null,
                    'address' => null,
                    'entry_date' => $item['graduation_year'] - 3 .'-07-15',
                    'graduation_date' => $item['graduation_year'].'-06-20',
                    'status' => 'GRADUATED',
                ],
            );

            $profile = AlumniProfile::firstOrCreate(
                ['student_id' => $student->id],
                [
                    'graduation_year' => $item['graduation_year'],
                    'current_occupation' => $item['current_occupation'],
                    'current_company' => $item['current_company'],
                    'city' => $item['city'],
                    'social_link' => null,
                    'is_featured' => $item['is_featured'],
                ],
            );

            AlumniStory::firstOrCreate(
                ['alumni_profile_id' => $profile->id, 'title' => $story['title']],
                [
                    'story' => $story['story'],
                    'career_story' => $story['career_story'],
                    'quote' => $story['quote'],
                    'is_featured' => $item['is_featured'],
                ],
            );
        }
    }
}
