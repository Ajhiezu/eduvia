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
        // 1. Add client sequence and offline sync fields to exam_answers
        Schema::table('exam_answers', function (Blueprint $table) {
            $table->unsignedBigInteger('client_sequence_id')->default(0)->after('saved_at');
            $table->timestamp('client_timestamp')->nullable()->after('client_sequence_id');
        });

        // 2. Exam Integrity Events (Anti-Cheating / Connectivity Logs)
        Schema::create('exam_integrity_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exam_attempt_id')->constrained('exam_attempts')->cascadeOnDelete();
            $table->foreignId('exam_id')->constrained('exams')->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('users')->cascadeOnDelete();
            $table->string('event_type', 50); // app_paused, app_resumed, connection_lost, connection_restored, device_switched
            $table->json('details')->nullable();
            $table->timestamp('client_timestamp')->nullable();
            $table->timestamp('recorded_at')->useCurrent();
            $table->timestamps();

            $table->index(['exam_attempt_id', 'recorded_at'], 'eil_attempt_time_idx');
            $table->index(['exam_id', 'student_id'], 'eil_exam_student_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('exam_integrity_logs');

        Schema::table('exam_answers', function (Blueprint $table) {
            $table->dropColumn(['client_sequence_id', 'client_timestamp']);
        });
    }
};
