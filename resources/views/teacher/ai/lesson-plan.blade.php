<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <div class="flex items-center gap-2 mb-1">
                    <a href="{{ route('teacher.ai.index') }}" class="text-xs text-zinc-500 hover:text-zinc-900 inline-flex items-center gap-1 font-medium transition-colors">
                        &larr; Asisten AI Pendidik
                    </a>
                    <span class="text-zinc-300">&bull;</span>
                    <span class="text-[10px] font-bold tracking-widest uppercase text-brand-600 font-sans">GENERATOR MODUL AJAR</span>
                </div>
                <h1 class="text-2xl sm:text-3xl font-serif font-bold text-zinc-900 tracking-tight">
                    Penyusun Modul Ajar / RPP
                </h1>
                <p class="text-xs sm:text-sm text-zinc-500 mt-1">
                    Rancang rencana pembelajaran Kurikulum Merdeka lengkap dengan Profil Pelajar Pancasila, langkah kegiatan, dan rubrik asesmen.
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
                    <h2 class="text-base font-bold text-zinc-900 mb-1">Parameter Modul Ajar</h2>
                    <p class="text-xs text-zinc-500 mb-6">Isi capaian pembelajaran dan alokasi waktu. AI akan mengonstruksi draf kegiatan berbasis pembelajaran aktif.</p>

                    <form action="{{ route('teacher.ai.lesson_plan.store') }}" method="POST" x-data="{ submitting: false }" @submit="submitting = true" class="space-y-5">
                        @csrf

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <!-- Mata Pelajaran -->
                            <div>
                                <label for="subject_id" class="block text-xs font-semibold text-zinc-700 mb-1.5">
                                    Mata Pelajaran <span class="text-rose-500">*</span>
                                </label>
                                <select name="subject_id" id="subject_id" class="w-full text-xs rounded-lg border-zinc-200 focus:border-brand-500 focus:ring-brand-500 bg-white" required>
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
                                    placeholder="cth: Kelas X SMA / Fase E"
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

                        <!-- Topik Pembelajaran -->
                        <div>
                            <label for="topic" class="block text-xs font-semibold text-zinc-700 mb-1.5">
                                Materi Pokok / Topik Pembelajaran <span class="text-rose-500">*</span>
                            </label>
                            <input type="text" name="topic" id="topic" value="{{ old('topic') }}"
                                placeholder="cth: Ekosistem dan Rantai Makanan / Integral Tertentu / Eksplanasi Ilmiah"
                                class="w-full text-xs rounded-lg border-zinc-200 focus:border-brand-500 focus:ring-brand-500" required>
                            @error('topic')
                                <p class="text-[11px] text-rose-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Capaian / Tujuan Pembelajaran (CP / TP) -->
                        <div>
                            <label for="learning_objectives" class="block text-xs font-semibold text-zinc-700 mb-1.5">
                                Capaian & Tujuan Pembelajaran (CP / TP) <span class="text-rose-500">*</span>
                            </label>
                            <textarea name="learning_objectives" id="learning_objectives" rows="4"
                                placeholder="Tuliskan tujuan spesifik yang ingin dicapai siswa. Misalnya: Peserta didik mampu mengidentifikasi komponen biotik-abiotik dan merancang jaring-jaring makanan pada ekosistem mangrove."
                                class="w-full text-xs rounded-lg border-zinc-200 focus:border-brand-500 focus:ring-brand-500" required>{{ old('learning_objectives') }}</textarea>
                            <p class="text-[11px] text-zinc-400 mt-1">Sertakan target Profil Pelajar Pancasila atau keterampilan abad 21 bila diperlukan.</p>
                            @error('learning_objectives')
                                <p class="text-[11px] text-rose-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Alokasi Waktu -->
                        <div class="sm:w-1/2">
                            <label for="duration_minutes" class="block text-xs font-semibold text-zinc-700 mb-1.5">
                                Alokasi Waktu Pertemuan (Menit)
                            </label>
                            <input type="number" name="duration_minutes" id="duration_minutes" min="15" max="360" step="5"
                                value="{{ old('duration_minutes', 90) }}"
                                class="w-full text-xs rounded-lg border-zinc-200 focus:border-brand-500 focus:ring-brand-500">
                            <p class="text-[11px] text-zinc-400 mt-1">Standar: 2 x 45 menit (90 menit).</p>
                            @error('duration_minutes')
                                <p class="text-[11px] text-rose-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Etika & Perlindungan -->
                        <div class="p-3.5 rounded-lg bg-zinc-50 border border-zinc-200 text-zinc-600 text-[11px] flex items-center gap-2.5">
                            <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                            <span>Data prompt Anda dipagari dengan proteksi anti-injeksi dan tidak membagikan data identitas pribadi guru/sekolah ke penyedia publik.</span>
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
                                <span x-text="submitting ? 'Sedang Menyusun Draf...' : 'Susun Draf Modul Ajar'">Susun Draf Modul Ajar</span>
                            </button>
                        </div>
                    </form>
                </div>
            </x-card>
        </div>

        <!-- Sidebar / Tips & Recent Drafts (1 Col) -->
        <div class="space-y-6">
            <!-- Quota Snapshot -->
            <div class="p-4 rounded-xl bg-white border border-zinc-200/80 shadow-2xs">
                <h3 class="text-xs font-bold text-zinc-900 mb-2 uppercase tracking-wider">Kuota Token Sekolah</h3>
                <div class="flex items-baseline justify-between text-xs mb-1.5">
                    <span class="text-zinc-500">Terpakai bulan ini:</span>
                    <span class="font-bold text-zinc-900">{{ number_format($quota->current_month_tokens_used) }} / {{ number_format($quota->monthly_token_limit) }}</span>
                </div>
                <div class="w-full bg-zinc-100 h-1.5 rounded-full overflow-hidden mb-2">
                    <div class="bg-brand-600 h-full rounded-full" style="width: {{ min(100, $quota->usagePercentage()) }}%"></div>
                </div>
                <p class="text-[11px] text-zinc-500">Setiap penyusunan modul ajar memerlukan sekitar 1.200 - 2.500 token.</p>
            </div>

            <!-- Recent Drafts List -->
            <div class="p-4 rounded-xl bg-white border border-zinc-200/80 shadow-2xs">
                <div class="flex items-center justify-between mb-3">
                    <h3 class="text-xs font-bold text-zinc-900 uppercase tracking-wider">Draf Modul Terakhir</h3>
                    <a href="{{ route('teacher.ai.index', ['feature' => 'lesson_plan']) }}" class="text-[11px] text-brand-600 hover:underline">Semua</a>
                </div>

                @forelse($recentDrafts as $draft)
                    <a href="{{ route('teacher.ai.drafts.show', $draft) }}" class="block p-2.5 rounded-lg hover:bg-zinc-50 border border-transparent hover:border-zinc-200 transition-colors mb-2">
                        <div class="flex items-center justify-between gap-2 mb-1">
                            <span class="text-xs font-semibold text-zinc-900 truncate">{{ $draft->prompt_metadata['topic'] ?? 'Modul Ajar' }}</span>
                            <x-badge :variant="$draft->status->value === 'accepted' ? 'success' : ($draft->status->value === 'discarded' ? 'danger' : 'neutral')" size="xs">
                                {{ strtoupper($draft->status->value) }}
                            </x-badge>
                        </div>
                        <div class="flex items-center justify-between text-[11px] text-zinc-400">
                            <span>{{ $draft->created_at->diffForHumans() }}</span>
                            <span>{{ number_format($draft->total_tokens) }} tokens</span>
                        </div>
                    </a>
                @empty
                    <p class="text-xs text-zinc-400 text-center py-4">Belum ada draf modul ajar tersimpan.</p>
                @endforelse
            </div>

            <!-- Pedagogy Guidance Card -->
            <div class="p-4 rounded-xl bg-zinc-900 text-zinc-100 shadow-2xs">
                <h3 class="text-xs font-bold text-rose-400 mb-2 uppercase tracking-wider">Struktur Modul Ajar</h3>
                <ul class="text-xs space-y-1.5 text-zinc-300">
                    <li class="flex items-start gap-1.5">
                        <span class="text-rose-400">&bull;</span>
                        <span><strong>Identitas:</strong> Fase, alokasi waktu, target peserta didik.</span>
                    </li>
                    <li class="flex items-start gap-1.5">
                        <span class="text-rose-400">&bull;</span>
                        <span><strong>Profil Pelajar Pancasila:</strong> Dimensi karakter terintegrasi.</span>
                    </li>
                    <li class="flex items-start gap-1.5">
                        <span class="text-rose-400">&bull;</span>
                        <span><strong>Langkah Pembelajaran:</strong> Pendahuluan, Inti bermakna, Penutup reflektif.</span>
                    </li>
                    <li class="flex items-start gap-1.5">
                        <span class="text-rose-400">&bull;</span>
                        <span><strong>Asesmen:</strong> Diagnostik, formatif, dan instrumen sumatif.</span>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</x-app-layout>
