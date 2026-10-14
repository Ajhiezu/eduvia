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
        Schema::create('student_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained('users')->cascadeOnDelete();
            $table->foreignId('school_id')->nullable()->constrained('schools')->nullOnDelete();
            $table->string('nis', 30)->nullable()->index();
            $table->string('nisn', 30)->nullable()->unique();
            $table->string('gender', 15)->nullable(); // 'male', 'female' (Laki-laki / Perempuan)
            $table->string('birth_place', 100)->nullable();
            $table->date('birth_date')->nullable();
            $table->string('religion', 30)->nullable();
            $table->text('address')->nullable();
            $table->string('parent_name', 150)->nullable();
            $table->string('parent_phone', 30)->nullable();
            $table->string('parent_relationship', 50)->nullable(); // Ayah, Ibu, Wali
            $table->string('student_status', 30)->default('active')->index(); // active, transferred, graduated, dropped, inactive
            $table->date('enrollment_date')->nullable();
            $table->date('graduation_date')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['school_id', 'student_status']);
        });

        Schema::create('student_status_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('users')->cascadeOnDelete();
            $table->string('previous_status', 30);
            $table->string('new_status', 30);
            $table->date('effective_date');
            $table->text('reason')->nullable();
            $table->foreignId('changed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['student_id', 'effective_date'], 'ssh_student_date_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_status_histories');
        Schema::dropIfExists('student_profiles');
    }
};
