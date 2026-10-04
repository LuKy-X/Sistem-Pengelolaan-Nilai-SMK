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
use App\Models\Semester;
use App\Models\StudentProfile;
use App\Models\Subject;
use App\Models\TeacherProfile;
use App\Models\TeachingAssignment;
use App\Models\TeachingSchedule;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CounselorJournalAndPermitTest extends TestCase
{
    use RefreshDatabase;

    protected User $counselor;

    protected TeacherProfile $counselorTeacher;

    protected SchoolClass $counseledClass;

    protected SchoolClass $foreignClass;

    /**
     * Penugasan mengajar milik Guru BK pada kelas binaannya.
     */
    protected TeachingAssignment $counselorAssignment;

    /**
     * Jadwal mengajar Guru BK, dibuat pada hari agar absensi hari ini diizinkan.
     */
    protected TeachingSchedule $counselorSchedule;

    protected LessonPeriod $period1;

    protected LessonPeriod $period2;

    protected LessonPeriod $period5;

    protected LessonPeriod $breakPeriod;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();

        $this->counselor = User::where('username', 'bk.dewi')->firstOrFail();

        // Akun BK harus memiliki profil guru agar bisa punya penugasan mengajar & jadwal.
        TeacherProfile::ensureCounselorProfiles();
        $this->counselorTeacher = $this->counselor->teacherProfile()->firstOrFail();

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

        // Guru BK harus punya penugasan mengajar + jadwal, sama seperti Guru pengajar.
        $this->counselorAssignment = TeachingAssignment::create([
            'teacher_id' => $this->counselorTeacher->id,
            'subject_id' => Subject::where('code', 'BIN')->firstOrFail()->id,
            'class_id' => $this->counseledClass->id,
            'semester_id' => Semester::where('is_active', true)->firstOrFail()->id,
            'weekly_hours' => 2,
            'is_active' => true,
        ]);

        $this->counselorSchedule = TeachingSchedule::create([
            'teaching_assignment_id' => $this->counselorAssignment->id,
            'day_of_week' => now()->dayOfWeekIso,
            'start_period_id' => $this->period1->id,
            'end_period_id' => $this->period2->id,
            'room' => 'Ruang BK',
        ]);
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

    public function test_counselor_sees_own_teaching_classes_like_a_teacher(): void
    {
        $this->actingAs($this->counselor)
            ->get(route('counselor.journals.index'))
            ->assertOk()
            ->assertSee('Daftar Kelas')
            ->assertSee($this->counseledClass->name)
            ->assertSee(route('counselor.journals.attendance'), escape: false)
            ->assertSee(route('counselor.journals.history'), escape: false);
    }

    public function test_journal_pages_render_navigation_links(): void
    {
        // Halaman jurnal harus menampilkan tautan navigasi yang bisa digunakan.
        foreach ([
            route('counselor.journals.index'),
            route('counselor.journals.attendance'),
            route('counselor.journals.history'),
        ] as $url) {
            $this->actingAs($this->counselor)
                ->get($url)
                ->assertOk()
                ->assertSee($url, escape: false);
        }
    }

    public function test_index_renders_when_an_assignment_has_no_schedule_yet(): void
    {
        // Penugasan tanpa jadwal memicu ringkasan "Belum Ada Jadwal" pada kartu kelas.
        TeachingAssignment::create([
            'teacher_id' => $this->counselorTeacher->id,
            'subject_id' => Subject::where('code', 'MTK')->firstOrFail()->id,
            'class_id' => $this->foreignClass->id,
            'semester_id' => Semester::where('is_active', true)->firstOrFail()->id,
            'weekly_hours' => 2,
            'is_active' => true,
        ]);

        $this->actingAs($this->counselor)
            ->get(route('counselor.journals.index'))
            ->assertOk()
            ->assertSee('Belum Ada Jadwal')
            ->assertSee($this->foreignClass->name);
    }

    public function test_counselor_form_is_blocked_on_a_day_outside_own_schedule(): void
    {
        $offScheduleDate = now()->subDay()->format('Y-m-d');

        $this->actingAs($this->counselor)
            ->get(route('counselor.journals.index', [
                'assignment_id' => $this->counselorAssignment->id,
                'date' => $offScheduleDate,
            ]))
            ->assertOk()
            ->assertSee('Di Luar Jadwal Mengajar')
            ->assertSee('Belum Dapat Diisi')
            ->assertSee('Terkunci');
    }

    public function test_counselor_form_is_available_on_a_scheduled_day(): void
    {
        $this->actingAs($this->counselor)
            ->get(route('counselor.journals.index', [
                'assignment_id' => $this->counselorAssignment->id,
                'date' => now()->format('Y-m-d'),
            ]))
            ->assertOk()
            ->assertSee('Manajemen Absensi')
            ->assertDontSee('Di Luar Jadwal Mengajar')
            ->assertDontSee('Belum Dapat Diisi');
    }

    public function test_counselor_can_store_journal_on_a_scheduled_day(): void
    {
        ClassJournal::query()->delete();
        $student = $this->enrolledStudent($this->counseledClass);
        $today = now()->format('Y-m-d');

        $this->actingAs($this->counselor)
            ->post(route('counselor.journals.store'), [
                'teaching_assignment_id' => $this->counselorAssignment->id,
                'journal_date' => $today,
                'start_period_id' => $this->period1->id,
                'end_period_id' => $this->period2->id,
                'material' => 'Bimbingan Karier dan Konseling Kelompok',
                'hadir_count' => 20,
                'sakit_count' => 1,
                'izin_count' => 0,
                'alpha_count' => 0,
                'absences' => [
                    ['student_id' => $student->id, 'status' => 'SAKIT', 'note' => 'Demam'],
                ],
            ])
            ->assertRedirect(route('counselor.journals.index', [
                'assignment_id' => $this->counselorAssignment->id,
                'date' => $today,
            ]))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('class_journals', [
            'teaching_assignment_id' => $this->counselorAssignment->id,
            'schedule_id' => $this->counselorSchedule->id,
            'created_by' => $this->counselorTeacher->id,
            'material' => 'Bimbingan Karier dan Konseling Kelompok',
        ]);

        $this->assertDatabaseHas('journal_attendances', [
            'student_id' => $student->id,
            'status' => AttendanceStatus::Sick->value,
            'note' => 'Demam',
        ]);
    }

    public function test_counselor_cannot_store_journal_outside_own_schedule(): void
    {
        ClassJournal::query()->delete();
        $offScheduleDate = now()->subDay()->format('Y-m-d');

        $this->actingAs($this->counselor)
            ->from(route('counselor.journals.index', [
                'assignment_id' => $this->counselorAssignment->id,
                'date' => $offScheduleDate,
            ]))
            ->post(route('counselor.journals.store'), [
                'teaching_assignment_id' => $this->counselorAssignment->id,
                'journal_date' => $offScheduleDate,
                'start_period_id' => $this->period1->id,
                'end_period_id' => $this->period2->id,
                'material' => 'Absensi di luar jadwal',
                'hadir_count' => 10,
            ])
            ->assertSessionHasErrors('journal_date');

        $this->assertDatabaseMissing('class_journals', ['material' => 'Absensi di luar jadwal']);
    }

    public function test_counselor_cannot_store_journal_for_another_teachers_assignment(): void
    {
        ClassJournal::query()->delete();
        $foreignAssignment = $this->foreignClass->teachingAssignments()->where('is_active', true)->firstOrFail();

        $this->actingAs($this->counselor)
            ->post(route('counselor.journals.store'), [
                'teaching_assignment_id' => $foreignAssignment->id,
                'journal_date' => now()->format('Y-m-d'),
                'start_period_id' => $this->period1->id,
                'end_period_id' => $this->period2->id,
                'material' => 'Jurnal milik guru lain',
                'hadir_count' => 10,
            ])
            ->assertForbidden();

        $this->assertDatabaseMissing('class_journals', ['material' => 'Jurnal milik guru lain']);
    }

    public function test_counselor_cannot_use_a_break_period_for_journal(): void
    {
        ClassJournal::query()->delete();

        $this->actingAs($this->counselor)
            ->from(route('counselor.journals.index', ['assignment_id' => $this->counselorAssignment->id]))
            ->post(route('counselor.journals.store'), [
                'teaching_assignment_id' => $this->counselorAssignment->id,
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
            'teaching_assignment_id' => $this->counselorAssignment->id,
            'journal_date' => $today,
            'start_period_id' => $this->period1->id,
            'end_period_id' => $this->period2->id,
            'material' => 'Jurnal bentrok',
            'created_by' => $this->counselorTeacher->id,
        ]);

        $this->actingAs($this->counselor)
            ->from(route('counselor.journals.index', ['assignment_id' => $this->counselorAssignment->id]))
            ->post(route('counselor.journals.store'), [
                'teaching_assignment_id' => $this->counselorAssignment->id,
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
            ->from(route('counselor.journals.index', ['assignment_id' => $this->counselorAssignment->id]))
            ->post(route('counselor.journals.store'), [
                'teaching_assignment_id' => $this->counselorAssignment->id,
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
            ->from(route('counselor.journals.index', ['assignment_id' => $this->counselorAssignment->id]))
            ->post(route('counselor.journals.store'), [
                'teaching_assignment_id' => $this->counselorAssignment->id,
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

    public function test_counselor_can_update_and_delete_own_journal(): void
    {
        ClassJournal::query()->delete();
        $journal = $this->createCounselorJournal('Jurnal Milik Sendiri', now());

        $this->actingAs($this->counselor)
            ->put(route('counselor.journals.update', $journal), [
                'start_period_id' => $this->period1->id,
                'end_period_id' => $this->period2->id,
                'material' => 'Jurnal Milik Sendiri (Direvisi)',
                'hadir_count' => 30,
            ])
            ->assertRedirect(route('counselor.journals.index', [
                'assignment_id' => $this->counselorAssignment->id,
                'date' => now()->format('Y-m-d'),
            ]));

        $this->assertDatabaseHas('class_journals', [
            'id' => $journal->id,
            'material' => 'Jurnal Milik Sendiri (Direvisi)',
        ]);

        $this->actingAs($this->counselor)
            ->delete(route('counselor.journals.destroy', $journal));

        $this->assertDatabaseMissing('class_journals', ['id' => $journal->id]);
    }

    public function test_counselor_cannot_modify_journal_of_another_teacher(): void
    {
        ClassJournal::query()->delete();
        $journal = $this->createJournal($this->counseledClass, 'Jurnal Guru Pengajar', now());

        $this->actingAs($this->counselor)
            ->put(route('counselor.journals.update', $journal), [
                'start_period_id' => $this->period1->id,
                'end_period_id' => $this->period2->id,
                'material' => 'Diubah oleh BK',
                'hadir_count' => 5,
            ])
            ->assertForbidden();

        $this->actingAs($this->counselor)
            ->delete(route('counselor.journals.destroy', $journal))
            ->assertForbidden();

        $this->assertDatabaseHas('class_journals', [
            'id' => $journal->id,
            'material' => 'Jurnal Guru Pengajar',
        ]);
    }

    public function test_counselor_can_read_attendance_of_counseled_class(): void
    {
        ClassJournal::query()->delete();
        $student = $this->enrolledStudent($this->counseledClass);
        $journal = $this->createJournal($this->counseledClass, 'Materi Matematika Kelas Binaan', now());

        JournalAttendance::create([
            'journal_id' => $journal->id,
            'student_id' => $student->id,
            'status' => AttendanceStatus::Absent,
        ]);

        $this->actingAs($this->counselor)
            ->get(route('counselor.journals.attendance', [
                'class_id' => $this->counseledClass->id,
                'date' => now()->format('Y-m-d'),
            ]))
            ->assertOk()
            ->assertSee('Lihat Absensi Kelas')
            ->assertSee('Mode lihat saja')
            ->assertSee('Materi Matematika Kelas Binaan')
            ->assertSee($student->full_name)
            // Halaman read-only tidak boleh menyediakan formulir absensi.
            ->assertDontSee('Manajemen Absensi')
            ->assertDontSee('formManajemenAbsensi', escape: false)
            ->assertDontSee('name="teaching_assignment_id"', escape: false);
    }

    public function test_counselor_can_read_attendance_for_all_classes_without_schedule(): void
    {
        ClassJournal::query()->delete();

        // Kelas yang TIDAK diampu dan TIDAK diajar: tidak boleh tampil.
        $this->createJournal($this->foreignClass, 'Materi Kelas Luar Cakupan', now()->subDay());

        // Journal dibuat pada hari tanpa jadwal BK, sehingga absensinya tetap
        // harus terlihat di tab read-only.
        $unscheduledDate = now()->subWeek()->next(Carbon::MONDAY);
        $this->createJournal($this->counseledClass, 'Absensi Hari Tanpa Jadwal', $unscheduledDate);

        $this->actingAs($this->counselor)
            ->get(route('counselor.journals.attendance', [
                'class_id' => '',
                'date' => $unscheduledDate->format('Y-m-d'),
            ]))
            ->assertOk()
            ->assertSee('Semua Kelas')
            ->assertSee('Absensi Hari Tanpa Jadwal')
            ->assertDontSee('Materi Kelas Luar Cakupan')
            // Tetap read-only: tidak ada formulir untuk kelas mana pun.
            ->assertDontSee('formManajemenAbsensi', escape: false);
    }

    public function test_counselor_attendance_tab_includes_taught_classes(): void
    {
        ClassJournal::query()->delete();

        // Kelas yang hanya diajar sebagai pengajar, bukan kelas binaaan.
        $this->counselor->counseledClasses()->sync([$this->counseledClass->id]);

        $taughtClass = SchoolClass::query()
            ->where('is_active', true)
            ->whereNotIn('id', [$this->counseledClass->id])
            ->firstOrFail();

        TeachingAssignment::create([
            'teacher_id' => $this->counselorTeacher->id,
            'subject_id' => Subject::where('code', 'MTK')->firstOrFail()->id,
            'class_id' => $taughtClass->id,
            'semester_id' => Semester::where('is_active', true)->firstOrFail()->id,
            'weekly_hours' => 2,
            'is_active' => true,
        ]);

        $this->createJournal($taughtClass, 'Materi Kelas Yang Diajar', now());

        $this->actingAs($this->counselor)
            ->get(route('counselor.journals.attendance', ['date' => now()->format('Y-m-d')]))
            ->assertOk()
            ->assertSee($taughtClass->name)
            ->assertSee('Materi Kelas Yang Diajar');
    }

    public function test_counselor_cannot_read_attendance_of_uncounseled_class(): void
    {
        ClassJournal::query()->delete();
        $this->createJournal($this->foreignClass, 'Materi Rahasia Kelas Luar', now());

        $this->actingAs($this->counselor)
            ->get(route('counselor.journals.attendance', [
                'class_id' => $this->foreignClass->id,
                'date' => now()->format('Y-m-d'),
            ]))
            ->assertOk()
            ->assertDontSee('Materi Rahasia Kelas Luar');
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

    protected function createCounselorJournal(string $material, Carbon $date): ClassJournal
    {
        return ClassJournal::create([
            'teaching_assignment_id' => $this->counselorAssignment->id,
            'schedule_id' => $this->counselorSchedule->id,
            'journal_date' => $date->format('Y-m-d'),
            'start_period_id' => $this->period1->id,
            'end_period_id' => $this->period2->id,
            'material' => $material,
            'notes' => 'Hadir: 30 | Sakit: 0 | Izin: 0 | Alpha: 0',
            'created_by' => $this->counselorTeacher->id,
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
