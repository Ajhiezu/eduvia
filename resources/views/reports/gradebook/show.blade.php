<x-layouts.app>
    <x-slot:headerTitle>
        Buku Nilai — {{ $gradebook->subject?->name }} ({{ $gradebook->classroom?->name }})
    </x-slot:headerTitle>

    <div class="space-y-6">
        @if (session('success'))
            <x-alert variant="success" dismissible>
                {{ session('success') }}
            </x-alert>
        @endif

        @if (session('warning'))
            <x-alert variant="warning" dismissible>
                {{ session('warning') }}
            </x-alert>
        @endif

        @if ($gradebook->status === \App\Models\SubjectGradebook::STATUS_REVISION_REQUESTED && $gradebook->verification_notes)
            <x-alert variant="danger">
                <strong>Catatan Revisi dari Kurikulum/Sekolah:</strong> {{ $gradebook->verification_notes }}
            </x-alert>
        @endif

        <!-- Top Header & Actions -->
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
            <div>
                <a href="{{ route('reports.gradebook.index') }}" class="inline-flex items-center text-xs font-semibold text-zinc-500 hover:text-zinc-800 transition-colors mb-1.5">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                    Kembali ke Rekap Nilai
                </a>
                <div class="flex items-center gap-3">
                    <h1 class="text-xl sm:text-2xl font-extrabold text-zinc-900 tracking-tight">
                        {{ $gradebook->subject?->name }} — Kelas {{ $gradebook->classroom?->name }}
                    </h1>
                    <x-badge :variant="$gradebook->statusBadgeVariant()" size="sm">
                        {{ $gradebook->statusLabel() }}
                    </x-badge>
                </div>
                <p class="text-xs text-zinc-500 mt-1">
                    Guru Pengampu: <span class="font-semibold text-zinc-700">{{ $gradebook->teacher?->name }}</span> •
                    Periode: <span class="font-semibold text-zinc-700">{{ $gradebook->academicYear?->name }} ({{ $gradebook->semester?->name }})</span>
                </p>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                @if($gradebook->isEditable())
                    <form action="{{ route('reports.gradebook.sync', $gradebook) }}" method="POST">
                        @csrf
                        <x-button type="submit" variant="secondary" size="sm" title="Tarik ulang nilai terbaru dari tugas dan CBT">
                            <svg class="w-4 h-4 mr-1.5 text-zinc-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                            </svg>
                            Sinkronkan Sumber
                        </x-button>
                    </form>

                    <x-button href="{{ route('reports.gradebook.edit', $gradebook) }}" variant="secondary" size="sm">
                        <svg class="w-4 h-4 mr-1.5 text-zinc-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                        </svg>
                        Edit Nilai / Deskripsi
                    </x-button>

                    <form action="{{ route('reports.gradebook.submit', $gradebook) }}" method="POST" onsubmit="return confirm('Kirimkan rekap nilai ini untuk verifikasi pihak sekolah? Nilai akan dikunci sementara.');">
                        @csrf
                        <x-button type="submit" variant="primary" size="sm">
                            <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            Ajukan Verifikasi
                        </x-button>
                    </form>
                @endif

                @if((auth()->user()->isSuperAdmin() || auth()->user()->isSchoolAdmin()) && $gradebook->status === \App\Models\SubjectGradebook::STATUS_SUBMITTED)
                    <!-- Admin Approval Controls -->
                    <form action="{{ route('reports.gradebook.verify', $gradebook) }}" method="POST">
                        @csrf
                        <x-button type="submit" variant="primary" size="sm" class="bg-emerald-600 hover:bg-emerald-700">
                            <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            Verifikasi & Setujui
                        </x-button>
                    </form>

                    <button
                        type="button"
                        onclick="document.getElementById('revisionModal').classList.remove('hidden')"
                        class="inline-flex items-center px-3 py-1.5 text-xs font-semibold rounded-lg text-red-700 bg-red-50 hover:bg-red-100 border border-red-200 transition-colors"
                    >
                        Minta Revisi
                    </button>
                @endif
            </div>
        </div>

        <!-- Formula & Weight Banner -->
        @php
            $weights = $gradebook->weight_snapshot ?? [];
            $wTugas = $weights['assignment_weight'] ?? 30;
            $wKuis = $weights['quiz_weight'] ?? 20;
            $wUts = $weights['midterm_weight'] ?? 25;
            $wUas = $weights['final_weight'] ?? 25;
            $kkm = $weights['passing_grade'] ?? 75;
        @endphp
        <x-card padding class="border-zinc-200 bg-zinc-50/50">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 text-xs">
                <div class="space-y-1">
                    <span class="font-bold text-zinc-900 uppercase text-[11px] tracking-wider">Formula Pembobotan Terapan:</span>
                    <div class="text-zinc-600">
                        Nilai Akhir = (Tugas × <strong>{{ $wTugas }}%</strong>) + (Kuis/Formatif × <strong>{{ $wKuis }}%</strong>) + (UTS × <strong>{{ $wUts }}%</strong>) + (UAS × <strong>{{ $wUas }}%</strong>)
                    </div>
                </div>

                <div class="flex items-center gap-4 text-zinc-700 shrink-0">
                    <div class="bg-white px-3 py-1.5 rounded-lg border border-zinc-200">
                        Batas KKM: <strong class="text-brand-700 font-extrabold">{{ $kkm }}</strong>
                    </div>
                    <div class="bg-white px-3 py-1.5 rounded-lg border border-zinc-200">
                        Total Siswa: <strong class="text-zinc-900 font-bold">{{ $gradebook->items->count() }}</strong>
                    </div>
                </div>
            </div>
        </x-card>

        <!-- Student Items Table -->
        <x-card padding="none" class="border-zinc-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-zinc-700">
                    <thead class="bg-zinc-50 border-b border-zinc-200 text-zinc-500 font-bold uppercase text-[10px] tracking-wider">
                        <tr>
                            <th class="py-3 px-3 w-10 text-center">No</th>
                            <th class="py-3 px-3">Nama Siswa & NIS</th>
                            <th class="py-3 px-3 text-center">Tugas ({{ $wTugas }}%)</th>
                            <th class="py-3 px-3 text-center">Kuis ({{ $wKuis }}%)</th>
                            <th class="py-3 px-3 text-center">UTS ({{ $wUts }}%)</th>
                            <th class="py-3 px-3 text-center">UAS ({{ $wUas }}%)</th>
                            <th class="py-3 px-3 text-center font-extrabold text-brand-700">Nilai Akhir</th>
                            <th class="py-3 px-3 text-center">Predikat</th>
                            <th class="py-3 px-3 text-center">Ketuntasan</th>
                            <th class="py-3 px-4 min-w-[260px]">Capaian Pembelajaran</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-200">
                        @forelse($gradebook->items as $index => $item)
                            <tr class="hover:bg-zinc-50/75 transition-colors">
                                <td class="py-3 px-3 text-center text-zinc-400 font-medium">{{ $index + 1 }}</td>
                                <td class="py-3 px-3 font-semibold text-zinc-900">
                                    {{ $item->student?->name }}
                                    <div class="text-[11px] text-zinc-400 font-normal">NIS: {{ $item->student?->studentProfile?->nis ?? '-' }}</div>
                                </td>
                                <td class="py-3 px-3 text-center font-bold text-zinc-800">{{ number_format($item->assignment_avg, 1) }}</td>
                                <td class="py-3 px-3 text-center font-bold text-zinc-800">{{ number_format($item->quiz_avg, 1) }}</td>
                                <td class="py-3 px-3 text-center font-bold text-zinc-800">{{ number_format($item->midterm_score, 1) }}</td>
                                <td class="py-3 px-3 text-center font-bold text-zinc-800">{{ number_format($item->final_score_raw, 1) }}</td>
                                <td class="py-3 px-3 text-center font-extrabold text-sm text-zinc-900 bg-zinc-50/50">
                                    {{ number_format($item->final_score, 1) }}
                                </td>
                                <td class="py-3 px-3 text-center">
                                    <x-badge :variant="$item->predicateBadgeVariant()" size="xs">
                                        {{ $item->predicate }}
                                    </x-badge>
                                </td>
                                <td class="py-3 px-3 text-center">
                                    @if($item->is_passed)
                                        <span class="inline-flex items-center text-[11px] font-bold text-emerald-600">
                                            ✓ Tuntas
                                        </span>
                                    @else
                                        <span class="inline-flex items-center text-[11px] font-bold text-red-600">
                                            ✗ Remidi
                                        </span>
                                    @endif
                                </td>
                                <td class="py-3 px-4 text-zinc-600 text-[11px] leading-relaxed">
                                    {{ $item->achievement_description ?: 'Capaian pembelajaran belum terisi.' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="py-8 text-center text-zinc-400">
                                    Belum ada data siswa dalam buku nilai ini.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-card>
    </div>

    <!-- Modal Minta Revisi (Admin Only) -->
    <div id="revisionModal" class="hidden fixed inset-0 z-50 overflow-y-auto bg-black/40 flex items-center justify-center p-4">
        <div class="bg-white rounded-xl shadow-xl max-w-md w-full p-6 space-y-4">
            <h3 class="text-base font-bold text-zinc-900">Kembalikan Rekap Nilai untuk Revisi</h3>
            <p class="text-xs text-zinc-500">
                Tuliskan instruksi atau catatan perbaikan yang wajib dilengkapi oleh guru pengampu mata pelajaran.
            </p>

            <form action="{{ route('reports.gradebook.request-revision', $gradebook) }}" method="POST">
                @csrf
                <div>
                    <x-label for="verification_notes" value="Catatan Revisi" required />
                    <x-textarea id="verification_notes" name="verification_notes" rows="4" class="mt-1" placeholder="Contoh: Harap lengkapi nilai tugas yang masih kosong untuk 3 siswa..." required></x-textarea>
                </div>

                <div class="mt-4 flex items-center justify-end gap-2">
                    <button type="button" onclick="document.getElementById('revisionModal').classList.add('hidden')" class="px-3 py-1.5 text-xs font-semibold rounded-lg text-zinc-600 hover:bg-zinc-100">
                        Batal
                    </button>
                    <x-button type="submit" variant="primary" size="sm" class="bg-red-600 hover:bg-red-700">
                        Kirim Catatan Revisi
                    </x-button>
                </div>
            </form>
        </div>
    </div>
</x-layouts.app>
