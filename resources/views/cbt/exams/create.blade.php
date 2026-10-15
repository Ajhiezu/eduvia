<x-layouts.app>
    <x-slot:headerTitle>
        Buat Ujian Baru CBT
    </x-slot:headerTitle>

    <div class="max-w-4xl mx-auto space-y-6">
        @if ($errors->any())
            <x-alert variant="danger" dismissible>
                <p class="font-bold">Terdapat kesalahan pada input:</p>
                <ul class="list-disc list-inside mt-1 text-xs">
                    @foreach ($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </x-alert>
        @endif

        <div class="flex items-center justify-between">
            <div>
                <a href="{{ route('exams.index') }}" class="inline-flex items-center text-xs font-semibold text-zinc-500 hover:text-zinc-800 transition-colors mb-1">
                    <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                    Kembali ke Daftar Ujian
                </a>
                <h1 class="text-xl sm:text-2xl font-extrabold text-zinc-900 tracking-tight">
                    Pengaturan Paket Ujian Baru
                </h1>
                <p class="text-xs text-zinc-500 mt-0.5">
                    Lengkapi identitas, durasi, jadwal pembukaan, dan kebijakan pengerjaan ujian.
                </p>
            </div>
        </div>

        <form action="{{ route('exams.store') }}" method="POST">
            @csrf

            <x-card padding class="space-y-6 border-zinc-200">
                <!-- Section 1: Identitas Ujian -->
                <div>
                    <h2 class="text-sm font-bold text-zinc-900 mb-3 pb-2 border-b border-zinc-100 flex items-center gap-2">
                        <span class="w-5 h-5 rounded-full bg-brand-50 text-brand-600 text-xs flex items-center justify-center font-bold">1</span>
                        Identitas & Mata Pelajaran
                    </h2>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="sm:col-span-2">
                            <label class="block text-xs font-bold text-zinc-700 mb-1">
                                Judul Ujian <span class="text-red-500">*</span>
                            </label>
                            <input
                                type="text"
                                name="title"
                                value="{{ old('title') }}"
                                required
                                placeholder="Contoh: Asesmen Sumatif Akhir Semester Genap - Matematika"
                                class="w-full text-xs rounded-lg border-zinc-200 focus:border-brand-500 focus:ring-brand-500 font-medium"
                            />
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-zinc-700 mb-1">
                                Mata Pelajaran <span class="text-red-500">*</span>
                            </label>
                            <select name="subject_id" required class="w-full text-xs rounded-lg border-zinc-200 focus:border-brand-500 focus:ring-brand-500">
                                <option value="">Pilih Mata Pelajaran</option>
                                @foreach ($subjects as $subj)
                                    <option value="{{ $subj->id }}" {{ old('subject_id') == $subj->id ? 'selected' : '' }}>
                                        {{ $subj->name }} ({{ $subj->code }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-zinc-700 mb-1">
                                Status Awal
                            </label>
                            <select name="status" class="w-full text-xs rounded-lg border-zinc-200 focus:border-brand-500 focus:ring-brand-500">
                                @foreach ($statuses as $val => $lbl)
                                    <option value="{{ $val }}" {{ old('status', \App\Models\Exam::STATUS_DRAFT) == $val ? 'selected' : '' }}>
                                        {{ $lbl }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="sm:col-span-2">
                            <label class="block text-xs font-bold text-zinc-700 mb-1">
                                Petunjuk & Instruksi Pengerjaan
                            </label>
                            <textarea
                                name="description"
                                rows="3"
                                placeholder="Tuliskan petunjuk pengerjaan ujian yang harus dibaca oleh siswa..."
                                class="w-full text-xs rounded-lg border-zinc-200 focus:border-brand-500 focus:ring-brand-500"
                            >{{ old('description') }}</textarea>
                        </div>
                    </div>
                </div>

                <!-- Section 2: Sasaran Kelas Peserta -->
                <div>
                    <h2 class="text-sm font-bold text-zinc-900 mb-3 pb-2 border-b border-zinc-100 flex items-center gap-2">
                        <span class="w-5 h-5 rounded-full bg-brand-50 text-brand-600 text-xs flex items-center justify-center font-bold">2</span>
                        Kelas Peserta Ujian <span class="text-red-500">*</span>
                    </h2>

                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-2.5 max-h-48 overflow-y-auto p-3 bg-zinc-50 rounded-lg border border-zinc-200">
                        @foreach ($classrooms as $cls)
                            <label class="inline-flex items-center gap-2 text-xs text-zinc-800 cursor-pointer p-1.5 rounded hover:bg-white transition-colors">
                                <input
                                    type="checkbox"
                                    name="classroom_ids[]"
                                    value="{{ $cls->id }}"
                                    {{ in_array($cls->id, (array) old('classroom_ids', [])) ? 'checked' : '' }}
                                    class="rounded text-brand-600 focus:ring-brand-500"
                                />
                                <span class="font-medium truncate">{{ $cls->name }}</span>
                            </label>
                        @endforeach
                    </div>
                    <p class="text-[11px] text-zinc-400 mt-1">Hanya siswa yang terdaftar aktif pada kelas terpilih yang dapat mengakses ujian ini.</p>
                </div>

                <!-- Section 3: Waktu, Durasi & Percobaan -->
                <div>
                    <h2 class="text-sm font-bold text-zinc-900 mb-3 pb-2 border-b border-zinc-100 flex items-center gap-2">
                        <span class="w-5 h-5 rounded-full bg-brand-50 text-brand-600 text-xs flex items-center justify-center font-bold">3</span>
                        Jadwal Waktu & Ketentuan Percobaan
                    </h2>

                    <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-zinc-700 mb-1">
                                Waktu Mulai Dibuka <span class="text-red-500">*</span>
                            </label>
                            <input
                                type="datetime-local"
                                name="start_time"
                                value="{{ old('start_time', now()->format('Y-m-d\TH:i')) }}"
                                required
                                class="w-full text-xs rounded-lg border-zinc-200 focus:border-brand-500 focus:ring-brand-500"
                            />
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-zinc-700 mb-1">
                                Waktu Berakhir Ditutup <span class="text-red-500">*</span>
                            </label>
                            <input
                                type="datetime-local"
                                name="end_time"
                                value="{{ old('end_time', now()->addDays(2)->format('Y-m-d\TH:i')) }}"
                                required
                                class="w-full text-xs rounded-lg border-zinc-200 focus:border-brand-500 focus:ring-brand-500"
                            />
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-zinc-700 mb-1">
                                Durasi Pengerjaan (Menit) <span class="text-red-500">*</span>
                            </label>
                            <input
                                type="number"
                                name="duration_minutes"
                                min="1"
                                max="600"
                                value="{{ old('duration_minutes', 60) }}"
                                required
                                class="w-full text-xs rounded-lg border-zinc-200 focus:border-brand-500 focus:ring-brand-500"
                            />
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-zinc-700 mb-1">
                                Batas Percobaan <span class="text-red-500">*</span>
                            </label>
                            <input
                                type="number"
                                name="max_attempts"
                                min="1"
                                max="10"
                                value="{{ old('max_attempts', 1) }}"
                                required
                                class="w-full text-xs rounded-lg border-zinc-200 focus:border-brand-500 focus:ring-brand-500"
                            />
                        </div>
                    </div>
                </div>

                <!-- Section 4: Kebijakan Nilai, Acak & Pembahasan -->
                <div>
                    <h2 class="text-sm font-bold text-zinc-900 mb-3 pb-2 border-b border-zinc-100 flex items-center gap-2">
                        <span class="w-5 h-5 rounded-full bg-brand-50 text-brand-600 text-xs flex items-center justify-center font-bold">4</span>
                        Integritas & Kebijakan Ulasan Nilai
                    </h2>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-zinc-700 mb-1">
                                Nilai Kelulusan Minimal (KKM) <span class="text-red-500">*</span>
                            </label>
                            <input
                                type="number"
                                name="passing_grade"
                                step="0.5"
                                min="0"
                                max="100"
                                value="{{ old('passing_grade', 75) }}"
                                required
                                class="w-full text-xs rounded-lg border-zinc-200 focus:border-brand-500 focus:ring-brand-500"
                            />
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-zinc-700 mb-1">
                                Kebijakan Tampil Pembahasan
                            </label>
                            <select name="explanation_policy" class="w-full text-xs rounded-lg border-zinc-200 focus:border-brand-500 focus:ring-brand-500">
                                @foreach ($explanationPolicies as $pol => $polLabel)
                                    <option value="{{ $pol }}" {{ old('explanation_policy', \App\Models\Exam::EXPLANATION_POLICY_AFTER_EXAM_END) == $pol ? 'selected' : '' }}>
                                        {{ $polLabel }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="sm:col-span-2 space-y-2.5 pt-2">
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input
                                    type="checkbox"
                                    name="shuffle_questions"
                                    value="1"
                                    {{ old('shuffle_questions', '1') ? 'checked' : '' }}
                                    class="rounded text-brand-600 focus:ring-brand-500"
                                />
                                <span class="text-xs text-zinc-800 font-medium">Acak Urutan Butir Soal untuk Tiap Siswa</span>
                            </label>

                            <label class="flex items-center gap-2 cursor-pointer">
                                <input
                                    type="checkbox"
                                    name="shuffle_options"
                                    value="1"
                                    {{ old('shuffle_options', '1') ? 'checked' : '' }}
                                    class="rounded text-brand-600 focus:ring-brand-500"
                                />
                                <span class="text-xs text-zinc-800 font-medium">Acak Urutan Opsi Pilihan Ganda</span>
                            </label>

                            <label class="flex items-center gap-2 cursor-pointer">
                                <input
                                    type="checkbox"
                                    name="show_result_immediately"
                                    value="1"
                                    {{ old('show_result_immediately', '1') ? 'checked' : '' }}
                                    class="rounded text-brand-600 focus:ring-brand-500"
                                />
                                <span class="text-xs text-zinc-800 font-medium">Tampilkan Skor Akhir Segera Setelah Siswa Mengumpulkan</span>
                            </label>
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 pt-4 border-t border-zinc-100">
                    <x-button href="{{ route('exams.index') }}" variant="ghost" size="sm">
                        Batal
                    </x-button>
                    <x-button type="submit" variant="primary" size="sm">
                        Simpan & Lanjutkan ke Penyusunan Soal &rarr;
                    </x-button>
                </div>
            </x-card>
        </form>
    </div>
</x-layouts.app>
