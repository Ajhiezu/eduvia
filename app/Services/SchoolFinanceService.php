<?php

namespace App\Services;

use App\Models\AcademicYear;
use App\Models\ClassroomStudent;
use App\Models\FeeCategory;
use App\Models\FeeRule;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Payment;
use App\Models\PaymentReminder;
use App\Models\PaymentReversal;
use App\Models\Receipt;
use App\Models\School;
use App\Models\StudentDiscount;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class SchoolFinanceService
{
    /**
     * Generate an auditable unique invoice number.
     * Format: INV/{SCHOOL_ID}/{YEAR}{MONTH}/{RANDOM_HASH}
     */
    public function generateInvoiceNumber(int $schoolId): string
    {
        do {
            $number = sprintf('INV/%03d/%s/%s', $schoolId, date('Ym'), strtoupper(Str::random(6)));
        } while (Invoice::where('invoice_number', $number)->exists());

        return $number;
    }

    /**
     * Generate an auditable unique payment number.
     */
    public function generatePaymentNumber(int $schoolId): string
    {
        do {
            $number = sprintf('PAY/%03d/%s/%s', $schoolId, date('Ym'), strtoupper(Str::random(6)));
        } while (Payment::where('payment_number', $number)->exists());

        return $number;
    }

    /**
     * Generate an auditable unique receipt number.
     */
    public function generateReceiptNumber(int $schoolId): string
    {
        do {
            $number = sprintf('KW/%03d/%s/%s', $schoolId, date('Ym'), strtoupper(Str::random(6)));
        } while (Receipt::where('receipt_number', $number)->exists());

        return $number;
    }

    /**
     * Create a single invoice with multiple line items.
     * All calculations are executed on the server using integer Rupiah.
     *
     * @param array{
     *     title: string,
     *     academic_year_id: int,
     *     fee_category_id?: ?int,
     *     billing_period_key?: ?string,
     *     issue_date: string,
     *     due_date: string,
     *     notes?: ?string,
     *     status?: ?string,
     *     items: array<array{
     *         fee_category_id?: ?int,
     *         item_name: string,
     *         quantity: int,
     *         unit_price: int,
     *         discount_amount?: int
     *     }>
     * } $data
     */
    public function createInvoice(School $school, User $student, array $data, ?User $creator = null): Invoice
    {
        return DB::transaction(function () use ($school, $student, $data, $creator) {
            $subtotal = 0;
            $totalDiscount = 0;

            foreach ($data['items'] as $item) {
                $qty = max(1, (int) ($item['quantity'] ?? 1));
                $price = max(0, (int) ($item['unit_price'] ?? 0));
                $itemDiscount = max(0, (int) ($item['discount_amount'] ?? 0));

                $itemSubtotal = $qty * $price;
                if ($itemDiscount > $itemSubtotal) {
                    $itemDiscount = $itemSubtotal;
                }

                $subtotal += $itemSubtotal;
                $totalDiscount += $itemDiscount;
            }

            $totalAmount = max(0, $subtotal - $totalDiscount);

            $invoice = Invoice::create([
                'school_id' => $school->id,
                'invoice_number' => $this->generateInvoiceNumber($school->id),
                'student_id' => $student->id,
                'academic_year_id' => $data['academic_year_id'],
                'fee_category_id' => $data['fee_category_id'] ?? null,
                'billing_period_key' => $data['billing_period_key'] ?? null,
                'title' => $data['title'],
                'issue_date' => $data['issue_date'],
                'due_date' => $data['due_date'],
                'subtotal' => $subtotal,
                'discount_amount' => $totalDiscount,
                'total_amount' => $totalAmount,
                'paid_amount' => 0,
                'status' => $data['status'] ?? Invoice::STATUS_ISSUED,
                'notes' => $data['notes'] ?? null,
                'created_by' => $creator?->id,
            ]);

            foreach ($data['items'] as $item) {
                $qty = max(1, (int) ($item['quantity'] ?? 1));
                $price = max(0, (int) ($item['unit_price'] ?? 0));
                $itemDiscount = max(0, (int) ($item['discount_amount'] ?? 0));
                $lineSubtotal = max(0, ($qty * $price) - $itemDiscount);

                InvoiceItem::create([
                    'invoice_id' => $invoice->id,
                    'fee_category_id' => $item['fee_category_id'] ?? null,
                    'item_name' => $item['item_name'],
                    'quantity' => $qty,
                    'unit_price' => $price,
                    'discount_amount' => $itemDiscount,
                    'subtotal' => $lineSubtotal,
                ]);
            }

            return $invoice;
        });
    }

    /**
     * Bulk generate recurring fee invoices (such as SPP) for enrolled students.
     * Prevents duplicate recurring invoices for the same student and period key.
     *
     * @return array{
     *     total_candidates: int,
     *     generated_count: int,
     *     skipped_count: int,
     *     generated_invoices: Collection<int, Invoice>
     * }
     */
    public function bulkGenerateRecurringInvoices(
        School $school,
        FeeCategory $feeCategory,
        AcademicYear $academicYear,
        string $billingPeriodKey,
        string $dueDate,
        ?int $gradeLevelId = null,
        ?int $classroomId = null,
        ?User $creator = null
    ): array {
        return DB::transaction(function () use (
            $school,
            $feeCategory,
            $academicYear,
            $billingPeriodKey,
            $dueDate,
            $gradeLevelId,
            $classroomId,
            $creator
        ) {
            // Find eligible enrolled students
            $enrollmentsQuery = ClassroomStudent::query()
                ->where('academic_year_id', $academicYear->id)
                ->where('enrollment_status', 'active')
                ->whereHas('classroom', function ($q) use ($school, $gradeLevelId, $classroomId) {
                    $q->where('school_id', $school->id);
                    if ($classroomId) {
                        $q->where('id', $classroomId);
                    }
                    if ($gradeLevelId) {
                        $q->where('grade_level_id', $gradeLevelId);
                    }
                })
                ->with(['student.studentProfile', 'classroom.gradeLevel']);

            $enrollments = $enrollmentsQuery->get();
            $totalCandidates = $enrollments->count();

            $generatedInvoices = collect();
            $skippedCount = 0;

            foreach ($enrollments as $enrollment) {
                $student = $enrollment->student;
                if (! $student) {
                    continue;
                }

                // Check for existing invoice for this period
                $exists = Invoice::query()
                    ->where('school_id', $school->id)
                    ->where('student_id', $student->id)
                    ->where('fee_category_id', $feeCategory->id)
                    ->where('billing_period_key', $billingPeriodKey)
                    ->where('status', '!=', Invoice::STATUS_CANCELLED)
                    ->exists();

                if ($exists) {
                    $skippedCount++;

                    continue;
                }

                // Determine base tariff using FeeRule override if exists
                $baseAmount = $feeCategory->getEffectiveAmount(
                    $academicYear->id,
                    $enrollment->classroom?->grade_level_id,
                    $enrollment->classroom_id
                );

                // Check for applicable active student discounts/scholarships
                $discountAmount = 0;
                $activeDiscount = StudentDiscount::query()
                    ->where('school_id', $school->id)
                    ->where('student_id', $student->id)
                    ->where('is_active', true)
                    ->where(function ($q) use ($feeCategory) {
                        $q->where('fee_category_id', $feeCategory->id)
                            ->orWhereNull('fee_category_id');
                    })
                    ->where(function ($q) use ($academicYear) {
                        $q->where('academic_year_id', $academicYear->id)
                            ->orWhereNull('academic_year_id');
                    })
                    ->first();

                if ($activeDiscount) {
                    $discountAmount = $activeDiscount->calculateDiscount($baseAmount);
                }

                $totalAmount = max(0, $baseAmount - $discountAmount);
                $title = "{$feeCategory->name} Periode {$billingPeriodKey}";

                $invoice = Invoice::create([
                    'school_id' => $school->id,
                    'invoice_number' => $this->generateInvoiceNumber($school->id),
                    'student_id' => $student->id,
                    'academic_year_id' => $academicYear->id,
                    'fee_category_id' => $feeCategory->id,
                    'billing_period_key' => $billingPeriodKey,
                    'title' => $title,
                    'issue_date' => Carbon::today()->toDateString(),
                    'due_date' => $dueDate,
                    'subtotal' => $baseAmount,
                    'discount_amount' => $discountAmount,
                    'total_amount' => $totalAmount,
                    'paid_amount' => 0,
                    'status' => Invoice::STATUS_ISSUED,
                    'notes' => $activeDiscount ? "Potongan beasiswa: {$activeDiscount->name}" : null,
                    'created_by' => $creator?->id,
                ]);

                InvoiceItem::create([
                    'invoice_id' => $invoice->id,
                    'fee_category_id' => $feeCategory->id,
                    'item_name' => $feeCategory->name,
                    'quantity' => 1,
                    'unit_price' => $baseAmount,
                    'discount_amount' => $discountAmount,
                    'subtotal' => $totalAmount,
                ]);

                $generatedInvoices->push($invoice);
            }

            return [
                'total_candidates' => $totalCandidates,
                'generated_count' => $generatedInvoices->count(),
                'skipped_count' => $skippedCount,
                'generated_invoices' => $generatedInvoices,
            ];
        });
    }

    /**
     * Record a manual payment (Cash or direct bank transfer verified by school staff).
     *
     * @param array{
     *     amount: int,
     *     payment_date: string,
     *     payment_method: string,
     *     reference_number?: ?string,
     *     notes?: ?string
     * } $data
     */
    public function recordManualPayment(Invoice $invoice, array $data, User $operator): Payment
    {
        return DB::transaction(function () use ($invoice, $data, $operator) {
            // Lock invoice row for concurrency safety
            $lockedInvoice = Invoice::where('id', $invoice->id)->lockForUpdate()->firstOrFail();

            $amount = (int) $data['amount'];
            if ($amount <= 0) {
                throw ValidationException::withMessages(['amount' => 'Nominal pembayaran harus lebih besar dari Rp 0.']);
            }

            $remaining = $lockedInvoice->remaining_balance;
            if ($amount > $remaining) {
                throw ValidationException::withMessages([
                    'amount' => 'Nominal pembayaran (Rp '.number_format($amount, 0, ',', '.').') melebihi sisa tagihan (Rp '.number_format($remaining, 0, ',', '.').').',
                ]);
            }

            $payment = Payment::create([
                'school_id' => $lockedInvoice->school_id,
                'payment_number' => $this->generatePaymentNumber($lockedInvoice->school_id),
                'invoice_id' => $lockedInvoice->id,
                'student_id' => $lockedInvoice->student_id,
                'paid_by' => $lockedInvoice->student_id,
                'amount' => $amount,
                'payment_date' => $data['payment_date'],
                'payment_method' => $data['payment_method'],
                'reference_number' => $data['reference_number'] ?? null,
                'proof_path' => null,
                'status' => Payment::STATUS_VERIFIED,
                'verified_by' => $operator->id,
                'verified_at' => now(),
                'notes' => $data['notes'] ?? null,
                'recorded_by' => $operator->id,
            ]);

            // Issue official receipt
            Receipt::create([
                'school_id' => $lockedInvoice->school_id,
                'receipt_number' => $this->generateReceiptNumber($lockedInvoice->school_id),
                'payment_id' => $payment->id,
                'invoice_id' => $lockedInvoice->id,
                'student_id' => $lockedInvoice->student_id,
                'amount' => $amount,
                'issued_at' => now(),
                'issued_by' => $operator->id,
            ]);

            // Recalculate invoice status & balance
            $lockedInvoice->recalculate();

            return $payment;
        });
    }

    /**
     * Submit manual bank transfer payment proof by parent or student.
     */
    public function submitPaymentProof(
        Invoice $invoice,
        User $payer,
        int $amount,
        string $paymentDate,
        string $paymentMethod,
        ?string $referenceNumber = null,
        ?UploadedFile $proofFile = null,
        ?string $notes = null
    ): Payment {
        return DB::transaction(function () use (
            $invoice,
            $payer,
            $amount,
            $paymentDate,
            $paymentMethod,
            $referenceNumber,
            $proofFile,
            $notes
        ) {
            $lockedInvoice = Invoice::where('id', $invoice->id)->lockForUpdate()->firstOrFail();

            if ($amount <= 0) {
                throw ValidationException::withMessages(['amount' => 'Nominal pembayaran harus lebih besar dari Rp 0.']);
            }

            $remaining = $lockedInvoice->remaining_balance;
            if ($amount > $remaining) {
                throw ValidationException::withMessages([
                    'amount' => 'Nominal pembayaran melebihi sisa tagihan (Rp '.number_format($remaining, 0, ',', '.').').',
                ]);
            }

            $proofPath = null;
            if ($proofFile) {
                // Store in private storage disk
                $proofPath = $proofFile->store('payment-proofs/'.$lockedInvoice->school_id, 'local');
            }

            $payment = Payment::create([
                'school_id' => $lockedInvoice->school_id,
                'payment_number' => $this->generatePaymentNumber($lockedInvoice->school_id),
                'invoice_id' => $lockedInvoice->id,
                'student_id' => $lockedInvoice->student_id,
                'paid_by' => $payer->id,
                'amount' => $amount,
                'payment_date' => $paymentDate,
                'payment_method' => $paymentMethod,
                'reference_number' => $referenceNumber,
                'proof_path' => $proofPath,
                'status' => Payment::STATUS_PENDING_VERIFICATION,
                'notes' => $notes,
                'recorded_by' => $payer->id,
            ]);

            return $payment;
        });
    }

    /**
     * Verify a pending payment submission.
     */
    public function verifyPaymentSubmission(Payment $payment, User $verifier): Payment
    {
        return DB::transaction(function () use ($payment, $verifier) {
            $lockedPayment = Payment::where('id', $payment->id)->lockForUpdate()->firstOrFail();

            if ($lockedPayment->status !== Payment::STATUS_PENDING_VERIFICATION) {
                throw ValidationException::withMessages([
                    'payment' => 'Hanya pembayaran dengan status menunggu verifikasi yang dapat diverifikasi.',
                ]);
            }

            $lockedInvoice = Invoice::where('id', $lockedPayment->invoice_id)->lockForUpdate()->firstOrFail();

            $lockedPayment->update([
                'status' => Payment::STATUS_VERIFIED,
                'verified_by' => $verifier->id,
                'verified_at' => now(),
            ]);

            // Generate receipt if not already exists
            if (! $lockedPayment->receipt) {
                Receipt::create([
                    'school_id' => $lockedPayment->school_id,
                    'receipt_number' => $this->generateReceiptNumber($lockedPayment->school_id),
                    'payment_id' => $lockedPayment->id,
                    'invoice_id' => $lockedInvoice->id,
                    'student_id' => $lockedPayment->student_id,
                    'amount' => $lockedPayment->amount,
                    'issued_at' => now(),
                    'issued_by' => $verifier->id,
                ]);
            }

            $lockedInvoice->recalculate();

            return $lockedPayment;
        });
    }

    /**
     * Reject a pending payment submission with auditable reason.
     */
    public function rejectPaymentSubmission(Payment $payment, User $verifier, string $reason): Payment
    {
        return DB::transaction(function () use ($payment, $verifier, $reason) {
            $lockedPayment = Payment::where('id', $payment->id)->lockForUpdate()->firstOrFail();

            if ($lockedPayment->status !== Payment::STATUS_PENDING_VERIFICATION) {
                throw ValidationException::withMessages([
                    'payment' => 'Hanya pembayaran dengan status menunggu verifikasi yang dapat ditolak.',
                ]);
            }

            $lockedPayment->update([
                'status' => Payment::STATUS_REJECTED,
                'verified_by' => $verifier->id,
                'verified_at' => now(),
                'rejection_reason' => $reason,
            ]);

            $invoice = Invoice::find($lockedPayment->invoice_id);
            $invoice?->recalculate();

            return $lockedPayment;
        });
    }

    /**
     * Reversal/correction of a previously verified payment.
     * Preserves audit history and prevents silent deletions.
     */
    public function reversePayment(Payment $payment, User $actor, string $reason): PaymentReversal
    {
        return DB::transaction(function () use ($payment, $actor, $reason) {
            $lockedPayment = Payment::where('id', $payment->id)->lockForUpdate()->firstOrFail();

            if ($lockedPayment->status !== Payment::STATUS_VERIFIED) {
                throw ValidationException::withMessages([
                    'payment' => 'Hanya pembayaran terverifikasi yang dapat dibatalkan/dikoreksi.',
                ]);
            }

            $reversal = PaymentReversal::create([
                'school_id' => $lockedPayment->school_id,
                'payment_id' => $lockedPayment->id,
                'invoice_id' => $lockedPayment->invoice_id,
                'amount' => $lockedPayment->amount,
                'reason' => $reason,
                'reversed_by' => $actor->id,
            ]);

            $lockedPayment->update([
                'status' => Payment::STATUS_REVERSED,
            ]);

            $lockedInvoice = Invoice::where('id', $lockedPayment->invoice_id)->lockForUpdate()->firstOrFail();
            $lockedInvoice->recalculate();

            return $reversal;
        });
    }

    /**
     * Send or log a payment reminder for an outstanding invoice.
     */
    public function logPaymentReminder(
        Invoice $invoice,
        User $sender,
        string $reminderType = 'due_date',
        string $channel = 'in_app',
        ?string $notes = null
    ): PaymentReminder {
        return PaymentReminder::create([
            'school_id' => $invoice->school_id,
            'invoice_id' => $invoice->id,
            'student_id' => $invoice->student_id,
            'reminder_type' => $reminderType,
            'channel' => $channel,
            'sent_at' => now(),
            'sent_by' => $sender->id,
            'status' => 'sent',
            'notes' => $notes ?? "Pengingat {$reminderType} untuk tagihan {$invoice->invoice_number}",
        ]);
    }
}
