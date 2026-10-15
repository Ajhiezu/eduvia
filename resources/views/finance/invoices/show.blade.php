<x-app-layout>
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8" x-data="{ showPayModal: false, showCancelModal: false, showReverseModal: false, selectedPaymentId: null }">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-6 border-b border-zinc-200">
            <div>
                <nav class="flex items-center gap-2 text-xs text-zinc-500 mb-2">
                    <a href="{{ route('finance.invoices.index') }}" class="hover:text-zinc-800">Manajemen Tagihan</a>
                    <span>&bull;</span>
                    <span class="text-zinc-800 font-mono font-semibold">{{ $invoice->invoice_number }}</span>
                </nav>
                <div class="flex items-center gap-3">
                    <h1 class="text-2xl font-bold text-zinc-900 tracking-tight font-mono">{{ $invoice->invoice_number }}</h1>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold border {{ $invoice->status_badge_class }}">
                        {{ $invoice->status_label }}
                    </span>
                </div>
                <p class="text-sm text-zinc-500 mt-1">{{ $invoice->title }}</p>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <a
                    href="{{ route('finance.pdf.invoice', ['invoice' => $invoice, 'mode' => 'download']) }}"
                    class="inline-flex items-center gap-2 px-3.5 py-2 rounded-lg border border-zinc-300 bg-white text-zinc-700 text-xs font-semibold hover:bg-zinc-50 transition-colors shadow-2xs"
                >
                    <svg class="w-4 h-4 text-zinc-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    <span>Unduh Faktur PDF</span>
                </a>

                @if (! $invoice->isPaid() && $invoice->status !== 'cancelled')
                    <a
                        href="{{ route('finance.payments.create', ['invoice_id' => $invoice->id]) }}"
                        class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-[#E11D2E] text-white text-xs font-semibold hover:bg-[#c81926] transition-colors shadow-2xs"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                        <span>Catat Pembayaran</span>
                    </a>

                    <form method="POST" action="{{ route('finance.invoices.reminder', $invoice) }}" class="inline">
                        @csrf
                        <button type="submit" class="px-3 py-2 rounded-lg bg-zinc-100 text-zinc-700 text-xs font-semibold hover:bg-zinc-200">
                            Kirim Pengingat
                        </button>
                    </form>
                @endif

                @if ($invoice->paid_amount == 0 && $invoice->status !== 'cancelled')
                    <button
                        type="button"
                        @click="showCancelModal = true"
                        class="px-3 py-2 rounded-lg border border-rose-200 bg-rose-50 text-rose-700 text-xs font-semibold hover:bg-rose-100"
                    >
                        Batalkan Tagihan
                    </button>
                @endif
            </div>
        </div>

        @if (session('success'))
            <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm flex items-center justify-between">
                <span>{{ session('success') }}</span>
            </div>
        @endif

        @if ($errors->any())
            <div class="p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-sm">
                <ul class="list-disc list-inside space-y-0.5">
                    @foreach ($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Metadata Grid -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- Siswa Information -->
            <div class="bg-white p-5 rounded-2xl border border-zinc-200 shadow-xs space-y-2 text-xs">
                <div class="text-[10px] font-bold text-zinc-400 uppercase tracking-wider">Identitas Siswa</div>
                <div class="text-sm font-bold text-zinc-900">{{ $invoice->student?->name }}</div>
                <div class="text-zinc-600">NIS: <span class="font-mono">{{ $invoice->student?->studentProfile?->nis ?? '-' }}</span></div>
                <div class="text-zinc-600">NISN: <span class="font-mono">{{ $invoice->student?->studentProfile?->nisn ?? '-' }}</span></div>
                <div class="text-zinc-600">Email: {{ $invoice->student?->email }}</div>
            </div>

            <!-- Periode & Tanggal -->
            <div class="bg-white p-5 rounded-2xl border border-zinc-200 shadow-xs space-y-2 text-xs">
                <div class="text-[10px] font-bold text-zinc-400 uppercase tracking-wider">Masa Tagihan</div>
                <div class="text-zinc-700">Tahun Ajaran: <span class="font-semibold">{{ $invoice->academicYear?->name }}</span></div>
                <div class="text-zinc-700">Kunci Periode: <span class="font-mono font-semibold">{{ $invoice->billing_period_key ?? 'Reguler' }}</span></div>
                <div class="text-zinc-700">Tanggal Terbit: <span class="font-semibold">{{ $invoice->issue_date ? $invoice->issue_date->format('d/m/Y') : '-' }}</span></div>
                <div class="text-zinc-700">Jatuh Tempo: <span class="font-semibold text-rose-600">{{ $invoice->due_date ? $invoice->due_date->format('d/m/Y') : '-' }}</span></div>
            </div>

            <!-- Ringkasan Saldo Finansial -->
            <div class="bg-white p-5 rounded-2xl border border-zinc-200 shadow-xs space-y-2 text-xs">
                <div class="text-[10px] font-bold text-zinc-400 uppercase tracking-wider">Ringkasan Pembayaran</div>
                <div class="flex justify-between text-zinc-600">
                    <span>Total Tagihan:</span>
                    <span class="font-mono font-bold text-zinc-900">{{ $invoice->formatted_total }}</span>
                </div>
                <div class="flex justify-between text-emerald-700">
                    <span>Sudah Dibayar:</span>
                    <span class="font-mono font-bold">{{ $invoice->formatted_paid }}</span>
                </div>
                <div class="flex justify-between text-sm font-extrabold pt-2 border-t border-zinc-100 text-zinc-900">
                    <span>Sisa Piutang:</span>
                    <span class="{{ $invoice->remaining_balance > 0 ? 'text-[#E11D2E]' : 'text-emerald-700' }} font-mono">
                        {{ $invoice->formatted_remaining }}
                    </span>
                </div>
            </div>
        </div>

        <!-- Rincian Line Items -->
        <div class="bg-white rounded-2xl border border-zinc-200 shadow-xs overflow-hidden">
            <div class="px-5 py-4 border-b border-zinc-200 bg-zinc-50/50 flex justify-between items-center">
                <h3 class="text-xs font-bold text-zinc-800 uppercase tracking-wider">Rincian Komponen Tagihan</h3>
                <span class="text-xs text-zinc-500">{{ $invoice->items->count() }} Komponen</span>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-zinc-50/80 text-zinc-500 font-semibold border-b border-zinc-200 uppercase tracking-wider text-[11px]">
                        <tr>
                            <th class="px-5 py-3">Komponen Biaya</th>
                            <th class="px-5 py-3 text-center">Kuantitas</th>
                            <th class="px-5 py-3 text-right">Harga Satuan</th>
                            <th class="px-5 py-3 text-right">Potongan Diskon</th>
                            <th class="px-5 py-3 text-right">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-200">
                        @foreach ($invoice->items as $item)
                            <tr>
                                <td class="px-5 py-3.5">
                                    <div class="font-bold text-zinc-900">{{ $item->item_name }}</div>
                                    @if ($item->feeCategory)
                                        <div class="text-[11px] text-zinc-400 mt-0.5">Kategori: {{ $item->feeCategory->name }}</div>
                                    @endif
                                </td>
                                <td class="px-5 py-3.5 text-center font-mono text-zinc-700">{{ $item->quantity }}</td>
                                <td class="px-5 py-3.5 text-right font-mono text-zinc-700">{{ $item->formatted_unit_price }}</td>
                                <td class="px-5 py-3.5 text-right font-mono text-emerald-700">{{ $item->formatted_discount }}</td>
                                <td class="px-5 py-3.5 text-right font-mono font-bold text-zinc-900">{{ $item->formatted_subtotal }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="bg-zinc-50 border-t border-zinc-200 text-xs">
                        <tr>
                            <td colspan="4" class="px-5 py-2 text-right font-semibold text-zinc-600">Subtotal Kotor:</td>
                            <td class="px-5 py-2 text-right font-mono font-semibold text-zinc-800">{{ $invoice->formatted_subtotal }}</td>
                        </tr>
                        @if ($invoice->discount_amount > 0)
                            <tr>
                                <td colspan="4" class="px-5 py-2 text-right font-semibold text-emerald-700">Total Potongan Diskon:</td>
                                <td class="px-5 py-2 text-right font-mono font-semibold text-emerald-700">- {{ $invoice->formatted_discount }}</td>
                            </tr>
                        @endif
                        <tr class="border-t border-zinc-200">
                            <td colspan="4" class="px-5 py-3 text-right font-bold text-zinc-900 text-sm">Total Bersih:</td>
                            <td class="px-5 py-3 text-right font-mono font-extrabold text-[#E11D2E] text-sm">{{ $invoice->formatted_total }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <!-- Riwayat Transaksi Pembayaran -->
        <div class="bg-white rounded-2xl border border-zinc-200 shadow-xs overflow-hidden">
            <div class="px-5 py-4 border-b border-zinc-200 bg-zinc-50/50 flex justify-between items-center">
                <h3 class="text-xs font-bold text-zinc-800 uppercase tracking-wider">Riwayat Pembayaran & Kuitansi</h3>
                <span class="text-xs text-zinc-500">{{ $invoice->payments->count() }} Transaksi</span>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-zinc-50/80 text-zinc-500 font-semibold border-b border-zinc-200 uppercase tracking-wider text-[11px]">
                        <tr>
                            <th class="px-5 py-3">No. Transaksi</th>
                            <th class="px-5 py-3">Tanggal</th>
                            <th class="px-5 py-3">Metode</th>
                            <th class="px-5 py-3">Nominal</th>
                            <th class="px-5 py-3">Status</th>
                            <th class="px-5 py-3">Kuitansi / Bukti</th>
                            <th class="px-5 py-3 text-right">Aksi Audit</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-200">
                        @forelse ($invoice->payments as $pay)
                            <tr class="hover:bg-zinc-50/60 transition-colors">
                                <td class="px-5 py-3.5 font-mono font-bold text-zinc-900">{{ $pay->payment_number }}</td>
                                <td class="px-5 py-3.5 text-zinc-700">{{ $pay->payment_date ? $pay->payment_date->format('d/m/Y') : '-' }}</td>
                                <td class="px-5 py-3.5 text-zinc-700">{{ $pay->method_label }}</td>
                                <td class="px-5 py-3.5 font-mono font-bold text-zinc-900">{{ $pay->formatted_amount }}</td>
                                <td class="px-5 py-3.5">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold border {{ $pay->status_badge_class }}">
                                        {{ $pay->status_label }}
                                    </span>
                                </td>
                                <td class="px-5 py-3.5">
                                    @if ($pay->receipt)
                                        <a href="{{ route('finance.pdf.receipt', ['receipt' => $pay->receipt, 'mode' => 'download']) }}" class="inline-flex items-center gap-1 text-emerald-700 hover:text-emerald-900 font-semibold">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                            <span>{{ $pay->receipt->receipt_number }}</span>
                                        </a>
                                    @elseif ($pay->proof_path)
                                        <a href="{{ route('finance.payments.proof', $pay) }}" class="inline-flex items-center gap-1 text-blue-700 hover:text-blue-900 font-semibold">
                                            <span>Bukti Transfer</span>
                                        </a>
                                    @else
                                        <span class="text-zinc-400">-</span>
                                    @endif
                                </td>
                                <td class="px-5 py-3.5 text-right">
                                    @if ($pay->status === 'verified')
                                        <form method="POST" action="{{ route('finance.payments.reverse', $pay) }}" onsubmit="return confirm('Koreksi / Batalkan pembayaran ini? Saldo tagihan akan dikembalikan.');" class="inline">
                                            @csrf
                                            <input type="hidden" name="reason" value="Koreksi transaksi oleh admin">
                                            <button type="submit" class="text-xs text-rose-600 hover:text-rose-800 font-semibold">
                                                Koreksi / Reversal
                                            </button>
                                        </form>
                                    @else
                                        <span class="text-zinc-400 text-xs">-</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-5 py-8 text-center text-zinc-400">
                                    Belum ada transaksi pembayaran yang dicatat untuk tagihan ini.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- MODAL: BATALKAN TAGIHAN -->
        <div x-show="showCancelModal" class="fixed inset-0 z-50 overflow-y-auto bg-zinc-900/60 backdrop-blur-xs flex items-center justify-center p-4" style="display: none;">
            <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-xl border border-zinc-200" @click.outside="showCancelModal = false">
                <h3 class="text-base font-bold text-zinc-900">Konfirmasi Pembatalan Tagihan</h3>
                <p class="text-xs text-zinc-600 mt-2">
                    Apakah Anda yakin ingin membatalkan tagihan <strong>{{ $invoice->invoice_number }}</strong>? Tindakan ini bersifat permanen dan tagihan tidak dapat lagi dibayar.
                </p>
                <form method="POST" action="{{ route('finance.invoices.cancel', $invoice) }}" class="mt-4 flex justify-end gap-2 text-xs">
                    @csrf
                    <button type="button" @click="showCancelModal = false" class="px-4 py-2 rounded-lg border border-zinc-300 font-semibold text-zinc-700">Tutup</button>
                    <button type="submit" class="px-4 py-2 rounded-lg bg-rose-600 text-white font-semibold hover:bg-rose-700">Ya, Batalkan Tagihan</button>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
