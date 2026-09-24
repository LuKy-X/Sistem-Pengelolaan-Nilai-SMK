<?php

namespace Database\Seeders;

use App\Models\SchoolProfile;
use Illuminate\Database\Seeder;

class SchoolProfileSeeder extends Seeder
{
    public function run(): void
    {
        SchoolProfile::firstOrCreate(
            ['school_name' => 'SMK Negeri 1 Solusi Digital'],
            [
                'npsn' => '20109988',
                'principal_name' => 'Dr. H. Bambang Sudarsono, M.Pd.',
                'address' => 'Jl. Pendidikan Kejuruan No. 123, Kota Cerdas',
                'phone' => '(021) 7891234',
                'email' => 'info@smkn1solusidigital.sch.id',
                'website' => 'https://smkn1solusidigital.sch.id',
                'description' => 'SMK Pusat Keunggulan dengan kurikulum link and match industri teknologi informasi, otomotif, dan industri kreatif.',
                'vision' => 'Menjadi pusat pendidikan vokasi unggul yang berkarakter, berdaya saing global, dan berjiwa wirausaha.',
                'mission' => '1. Menyelenggarakan pembelajaran berkualitas berbasis industri.\n2. Membangun karakter santun, disiplin, dan kompeten.\n3. Menghasilkan lulusan yang terserap di dunia kerja, berwirausaha, atau melanjutkan studi.',
                'history' => 'Didirikan pada tahun 1995 untuk menjawab kebutuhan tenaga kerja terampil di bidang teknologi dan rekayasa.',
            ]
        );
    }
}
