<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <div class="flex items-center gap-2 mb-1">
                    <a href="{{ route('teacher.ai.index') }}" class="text-xs text-zinc-500 hover:text-zinc-900 inline-flex items-center gap-1 font-medium transition-colors">
                        &larr; Asisten AI Pendidik
                    </a>
                    <span class="text-zinc-300">&bull;</span>
                    <span class="text-[10px] font-bold tracking-widest uppercase text-brand-600 font-sans">
                        {{ strtoupper(str_replace('_', ' ', $draft->feature_type->value)) }}
                    </span>
                    <span class="text-zinc-300">&bull;</span>
                    <x-badge :variant="$draft->status->value === 'accepted' ? 'success' : ($draft->status->value === 'discarded' ? 'danger' : 'neutral')" size="xs">
                        {{ strtoupper($draft->status->value) }}
                    </x-badge>
                </div>
                <h1 class="text-2xl sm:text-3xl font-serif font-bold text-zinc-900 tracking-tight">
                    {{ $draft->prompt_metadata['topic'] ?? $draft->prompt_metadata['task_title'] ?? $draft->prompt_metadata['assignment_title'] ?? 'Draf Materi AI' }}
                </h1>
                <p class="text-xs sm:text-sm text-zinc-500 mt-1">
                    Dihasilkan pada {{ $draft->created_at->format('d M Y, H:i') }} WIB &bull; Versi {{ $draft->version }}
                </p>
            </div>

            <!-- Header Action Controls -->
            <div class="flex flex-wrap items-center gap-2">
                @if($draft->status->value === 'draft')
                    <!-- Discard Button -->
                    <form action="{{ route('teacher.ai.drafts.update', $draft) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin membuang draf ini? Draf yang dibuang tidak dapat dipulihkan.');">
                        @csrf
                        <input type="hidden" name="action" value="discard">
                        <button type="submit" class="px-3 py-2 text-xs font-semibold rounded-lg bg-zinc-100 hover:bg-rose-50 text-zinc-700 hover:text-rose-700 border border-zinc-200 transition-colors">
                            Buang Draf
                        </button>
                    </form>

                    <!-- Accept Button -->
                    <form action="{{ route('teacher.ai.drafts.update', $draft) }}" method="POST">
                        @csrf
                        <input type="hidden" name="action" value="accept">
                        <button type="submit" class="inline-flex items-center gap-1.5 px-4 py-2 text-xs font-semibold rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white shadow-xs transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            <span>Setujui Draf Resmi</span>
                        </button>
                    </form>
                @endif

                @if($draft->feature_type->value === 'question_set' && $draft->status->value !== 'discarded')
                    <button type="button" @click="$dispatch('open-cbt-modal')" class="inline-flex items-center gap-1.5 px-4 py-2 text-xs font-semibold rounded-lg bg-[#E11D2E] hover:bg-[#c81926] text-white shadow-xs transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                        <span>Impor ke Bank Soal CBT</span>
                    </button>
                @endif
            </div>
        </div>
    </x-slot>

    <div x-data="{ activeTab: 'rendered', cbtModalOpen: false }" @open-cbt-modal.window="cbtModalOpen = true" class="space-y-6">
        <!-- 1. Pedagogic Acceptance Notice -->
        @if($draft->status->value === 'draft')
            <div class="p-4 rounded-xl bg-amber-50/70 border border-amber-200/80 text-amber-950 text-xs flex items-start gap-3">
                <svg class="w-5 h-5 text-amber-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                <div class="space-y-1">
                    <strong>PERIKSA KEABSAHAN & KESESUAIAN MATERI:</strong>
                    <p>Draf ini disusun secara otomatis oleh AI dan belum dipublikasikan kepada siswa. Periksa kebenaran konten, kunci jawaban, dan etika bahasa sebelum Anda menekan tombol <strong>Setujui Draf Resmi</strong>.</p>
                </div>
            </div>
        @elseif($draft->status->value === 'accepted')
            <div class="p-4 rounded-xl bg-emerald-50/70 border border-emerald-200/80 text-emerald-950 text-xs flex items-center gap-3">
                <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <div>
                    <strong>DRAF TELAH DISETUJUI GURU PADA {{ $draft->accepted_at?->format('d M Y, H:i') }} WIB.</strong>
                    <span class="block text-emerald-800 text-[11px]">Materi ini sah dan siap dipergunakan dalam aktivitas belajar mengajar sekolah.</span>
                </div>
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
            <!-- Main Content Area (3 Cols) -->
            <div class="lg:col-span-3 space-y-6">
                <x-card>
                    <!-- Tabs -->
                    <div class="px-6 pt-4 border-b border-zinc-100 flex items-center justify-between">
                        <div class="flex items-center gap-4">
                            <button type="button" @click="activeTab = 'rendered'"
                                :class="activeTab === 'rendered' ? 'border-brand-600 text-brand-600 font-bold' : 'border-transparent text-zinc-500 hover:text-zinc-800'"
                                class="pb-3 text-xs border-b-2 transition-colors">
                                Pratinjau Materi
                            </button>
                            <button type="button" @click="activeTab = 'raw'"
                                :class="activeTab === 'raw' ? 'border-brand-600 text-brand-600 font-bold' : 'border-transparent text-zinc-500 hover:text-zinc-800'"
                                class="pb-3 text-xs border-b-2 transition-colors">
                                Data JSON Mentah
                            </button>
                        </div>

                        <span class="text-[11px] text-zinc-400 font-mono">
                            {{ $draft->model_used }} &bull; {{ number_format($draft->total_tokens) }} tokens
                        </span>
                    </div>

                    <div class="p-6">
                        @php
                            $content = $draft->edited_content ?? $draft->generated_content;
                        @endphp

                        <!-- Tab 1: Rendered View -->
                        <div x-show="activeTab === 'rendered'" class="space-y-6">
                            <!-- 1. Question Set -->
                            @if($draft->feature_type->value === 'question_set')
                                <div class="space-y-4">
                                    <div class="flex items-center justify-between pb-3 border-b border-zinc-100">
                                        <h2 class="text-sm font-bold text-zinc-900">
                                            Daftar Butir Soal ({{ count($content['questions'] ?? []) }} Butir)
                                        </h2>
                                        <span class="text-xs text-zinc-500">Mata Pelajaran: {{ $draft->subject?->name ?? 'Umum' }}</span>
                                    </div>

                                    @foreach(($content['questions'] ?? []) as $idx => $q)
                                        <div class="p-4 rounded-xl border border-zinc-200/90 bg-white shadow-2xs space-y-3">
                                            <div class="flex items-center justify-between gap-2">
                                                <div class="flex items-center gap-2">
                                                    <span class="w-6 h-6 rounded-full bg-zinc-900 text-white text-xs font-bold flex items-center justify-center">
                                                        {{ $idx + 1 }}
                                                    </span>
                                                    <x-badge variant="neutral" size="xs">{{ strtoupper($q['question_type'] ?? 'PG') }}</x-badge>
                                                    <span class="text-[11px] font-semibold text-zinc-500">Level: {{ $q['bloom_taxonomy'] ?? 'C3' }}</span>
                                                </div>
                                                <span class="text-[11px] text-zinc-400">Bobot: {{ $q['score'] ?? 1 }} Poin</span>
                                            </div>

                                            <div class="text-xs text-zinc-900 leading-relaxed font-medium pl-8">
                                                {!! nl2br(e($q['question_text'] ?? '')) !!}
                                            </div>

                                            @if(!empty($q['options']) && is_array($q['options']))
                                                <div class="pl-8 space-y-1.5">
                                                    @foreach($q['options'] as $key => $opt)
                                                        @php
                                                            $optKey = is_string($key) ? $key : ($opt['key'] ?? chr(65 + $loop->index));
                                                            $optText = is_array($opt) ? ($opt['text'] ?? '') : $opt;
                                                            $isCorrect = ($q['correct_answer'] ?? '') === $optKey || ($opt['is_correct'] ?? false);
                                                        @endphp
                                                        <div class="flex items-start gap-2 p-2 rounded-lg text-xs {{ $isCorrect ? 'bg-emerald-50 border border-emerald-200 text-emerald-950 font-medium' : 'bg-zinc-50/70 text-zinc-700' }}">
                                                            <span class="font-bold shrink-0 {{ $isCorrect ? 'text-emerald-700' : 'text-zinc-500' }}">{{ $optKey }}.</span>
                                                            <span class="flex-1">{{ $optText }}</span>
                                                            @if($isCorrect)
                                                                <span class="text-[10px] font-bold text-emerald-700 uppercase tracking-wider shrink-0 bg-emerald-100 px-1.5 py-0.5 rounded">Kunci</span>
                                                            @endif
                                                        </div>
                                                    @endforeach
                                                </div>
                                            @endif

                                            @if(!empty($q['explanation']))
                                                <div class="ml-8 p-3 rounded-lg bg-zinc-50 border border-zinc-200/70 text-[11px] text-zinc-600 space-y-1">
                                                    <span class="font-bold text-zinc-800 block">Rasional Pembahasan:</span>
                                                    <p class="leading-relaxed">{{ $q['explanation'] }}</p>
                                                </div>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>

                            <!-- 2. Lesson Plan -->
                            @elseif($draft->feature_type->value === 'lesson_plan')
                                <div class="space-y-6">
                                    <div class="border-b border-zinc-100 pb-3">
                                        <h2 class="text-base font-bold text-zinc-900">{{ $content['title'] ?? 'Modul Ajar Kurikulum Merdeka' }}</h2>
                                        <div class="flex flex-wrap items-center gap-3 text-xs text-zinc-500 mt-1">
                                            <span>Fase/Kelas: <strong>{{ $content['grade_level'] ?? '-' }}</strong></span>
                                            <span>&bull;</span>
                                            <span>Alokasi Waktu: <strong>{{ $content['duration_minutes'] ?? 90 }} Menit</strong></span>
                                        </div>
                                    </div>

                                    <!-- Profil Pancasila -->
                                    @if(!empty($content['pancasila_dimensions']))
                                        <div class="p-4 rounded-xl bg-rose-50/60 border border-rose-200/80 space-y-2">
                                            <h3 class="text-xs font-bold text-rose-900 uppercase tracking-wider">Dimensi Profil Pelajar Pancasila</h3>
                                            <div class="flex flex-wrap gap-1.5">
                                                @foreach($content['pancasila_dimensions'] as $dim)
                                                    <span class="px-2.5 py-1 rounded-full bg-white text-rose-800 border border-rose-200 text-xs font-medium">
                                                        {{ $dim }}
                                                    </span>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endif

                                    <!-- Tujuan Pembelajaran -->
                                    <div class="space-y-2">
                                        <h3 class="text-xs font-bold text-zinc-900 uppercase tracking-wider">Tujuan Pembelajaran (TP)</h3>
                                        <ul class="text-xs text-zinc-700 space-y-1.5 list-disc pl-5 leading-relaxed">
                                            @foreach(($content['learning_objectives'] ?? []) as $obj)
                                                <li>{{ $obj }}</li>
                                            @endforeach
                                        </ul>
                                    </div>

                                    <!-- Langkah Pembelajaran -->
                                    <div class="space-y-3">
                                        <h3 class="text-xs font-bold text-zinc-900 uppercase tracking-wider">Langkah-Langkah Kegiatan Pembelajaran</h3>

                                        <div class="space-y-2.5 text-xs text-zinc-800">
                                            <!-- Pendahuluan -->
                                            <div class="p-3.5 rounded-lg border border-zinc-200 bg-zinc-50">
                                                <div class="font-bold text-zinc-900 mb-1">A. Kegiatan Pendahuluan (10 - 15 Menit)</div>
                                                <div class="text-zinc-600 leading-relaxed whitespace-pre-line">{{ is_array($content['activities']['introduction'] ?? '') ? implode("\n", $content['activities']['introduction']) : ($content['activities']['introduction'] ?? '-') }}</div>
                                            </div>

                                            <!-- Inti -->
                                            <div class="p-3.5 rounded-lg border border-brand-200 bg-brand-50/20">
                                                <div class="font-bold text-zinc-900 mb-1">B. Kegiatan Inti Bermakna</div>
                                                <div class="text-zinc-700 leading-relaxed whitespace-pre-line">{{ is_array($content['activities']['core'] ?? '') ? implode("\n", $content['activities']['core']) : ($content['activities']['core'] ?? '-') }}</div>
                                            </div>

                                            <!-- Penutup -->
                                            <div class="p-3.5 rounded-lg border border-zinc-200 bg-zinc-50">
                                                <div class="font-bold text-zinc-900 mb-1">C. Kegiatan Penutup & Refleksi (10 - 15 Menit)</div>
                                                <div class="text-zinc-600 leading-relaxed whitespace-pre-line">{{ is_array($content['activities']['closing'] ?? '') ? implode("\n", $content['activities']['closing']) : ($content['activities']['closing'] ?? '-') }}</div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Asesmen & Diferensiasi -->
                                    @if(!empty($content['assessment']))
                                        <div class="space-y-2">
                                            <h3 class="text-xs font-bold text-zinc-900 uppercase tracking-wider">Rancangan Asesmen</h3>
                                            <div class="p-3.5 rounded-lg bg-zinc-50 border border-zinc-200 text-xs text-zinc-700 leading-relaxed">
                                                {{ is_array($content['assessment']) ? json_encode($content['assessment'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) : $content['assessment'] }}
                                            </div>
                                        </div>
                                    @endif
                                </div>

                            <!-- 3. Grading Rubric -->
                            @elseif($draft->feature_type->value === 'grading_rubric')
                                <div class="space-y-4">
                                    <div class="border-b border-zinc-100 pb-3">
                                        <h2 class="text-base font-bold text-zinc-900">{{ $content['rubric_title'] ?? 'Rubrik Penilaian Autentik' }}</h2>
                                        <p class="text-xs text-zinc-500 mt-1">{{ $content['description'] ?? 'Matriks kriteria performa siswa.' }}</p>
                                    </div>

                                    <div class="overflow-x-auto border border-zinc-200 rounded-xl">
                                        <table class="w-full text-left text-xs">
                                            <thead class="bg-zinc-50 border-b border-zinc-200 text-[11px] font-bold text-zinc-700 uppercase">
                                                <tr>
                                                    <th class="p-3">Kriteria Penilaian</th>
                                                    <th class="p-3">Mahir (4)</th>
                                                    <th class="p-3">Cakap (3)</th>
                                                    <th class="p-3">Layak (2)</th>
                                                    <th class="p-3">Berkembang (1)</th>
                                                    <th class="p-3 text-right">Bobot</th>
                                                </tr>
                                            </thead>
                                            <tbody class="divide-y divide-zinc-200">
                                                @foreach(($content['criteria'] ?? []) as $crit)
                                                    <tr class="hover:bg-zinc-50/50">
                                                        <td class="p-3 font-semibold text-zinc-900 align-top max-w-[180px]">
                                                            {{ $crit['name'] ?? 'Kriteria' }}
                                                            @if(!empty($crit['description']))
                                                                <span class="block text-[11px] font-normal text-zinc-500 mt-0.5">{{ $crit['description'] }}</span>
                                                            @endif
                                                        </td>
                                                        <td class="p-3 text-zinc-700 align-top text-[11px] leading-relaxed">{{ $crit['levels']['mahir'] ?? $crit['levels'][4] ?? '-' }}</td>
                                                        <td class="p-3 text-zinc-700 align-top text-[11px] leading-relaxed">{{ $crit['levels']['cakap'] ?? $crit['levels'][3] ?? '-' }}</td>
                                                        <td class="p-3 text-zinc-700 align-top text-[11px] leading-relaxed">{{ $crit['levels']['layak'] ?? $crit['levels'][2] ?? '-' }}</td>
                                                        <td class="p-3 text-zinc-700 align-top text-[11px] leading-relaxed">{{ $crit['levels']['berkembang'] ?? $crit['levels'][1] ?? '-' }}</td>
                                                        <td class="p-3 text-right font-bold text-zinc-900 align-top">{{ $crit['weight_percentage'] ?? '25' }}%</td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>

                            <!-- 4. Essay Feedback -->
                            @elseif($draft->feature_type->value === 'essay_feedback')
                                <div class="space-y-5">
                                    <!-- Score Callout -->
                                    <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 flex items-center justify-between">
                                        <div>
                                            <span class="text-xs text-emerald-700 uppercase font-bold tracking-wider">Usulan Skor AI</span>
                                            <h2 class="text-3xl font-extrabold text-emerald-900 mt-0.5">
                                                {{ $content['suggested_score'] ?? '-' }} <span class="text-sm font-normal text-emerald-600">/ 100</span>
                                            </h2>
                                        </div>
                                        <span class="text-xs text-emerald-800 text-right max-w-xs">
                                            Nilai ini adalah rekomendasi awal. Guru memegang otoritas penuh untuk menentukan nilai akhir di buku nilai.
                                        </span>
                                    </div>

                                    <!-- Strengths -->
                                    <div class="p-4 rounded-xl bg-white border border-zinc-200 space-y-2">
                                        <h3 class="text-xs font-bold text-emerald-800 uppercase tracking-wider flex items-center gap-1.5">
                                            <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                            Kekuatan & Poin Positif Tulisan
                                        </h3>
                                        <ul class="text-xs text-zinc-700 space-y-1.5 list-disc pl-5 leading-relaxed">
                                            @foreach(($content['strengths'] ?? []) as $st)
                                                <li>{{ $st }}</li>
                                            @endforeach
                                        </ul>
                                    </div>

                                    <!-- Areas for Improvement -->
                                    <div class="p-4 rounded-xl bg-white border border-zinc-200 space-y-2">
                                        <h3 class="text-xs font-bold text-amber-800 uppercase tracking-wider flex items-center gap-1.5">
                                            <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                                            Area Perbaikan & Catatan Kritis
                                        </h3>
                                        <ul class="text-xs text-zinc-700 space-y-1.5 list-disc pl-5 leading-relaxed">
                                            @foreach(($content['areas_for_improvement'] ?? []) as $imp)
                                                <li>{{ $imp }}</li>
                                            @endforeach
                                        </ul>
                                    </div>

                                    <!-- Specific Suggestions -->
                                    <div class="p-4 rounded-xl bg-white border border-zinc-200 space-y-2">
                                        <h3 class="text-xs font-bold text-zinc-900 uppercase tracking-wider">Saran Konstruktif untuk Siswa</h3>
                                        <p class="text-xs text-zinc-700 leading-relaxed whitespace-pre-line">{{ $content['constructive_feedback'] ?? '-' }}</p>
                                    </div>
                                </div>
                            @endif
                        </div>

                        <!-- Tab 2: Raw JSON Editor & Form -->
                        <div x-show="activeTab === 'raw'" class="space-y-4">
                            <form action="{{ route('teacher.ai.drafts.update', $draft) }}" method="POST" class="space-y-4">
                                @csrf
                                <input type="hidden" name="action" value="save_edit">
                                <div>
                                    <label class="block text-xs font-bold text-zinc-700 mb-1.5">
                                        Sunting Konten JSON Mentah
                                    </label>
                                    <textarea name="edited_content_raw" rows="18"
                                        class="w-full text-xs font-mono rounded-lg border-zinc-200 focus:border-brand-500 focus:ring-brand-500 bg-zinc-950 text-emerald-400 p-4 leading-relaxed">{{ json_encode($content, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</textarea>
                                    <p class="text-[11px] text-zinc-400 mt-1">Pastikan format JSON tetap valid sebelum menyimpan.</p>
                                </div>

                                @if($draft->status->value === 'draft')
                                    <button type="submit" class="px-4 py-2 text-xs font-semibold rounded-lg bg-zinc-900 hover:bg-black text-white shadow-xs transition-colors">
                                        Simpan Perubahan Draf
                                    </button>
                                @endif
                            </form>
                        </div>
                    </div>
                </x-card>

                <!-- Feedback & Rating Box -->
                <x-card>
                    <div class="p-6">
                        <h3 class="text-sm font-bold text-zinc-900 mb-1">Evaluasi Kualitas Keluaran AI</h3>
                        <p class="text-xs text-zinc-500 mb-4">Umpan balik Anda membantu kami mengevaluasi prompt dan keandalan model AI bagi para pendidik.</p>

                        <form action="{{ route('teacher.ai.drafts.update', $draft) }}" method="POST" class="space-y-4">
                            @csrf
                            <input type="hidden" name="action" value="feedback">

                            <div class="flex items-center gap-6">
                                <div>
                                    <label class="block text-xs font-semibold text-zinc-700 mb-1.5">Rating Kualitas (1 - 5 Bintang)</label>
                                    <select name="feedback_rating" class="text-xs rounded-lg border-zinc-200 focus:border-brand-500 focus:ring-brand-500 bg-white">
                                        <option value="5" {{ $draft->feedback_rating == 5 ? 'selected' : '' }}>5 Bintang - Sangat Akurat & Relevan</option>
                                        <option value="4" {{ $draft->feedback_rating == 4 ? 'selected' : '' }}>4 Bintang - Baik, Sedikit Penyesuaian</option>
                                        <option value="3" {{ $draft->feedback_rating == 3 ? 'selected' : '' }}>3 Bintang - Cukup, Perlu Diedit</option>
                                        <option value="2" {{ $draft->feedback_rating == 2 ? 'selected' : '' }}>2 Bintang - Kurang Memuaskan</option>
                                        <option value="1" {{ $draft->feedback_rating == 1 ? 'selected' : '' }}>1 Bintang - Tidak Sesuai / Buruk</option>
                                    </select>
                                </div>

                                <div class="pt-6">
                                    <label class="flex items-center gap-2 text-xs text-rose-700 cursor-pointer">
                                        <input type="checkbox" name="is_flagged_bad_output" value="1" {{ $draft->is_flagged_bad_output ? 'checked' : '' }} class="rounded border-rose-300 text-rose-600 focus:ring-rose-500">
                                        <span class="font-medium">Tandai Konten Bermasalah / Halusinasi</span>
                                    </label>
                                </div>
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-zinc-700 mb-1.5">Catatan Evaluasi Guru (Opsional)</label>
                                <textarea name="feedback_notes" rows="2" placeholder="Tuliskan masukan khusus, misalnya: 'Kunci nomor 3 keliru karena...' atau 'Langkah pembelajaran sangat inspiratif'"
                                    class="w-full text-xs rounded-lg border-zinc-200 focus:border-brand-500 focus:ring-brand-500">{{ $draft->feedback_notes }}</textarea>
                            </div>

                            <button type="submit" class="px-4 py-2 text-xs font-semibold rounded-lg bg-zinc-100 hover:bg-zinc-200 text-zinc-800 border border-zinc-200 transition-colors">
                                Kirim Evaluasi
                            </button>
                        </form>
                    </div>
                </x-card>
            </div>

            <!-- Sidebar Info & Metadata (1 Col) -->
            <div class="space-y-6">
                <!-- Metadata Card -->
                <div class="p-5 rounded-2xl bg-white border border-zinc-200/80 shadow-2xs space-y-4">
                    <h3 class="text-xs font-bold text-zinc-900 uppercase tracking-wider">Metadata Generasi</h3>

                    <div class="space-y-3 text-xs">
                        <div class="flex items-center justify-between pb-2 border-b border-zinc-100">
                            <span class="text-zinc-500">Model AI</span>
                            <span class="font-mono font-semibold text-zinc-900">{{ $draft->model_used }}</span>
                        </div>
                        <div class="flex items-center justify-between pb-2 border-b border-zinc-100">
                            <span class="text-zinc-500">Total Token</span>
                            <span class="font-semibold text-zinc-900">{{ number_format($draft->total_tokens) }}</span>
                        </div>
                        <div class="flex items-center justify-between pb-2 border-b border-zinc-100">
                            <span class="text-zinc-500">Prompt / Selesai</span>
                            <span class="text-zinc-700">{{ number_format($draft->prompt_tokens) }} / {{ number_format($draft->completion_tokens) }}</span>
                        </div>
                        <div class="flex items-center justify-between pb-2 border-b border-zinc-100">
                            <span class="text-zinc-500">Latensi Respons</span>
                            <span class="font-semibold text-zinc-900">{{ $draft->latency_ms }} ms</span>
                        </div>
                        <div class="flex items-center justify-between pb-2 border-b border-zinc-100">
                            <span class="text-zinc-500">Estimasi Biaya</span>
                            <span class="font-semibold text-emerald-700">Rp {{ number_format($draft->estimated_cost_idr, 2, ',', '.') }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-zinc-500">ID Referensi</span>
                            <span class="font-mono text-[10px] text-zinc-400 truncate max-w-[100px]">{{ $draft->id }}</span>
                        </div>
                    </div>
                </div>

                <!-- Teacher Controls Checklist -->
                <div class="p-5 rounded-2xl bg-zinc-900 text-zinc-100 shadow-2xs space-y-3">
                    <h3 class="text-xs font-bold text-rose-400 uppercase tracking-wider">Checklist Peninjauan Guru</h3>
                    <ul class="text-xs space-y-2 text-zinc-300">
                        <li class="flex items-start gap-2">
                            <span class="text-emerald-400 font-bold">&#10003;</span>
                            <span>Kesesuaian materi dengan kurikulum satuan pendidikan.</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <span class="text-emerald-400 font-bold">&#10003;</span>
                            <span>Kebenaran fakta ilmiah dan kunci jawaban butir soal.</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <span class="text-emerald-400 font-bold">&#10003;</span>
                            <span>Ketiadaan konten SARA, bias gender, atau materi berbahaya.</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- CBT Import Modal -->
        @if($draft->feature_type->value === 'question_set')
            <div x-show="cbtModalOpen" x-cloak class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
                <div class="fixed inset-0 bg-black/60 backdrop-blur-xs transition-opacity" @click="cbtModalOpen = false"></div>
                <div class="min-h-full flex items-center justify-center p-4">
                    <div class="relative bg-white rounded-2xl max-w-md w-full p-6 shadow-xl space-y-4 border border-zinc-200">
                        <div class="flex items-center justify-between pb-3 border-b border-zinc-100">
                            <div class="flex items-center gap-2">
                                <div class="w-8 h-8 rounded-lg bg-rose-50 text-rose-600 flex items-center justify-center font-bold">
                                    CBT
                                </div>
                                <h3 class="text-sm font-bold text-zinc-900">Impor ke Bank Soal CBT</h3>
                            </div>
                            <button type="button" @click="cbtModalOpen = false" class="text-zinc-400 hover:text-zinc-700">&times;</button>
                        </div>

                        <p class="text-xs text-zinc-500 leading-relaxed">
                            Butir-butir soal dari draf ini ({{ count($content['questions'] ?? []) }} butir) akan dimasukkan ke dalam Bank Soal CBT resmi sekolah Anda dengan status aktif.
                        </p>

                        <form action="{{ route('teacher.ai.drafts.import_cbt', $draft) }}" method="POST" class="space-y-4">
                            @csrf

                            <div>
                                <label for="cbt_subject_id" class="block text-xs font-semibold text-zinc-700 mb-1.5">
                                    Target Mata Pelajaran CBT <span class="text-rose-500">*</span>
                                </label>
                                <select name="subject_id" id="cbt_subject_id" class="w-full text-xs rounded-lg border-zinc-200 focus:border-brand-500 focus:ring-brand-500 bg-white" required>
                                    <option value="">Pilih Mata Pelajaran...</option>
                                    @foreach($subjects as $subj)
                                        <option value="{{ $subj->id }}" {{ ($draft->subject_id ?? old('subject_id')) == $subj->id ? 'selected' : '' }}>
                                            {{ $subj->name }} ({{ $subj->code }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label for="cbt_grade_level_id" class="block text-xs font-semibold text-zinc-700 mb-1.5">
                                    Target Jenjang Kelas
                                </label>
                                <select name="grade_level_id" id="cbt_grade_level_id" class="w-full text-xs rounded-lg border-zinc-200 focus:border-brand-500 focus:ring-brand-500 bg-white">
                                    <option value="">Semua / Tidak ditentukan</option>
                                    @foreach($gradeLevels as $gl)
                                        <option value="{{ $gl->id }}" {{ old('grade_level_id') == $gl->id ? 'selected' : '' }}>
                                            {{ $gl->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="flex items-center justify-end gap-2 pt-3 border-t border-zinc-100">
                                <button type="button" @click="cbtModalOpen = false" class="px-4 py-2 text-xs font-medium text-zinc-600 hover:text-zinc-900">
                                    Batal
                                </button>
                                <button type="submit" class="px-4 py-2 text-xs font-semibold rounded-lg bg-[#E11D2E] hover:bg-[#c81926] text-white shadow-xs">
                                    Konfirmasi & Impor
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        @endif
    </div>
</x-app-layout>
