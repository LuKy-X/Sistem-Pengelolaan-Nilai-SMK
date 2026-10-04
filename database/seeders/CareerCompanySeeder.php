<?php

namespace Database\Seeders;

use App\Models\CareerCompany;
use Illuminate\Database\Seeder;

class CareerCompanySeeder extends Seeder
{
    public function run(): void
    {
        $companies = [
            [
                'name' => 'Batik Danar Hadi',
                'industry' => 'Batik Premium & Fashion',
                'address' => 'Surakarta',
            ],
            [
                'name' => 'Batik Kauman Solo',
                'industry' => 'Batik Tulis Tradisional & Kursus',
                'address' => 'Surakarta',
            ],
            [
                'name' => 'Batik Keris Solo',
                'industry' => 'Batik Tulis, Cap & Garment',
                'address' => 'Surakarta',
            ],
            [
                'name' => 'Bengkel Las & Fabrication Jaya',
                'industry' => 'Las Listrik, Argon & Fabrication',
                'address' => 'Karanganyar',
            ],
            [
                'name' => 'CV Jaya Mandiri Elektronika',
                'industry' => 'Elektronika & Perakitan Komponen',
                'address' => 'Karanganyar',
            ],
            [
                'name' => 'CV Mesin Jaya Abadi',
                'industry' => 'Bengkel Mesin & Fabrication Custom',
                'address' => 'Karanganyar',
            ],
            [
                'name' => 'CV Muslim Wear Indonesia',
                'industry' => 'Busana Muslim & Hijab',
                'address' => 'Karanganyar',
            ],
            [
                'name' => 'PT Astra Otoparts Tbk - Plant Solo',
                'industry' => 'Manufaktur Komponen Otomotif',
                'address' => 'Karanganyar',
            ],
            [
                'name' => 'PT Denso Indonesia - Solo Plant',
                'industry' => 'Sistem Pendingin & Elektrikal Otomotif',
                'address' => 'Boyolali',
            ],
            [
                'name' => 'PT Digital Karya Nusantara',
                'industry' => 'E-Commerce & Marketplace Development',
                'address' => 'Karanganyar',
            ],
            [
                'name' => 'PT Intimas Surya',
                'industry' => 'Alat Pertanian & Mesin Pertanian',
                'address' => 'Sukoharjo',
            ],
            [
                'name' => 'PT Jogja Digital Creative',
                'industry' => 'Software House & Digital Marketing',
                'address' => 'Surakarta',
            ],
            [
                'name' => 'PT Krakatau Steel - Unit Solo',
                'industry' => 'Pengolahan Baja & Fabrication',
                'address' => 'Boyolali',
            ],
            [
                'name' => 'PT Mitsuba Indonesia',
                'industry' => 'Starter Motor & Wiper System',
                'address' => 'Brebes',
            ],
            [
                'name' => 'PT Nasmoco Solution',
                'industry' => 'Perangkat Lunak',
                'address' => 'Surakarta',
            ],
            [
                'name' => 'PT Pindad (Persero) - Unit Solo',
                'industry' => 'Manufaktur Senjata & Mesin Presisi',
                'address' => 'Sukoharjo',
            ],
            [
                'name' => 'PT Presisi Engineering',
                'industry' => 'CNC Machining & Precision Parts',
                'address' => 'Surakarta',
            ],
            [
                'name' => 'PT Sekaruna Prima Teknologi',
                'industry' => 'Pengembangan Perangkat Lunak & IT',
                'address' => 'Surakarta',
            ],
            [
                'name' => 'PT Solo Textile Industries',
                'industry' => 'Tenun, Jahit, dan Konveksi Pakaian',
                'address' => 'Karanganyar',
            ],
            [
                'name' => 'PT Teknokrat Indonesia',
                'industry' => 'Sistem Informasi & Aplikasi Enterprise',
                'address' => 'Karanganyar',
            ],
            [
                'name' => 'Yamaha Motor Parts Manufacturing Indonesia',
                'industry' => 'Komponen Mesin Sepeda Motor',
                'address' => 'Sragen',
            ],
        ];

        foreach ($companies as $company) {
            CareerCompany::query()->updateOrCreate(
                ['name' => $company['name']],
                [
                    'industry' => $company['industry'],
                    'address' => $company['address'],
                ],
            );
        }
    }
}
