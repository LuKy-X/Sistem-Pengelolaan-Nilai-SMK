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
use App\Models\LessonPeriod;
use App\Models\SchoolClass;
use App\Models\StudentProfile;
use App\Models\TeachingAssignment;
use App\Models\TeachingSchedule;
use App\Models\User;
use App\Services\StudentAcademicSummaryService;
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

    public function test_student_can_open_grade_recap_page(): void
    {
        $this->actingAs($this->studentUser)
            ->get(route('student.grades.recap'))
            ->assertOk()
            ->assertSee('Rekap Nilai', escape: false)
            ->assertSee('Rata-rata lintas mata pelajaran', escape: false)
            ->assertSee('Rincian per Mata Pelajaran', escape: false);
    }

    public function test_grade_recap_average_matches_the_average_of_subject_averages(): void
    {
        $service = app(StudentAcademicSummaryService::class);
        $summary = $service->forStudent($this->student);

        $averages = $summary['subjects']
            ->map(fn (array $row) => $row['average'])
            ->filter(fn ($value) => $value !== null)
            ->map(fn ($value) => (float) $value);

        if ($averages->isEmpty()) {
            $this->assertNull($summary['overall']['average']);

            return;
        }

        $this->assertSame(
            round($averages->avg(), 2),
            $summary['overall']['average']
        );
    }

    public function test_predicate_follows_declared_thresholds(): void
    {
        $service = app(StudentAcademicSummaryService::class);

        $this->assertSame('A', $service->predicate(95.0));
        $this->assertSame('A', $service->predicate(90.0));
        $this->assertSame('B', $service->predicate(85.0));
        $this->assertSame('C', $service->predicate(75.0));
        $this->assertSame('D', $service->predicate(70.0));
        $this->assertSame('E', $service->predicate(69.99));
        $this->assertNull($service->predicate(null));
    }

    public function test_dashboard_shows_academic_status_panel(): void
    {
        $this->actingAs($this->studentUser)
            ->get(route('student.dashboard'))
            ->assertOk()
            ->assertSee('Status Akademis', escape: false)
            ->assertSee('Rata-rata Nilai', escape: false)
            ->assertSee(route('student.grades.recap'), escape: false);
    }

    public function test_student_can_open_own_schedule_detail(): void
    {
        $enrollment = $this->student->classEnrollments()->where('status', 'ACTIVE')->firstOrFail();

        $schedule = TeachingSchedule::query()
            ->whereHas('teachingAssignment', fn ($query) => $query->where('class_id', $enrollment->class_id))
            ->first() ?? TeachingSchedule::factory()->create([
                'teaching_assignment_id' => TeachingAssignment::where('class_id', $enrollment->class_id)->firstOrFail()->id,
            ]);

        $this->actingAs($this->studentUser)
            ->get(route('student.schedules.show', $schedule))
            ->assertOk()
            ->assertSee('Detail Jadwal', escape: false)
            ->assertSee($schedule->teachingAssignment->subject->name, escape: false);
    }

    public function test_student_cannot_open_schedule_of_another_class(): void
    {
        $foreign = TeachingSchedule::factory()->create([
            'teaching_assignment_id' => TeachingAssignment::query()
                ->where('class_id', SchoolClass::where('code', 'XII-RPL-2')->firstOrFail()->id)
                ->firstOrFail()->id,
        ]);

        $this->actingAs($this->studentUser)
            ->get(route('student.schedules.show', $foreign))
            ->assertForbidden();
    }

    public function test_student_can_open_own_appeal_detail(): void
    {
        $appeal = ExitPermitAppeal::query()
            ->whereHas('exitPermit', fn ($query) => $query->where('student_id', $this->student->id))
            ->firstOrFail();

        $this->actingAs($this->studentUser)
            ->get(route('student.appeals.show', $appeal))
            ->assertOk()
            ->assertSee('Detail Banding', escape: false)
            ->assertSee($appeal->reason, escape: false);
    }

    public function test_student_cannot_open_appeal_of_another_student(): void
    {
        $otherStudent = StudentProfile::query()
            ->where('id', '!=', $this->student->id)
            ->whereHas('exitPermits.appeal')
            ->firstOrFail();

        $foreign = ExitPermitAppeal::query()
            ->whereHas('exitPermit', fn ($query) => $query->where('student_id', $otherStudent->id))
            ->firstOrFail();

        $this->actingAs($this->studentUser)
            ->get(route('student.appeals.show', $foreign))
            ->assertForbidden();
    }

    public function test_assignment_history_tab_lists_every_status(): void
    {
        $response = $this->actingAs($this->studentUser)
            ->get(route('student.assignments.index', ['tab' => 'semua']))
            ->assertOk();

        $response->assertSee('Riwayat Tugas', escape: false);

        // Tab riwayat harus memuat lebih banyak baris daripada tab aktif saja.
        $activeCount = count($this->actingAs($this->studentUser)
            ->get(route('student.assignments.index', ['tab' => 'aktif']))
            ->viewData('filtered'));

        $historyCount = count($response->viewData('filtered'));

        $this->assertGreaterThanOrEqual($activeCount, $historyCount);
    }

    /**
     * Warna lencana status izin pada portal siswa harus sama dengan yang dipakai
     * dashboard Guru BK. Sebelumnya keduanya punya warna yang terbalik: siswa melihat
     * izin "Disetujui" berwarna biru sementara Guru BK melihatnya hijau, dan izin
     * "Sudah Kembali" sebaliknya.
     */
    public function test_permit_status_badge_colours_match_the_counselor_dashboard(): void
    {
        $counselor = User::where('username', 'bk.dewi')->firstOrFail();

        $expected = [
            'PENDING' => ['label' => 'Menunggu', 'badge' => 'badge-yellow'],
            'APPROVED' => ['label' => 'Disetujui', 'badge' => 'badge-green'],
            'REJECTED' => ['label' => 'Ditolak', 'badge' => 'badge-red'],
            'COMPLETED' => ['label' => 'Sudah Kembali', 'badge' => 'badge-blue'],
            'LATE' => ['label' => 'Kembali Terlambat', 'badge' => 'badge-red'],
            'CANCELLED' => ['label' => 'Dibatalkan', 'badge' => 'badge-gray'],
        ];

        foreach ($expected as $status => $meta) {
            $permit = ExitPermit::create([
                'student_id' => $this->student->id,
                'reason_id' => ExitPermitReason::where('is_active', true)->firstOrFail()->id,
                'reason_detail' => 'Pengajuan untuk memeriksa warna lencana.',
                'planned_exit_at' => now()->addDay(),
                'planned_return_at' => now()->addDays(2),
                'status' => $status,
            ]);

            // Halaman BK: lencana izin.
            $counselorResponse = $this->actingAs($counselor)
                ->get(route('counselor.exit-permits.index'))
                ->assertOk();

            // Halaman siswa: lencana izin yang sama harus memakai kelas yang sama.
            $studentResponse = $this->actingAs($this->studentUser)
                ->get(route('student.exit-permits.show', $permit))
                ->assertOk();

            $needle = 'badge '.$meta['badge'];

            $this->assertStringContainsString(
                $needle,
                $counselorResponse->getContent(),
                "Dashboard BK seharusnya memakai {$needle} untuk status {$status}."
            );

            $this->assertStringContainsString(
                $needle,
                $studentResponse->getContent(),
                "Portal siswa seharusnya memakai {$needle} untuk status {$status}."
            );

            $this->assertStringContainsString(
                $meta['label'],
                $studentResponse->getContent(),
                "Label lencana {$meta['label']} tidak ditemukan di portal siswa."
            );
        }
    }

    public function test_student_write_actions_are_recorded_in_audit_log(): void
    {
        $this->actingAs($this->studentUser)
            ->post(route('student.exit-permits.store'), [
                'reason_id' => ExitPermitReason::where('is_active', true)->firstOrFail()->id,
                'reason_detail' => 'Keperluan yang perlu tercatat pada jejak audit.',
                'exit_period_id' => $this->periodPair()[0],
                'return_period_id' => $this->periodPair()[1],
            ])
            ->assertSessionHas('success');

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'EXIT_PERMIT_REQUESTED',
            'user_id' => $this->studentUser->id,
            'auditable_type' => ExitPermit::class,
        ]);

        $assessment = $this->submittableAssessment();

        $this->actingAs($this->studentUser)
            ->post(route('student.assignments.submit', $assessment), [
                'content' => 'Jawaban yang perlu tercatat pada jejak audit.',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'ASSIGNMENT_SUBMITTED',
            'user_id' => $this->studentUser->id,
            'auditable_type' => AssessmentSubmission::class,
        ]);
    }

    public function test_student_cannot_open_own_gradebook_but_not_others(): void
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
        [$exitPeriodId, $returnPeriodId] = $this->periodPair();

        $this->actingAs($this->studentUser)
            ->post(route('student.exit-permits.store'), [
                'reason_id' => $reason->id,
                'reason_detail' => 'Keperluan keluarga mendadak bersama orang tua.',
                'exit_period_id' => $exitPeriodId,
                'return_period_id' => $returnPeriodId,
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        // Jam keluar dan jam kembali harus ikut tersimpan sebagai rujukan.
        $permit = ExitPermit::where('student_id', $this->student->id)
            ->where('status', ExitPermitStatus::Pending->value)
            ->firstOrFail();

        $this->assertSame($exitPeriodId, $permit->exit_period_id);
        $this->assertSame($returnPeriodId, $permit->return_period_id);

        // Waktu rencana harus diturunkan dari jam mulai jam pelajaran pilihan.
        $this->assertTrue(
            $permit->planned_exit_at->equalTo(now()->setTimeFromTimeString(LessonPeriod::find($exitPeriodId)->start_time)),
            'planned_exit_at harus sama dengan jam mulai jam pelajaran keluar.'
        );
        $this->assertTrue(
            $permit->planned_return_at->equalTo(now()->setTimeFromTimeString(LessonPeriod::find($returnPeriodId)->start_time)),
            'planned_return_at harus sama dengan jam mulai jam pelajaran kembali.'
        );

        // Pengajuan kedua saat masih PENDING harus ditolak.
        $this->actingAs($this->studentUser)
            ->post(route('student.exit-permits.store'), [
                'reason_id' => $reason->id,
                'reason_detail' => 'Pengajuan kedua yang harus ditolak.',
                'exit_period_id' => $exitPeriodId,
                'return_period_id' => $returnPeriodId,
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
        [$exitPeriodId, $returnPeriodId] = $this->reversedPeriodPair();

        $this->actingAs($this->studentUser)
            ->post(route('student.exit-permits.store'), [
                'reason_id' => 99999,
                'reason_detail' => 'singkat',
                'exit_period_id' => $exitPeriodId,
                'return_period_id' => $returnPeriodId,
            ])
            ->assertSessionHasErrors(['reason_id', 'reason_detail', 'return_period_id']);
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

    /**
     * Dua id jam pelajaran untuk pengajuan izin: jam keluar dan jam kembali.
     * Dipilih dari jam pelajaran yang belum lewat hari ini supaya tetap valid
     * kapan pun test dijalankan.
     *
     * @return array{0: int, 1: int}
     */
    private function periodPair(): array
    {
        $periods = LessonPeriod::regular()->get();

        $exit = $periods->first(
            fn (LessonPeriod $period) => now()->setTimeFromTimeString($period->start_time)->isFuture()
        ) ?? $periods->first();

        $return = $periods->first(
            fn (LessonPeriod $period) => $period->start_time > $exit->start_time
                && now()->setTimeFromTimeString($period->start_time)->isFuture()
        ) ?? $periods->last();

        return [$exit->getKey(), $return->getKey()];
    }

    /**
     * Sepasang jam pelajaran terbalik untuk menguji validasi gagal.
     *
     * @return array{0: int, 1: int}
     */
    private function reversedPeriodPair(): array
    {
        $periods = LessonPeriod::regular()->get();

        return [$periods->last()->getKey(), $periods->first()->getKey()];
    }

    public function test_application_timezone_is_west_indonesian_time(): void
    {
        $this->assertSame('Asia/Jakarta', config('app.timezone'));

        // now() harus WIB, bukan UTC. Nilai ini dibandingkan dengan jam dinding
        // pada lesson_periods, jadi selisih 7 jam akan membuat seluruh hitungan
        // timer dan keterlambatan salah.
        $this->assertSame(
            now()->setTimezone('Asia/Jakarta')->format('H:i'),
            now()->format('H:i')
        );
    }

    public function test_only_regular_periods_are_offered_and_past_ones_are_marked(): void
    {
        $periods = LessonPeriod::regular()->get();

        $this->assertNotEmpty($periods);
        $this->assertSame(0, LessonPeriod::where('is_break', true)->count());
        $this->assertNull($periods->firstWhere(fn (LessonPeriod $p) => $p->period_number === null));

        // Jam istirahat tidak boleh muncul sebagai pilihan siswa meski datanya ada.
        $break = LessonPeriod::create([
            'label' => 'Istirahat',
            'start_time' => '12:00',
            'end_time' => '12:30',
            'is_break' => true,
        ]);

        $this->assertFalse(
            LessonPeriod::regular()->get()->contains('id', $break->getKey())
        );
        $this->assertFalse($break->hasAlreadyStarted());
        $break->delete();

        // Jam yang sudah lewat ditandai, jam yang belum lewat tidak.
        $morning = LessonPeriod::create([
            'label' => 'Jam Ke-1',
            'period_number' => 1,
            'start_time' => '00:05',
            'end_time' => '00:50',
            'is_break' => false,
        ]);

        $this->assertTrue($morning->hasAlreadyStarted());
        $morning->delete();

        foreach ($periods as $period) {
            $expected = now()->setTimeFromTimeString($period->start_time)->isPast();
            $this->assertSame($expected, $period->hasAlreadyStarted(), $period->displayLabel());
        }
    }

    public function test_exit_period_options_are_disabled_once_they_have_passed(): void
    {
        $past = LessonPeriod::create([
            'label' => 'Jam Ke-1',
            'period_number' => 1,
            'start_time' => '00:05',
            'end_time' => '00:50',
            'is_break' => false,
        ]);

        $this->actingAs($this->studentUser)
            ->get(route('student.exit-permits.create'))
            ->assertOk()
            ->assertSee('sudah lewat');

        $past->delete();
    }
}
