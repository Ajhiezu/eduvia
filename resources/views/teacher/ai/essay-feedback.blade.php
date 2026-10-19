<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <div class="flex items-center gap-2 mb-1">
                    <a href="{{ route('teacher.ai.index') }}" class="text-xs text-zinc-500 hover:text-zinc-900 inline-flex items-center gap-1 font-medium transition-colors">
                        &larr; Asisten AI Pendidik
                    </a>
                    <span class="text-zinc-300">&bull;</span>
                    <span class="text-[10px] font-bold tracking-widest uppercase text-emerald-600 font-sans">ANALISIS & UMPAN BALIK ESAI</span>
                </div>
                <h1 class="text-2xl sm:text-3xl font-serif font-bold text-zinc-900 tracking-tight">
                    Asisten Koreksi & Umpan Balik Esai
                </h1>
                <p class="text-xs sm:text-sm text-zinc-500 mt-1">
                    Analisis narasi karya tulis siswa untuk menghasilkan saran formatif, poin apresiasi, dan rekomendasi revisi bermakna.
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
                    <h2 class="text-base font-bold text-zinc-900 mb-1">Teks Esai & Perintah Tugas</h2>
                    <p class="text-xs text-zinc-500 mb-6">Masukkan judul tugas dan teks esai siswa. Sistem secara otomatis menyaring data sensitif (NIK, NISN, email, no HP) demi kepatuhan UU PDP.</p>

                    <form action="{{ route('teacher.ai.essay_feedback.store') }}" method="POST" x-data="{ submitting: false }" @submit="submitting = true" class="space-y-5">
                        @csrf

                        <!-- Judul Tugas -->
                        <div>
                            <label for="assignment_title" class="block text-xs font-semibold text-zinc-700 mb-1.5">
                                Judul / Topik Tugas Esai <span class="text-rose-500">*</span>
                            </label>
                            <input type="text" name="assignment_title" id="assignment_title" value="{{ old('assignment_title') }}"
                                placeholder="cth: Esai Refleksi Dampak Globalisasi terhadap Budaya Lokal"
                                class="w-full text-xs rounded-lg border-zinc-200 focus:border-brand-500 focus:ring-brand-500" required>
                            @error('assignment_title')
                                <p class="text-[11px] text-rose-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Pseudo ID / Kode Samaran Siswa -->
                        <div>
                            <label for="student_pseudo_id" class="block text-xs font-semibold text-zinc-700 mb-1.5">
                                Kode Siswa / ID Samaran (Opsional)
                            </label>
                            <input type="text" name="student_pseudo_id" id="student_pseudo_id" value="{{ old('student_pseudo_id') }}"
                                placeholder="cth: SISWA-A01 / No. Absen 12 (Jangan gunakan Nama Lengkap asli)"
                                class="w-full text-xs rounded-lg border-zinc-200 focus:border-brand-500 focus:ring-brand-500">
                            <p class="text-[11px] text-zinc-400 mt-1">Disarankan menggunakan nomor urut presensi atau inisial untuk melindungi privasi siswa.</p>
                            @error('student_pseudo_id')
                                <p class="text-[11px] text-rose-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Teks Esai Siswa -->
                        <div>
                            <label for="essay_text" class="block text-xs font-semibold text-zinc-700 mb-1.5">
                                Teks Esai / Jawaban Siswa <span class="text-rose-500">*</span>
                            </label>
                            <textarea name="essay_text" id="essay_text" rows="8"
                                placeholder="Tempelkan (paste) teks esai siswa di sini..."
                                class="w-full text-xs rounded-lg border-zinc-200 focus:border-brand-500 focus:ring-brand-500 font-mono text-[11px] leading-relaxed" required>{{ old('essay_text') }}</textarea>
                            <p class="text-[11px] text-zinc-400 mt-1">Minimal 20 karakter, maksimal 10.000 karakter per analisis.</p>
                            @error('essay_text')
                                <p class="text-[11px] text-rose-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Perlindungan UU PDP -->
                        <div class="p-3.5 rounded-lg bg-emerald-50/70 border border-emerald-200 text-emerald-950 text-[11px] flex items-start gap-2.5">
                            <svg class="w-4 h-4 text-emerald-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                            <div class="space-y-0.5">
                                <strong>PROTEKSI DATA PRIBADI (UU PDP NO. 27/2022):</strong>
                                <p class="text-emerald-900/90 leading-relaxed">
                                    Modul ini dilengkapi dengan <em>PII Scrubber</em> berlapis. Pola NIK (16 digit), NISN (10 digit), nomor telepon (+62/08), dan alamat email pada teks esai akan secara otomatis disamarkan (redacted) menjadi <code>[REDACTED_NIK]</code>, <code>[REDACTED_NISN]</code>, dll sebelum dikirimkan ke mesin AI.
                                </p>
                            </div>
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
                                <span x-text="submitting ? 'Menganalisis Karya Tulis...' : 'Analisis & Buat Umpan Balik'">Analisis & Buat Umpan Balik</span>
                            </button>
                        </div>
                    </form>
                </div>
            </x-card>
        </div>

        <!-- Sidebar / Tips & Recent Drafts -->
        <div class="space-y-6">
            <!-- Recent Drafts List -->
            <div class="p-4 rounded-xl bg-white border border-zinc-200/80 shadow-2xs">
                <div class="flex items-center justify-between mb-3">
                    <h3 class="text-xs font-bold text-zinc-900 uppercase tracking-wider">Umpan Balik Terakhir</h3>
                    <a href="{{ route('teacher.ai.index', ['feature' => 'essay_feedback']) }}" class="text-[11px] text-brand-600 hover:underline">Semua</a>
                </div>

                @forelse($recentDrafts as $draft)
                    <a href="{{ route('teacher.ai.drafts.show', $draft) }}" class="block p-2.5 rounded-lg hover:bg-zinc-50 border border-transparent hover:border-zinc-200 transition-colors mb-2">
                        <div class="flex items-center justify-between gap-2 mb-1">
                            <span class="text-xs font-semibold text-zinc-900 truncate">{{ $draft->prompt_metadata['assignment_title'] ?? 'Umpan Balik Esai' }}</span>
                            <x-badge :variant="$draft->status->value === 'accepted' ? 'success' : ($draft->status->value === 'discarded' ? 'danger' : 'neutral')" size="xs">
                                {{ strtoupper($draft->status->value) }}
                            </x-badge>
                        </div>
                        <div class="flex items-center justify-between text-[11px] text-zinc-400">
                            <span>{{ $draft->created_at->diffForHumans() }}</span>
                            <span>Skor: {{ $draft->generated_content['suggested_score'] ?? '-' }}/100</span>
                        </div>
                    </a>
                @empty
                    <p class="text-xs text-zinc-400 text-center py-4">Belum ada analisis esai tersimpan.</p>
                @endforelse
            </div>
        </div>
    </div>
</x-app-layout>
