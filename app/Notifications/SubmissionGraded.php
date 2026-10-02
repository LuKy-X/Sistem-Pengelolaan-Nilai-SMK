<?php

namespace App\Notifications;

use App\Models\GradebookScore;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

/**
 * Nilai siswa untuk satu komponen penilaian sudah diisi guru.
 *
 * Notifikasi ini sengaja dipicu dari perubahan pada tabel nilai, bukan dari
 * tombol penilaian, sehingga semua jalur penilaian guru akan menghasilkannya.
 */
class SubmissionGraded extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly int $gradebookScoreId,
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
        $score = GradebookScore::with(['column.gradebook.teachingAssignment.subject'])->find($this->gradebookScoreId);

        if ($score === null) {
            return [
                'title' => 'Nilai baru masuk',
                'body' => 'Guru telah mengisi nilai untuk salah satu komponen penilaian Anda.',
                'url' => route('student.grades.index'),
                'icon' => 'grade',
            ];
        }

        $subject = $score->column?->gradebook?->teachingAssignment?->subject?->name ?? 'Mata Pelajaran';
        $columnName = $score->column?->name ?? 'Komponen nilai';

        return [
            'title' => 'Nilai '.$subject.' diperbarui',
            'body' => $columnName
                .($score->final_score !== null ? ' · nilai '.rtrim(rtrim(number_format((float) $score->final_score, 2), '0'), '.') : '')
                .($score->feedback !== null && $score->feedback !== '' ? ' · ada catatan guru' : ''),
            'url' => route('student.grades.recap'),
            'icon' => 'grade',
        ];
    }
}
