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
        // 1. Pilot Import Batches (Surgical, Idempotent, & Rollbackable Data Onboarding)
        Schema::create('pilot_import_batches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->string('batch_code', 50)->unique();
            $table->string('entity_type', 30); // students, teachers, classrooms, parents
            $table->string('file_name', 255);
            $table->unsignedInteger('total_rows')->default(0);
            $table->unsignedInteger('imported_rows')->default(0);
            $table->unsignedInteger('failed_rows')->default(0);
            $table->json('reconciliation_report')->nullable();
            $table->json('created_ids')->nullable(); // Array of IDs created in this batch for surgical rollback
            $table->string('status', 20)->default('completed'); // completed, rolled_back
            $table->foreignId('imported_by')->constrained('users')->cascadeOnDelete();
            $table->timestamp('rolled_back_at')->nullable();
            $table->foreignId('rolled_back_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['school_id', 'entity_type', 'status'], 'pib_school_entity_status_idx');
        });

        // 2. In-App Feedback & Triase
        Schema::create('pilot_feedbacks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('role', 30); // student, teacher, parent, staff, admin
            $table->string('category', 30); // bug, ux_friction, feature_suggestion, training_needed
            $table->string('severity', 10)->default('p3'); // p0, p1, p2, p3
            $table->string('title', 200);
            $table->text('description');
            $table->string('screenshot_path')->nullable();
            $table->string('app_version', 30)->default('1.0.0');
            $table->json('device_info')->nullable(); // OS, screen size, NO PII
            $table->string('status', 20)->default('open'); // open, in_triage, in_progress, resolved, rejected
            $table->text('triage_notes')->nullable();
            $table->text('response_template_sent')->nullable();
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();

            $table->index(['school_id', 'severity', 'status'], 'pfb_school_sev_status_idx');
        });

        // 3. Periodic Anonymous Surveys (Week 2, Week 6, Final)
        Schema::create('pilot_surveys', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->unsignedTinyInteger('wave_number')->default(1);
            $table->string('respondent_role', 30); // teacher, parent, student, staff
            $table->unsignedTinyInteger('satisfaction_score'); // 1-5
            $table->unsignedTinyInteger('ease_of_use_score'); // 1-5
            $table->string('main_obstacle', 255)->nullable();
            $table->text('constructive_feedback')->nullable();
            $table->timestamps();

            $table->index(['school_id', 'wave_number', 'respondent_role'], 'ps_school_wave_role_idx');
        });

        // 4. Pilot Aggregate Metrics (Zero PII telemetry)
        Schema::create('pilot_aggregate_metrics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->date('metric_date');
            $table->string('metric_key', 80);
            $table->decimal('metric_value', 12, 4)->default(0);
            $table->json('dimensions')->nullable();
            $table->timestamps();

            $table->unique(['school_id', 'metric_date', 'metric_key'], 'pam_school_date_key_uniq');
        });

        // 5. Secure Limited Support Access Logs
        Schema::create('pilot_support_access_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->foreignId('operator_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('target_user_id')->constrained('users')->cascadeOnDelete();
            $table->string('authorized_by_pic', 150);
            $table->string('reason', 255);
            $table->string('ip_address', 45);
            $table->string('access_type', 40)->default('scrubbed_diagnostics');
            $table->timestamp('expires_at');
            $table->timestamp('revoked_at')->nullable();
            $table->timestamps();

            $table->index(['school_id', 'operator_id', 'expires_at'], 'psal_school_op_exp_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pilot_support_access_logs');
        Schema::dropIfExists('pilot_aggregate_metrics');
        Schema::dropIfExists('pilot_surveys');
        Schema::dropIfExists('pilot_feedbacks');
        Schema::dropIfExists('pilot_import_batches');
    }
};
