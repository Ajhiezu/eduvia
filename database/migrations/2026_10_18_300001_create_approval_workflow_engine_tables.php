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
        Schema::dropIfExists('approval_delegations');
        Schema::dropIfExists('approval_histories');
        Schema::dropIfExists('approval_requests');
        Schema::dropIfExists('approval_workflow_steps');
        Schema::dropIfExists('approval_workflows');

        Schema::create('approval_workflows', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->string('code', 50);
            $table->string('name', 150);
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['school_id', 'code']);
        });

        Schema::create('approval_workflow_steps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workflow_id')->constrained('approval_workflows')->cascadeOnDelete();
            $table->unsignedInteger('step_order');
            $table->string('name', 100);
            $table->string('approver_role', 50);
            $table->foreignId('specific_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->unsignedInteger('timeout_hours')->default(48);
            $table->json('condition_rules')->nullable();
            $table->timestamps();

            $table->unique(['workflow_id', 'step_order']);
        });

        Schema::create('approval_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->foreignId('workflow_id')->constrained('approval_workflows')->cascadeOnDelete();
            $table->string('request_number', 50)->unique();
            $table->string('title', 255);
            $table->foreignId('requester_id')->constrained('users')->cascadeOnDelete();
            $table->unsignedInteger('current_step')->default(1);
            $table->string('status', 30)->default('pending'); // pending, approved, rejected, cancelled
            $table->json('payload');
            $table->string('approvable_type', 100)->nullable();
            $table->unsignedBigInteger('approvable_id')->nullable();
            $table->timestamp('submitted_at');
            $table->timestamp('completed_at')->nullable();
            $table->text('cancellation_reason')->nullable();
            $table->timestamps();

            $table->index(['school_id', 'status']);
            $table->index(['approvable_type', 'approvable_id']);
        });

        Schema::create('approval_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('request_id')->constrained('approval_requests')->cascadeOnDelete();
            $table->unsignedInteger('step_number');
            $table->foreignId('actor_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('delegated_from_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('action', 30); // submitted, approved, rejected, escalated, cancelled
            $table->text('comment')->nullable();
            $table->timestamp('acted_at');
            $table->timestamps();
        });

        Schema::create('approval_delegations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->foreignId('delegator_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('delegatee_id')->constrained('users')->cascadeOnDelete();
            $table->date('start_date');
            $table->date('end_date');
            $table->string('reason', 255);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['school_id', 'is_active', 'start_date', 'end_date'], 'appr_deleg_active_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('approval_delegations');
        Schema::dropIfExists('approval_histories');
        Schema::dropIfExists('approval_requests');
        Schema::dropIfExists('approval_workflow_steps');
        Schema::dropIfExists('approval_workflows');
    }
};
