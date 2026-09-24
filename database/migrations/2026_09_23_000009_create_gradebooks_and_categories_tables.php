<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('gradebooks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('teaching_assignment_id')->constrained('teaching_assignments')->restrictOnDelete();
            $table->string('name', 150);
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('gradebook_categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('gradebook_id')->constrained('gradebooks')->cascadeOnDelete();
            $table->string('name', 100);
            $table->string('code', 50);
            $table->decimal('weight', 5, 2)->nullable();
            $table->integer('sort_order')->default(0);
            $table->boolean('is_included_in_average')->default(true);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gradebook_categories');
        Schema::dropIfExists('gradebooks');
    }
};
