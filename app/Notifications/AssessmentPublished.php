<?php

namespace App\Notifications;

use App\Models\Assessment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

/**
 * Tugas baru diterbitkan guru untuk kelas siswa.
 *
 * Pesan ini dikirim ke setiap siswa yang terdaftar pada kelas tersebut, bukan
 * hanya ke satu siswa, jadi pengirimannya memakai Notification::send() pada
 * koleksi pengguna.
 */
class AssessmentPublished extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly int $assessmentId,
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
        $assessment = Assessment::with('teachingAssignment.subject')->find($this->assessmentId);

        if ($assessment === null) {
            return [
                'title' => 'Tugas baru',
                'body' => 'Ada tugas baru yang diterbitkan untuk kelas Anda.',
                'url' => route('student.assignments.index'),
                'icon' => 'assignment',
            ];
        }

        $subject = $assessment->teachingAssignment?->subject?->name ?? 'Mata Pelajaran';

        return [
            'title' => 'Tugas baru: '.$assessment->title,
            'body' => $subject.($assessment->due_at !== null ? ' · tenggat '.$assessment->due_at->translatedFormat('d M Y H:i') : ''),
            'url' => route('student.assignments.show', $assessment),
            'icon' => 'assignment',
        ];
    }
}
