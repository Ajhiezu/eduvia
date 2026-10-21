<x-app-layout>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-6 border-b border-zinc-200">
            <div>
                <nav class="flex items-center gap-2 text-xs text-zinc-500 mb-2">
                    <span class="text-zinc-500">Keuangan Sekolah</span>
                    <span>&bull;</span>
                    <span class="text-zinc-800 font-semibold">Riwayat Pembayaran</span>
                </nav>
                <h1 class="text-2xl font-bold text-zinc-900 tracking-tight">Riwayat Pembayaran Masuk</h1>
                <p class="text-sm text-zinc-500 mt-1">Audit log seluruh transaksi setoran tunai dan transfer bank yang tercatat.</p>
            </div>
            <div class="flex flex-wrap items-center gap-2.5">
                <a
                    href="{{ route('finance.payments.queue') }}"
                    class="relative inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-zinc-900 text-white text-xs font-semibold hover:bg-zinc-800 transition-colors shadow-xs"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span>Antrean Verifikasi</span>
                    @if ($totalPendingCount > 0)
                        <span class="px-1.5 py-0.5 rounded-full bg-[#E11D2E] text-white text-[10px] font-bold">
                            {{ $totalPendingCount }}
                        </span>
                    @endif
                </a>
                <a
                    href="{{ route('finance.payments.create') }}"
                    class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-[#E11D2E] text-white text-xs font-semibold hover:bg-[#c81926] transition-colors shadow-xs"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    <span>Catat Setoran Manual</span>
                </a>
            </div>
        </div>

        @if (session('success'))
            <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm">
                {{ session('success') }}
            </div>
        @endif

        <!-- KPI Metrics -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div class="bg-white p-5 rounded-2xl border border-zinc-200 shadow-xs">
                <div class="text-xs font-bold text-emerald-600 uppercase tracking-wider">Total Kas Masuk Terverifikasi</div>
                <div class="text-2xl font-extrabold text-emerald-700 mt-2">Rp {{ number_format($totalVerifiedAmount, 0, ',', '.') }}</div>
                <div class="text-[11px] text-zinc-500 mt-1">Pembayaran resmi yang telah diterbitkan kuitansinya</div>
            </div>
            <div class="bg-white p-5 rounded-2xl border border-zinc-200 shadow-xs">
                <div class="text-xs font-bold text-amber-600 uppercase tracking-wider">Menunggu Verifikasi Staf</div>
                <div class="text-2xl font-extrabold text-amber-700 mt-2">{{ $totalPendingCount }} Pengajuan</div>
                <div class="text-[11px] text-zinc-500 mt-1">Bukti transfer yang diajukan oleh wali murid</div>
            </div>
            <div class="bg-white p-5 rounded-2xl border border-zinc-200 shadow-xs">
                <div class="text-xs font-bold text-zinc-400 uppercase tracking-wider">Transaksi Dikoreksi / Reversal</div>
                <div class="text-2xl font-extrabold text-zinc-700 mt-2">{{ $totalReversedCount }} Transaksi</div>
                <div class="text-[11px] text-zinc-500 mt-1">Audit transaksi yang dibatalkan oleh petugas</div>
            </div>
        </div>

        <!-- Filter Form -->
        <form method="GET" action="{{ route('finance.payments.index') }}" class="bg-white p-4 rounded-2xl border border-zinc-200 shadow-xs space-y-3">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3 text-xs">
                <div class="lg:col-span-2">
                    <label class="block text-sm font-medium text-zinc-700 mb-1.5">Cari Transaksi / Siswa</label>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="No Pembayaran, Nama Siswa, No Tagihan..." class="w-full px-3.5 py-2.5 rounded-lg border border-zinc-200 text-sm focus:border-[#E11D2E] focus:ring-2 focus:ring-[#E11D2E]/20 placeholder-zinc-400">
                </div>
                <div>
                    <label class="block text-sm font-medium text-zinc-700 mb-1.5">Metode</label>
                    <select name="method" class="w-full px-3 py-2.5 rounded-lg border border-zinc-200 text-sm focus:border-[#E11D2E] focus:ring-2 focus:ring-[#E11D2E]/20">
                        <option value="">Semua Metode</option>
                        <option value="cash" {{ request('method') === 'cash' ? 'selected' : '' }}>Tunai (Kasir)</option>
                        <option value="bank_transfer" {{ request('method') === 'bank_transfer' ? 'selected' : '' }}>Transfer Bank</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-zinc-700 mb-1.5">Dari Tanggal</label>
                    <input type="date" name="start_date" value="{{ request('start_date') }}" class="w-full px-3 py-2.5 rounded-lg border border-zinc-200 text-sm focus:border-[#E11D2E] focus:ring-2 focus:ring-[#E11D2E]/20">
                </div>
                <div>
                    <label class="block text-sm font-medium text-zinc-700 mb-1.5">Sampai Tanggal</label>
                    <input type="date" name="end_date" value="{{ request('end_date') }}" class="w-full px-3 py-2.5 rounded-lg border border-zinc-200 text-sm focus:border-[#E11D2E] focus:ring-2 focus:ring-[#E11D2E]/20">
                </div>
            </div>
            <div class="flex items-center justify-end gap-3 pt-3 border-t border-zinc-100">
                <a href="{{ route('finance.payments.index') }}" class="px-4 py-2.5 rounded-lg border border-zinc-200 text-zinc-600 text-sm font-medium hover:bg-zinc-50 transition-colors">Reset</a>
                <button type="submit" class="px-5 py-2.5 rounded-lg bg-zinc-900 text-white text-sm font-semibold hover:bg-zinc-800 transition-colors">Terapkan Filter</button>
            </div>
        </form>

        <!-- Payments Table -->
        <div class="bg-white rounded-2xl border border-zinc-200 shadow-xs overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-zinc-50/80 text-zinc-500 font-semibold border-b border-zinc-200 uppercase tracking-wider text-[11px]">
                        <tr>
                            <th class="px-5 py-3.5">No. Pembayaran</th>
                            <th class="px-5 py-3.5">Tanggal</th>
                            <th class="px-5 py-3.5">Nama Siswa</th>
                            <th class="px-5 py-3.5">No. Tagihan</th>
                            <th class="px-5 py-3.5">Metode</th>
                            <th class="px-5 py-3.5 text-right">Nominal</th>
                            <th class="px-5 py-3.5 text-center">Status</th>
                            <th class="px-5 py-3.5 text-right">Kuitansi / Audit</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-200">
                        @forelse ($payments as $pay)
                            <tr class="hover:bg-zinc-50/60 transition-colors">
                                <td class="px-5 py-3.5 font-mono font-bold text-zinc-900">{{ $pay->payment_number }}</td>
                                <td class="px-5 py-3.5 text-zinc-700">{{ $pay->payment_date ? $pay->payment_date->format('d/m/Y') : '-' }}</td>
                                <td class="px-5 py-3.5 font-bold text-zinc-900">{{ $pay->student?->name }}</td>
                                <td class="px-5 py-3.5 font-mono text-zinc-600">
                                    <a href="{{ route('finance.invoices.show', $pay->invoice_id) }}" class="hover:text-brand-600 underline">
                                        {{ $pay->invoice?->invoice_number }}
                                    </a>
                                </td>
                                <td class="px-5 py-3.5 text-zinc-700">{{ $pay->method_label }}</td>
                                <td class="px-5 py-3.5 text-right font-mono font-extrabold text-zinc-900">{{ $pay->formatted_amount }}</td>
                                <td class="px-5 py-3.5 text-center">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold border {{ $pay->status_badge_class }}">
                                        {{ $pay->status_label }}
                                    </span>
                                </td>
                                <td class="px-5 py-3.5 text-right">
                                    @if ($pay->receipt)
                                        <a href="{{ route('finance.pdf.receipt', ['receipt' => $pay->receipt, 'mode' => 'download']) }}" class="inline-flex items-center gap-1 text-emerald-700 hover:text-emerald-900 font-semibold" title="Unduh Kwitansi">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                            <span>{{ $pay->receipt->receipt_number }}</span>
                                        </a>
                                    @else
                                        <span class="text-zinc-400">-</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-5 py-12 text-center text-zinc-400">
                                    Belum ada transaksi pembayaran yang tercatat.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($payments->hasPages())
                <div class="px-5 py-3 border-t border-zinc-200">
                    {{ $payments->links() }}
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
