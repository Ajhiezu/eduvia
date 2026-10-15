<x-layouts.app>
    <x-slot:headerTitle>
        Detail Rapor — {{ $reportCard->academicYear?->name }} ({{ $reportCard->semester?->name }})
    </x-slot:headerTitle>

    @php
        $snapshot = $reportCard->isPublished() && !empty($reportCard->snapshot)
            ? $reportCard->snapshot
            : $reportCard->buildSnapshot();
    @endphp

    <div class="space-y-6">
        <!-- Top Nav & Header -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <a href="{{ route('student.reports.index') }}" class="inline-flex items-center text-xs font-semibold text-zinc-500 hover:text-zinc-800 transition-colors mb-1.5">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                    Kembali ke Daftar Rapor Saya
                </a>
                <div class="flex items-center gap-3">
                    <h1 class="text-xl sm:text-2xl font-extrabold text-zinc-900 tracking-tight">
                        Rapor Semester: {{ $snapshot['academic']['academic_year_name'] }} ({{ $snapshot['academic']['semester_name'] }})
                    </h1>
                    <x-badge variant="success" size="sm">Resmi Diterbitkan</x-badge>
                </div>
                <p class="text-xs text-zinc-500 mt-1">
                    Kelas: <span class="font-semibold text-zinc-700">{{ $snapshot['academic']['classroom_name'] }}</span> •
                    Wali Kelas: <span class="font-semibold text-zinc-700">{{ $snapshot['homeroom_teacher']['name'] }}</span> •
                    Diterbitkan: <span class="font-semibold text-zinc-700">{{ $snapshot['published_info']['published_at'] ?? '-' }}</span>
                </p>
            </div>

            <div class="flex items-center gap-2.5">
                <x-button href="{{ route('reports.cards.pdf', $reportCard) }}" target="_blank" variant="primary" size="sm">
                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                    </svg>
                    Cetak / Unduh PDF Resmi
                </x-button>
            </div>
        </div>

        <!-- Academic Table -->
        <x-card padding="none" class="border-zinc-200 overflow-hidden">
            <div class="px-5 py-4 border-b border-zinc-200 bg-zinc-50/50 flex items-center justify-between">
                <div>
                    <h2 class="text-sm font-bold text-zinc-900">A. Hasil Penilaian Capaian Pembelajaran</h2>
                    <p class="text-xs text-zinc-500 mt-0.5">Daftar nilai akhir semester untuk seluruh mata pelajaran.</p>
                </div>

                <div class="text-xs font-semibold text-zinc-700">
                    Rata-rata Nilai: <span class="text-base font-extrabold text-brand-700">{{ number_format($snapshot['summary']['average_score'] ?? 0, 1) }}</span>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-zinc-700">
                    <thead class="bg-zinc-50 border-b border-zinc-200 text-zinc-500 font-bold uppercase text-[10px] tracking-wider">
                        <tr>
                            <th class="py-3 px-3 w-10 text-center">No</th>
                            <th class="py-3 px-4">Mata Pelajaran</th>
                            <th class="py-3 px-3 text-center">KKM</th>
                            <th class="py-3 px-3 text-center">Nilai Akhir</th>
                            <th class="py-3 px-3 text-center">Predikat</th>
                            <th class="py-3 px-3 text-center">Status</th>
                            <th class="py-3 px-4 min-w-[280px]">Deskripsi Capaian Pembelajaran</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-200">
                        @forelse($snapshot['items'] as $index => $item)
                            <tr class="hover:bg-zinc-50/75 transition-colors">
                                <td class="py-3.5 px-3 text-center text-zinc-400 font-medium">{{ $index + 1 }}</td>
                                <td class="py-3.5 px-4 font-semibold text-zinc-900">
                                    {{ $item['subject_name'] }}
                                    <div class="text-[11px] text-zinc-400 font-normal">Pengampu: {{ $item['teacher_name'] ?? '-' }}</div>
                                </td>
                                <td class="py-3.5 px-3 text-center font-bold text-zinc-700">{{ number_format($item['passing_grade'], 0) }}</td>
                                <td class="py-3.5 px-3 text-center font-extrabold text-sm text-zinc-900 bg-zinc-50/40">
                                    {{ number_format($item['final_score'], 1) }}
                                </td>
                                <td class="py-3.5 px-3 text-center">
                                    @php
                                        $badgeVar = match($item['predicate'] ?? 'C') {
                                            'A' => 'success',
                                            'B' => 'brand',
                                            'C' => 'warning',
                                            default => 'danger'
                                        };
                                    @endphp
                                    <x-badge :variant="$badgeVar" size="xs">
                                        {{ $item['predicate'] }}
                                    </x-badge>
                                </td>
                                <td class="py-3.5 px-3 text-center">
                                    @if($item['is_passed'])
                                        <span class="text-[11px] font-bold text-emerald-600">✓ Tuntas</span>
                                    @else
                                        <span class="text-[11px] font-bold text-red-600">✗ Remidi</span>
                                    @endif
                                </td>
                                <td class="py-3.5 px-4 text-zinc-600 text-[11px] leading-relaxed">
                                    {{ $item['achievement_description'] ?: '-' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="py-8 text-center text-zinc-400">
                                    Tidak ada data mata pelajaran pada rapor ini.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-card>

        <!-- Non-academic & Attendance -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <x-card padding class="border-zinc-200 space-y-3">
                <div class="text-xs font-bold uppercase tracking-wider text-zinc-400">Kehadiran Siswa</div>
                @php
                    $att = $snapshot['attendance'] ?? [];
                @endphp
                <div class="grid grid-cols-4 gap-2 text-center pt-1">
                    <div class="p-3 bg-zinc-50 rounded-lg border border-zinc-200">
                        <div class="text-[11px] text-zinc-500">Hadir</div>
                        <div class="text-lg font-extrabold text-zinc-900 mt-0.5">{{ $att['present'] ?? 0 }}</div>
                    </div>
                    <div class="p-3 bg-zinc-50 rounded-lg border border-zinc-200">
                        <div class="text-[11px] text-zinc-500">Sakit</div>
                        <div class="text-lg font-extrabold text-zinc-900 mt-0.5">{{ $att['sick'] ?? 0 }}</div>
                    </div>
                    <div class="p-3 bg-zinc-50 rounded-lg border border-zinc-200">
                        <div class="text-[11px] text-zinc-500">Izin</div>
                        <div class="text-lg font-extrabold text-zinc-900 mt-0.5">{{ $att['permit'] ?? 0 }}</div>
                    </div>
                    <div class="p-3 bg-zinc-50 rounded-lg border border-zinc-200">
                        <div class="text-[11px] text-zinc-500">Alpa</div>
                        <div class="text-lg font-extrabold {{ ($att['absent'] ?? 0) > 0 ? 'text-red-600' : 'text-zinc-900' }} mt-0.5">{{ $att['absent'] ?? 0 }}</div>
                    </div>
                </div>
            </x-card>

            <x-card padding class="border-zinc-200 space-y-3">
                <div class="text-xs font-bold uppercase tracking-wider text-zinc-400">Ekstrakurikuler</div>
                <div class="divide-y divide-zinc-100">
                    @forelse($snapshot['extracurriculars'] ?? [] as $ekskul)
                        <div class="py-2.5 flex items-center justify-between text-xs">
                            <div>
                                <span class="font-bold text-zinc-800">{{ $ekskul['name'] }}</span>
                                <div class="text-[11px] text-zinc-500">{{ $ekskul['description'] ?? '-' }}</div>
                            </div>
                            <x-badge variant="brand" size="xs">Nilai: {{ $ekskul['predicate'] ?? 'A' }}</x-badge>
                        </div>
                    @empty
                        <p class="text-xs text-zinc-400 italic py-3 text-center">Tidak ada ekstrakurikuler yang terdaftar.</p>
                    @endforelse
                </div>
            </x-card>
        </div>

        <!-- Homeroom Notes -->
        <x-card padding class="border-zinc-200 space-y-2 bg-gradient-to-r from-zinc-50 to-white">
            <div class="text-xs font-bold uppercase tracking-wider text-zinc-400">Pesan & Catatan Wali Kelas</div>
            <div class="text-xs text-zinc-700 italic leading-relaxed">
                "{{ $snapshot['homeroom_notes'] ?: 'Teruslah bersemangat dalam menuntut ilmu, kembangkan potensi bakat dan jaga integritas akhlak mulia dalam setiap langkah pembelajaran.' }}"
            </div>
            <div class="pt-2 text-[11px] text-zinc-400">
                Wali Kelas: <span class="font-semibold text-zinc-700">{{ $snapshot['homeroom_teacher']['name'] }}</span>
            </div>
        </x-card>
    </div>
</x-layouts.app>
