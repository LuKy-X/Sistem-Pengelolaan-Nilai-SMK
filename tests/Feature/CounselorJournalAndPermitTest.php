<?php

namespace Tests\Feature;

use App\Enums\AttendanceStatus;
use App\Enums\ExitPermitStatus;
use App\Models\ClassEnrollment;
use App\Models\ClassJournal;
use App\Models\ExitPermit;
use App\Models\ExitPermitReason;
use App\Models\JournalAttendance;
use App\Models\LessonPeriod;
use App\Models\SchoolClass;
use App\Models\StudentProfile;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CounselorJournalAndPermitTest extends TestCase
{
    use RefreshDatabase;

    protected User $counselor;

    protected SchoolClass $counseledClass;

    protected SchoolClass $foreignClass;

    protected LessonPeriod $period1;

    protected LessonPeriod $period2;

    protected LessonPeriod $period5;

    protected LessonPeriod $breakPeriod;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();

        $this->counselor = User::where('username', 'bk.dewi')->firstOrFail();

        // Kelas assayed: satu yang diampu, satu yang bukan (harus punya penugasan mengajar aktif).
        $classesWithAssignments = SchoolClass::query()
            ->where('is_active', true)
            ->whereHas('teachingAssignments', fn ($query) => $query->where('is_active', true))
            ->orderBy('id')
            ->get();

        $this->counseledClass = $classesWithAssignments->first();
        $this->foreignClass = $classesWithAssignments->last();
        $this->counselor->counseledClasses()->sync([$this->counseledClass->id]);

        $this->period1 = LessonPeriod::where('period_number', 1)->firstOrFail();
        $this->period2 = LessonPeriod::where('period_number', 2)->firstOrFail();
        $this->period5 = LessonPeriod::where('period_number', 5)->firstOrFail();
        $this->breakPeriod = LessonPeriod::where('is_break', true)->firstOrFail();
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    public function test_bk_sidebar_exposes_absensi_kelas_menu(): void
    {
        $this->actingAs($this->counselor)
            ->get(route('counselor.dashboard'))
            ->assertOk()
            ->assertSee(route('counselor.journals.index'), escape: false)
            ->assertSee('Absensi Kelas');
    }

    public function test_counselor_sees_class_selection_page_for_absensi(): void
    {
        $this->actingAs($this->counselor)
            ->get(route('counselor.journals.index'))
            ->assertOk()
            ->assertSee('Daftar Kelas Binaan')
            ->assertSee($this->counseledClass->name);
    }

    public function test_counselor_can_store_journal_for_counseled_class(): void
    {
        ClassJournal::query()->delete();
        $student = $this->enrolledStudent($this->counseledClass);
        $today = now()->format('Y-m-d');

        $this->actingAs($this->counselor)
            ->post(route('counselor.journals.store'), [
                'class_id' => $this->counseledClass->id,
                'journal_date' => $today,
                'start_period_id' => $this->period1->id,
                'end_period_id' => $this->period2->id,
                'material' => 'BimbinganKarir dan Konseling Kelompok',
                'hadir_count' => 20,
                'sakit_count' => 1,
                'izin_count' => 0,
                'alpha_count' => 0,
                'absences' => [
                    ['student_id' => $student->id, 'status' => 'SAKIT', 'note' => 'Demam'],
                ],
            ])
            ->assertRedirect(route('counselor.journals.index', [
                'class_id' => $this->counseledClass->id,
                'date' => $today,
            ]))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('class_journals', [
            'material' => 'BimbinganKarir dan Konseling Kelompok',
        ]);

        $this->assertDatabaseHas('journal_attendances', [
            'student_id' => $student->id,
            'status' => AttendanceStatus::Sick->value,
            'note' => 'Demam',
        ]);
    }

    public function test_counselor_cannot_store_journal_for_a_class_outside_counseling(): void
    {
        ClassJournal::query()->delete();

        $this->actingAs($this->counselor)
            ->post(route('counselor.journals.store'), [
                'class_id' => $this->foreignClass->id,
                'journal_date' => now()->format('Y-m-d'),
                'start_period_id' => $this->period1->id,
                'end_period_id' => $this->period2->id,
                'material' => 'Jurnal di luar kelas binaan',
                'hadir_count' => 10,
            ])
            ->assertForbidden();

        $this->assertDatabaseMissing('class_journals', ['material' => 'Jurnal di luar kelas binaan']);
    }

    public function test_counselor_cannot_use_a_break_period_for_journal(): void
    {
        ClassJournal::query()->delete();

        $this->actingAs($this->counselor)
            ->from(route('counselor.journals.index', ['class_id' => $this->counseledClass->id]))
            ->post(route('counselor.journals.store'), [
                'class_id' => $this->counseledClass->id,
                'journal_date' => now()->format('Y-m-d'),
                'start_period_id' => $this->period1->id,
                'end_period_id' => $this->breakPeriod->id,
                'material' => 'Absensi saat istirahat',
                'hadir_count' => 10,
            ])
            ->assertSessionHasErrors('start_period_id');

        $this->assertDatabaseMissing('class_journals', ['material' => 'Absensi saat istirahat']);
    }

    public function test_counselor_cannot_fill_two_journals_on_the_same_period(): void
    {
        ClassJournal::query()->delete();
        $today = now()->format('Y-m-d');

        ClassJournal::create([
            'teaching_assignment_id' => $this->counseledClass->teachingAssignments()->value('id'),
            'journal_date' => $today,
            'start_period_id' => $this->period1->id,
            'end_period_id' => $this->period2->id,
            'material' => 'Jurnal bentrok',
            'created_by' => $this->counseledClass->teachingAssignments()->value('teacher_id'),
        ]);

        $this->actingAs($this->counselor)
            ->from(route('counselor.journals.index', ['class_id' => $this->counseledClass->id]))
            ->post(route('counselor.journals.store'), [
                'class_id' => $this->counseledClass->id,
                'journal_date' => $today,
                'start_period_id' => $this->period2->id,
                'end_period_id' => $this->period5->id,
                'material' => 'Jurnal kedua',
                'hadir_count' => 10,
            ])
            ->assertSessionHasErrors('start_period_id');

        $this->assertDatabaseMissing('class_journals', ['material' => 'Jurnal kedua']);
    }

    public function test_counselor_cannot_fill_journal_for_a_future_date(): void
    {
        ClassJournal::query()->delete();

        $this->actingAs($this->counselor)
            ->from(route('counselor.journals.index', ['class_id' => $this->counseledClass->id]))
            ->post(route('counselor.journals.store'), [
                'class_id' => $this->counseledClass->id,
                'journal_date' => now()->addWeek()->format('Y-m-d'),
                'start_period_id' => $this->period1->id,
                'end_period_id' => $this->period2->id,
                'material' => 'Jurnal minggu depan',
                'hadir_count' => 10,
            ])
            ->assertSessionHasErrors('journal_date');

        $this->assertDatabaseMissing('class_journals', ['material' => 'Jurnal minggu depan']);
    }

    public function test_counselor_cannot_mark_attendance_for_a_student_outside_the_class(): void
    {
        ClassJournal::query()->delete();
        JournalAttendance::query()->delete();

        $foreignStudent = StudentProfile::query()
            ->whereHas('currentEnrollment', fn ($query) => $query->where('class_id', $this->foreignClass->id))
            ->firstOrFail();

        $this->actingAs($this->counselor)
            ->from(route('counselor.journals.index', ['class_id' => $this->counseledClass->id]))
            ->post(route('counselor.journals.store'), [
                'class_id' => $this->counseledClass->id,
                'journal_date' => now()->format('Y-m-d'),
                'start_period_id' => $this->period1->id,
                'end_period_id' => $this->period2->id,
                'material' => 'Absensi lintas kelas',
                'hadir_count' => 10,
                'absences' => [
                    ['student_id' => $foreignStudent->id, 'status' => 'ALPHA'],
                ],
            ])
            ->assertSessionHasErrors('absences');

        $this->assertDatabaseMissing('class_journals', ['material' => 'Absensi lintas kelas']);
    }

    public function test_attendance_summary_is_derived_from_real_enrollment_not_stale_notes(): void
    {
        ClassJournal::query()->delete();
        JournalAttendance::query()->delete();

        $student = $this->enrolledStudent($this->counseledClass);

        // Catatan sengaja berisi rekap lama yang tidak cocok dengan data siswa aktual.
        $journal = ClassJournal::create([
            'teaching_assignment_id' => $this->counseledClass->teachingAssignments()->where('is_active', true)->value('id'),
            'journal_date' => now()->subDay()->format('Y-m-d'),
            'start_period_id' => $this->period1->id,
            'end_period_id' => $this->period2->id,
            'material' => 'Jurnal dengan ringkasan basi',
            'notes' => "Catatan lama.\nHadir: 34 | Sakit: 1 | Izin: 0 | Alpha: 0",
            'created_by' => $this->counseledClass->teachingAssignments()->where('is_active', true)->value('teacher_id'),
        ]);

        JournalAttendance::create([
            'journal_id' => $journal->id,
            'student_id' => $student->id,
            'status' => AttendanceStatus::Sick,
        ]);

        $activeCount = ClassEnrollment::where('class_id', $this->counseledClass->id)->where('status', 'ACTIVE')->count();

        $journal->refresh()->load('attendances');

        $this->assertSame(1, $journal->sakit_count);
        $this->assertSame(0, $journal->izin_count);
        $this->assertSame(0, $journal->alpha_count);
        $this->assertSame($activeCount - 1, $journal->hadir_count);
        $this->assertNotSame(34, $journal->hadir_count);
    }

    public function test_journal_without_absence_rows_counts_every_active_student_as_present(): void
    {
        ClassJournal::query()->delete();
        JournalAttendance::query()->delete();

        $journal = $this->createJournal($this->counseledClass, 'Sesi tanpa absensi tercatat', now()->subDays(3));
        $activeCount = ClassEnrollment::where('class_id', $this->counseledClass->id)->where('status', 'ACTIVE')->count();

        $journal->refresh()->load('attendances');

        $this->assertSame($activeCount, $journal->hadir_count);
    }

    public function test_counselor_can_list_all_journals_of_counseled_classes(): void
    {
        ClassJournal::query()->delete();

        $journal = $this->createJournal($this->counseledClass, 'Persiapan Ujian Nasional', now()->subDays(2));

        $this->actingAs($this->counselor)
            ->get(route('counselor.journals.history'))
            ->assertOk()
            ->assertSee('Riwayat Jurnal Kelas')
            ->assertSee('Persiapan Ujian Nasional')
            ->assertSee($this->counseledClass->name);

        $this->actingAs($this->counselor)
            ->get(route('counselor.journals.history', ['q' => 'Persiapan']))
            ->assertOk()
            ->assertSee('Persiapan Ujian Nasional');

        $this->actingAs($this->counselor)
            ->get(route('counselor.journals.history', ['q' => 'Tidak Ada Jurnal Ini']))
            ->assertOk()
            ->assertDontSee('Persiapan Ujian Nasional');

        $this->assertSame($journal->teachingAssignment->class_id, $this->counseledClass->id);
    }

    public function test_counselor_history_does_not_leak_journals_of_uncounseled_classes(): void
    {
        ClassJournal::query()->delete();

        $this->createJournal($this->foreignClass, 'Jurnal Kelas Luar Binaan', now()->subDay());

        $this->actingAs($this->counselor)
            ->get(route('counselor.journals.history'))
            ->assertOk()
            ->assertDontSee('Jurnal Kelas Luar Binaan');
    }

    public function test_counselor_can_open_journal_detail_for_counseled_class(): void
    {
        ClassJournal::query()->delete();
        $student = $this->enrolledStudent($this->counseledClass);
        $journal = $this->createJournal($this->counseledClass, 'Materi Uji Coba', now()->subDay());

        JournalAttendance::create([
            'journal_id' => $journal->id,
            'student_id' => $student->id,
            'status' => AttendanceStatus::Absent,
            'note' => 'Tidak mengerjakan tugas',
        ]);

        $this->actingAs($this->counselor)
            ->get(route('counselor.journals.show', $journal))
            ->assertOk()
            ->assertSee('Materi Uji Coba')
            ->assertSee($student->full_name)
            ->assertSee('Tidak mengerjakan tugas');
    }

    public function test_counselor_cannot_open_journal_detail_of_uncounseled_class(): void
    {
        ClassJournal::query()->delete();
        $journal = $this->createJournal($this->foreignClass, 'Jurnal Rahasia Kelas Luar', now()->subDay());

        $this->actingAs($this->counselor)
            ->get(route('counselor.journals.show', $journal))
            ->assertForbidden();
    }

    public function test_pending_permit_row_lets_counselor_open_detail_before_deciding(): void
    {
        $permit = $this->pendingPermit();

        $this->actingAs($this->counselor)
            ->get(route('counselor.exit-permits.index', ['status' => 'PENDING']))
            ->assertOk()
            ->assertSee('Cek Detail')
            ->assertSee(route('counselor.exit-permits.show', $permit), escape: false);

        $this->actingAs($this->counselor)
            ->get(route('counselor.exit-permits.show', $permit))
            ->assertOk()
            ->assertSee('Jam Kembali Disetujui');
    }

    public function test_counselor_approves_permit_with_self_determined_return_time(): void
    {
        $permit = $this->pendingPermit([
            'planned_return_at' => now()->addHours(4),
        ]);

        $returnAt = now()->addHour()->startOfMinute();

        $this->actingAs($this->counselor)
            ->post(route('counselor.exit-permits.approve', $permit), [
                'approved_exit_at' => $permit->planned_exit_at->format('Y-m-d\TH:i'),
                'approved_return_at' => $returnAt->format('Y-m-d\TH:i'),
                'approval_note' => 'Kembali paling lambat pukul '.($returnAt->format('H.i')).'.',
            ])
            ->assertRedirect(route('counselor.exit-permits.index'))
            ->assertSessionHas('success');

        $permit->refresh();

        $this->assertSame(ExitPermitStatus::Approved, $permit->status);
        $this->assertTrue($permit->approved_return_at->equalTo($returnAt));
        $this->assertTrue($permit->effectiveReturnAt()->equalTo($returnAt));
        $this->assertDatabaseHas('audit_logs', [
            'action' => 'EXIT_PERMIT_APPROVED',
            'auditable_id' => $permit->id,
        ]);
    }

    public function test_approved_return_time_must_be_after_the_approved_exit_time(): void
    {
        $permit = $this->pendingPermit([
            'planned_exit_at' => now(),
            'planned_return_at' => now()->addHours(3),
        ]);

        $this->actingAs($this->counselor)
            ->from(route('counselor.exit-permits.show', $permit))
            ->post(route('counselor.exit-permits.approve', $permit), [
                'approved_exit_at' => now()->format('Y-m-d\TH:i'),
                'approved_return_at' => now()->subHour()->format('Y-m-d\TH:i'),
            ])
            ->assertSessionHasErrors('approved_return_at');

        $this->assertSame(ExitPermitStatus::Pending, $permit->refresh()->status);
    }

    public function test_approved_return_time_must_be_after_the_planned_exit_time(): void
    {
        $permit = $this->pendingPermit([
            'planned_exit_at' => now(),
            'planned_return_at' => now()->addHours(3),
        ]);

        $this->actingAs($this->counselor)
            ->from(route('counselor.exit-permits.show', $permit))
            ->post(route('counselor.exit-permits.approve', $permit), [
                'approved_return_at' => now()->subDay()->format('Y-m-d\TH:i'),
            ])
            ->assertSessionHasErrors('approved_return_at');

        $this->assertSame(ExitPermitStatus::Pending, $permit->refresh()->status);
    }

    public function test_return_after_the_bk_approved_deadline_marks_permit_as_late(): void
    {
        $permit = $this->pendingPermit([
            'planned_return_at' => now()->addHours(4),
        ]);

        $this->actingAs($this->counselor)
            ->post(route('counselor.exit-permits.approve', $permit), [
                'approved_exit_at' => $permit->planned_exit_at->format('Y-m-d\TH:i'),
                'approved_return_at' => now()->addMinutes(30)->format('Y-m-d\TH:i'),
            ])
            ->assertSessionHas('success');

        Carbon::setTestNow(now()->addMinutes(45));

        $this->actingAs($this->counselor)
            ->post(route('counselor.exit-permits.complete', $permit))
            ->assertRedirect(route('counselor.exit-permits.index'))
            ->assertSessionHas('success');

        $this->assertSame(ExitPermitStatus::Late, $permit->refresh()->status);
    }

    public function test_return_before_the_bk_approved_deadline_marks_permit_as_completed(): void
    {
        $permit = $this->pendingPermit([
            'planned_return_at' => now()->subHour(),
        ]);

        $this->actingAs($this->counselor)
            ->post(route('counselor.exit-permits.approve', $permit), [
                'approved_exit_at' => $permit->planned_exit_at->format('Y-m-d\TH:i'),
                'approved_return_at' => now()->addHour()->format('Y-m-d\TH:i'),
            ]);

        $this->actingAs($this->counselor)
            ->post(route('counselor.exit-permits.complete', $permit));

        $this->assertSame(ExitPermitStatus::Completed, $permit->refresh()->status);
    }

    protected function enrolledStudent(SchoolClass $schoolClass): StudentProfile
    {
        return ClassEnrollment::where('class_id', $schoolClass->id)
            ->where('status', 'ACTIVE')
            ->firstOrFail()
            ->student;
    }

    protected function createJournal(SchoolClass $schoolClass, string $material, Carbon $date): ClassJournal
    {
        $assignment = $schoolClass->teachingAssignments()->where('is_active', true)->firstOrFail();

        return ClassJournal::create([
            'teaching_assignment_id' => $assignment->id,
            'journal_date' => $date->format('Y-m-d'),
            'start_period_id' => $this->period1->id,
            'end_period_id' => $this->period2->id,
            'material' => $material,
            'notes' => 'Hadir: 30 | Sakit: 0 | Izin: 0 | Alpha: 0',
            'created_by' => $assignment->teacher_id,
        ]);
    }

    protected function pendingPermit(array $overrides = []): ExitPermit
    {
        $student = $this->enrolledStudent($this->counseledClass);

        return ExitPermit::create(array_merge([
            'student_id' => $student->id,
            'reason_id' => ExitPermitReason::firstOrFail()->id,
            'reason_detail' => 'Izin Accompanyi orang tua berobat ke puskesmas '.random_int(1000, 9999).'.',
            'requested_at' => now()->subMinutes(20),
            'planned_exit_at' => now(),
            'planned_return_at' => now()->addHours(2),
            'status' => ExitPermitStatus::Pending,
        ], $overrides));
    }
}
