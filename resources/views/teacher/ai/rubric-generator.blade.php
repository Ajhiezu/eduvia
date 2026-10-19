<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <div class="flex items-center gap-2 mb-1">
                    <a href="{{ route('teacher.ai.index') }}" class="text-xs text-zinc-500 hover:text-zinc-900 inline-flex items-center gap-1 font-medium transition-colors">
                        &larr; Asisten AI Pendidik
                    </a>
                    <span class="text-zinc-300">&bull;</span>
                    <span class="text-[10px] font-bold tracking-widest uppercase text-violet-600 font-sans">GENERATOR RUBRIK PENILAIAN</span>
                </div>
                <h1 class="text-2xl sm:text-3xl font-serif font-bold text-zinc-900 tracking-tight">
                    Penyusun Rubrik Evaluasi & Asesmen
                </h1>
                <p class="text-xs sm:text-sm text-zinc-500 mt-1">
                    Rancang kisi-kisi penilaian autentik berbentuk matriks skala berkriteria terukur (Mahir, Cakap, Layak, Berkembang).
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
                    <h2 class="text-base font-bold text-zinc-900 mb-1">Kriteria Tugas & Rubrik</h2>
                    <p class="text-xs text-zinc-500 mb-6">Tuliskan instrumen penugasan dan kriteria capaian. AI akan menyusun kriteria penilaian terperinci beserta bobot skor.</p>

                    <form action="{{ route('teacher.ai.rubrics.store') }}" method="POST" x-data="{ submitting: false }" @submit="submitting = true" class="space-y-5">
                        @csrf

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

                        <!-- Judul Tugas / Proyek -->
                        <div>
                            <label for="task_title" class="block text-xs font-semibold text-zinc-700 mb-1.5">
                                Judul Tugas / Proyek Penilaian <span class="text-rose-500">*</span>
                            </label>
                            <input type="text" name="task_title" id="task_title" value="{{ old('task_title') }}"
                                placeholder="cth: Proyek Pembuatan Infografis Siklus Air / Praktik Pidato Persuasif / Laporan Praktikum Fisika"
                                class="w-full text-xs rounded-lg border-zinc-200 focus:border-brand-500 focus:ring-brand-500" required>
                            @error('task_title')
                                <p class="text-[11px] text-rose-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Tujuan & Indikator Ketercapaian -->
                        <div>
                            <label for="learning_goals" class="block text-xs font-semibold text-zinc-700 mb-1.5">
                                Tujuan & Indikator Ketercapaian <span class="text-rose-500">*</span>
                            </label>
                            <textarea name="learning_goals" id="learning_goals" rows="4"
                                placeholder="Tuliskan aspek yang ingin diukur, misalnya keaslian ide, penguasaan materi, struktur penyampaian, penggunaan tata bahasa, dan estetika visual."
                                class="w-full text-xs rounded-lg border-zinc-200 focus:border-brand-500 focus:ring-brand-500" required>{{ old('learning_goals') }}</textarea>
                            @error('learning_goals')
                                <p class="text-[11px] text-rose-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Skala Penilaian -->
                        <div>
                            <label for="scale" class="block text-xs font-semibold text-zinc-700 mb-1.5">
                                Format Skala Kinerja
                            </label>
                            <select name="scale" id="scale" class="w-full text-xs rounded-lg border-zinc-200 focus:border-brand-500 focus:ring-brand-500 bg-white">
                                <option value="4_tingkat_merdeka" {{ old('scale') == '4_tingkat_merdeka' ? 'selected' : '' }}>4 Tingkat: Perlu Bimbingan (1), Cukup (2), Baik (3), Sangat Baik (4)</option>
                                <option value="5_tingkat_angka" {{ old('scale') == '5_tingkat_angka' ? 'selected' : '' }}>5 Tingkat Skala Nilai (1 - 5 / Sangat Kurang s.d. Sempurna)</option>
                                <option value="3_tingkat_ringkas" {{ old('scale') == '3_tingkat_ringkas' ? 'selected' : '' }}>3 Tingkat Ringkas (Belum Berkembang, Berkembang, Cakap)</option>
                            </select>
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
                                <span x-text="submitting ? 'Menyusun Rubrik Penilaian...' : 'Susun Rubrik Penilaian'">Susun Rubrik Penilaian</span>
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
                    <h3 class="text-xs font-bold text-zinc-900 uppercase tracking-wider">Draf Rubrik Terakhir</h3>
                    <a href="{{ route('teacher.ai.index', ['feature' => 'grading_rubric']) }}" class="text-[11px] text-brand-600 hover:underline">Semua</a>
                </div>

                @forelse($recentDrafts as $draft)
                    <a href="{{ route('teacher.ai.drafts.show', $draft) }}" class="block p-2.5 rounded-lg hover:bg-zinc-50 border border-transparent hover:border-zinc-200 transition-colors mb-2">
                        <div class="flex items-center justify-between gap-2 mb-1">
                            <span class="text-xs font-semibold text-zinc-900 truncate">{{ $draft->prompt_metadata['task_title'] ?? 'Rubrik Penilaian' }}</span>
                            <x-badge :variant="$draft->status->value === 'accepted' ? 'success' : ($draft->status->value === 'discarded' ? 'danger' : 'neutral')" size="xs">
                                {{ strtoupper($draft->status->value) }}
                            </x-badge>
                        </div>
                        <div class="flex items-center justify-between text-[11px] text-zinc-400">
                            <span>{{ $draft->created_at->diffForHumans() }}</span>
                            <span>{{ count($draft->generated_content['criteria'] ?? []) }} kriteria</span>
                        </div>
                    </a>
                @empty
                    <p class="text-xs text-zinc-400 text-center py-4">Belum ada rubrik tersimpan.</p>
                @endforelse
            </div>
        </div>
    </div>
</x-app-layout>
