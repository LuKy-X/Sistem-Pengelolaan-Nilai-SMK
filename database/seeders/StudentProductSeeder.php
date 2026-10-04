<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\ProductCategory;
use App\Models\StudentProduct;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class StudentProductSeeder extends Seeder
{
    public function run(): void
    {
        $products = [
            ['code' => 'PU-006', 'category' => 'Rekayasa Perangkat Lunak', 'department' => 'RPL', 'name' => 'Website Company Profile', 'price' => 2500000, 'description' => 'Website untuk memperkenalkan perusahaan, brand, atau bisnis lokal. Desain modern, responsif di semua perangkat, dan mudah dikelola.'],
            ['code' => 'PU-005', 'category' => 'Rekayasa Perangkat Lunak', 'department' => 'RPL', 'name' => 'Aplikasi Kasir (Point of Sale)', 'price' => 3500000, 'description' => 'Aplikasi berbasis web atau desktop untuk mengelola transaksi penjualan, stok barang, dan laporan keuangan. Mendukung laporan real-time serta akses online dan offline.'],
            ['code' => 'PU-004', 'category' => 'Rekayasa Perangkat Lunak', 'department' => 'RPL', 'name' => 'Sistem Informasi Sekolah', 'price' => 5000000, 'description' => 'Platform untuk mengelola data siswa, guru, mata pelajaran, nilai, dan absensi, dilengkapi dashboard interaktif dan fitur cetak laporan otomatis.'],
            ['code' => 'PU-003', 'category' => 'Rekayasa Perangkat Lunak', 'department' => 'RPL', 'name' => 'Aplikasi Absensi QR Code', 'price' => 2000000, 'description' => 'Aplikasi absensi dengan pemindaian QR untuk mencatat kehadiran siswa atau karyawan. Dilengkapi integrasi kamera dan riwayat absensi real-time.'],
            ['code' => 'PU-001', 'category' => 'Rekayasa Perangkat Lunak', 'department' => 'RPL', 'name' => 'Aplikasi Absensi Siswa', 'price' => 1000000, 'description' => 'Aplikasi web untuk mencatat kehadiran siswa.'],
            ['code' => 'PU-010', 'category' => 'Teknik Pembuatan Kain', 'department' => 'TPK', 'name' => 'Kain Lurik Motif Surjan Sapit Urang', 'price' => 350000, 'description' => 'Kain lurik bermotif khas Surjan Sapit Urang, dibuat dengan tangan menggunakan bahan katun premium dan warna yang tahan lama.'],
            ['code' => 'PU-009', 'category' => 'Teknik Pembuatan Kain', 'department' => 'TPK', 'name' => 'Kain Ecoprint', 'price' => 150000, 'description' => 'Kain dengan motif alami dari daun dan bunga. Ramah lingkungan, setiap lembar memiliki motif unik, dan cocok untuk fashion maupun dekorasi.'],
            ['code' => 'PU-008', 'category' => 'Teknik Pembuatan Kain', 'department' => 'TPK', 'name' => 'Kain Lurik Motif Dibyo', 'price' => 250000, 'description' => 'Kain lurik dengan pengerjaan rapi dan detail halus, cocok untuk pakaian atau dekorasi, menggunakan pewarna alami yang ramah lingkungan.'],
            ['code' => 'PU-007', 'category' => 'Teknik Pembuatan Kain', 'department' => 'TPK', 'name' => 'Kain Batik Tulis', 'price' => 450000, 'description' => 'Kain batik tulis bermotif khas buatan tangan, menggunakan bahan katun premium dan warna yang tahan lama.'],
            ['code' => 'PU-014', 'category' => 'Teknik Ototronik', 'department' => 'TKR', 'name' => 'Servis Berkala & Perawatan Mesin Mobil', 'price' => 300000, 'description' => 'Layanan perawatan rutin mesin mobil yang mencakup penggantian oli, pembersihan busi dan filter udara maupun bahan bakar, tune-up, serta pengecekan cairan radiator.'],
            ['code' => 'PU-013', 'category' => 'Teknik Ototronik', 'department' => 'TKR', 'name' => 'Diagnosa dan Perbaikan ECU', 'price' => 350000, 'description' => 'Layanan pemindaian dan perbaikan unit kontrol elektronik kendaraan untuk mengatasi kode error, performa mesin yang kurang optimal, atau kerusakan modul akibat korsleting.'],
            ['code' => 'PU-012', 'category' => 'Teknik Ototronik', 'department' => 'TKR', 'name' => 'Pemasangan Sistem Kelistrikan Mobil/Motor', 'price' => 250000, 'description' => 'Layanan instalasi kabel dan komponen kelistrikan mobil maupun motor, termasuk peremajaan jalur kabel, pemasangan aksesori elektronik, dan penataan sekring.'],
            ['code' => 'PU-011', 'category' => 'Teknik Ototronik', 'department' => 'TKR', 'name' => 'Servis & Perawatan Sistem Elektronik Otomotif', 'price' => 200000, 'description' => 'Layanan pengecekan, pemeliharaan, dan perbaikan komponen elektronik kendaraan seperti sensor, indikator dashboard, dan sistem pencahayaan.'],
            ['code' => 'PU-017', 'category' => 'Teknik Pemesinan', 'department' => 'TPM', 'name' => 'Idler Body Bushing', 'price' => 350000, 'description' => 'Bushing logam silindris presisi tinggi sebagai selongsong bantalan penahan beban berat. Terbuat dari baja dengan finishing halus untuk meminimalkan gesekan dan memperpanjang usia pakai komponen.'],
            ['code' => 'PU-016', 'category' => 'Teknik Pemesinan', 'department' => 'TPM', 'name' => 'Casting Ring Eccentric S45C', 'price' => 1250000, 'description' => 'Komponen cincin eksentrik presisi dari baja karbon menengah S45C untuk spesifikasi teknis proyek HLP 25/26, dengan ketahanan aus dan kekuatan struktural untuk aplikasi industri.'],
            ['code' => 'PU-015', 'category' => 'Teknik Pemesinan', 'department' => 'TPM', 'name' => 'Stepped Shaft Collar', 'price' => 275000, 'description' => 'Komponen silinder logam presisi yang diproduksi melalui proses CNC turning. Lubang tengah dibuat berakurasi tinggi agar terpasang pas pada poros mesin.'],
            ['code' => 'PU-002', 'category' => 'Teknik Pemesinan', 'department' => 'TPM', 'name' => 'Mould & Dies untuk Industri Manufaktur', 'price' => 2500000, 'description' => 'Mould dan dies untuk kebutuhan industri manufaktur.'],
        ];

        foreach ($products as $productData) {
            $category = ProductCategory::query()->firstOrCreate(
                ['name' => $productData['category']],
                ['description' => 'Produk unggulan '.$productData['category'].'.'],
            );

            $department = Department::query()->where('code', $productData['department'])->first();
            $slug = Str::slug($productData['code'].' '.$productData['name']);
            $product = StudentProduct::query()->firstOrNew(['slug' => $slug]);
            $isNewProduct = ! $product->exists;

            $product->fill([
                'category_id' => $category->id,
                'department_id' => $department?->id,
                'name' => $productData['name'],
                'description' => $productData['description'],
                'price' => $productData['price'],
                'contact' => '082131021655',
            ]);

            if ($isNewProduct) {
                $product->fill([
                    'status' => 'AVAILABLE',
                ]);
            }

            $product->save();
        }
    }
}
