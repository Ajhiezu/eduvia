<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations for AI Teacher Assistant (Phase 4B).
     */
    public function up(): void
    {
        // 1. Quota & Budget Configuration per School
        Schema::create('school_ai_quotas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->unsignedBigInteger('monthly_token_limit')->default(500000); // 500k tokens/month
            $table->decimal('monthly_budget_idr', 12, 2)->default(100000.00); // Rp 100.000,-/month
            $table->unsignedBigInteger('current_month_tokens_used')->default(0);
            $table->decimal('current_month_cost_idr', 12, 2)->default(0.00);
            $table->date('quota_reset_at')->nullable();
            $table->boolean('is_ai_enabled')->default(false); // Default OFF per tenant rule
            $table->unsignedSmallInteger('rate_limit_per_minute')->default(10);
            $table->timestamps();

            $table->unique('school_id', 'school_ai_quotas_school_unique');
        });

        // 2. Drafts created by AI for Teacher Review (Draft AI, never auto-published)
        Schema::create('ai_generation_drafts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->foreignId('teacher_id')->constrained('users')->cascadeOnDelete();
            $table->string('feature_type', 40); // lesson_plan, question_set, grading_rubric, essay_feedback
            $table->foreignId('subject_id')->nullable()->constrained('subjects')->nullOnDelete();
            $table->foreignId('grade_level_id')->nullable()->constrained('grade_levels')->nullOnDelete();
            $table->string('title', 255);
            $table->json('input_context')->nullable(); // Non-PII prompt context
            $table->json('generated_content'); // Raw AI structured output
            $table->json('edited_content')->nullable(); // Teacher edits
            $table->string('status', 20)->default('draft'); // draft, accepted, discarded
            $table->unsignedSmallInteger('version')->default(1);
            $table->timestamp('accepted_at')->nullable();
            $table->timestamp('discarded_at')->nullable();
            $table->string('destination_type', 50)->nullable(); // e.g. exam_questions, learning_materials
            $table->unsignedBigInteger('destination_id')->nullable();
            $table->string('provider', 30)->default('gemini');
            $table->string('model_name', 50)->default('gemini-1.5-flash');
            $table->unsignedInteger('tokens_used')->default(0);
            $table->decimal('estimated_cost_idr', 10, 2)->default(0.00);
            $table->unsignedTinyInteger('feedback_rating')->nullable(); // 1-5 stars
            $table->text('feedback_notes')->nullable();
            $table->boolean('is_flagged_bad_output')->default(false);
            $table->timestamps();

            $table->index(['school_id', 'teacher_id', 'status'], 'ai_drafts_school_teacher_status_idx');
            $table->index(['feature_type', 'status'], 'ai_drafts_type_status_idx');
        });

        // 3. Immutable Token & Cost Usage Ledger (for audit and yayasan aggregation)
        Schema::create('ai_usage_ledgers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('feature_type', 40);
            $table->string('provider', 30);
            $table->string('model_name', 50);
            $table->unsignedInteger('prompt_tokens')->default(0);
            $table->unsignedInteger('completion_tokens')->default(0);
            $table->unsignedInteger('total_tokens')->default(0);
            $table->decimal('estimated_cost_idr', 10, 4)->default(0.0000);
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['school_id', 'created_at'], 'ai_usage_school_time_idx');
            $table->index(['user_id', 'created_at'], 'ai_usage_user_time_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ai_usage_ledgers');
        Schema::dropIfExists('ai_generation_drafts');
        Schema::dropIfExists('school_ai_quotas');
    }
};
