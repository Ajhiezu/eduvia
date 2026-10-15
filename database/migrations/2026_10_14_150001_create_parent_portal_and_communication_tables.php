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
        // 1. Hubungan Akun Orang Tua & Siswa
        Schema::create('parent_student_relations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->foreignId('parent_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('users')->cascadeOnDelete();
            $table->string('relationship_type', 30)->default('guardian'); // father, mother, guardian, other
            $table->string('status', 30)->default('active'); // pending, active, inactive, rejected
            $table->boolean('is_primary_contact')->default(false);
            $table->timestamp('verified_at')->nullable();
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['parent_id', 'student_id'], 'psr_parent_student_unique');
            $table->index(['school_id', 'status'], 'psr_school_status_idx');
            $table->index(['student_id', 'status'], 'psr_student_status_idx');
        });

        // 2. Kode Undangan / Penautan Akun Orang Tua
        Schema::create('parent_link_codes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('users')->cascadeOnDelete();
            $table->string('code', 32)->unique();
            $table->string('relationship_type', 30)->default('guardian');
            $table->timestamp('expires_at');
            $table->unsignedInteger('max_uses')->default(1);
            $table->unsignedInteger('used_count')->default(0);
            $table->foreignId('used_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('used_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['code', 'expires_at'], 'plc_code_exp_idx');
            $table->index(['student_id', 'expires_at'], 'plc_student_exp_idx');
        });

        // 3. Pengumuman Internal Sekolah
        Schema::create('school_announcements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->string('title');
            $table->string('slug')->index();
            $table->text('content');
            $table->string('category', 50)->default('general'); // general, academic, event, holiday, urgent
            $table->string('target_audience', 50)->default('all'); // all, parents, students, teachers
            $table->foreignId('grade_level_id')->nullable()->constrained('grade_levels')->nullOnDelete();
            $table->foreignId('classroom_id')->nullable()->constrained('classrooms')->nullOnDelete();
            $table->string('status', 30)->default('published'); // draft, published, archived
            $table->boolean('is_pinned')->default(false);
            $table->timestamp('published_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->string('attachment_path')->nullable();
            $table->string('attachment_name')->nullable();
            $table->unsignedInteger('view_count')->default(0);
            $table->timestamps();

            $table->index(['school_id', 'status', 'published_at'], 'sa_school_pub_idx');
            $table->index(['school_id', 'target_audience'], 'sa_school_target_idx');
        });

        // 4. Percakapan Komunikasi Guru - Orang Tua
        Schema::create('parent_teacher_conversations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('parent_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('teacher_id')->constrained('users')->cascadeOnDelete();
            $table->string('subject');
            $table->string('status', 30)->default('active'); // active, closed, archived
            $table->timestamp('last_message_at')->nullable();
            $table->timestamps();

            $table->index(['school_id', 'parent_id'], 'ptc_school_parent_idx');
            $table->index(['school_id', 'teacher_id'], 'ptc_school_teacher_idx');
            $table->index(['student_id'], 'ptc_student_idx');
        });

        // 5. Pesan dalam Percakapan
        Schema::create('conversation_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('conversation_id')->constrained('parent_teacher_conversations')->cascadeOnDelete();
            $table->foreignId('sender_id')->constrained('users')->cascadeOnDelete();
            $table->text('body');
            $table->string('attachment_path')->nullable();
            $table->string('attachment_name')->nullable();
            $table->boolean('is_read')->default(false);
            $table->timestamp('read_at')->nullable();
            $table->timestamps();

            $table->index(['conversation_id', 'created_at'], 'cm_conv_time_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('conversation_messages');
        Schema::dropIfExists('parent_teacher_conversations');
        Schema::dropIfExists('school_announcements');
        Schema::dropIfExists('parent_link_codes');
        Schema::dropIfExists('parent_student_relations');
    }
};
