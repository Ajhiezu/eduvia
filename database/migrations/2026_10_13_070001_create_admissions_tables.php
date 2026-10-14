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
        // 1. Admission Periods (Periode PPDB/SPMB)
        Schema::create('admission_periods', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->foreignId('academic_year_id')->constrained('academic_years')->cascadeOnDelete();
            $table->string('name', 150);
            $table->string('code', 50);
            $table->date('start_date');
            $table->date('end_date');
            $table->date('announcement_date');
            $table->date('re_registration_start_date');
            $table->date('re_registration_end_date');
            $table->unsignedInteger('quota')->default(100);
            $table->string('status', 30)->default('draft'); // draft, open, closed, completed
            $table->text('description')->nullable();
            $table->text('requirements')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['school_id', 'code']);
            $table->index(['school_id', 'status']);
        });

        // 2. Admission Tracks (Jalur Pendaftaran: Zonasi, Prestasi, Afirmasi, dll.)
        Schema::create('admission_tracks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('admission_period_id')->constrained('admission_periods')->cascadeOnDelete();
            $table->string('name', 100);
            $table->string('code', 30);
            $table->unsignedInteger('quota')->default(30);
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['admission_period_id', 'code']);
        });

        // 3. Admission Applicants (Calon Siswa Pendaftar)
        Schema::create('admission_applicants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('admission_period_id')->constrained('admission_periods')->cascadeOnDelete();
            $table->foreignId('admission_track_id')->constrained('admission_tracks')->restrictOnDelete();
            $table->foreignId('target_grade_level_id')->nullable()->constrained('grade_levels')->nullOnDelete();
            $table->string('registration_number', 50)->unique();
            $table->string('tracking_code', 20)->index(); // Random secure token for status checking
            $table->string('full_name', 150);
            $table->string('nisn', 30)->nullable();
            $table->string('nik', 30)->nullable();
            $table->string('gender', 15); // male, female, L, P
            $table->string('birth_place', 100)->nullable();
            $table->date('birth_date')->nullable();
            $table->string('religion', 30)->nullable();
            $table->text('address')->nullable();
            $table->string('phone', 30);
            $table->string('email', 150)->nullable();
            $table->string('previous_school', 150)->nullable();
            $table->string('parent_name', 150);
            $table->string('parent_phone', 30);
            $table->string('parent_relationship', 50)->nullable();
            $table->string('status', 30)->default('pending'); // pending, verified, revision_needed, accepted, rejected, re_registered, converted
            $table->text('rejection_reason')->nullable();
            $table->decimal('selection_score', 5, 2)->nullable();
            $table->text('selection_notes')->nullable();
            $table->dateTime('re_registered_at')->nullable();
            $table->dateTime('converted_at')->nullable();
            $table->foreignId('converted_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['admission_period_id', 'status']);
            $table->index(['registration_number', 'tracking_code']);
        });

        // 4. Applicant Documents (Dokumen Persyaratan Calon Siswa)
        Schema::create('applicant_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('admission_applicant_id')->constrained('admission_applicants')->cascadeOnDelete();
            $table->string('document_type', 50); // kartu_keluarga, akta_kelahiran, ijazah_skhun, rapor, sertifikat, foto, lainnya
            $table->string('file_path', 255);
            $table->string('original_name', 255);
            $table->unsignedInteger('file_size'); // bytes
            $table->string('mime_type', 100);
            $table->string('status', 30)->default('pending'); // pending, valid, invalid
            $table->text('review_notes')->nullable();
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('verified_at')->nullable();
            $table->timestamps();

            $table->index(['admission_applicant_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('applicant_documents');
        Schema::dropIfExists('admission_applicants');
        Schema::dropIfExists('admission_tracks');
        Schema::dropIfExists('admission_periods');
    }
};
