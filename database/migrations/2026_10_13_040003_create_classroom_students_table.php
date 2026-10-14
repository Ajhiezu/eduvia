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
        Schema::create('classroom_students', function (Blueprint $table) {
            $table->id();
            $table->foreignId('classroom_id')->constrained('classrooms')->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('academic_year_id')->constrained('academic_years')->cascadeOnDelete();
            $table->string('enrollment_status')->default('active'); // active, transferred, completed, dropped
            $table->date('joined_at');
            $table->date('left_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['classroom_id', 'enrollment_status'], 'cs_class_status_idx');
            $table->index(['student_id', 'academic_year_id', 'enrollment_status'], 'cs_student_year_status_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('classroom_students');
    }
};
