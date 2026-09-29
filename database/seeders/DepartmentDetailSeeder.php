<?php

namespace Database\Seeders;

use App\Models\Department;
use Illuminate\Database\Seeder;

class DepartmentDetailSeeder extends Seeder
{
    /**
     * Kompetensi keahlian, facilities, and productive subjects shown on the public
     * department pages.
     *
     * Keyed by `departments.code` so the content stays attached to the program
     * it describes even when a department is renamed. Programs without an entry
     * simply render the empty state on the public page.
     *
     * `subjects[].code` is globally unique in the `subjects` table, so every
     * department-scoped code is prefixed with the department code.
     *
     * @var array<string, array{competencies: list<array{title: string, description: string}>, facilities: list<array{name: string, description: string}>, subjects: list<array{code: string, name: string}>}>
     */
    private const DETAILS = [
        'RPL' => [
            'competencies' => [
                ['title' => 'Pemrograman Web dan Framework', 'description' => 'Membangun aplikasi web dinamis dengan HTML, CSS, JavaScript, Laravel, dan CodeIgniter.'],
                ['title' => 'Mobile Development', 'description' => 'Mengembangkan aplikasi Android dan lintas platform menggunakan Kotlin serta Flutter.'],
                ['title' => 'Basis Data dan Backend', 'description' => 'Merancang basis data relasional, REST API, dan arsitektur berlapis yang aman.'],
                ['title' => 'Jaringan dan Infrastruktur', 'description' => 'Konfigurasi jaringan komputer, server, cloud, dan keamanan sistem informasi.'],
                ['title' => 'Desain Antarmuka dan UX', 'description' => 'Menyusun wireframe, prototipe, dan antarmuka yang mudah digunakan sesuai prinsip UX.'],
            ],
            'facilities' => [
                ['name' => 'Lab Komputer Terpadu', 'description' => '40 unit PC dengan spesifikasi mutakhir untuk pengembangan aplikasi dan basis data.'],
                ['name' => 'Lab Jaringan dan Server', 'description' => 'Perangkat jaringan, server lokal, dan simulator cloud untuk praktik langsung.'],
                ['name' => 'Studio UI/UX', 'description' => 'Ruang kerja kolaboratif dengan perangkat desain dan papan prototipe digital.'],
            ],
            'subjects' => [
                ['code' => 'PBO', 'name' => 'Pemrograman Berorientasi Objek'],
                ['code' => 'PWPB', 'name' => 'Pemrograman Web dan Perangkat Bergerak'],
                ['code' => 'BD', 'name' => 'Basis Data'],
                ['code' => 'RPL-DASAR-JARINGAN', 'name' => 'Dasar-Dasar Jaringan Komputer'],
                ['code' => 'RPL-DESAIN-UIUX', 'name' => 'Desain Antarmuka dan Pengalaman Pengguna'],
                ['code' => 'RPL-KEAMANAN-SISTEM', 'name' => 'Keamanan Sistem Informasi'],
            ],
        ],
        'TKR' => [
            'competencies' => [
                ['title' => 'Mesin Bensin', 'description' => 'Perawatan mesin bensin empat silinder, sistem bahan bakar, dan sistem pendinginan.'],
                ['title' => 'Mesin Diesel', 'description' => 'Servis mesin diesel common rail termasuk sistem injeksi dan turbocharger.'],
                ['title' => 'Kelistrikan Bodi', 'description' => 'Pemasangan sistem kelistrikan bodi, panel instrumen, dan kelistrikan modern.'],
                ['title' => 'Sistem EFI dan ECU', 'description' => 'Diagnosis komputer kendaraan menggunakan scanner dan penyetelan ECU dasar.'],
                ['title' => 'Sistem Rem dan Suspensi', 'description' => 'Perbaikan sistem rem, suspensi, dan penyetelan roda sesuai standar bengkel resmi.'],
            ],
            'facilities' => [
                ['name' => 'Bengkel Otomotif', 'description' => 'Empat bay servis dengan lift hidrolik dan alat ukur presisi.'],
                ['name' => 'Lab Komputer Kendaraan', 'description' => 'Scanner ECU, osiloskop, dan perangkat diagnosis kode kerusakan digital.'],
                ['name' => 'Ruang Praktik Mandiri', 'description' => 'Ruang khusus dan perangkat terbuka yang dapat digunakan di luar jam pelajaran.'],
            ],
            'subjects' => [
                ['code' => 'TKR-MESIN-BENSIN', 'name' => 'Teknik Mesin Bensin'],
                ['code' => 'TKR-MESIN-DIESEL', 'name' => 'Teknik Mesin Diesel'],
                ['code' => 'TKR-ELEKTRIKAN-BODI', 'name' => 'Kelistrikan Bodi'],
                ['code' => 'TKR-SISTEM-EFI', 'name' => 'Sistem EFI dan ECU'],
                ['code' => 'TKR-REM-SUSPENSI', 'name' => 'Sistem Rem dan Suspensi'],
            ],
        ],
        'DKV' => [
            'competencies' => [
                ['title' => 'Desain Grafis dan Tipografi', 'description' => 'Merancang layout brosur, poster, dan identitas visual dengan tipografi yang tepat.'],
                ['title' => 'Desain Vektor dan Cetak', 'description' => 'Membuat ilustrasi vektor dan menyiapkan berkas siap cetak sesuai spesifikasi.'],
                ['title' => 'Animasi dan Video Editing', 'description' => 'Menyusun motion graphic dan mengedit video untuk kebutuhan promosi sekolah.'],
                ['title' => 'Fotografi dan Videografi', 'description' => 'Menguasai teknik pencahayaan, sudut ambil, dan produksi konten digital.'],
                ['title' => 'UI/UX dan Produk Digital', 'description' => 'Merancang tampilan dan pengalaman pengguna untuk aplikasi serta website.'],
            ],
            'facilities' => [
                ['name' => 'Studio Komputer Grafis', 'description' => 'Workstation dengan monitor terkalibrasi dan perangkat lunak desain profesional.'],
                ['name' => 'Ruang Foto dan Video', 'description' => 'Backdrop, lighting studio, dan kamera untuk praktik produksi konten.'],
                ['name' => 'Studio Audio', 'description' => 'Perangkat rekaman untuk praktik produksi audio dan animasi.'],
            ],
            'subjects' => [
                ['code' => 'DKV-DESAIN-GRAFIS', 'name' => 'Desain Grafis dan Tipografi'],
                ['code' => 'DKV-DESAIN-VEKTOR', 'name' => 'Desain Vektor dan Preparasi Cetak'],
                ['code' => 'DKV-ANIMASI-VIDEO', 'name' => 'Animasi dan Video Editing'],
                ['code' => 'DKV-FOTOGRAFI-VIDEO', 'name' => 'Fotografi dan Videografi'],
                ['code' => 'DKV-UIUX-DIGITAL', 'name' => 'UI/UX dan Produk Digital'],
            ],
        ],
        'TPM' => [
            'competencies' => [
                ['title' => 'Bubutan dan Fabrikasi', 'description' => 'Mengerjakan pembubutan, frais, dan gerinda sesuai gambar teknik.'],
                ['title' => 'Pengelasan dan Brazing', 'description' => 'Teknik pengelasan SMAW, GMAW, TIG, dan brazing paduan logam.'],
                ['title' => 'Pemesinan CNC', 'description' => 'Pengoperasian mesin bubut CNC dan frais CNC dengan kontrol numerik.'],
                ['title' => 'Desain dan Simulasi CAD', 'description' => 'Membuat model tiga dimensi dan simulasi proses manufaktur berbasis AutoCAD dan SolidWorks.'],
                ['title' => 'Metrologi dan K3', 'description' => 'Pengukuran dimensi, toleransi, serta penerapan prosedur kesehatan dan keselamatan kerja.'],
            ],
            'facilities' => [
                ['name' => 'Lab Pemesinan Konvensional', 'description' => 'Mesin bubut, frais, bor, dan gerinda dengan kondisi terawat.'],
                ['name' => 'Lab CNC', 'description' => 'Mesin bubut CNC dua sumbu, frais CNC tiga sumbu, dan panel kontrol.'],
                ['name' => 'Lab Pengelasan', 'description' => 'Mesin las TIG, MIG, dan SMAW lengkap dengan ventilasi dan alat pelindung diri.'],
            ],
            'subjects' => [
                ['code' => 'TPM-BUB-FABRIKASI', 'name' => 'Bubutan dan Fabrikasi'],
                ['code' => 'TPM-PENGELASAN', 'name' => 'Pengelasan dan Brazing'],
                ['code' => 'TPM-PEMESINAN-CNC', 'name' => 'Pemesinan CNC'],
                ['code' => 'TPM-DESAIN-CAD', 'name' => 'Desain dan Simulasi CAD'],
                ['code' => 'TPM-METROLOGI-K3', 'name' => 'Metrologi dan K3'],
            ],
        ],
        'TPK' => [
            'competencies' => [
                ['title' => 'Persiapan Mesin dan Bahan Baku', 'description' => 'Menyiapkan mesin rajut, mesin tenun, serta benang sesuai jenis kain.'],
                ['title' => 'Warna dan Rajut', 'description' => 'Melakukan proses pewarnaan, pencampuran cat, dan pengaturan motif rajut.'],
                ['title' => 'Tenun dan Rajut', 'description' => 'Mengoperasikan mesin tenun dan rajut untuk kain dalam jumlah besar.'],
                ['title' => 'Jahit dan Finishing', 'description' => 'Pemasahan, jahit, dan finishing produk kain hingga siap kirim.'],
                ['title' => 'Mutu Kain dan Dyeing', 'description' => 'Pemeriksaan kerapatan, kelenturan, dan ketahanan warna kain.'],
            ],
            'facilities' => [
                ['name' => 'Lab Tenun dan Rajut', 'description' => 'Mesin tenun manual dan rajut industri dengan jumlah mesin terbatas.'],
                ['name' => 'Lab Warna', 'description' => 'Pencampuran cat, pengeringan, dan kendali mutu warna.'],
                ['name' => 'Lab Jahit dan Finishing', 'description' => 'Mesin jahit profesional, overlock, dan setrika uap.'],
            ],
            'subjects' => [
                ['code' => 'TPK-PERSIAPAN-MESIN', 'name' => 'Persiapan Mesin dan Bahan Baku'],
                ['code' => 'TPK-WARNA-RAJUT', 'name' => 'Warna dan Rajut'],
                ['code' => 'TPK-TENUN-RAJUT', 'name' => 'Tenun dan Rajut'],
                ['code' => 'TPK-JAHIT-FINISHING', 'name' => 'Jahit dan Finishing'],
                ['code' => 'TPK-MUTU-KAIN', 'name' => 'Mutu Kain dan Dyeing'],
            ],
        ],
    ];

    public function run(): void
    {
        foreach (self::DETAILS as $code => $detail) {
            $department = Department::where('code', $code)->first();

            if ($department === null) {
                continue;
            }

            foreach ($detail['competencies'] as $index => $competency) {
                $department->competencies()->firstOrCreate(
                    ['title' => $competency['title']],
                    ['description' => $competency['description'], 'sort_order' => $index + 1],
                );
            }

            foreach ($detail['facilities'] as $index => $facility) {
                $department->facilities()->firstOrCreate(
                    ['name' => $facility['name']],
                    ['description' => $facility['description'], 'sort_order' => $index + 1],
                );
            }

            foreach ($detail['subjects'] as $subject) {
                $department->subjects()->firstOrCreate(
                    ['code' => $subject['code']],
                    ['name' => $subject['name'], 'category' => 'MUATAN_KEJURUAN'],
                );
            }
        }
    }
}
