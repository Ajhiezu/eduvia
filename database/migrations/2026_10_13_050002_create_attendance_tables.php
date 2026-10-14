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
        Schema::create('attendance_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->nullable()->constrained('schools')->nullOnDelete();
            $table->foreignId('classroom_id')->constrained('classrooms')->cascadeOnDelete();
            $table->foreignId('teaching_assignment_id')->nullable()->constrained('teaching_assignments')->cascadeOnDelete();
            $table->foreignId('academic_year_id')->constrained('academic_years')->cascadeOnDelete();
            $table->foreignId('semester_id')->nullable()->constrained('semesters')->nullOnDelete();
            $table->foreignId('teacher_id')->constrained('users')->cascadeOnDelete();
            $table->string('session_type', 30)->default('subject_session'); // 'subject_session' | 'daily'
            $table->unsignedSmallInteger('meeting_number')->default(1);
            $table->date('date');
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
            $table->string('topic', 255)->nullable();
            $table->string('status', 20)->default('draft'); // 'draft' | 'finalized'
            $table->timestamp('finalized_at')->nullable();
            $table->foreignId('finalized_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['classroom_id', 'date'], 'as_class_date_idx');
            $table->index(['teacher_id', 'date'], 'as_teacher_date_idx');
            $table->index(['teaching_assignment_id', 'status'], 'as_ta_status_idx');
        });

        Schema::create('attendance_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('attendance_session_id')->constrained('attendance_sessions')->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('users')->cascadeOnDelete();
            $table->string('status', 20)->default('present'); // 'present', 'sick', 'permit', 'absent', 'late'
            $table->string('notes', 255)->nullable();
            $table->timestamps();

            $table->unique(['attendance_session_id', 'student_id'], 'ar_session_student_uniq');
            $table->index(['student_id', 'status'], 'ar_student_status_idx');
        });

        Schema::create('attendance_audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('attendance_record_id')->constrained('attendance_records')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('previous_status', 20);
            $table->string('new_status', 20);
            $table->text('reason')->nullable();
            $table->timestamps();

            $table->index(['attendance_record_id', 'created_at'], 'aal_rec_created_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendance_audit_logs');
        Schema::dropIfExists('attendance_records');
        Schema::dropIfExists('attendance_sessions');
    }
};
