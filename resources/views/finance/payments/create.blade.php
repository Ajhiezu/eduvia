<x-app-layout>
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8">
        <!-- Header -->
        <div class="flex items-center justify-between pb-6 border-b border-zinc-200">
            <div>
                <nav class="flex items-center gap-2 text-xs text-zinc-500 mb-2">
                    <a href="{{ route('finance.payments.index') }}" class="hover:text-zinc-800">Riwayat Pembayaran</a>
                    <span>&bull;</span>
                    <span class="text-zinc-800 font-semibold">Catat Pembayaran Masuk</span>
                </nav>
                <h1 class="text-2xl font-bold text-zinc-900 tracking-tight">Catat Setoran Manual</h1>
                <p class="text-sm text-zinc-500 mt-1">Input penerimaan pembayaran tunai di kasir sekolah atau transfer bank langsung.</p>
            </div>
            <a href="{{ route('finance.payments.index') }}" class="px-3.5 py-1.5 rounded-lg border border-zinc-300 text-zinc-700 text-xs font-semibold hover:bg-zinc-50">
                Kembali
            </a>
        </div>

        @if ($errors->any())
            <div class="p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-sm">
                <p class="font-semibold mb-1">Gagal menyimpan pembayaran:</p>
                <ul class="list-disc list-inside space-y-0.5">
                    @foreach ($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('finance.payments.store') }}" class="space-y-6">
            @csrf

            <!-- Form Card -->
            <div class="bg-white p-6 rounded-2xl border border-zinc-200 shadow-xs space-y-4 text-xs">
                <div>
                    <label class="block font-semibold text-zinc-700 mb-1">Pilih Tagihan yang Akan Dibayar <span class="text-rose-500">*</span></label>
                    @if ($selectedInvoice)
                        <input type="hidden" name="invoice_id" value="{{ $selectedInvoice->id }}">
                        <div class="p-4 rounded-xl bg-zinc-50 border border-zinc-200 space-y-2">
                            <div class="flex justify-between items-center">
                                <span class="font-mono font-bold text-zinc-900 text-sm">{{ $selectedInvoice->invoice_number }}</span>
                                <span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-blue-50 text-blue-700">{{ $selectedInvoice->status_label }}</span>
                            </div>
                            <div class="font-semibold text-zinc-800">{{ $selectedInvoice->student?->name }} &bull; {{ $selectedInvoice->title }}</div>
                            <div class="flex justify-between text-zinc-600 pt-1 border-t border-zinc-200">
                                <span>Total Tagihan: <strong>{{ $selectedInvoice->formatted_total }}</strong></span>
                                <span>Sisa Kewajiban: <strong class="text-[#E11D2E]">{{ $selectedInvoice->formatted_remaining }}</strong></span>
                            </div>
                        </div>
                    @else
                        <select name="invoice_id" required class="w-full px-3 py-2 rounded-lg border border-zinc-300 focus:ring-2 focus:ring-brand-500">
                            <option value="">-- Pilih Tagihan Belum Lunas --</option>
                            @foreach ($unpaidInvoices as $inv)
                                <option value="{{ $inv->id }}">
                                    [{{ $inv->invoice_number }}] {{ $inv->student?->name }} — {{ $inv->title }} (Sisa: {{ $inv->formatted_remaining }})
                                </option>
                            @endforeach
                        </select>
                    @endif
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block font-semibold text-zinc-700 mb-1">Nominal yang Dibayarkan (Rp) <span class="text-rose-500">*</span></label>
                        <input
                            type="number"
                            name="amount"
                            min="1"
                            step="1000"
                            value="{{ $selectedInvoice ? $selectedInvoice->remaining_balance : old('amount') }}"
                            required
                            class="w-full px-3 py-2 rounded-lg border border-zinc-300 focus:ring-2 focus:ring-brand-500 font-mono text-sm"
                        >
                        <span class="text-[10px] text-zinc-400 mt-1 block">Mendukung pembayaran penuh maupun cicilan sebagian.</span>
                    </div>

                    <div>
                        <label class="block font-semibold text-zinc-700 mb-1">Tanggal Pembayaran <span class="text-rose-500">*</span></label>
                        <input
                            type="date"
                            name="payment_date"
                            value="{{ old('payment_date', date('Y-m-d')) }}"
                            required
                            class="w-full px-3 py-2 rounded-lg border border-zinc-300 focus:ring-2 focus:ring-brand-500"
                        >
                    </div>

                    <div>
                        <label class="block font-semibold text-zinc-700 mb-1">Metode Pembayaran <span class="text-rose-500">*</span></label>
                        <select name="payment_method" required class="w-full px-3 py-2 rounded-lg border border-zinc-300 focus:ring-2 focus:ring-brand-500">
                            <option value="cash" selected>Tunai (Kasir Sekolah)</option>
                            <option value="bank_transfer">Transfer Bank Resmi Sekolah</option>
                            <option value="other">Lainnya / Saluran Resmi Disetujui</option>
                        </select>
                    </div>

                    <div>
                        <label class="block font-semibold text-zinc-700 mb-1">Nomor Referensi Bank / Bukti</label>
                        <input
                            type="text"
                            name="reference_number"
                            value="{{ old('reference_number') }}"
                            placeholder="Misal: TRX-9872120 / No Kwitansi Manual"
                            class="w-full px-3 py-2 rounded-lg border border-zinc-300 focus:ring-2 focus:ring-brand-500 font-mono"
                        >
                    </div>
                </div>

                <div>
                    <label class="block font-semibold text-zinc-700 mb-1">Catatan Tambahan (Opsional)</label>
                    <textarea name="notes" rows="2" placeholder="Catatan nama pembayar (ayah/ibu/wali) atau keterangan setoran..." class="w-full px-3 py-2 rounded-lg border border-zinc-300 focus:ring-2 focus:ring-brand-500"></textarea>
                </div>
            </div>

            <!-- Submit Button -->
            <div class="flex items-center justify-end gap-3 pt-4">
                <a href="{{ route('finance.payments.index') }}" class="px-5 py-2.5 rounded-lg border border-zinc-300 text-zinc-700 text-xs font-semibold hover:bg-zinc-50">
                    Batal
                </a>
                <button type="submit" class="px-6 py-2.5 rounded-lg bg-[#E11D2E] text-white text-xs font-semibold hover:bg-[#c81926] shadow-sm">
                    Simpan & Terbitkan Kuitansi
                </button>
            </div>
        </form>
    </div>
</x-app-layout>
