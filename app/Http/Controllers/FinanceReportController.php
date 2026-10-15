<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Payment;
use App\Models\PaymentReversal;
use App\Models\School;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class FinanceReportController extends Controller
{
    protected function currentSchoolId(): ?int
    {
        return auth()->user()?->school_id ?? School::current()?->id;
    }

    /**
     * Financial report and reconciliation dashboard.
     */
    public function index(Request $request): View
    {
        $schoolId = $this->currentSchoolId();

        $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->toDateString());
        $endDate = $request->input('end_date', Carbon::now()->endOfMonth()->toDateString());

        // Invoices Issued
        $invoicesQuery = Invoice::query()
            ->when($schoolId, fn ($q) => $q->where('school_id', $schoolId))
            ->whereBetween('issue_date', [$startDate, $endDate]);

        $totalInvoicedAmount = (int) (clone $invoicesQuery)->where('status', '!=', Invoice::STATUS_CANCELLED)->sum('total_amount');
        $totalInvoicesCount = (clone $invoicesQuery)->where('status', '!=', Invoice::STATUS_CANCELLED)->count();

        // Verified Collections
        $paymentsQuery = Payment::query()
            ->when($schoolId, fn ($q) => $q->where('school_id', $schoolId))
            ->where('status', Payment::STATUS_VERIFIED)
            ->whereBetween('payment_date', [$startDate, $endDate]);

        $totalCollectedAmount = (int) (clone $paymentsQuery)->sum('amount');
        $totalPaymentsCount = (clone $paymentsQuery)->count();

        // Pending Submissions
        $pendingAmount = (int) Payment::query()
            ->when($schoolId, fn ($q) => $q->where('school_id', $schoolId))
            ->where('status', Payment::STATUS_PENDING_VERIFICATION)
            ->sum('amount');

        // All-time Outstanding & Overdue
        $allInvoices = Invoice::query()->when($schoolId, fn ($q) => $q->where('school_id', $schoolId));
        $allInvoicedSum = (int) (clone $allInvoices)->where('status', '!=', Invoice::STATUS_CANCELLED)->sum('total_amount');
        $allPaidSum = (int) (clone $allInvoices)->where('status', '!=', Invoice::STATUS_CANCELLED)->sum('paid_amount');
        $totalOutstandingReceivables = max(0, $allInvoicedSum - $allPaidSum);

        $totalOverdueAmount = (int) (clone $allInvoices)->overdue()->sum(DB::raw('total_amount - paid_amount'));

        // Collections by Payment Method
        $collectionsByMethod = Payment::query()
            ->when($schoolId, fn ($q) => $q->where('school_id', $schoolId))
            ->where('status', Payment::STATUS_VERIFIED)
            ->whereBetween('payment_date', [$startDate, $endDate])
            ->select('payment_method', DB::raw('SUM(amount) as total_amount'), DB::raw('COUNT(*) as total_count'))
            ->groupBy('payment_method')
            ->get();

        // Collections by Fee Category
        $collectionsByCategory = Payment::query()
            ->when($schoolId, fn ($q) => $q->where('payments.school_id', $schoolId))
            ->where('payments.status', Payment::STATUS_VERIFIED)
            ->whereBetween('payments.payment_date', [$startDate, $endDate])
            ->join('invoices', 'payments.invoice_id', '=', 'invoices.id')
            ->leftJoin('fee_categories', 'invoices.fee_category_id', '=', 'fee_categories.id')
            ->select(
                DB::raw('COALESCE(fee_categories.name, "Biaya Lainnya") as category_name'),
                DB::raw('SUM(payments.amount) as total_amount'),
                DB::raw('COUNT(payments.id) as total_count')
            )
            ->groupBy('category_name')
            ->get();

        // Reversals
        $reversals = PaymentReversal::query()
            ->when($schoolId, fn ($q) => $q->where('school_id', $schoolId))
            ->with(['payment', 'invoice', 'reverser'])
            ->latest('id')
            ->take(10)
            ->get();

        $totalReversalsAmount = (int) PaymentReversal::query()
            ->when($schoolId, fn ($q) => $q->where('school_id', $schoolId))
            ->sum('amount');

        return view('finance.reports.index', compact(
            'startDate',
            'endDate',
            'totalInvoicedAmount',
            'totalInvoicesCount',
            'totalCollectedAmount',
            'totalPaymentsCount',
            'pendingAmount',
            'totalOutstandingReceivables',
            'totalOverdueAmount',
            'collectionsByMethod',
            'collectionsByCategory',
            'reversals',
            'totalReversalsAmount'
        ));
    }

    /**
     * Export transaction ledger to CSV.
     */
    public function exportCsv(Request $request): StreamedResponse
    {
        $schoolId = $this->currentSchoolId();
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        $payments = Payment::query()
            ->when($schoolId, fn ($q) => $q->where('school_id', $schoolId))
            ->when($startDate, fn ($q) => $q->whereDate('payment_date', '>=', $startDate))
            ->when($endDate, fn ($q) => $q->whereDate('payment_date', '<=', $endDate))
            ->with(['student.studentProfile', 'invoice.feeCategory', 'recorder', 'verifier'])
            ->orderBy('payment_date', 'desc')
            ->get();

        $filename = 'Laporan_Keuangan_'.date('Y-m-d_His').'.csv';

        return response()->streamDownload(function () use ($payments) {
            $handle = fopen('php://output', 'w');
            // Write UTF-8 BOM for Excel compatibility
            fwrite($handle, "\xEF\xBB\xBF");

            // CSV Header
            fputcsv($handle, [
                'No',
                'Nomor Pembayaran',
                'Tanggal',
                'Nomor Tagihan',
                'Nama Siswa',
                'NIS',
                'Kategori Biaya',
                'Metode Pembayaran',
                'Nominal (IDR)',
                'Status',
                'Referensi Bank',
                'Dicatat Oleh',
                'Diverifikasi Oleh',
            ]);

            $no = 1;
            foreach ($payments as $payment) {
                fputcsv($handle, [
                    $no++,
                    $payment->payment_number,
                    $payment->payment_date ? $payment->payment_date->format('d/m/Y') : '-',
                    $payment->invoice?->invoice_number ?? '-',
                    $payment->student?->name ?? '-',
                    $payment->student?->studentProfile?->nis ?? '-',
                    $payment->invoice?->feeCategory?->name ?? 'Umum',
                    $payment->method_label,
                    $payment->amount,
                    $payment->status_label,
                    $payment->reference_number ?? '-',
                    $payment->recorder?->name ?? '-',
                    $payment->verifier?->name ?? '-',
                ]);
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }
}
