<x-app-layout>
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-6 border-b border-zinc-200">
            <div>
                <nav class="flex items-center gap-2 text-xs text-zinc-500 mb-2">
                    <a href="{{ route('student.finance.index') }}" class="hover:text-zinc-800">Tagihan Saya</a>
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
            <a
                href="{{ route('finance.pdf.invoice', ['invoice' => $invoice, 'mode' => 'download']) }}"
                class="px-3.5 py-2 rounded-lg border border-zinc-300 text-zinc-700 text-xs font-semibold hover:bg-zinc-50"
            >
                Unduh Faktur PDF
            </a>
        </div>

        <!-- Info Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
            <div class="bg-white p-5 rounded-2xl border border-zinc-200 shadow-xs space-y-2">
                <div class="text-[10px] font-bold text-zinc-400 uppercase tracking-wider">Masa & Tanggal Tagihan</div>
                <div class="text-zinc-700">Tahun Ajaran: <strong class="text-zinc-900">{{ $invoice->academicYear?->name }}</strong></div>
                <div class="text-zinc-700">Periode: <strong class="text-zinc-900 font-mono">{{ $invoice->billing_period_key ?? 'Reguler' }}</strong></div>
                <div class="text-zinc-700">Tanggal Terbit: {{ $invoice->issue_date ? $invoice->issue_date->format('d/m/Y') : '-' }}</div>
                <div class="text-zinc-700">Batas Pembayaran: <span class="font-bold text-rose-600">{{ $invoice->due_date ? $invoice->due_date->format('d/m/Y') : '-' }}</span></div>
            </div>

            <div class="bg-white p-5 rounded-2xl border border-zinc-200 shadow-xs space-y-2">
                <div class="text-[10px] font-bold text-zinc-400 uppercase tracking-wider">Ringkasan Saldo</div>
                <div class="flex justify-between text-zinc-600">
                    <span>Total Tagihan:</span>
                    <span class="font-bold font-mono text-zinc-900">{{ $invoice->formatted_total }}</span>
                </div>
                <div class="flex justify-between text-emerald-700">
                    <span>Sudah Dibayar:</span>
                    <span class="font-bold font-mono">{{ $invoice->formatted_paid }}</span>
                </div>
                <div class="flex justify-between text-sm font-bold pt-2 border-t border-zinc-100 text-zinc-900">
                    <span>Sisa Piutang:</span>
                    <span class="{{ $invoice->remaining_balance > 0 ? 'text-[#E11D2E]' : 'text-emerald-700' }} font-mono">
                        {{ $invoice->formatted_remaining }}
                    </span>
                </div>
            </div>
        </div>

        <!-- Line Items -->
        <div class="bg-white rounded-2xl border border-zinc-200 shadow-xs overflow-hidden">
            <div class="px-5 py-3.5 border-b border-zinc-200 bg-zinc-50/50">
                <h3 class="text-xs font-bold text-zinc-800 uppercase tracking-wider">Rincian Komponen Biaya</h3>
            </div>
            <table class="w-full text-left text-xs">
                <thead class="bg-zinc-50/80 text-zinc-500 font-semibold border-b border-zinc-200 uppercase tracking-wider text-[11px]">
                    <tr>
                        <th class="px-5 py-3">Komponen</th>
                        <th class="px-5 py-3 text-center">Kuantitas</th>
                        <th class="px-5 py-3 text-right">Harga Satuan</th>
                        <th class="px-5 py-3 text-right">Potongan Diskon</th>
                        <th class="px-5 py-3 text-right">Subtotal</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-200">
                    @foreach ($invoice->items as $item)
                        <tr>
                            <td class="px-5 py-3.5 font-bold text-zinc-900">{{ $item->item_name }}</td>
                            <td class="px-5 py-3.5 text-center font-mono">{{ $item->quantity }}</td>
                            <td class="px-5 py-3.5 text-right font-mono">{{ $item->formatted_unit_price }}</td>
                            <td class="px-5 py-3.5 text-right font-mono text-emerald-700">{{ $item->formatted_discount }}</td>
                            <td class="px-5 py-3.5 text-right font-mono font-bold text-zinc-900">{{ $item->formatted_subtotal }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>
