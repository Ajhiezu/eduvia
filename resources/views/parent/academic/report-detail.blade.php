<x-layouts.app>
    <x-slot:headerTitle>
        Buku Rapor Digital - {{ $reportCard->student?->name }}
    </x-slot:headerTitle>

    <div class="max-w-5xl mx-auto space-y-6">

        <!-- Top Navigation & Action -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-4 border-b border-zinc-200">
            <div>
                <a href="{{ route('parent.reports') }}" class="text-xs text-zinc-500 hover:text-zinc-900 inline-flex items-center gap-1 font-semibold">
                    &larr; Kembali ke Daftar Rapor
                </a>
                <h1 class="text-xl sm:text-2xl font-extrabold text-zinc-900 mt-1">
                    Laporan Hasil Belajar (Rapor Semester)
                </h1>
                <p class="text-xs text-zinc-500">
                    {{ $reportCard->academicYear?->name }} &bull; {{ $reportCard->semester?->name }}
                </p>
            </div>

            <div class="flex items-center gap-2">
                <a
                    href="{{ route('reports.pdf.stream', $reportCard) }}"
                    target="_blank"
                    class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-xs font-bold border border-zinc-300 hover:bg-zinc-50 text-zinc-800 transition-colors"
                >
                    <svg class="w-4 h-4 text-zinc-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                    <span>Pratinjau PDF</span>
                </a>
                <a
                    href="{{ route('reports.pdf.download', $reportCard) }}"
                    class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-xs font-bold bg-[#E11D2E] hover:bg-[#C81423] text-white shadow-xs transition-colors"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                    <span>Unduh Lembar PDF</span>
                </a>
            </div>
        </div>

        <!-- Official Report Card Sheet -->
        <div class="bg-white rounded-2xl border border-zinc-200 shadow-sm p-6 sm:p-10 space-y-8">

            <!-- School & Student Header Info -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pb-6 border-b border-zinc-200 text-xs">
                <div class="space-y-1.5">
                    <p><span class="text-zinc-500 w-32 inline-block">Nama Peserta Didik:</span> <strong class="text-zinc-900 text-sm">{{ $reportCard->student?->name }}</strong></p>
                    <p><span class="text-zinc-500 w-32 inline-block">NIS / NISN:</span> <strong class="text-zinc-800">{{ $reportCard->student?->studentProfile?->nis ?? '-' }} / {{ $reportCard->student?->studentProfile?->nisn ?? '-' }}</strong></p>
                    <p><span class="text-zinc-500 w-32 inline-block">Nama Sekolah:</span> <strong class="text-zinc-800">{{ $reportCard->school?->name }}</strong></p>
                    <p><span class="text-zinc-500 w-32 inline-block">Alamat Sekolah:</span> <span class="text-zinc-700">{{ $reportCard->school?->address ?? '-' }}</span></p>
                </div>
                <div class="space-y-1.5 sm:text-right">
                    <p><span class="text-zinc-500 sm:w-28 sm:inline-block">Kelas:</span> <strong class="text-zinc-900">{{ $reportCard->classroom?->name }}</strong></p>
                    <p><span class="text-zinc-500 sm:w-28 sm:inline-block">Fase / Jenjang:</span> <span class="text-zinc-800">{{ $reportCard->classroom?->gradeLevel?->name ?? '-' }}</span></p>
                    <p><span class="text-zinc-500 sm:w-28 sm:inline-block">Semester:</span> <strong class="text-zinc-800">{{ $reportCard->semester?->name }}</strong></p>
                    <p><span class="text-zinc-500 sm:w-28 sm:inline-block">Tahun Ajaran:</span> <span class="text-zinc-800">{{ $reportCard->academicYear?->name }}</span></p>
                </div>
            </div>

            <!-- Subject Scores Table -->
            <div class="space-y-3">
                <h3 class="font-extrabold text-sm text-zinc-900 uppercase tracking-wider">A. Nilai Capaian Akademik & Pembelajaran</h3>
                <div class="overflow-x-auto border border-zinc-200 rounded-xl">
                    <table class="w-full text-left text-xs text-zinc-700">
                        <thead class="bg-zinc-50 border-b border-zinc-200 text-zinc-600 font-bold">
                            <tr>
                                <th class="px-3 py-2.5 w-10 text-center">No</th>
                                <th class="px-4 py-2.5">Mata Pelajaran</th>
                                <th class="px-4 py-2.5 text-center w-24">Nilai Akhir</th>
                                <th class="px-4 py-2.5 text-center w-20">Predikat</th>
                                <th class="px-4 py-2.5">Capaian Kompetensi / Catatan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-200">
                            @forelse ($reportCard->items as $idx => $item)
                                <tr class="hover:bg-zinc-50/50">
                                    <td class="px-3 py-3 text-center text-zinc-500 font-medium">{{ $idx + 1 }}</td>
                                    <td class="px-4 py-3 font-bold text-zinc-900">{{ $item->subject?->name ?? 'Mata Pelajaran' }}</td>
                                    <td class="px-4 py-3 text-center font-extrabold text-zinc-900 text-sm">{{ $item->final_score !== null ? round($item->final_score, 1) : '-' }}</td>
                                    <td class="px-4 py-3 text-center">
                                        <span class="px-2 py-0.5 rounded font-extrabold text-xs {{ $item->predicate === 'A' ? 'bg-emerald-50 text-emerald-800' : ($item->predicate === 'B' ? 'bg-blue-50 text-blue-800' : 'bg-zinc-100 text-zinc-700') }}">
                                            {{ $item->predicate ?? '-' }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-zinc-600 text-[11px] leading-relaxed">
                                        {{ $item->competency_description ?: 'Mencapai kompetensi pembelajaran dengan baik.' }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-4 py-6 text-center text-zinc-400">Belum ada data nilai mata pelajaran.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Attendance & Extracurriculars Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Extracurricular -->
                <div class="space-y-3">
                    <h3 class="font-extrabold text-sm text-zinc-900 uppercase tracking-wider">B. Kegiatan Ekstrakurikuler</h3>
                    <div class="border border-zinc-200 rounded-xl overflow-hidden text-xs">
                        <table class="w-full text-left">
                            <thead class="bg-zinc-50 border-b border-zinc-200 font-bold text-zinc-600">
                                <tr>
                                    <th class="px-3 py-2">Kegiatan</th>
                                    <th class="px-3 py-2 text-center w-20">Predikat</th>
                                    <th class="px-3 py-2">Keterangan</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-zinc-200">
                                @if (! empty($reportCard->extracurricular_evaluations) && is_array($reportCard->extracurricular_evaluations))
                                    @foreach ($reportCard->extracurricular_evaluations as $ekskul)
                                        <tr>
                                            <td class="px-3 py-2.5 font-semibold text-zinc-900">{{ $ekskul['name'] ?? '-' }}</td>
                                            <td class="px-3 py-2.5 text-center font-bold text-emerald-700">{{ $ekskul['grade'] ?? 'Baik' }}</td>
                                            <td class="px-3 py-2.5 text-zinc-600 text-[11px]">{{ $ekskul['description'] ?? '-' }}</td>
                                        </tr>
                                    @endforeach
                                @else
                                    <tr>
                                        <td colspan="3" class="px-3 py-4 text-center text-zinc-400">Tidak ada catatan ekstrakurikuler.</td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Attendance Summary -->
                <div class="space-y-3">
                    <h3 class="font-extrabold text-sm text-zinc-900 uppercase tracking-wider">C. Ketidakhadiran Semester</h3>
                    <div class="border border-zinc-200 rounded-xl p-4 text-xs space-y-2.5 bg-zinc-50/40">
                        @php $att = $reportCard->attendance_summary ?? []; @endphp
                        <div class="flex items-center justify-between pb-1.5 border-b border-zinc-200">
                            <span class="text-zinc-600">Sakit (S)</span>
                            <span class="font-bold text-zinc-900">{{ $att['sick'] ?? 0 }} hari</span>
                        </div>
                        <div class="flex items-center justify-between pb-1.5 border-b border-zinc-200">
                            <span class="text-zinc-600">Izin (I)</span>
                            <span class="font-bold text-zinc-900">{{ $att['permit'] ?? 0 }} hari</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-zinc-600">Tanpa Keterangan / Alpa (A)</span>
                            <span class="font-bold text-zinc-900">{{ $att['absent'] ?? 0 }} hari</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Homeroom Notes -->
            <div class="space-y-2 pt-2">
                <h3 class="font-extrabold text-sm text-zinc-900 uppercase tracking-wider">D. Catatan Wali Kelas</h3>
                <div class="p-4 rounded-xl border border-zinc-200 bg-zinc-50/60 text-xs text-zinc-800 leading-relaxed italic">
                    "{{ $reportCard->homeroom_notes ?: 'Terus tingkatkan semangat belajar, pertahankan prestasi akademik dan budi pekerti yang luhur.' }}"
                </div>
            </div>

            <!-- Signatures Strip -->
            <div class="pt-8 border-t border-zinc-200 grid grid-cols-2 sm:grid-cols-3 gap-6 text-center text-xs">
                <div>
                    <p class="text-zinc-500 mb-16">Orang Tua / Wali Murid</p>
                    <p class="font-bold text-zinc-900 underline">( {{ auth()->user()->name }} )</p>
                </div>
                <div class="hidden sm:block">
                    <p class="text-zinc-500 mb-16">Wali Kelas</p>
                    <p class="font-bold text-zinc-900 underline">( {{ $reportCard->homeroomTeacher?->name ?? '-' }} )</p>
                </div>
                <div>
                    <p class="text-zinc-500 mb-16">Mengetahui, Kepala Sekolah</p>
                    <p class="font-bold text-zinc-900 underline">( Kepala Sekolah )</p>
                </div>
            </div>

        </div>

    </div>
</x-layouts.app>
