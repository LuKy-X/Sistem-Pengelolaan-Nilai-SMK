<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\StaffProfile;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Seeder khusus role BK — membuat beberapa akun Guru Bimbingan Konseling
 * beserta StaffProfile-nya. Semua password default: password123.
 */
class BkUserSeeder extends Seeder
{
    public function run(): void
    {
        if (app()->isProduction()) {
            return;
        }

        $counselorRole = Role::where('code', 'COUNSELOR')->first();

        if (! $counselorRole) {
            $this->command->error('Role COUNSELOR belum ada. Jalankan RoleSeeder terlebih dahulu.');

            return;
        }

        $defaultPassword = Hash::make('password123');

        $counselors = [
            [
                'user' => [
                    'username' => 'bk.dewi',
                    'name' => 'Dewi Lestari, S.Psi.',
                    'email' => 'dewi@smk.test',
                    'password' => $defaultPassword,
                    'is_active' => true,
                ],
                'staff' => [
                    'employee_number' => '198505052010012003',
                    'full_name' => 'Dewi Lestari, S.Psi.',
                    'phone' => '081234567892',
                ],
            ],
            [
                'user' => [
                    'username' => 'bk.rudi',
                    'name' => 'Rudi Hermawan, S.Pd.',
                    'email' => 'rudi.bk@smk.test',
                    'password' => $defaultPassword,
                    'is_active' => true,
                ],
                'staff' => [
                    'employee_number' => '197803122003021004',
                    'full_name' => 'Rudi Hermawan, S.Pd.',
                    'phone' => '081298765432',
                ],
            ],
            [
                'user' => [
                    'username' => 'bk.yanti',
                    'name' => 'Sri Wahyanti, M.Pd.',
                    'email' => 'yanti.bk@smk.test',
                    'password' => $defaultPassword,
                    'is_active' => true,
                ],
                'staff' => [
                    'employee_number' => '198011202007012005',
                    'full_name' => 'Sri Wahyanti, M.Pd.',
                    'phone' => '085600112233',
                ],
            ],
        ];

        foreach ($counselors as $data) {
            $user = User::firstOrCreate(
                ['username' => $data['user']['username']],
                $data['user']
            );

            $user->roles()->syncWithoutDetaching([$counselorRole->id]);

            StaffProfile::firstOrCreate(
                ['user_id' => $user->id],
                array_merge($data['staff'], ['user_id' => $user->id])
            );
        }

        $this->command->info('BkUserSeeder: '.count($counselors).' akun guru BK berhasil dibuat.');
    }
}
