<?php

namespace Database\Seeders;

use App\Models\AcademicYear;
use App\Models\DisciplineCategory;
use App\Models\DisciplineRecord;
use App\Models\StudentProfile;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Seeder rekam jejak konseling untuk fitur BK.
 *
 * Konseling menggunakan model DisciplineRecord dengan source_type:
 *  - COUNSELING     → Konseling Individual
 *  - INTERVIEW      → Wawancara Siswa
 *  - HOME_VISIT     → Kunjungan Rumah
 *  - PARENT_MEETING → Pertemuan Orang Tua
 *
 * Points_delta = 0 (konseling tidak memengaruhi saldo poin).
 */
class BkCounselingSeeder extends Seeder
{
    public function run(): void
    {
        $year = AcademicYear::where('is_active', true)->first() ?? AcademicYear::first();

        if (! $year) {
            $this->command->error('Belum ada tahun ajaran aktif.');

            return;
        }

        $bkUser = User::where('username', 'bk.dewi')->first();
        $bkUser2 = User::where('username', 'bk.rudi')->first();

        // Gunakan kategori pelanggaran sebagai konteks konseling (wajib ada di form)
        $catTerlambat = DisciplineCategory::where('name', 'like', 'Terlambat Masuk%')->first();
        $catMeninggalkan = DisciplineCategory::where('name', 'Meninggalkan Kelas Tanpa Izin')->first();
        $catMerokok = DisciplineCategory::where('name', 'Merokok / Membawa Rokok/Vape')->first();
        $catAtribut = DisciplineCategory::where('name', 'Atribut Seragam Tidak Lengkap')->first();
        $catPerkelahian = DisciplineCategory::where('name', 'Terlibat Perkelahian / Tawuran')->first();

        $s = fn (string $nis): ?StudentProfile => StudentProfile::where('nis', $nis)->first();

        $logs = [];

        // ─── Ahmad (NIS 10001) — konseling akademik & kehadiran ──────────────
        $ahmad = $s('10001');

        if ($ahmad && $catTerlambat) {
            $logs[] = [
                'student_id' => $ahmad->id,
                'academic_year_id' => $year->id,
                'category_id' => $catTerlambat->id,
                'points_delta' => 0,
                'occurred_at' => now()->subDays(38),
                'description' => 'Konseling individual — Ahmad mengungkapkan kesulitan bangun pagi karena tidur larut. Disarankan mengatur jadwal belajar dan istirahat. Orang tua akan dihubungi jika terulang.',
                'source_type' => 'COUNSELING',
                'created_by' => $bkUser?->id,
            ];

            $logs[] = [
                'student_id' => $ahmad->id,
                'academic_year_id' => $year->id,
                'category_id' => $catTerlambat->id,
                'points_delta' => 0,
                'occurred_at' => now()->subDays(18),
                'description' => 'Wawancara tindak lanjut — Ahmad menyatakan sudah berusaha tidur lebih awal. Perlu monitoring dua minggu ke depan.',
                'source_type' => 'INTERVIEW',
                'created_by' => $bkUser?->id,
            ];
        }

        // ─── Rizky (NIS 10003) — konseling pelanggaran berat + kunjungan rumah
        $rizky = $s('10003');

        if ($rizky && $catMerokok) {
            $logs[] = [
                'student_id' => $rizky->id,
                'academic_year_id' => $year->id,
                'category_id' => $catMerokok->id,
                'points_delta' => 0,
                'occurred_at' => now()->subDays(58),
                'description' => 'Konseling individual pasca insiden vape — Rizky mengakui kebiasaan tersebut dimulai karena pengaruh lingkungan pergaulan di luar sekolah. Diberi pemahaman tentang bahaya rokok dan dampak terhadap nilai akademik.',
                'source_type' => 'COUNSELING',
                'created_by' => $bkUser?->id,
            ];

            $logs[] = [
                'student_id' => $rizky->id,
                'academic_year_id' => $year->id,
                'category_id' => $catMerokok->id,
                'points_delta' => 0,
                'occurred_at' => now()->subDays(52),
                'description' => 'Pertemuan orang tua — Wali Rizky (ibu) hadir. Dibahas riwayat pelanggaran dan strategi pengawasan di rumah. Disepakati check-in mingguan ke guru BK.',
                'source_type' => 'PARENT_MEETING',
                'created_by' => $bkUser?->id,
            ];

            $logs[] = [
                'student_id' => $rizky->id,
                'academic_year_id' => $year->id,
                'category_id' => $catMerokok->id,
                'points_delta' => 0,
                'occurred_at' => now()->subDays(40),
                'description' => 'Kunjungan rumah — Bertemu ibu Rizky di rumah. Lingkungan rumah kondusif. Orang tua berkomitmen memantau pergaulan. Rizky tampak lebih kooperatif.',
                'source_type' => 'HOME_VISIT',
                'created_by' => $bkUser?->id,
            ];
        }

        // ─── Bagas (NIS 10006) — konseling pasca SP-1 ───────────────────────
        $bagas = $s('10006');

        if ($bagas && $catMeninggalkan) {
            $logs[] = [
                'student_id' => $bagas->id,
                'academic_year_id' => $year->id,
                'category_id' => $catMeninggalkan->id,
                'points_delta' => 0,
                'occurred_at' => now()->subDays(43),
                'description' => 'Konseling individual — Bagas mengungkapkan jenuh terhadap beberapa mata pelajaran. Disepakati program monitoring mingguan dan target hadir penuh selama sebulan ke depan.',
                'source_type' => 'COUNSELING',
                'created_by' => $bkUser?->id,
            ];

            $logs[] = [
                'student_id' => $bagas->id,
                'academic_year_id' => $year->id,
                'category_id' => $catMeninggalkan->id,
                'points_delta' => 0,
                'occurred_at' => now()->subDays(28),
                'description' => 'Pertemuan orang tua tindak lanjut SP-1 — Orang tua Bagas kooperatif. Disepakati pengawasan ekstra dan komunikasi rutin antara orang tua dan wali kelas.',
                'source_type' => 'PARENT_MEETING',
                'created_by' => $bkUser?->id,
            ];
        }

        // ─── Iqbal (NIS 10008) — konseling intensif pasca SP-2 ──────────────
        $iqbal = $s('10008');

        if ($iqbal && $catMerokok) {
            $logs[] = [
                'student_id' => $iqbal->id,
                'academic_year_id' => $year->id,
                'category_id' => $catMerokok->id,
                'points_delta' => 0,
                'occurred_at' => now()->subDays(88),
                'description' => 'Konseling awal pasca insiden rokok — Iqbal cenderung defensif. Diberikan refleksi tentang konsekuensi jangka panjang terhadap kelulusan.',
                'source_type' => 'COUNSELING',
                'created_by' => $bkUser?->id,
            ];

            $logs[] = [
                'student_id' => $iqbal->id,
                'academic_year_id' => $year->id,
                'category_id' => $catMerokok->id,
                'points_delta' => 0,
                'occurred_at' => now()->subDays(75),
                'description' => 'Pertemuan orang tua pasca SP-1 — Ayah dan Ibu Iqbal hadir. Disampaikan bahwa jika pelanggaran berlanjut akan dikeluarkan dari sekolah.',
                'source_type' => 'PARENT_MEETING',
                'created_by' => $bkUser?->id,
            ];

            $logs[] = [
                'student_id' => $iqbal->id,
                'academic_year_id' => $year->id,
                'category_id' => $catMeninggalkan ?? $catMerokok,
                'points_delta' => 0,
                'occurred_at' => now()->subDays(60),
                'description' => 'Kunjungan rumah — Bertemu kedua orang tua. Situasi keluarga cukup baik. Orang tua menyatakan sudah menegur keras. Iqbal berjanji tidak akan mengulangi.',
                'source_type' => 'HOME_VISIT',
                'created_by' => $bkUser?->id,
            ];

            $logs[] = [
                'student_id' => $iqbal->id,
                'academic_year_id' => $year->id,
                'category_id' => $catMeninggalkan ?? $catMerokok,
                'points_delta' => 0,
                'occurred_at' => now()->subDays(52),
                'description' => 'Konseling individual pasca SP-2 — Iqbal mulai menunjukkan perbaikan sikap. Direncanakan konseling kelompok bersama teman-teman yang juga bermasalah kehadiran.',
                'source_type' => 'COUNSELING',
                'created_by' => $bkUser?->id,
            ];

            $logs[] = [
                'student_id' => $iqbal->id,
                'academic_year_id' => $year->id,
                'category_id' => $catMeninggalkan ?? $catMerokok,
                'points_delta' => 0,
                'occurred_at' => now()->subDays(10),
                'description' => 'Wawancara check-in mingguan — Iqbal hadir penuh dua minggu terakhir. Ada peningkatan positif. Akan terus dipantau.',
                'source_type' => 'INTERVIEW',
                'created_by' => $bkUser2?->id ?? $bkUser?->id,
            ];
        }

        // ─── Faturrahman (NIS 10004) — konseling motivasi belajar ───────────
        $fatur = $s('10004');

        if ($fatur && $catAtribut) {
            $logs[] = [
                'student_id' => $fatur->id,
                'academic_year_id' => $year->id,
                'category_id' => $catAtribut->id,
                'points_delta' => 0,
                'occurred_at' => now()->subDays(22),
                'description' => 'Konseling motivasi — Fatur mengungkapkan merasa kurang percaya diri di kelas. Diberikan teknik self-talk positif dan dianjurkan bergabung dengan ekstrakurikuler.',
                'source_type' => 'COUNSELING',
                'created_by' => $bkUser2?->id ?? $bkUser?->id,
            ];
        }

        // ─── Dinda (NIS 10007) — konseling karir/masa depan ─────────────────
        $dinda = $s('10007');

        if ($dinda && $catAtribut) {
            $logs[] = [
                'student_id' => $dinda->id,
                'academic_year_id' => $year->id,
                'category_id' => $catAtribut->id,
                'points_delta' => 0,
                'occurred_at' => now()->subDays(12),
                'description' => 'Konseling karir — Dinda belum yakin dengan pilihan jurusan kuliah. Diberikan tes minat bakat sederhana dan diskusi peluang karir di bidang teknologi informasi.',
                'source_type' => 'COUNSELING',
                'created_by' => $bkUser2?->id ?? $bkUser?->id,
            ];
        }

        foreach ($logs as $log) {
            DisciplineRecord::create($log);
        }

        $totalCounseling = DisciplineRecord::whereIn('source_type', ['COUNSELING', 'INTERVIEW', 'HOME_VISIT', 'PARENT_MEETING'])
            ->where('academic_year_id', $year->id)
            ->count();

        $this->command->info("BkCounselingSeeder: {$totalCounseling} rekam jejak konseling dibuat.");
    }
}
