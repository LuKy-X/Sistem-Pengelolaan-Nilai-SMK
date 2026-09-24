<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('student_products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained('product_categories')->restrictOnDelete();
            $table->foreignId('department_id')->nullable()->constrained('departments')->nullOnDelete();
            $table->string('name', 150);
            $table->string('slug', 180)->unique();
            $table->text('description')->nullable();
            $table->decimal('price', 12, 2)->nullable();
            $table->string('contact', 100)->nullable();
            $table->string('status', 20)->default('AVAILABLE');
            $table->timestamps();
        });

        Schema::create('product_students', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('student_products')->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('student_profiles')->cascadeOnDelete();

            $table->unique(['product_id', 'student_id'], 'unique_product_student');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_students');
        Schema::dropIfExists('student_products');
        Schema::dropIfExists('product_categories');
    }
};
