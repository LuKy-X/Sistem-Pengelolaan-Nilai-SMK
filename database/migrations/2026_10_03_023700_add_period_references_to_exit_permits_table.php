<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Menambahkan rujukan jam pelajaran pada pengajuan izin keluar.
 *
 * Siswa tidak lagi mengetik jam. Ia memilih "jam pelajaran ke berapa keluar" dan
 * "ke berapa harus sudah kembali", lalu planned_exit_at dan planned_return_at
 * diturunkan dari jam mulai kedua jam pelajaran tersebut.
 *
 * Kedua kolom timestamp yang sudah ada tetap dipertahankan dan tetap diisi,
 * karena seluruh timer, deteksi keterlambatan, notifikasi, dan halaman modul BK
 * membacanya. Rujukan jam pelajaran ditambahkan supaya pilihan siswa ikut
 * tercatat dan bisa ditampilkan kembali.
 *
 * Kolom ini nullable karena data lama tidak punya rujukan jam pelajaran.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('exit_permits', function (Blueprint $table) {
            if (! Schema::hasColumn('exit_permits', 'exit_period_id')) {
                $table->foreignId('exit_period_id')
                    ->nullable()
                    ->after('reason_detail')
                    ->constrained('lesson_periods')
                    ->restrictOnDelete();
            }

            if (! Schema::hasColumn('exit_permits', 'return_period_id')) {
                $table->foreignId('return_period_id')
                    ->nullable()
                    ->after('exit_period_id')
                    ->constrained('lesson_periods')
                    ->restrictOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('exit_permits', function (Blueprint $table) {
            if (Schema::hasColumn('exit_permits', 'return_period_id')) {
                $table->dropConstrainedForeignId('return_period_id');
            }

            if (Schema::hasColumn('exit_permits', 'exit_period_id')) {
                $table->dropConstrainedForeignId('exit_period_id');
            }
        });
    }
};
