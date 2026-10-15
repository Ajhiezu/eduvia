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
        // 1. Fee Categories (Kategori Biaya: SPP, Uang Gedung, Ujian, dll.)
        Schema::create('fee_categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->string('code', 50);
            $table->string('name', 150);
            $table->text('description')->nullable();
            $table->string('type', 30)->default('recurring'); // recurring, one_time
            $table->string('billing_period', 30)->default('monthly'); // monthly, semester, annual, once
            $table->unsignedBigInteger('amount')->default(0); // Nominal standar dalam Rupiah (integer)
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['school_id', 'code'], 'fc_school_code_unique');
            $table->index(['school_id', 'is_active'], 'fc_school_active_idx');
        });

        // 2. Fee Rules (Aturan Tarif Khusus per Tahun Ajaran, Tingkat Kelas, atau Rombel)
        Schema::create('fee_rules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->foreignId('fee_category_id')->constrained('fee_categories')->cascadeOnDelete();
            $table->foreignId('academic_year_id')->constrained('academic_years')->cascadeOnDelete();
            $table->foreignId('grade_level_id')->nullable()->constrained('grade_levels')->nullOnDelete();
            $table->foreignId('classroom_id')->nullable()->constrained('classrooms')->nullOnDelete();
            $table->unsignedBigInteger('amount'); // Nominal khusus tarif ini
            $table->string('notes')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['school_id', 'fee_category_id', 'academic_year_id'], 'fr_school_cat_year_idx');
        });

        // 3. Student Discounts / Scholarships (Potongan Biaya & Beasiswa)
        Schema::create('student_discounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('fee_category_id')->nullable()->constrained('fee_categories')->nullOnDelete();
            $table->foreignId('academic_year_id')->nullable()->constrained('academic_years')->nullOnDelete();
            $table->string('name', 150); // Beasiswa Prestasi, Keringanan Yatim, dll.
            $table->string('type', 30)->default('fixed'); // fixed (nominal IDR), percentage (%)
            $table->unsignedBigInteger('value')->default(0); // Nilai nominal atau persentase (1-100)
            $table->text('reason')->nullable();
            $table->foreignId('authorized_by')->nullable()->constrained('users')->nullOnDelete();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['school_id', 'student_id', 'is_active'], 'sd_school_student_idx');
        });

        // 4. Invoices (Tagihan Siswa)
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->string('invoice_number', 50)->unique();
            $table->foreignId('student_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('academic_year_id')->constrained('academic_years')->cascadeOnDelete();
            $table->foreignId('fee_category_id')->nullable()->constrained('fee_categories')->nullOnDelete();
            $table->string('billing_period_key', 50)->nullable(); // e.g. "2026-10" untuk pencegahan duplikasi SPP bulan yg sama
            $table->string('title', 200);
            $table->date('issue_date');
            $table->date('due_date');
            $table->unsignedBigInteger('subtotal')->default(0);
            $table->unsignedBigInteger('discount_amount')->default(0);
            $table->unsignedBigInteger('total_amount')->default(0);
            $table->unsignedBigInteger('paid_amount')->default(0);
            $table->string('status', 30)->default('issued'); // draft, issued, partially_paid, paid, overdue, cancelled
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['school_id', 'status', 'due_date'], 'inv_school_status_due_idx');
            $table->index(['student_id', 'status'], 'inv_student_status_idx');
            $table->unique(['school_id', 'student_id', 'fee_category_id', 'billing_period_key'], 'inv_student_fee_period_unique');
        });

        // 5. Invoice Items (Rincian Item Tagihan)
        Schema::create('invoice_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained('invoices')->cascadeOnDelete();
            $table->foreignId('fee_category_id')->nullable()->constrained('fee_categories')->nullOnDelete();
            $table->string('item_name', 200);
            $table->unsignedInteger('quantity')->default(1);
            $table->unsignedBigInteger('unit_price');
            $table->unsignedBigInteger('discount_amount')->default(0);
            $table->unsignedBigInteger('subtotal');
            $table->timestamps();

            $table->index(['invoice_id'], 'ii_invoice_idx');
        });

        // 6. Payments (Catatan Pembayaran & Konfirmasi)
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->string('payment_number', 50)->unique();
            $table->foreignId('invoice_id')->constrained('invoices')->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('paid_by')->nullable()->constrained('users')->nullOnDelete();
            $table->unsignedBigInteger('amount');
            $table->date('payment_date');
            $table->string('payment_method', 30)->default('cash'); // cash, bank_transfer, other
            $table->string('reference_number', 100)->nullable();
            $table->string('proof_path')->nullable();
            $table->string('status', 30)->default('verified'); // pending_verification, verified, rejected, reversed
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('verified_at')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['school_id', 'status', 'payment_date'], 'pay_school_status_date_idx');
            $table->index(['invoice_id', 'status'], 'pay_invoice_status_idx');
        });

        // 7. Receipts (Kuitansi Resmi Pembayaran)
        Schema::create('receipts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->string('receipt_number', 50)->unique();
            $table->foreignId('payment_id')->constrained('payments')->cascadeOnDelete();
            $table->foreignId('invoice_id')->constrained('invoices')->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('users')->cascadeOnDelete();
            $table->unsignedBigInteger('amount');
            $table->timestamp('issued_at');
            $table->foreignId('issued_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('pdf_path')->nullable();
            $table->timestamps();

            $table->index(['school_id', 'receipt_number'], 'rcp_school_receipt_num_idx');
        });

        // 8. Payment Reversals (Pembatalan/Koreksi Pembayaran Audit-Safe)
        Schema::create('payment_reversals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->foreignId('payment_id')->constrained('payments')->cascadeOnDelete();
            $table->foreignId('invoice_id')->constrained('invoices')->cascadeOnDelete();
            $table->unsignedBigInteger('amount');
            $table->text('reason');
            $table->foreignId('reversed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['school_id', 'payment_id'], 'pr_school_payment_idx');
        });

        // 9. Payment Reminders (Log Pengingat Tagihan)
        Schema::create('payment_reminders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->foreignId('invoice_id')->constrained('invoices')->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('users')->cascadeOnDelete();
            $table->string('reminder_type', 30)->default('due_date'); // upcoming, due_date, overdue
            $table->string('channel', 30)->default('in_app'); // in_app, email, whatsapp
            $table->timestamp('sent_at');
            $table->foreignId('sent_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('status', 30)->default('sent');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['school_id', 'invoice_id'], 'prem_school_inv_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_reminders');
        Schema::dropIfExists('payment_reversals');
        Schema::dropIfExists('receipts');
        Schema::dropIfExists('payments');
        Schema::dropIfExists('invoice_items');
        Schema::dropIfExists('invoices');
        Schema::dropIfExists('student_discounts');
        Schema::dropIfExists('fee_rules');
        Schema::dropIfExists('fee_categories');
    }
};
