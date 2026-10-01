<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE assessments MODIFY COLUMN type ENUM('TASK', 'QUIZ', 'PROJECT', 'EXAM', 'REMEDIAL', 'OTHER') NOT NULL DEFAULT 'TASK'");
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE assessments MODIFY COLUMN type ENUM('TASK', 'QUIZ', 'PROJECT', 'EXAM', 'OTHER') NOT NULL DEFAULT 'TASK'");
        }
    }
};
