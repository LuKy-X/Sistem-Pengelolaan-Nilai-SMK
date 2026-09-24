<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            ['code' => 'ADMIN', 'name' => 'Administrator'],
            ['code' => 'TEACHER', 'name' => 'Guru Pengajar'],
            ['code' => 'STUDENT', 'name' => 'Siswa'],
            ['code' => 'COUNSELOR', 'name' => 'Guru Bimbingan Konseling (BK)'],
        ];

        foreach ($roles as $role) {
            Role::firstOrCreate(['code' => $role['code']], $role);
        }
    }
}
