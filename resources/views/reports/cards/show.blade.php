<x-layouts.app>
    <x-slot:headerTitle>
        Buku Rapor — {{ $reportCard->student?->name }}
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

        @if ($reportCard->status === \App\Models\StudentReportCard::STATUS_REVISION_REQUESTED && $reportCard->revision_notes)
            <x-alert variant="danger">
                <strong>Catatan Revisi dari Pihak Sekolah:</strong> {{ $reportCard->revision_notes }}
            </x-alert>
        @endif

        <!-- Action Header -->
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
            <div>
                <a href="{{ route('reports.cards.classroom', $reportCard->classroom_id) }}" class="inline-flex items-center text-xs font-semibold text-zinc-500 hover:text-zinc-800 transition-colors mb-1.5">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                    Kembali ke Rombel Kelas
                </a>
                <div class="flex items-center gap-3">
                    <h1 class="text-xl sm:text-2xl font-extrabold text-zinc-900 tracking-tight">
                        Rapor Semester: {{ $reportCard->student?->name }}
                    </h1>
                    <x-badge :variant="$reportCard->statusBadgeVariant()" size="sm">
                        {{ $reportCard->statusLabel() }}
                    </x-badge>
                </div>
                <p class="text-xs text-zinc-500 mt-1">
                    NIS: <span class="font-semibold text-zinc-700">{{ $reportCard->student?->studentProfile?->nis ?? '-' }}</span> •
                    NISN: <span class="font-semibold text-zinc-700">{{ $reportCard->student?->studentProfile?->nisn ?? '-' }}</span> •
                    Kelas: <span class="font-semibold text-zinc-700">{{ $reportCard->classroom?->name }}</span> •
                    Periode: <span class="font-semibold text-zinc-700">{{ $reportCard->academicYear?->name }} ({{ $reportCard->semester?->name }})</span>
                </p>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                @if($reportCard->isEditable())
                    <form action="{{ route('reports.cards.sync', $reportCard) }}" method="POST">
                        @csrf
                        <x-button type="submit" variant="secondary" size="sm" title="Sinkronkan ulang data nilai dari gradebook mapel terverifikasi">
                            <svg class="w-4 h-4 mr-1.5 text-zinc-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                            </svg>
                            Sinkronkan Nilai
                        </x-button>
                    </form>

                    <x-button href="{{ route('reports.cards.edit', $reportCard) }}" variant="secondary" size="sm">
                        <svg class="w-4 h-4 mr-1.5 text-zinc-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                        </svg>
                        Edit Catatan & Nonakademik
                    </x-button>

                    <form action="{{ route('reports.cards.submit', $reportCard) }}" method="POST" onsubmit="return confirm('Ajukan buku rapor ini untuk verifikasi pihak sekolah?');">
                        @csrf
                        <x-button type="submit" variant="primary" size="sm">
                            <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            Ajukan Verifikasi
                        </x-button>
                    </form>
                @endif

                @if((auth()->user()->isSuperAdmin() || auth()->user()->isSchoolAdmin()) && $reportCard->status === \App\Models\StudentReportCard::STATUS_PENDING_REVIEW)
                    <form action="{{ route('reports.cards.approve', $reportCard) }}" method="POST">
                        @csrf
                        <x-button type="submit" variant="primary" size="sm" class="bg-blue-600 hover:bg-blue-700">
                            <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            Setujui Rapor
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

                @if((auth()->user()->isSuperAdmin() || auth()->user()->isSchoolAdmin()) && in_array($reportCard->status, [\App\Models\StudentReportCard::STATUS_APPROVED, \App\Models\StudentReportCard::STATUS_PENDING_REVIEW]))
                    <form action="{{ route('reports.cards.publish', $reportCard) }}" method="POST" onsubmit="return confirm('Terbitkan buku rapor ini secara resmi? Snapshot permanen akan dihasilkan dan siswa dapat melihat hasilnya.');">
                        @csrf
                        <x-button type="submit" variant="primary" size="sm" class="bg-emerald-600 hover:bg-emerald-700">
                            <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            Terbitkan Rapor Resmi
                        </x-button>
                    </form>
                @endif

                <!-- Print / PDF Button -->
                <x-button href="{{ route('reports.cards.pdf', $reportCard) }}" target="_blank" variant="secondary" size="sm">
                    <svg class="w-4 h-4 mr-1.5 text-zinc-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                    </svg>
                    Cetak / Unduh PDF
                </x-button>
            </div>
        </div>

        <!-- Academic Report Card Table -->
        <x-card padding="none" class="border-zinc-200 overflow-hidden">
            <div class="px-5 py-4 border-b border-zinc-200 bg-zinc-50/50 flex items-center justify-between">
                <div>
                    <h2 class="text-sm font-bold text-zinc-900">A. Nilai Capaian Kompetensi Akademik</h2>
                    <p class="text-xs text-zinc-500 mt-0.5">Dihimpun dari rekapitulasi buku nilai mata pelajaran yang telah terverifikasi.</p>
                </div>

                <div class="text-xs font-semibold text-zinc-700">
                    Rata-rata Nilai: <span class="text-base font-extrabold text-brand-700">{{ number_format($reportCard->calculateAverageScore(), 1) }}</span>
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
                            <th class="py-3 px-3 text-center">Ketuntasan</th>
                            <th class="py-3 px-4 min-w-[280px]">Deskripsi Capaian Pembelajaran</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-200">
                        @forelse($reportCard->items as $index => $item)
                            <tr class="hover:bg-zinc-50/75 transition-colors">
                                <td class="py-3 px-3 text-center text-zinc-400 font-medium">{{ $index + 1 }}</td>
                                <td class="py-3 px-4 font-semibold text-zinc-900">
                                    {{ $item->subject_name }}
                                    <div class="text-[11px] text-zinc-400 font-normal">Guru: {{ $item->teacher_name ?? '-' }}</div>
                                </td>
                                <td class="py-3 px-3 text-center font-bold text-zinc-700">{{ number_format($item->passing_grade, 0) }}</td>
                                <td class="py-3 px-3 text-center font-extrabold text-sm text-zinc-900 bg-zinc-50/40">
                                    {{ number_format($item->final_score, 1) }}
                                </td>
                                <td class="py-3 px-3 text-center">
                                    <x-badge :variant="$item->predicateBadgeVariant()" size="xs">
                                        {{ $item->predicate }}
                                    </x-badge>
                                </td>
                                <td class="py-3 px-3 text-center">
                                    @if($item->is_passed)
                                        <span class="text-[11px] font-bold text-emerald-600">✓ Tuntas</span>
                                    @else
                                        <span class="text-[11px] font-bold text-red-600">✗ Remidi</span>
                                    @endif
                                </td>
                                <td class="py-3 px-4 text-zinc-600 text-[11px] leading-relaxed">
                                    {{ $item->achievement_description ?: 'Capaian pembelajaran belum terisi.' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="py-8 text-center text-zinc-400">
                                    Belum ada nilai mata pelajaran yang dikompilasi pada rapor ini.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-card>

        <!-- Non-academic & Attendance Section -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Attendance Summary -->
            <x-card padding class="border-zinc-200 space-y-3">
                <div class="text-xs font-bold uppercase tracking-wider text-zinc-400">Rekapitulasi Presensi Kehadiran</div>
                @php
                    $att = $reportCard->attendance_summary ?? [];
                @endphp
                <div class="grid grid-cols-4 gap-2 text-center pt-1">
                    <div class="p-3 bg-zinc-50 rounded-lg border border-zinc-200">
                        <div class="text-[11px] text-zinc-500 font-medium">Hadir</div>
                        <div class="text-lg font-extrabold text-zinc-900 mt-0.5">{{ $att['present'] ?? 0 }}</div>
                        <div class="text-[10px] text-zinc-400">sesi</div>
                    </div>
                    <div class="p-3 bg-zinc-50 rounded-lg border border-zinc-200">
                        <div class="text-[11px] text-zinc-500 font-medium">Sakit</div>
                        <div class="text-lg font-extrabold text-zinc-900 mt-0.5">{{ $att['sick'] ?? 0 }}</div>
                        <div class="text-[10px] text-zinc-400">hari</div>
                    </div>
                    <div class="p-3 bg-zinc-50 rounded-lg border border-zinc-200">
                        <div class="text-[11px] text-zinc-500 font-medium">Izin</div>
                        <div class="text-lg font-extrabold text-zinc-900 mt-0.5">{{ $att['permit'] ?? 0 }}</div>
                        <div class="text-[10px] text-zinc-400">hari</div>
                    </div>
                    <div class="p-3 bg-zinc-50 rounded-lg border border-zinc-200">
                        <div class="text-[11px] text-zinc-500 font-medium">Alpa</div>
                        <div class="text-lg font-extrabold {{ ($att['absent'] ?? 0) > 0 ? 'text-red-600' : 'text-zinc-900' }} mt-0.5">{{ $att['absent'] ?? 0 }}</div>
                        <div class="text-[10px] text-zinc-400">hari</div>
                    </div>
                </div>
            </x-card>

            <!-- Extracurriculars -->
            <x-card padding class="border-zinc-200 space-y-3">
                <div class="text-xs font-bold uppercase tracking-wider text-zinc-400">Kegiatan Ekstrakurikuler</div>
                <div class="divide-y divide-zinc-100">
                    @forelse($reportCard->extracurricular_evaluations ?? [] as $ekskul)
                        <div class="py-2.5 flex items-center justify-between text-xs">
                            <div>
                                <span class="font-bold text-zinc-800">{{ $ekskul['name'] }}</span>
                                <div class="text-[11px] text-zinc-500">{{ $ekskul['description'] ?? '-' }}</div>
                            </div>
                            <x-badge variant="brand" size="xs">Nilai: {{ $ekskul['predicate'] ?? 'A' }}</x-badge>
                        </div>
                    @empty
                        <p class="text-xs text-zinc-400 italic py-3 text-center">Belum ada data ekstrakurikuler yang diinput.</p>
                    @endforelse
                </div>
            </x-card>
        </div>

        <!-- Homeroom Notes Box -->
        <x-card padding class="border-zinc-200 space-y-2 bg-gradient-to-r from-zinc-50 to-white">
            <div class="text-xs font-bold uppercase tracking-wider text-zinc-400">Catatan Wali Kelas</div>
            <div class="text-xs text-zinc-700 italic leading-relaxed">
                "{{ $reportCard->homeroom_notes ?: 'Teruslah bersemangat dalam menuntut ilmu, kembangkan potensi bakat dan jaga integritas akhlak mulia dalam setiap langkah pembelajaran.' }}"
            </div>
            <div class="pt-2 text-[11px] text-zinc-400">
                Wali Kelas: <span class="font-semibold text-zinc-700">{{ $reportCard->homeroomTeacher?->name }}</span>
            </div>
        </x-card>
    </div>

    <!-- Modal Minta Revisi (Admin Only) -->
    <div id="revisionModal" class="hidden fixed inset-0 z-50 overflow-y-auto bg-black/40 flex items-center justify-center p-4">
        <div class="bg-white rounded-xl shadow-xl max-w-md w-full p-6 space-y-4">
            <h3 class="text-base font-bold text-zinc-900">Kembalikan Rapor untuk Revisi</h3>
            <p class="text-xs text-zinc-500">
                Tuliskan catatan perbaikan untuk wali kelas mengenai rapor siswa ini.
            </p>

            <form action="{{ route('reports.cards.request-revision', $reportCard) }}" method="POST">
                @csrf
                <div>
                    <x-label for="revision_notes" value="Catatan Revisi" required />
                    <x-textarea id="revision_notes" name="revision_notes" rows="4" class="mt-1" placeholder="Contoh: Harap perbaiki catatan perkembangan atau periksa kembali presensi..." required></x-textarea>
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
