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
        // 1. Device Tokens for Push Notifications
        Schema::create('device_tokens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->nullable()->constrained('schools')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('token', 500);
            $table->string('platform', 20)->default('android'); // android, ios, web
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_used_at')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'token'], 'dt_user_token_unique');
            $table->index(['school_id', 'is_active'], 'dt_school_active_idx');
        });

        // 2. Notification Preferences & Quiet Hours
        Schema::create('notification_preferences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->nullable()->constrained('schools')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete()->unique();
            $table->boolean('channel_push')->default(true);
            $table->boolean('channel_whatsapp')->default(true);
            $table->boolean('channel_email')->default(false);
            $table->boolean('quiet_hours_enabled')->default(true);
            $table->string('quiet_hours_start', 5)->default('21:00');
            $table->string('quiet_hours_end', 5)->default('06:00');
            $table->boolean('whatsapp_opt_in')->default(false);
            $table->timestamp('whatsapp_opt_in_at')->nullable();
            $table->string('whatsapp_number', 30)->nullable();
            $table->timestamps();

            $table->index(['school_id'], 'np_school_idx');
        });

        // 3. Notification Delivery & Audit Logs
        Schema::create('notification_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->nullable()->constrained('schools')->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('event_key', 100);
            $table->string('channel', 20); // push, whatsapp, email
            $table->string('recipient', 150);
            $table->string('title', 255);
            $table->string('body_preview', 500);
            $table->string('status', 30); // sent, failed, queued, suppressed_quiet_hours, deduplicated
            $table->text('error_message')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();

            $table->index(['school_id', 'created_at'], 'nl_school_created_idx');
            $table->index(['event_key', 'user_id', 'created_at'], 'nl_dedup_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notification_logs');
        Schema::dropIfExists('notification_preferences');
        Schema::dropIfExists('device_tokens');
    }
};
