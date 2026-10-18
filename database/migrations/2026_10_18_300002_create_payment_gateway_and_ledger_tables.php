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
        // 1. Payment Gateway Transactions (Midtrans Core API tracking)
        Schema::create('payment_gateway_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('payer_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('order_id', 64)->unique();
            $table->string('payment_method', 30); // bca_va, bni_va, bri_va, mandiri_bill, permata_va, qris
            $table->unsignedBigInteger('gross_amount');
            $table->string('transaction_status', 30)->default('pending'); // pending, settlement, expire, cancel, failure
            $table->string('va_number', 50)->nullable();
            $table->string('bill_key', 50)->nullable();
            $table->string('biller_code', 50)->nullable();
            $table->text('qr_code_url')->nullable();
            $table->timestamp('expiry_time');
            $table->timestamp('settlement_time')->nullable();
            $table->string('idempotency_key', 100)->unique();
            $table->json('raw_payload')->nullable();
            $table->json('raw_response')->nullable();
            $table->timestamps();

            $table->index(['school_id', 'transaction_status'], 'pg_tx_school_status_idx');
            $table->index(['student_id', 'transaction_status'], 'pg_tx_student_status_idx');
        });

        // 2. Multi-Invoice Payment Allocations
        Schema::create('payment_allocations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payment_id')->nullable()->constrained('payments')->nullOnDelete();
            $table->foreignId('payment_gateway_transaction_id')->constrained('payment_gateway_transactions')->cascadeOnDelete();
            $table->foreignId('invoice_id')->constrained('invoices')->cascadeOnDelete();
            $table->unsignedBigInteger('amount_applied');
            $table->unsignedSmallInteger('allocation_order')->default(1);
            $table->timestamps();

            $table->index(['payment_gateway_transaction_id'], 'pg_alloc_tx_idx');
            $table->index(['invoice_id'], 'pg_alloc_inv_idx');
            $table->index(['payment_id'], 'pg_alloc_pay_idx');
        });

        // 3. Append-Only Financial Ledger (Double-Entry Journal)
        Schema::create('financial_ledger_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->string('entry_number', 50)->unique();
            $table->string('entry_type', 10); // debit, credit
            $table->string('account_code', 20); // 1101 (Kas/Bank), 4101 (Pendapatan SPP), 1103 (Piutang SPP)
            $table->string('account_name', 100);
            $table->unsignedBigInteger('amount');
            $table->string('reference_type', 50); // Payment, PaymentGatewayTransaction, PaymentReversal, Invoice
            $table->unsignedBigInteger('reference_id');
            $table->string('description', 255);
            $table->timestamp('posted_at');
            $table->foreignId('posted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['school_id', 'posted_at'], 'fin_ledg_school_post_idx');
            $table->index(['account_code', 'posted_at'], 'fin_ledg_acc_post_idx');
            $table->index(['reference_type', 'reference_id'], 'fin_ledg_ref_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('financial_ledger_entries');
        Schema::dropIfExists('payment_allocations');
        Schema::dropIfExists('payment_gateway_transactions');
    }
};
