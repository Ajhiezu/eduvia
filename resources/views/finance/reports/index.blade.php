<x-app-layout>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-6 border-b border-zinc-200">
            <div>
                <nav class="flex items-center gap-2 text-xs text-zinc-500 mb-2">
                    <span class="text-zinc-500">Keuangan Sekolah</span>
                    <span>&bull;</span>
                    <span class="text-zinc-800 font-semibold">Laporan & Rekonsiliasi</span>
                </nav>
                <h1 class="text-2xl font-bold text-zinc-900 tracking-tight">Laporan Keuangan & Rekonsiliasi</h1>
                <p class="text-sm text-zinc-500 mt-1">Audit transaksi penerimaan kas, piutang tertunggak, dan rincian per pos anggaran biaya.</p>
            </div>
            <div class="flex items-center gap-3">
                <a
                    href="{{ route('finance.reports.export-csv', ['start_date' => $startDate, 'end_date' => $endDate]) }}"
                    class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-emerald-700 text-white text-xs font-semibold hover:bg-emerald-800 transition-colors shadow-xs"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    <span>Ekspor Ledger ke CSV</span>
                </a>
            </div>
        </div>

        <!-- Date Range Filter -->
        <form method="GET" action="{{ route('finance.reports.index') }}" class="bg-white p-4 rounded-2xl border border-zinc-200 shadow-xs flex flex-wrap items-end gap-3 text-xs">
            <div>
                <label class="block font-semibold text-zinc-700 mb-1">Periode Awal</label>
                <input type="date" name="start_date" value="{{ $startDate }}" class="px-3 py-1.5 rounded-lg border border-zinc-300">
            </div>
            <div>
                <label class="block font-semibold text-zinc-700 mb-1">Periode Akhir</label>
                <input type="date" name="end_date" value="{{ $endDate }}" class="px-3 py-1.5 rounded-lg border border-zinc-300">
            </div>
            <button type="submit" class="px-4 py-1.5 rounded-lg bg-zinc-900 text-white font-semibold hover:bg-zinc-800">
                Tampilkan Laporan
            </button>
        </form>

        <!-- KPI Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="bg-white p-5 rounded-2xl border border-zinc-200 shadow-xs">
                <div class="text-[11px] font-bold text-zinc-400 uppercase tracking-wider">Tagihan Terbit (Periode Ini)</div>
                <div class="text-xl font-extrabold text-zinc-900 mt-2">Rp {{ number_format($totalInvoicedAmount, 0, ',', '.') }}</div>
                <div class="text-[11px] text-zinc-500 mt-1">{{ $totalInvoicesCount }} Dokumen Faktur</div>
            </div>

            <div class="bg-white p-5 rounded-2xl border border-zinc-200 shadow-xs">
                <div class="text-[11px] font-bold text-emerald-600 uppercase tracking-wider">Kas Masuk Terverifikasi</div>
                <div class="text-xl font-extrabold text-emerald-700 mt-2">Rp {{ number_format($totalCollectedAmount, 0, ',', '.') }}</div>
                <div class="text-[11px] text-emerald-600/80 mt-1">{{ $totalPaymentsCount }} Transaksi Resmi</div>
            </div>

            <div class="bg-white p-5 rounded-2xl border border-zinc-200 shadow-xs">
                <div class="text-[11px] font-bold text-amber-600 uppercase tracking-wider">Total Piutang Berjalan</div>
                <div class="text-xl font-extrabold text-amber-700 mt-2">Rp {{ number_format($totalOutstandingReceivables, 0, ',', '.') }}</div>
                <div class="text-[11px] text-amber-600/80 mt-1">Sisa tagihan belum terbayar</div>
            </div>

            <div class="bg-white p-5 rounded-2xl border border-zinc-200 shadow-xs">
                <div class="text-[11px] font-bold text-rose-600 uppercase tracking-wider">Piutang Jatuh Tempo</div>
                <div class="text-xl font-extrabold text-rose-700 mt-2">Rp {{ number_format($totalOverdueAmount, 0, ',', '.') }}</div>
                <div class="text-[11px] text-rose-600/80 mt-1">Melewati batas tanggal due date</div>
            </div>
        </div>

        <!-- Breakdown Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Pos Kategori Biaya -->
            <div class="bg-white rounded-2xl border border-zinc-200 shadow-xs overflow-hidden">
                <div class="px-5 py-4 border-b border-zinc-200 bg-zinc-50/50">
                    <h3 class="text-xs font-bold text-zinc-800 uppercase tracking-wider">Penerimaan per Pos Kategori Biaya</h3>
                </div>
                <div class="p-5">
                    <table class="w-full text-left text-xs">
                        <thead class="text-zinc-500 uppercase font-semibold text-[10px] border-b border-zinc-200">
                            <tr>
                                <th class="pb-2">Kategori</th>
                                <th class="pb-2 text-center">Frekuensi</th>
                                <th class="pb-2 text-right">Total Terkumpul</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-100">
                            @forelse ($collectionsByCategory as $item)
                                <tr>
                                    <td class="py-2.5 font-bold text-zinc-900">{{ $item->category_name }}</td>
                                    <td class="py-2.5 text-center text-zinc-600 font-mono">{{ $item->total_count }}x</td>
                                    <td class="py-2.5 text-right font-mono font-bold text-emerald-700">Rp {{ number_format($item->total_amount, 0, ',', '.') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="py-6 text-center text-zinc-400">Belum ada penerimaan pada periode ini.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Metode Pembayaran -->
            <div class="bg-white rounded-2xl border border-zinc-200 shadow-xs overflow-hidden">
                <div class="px-5 py-4 border-b border-zinc-200 bg-zinc-50/50">
                    <h3 class="text-xs font-bold text-zinc-800 uppercase tracking-wider">Penerimaan per Metode Saluran</h3>
                </div>
                <div class="p-5">
                    <table class="w-full text-left text-xs">
                        <thead class="text-zinc-500 uppercase font-semibold text-[10px] border-b border-zinc-200">
                            <tr>
                                <th class="pb-2">Saluran Pembayaran</th>
                                <th class="pb-2 text-center">Jumlah Transaksi</th>
                                <th class="pb-2 text-right">Total Kas</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-100">
                            @forelse ($collectionsByMethod as $method)
                                <tr>
                                    <td class="py-2.5 font-bold text-zinc-900">
                                        {{ $method->payment_method === 'cash' ? 'Tunai (Kasir Sekolah)' : ($method->payment_method === 'bank_transfer' ? 'Transfer Bank' : 'Lainnya') }}
                                    </td>
                                    <td class="py-2.5 text-center text-zinc-600 font-mono">{{ $method->total_count }}x</td>
                                    <td class="py-2.5 text-right font-mono font-bold text-emerald-700">Rp {{ number_format($method->total_amount, 0, ',', '.') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="py-6 text-center text-zinc-400">Belum ada transaksi pada periode ini.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Audit Reversal / Pembatalan -->
        <div class="bg-white rounded-2xl border border-zinc-200 shadow-xs overflow-hidden">
            <div class="px-5 py-4 border-b border-zinc-200 bg-zinc-50/50 flex justify-between items-center">
                <h3 class="text-xs font-bold text-zinc-800 uppercase tracking-wider">Audit Log Pembatalan & Koreksi (Reversals)</h3>
                <span class="text-xs font-bold text-rose-600">Total Koreksi: Rp {{ number_format($totalReversalsAmount, 0, ',', '.') }}</span>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-zinc-50/80 text-zinc-500 font-semibold border-b border-zinc-200 uppercase tracking-wider text-[11px]">
                        <tr>
                            <th class="px-5 py-3">Waktu Koreksi</th>
                            <th class="px-5 py-3">No. Transaksi</th>
                            <th class="px-5 py-3">No. Tagihan</th>
                            <th class="px-5 py-3">Nominal Dibatalkan</th>
                            <th class="px-5 py-3">Alasan Koreksi</th>
                            <th class="px-5 py-3 text-right">Petugas / Operator</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-200">
                        @forelse ($reversals as $rev)
                            <tr>
                                <td class="px-5 py-3 font-mono text-zinc-600">{{ $rev->created_at->format('d/m/Y H:i') }}</td>
                                <td class="px-5 py-3 font-mono font-bold text-zinc-900">{{ $rev->payment?->payment_number }}</td>
                                <td class="px-5 py-3 font-mono text-zinc-600">{{ $rev->invoice?->invoice_number }}</td>
                                <td class="px-5 py-3 font-mono font-bold text-rose-600">{{ $rev->formatted_amount }}</td>
                                <td class="px-5 py-3 text-zinc-700">{{ $rev->reason }}</td>
                                <td class="px-5 py-3 text-right font-semibold text-zinc-900">{{ $rev->reverser?->name }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-5 py-8 text-center text-zinc-400">
                                    Tidak ada catatan pembatalan pembayaran (transaksi keuangan bersih tanpa reversal).
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
