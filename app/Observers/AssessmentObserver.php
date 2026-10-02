<?php

namespace App\Observers;

use App\Enums\AssessmentStatus;
use App\Models\Assessment;
use App\Models\StudentProfile;
use App\Notifications\AssessmentPublished;
use Illuminate\Support\Facades\Notification;

/**
 * Mengirim notifikasi ke siswa ketika guru menerbitkan tugas.
 *
 * Observer dippasang pada model, bukan pada controller Guru, supaya modul Guru
 * tidak perlu diubah untuk fitur notifikasi siswa.
 */
class AssessmentObserver
{
    public function created(Assessment $assessment): void
    {
        if ($assessment->status === AssessmentStatus::Published) {
            $this->notifyClassStudents($assessment);
        }
    }

    public function updated(Assessment $assessment): void
    {
        // Hanya saat status benar-benar berubah menjadi terbit. Menyimpan ulang
        // assessment yang sudah terbit tidak boleh mengirim notifikasi lagi.
        if (! $assessment->wasChanged('status')) {
            return;
        }

        if ($assessment->status !== AssessmentStatus::Published) {
            return;
        }

        $this->notifyClassStudents($assessment);
    }

    private function notifyClassStudents(Assessment $assessment): void
    {
        $classId = $assessment->teachingAssignment?->class_id;

        if ($classId === null) {
            return;
        }

        $notifiables = StudentProfile::query()
            ->whereHas('classEnrollments', fn ($query) => $query
                ->where('class_id', $classId)
                ->where('status', 'ACTIVE'))
            ->with('user')
            ->get()
            ->pluck('user')
            ->filter()
            ->unique('id')
            ->values();

        if ($notifiables->isEmpty()) {
            return;
        }

        Notification::send($notifiables, new AssessmentPublished($assessment->getKey()));
    }
}
