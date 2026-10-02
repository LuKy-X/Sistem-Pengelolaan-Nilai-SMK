<?php

namespace App\Observers;

use App\Models\GradebookScore;
use App\Notifications\SubmissionGraded;

/**
 * Mengirim notifikasi ke siswa saat guru mengisi atau memperbaiki nilainya.
 *
 * Observer dipasang pada tabel nilai, bukan pada tombol penilaian, sehingga
 * semua jalur input nilai guru tetap menghasilkan notifikasi yang sama.
 */
class GradebookScoreObserver
{
    public function created(GradebookScore $score): void
    {
        $this->notifyStudent($score);
    }

    public function updated(GradebookScore $score): void
    {
        // Hanya saat nilai benar-benar berubah. Menyimpan ulang feedback atau
        // kolom lain tidak mengirim notifikasi baru.
        if (! $score->wasChanged('final_score')) {
            return;
        }

        $this->notifyStudent($score);
    }

    private function notifyStudent(GradebookScore $score): void
    {
        if ($score->final_score === null) {
            return;
        }

        $user = $score->student?->user;

        if ($user === null) {
            return;
        }

        $user->notify(new SubmissionGraded($score->getKey()));
    }
}
