<x-app-layout>
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8">
        <!-- Header -->
        <div class="flex items-center justify-between pb-6 border-b border-zinc-200">
            <div>
                <nav class="flex items-center gap-2 text-xs text-zinc-500 mb-2">
                    <a href="{{ route('finance.invoices.index') }}" class="hover:text-zinc-800">Manajemen Tagihan</a>
                    <span>&bull;</span>
                    <span class="text-zinc-800 font-semibold">Penerbitan Massal</span>
                </nav>
                <h1 class="text-2xl font-bold text-zinc-900 tracking-tight">Penerbitan Tagihan Massal (SPP)</h1>
                <p class="text-sm text-zinc-500 mt-1">Generate otomatis tagihan berkala untuk seluruh siswa aktif dalam satu rombel atau tingkat kelas.</p>
            </div>
            <a href="{{ route('finance.invoices.index') }}" class="px-3.5 py-1.5 rounded-lg border border-zinc-300 text-zinc-700 text-xs font-semibold hover:bg-zinc-50">
                Kembali
            </a>
        </div>

        @if ($errors->any())
            <div class="p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-sm">
                <p class="font-semibold mb-1">Terjadi kesalahan validasi:</p>
                <ul class="list-disc list-inside space-y-0.5">
                    @foreach ($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Guard Notice -->
        <div class="p-4 rounded-2xl bg-blue-50/70 border border-blue-200 text-blue-900 text-xs space-y-1.5">
            <div class="font-bold flex items-center gap-2">
                <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span>Perlindungan Integritas Tagihan Finansial (Audit-Safe)</span>
            </div>
            <p class="text-blue-800 leading-relaxed">
                Sistem secara otomatis memeriksa apakah siswa sudah memiliki tagihan untuk kategori dan periode yang sama. Siswa yang sudah ditagih akan <strong>dilewati secara aman</strong> sehingga tidak ada risiko tagihan ganda. Besaran nominal otomatis memperhitungkan tarif khusus kelas dan potongan beasiswa aktif milik siswa.
            </p>
        </div>

        <form method="POST" action="{{ route('finance.invoices.bulk.store') }}" class="space-y-6" onsubmit="return confirm('Apakah Anda yakin ingin menerbitkan tagihan massal untuk kriteria ini?');">
            @csrf

            <div class="bg-white p-6 rounded-2xl border border-zinc-200 shadow-xs space-y-4 text-xs">
                <h2 class="text-sm font-bold text-zinc-900 uppercase tracking-wider pb-2 border-b border-zinc-100">Kriteria Penagihan Massal</h2>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block font-semibold text-zinc-700 mb-1">Kategori Biaya <span class="text-rose-500">*</span></label>
                        <select name="fee_category_id" required class="w-full px-3 py-2 rounded-lg border border-zinc-300 focus:ring-2 focus:ring-brand-500">
                            @foreach ($feeCategories as $fc)
                                <option value="{{ $fc->id }}">{{ $fc->name }} (Standar: {{ $fc->formatted_amount }})</option>
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
                        <label class="block font-semibold text-zinc-700 mb-1">Kunci Periode Tagihan <span class="text-rose-500">*</span></label>
                        <input
                            type="text"
                            name="billing_period_key"
                            value="{{ old('billing_period_key', date('Y-m')) }}"
                            placeholder="Format: YYYY-MM (misal: 2026-11)"
                            required
                            class="w-full px-3 py-2 rounded-lg border border-zinc-300 focus:ring-2 focus:ring-brand-500 font-mono"
                        >
                        <span class="text-[10px] text-zinc-400 mt-1 block">Identifikasi unik periode penagihan untuk mencegah duplikasi (misal: 2026-11 untuk SPP Nov 2026).</span>
                    </div>

                    <div>
                        <label class="block font-semibold text-zinc-700 mb-1">Batas Waktu Pembayaran (Jatuh Tempo) <span class="text-rose-500">*</span></label>
                        <input
                            type="date"
                            name="due_date"
                            value="{{ old('due_date', date('Y-m-10', strtotime('+1 month'))) }}"
                            required
                            class="w-full px-3 py-2 rounded-lg border border-zinc-300 focus:ring-2 focus:ring-brand-500"
                        >
                    </div>
                </div>

                <div class="pt-4 border-t border-zinc-100">
                    <h3 class="font-bold text-zinc-800 mb-3">Target Sasaran Siswa (Opsional)</h3>
                    <p class="text-zinc-500 text-[11px] mb-3">Biarkan kosong untuk menerbitkan ke seluruh siswa terdaftar pada tahun ajaran aktif.</p>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block font-semibold text-zinc-700 mb-1">Tingkat Kelas Tertentu</label>
                            <select name="grade_level_id" class="w-full px-3 py-2 rounded-lg border border-zinc-300">
                                <option value="">-- Seluruh Jenjang / Tingkat --</option>
                                @foreach ($gradeLevels as $gl)
                                    <option value="{{ $gl->id }}">Tingkat {{ $gl->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block font-semibold text-zinc-700 mb-1">Rombel Kelas Spesifik</label>
                            <select name="classroom_id" class="w-full px-3 py-2 rounded-lg border border-zinc-300">
                                <option value="">-- Seluruh Rombel Kelas --</option>
                                @foreach ($classrooms as $cr)
                                    <option value="{{ $cr->id }}">Kelas {{ $cr->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Submit Button -->
            <div class="flex items-center justify-end gap-3 pt-4">
                <a href="{{ route('finance.invoices.index') }}" class="px-5 py-2.5 rounded-lg border border-zinc-300 text-zinc-700 text-xs font-semibold hover:bg-zinc-50">
                    Batal
                </a>
                <button type="submit" class="px-6 py-2.5 rounded-lg bg-[#E11D2E] text-white text-xs font-semibold hover:bg-[#c81926] shadow-sm">
                    Mulai Terbitkan Tagihan Massal
                </button>
            </div>
        </form>
    </div>
</x-app-layout>
