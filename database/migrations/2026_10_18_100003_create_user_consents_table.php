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
        Schema::create('user_consents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('consent_type', 60); // terms_of_service, privacy_policy, child_data_processing, etc.
            $table->string('version', 20)->default('1.0');
            $table->boolean('is_granted')->default(true);
            $table->foreignId('parent_user_id')->nullable()->constrained('users')->nullOnDelete(); // Parent consenting for child
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamp('consented_at')->useCurrent();
            $table->timestamp('revoked_at')->nullable();
            $table->timestamps();

            $table->index(['school_id', 'user_id', 'consent_type'], 'uc_school_user_type_idx');
            $table->index(['user_id', 'is_granted'], 'uc_user_granted_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_consents');
    }
};
