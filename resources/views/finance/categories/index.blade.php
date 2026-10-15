<x-app-layout>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8" x-data="{ activeTab: 'categories', showCatModal: false, showRuleModal: false, showDiscountModal: false }">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-6 border-b border-zinc-200">
            <div>
                <nav class="flex items-center gap-2 text-xs text-zinc-500 mb-2">
                    <a href="{{ route('finance.invoices.index') }}" class="hover:text-zinc-800">Keuangan Sekolah</a>
                    <span>&bull;</span>
                    <span class="text-zinc-800 font-semibold">Konfigurasi Tarif & Beasiswa</span>
                </nav>
                <h1 class="text-2xl font-bold text-zinc-900 tracking-tight">Konfigurasi Tarif & Potongan Biaya</h1>
                <p class="text-sm text-zinc-500 mt-1">Kelola master komponen biaya, tarif per jenjang/kelas, dan kebijakan beasiswa.</p>
            </div>
            <div class="flex items-center gap-2">
                <button
                    type="button"
                    @click="showCatModal = true"
                    class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-[#E11D2E] text-white text-xs font-semibold hover:bg-[#c81926] shadow-sm transition-colors"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    <span>Tambah Kategori Biaya</span>
                </button>
            </div>
        </div>

        @if (session('success'))
            <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm flex items-center justify-between">
                <span>{{ session('success') }}</span>
            </div>
        @endif

        @if ($errors->any())
            <div class="p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-sm">
                <p class="font-semibold mb-1">Mohon perbaiki kesalahan berikut:</p>
                <ul class="list-disc list-inside space-y-0.5">
                    @foreach ($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Tab Controls -->
        <div class="flex border-b border-zinc-200 gap-6 text-sm font-medium">
            <button
                @click="activeTab = 'categories'"
                :class="activeTab === 'categories' ? 'text-[#E11D2E] border-b-2 border-[#E11D2E] pb-3 font-bold' : 'text-zinc-500 hover:text-zinc-800 pb-3'"
            >
                1. Kategori Biaya ({{ $categories->count() }})
            </button>
            <button
                @click="activeTab = 'rules'"
                :class="activeTab === 'rules' ? 'text-[#E11D2E] border-b-2 border-[#E11D2E] pb-3 font-bold' : 'text-zinc-500 hover:text-zinc-800 pb-3'"
            >
                2. Tarif Khusus Jenjang/Kelas ({{ $rules->total() }})
            </button>
            <button
                @click="activeTab = 'discounts'"
                :class="activeTab === 'discounts' ? 'text-[#E11D2E] border-b-2 border-[#E11D2E] pb-3 font-bold' : 'text-zinc-500 hover:text-zinc-800 pb-3'"
            >
                3. Beasiswa & Keringanan ({{ $discounts->total() }})
            </button>
        </div>

        <!-- TAB 1: KATEGORI BIAYA -->
        <div x-show="activeTab === 'categories'" class="space-y-4">
            <div class="bg-white rounded-2xl border border-zinc-200 shadow-xs overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-zinc-50/80 text-zinc-500 font-semibold border-b border-zinc-200 uppercase tracking-wider text-[11px]">
                            <tr>
                                <th class="px-5 py-3">Kode</th>
                                <th class="px-5 py-3">Nama Biaya</th>
                                <th class="px-5 py-3">Tipe</th>
                                <th class="px-5 py-3">Frekuensi</th>
                                <th class="px-5 py-3">Nominal Standar</th>
                                <th class="px-5 py-3">Aturan Khusus</th>
                                <th class="px-5 py-3">Status</th>
                                <th class="px-5 py-3 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-200">
                            @forelse ($categories as $cat)
                                <tr class="hover:bg-zinc-50/60 transition-colors">
                                    <td class="px-5 py-3.5 font-mono font-bold text-zinc-900">{{ $cat->code }}</td>
                                    <td class="px-5 py-3.5">
                                        <div class="font-bold text-zinc-900">{{ $cat->name }}</div>
                                        <div class="text-[11px] text-zinc-400 mt-0.5">{{ $cat->description ?? '-' }}</div>
                                    </td>
                                    <td class="px-5 py-3.5">
                                        <span class="px-2 py-0.5 rounded text-[11px] font-semibold {{ $cat->type === 'recurring' ? 'bg-blue-50 text-blue-700' : 'bg-purple-50 text-purple-700' }}">
                                            {{ $cat->type === 'recurring' ? 'Rutin (Berkala)' : 'Sekali Bayar' }}
                                        </span>
                                    </td>
                                    <td class="px-5 py-3.5 capitalize font-medium text-zinc-700">{{ $cat->billing_period }}</td>
                                    <td class="px-5 py-3.5 font-bold text-zinc-900">{{ $cat->formatted_amount }}</td>
                                    <td class="px-5 py-3.5 text-zinc-500">{{ $cat->rules_count }} aturan</td>
                                    <td class="px-5 py-3.5">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-semibold {{ $cat->is_active ? 'bg-emerald-50 text-emerald-700' : 'bg-zinc-100 text-zinc-500' }}">
                                            {{ $cat->is_active ? 'Aktif' : 'Nonaktif' }}
                                        </span>
                                    </td>
                                    <td class="px-5 py-3.5 text-right">
                                        <button
                                            type="button"
                                            @click="showRuleModal = true"
                                            class="text-xs text-brand-600 hover:text-brand-700 font-semibold"
                                        >
                                            + Tarif Khusus
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="px-5 py-10 text-center text-zinc-400 text-xs">
                                        Belum ada kategori tarif biaya. Klik tombol "Tambah Kategori Biaya" untuk memulai.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- TAB 2: TARIF KHUSUS -->
        <div x-show="activeTab === 'rules'" class="space-y-4" style="display: none;">
            <div class="flex justify-between items-center">
                <p class="text-xs text-zinc-500">Tarif khusus menimpa (override) nominal standar untuk rombel atau tingkat kelas tertentu.</p>
                <button
                    type="button"
                    @click="showRuleModal = true"
                    class="px-3.5 py-1.5 rounded-lg bg-zinc-900 text-white text-xs font-semibold hover:bg-zinc-800"
                >
                    + Tambah Tarif Khusus
                </button>
            </div>
            <div class="bg-white rounded-2xl border border-zinc-200 shadow-xs overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-zinc-50/80 text-zinc-500 font-semibold border-b border-zinc-200 uppercase tracking-wider text-[11px]">
                            <tr>
                                <th class="px-5 py-3">Kategori Biaya</th>
                                <th class="px-5 py-3">Tahun Ajaran</th>
                                <th class="px-5 py-3">Tingkat / Kelas</th>
                                <th class="px-5 py-3">Nominal Khusus</th>
                                <th class="px-5 py-3">Catatan</th>
                                <th class="px-5 py-3 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-200">
                            @forelse ($rules as $rule)
                                <tr class="hover:bg-zinc-50/60 transition-colors">
                                    <td class="px-5 py-3.5 font-bold text-zinc-900">{{ $rule->feeCategory?->name }}</td>
                                    <td class="px-5 py-3.5 text-zinc-600">{{ $rule->academicYear?->name }}</td>
                                    <td class="px-5 py-3.5 font-semibold text-zinc-800">
                                        @if ($rule->classroom)
                                            Kelas {{ $rule->classroom->name }}
                                        @elseif ($rule->gradeLevel)
                                            Tingkat {{ $rule->gradeLevel->name }}
                                        @else
                                            Semua Jenjang
                                        @endif
                                    </td>
                                    <td class="px-5 py-3.5 font-bold text-zinc-900">{{ $rule->formatted_amount }}</td>
                                    <td class="px-5 py-3.5 text-zinc-400 text-[11px]">{{ $rule->notes ?? '-' }}</td>
                                    <td class="px-5 py-3.5 text-right">
                                        <form method="POST" action="{{ route('finance.rules.destroy', $rule) }}" onsubmit="return confirm('Hapus aturan tarif khusus ini?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-rose-600 hover:text-rose-800 font-semibold text-xs">Hapus</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-5 py-8 text-center text-zinc-400 text-xs">
                                        Belum ada aturan tarif khusus. Seluruh siswa menggunakan tarif standar kategori biaya.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if ($rules->hasPages())
                    <div class="px-5 py-3 border-t border-zinc-200">
                        {{ $rules->links() }}
                    </div>
                @endif
            </div>
        </div>

        <!-- TAB 3: BEASISWA & KERINGANAN -->
        <div x-show="activeTab === 'discounts'" class="space-y-4" style="display: none;">
            <div class="flex justify-between items-center">
                <p class="text-xs text-zinc-500">Daftar siswa penerima beasiswa prestasi, keringanan yatim/dhuafa, atau diskon khusus.</p>
                <button
                    type="button"
                    @click="showDiscountModal = true"
                    class="px-3.5 py-1.5 rounded-lg bg-zinc-900 text-white text-xs font-semibold hover:bg-zinc-800"
                >
                    + Berikan Potongan Siswa
                </button>
            </div>
            <div class="bg-white rounded-2xl border border-zinc-200 shadow-xs overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-zinc-50/80 text-zinc-500 font-semibold border-b border-zinc-200 uppercase tracking-wider text-[11px]">
                            <tr>
                                <th class="px-5 py-3">Nama Siswa</th>
                                <th class="px-5 py-3">Nama Program / Beasiswa</th>
                                <th class="px-5 py-3">Nilai Potongan</th>
                                <th class="px-5 py-3">Berlaku Untuk</th>
                                <th class="px-5 py-3">Keterangan / Alasan</th>
                                <th class="px-5 py-3 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-200">
                            @forelse ($discounts as $disc)
                                <tr class="hover:bg-zinc-50/60 transition-colors">
                                    <td class="px-5 py-3.5">
                                        <div class="font-bold text-zinc-900">{{ $disc->student?->name }}</div>
                                        <div class="text-[11px] text-zinc-400">NIS: {{ $disc->student?->studentProfile?->nis ?? '-' }}</div>
                                    </td>
                                    <td class="px-5 py-3.5 font-semibold text-zinc-800">{{ $disc->name }}</td>
                                    <td class="px-5 py-3.5 font-bold text-emerald-700 bg-emerald-50/40">
                                        {{ $disc->formatted_value }}
                                    </td>
                                    <td class="px-5 py-3.5 text-zinc-600">
                                        {{ $disc->feeCategory?->name ?? 'Semua Biaya' }}
                                        <span class="text-zinc-400">({{ $disc->academicYear?->name ?? 'Semua TA' }})</span>
                                    </td>
                                    <td class="px-5 py-3.5 text-zinc-500 text-[11px]">{{ $disc->reason ?? '-' }}</td>
                                    <td class="px-5 py-3.5 text-right">
                                        <form method="POST" action="{{ route('finance.discounts.destroy', $disc) }}" onsubmit="return confirm('Hapus data potongan beasiswa ini?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-rose-600 hover:text-rose-800 font-semibold text-xs">Hapus</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-5 py-8 text-center text-zinc-400 text-xs">
                                        Belum ada siswa yang mendapatkan potongan atau beasiswa khusus.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if ($discounts->hasPages())
                    <div class="px-5 py-3 border-t border-zinc-200">
                        {{ $discounts->links() }}
                    </div>
                @endif
            </div>
        </div>

        <!-- MODAL: TAMBAH KATEGORI BIAYA -->
        <div x-show="showCatModal" class="fixed inset-0 z-50 overflow-y-auto bg-zinc-900/60 backdrop-blur-xs flex items-center justify-center p-4" style="display: none;">
            <div class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-xl border border-zinc-200" @click.outside="showCatModal = false">
                <div class="flex items-center justify-between pb-4 border-b border-zinc-100">
                    <h3 class="text-base font-bold text-zinc-900">Tambah Kategori Biaya Baru</h3>
                    <button type="button" @click="showCatModal = false" class="text-zinc-400 hover:text-zinc-600">&times;</button>
                </div>
                <form method="POST" action="{{ route('finance.categories.store') }}" class="space-y-4 pt-4 text-xs">
                    @csrf
                    <div>
                        <label class="block font-semibold text-zinc-700 mb-1">Kode Biaya <span class="text-rose-500">*</span></label>
                        <input type="text" name="code" placeholder="Misal: SPP, GEDUNG, UJIAN" required class="w-full px-3 py-2 rounded-lg border border-zinc-300 focus:ring-2 focus:ring-brand-500 uppercase font-mono">
                    </div>
                    <div>
                        <label class="block font-semibold text-zinc-700 mb-1">Nama Biaya <span class="text-rose-500">*</span></label>
                        <input type="text" name="name" placeholder="Misal: SPP Bulanan Reguler" required class="w-full px-3 py-2 rounded-lg border border-zinc-300 focus:ring-2 focus:ring-brand-500">
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block font-semibold text-zinc-700 mb-1">Tipe Biaya <span class="text-rose-500">*</span></label>
                            <select name="type" required class="w-full px-3 py-2 rounded-lg border border-zinc-300 focus:ring-2 focus:ring-brand-500">
                                <option value="recurring">Rutin (Berkala)</option>
                                <option value="one_time">Sekali Bayar</option>
                            </select>
                        </div>
                        <div>
                            <label class="block font-semibold text-zinc-700 mb-1">Frekuensi Penagihan <span class="text-rose-500">*</span></label>
                            <select name="billing_period" required class="w-full px-3 py-2 rounded-lg border border-zinc-300 focus:ring-2 focus:ring-brand-500">
                                <option value="monthly">Bulanan</option>
                                <option value="semester">Per Semester</option>
                                <option value="annual">Tahunan</option>
                                <option value="once">Sekali Bayar</option>
                            </select>
                        </div>
                    </div>
                    <div>
                        <label class="block font-semibold text-zinc-700 mb-1">Nominal Standar (Rp) <span class="text-rose-500">*</span></label>
                        <input type="number" name="amount" min="0" step="1000" placeholder="Misal: 500000" required class="w-full px-3 py-2 rounded-lg border border-zinc-300 focus:ring-2 focus:ring-brand-500">
                    </div>
                    <div>
                        <label class="block font-semibold text-zinc-700 mb-1">Deskripsi Tambahan</label>
                        <textarea name="description" rows="2" placeholder="Catatan kegunaan atau rincian biaya..." class="w-full px-3 py-2 rounded-lg border border-zinc-300 focus:ring-2 focus:ring-brand-500"></textarea>
                    </div>
                    <div class="flex justify-end gap-2 pt-3 border-t border-zinc-100">
                        <button type="button" @click="showCatModal = false" class="px-4 py-2 rounded-lg border border-zinc-300 text-zinc-700 font-semibold hover:bg-zinc-50">Batal</button>
                        <button type="submit" class="px-4 py-2 rounded-lg bg-[#E11D2E] text-white font-semibold hover:bg-[#c81926]">Simpan Kategori</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- MODAL: TAMBAH TARIF KHUSUS -->
        <div x-show="showRuleModal" class="fixed inset-0 z-50 overflow-y-auto bg-zinc-900/60 backdrop-blur-xs flex items-center justify-center p-4" style="display: none;">
            <div class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-xl border border-zinc-200" @click.outside="showRuleModal = false">
                <div class="flex items-center justify-between pb-4 border-b border-zinc-100">
                    <h3 class="text-base font-bold text-zinc-900">Tambah Aturan Tarif Khusus</h3>
                    <button type="button" @click="showRuleModal = false" class="text-zinc-400 hover:text-zinc-600">&times;</button>
                </div>
                <form method="POST" action="{{ route('finance.rules.store') }}" class="space-y-4 pt-4 text-xs">
                    @csrf
                    <div>
                        <label class="block font-semibold text-zinc-700 mb-1">Kategori Biaya <span class="text-rose-500">*</span></label>
                        <select name="fee_category_id" required class="w-full px-3 py-2 rounded-lg border border-zinc-300">
                            @foreach ($categories as $cat)
                                <option value="{{ $cat->id }}">{{ $cat->name }} (Standar: {{ $cat->formatted_amount }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block font-semibold text-zinc-700 mb-1">Tahun Ajaran <span class="text-rose-500">*</span></label>
                        <select name="academic_year_id" required class="w-full px-3 py-2 rounded-lg border border-zinc-300">
                            @foreach ($academicYears as $ay)
                                <option value="{{ $ay->id }}">{{ $ay->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block font-semibold text-zinc-700 mb-1">Tingkat Kelas (Opsional)</label>
                            <select name="grade_level_id" class="w-full px-3 py-2 rounded-lg border border-zinc-300">
                                <option value="">-- Semua Tingkat --</option>
                                @foreach ($gradeLevels as $gl)
                                    <option value="{{ $gl->id }}">Tingkat {{ $gl->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block font-semibold text-zinc-700 mb-1">Rombel Kelas (Opsional)</label>
                            <select name="classroom_id" class="w-full px-3 py-2 rounded-lg border border-zinc-300">
                                <option value="">-- Semua Rombel --</option>
                                @foreach ($classrooms as $cr)
                                    <option value="{{ $cr->id }}">Kelas {{ $cr->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div>
                        <label class="block font-semibold text-zinc-700 mb-1">Nominal Tarif Khusus (Rp) <span class="text-rose-500">*</span></label>
                        <input type="number" name="amount" min="0" step="1000" placeholder="Misal: 450000" required class="w-full px-3 py-2 rounded-lg border border-zinc-300">
                    </div>
                    <div>
                        <label class="block font-semibold text-zinc-700 mb-1">Catatan</label>
                        <input type="text" name="notes" placeholder="Misal: Subsidi kelas inklusi" class="w-full px-3 py-2 rounded-lg border border-zinc-300">
                    </div>
                    <div class="flex justify-end gap-2 pt-3 border-t border-zinc-100">
                        <button type="button" @click="showRuleModal = false" class="px-4 py-2 rounded-lg border border-zinc-300 text-zinc-700 font-semibold hover:bg-zinc-50">Batal</button>
                        <button type="submit" class="px-4 py-2 rounded-lg bg-zinc-900 text-white font-semibold hover:bg-zinc-800">Simpan Tarif Khusus</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- MODAL: TAMBAH BEASISWA / POTONGAN -->
        <div x-show="showDiscountModal" class="fixed inset-0 z-50 overflow-y-auto bg-zinc-900/60 backdrop-blur-xs flex items-center justify-center p-4" style="display: none;">
            <div class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-xl border border-zinc-200" @click.outside="showDiscountModal = false">
                <div class="flex items-center justify-between pb-4 border-b border-zinc-100">
                    <h3 class="text-base font-bold text-zinc-900">Berikan Beasiswa / Potongan Biaya</h3>
                    <button type="button" @click="showDiscountModal = false" class="text-zinc-400 hover:text-zinc-600">&times;</button>
                </div>
                <form method="POST" action="{{ route('finance.discounts.store') }}" class="space-y-4 pt-4 text-xs">
                    @csrf
                    <div>
                        <label class="block font-semibold text-zinc-700 mb-1">ID Siswa (User ID) <span class="text-rose-500">*</span></label>
                        <input type="number" name="student_id" placeholder="Masukkan ID Siswa terdaftar" required class="w-full px-3 py-2 rounded-lg border border-zinc-300">
                    </div>
                    <div>
                        <label class="block font-semibold text-zinc-700 mb-1">Nama Beasiswa / Program <span class="text-rose-500">*</span></label>
                        <input type="text" name="name" placeholder="Misal: Beasiswa Juara OSN 50%" required class="w-full px-3 py-2 rounded-lg border border-zinc-300">
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block font-semibold text-zinc-700 mb-1">Bentuk Potongan <span class="text-rose-500">*</span></label>
                            <select name="type" required class="w-full px-3 py-2 rounded-lg border border-zinc-300">
                                <option value="percentage">Persentase (%)</option>
                                <option value="fixed">Nominal Tetap (Rp)</option>
                            </select>
                        </div>
                        <div>
                            <label class="block font-semibold text-zinc-700 mb-1">Besaran Nilai <span class="text-rose-500">*</span></label>
                            <input type="number" name="value" min="1" placeholder="Misal: 50 untuk 50%" required class="w-full px-3 py-2 rounded-lg border border-zinc-300">
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block font-semibold text-zinc-700 mb-1">Kategori Biaya (Opsional)</label>
                            <select name="fee_category_id" class="w-full px-3 py-2 rounded-lg border border-zinc-300">
                                <option value="">-- Semua Kategori --</option>
                                @foreach ($categories as $cat)
                                    <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block font-semibold text-zinc-700 mb-1">Tahun Ajaran (Opsional)</label>
                            <select name="academic_year_id" class="w-full px-3 py-2 rounded-lg border border-zinc-300">
                                <option value="">-- Semua TA --</option>
                                @foreach ($academicYears as $ay)
                                    <option value="{{ $ay->id }}">{{ $ay->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div>
                        <label class="block font-semibold text-zinc-700 mb-1">Alasan / SK Pemberian</label>
                        <textarea name="reason" rows="2" placeholder="Nomor SK Kepala Sekolah atau alasan pertimbangan..." class="w-full px-3 py-2 rounded-lg border border-zinc-300"></textarea>
                    </div>
                    <div class="flex justify-end gap-2 pt-3 border-t border-zinc-100">
                        <button type="button" @click="showDiscountModal = false" class="px-4 py-2 rounded-lg border border-zinc-300 text-zinc-700 font-semibold hover:bg-zinc-50">Batal</button>
                        <button type="submit" class="px-4 py-2 rounded-lg bg-[#E11D2E] text-white font-semibold hover:bg-[#c81926]">Simpan Beasiswa</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
