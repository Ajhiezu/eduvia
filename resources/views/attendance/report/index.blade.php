<x-layouts.app>
    <x-slot:headerTitle>
        Rekapitulasi Presensi Pembelajaran
    </x-slot:headerTitle>

    <div class="space-y-6">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <div class="flex items-center gap-2 text-xs text-zinc-500 mb-1">
                    <a href="{{ route('attendance-sessions.index') }}" class="hover:text-zinc-800">&larr; Kembali ke Daftar Sesi</a>
                </div>
                <h1 class="text-xl sm:text-2xl font-extrabold text-zinc-900 tracking-tight">
                    Rekapitulasi & Laporan Kehadiran Kelas
                </h1>
                <p class="text-xs text-zinc-500 mt-0.5">
                    Laporan persentase kehadiran per peserta didik dan matriks kehadiran kumulatif mata pelajaran.
                </p>
            </div>
        </div>

        <!-- Filter Selector Card -->
        <x-card padding>
            <form method="GET" action="{{ route('attendance.report') }}" class="flex flex-col sm:flex-row items-end gap-3">
                <div class="flex-1 w-full">
                    <label class="block text-xs font-bold text-zinc-700 mb-1">
                        Pilih Mata Pelajaran & Rombongan Belajar:
                    </label>
                    <select
                        name="teaching_assignment_id"
                        class="w-full rounded-lg border border-zinc-300 text-zinc-900 text-xs py-2 px-3 focus:border-brand-500 focus:ring-2 focus:ring-brand-100"
                        required
                    >
                        <option value="">-- Pilih Penugasan Pembelajaran --</option>
                        @foreach ($assignments as $a)
                            <option value="{{ $a->id }}" {{ $assignmentId == $a->id ? 'selected' : '' }}>
                                {{ $a->subject?->name }} &bull; Kelas {{ $a->classroom?->name }} (Guru: {{ $a->teacher?->name }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <x-button type="submit" variant="primary" size="md">
                    Tampilkan Rekap
                </x-button>
            </form>
        </x-card>

        @if ($selectedAssignment)
            <!-- Metric Strip for the Class -->
            <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
                <x-card padding class="border-zinc-200">
                    <div class="text-[11px] font-bold uppercase tracking-wider text-zinc-400">Mata Pelajaran</div>
                    <div class="mt-1 text-base font-extrabold text-zinc-900 truncate">
                        {{ $selectedAssignment->subject?->name }}
                    </div>
                    <div class="mt-1 text-xs text-zinc-500">Kelas {{ $selectedAssignment->classroom?->name }}</div>
                </x-card>

                <x-card padding class="border-zinc-200">
                    <div class="text-[11px] font-bold uppercase tracking-wider text-zinc-400">Total Sesi Dilaksanakan</div>
                    <div class="mt-1 text-2xl font-extrabold text-zinc-900">
                        {{ $sessions->count() }} <span class="text-xs font-normal text-zinc-500">Pertemuan</span>
                    </div>
                    <div class="mt-1 text-xs text-zinc-500">Semester Ganjil</div>
                </x-card>

                <x-card padding class="border-zinc-200">
                    <div class="text-[11px] font-bold uppercase tracking-wider text-zinc-400">Jumlah Siswa Terdaftar</div>
                    <div class="mt-1 text-2xl font-extrabold text-zinc-900">
                        {{ $studentsSummary->count() }} <span class="text-xs font-normal text-zinc-500">Siswa</span>
                    </div>
                    <div class="mt-1 text-xs text-zinc-500">Peserta didik aktif</div>
                </x-card>

                <x-card padding class="border-zinc-200">
                    <div class="text-[11px] font-bold uppercase tracking-wider text-brand-600">Rata-rata Kehadiran Kelas</div>
                    <div class="mt-1 text-2xl font-extrabold text-brand-700">
                        {{ $classAverageRate }}%
                    </div>
                    <div class="mt-1 text-[11px] text-zinc-400">Akumulasi seluruh siswa</div>
                </x-card>
            </div>

            <!-- Formula Documentation Callout -->
            <div class="p-3 bg-zinc-50 border border-zinc-200 rounded-xl text-xs text-zinc-700 flex items-start gap-2.5">
                <svg class="w-4 h-4 text-brand-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <div>
                    <span class="font-bold text-zinc-900">Metodologi & Rumus Penghitungan:</span>
                    Persentase kehadiran dihitung dengan formula baku:
                    <code class="px-1.5 py-0.5 rounded bg-zinc-200 text-zinc-800 font-mono text-[11px] font-bold">
                        Persentase = (Hadir + Terlambat) / Total Pertemuan &times; 100%
                    </code>.
                    Hari di mana siswa belum terdaftar tidak dihitung sebagai ketidakhadiran.
                </div>
            </div>

            <!-- Summary Table -->
            <x-card :padding="false">
                <div class="overflow-x-auto" data-scrollable>
                    <table class="w-full min-w-[800px] text-left text-xs text-zinc-600">
                        <thead class="bg-zinc-50/80 text-zinc-500 font-semibold border-b border-zinc-200">
                            <tr>
                                <th class="py-3 px-4 w-12 text-center">No</th>
                                <th class="py-3 px-4">Nama Peserta Didik</th>
                                <th class="py-3 px-4">NISN / Identitas</th>
                                <th class="py-3 px-3 text-center text-emerald-700 font-bold">Hadir (H)</th>
                                <th class="py-3 px-3 text-center text-amber-700 font-bold">Terlambat (T)</th>
                                <th class="py-3 px-3 text-center text-blue-700 font-bold">Izin (I)</th>
                                <th class="py-3 px-3 text-center text-purple-700 font-bold">Sakit (S)</th>
                                <th class="py-3 px-3 text-center text-rose-700 font-bold">Alpa (A)</th>
                                <th class="py-3 px-3 text-center font-bold">Total Sesi</th>
                                <th class="py-3 px-4 text-right font-bold">Persentase</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-200">
                            @forelse ($studentsSummary as $index => $item)
                                <tr class="hover:bg-zinc-50/50 transition-colors">
                                    <td class="py-3 px-4 text-center text-zinc-400 font-medium">
                                        {{ $index + 1 }}
                                    </td>
                                    <td class="py-3 px-4">
                                        <div class="font-bold text-zinc-900">{{ $item['student']->name }}</div>
                                        <div class="text-[10px] text-zinc-400">{{ $item['student']->email }}</div>
                                    </td>
                                    <td class="py-3 px-4 font-mono text-zinc-600">
                                        {{ $item['student']->nisn ?? $item['student']->username ?? '-' }}
                                    </td>
                                    <td class="py-3 px-3 text-center font-bold text-emerald-700">
                                        {{ $item['present'] }}
                                    </td>
                                    <td class="py-3 px-3 text-center font-bold text-amber-700">
                                        {{ $item['late'] }}
                                    </td>
                                    <td class="py-3 px-3 text-center font-bold text-blue-700">
                                        {{ $item['permit'] }}
                                    </td>
                                    <td class="py-3 px-3 text-center font-bold text-purple-700">
                                        {{ $item['sick'] }}
                                    </td>
                                    <td class="py-3 px-3 text-center font-bold text-rose-700">
                                        {{ $item['absent'] }}
                                    </td>
                                    <td class="py-3 px-3 text-center font-semibold text-zinc-800">
                                        {{ $item['total'] }}
                                    </td>
                                    <td class="py-3 px-4 text-right">
                                        <span class="inline-flex items-center gap-1.5 font-bold {{ $item['rate'] >= 85 ? 'text-emerald-700' : ($item['rate'] >= 75 ? 'text-amber-700' : 'text-rose-700') }}">
                                            {{ $item['rate'] }}%
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="10" class="py-8 text-center text-zinc-400">
                                        Tidak ada siswa terdaftar pada rombel ini.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </x-card>
        @else
            <!-- Empty State Prompt -->
            <x-card padding>
                <div class="p-8 text-center">
                    <div class="w-12 h-12 mx-auto rounded-full bg-zinc-100 flex items-center justify-center text-zinc-400 mb-3">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                        </svg>
                    </div>
                    <h3 class="text-sm font-bold text-zinc-900">Pilih Mata Pelajaran Terlebih Dahulu</h3>
                    <p class="text-xs text-zinc-500 max-w-sm mx-auto mt-1">
                        Pilih penugasan mengajar mata pelajaran dan kelas pada formulir di atas untuk menampilkan ringkasan rekapitulasi kehadiran.
                    </p>
                </div>
            </x-card>
        @endif
    </div>
</x-layouts.app>
