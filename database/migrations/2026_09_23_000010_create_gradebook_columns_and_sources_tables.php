<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('gradebook_columns', function (Blueprint $table) {
            $table->id();
            $table->foreignId('gradebook_id')->constrained('gradebooks')->cascadeOnDelete();
            $table->foreignId('category_id')->nullable()->constrained('gradebook_categories')->nullOnDelete();
            $table->string('name', 100);
            $table->string('code', 50)->nullable();
            $table->enum('column_type', ['SCORE', 'SUMMARY'])->default('SCORE');
            $table->enum('calculation_type', ['AVERAGE', 'SUM', 'WEIGHTED_AVERAGE'])->nullable();
            $table->decimal('max_score', 5, 2)->nullable()->default(100.00);
            $table->decimal('weight', 5, 2)->nullable();
            $table->integer('sort_order')->default(0);
            $table->boolean('is_visible')->default(true);
            $table->boolean('is_included_in_average')->default(true);
            $table->timestamps();
        });

        Schema::create('gradebook_column_sources', function (Blueprint $table) {
            $table->id();
            $table->foreignId('summary_column_id')->constrained('gradebook_columns')->cascadeOnDelete();
            $table->foreignId('source_column_id')->constrained('gradebook_columns')->cascadeOnDelete();
            $table->decimal('weight', 5, 2)->nullable();
            $table->timestamps();

            $table->unique(['summary_column_id', 'source_column_id'], 'unique_col_source');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gradebook_column_sources');
        Schema::dropIfExists('gradebook_columns');
    }
};
