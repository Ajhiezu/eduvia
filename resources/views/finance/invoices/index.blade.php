<x-app-layout>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-6 border-b border-zinc-200">
            <div>
                <nav class="flex items-center gap-2 text-xs text-zinc-500 mb-2">
                    <span class="text-zinc-500">Keuangan Sekolah</span>
                    <span>&bull;</span>
                    <span class="text-zinc-800 font-semibold">Manajemen Tagihan</span>
                </nav>
                <h1 class="text-2xl font-bold text-zinc-900 tracking-tight">Daftar Tagihan Siswa (Invoices)</h1>
                <p class="text-sm text-zinc-500 mt-1">Penerbitan, pemantauan saldo piutang, dan status pembayaran SPP serta biaya pendidikan.</p>
            </div>
            <div class="flex flex-wrap items-center gap-2.5">
                <a
                    href="{{ route('finance.invoices.bulk') }}"
                    class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-zinc-900 text-white text-xs font-semibold hover:bg-zinc-800 transition-colors shadow-xs"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                    <span>Terbitkan Massal (SPP)</span>
                </a>
                <a
                    href="{{ route('finance.invoices.create') }}"
                    class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-[#E11D2E] text-white text-xs font-semibold hover:bg-[#c81926] transition-colors shadow-xs"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    <span>Buat Tagihan Tunggal</span>
                </a>
            </div>
        </div>

        @if (session('success'))
            <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm flex items-center justify-between">
                <span>{{ session('success') }}</span>
            </div>
        @endif

        <!-- Financial KPI Metrics -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="bg-white p-5 rounded-2xl border border-zinc-200 shadow-xs">
                <div class="text-xs font-bold text-zinc-400 uppercase tracking-wider">Total Tagihan Diterbitkan</div>
                <div class="text-xl font-extrabold text-zinc-900 mt-2">Rp {{ number_format($totalInvoiced, 0, ',', '.') }}</div>
                <div class="text-[11px] text-zinc-500 mt-1">Akumulasi seluruh tagihan aktif</div>
            </div>
            <div class="bg-white p-5 rounded-2xl border border-zinc-200 shadow-xs">
                <div class="text-xs font-bold text-emerald-600 uppercase tracking-wider">Penerimaan Terverifikasi</div>
                <div class="text-xl font-extrabold text-emerald-700 mt-2">Rp {{ number_format($totalPaid, 0, ',', '.') }}</div>
                <div class="text-[11px] text-emerald-600/80 mt-1">Pembayaran riil yang telah masuk</div>
            </div>
            <div class="bg-white p-5 rounded-2xl border border-zinc-200 shadow-xs">
                <div class="text-xs font-bold text-amber-600 uppercase tracking-wider">Sisa Piutang Tertunggak</div>
                <div class="text-xl font-extrabold text-amber-700 mt-2">Rp {{ number_format($totalOutstanding, 0, ',', '.') }}</div>
                <div class="text-[11px] text-amber-600/80 mt-1">Kewajiban siswa yang belum lunas</div>
            </div>
            <div class="bg-white p-5 rounded-2xl border border-zinc-200 shadow-xs">
                <div class="text-xs font-bold text-rose-600 uppercase tracking-wider">Tagihan Jatuh Tempo</div>
                <div class="text-xl font-extrabold text-rose-700 mt-2">{{ $totalOverdueCount }} Tagihan</div>
                <div class="text-[11px] text-rose-600/80 mt-1">Melewati batas waktu pembayaran</div>
            </div>
        </div>

        <!-- Filters Form -->
        <form method="GET" action="{{ route('finance.invoices.index') }}" class="bg-white p-4 rounded-2xl border border-zinc-200 shadow-xs space-y-3">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 text-xs">
                <div>
                    <label class="block font-semibold text-zinc-700 mb-1">Cari Siswa / Nomor Tagihan</label>
                    <input
                        type="text"
                        name="search"
                        value="{{ request('search') }}"
                        placeholder="Ketik nama siswa, NIS, atau no INV..."
                        class="w-full px-3 py-2 rounded-lg border border-zinc-300 focus:ring-2 focus:ring-brand-500"
                    >
                </div>
                <div>
                    <label class="block font-semibold text-zinc-700 mb-1">Status Pembayaran</label>
                    <select name="status" class="w-full px-3 py-2 rounded-lg border border-zinc-300 focus:ring-2 focus:ring-brand-500">
                        <option value="">-- Semua Status --</option>
                        <option value="issued" {{ request('status') === 'issued' ? 'selected' : '' }}>Diterbitkan (Belum Bayar)</option>
                        <option value="partially_paid" {{ request('status') === 'partially_paid' ? 'selected' : '' }}>Sebagian Dibayar</option>
                        <option value="paid" {{ request('status') === 'paid' ? 'selected' : '' }}>Lunas</option>
                        <option value="overdue" {{ request('status') === 'overdue' ? 'selected' : '' }}>Jatuh Tempo (Terlambat)</option>
                        <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>Draf</option>
                        <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Dibatalkan</option>
                    </select>
                </div>
                <div>
                    <label class="block font-semibold text-zinc-700 mb-1">Tahun Ajaran</label>
                    <select name="academic_year_id" class="w-full px-3 py-2 rounded-lg border border-zinc-300 focus:ring-2 focus:ring-brand-500">
                        <option value="">-- Semua Tahun Ajaran --</option>
                        @foreach ($academicYears as $ay)
                            <option value="{{ $ay->id }}" {{ request('academic_year_id') == $ay->id ? 'selected' : '' }}>{{ $ay->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block font-semibold text-zinc-700 mb-1">Kelas / Rombel</label>
                    <select name="classroom_id" class="w-full px-3 py-2 rounded-lg border border-zinc-300 focus:ring-2 focus:ring-brand-500">
                        <option value="">-- Semua Kelas --</option>
                        @foreach ($classrooms as $cr)
                            <option value="{{ $cr->id }}" {{ request('classroom_id') == $cr->id ? 'selected' : '' }}>Kelas {{ $cr->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="flex items-center justify-end gap-2 pt-2 border-t border-zinc-100">
                <a href="{{ route('finance.invoices.index') }}" class="px-3 py-1.5 rounded-lg border border-zinc-300 text-zinc-600 text-xs font-semibold hover:bg-zinc-50">Reset</a>
                <button type="submit" class="px-4 py-1.5 rounded-lg bg-zinc-900 text-white text-xs font-semibold hover:bg-zinc-800">Terapkan Filter</button>
            </div>
        </form>

        <!-- Invoices Table -->
        <div class="bg-white rounded-2xl border border-zinc-200 shadow-xs overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-zinc-50/80 text-zinc-500 font-semibold border-b border-zinc-200 uppercase tracking-wider text-[11px]">
                        <tr>
                            <th class="px-5 py-3.5">Nomor & Judul Tagihan</th>
                            <th class="px-5 py-3.5">Nama Siswa</th>
                            <th class="px-5 py-3.5">Batas Tempo</th>
                            <th class="px-5 py-3.5 text-right">Total Tagihan</th>
                            <th class="px-5 py-3.5 text-right">Sudah Dibayar</th>
                            <th class="px-5 py-3.5 text-right">Sisa Piutang</th>
                            <th class="px-5 py-3.5 text-center">Status</th>
                            <th class="px-5 py-3.5 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-200">
                        @forelse ($invoices as $inv)
                            <tr class="hover:bg-zinc-50/60 transition-colors">
                                <td class="px-5 py-3.5">
                                    <div class="font-mono font-bold text-zinc-900">{{ $inv->invoice_number }}</div>
                                    <div class="text-[11px] text-zinc-500 mt-0.5 truncate max-w-xs">{{ $inv->title }}</div>
                                </td>
                                <td class="px-5 py-3.5">
                                    <div class="font-bold text-zinc-900">{{ $inv->student?->name }}</div>
                                    <div class="text-[11px] text-zinc-400">NIS: {{ $inv->student?->studentProfile?->nis ?? '-' }}</div>
                                </td>
                                <td class="px-5 py-3.5">
                                    <div class="text-zinc-700">{{ $inv->due_date ? $inv->due_date->format('d/m/Y') : '-' }}</div>
                                    @if ($inv->due_date && $inv->due_date->isPast() && ! $inv->isPaid() && $inv->status !== 'cancelled')
                                        <div class="text-[10px] font-semibold text-rose-600">Lewat jatuh tempo</div>
                                    @endif
                                </td>
                                <td class="px-5 py-3.5 text-right font-semibold text-zinc-900">{{ $inv->formatted_total }}</td>
                                <td class="px-5 py-3.5 text-right font-semibold text-emerald-700">{{ $inv->formatted_paid }}</td>
                                <td class="px-5 py-3.5 text-right font-bold text-zinc-900">
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
                                        <a href="{{ route('finance.invoices.show', $inv) }}" class="p-1.5 rounded-md text-zinc-600 hover:text-zinc-900 hover:bg-zinc-100" title="Lihat Rincian">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                        </a>
                                        <a href="{{ route('finance.pdf.invoice', ['invoice' => $inv, 'mode' => 'download']) }}" class="p-1.5 rounded-md text-zinc-600 hover:text-brand-600 hover:bg-zinc-100" title="Unduh PDF">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-5 py-12 text-center text-zinc-400">
                                    Tidak ada data tagihan yang sesuai dengan filter pencarian.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($invoices->hasPages())
                <div class="px-5 py-3 border-t border-zinc-200">
                    {{ $invoices->links() }}
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
