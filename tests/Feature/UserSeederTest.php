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
}
