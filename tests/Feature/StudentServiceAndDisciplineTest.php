<?php

namespace Tests\Feature;

use App\Enums\DisciplineCategoryType;
use App\Enums\ExitPermitStatus;
use App\Models\AcademicYear;
use App\Models\AuditLog;
use App\Models\DisciplineCategory;
use App\Models\DisciplineRecord;
use App\Models\DisciplineSetting;
use App\Models\ExitPermit;
use App\Models\ExitPermitReason;
use App\Models\Role;
use App\Models\StaffProfile;
use App\Models\StudentProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Tests\TestCase;

class StudentServiceAndDisciplineTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Role::firstOrCreate(['code' => 'ADMIN'], ['name' => 'Admin']);
        Role::firstOrCreate(['code' => 'TEACHER'], ['name' => 'Guru']);
        Role::firstOrCreate(['code' => 'STUDENT'], ['name' => 'Siswa']);
        Role::firstOrCreate(['code' => 'COUNSELOR'], ['name' => 'BK']);
    }

    public function test_student_cannot_view_another_students_exit_permit(): void
    {
        $reason = ExitPermitReason::create(['name' => 'Sakit ke Faskes']);

        // Siswa A
        $userA = User::factory()->create();
        $userA->roles()->attach(Role::where('code', 'STUDENT')->first());
        $studentA = StudentProfile::factory()->create(['user_id' => $userA->id]);

        // Siswa B
        $userB = User::factory()->create();
        $userB->roles()->attach(Role::where('code', 'STUDENT')->first());
        $studentB = StudentProfile::factory()->create(['user_id' => $userB->id]);

        $permitA = ExitPermit::create([
            'student_id' => $studentA->id,
            'reason_id' => $reason->id,
            'reason_detail' => 'Demam tinggi',
            'requested_at' => now(),
            'planned_exit_at' => now(),
            'planned_return_at' => now()->addHours(2),
            'status' => ExitPermitStatus::Pending,
        ]);

        // Siswa A boleh melihat izin miliknya
        $this->assertTrue(Gate::forUser($userA)->allows('view', $permitA));

        // Siswa B DILARANG melihat izin Siswa A
        $this->assertFalse(Gate::forUser($userB)->allows('view', $permitA));
    }

    public function test_counselor_can_process_exit_permit_and_timer_computes(): void
    {
        $counselorUser = User::factory()->create();
        $counselorUser->roles()->attach(Role::where('code', 'COUNSELOR')->first());
        $counselorProfile = StaffProfile::factory()->create(['user_id' => $counselorUser->id]);

        $student = StudentProfile::factory()->create();
        $reason = ExitPermitReason::create(['name' => 'Urusan Keluarga']);

        $plannedReturn = now()->addMinutes(90);

        $permit = ExitPermit::create([
            'student_id' => $student->id,
            'reason_id' => $reason->id,
            'reason_detail' => 'Izin menghadiri pernikahan saudara',
            'requested_at' => now(),
            'planned_exit_at' => now(),
            'planned_return_at' => $plannedReturn,
            'status' => ExitPermitStatus::Pending,
        ]);

        // Counselor berhak memproses izin
        $this->assertTrue(Gate::forUser($counselorUser)->allows('process', $permit));

        // BK menyetujui izin
        $permit->update([
            'status' => ExitPermitStatus::Approved,
            'approved_at' => now(),
            'approved_by' => $counselorProfile->id,
            'actual_exit_at' => now(),
        ]);

        $this->assertEquals(ExitPermitStatus::Approved, $permit->status);
        $this->assertGreaterThan(0, $permit->minutes_remaining);
        $this->assertFalse($permit->isOverdue());
    }

    public function test_discipline_records_mutate_points_and_calculate_balance(): void
    {
        $year = AcademicYear::factory()->create();
        $admin = User::factory()->create();
        $student = StudentProfile::factory()->create();

        // Setting: Poin awal 100
        $setting = DisciplineSetting::create([
            'academic_year_id' => $year->id,
            'initial_points' => 100,
            'minimum_points' => 0,
            'sp1_threshold' => 50,
        ]);

        $catViolation = DisciplineCategory::create([
            'name' => 'Terlambat Masuk Sekolah',
            'type' => DisciplineCategoryType::Violation,
            'default_points' => -10,
        ]);

        $catReward = DisciplineCategory::create([
            'name' => 'Juara Lomba Tingkat Kabupaten',
            'type' => DisciplineCategoryType::Reward,
            'default_points' => 15,
        ]);

        // Siswa melakukan pelanggaran (-10)
        DisciplineRecord::create([
            'student_id' => $student->id,
            'academic_year_id' => $year->id,
            'category_id' => $catViolation->id,
            'points_delta' => -10,
            'occurred_at' => now(),
            'description' => 'Terlambat 20 menit',
            'created_by' => $admin->id,
        ]);

        // Siswa mendapatkan prestasi (+15)
        DisciplineRecord::create([
            'student_id' => $student->id,
            'academic_year_id' => $year->id,
            'category_id' => $catReward->id,
            'points_delta' => 15,
            'occurred_at' => now(),
            'description' => 'Juara 1 LKS Web Technologies',
            'created_by' => $admin->id,
        ]);

        // Kalkulasi saldo poin: initial_points + SUM(points_delta)
        $totalDelta = $student->disciplineRecords()
            ->where('academic_year_id', $year->id)
            ->sum('points_delta');

        $currentBalance = $setting->initial_points + $totalDelta;

        // 100 - 10 + 15 = 105
        $this->assertEquals(5, $totalDelta);
        $this->assertEquals(105, $currentBalance);
    }

    public function test_audit_log_records_critical_events(): void
    {
        $user = User::factory()->create();
        $student = StudentProfile::factory()->create();

        $log = AuditLog::create([
            'user_id' => $user->id,
            'action' => 'DISCIPLINE_RECORD_CREATED',
            'auditable_type' => StudentProfile::class,
            'auditable_id' => $student->id,
            'old_values' => ['points' => 100],
            'new_values' => ['points' => 90, 'violation' => 'Terlambat'],
            'ip_address' => '127.0.0.1',
            'user_agent' => 'PHPUnit Test Agent',
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'id' => $log->id,
            'action' => 'DISCIPLINE_RECORD_CREATED',
            'auditable_id' => $student->id,
        ]);
        $this->assertEquals(100, $log->old_values['points']);
        $this->assertEquals(90, $log->new_values['points']);
    }
}
