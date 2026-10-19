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
        Schema::create('student_leave_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('parent_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('classroom_id')->nullable()->constrained('classrooms')->nullOnDelete();
            $table->string('leave_type', 30); // sick, permit, other
            $table->date('start_date');
            $table->date('end_date');
            $table->text('reason');
            $table->string('attachment_path')->nullable();
            $table->string('attachment_name')->nullable();
            $table->string('status', 30)->default('pending'); // pending, approved, rejected
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->timestamps();

            $table->index(['school_id', 'status', 'start_date'], 'slr_school_status_date_idx');
            $table->index(['student_id', 'status'], 'slr_student_status_idx');
            $table->index(['classroom_id', 'status'], 'slr_classroom_status_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_leave_requests');
    }
};
