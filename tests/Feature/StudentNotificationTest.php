<?php

namespace Tests\Feature;

use App\Enums\AssessmentStatus;
use App\Enums\ExitPermitStatus;
use App\Models\Assessment;
use App\Models\ExitPermit;
use App\Models\ExitPermitReason;
use App\Models\Gradebook;
use App\Models\GradebookColumn;
use App\Models\GradebookScore;
use App\Models\SchoolClass;
use App\Models\StudentProfile;
use App\Models\TeachingAssignment;
use App\Models\User;
use App\Notifications\AssessmentPublished;
use App\Notifications\ExitPermitDecided;
use App\Notifications\SubmissionGraded;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Notifikasi siswa sengaja diuji di level model, bukan lewat HTTP ke modul
 * Guru atau Guru BK. Tujuannya membuktikan bahwa observer bekerja tanpa
 * perubahan pada kedua modul tersebut.
 */
class StudentNotificationTest extends TestCase
{
    use RefreshDatabase;

    private User $studentUser;

    private StudentProfile $student;

    private User $teacherUser;

    private User $counselorUser;

    protected function setUp(): void
    {
        parent::setUp();

        // Seeder ikut membuat buku nilai, nilai siswa, dan izin keluar. Proses itu
        // tidak boleh menghasilkan notifikasi, jadi event model dimatikan selama
        // seeding. Setelah seeding observer tetap aktif seperti di aplikasi nyata,
        // karena diregistrasikan oleh AppServiceProvider.
        Model::withoutEvents(function () {
            $this->seed();
        });

        $this->studentUser = User::where('username', 'siswa.ahmad')->firstOrFail();
        $this->student = $this->studentUser->studentProfile;
        $this->teacherUser = User::where('username', 'guru.agus')->firstOrFail();
        $this->counselorUser = User::where('username', 'bk.dewi')->firstOrFail();
    }

    public function test_publishing_assessment_notifies_students_of_that_class(): void
    {
        $assessment = Assessment::factory()->create([
            'teaching_assignment_id' => $this->ownAssignmentId(),
            'status' => AssessmentStatus::Draft,
        ]);

        $this->assertSame(0, $this->studentUser->unreadNotifications()->count());

        $assessment->update(['status' => AssessmentStatus::Published]);

        $this->assertSame(1, $this->studentUser->unreadNotifications()->count());

        $notification = $this->studentUser->notifications()->firstOrFail();
        $this->assertSame(AssessmentPublished::class, $notification->type);
        $this->assertSame(route('student.assignments.show', $assessment), $notification->data['url']);
    }

    public function test_resaving_a_published_assessment_does_not_notify_again(): void
    {
        Assessment::factory()->create([
            'teaching_assignment_id' => $this->ownAssignmentId(),
            'status' => AssessmentStatus::Published,
        ]);

        $initial = $this->studentUser->unreadNotifications()->count();
        $this->assertGreaterThan(0, $initial);

        $assessment = Assessment::query()->latest('id')->firstOrFail();
        $assessment->update(['title' => 'Judul diubah guru']);
        $assessment->update(['description' => 'Deskripsi diubah lagi']);

        $this->assertSame($initial, $this->studentUser->unreadNotifications()->count());
    }

    public function test_publishing_assessment_does_not_notify_students_of_another_class(): void
    {
        $otherClassId = SchoolClass::where('code', 'XII-RPL-2')->firstOrFail()->id;

        Assessment::factory()->create([
            'teaching_assignment_id' => TeachingAssignment::where('class_id', $otherClassId)->firstOrFail()->id,
            'status' => AssessmentStatus::Published,
        ]);

        $this->assertSame(0, $this->studentUser->unreadNotifications()->count());
    }

    public function test_teacher_grading_notifies_the_student(): void
    {
        $this->makeScore(['final_score' => 88.5]);

        $this->assertSame(1, $this->studentUser->unreadNotifications()->count());

        $notification = $this->studentUser->notifications()->firstOrFail();
        $this->assertSame(SubmissionGraded::class, $notification->type);
        $this->assertStringContainsString('88.5', $notification->data['body']);
    }

    public function test_resaving_the_same_score_does_not_notify_again(): void
    {
        $score = $this->makeScore(['final_score' => 80]);

        $initial = $this->studentUser->unreadNotifications()->count();

        $score->update(['feedback' => 'Tambahkan komentar saja']);
        $score->update(['graded_by' => $this->teacherUser->teacherProfile->id]);

        $this->assertSame($initial, $this->studentUser->unreadNotifications()->count());
    }

    public function test_changing_the_score_value_notifies_again(): void
    {
        $score = $this->makeScore(['final_score' => 70]);

        $initial = $this->studentUser->unreadNotifications()->count();

        $score->update(['final_score' => 92]);

        $this->assertSame($initial + 1, $this->studentUser->unreadNotifications()->count());
    }

    public function test_counselor_approving_exit_permit_notifies_student(): void
    {
        $permit = $this->makePendingPermit();

        $this->assertSame(0, $this->studentUser->unreadNotifications()->count());

        $permit->update([
            'status' => ExitPermitStatus::Approved,
            'approved_at' => now(),
            'approved_by' => $this->counselorUser->staffProfile->id,
        ]);

        $this->assertSame(1, $this->studentUser->unreadNotifications()->count());

        $notification = $this->studentUser->notifications()->firstOrFail();
        $this->assertSame(ExitPermitDecided::class, $notification->type);
        $this->assertSame('Izin keluar disetujui', $notification->data['title']);
        $this->assertSame(route('student.exit-permits.show', $permit), $notification->data['url']);
    }

    public function test_rejecting_exit_permit_notifies_student_with_rejection_note(): void
    {
        $permit = $this->makePendingPermit();

        $permit->update([
            'status' => ExitPermitStatus::Rejected,
            'rejection_note' => 'Alasan tidak mendesak.',
        ]);

        $notification = $this->studentUser->notifications()->firstOrFail();
        $this->assertSame('Izin keluar ditolak', $notification->data['title']);
        $this->assertStringContainsString('Alasan tidak mendesak.', $notification->data['body']);
    }

    public function test_notification_is_not_delivered_to_other_students(): void
    {
        $this->makeScore(['final_score' => 90]);

        $otherStudent = User::where('username', 'siswa.siti')->firstOrFail();

        $this->assertSame(0, $otherStudent->unreadNotifications()->count());
    }

    public function test_student_can_open_notification_page(): void
    {
        $this->studentUser->notify(new ExitPermitDecided(
            ExitPermit::where('student_id', $this->student->id)->firstOrFail()->id
        ));

        $this->actingAs($this->studentUser)
            ->get(route('student.notifications.index'))
            ->assertOk()
            ->assertSee('Notifikasi', escape: false);
    }

    public function test_opening_notification_marks_it_read_and_redirects_to_target(): void
    {
        $permit = ExitPermit::where('student_id', $this->student->id)->firstOrFail();
        $this->studentUser->notify(new ExitPermitDecided($permit->id));

        $notificationId = $this->studentUser->notifications()->firstOrFail()->id;

        $this->assertSame(1, $this->studentUser->unreadNotifications()->count());

        $this->actingAs($this->studentUser)
            ->get(route('student.notifications.read', $notificationId))
            ->assertRedirect(route('student.exit-permits.show', $permit));

        $this->assertSame(0, $this->studentUser->unreadNotifications()->count());
    }

    public function test_student_cannot_open_notification_belonging_to_others(): void
    {
        $permit = ExitPermit::where('student_id', $this->student->id)->firstOrFail();
        $this->studentUser->notify(new ExitPermitDecided($permit->id));

        $notificationId = $this->studentUser->notifications()->firstOrFail()->id;

        $otherStudent = User::where('username', 'siswa.siti')->firstOrFail();

        $this->actingAs($otherStudent)
            ->get(route('student.notifications.read', $notificationId))
            ->assertNotFound();
    }

    public function test_mark_all_read_clears_unread_counter(): void
    {
        $permit = ExitPermit::where('student_id', $this->student->id)->firstOrFail();
        $this->studentUser->notify(new ExitPermitDecided($permit->id));

        $this->assertGreaterThan(0, $this->studentUser->unreadNotifications()->count());

        $this->actingAs($this->studentUser)
            ->post(route('student.notifications.read-all'))
            ->assertRedirect(route('student.notifications.index'))
            ->assertSessionHas('success');

        $this->assertSame(0, $this->studentUser->unreadNotifications()->count());
    }

    public function test_unread_badge_link_is_present_on_student_layout(): void
    {
        $permit = ExitPermit::where('student_id', $this->student->id)->firstOrFail();
        $this->studentUser->notify(new ExitPermitDecided($permit->id));

        $this->actingAs($this->studentUser)
            ->get(route('student.dashboard'))
            ->assertOk()
            ->assertSee(route('student.notifications.index'), escape: false);
    }

    public function test_notification_body_is_escaped_when_rendered(): void
    {
        // Catatan penolakan diisi Guru BK dan bisa memuat HTML. Halaman notifikasi
        // harus meng-escape-nya, bukan menampilkannya mentah.
        $permit = $this->makePendingPermit();

        $permit->update([
            'status' => ExitPermitStatus::Rejected,
            'rejection_note' => '<script>alert("xss")</script>',
        ]);

        $response = $this->actingAs($this->studentUser)
            ->get(route('student.notifications.index'))
            ->assertOk();

        $response->assertDontSee('<script>alert("xss")</script>', escape: false);
        $response->assertSee('&lt;script&gt;', escape: false);
    }

    /**
     * Nilai siswa untuk satu kolom buku nilai.
     *
     * @param  array<string, mixed>  $attributes
     */
    private function makeScore(array $attributes = []): GradebookScore
    {
        return GradebookScore::create(array_merge([
            'gradebook_column_id' => $this->ownColumnId(),
            'student_id' => $this->student->id,
            'raw_score' => $attributes['final_score'] ?? null,
            'final_score' => $attributes['final_score'] ?? null,
            'max_score_snapshot' => 100,
            'source' => 'MANUAL',
            'graded_by' => $this->teacherUser->teacherProfile->id,
            'graded_at' => now(),
        ], $attributes));
    }

    private function makePendingPermit(): ExitPermit
    {
        return ExitPermit::create([
            'student_id' => $this->student->id,
            'reason_id' => ExitPermitReason::where('is_active', true)->firstOrFail()->id,
            'reason_detail' => 'Keperluan kesehatan.',
            'planned_exit_at' => now()->addHour(),
            'planned_return_at' => now()->addHours(3),
            'status' => ExitPermitStatus::Pending,
        ]);
    }

    private function ownAssignmentId(): int
    {
        $classIds = $this->student->classEnrollments()->where('status', 'ACTIVE')->pluck('class_id');

        return TeachingAssignment::whereIn('class_id', $classIds)->firstOrFail()->id;
    }

    /**
     * Kolom buku nilai yang belum memiliki nilai untuk siswa ini, supaya penulisan
     * nilai oleh guru benar-benar tersimpan baru dan bukan mengubah nilai lama.
     */
    private function ownColumnId(): int
    {
        $gradebookIds = $this->studentGradebooks();

        $taken = GradebookScore::query()
            ->where('student_id', $this->student->id)
            ->pluck('gradebook_column_id')
            ->all();

        return GradebookColumn::query()
            ->whereIn('gradebook_id', $gradebookIds)
            ->whereNotIn('id', $taken)
            ->firstOrFail()->id;
    }

    /**
     * @return array<int, int>
     */
    private function studentGradebooks(): array
    {
        return Gradebook::query()
            ->whereHas('students', fn ($query) => $query->where('student_id', $this->student->id))
            ->pluck('id')
            ->all();
    }
}
