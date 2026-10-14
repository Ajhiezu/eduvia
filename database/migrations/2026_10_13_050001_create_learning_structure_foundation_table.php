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
        Schema::create('subjects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->nullable()->constrained('schools')->nullOnDelete();
            $table->string('code', 30)->nullable();
            $table->string('name', 150);
            $table->string('group', 50)->default('Umum'); // e.g. Umum, Peminatan, Kejuruan, Muatan Lokal
            $table->unsignedSmallInteger('order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->text('description')->nullable();
            $table->timestamps();

            $table->index(['school_id', 'is_active']);
        });

        Schema::create('teaching_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->nullable()->constrained('schools')->nullOnDelete();
            $table->foreignId('academic_year_id')->constrained('academic_years')->cascadeOnDelete();
            $table->foreignId('semester_id')->nullable()->constrained('semesters')->nullOnDelete();
            $table->foreignId('classroom_id')->constrained('classrooms')->cascadeOnDelete();
            $table->foreignId('subject_id')->constrained('subjects')->cascadeOnDelete();
            $table->foreignId('teacher_id')->constrained('users')->cascadeOnDelete();
            $table->unsignedTinyInteger('hours_per_week')->default(2);
            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(
                ['academic_year_id', 'classroom_id', 'subject_id', 'teacher_id'],
                'ta_year_class_subj_teacher_uniq'
            );
            $table->index(['teacher_id', 'academic_year_id', 'is_active'], 'ta_teacher_year_active_idx');
        });

        Schema::create('lesson_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('teaching_assignment_id')->constrained('teaching_assignments')->cascadeOnDelete();
            $table->unsignedTinyInteger('day_of_week'); // 1 = Senin, 2 = Selasa, ..., 6 = Sabtu, 7 = Minggu
            $table->time('start_time');
            $table->time('end_time');
            $table->string('room', 60)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['teaching_assignment_id', 'day_of_week'], 'ls_ta_day_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lesson_schedules');
        Schema::dropIfExists('teaching_assignments');
        Schema::dropIfExists('subjects');
    }
};
