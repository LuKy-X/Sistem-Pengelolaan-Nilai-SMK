<?php

namespace App\Observers;

use App\Enums\ExitPermitStatus;
use App\Models\ExitPermit;
use App\Notifications\ExitPermitDecided;

/**
 * Mengirim notifikasi ke siswa saat status izin keluar berubah karena diproses
 * Guru BK: disetujui, ditolak, atau ditandai sudah kembali.
 *
 * Observer dippasang pada model sehingga modul BK tidak perlu diubah untuk
 * fitur notifikasi siswa.
 */
class ExitPermitObserver
{
    /**
     * Status yang layak diberitahukan ke siswa. PENDING dan CANCELLED sengaja
     * tidak karena keduanya belum berarti apa pun bagi siswa.
     *
     * @var list<ExitPermitStatus>
     */
    private const NOTIFIABLE_STATUSES = [
        ExitPermitStatus::Approved,
        ExitPermitStatus::Rejected,
        ExitPermitStatus::Completed,
        ExitPermitStatus::Late,
    ];

    public function updated(ExitPermit $permit): void
    {
        if (! $permit->wasChanged('status')) {
            return;
        }

        if (! in_array($permit->status, self::NOTIFIABLE_STATUSES, true)) {
            return;
        }

        $user = $permit->student?->user;

        if ($user === null) {
            return;
        }

        $permit->loadMissing(['exitPeriod', 'returnPeriod']);

        $user->notify(new ExitPermitDecided($permit->getKey()));
    }
}
