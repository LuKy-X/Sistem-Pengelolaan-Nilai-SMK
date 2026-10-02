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
        Schema::table('lesson_periods', function (Blueprint $table) {
            if (! Schema::hasColumn('lesson_periods', 'sort_order')) {
                $table->unsignedInteger('sort_order')->default(1)->after('id');
            }
            $table->unsignedInteger('period_number')->nullable()->change();
        });

        try {
            Schema::table('lesson_periods', function (Blueprint $table) {
                $table->dropUnique('lesson_periods_period_number_unique');
            });
        } catch (Throwable $e) {
            // Unique index might already be absent in some test environments
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('lesson_periods', function (Blueprint $table) {
            if (Schema::hasColumn('lesson_periods', 'sort_order')) {
                $table->dropColumn('sort_order');
            }
        });
    }
};
