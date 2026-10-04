<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\StudentProfile;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class StudentAccountSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $studentRole = Role::query()->where('code', 'STUDENT')->firstOrFail();
        $defaultPassword = Hash::make('password123');

        $query = StudentProfile::query()->whereNull('user_id');
        $totalToProcess = $query->count();

        if ($totalToProcess === 0) {
            $this->command?->info('Semua data siswa sudah memiliki akun login User.');

            return;
        }

        $this->command?->info("Memproses pembuatan akun login untuk {$totalToProcess} siswa...");

        $createdCount = 0;

        StudentProfile::query()
            ->whereNull('user_id')
            ->chunkById(100, function ($students) use ($studentRole, $defaultPassword, &$createdCount): void {
                DB::transaction(function () use ($students, $studentRole, $defaultPassword, &$createdCount): void {
                    foreach ($students as $student) {
                        $firstName = strtolower(Str::slug(explode(' ', trim($student->full_name))[0] ?? 'siswa'));
                        if (empty($firstName)) {
                            $firstName = 'siswa';
                        }

                        $username = 'siswa.'.$firstName.'.'.$student->id;
                        $email = 'siswa'.$student->id.'@smk.test';

                        $user = User::query()->firstOrCreate(
                            ['username' => $username],
                            [
                                'name' => $student->full_name,
                                'email' => $email,
                                'password' => $defaultPassword,
                                'is_active' => true,
                            ]
                        );

                        $user->roles()->syncWithoutDetaching([$studentRole->id]);

                        $student->update(['user_id' => $user->id]);
                        $createdCount++;
                    }
                });
            });

        $this->command?->info("Berhasil membuat dan menautkan {$createdCount} akun siswa!");
    }
}
