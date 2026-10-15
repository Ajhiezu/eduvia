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
        // 1. Bank Soal (Question Bank)
        Schema::create('exam_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->foreignId('subject_id')->constrained('subjects')->cascadeOnDelete();
            $table->foreignId('grade_level_id')->nullable()->constrained('grade_levels')->nullOnDelete();
            $table->string('question_type', 30); // multiple_choice_single, multiple_choice_multi, true_false, short_answer, essay
            $table->mediumText('content');
            $table->json('options')->nullable(); // [{"key": "A", "text": "..."}, ...]
            $table->json('correct_answer')->nullable(); // "A", ["A", "C"], "true", ["keyword1", "keyword2"]
            $table->decimal('default_score', 8, 2)->default(10.00);
            $table->text('explanation')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['school_id', 'subject_id', 'is_active'], 'eq_school_subj_active_idx');
            $table->index(['created_by', 'is_active'], 'eq_creator_active_idx');
        });

        // 2. Modul Ujian CBT (Exams)
        Schema::create('exams', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->foreignId('teacher_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('subject_id')->constrained('subjects')->cascadeOnDelete();
            $table->foreignId('academic_year_id')->nullable()->constrained('academic_years')->nullOnDelete();
            $table->foreignId('semester_id')->nullable()->constrained('semesters')->nullOnDelete();
            $table->string('title', 255);
            $table->text('description')->nullable();
            $table->text('instructions')->nullable();
            $table->unsignedSmallInteger('duration_minutes')->default(60);
            $table->dateTime('start_time');
            $table->dateTime('end_time');
            $table->unsignedTinyInteger('max_attempts')->default(1);
            $table->decimal('passing_grade', 5, 2)->nullable(); // KKM
            $table->string('status', 20)->default('draft'); // draft, published, closed, archived
            $table->boolean('shuffle_questions')->default(false);
            $table->boolean('shuffle_options')->default(false);
            $table->boolean('show_result_immediately')->default(true);
            $table->string('show_explanation_policy', 30)->default('after_closed'); // never, after_submit, after_closed
            $table->decimal('total_score', 8, 2)->default(0.00);
            $table->timestamps();

            $table->index(['school_id', 'status', 'start_time', 'end_time'], 'exams_school_status_time_idx');
            $table->index(['teacher_id', 'status'], 'exams_teacher_status_idx');
        });

        // 3. Exam Classrooms Pivot
        Schema::create('exam_classrooms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exam_id')->constrained('exams')->cascadeOnDelete();
            $table->foreignId('classroom_id')->constrained('classrooms')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['exam_id', 'classroom_id'], 'exam_classroom_unique');
        });

        // 4. Exam Question Items (Exam composition with specific weight & order)
        Schema::create('exam_question_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exam_id')->constrained('exams')->cascadeOnDelete();
            $table->foreignId('question_id')->constrained('exam_questions')->cascadeOnDelete();
            $table->unsignedSmallInteger('order')->default(1);
            $table->decimal('score', 8, 2)->default(10.00);
            $table->timestamps();

            $table->unique(['exam_id', 'question_id'], 'exam_question_unique');
            $table->index(['exam_id', 'order'], 'eqi_exam_order_idx');
        });

        // 5. Exam Attempts (Sesi Percobaan Pengerjaan Siswa)
        Schema::create('exam_attempts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exam_id')->constrained('exams')->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('users')->cascadeOnDelete();
            $table->unsignedTinyInteger('attempt_number')->default(1);
            $table->dateTime('started_at');
            $table->dateTime('expires_at');
            $table->dateTime('submitted_at')->nullable();
            $table->string('status', 20)->default('in_progress'); // in_progress, submitted, abandoned, graded
            $table->json('questions_snapshot'); // Frozen snapshot of questions and order
            $table->decimal('total_score', 8, 2)->nullable();
            $table->decimal('final_percentage', 5, 2)->nullable();
            $table->boolean('is_passed')->nullable();
            $table->dateTime('graded_at')->nullable();
            $table->foreignId('graded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('teacher_notes')->nullable();
            $table->timestamps();

            $table->index(['exam_id', 'student_id', 'status'], 'ea_exam_student_status_idx');
            $table->index(['student_id', 'status'], 'ea_student_status_idx');
        });

        // 6. Exam Answers (Jawaban Siswa per Soal & Autosave)
        Schema::create('exam_answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exam_attempt_id')->constrained('exam_attempts')->cascadeOnDelete();
            $table->foreignId('question_id')->constrained('exam_questions')->cascadeOnDelete();
            $table->json('answer')->nullable(); // Given answer: string, array, or text
            $table->boolean('is_marked_for_review')->default(false); // Ragu-ragu
            $table->boolean('is_correct')->nullable(); // Nullable for essay until graded
            $table->decimal('score', 8, 2)->default(0.00);
            $table->text('feedback')->nullable();
            $table->dateTime('graded_at')->nullable();
            $table->foreignId('graded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('saved_at')->nullable();
            $table->timestamps();

            $table->unique(['exam_attempt_id', 'question_id'], 'ea_attempt_question_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('exam_answers');
        Schema::dropIfExists('exam_attempts');
        Schema::dropIfExists('exam_question_items');
        Schema::dropIfExists('exam_classrooms');
        Schema::dropIfExists('exams');
        Schema::dropIfExists('exam_questions');
    }
};
