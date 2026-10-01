<?php

namespace Database\Seeders;

use App\Enums\AppealDecision;
use App\Enums\DisciplinaryLetterType;
use App\Enums\ExitPermitStatus;
use App\Models\AcademicYear;
use App\Models\DisciplinaryLetter;
use App\Models\DisciplineCategory;
use App\Models\DisciplineRecord;
use App\Models\ExitPermit;
use App\Models\ExitPermitAppeal;
use App\Models\ExitPermitReason;
use App\Models\StaffProfile;
use App\Models\StudentProfile;
use App\Models\User;
use Illuminate\Database\Seeder;

class SampleBkDataSeeder extends Seeder
{
    public function run(): void
    {
        $academicYear = AcademicYear::where('is_active', true)->first()
            ?? AcademicYear::first();

        $students = StudentProfile::where('status', 'ACTIVE')->get();
        $categories = DisciplineCategory::all()->keyBy('id');
        $reasons = ExitPermitReason::all();
        $bkUser = User::where('username', 'bk.dewi')->first();
        $staff = StaffProfile::where('user_id', $bkUser?->id)->first();

        if ($students->isEmpty() || $categories->isEmpty() || $reasons->isEmpty()) {
            $this->command->warn('Data master (siswa / kategori / alasan) belum lengkap. Jalankan seeder utama dulu.');

            return;
        }

        // ─── 1. Poin Disiplin (DisciplineRecord) ─────────────────────────────
        // Kategori pelanggaran (points_delta negatif)
        $violationCategoryIds = $categories->filter(fn ($c) => str_contains($c->name, 'Terlambat')
            || str_contains($c->name, 'Atribut')
            || str_contains($c->name, 'Meninggalkan')
            || str_contains($c->name, 'Merokok')
            || str_contains($c->name, 'Perkelahian')
        )->keys()->values();

        // Kategori prestasi (points_delta positif)
        $rewardCategoryIds = $categories->filter(fn ($c) => str_contains($c->name, 'Juara')
            || str_contains($c->name, 'OSIS')
            || str_contains($c->name, 'Teladan')
        )->keys()->values();

        $disciplineEntries = [
            // Ahmad — banyak pelanggaran kecil, mendekati ambang SP1
            ['student_id' => 1, 'category_id' => 1, 'points_delta' => -5,  'occurred_at' => now()->subDays(60), 'description' => 'Terlambat 10 menit saat apel pagi', 'source_type' => 'MANUAL'],
            ['student_id' => 1, 'category_id' => 1, 'points_delta' => -5,  'occurred_at' => now()->subDays(50), 'description' => 'Terlambat masuk setelah jam istirahat', 'source_type' => 'MANUAL'],
            ['student_id' => 1, 'category_id' => 3, 'points_delta' => -3,  'occurred_at' => now()->subDays(45), 'description' => 'Tidak memakai ikat pinggang sesuai aturan', 'source_type' => 'MANUAL'],
            ['student_id' => 1, 'category_id' => 2, 'points_delta' => -10, 'occurred_at' => now()->subDays(35), 'description' => 'Terlambat 20 menit, alasan tidak jelas', 'source_type' => 'MANUAL'],
            ['student_id' => 1, 'category_id' => 4, 'points_delta' => -7,  'occurred_at' => now()->subDays(20), 'description' => 'Keluar kelas saat pelajaran Matematika tanpa izin guru', 'source_type' => 'MANUAL'],
            ['student_id' => 1, 'category_id' => 9, 'points_delta' => +5,  'occurred_at' => now()->subDays(15), 'description' => 'Aktif sebagai Ketua Seksi di OSIS', 'source_type' => 'MANUAL'],

            // Siti — satu pelanggaran berat + prestasi
            ['student_id' => 2, 'category_id' => 1, 'points_delta' => -5,  'occurred_at' => now()->subDays(55), 'description' => 'Terlambat masuk pagi hari', 'source_type' => 'MANUAL'],
            ['student_id' => 2, 'category_id' => 7, 'points_delta' => +15, 'occurred_at' => now()->subDays(40), 'description' => 'Juara 1 Lomba Desain Grafis Tingkat Kota', 'source_type' => 'MANUAL'],
            ['student_id' => 2, 'category_id' => 3, 'points_delta' => -3,  'occurred_at' => now()->subDays(10), 'description' => 'Seragam tidak rapi saat upacara', 'source_type' => 'MANUAL'],

            // Rizky — pelanggaran berat, sudah dapat SP1
            ['student_id' => 3, 'category_id' => 2, 'points_delta' => -10, 'occurred_at' => now()->subDays(70), 'description' => 'Terlambat 30 menit tanpa keterangan', 'source_type' => 'MANUAL'],
            ['student_id' => 3, 'category_id' => 5, 'points_delta' => -25, 'occurred_at' => now()->subDays(60), 'description' => 'Kedapatan membawa vape di dalam tas sekolah', 'source_type' => 'MANUAL'],
            ['student_id' => 3, 'category_id' => 4, 'points_delta' => -7,  'occurred_at' => now()->subDays(50), 'description' => 'Keluar kelas saat jam pelajaran berlangsung', 'source_type' => 'MANUAL'],
            ['student_id' => 3, 'category_id' => 2, 'points_delta' => -10, 'occurred_at' => now()->subDays(30), 'description' => 'Kembali terlambat setelah libur panjang', 'source_type' => 'MANUAL'],
            ['student_id' => 3, 'category_id' => 10, 'points_delta' => +10, 'occurred_at' => now()->subDays(15), 'description' => 'Mengembalikan dompet milik siswa kelas XI yang tertinggal', 'source_type' => 'MANUAL'],
        ];

        foreach ($disciplineEntries as $entry) {
            DisciplineRecord::create(array_merge($entry, [
                'academic_year_id' => $academicYear->id,
                'created_by' => $bkUser?->id,
            ]));
        }

        // ─── 2. Surat Peringatan (DisciplinaryLetter) ────────────────────────
        DisciplinaryLetter::create([
            'student_id' => 3,
            'academic_year_id' => $academicYear->id,
            'type' => DisciplinaryLetterType::Sp1,
            'reason' => 'Akumulasi pelanggaran tata tertib: membawa vape dan terlambat berulang kali mencapai ambang batas SP-1.',
            'issued_at' => now()->subDays(55)->toDateString(),
            'issued_by' => $bkUser?->id,
            'notes' => 'Orang tua/wali telah dihubungi dan hadir untuk penandatanganan surat.',
            'status' => 'ACTIVE',
        ]);

        // ─── 3. Izin Keluar (ExitPermit) ─────────────────────────────────────
        $reason1 = $reasons->firstWhere('name', 'Sakit / Berobat ke Faskes');
        $reason2 = $reasons->firstWhere('name', 'Keperluan Keluarga Mendesak');
        $reason3 = $reasons->firstWhere('name', 'Mengurus Dokumen Resmi / KTP');

        $exitPermits = [
            // Ahmad — sudah selesai (normal)
            [
                'student_id' => 1,
                'reason_id' => $reason1?->id,
                'reason_detail' => 'Sakit kepala dan demam, perlu ke puskesmas',
                'requested_at' => now()->subDays(40),
                'planned_exit_at' => now()->subDays(40)->setTime(10, 0),
                'planned_return_at' => now()->subDays(40)->setTime(12, 0),
                'approved_at' => now()->subDays(40)->setTime(9, 55),
                'approved_by' => $staff?->id,
                'actual_exit_at' => now()->subDays(40)->setTime(10, 5),
                'actual_return_at' => now()->subDays(40)->setTime(11, 50),
                'status' => ExitPermitStatus::Completed,
                'approval_note' => 'Diizinkan, harap langsung ke puskesmas.',
            ],
            // Ahmad — ditolak
            [
                'student_id' => 1,
                'reason_id' => $reason2?->id,
                'reason_detail' => 'Ingin menemani adik ke dokter',
                'requested_at' => now()->subDays(25),
                'planned_exit_at' => now()->subDays(25)->setTime(13, 0),
                'planned_return_at' => now()->subDays(25)->setTime(14, 30),
                'status' => ExitPermitStatus::Rejected,
                'rejection_note' => 'Alasan tidak mendesak, bisa dilakukan di luar jam sekolah.',
            ],
            // Siti — sudah selesai, pulang tepat waktu
            [
                'student_id' => 2,
                'reason_id' => $reason3?->id,
                'reason_detail' => 'Mengurus pembuatan KTP di Disdukcapil',
                'requested_at' => now()->subDays(30),
                'planned_exit_at' => now()->subDays(30)->setTime(9, 0),
                'planned_return_at' => now()->subDays(30)->setTime(11, 0),
                'approved_at' => now()->subDays(30)->setTime(8, 50),
                'approved_by' => $staff?->id,
                'actual_exit_at' => now()->subDays(30)->setTime(9, 10),
                'actual_return_at' => now()->subDays(30)->setTime(10, 55),
                'status' => ExitPermitStatus::Completed,
                'approval_note' => 'Diizinkan, bawa bukti pengurusan KTP.',
            ],
            // Rizky — terlambat kembali (LATE), sudah ada banding
            [
                'student_id' => 3,
                'reason_id' => $reason1?->id,
                'reason_detail' => 'Kontrol ulang ke dokter spesialis',
                'requested_at' => now()->subDays(20),
                'planned_exit_at' => now()->subDays(20)->setTime(10, 0),
                'planned_return_at' => now()->subDays(20)->setTime(12, 0),
                'approved_at' => now()->subDays(20)->setTime(9, 45),
                'approved_by' => $staff?->id,
                'actual_exit_at' => now()->subDays(20)->setTime(10, 15),
                'actual_return_at' => now()->subDays(20)->setTime(13, 30),
                'status' => ExitPermitStatus::Late,
                'approval_note' => 'Diizinkan, wajib kembali sebelum jam 12.',
            ],
            // Siti — masih pending
            [
                'student_id' => 2,
                'reason_id' => $reason2?->id,
                'reason_detail' => 'Ayah dirawat di rumah sakit, perlu menengok',
                'requested_at' => now()->subHours(2),
                'planned_exit_at' => now()->addHours(1),
                'planned_return_at' => now()->addHours(3),
                'status' => ExitPermitStatus::Pending,
            ],
        ];

        $createdPermits = [];
        foreach ($exitPermits as $permitData) {
            $createdPermits[] = ExitPermit::create($permitData);
        }

        // ─── 4. Banding Izin Keluar (ExitPermitAppeal) ───────────────────────
        // Banding atas izin Rizky yang terlambat (permit index 3)
        ExitPermitAppeal::create([
            'exit_permit_id' => $createdPermits[3]->id,
            'submitted_at' => now()->subDays(19),
            'reason' => 'Antrian di klinik sangat panjang karena banyak pasien, saya tidak bisa meninggalkan sebelum diperiksa dokter. Sudah menghubungi piket sekolah via telepon tapi tidak ada yang angkat.',
            'decision' => AppealDecision::Accepted,
            'decision_note' => 'Banding diterima, keterlambatan dikecualikan dari catatan poin. Bukti antrian dari klinik diterima.',
            'decided_by' => $staff?->id,
            'decided_at' => now()->subDays(18),
        ]);

        // Banding atas izin Ahmad yang ditolak (permit index 1)
        ExitPermitAppeal::create([
            'exit_permit_id' => $createdPermits[1]->id,
            'submitted_at' => now()->subDays(24),
            'reason' => 'Adik saya adalah satu-satunya yang bisa diantar karena orang tua sedang bekerja. Kondisi adik membutuhkan pendampingan.',
            'decision' => AppealDecision::Rejected,
            'decision_note' => 'Banding tetap ditolak. Alasan tidak cukup kuat untuk dikecualikan dari aturan.',
            'decided_by' => $staff?->id,
            'decided_at' => now()->subDays(23),
        ]);

        $this->command->info('Data dummy BK berhasil dibuat:');
        $this->command->info('  - '.DisciplineRecord::count().' catatan poin disiplin');
        $this->command->info('  - '.DisciplinaryLetter::count().' surat peringatan');
        $this->command->info('  - '.ExitPermit::count().' izin keluar');
        $this->command->info('  - '.ExitPermitAppeal::count().' banding izin keluar');
    }
}
