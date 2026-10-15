<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Receipt;
use App\Models\School;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Str;

class InvoicePdfController extends Controller
{
    protected function currentSchoolId(): ?int
    {
        return auth()->user()?->school_id ?? School::current()?->id;
    }

    /**
     * Authorize access to invoice or receipt.
     */
    protected function authorizeAccess(int $schoolId, int $studentId): void
    {
        $user = auth()->user();
        $currentSchoolId = $this->currentSchoolId();

        if ($currentSchoolId && (int) $schoolId !== (int) $currentSchoolId) {
            abort(403, 'Akses ditolak: Dokumen ini tidak terdaftar pada sekolah Anda.');
        }

        // Super Admin & School Admin have access
        if ($user->isSuperAdmin() || $user->isSchoolAdmin() || $user->hasRole('staff')) {
            return;
        }

        // Student can only access their own
        if ($user->isStudent()) {
            if ((int) $studentId !== (int) $user->id) {
                abort(403, 'Akses ditolak: Anda hanya berhak mengakses dokumen milik Anda sendiri.');
            }

            return;
        }

        // Parent can only access their verified linked children
        if ($user->isParent()) {
            $isLinkedChild = $user->activeChildren()->where('users.id', $studentId)->exists();
            if (! $isLinkedChild) {
                abort(403, 'Akses ditolak: Siswa ini bukan anak yang terhubung aktif dengan akun Anda.');
            }

            return;
        }

        abort(403, 'Akses ditolak.');
    }

    /**
     * Generate official invoice PDF document.
     */
    public function downloadInvoice(Invoice $invoice, Request $request): Response
    {
        $this->authorizeAccess($invoice->school_id, $invoice->student_id);

        $invoice->loadMissing([
            'school',
            'student.studentProfile',
            'academicYear',
            'feeCategory',
            'items',
            'payments' => fn ($q) => $q->where('status', 'verified'),
        ]);

        $pdf = Pdf::loadView('finance.pdf.invoice', [
            'invoice' => $invoice,
            'school' => $invoice->school,
        ])->setPaper('a4', 'portrait');

        $cleanNumber = Str::slug(str_replace('/', '-', $invoice->invoice_number));
        $filename = "Tagihan_{$cleanNumber}.pdf";

        if ($request->query('mode') === 'download') {
            return $pdf->download($filename);
        }

        return $pdf->stream($filename);
    }

    /**
     * Generate official payment receipt (Kwitansi) PDF document.
     */
    public function downloadReceipt(Receipt $receipt, Request $request): Response
    {
        $this->authorizeAccess($receipt->school_id, $receipt->student_id);

        $receipt->loadMissing([
            'school',
            'student.studentProfile',
            'invoice.feeCategory',
            'payment',
            'issuer',
        ]);

        $pdf = Pdf::loadView('finance.pdf.receipt', [
            'receipt' => $receipt,
            'school' => $receipt->school,
        ])->setPaper('a5', 'landscape');

        $cleanNumber = Str::slug(str_replace('/', '-', $receipt->receipt_number));
        $filename = "Kwitansi_{$cleanNumber}.pdf";

        if ($request->query('mode') === 'download') {
            return $pdf->download($filename);
        }

        return $pdf->stream($filename);
    }
}
