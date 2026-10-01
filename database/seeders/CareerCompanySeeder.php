<?php

namespace Database\Seeders;

use App\Models\CareerCompany;
use Illuminate\Database\Seeder;

class CareerCompanySeeder extends Seeder
{
    public function run(): void
    {
        $companies = [
            ['name' => 'PT Karya Digital Nusantara', 'industry' => 'Teknologi Informasi'],
            ['name' => 'PT Otomotif Sejahtera', 'industry' => 'Otomotif'],
            ['name' => 'CV Tekstil Mandiri', 'industry' => 'Tekstil'],
            ['name' => 'PT Presisi Mekanik', 'industry' => 'Metal dan Manufaktur'],
            ['name' => 'PT Rizki Food Industry', 'industry' => 'Makanan dan Minuman'],
            ['name' => 'PT Kreatif Grafika', 'industry' => 'Industri Kreatif'],
            ['name' => 'PT Sumber Energi Terbarukan', 'industry' => 'Energi'],
            ['name' => 'Bank Nasional Cabang', 'industry' => 'Perbankan'],
        ];

        foreach ($companies as $company) {
            CareerCompany::firstOrCreate(
                ['name' => $company['name']],
                [
                    'industry' => $company['industry'],
                    'address' => null,
                    'phone' => null,
                    'email' => null,
                    'website' => null,
                    'logo' => null,
                ],
            );
        }
    }
}
