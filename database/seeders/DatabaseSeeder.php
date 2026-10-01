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
<<<<<<< HEAD
            SampleBkDataSeeder::class,
=======
            DepartmentDetailSeeder::class,
            ProductCategorySeeder::class,
            StudentProductSeeder::class,
            ArticleCategorySeeder::class,
            ArticleSeeder::class,
            CareerCompanySeeder::class,
            CareerServiceSeeder::class,
            CareerOpportunitySeeder::class,
            AlumniSeeder::class,
            AdmissionSeeder::class,
            AchievementCategorySeeder::class,
            AchievementSeeder::class,
            SiteStatisticSeeder::class,
>>>>>>> ad921520bf629a2225abf00dd8665c0358b0629a
        ]);
    }
}
