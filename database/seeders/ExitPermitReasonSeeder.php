<?php

namespace Database\Seeders;

use App\Models\ExitPermitReason;
use Illuminate\Database\Seeder;

class ExitPermitReasonSeeder extends Seeder
{
    public function run(): void
    {
        $reasons = [
            ['name' => 'Sakit / Berobat ke Faskes', 'description' => 'Siswa mendadak sakit saat KBM dan memerlukan penanganan medis di luar sekolah.'],
            ['name' => 'Keperluan Keluarga Mendesak', 'description' => 'Ada berita duka atau urusan keluarga yang darurat dan dijemput orang tua/wali.'],
            ['name' => 'Tugas Dinas / Delegasi Lomba', 'description' => 'Mewakili sekolah dalam kompetisi, pelatihan, atau kegiatan resmi dinas pendidikan.'],
            ['name' => 'Mengurus Dokumen Resmi / KTP', 'description' => 'Mengurus administrasi kependudukan atau kepolisian yang terjadwal.'],
        ];

        foreach ($reasons as $reason) {
            ExitPermitReason::firstOrCreate(['name' => $reason['name']], $reason);
        }
    }
}
