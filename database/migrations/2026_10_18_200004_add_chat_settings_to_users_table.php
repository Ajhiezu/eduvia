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
        Schema::table('users', function (Blueprint $table) {
            $table->string('chat_service_hours_start', 5)->default('07:30')->after('is_active');
            $table->string('chat_service_hours_end', 5)->default('16:00')->after('chat_service_hours_start');
            $table->json('chat_service_days')->nullable()->after('chat_service_hours_end');
            $table->text('chat_auto_reply_message')->nullable()->after('chat_service_days');
            $table->boolean('chat_auto_reply_enabled')->default(true)->after('chat_auto_reply_message');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'chat_service_hours_start',
                'chat_service_hours_end',
                'chat_service_days',
                'chat_auto_reply_message',
                'chat_auto_reply_enabled',
            ]);
        });
    }
};
