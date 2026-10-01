<?php

namespace Database\Seeders;

use App\Enums\AppealDecision;
use App\Enums\ExitPermitStatus;
use App\Models\ExitPermit;
use App\Models\ExitPermitAppeal;
use App\Models\ExitPermitReason;
use App\Models\StaffProfile;
use App\Models\StudentProfile;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Seeder izin keluar dan banding untuk fitur BK.
 *
 * Skenario yang dicakup:
 *  - Izin PENDING (menunggu persetujuan)
 *  - Izin APPROVED (sedang berlangsung, belum kembali)
 *  - Izin COMPLETED (kembali tepat waktu)
 *  - Izin LATE (terlambat kembali, belum ada banding)
 *  - Izin LATE dengan banding PENDING
 *  - Izin LATE dengan banding ACCEPTED
 *  - Izin LATE dengan banding REJECTED
 *  - Izin REJECTED (ditolak guru BK)
 *  - Izin CANCELLED (dibatalkan siswa)
 */
class BkExitPermitSeeder extends Seeder
{
    public function run(): void
    {
        $bkUser = User::where('username', 'bk.dewi')->first();
        $bkUser2 = User::where('username', 'bk.rudi')->first();
        $staff = StaffProfile::where('user_id', $bkUser?->id)->first();
        $staff2 = StaffProfile::where('user_id', $bkUser2?->id)->first();

        $reasons = ExitPermitReason::all()->keyBy('name');

        // Helper: ambil id alasan aman
        $rid = fn (string $name): ?int => $reasons->get($name)?->id;

        // Helper: ambil StudentProfile berdasar NIS
        $s = fn (string $nis): ?StudentProfile => StudentProfile::where('nis', $nis)->first();

        $sakit = $rid('Sakit / Berobat ke Faskes');
        $keluarga = $rid('Keperluan Keluarga Mendesak');
        $lomba = $rid('Tugas Dinas / Delegasi Lomba');
        $dokumen = $rid('Mengurus Dokumen Resmi / KTP');

        if (! $sakit || ! $keluarga) {
            $this->command->error('Data alasan izin belum ada. Jalankan ExitPermitReasonSeeder terlebih dahulu.');

            return;
        }

        $permits = [];

        // ─── Faturrahman (NIS 10004) ──────────────────────────────────────────
        $fatur = $s('10004');

        if ($fatur) {
            // COMPLETED — kembali tepat waktu
            $permits[] = ExitPermit::create([
                'student_id' => $fatur->id,
                'reason_id' => $sakit,
                'reason_detail' => 'Sakit maag kambuh, perlu ke puskesmas untuk mendapat obat.',
                'requested_at' => now()->subDays(45),
                'planned_exit_at' => now()->subDays(45)->setTime(9, 30),
                'planned_return_at' => now()->subDays(45)->setTime(11, 30),
                'approved_at' => now()->subDays(45)->setTime(9, 25),
                'approved_by' => $staff?->id,
                'actual_exit_at' => now()->subDays(45)->setTime(9, 35),
                'actual_return_at' => now()->subDays(45)->setTime(11, 20),
                'status' => ExitPermitStatus::Completed,
                'approval_note' => 'Diizinkan, bawa surat keterangan sakit dari dokter.',
            ]);

            // REJECTED — alasan tidak mendesak
            $permits[] = ExitPermit::create([
                'student_id' => $fatur->id,
                'reason_id' => $keluarga,
                'reason_detail' => 'Ingin menghadiri syukuran ulang tahun sepupu.',
                'requested_at' => now()->subDays(30),
                'planned_exit_at' => now()->subDays(30)->setTime(13, 0),
                'planned_return_at' => now()->subDays(30)->setTime(15, 0),
                'status' => ExitPermitStatus::Rejected,
                'rejection_note' => 'Alasan tidak termasuk kategori mendesak. Bisa dilakukan di luar jam sekolah.',
            ]);

            // PENDING — baru diajukan hari ini
            $permits[] = ExitPermit::create([
                'student_id' => $fatur->id,
                'reason_id' => $sakit,
                'reason_detail' => 'Kepala pusing sejak pagi, ingin periksa ke klinik sekolah dan jika perlu ke puskesmas.',
                'requested_at' => now()->subHours(1),
                'planned_exit_at' => now()->addMinutes(30),
                'planned_return_at' => now()->addHours(2),
                'status' => ExitPermitStatus::Pending,
            ]);
        }

        // ─── Rena (NIS 10005) ────────────────────────────────────────────────
        $rena = $s('10005');

        if ($rena) {
            // COMPLETED — delegasi lomba
            $permits[] = ExitPermit::create([
                'student_id' => $rena->id,
                'reason_id' => $lomba,
                'reason_detail' => 'Mengikuti Technical Meeting LKS Web Design Tingkat Provinsi di Semarang.',
                'requested_at' => now()->subDays(32),
                'planned_exit_at' => now()->subDays(32)->setTime(7, 0),
                'planned_return_at' => now()->subDays(32)->setTime(17, 0),
                'approved_at' => now()->subDays(32)->setTime(6, 50),
                'approved_by' => $staff?->id,
                'actual_exit_at' => now()->subDays(32)->setTime(7, 10),
                'actual_return_at' => now()->subDays(32)->setTime(16, 45),
                'status' => ExitPermitStatus::Completed,
                'approval_note' => 'Delegasi resmi, dilengkapi surat dari Dinas Pendidikan.',
            ]);

            // LATE — terlambat kembali, banding PENDING
            $latePerm = ExitPermit::create([
                'student_id' => $rena->id,
                'reason_id' => $dokumen,
                'reason_detail' => 'Mengurus perpanjangan KTP di Disdukcapil Karanganyar.',
                'requested_at' => now()->subDays(15),
                'planned_exit_at' => now()->subDays(15)->setTime(8, 0),
                'planned_return_at' => now()->subDays(15)->setTime(10, 0),
                'approved_at' => now()->subDays(15)->setTime(7, 55),
                'approved_by' => $staff?->id,
                'actual_exit_at' => now()->subDays(15)->setTime(8, 5),
                'actual_return_at' => now()->subDays(15)->setTime(11, 30),
                'status' => ExitPermitStatus::Late,
                'approval_note' => 'Diizinkan. Wajib kembali tepat pukul 10.00.',
            ]);
            $permits[] = $latePerm;

            ExitPermitAppeal::create([
                'exit_permit_id' => $latePerm->id,
                'submitted_at' => now()->subDays(14),
                'reason' => 'Antrean di Disdukcapil sangat panjang karena hari itu adalah hari terakhir masa pendaftaran. Saya sudah menginformasikan ke piket melalui WhatsApp.',
                'decision' => AppealDecision::Pending,
            ]);
        }

        // ─── Bagas (NIS 10006) ───────────────────────────────────────────────
        $bagas = $s('10006');

        if ($bagas) {
            // LATE — terlambat, banding ACCEPTED
            $latePerm2 = ExitPermit::create([
                'student_id' => $bagas->id,
                'reason_id' => $sakit,
                'reason_detail' => 'Demam tinggi, perlu ke dokter dan apotek.',
                'requested_at' => now()->subDays(50),
                'planned_exit_at' => now()->subDays(50)->setTime(10, 0),
                'planned_return_at' => now()->subDays(50)->setTime(12, 0),
                'approved_at' => now()->subDays(50)->setTime(9, 50),
                'approved_by' => $staff?->id,
                'actual_exit_at' => now()->subDays(50)->setTime(10, 10),
                'actual_return_at' => now()->subDays(50)->setTime(13, 15),
                'status' => ExitPermitStatus::Late,
                'approval_note' => 'Diizinkan dengan catatan bawa surat dokter.',
            ]);
            $permits[] = $latePerm2;

            ExitPermitAppeal::create([
                'exit_permit_id' => $latePerm2->id,
                'submitted_at' => now()->subDays(49),
                'reason' => 'Dokter praktek baru buka pukul 11.00 sehingga antrian baru selesai saat siang. Surat keterangan sakit terlampir.',
                'decision' => AppealDecision::Accepted,
                'decision_note' => 'Banding diterima. Surat keterangan dokter valid. Keterlambatan dimaklumi dan tidak dikenakan pengurangan poin.',
                'decided_by' => $staff?->id,
                'decided_at' => now()->subDays(48),
            ]);

            // COMPLETED — normal
            $permits[] = ExitPermit::create([
                'student_id' => $bagas->id,
                'reason_id' => $keluarga,
                'reason_detail' => 'Kakek meninggal dunia, perlu pulang untuk keperluan pemakaman.',
                'requested_at' => now()->subDays(35),
                'planned_exit_at' => now()->subDays(35)->setTime(8, 30),
                'planned_return_at' => now()->subDays(35)->setTime(13, 0),
                'approved_at' => now()->subDays(35)->setTime(8, 20),
                'approved_by' => $staff?->id,
                'actual_exit_at' => now()->subDays(35)->setTime(8, 35),
                'actual_return_at' => now()->subDays(35)->setTime(12, 50),
                'status' => ExitPermitStatus::Completed,
                'approval_note' => 'Diizinkan atas dasar kemanusiaan. Turut berduka.',
            ]);
        }

        // ─── Dinda (NIS 10007) ───────────────────────────────────────────────
        $dinda = $s('10007');

        if ($dinda) {
            // APPROVED — sedang keluar (belum kembali)
            $permits[] = ExitPermit::create([
                'student_id' => $dinda->id,
                'reason_id' => $sakit,
                'reason_detail' => 'Sakit kepala berat dan pusing, akan ke klinik terdekat.',
                'requested_at' => now()->subMinutes(45),
                'planned_exit_at' => now()->subMinutes(30),
                'planned_return_at' => now()->addHours(2),
                'approved_at' => now()->subMinutes(35),
                'approved_by' => $staff2?->id ?? $staff?->id,
                'actual_exit_at' => now()->subMinutes(28),
                'status' => ExitPermitStatus::Approved,
                'approval_note' => 'Diizinkan. Hubungi piket jika butuh lebih lama.',
            ]);
        }

        // ─── Iqbal (NIS 10008) ───────────────────────────────────────────────
        $iqbal = $s('10008');

        if ($iqbal) {
            // LATE — banding REJECTED
            $latePerm3 = ExitPermit::create([
                'student_id' => $iqbal->id,
                'reason_id' => $keluarga,
                'reason_detail' => 'Ibu sakit, perlu mengantar ke rumah sakit.',
                'requested_at' => now()->subDays(25),
                'planned_exit_at' => now()->subDays(25)->setTime(9, 0),
                'planned_return_at' => now()->subDays(25)->setTime(11, 0),
                'approved_at' => now()->subDays(25)->setTime(8, 50),
                'approved_by' => $staff?->id,
                'actual_exit_at' => now()->subDays(25)->setTime(9, 5),
                'actual_return_at' => now()->subDays(25)->setTime(14, 0),
                'status' => ExitPermitStatus::Late,
                'approval_note' => 'Diizinkan, harus kembali sebelum jam 11.',
            ]);
            $permits[] = $latePerm3;

            ExitPermitAppeal::create([
                'exit_permit_id' => $latePerm3->id,
                'submitted_at' => now()->subDays(24),
                'reason' => 'Proses admisi rumah sakit memakan waktu lama karena antrian BPJS.',
                'decision' => AppealDecision::Rejected,
                'decision_note' => 'Banding ditolak. Iqbal sudah memiliki riwayat keterlambatan berulang. Pengurangan poin tetap diberlakukan sesuai kebijakan.',
                'decided_by' => $staff?->id,
                'decided_at' => now()->subDays(23),
            ]);

            // CANCELLED — dibatalkan sendiri
            $permits[] = ExitPermit::create([
                'student_id' => $iqbal->id,
                'reason_id' => $dokumen,
                'reason_detail' => 'Mengurus KTP, ternyata tidak jadi karena teman bisa menggantikan.',
                'requested_at' => now()->subDays(10),
                'planned_exit_at' => now()->subDays(10)->setTime(10, 0),
                'planned_return_at' => now()->subDays(10)->setTime(12, 0),
                'status' => ExitPermitStatus::Cancelled,
            ]);
        }

        // ─── Aldian (NIS 10010) ─── PENDING baru ────────────────────────────
        $aldi = $s('10010');

        if ($aldi) {
            $permits[] = ExitPermit::create([
                'student_id' => $aldi->id,
                'reason_id' => $keluarga,
                'reason_detail' => 'Ayah mengalami kecelakaan ringan, dipanggil orang tua untuk ke rumah sakit.',
                'requested_at' => now()->subMinutes(20),
                'planned_exit_at' => now()->addMinutes(10),
                'planned_return_at' => now()->addHours(3),
                'status' => ExitPermitStatus::Pending,
            ]);
        }

        $countByStatus = ExitPermit::selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $this->command->info('BkExitPermitSeeder: '.array_sum($countByStatus->toArray()).' izin keluar dibuat.');

        foreach ($countByStatus as $status => $count) {
            $this->command->line("  - {$status}: {$count}");
        }

        $this->command->info('  - '.ExitPermitAppeal::count().' banding izin keluar dibuat.');
    }
}
