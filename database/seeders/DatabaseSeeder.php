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
            TeacherAccountSeeder::class,
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
            BkUserSeeder::class,
            SampleClassSeeder::class,
            BkStudentSeeder::class,
            SampleTeachingAssignmentSeeder::class,
            SampleBkDataSeeder::class,
            BkDisciplineSeeder::class,
            BkExitPermitSeeder::class,
            BkCounselingSeeder::class,
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
        ]);
    }
}
