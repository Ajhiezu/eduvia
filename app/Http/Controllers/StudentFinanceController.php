<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StudentFinanceController extends Controller
{
    /**
     * Student portal finance overview: own invoices & receipts.
     */
    public function index(Request $request): View
    {
        $student = $request->user();

        $invoices = Invoice::query()
            ->where('student_id', $student->id)
            ->where('status', '!=', Invoice::STATUS_CANCELLED)
            ->with(['feeCategory', 'academicYear', 'items'])
            ->latest('due_date')
            ->get();

        $payments = Payment::query()
            ->where('student_id', $student->id)
            ->with(['invoice.feeCategory', 'receipt'])
            ->latest('payment_date')
            ->get();

        $totalInvoiced = (int) $invoices->sum('total_amount');
        $totalPaid = (int) $invoices->sum('paid_amount');
        $totalOutstanding = max(0, $totalInvoiced - $totalPaid);

        return view('student.finance.index', compact(
            'student',
            'invoices',
            'payments',
            'totalInvoiced',
            'totalPaid',
            'totalOutstanding'
        ));
    }

    /**
     * Student view of specific invoice.
     */
    public function showInvoice(Invoice $invoice, Request $request): View
    {
        $student = $request->user();

        if ((int) $invoice->student_id !== (int) $student->id) {
            abort(403, 'Akses ditolak: Tagihan ini bukan milik akun Anda.');
        }

        $invoice->load([
            'academicYear',
            'feeCategory',
            'items',
            'payments.receipt',
        ]);

        return view('student.finance.show', compact('invoice'));
    }
}
