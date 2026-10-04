<?php

namespace Database\Seeders;

use App\Enums\CareerOpportunityStatus;
use App\Enums\CareerOpportunityType;
use App\Models\CareerCompany;
use App\Models\CareerOpportunity;
use Illuminate\Database\Seeder;

class CareerOpportunitySeeder extends Seeder
{
    public function run(): void
    {
        $opportunities = [
            [
                'company' => 'PT Digital Karya Nusantara',
                'type' => CareerOpportunityType::Internship,
                'title' => 'Magang Backend Developer',
                'description' => 'Magang selama tiga bulan pada pengembangan REST API dan basis data.',
                'requirements' => 'Pengetahuan PHP atau Node.js,ahami Git, dan siap bekerja tim.',
                'location' => 'Karanganyar',
                'open_date' => '2026-09-01',
                'close_date' => '2026-10-15',
                'status' => CareerOpportunityStatus::Open,
            ],
            [
                'company' => 'Yamaha Motor Parts Manufacturing Indonesia',
                'type' => CareerOpportunityType::Internship,
                'title' => 'Magang Teknisi Kendaraan',
                'description' => 'Penempatan magang pada proses produksi komponen otomotif.',
                'requirements' => 'Siswa kelas XI atau XII TKR, mampu bekerja shift.',
                'location' => 'Sragen',
                'open_date' => '2026-08-25',
                'close_date' => '2026-10-10',
                'status' => CareerOpportunityStatus::Open,
            ],
            [
                'company' => 'PT Solo Textile Industries',
                'type' => CareerOpportunityType::Job,
                'title' => 'Operator Tenun dan Finishing',
                'description' => 'Lowongan kerja untuk mengoperasikan mesin tenun dan proses finishing.',
                'requirements' => 'Telaten, teliti, dan mampu bekerja dalam tim.',
                'location' => 'Karanganyar',
                'open_date' => '2026-09-12',
                'close_date' => '2026-11-01',
                'status' => CareerOpportunityStatus::Open,
            ],
            [
                'company' => 'PT Presisi Engineering',
                'type' => CareerOpportunityType::Internship,
                'title' => 'Magang Pemesinan CNC',
                'description' => 'Praktik pembubutan dan frais CNC bersama operator berpengalaman.',
                'requirements' => 'Siswa kelas XII TPM yang paham dasar pemesinan.',
                'location' => 'Surakarta',
                'open_date' => '2026-09-08',
                'close_date' => '2026-10-30',
                'status' => CareerOpportunityStatus::Open,
            ],
            [
                'company' => 'PT Jogja Digital Creative',
                'type' => CareerOpportunityType::Job,
                'title' => 'Desain Grafis Junior',
                'description' => 'Mengerjakan desain promosi dan media sosial perusahaan.',
                'requirements' => 'Menguasai Adobe Illustrator dan Photoshop.',
                'location' => 'Surakarta',
                'open_date' => '2026-08-15',
                'close_date' => '2026-09-30',
                'status' => CareerOpportunityStatus::Closed,
            ],
        ];

        foreach ($opportunities as $opportunity) {
            $company = CareerCompany::where('name', $opportunity['company'])->first();

            if ($company === null) {
                continue;
            }

            CareerOpportunity::firstOrCreate(
                ['title' => $opportunity['title'], 'company_id' => $company->id],
                [
                    'type' => $opportunity['type'],
                    'description' => $opportunity['description'],
                    'requirements' => $opportunity['requirements'],
                    'location' => $opportunity['location'],
                    'open_date' => $opportunity['open_date'],
                    'close_date' => $opportunity['close_date'],
                    'application_link' => null,
                    'status' => $opportunity['status'],
                ],
            );
        }
    }
}
