<x-layouts.app>
    <x-slot:headerTitle>
        E-Rapor — Kelas {{ $classroom->name }}
    </x-slot:headerTitle>

    <div class="space-y-6">
        @if (session('success'))
            <x-alert variant="success" dismissible>
                {{ session('success') }}
            </x-alert>
        @endif

        @if (session('error'))
            <x-alert variant="danger" dismissible>
                {{ session('error') }}
            </x-alert>
        @endif

        @if (session('warning'))
            <x-alert variant="warning" dismissible>
                {{ session('warning') }}
            </x-alert>
        @endif

        <!-- Top Header & Actions -->
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
            <div>
                <a href="{{ route('reports.cards.index') }}" class="inline-flex items-center text-xs font-semibold text-zinc-500 hover:text-zinc-800 transition-colors mb-1.5">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                    Kembali ke Daftar Rombel
                </a>
                <h1 class="text-xl sm:text-2xl font-extrabold text-zinc-900 tracking-tight">
                    E-Rapor Digital — Kelas {{ $classroom->name }}
                </h1>
                <p class="text-xs text-zinc-500 mt-1">
                    Wali Kelas: <span class="font-semibold text-zinc-700">{{ $classroom->homeroomTeacher?->name ?? 'Belum Ditugaskan' }}</span> •
                    Tahun Ajaran: <span class="font-semibold text-zinc-700">{{ $classroom->academicYear?->name }}</span>
                </p>
            </div>

            <div class="flex flex-wrap items-center gap-2.5">
                <!-- Select Semester Form -->
                <form action="{{ route('reports.cards.classroom', $classroom) }}" method="GET" class="flex items-center gap-2">
                    <x-select name="semester_id" class="text-xs py-1.5" onchange="this.form.submit()">
                        @foreach($semesters as $sem)
                            <option value="{{ $sem->id }}" {{ $selectedSemesterId == $sem->id ? 'selected' : '' }}>
                                {{ $sem->name }} {{ $sem->is_active ? '(Aktif)' : '' }}
                            </option>
                        @endforeach
                    </x-select>
                </form>

                <!-- Bulk Sync Button -->
                <form action="{{ route('reports.cards.bulk-sync', $classroom) }}" method="POST">
                    @csrf
                    <input type="hidden" name="semester_id" value="{{ $selectedSemesterId }}">
                    <x-button type="submit" variant="secondary" size="sm" title="Tarik seluruh nilai terverifikasi dari guru mapel">
                        <svg class="w-4 h-4 mr-1.5 text-zinc-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                        </svg>
                        Kompilasi / Sinkronkan E-Rapor
                    </x-button>
                </form>

                <!-- Bulk Submit Button -->
                <form action="{{ route('reports.cards.bulk-submit', $classroom) }}" method="POST" onsubmit="return confirm('Ajukan seluruh buku rapor siswa yang telah siap untuk diverifikasi sekolah?');">
                    @csrf
                    <input type="hidden" name="semester_id" value="{{ $selectedSemesterId }}">
                    <x-button type="submit" variant="secondary" size="sm">
                        <svg class="w-4 h-4 mr-1.5 text-zinc-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        Ajukan Verifikasi Kelas
                    </x-button>
                </form>

                @if(auth()->user()->isSuperAdmin() || auth()->user()->isSchoolAdmin())
                    <!-- Admin Bulk Publish Button -->
                    <form action="{{ route('reports.cards.bulk-publish', $classroom) }}" method="POST" onsubmit="return confirm('Terbitkan seluruh buku rapor kelas ini secara resmi? Snapshot permanen akan dikunci.');">
                        @csrf
                        <input type="hidden" name="semester_id" value="{{ $selectedSemesterId }}">
                        <x-button type="submit" variant="primary" size="sm" class="bg-emerald-600 hover:bg-emerald-700">
                            <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            Terbitkan Seluruh Rapor
                        </x-button>
                    </form>
                @endif
            </div>
        </div>

        <!-- Student Report Cards Table -->
        <x-card padding="none" class="border-zinc-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-zinc-700">
                    <thead class="bg-zinc-50 border-b border-zinc-200 text-zinc-500 font-bold uppercase text-[10px] tracking-wider">
                        <tr>
                            <th class="py-3 px-3 w-10 text-center">No</th>
                            <th class="py-3 px-4">Nama Siswa</th>
                            <th class="py-3 px-4">NIS / NISN</th>
                            <th class="py-3 px-4 text-center">Mapel Terisi</th>
                            <th class="py-3 px-4 text-center">Rata-rata Nilai</th>
                            <th class="py-3 px-4 text-center">Presensi (S/I/A)</th>
                            <th class="py-3 px-4 text-center">Status Rapor</th>
                            <th class="py-3 px-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-200">
                        @forelse($classroom->activeStudents as $index => $enrollment)
                            @php
                                $student = $enrollment->student;
                                $report = $reportCards->get($student?->id);
                                $itemsCount = $report?->items->count() ?? 0;
                                $avgScore = $report ? $report->calculateAverageScore() : 0;
                                $att = $report?->attendance_summary ?? [];
                                $sick = $att['sick'] ?? 0;
                                $permit = $att['permit'] ?? 0;
                                $absent = $att['absent'] ?? 0;
                            @endphp
                            <tr class="hover:bg-zinc-50/75 transition-colors">
                                <td class="py-3.5 px-3 text-center text-zinc-400 font-medium">{{ $index + 1 }}</td>
                                <td class="py-3.5 px-4 font-semibold text-zinc-900">
                                    {{ $student?->name }}
                                </td>
                                <td class="py-3.5 px-4 text-zinc-600">
                                    {{ $student?->studentProfile?->nis ?? '-' }} / {{ $student?->studentProfile?->nisn ?? '-' }}
                                </td>
                                <td class="py-3.5 px-4 text-center font-bold text-zinc-800">
                                    {{ $itemsCount }} Mapel
                                </td>
                                <td class="py-3.5 px-4 text-center font-extrabold text-sm text-brand-700">
                                    {{ $itemsCount > 0 ? number_format($avgScore, 1) : '-' }}
                                </td>
                                <td class="py-3.5 px-4 text-center text-zinc-600">
                                    {{ $sick }} / {{ $permit }} / <span class="{{ $absent > 0 ? 'text-red-600 font-bold' : '' }}">{{ $absent }}</span>
                                </td>
                                <td class="py-3.5 px-4 text-center">
                                    @if($report)
                                        <x-badge :variant="$report->statusBadgeVariant()" size="xs">
                                            {{ $report->statusLabel() }}
                                        </x-badge>
                                    @else
                                        <x-badge variant="neutral" size="xs">Belum Dikompilasi</x-badge>
                                    @endif
                                </td>
                                <td class="py-3.5 px-4 text-right whitespace-nowrap">
                                    @if($report)
                                        <div class="flex items-center justify-end gap-1.5">
                                            <x-button href="{{ route('reports.cards.show', $report) }}" variant="ghost" size="xs">
                                                Buka Rapor
                                            </x-button>
                                            @if($report->isEditable())
                                                <x-button href="{{ route('reports.cards.edit', $report) }}" variant="secondary" size="xs">
                                                    Catatan Wali
                                                </x-button>
                                            @endif
                                            <a href="{{ route('reports.cards.pdf', $report) }}" target="_blank" class="inline-flex items-center p-1.5 text-zinc-500 hover:text-zinc-800 hover:bg-zinc-100 rounded" title="Unduh / Cetak PDF">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                                                </svg>
                                            </a>
                                        </div>
                                    @else
                                        <span class="text-[11px] text-zinc-400 italic">Klik tombol kompilasi di atas</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="py-8 text-center text-zinc-400">
                                    Tidak ada siswa aktif terdaftar pada rombel ini.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-card>
    </div>
</x-layouts.app>
