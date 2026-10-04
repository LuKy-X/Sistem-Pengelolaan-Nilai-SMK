<?php

namespace Tests\Feature;

use App\Models\TeacherProfile;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UserSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_directory_teachers_get_demo_accounts_and_profiles_idempotently(): void
    {
        $this->seed(RoleSeeder::class);
        $this->seed(UserSeeder::class);

        $teacher = User::query()->where('username', 'guru125')->firstOrFail();
        $profile = TeacherProfile::query()->where('user_id', $teacher->id)->firstOrFail();

        $this->assertSame('guru125@smk.test', $teacher->email);
        $this->assertTrue(Hash::check('password123', $teacher->password));
        $this->assertTrue($teacher->hasRole('TEACHER'));
        $this->assertSame('DIR-125', $profile->nip);
        $this->assertSame('Afif Nuruddin Maisaroh', $profile->full_name);
        $this->assertSame('MALE', $profile->gender);
        $this->assertSame(76, TeacherProfile::query()->count());

        $this->seed(UserSeeder::class);

        $this->assertSame(76, TeacherProfile::query()->count());
    }

    public function test_admin_and_students_are_created_by_user_seeder(): void
    {
        $this->seed(RoleSeeder::class);
        $this->seed(UserSeeder::class);

        $admin = User::query()->where('username', 'admin')->firstOrFail();
        $this->assertTrue($admin->hasRole('ADMIN'));
        $this->assertTrue(Hash::check('password123', $admin->password));

        $studentAhmad = User::query()->where('username', 'siswa.ahmad')->firstOrFail();
        $this->assertTrue($studentAhmad->hasRole('STUDENT'));
        $this->assertNotNull($studentAhmad->studentProfile);
        $this->assertSame('10001', $studentAhmad->studentProfile->nis);

        $studentSiti = User::query()->where('username', 'siswa.siti')->firstOrFail();
        $this->assertTrue($studentSiti->hasRole('STUDENT'));

        $studentRizky = User::query()->where('username', 'siswa.rizky')->firstOrFail();
        $this->assertTrue($studentRizky->hasRole('STUDENT'));
    }

    public function test_specified_teachers_are_assigned_as_counselors_with_proper_profiles(): void
    {
        $this->seed(RoleSeeder::class);
        $this->seed(UserSeeder::class);

        $counselorNames = [
            'Endah Dwi Sayekti',
            'Fitriyah Maimun Thofiah',
            'Puput Sinta Dewi',
        ];

        foreach ($counselorNames as $name) {
            $user = User::query()->where('name', $name)->firstOrFail();
            $this->assertTrue($user->hasRole('COUNSELOR'), "User {$name} should have COUNSELOR role");
            $this->assertTrue($user->isCounselor(), "User {$name} should return true for isCounselor()");
            $this->assertSame('counselor.dashboard', $user->dashboardRouteName());
            $this->assertNotNull($user->staffProfile, "User {$name} should have StaffProfile");
            $this->assertNotNull($user->teacherProfile, "User {$name} should have TeacherProfile");
            $this->assertTrue($user->teacherProfile->isCounselor());
        }
    }
}
