<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <div class="flex items-center gap-2 mb-1">
                    <span class="text-[10px] font-bold tracking-widest uppercase text-zinc-400 font-sans">MODUL GURU</span>
                    <span class="text-zinc-300">&bull;</span>
                    <x-badge variant="primary" size="xs">Fase 4B</x-badge>
                </div>
                <h1 class="text-2xl sm:text-3xl font-serif font-bold text-zinc-900 tracking-tight">
                    Asisten AI Pendidik
                </h1>
                <p class="text-xs sm:text-sm text-zinc-500 mt-1">
                    Bantuan penyusunan modul ajar, butir soal CBT, rubrik evaluasi, dan saran umpan balik tugas di bawah kendali penuh guru.
                </p>
            </div>

            <!-- Quick Generator Action Menu -->
            <div class="flex flex-wrap items-center gap-2">
                <a href="{{ route('teacher.ai.lesson_plan') }}" class="inline-flex items-center gap-1.5 px-3 py-2 text-xs font-semibold rounded-lg bg-zinc-900 text-white hover:bg-black shadow-xs transition-colors">
                    <svg class="w-4 h-4 text-rose-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                    <span>Modul Ajar</span>
                </a>
                <a href="{{ route('teacher.ai.questions') }}" class="inline-flex items-center gap-1.5 px-3 py-2 text-xs font-semibold rounded-lg bg-zinc-900 text-white hover:bg-black shadow-xs transition-colors">
                    <svg class="w-4 h-4 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span>Paket Soal CBT</span>
                </a>
                <a href="{{ route('teacher.ai.rubrics') }}" class="inline-flex items-center gap-1.5 px-3 py-2 text-xs font-semibold rounded-lg bg-zinc-100 text-zinc-800 hover:bg-zinc-200 border border-zinc-200 transition-colors">
                    <span>Rubrik</span>
                </a>
                <a href="{{ route('teacher.ai.essay_feedback') }}" class="inline-flex items-center gap-1.5 px-3 py-2 text-xs font-semibold rounded-lg bg-zinc-100 text-zinc-800 hover:bg-zinc-200 border border-zinc-200 transition-colors">
                    <span>Umpan Balik Esai</span>
                </a>
            </div>
        </div>
    </x-slot>

    <div class="space-y-6">
        <!-- 1. Quota & Transparency Stat Banner -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="p-4 rounded-xl bg-white border border-zinc-200/80 shadow-2xs">
                <span class="text-xs text-zinc-400 block mb-1">Status Layanan AI</span>
                <div class="flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full {{ $quota->is_ai_enabled ? 'bg-emerald-500' : 'bg-rose-500' }}"></span>
                    <strong class="text-sm font-bold text-zinc-900">{{ $quota->is_ai_enabled ? 'Aktif (Berlisensi)' : 'Non-Aktif' }}</strong>
                </div>
                <span class="text-[11px] text-zinc-500 mt-1 block">Batas Laju: {{ $quota->rate_limit_per_minute }} req/menit</span>
            </div>

            <div class="p-4 rounded-xl bg-white border border-zinc-200/80 shadow-2xs">
                <span class="text-xs text-zinc-400 block mb-1">Penggunaan Kuota Token</span>
                <strong class="text-sm font-bold text-zinc-900">{{ number_format($quota->current_month_tokens_used) }} / {{ number_format($quota->monthly_token_limit) }}</strong>
                <div class="w-full bg-zinc-100 h-1.5 rounded-full overflow-hidden mt-2">
                    <div class="bg-brand-600 h-full rounded-full" style="width: {{ min(100, $quota->usagePercentage()) }}%"></div>
                </div>
                <span class="text-[11px] text-zinc-500 mt-1 block">{{ $quota->usagePercentage() }}% terpakai</span>
            </div>

            <div class="p-4 rounded-xl bg-white border border-zinc-200/80 shadow-2xs">
                <span class="text-xs text-zinc-400 block mb-1">Estimasi Biaya Berjalan</span>
                <strong class="text-sm font-bold text-zinc-900">Rp {{ number_format($quota->current_month_cost_idr, 0, ',', '.') }}</strong>
                <span class="text-[11px] text-zinc-500 mt-1 block">Pagu: Rp {{ number_format($quota->monthly_budget_idr, 0, ',', '.') }}</span>
            </div>

            <div class="p-4 rounded-xl bg-white border border-zinc-200/80 shadow-2xs">
                <span class="text-xs text-zinc-400 block mb-1">Siklus Kuota</span>
                <strong class="text-sm font-bold text-zinc-900">{{ $quota->quota_reset_at ? $quota->quota_reset_at->format('d M Y') : 'Awal Bulan' }}</strong>
                <span class="text-[11px] text-emerald-700 mt-1 block">Penyedia: Google Gemini</span>
            </div>
        </div>

        <!-- 2. Pedagogic & Ethical Notice Callout -->
        <div class="p-4 rounded-xl bg-amber-50/70 border border-amber-200/80 text-amber-950 text-xs flex items-start gap-3">
            <svg class="w-5 h-5 text-amber-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            <div class="space-y-1">
                <strong>PRINSIP KENDALI PENUH GURU:</strong>
                <p>Seluruh materi yang dihasilkan oleh AI berstatus <strong>Draf Mentah</strong> dan tidak pernah dipublikasikan langsung kepada siswa. Guru wajib memeriksa kebenaran kunci jawaban, kesesuaian capaian pembelajaran, dan etika konten sebelum menyetujui draf.</p>
            </div>
        </div>

        <!-- 3. Generator Tool Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <a href="{{ route('teacher.ai.lesson_plan') }}" class="p-5 rounded-2xl bg-white border border-zinc-200/80 hover:border-zinc-900 hover:shadow-md transition-all group flex flex-col justify-between min-h-[170px]">
                <div>
                    <div class="w-10 h-10 rounded-xl bg-rose-50 text-[#E11D2E] flex items-center justify-center mb-3 group-hover:scale-105 transition-transform">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                    </div>
                    <h2 class="text-sm font-bold text-zinc-900 group-hover:text-black">Modul Ajar / RPP</h2>
                    <p class="text-xs text-zinc-500 mt-1 line-clamp-2">Susun rencana pembelajaran dari tujuan CP/TP dan alokasi waktu secara otomatis.</p>
                </div>
                <span class="text-xs font-semibold text-brand-600 mt-3 inline-flex items-center gap-1">Buka Generator &rarr;</span>
            </a>

            <a href="{{ route('teacher.ai.questions') }}" class="p-5 rounded-2xl bg-white border border-zinc-200/80 hover:border-zinc-900 hover:shadow-md transition-all group flex flex-col justify-between min-h-[170px]">
                <div>
                    <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-700 flex items-center justify-center mb-3 group-hover:scale-105 transition-transform">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <h2 class="text-sm font-bold text-zinc-900 group-hover:text-black">Generator Soal CBT</h2>
                    <p class="text-xs text-zinc-500 mt-1 line-clamp-2">Paket butir soal berjenjang (PG, Benar/Salah, Esai) dengan kunci & pembahasan.</p>
                </div>
                <span class="text-xs font-semibold text-amber-700 mt-3 inline-flex items-center gap-1">Buka Generator &rarr;</span>
            </a>

            <a href="{{ route('teacher.ai.rubrics') }}" class="p-5 rounded-2xl bg-white border border-zinc-200/80 hover:border-zinc-900 hover:shadow-md transition-all group flex flex-col justify-between min-h-[170px]">
                <div>
                    <div class="w-10 h-10 rounded-xl bg-sky-50 text-sky-700 flex items-center justify-center mb-3 group-hover:scale-105 transition-transform">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>
                    </div>
                    <h2 class="text-sm font-bold text-zinc-900 group-hover:text-black">Rubrik Penilaian</h2>
                    <p class="text-xs text-zinc-500 mt-1 line-clamp-2">Kriteria penilaian terbobot dengan deskriptor capaian bertingkat.</p>
                </div>
                <span class="text-xs font-semibold text-sky-700 mt-3 inline-flex items-center gap-1">Buka Generator &rarr;</span>
            </a>

            <a href="{{ route('teacher.ai.essay_feedback') }}" class="p-5 rounded-2xl bg-white border border-zinc-200/80 hover:border-zinc-900 hover:shadow-md transition-all group flex flex-col justify-between min-h-[170px]">
                <div>
                    <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-700 flex items-center justify-center mb-3 group-hover:scale-105 transition-transform">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                    </div>
                    <h2 class="text-sm font-bold text-zinc-900 group-hover:text-black">Umpan Balik Esai</h2>
                    <p class="text-xs text-zinc-500 mt-1 line-clamp-2">Saran komentar konstruktif dan catatan kelebihan tulisan siswa (anonim).</p>
                </div>
                <span class="text-xs font-semibold text-emerald-700 mt-3 inline-flex items-center gap-1">Buka Generator &rarr;</span>
            </a>
        </div>

        <!-- 4. Drafts Table / History -->
        <div class="bg-white rounded-2xl border border-zinc-200/80 shadow-2xs overflow-hidden">
            <div class="p-5 border-b border-zinc-100 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h2 class="text-base font-bold text-zinc-900">Riwayat Draf Materi AI Anda</h2>
                    <p class="text-xs text-zinc-400 mt-0.5">Draf yang belum disetujui hanya terlihat oleh Anda.</p>
                </div>

                <!-- Filter Controls -->
                <form method="GET" action="{{ route('teacher.ai.index') }}" class="flex flex-wrap items-center gap-2 text-xs">
                    <select name="feature" onchange="this.form.submit()" class="px-2.5 py-1.5 rounded-lg border border-zinc-200 text-zinc-700 bg-zinc-50 font-medium">
                        <option value="">Semua Modul</option>
                        <option value="lesson_plan" {{ $featureFilter === 'lesson_plan' ? 'selected' : '' }}>Modul Ajar</option>
                        <option value="question_set" {{ $featureFilter === 'question_set' ? 'selected' : '' }}>Paket Soal</option>
                        <option value="grading_rubric" {{ $featureFilter === 'grading_rubric' ? 'selected' : '' }}>Rubrik</option>
                        <option value="essay_feedback" {{ $featureFilter === 'essay_feedback' ? 'selected' : '' }}>Umpan Balik Esai</option>
                    </select>

                    <select name="status" onchange="this.form.submit()" class="px-2.5 py-1.5 rounded-lg border border-zinc-200 text-zinc-700 bg-zinc-50 font-medium">
                        <option value="all" {{ $statusFilter === 'all' ? 'selected' : '' }}>Semua Status</option>
                        <option value="draft" {{ $statusFilter === 'draft' ? 'selected' : '' }}>Menunggu Tinjauan (Draf)</option>
                        <option value="accepted" {{ $statusFilter === 'accepted' ? 'selected' : '' }}>Sudah Disetujui</option>
                        <option value="discarded" {{ $statusFilter === 'discarded' ? 'selected' : '' }}>Dibuang</option>
                    </select>
                </form>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-zinc-50/80 border-b border-zinc-100 text-zinc-500 font-semibold uppercase tracking-wider text-[10px]">
                        <tr>
                            <th class="py-3 px-4">Judul Draf</th>
                            <th class="py-3 px-4">Jenis Modul</th>
                            <th class="py-3 px-4">Status Verifikasi</th>
                            <th class="py-3 px-4">Token & Biaya</th>
                            <th class="py-3 px-4">Dibuat Pada</th>
                            <th class="py-3 px-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-100">
                        @forelse ($drafts as $item)
                            <tr class="hover:bg-zinc-50/50 transition-colors">
                                <td class="py-3.5 px-4 font-semibold text-zinc-900 max-w-xs truncate">
                                    <a href="{{ route('teacher.ai.drafts.show', $item) }}" class="hover:text-brand-600 transition-colors">
                                        {{ $item->title }}
                                    </a>
                                </td>
                                <td class="py-3.5 px-4 text-zinc-600">
                                    {{ $item->feature_type->label() }}
                                </td>
                                <td class="py-3.5 px-4">
                                    <x-badge :variant="$item->status->badgeVariant()" size="xs">
                                        {{ $item->status->label() }}
                                    </x-badge>
                                </td>
                                <td class="py-3.5 px-4 text-zinc-500 font-mono text-[11px]">
                                    {{ number_format($item->tokens_used) }} tok &bull; Rp {{ number_format($item->estimated_cost_idr, 0, ',', '.') }}
                                </td>
                                <td class="py-3.5 px-4 text-zinc-500">
                                    {{ $item->created_at->format('d M Y, H:i') }}
                                </td>
                                <td class="py-3.5 px-4 text-right">
                                    <a href="{{ route('teacher.ai.drafts.show', $item) }}" class="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-semibold rounded bg-zinc-100 hover:bg-zinc-200 text-zinc-800 transition-colors">
                                        Tinjau Draf &rarr;
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-12 text-center text-zinc-400">
                                    Belum ada draf AI yang tersimpan. Pilih salah satu generator di atas untuk mulai menyusun materi pembelajaran.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($drafts->hasPages())
                <div class="p-4 border-t border-zinc-100">
                    {{ $drafts->links() }}
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
