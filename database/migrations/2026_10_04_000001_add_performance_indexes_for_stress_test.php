<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Index pencarian jurnal kelas berdasarkan tanggal & penugasan mengajar
        // Note: journal_date di awal agar tidak mengambil alih constraint foreign key MySQL
        Schema::table('class_journals', function (Blueprint $table) {
            $table->index(['journal_date', 'teaching_assignment_id'], 'idx_class_journals_date_assignment');
        });

        // 2. Index pencarian jadwal mengajar hari ini berdasarkan hari & penugasan
        // Note: day_of_week di awal agar tidak mengambil alih constraint foreign key MySQL
        Schema::table('teaching_schedules', function (Blueprint $table) {
            $table->index(['day_of_week', 'teaching_assignment_id'], 'idx_schedules_day_assignment');
        });

        // 3. Index filter status dan urutan pengajuan izin keluar siswa
        Schema::table('exit_permits', function (Blueprint $table) {
            $table->index(['status', 'requested_at'], 'idx_exit_permits_status_requested');
        });

        // 4. Index filter tugas siswa berstatus SUBMITTED untuk antrean review guru
        Schema::table('assessment_submissions', function (Blueprint $table) {
            $table->index(['status', 'submitted_at'], 'idx_submissions_status_submitted');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('class_journals', function (Blueprint $table) {
            $table->dropIndex('idx_class_journals_date_assignment');
        });

        Schema::table('teaching_schedules', function (Blueprint $table) {
            $table->dropIndex('idx_schedules_day_assignment');
        });

        Schema::table('exit_permits', function (Blueprint $table) {
            $table->dropIndex('idx_exit_permits_status_requested');
        });

        Schema::table('assessment_submissions', function (Blueprint $table) {
            $table->dropIndex('idx_submissions_status_submitted');
        });
    }
};
