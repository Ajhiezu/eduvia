<x-layouts.app>
    <x-slot:headerTitle>
        Laporan Kesiswaan
    </x-slot:headerTitle>

    <div class="space-y-6">
        <!-- Action & Navigation Header -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <div class="flex items-center gap-2">
                    <x-button href="{{ route('students.index') }}" variant="secondary" size="xs">
                        <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                        </svg>
                        Kembali ke Data Siswa
                    </x-button>
                    <span class="text-xs text-zinc-400">/</span>
                    <span class="text-xs font-semibold text-zinc-600">Laporan Rekapitulasi</span>
                </div>
                <h1 class="text-xl sm:text-2xl font-extrabold text-zinc-900 tracking-tight mt-1.5">
                    Laporan & Demografi Kesiswaan
                </h1>
                <p class="text-xs text-zinc-500 mt-0.5">
                    Ringkasan statistik peserta didik berdasarkan status operasional, gender, dan distribusi rombongan belajar.
                </p>
            </div>

            <div class="flex items-center gap-2 flex-wrap">
                <x-button onclick="window.print()" variant="secondary" size="sm">
                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                    </svg>
                    Cetak Ringkasan
                </x-button>
                <x-button href="{{ route('students.promotion.index') }}" variant="primary" size="sm">
                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                    </svg>
                    Kenaikan & Kelulusan
                </x-button>
            </div>
        </div>

        <!-- 4-Card Primary Status Metric Strip -->
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-2.5 sm:gap-4">
            <x-card padding class="border-zinc-200">
                <div class="text-[11px] font-bold uppercase tracking-wider text-emerald-600">Siswa Aktif</div>
                <div class="mt-1 text-2xl font-extrabold text-emerald-700">{{ number_format($activeCount) }}</div>
                <div class="mt-1 text-xs text-zinc-400">
                    {{ $totalStudents > 0 ? round(($activeCount / $totalStudents) * 100, 1) : 0 }}% dari total riwayat
                </div>
            </x-card>

            <x-card padding class="border-zinc-200">
                <div class="text-[11px] font-bold uppercase tracking-wider text-blue-600">Alumni / Lulus</div>
                <div class="mt-1 text-2xl font-extrabold text-blue-700">{{ number_format($graduatedCount) }}</div>
                <div class="mt-1 text-xs text-zinc-400">Menyelesaikan jenjang studi</div>
            </x-card>

            <x-card padding class="border-zinc-200">
                <div class="text-[11px] font-bold uppercase tracking-wider text-amber-600">Mutasi Keluar</div>
                <div class="mt-1 text-2xl font-extrabold text-amber-700">{{ number_format($transferredCount) }}</div>
                <div class="mt-1 text-xs text-zinc-400">Pindah ke sekolah lain</div>
            </x-card>

            <x-card padding class="border-zinc-200">
                <div class="text-[11px] font-bold uppercase tracking-wider text-zinc-500">Total Riwayat Terdata</div>
                <div class="mt-1 text-2xl font-extrabold text-zinc-800">{{ number_format($totalStudents) }}</div>
                <div class="mt-1 text-xs text-zinc-400">Keseluruhan entri siswa</div>
            </x-card>
        </div>

        <!-- Demographics Breakdown: Gender & Status Details -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Gender Breakdown Card -->
            <x-card padding class="border-zinc-200">
                <h3 class="text-sm font-bold text-zinc-900 tracking-tight flex items-center justify-between">
                    <span>Komposisi Jenis Kelamin</span>
                    <span class="text-xs font-normal text-zinc-500">{{ number_format($maleCount + $femaleCount) }} terverifikasi</span>
                </h3>
                <p class="text-xs text-zinc-500 mt-1">Perbandingan jumlah peserta didik laki-laki dan perempuan terdata.</p>

                @php
                    $genderTotal = $maleCount + $femaleCount;
                    $malePercent = $genderTotal > 0 ? round(($maleCount / $genderTotal) * 100, 1) : 0;
                    $femalePercent = $genderTotal > 0 ? round(($femaleCount / $genderTotal) * 100, 1) : 0;
                @endphp

                <div class="mt-4 space-y-4">
                    <!-- Progress Bar -->
                    <div class="h-3 w-full bg-zinc-100 rounded-full overflow-hidden flex">
                        <div class="bg-blue-600 h-full transition-all duration-300" style="width: {{ $malePercent }}%"></div>
                        <div class="bg-pink-500 h-full transition-all duration-300" style="width: {{ $femalePercent }}%"></div>
                    </div>

                    <div class="grid grid-cols-2 gap-4 pt-2">
                        <div class="p-3 bg-blue-50/60 rounded-lg border border-blue-100">
                            <div class="flex items-center gap-2">
                                <span class="w-2.5 h-2.5 rounded-full bg-blue-600"></span>
                                <span class="text-xs font-semibold text-blue-900">Laki-laki</span>
                            </div>
                            <div class="mt-2 text-xl font-bold text-blue-900">{{ number_format($maleCount) }}</div>
                            <div class="text-[11px] text-blue-700/80">{{ $malePercent }}% dari total</div>
                        </div>

                        <div class="p-3 bg-pink-50/60 rounded-lg border border-pink-100">
                            <div class="flex items-center gap-2">
                                <span class="w-2.5 h-2.5 rounded-full bg-pink-500"></span>
                                <span class="text-xs font-semibold text-pink-900">Perempuan</span>
                            </div>
                            <div class="mt-2 text-xl font-bold text-pink-900">{{ number_format($femaleCount) }}</div>
                            <div class="text-[11px] text-pink-700/80">{{ $femalePercent }}% dari total</div>
                        </div>
                    </div>
                </div>
            </x-card>

            <!-- Status Distribution Card -->
            <x-card padding class="border-zinc-200">
                <h3 class="text-sm font-bold text-zinc-900 tracking-tight">Status Operasional Siswa</h3>
                <p class="text-xs text-zinc-500 mt-1">Pembagian kategori keaktifan operasional peserta didik.</p>

                <div class="mt-4 space-y-2.5">
                    <div class="flex items-center justify-between text-xs py-1.5 border-b border-zinc-100">
                        <div class="flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                            <span class="font-medium text-zinc-700">Aktif Mengikuti Pembelajaran</span>
                        </div>
                        <span class="font-bold text-zinc-900">{{ number_format($activeCount) }} siswa</span>
                    </div>

                    <div class="flex items-center justify-between text-xs py-1.5 border-b border-zinc-100">
                        <div class="flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-blue-500"></span>
                            <span class="font-medium text-zinc-700">Lulus / Alumni</span>
                        </div>
                        <span class="font-bold text-zinc-900">{{ number_format($graduatedCount) }} siswa</span>
                    </div>

                    <div class="flex items-center justify-between text-xs py-1.5 border-b border-zinc-100">
                        <div class="flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                            <span class="font-medium text-zinc-700">Mutasi / Pindah Sekolah</span>
                        </div>
                        <span class="font-bold text-zinc-900">{{ number_format($transferredCount) }} siswa</span>
                    </div>

                    <div class="flex items-center justify-between text-xs py-1.5">
                        <div class="flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-rose-500"></span>
                            <span class="font-medium text-zinc-700">Dikeluarkan / Mengundurkan Diri</span>
                        </div>
                        <span class="font-bold text-zinc-900">{{ number_format($droppedCount) }} siswa</span>
                    </div>
                </div>
            </x-card>
        </div>

        <!-- Classroom Distribution Table -->
        <x-card padding class="border-zinc-200">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 pb-4 border-b border-zinc-100">
                <div>
                    <h2 class="text-sm font-bold text-zinc-900 tracking-tight">Distribusi Rombongan Belajar (Kelas Aktif)</h2>
                    <p class="text-xs text-zinc-500">Kapasitas dan jumlah peserta didik aktif per rombel.</p>
                </div>
                <div class="text-xs text-zinc-500">
                    Total {{ $classrooms->count() }} rombel aktif
                </div>
            </div>

            <div class="overflow-x-auto mt-4" data-scrollable>
                <table class="w-full text-left border-collapse text-xs min-w-[650px]">
                    <thead>
                        <tr class="border-b border-zinc-200 bg-zinc-50/75 text-zinc-600 font-semibold uppercase tracking-wider text-[11px]">
                            <th class="py-2.5 px-3">Rombel / Kelas</th>
                            <th class="py-2.5 px-3">Tingkat</th>
                            <th class="py-2.5 px-3">Tahun Ajaran</th>
                            <th class="py-2.5 px-3 text-center">Siswa Aktif</th>
                            <th class="py-2.5 px-3 text-center">Kapasitas</th>
                            <th class="py-2.5 px-3">Utilisasi</th>
                            <th class="py-2.5 px-3 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-100">
                        @forelse ($classrooms as $classroom)
                            @php
                                $cap = $classroom->capacity ?: 36;
                                $activeStudentsCount = $classroom->active_students_count;
                                $utilPercent = min(100, round(($activeStudentsCount / $cap) * 100));
                            @endphp
                            <tr class="hover:bg-zinc-50/50 transition-colors">
                                <td class="py-3 px-3">
                                    <div class="font-bold text-zinc-900">{{ $classroom->name }}</div>
                                    <div class="text-[11px] text-zinc-400">Kode: {{ $classroom->code ?? '-' }}</div>
                                </td>
                                <td class="py-3 px-3 text-zinc-700 font-medium">
                                    {{ $classroom->gradeLevel?->name ?? 'Tingkat ' . $classroom->grade_level }}
                                </td>
                                <td class="py-3 px-3 text-zinc-600">
                                    {{ $classroom->academicYear?->name ?? '-' }}
                                </td>
                                <td class="py-3 px-3 text-center">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full font-bold text-emerald-800 bg-emerald-50 border border-emerald-200">
                                        {{ $activeStudentsCount }}
                                    </span>
                                </td>
                                <td class="py-3 px-3 text-center text-zinc-600 font-medium">
                                    {{ $cap }}
                                </td>
                                <td class="py-3 px-3 w-40">
                                    <div class="space-y-1">
                                        <div class="flex items-center justify-between text-[11px]">
                                            <span class="font-medium text-zinc-600">{{ $utilPercent }}%</span>
                                            <span class="text-zinc-400">{{ $activeStudentsCount }}/{{ $cap }}</span>
                                        </div>
                                        <div class="w-full bg-zinc-100 rounded-full h-1.5 overflow-hidden">
                                            <div class="h-1.5 rounded-full {{ $utilPercent > 90 ? 'bg-amber-500' : 'bg-primary' }}" style="width: {{ $utilPercent }}%"></div>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-3 px-3 text-right">
                                    <a href="{{ route('students.index', ['classroom_id' => $classroom->id]) }}" class="inline-flex items-center text-primary hover:underline font-semibold text-xs">
                                        Lihat Siswa &rarr;
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="py-8 text-center text-zinc-400">
                                    Belum ada rombongan belajar aktif yang terdaftar.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-card>
    </div>
</x-layouts.app>
