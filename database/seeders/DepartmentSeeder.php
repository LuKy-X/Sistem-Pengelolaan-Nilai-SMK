<?php

namespace Database\Seeders;

use App\Models\Department;
use Illuminate\Database\Seeder;

class DepartmentSeeder extends Seeder
{
    public function run(): void
    {
        $departments = $this->departments();

        foreach ($departments as $department) {
            Department::updateOrCreate(['code' => $department['code']], $department);
        }

        $this->removePlaceholderDepartments(array_column($departments, 'code'));
    }

    /**
     * Drop leftover scaffold rows that only repeat their own `code` and carry no
     * content at all. Such a row shows up on the public "Kompetensi Keahlian"
     * page as a card with empty competencies, facilities, and subjects.
     *
     * The guard is deliberately strict so a real department is never removed.
     *
     * @param  list<string>  $realCodes
     */
    private function removePlaceholderDepartments(array $realCodes): void
    {
        Department::query()
            ->whereNotIn('code', $realCodes)
            ->whereColumn('name', 'code')
            ->doesntHave('subjects')
            ->doesntHave('competencies')
            ->doesntHave('facilities')
            ->doesntHave('studentProducts')
            ->doesntHave('classes')
            ->get()
            ->each(fn (Department $department) => $department->delete());
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function departments(): array
    {
        return [
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
                'code' => 'TPM',
                'name' => 'Mesin',
                'short_name' => 'Mesin',
                'description' => 'Fokus pada mesin bubut, frais, CNC, pengelasan, dan manufaktur presisi.',
                'vision' => 'Menjadi pusat pelatihan teknik mesin yang menghasilkan pekerja terampil.',
                'mission' => 'Melatih keterampilan mesin bubut, frais, CNC, dan pengelasan.',
                'career_prospects' => 'Operator CNC, Teknisi Presisi, Welder, QC Inspection.',
                'is_active' => true,
            ],
            [
                'code' => 'TPK',
                'name' => 'Tekstil',
                'short_name' => 'Tekstil',
                'description' => 'Fokus pada tenun, rajut, warna, jahit, dan percetakan kain.',
                'vision' => 'Menjadi contoh produksi kain yang berkelanjutan.',
                'mission' => 'Melatih keterampilan tenun, pewarnaan, dan jahit mesin.',
                'career_prospects' => 'Operator Tenun, Desainer Kain, Penjahit Produksi, QC Tekstil.',
                'is_active' => true,
            ],
        ];
    }
}
