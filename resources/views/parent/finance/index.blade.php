<x-app-layout>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-6 border-b border-zinc-200">
            <div>
                <nav class="flex items-center gap-2 text-xs text-zinc-500 mb-2">
                    <a href="{{ route('parent.dashboard') }}" class="hover:text-zinc-800">Portal Orang Tua</a>
                    <span>&bull;</span>
                    <span class="text-zinc-800 font-semibold">Keuangan & SPP Anak</span>
                </nav>
                <h1 class="text-2xl font-bold text-zinc-900 tracking-tight">Status Tagihan & Pembayaran SPP</h1>
                <p class="text-sm text-zinc-500 mt-1">Pantau kewajiban biaya pendidikan, unduh kuitansi resmi, dan konfirmasi bukti transfer bank.</p>
            </div>

            <!-- Child Switcher Tabs if parent has multiple verified children -->
            @if ($activeChildren->count() > 1)
                <div class="flex items-center gap-2 p-1.5 bg-zinc-100 rounded-xl">
                    <span class="text-xs font-semibold text-zinc-500 px-2">Pilih Anak:</span>
                    @foreach ($activeChildren as $child)
                        <a
                            href="{{ route('parent.finance.index', ['child_id' => $child->id]) }}"
                            class="px-3 py-1.5 rounded-lg text-xs font-bold transition-all {{ $activeChild?->id === $child->id ? 'bg-white text-[#E11D2E] shadow-2xs' : 'text-zinc-600 hover:text-zinc-900' }}"
                        >
                            {{ $child->name }}
                        </a>
                    @endforeach
                </div>
            @endif
        </div>

        @if (session('success'))
            <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm">
                {{ session('success') }}
            </div>
        @endif

        @if (! $activeChild)
            <div class="bg-white p-12 rounded-2xl border border-zinc-200 text-center space-y-3">
                <svg class="w-12 h-12 text-zinc-300 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                <h3 class="text-base font-bold text-zinc-800">Belum Ada Akun Anak yang Terhubung</h3>
                <p class="text-xs text-zinc-500 max-w-md mx-auto">
                    Akun Anda belum memiliki anak yang terverifikasi aktif. Hubungi bagian tata usaha sekolah atau masukkan kode tautan pada menu data anak.
                </p>
                <div class="pt-2">
                    <a href="{{ route('parent.children.index') }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-[#E11D2E] text-white text-xs font-semibold hover:bg-[#c81926]">
                        Tautkan Akun Anak
                    </a>
                </div>
            </div>
        @else
            <!-- Child Profile Summary Card -->
            <div class="p-4 rounded-2xl bg-zinc-50 border border-zinc-200 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 text-xs">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-zinc-200 flex items-center justify-center font-bold text-zinc-700 text-sm">
                        {{ substr($activeChild->name, 0, 1) }}
                    </div>
                    <div>
                        <div class="text-sm font-bold text-zinc-900">{{ $activeChild->name }}</div>
                        <div class="text-zinc-500">NIS: {{ $activeChild->studentProfile?->nis ?? '-' }} &bull; NISN: {{ $activeChild->studentProfile?->nisn ?? '-' }}</div>
                    </div>
                </div>
                <div class="text-zinc-600">
                    Sekolah: <strong class="text-zinc-800">{{ $school?->name ?? 'Eduvia' }}</strong>
                </div>
            </div>

            <!-- Financial Summary Cards -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div class="bg-white p-5 rounded-2xl border border-zinc-200 shadow-xs">
                    <div class="text-[11px] font-bold text-zinc-400 uppercase tracking-wider">Total Tagihan Pendidikan</div>
                    <div class="text-2xl font-extrabold text-zinc-900 mt-2">Rp {{ number_format($totalInvoiced, 0, ',', '.') }}</div>
                    <div class="text-[11px] text-zinc-500 mt-1">Keseluruhan tagihan terbit tahun berjalan</div>
                </div>

                <div class="bg-white p-5 rounded-2xl border border-zinc-200 shadow-xs">
                    <div class="text-[11px] font-bold text-emerald-600 uppercase tracking-wider">Sudah Terbayar (Lunas)</div>
                    <div class="text-2xl font-extrabold text-emerald-700 mt-2">Rp {{ number_format($totalPaid, 0, ',', '.') }}</div>
                    <div class="text-[11px] text-emerald-600/80 mt-1">Pembayaran resmi yang telah diverifikasi kasir</div>
                </div>

                <div class="bg-white p-5 rounded-2xl border border-zinc-200 shadow-xs">
                    <div class="text-[11px] font-bold text-amber-600 uppercase tracking-wider">Sisa Kewajiban Belum Lunas</div>
                    <div class="text-2xl font-extrabold text-amber-700 mt-2">Rp {{ number_format($totalOutstanding, 0, ',', '.') }}</div>
                    <div class="text-[11px] text-amber-600/80 mt-1">
                        @if ($pendingPaymentsCount > 0)
                            <span class="text-blue-600 font-semibold">{{ $pendingPaymentsCount }} pembayaran sedang diverifikasi</span>
                        @else
                            Perlu diselesaikan sebelum jatuh tempo
                        @endif
                    </div>
                </div>
            </div>

            <!-- Invoices Table -->
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
                                <th class="px-5 py-3.5">Perihal / Kategori</th>
                                <th class="px-5 py-3.5">Batas Waktu</th>
                                <th class="px-5 py-3.5 text-right">Total Biaya</th>
                                <th class="px-5 py-3.5 text-right">Sisa Tagihan</th>
                                <th class="px-5 py-3.5 text-center">Status</th>
                                <th class="px-5 py-3.5 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-200">
                            @forelse ($invoices as $inv)
                                <tr class="hover:bg-zinc-50/60 transition-colors">
                                    <td class="px-5 py-3.5 font-mono font-bold text-zinc-900">{{ $inv->invoice_number }}</td>
                                    <td class="px-5 py-3.5">
                                        <div class="font-bold text-zinc-900">{{ $inv->title }}</div>
                                        <div class="text-[11px] text-zinc-400">Periode: {{ $inv->billing_period_key ?? 'Reguler' }}</div>
                                    </td>
                                    <td class="px-5 py-3.5">
                                        <div class="text-zinc-800">{{ $inv->due_date ? $inv->due_date->format('d/m/Y') : '-' }}</div>
                                        @if ($inv->due_date && $inv->due_date->isPast() && ! $inv->isPaid())
                                            <span class="text-[10px] text-rose-600 font-semibold">Jatuh Tempo</span>
                                        @endif
                                    </td>
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
                                            @if (! $inv->isPaid())
                                                <a
                                                    href="{{ route('parent.finance.pay', $inv) }}"
                                                    class="px-3 py-1.5 rounded-lg bg-[#E11D2E] text-white text-xs font-semibold hover:bg-[#c81926] shadow-2xs"
                                                >
                                                    Bayar / Konfirmasi
                                                </a>
                                            @endif
                                            <a
                                                href="{{ route('parent.finance.show', $inv) }}"
                                                class="px-3 py-1.5 rounded-lg border border-zinc-300 text-zinc-700 text-xs font-semibold hover:bg-zinc-50"
                                            >
                                                Rincian
                                            </a>
                                            <a
                                                href="{{ route('finance.pdf.invoice', ['invoice' => $inv, 'mode' => 'download']) }}"
                                                class="p-1.5 rounded-lg text-zinc-500 hover:text-zinc-800 hover:bg-zinc-100"
                                                title="Unduh Faktur PDF"
                                            >
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-5 py-10 text-center text-zinc-400">
                                        Tidak ada tagihan yang belum lunas untuk siswa ini.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Payment History & Receipts -->
            <div class="bg-white rounded-2xl border border-zinc-200 shadow-xs overflow-hidden">
                <div class="px-5 py-4 border-b border-zinc-200 bg-zinc-50/50 flex justify-between items-center">
                    <h3 class="text-xs font-bold text-zinc-800 uppercase tracking-wider">Riwayat Setoran & Kuitansi Resmi</h3>
                    <span class="text-xs text-zinc-500">{{ $payments->count() }} Transaksi</span>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-zinc-50/80 text-zinc-500 font-semibold border-b border-zinc-200 uppercase tracking-wider text-[11px]">
                            <tr>
                                <th class="px-5 py-3">No. Transaksi</th>
                                <th class="px-5 py-3">Tanggal Setor</th>
                                <th class="px-5 py-3">Tagihan Terkait</th>
                                <th class="px-5 py-3">Saluran Pembayaran</th>
                                <th class="px-5 py-3 text-right">Nominal</th>
                                <th class="px-5 py-3 text-center">Status</th>
                                <th class="px-5 py-3 text-right">Dokumen Kuitansi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-200">
                            @forelse ($payments as $pay)
                                <tr class="hover:bg-zinc-50/60 transition-colors">
                                    <td class="px-5 py-3.5 font-mono font-bold text-zinc-900">{{ $pay->payment_number }}</td>
                                    <td class="px-5 py-3.5 text-zinc-700">{{ $pay->payment_date ? $pay->payment_date->format('d/m/Y') : '-' }}</td>
                                    <td class="px-5 py-3.5 font-semibold text-zinc-800">{{ $pay->invoice?->title }}</td>
                                    <td class="px-5 py-3.5 text-zinc-600">{{ $pay->method_label }}</td>
                                    <td class="px-5 py-3.5 text-right font-mono font-bold text-zinc-900">{{ $pay->formatted_amount }}</td>
                                    <td class="px-5 py-3.5 text-center">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold border {{ $pay->status_badge_class }}">
                                            {{ $pay->status_label }}
                                        </span>
                                    </td>
                                    <td class="px-5 py-3.5 text-right">
                                        @if ($pay->receipt)
                                            <a
                                                href="{{ route('finance.pdf.receipt', ['receipt' => $pay->receipt, 'mode' => 'download']) }}"
                                                class="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg bg-emerald-50 text-emerald-700 border border-emerald-200 font-bold hover:bg-emerald-100 transition-colors text-xs"
                                            >
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                                <span>Unduh Kuitansi ({{ $pay->receipt->receipt_number }})</span>
                                            </a>
                                        @elseif ($pay->status === 'pending_verification')
                                            <span class="text-zinc-400 text-xs italic">Menunggu verifikasi staf</span>
                                        @elseif ($pay->status === 'rejected')
                                            <span class="text-rose-600 text-[11px]" title="{{ $pay->rejection_reason }}">Ditolak: {{ $pay->rejection_reason }}</span>
                                        @else
                                            <span class="text-zinc-400">-</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-5 py-8 text-center text-zinc-400">
                                        Belum ada riwayat pembayaran yang tercatat.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    </div>
</x-app-layout>
