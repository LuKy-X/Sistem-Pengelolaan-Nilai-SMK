<?php

namespace App\Notifications;

use App\Enums\ExitPermitStatus;
use App\Models\ExitPermit;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

/**
 * Hasil pengajuan izin keluar sekolah: disetujui, ditolak, atau ditandai sudah
 * kembali oleh Guru BK.
 */
class ExitPermitDecided extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly int $exitPermitId,
    ) {}

    /**
     * @return list<string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $permit = ExitPermit::with(['reason', 'exitPeriod', 'returnPeriod'])->find($this->exitPermitId);

        if ($permit === null) {
            return [
                'title' => 'Pembaruan izin keluar',
                'body' => 'Status izin keluar sekolah Anda berubah.',
                'url' => route('student.exit-permits.index'),
                'icon' => 'permit',
            ];
        }

        $reason = $permit->reason?->name ?? 'Izin keluar';
        $deadline = $permit->returnPeriod !== null
            ? 'harus kembali di '.$permit->returnPeriod->displayLabel()
            : 'rencana kembali '.($permit->planned_return_at?->translatedFormat('d M Y H:i') ?? '-');

        [$title, $body] = match ($permit->status) {
            ExitPermitStatus::Approved => [
                'Izin keluar disetujui',
                $reason.' · '.$deadline,
            ],
            ExitPermitStatus::Rejected => [
                'Izin keluar ditolak',
                $reason.($permit->rejection_note !== null ? ' · '.$permit->rejection_note : ''),
            ],
            ExitPermitStatus::Late => [
                'Izin tercatat terlambat kembali',
                $reason.' · '.$deadline,
            ],
            ExitPermitStatus::Completed => [
                'Izin selesai',
                $reason.' · sudah tercatat kembali tepat waktu.',
            ],
            default => [
                'Pembaruan izin keluar',
                'Status izin keluar sekolah Anda berubah.',
            ],
        };

        return [
            'title' => $title,
            'body' => $body,
            'url' => route('student.exit-permits.show', $permit),
            'icon' => 'permit',
        ];
    }
}
