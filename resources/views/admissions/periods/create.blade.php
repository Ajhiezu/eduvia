<x-layouts.app>
    <x-slot:headerTitle>
        Tambah Periode PPDB
    </x-slot:headerTitle>

    <div class="space-y-6">
        <!-- Back Navigation -->
        <div class="flex items-center gap-2">
            <x-button href="{{ route('admissions.periods.index') }}" variant="secondary" size="xs">
                &larr; Kembali
            </x-button>
            <span class="text-xs text-zinc-400">/</span>
            <span class="text-xs font-semibold text-zinc-600">Tambah Periode Baru</span>
        </div>

        <div>
            <h1 class="text-xl sm:text-2xl font-extrabold text-zinc-900 tracking-tight">
                Buat Periode Pendaftaran Baru
            </h1>
            <p class="text-xs text-zinc-500 mt-0.5">
                Tentukan jadwal pendaftaran, kuota total, jalur seleksi, serta persyaratan dokumen.
            </p>
        </div>

        <!-- Validation Errors -->
        @if ($errors->any())
            <x-alert variant="danger" dismissible>
                <div class="font-bold">Gagal menyimpan data:</div>
                <ul class="list-disc pl-5 mt-1 space-y-0.5 text-xs">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </x-alert>
        @endif

        <form
            action="{{ route('admissions.periods.store') }}"
            method="POST"
            x-data="{
                tracks: [
                    { name: 'Jalur Zonasi', code: 'ZONASI', quota: 50, description: 'Berdasarkan radius tempat tinggal' },
                    { name: 'Jalur Prestasi', code: 'PRESTASI', quota: 30, description: 'Berdasarkan nilai rapor dan piagam' }
                ],
                addTrack() {
                    this.tracks.push({ name: '', code: '', quota: 10, description: '' });
                },
                removeTrack(index) {
                    if (this.tracks.length > 1) {
                        this.tracks.splice(index, 1);
                    }
                }
            }"
            class="space-y-6"
        >
            @csrf

            <!-- Section 1: Informasi Umum -->
            <x-card padding class="border-zinc-200">
                <h2 class="text-sm font-bold text-zinc-900 tracking-tight pb-3 border-b border-zinc-100">
                    1. Informasi Umum Periode
                </h2>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mt-4">
                    <div class="sm:col-span-2">
                        <x-label for="name" required>Nama Periode PPDB</x-label>
                        <x-input
                            id="name"
                            name="name"
                            :value="old('name', 'PPDB Tahun Ajaran 2026/2027 Gelombang 1')"
                            required
                        />
                    </div>

                    <div>
                        <x-label for="code" required>Kode Periode (Unik)</x-label>
                        <x-input
                            id="code"
                            name="code"
                            :value="old('code', 'PPDB-2026-G1')"
                            class="uppercase font-mono"
                            required
                        />
                    </div>

                    <div>
                        <x-label for="academic_year_id" required>Tahun Ajaran Target</x-label>
                        <select
                            id="academic_year_id"
                            name="academic_year_id"
                            required
                            class="w-full mt-1 px-3 py-2 text-xs sm:text-sm bg-white border border-zinc-200 rounded-lg text-zinc-800 focus:outline-none focus:ring-2 focus:ring-red-100 focus:border-primary"
                        >
                            @foreach ($academicYears as $year)
                                <option value="{{ $year->id }}" @selected(old('academic_year_id') == $year->id)>
                                    {{ $year->name }} {{ $year->is_active ? '(Aktif)' : '' }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <x-label for="quota" required>Total Kuota Penerimaan</x-label>
                        <x-input
                            id="quota"
                            name="quota"
                            type="number"
                            :value="old('quota', 100)"
                            min="1"
                            required
                        />
                    </div>

                    <div>
                        <x-label for="status" required>Status Awal</x-label>
                        <select
                            id="status"
                            name="status"
                            required
                            class="w-full mt-1 px-3 py-2 text-xs sm:text-sm bg-white border border-zinc-200 rounded-lg text-zinc-800 focus:outline-none focus:ring-2 focus:ring-red-100 focus:border-primary"
                        >
                            <option value="draft" @selected(old('status') == 'draft')>Draft (Persiapan)</option>
                            <option value="open" @selected(old('status') == 'open')>Buka Pendaftaran (Open)</option>
                            <option value="closed" @selected(old('status') == 'closed')>Tutup Pendaftaran (Closed)</option>
                        </select>
                    </div>
                </div>
            </x-card>

            <!-- Section 2: Jadwal & Timeline -->
            <x-card padding class="border-zinc-200">
                <h2 class="text-sm font-bold text-zinc-900 tracking-tight pb-3 border-b border-zinc-100">
                    2. Jadwal & Timeline Penerimaan
                </h2>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 mt-4">
                    <div>
                        <x-label for="start_date" required>Tanggal Buka Pendaftaran</x-label>
                        <x-input
                            id="start_date"
                            name="start_date"
                            type="date"
                            :value="old('start_date', now()->toDateString())"
                            required
                        />
                    </div>

                    <div>
                        <x-label for="end_date" required>Tanggal Tutup Pendaftaran</x-label>
                        <x-input
                            id="end_date"
                            name="end_date"
                            type="date"
                            :value="old('end_date', now()->addDays(30)->toDateString())"
                            required
                        />
                    </div>

                    <div>
                        <x-label for="announcement_date" required>Tanggal Pengumuman Hasil</x-label>
                        <x-input
                            id="announcement_date"
                            name="announcement_date"
                            type="date"
                            :value="old('announcement_date', now()->addDays(35)->toDateString())"
                            required
                        />
                    </div>

                    <div>
                        <x-label for="re_registration_start_date" required>Mulai Daftar Ulang</x-label>
                        <x-input
                            id="re_registration_start_date"
                            name="re_registration_start_date"
                            type="date"
                            :value="old('re_registration_start_date', now()->addDays(36)->toDateString())"
                            required
                        />
                    </div>

                    <div>
                        <x-label for="re_registration_end_date" required>Batas Akhir Daftar Ulang</x-label>
                        <x-input
                            id="re_registration_end_date"
                            name="re_registration_end_date"
                            type="date"
                            :value="old('re_registration_end_date', now()->addDays(42)->toDateString())"
                            required
                        />
                    </div>
                </div>
            </x-card>

            <!-- Section 3: Jalur Pendaftaran (Dynamic Alpine) -->
            <x-card padding class="border-zinc-200">
                <div class="flex items-center justify-between pb-3 border-b border-zinc-100">
                    <div>
                        <h2 class="text-sm font-bold text-zinc-900 tracking-tight">
                            3. Jalur Pendaftaran
                        </h2>
                        <p class="text-xs text-zinc-500">Konfigurasi jalur dan kuota masing-masing jalur seleksi.</p>
                    </div>

                    <button
                        type="button"
                        @click="addTrack()"
                        class="px-2.5 py-1 text-xs font-semibold rounded-lg bg-zinc-100 hover:bg-zinc-200 text-zinc-800 transition-colors"
                    >
                        + Tambah Jalur
                    </button>
                </div>

                <div class="space-y-3 mt-4">
                    <template x-for="(track, index) in tracks" :key="index">
                        <div class="p-3 bg-zinc-50 rounded-xl border border-zinc-200 flex flex-col sm:flex-row items-start sm:items-center gap-3">
                            <div class="w-full sm:w-1/3">
                                <label class="text-[11px] font-medium text-zinc-500 block mb-1">Nama Jalur</label>
                                <input
                                    type="text"
                                    :name="`tracks[${index}][name]`"
                                    x-model="track.name"
                                    placeholder="Contoh: Jalur Zonasi"
                                    required
                                    class="w-full px-2.5 py-1.5 text-xs bg-white border border-zinc-200 rounded-lg text-zinc-800 focus:outline-none focus:ring-1 focus:ring-primary"
                                >
                            </div>

                            <div class="w-full sm:w-1/4">
                                <label class="text-[11px] font-medium text-zinc-500 block mb-1">Kode</label>
                                <input
                                    type="text"
                                    :name="`tracks[${index}][code]`"
                                    x-model="track.code"
                                    placeholder="ZONASI"
                                    required
                                    class="w-full px-2.5 py-1.5 text-xs bg-white border border-zinc-200 rounded-lg text-zinc-800 uppercase font-mono focus:outline-none focus:ring-1 focus:ring-primary"
                                >
                            </div>

                            <div class="w-full sm:w-1/6">
                                <label class="text-[11px] font-medium text-zinc-500 block mb-1">Kuota Kursi</label>
                                <input
                                    type="number"
                                    :name="`tracks[${index}][quota]`"
                                    x-model="track.quota"
                                    min="1"
                                    required
                                    class="w-full px-2.5 py-1.5 text-xs bg-white border border-zinc-200 rounded-lg text-zinc-800 focus:outline-none focus:ring-1 focus:ring-primary font-bold"
                                >
                            </div>

                            <div class="w-full sm:flex-1">
                                <label class="text-[11px] font-medium text-zinc-500 block mb-1">Keterangan Singkat</label>
                                <input
                                    type="text"
                                    :name="`tracks[${index}][description]`"
                                    x-model="track.description"
                                    placeholder="Kriteria jalur ini"
                                    class="w-full px-2.5 py-1.5 text-xs bg-white border border-zinc-200 rounded-lg text-zinc-800 focus:outline-none focus:ring-1 focus:ring-primary"
                                >
                            </div>

                            <button
                                type="button"
                                @click="removeTrack(index)"
                                x-show="tracks.length > 1"
                                class="text-rose-500 hover:text-rose-700 p-1.5 rounded-lg hover:bg-rose-50 transition-colors mt-4 sm:mt-0"
                                title="Hapus Jalur"
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                </svg>
                            </button>
                        </div>
                    </template>
                </div>
            </x-card>

            <!-- Actions -->
            <div class="flex items-center justify-end gap-3 pt-2">
                <x-button href="{{ route('admissions.periods.index') }}" variant="secondary" size="md">
                    Batal
                </x-button>
                <x-button type="submit" variant="primary" size="md">
                    Simpan Periode PPDB &rarr;
                </x-button>
            </div>
        </form>
    </div>
</x-layouts.app>
