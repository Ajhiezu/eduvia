<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <div class="flex items-center gap-2 mb-1">
                    <a href="{{ route('teacher.ai.index') }}" class="text-xs text-zinc-500 hover:text-zinc-900 inline-flex items-center gap-1 font-medium transition-colors">
                        &larr; Asisten AI Pendidik
                    </a>
                    <span class="text-zinc-300">&bull;</span>
                    <span class="text-[10px] font-bold tracking-widest uppercase text-amber-600 font-sans">GENERATOR SOAL CBT</span>
                </div>
                <h1 class="text-2xl sm:text-3xl font-serif font-bold text-zinc-900 tracking-tight">
                    Penyusun Paket Soal Ujian & CBT
                </h1>
                <p class="text-xs sm:text-sm text-zinc-500 mt-1">
                    Hasilkan butir soal pilihan ganda, benar/salah, atau esai terstruktur lengkap dengan kunci dan pembahasan yang siap diimpor ke Bank Soal CBT.
                </p>
            </div>

            <div class="flex items-center gap-2">
                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-medium {{ $quota->is_ai_enabled ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-rose-50 text-rose-700 border border-rose-200' }}">
                    <span class="w-1.5 h-1.5 rounded-full {{ $quota->is_ai_enabled ? 'bg-emerald-500' : 'bg-rose-500' }}"></span>
                    {{ $quota->is_ai_enabled ? 'Kuota Tersedia' : 'AI Non-Aktif' }}
                </span>
            </div>
        </div>
    </x-slot>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Form Column (2 Cols) -->
        <div class="lg:col-span-2 space-y-6">
            <x-card>
                <div class="p-6">
                    <h2 class="text-base font-bold text-zinc-900 mb-1">Konfigurasi Butir Soal</h2>
                    <p class="text-xs text-zinc-500 mb-6">Tentukan mata pelajaran, tingkat kesulitan taksonomi Bloom, dan jenis format butir soal yang dibutuhkan.</p>

                    <form action="{{ route('teacher.ai.questions.store') }}" method="POST" x-data="{ submitting: false }" @submit="submitting = true" class="space-y-5">
                        @csrf

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <!-- Mata Pelajaran -->
                            <div>
                                <label for="subject_id" class="block text-xs font-semibold text-zinc-700 mb-1.5">
                                    Mata Pelajaran
                                </label>
                                <select name="subject_id" id="subject_id" class="w-full text-xs rounded-lg border-zinc-200 focus:border-brand-500 focus:ring-brand-500 bg-white">
                                    <option value="">Pilih Mata Pelajaran...</option>
                                    @foreach($subjects as $subj)
                                        <option value="{{ $subj->id }}" {{ old('subject_id') == $subj->id ? 'selected' : '' }}>
                                            {{ $subj->name }} ({{ $subj->code }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('subject_id')
                                    <p class="text-[11px] text-rose-600 mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Jenjang / Tingkat Kelas -->
                            <div>
                                <label for="grade_level" class="block text-xs font-semibold text-zinc-700 mb-1.5">
                                    Jenjang / Tingkat Kelas <span class="text-rose-500">*</span>
                                </label>
                                <input type="text" name="grade_level" id="grade_level" list="gradeLevelsList"
                                    value="{{ old('grade_level') }}"
                                    placeholder="cth: Kelas XI SMA / Fase F"
                                    class="w-full text-xs rounded-lg border-zinc-200 focus:border-brand-500 focus:ring-brand-500" required>
                                <datalist id="gradeLevelsList">
                                    @foreach($gradeLevels as $gl)
                                        <option value="{{ $gl->name }}">
                                    @endforeach
                                </datalist>
                                @error('grade_level')
                                    <p class="text-[11px] text-rose-600 mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <!-- Topik / Materi Ujian -->
                        <div>
                            <label for="topic" class="block text-xs font-semibold text-zinc-700 mb-1.5">
                                Topik / Pokok Bahasan Ujian <span class="text-rose-500">*</span>
                            </label>
                            <input type="text" name="topic" id="topic" value="{{ old('topic') }}"
                                placeholder="cth: Hukum Termodinamika, Reaksi Redoks, Teks Negosiasi, Sistem Koloid"
                                class="w-full text-xs rounded-lg border-zinc-200 focus:border-brand-500 focus:ring-brand-500" required>
                            @error('topic')
                                <p class="text-[11px] text-rose-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <!-- Jumlah Soal -->
                            <div>
                                <label for="count" class="block text-xs font-semibold text-zinc-700 mb-1.5">
                                    Jumlah Butir Soal (1 - 20) <span class="text-rose-500">*</span>
                                </label>
                                <input type="number" name="count" id="count" min="1" max="20"
                                    value="{{ old('count', 5) }}"
                                    class="w-full text-xs rounded-lg border-zinc-200 focus:border-brand-500 focus:ring-brand-500" required>
                                <p class="text-[11px] text-zinc-400 mt-1">Disarankan 5 - 10 butir per permintaan agar akurasi tetap optimal.</p>
                                @error('count')
                                    <p class="text-[11px] text-rose-600 mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Distribusi Kesulitan -->
                            <div>
                                <label for="difficulty_distribution" class="block text-xs font-semibold text-zinc-700 mb-1.5">
                                    Distribusi Tingkat Kesulitan (Bloom)
                                </label>
                                <select name="difficulty_distribution" id="difficulty_distribution" class="w-full text-xs rounded-lg border-zinc-200 focus:border-brand-500 focus:ring-brand-500 bg-white">
                                    <option value="Seimbang (30% Mudah, 50% Sedang, 20% HOTS)" {{ old('difficulty_distribution') == 'Seimbang (30% Mudah, 50% Sedang, 20% HOTS)' ? 'selected' : '' }}>Seimbang (30% Mudah, 50% Sedang, 20% HOTS)</option>
                                    <option value="Dominan HOTS / Analisis (C4 - C6)" {{ old('difficulty_distribution') == 'Dominan HOTS / Analisis (C4 - C6)' ? 'selected' : '' }}>Dominan HOTS / Analisis (C4 - C6)</option>
                                    <option value="Pemahaman Dasar & Konseptual (C1 - C3)" {{ old('difficulty_distribution') == 'Pemahaman Dasar & Konseptual (C1 - C3)' ? 'selected' : '' }}>Pemahaman Dasar & Konseptual (C1 - C3)</option>
                                </select>
                            </div>
                        </div>

                        <!-- Format Tipe Soal -->
                        <div>
                            <span class="block text-xs font-semibold text-zinc-700 mb-2">Tipe Soal yang Disertakan</span>
                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                                <label class="flex items-center gap-2 p-3 rounded-lg border border-zinc-200 hover:bg-zinc-50 cursor-pointer text-xs">
                                    <input type="checkbox" name="question_types[]" value="multiple_choice_single" checked class="rounded border-zinc-300 text-brand-600 focus:ring-brand-500">
                                    <span class="font-medium text-zinc-800">Pilihan Ganda</span>
                                </label>
                                <label class="flex items-center gap-2 p-3 rounded-lg border border-zinc-200 hover:bg-zinc-50 cursor-pointer text-xs">
                                    <input type="checkbox" name="question_types[]" value="true_false" class="rounded border-zinc-300 text-brand-600 focus:ring-brand-500">
                                    <span class="font-medium text-zinc-800">Benar / Salah</span>
                                </label>
                                <label class="flex items-center gap-2 p-3 rounded-lg border border-zinc-200 hover:bg-zinc-50 cursor-pointer text-xs">
                                    <input type="checkbox" name="question_types[]" value="essay" class="rounded border-zinc-300 text-brand-600 focus:ring-brand-500">
                                    <span class="font-medium text-zinc-800">Uraian / Esai</span>
                                </label>
                            </div>
                        </div>

                        <!-- Etika & Perlindungan -->
                        <div class="p-3.5 rounded-lg bg-amber-50/70 border border-amber-200 text-amber-900 text-[11px] flex items-center gap-2.5">
                            <svg class="w-4 h-4 text-amber-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <span>Soal yang disusun AI akan dilengkapi kunci jawaban dan rasional pembahasan. Anda dapat menyunting dan mengimpor butir soal terpilih ke Bank Soal CBT setelah peninjauan.</span>
                        </div>

                        <!-- Action Submit -->
                        <div class="flex items-center justify-between pt-4 border-t border-zinc-100">
                            <a href="{{ route('teacher.ai.index') }}" class="px-4 py-2 text-xs font-medium text-zinc-600 hover:text-zinc-900 transition-colors">
                                Batal
                            </a>
                            <button type="submit" :disabled="submitting"
                                class="inline-flex items-center gap-2 px-5 py-2.5 rounded-lg bg-[#E11D2E] hover:bg-[#c81926] text-white text-xs font-semibold shadow-xs disabled:opacity-60 transition-colors">
                                <svg x-show="submitting" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                <span x-text="submitting ? 'Menyusun Soal & Pembahasan...' : 'Susun Paket Soal CBT'">Susun Paket Soal CBT</span>
                            </button>
                        </div>
                    </form>
                </div>
            </x-card>
        </div>

        <!-- Sidebar / Recent Drafts & CBT Integration Notice -->
        <div class="space-y-6">
            <!-- CBT Bank Soal Link -->
            <div class="p-4 rounded-xl bg-gradient-to-br from-amber-50 to-orange-50 border border-amber-200/80 text-amber-950 shadow-2xs">
                <div class="flex items-center gap-2 mb-2">
                    <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                    <h3 class="text-xs font-bold uppercase tracking-wider">Integrasi Bank Soal CBT</h3>
                </div>
                <p class="text-xs text-amber-900/90 leading-relaxed mb-3">
                    Setelah draf soal selesai ditinjau, Anda dapat langsung menekan tombol <strong>Impor ke Bank Soal CBT</strong> untuk mengonversinya menjadi butir soal ujian resmi Eduvia.
                </p>
                <a href="{{ route('exam-questions.index') }}" class="text-xs font-semibold text-amber-800 hover:text-amber-950 inline-flex items-center gap-1 underline">
                    Buka Bank Soal CBT &rarr;
                </a>
            </div>

            <!-- Recent Drafts List -->
            <div class="p-4 rounded-xl bg-white border border-zinc-200/80 shadow-2xs">
                <div class="flex items-center justify-between mb-3">
                    <h3 class="text-xs font-bold text-zinc-900 uppercase tracking-wider">Draf Soal Terakhir</h3>
                    <a href="{{ route('teacher.ai.index', ['feature' => 'question_set']) }}" class="text-[11px] text-brand-600 hover:underline">Semua</a>
                </div>

                @forelse($recentDrafts as $draft)
                    <a href="{{ route('teacher.ai.drafts.show', $draft) }}" class="block p-2.5 rounded-lg hover:bg-zinc-50 border border-transparent hover:border-zinc-200 transition-colors mb-2">
                        <div class="flex items-center justify-between gap-2 mb-1">
                            <span class="text-xs font-semibold text-zinc-900 truncate">{{ $draft->prompt_metadata['topic'] ?? 'Paket Soal' }}</span>
                            <x-badge :variant="$draft->status->value === 'accepted' ? 'success' : ($draft->status->value === 'discarded' ? 'danger' : 'neutral')" size="xs">
                                {{ strtoupper($draft->status->value) }}
                            </x-badge>
                        </div>
                        <div class="flex items-center justify-between text-[11px] text-zinc-400">
                            <span>{{ $draft->created_at->diffForHumans() }}</span>
                            <span>{{ count($draft->generated_content['questions'] ?? []) }} butir</span>
                        </div>
                    </a>
                @empty
                    <p class="text-xs text-zinc-400 text-center py-4">Belum ada paket soal tersimpan.</p>
                @endforelse
            </div>
        </div>
    </div>
</x-app-layout>
