<x-app-layout>
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8" x-data="{
        items: [
            { item_name: '', quantity: 1, unit_price: 0, discount_amount: 0, fee_category_id: '' }
        ],
        addItem() {
            this.items.push({ item_name: '', quantity: 1, unit_price: 0, discount_amount: 0, fee_category_id: '' });
        },
        removeItem(index) {
            if (this.items.length > 1) {
                this.items.splice(index, 1);
            }
        },
        calculateSubtotal() {
            return this.items.reduce((sum, item) => sum + (Number(item.quantity || 1) * Number(item.unit_price || 0)), 0);
        },
        calculateDiscount() {
            return this.items.reduce((sum, item) => sum + Number(item.discount_amount || 0), 0);
        },
        calculateTotal() {
            return Math.max(0, this.calculateSubtotal() - this.calculateDiscount());
        },
        formatRupiah(num) {
            return 'Rp ' + Number(num).toLocaleString('id-ID');
        }
    }">
        <!-- Header -->
        <div class="flex items-center justify-between pb-6 border-b border-zinc-200">
            <div>
                <nav class="flex items-center gap-2 text-xs text-zinc-500 mb-2">
                    <a href="{{ route('finance.invoices.index') }}" class="hover:text-zinc-800">Manajemen Tagihan</a>
                    <span>&bull;</span>
                    <span class="text-zinc-800 font-semibold">Buat Tagihan Tunggal</span>
                </nav>
                <h1 class="text-2xl font-bold text-zinc-900 tracking-tight">Terbitkan Tagihan Baru</h1>
                <p class="text-sm text-zinc-500 mt-1">Buat tagihan individual untuk siswa dengan satu atau beberapa rincian komponen biaya.</p>
            </div>
            <a href="{{ route('finance.invoices.index') }}" class="px-3.5 py-1.5 rounded-lg border border-zinc-300 text-zinc-700 text-xs font-semibold hover:bg-zinc-50">
                Kembali
            </a>
        </div>

        @if ($errors->any())
            <div class="p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-sm">
                <p class="font-semibold mb-1">Gagal menerbitkan tagihan:</p>
                <ul class="list-disc list-inside space-y-0.5">
                    @foreach ($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('finance.invoices.store') }}" class="space-y-6">
            @csrf

            <!-- Informasi Siswa & Periode -->
            <div class="bg-white p-6 rounded-2xl border border-zinc-200 shadow-xs space-y-4">
                <h2 class="text-sm font-bold text-zinc-900 uppercase tracking-wider pb-2 border-b border-zinc-100">1. Data Siswa & Masa Berlaku</h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                    <div>
                        <label class="block font-semibold text-zinc-700 mb-1">Pilih Siswa <span class="text-rose-500">*</span></label>
                        <select name="student_id" required class="w-full px-3 py-2 rounded-lg border border-zinc-300 focus:ring-2 focus:ring-brand-500">
                            <option value="">-- Pilih Siswa --</option>
                            @foreach ($students as $stu)
                                <option value="{{ $stu->id }}" {{ old('student_id') == $stu->id ? 'selected' : '' }}>
                                    {{ $stu->name }} (NIS: {{ $stu->studentProfile?->nis ?? '-' }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block font-semibold text-zinc-700 mb-1">Tahun Ajaran <span class="text-rose-500">*</span></label>
                        <select name="academic_year_id" required class="w-full px-3 py-2 rounded-lg border border-zinc-300 focus:ring-2 focus:ring-brand-500">
                            @foreach ($academicYears as $ay)
                                <option value="{{ $ay->id }}" {{ $ay->is_active ? 'selected' : '' }}>{{ $ay->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block font-semibold text-zinc-700 mb-1">Judul / Perihal Tagihan <span class="text-rose-500">*</span></label>
                        <input type="text" name="title" value="{{ old('title') }}" placeholder="Contoh: SPP & Seragam Sekolah Bulan November" required class="w-full px-3 py-2 rounded-lg border border-zinc-300 focus:ring-2 focus:ring-brand-500">
                    </div>
                    <div>
                        <label class="block font-semibold text-zinc-700 mb-1">Kategori Biaya Utama (Opsional)</label>
                        <select name="fee_category_id" class="w-full px-3 py-2 rounded-lg border border-zinc-300 focus:ring-2 focus:ring-brand-500">
                            <option value="">-- Biaya Campuran / Umum --</option>
                            @foreach ($feeCategories as $fc)
                                <option value="{{ $fc->id }}">{{ $fc->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block font-semibold text-zinc-700 mb-1">Tanggal Terbit <span class="text-rose-500">*</span></label>
                        <input type="date" name="issue_date" value="{{ old('issue_date', date('Y-m-d')) }}" required class="w-full px-3 py-2 rounded-lg border border-zinc-300 focus:ring-2 focus:ring-brand-500">
                    </div>
                    <div>
                        <label class="block font-semibold text-zinc-700 mb-1">Batas Waktu Pembayaran (Jatuh Tempo) <span class="text-rose-500">*</span></label>
                        <input type="date" name="due_date" value="{{ old('due_date', date('Y-m-d', strtotime('+14 days'))) }}" required class="w-full px-3 py-2 rounded-lg border border-zinc-300 focus:ring-2 focus:ring-brand-500">
                    </div>
                </div>
            </div>

            <!-- Rincian Komponen Biaya (Line Items) -->
            <div class="bg-white p-6 rounded-2xl border border-zinc-200 shadow-xs space-y-4">
                <div class="flex items-center justify-between pb-2 border-b border-zinc-100">
                    <h2 class="text-sm font-bold text-zinc-900 uppercase tracking-wider">2. Rincian Komponen Tagihan</h2>
                    <button
                        type="button"
                        @click="addItem()"
                        class="px-3 py-1 rounded-lg bg-zinc-100 text-zinc-700 hover:bg-zinc-200 text-xs font-semibold"
                    >
                        + Tambah Baris
                    </button>
                </div>

                <div class="space-y-3">
                    <template x-for="(item, index) in items" :key="index">
                        <div class="p-4 rounded-xl border border-zinc-200 bg-zinc-50/50 space-y-3 text-xs">
                            <div class="flex items-center justify-between">
                                <span class="font-bold text-zinc-700" x-text="'Item #' + (index + 1)"></span>
                                <button
                                    type="button"
                                    x-show="items.length > 1"
                                    @click="removeItem(index)"
                                    class="text-rose-600 hover:text-rose-800 font-semibold"
                                >
                                    Hapus
                                </button>
                            </div>
                            <div class="grid grid-cols-1 sm:grid-cols-4 gap-3">
                                <div class="sm:col-span-2">
                                    <label class="block font-medium text-zinc-600 mb-1">Nama Item Komponen</label>
                                    <input
                                        type="text"
                                        :name="'items[' + index + '][item_name]'"
                                        x-model="item.item_name"
                                        placeholder="Contoh: SPP Bulan November"
                                        required
                                        class="w-full px-3 py-1.5 rounded-lg border border-zinc-300 bg-white"
                                    >
                                </div>
                                <div>
                                    <label class="block font-medium text-zinc-600 mb-1">Kuantitas</label>
                                    <input
                                        type="number"
                                        min="1"
                                        :name="'items[' + index + '][quantity]'"
                                        x-model.number="item.quantity"
                                        required
                                        class="w-full px-3 py-1.5 rounded-lg border border-zinc-300 bg-white text-right"
                                    >
                                </div>
                                <div>
                                    <label class="block font-medium text-zinc-600 mb-1">Harga Satuan (Rp)</label>
                                    <input
                                        type="number"
                                        min="0"
                                        step="1000"
                                        :name="'items[' + index + '][unit_price]'"
                                        x-model.number="item.unit_price"
                                        required
                                        class="w-full px-3 py-1.5 rounded-lg border border-zinc-300 bg-white text-right font-mono"
                                    >
                                </div>
                            </div>
                            <div class="grid grid-cols-1 sm:grid-cols-4 gap-3 pt-2">
                                <div class="sm:col-span-2">
                                    <label class="block font-medium text-zinc-600 mb-1">Kategori Biaya Terkait (Opsional)</label>
                                    <select :name="'items[' + index + '][fee_category_id]'" x-model="item.fee_category_id" class="w-full px-3 py-1.5 rounded-lg border border-zinc-300 bg-white">
                                        <option value="">-- Umum / Lainnya --</option>
                                        @foreach ($feeCategories as $fc)
                                            <option value="{{ $fc->id }}">{{ $fc->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label class="block font-medium text-zinc-600 mb-1">Diskon / Potongan (Rp)</label>
                                    <input
                                        type="number"
                                        min="0"
                                        step="1000"
                                        :name="'items[' + index + '][discount_amount]'"
                                        x-model.number="item.discount_amount"
                                        class="w-full px-3 py-1.5 rounded-lg border border-zinc-300 bg-white text-right font-mono text-emerald-700"
                                    >
                                </div>
                                <div class="flex flex-col justify-end text-right">
                                    <span class="text-[11px] text-zinc-500">Subtotal Item</span>
                                    <span class="font-bold text-zinc-900 font-mono text-sm" x-text="formatRupiah(Math.max(0, (item.quantity * item.unit_price) - item.discount_amount))"></span>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>

                <!-- Live Total Preview Box -->
                <div class="mt-4 p-4 rounded-xl bg-zinc-100 border border-zinc-200 text-xs space-y-1.5">
                    <div class="flex justify-between text-zinc-600">
                        <span>Subtotal Kotor:</span>
                        <span class="font-mono font-semibold" x-text="formatRupiah(calculateSubtotal())"></span>
                    </div>
                    <div class="flex justify-between text-emerald-700">
                        <span>Total Potongan Diskon:</span>
                        <span class="font-mono font-semibold" x-text="formatRupiah(calculateDiscount())"></span>
                    </div>
                    <div class="flex justify-between text-base font-bold text-zinc-900 pt-2 border-t border-zinc-200">
                        <span>Total Tagihan Akhir:</span>
                        <span class="text-[#E11D2E] font-mono" x-text="formatRupiah(calculateTotal())"></span>
                    </div>
                </div>
            </div>

            <!-- Catatan Tambahan -->
            <div class="bg-white p-6 rounded-2xl border border-zinc-200 shadow-xs space-y-2 text-xs">
                <label class="block font-semibold text-zinc-700">Catatan untuk Siswa / Orang Tua (Opsional)</label>
                <textarea name="notes" rows="3" placeholder="Informasi rekening tujuan transfer bank, instruksi batas waktu, atau ketentuan dispensasi..." class="w-full px-3 py-2 rounded-lg border border-zinc-300 focus:ring-2 focus:ring-brand-500"></textarea>
            </div>

            <!-- Submit Button -->
            <div class="flex items-center justify-end gap-3 pt-4">
                <a href="{{ route('finance.invoices.index') }}" class="px-5 py-2.5 rounded-lg border border-zinc-300 text-zinc-700 text-xs font-semibold hover:bg-zinc-50">
                    Batal
                </a>
                <button type="submit" class="px-6 py-2.5 rounded-lg bg-[#E11D2E] text-white text-xs font-semibold hover:bg-[#c81926] shadow-sm">
                    Terbitkan Tagihan Sekarang
                </button>
            </div>
        </form>
    </div>
</x-app-layout>
