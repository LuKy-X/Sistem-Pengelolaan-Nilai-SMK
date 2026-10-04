<?php

namespace Tests\Feature;

use App\Models\StudentProfile;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Database\Seeders\StudentAccountSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class StudentAccountSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_students_without_user_get_accounts_and_role_idempotently(): void
    {
        $this->seed(RoleSeeder::class);

        // Buat 3 profil siswa tanpa akun User
        $student1 = StudentProfile::create([
            'nis' => 'TEST-001',
            'nisn' => 'NISN-001',
            'full_name' => 'Budi Santoso',
            'gender' => 'MALE',
            'status' => 'ACTIVE',
        ]);

        $student2 = StudentProfile::create([
            'nis' => 'TEST-002',
            'nisn' => 'NISN-002',
            'full_name' => 'Siti Aisyah Putri',
            'gender' => 'FEMALE',
            'status' => 'ACTIVE',
        ]);

        // Jalankan seeder
        $this->seed(StudentAccountSeeder::class);

        // Refresh model
        $student1->refresh();
        $student2->refresh();

        // 1. Verifikasi User terkait sudah dibuat
        $this->assertNotNull($student1->user_id);
        $this->assertNotNull($student2->user_id);

        $user1 = User::find($student1->user_id);
        $user2 = User::find($student2->user_id);

        $this->assertNotNull($user1);
        $this->assertNotNull($user2);

        // 2. Verifikasi Username & Email
        $this->assertSame('siswa.budi.'.$student1->id, $user1->username);
        $this->assertSame('siswa'.$student1->id.'@smk.test', $user1->email);
        $this->assertSame('Budi Santoso', $user1->name);
        $this->assertTrue($user1->is_active);
        $this->assertTrue(Hash::check('password123', $user1->password));
        $this->assertTrue($user1->hasRole('STUDENT'));

        $this->assertSame('siswa.siti.'.$student2->id, $user2->username);
        $this->assertSame('siswa'.$student2->id.'@smk.test', $user2->email);
        $this->assertTrue($user2->hasRole('STUDENT'));

        // 3. Verifikasi Idempotensi (dijalankan kedua kali tidak duplikat/error)
        $this->seed(StudentAccountSeeder::class);

        $this->assertSame(2, User::query()->where('username', 'like', 'siswa.%')->count());
        $this->assertSame(0, StudentProfile::query()->whereNull('user_id')->count());
    }
}
