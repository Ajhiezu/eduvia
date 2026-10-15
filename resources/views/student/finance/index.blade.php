<x-app-layout>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-6 border-b border-zinc-200">
            <div>
                <nav class="flex items-center gap-2 text-xs text-zinc-500 mb-2">
                    <span class="text-zinc-500">Portal Siswa</span>
                    <span>&bull;</span>
                    <span class="text-zinc-800 font-semibold">Tagihan & Pembayaran</span>
                </nav>
                <h1 class="text-2xl font-bold text-zinc-900 tracking-tight">Status Tagihan & Riwayat Pembayaran</h1>
                <p class="text-sm text-zinc-500 mt-1">Informasi resmi tagihan biaya sekolah dan bukti kuitansi pembayaran yang telah lunas.</p>
            </div>
        </div>

        <!-- Financial Summary -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div class="bg-white p-5 rounded-2xl border border-zinc-200 shadow-xs">
                <div class="text-[11px] font-bold text-zinc-400 uppercase tracking-wider">Total Tagihan Pendidikan</div>
                <div class="text-2xl font-extrabold text-zinc-900 mt-2">Rp {{ number_format($totalInvoiced, 0, ',', '.') }}</div>
            </div>
            <div class="bg-white p-5 rounded-2xl border border-zinc-200 shadow-xs">
                <div class="text-[11px] font-bold text-emerald-600 uppercase tracking-wider">Sudah Terbayar</div>
                <div class="text-2xl font-extrabold text-emerald-700 mt-2">Rp {{ number_format($totalPaid, 0, ',', '.') }}</div>
            </div>
            <div class="bg-white p-5 rounded-2xl border border-zinc-200 shadow-xs">
                <div class="text-[11px] font-bold text-amber-600 uppercase tracking-wider">Sisa Kewajiban</div>
                <div class="text-2xl font-extrabold text-amber-700 mt-2">Rp {{ number_format($totalOutstanding, 0, ',', '.') }}</div>
            </div>
        </div>

        <!-- Invoices List -->
        <div class="bg-white rounded-2xl border border-zinc-200 shadow-xs overflow-hidden">
            <div class="px-5 py-4 border-b border-zinc-200 bg-zinc-50/50 flex justify-between items-center">
                <h3 class="text-xs font-bold text-zinc-800 uppercase tracking-wider">Daftar Tagihan Biaya Pendidikan</h3>
                <span class="text-xs text-zinc-500">{{ $invoices->count() }} Tagihan</span>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-zinc-50/80 text-zinc-500 font-semibold border-b border-zinc-200 uppercase tracking-wider text-[11px]">
                        <tr>
                            <th class="px-5 py-3.5">No. Tagihan</th>
                            <th class="px-5 py-3.5">Perihal</th>
                            <th class="px-5 py-3.5">Jatuh Tempo</th>
                            <th class="px-5 py-3.5 text-right">Total Tagihan</th>
                            <th class="px-5 py-3.5 text-right">Sisa Tagihan</th>
                            <th class="px-5 py-3.5 text-center">Status</th>
                            <th class="px-5 py-3.5 text-right">Faktur</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-200">
                        @forelse ($invoices as $inv)
                            <tr class="hover:bg-zinc-50/60 transition-colors">
                                <td class="px-5 py-3.5 font-mono font-bold text-zinc-900">{{ $inv->invoice_number }}</td>
                                <td class="px-5 py-3.5 font-semibold text-zinc-800">{{ $inv->title }}</td>
                                <td class="px-5 py-3.5 text-zinc-700">{{ $inv->due_date ? $inv->due_date->format('d/m/Y') : '-' }}</td>
                                <td class="px-5 py-3.5 text-right font-mono font-semibold text-zinc-800">{{ $inv->formatted_total }}</td>
                                <td class="px-5 py-3.5 text-right font-mono font-bold text-zinc-900">
                                    @if ($inv->remaining_balance > 0)
                                        <span class="text-amber-700">{{ $inv->formatted_remaining }}</span>
                                    @else
                                        <span class="text-emerald-700">Rp 0 (Lunas)</span>
                                    @endif
                                </td>
                                <td class="px-5 py-3.5 text-center">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold border {{ $inv->status_badge_class }}">
                                        {{ $inv->status_label }}
                                    </span>
                                </td>
                                <td class="px-5 py-3.5 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <a href="{{ route('student.finance.show', $inv) }}" class="px-2.5 py-1 rounded-md border border-zinc-200 hover:bg-zinc-50 text-xs font-semibold">
                                            Rincian
                                        </a>
                                        <a href="{{ route('finance.pdf.invoice', ['invoice' => $inv, 'mode' => 'download']) }}" class="p-1 rounded-md text-zinc-500 hover:text-brand-600" title="Unduh PDF">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-5 py-8 text-center text-zinc-400">
                                    Tidak ada tagihan yang tercatat untuk akun Anda.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Receipts List -->
        <div class="bg-white rounded-2xl border border-zinc-200 shadow-xs overflow-hidden">
            <div class="px-5 py-4 border-b border-zinc-200 bg-zinc-50/50 flex justify-between items-center">
                <h3 class="text-xs font-bold text-zinc-800 uppercase tracking-wider">Kuitansi Pembayaran Resmi</h3>
                <span class="text-xs text-zinc-500">{{ $payments->count() }} Transaksi</span>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-zinc-50/80 text-zinc-500 font-semibold border-b border-zinc-200 uppercase tracking-wider text-[11px]">
                        <tr>
                            <th class="px-5 py-3">No. Transaksi</th>
                            <th class="px-5 py-3">Tanggal</th>
                            <th class="px-5 py-3">Tagihan</th>
                            <th class="px-5 py-3 text-right">Nominal</th>
                            <th class="px-5 py-3 text-center">Status</th>
                            <th class="px-5 py-3 text-right">Kuitansi Resmi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-200">
                        @forelse ($payments as $pay)
                            <tr class="hover:bg-zinc-50/60 transition-colors">
                                <td class="px-5 py-3.5 font-mono font-bold text-zinc-900">{{ $pay->payment_number }}</td>
                                <td class="px-5 py-3.5 text-zinc-700">{{ $pay->payment_date ? $pay->payment_date->format('d/m/Y') : '-' }}</td>
                                <td class="px-5 py-3.5 font-semibold text-zinc-800">{{ $pay->invoice?->title }}</td>
                                <td class="px-5 py-3.5 text-right font-mono font-bold text-zinc-900">{{ $pay->formatted_amount }}</td>
                                <td class="px-5 py-3.5 text-center">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold border {{ $pay->status_badge_class }}">
                                        {{ $pay->status_label }}
                                    </span>
                                </td>
                                <td class="px-5 py-3.5 text-right">
                                    @if ($pay->receipt)
                                        <a href="{{ route('finance.pdf.receipt', ['receipt' => $pay->receipt, 'mode' => 'download']) }}" class="inline-flex items-center gap-1 text-emerald-700 hover:text-emerald-900 font-bold">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                            <span>Unduh Kwitansi</span>
                                        </a>
                                    @else
                                        <span class="text-zinc-400">-</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-5 py-8 text-center text-zinc-400">Belum ada riwayat kuitansi.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
