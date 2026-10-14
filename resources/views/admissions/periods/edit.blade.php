<x-layouts.app>
    <x-slot:headerTitle>
        Edit Periode PPDB &mdash; {{ $period->name }}
    </x-slot:headerTitle>

    <div class="space-y-6">
        <!-- Back Navigation -->
        <div class="flex items-center gap-2">
            <x-button href="{{ route('admissions.periods.show', $period) }}" variant="secondary" size="xs">
                &larr; Kembali
            </x-button>
            <span class="text-xs text-zinc-400">/</span>
            <span class="text-xs font-semibold text-zinc-600">Edit Periode</span>
        </div>

        <div>
            <h1 class="text-xl sm:text-2xl font-extrabold text-zinc-900 tracking-tight">
                Edit Konfigurasi Periode PPDB
            </h1>
            <p class="text-xs text-zinc-500 mt-0.5">
                Perbarui tanggal pembukaan, jadwal pengumuman, dan kuota penerimaan.
            </p>
        </div>

        @if ($errors->any())
            <x-alert variant="danger" dismissible>
                {{ $errors->first() }}
            </x-alert>
        @endif

        <form action="{{ route('admissions.periods.update', $period) }}" method="POST" class="space-y-6">
            @csrf
            @method('PUT')

            <x-card padding class="border-zinc-200">
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div class="sm:col-span-2">
                        <x-label for="name" required>Nama Periode PPDB</x-label>
                        <x-input
                            id="name"
                            name="name"
                            :value="old('name', $period->name)"
                            required
                        />
                    </div>

                    <div>
                        <x-label for="code" required>Kode Periode</x-label>
                        <x-input
                            id="code"
                            name="code"
                            :value="old('code', $period->code)"
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
                                <option value="{{ $year->id }}" @selected(old('academic_year_id', $period->academic_year_id) == $year->id)>
                                    {{ $year->name }}
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
                            :value="old('quota', $period->quota)"
                            min="1"
                            required
                        />
                    </div>

                    <div>
                        <x-label for="status" required>Status Periode</x-label>
                        <select
                            id="status"
                            name="status"
                            required
                            class="w-full mt-1 px-3 py-2 text-xs sm:text-sm bg-white border border-zinc-200 rounded-lg text-zinc-800 focus:outline-none focus:ring-2 focus:ring-red-100 focus:border-primary font-bold"
                        >
                            <option value="draft" @selected(old('status', $period->status) == 'draft')>Draft (Persiapan)</option>
                            <option value="open" @selected(old('status', $period->status) == 'open')>Buka Pendaftaran (Open)</option>
                            <option value="closed" @selected(old('status', $period->status) == 'closed')>Tutup Pendaftaran (Closed)</option>
                            <option value="completed" @selected(old('status', $period->status) == 'completed')>Selesai (Completed)</option>
                        </select>
                    </div>

                    <div>
                        <x-label for="start_date" required>Tanggal Mulai Pendaftaran</x-label>
                        <x-input
                            id="start_date"
                            name="start_date"
                            type="date"
                            :value="old('start_date', $period->start_date?->toDateString())"
                            required
                        />
                    </div>

                    <div>
                        <x-label for="end_date" required>Tanggal Akhir Pendaftaran</x-label>
                        <x-input
                            id="end_date"
                            name="end_date"
                            type="date"
                            :value="old('end_date', $period->end_date?->toDateString())"
                            required
                        />
                    </div>

                    <div>
                        <x-label for="announcement_date" required>Tanggal Pengumuman</x-label>
                        <x-input
                            id="announcement_date"
                            name="announcement_date"
                            type="date"
                            :value="old('announcement_date', $period->announcement_date?->toDateString())"
                            required
                        />
                    </div>

                    <div>
                        <x-label for="re_registration_start_date" required>Mulai Daftar Ulang</x-label>
                        <x-input
                            id="re_registration_start_date"
                            name="re_registration_start_date"
                            type="date"
                            :value="old('re_registration_start_date', $period->re_registration_start_date?->toDateString())"
                            required
                        />
                    </div>

                    <div>
                        <x-label for="re_registration_end_date" required>Batas Akhir Daftar Ulang</x-label>
                        <x-input
                            id="re_registration_end_date"
                            name="re_registration_end_date"
                            type="date"
                            :value="old('re_registration_end_date', $period->re_registration_end_date?->toDateString())"
                            required
                        />
                    </div>

                    <div class="sm:col-span-3">
                        <x-label for="description">Deskripsi Periode</x-label>
                        <textarea
                            id="description"
                            name="description"
                            rows="2"
                            class="w-full mt-1 px-3 py-2 text-xs sm:text-sm bg-white border border-zinc-200 rounded-lg text-zinc-800 placeholder-zinc-400 focus:outline-none focus:ring-2 focus:ring-red-100 focus:border-primary"
                        >{{ old('description', $period->description) }}</textarea>
                    </div>

                    <div class="sm:col-span-3">
                        <x-label for="requirements">Persyaratan Berkas</x-label>
                        <textarea
                            id="requirements"
                            name="requirements"
                            rows="3"
                            class="w-full mt-1 px-3 py-2 text-xs sm:text-sm bg-white border border-zinc-200 rounded-lg text-zinc-800 placeholder-zinc-400 focus:outline-none focus:ring-2 focus:ring-red-100 focus:border-primary"
                        >{{ old('requirements', $period->requirements) }}</textarea>
                    </div>
                </div>
            </x-card>

            <div class="flex items-center justify-end gap-3">
                <x-button href="{{ route('admissions.periods.show', $period) }}" variant="secondary" size="md">
                    Batal
                </x-button>
                <x-button type="submit" variant="primary" size="md">
                    Simpan Perubahan &rarr;
                </x-button>
            </div>
        </form>
    </div>
</x-layouts.app>
