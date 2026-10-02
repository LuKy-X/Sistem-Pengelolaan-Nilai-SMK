<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\AlumniProfile;
use App\Models\ClassEnrollment;
use App\Models\Department;
use App\Models\GradeLevel;
use App\Models\LessonPeriod;
use App\Models\Role;
use App\Models\SchoolClass;
use App\Models\Semester;
use App\Models\StudentProfile;
use App\Models\Subject;
use App\Models\TeacherProfile;
use App\Models\TeachingAssignment;
use App\Models\TeachingSchedule;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AcademicRevisionTest extends TestCase
{
    use RefreshDatabase;

    private User $adminUser;

    private AcademicYear $year;

    private Semester $semester;

    private Department $dept;

    private GradeLevel $levelXII;

    protected function setUp(): void
    {
        parent::setUp();

        Role::firstOrCreate(['code' => 'ADMIN'], ['name' => 'Admin']);
        Role::firstOrCreate(['code' => 'TEACHER'], ['name' => 'Guru']);
        Role::firstOrCreate(['code' => 'STUDENT'], ['name' => 'Siswa']);

        $this->adminUser = User::factory()->create();
        $this->adminUser->roles()->attach(Role::where('code', 'ADMIN')->first());

        $this->year = AcademicYear::create([
            'name' => '2025/2026',
            'start_date' => '2025-07-01',
            'end_date' => '2026-06-30',
            'is_active' => true,
        ]);

        $this->semester = Semester::create([
            'academic_year_id' => $this->year->id,
            'name' => 'Semester Ganjil',
            'semester_number' => 1,
            'start_date' => '2025-07-01',
            'end_date' => '2025-12-31',
            'is_active' => true,
        ]);

        $this->dept = Department::create([
            'code' => 'TKJ',
            'name' => 'Teknik Komputer dan Jaringan',
            'is_active' => true,
        ]);

        $this->levelXII = GradeLevel::firstOrCreate(['code' => 'XII'], ['name' => 'Tingkat XII']);
    }

    public function test_graduating_class_leaves_class_empty_and_creates_alumni_profile(): void
    {
        $classXII = SchoolClass::create([
            'academic_year_id' => $this->year->id,
            'department_id' => $this->dept->id,
            'grade_level_id' => $this->levelXII->id,
            'code' => 'XII-TKJ-1',
            'name' => 'XII TKJ 1',
            'is_active' => true,
        ]);

        $student = StudentProfile::factory()->create([
            'full_name' => 'Bintang Lulusan',
            'nis' => '99001',
            'status' => 'ACTIVE',
        ]);

        $enrollment = ClassEnrollment::create([
            'class_id' => $classXII->id,
            'student_id' => $student->id,
            'start_date' => '2025-07-15',
            'status' => 'ACTIVE',
        ]);

        $response = $this->actingAs($this->adminUser)->post(route('admin.academic.classes.promote'), [
            'source_class_id' => $classXII->id,
            'action_type' => 'graduate',
            'student_ids' => [$student->id],
            'promotion_date' => '2026-06-30',
            'deactivate_source_class' => 1,
        ]);

        $response->assertRedirect(route('admin.academic.classes.index'));
        $response->assertSessionHas('success');

        // Status update verification
        $this->assertEquals('COMPLETED', $enrollment->fresh()->status);
        $this->assertEquals('GRADUATED', $student->fresh()->status);

        // Alumni record created
        $alumni = AlumniProfile::where('student_id', $student->id)->first();
        $this->assertNotNull($alumni);
        $this->assertEquals(2026, $alumni->graduation_year);

        // Class show view should NOT list the completed student and should show empty state
        $showResponse = $this->actingAs($this->adminUser)->get(route('admin.academic.classes.show', $classXII));
        $showResponse->assertOk();
        $showResponse->assertSee('Belum ada siswa aktif yang terdaftar di rombel ini');
        $showResponse->assertDontSee($student->nis);
    }

    public function test_scheduling_enforces_weekly_hours_limit_per_class(): void
    {
        // Setup jam pelajaran
        $p1 = LessonPeriod::create(['period_number' => 1, 'label' => 'Jam 1', 'start_time' => '07:30:00', 'end_time' => '08:15:00']);
        $p2 = LessonPeriod::create(['period_number' => 2, 'label' => 'Jam 2', 'start_time' => '08:15:00', 'end_time' => '09:00:00']);
        $p3 = LessonPeriod::create(['period_number' => 3, 'label' => 'Jam 3', 'start_time' => '09:15:00', 'end_time' => '10:00:00']);
        $p4 = LessonPeriod::create(['period_number' => 4, 'label' => 'Jam 4', 'start_time' => '10:00:00', 'end_time' => '10:45:00']);
        $p5 = LessonPeriod::create(['period_number' => 5, 'label' => 'Jam 5', 'start_time' => '10:45:00', 'end_time' => '11:30:00']);

        $teacher = TeacherProfile::factory()->create();
        $subject = Subject::create([
            'code' => 'PROG',
            'name' => 'Pemrograman Dasar',
            'is_active' => true,
        ]);

        $classA = SchoolClass::create([
            'academic_year_id' => $this->year->id,
            'department_id' => $this->dept->id,
            'grade_level_id' => $this->levelXII->id,
            'code' => 'XII-TKJ-A',
            'name' => 'XII TKJ A',
            'is_active' => true,
        ]);

        $classB = SchoolClass::create([
            'academic_year_id' => $this->year->id,
            'department_id' => $this->dept->id,
            'grade_level_id' => $this->levelXII->id,
            'code' => 'XII-TKJ-B',
            'name' => 'XII TKJ B',
            'is_active' => true,
        ]);

        // Guru ditugaskan 4 jam/minggu di Kelas A
        $assignmentA = TeachingAssignment::create([
            'teacher_id' => $teacher->id,
            'subject_id' => $subject->id,
            'class_id' => $classA->id,
            'semester_id' => $this->semester->id,
            'weekly_hours' => 4,
            'is_active' => true,
        ]);

        // Guru juga ditugaskan 4 jam/minggu di Kelas B
        $assignmentB = TeachingAssignment::create([
            'teacher_id' => $teacher->id,
            'subject_id' => $subject->id,
            'class_id' => $classB->id,
            'semester_id' => $this->semester->id,
            'weekly_hours' => 4,
            'is_active' => true,
        ]);

        // Jadwalkan 4 jam di Kelas A (2 jam slot 1-2, 2 jam slot 3-4 di hari Senin)
        $scheduleA1 = $this->actingAs($this->adminUser)->post(route('admin.academic.schedules.store'), [
            'teaching_assignment_id' => $assignmentA->id,
            'day_of_week' => 1,
            'start_period_id' => $p1->id,
            'end_period_id' => $p2->id, // 2 jam
        ]);
        $scheduleA1->assertRedirect();
        $this->assertEquals(1, TeachingSchedule::where('teaching_assignment_id', $assignmentA->id)->count());

        $scheduleA2 = $this->actingAs($this->adminUser)->post(route('admin.academic.schedules.store'), [
            'teaching_assignment_id' => $assignmentA->id,
            'day_of_week' => 1,
            'start_period_id' => $p3->id,
            'end_period_id' => $p4->id, // 2 jam, total 4 jam
        ]);
        $scheduleA2->assertRedirect();
        $this->assertEquals(2, TeachingSchedule::where('teaching_assignment_id', $assignmentA->id)->count());

        // Coba jadwalkan jam ke-5 di Kelas A -> Harus gagal karena limit 4 jam sudah penuh!
        $scheduleA3 = $this->actingAs($this->adminUser)->post(route('admin.academic.schedules.store'), [
            'teaching_assignment_id' => $assignmentA->id,
            'day_of_week' => 2,
            'start_period_id' => $p1->id,
            'end_period_id' => $p2->id, // mencoba 2 jam tambahan
        ]);
        $scheduleA3->assertSessionHas('error');
        // Jumlah jadwal Kelas A tetap 2 (tidak bertambah)
        $this->assertEquals(2, TeachingSchedule::where('teaching_assignment_id', $assignmentA->id)->count());

        // Guru yang sama masih bisa dijadwalkan di Kelas B hingga 4 jam!
        $scheduleB1 = $this->actingAs($this->adminUser)->post(route('admin.academic.schedules.store'), [
            'teaching_assignment_id' => $assignmentB->id,
            'day_of_week' => 2,
            'start_period_id' => $p1->id,
            'end_period_id' => $p4->id, // 4 jam langsung (p1 s/d p4)
        ]);
        $scheduleB1->assertRedirect();
        $this->assertEquals(1, TeachingSchedule::where('teaching_assignment_id', $assignmentB->id)->count());
    }

    public function test_realtime_unique_check_endpoint(): void
    {
        $existingUser = User::factory()->create(['username' => 'existing_user']);
        $existingStudent = StudentProfile::factory()->create(['nis' => '12345']);

        // Check existing username -> available: false
        $response1 = $this->actingAs($this->adminUser)->postJson(route('admin.check-unique'), [
            'type' => 'username',
            'value' => 'existing_user',
        ]);
        $response1->assertOk();
        $response1->assertJson(['available' => false]);

        // Check new username -> available: true
        $response2 = $this->actingAs($this->adminUser)->postJson(route('admin.check-unique'), [
            'type' => 'username',
            'value' => 'brand_new_user',
        ]);
        $response2->assertOk();
        $response2->assertJson(['available' => true]);

        // Check existing NIS -> available: false
        $response3 = $this->actingAs($this->adminUser)->postJson(route('admin.check-unique'), [
            'type' => 'nis',
            'value' => '12345',
        ]);
        $response3->assertOk();
        $response3->assertJson(['available' => false]);

        // Check existing NIS with ignore_id -> available: true
        $response4 = $this->actingAs($this->adminUser)->postJson(route('admin.check-unique'), [
            'type' => 'nis',
            'value' => '12345',
            'ignore_id' => $existingStudent->id,
        ]);
        $response4->assertOk();
        $response4->assertJson(['available' => true]);
    }

    public function test_schedule_cannot_overwrite_or_overlap_existing_slot_in_same_class(): void
    {
        $class = SchoolClass::create([
            'academic_year_id' => $this->year->id,
            'department_id' => $this->dept->id,
            'grade_level_id' => $this->levelXII->id,
            'code' => 'XII-TKJ-SCHEDULE-1',
            'name' => 'XII TKJ Schedule 1',
            'is_active' => true,
        ]);

        $teacher1 = TeacherProfile::factory()->create();
        $teacher2 = TeacherProfile::factory()->create();

        $sub1 = Subject::create(['code' => 'MAT-SCH1', 'name' => 'Matematika', 'is_active' => true]);
        $sub2 = Subject::create(['code' => 'BIN-SCH1', 'name' => 'Bahasa Indonesia', 'is_active' => true]);

        $assign1 = TeachingAssignment::create([
            'academic_year_id' => $this->year->id,
            'semester_id' => $this->semester->id,
            'class_id' => $class->id,
            'subject_id' => $sub1->id,
            'teacher_id' => $teacher1->id,
            'weekly_hours' => 6,
        ]);

        $assign2 = TeachingAssignment::create([
            'academic_year_id' => $this->year->id,
            'semester_id' => $this->semester->id,
            'class_id' => $class->id,
            'subject_id' => $sub2->id,
            'teacher_id' => $teacher2->id,
            'weekly_hours' => 6,
        ]);

        $p1 = LessonPeriod::firstOrCreate(['period_number' => 101], ['label' => 'Jam 101', 'start_time' => '07:00:00', 'end_time' => '07:45:00', 'is_break' => false]);
        $p2 = LessonPeriod::firstOrCreate(['period_number' => 102], ['label' => 'Jam 102', 'start_time' => '07:45:00', 'end_time' => '08:30:00', 'is_break' => false]);
        $p3 = LessonPeriod::firstOrCreate(['period_number' => 103], ['label' => 'Jam 103', 'start_time' => '08:30:00', 'end_time' => '09:15:00', 'is_break' => false]);

        // Simpan jadwal pertama: Hari Senin (1), Jam 101 s/d 102
        $res1 = $this->actingAs($this->adminUser)->postJson(route('admin.academic.schedules.store'), [
            'teaching_assignment_id' => $assign1->id,
            'day_of_week' => 1,
            'start_period_id' => $p1->id,
            'end_period_id' => $p2->id,
        ]);
        $res1->assertOk();
        $this->assertEquals(1, TeachingSchedule::where('teaching_assignment_id', $assign1->id)->count());

        // Coba timpa jadwal pada jam yang sama (Jam 102) di hari yang sama untuk kelas ini -> Harus gagal (422)
        $res2 = $this->actingAs($this->adminUser)->postJson(route('admin.academic.schedules.store'), [
            'teaching_assignment_id' => $assign2->id,
            'day_of_week' => 1,
            'start_period_id' => $p2->id,
            'end_period_id' => $p3->id,
        ]);
        $res2->assertStatus(422);
        $res2->assertJson(['success' => false]);
        $this->assertStringContainsString('Bentrok jadwal', $res2->json('message'));
        $this->assertEquals(0, TeachingSchedule::where('teaching_assignment_id', $assign2->id)->count());
    }

    public function test_schedule_cannot_be_placed_on_break_period(): void
    {
        $class = SchoolClass::create([
            'academic_year_id' => $this->year->id,
            'department_id' => $this->dept->id,
            'grade_level_id' => $this->levelXII->id,
            'code' => 'XII-TKJ-BREAK-1',
            'name' => 'XII TKJ Break 1',
            'is_active' => true,
        ]);

        $teacher = TeacherProfile::factory()->create();
        $sub = Subject::create(['code' => 'SEJ-SCH1', 'name' => 'Sejarah', 'is_active' => true]);

        $assign = TeachingAssignment::create([
            'academic_year_id' => $this->year->id,
            'semester_id' => $this->semester->id,
            'class_id' => $class->id,
            'subject_id' => $sub->id,
            'teacher_id' => $teacher->id,
            'weekly_hours' => 4,
        ]);

        $pNormal = LessonPeriod::firstOrCreate(['period_number' => 201], ['label' => 'Jam 201', 'start_time' => '07:00:00', 'end_time' => '07:45:00', 'is_break' => false]);
        $pBreak = LessonPeriod::firstOrCreate(['period_number' => 202], ['label' => 'Istirahat Pagi', 'start_time' => '07:45:00', 'end_time' => '08:15:00', 'is_break' => true]);

        // Coba tempatkan jadwal langsung pada jam istirahat -> 422
        $resBreak = $this->actingAs($this->adminUser)->postJson(route('admin.academic.schedules.store'), [
            'teaching_assignment_id' => $assign->id,
            'day_of_week' => 2,
            'start_period_id' => $pBreak->id,
            'end_period_id' => $pBreak->id,
        ]);
        $resBreak->assertStatus(422);
        $this->assertStringContainsString('jam istirahat', $resBreak->json('message'));

        // Coba tempatkan rentang yang melintasi jam istirahat -> 422
        $resSpan = $this->actingAs($this->adminUser)->postJson(route('admin.academic.schedules.store'), [
            'teaching_assignment_id' => $assign->id,
            'day_of_week' => 2,
            'start_period_id' => $pNormal->id,
            'end_period_id' => $pBreak->id,
        ]);
        $resSpan->assertStatus(422);
        $this->assertStringContainsString('jam istirahat', $resSpan->json('message'));
    }

    public function test_can_create_and_manage_break_period_with_time_format(): void
    {
        // Tambah jam istirahat baru dengan format time HH:MM
        $resStore = $this->actingAs($this->adminUser)->postJson(route('admin.academic.schedules.periods.store'), [
            'label' => 'Istirahat',
            'start_time' => '11:45',
            'end_time' => '12:30',
            'is_break' => 1,
        ]);
        $resStore->assertOk();
        $this->assertDatabaseHas('lesson_periods', [
            'period_number' => null,
            'label' => 'Istirahat',
            'start_time' => '11:45:00',
            'end_time' => '12:30:00',
            'is_break' => 1,
        ]);

        $breakPeriod = LessonPeriod::where('is_break', true)->where('start_time', '11:45:00')->first();

        // Update jam istirahat
        $resUpdate = $this->actingAs($this->adminUser)->putJson(route('admin.academic.schedules.periods.update', $breakPeriod), [
            'label' => 'Istirahat',
            'start_time' => '11:45',
            'end_time' => '12:45',
            'is_break' => 1,
        ]);
        $resUpdate->assertOk();
        $this->assertEquals('12:45:00', $breakPeriod->fresh()->end_time);

        // Hapus jam istirahat
        $resDel = $this->actingAs($this->adminUser)->deleteJson(route('admin.academic.schedules.periods.destroy', $breakPeriod));
        $resDel->assertOk();
        $this->assertDatabaseMissing('lesson_periods', ['id' => $breakPeriod->id]);
    }

    public function test_break_period_does_not_increment_lesson_period_numbers(): void
    {
        LessonPeriod::truncate();

        // Jam 1
        $p1 = LessonPeriod::create(['label' => 'Jam Ke-1', 'start_time' => '07:00:00', 'end_time' => '07:45:00', 'is_break' => false]);
        // Istirahat
        $pBreak = LessonPeriod::create(['label' => 'Istirahat', 'start_time' => '07:45:00', 'end_time' => '08:15:00', 'is_break' => true]);
        // Jam 2 (setelah istirahat tetap jadi Jam ke-2, bukan Jam ke-3)
        $p2 = LessonPeriod::create(['label' => 'Jam Ke-2', 'start_time' => '08:15:00', 'end_time' => '09:00:00', 'is_break' => false]);

        LessonPeriod::resequence();

        $this->assertEquals(1, $p1->fresh()->period_number);
        $this->assertNull($pBreak->fresh()->period_number);
        $this->assertEquals('Istirahat', $pBreak->fresh()->label);
        $this->assertEquals(2, $p2->fresh()->period_number);
    }

    public function test_can_insert_period_between_existing_periods_and_subsequent_periods_shift(): void
    {
        LessonPeriod::truncate();

        // Buat Jam 1 (07:00 - 07:45) dan Jam 2 (08:15 - 09:00), dengan celah waktu 07:45 - 08:15
        $p1 = LessonPeriod::create(['sort_order' => 1, 'label' => 'Jam Ke-1', 'start_time' => '07:00:00', 'end_time' => '07:45:00', 'is_break' => false]);
        $p2 = LessonPeriod::create(['sort_order' => 2, 'label' => 'Jam Ke-2', 'start_time' => '08:15:00', 'end_time' => '09:00:00', 'is_break' => false]);
        LessonPeriod::resequence();

        $this->assertEquals(1, $p1->fresh()->period_number);
        $this->assertEquals(2, $p2->fresh()->period_number);

        // Sisipkan jam baru di bawah Jam 1 (insert_position = after, target_period_id = $p1->id)
        $res = $this->actingAs($this->adminUser)->postJson(route('admin.academic.schedules.periods.store'), [
            'insert_position' => 'after',
            'target_period_id' => $p1->id,
            'start_time' => '07:45',
            'end_time' => '08:15',
            'is_break' => 0,
        ]);
        $res->assertOk();

        // Cek bahwa sekarang ada 3 periode
        $all = LessonPeriod::orderBy('sort_order')->get();
        $this->assertCount(3, $all);

        // Period 1 tetap Jam ke-1
        $this->assertEquals(1, $all[0]->period_number);
        $this->assertEquals($p1->id, $all[0]->id);

        // Period baru disisipkan menjadi Jam ke-2
        $this->assertEquals(2, $all[1]->period_number);

        // Jam lama (p2) bergeser menjadi Jam ke-3!
        $this->assertEquals(3, $all[2]->period_number);
        $this->assertEquals($p2->id, $all[2]->id);
    }

    public function test_cannot_create_or_update_period_with_overlapping_time_range(): void
    {
        LessonPeriod::truncate();

        // Jam 1: 07:00 - 09:00
        $p1 = LessonPeriod::create([
            'sort_order' => 1,
            'label' => 'Jam Ke-1',
            'start_time' => '07:00:00',
            'end_time' => '09:00:00',
            'is_break' => false,
        ]);

        // 1. Coba tambah jam yang menabrak sebagian (08:00 - 10:00) -> 422
        $res1 = $this->actingAs($this->adminUser)->postJson(route('admin.academic.schedules.periods.store'), [
            'start_time' => '08:00',
            'end_time' => '10:00',
            'is_break' => 0,
        ]);
        $res1->assertStatus(422);
        $this->assertStringContainsString('bertabrakan', $res1->json('message'));

        // 2. Coba tambah jam yang berada di dalam jam yang sudah ada (07:30 - 08:30) -> 422
        $res2 = $this->actingAs($this->adminUser)->postJson(route('admin.academic.schedules.periods.store'), [
            'start_time' => '07:30',
            'end_time' => '08:30',
            'is_break' => 0,
        ]);
        $res2->assertStatus(422);
        $this->assertStringContainsString('bertabrakan', $res2->json('message'));

        // 3. Tambah jam di luar waktu tersebut (09:00 - 10:00) -> Sukses
        $res3 = $this->actingAs($this->adminUser)->postJson(route('admin.academic.schedules.periods.store'), [
            'start_time' => '09:00',
            'end_time' => '10:00',
            'is_break' => 0,
        ]);
        $res3->assertOk();

        $p2 = LessonPeriod::where('start_time', '09:00:00')->first();

        // 4. Coba update jam 2 agar overlap dengan jam 1 (misal 08:30 - 10:00) -> 422
        $res4 = $this->actingAs($this->adminUser)->putJson(route('admin.academic.schedules.periods.update', $p2), [
            'start_time' => '08:30',
            'end_time' => '10:00',
            'is_break' => 0,
        ]);
        $res4->assertStatus(422);
        $this->assertStringContainsString('bertabrakan', $res4->json('message'));
    }

    public function test_export_schedule_pdf_and_excel_endpoints(): void
    {
        $class = SchoolClass::create([
            'academic_year_id' => $this->year->id,
            'department_id' => $this->dept->id,
            'grade_level_id' => $this->levelXII->id,
            'code' => 'XII-TKJ-EX',
            'name' => 'XII TKJ Export',
            'is_active' => true,
        ]);

        $teacher = TeacherProfile::factory()->create([
            'full_name' => 'Guru Export PDF',
            'nip' => '1987654321',
        ]);

        $subject = Subject::create([
            'code' => 'EXP1',
            'name' => 'Pemrograman Web Export',
        ]);

        $assignment = TeachingAssignment::create([
            'academic_year_id' => $this->year->id,
            'semester_id' => $this->semester->id,
            'teacher_id' => $teacher->id,
            'subject_id' => $subject->id,
            'class_id' => $class->id,
            'weekly_hours' => 4,
        ]);

        $p1 = LessonPeriod::create([
            'sort_order' => 1,
            'period_number' => 1,
            'label' => 'Jam Ke-1',
            'start_time' => '07:00:00',
            'end_time' => '07:45:00',
            'is_break' => false,
        ]);

        $p2 = LessonPeriod::create([
            'sort_order' => 2,
            'period_number' => 2,
            'label' => 'Jam Ke-2',
            'start_time' => '07:45:00',
            'end_time' => '08:30:00',
            'is_break' => false,
        ]);

        TeachingSchedule::create([
            'teaching_assignment_id' => $assignment->id,
            'day_of_week' => 1, // Senin
            'start_period_id' => $p1->id,
            'end_period_id' => $p2->id,
            'room' => 'Lab Komputer 1',
        ]);

        // 1. Test Export PDF
        $resPdf = $this->actingAs($this->adminUser)->get(route('admin.academic.schedules.export.pdf', [
            'class_id' => $class->id,
            'days_count' => 5,
        ]));
        $resPdf->assertOk();
        $resPdf->assertSee('JADWAL PELAJARAN MINGGUAN KELAS XII TKJ EXPORT');
        $resPdf->assertSee('Pemrograman Web Export');
        $resPdf->assertSee('Guru Export PDF');
        $resPdf->assertSee('Lab Komputer 1');

        // 2. Test Export Excel
        $resExcel = $this->actingAs($this->adminUser)->get(route('admin.academic.schedules.export.excel', [
            'class_id' => $class->id,
            'days_count' => 5,
        ]));
        $resExcel->assertOk();
        $resExcel->assertHeader('Content-Type', 'application/vnd.ms-excel; charset=utf-8');
        $this->assertStringContainsString('attachment; filename="Jadwal_Pelajaran_XII_TKJ_Export_', $resExcel->headers->get('Content-Disposition'));
        $resExcel->assertSee('Pemrograman Web Export');
        $resExcel->assertSee('Guru Export PDF');
    }
}
