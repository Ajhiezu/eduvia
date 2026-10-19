<x-layouts.app>
    <x-slot:headerTitle>
        Edit Mata Pelajaran
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
                    Edit Mata Pelajaran: {{ $subject->name }}
                </h1>
                <p class="text-xs text-zinc-500 mt-0.5">
                    Perbarui informasi mata pelajaran, alokasi JP, dan kurikulum.
                </p>
            </div>
        </div>

        @if ($errors->any())
            <x-alert variant="danger" dismissible>
                {{ $errors->first() }}
            </x-alert>
        @endif

        <form method="POST" action="{{ route('subjects.update', $subject) }}">
            @csrf
            @method('PUT')

            <x-card padding class="space-y-6">
                <div class="border-b border-zinc-200 pb-4">
                    <h2 class="text-sm font-bold text-zinc-900 uppercase tracking-wide">
                        Informasi Utama Mata Pelajaran
                    </h2>
                    <p class="text-xs text-zinc-500 mt-0.5">
                        Identitas dasar untuk kodefikasi dan penjadwalan pelajaran.
                    </p>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                    <!-- Code -->
                    <div>
                        <label for="code" class="block text-sm font-medium text-zinc-700 mb-1.5">
                            Kode Mata Pelajaran <span class="text-zinc-400 font-normal text-xs">(Opsional)</span>
                        </label>
                        <input
                            type="text"
                            id="code"
                            name="code"
                            value="{{ old('code', $subject->code) }}"
                            placeholder="Contoh: MAT-10, BIND-7"
                            class="w-full rounded-lg border border-zinc-200 text-zinc-900 text-base py-2.5 px-3.5 focus:border-[#E11D2E] focus:ring-2 focus:ring-[#E11D2E]/20 placeholder-zinc-400 font-mono"
                        >
                    </div>

                    <!-- Name -->
                    <div>
                        <label for="name" class="block text-sm font-medium text-zinc-700 mb-1.5">
                            Nama Mata Pelajaran <span class="text-[#E11D2E]">*</span>
                        </label>
                        <input
                            type="text"
                            id="name"
                            name="name"
                            value="{{ old('name', $subject->name) }}"
                            required
                            placeholder="Contoh: Matematika, Bahasa Indonesia"
                            class="w-full rounded-lg border border-zinc-200 text-zinc-900 text-base py-2.5 px-3.5 focus:border-[#E11D2E] focus:ring-2 focus:ring-[#E11D2E]/20 placeholder-zinc-400"
                        >
                    </div>

                    <!-- Group -->
                    <div>
                        <label for="group" class="block text-sm font-medium text-zinc-700 mb-1.5">
                            Kelompok Mata Pelajaran <span class="text-[#E11D2E]">*</span>
                        </label>
                        <select
                            id="group"
                            name="group"
                            required
                            class="w-full rounded-lg border border-zinc-200 text-zinc-900 text-base py-2.5 pl-3.5 pr-11 focus:border-[#E11D2E] focus:ring-2 focus:ring-[#E11D2E]/20"
                        >
                            <option value="Umum" @selected(old('group', $subject->group) === 'Umum')>Umum / Inti Nasional</option>
                            <option value="Peminatan" @selected(old('group', $subject->group) === 'Peminatan')>Peminatan (MIPA / IPS / Bahasa - SMA)</option>
                            <option value="Kejuruan" @selected(old('group', $subject->group) === 'Kejuruan')>Kejuruan / Keahlian (SMK)</option>
                            <option value="Muatan Lokal" @selected(old('group', $subject->group) === 'Muatan Lokal')>Muatan Lokal</option>
                        </select>
                    </div>

                    <!-- Educational Level -->
                    <div>
                        <label for="educational_level" class="block text-sm font-medium text-zinc-700 mb-1.5">
                            Jenjang Sekolah
                        </label>
                        <select
                            id="educational_level"
                            name="educational_level"
                            class="w-full rounded-lg border border-zinc-200 text-zinc-900 text-base py-2.5 pl-3.5 pr-11 focus:border-[#E11D2E] focus:ring-2 focus:ring-[#E11D2E]/20"
                        >
                            <option value="">Semua Jenjang</option>
                            <option value="sd" @selected(old('educational_level', $subject->educational_level) === 'sd')>SD / MI</option>
                            <option value="smp" @selected(old('educational_level', $subject->educational_level) === 'smp')>SMP / MTs</option>
                            <option value="sma" @selected(old('educational_level', $subject->educational_level) === 'sma')>SMA / MA</option>
                            <option value="smk" @selected(old('educational_level', $subject->educational_level) === 'smk')>SMK / MAK</option>
                        </select>
                    </div>

                    <!-- Curriculum -->
                    <div>
                        <label for="curriculum" class="block text-sm font-medium text-zinc-700 mb-1.5">
                            Kurikulum
                        </label>
                        <select
                            id="curriculum"
                            name="curriculum"
                            class="w-full rounded-lg border border-zinc-200 text-zinc-900 text-base py-2.5 pl-3.5 pr-11 focus:border-[#E11D2E] focus:ring-2 focus:ring-[#E11D2E]/20"
                        >
                            <option value="merdeka" @selected(old('curriculum', $subject->curriculum) === 'merdeka')>Kurikulum Merdeka</option>
                            <option value="k13" @selected(old('curriculum', $subject->curriculum) === 'k13')>Kurikulum 2013 (K-13)</option>
                            <option value="custom" @selected(old('curriculum', $subject->curriculum) === 'custom')>Kurikulum Khusus / Mandiri</option>
                        </select>
                    </div>

                    <!-- Credit Hours -->
                    <div>
                        <label for="credit_hours" class="block text-sm font-medium text-zinc-700 mb-1.5">
                            Beban Belajar Mingguan (JP) <span class="text-[#E11D2E]">*</span>
                        </label>
                        <input
                            type="number"
                            id="credit_hours"
                            name="credit_hours"
                            min="1"
                            max="20"
                            value="{{ old('credit_hours', $subject->credit_hours ?? 2) }}"
                            required
                            class="w-full rounded-lg border border-zinc-200 text-zinc-900 text-base py-2.5 px-3.5 focus:border-[#E11D2E] focus:ring-2 focus:ring-[#E11D2E]/20 font-mono"
                        >
                    </div>

                    <!-- Academic Program (SMA/SMK Specific) -->
                    <div class="sm:col-span-2">
                        <label for="academic_program" class="block text-sm font-medium text-zinc-700 mb-1.5">
                            Peminatan / Konsentrasi Keahlian <span class="text-zinc-400 font-normal text-xs">(Khusus SMA / SMK, misal: MIPA, IPS, TKJ, RPL)</span>
                        </label>
                        <input
                            type="text"
                            id="academic_program"
                            name="academic_program"
                            value="{{ old('academic_program', $subject->academic_program) }}"
                            placeholder="Contoh: MIPA, Teknik Komputer dan Jaringan, Otomatisasi Tata Kelola Perkantoran"
                            class="w-full rounded-lg border border-zinc-200 text-zinc-900 text-base py-2.5 px-3.5 focus:border-[#E11D2E] focus:ring-2 focus:ring-[#E11D2E]/20 placeholder-zinc-400"
                        >
                    </div>

                    <!-- Order -->
                    <div>
                        <label for="order" class="block text-sm font-medium text-zinc-700 mb-1.5">
                            Urutan Tampilan
                        </label>
                        <input
                            type="number"
                            id="order"
                            name="order"
                            min="0"
                            value="{{ old('order', $subject->order) }}"
                            class="w-full rounded-lg border border-zinc-200 text-zinc-900 text-base py-2.5 px-3.5 focus:border-[#E11D2E] focus:ring-2 focus:ring-[#E11D2E]/20 font-mono"
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
                                @checked(old('is_active', $subject->is_active))
                            >
                            <div class="w-9 h-5 bg-zinc-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-zinc-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-emerald-600"></div>
                            <span class="ml-3 text-sm font-medium text-zinc-700">Mata Pelajaran Aktif</span>
                        </label>
                    </div>

                    <!-- Description / Learning Outcomes -->
                    <div class="sm:col-span-2">
                        <label for="description" class="block text-sm font-medium text-zinc-700 mb-1.5">
                            Deskripsi / Capaian Pembelajaran <span class="text-zinc-400 font-normal text-xs">(Opsional)</span>
                        </label>
                        <textarea
                            id="description"
                            name="description"
                            rows="3"
                            placeholder="Ringkasan ruang lingkup materi atau capaian pembelajaran mata pelajaran..."
                            class="w-full rounded-lg border border-zinc-200 text-zinc-900 text-base py-2.5 px-3.5 focus:border-[#E11D2E] focus:ring-2 focus:ring-[#E11D2E]/20 placeholder-zinc-400 min-h-[96px]"
                        >{{ old('description', $subject->description) }}</textarea>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 pt-4 border-t border-zinc-200">
                    <x-button href="{{ route('subjects.index') }}" variant="outline" size="md">
                        Batal
                    </x-button>
                    <x-button type="submit" variant="primary" size="md">
                        Simpan Perubahan
                    </x-button>
                </div>
            </x-card>
        </form>
    </div>
</x-layouts.app>
