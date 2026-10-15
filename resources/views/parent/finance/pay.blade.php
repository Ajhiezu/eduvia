<x-app-layout>
    <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8">
        <!-- Header -->
        <div class="flex items-center justify-between pb-6 border-b border-zinc-200">
            <div>
                <nav class="flex items-center gap-2 text-xs text-zinc-500 mb-2">
                    <a href="{{ route('parent.finance.index') }}" class="hover:text-zinc-800">Keuangan Anak</a>
                    <span>&bull;</span>
                    <span class="text-zinc-800 font-semibold">Konfirmasi Setoran</span>
                </nav>
                <h1 class="text-2xl font-bold text-zinc-900 tracking-tight">Konfirmasi Bukti Transfer Bank</h1>
                <p class="text-sm text-zinc-500 mt-1">Kirimkan rincian setoran dan lampirkan bukti transfer untuk diverifikasi pihak sekolah.</p>
            </div>
            <a href="{{ route('parent.finance.index') }}" class="px-3.5 py-1.5 rounded-lg border border-zinc-300 text-zinc-700 text-xs font-semibold hover:bg-zinc-50">
                Batal
            </a>
        </div>

        @if ($errors->any())
            <div class="p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-sm">
                <p class="font-semibold mb-1">Gagal mengirim konfirmasi:</p>
                <ul class="list-disc list-inside space-y-0.5">
                    @foreach ($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Tagihan Target Info Card -->
        <div class="p-5 rounded-2xl bg-zinc-50 border border-zinc-200 text-xs space-y-2">
            <div class="flex justify-between items-center">
                <span class="font-mono font-bold text-zinc-900 text-sm">{{ $invoice->invoice_number }}</span>
                <span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-amber-50 text-amber-700 border border-amber-200">{{ $invoice->status_label }}</span>
            </div>
            <div class="font-bold text-zinc-800 text-sm">{{ $invoice->title }}</div>
            <div class="text-zinc-500">Nama Siswa: <strong class="text-zinc-900">{{ $invoice->student?->name }}</strong></div>
            <div class="flex justify-between pt-2 border-t border-zinc-200 text-zinc-700">
                <span>Total Tagihan: <strong>{{ $invoice->formatted_total }}</strong></span>
                <span>Sisa Kewajiban: <strong class="text-[#E11D2E] font-mono text-sm">{{ $invoice->formatted_remaining }}</strong></span>
            </div>
        </div>

        <!-- Form Konfirmasi -->
        <form method="POST" action="{{ route('parent.finance.submit-pay', $invoice) }}" enctype="multipart/form-data" class="bg-white p-6 rounded-2xl border border-zinc-200 shadow-xs space-y-4 text-xs">
            @csrf

            <div>
                <label class="block font-semibold text-zinc-700 mb-1">Nominal yang Anda Transfer (Rp) <span class="text-rose-500">*</span></label>
                <input
                    type="number"
                    name="amount"
                    min="1"
                    max="{{ $invoice->remaining_balance }}"
                    step="1000"
                    value="{{ old('amount', $invoice->remaining_balance) }}"
                    required
                    class="w-full px-3 py-2 rounded-lg border border-zinc-300 focus:ring-2 focus:ring-brand-500 font-mono text-sm"
                >
                <span class="text-[10px] text-zinc-400 mt-1 block">Maksimal sebesar sisa tagihan: {{ $invoice->formatted_remaining }}.</span>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block font-semibold text-zinc-700 mb-1">Tanggal Transfer <span class="text-rose-500">*</span></label>
                    <input
                        type="date"
                        name="payment_date"
                        value="{{ old('payment_date', date('Y-m-d')) }}"
                        required
                        class="w-full px-3 py-2 rounded-lg border border-zinc-300 focus:ring-2 focus:ring-brand-500"
                    >
                </div>

                <div>
                    <label class="block font-semibold text-zinc-700 mb-1">Saluran Pembayaran <span class="text-rose-500">*</span></label>
                    <select name="payment_method" required class="w-full px-3 py-2 rounded-lg border border-zinc-300 focus:ring-2 focus:ring-brand-500">
                        <option value="bank_transfer" selected>Transfer Bank (ATM/Mobile Banking)</option>
                        <option value="cash">Setoran Tunai di Sekolah</option>
                        <option value="other">Saluran Lainnya</option>
                    </select>
                </div>
            </div>

            <div>
                <label class="block font-semibold text-zinc-700 mb-1">Nomor Referensi Bank / No. Struk (Opsional)</label>
                <input
                    type="text"
                    name="reference_number"
                    value="{{ old('reference_number') }}"
                    placeholder="Contoh: No Ref 202610159981 atau 4 digit rekening asal"
                    class="w-full px-3 py-2 rounded-lg border border-zinc-300 focus:ring-2 focus:ring-brand-500 font-mono"
                >
            </div>

            <div>
                <label class="block font-semibold text-zinc-700 mb-1">Upload Foto / Dokumen Bukti Transfer</label>
                <input
                    type="file"
                    name="proof_file"
                    accept=".jpg,.jpeg,.png,.pdf"
                    class="w-full px-3 py-2 rounded-lg border border-zinc-300 focus:ring-2 focus:ring-brand-500 file:mr-4 file:py-1.5 file:px-3 file:rounded-md file:border-0 file:text-xs file:font-semibold file:bg-zinc-100 file:text-zinc-700 hover:file:bg-zinc-200"
                >
                <span class="text-[10px] text-zinc-400 mt-1 block">Format: JPG, PNG, atau PDF (Maksimal 3 MB). File disimpan dengan aman pada penyimpanan privat sekolah.</span>
            </div>

            <div>
                <label class="block font-semibold text-zinc-700 mb-1">Catatan Tambahan (Opsional)</label>
                <textarea
                    name="notes"
                    rows="2"
                    placeholder="Nama pemilik rekening pengirim atau informasi pendukung lainnya..."
                    class="w-full px-3 py-2 rounded-lg border border-zinc-300 focus:ring-2 focus:ring-brand-500"
                ></textarea>
            </div>

            <div class="flex justify-end gap-2 pt-4 border-t border-zinc-100">
                <a href="{{ route('parent.finance.index') }}" class="px-4 py-2 rounded-lg border border-zinc-300 text-zinc-700 font-semibold hover:bg-zinc-50">
                    Batal
                </a>
                <button type="submit" class="px-5 py-2 rounded-lg bg-[#E11D2E] text-white font-semibold hover:bg-[#c81926] shadow-sm">
                    Kirim Konfirmasi Pembayaran
                </button>
            </div>
        </form>
    </div>
</x-app-layout>
