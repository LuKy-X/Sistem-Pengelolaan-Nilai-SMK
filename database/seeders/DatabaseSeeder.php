<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,
            SchoolProfileSeeder::class,
            AcademicYearSeeder::class,
            SemesterSeeder::class,
            GradeLevelSeeder::class,
            DepartmentSeeder::class,
            SubjectSeeder::class,
            LessonPeriodSeeder::class,
            DisciplineCategorySeeder::class,
            DisciplineSettingSeeder::class,
            ExitPermitReasonSeeder::class,
            UserSeeder::class,
            SampleClassSeeder::class,
            SampleTeachingAssignmentSeeder::class,
        ]);
    }
}
