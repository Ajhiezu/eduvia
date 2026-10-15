<x-layouts.app>
    <x-slot:headerTitle>
        Tambah Mata Pelajaran
    </x-slot:headerTitle>

    <div class="max-w-4xl mx-auto space-y-6">
        <!-- Navigation & Header -->
        <div class="flex items-center justify-between">
            <div>
                <a href="{{ route('subjects.index') }}" class="inline-flex items-center text-xs font-semibold text-zinc-500 hover:text-zinc-900 transition-colors mb-2">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                    Kembali ke Daftar Mata Pelajaran
                </a>
                <h1 class="text-xl sm:text-2xl font-extrabold text-zinc-900 tracking-tight">
                    Tambah Mata Pelajaran Baru
                </h1>
                <p class="text-xs text-zinc-500 mt-0.5">
                    Definisikan mata pelajaran baru dalam kurikulum operasional sekolah Anda.
                </p>
            </div>
        </div>

        @if ($errors->any())
            <x-alert variant="danger" dismissible>
                {{ $errors->first() }}
            </x-alert>
        @endif

        <form method="POST" action="{{ route('subjects.store') }}">
            @csrf

            <x-card padding class="space-y-6">
                <div class="border-b border-zinc-200 pb-4">
                    <h2 class="text-sm font-bold text-zinc-900 uppercase tracking-wide">
                        Informasi Utama Mata Pelajaran
                    </h2>
                    <p class="text-xs text-zinc-500 mt-0.5">
                        Identitas dasar untuk kodefikasi dan penjadwalan pelajaran.
                    </p>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <!-- Code -->
                    <div>
                        <label for="code" class="block text-xs font-semibold text-zinc-700 mb-1">
                            Kode Mata Pelajaran <span class="text-zinc-400 font-normal">(Opsional)</span>
                        </label>
                        <input
                            type="text"
                            id="code"
                            name="code"
                            value="{{ old('code') }}"
                            placeholder="Contoh: MAT-10, BIND-7"
                            class="w-full rounded-lg border border-zinc-300 text-zinc-900 text-xs py-2 px-3 focus:border-brand-500 focus:ring-2 focus:ring-brand-100 placeholder-zinc-400 font-mono"
                        >
                    </div>

                    <!-- Name -->
                    <div>
                        <label for="name" class="block text-xs font-semibold text-zinc-700 mb-1">
                            Nama Mata Pelajaran <span class="text-brand-600">*</span>
                        </label>
                        <input
                            type="text"
                            id="name"
                            name="name"
                            value="{{ old('name') }}"
                            required
                            placeholder="Contoh: Matematika, Bahasa Indonesia"
                            class="w-full rounded-lg border border-zinc-300 text-zinc-900 text-xs py-2 px-3 focus:border-brand-500 focus:ring-2 focus:ring-brand-100 placeholder-zinc-400"
                        >
                    </div>

                    <!-- Group -->
                    <div>
                        <label for="group" class="block text-xs font-semibold text-zinc-700 mb-1">
                            Kelompok Mata Pelajaran <span class="text-brand-600">*</span>
                        </label>
                        <select
                            id="group"
                            name="group"
                            required
                            class="w-full rounded-lg border border-zinc-300 text-zinc-900 text-xs py-2 px-3 focus:border-brand-500 focus:ring-2 focus:ring-brand-100"
                        >
                            <option value="Umum" @selected(old('group') === 'Umum')>Umum / Inti Nasional</option>
                            <option value="Peminatan" @selected(old('group') === 'Peminatan')>Peminatan (MIPA / IPS / Bahasa - SMA)</option>
                            <option value="Kejuruan" @selected(old('group') === 'Kejuruan')>Kejuruan / Keahlian (SMK)</option>
                            <option value="Muatan Lokal" @selected(old('group') === 'Muatan Lokal')>Muatan Lokal</option>
                        </select>
                    </div>

                    <!-- Educational Level -->
                    <div>
                        <label for="educational_level" class="block text-xs font-semibold text-zinc-700 mb-1">
                            Jenjang Sekolah
                        </label>
                        <select
                            id="educational_level"
                            name="educational_level"
                            class="w-full rounded-lg border border-zinc-300 text-zinc-900 text-xs py-2 px-3 focus:border-brand-500 focus:ring-2 focus:ring-brand-100"
                        >
                            <option value="">Semua Jenjang</option>
                            <option value="sd" @selected(old('educational_level') === 'sd')>SD / MI</option>
                            <option value="smp" @selected(old('educational_level') === 'smp')>SMP / MTs</option>
                            <option value="sma" @selected(old('educational_level') === 'sma')>SMA / MA</option>
                            <option value="smk" @selected(old('educational_level') === 'smk')>SMK / MAK</option>
                        </select>
                    </div>

                    <!-- Curriculum -->
                    <div>
                        <label for="curriculum" class="block text-xs font-semibold text-zinc-700 mb-1">
                            Kurikulum
                        </label>
                        <select
                            id="curriculum"
                            name="curriculum"
                            class="w-full rounded-lg border border-zinc-300 text-zinc-900 text-xs py-2 px-3 focus:border-brand-500 focus:ring-2 focus:ring-brand-100"
                        >
                            <option value="merdeka" @selected(old('curriculum', 'merdeka') === 'merdeka')>Kurikulum Merdeka</option>
                            <option value="k13" @selected(old('curriculum') === 'k13')>Kurikulum 2013 (K-13)</option>
                            <option value="custom" @selected(old('curriculum') === 'custom')>Kurikulum Khusus / Mandiri</option>
                        </select>
                    </div>

                    <!-- Credit Hours -->
                    <div>
                        <label for="credit_hours" class="block text-xs font-semibold text-zinc-700 mb-1">
                            Beban Belajar Mingguan (JP) <span class="text-brand-600">*</span>
                        </label>
                        <input
                            type="number"
                            id="credit_hours"
                            name="credit_hours"
                            min="1"
                            max="20"
                            value="{{ old('credit_hours', 2) }}"
                            required
                            class="w-full rounded-lg border border-zinc-300 text-zinc-900 text-xs py-2 px-3 focus:border-brand-500 focus:ring-2 focus:ring-brand-100 font-mono"
                        >
                    </div>

                    <!-- Academic Program (SMA/SMK Specific) -->
                    <div class="sm:col-span-2">
                        <label for="academic_program" class="block text-xs font-semibold text-zinc-700 mb-1">
                            Peminatan / Konsentrasi Keahlian <span class="text-zinc-400 font-normal">(Khusus SMA / SMK, misal: MIPA, IPS, TKJ, RPL)</span>
                        </label>
                        <input
                            type="text"
                            id="academic_program"
                            name="academic_program"
                            value="{{ old('academic_program') }}"
                            placeholder="Contoh: MIPA, Teknik Komputer dan Jaringan, Otomatisasi Tata Kelola Perkantoran"
                            class="w-full rounded-lg border border-zinc-300 text-zinc-900 text-xs py-2 px-3 focus:border-brand-500 focus:ring-2 focus:ring-brand-100 placeholder-zinc-400"
                        >
                    </div>

                    <!-- Order -->
                    <div>
                        <label for="order" class="block text-xs font-semibold text-zinc-700 mb-1">
                            Urutan Tampilan
                        </label>
                        <input
                            type="number"
                            id="order"
                            name="order"
                            min="0"
                            value="{{ old('order', 0) }}"
                            class="w-full rounded-lg border border-zinc-300 text-zinc-900 text-xs py-2 px-3 focus:border-brand-500 focus:ring-2 focus:ring-brand-100 font-mono"
                        >
                    </div>

                    <!-- Is Active Toggle -->
                    <div class="flex items-center pt-5">
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input
                                type="checkbox"
                                name="is_active"
                                value="1"
                                class="sr-only peer"
                                @checked(old('is_active', true))
                            >
                            <div class="w-9 h-5 bg-zinc-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-zinc-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-emerald-600"></div>
                            <span class="ml-3 text-xs font-semibold text-zinc-700">Mata Pelajaran Aktif</span>
                        </label>
                    </div>

                    <!-- Description / Learning Outcomes -->
                    <div class="sm:col-span-2">
                        <label for="description" class="block text-xs font-semibold text-zinc-700 mb-1">
                            Deskripsi / Capaian Pembelajaran <span class="text-zinc-400 font-normal">(Opsional)</span>
                        </label>
                        <textarea
                            id="description"
                            name="description"
                            rows="3"
                            placeholder="Ringkasan ruang lingkup materi atau capaian pembelajaran mata pelajaran..."
                            class="w-full rounded-lg border border-zinc-300 text-zinc-900 text-xs py-2 px-3 focus:border-brand-500 focus:ring-2 focus:ring-brand-100 placeholder-zinc-400"
                        >{{ old('description') }}</textarea>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 pt-4 border-t border-zinc-200">
                    <x-button href="{{ route('subjects.index') }}" variant="outline" size="sm">
                        Batal
                    </x-button>
                    <x-button type="submit" variant="primary" size="sm">
                        Simpan Mata Pelajaran
                    </x-button>
                </div>
            </x-card>
        </form>
    </div>
</x-layouts.app>
