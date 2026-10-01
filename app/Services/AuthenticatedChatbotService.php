<?php

namespace App\Services;

use App\Enums\AssessmentStatus;
use App\Enums\ExitPermitStatus;
use App\Models\Assessment;
use App\Models\AssessmentSubmission;
use App\Models\ClassJournal;
use App\Models\DisciplinaryLetter;
use App\Models\DisciplineRecord;
use App\Models\ExitPermit;
use App\Models\ExitPermitAppeal;
use App\Models\Gradebook;
use App\Models\GradebookScore;
use App\Models\GradebookStudent;
use App\Models\Rubric;
use App\Models\StudentProfile;
use App\Models\TeacherProfile;
use App\Models\TeachingAssignment;
use App\Models\User;
use DateTimeInterface;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Throwable;

/**
 * Retrieval-backed chatbot for authenticated users (teacher, student, counselor).
 *
 * Every answer is scoped strictly to the authenticated user's own data.
 * - Siswa: hanya melihat nilai, tugas, izin, dan catatan disiplin miliknya sendiri.
 * - Guru: hanya melihat buku nilai, tugas, jurnal, dan rubrik yang ia buat.
 * - BK (Counselor): hanya melihat data izin, disiplin, dan konseling yang ia kelola.
 *
 * Matching uses the same keyword-scoring engine as PublicChatbotService.
 * Navigation links point directly to the relevant management page.
 */
class AuthenticatedChatbotService
{
    // =========================================================
    // SHARED INTENT TABLE
    // =========================================================

    /**
     * @var list<array{intent: string, keywords: list<string>}>
     */
    private const SHARED_INTENTS = [
        ['intent' => 'greeting', 'keywords' => ['halo', 'hai', 'hello', 'hi', 'assalamualaikum', 'selamat pagi', 'selamat siang', 'selamat sore', 'pagi', 'siang', 'sore', 'malam']],
        ['intent' => 'thanks', 'keywords' => ['terima kasih', 'makasih', 'mksh', 'thanks', 'thank you', 'thx']],
        ['intent' => 'help', 'keywords' => ['bantuan', 'bisa apa', 'fitur', 'apa saja', 'apa yang bisa', 'help', 'menu']],
    ];

    /**
     * @var list<array{intent: string, keywords: list<string>}>
     */
    private const STUDENT_INTENTS = [
        ['intent' => 'student_grades', 'keywords' => ['nilai', 'rapot', 'rapor', 'skor', 'score', 'hasil ujian', 'hasil ulangan', 'nilai akhir', 'nilai tugas', 'rekap nilai', 'buku nilai', 'gradebook']],
        ['intent' => 'student_assignments', 'keywords' => ['tugas', 'ulangan', 'kuis', 'ujian', 'deadline', 'pengumpulan', 'kumpul', 'submit', 'pr', 'pekerjaan rumah', 'assessment', 'remedial']],
        ['intent' => 'student_attendance', 'keywords' => ['absen', 'kehadiran', 'hadir', 'alpa', 'izin', 'sakit', 'presensi', 'absensi', 'tidak masuk']],
        ['intent' => 'student_exit_permit', 'keywords' => ['izin keluar', 'exit permit', 'izin meninggalkan', 'pulang duluan', 'keluar sekolah', 'surat izin', 'pergi']],
        ['intent' => 'student_discipline', 'keywords' => ['disiplin', 'poin', 'pelanggaran', 'catatan', 'surat peringatan', 'sp', 'sanksi', 'teguran']],
        ['intent' => 'student_schedule', 'keywords' => ['jadwal', 'pelajaran', 'mata pelajaran', 'hari ini', 'besok', 'minggu ini', 'jam', 'kelas', 'mapel']],
        ['intent' => 'student_profile', 'keywords' => ['profil', 'biodata', 'data diri', 'nis', 'nisn', 'kelas saya', 'jurusan saya']],
    ];

    /**
     * @var list<array{intent: string, keywords: list<string>}>
     */
    private const TEACHER_INTENTS = [
        ['intent' => 'teacher_gradebooks', 'keywords' => ['buku nilai', 'gradebook', 'rekap nilai', 'nilai siswa', 'daftar nilai', 'input nilai', 'nilai kelas', 'penilaian']],
        ['intent' => 'teacher_assessments', 'keywords' => ['tugas', 'ulangan', 'kuis', 'ujian', 'assessment', 'soal', 'remedial', 'remidi', 'buat tugas', 'publikasi tugas']],
        ['intent' => 'teacher_submissions', 'keywords' => ['pengumpulan', 'submit siswa', 'kumpulan tugas', 'jawaban siswa', 'yang belum mengumpulkan', 'belum kumpul', 'sudah kumpul']],
        ['intent' => 'teacher_journals', 'keywords' => ['jurnal', 'jurnal kelas', 'absen mengajar', 'presensi mengajar', 'materi ajar', 'kehadiran siswa', 'jurnal mengajar']],
        ['intent' => 'teacher_rubrics', 'keywords' => ['rubrik', 'rubric', 'kriteria penilaian', 'acuan nilai', 'pedoman penilaian']],
        ['intent' => 'teacher_classes', 'keywords' => ['kelas', 'rombel', 'mengajar', 'jadwal mengajar', 'penugasan', 'assignment', 'mata pelajaran saya', 'mapel saya']],
        ['intent' => 'teacher_profile', 'keywords' => ['profil', 'biodata', 'data saya', 'nip', 'akun']],
    ];

    /**
     * @var list<array{intent: string, keywords: list<string>}>
     */
    private const COUNSELOR_INTENTS = [
        ['intent' => 'counselor_exit_permits', 'keywords' => ['izin keluar', 'exit permit', 'izin siswa', 'surat izin', 'yang keluar', 'keluar sekolah', 'terlambat kembali', 'overdue', 'banding']],
        ['intent' => 'counselor_discipline', 'keywords' => ['disiplin', 'poin', 'pelanggaran', 'catatan disiplin', 'rekap pelanggaran', 'rekap disiplin', 'poin siswa']],
        ['intent' => 'counselor_letters', 'keywords' => ['surat peringatan', 'sp', 'sp1', 'sp2', 'sp3', 'surat teguran', 'surat pembinaan', 'surat', 'disciplinary']],
        ['intent' => 'counselor_students', 'keywords' => ['siswa', 'data siswa', 'rekap siswa', 'profil siswa', 'cari siswa', 'daftar siswa']],
        ['intent' => 'counselor_counseling', 'keywords' => ['konseling', 'bimbingan', 'catatan konseling', 'sesi konseling', 'rekam konseling', 'rekap konseling']],
        ['intent' => 'counselor_appeals', 'keywords' => ['banding', 'appeal', 'ajuan banding', 'protes', 'keberatan']],
        ['intent' => 'counselor_profile', 'keywords' => ['profil', 'biodata', 'data saya', 'akun saya']],
    ];

    /**
     * @var list<string>
     */
    private const DETAIL_MARKERS = [
        'detail', 'lengkap', 'semua', 'rinci', 'info', 'terbaru', 'berapa', 'nama',
        'daftar', 'list', 'rekap', 'hari ini', 'minggu ini', 'bulan ini', 'terakhir',
        'belum', 'sudah', 'lagi', 'mana saja', 'siapa saja',
    ];

    /**
     * @var array<string, mixed>
     */
    private array $memo = [];

    // =========================================================
    // PUBLIC API
    // =========================================================

    /**
     * Opening message personalised to the authenticated user's role.
     *
     * @return array{reply: string, suggestions: list<string>}
     */
    public function opening(User $user): array
    {
        $name = $this->firstName($user->name);

        if ($user->isTeacher()) {
            return [
                'reply' => "Halo, {$name}! Saya asisten AI khusus untuk portal guru.\n"
                    .'Saya bisa bantu soal buku nilai, tugas siswa, jurnal kelas, dan rubrik penilaian milikmu.',
                'suggestions' => ['Buku nilai saya', 'Tugas yang belum dinilai', 'Jurnal kelas terbaru'],
            ];
        }

        if ($user->isStudent()) {
            return [
                'reply' => "Halo, {$name}! Saya asisten AI khusus untuk portal siswa.\n"
                    .'Saya bisa bantu soal nilai, tugas, izin keluar, dan catatan disiplinmu.',
                'suggestions' => ['Nilai saya', 'Tugas yang belum dikumpulkan', 'Cek poin disiplin saya'],
            ];
        }

        if ($user->isCounselor()) {
            return [
                'reply' => "Halo, {$name}! Saya asisten AI khusus untuk BK.\n"
                    .'Saya bisa bantu soal izin keluar siswa, catatan disiplin, surat peringatan, dan rekam konseling.',
                'suggestions' => ['Izin keluar pending', 'Siswa poin disiplin tertinggi', 'Rekap konseling hari ini'],
            ];
        }

        return [
            'reply' => "Halo, {$name}! Saya asisten AI. Ada yang bisa saya bantu?",
            'suggestions' => [],
        ];
    }

    /**
     * Answer an authenticated user question using their own data only.
     *
     * @return array{
     *     intent: string,
     *     reply: string,
     *     suggestions: list<string>,
     *     links: list<array{label: string, url: string}>
     * }
     */
    public function answer(User $user, string $question): array
    {
        $question = trim($question);

        if ($question === '') {
            return $this->reply('empty', 'Silakan tulis pertanyaanmu dulu ya.', []);
        }

        $detail = $this->wantsDetail($question);
        $intent = $this->detectIntent($user, $question);

        if ($user->isTeacher()) {
            return $this->dispatchTeacher($user, $intent, $question, $detail);
        }

        if ($user->isStudent()) {
            return $this->dispatchStudent($user, $intent, $question, $detail);
        }

        if ($user->isCounselor()) {
            return $this->dispatchCounselor($user, $intent, $question, $detail);
        }

        return $this->reply('unsupported', 'Maaf, fitur chatbot belum tersedia untuk rolemu saat ini.', []);
    }

    // =========================================================
    // DISPATCH PER ROLE
    // =========================================================

    /**
     * @return array{intent: string, reply: string, suggestions: list<string>, links: list<array{label: string, url: string}>}
     */
    private function dispatchTeacher(User $user, string $intent, string $question, bool $detail): array
    {
        return match ($intent) {
            'greeting' => $this->greetingAnswer($user),
            'thanks' => $this->thanksAnswer(),
            'help' => $this->teacherHelpAnswer(),
            'teacher_gradebooks' => $this->teacherGradebooksAnswer($user, $detail),
            'teacher_assessments' => $this->teacherAssessmentsAnswer($user, $detail),
            'teacher_submissions' => $this->teacherSubmissionsAnswer($user, $detail),
            'teacher_journals' => $this->teacherJournalsAnswer($user, $detail),
            'teacher_rubrics' => $this->teacherRubricsAnswer($user, $detail),
            'teacher_classes' => $this->teacherClassesAnswer($user, $detail),
            'teacher_profile' => $this->teacherProfileAnswer($user),
            default => $this->teacherFallbackAnswer(),
        };
    }

    /**
     * @return array{intent: string, reply: string, suggestions: list<string>, links: list<array{label: string, url: string}>}
     */
    private function dispatchStudent(User $user, string $intent, string $question, bool $detail): array
    {
        return match ($intent) {
            'greeting' => $this->greetingAnswer($user),
            'thanks' => $this->thanksAnswer(),
            'help' => $this->studentHelpAnswer(),
            'student_grades' => $this->studentGradesAnswer($user, $detail),
            'student_assignments' => $this->studentAssignmentsAnswer($user, $detail),
            'student_attendance' => $this->studentAttendanceAnswer($user),
            'student_exit_permit' => $this->studentExitPermitAnswer($user, $detail),
            'student_discipline' => $this->studentDisciplineAnswer($user, $detail),
            'student_schedule' => $this->studentScheduleAnswer($user),
            'student_profile' => $this->studentProfileAnswer($user),
            default => $this->studentFallbackAnswer(),
        };
    }

    /**
     * @return array{intent: string, reply: string, suggestions: list<string>, links: list<array{label: string, url: string}>}
     */
    private function dispatchCounselor(User $user, string $intent, string $question, bool $detail): array
    {
        return match ($intent) {
            'greeting' => $this->greetingAnswer($user),
            'thanks' => $this->thanksAnswer(),
            'help' => $this->counselorHelpAnswer(),
            'counselor_exit_permits' => $this->counselorExitPermitsAnswer($detail),
            'counselor_discipline' => $this->counselorDisciplineAnswer($detail),
            'counselor_letters' => $this->counselorLettersAnswer($detail),
            'counselor_students' => $this->counselorStudentsAnswer($detail),
            'counselor_counseling' => $this->counselorCounselingAnswer($detail),
            'counselor_appeals' => $this->counselorAppealsAnswer($detail),
            'counselor_profile' => $this->counselorProfileAnswer($user),
            default => $this->counselorFallbackAnswer(),
        };
    }

    // =========================================================
    // SHARED ANSWERS
    // =========================================================

    /**
     * @return array{intent: string, reply: string, suggestions: list<string>, links: list<array{label: string, url: string}>}
     */
    private function greetingAnswer(User $user): array
    {
        $name = $this->firstName($user->name);
        $role = match (true) {
            $user->isTeacher() => 'Bapak/Ibu guru',
            $user->isStudent() => 'teman',
            $user->isCounselor() => 'Bapak/Ibu BK',
            default => '',
        };

        return $this->reply(
            'greeting',
            "Halo, {$name}! Selamat datang kembali".($role !== '' ? ", {$role}" : '').'. Ada yang bisa saya bantu?',
            match (true) {
                $user->isTeacher() => ['Buku nilai saya', 'Tugas yang belum dinilai', 'Jurnal terbaru'],
                $user->isStudent() => ['Nilai terbaru saya', 'Tugas deadline terdekat', 'Poin disiplin saya'],
                $user->isCounselor() => ['Izin keluar pending', 'Siswa disiplin hari ini', 'Rekap konseling'],
                default => [],
            },
        );
    }

    /**
     * @return array{intent: string, reply: string, suggestions: list<string>, links: list<array{label: string, url: string}>}
     */
    private function thanksAnswer(): array
    {
        return $this->reply(
            'thanks',
            'Sama-sama! Kalau ada yang perlu ditanyakan lagi, saya siap membantu.',
            [],
        );
    }

    // =========================================================
    // TEACHER ANSWERS
    // =========================================================

    /**
     * @return array{intent: string, reply: string, suggestions: list<string>, links: list<array{label: string, url: string}>}
     */
    private function teacherHelpAnswer(): array
    {
        return $this->reply(
            'help',
            "Saya asisten AI khusus portal guru. Saya bisa bantu:\n"
            ."• **Buku Nilai** — rekap nilai per kelas dan kolom\n"
            ."• **Tugas & Ulangan** — daftar assessment yang kamu buat\n"
            ."• **Pengumpulan Tugas** — status submit siswa\n"
            ."• **Jurnal Kelas** — riwayat jurnal mengajarmu\n"
            ."• **Rubrik Penilaian** — rubrik yang kamu buat\n"
            .'• **Kelas yang Diajar** — penugasan mengajarmu',
            ['Buku nilai saya', 'Tugas aktif', 'Jurnal terbaru'],
            [['label' => 'Dashboard Guru', 'url' => route('teacher.dashboard')]],
        );
    }

    /**
     * @return array{intent: string, reply: string, suggestions: list<string>, links: list<array{label: string, url: string}>}
     */
    private function teacherGradebooksAnswer(User $user, bool $detail): array
    {
        $teacher = $this->teacherProfile($user);

        if ($teacher === null) {
            return $this->reply('teacher_gradebooks', 'Profil guru tidak ditemukan. Hubungi admin sekolah.', []);
        }

        $gradebooks = $this->guard(fn (): Collection => Gradebook::query()
            ->with(['teachingAssignment.subject', 'teachingAssignment.schoolClass', 'teachingAssignment.semester'])
            ->whereHas('teachingAssignment', fn ($q) => $q->where('teacher_id', $teacher->id))
            ->where('is_active', true)
            ->orderByDesc('id')
            ->limit($detail ? 10 : 5)
            ->get()) ?? new Collection;

        if ($gradebooks->isEmpty()) {
            return $this->reply(
                'teacher_gradebooks',
                'Kamu belum memiliki buku nilai aktif. Buat buku nilai baru dari menu Buku Nilai.',
                ['Kelas yang saya ajar', 'Buat buku nilai baru'],
                [['label' => 'Buku Nilai', 'url' => route('teacher.gradebooks.index')]],
            );
        }

        $lines = ["Kamu memiliki **{$gradebooks->count()}** buku nilai aktif:"];

        foreach ($gradebooks as $gradebook) {
            $assignment = $gradebook->teachingAssignment;
            $subject = $assignment?->subject?->name ?? '-';
            $class = $assignment?->schoolClass?->name ?? '-';
            $semester = $assignment?->semester?->name ?? '';
            $lines[] = "• **{$gradebook->name}** — {$subject} / {$class}"
                .($detail && $semester !== '' ? " ({$semester})" : '');
        }

        return $this->reply(
            'teacher_gradebooks',
            implode("\n", $lines),
            ['Input nilai siswa', 'Tugas yang belum dinilai', 'Jurnal kelas'],
            [['label' => 'Buka Buku Nilai', 'url' => route('teacher.gradebooks.index')]],
        );
    }

    /**
     * @return array{intent: string, reply: string, suggestions: list<string>, links: list<array{label: string, url: string}>}
     */
    private function teacherAssessmentsAnswer(User $user, bool $detail): array
    {
        $teacher = $this->teacherProfile($user);

        if ($teacher === null) {
            return $this->reply('teacher_assessments', 'Profil guru tidak ditemukan.', []);
        }

        $assessments = $this->guard(fn (): Collection => Assessment::query()
            ->with(['teachingAssignment.subject', 'teachingAssignment.schoolClass'])
            ->where('created_by', $teacher->id)
            ->orderByDesc('due_at')
            ->limit($detail ? 10 : 5)
            ->get()) ?? new Collection;

        if ($assessments->isEmpty()) {
            return $this->reply(
                'teacher_assessments',
                'Kamu belum membuat tugas atau ulangan apapun.',
                ['Buat tugas baru', 'Buku nilai saya'],
                [['label' => 'Tugas & Ulangan', 'url' => route('teacher.assessments.index')]],
            );
        }

        $upcoming = $assessments->filter(fn ($a) => $a->due_at !== null && $a->due_at->isFuture());
        $total = $assessments->count();

        $lines = ["Kamu memiliki **{$total}** tugas/ulangan"
            .($upcoming->count() > 0 ? ", **{$upcoming->count()}** akan datang" : '').':'];

        foreach ($assessments->take($detail ? 8 : 4) as $assessment) {
            $subject = $assessment->teachingAssignment?->subject?->name ?? '-';
            $class = $assessment->teachingAssignment?->schoolClass?->name ?? '';
            $due = $assessment->due_at !== null ? ' (deadline '.$this->formatDate($assessment->due_at).')' : '';
            $type = $assessment->type?->label() ?? $assessment->type?->value ?? '';
            $status = $assessment->status?->value ?? '';
            $lines[] = "• **{$assessment->title}** — {$type} {$subject}"
                .($class !== '' ? " / {$class}" : '')
                .($detail ? "{$due} [{$status}]" : $due);
        }

        return $this->reply(
            'teacher_assessments',
            implode("\n", $lines),
            ['Status pengumpulan', 'Buku nilai saya', 'Buat tugas baru'],
            [['label' => 'Semua Tugas', 'url' => route('teacher.assessments.index')]],
        );
    }

    /**
     * @return array{intent: string, reply: string, suggestions: list<string>, links: list<array{label: string, url: string}>}
     */
    private function teacherSubmissionsAnswer(User $user, bool $detail): array
    {
        $teacher = $this->teacherProfile($user);

        if ($teacher === null) {
            return $this->reply('teacher_submissions', 'Profil guru tidak ditemukan.', []);
        }

        $pending = $this->guard(fn (): Collection => AssessmentSubmission::query()
            ->with(['assessment.teachingAssignment.subject', 'student.user'])
            ->whereHas('assessment', fn ($q) => $q->where('created_by', $teacher->id))
            ->whereNull('reviewed_at')
            ->orderByDesc('submitted_at')
            ->limit($detail ? 10 : 5)
            ->get()) ?? new Collection;

        $total = (int) $this->guard(fn (): int => AssessmentSubmission::query()
            ->whereHas('assessment', fn ($q) => $q->where('created_by', $teacher->id))
            ->whereNull('reviewed_at')
            ->count()) ?? 0;

        if ($total === 0) {
            return $this->reply(
                'teacher_submissions',
                'Tidak ada pengumpulan tugas yang menunggu penilaian. Semua sudah dinilai!',
                ['Buku nilai saya', 'Lihat semua tugas'],
                [['label' => 'Tugas & Ulangan', 'url' => route('teacher.assessments.index')]],
            );
        }

        $lines = ["Ada **{$total}** pengumpulan tugas yang belum dinilai:"];

        foreach ($pending as $submission) {
            $studentName = $submission->student?->user?->name ?? 'Siswa';
            $assessmentTitle = $submission->assessment?->title ?? '-';
            $subject = $submission->assessment?->teachingAssignment?->subject?->name ?? '';
            $submittedAt = $this->formatDate($submission->submitted_at);
            $lines[] = "• **{$studentName}** — {$assessmentTitle}"
                .($subject !== '' ? " ({$subject})" : '')
                .($detail ? " — dikumpul {$submittedAt}" : '');
        }

        return $this->reply(
            'teacher_submissions',
            implode("\n", $lines),
            ['Nilai sekarang', 'Buku nilai saya'],
            [['label' => 'Tugas & Ulangan', 'url' => route('teacher.assessments.index')]],
        );
    }

    /**
     * @return array{intent: string, reply: string, suggestions: list<string>, links: list<array{label: string, url: string}>}
     */
    private function teacherJournalsAnswer(User $user, bool $detail): array
    {
        $teacher = $this->teacherProfile($user);

        if ($teacher === null) {
            return $this->reply('teacher_journals', 'Profil guru tidak ditemukan.', []);
        }

        $journals = $this->guard(fn (): Collection => ClassJournal::query()
            ->with(['teachingAssignment.subject', 'teachingAssignment.schoolClass'])
            ->where('created_by', $teacher->id)
            ->orderByDesc('journal_date')
            ->limit($detail ? 8 : 4)
            ->get()) ?? new Collection;

        $total = (int) $this->guard(fn (): int => ClassJournal::query()
            ->where('created_by', $teacher->id)
            ->count()) ?? 0;

        if ($total === 0) {
            return $this->reply(
                'teacher_journals',
                'Kamu belum memiliki jurnal kelas. Buat jurnal mengajar dari menu Jurnal Kelas.',
                ['Buku nilai saya', 'Kelas yang saya ajar'],
                [['label' => 'Jurnal Kelas', 'url' => route('teacher.journals.index')]],
            );
        }

        $lines = ["Total **{$total}** jurnal kelas. ".($detail ? 'Jurnal terbaru:' : 'Beberapa terbaru:')];

        foreach ($journals as $journal) {
            $subject = $journal->teachingAssignment?->subject?->name ?? '-';
            $class = $journal->teachingAssignment?->schoolClass?->name ?? '-';
            $material = filled($journal->material) ? ' — '.$this->clip($journal->material, 60) : '';
            $lines[] = "• **{$this->formatDate($journal->journal_date)}** — {$subject} / {$class}{$material}";
        }

        return $this->reply(
            'teacher_journals',
            implode("\n", $lines),
            ['Buat jurnal baru', 'Buku nilai saya'],
            [['label' => 'Semua Jurnal', 'url' => route('teacher.journals.index')]],
        );
    }

    /**
     * @return array{intent: string, reply: string, suggestions: list<string>, links: list<array{label: string, url: string}>}
     */
    private function teacherRubricsAnswer(User $user, bool $detail): array
    {
        $teacher = $this->teacherProfile($user);

        if ($teacher === null) {
            return $this->reply('teacher_rubrics', 'Profil guru tidak ditemukan.', []);
        }

        $rubrics = $this->guard(fn (): Collection => Rubric::query()
            ->where('created_by', $teacher->id)
            ->orderByDesc('id')
            ->limit($detail ? 8 : 4)
            ->get()) ?? new Collection;

        if ($rubrics->isEmpty()) {
            return $this->reply(
                'teacher_rubrics',
                'Kamu belum membuat rubrik penilaian. Buat rubrik dari menu Rubrik Penilaian.',
                ['Buat tugas baru', 'Buku nilai saya'],
                [['label' => 'Rubrik Penilaian', 'url' => route('teacher.rubrics.index')]],
            );
        }

        $lines = ["Kamu memiliki **{$rubrics->count()}** rubrik penilaian:"];

        foreach ($rubrics as $rubric) {
            $desc = filled($rubric->description) ? ' — '.$this->clip($rubric->description, 60) : '';
            $lines[] = "• **{$rubric->name}**{$desc}";
        }

        return $this->reply(
            'teacher_rubrics',
            implode("\n", $lines),
            ['Buat rubrik baru', 'Tugas saya', 'Buku nilai saya'],
            [['label' => 'Rubrik Penilaian', 'url' => route('teacher.rubrics.index')]],
        );
    }

    /**
     * @return array{intent: string, reply: string, suggestions: list<string>, links: list<array{label: string, url: string}>}
     */
    private function teacherClassesAnswer(User $user, bool $detail): array
    {
        $teacher = $this->teacherProfile($user);

        if ($teacher === null) {
            return $this->reply('teacher_classes', 'Profil guru tidak ditemukan.', []);
        }

        $assignments = $this->guard(fn (): Collection => TeachingAssignment::query()
            ->with(['subject', 'schoolClass', 'semester'])
            ->where('teacher_id', $teacher->id)
            ->where('is_active', true)
            ->orderByDesc('id')
            ->get()) ?? new Collection;

        if ($assignments->isEmpty()) {
            return $this->reply(
                'teacher_classes',
                'Kamu belum memiliki penugasan mengajar aktif. Hubungi admin untuk penugasan.',
                ['Buku nilai saya', 'Profil saya'],
                [['label' => 'Dashboard Guru', 'url' => route('teacher.dashboard')]],
            );
        }

        $lines = ["Kamu mengajar **{$assignments->count()}** kelas aktif:"];

        foreach ($assignments as $assignment) {
            $subject = $assignment->subject?->name ?? '-';
            $class = $assignment->schoolClass?->name ?? '-';
            $semester = $detail && $assignment->semester !== null ? " ({$assignment->semester->name})" : '';
            $hours = $detail && $assignment->weekly_hours > 0 ? " — {$assignment->weekly_hours} jam/minggu" : '';
            $lines[] = "• **{$subject}** di kelas {$class}{$semester}{$hours}";
        }

        return $this->reply(
            'teacher_classes',
            implode("\n", $lines),
            ['Buku nilai saya', 'Jurnal kelas', 'Tugas saya'],
            [['label' => 'Dashboard Guru', 'url' => route('teacher.dashboard')]],
        );
    }

    /**
     * @return array{intent: string, reply: string, suggestions: list<string>, links: list<array{label: string, url: string}>}
     */
    private function teacherProfileAnswer(User $user): array
    {
        $teacher = $this->teacherProfile($user);

        $lines = ["**Profil Guru**\nNama: {$user->name}"];

        if ($teacher !== null) {
            if (filled($teacher->nip)) {
                $lines[] = "NIP: {$teacher->nip}";
            }
            if (filled($teacher->phone)) {
                $lines[] = "Telepon: {$teacher->phone}";
            }
        }

        if (filled($user->email)) {
            $lines[] = "Email: {$user->email}";
        }

        return $this->reply(
            'teacher_profile',
            implode("\n", $lines),
            ['Kelas yang saya ajar', 'Buku nilai saya'],
            [['label' => 'Edit Profil', 'url' => route('teacher.profile.index')]],
        );
    }

    /**
     * @return array{intent: string, reply: string, suggestions: list<string>, links: list<array{label: string, url: string}>}
     */
    private function teacherFallbackAnswer(): array
    {
        return $this->reply(
            'fallback',
            "Maaf, aku belum mengenali pertanyaan itu.\n"
            .'Coba tanyakan soal: **buku nilai**, **tugas siswa**, **pengumpulan**, **jurnal kelas**, **rubrik**, atau **kelas yang diajar**.',
            ['Buku nilai saya', 'Tugas belum dinilai', 'Jurnal terbaru'],
            [['label' => 'Dashboard Guru', 'url' => route('teacher.dashboard')]],
        );
    }

    // =========================================================
    // STUDENT ANSWERS
    // =========================================================

    /**
     * @return array{intent: string, reply: string, suggestions: list<string>, links: list<array{label: string, url: string}>}
     */
    private function studentHelpAnswer(): array
    {
        return $this->reply(
            'help',
            "Saya asisten AI khusus portal siswa. Saya bisa bantu:\n"
            ."• **Nilai** — rekap nilai dan skor per mata pelajaran\n"
            ."• **Tugas** — daftar tugas dan deadline\n"
            ."• **Izin Keluar** — status izin keluar sekolah\n"
            ."• **Disiplin** — poin dan catatan pelanggaranmu\n"
            ."• **Jadwal** — jadwal pelajaranmu\n"
            .'• **Profil** — data dirimu',
            ['Nilai terbaru saya', 'Tugas deadline terdekat', 'Poin disiplin saya'],
        );
    }

    /**
     * @return array{intent: string, reply: string, suggestions: list<string>, links: list<array{label: string, url: string}>}
     */
    private function studentGradesAnswer(User $user, bool $detail): array
    {
        $student = $this->studentProfile($user);

        if ($student === null) {
            return $this->reply('student_grades', 'Profil siswa tidak ditemukan. Hubungi admin sekolah.', []);
        }

        // Get gradebook memberships
        $memberships = $this->guard(fn (): Collection => GradebookStudent::query()
            ->with([
                'gradebook.teachingAssignment.subject',
                'gradebook.teachingAssignment.schoolClass',
            ])
            ->where('student_id', $student->id)
            ->where('status', 'ACTIVE')
            ->get()) ?? new Collection;

        if ($memberships->isEmpty()) {
            return $this->reply(
                'student_grades',
                'Kamu belum terdaftar di buku nilai manapun.',
                ['Jadwal pelajaranku', 'Profil saya'],
                [['label' => 'Rekap Nilai', 'url' => route('student.grades.index')]],
            );
        }

        $lines = ["Kamu terdaftar di **{$memberships->count()}** buku nilai."];

        if ($detail) {
            // Get recent scores
            $scores = $this->guard(fn (): Collection => GradebookScore::query()
                ->with(['column.gradebook.teachingAssignment.subject'])
                ->where('student_id', $student->id)
                ->whereNotNull('final_score')
                ->orderByDesc('graded_at')
                ->limit(6)
                ->get()) ?? new Collection;

            if ($scores->isNotEmpty()) {
                $lines[] = 'Nilai terbaru yang kamu terima:';
                foreach ($scores as $score) {
                    $subject = $score->column?->gradebook?->teachingAssignment?->subject?->name ?? '-';
                    $colName = $score->column?->name ?? '-';
                    $finalScore = number_format((float) $score->final_score, 1);
                    $maxScore = number_format((float) $score->max_score_snapshot, 0);
                    $lines[] = "• **{$subject}** — {$colName}: **{$finalScore}** / {$maxScore}";
                }
            }
        } else {
            $lines[] = 'Ketik "nilai lengkap" untuk melihat rekap nilai terbaru.';
        }

        return $this->reply(
            'student_grades',
            implode("\n", $lines),
            ['Nilai tugas terbaru', 'Tugas yang belum kukumpulkan', 'Jadwal pelajaran'],
            [['label' => 'Rekap Nilai', 'url' => route('student.grades.index')]],
        );
    }

    /**
     * @return array{intent: string, reply: string, suggestions: list<string>, links: list<array{label: string, url: string}>}
     */
    private function studentAssignmentsAnswer(User $user, bool $detail): array
    {
        $student = $this->studentProfile($user);

        if ($student === null) {
            return $this->reply('student_assignments', 'Profil siswa tidak ditemukan.', []);
        }

        // Active assessments for the student's class
        $enrollment = $this->guard(fn () => $student->currentEnrollment?->load('schoolClass'));

        $classId = $enrollment?->schoolClass?->id ?? null;

        $assessments = $this->guard(fn (): Collection => Assessment::query()
            ->with(['teachingAssignment.subject'])
            ->whereHas('teachingAssignment', fn ($q) => $q->when($classId !== null, fn ($q2) => $q2->where('class_id', $classId)))
            ->where('status', AssessmentStatus::Published)
            ->orderBy('due_at')
            ->limit($detail ? 10 : 5)
            ->get()) ?? new Collection;

        if ($assessments->isEmpty()) {
            return $this->reply(
                'student_assignments',
                'Tidak ada tugas aktif saat ini untuk kelasmu.',
                ['Nilai terbaru saya', 'Jadwal pelajaran'],
                [['label' => 'Daftar Tugas', 'url' => route('student.assignments.index')]],
            );
        }

        // Split submitted vs not
        $submittedIds = $this->guard(fn (): Collection => AssessmentSubmission::query()
            ->where('student_id', $student->id)
            ->pluck('assessment_id')) ?? new Collection;

        $pending = $assessments->whereNotIn('id', $submittedIds->all());
        $submitted = $assessments->whereIn('id', $submittedIds->all());

        $lines = [];

        if ($pending->isNotEmpty()) {
            $lines[] = "**{$pending->count()}** tugas belum dikumpulkan:";
            foreach ($pending->take(5) as $assessment) {
                $subject = $assessment->teachingAssignment?->subject?->name ?? '-';
                $due = $assessment->due_at !== null ? ' — deadline '.$this->formatDate($assessment->due_at) : '';
                $type = $assessment->type?->label() ?? $assessment->type?->value ?? '';
                $lines[] = "• **{$assessment->title}** ({$type} {$subject}){$due}";
            }
        } else {
            $lines[] = 'Selamat! Semua tugas aktif sudah kamu kumpulkan.';
        }

        if ($detail && $submitted->isNotEmpty()) {
            $lines[] = "**{$submitted->count()}** tugas sudah dikumpulkan.";
        }

        return $this->reply(
            'student_assignments',
            implode("\n", $lines),
            ['Nilai tugas saya', 'Izin keluar', 'Jadwal pelajaran'],
            [['label' => 'Daftar Tugas', 'url' => route('student.assignments.index')]],
        );
    }

    /**
     * @return array{intent: string, reply: string, suggestions: list<string>, links: list<array{label: string, url: string}>}
     */
    private function studentAttendanceAnswer(User $user): array
    {
        $student = $this->studentProfile($user);

        if ($student === null) {
            return $this->reply('student_attendance', 'Profil siswa tidak ditemukan.', []);
        }

        return $this->reply(
            'student_attendance',
            'Untuk melihat rekap kehadiran, kunjungi halaman Jadwal Pelajaran atau Nilai di portalmu.',
            ['Jadwal pelajaran', 'Nilai saya', 'Izin keluar'],
            [['label' => 'Jadwal Pelajaran', 'url' => route('student.schedules.index')]],
        );
    }

    /**
     * @return array{intent: string, reply: string, suggestions: list<string>, links: list<array{label: string, url: string}>}
     */
    private function studentExitPermitAnswer(User $user, bool $detail): array
    {
        $student = $this->studentProfile($user);

        if ($student === null) {
            return $this->reply('student_exit_permit', 'Profil siswa tidak ditemukan.', []);
        }

        $permits = $this->guard(fn (): Collection => ExitPermit::query()
            ->with('reason')
            ->where('student_id', $student->id)
            ->orderByDesc('requested_at')
            ->limit($detail ? 8 : 3)
            ->get()) ?? new Collection;

        $activePermit = $this->guard(fn () => ExitPermit::query()
            ->with('reason')
            ->where('student_id', $student->id)
            ->whereIn('status', [ExitPermitStatus::Approved, ExitPermitStatus::Late])
            ->latest('approved_at')
            ->first());

        if ($permits->isEmpty()) {
            return $this->reply(
                'student_exit_permit',
                'Kamu belum pernah mengajukan izin keluar.',
                ['Ajukan izin keluar', 'Jadwal pelajaran'],
                [['label' => 'Izin Keluar', 'url' => route('student.exit-permits.index')]],
            );
        }

        $lines = [];

        if ($activePermit !== null) {
            $status = $activePermit->status === ExitPermitStatus::Late ? 'TERLAMBAT KEMBALI' : 'SEDANG AKTIF';
            $returnAt = $this->formatDate($activePermit->planned_return_at);
            $lines[] = "⚠️ Kamu memiliki izin keluar yang **{$status}**.\nRencana kembali: {$returnAt}";
        }

        $lines[] = "Riwayat izin keluar (**{$permits->count()}** terakhir):";

        foreach ($permits as $permit) {
            $reason = $permit->reason?->name ?? 'Lainnya';
            $statusLabel = $permit->status?->label() ?? $permit->status?->value ?? '-';
            $date = $this->formatDate($permit->requested_at);
            $lines[] = "• {$date} — {$reason} [{$statusLabel}]";
        }

        return $this->reply(
            'student_exit_permit',
            implode("\n", $lines),
            ['Ajukan izin baru', 'Poin disiplin saya', 'Jadwal pelajaran'],
            [['label' => 'Izin Keluar', 'url' => route('student.exit-permits.index')]],
        );
    }

    /**
     * @return array{intent: string, reply: string, suggestions: list<string>, links: list<array{label: string, url: string}>}
     */
    private function studentDisciplineAnswer(User $user, bool $detail): array
    {
        $student = $this->studentProfile($user);

        if ($student === null) {
            return $this->reply('student_discipline', 'Profil siswa tidak ditemukan.', []);
        }

        $totalPoints = (int) $this->guard(fn (): int => DisciplineRecord::query()
            ->where('student_id', $student->id)
            ->sum('points_delta')) ?? 0;

        $letters = (int) $this->guard(fn (): int => DisciplinaryLetter::query()
            ->where('student_id', $student->id)
            ->count()) ?? 0;

        $recentRecords = $this->guard(fn (): Collection => DisciplineRecord::query()
            ->with('category')
            ->where('student_id', $student->id)
            ->orderByDesc('occurred_at')
            ->limit($detail ? 5 : 3)
            ->get()) ?? new Collection;

        $lines = [
            "**Catatan Disiplin**\nTotal poin saat ini: **{$totalPoints}**"
                .($letters > 0 ? "\nSurat peringatan diterima: **{$letters}**" : ''),
        ];

        if ($recentRecords->isNotEmpty()) {
            $lines[] = 'Catatan terbaru:';
            foreach ($recentRecords as $record) {
                $category = $record->category?->name ?? 'Umum';
                $delta = $record->points_delta > 0 ? "+{$record->points_delta}" : (string) $record->points_delta;
                $date = $this->formatDate($record->occurred_at);
                $lines[] = "• {$date} — {$category} ({$delta} poin)";
            }
        } elseif ($totalPoints === 0) {
            $lines[] = 'Tidak ada catatan pelanggaran. Pertahankan! 👍';
        }

        return $this->reply(
            'student_discipline',
            implode("\n", $lines),
            ['Izin keluar saya', 'Nilai saya', 'Jadwal pelajaran'],
            [['label' => 'Info Disiplin', 'url' => route('student.discipline.index')]],
        );
    }

    /**
     * @return array{intent: string, reply: string, suggestions: list<string>, links: list<array{label: string, url: string}>}
     */
    private function studentScheduleAnswer(User $user): array
    {
        $student = $this->studentProfile($user);

        if ($student === null) {
            return $this->reply('student_schedule', 'Profil siswa tidak ditemukan.', []);
        }

        $enrollment = $this->guard(fn () => $student->currentEnrollment?->load('schoolClass.teachingAssignments.subject'));

        if ($enrollment === null || $enrollment->schoolClass === null) {
            return $this->reply(
                'student_schedule',
                'Kamu belum terdaftar di kelas aktif manapun. Hubungi admin sekolah.',
                ['Profil saya', 'Nilai saya'],
            );
        }

        $class = $enrollment->schoolClass;
        $subjects = $this->guard(fn (): Collection => $class->teachingAssignments()
            ->with(['subject', 'teacher.user'])
            ->where('is_active', true)
            ->get()) ?? new Collection;

        $lines = ["Kamu berada di kelas **{$class->name}**."];

        if ($subjects->isNotEmpty()) {
            $lines[] = "Mata pelajaran yang diajarkan ({$subjects->count()}):";
            foreach ($subjects as $assignment) {
                $subject = $assignment->subject?->name ?? '-';
                $teacherName = $assignment->teacher?->user?->name ?? '-';
                $lines[] = "• **{$subject}** — {$teacherName}";
            }
        } else {
            $lines[] = 'Belum ada mata pelajaran yang terdaftar untuk kelasmu.';
        }

        return $this->reply(
            'student_schedule',
            implode("\n", $lines),
            ['Tugas belum dikumpulkan', 'Nilai saya', 'Izin keluar'],
            [['label' => 'Jadwal Pelajaran', 'url' => route('student.schedules.index')]],
        );
    }

    /**
     * @return array{intent: string, reply: string, suggestions: list<string>, links: list<array{label: string, url: string}>}
     */
    private function studentProfileAnswer(User $user): array
    {
        $student = $this->studentProfile($user);

        $lines = ["**Profil Siswa**\nNama: {$user->name}"];

        if ($student !== null) {
            if (filled($student->nis)) {
                $lines[] = "NIS: {$student->nis}";
            }
            if (filled($student->nisn)) {
                $lines[] = "NISN: {$student->nisn}";
            }

            $enrollment = $this->guard(fn () => $student->currentEnrollment?->load('schoolClass'));
            if ($enrollment?->schoolClass !== null) {
                $lines[] = "Kelas: {$enrollment->schoolClass->name}";
            }
        }

        if (filled($user->email)) {
            $lines[] = "Email: {$user->email}";
        }

        return $this->reply(
            'student_profile',
            implode("\n", $lines),
            ['Nilai saya', 'Tugas saya', 'Jadwal pelajaran'],
        );
    }

    /**
     * @return array{intent: string, reply: string, suggestions: list<string>, links: list<array{label: string, url: string}>}
     */
    private function studentFallbackAnswer(): array
    {
        return $this->reply(
            'fallback',
            "Maaf, aku belum mengenali pertanyaan itu.\n"
            .'Coba tanyakan soal: **nilai**, **tugas**, **izin keluar**, **disiplin**, atau **jadwal pelajaran**.',
            ['Nilai terbaru saya', 'Tugas yang belum kukumpulkan', 'Poin disiplin saya'],
        );
    }

    // =========================================================
    // COUNSELOR ANSWERS
    // =========================================================

    /**
     * @return array{intent: string, reply: string, suggestions: list<string>, links: list<array{label: string, url: string}>}
     */
    private function counselorHelpAnswer(): array
    {
        return $this->reply(
            'help',
            "Saya asisten AI khusus BK. Saya bisa bantu:\n"
            ."• **Izin Keluar** — daftar izin pending, aktif, dan terlambat\n"
            ."• **Disiplin** — rekap catatan pelanggaran siswa\n"
            ."• **Surat Peringatan** — daftar SP yang diterbitkan\n"
            ."• **Data Siswa** — cari dan lihat profil siswa\n"
            ."• **Konseling** — rekap sesi konseling\n"
            .'• **Banding** — izin keluar yang diajukan banding',
            ['Izin keluar pending', 'Rekap disiplin hari ini', 'Banding belum diproses'],
            [['label' => 'Dashboard BK', 'url' => route('counselor.dashboard')]],
        );
    }

    /**
     * @return array{intent: string, reply: string, suggestions: list<string>, links: list<array{label: string, url: string}>}
     */
    private function counselorExitPermitsAnswer(bool $detail): array
    {
        $pending = (int) $this->guard(fn (): int => ExitPermit::query()
            ->whereIn('status', [ExitPermitStatus::Pending])
            ->count()) ?? 0;

        $active = (int) $this->guard(fn (): int => ExitPermit::query()
            ->where('status', ExitPermitStatus::Approved)
            ->count()) ?? 0;

        $overdue = (int) $this->guard(fn (): int => ExitPermit::query()
            ->where('status', ExitPermitStatus::Late)
            ->count()) ?? 0;

        $lines = [
            "**Ringkasan Izin Keluar Hari Ini**\n"
            ."• Menunggu persetujuan: **{$pending}**\n"
            ."• Sedang aktif (keluar): **{$active}**\n"
            ."• Terlambat kembali: **{$overdue}**",
        ];

        if ($detail && $pending > 0) {
            $permits = $this->guard(fn (): Collection => ExitPermit::query()
                ->with(['student.user', 'reason'])
                ->where('status', ExitPermitStatus::Pending)
                ->orderBy('requested_at')
                ->limit(5)
                ->get()) ?? new Collection;

            if ($permits->isNotEmpty()) {
                $lines[] = 'Izin menunggu persetujuan:';
                foreach ($permits as $permit) {
                    $name = $permit->student?->user?->name ?? 'Siswa';
                    $reason = $permit->reason?->name ?? 'Lainnya';
                    $time = $permit->requested_at?->format('H:i') ?? '-';
                    $lines[] = "• **{$name}** — {$reason} (jam {$time})";
                }
            }
        }

        if ($overdue > 0) {
            $lines[] = "⚠️ Ada **{$overdue}** siswa yang terlambat kembali!";
        }

        return $this->reply(
            'counselor_exit_permits',
            implode("\n", $lines),
            ['Setujui izin', 'Siswa terlambat kembali', 'Rekap disiplin'],
            [['label' => 'Kelola Izin Keluar', 'url' => route('counselor.exit-permits.index')]],
        );
    }

    /**
     * @return array{intent: string, reply: string, suggestions: list<string>, links: list<array{label: string, url: string}>}
     */
    private function counselorDisciplineAnswer(bool $detail): array
    {
        $todayCount = (int) $this->guard(fn (): int => DisciplineRecord::query()
            ->whereDate('occurred_at', today())
            ->count()) ?? 0;

        $weekCount = (int) $this->guard(fn (): int => DisciplineRecord::query()
            ->whereBetween('occurred_at', [now()->startOfWeek(), now()->endOfWeek()])
            ->count()) ?? 0;

        $lines = [
            "**Catatan Disiplin**\n"
            ."• Pelanggaran hari ini: **{$todayCount}**\n"
            ."• Pelanggaran minggu ini: **{$weekCount}**",
        ];

        if ($detail) {
            $records = $this->guard(fn (): Collection => DisciplineRecord::query()
                ->with(['student.user', 'category'])
                ->orderByDesc('occurred_at')
                ->limit(6)
                ->get()) ?? new Collection;

            if ($records->isNotEmpty()) {
                $lines[] = 'Catatan terbaru:';
                foreach ($records as $record) {
                    $name = $record->student?->user?->name ?? 'Siswa';
                    $category = $record->category?->name ?? 'Umum';
                    $delta = $record->points_delta > 0 ? "+{$record->points_delta}" : (string) $record->points_delta;
                    $lines[] = "• **{$name}** — {$category} ({$delta} poin) [{$this->formatDate($record->occurred_at)}]";
                }
            }
        }

        return $this->reply(
            'counselor_discipline',
            implode("\n", $lines),
            ['Input catatan disiplin', 'Surat peringatan', 'Data siswa'],
            [['label' => 'Catatan Disiplin', 'url' => route('counselor.discipline.index')]],
        );
    }

    /**
     * @return array{intent: string, reply: string, suggestions: list<string>, links: list<array{label: string, url: string}>}
     */
    private function counselorLettersAnswer(bool $detail): array
    {
        $total = (int) $this->guard(fn (): int => DisciplinaryLetter::query()->count()) ?? 0;

        $thisYear = (int) $this->guard(fn (): int => DisciplinaryLetter::query()
            ->whereYear('issued_at', now()->year)
            ->count()) ?? 0;

        $lines = [
            "**Surat Peringatan (SP)**\n"
            ."• Total surat diterbitkan: **{$total}**\n"
            ."• Tahun ini: **{$thisYear}**",
        ];

        if ($detail) {
            $letters = $this->guard(fn (): Collection => DisciplinaryLetter::query()
                ->with(['student.user'])
                ->orderByDesc('issued_at')
                ->limit(5)
                ->get()) ?? new Collection;

            if ($letters->isNotEmpty()) {
                $lines[] = 'Surat terbaru:';
                foreach ($letters as $letter) {
                    $name = $letter->student?->user?->name ?? 'Siswa';
                    $type = $letter->type?->value ?? '-';
                    $date = $this->formatDate($letter->issued_at);
                    $lines[] = "• **{$name}** — {$type} ({$date})";
                }
            }
        }

        return $this->reply(
            'counselor_letters',
            implode("\n", $lines),
            ['Buat surat peringatan', 'Catatan disiplin', 'Data siswa'],
            [['label' => 'Surat Peringatan', 'url' => route('counselor.disciplinary-letters.index')]],
        );
    }

    /**
     * @return array{intent: string, reply: string, suggestions: list<string>, links: list<array{label: string, url: string}>}
     */
    private function counselorStudentsAnswer(bool $detail): array
    {
        $total = (int) $this->guard(fn (): int => StudentProfile::query()->where('status', 'ACTIVE')->count()) ?? 0;

        $lines = ["Total siswa aktif: **{$total}**"];

        if ($detail) {
            $lines[] = 'Gunakan halaman Data Siswa untuk mencari, melihat profil, dan riwayat disiplin tiap siswa.';
        }

        return $this->reply(
            'counselor_students',
            implode("\n", $lines),
            ['Cari siswa', 'Catatan disiplin', 'Izin keluar'],
            [['label' => 'Data Siswa', 'url' => route('counselor.students.index')]],
        );
    }

    /**
     * @return array{intent: string, reply: string, suggestions: list<string>, links: list<array{label: string, url: string}>}
     */
    private function counselorCounselingAnswer(bool $detail): array
    {
        return $this->reply(
            'counselor_counseling',
            'Rekap sesi konseling tersedia di halaman Rekam Konseling. Buka halaman tersebut untuk melihat dan menambah catatan konseling.',
            ['Tambah sesi konseling', 'Data siswa', 'Catatan disiplin'],
            [['label' => 'Rekam Konseling', 'url' => route('counselor.counseling.index')]],
        );
    }

    /**
     * @return array{intent: string, reply: string, suggestions: list<string>, links: list<array{label: string, url: string}>}
     */
    private function counselorAppealsAnswer(bool $detail): array
    {
        $pending = (int) $this->guard(fn (): int => ExitPermitAppeal::query()
            ->whereNull('decided_at')
            ->count()) ?? 0;

        $lines = ["Banding izin keluar yang belum diproses: **{$pending}**"];

        if ($pending > 0 && $detail) {
            $appeals = $this->guard(fn (): Collection => ExitPermitAppeal::query()
                ->with(['exitPermit.student.user'])
                ->whereNull('decided_at')
                ->orderBy('created_at')
                ->limit(5)
                ->get()) ?? new Collection;

            if ($appeals->isNotEmpty()) {
                $lines[] = 'Banding menunggu:';
                foreach ($appeals as $appeal) {
                    $name = $appeal->exitPermit?->student?->user?->name ?? 'Siswa';
                    $date = $this->formatDate($appeal->created_at);
                    $lines[] = "• **{$name}** — diajukan {$date}";
                }
            }
        }

        if ($pending === 0) {
            $lines[] = 'Tidak ada banding yang menunggu keputusan.';
        }

        return $this->reply(
            'counselor_appeals',
            implode("\n", $lines),
            ['Putuskan banding', 'Izin keluar aktif', 'Data siswa'],
            [['label' => 'Banding Keterlambatan', 'url' => route('counselor.appeals.index')]],
        );
    }

    /**
     * @return array{intent: string, reply: string, suggestions: list<string>, links: list<array{label: string, url: string}>}
     */
    private function counselorProfileAnswer(User $user): array
    {
        $staff = $this->guard(fn () => $user->staffProfile);

        $lines = ["**Profil BK**\nNama: {$user->name}"];

        if ($staff !== null) {
            if (filled($staff->employee_number)) {
                $lines[] = "NIP/No. Pegawai: {$staff->employee_number}";
            }
            if (filled($staff->phone)) {
                $lines[] = "Telepon: {$staff->phone}";
            }
        }

        if (filled($user->email)) {
            $lines[] = "Email: {$user->email}";
        }

        return $this->reply(
            'counselor_profile',
            implode("\n", $lines),
            ['Izin keluar pending', 'Catatan disiplin', 'Data siswa'],
            [['label' => 'Dashboard BK', 'url' => route('counselor.dashboard')]],
        );
    }

    /**
     * @return array{intent: string, reply: string, suggestions: list<string>, links: list<array{label: string, url: string}>}
     */
    private function counselorFallbackAnswer(): array
    {
        return $this->reply(
            'fallback',
            "Maaf, aku belum mengenali pertanyaan itu.\n"
            .'Coba tanyakan soal: **izin keluar**, **disiplin**, **surat peringatan**, **konseling**, **banding**, atau **data siswa**.',
            ['Izin keluar pending', 'Catatan disiplin hari ini', 'Surat peringatan terbaru'],
            [['label' => 'Dashboard BK', 'url' => route('counselor.dashboard')]],
        );
    }

    // =========================================================
    // INTENT DETECTION
    // =========================================================

    private function detectIntent(User $user, string $question): string
    {
        $haystack = $this->normalize($question);

        $intents = array_merge(
            self::SHARED_INTENTS,
            match (true) {
                $user->isTeacher() => self::TEACHER_INTENTS,
                $user->isStudent() => self::STUDENT_INTENTS,
                $user->isCounselor() => self::COUNSELOR_INTENTS,
                default => [],
            },
        );

        $best = '';
        $bestScore = 0;
        $bestPosition = -1;

        foreach ($intents as $group) {
            $score = 0;
            $position = -1;

            foreach ($group['keywords'] as $keyword) {
                $offset = $this->rootPosition($haystack, $keyword);

                if ($offset === null) {
                    continue;
                }

                $score += mb_strlen($keyword);
                $position = max($position, $offset);
            }

            if ($score === 0) {
                continue;
            }

            if ($score > $bestScore || ($score === $bestScore && $position > $bestPosition)) {
                $bestScore = $score;
                $bestPosition = $position;
                $best = $group['intent'];
            }
        }

        return $best;
    }

    private function rootPosition(string $haystack, string $keyword): ?int
    {
        $needle = $this->normalize($keyword);

        if ($needle === '') {
            return null;
        }

        $pattern = '/(?:^|\s)'.preg_quote($needle, '/').'\w*/u';

        return preg_match($pattern, $haystack, $matches, PREG_OFFSET_CAPTURE) === 1
            ? $matches[0][1]
            : null;
    }

    private function wantsDetail(string $question): bool
    {
        $haystack = $this->normalize($question);

        foreach (self::DETAIL_MARKERS as $marker) {
            if ($this->rootPosition($haystack, $marker) !== null) {
                return true;
            }
        }

        return false;
    }

    private function normalize(string $value): string
    {
        $value = mb_strtolower(trim($value));
        $value = preg_replace('/[^\p{L}\p{N}\s]+/u', ' ', $value) ?? $value;

        return trim(preg_replace('/\s+/u', ' ', $value) ?? $value);
    }

    // =========================================================
    // HELPERS
    // =========================================================

    /**
     * @param  list<string>  $suggestions
     * @param  list<array{label: string, url: string}>  $links
     * @return array{intent: string, reply: string, suggestions: list<string>, links: list<array{label: string, url: string}>}
     */
    private function reply(string $intent, string $reply, array $suggestions = [], array $links = []): array
    {
        return [
            'intent' => $intent,
            'reply' => $reply,
            'suggestions' => array_slice(array_values($suggestions), 0, 3),
            'links' => array_slice(array_values($links), 0, 2),
        ];
    }

    private function teacherProfile(User $user): ?TeacherProfile
    {
        return $this->memo['teacher_profile_'.$user->id] ??= $this->guard(fn () => $user->teacherProfile);
    }

    private function studentProfile(User $user): ?StudentProfile
    {
        return $this->memo['student_profile_'.$user->id] ??= $this->guard(fn () => $user->studentProfile);
    }

    private function firstName(string $name): string
    {
        return explode(' ', trim($name))[0];
    }

    private function clip(string $value, int $limit): string
    {
        $value = trim(preg_replace('/\s+/u', ' ', $value) ?? $value);

        if (mb_strlen($value) <= $limit) {
            return $value;
        }

        return rtrim(mb_substr($value, 0, $limit - 1)).'...';
    }

    private function formatDate(DateTimeInterface|string|null $date): string
    {
        if ($date === null || $date === '') {
            return '-';
        }

        $months = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
        ];

        try {
            $moment = $date instanceof DateTimeInterface ? Carbon::instance($date) : Carbon::parse($date);

            return $moment->day.' '.$months[$moment->month].' '.$moment->year;
        } catch (Throwable) {
            return '-';
        }
    }

    private function guard(callable $callback): mixed
    {
        try {
            return $callback();
        } catch (Throwable) {
            return null;
        }
    }
}
