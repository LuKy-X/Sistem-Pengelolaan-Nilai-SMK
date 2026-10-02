<?php

namespace Tests\Feature;

use App\Enums\AppealDecision;
use App\Enums\AssessmentStatus;
use App\Enums\ExitPermitStatus;
use App\Enums\SubmissionStatus;
use App\Models\Assessment;
use App\Models\AssessmentSubmission;
use App\Models\ExitPermit;
use App\Models\ExitPermitAppeal;
use App\Models\ExitPermitReason;
use App\Models\Gradebook;
use App\Models\SchoolClass;
use App\Models\StudentProfile;
use App\Models\TeachingAssignment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class StudentPortalTest extends TestCase
{
    use RefreshDatabase;

    protected User $studentUser;

    protected StudentProfile $student;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();

        $this->studentUser = User::where('username', 'siswa.ahmad')->firstOrFail();
        $this->student = $this->studentUser->studentProfile;
    }

    public function test_student_is_redirected_to_student_dashboard_after_login(): void
    {
        $this->post('/login', [
            'login' => 'siswa.ahmad',
            'password' => 'password123',
        ])->assertRedirect(route('student.dashboard'));

        $this->assertAuthenticated();
    }

    public function test_authenticated_student_can_reach_dashboard_from_root(): void
    {
        // "/" milik landing page publik, jadi tidak boleh diubah dari modul siswa.
        // Yang dipastikan modul ini: halaman publik tetap memuat jalan keluar ke
        // dashboard, dan dashboard siswa sendiri tetap bisa diakses langsung.
        $this->actingAs($this->studentUser)
            ->get('/')
            ->assertOk()
            ->assertSee(route('student.dashboard'), escape: false);

        $this->actingAs($this->studentUser)
            ->get(route('student.dashboard'))
            ->assertOk()
            ->assertSee('Dashboard Siswa', escape: false);
    }

    public function test_guest_is_redirected_to_login_from_student_pages(): void
    {
        $this->get(route('student.dashboard'))->assertRedirect(route('login'));
        $this->get(route('student.assignments.index'))->assertRedirect(route('login'));
        $this->get(route('student.exit-permits.index'))->assertRedirect(route('login'));
        $this->get(route('student.discipline.index'))->assertRedirect(route('login'));
    }

    public function test_non_student_role_cannot_access_student_pages(): void
    {
        $teacher = User::where('username', 'guru.agus')->firstOrFail();

        $this->actingAs($teacher)->get(route('student.dashboard'))->assertForbidden();
        $this->actingAs($teacher)->get(route('student.grades.index'))->assertForbidden();
        $this->actingAs($teacher)->get(route('student.exit-permits.index'))->assertForbidden();
        $this->actingAs($teacher)->get(route('student.appeals.index'))->assertForbidden();
    }

    public function test_student_can_render_every_student_page(): void
    {
        $pages = [
            route('student.dashboard'),
            route('student.schedules.index'),
            route('student.grades.index'),
            route('student.assignments.index'),
            route('student.exit-permits.index'),
            route('student.exit-permits.create'),
            route('student.appeals.index'),
            route('student.discipline.index'),
            route('student.profile.index'),
        ];

        foreach ($pages as $url) {
            $this->actingAs($this->studentUser)
                ->get($url)
                ->assertOk();
        }
    }

    public function test_student_can_open_own_gradebook_but_not_others(): void
    {
        $ownGradebook = Gradebook::query()
            ->whereHas('students', fn ($query) => $query->where('student_id', $this->student->id))
            ->firstOrFail();

        $this->actingAs($this->studentUser)
            ->get(route('student.grades.show', $ownGradebook))
            ->assertOk();

        // Buku nilai kelas lain yang tidak memuat siswa ini.
        $otherClass = SchoolClass::where('code', 'XII-RPL-2')->firstOrFail();
        $otherGradebook = Gradebook::factory()->create([
            'teaching_assignment_id' => TeachingAssignment::query()
                ->where('class_id', $otherClass->id)
                ->firstOrFail()->id,
        ]);

        $this->actingAs($this->studentUser)
            ->get(route('student.grades.show', $otherGradebook))
            ->assertForbidden();
    }

    public function test_student_can_view_published_assessment_of_own_class_only(): void
    {
        $assessment = Assessment::query()
            ->where('status', AssessmentStatus::Published)
            ->whereHas('teachingAssignment', function ($query) {
                $query->whereIn('class_id', $this->student->classEnrollments()->where('status', 'ACTIVE')->pluck('class_id'));
            })
            ->firstOrFail();

        $this->actingAs($this->studentUser)
            ->get(route('student.assignments.show', $assessment))
            ->assertOk()
            ->assertSee($assessment->title);

        // Tugas kelas lain: siswa tidak terdaftar -> 403 dari AssessmentPolicy.
        $otherClass = SchoolClass::where('code', 'XII-RPL-2')->firstOrFail();
        $foreignAssessment = Assessment::factory()->create([
            'teaching_assignment_id' => TeachingAssignment::query()
                ->where('class_id', $otherClass->id)
                ->firstOrFail()->id,
        ]);

        $this->actingAs($this->studentUser)
            ->get(route('student.assignments.show', $foreignAssessment))
            ->assertForbidden();

        // Tugas draft tidak boleh terlihat siswa.
        $draft = Assessment::factory()->create([
            'teaching_assignment_id' => $assessment->teaching_assignment_id,
            'gradebook_column_id' => $assessment->gradebook_column_id,
            'status' => AssessmentStatus::Draft,
            'published_at' => null,
        ]);

        $this->actingAs($this->studentUser)
            ->get(route('student.assignments.show', $draft))
            ->assertNotFound();
    }

    public function test_student_can_submit_assignment_with_text_and_file(): void
    {
        Storage::fake('public');

        $assessment = $this->submittableAssessment();

        $this->actingAs($this->studentUser)
            ->post(route('student.assignments.submit', $assessment), [
                'content' => 'Jawaban saya untuk tugas ini.',
                'attachment' => UploadedFile::fake()->create('jawaban.pdf', 128, 'application/pdf'),
            ])
            ->assertRedirect(route('student.assignments.show', $assessment))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('assessment_submissions', [
            'assessment_id' => $assessment->id,
            'student_id' => $this->student->id,
            'status' => SubmissionStatus::Submitted->value,
            'late_minutes' => 0,
        ]);

        $submission = AssessmentSubmission::where('assessment_id', $assessment->id)->firstOrFail();
        $this->assertCount(1, $submission->media);
        Storage::disk('public')->assertExists($submission->media->first()->path);
    }

    public function test_late_submission_records_late_minutes_from_deadline(): void
    {
        $assessment = $this->submittableAssessment();
        $assessment->update(['due_at' => now()->subMinutes(90)]);

        $this->actingAs($this->studentUser)
            ->post(route('student.assignments.submit', $assessment), [
                'content' => 'Jawaban terlambat saya.',
            ])
            ->assertRedirect();

        $submission = AssessmentSubmission::where('assessment_id', $assessment->id)
            ->where('student_id', $this->student->id)
            ->firstOrFail();

        $this->assertEqualsWithDelta(90, $submission->late_minutes, 1);
    }

    public function test_reviewed_submission_cannot_be_resubmitted(): void
    {
        $assessment = $this->submittableAssessment();

        AssessmentSubmission::create([
            'assessment_id' => $assessment->id,
            'student_id' => $this->student->id,
            'submitted_at' => now()->subDay(),
            'status' => SubmissionStatus::Reviewed,
            'content' => 'Jawaban lama',
        ]);

        $this->actingAs($this->studentUser)
            ->post(route('student.assignments.submit', $assessment), [
                'content' => 'Percobaan ubah jawaban.',
            ])
            ->assertSessionHas('error');

        $this->assertDatabaseHas('assessment_submissions', [
            'assessment_id' => $assessment->id,
            'student_id' => $this->student->id,
            'content' => 'Jawaban lama',
        ]);
    }

    public function test_student_can_request_exit_permit_and_cannot_have_two_open(): void
    {
        $reason = ExitPermitReason::where('is_active', true)->firstOrFail();

        $this->actingAs($this->studentUser)
            ->post(route('student.exit-permits.store'), [
                'reason_id' => $reason->id,
                'reason_detail' => 'Keperluan keluarga mendadak bersama orang tua.',
                'planned_exit_at' => now()->addHour()->format('Y-m-d\TH:i'),
                'planned_return_at' => now()->addHours(3)->format('Y-m-d\TH:i'),
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseHas('exit_permits', [
            'student_id' => $this->student->id,
            'status' => ExitPermitStatus::Pending->value,
        ]);

        // Pengajuan kedua saat masih PENDING harus ditolak.
        $this->actingAs($this->studentUser)
            ->post(route('student.exit-permits.store'), [
                'reason_id' => $reason->id,
                'reason_detail' => 'Pengajuan kedua yang harus ditolak.',
                'planned_exit_at' => now()->addHour()->format('Y-m-d\TH:i'),
                'planned_return_at' => now()->addHours(2)->format('Y-m-d\TH:i'),
            ])
            ->assertSessionHas('error');

        // Hanya izin berstatus PENDING yang dihitung. Seeder membuat izin Completed dan
        // Rejected untuk siswa yang sama, jadi count global tidak boleh dipakai di sini.
        $this->assertSame(1, ExitPermit::where('student_id', $this->student->id)
            ->where('status', ExitPermitStatus::Pending->value)
            ->count());
    }

    public function test_exit_permit_requires_valid_reason_and_schedule(): void
    {
        $this->actingAs($this->studentUser)
            ->post(route('student.exit-permits.store'), [
                'reason_id' => 99999,
                'reason_detail' => 'singkat',
                'planned_exit_at' => now()->addHours(3)->format('Y-m-d\TH:i'),
                'planned_return_at' => now()->addHour()->format('Y-m-d\TH:i'),
            ])
            ->assertSessionHasErrors(['reason_id', 'reason_detail', 'planned_return_at']);
    }

    public function test_student_cannot_view_other_students_permit(): void
    {
        $otherStudent = StudentProfile::where('id', '!=', $this->student->id)->firstOrFail();
        $reason = ExitPermitReason::where('is_active', true)->firstOrFail();

        $permit = ExitPermit::create([
            'student_id' => $otherStudent->id,
            'reason_id' => $reason->id,
            'reason_detail' => 'Izin milik siswa lain.',
            'requested_at' => now(),
            'planned_exit_at' => now(),
            'planned_return_at' => now()->addHour(),
            'status' => ExitPermitStatus::Pending,
        ]);

        $this->actingAs($this->studentUser)
            ->get(route('student.exit-permits.show', $permit))
            ->assertForbidden();
    }

    public function test_student_can_appeal_only_for_late_permit(): void
    {
        $reason = ExitPermitReason::where('is_active', true)->firstOrFail();

        // Izin selesai tepat waktu: banding harus ditolak.
        $completed = ExitPermit::create([
            'student_id' => $this->student->id,
            'reason_id' => $reason->id,
            'reason_detail' => 'Izin selesai tepat waktu.',
            'requested_at' => now()->subDay(),
            'planned_exit_at' => now()->subDay(),
            'planned_return_at' => now()->subDay()->addHour(),
            'actual_return_at' => now()->subDay()->addMinutes(30),
            'status' => ExitPermitStatus::Completed,
        ]);

        $this->actingAs($this->studentUser)
            ->post(route('student.exit-permits.appeal', $completed), [
                'reason' => 'Banding pada izin yang tidak terlambat.',
            ])
            ->assertSessionHas('error');

        // Izin terlambat: banding diterima.
        $late = ExitPermit::create([
            'student_id' => $this->student->id,
            'reason_id' => $reason->id,
            'reason_detail' => 'Izin terlambat kembali.',
            'requested_at' => now()->subHours(3),
            'planned_exit_at' => now()->subHours(3),
            'planned_return_at' => now()->subHour(),
            'actual_return_at' => now()->subMinutes(20),
            'status' => ExitPermitStatus::Late,
        ]);

        $this->actingAs($this->studentUser)
            ->post(route('student.exit-permits.appeal', $late), [
                'reason' => 'Antrean di klinik sangat panjang dan tidak bisa ditinggal.',
            ])
            ->assertRedirect(route('student.exit-permits.show', $late))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('exit_permit_appeals', [
            'exit_permit_id' => $late->id,
            'decision' => AppealDecision::Pending->value,
        ]);

        // Banding kedua untuk izin yang sama harus ditolak.
        $this->actingAs($this->studentUser)
            ->post(route('student.exit-permits.appeal', $late), [
                'reason' => 'Percobaan banding ganda yang harus gagal.',
            ])
            ->assertSessionHas('error');

        $this->assertEquals(1, ExitPermitAppeal::where('exit_permit_id', $late->id)->count());
    }

    public function test_student_can_update_contact_and_password(): void
    {
        $this->actingAs($this->studentUser)
            ->put(route('student.profile.update'), [
                'email' => 'ahmad.baru@smk.test',
                'phone' => '081200001111',
                'address' => 'Alamat baru siswa',
            ])
            ->assertRedirect(route('student.profile.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('users', ['id' => $this->studentUser->id, 'email' => 'ahmad.baru@smk.test']);
        $this->assertDatabaseHas('student_profiles', ['id' => $this->student->id, 'phone' => '081200001111']);

        $this->actingAs($this->studentUser->fresh())
            ->put(route('student.profile.password'), [
                'current_password' => 'password123',
                'password' => 'sandiBaru99',
                'password_confirmation' => 'sandiBaru99',
            ])
            ->assertRedirect(route('student.profile.index'))
            ->assertSessionHas('success');

        // Keluar dulu: POST /login berada di middleware "guest", jadi sesi dari
        // actingAs() sebelumnya harus diakhiri sebelum mencoba login lagi.
        $this->post('/logout')->assertRedirect();

        $this->post('/login', [
            'login' => 'siswa.ahmad',
            'password' => 'sandiBaru99',
        ])->assertRedirect(route('student.dashboard'));
    }

    public function test_student_pages_render_expected_heading(): void
    {
        // Setiap halaman harus menampilkan judul yang membedakannya, bukan sekadar 200.
        // Ini menangkap regresi seperti variabel view yang undefined lalu jadi kosong.
        $pages = [
            route('student.dashboard') => 'Dashboard Siswa',
            route('student.schedules.index') => 'Jadwal Pelajaran',
            route('student.grades.index') => 'Nilai Saya',
            route('student.assignments.index') => 'Tugas Saya',
            route('student.exit-permits.index') => 'Izin Keluar Sekolah',
            route('student.exit-permits.create') => 'Ajukan Izin Keluar',
            route('student.appeals.index') => 'Banding Keterlambatan',
            route('student.discipline.index') => 'Buku Saku Siswa',
            route('student.profile.index') => 'Profil Saya',
        ];

        foreach ($pages as $url => $heading) {
            $this->actingAs($this->studentUser)
                ->get($url)
                ->assertOk()
                ->assertSee($heading, escape: false);
        }
    }

    public function test_student_can_open_own_exit_permit_detail_page(): void
    {
        $permit = $this->student->exitPermits()->latest('requested_at')->firstOrFail();

        $this->actingAs($this->studentUser)
            ->get(route('student.exit-permits.show', $permit))
            ->assertOk()
            ->assertSee($permit->reason->name, escape: false)
            ->assertSee($permit->reason_detail, escape: false);
    }

    public function test_exit_permit_detail_shows_planned_return_time(): void
    {
        $permit = ExitPermit::create([
            'student_id' => $this->student->id,
            'reason_id' => ExitPermitReason::where('is_active', true)->firstOrFail()->id,
            'reason_detail' => 'Keperluan kesehatan yang perlu ditindaklanjuti.',
            'planned_exit_at' => now()->addHour(),
            'planned_return_at' => now()->addHours(4),
            'status' => ExitPermitStatus::Approved,
            'approved_at' => now(),
            'actual_exit_at' => now(),
        ]);

        $this->actingAs($this->studentUser)
            ->get(route('student.exit-permits.show', $permit))
            ->assertOk()
            // Timer dihitung dari planned_return_at, jadi atribut data-return-at harus ada.
            ->assertSee('data-return-at="'.$permit->planned_return_at->timestamp.'"', escape: false);
    }

    public function test_assignment_listing_supports_subject_filter_and_status_tabs(): void
    {
        $enrollment = $this->student->classEnrollments()->where('status', 'ACTIVE')->firstOrFail();
        $assignment = TeachingAssignment::where('class_id', $enrollment->class_id)->firstOrFail();
        $subjectId = $assignment->subject_id;

        $all = $this->actingAs($this->studentUser)
            ->get(route('student.assignments.index'))
            ->assertOk();
        $all->assertSee('Tugas Saya', escape: false);

        $filtered = $this->actingAs($this->studentUser)
            ->get(route('student.assignments.index', ['subject' => $subjectId]))
            ->assertOk();
        $filtered->assertSee('Tugas Saya', escape: false);

        foreach (['aktif', 'terlewat', 'selesai'] as $tab) {
            $this->actingAs($this->studentUser)
                ->get(route('student.assignments.index', ['tab' => $tab]))
                ->assertOk()
                ->assertSee('Tugas Saya', escape: false);
        }
    }

    public function test_exit_permit_listing_supports_status_filter(): void
    {
        foreach ([ExitPermitStatus::Pending, ExitPermitStatus::Completed] as $status) {
            $this->actingAs($this->studentUser)
                ->get(route('student.exit-permits.index', ['status' => $status->value]))
                ->assertOk()
                ->assertSee('Izin Keluar Sekolah', escape: false);
        }
    }

    public function test_appeal_listing_supports_decision_filter(): void
    {
        foreach ([AppealDecision::Pending, AppealDecision::Accepted, AppealDecision::Rejected] as $decision) {
            $this->actingAs($this->studentUser)
                ->get(route('student.appeals.index', ['decision' => $decision->value]))
                ->assertOk()
                ->assertSee('Banding Keterlambatan', escape: false);
        }
    }

    public function test_resubmitting_with_attachment_only_preserves_text_answer(): void
    {
        Storage::fake('public');

        $assessment = $this->submittableAssessment();

        $this->actingAs($this->studentUser)
            ->post(route('student.assignments.submit', $assessment), [
                'content' => 'Jawaban_esai_lama_yang_harus_tetap_ada.',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('assessment_submissions', [
            'assessment_id' => $assessment->id,
            'student_id' => $this->student->id,
            'content' => 'Jawaban_esai_lama_yang_harus_tetap_ada.',
        ]);

        // Kirim ulang hanya dengan lampiran: jawaban teks lama tidak boleh hilang.
        $this->actingAs($this->studentUser)
            ->post(route('student.assignments.submit', $assessment), [
                'attachment' => UploadedFile::fake()->create('tugas.pdf', 64, 'application/pdf'),
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('assessment_submissions', [
            'assessment_id' => $assessment->id,
            'student_id' => $this->student->id,
            'content' => 'Jawaban_esai_lama_yang_harus_tetap_ada.',
        ]);

        // Satu baris submission yang sama yang diperbarui (updateOrCreate),
        // jadi jumlahnya tetap satu — yang diuji adalah isi jawabannya.
        $this->assertSame(1, AssessmentSubmission::where('assessment_id', $assessment->id)
            ->where('student_id', $this->student->id)
            ->count());
    }

    public function test_gradebook_detail_renders_rubric_modal_trigger_and_target(): void
    {
        $gradebook = Gradebook::query()
            ->whereHas('students', fn ($query) => $query->where('student_id', $this->student->id))
            ->firstOrFail();

        // Delegasi modal berada di layouts/student.blade.php; tombol harus punya target
        // yang cocok dengan id modal agar rincian rubrik benar-benar bisa dibuka.
        $response = $this->actingAs($this->studentUser)
            ->get(route('student.grades.show', $gradebook))
            ->assertOk();

        $response->assertSee('data-modal-open', escape: false);
        $response->assertSee('data-modal-close', escape: false);
    }

    public function test_student_layout_has_modal_click_delegation(): void
    {
        $layout = view()->getFinder()->find('layouts.student');
        $contents = (string) file_get_contents($layout);

        $this->assertStringContainsString('[data-modal-open]', $contents);
        $this->assertStringContainsString('[data-modal-close]', $contents);
        $this->assertStringContainsString('openModal(', $contents);
        $this->assertStringContainsString('closeModal(', $contents);
    }

    /**
     * Tugas terbit dengan pengumpulan wajib di kelas siswa ini.
     */
    private function submittableAssessment(): Assessment
    {
        $assessment = Assessment::query()
            ->where('status', AssessmentStatus::Published)
            ->where('submission_required', true)
            ->whereHas('teachingAssignment', function ($query) {
                $query->whereIn('class_id', $this->student->classEnrollments()->where('status', 'ACTIVE')->pluck('class_id'));
            })
            ->whereDoesntHave('submissions', fn ($query) => $query->where('student_id', $this->student->id))
            ->first();

        if ($assessment === null) {
            $enrollment = $this->student->classEnrollments()->where('status', 'ACTIVE')->firstOrFail();
            $assignment = TeachingAssignment::where('class_id', $enrollment->class_id)->firstOrFail();

            $assessment = Assessment::factory()->create([
                'teaching_assignment_id' => $assignment->id,
                'submission_required' => true,
            ]);
        }

        return $assessment;
    }
}
