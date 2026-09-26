<?php

namespace Database\Seeders;

use App\Models\Department;
use Illuminate\Database\Seeder;

class DepartmentSeeder extends Seeder
{
    public function run(): void
    {
        $depts = [
            [
                'code' => 'RPL',
                'name' => 'Rekayasa Perangkat Lunak',
                'short_name' => 'RPL',
                'description' => 'Fokus pada pengembangan aplikasi web, mobile, database, dan cloud computing.',
                'vision' => 'Menjadi pusat keahlian software engineering berstandar industri modern.',
                'mission' => 'Membekali siswa dengan logika pemrograman, arsitektur data, dan clean code.',
                'career_prospects' => 'Junior Software Engineer, Web Developer, Mobile Developer, QA Tester.',
                'is_active' => true,
            ],
            [
                'code' => 'TKR',
                'name' => 'Teknik Kendaraan Ringan',
                'short_name' => 'TKR',
                'description' => 'Fokus pada pemeliharaan mesin otomotif modern, kelistrikan bodi, dan EFI.',
                'vision' => 'Mencetak mekanik handal berstandar bengkel resmi APM.',
                'mission' => 'Melatih keterampilan tune up, overhoul, dan diagnosis komputer kendaraan.',
                'career_prospects' => 'Mekanik Otomotif, Service Advisor, Teknisi Kelistrikan Mobil.',
                'is_active' => true,
            ],
            [
                'code' => 'DKV',
                'name' => 'Desain Komunikasi Visual',
                'short_name' => 'DKV',
                'description' => 'Fokus pada desain grafis, animasi, UI/UX, fotografi, dan videografi digital.',
                'vision' => 'Menghasilkan talenta industri kreatif visual yang inovatif.',
                'mission' => 'Mengasah kepekaan estetika, ilustrasi digital, dan komunikasi brand.',
                'career_prospects' => 'Graphic Designer, UI/UX Designer, Motion Graphic Designer, Content Creator.',
                'is_active' => true,
            ],
            [
                'code' => 'TPM',
                'name' => 'Teknik Pemesinan',
                'short_name' => 'TPM',
                'description' => 'Fokus pada mesin bubut, frais, CNC, pengelasan, dan manufaktur presisi.',
                'vision' => 'Menjadi pusat pelatihan teknik mesin yang menghasilkan pekerja terampil.',
                'mission' => 'Melatih keterampilan mesin bubut, frais, CNC, dan pengelasan.',
                'career_prospects' => 'Operator CNC, Teknisi Presisi, Welder, QC Inspection.',
                'is_active' => true,
            ],
            [
                'code' => 'TPK',
                'name' => 'Tekstil dan Percetakan',
                'description' => 'Fokus pada tenun, rajut, warna, jahit, dan percetakan kain.',
                'vision' => 'Menjadi contoh produksi kain yang berkelanjutan.',
                'mission' => 'Melatih keterampilan tenun, pewarnaan, dan jahit mesin.',
                'career_prospects' => 'Operator Tenun, Desainer Kain, Penjahit Produksi, QC Tekstil.',
                'is_active' => true,
            ],
        ];

        foreach ($depts as $dept) {
            Department::updateOrCreate(['code' => $dept['code']], $dept);
        }
    }
}
