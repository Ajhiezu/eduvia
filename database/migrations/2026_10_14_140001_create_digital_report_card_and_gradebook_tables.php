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
        // 1. Assessment Weight Configurations (Bobot Penilaian Semester)
        Schema::create('assessment_weight_configurations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->foreignId('academic_year_id')->nullable()->constrained('academic_years')->nullOnDelete();
            $table->foreignId('semester_id')->nullable()->constrained('semesters')->nullOnDelete();
            $table->foreignId('subject_id')->nullable()->constrained('subjects')->nullOnDelete();
            $table->string('name', 100);
            $table->decimal('assignment_weight', 5, 2)->default(30.00); // e.g. 30%
            $table->decimal('quiz_weight', 5, 2)->default(20.00);       // e.g. 20%
            $table->decimal('midterm_weight', 5, 2)->default(25.00);    // e.g. 25% (UTS)
            $table->decimal('final_weight', 5, 2)->default(25.00);      // e.g. 25% (UAS)
            $table->decimal('passing_grade', 5, 2)->default(75.00);     // KKM
            $table->json('grade_scale')->nullable();                    // Predicate thresholds: A, B, C, D
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['school_id', 'is_active']);
            $table->index(['school_id', 'academic_year_id', 'semester_id'], 'awc_school_year_sem_idx');
        });

        // 2. Subject Gradebooks (Rekapitulasi Nilai Mata Pelajaran per Kelas)
        Schema::create('subject_gradebooks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->foreignId('classroom_id')->constrained('classrooms')->cascadeOnDelete();
            $table->foreignId('subject_id')->constrained('subjects')->cascadeOnDelete();
            $table->foreignId('teacher_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('academic_year_id')->constrained('academic_years')->cascadeOnDelete();
            $table->foreignId('semester_id')->constrained('semesters')->cascadeOnDelete();
            $table->foreignId('assessment_weight_configuration_id')
                ->nullable()
                ->constrained('assessment_weight_configurations')
                ->nullOnDelete();
            $table->json('weight_snapshot')->nullable();
            $table->string('status', 30)->default('draft'); // draft, submitted, verified, revision_requested
            $table->text('submission_notes')->nullable();
            $table->text('verification_notes')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(
                ['classroom_id', 'subject_id', 'academic_year_id', 'semester_id'],
                'sg_class_subj_year_sem_uniq'
            );
            $table->index(['school_id', 'status']);
            $table->index(['teacher_id', 'status']);
        });

        // 3. Subject Gradebook Items (Detail Nilai Siswa per Mata Pelajaran)
        Schema::create('subject_gradebook_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subject_gradebook_id')->constrained('subject_gradebooks')->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('users')->cascadeOnDelete();
            $table->decimal('assignment_avg', 5, 2)->default(0.00);
            $table->decimal('quiz_avg', 5, 2)->default(0.00);
            $table->decimal('midterm_score', 5, 2)->default(0.00);
            $table->decimal('final_score_raw', 5, 2)->default(0.00);
            $table->json('manual_scores')->nullable(); // Additional formative/project scores
            $table->decimal('final_score', 5, 2)->default(0.00);
            $table->string('predicate', 5)->default('C');
            $table->boolean('is_passed')->default(false);
            $table->boolean('is_complete')->default(true);
            $table->text('achievement_description')->nullable();
            $table->text('improvement_description')->nullable();
            $table->timestamps();

            $table->unique(
                ['subject_gradebook_id', 'student_id'],
                'sgi_gradebook_student_uniq'
            );
            $table->index(['student_id', 'final_score']);
        });

        // 4. Student Report Cards (Buku Rapor Semester Siswa)
        Schema::create('student_report_cards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('classroom_id')->constrained('classrooms')->cascadeOnDelete();
            $table->foreignId('academic_year_id')->constrained('academic_years')->cascadeOnDelete();
            $table->foreignId('semester_id')->constrained('semesters')->cascadeOnDelete();
            $table->foreignId('homeroom_teacher_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('status', 30)->default('draft'); // draft, pending_review, approved, published, revision_requested
            $table->json('attendance_summary')->nullable();
            $table->json('extracurricular_evaluations')->nullable();
            $table->json('student_achievements')->nullable();
            $table->text('homeroom_notes')->nullable();
            $table->text('revision_notes')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->foreignId('published_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('published_at')->nullable();
            $table->json('snapshot')->nullable(); // Immutable snapshot upon publication
            $table->timestamps();

            $table->unique(
                ['student_id', 'classroom_id', 'academic_year_id', 'semester_id'],
                'src_student_class_year_sem_uniq'
            );
            $table->index(['school_id', 'status']);
            $table->index(['classroom_id', 'status']);
            $table->index(['homeroom_teacher_id', 'status']);
        });

        // 5. Student Report Card Items (Nilai Mata Pelajaran dalam Rapor)
        Schema::create('student_report_card_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_report_card_id')->constrained('student_report_cards')->cascadeOnDelete();
            $table->foreignId('subject_id')->constrained('subjects')->cascadeOnDelete();
            $table->foreignId('subject_gradebook_item_id')->nullable()->constrained('subject_gradebook_items')->nullOnDelete();
            $table->string('subject_name', 150);
            $table->string('subject_code', 30)->nullable();
            $table->string('subject_group', 50)->default('Umum');
            $table->string('teacher_name', 150)->nullable();
            $table->decimal('passing_grade', 5, 2)->default(75.00);
            $table->decimal('final_score', 5, 2)->default(0.00);
            $table->string('predicate', 5)->default('C');
            $table->boolean('is_passed')->default(false);
            $table->text('achievement_description')->nullable();
            $table->unsignedSmallInteger('order_index')->default(0);
            $table->timestamps();

            $table->unique(
                ['student_report_card_id', 'subject_id'],
                'srci_report_subject_uniq'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_report_card_items');
        Schema::dropIfExists('student_report_cards');
        Schema::dropIfExists('subject_gradebook_items');
        Schema::dropIfExists('subject_gradebooks');
        Schema::dropIfExists('assessment_weight_configurations');
    }
};
