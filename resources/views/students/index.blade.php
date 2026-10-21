<x-layouts.app>
    <x-slot:headerTitle>
        Manajemen Kesiswaan
    </x-slot:headerTitle>

    <div class="space-y-6">
        <!-- Alerts -->
        @if (session('status'))
            <x-alert variant="success" dismissible>
                {{ session('status') }}
            </x-alert>
        @endif

        @if ($errors->any())
            <x-alert variant="danger" dismissible>
                {{ $errors->first() }}
            </x-alert>
        @endif

        <!-- Action & Navigation Header -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h1 class="text-xl sm:text-2xl font-extrabold text-zinc-900 tracking-tight">
                    Manajemen Kesiswaan & Peserta Didik
                </h1>
                <p class="text-xs text-zinc-500 mt-0.5">
                    Kelola data induk peserta didik, status akademik, siklus mutasi, dan rekapitulasi kesiswaan.
                </p>
            </div>

            <div class="flex items-center gap-2.5 flex-wrap">
                <x-button href="{{ route('students.export.csv', request()->query()) }}" variant="outline" size="sm">
                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                    </svg>
                    Ekspor CSV
                </x-button>

                <x-button href="{{ route('students.import.index') }}" variant="outline" size="sm">
                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                    </svg>
                    Impor CSV
                </x-button>

                <x-button href="{{ route('students.report.index') }}" variant="secondary" size="sm">
                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                    Laporan Kesiswaan
                </x-button>

                <x-button href="{{ route('students.promotion.index') }}" variant="secondary" size="sm">
                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                    </svg>
                    Kenaikan Kelas & Kelulusan
                </x-button>

                <x-button href="{{ route('students.create') }}" variant="primary" size="sm">
                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                    </svg>
                    Registrasi Siswa Baru
                </x-button>
            </div>
        </div>

        <!-- 4-Card Metis Metric Summary Strip -->
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-2.5 sm:gap-4">
            <x-card padding class="border-zinc-200">
                <div class="text-[11px] font-bold uppercase tracking-wider text-emerald-600">Siswa Aktif</div>
                <div class="mt-1 text-2xl font-extrabold text-emerald-700">{{ $totalActiveCount }}</div>
                <div class="mt-1 text-xs text-zinc-400">Terdaftar aktif di sekolah</div>
            </x-card>

            <x-card padding class="border-zinc-200">
                <div class="text-[11px] font-bold uppercase tracking-wider text-amber-600">Pindah / Mutasi</div>
                <div class="mt-1 text-2xl font-extrabold text-amber-700">{{ $totalTransferredCount }}</div>
                <div class="mt-1 text-xs text-zinc-400">Mutasi ke sekolah lain</div>
            </x-card>

            <x-card padding class="border-zinc-200">
                <div class="text-[11px] font-bold uppercase tracking-wider text-blue-600">Alumni / Lulus</div>
                <div class="mt-1 text-2xl font-extrabold text-blue-700">{{ $totalGraduatedCount }}</div>
                <div class="mt-1 text-xs text-zinc-400">Telah lulus pendidikan</div>
            </x-card>

            <x-card padding class="border-zinc-200">
                <div class="text-[11px] font-bold uppercase tracking-wider text-rose-600">Keluar / Non-Aktif</div>
                <div class="mt-1 text-2xl font-extrabold text-rose-700">{{ $totalDroppedCount }}</div>
                <div class="mt-1 text-xs text-zinc-400">Mengundurkan diri / drop out</div>
            </x-card>
        </div>

        <!-- Filter Card -->
        <x-card padding>
            <form method="GET" action="{{ route('students.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-3 items-center">
                <!-- Search Input -->
                <div class="sm:col-span-2 lg:col-span-3">
                    <input
                        type="search"
                        name="search"
                        value="{{ $search }}"
                        placeholder="Cari nama siswa, NIS, NISN, atau email..."
                        class="w-full rounded-lg border border-zinc-200 text-zinc-900 text-sm py-2.5 px-3.5 focus:border-[#E11D2E] focus:ring-2 focus:ring-[#E11D2E]/20 placeholder-zinc-400"
                    >
                </div>

                <!-- Status Filter -->
                <div class="lg:col-span-3">
                    <select
                        name="status"
                        onchange="this.form.submit()"
                        class="w-full rounded-lg border border-zinc-200 text-zinc-900 text-sm py-2.5 px-3 focus:border-[#E11D2E] focus:ring-2 focus:ring-[#E11D2E]/20"
                    >
                        <option value="">Semua Status</option>
                        <option value="active" {{ $status === 'active' ? 'selected' : '' }}>Aktif</option>
                        <option value="transferred" {{ $status === 'transferred' ? 'selected' : '' }}>Pindah / Mutasi</option>
                        <option value="graduated" {{ $status === 'graduated' ? 'selected' : '' }}>Lulus</option>
                        <option value="dropped" {{ $status === 'dropped' ? 'selected' : '' }}>Keluar / Dropped</option>
                    </select>
                </div>

                <!-- Classroom Filter -->
                <div class="lg:col-span-3">
                    <select
                        name="classroom_id"
                        onchange="this.form.submit()"
                        class="w-full rounded-lg border border-zinc-200 text-zinc-900 text-sm py-2.5 px-3 focus:border-[#E11D2E] focus:ring-2 focus:ring-[#E11D2E]/20"
                    >
                        <option value="">Semua Rombel</option>
                        @foreach ($classrooms as $c)
                            <option value="{{ $c->id }}" {{ $classroomId == $c->id ? 'selected' : '' }}>
                                {{ $c->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Grade Level Filter -->
                <div class="lg:col-span-3">
                    <select
                        name="grade_level_id"
                        onchange="this.form.submit()"
                        class="w-full rounded-lg border border-zinc-200 text-zinc-900 text-sm py-2.5 px-3 focus:border-[#E11D2E] focus:ring-2 focus:ring-[#E11D2E]/20"
                    >
                        <option value="">Semua Tingkat Kelas</option>
                        @foreach ($gradeLevels as $gl)
                            <option value="{{ $gl->id }}" {{ $gradeLevelId == $gl->id ? 'selected' : '' }}>
                                {{ $gl->displayLabel() }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </form>
        </x-card>

        <!-- Students Table Card -->
        <x-card :padding="false">
            @if ($students->isEmpty())
                <div class="p-8 text-center">
                    <div class="w-12 h-12 mx-auto rounded-full bg-zinc-100 flex items-center justify-center text-zinc-400 mb-3">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                        </svg>
                    </div>
                    <h4 class="text-sm font-bold text-zinc-800">Tidak Ada Data Siswa Ditemukan</h4>
                    <p class="text-xs text-zinc-500 max-w-sm mx-auto mt-1 mb-4">
                        Tidak ada data peserta didik yang cocok dengan filter pencarian saat ini.
                    </p>
                    <x-button href="{{ route('students.create') }}" variant="primary" size="sm">
                        Registrasi Siswa Baru
                    </x-button>
                </div>
            @else
                <div class="overflow-x-auto" data-scrollable>
                    <table class="w-full min-w-[720px] text-left text-xs text-zinc-600">
                        <thead class="bg-zinc-50/80 text-zinc-500 font-semibold border-b border-zinc-200">
                            <tr>
                                <th class="py-3 px-4 w-12 text-center">No</th>
                                <th class="py-3 px-4">Nama Peserta Didik</th>
                                <th class="py-3 px-4">NIS / NISN</th>
                                <th class="py-3 px-4">Kelas Aktif</th>
                                <th class="py-3 px-4">Status Siswa</th>
                                <th class="py-3 px-4">Data Kontak / Wali</th>
                                <th class="py-3 px-4 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-200">
                            @foreach ($students as $index => $item)
                                @php
                                    $profile = $item->studentProfile;
                                    $currentClass = $item->classroomEnrollments->where('enrollment_status', 'active')->first()?->classroom;
                                @endphp
                                <tr class="hover:bg-zinc-50/50 transition-colors">
                                    <td class="py-3 px-4 text-center text-zinc-400 font-medium">
                                        {{ $students->firstItem() + $index }}
                                    </td>
                                    <td class="py-3 px-4">
                                        <div class="flex items-center gap-2.5">
                                            <div class="w-7 h-7 rounded-full bg-zinc-200 text-zinc-700 font-bold flex items-center justify-center text-xs shrink-0">
                                                {{ substr($item->name, 0, 1) }}
                                            </div>
                                            <div>
                                                <a href="{{ route('students.show', $item) }}" class="font-bold text-zinc-900 hover:text-brand-600 transition-colors block">
                                                    {{ $item->name }}
                                                </a>
                                                <span class="text-[11px] text-zinc-400 block">{{ $item->email }}</span>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="py-3 px-4 font-mono text-zinc-700">
                                        <div>{{ $profile?->nis ?: '-' }}</div>
                                        <div class="text-[10px] text-zinc-400">{{ $profile?->nisn ? "NISN: {$profile->nisn}" : '-' }}</div>
                                    </td>
                                    <td class="py-3 px-4">
                                        @if ($currentClass)
                                            <span class="font-bold text-zinc-800">{{ $currentClass->name }}</span>
                                            <span class="text-[10px] text-zinc-400 block">{{ $currentClass->gradeLevel?->displayLabel() }}</span>
                                        @else
                                            <span class="text-zinc-400 italic">Belum ditempatkan</span>
                                        @endif
                                    </td>
                                    <td class="py-3 px-4">
                                        <x-badge :variant="$profile?->statusBadgeVariant() ?? 'neutral'" size="sm">
                                            {{ $profile?->statusLabel() ?? 'Aktif' }}
                                        </x-badge>
                                    </td>
                                    <td class="py-3 px-4 text-zinc-600">
                                        @if ($profile?->parent_name)
                                            <div>{{ $profile->parent_name }} <span class="text-[10px] text-zinc-400">({{ $profile->parent_relationship ?? 'Wali' }})</span></div>
                                            <div class="text-[10px] text-zinc-400 font-mono">{{ $profile->parent_phone ?: ($item->phone ?: '-') }}</div>
                                        @else
                                            <span class="text-zinc-400">-</span>
                                        @endif
                                    </td>
                                    <td class="py-3 px-4 text-right">
                                        <x-button href="{{ route('students.show', $item) }}" variant="secondary" size="sm">
                                            Profil Detail &rarr;
                                        </x-button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @if ($students->hasPages())
                    <div class="p-4 border-t border-zinc-200">
                        {{ $students->links() }}
                    </div>
                @endif
            @endif
        </x-card>
    </div>
</x-layouts.app>
