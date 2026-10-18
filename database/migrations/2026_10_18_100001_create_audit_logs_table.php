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
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->nullable()->constrained('schools')->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('user_name', 150)->nullable();
            $table->string('role', 50)->nullable();
            $table->string('auditable_type', 150)->nullable();
            $table->unsignedBigInteger('auditable_id')->nullable();
            $table->string('action', 50); // created, updated, deleted, login, sensitive_access, etc.
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->string('request_id', 100)->nullable();
            $table->string('description', 255)->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['school_id', 'created_at'], 'audit_school_created_idx');
            $table->index(['auditable_type', 'auditable_id'], 'audit_morph_idx');
            $table->index(['user_id', 'created_at'], 'audit_user_created_idx');
            $table->index(['action', 'created_at'], 'audit_action_created_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};
