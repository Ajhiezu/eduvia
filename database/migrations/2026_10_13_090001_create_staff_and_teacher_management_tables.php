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
        // 1. Add school_id to users if not present
        if (! Schema::hasColumn('users', 'school_id')) {
            Schema::table('users', function (Blueprint $table) {
                $table->foreignId('school_id')
                    ->nullable()
                    ->after('id')
                    ->constrained('schools')
                    ->nullOnDelete();
            });
        }

        // 2. Create staff_profiles table
        Schema::create('staff_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->string('nip', 50)->nullable()->index();
            $table->string('nuptk', 50)->nullable()->index();
            $table->string('nik', 30)->nullable();
            $table->string('gender', 10)->default('male');
            $table->string('birth_place')->nullable();
            $table->date('birth_date')->nullable();
            $table->string('position', 50)->default('guru_mapel')->index();
            $table->string('work_unit', 100)->nullable();
            $table->string('employment_status', 30)->default('gty');
            $table->date('join_date')->nullable();
            $table->string('last_education', 50)->nullable();
            $table->text('address')->nullable();
            $table->string('phone', 30)->nullable();
            $table->string('status', 30)->default('active')->index();
            $table->string('invitation_token', 100)->nullable()->unique();
            $table->timestamp('invitation_sent_at')->nullable();
            $table->timestamp('activated_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // 3. Create staff_status_histories table
        Schema::create('staff_status_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('changed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('action', 50)->index();
            $table->string('previous_value')->nullable();
            $table->string('new_value')->nullable();
            $table->text('reason')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('staff_status_histories');
        Schema::dropIfExists('staff_profiles');

        if (Schema::hasColumn('users', 'school_id')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropForeign(['school_id']);
                $table->dropColumn('school_id');
            });
        }
    }
};
