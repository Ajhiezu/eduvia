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
        // 1. Modul Materi Pembelajaran (Learning Materials)
        Schema::create('learning_materials', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->nullable()->constrained('schools')->nullOnDelete();
            $table->foreignId('teaching_assignment_id')->constrained('teaching_assignments')->cascadeOnDelete();
            $table->foreignId('classroom_id')->constrained('classrooms')->cascadeOnDelete();
            $table->foreignId('subject_id')->constrained('subjects')->cascadeOnDelete();
            $table->foreignId('teacher_id')->constrained('users')->cascadeOnDelete();
            $table->string('title');
            $table->string('topic', 150)->nullable();
            $table->text('description')->nullable();
            $table->longText('content')->nullable();
            $table->string('video_url')->nullable();
            $table->string('status', 25)->default('draft'); // draft, published, archived
            $table->timestamp('published_at')->nullable();
            $table->unsignedSmallInteger('order')->default(0);
            $table->timestamps();

            $table->index(['school_id', 'classroom_id', 'status'], 'lm_school_class_status_idx');
            $table->index(['teaching_assignment_id', 'status'], 'lm_ta_status_idx');
            $table->index(['teacher_id', 'status'], 'lm_teacher_status_idx');
        });

        // 2. Lampiran Materi Pembelajaran
        Schema::create('learning_material_attachments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('learning_material_id')->constrained('learning_materials')->cascadeOnDelete();
            $table->string('file_path');
            $table->string('file_name');
            $table->unsignedBigInteger('file_size');
            $table->string('mime_type', 100);
            $table->timestamps();

            $table->index('learning_material_id', 'lma_material_id_idx');
        });

        // 3. Progress Mempelajari Materi per Siswa
        Schema::create('learning_material_progress', function (Blueprint $table) {
            $table->id();
            $table->foreignId('learning_material_id')->constrained('learning_materials')->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('users')->cascadeOnDelete();
            $table->string('status', 25)->default('in_progress'); // in_progress, completed
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('last_accessed_at')->nullable();
            $table->timestamps();

            $table->unique(['learning_material_id', 'student_id'], 'lmp_material_student_uniq');
            $table->index(['student_id', 'status'], 'lmp_student_status_idx');
        });

        // 4. Modul Tugas Pembelajaran (Assignments)
        Schema::create('assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->nullable()->constrained('schools')->nullOnDelete();
            $table->foreignId('teaching_assignment_id')->constrained('teaching_assignments')->cascadeOnDelete();
            $table->foreignId('classroom_id')->constrained('classrooms')->cascadeOnDelete();
            $table->foreignId('subject_id')->constrained('subjects')->cascadeOnDelete();
            $table->foreignId('teacher_id')->constrained('users')->cascadeOnDelete();
            $table->string('title');
            $table->string('topic', 150)->nullable();
            $table->longText('instructions');
            $table->string('submission_type', 25)->default('both'); // text_only, file_only, both
            $table->unsignedSmallInteger('max_file_size_mb')->default(10);
            $table->string('allowed_file_types')->default('pdf,docx,jpg,jpeg,png,zip');
            $table->dateTime('due_date');
            $table->unsignedSmallInteger('max_score')->default(100);
            $table->string('status', 25)->default('draft'); // draft, published, cancelled
            $table->timestamp('published_at')->nullable();
            $table->boolean('allow_late_submission')->default(true);
            $table->boolean('allow_resubmission')->default(true);
            $table->timestamps();

            $table->index(['school_id', 'classroom_id', 'status'], 'asgn_school_class_status_idx');
            $table->index(['teaching_assignment_id', 'status'], 'asgn_ta_status_idx');
            $table->index(['teacher_id', 'status'], 'asgn_teacher_status_idx');
            $table->index(['due_date', 'status'], 'asgn_due_status_idx');
        });

        // 5. Lampiran Tugas dari Guru
        Schema::create('assignment_attachments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assignment_id')->constrained('assignments')->cascadeOnDelete();
            $table->string('file_path');
            $table->string('file_name');
            $table->unsignedBigInteger('file_size');
            $table->string('mime_type', 100);
            $table->timestamps();

            $table->index('assignment_id', 'asgna_assignment_id_idx');
        });

        // 6. Pengumpulan Tugas Siswa (Submissions)
        Schema::create('assignment_submissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assignment_id')->constrained('assignments')->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('users')->cascadeOnDelete();
            $table->string('status', 25)->default('draft'); // draft, submitted, late, graded, revision_requested, resubmitted
            $table->longText('content')->nullable();
            $table->dateTime('submitted_at')->nullable();
            $table->boolean('is_late')->default(false);
            $table->decimal('score', 5, 2)->nullable();
            $table->text('feedback')->nullable();
            $table->dateTime('graded_at')->nullable();
            $table->unsignedBigInteger('graded_by')->nullable();
            $table->foreign('graded_by', 'asgns_gb_fk')->references('id')->on('users')->nullOnDelete();
            $table->boolean('is_grade_published')->default(false);
            $table->dateTime('grade_published_at')->nullable();
            $table->dateTime('revision_requested_at')->nullable();
            $table->text('revision_notes')->nullable();
            $table->unsignedSmallInteger('attempt_number')->default(1);
            $table->timestamps();

            $table->unique(['assignment_id', 'student_id'], 'asgns_assignment_student_uniq');
            $table->index(['assignment_id', 'status'], 'asgns_assignment_status_idx');
            $table->index(['student_id', 'status'], 'asgns_student_status_idx');
        });

        // 7. Lampiran Jawaban Pengumpulan Siswa
        Schema::create('assignment_submission_attachments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('assignment_submission_id');
            $table->foreign('assignment_submission_id', 'asgnsa_sub_fk')->references('id')->on('assignment_submissions')->cascadeOnDelete();
            $table->string('file_path');
            $table->string('file_name');
            $table->unsignedBigInteger('file_size');
            $table->string('mime_type', 100);
            $table->timestamps();

            $table->index('assignment_submission_id', 'asgnsa_sub_id_idx');
        });

        // 8. Histori Revisi & Penilaian Pengumpulan
        Schema::create('assignment_submission_histories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('assignment_submission_id');
            $table->foreign('assignment_submission_id', 'asgnsh_sub_fk')->references('id')->on('assignment_submissions')->cascadeOnDelete();
            $table->unsignedSmallInteger('attempt_number');
            $table->longText('content')->nullable();
            $table->decimal('score', 5, 2)->nullable();
            $table->text('feedback')->nullable();
            $table->string('status', 25);
            $table->dateTime('submitted_at')->nullable();
            $table->dateTime('graded_at')->nullable();
            $table->unsignedBigInteger('graded_by')->nullable();
            $table->foreign('graded_by', 'asgnsh_gb_fk')->references('id')->on('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['assignment_submission_id', 'attempt_number'], 'asgnsh_sub_attempt_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assignment_submission_histories');
        Schema::dropIfExists('assignment_submission_attachments');
        Schema::dropIfExists('assignment_submissions');
        Schema::dropIfExists('assignment_attachments');
        Schema::dropIfExists('assignments');
        Schema::dropIfExists('learning_material_progress');
        Schema::dropIfExists('learning_material_attachments');
        Schema::dropIfExists('learning_materials');
    }
};
