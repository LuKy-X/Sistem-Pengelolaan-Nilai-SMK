<?php

namespace Database\Seeders;

use App\Enums\DisciplinaryLetterType;
use App\Models\AcademicYear;
use App\Models\DisciplinaryLetter;
use App\Models\DisciplineCategory;
use App\Models\DisciplineRecord;
use App\Models\DisciplineSetting;
use App\Models\StaffProfile;
use App\Models\StudentProfile;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

/**
 * Seeder data poin disiplin dan surat peringatan untuk fitur BK.
 *
 * Skenario yang dicakup:
 *  - Siswa bersih (hanya pelanggaran kecil)
 *  - Siswa punya prestasi (poin positif signifikan)
 *  - Siswa mendekati batas SP-1 (poin 55–70)
 *  - Siswa sudah menerima SP-1 (poin 30–49)
 *  - Siswa sudah menerima SP-2 (poin 10–29)
 *  - Siswa dengan campuran pelanggaran dan prestasi
 */
class BkDisciplineSeeder extends Seeder
{
    public function run(): void
    {
        $year = AcademicYear::where('is_active', true)->first() ?? AcademicYear::first();

        if (! $year) {
            $this->command->error('Belum ada tahun ajaran. Jalankan AcademicYearSeeder terlebih dahulu.');

            return;
        }

        $bkUser = User::where('username', 'bk.dewi')->first();
        $bkUser2 = User::where('username', 'bk.rudi')->first();
        $staff = StaffProfile::where('user_id', $bkUser?->id)->first();

        $cat = DisciplineCategory::all()->keyBy('name');

        // Helper: ambil id kategori aman
        $cid = fn (string $name): ?int => $cat->get($name)?->id;

        // Ambil semua siswa berdasar NIS
        $s = fn (string $nis): ?StudentProfile => StudentProfile::where('nis', $nis)->first();

        // Pastikan DisciplineSetting tersedia
        DisciplineSetting::firstOrCreate(
            ['academic_year_id' => $year->id],
            [
                'initial_points' => 100,
                'minimum_points' => 0,
                'warning_threshold' => 75,
                'sp1_threshold' => 50,
                'sp2_threshold' => 30,
                'sp3_threshold' => 10,
            ]
        );

        // ─────────────────────────────────────────────────────────────────────
        // SISWA LAMA (Ahmad, Siti, Rizky) — sudah dihandle SampleBkDataSeeder,
        // tidak di-seed ulang untuk menghindari duplikasi.
        // ─────────────────────────────────────────────────────────────────────

        // ─── Faturrahman (NIS 10004) — mendekati SP1, poin sisa ~52 ─────────
        $fatur = $s('10004');

        if ($fatur) {
            $this->createRecords($year->id, $bkUser?->id, [
                [$fatur->id, $cid('Terlambat Masuk Sekolah (< 15 menit)'), -5, now()->subDays(80), 'Terlambat saat upacara bendera', 'MANUAL'],
                [$fatur->id, $cid('Terlambat Masuk Sekolah (< 15 menit)'), -5, now()->subDays(65), 'Terlambat masuk pagi', 'MANUAL'],
                [$fatur->id, $cid('Atribut Seragam Tidak Lengkap'), -5, now()->subDays(55), 'Tidak memakai kaos kaki putih saat upacara', 'MANUAL'],
                [$fatur->id, $cid('Terlambat Masuk Sekolah (> 15 menit)'), -10, now()->subDays(40), 'Terlambat 20 menit tanpa keterangan', 'MANUAL'],
                [$fatur->id, $cid('Atribut Seragam Tidak Lengkap'), -5, now()->subDays(20), 'Dasi tidak dipakai saat jam pertama', 'MANUAL'],
                [$fatur->id, $cid('Terlambat Masuk Sekolah (< 15 menit)'), -5, now()->subDays(10), 'Kembali terlambat setelah libur nasional', 'MANUAL'],
                [$fatur->id, $cid('Pengurus Aktif OSIS / MPK / Ekstrakurikuler'), +10, now()->subDays(35), 'Anggota aktif seksi olahraga OSIS', 'MANUAL'],
            ]);
            // Sisa poin: 100 - 5 - 5 - 5 - 10 - 5 - 5 + 10 = 75 (tepat di batas warning)
        }

        // ─── Rena (NIS 10005) — siswa berprestasi, poin tinggi ──────────────
        $rena = $s('10005');

        if ($rena) {
            $this->createRecords($year->id, $bkUser?->id, [
                [$rena->id, $cid('Terlambat Masuk Sekolah (< 15 menit)'), -5, now()->subDays(70), 'Terlambat 10 menit karena macet', 'MANUAL'],
                [$rena->id, $cid('Juara 1 Lomba Tingkat Kabupaten / Kota'), +20, now()->subDays(50), 'Juara 1 Lomba LKS Web Design Tingkat Kota', 'MANUAL'],
                [$rena->id, $cid('Juara 1/2/3 Lomba Tingkat Provinsi / Nasional'), +35, now()->subDays(30), 'Juara 2 LKS Tingkat Provinsi Jawa Tengah bidang Web Design', 'MANUAL'],
                [$rena->id, $cid('Pengurus Aktif OSIS / MPK / Ekstrakurikuler'), +10, now()->subDays(15), 'Ketua Sie. Humas OSIS periode 2025/2026', 'MANUAL'],
            ]);
            // Sisa poin: 100 - 5 + 20 + 35 + 10 = 160 (poin tinggi)
        }

        // ─── Bagas (NIS 10006) — mendapat SP-1, poin sisa ~45 ───────────────
        $bagas = $s('10006');

        if ($bagas) {
            $this->createRecords($year->id, $bkUser?->id, [
                [$bagas->id, $cid('Terlambat Masuk Sekolah (> 15 menit)'), -10, now()->subDays(90), 'Terlambat 25 menit tanpa surat', 'MANUAL'],
                [$bagas->id, $cid('Meninggalkan Kelas Tanpa Izin'), -15, now()->subDays(80), 'Keluar kelas saat pelajaran Bahasa Inggris', 'MANUAL'],
                [$bagas->id, $cid('Atribut Seragam Tidak Lengkap'), -5, now()->subDays(70), 'Tidak memakai topi saat upacara', 'MANUAL'],
                [$bagas->id, $cid('Terlambat Masuk Sekolah (> 15 menit)'), -10, now()->subDays(60), 'Terlambat lagi 20 menit', 'MANUAL'],
                [$bagas->id, $cid('Meninggalkan Kelas Tanpa Izin'), -15, now()->subDays(45), 'Keluar kelas saat ulangan harian', 'MANUAL'],
            ]);
            // Sisa poin: 100 - 10 - 15 - 5 - 10 - 15 = 45 (di bawah SP1 threshold 50)

            DisciplinaryLetter::firstOrCreate(
                ['student_id' => $bagas->id, 'type' => DisciplinaryLetterType::Sp1],
                [
                    'academic_year_id' => $year->id,
                    'reason' => 'Akumulasi pelanggaran: meninggalkan kelas tanpa izin dua kali dan terlambat berulang kali. Total pengurangan poin mencapai ambang batas SP-1.',
                    'issued_at' => now()->subDays(42)->toDateString(),
                    'issued_by' => $bkUser?->id,
                    'notes' => 'Orang tua telah dipanggil dan menandatangani SP-1. Siswa diberi bimbingan khusus.',
                    'status' => 'ACTIVE',
                ]
            );
        }

        // ─── Dinda (NIS 10007) — rekam bersih, poin normal ──────────────────
        $dinda = $s('10007');

        if ($dinda) {
            $this->createRecords($year->id, $bkUser2?->id ?? $bkUser?->id, [
                [$dinda->id, $cid('Terlambat Masuk Sekolah (< 15 menit)'), -5, now()->subDays(30), 'Terlambat 8 menit', 'MANUAL'],
                [$dinda->id, $cid('Aksi Teladan Kejujuran (Mengembalikan Barang Hilang)'), +10, now()->subDays(20), 'Menemukan dan mengembalikan dompet teman kelas XI lain', 'MANUAL'],
            ]);
        }

        // ─── Iqbal (NIS 10008) — pelanggaran berat, mendapat SP-1 & SP-2 ────
        $iqbal = $s('10008');

        if ($iqbal) {
            $this->createRecords($year->id, $bkUser?->id, [
                [$iqbal->id, $cid('Terlambat Masuk Sekolah (> 15 menit)'), -10, now()->subDays(100), 'Terlambat 30 menit', 'MANUAL'],
                [$iqbal->id, $cid('Merokok / Membawa Rokok/Vape'), -25, now()->subDays(90), 'Kedapatan membawa rokok di dalam tas', 'MANUAL'],
                [$iqbal->id, $cid('Meninggalkan Kelas Tanpa Izin'), -15, now()->subDays(80), 'Tidak hadir kembali setelah jam istirahat', 'MANUAL'],
                [$iqbal->id, $cid('Terlambat Masuk Sekolah (> 15 menit)'), -10, now()->subDays(60), 'Terlambat 25 menit', 'MANUAL'],
                [$iqbal->id, $cid('Atribut Seragam Tidak Lengkap'), -5, now()->subDays(45), 'Baju tidak dimasukkan', 'MANUAL'],
                [$iqbal->id, $cid('Terlambat Masuk Sekolah (< 15 menit)'), -5, now()->subDays(20), 'Terlambat 12 menit', 'MANUAL'],
            ]);
            // Sisa poin: 100 - 10 - 25 - 15 - 10 - 5 - 5 = 30 (tepat ambang SP-2)

            DisciplinaryLetter::firstOrCreate(
                ['student_id' => $iqbal->id, 'type' => DisciplinaryLetterType::Sp1],
                [
                    'academic_year_id' => $year->id,
                    'reason' => 'Pelanggaran membawa rokok dan akumulasi keterlambatan. Poin turun di bawah 50.',
                    'issued_at' => now()->subDays(88)->toDateString(),
                    'issued_by' => $bkUser?->id,
                    'notes' => 'Orang tua hadir. Siswa menyatakan penyesalan dan bersedia dibimbing.',
                    'status' => 'ACTIVE',
                ]
            );

            DisciplinaryLetter::firstOrCreate(
                ['student_id' => $iqbal->id, 'type' => DisciplinaryLetterType::Sp2],
                [
                    'academic_year_id' => $year->id,
                    'reason' => 'Pelanggaran berlanjut setelah SP-1. Tidak ada perubahan perilaku signifikan. Poin mencapai ambang batas SP-2.',
                    'issued_at' => now()->subDays(55)->toDateString(),
                    'issued_by' => $bkUser?->id,
                    'notes' => 'Orang tua hadir bersama wali kelas. Dibuat kesepakatan tertulis tiga pihak.',
                    'status' => 'ACTIVE',
                ]
            );
        }

        // ─── Sartika (NIS 10009) — siswa baru, belum ada catatan ─────────────
        // Sengaja tidak dibuat record — untuk menampilkan state kosong di UI.

        // ─── Aldian (NIS 10010) — kelas X, beberapa pelanggaran ringan ──────
        $aldi = $s('10010');

        if ($aldi) {
            $this->createRecords($year->id, $bkUser2?->id ?? $bkUser?->id, [
                [$aldi->id, $cid('Atribut Seragam Tidak Lengkap'), -5, now()->subDays(25), 'Tidak memakai ikat pinggang saat hari pertama masuk', 'MANUAL'],
                [$aldi->id, $cid('Terlambat Masuk Sekolah (< 15 menit)'), -5, now()->subDays(15), 'Terlambat 7 menit', 'MANUAL'],
                [$aldi->id, $cid('Pengurus Aktif OSIS / MPK / Ekstrakurikuler'), +10, now()->subDays(5), 'Terpilih sebagai anggota MPK kelas X', 'MANUAL'],
            ]);
        }

        // ─── Annisa (NIS 10011) — kelas X, prestasi awal ────────────────────
        $nisa = $s('10011');

        if ($nisa) {
            $this->createRecords($year->id, $bkUser?->id, [
                [$nisa->id, $cid('Juara 1 Lomba Tingkat Kabupaten / Kota'), +20, now()->subDays(10), 'Juara 1 Olimpiade Matematika Tingkat Kota (kelas X)', 'MANUAL'],
            ]);
        }

        // ─── Hendra (NIS 10012) — kelas X, rekam bersih ─────────────────────
        // Sengaja tidak dibuat record — data kosong untuk demo filter.

        // ─── Novita (NIS 10013) — kelas X, satu pelanggaran ─────────────────
        $vita = $s('10013');

        if ($vita) {
            $this->createRecords($year->id, $bkUser?->id, [
                [$vita->id, $cid('Atribut Seragam Tidak Lengkap'), -5, now()->subDays(8), 'Rambut tidak diikat sesuai peraturan', 'MANUAL'],
            ]);
        }

        $totalRecords = DisciplineRecord::where('academic_year_id', $year->id)
            ->whereNotIn('source_type', ['COUNSELING', 'INTERVIEW', 'HOME_VISIT', 'PARENT_MEETING'])
            ->count();

        $totalLetters = DisciplinaryLetter::where('academic_year_id', $year->id)->count();

        $this->command->info("BkDisciplineSeeder: {$totalRecords} catatan poin disiplin, {$totalLetters} surat peringatan.");
    }

    /**
     * Buat DisciplineRecord dari array ringkas.
     *
     * @param  array<int, array{int|null, int|null, int, Carbon, string, string}>  $entries
     */
    protected function createRecords(int $yearId, ?int $createdBy, array $entries): void
    {
        foreach ($entries as [$studentId, $categoryId, $pointsDelta, $occurredAt, $description, $sourceType]) {
            if ($studentId === null || $categoryId === null) {
                continue;
            }

            DisciplineRecord::create([
                'student_id' => $studentId,
                'academic_year_id' => $yearId,
                'category_id' => $categoryId,
                'points_delta' => $pointsDelta,
                'occurred_at' => $occurredAt,
                'description' => $description,
                'source_type' => $sourceType,
                'created_by' => $createdBy,
            ]);
        }
    }
}
