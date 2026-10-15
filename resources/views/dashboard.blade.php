<x-layouts.app>
    <x-slot:headerTitle>
        Dashboard
    </x-slot:headerTitle>

    <div class="space-y-6">

        <!-- ================================================================== -->
        <!-- TOP TITLE & QUICK ACTION BAR (ROLE-TAILORED)                      -->
        <!-- ================================================================== -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-2 border-b border-zinc-200/80">
            <div>
                <h1 class="text-2xl font-extrabold text-zinc-900 tracking-tight">
                    @if (auth()->user()->hasRole(\App\Enums\UserRole::STUDENT))
                        Dashboard Siswa
                    @elseif (auth()->user()->hasRole(\App\Enums\UserRole::TEACHER))
                        Dashboard Guru
                    @else
                        Dashboard Administrator
                    @endif
                </h1>
                <p class="text-xs text-zinc-500 mt-1">
                    @if (auth()->user()->hasRole(\App\Enums\UserRole::STUDENT))
                        Selamat datang kembali, <strong class="text-zinc-800">{{ auth()->user()->name }}</strong>. Semangat belajar dan pantau aktivitas akademik Anda.
                    @elseif (auth()->user()->hasRole(\App\Enums\UserRole::TEACHER))
                        Selamat datang kembali, <strong class="text-zinc-800">{{ auth()->user()->name }}</strong>. Kelola pembelajaran, rombongan belajar, dan presensi siswa.
                    @else
                        Selamat datang kembali, <strong class="text-zinc-800">{{ auth()->user()->name }}</strong>. Ikhtisar operasional sekolah dan fondasi platform Eduvia.
                    @endif
                </p>
            </div>

            <!-- Role Action Group -->
            <div class="flex items-center gap-2 flex-wrap">
                <a
                    href="{{ route('dashboard') }}"
                    class="p-2 rounded-lg border border-zinc-200 bg-white hover:bg-zinc-50 text-zinc-600 transition-colors shadow-2xs"
                    title="Segarkan Data"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                    </svg>
                </a>

                @if (auth()->user()->hasRole(\App\Enums\UserRole::SUPER_ADMIN, \App\Enums\UserRole::SCHOOL_ADMIN))
                    <a
                        href="{{ route('settings.system') }}"
                        class="p-2 rounded-lg border border-zinc-200 bg-white hover:bg-zinc-50 text-zinc-600 transition-colors shadow-2xs"
                        title="Pengaturan Sistem"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                    </a>

                    <x-button href="{{ route('school.profile') }}" variant="primary" size="sm">
                        <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        <span>Kelola Profil Sekolah</span>
                    </x-button>
                @elseif (auth()->user()->hasRole(\App\Enums\UserRole::TEACHER))
                    <x-button href="{{ route('attendance-sessions.create') }}" variant="primary" size="sm">
                        <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        <span>Buat Sesi Presensi</span>
                    </x-button>
                @elseif (auth()->user()->hasRole(\App\Enums\UserRole::STUDENT))
                    <x-button href="{{ route('attendance.my') }}" variant="primary" size="sm">
                        <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <span>Presensi Saya</span>
                    </x-button>
                @endif
            </div>
        </div>

        <!-- ================================================================== -->
        <!-- ROLE 1: DASHBOARD SISWA (STUDENT VIEW)                             -->
        <!-- ================================================================== -->
        @if (auth()->user()->hasRole(\App\Enums\UserRole::STUDENT))
            <!-- 4-Column Student Metric Strip -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <!-- Metric 1: Rombongan Belajar -->
                <div class="bg-white rounded-xl border border-zinc-200 p-5 flex flex-col justify-between transition-all duration-150 hover:border-zinc-300">
                    <div class="flex items-start justify-between">
                        <div>
                            <p class="text-[11px] font-bold uppercase tracking-wider text-zinc-400">Rombongan Belajar</p>
                            <h3 class="text-lg font-bold text-zinc-900 mt-1 truncate" title="{{ $enrollment?->classroom?->name ?? 'Belum Terdaftar' }}">
                                {{ $enrollment?->classroom?->name ?? 'Belum Terdaftar' }}
                            </h3>
                        </div>
                        <div class="w-10 h-10 rounded-lg bg-zinc-100 text-zinc-600 flex items-center justify-center shrink-0">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                            </svg>
                        </div>
                    </div>
                    <div class="mt-4 pt-3 border-t border-zinc-100 flex items-center justify-between text-xs">
                        <span class="text-zinc-500">NISN: <strong>{{ $studentProfile?->nisn ?? '-' }}</strong></span>
                        <span class="inline-flex items-center gap-1 text-emerald-600 font-semibold">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                            {{ ucfirst($enrollment?->enrollment_status ?? 'Aktif') }}
                        </span>
                    </div>
                </div>

                <!-- Metric 2: Tingkat Kehadiran -->
                <div class="bg-white rounded-xl border border-zinc-200 p-5 flex flex-col justify-between transition-all duration-150 hover:border-zinc-300">
                    <div class="flex items-start justify-between">
                        <div>
                            <p class="text-[11px] font-bold uppercase tracking-wider text-zinc-400">Tingkat Kehadiran</p>
                            <h3 class="text-2xl font-extrabold text-zinc-900 mt-1 tracking-tight">
                                {{ $attendanceSummary['rate'] ?? 100 }}%
                            </h3>
                        </div>
                        <div class="w-10 h-10 rounded-lg bg-brand-50 text-brand-600 flex items-center justify-center shrink-0">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                    </div>
                    <div class="mt-4 pt-3 border-t border-zinc-100 flex items-center justify-between text-xs">
                        <span class="text-zinc-500">Hadir: <strong>{{ ($attendanceSummary['present'] ?? 0) + ($attendanceSummary['late'] ?? 0) }}/{{ $attendanceSummary['total'] ?? 0 }} Sesi</strong></span>
                        <span class="inline-flex items-center gap-1 text-brand-600 font-semibold">
                            Presensi Aktif
                        </span>
                    </div>
                </div>

                <!-- Metric 3: Tahun Ajaran -->
                <div class="bg-white rounded-xl border border-zinc-200 p-5 flex flex-col justify-between transition-all duration-150 hover:border-zinc-300">
                    <div class="flex items-start justify-between">
                        <div>
                            <p class="text-[11px] font-bold uppercase tracking-wider text-zinc-400">Tahun Ajaran</p>
                            <h3 class="text-xl font-extrabold text-zinc-900 mt-1 tracking-tight">
                                {{ $academicYear?->name ?? '2025/2026' }}
                            </h3>
                        </div>
                        <div class="w-10 h-10 rounded-lg bg-zinc-100 text-zinc-600 flex items-center justify-center shrink-0">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                        </div>
                    </div>
                    <div class="mt-4 pt-3 border-t border-zinc-100 flex items-center justify-between text-xs">
                        <span class="text-zinc-500">Periode Akademik</span>
                        <span class="inline-flex items-center gap-1 text-emerald-600 font-semibold">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                            Berjalan
                        </span>
                    </div>
                </div>

                <!-- Metric 4: Semester Berjalan -->
                <div class="bg-white rounded-xl border border-zinc-200 p-5 flex flex-col justify-between transition-all duration-150 hover:border-zinc-300">
                    <div class="flex items-start justify-between">
                        <div>
                            <p class="text-[11px] font-bold uppercase tracking-wider text-zinc-400">Semester Berjalan</p>
                            <h3 class="text-xl font-extrabold text-zinc-900 mt-1 tracking-tight">
                                {{ $semester?->name ?? 'Semester Ganjil' }}
                            </h3>
                        </div>
                        <div class="w-10 h-10 rounded-lg bg-zinc-100 text-zinc-600 flex items-center justify-center shrink-0">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                            </svg>
                        </div>
                    </div>
                    <div class="mt-4 pt-3 border-t border-zinc-100 flex items-center justify-between text-xs">
                        <span class="text-zinc-500">Tipe: <strong>{{ ucfirst($semester?->semester_type?->value ?? 'Ganjil') }}</strong></span>
                        <span class="inline-flex items-center gap-1 text-emerald-600 font-semibold">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                            Aktif
                        </span>
                    </div>
                </div>
            </div>

            <!-- Student Main Split Grid -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Left 2 Columns -->
                <div class="lg:col-span-2 space-y-6">
                    <!-- Student Academic Profile Card -->
                    <x-card title="Biodata & Status Siswa" subtitle="Informasi registrasi akademik peserta didik resmi di Eduvia.">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                            <div class="p-3.5 rounded-lg bg-zinc-50/70 border border-zinc-100">
                                <span class="text-zinc-400 font-medium block text-[11px]">Nama Lengkap</span>
                                <span class="text-zinc-900 font-bold text-sm mt-0.5 block">{{ auth()->user()->name }}</span>
                            </div>
                            <div class="p-3.5 rounded-lg bg-zinc-50/70 border border-zinc-100">
                                <span class="text-zinc-400 font-medium block text-[11px]">Nomor Induk Siswa Nasional (NISN)</span>
                                <span class="text-zinc-900 font-bold text-sm mt-0.5 block">{{ $studentProfile?->nisn ?? 'Belum tercatat' }}</span>
                            </div>
                            <div class="p-3.5 rounded-lg bg-zinc-50/70 border border-zinc-100">
                                <span class="text-zinc-400 font-medium block text-[11px]">Nomor Induk Siswa (NIS)</span>
                                <span class="text-zinc-800 font-semibold mt-0.5 block">{{ $studentProfile?->nis ?? '-' }}</span>
                            </div>
                            <div class="p-3.5 rounded-lg bg-zinc-50/70 border border-zinc-100">
                                <span class="text-zinc-400 font-medium block text-[11px]">Rombongan Belajar (Kelas)</span>
                                <span class="text-zinc-800 font-semibold mt-0.5 block">{{ $enrollment?->classroom?->name ?? 'Belum ditentukan' }}</span>
                            </div>
                            <div class="p-3.5 rounded-lg bg-zinc-50/70 border border-zinc-100">
                                <span class="text-zinc-400 font-medium block text-[11px]">Tingkat Kelas</span>
                                <span class="text-zinc-800 font-semibold mt-0.5 block">Kelas {{ $enrollment?->classroom?->gradeLevel?->name ?? '-' }}</span>
                            </div>
                            <div class="p-3.5 rounded-lg bg-zinc-50/70 border border-zinc-100">
                                <span class="text-zinc-400 font-medium block text-[11px]">Wali Kelas</span>
                                <span class="text-zinc-800 font-semibold mt-0.5 block">{{ $enrollment?->classroom?->homeroomTeacher?->name ?? 'Belum ditentukan' }}</span>
                            </div>
                        </div>
                    </x-card>

                    <!-- Student Attendance Summary Card -->
                    <x-card title="Riwayat Presensi Belajar Terbaru" subtitle="Catatan kehadiran Anda pada pertemuan tatap muka dan pembelajaran.">
                        <!-- 5-Metric Quick Bar -->
                        <div class="grid grid-cols-2 sm:grid-cols-5 gap-2.5 mb-4">
                            <div class="p-2.5 rounded-lg bg-emerald-50/80 border border-emerald-100 text-center">
                                <span class="text-[10px] font-bold uppercase tracking-wider text-emerald-600 block">Hadir</span>
                                <span class="text-base font-extrabold text-emerald-700 mt-0.5 block">{{ $attendanceSummary['present'] ?? 0 }}</span>
                            </div>
                            <div class="p-2.5 rounded-lg bg-amber-50/80 border border-amber-100 text-center">
                                <span class="text-[10px] font-bold uppercase tracking-wider text-amber-600 block">Terlambat</span>
                                <span class="text-base font-extrabold text-amber-700 mt-0.5 block">{{ $attendanceSummary['late'] ?? 0 }}</span>
                            </div>
                            <div class="p-2.5 rounded-lg bg-blue-50/80 border border-blue-100 text-center">
                                <span class="text-[10px] font-bold uppercase tracking-wider text-blue-600 block">Izin</span>
                                <span class="text-base font-extrabold text-blue-700 mt-0.5 block">{{ $attendanceSummary['permit'] ?? 0 }}</span>
                            </div>
                            <div class="p-2.5 rounded-lg bg-purple-50/80 border border-purple-100 text-center">
                                <span class="text-[10px] font-bold uppercase tracking-wider text-purple-600 block">Sakit</span>
                                <span class="text-base font-extrabold text-purple-700 mt-0.5 block">{{ $attendanceSummary['sick'] ?? 0 }}</span>
                            </div>
                            <div class="p-2.5 rounded-lg bg-rose-50/80 border border-rose-100 text-center col-span-2 sm:col-span-1">
                                <span class="text-[10px] font-bold uppercase tracking-wider text-rose-600 block">Alpa</span>
                                <span class="text-base font-extrabold text-rose-700 mt-0.5 block">{{ $attendanceSummary['absent'] ?? 0 }}</span>
                            </div>
                        </div>

                        @if (isset($recentAttendance) && $recentAttendance->isNotEmpty())
                            <div class="overflow-x-auto rounded-lg border border-zinc-200" data-scrollable>
                                <table class="w-full text-left text-xs text-zinc-600 min-w-[480px]">
                                    <thead class="bg-zinc-50 text-zinc-500 font-semibold border-b border-zinc-200">
                                        <tr>
                                            <th class="py-2.5 px-3">Tanggal</th>
                                            <th class="py-2.5 px-3">Mata Pelajaran</th>
                                            <th class="py-2.5 px-3">Guru</th>
                                            <th class="py-2.5 px-3">Status</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-zinc-200">
                                        @foreach ($recentAttendance as $item)
                                            <tr class="hover:bg-zinc-50/50">
                                                <td class="py-2.5 px-3 font-medium text-zinc-800">
                                                    {{ $item->session?->date ? $item->session->date->translatedFormat('d M Y') : '-' }}
                                                </td>
                                                <td class="py-2.5 px-3 font-bold text-zinc-900">
                                                    {{ $item->session?->teachingAssignment?->subject?->name ?? 'Mata Pelajaran' }}
                                                </td>
                                                <td class="py-2.5 px-3 text-zinc-600">
                                                    {{ $item->session?->teacher?->name ?? '-' }}
                                                </td>
                                                <td class="py-2.5 px-3">
                                                    <x-badge :variant="$item->statusBadgeVariant()" size="sm">
                                                        {{ $item->statusLabel() }}
                                                    </x-badge>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="p-6 text-center text-zinc-400 text-xs rounded-lg border border-dashed border-zinc-200">
                                Belum ada riwayat sesi presensi yang tercatat untuk akun Anda.
                            </div>
                        @endif

                        <div class="mt-4 pt-3 border-t border-zinc-100 flex items-center justify-between">
                            <span class="text-xs text-zinc-500">Lihat rincian riwayat presensi semua mata pelajaran</span>
                            <x-button href="{{ route('attendance.my') }}" variant="primary" size="sm">
                                Buka Rekap Presensi &rarr;
                            </x-button>
                        </div>
                    </x-card>
                </div>

                <!-- Right 1 Column -->
                <div class="space-y-6">
                    <!-- Student User Account Card -->
                    <x-card title="Informasi Akun Siswa">
                        <div class="flex items-center gap-3 pb-4 mb-4 border-b border-zinc-100">
                            <div class="w-12 h-12 rounded-xl bg-brand-600 text-white font-extrabold flex items-center justify-center text-base shadow-xs shrink-0">
                                {{ substr(auth()->user()->name, 0, 1) }}
                            </div>
                            <div class="min-w-0">
                                <h4 class="font-bold text-zinc-900 text-sm truncate">{{ auth()->user()->name }}</h4>
                                <p class="text-xs text-zinc-500 truncate">{{ auth()->user()->email }}</p>
                            </div>
                        </div>

                        <dl class="space-y-2.5 text-xs">
                            <div class="flex items-center justify-between pb-2 border-b border-zinc-100">
                                <dt class="text-zinc-500">Peran Akun</dt>
                                <dd>
                                    <x-badge :variant="auth()->user()->role->badgeVariant()" size="sm">
                                        {{ auth()->user()->role->label() }}
                                    </x-badge>
                                </dd>
                            </div>
                            <div class="flex items-center justify-between pb-2 border-b border-zinc-100">
                                <dt class="text-zinc-500">Status Akun</dt>
                                <dd>
                                    <x-badge variant="success" size="sm" dot>Aktif</x-badge>
                                </dd>
                            </div>
                            <div class="flex items-center justify-between pb-2 border-b border-zinc-100">
                                <dt class="text-zinc-500">Sekolah</dt>
                                <dd class="font-semibold text-zinc-800 text-right truncate max-w-[150px]" title="{{ $school?->name }}">
                                    {{ $school?->short_name ?? $school?->name ?? 'Nusantara Utama' }}
                                </dd>
                            </div>
                            <div class="flex items-center justify-between">
                                <dt class="text-zinc-500">NPSN</dt>
                                <dd class="font-semibold text-zinc-800">{{ $school?->npsn ?? '-' }}</dd>
                            </div>
                        </dl>
                    </x-card>

                    <!-- Student Quick Link Card -->
                    <x-card title="Pintasan Aktivitas Belajar">
                        <div class="space-y-2.5">
                            <a
                                href="{{ route('student.materials.index') }}"
                                class="flex items-center gap-3 p-3 rounded-lg border border-zinc-100 hover:border-brand-200 bg-zinc-50/50 hover:bg-brand-50/30 transition-all group"
                            >
                                <div class="w-8 h-8 rounded-lg bg-white border border-zinc-200 flex items-center justify-center text-brand-600 shadow-2xs group-hover:border-brand-300">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                                    </svg>
                                </div>
                                <div class="min-w-0 flex-1">
                                    <h5 class="text-xs font-bold text-zinc-900 group-hover:text-brand-700">Materi Pelajaran</h5>
                                    <p class="text-[11px] text-zinc-500 mt-0.5">Baca modul & tonton video materi ajar.</p>
                                </div>
                                <svg class="w-4 h-4 text-zinc-400 group-hover:text-brand-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                </svg>
                            </a>

                            <a
                                href="{{ route('student.assignments.index') }}"
                                class="flex items-center gap-3 p-3 rounded-lg border border-zinc-100 hover:border-brand-200 bg-zinc-50/50 hover:bg-brand-50/30 transition-all group"
                            >
                                <div class="w-8 h-8 rounded-lg bg-white border border-zinc-200 flex items-center justify-center text-brand-600 shadow-2xs group-hover:border-brand-300">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                                    </svg>
                                </div>
                                <div class="min-w-0 flex-1">
                                    <h5 class="text-xs font-bold text-zinc-900 group-hover:text-brand-700">Tugas & Evaluasi</h5>
                                    <p class="text-[11px] text-zinc-500 mt-0.5">Kerjakan tugas & lihat evaluasi nilai.</p>
                                </div>
                                <svg class="w-4 h-4 text-zinc-400 group-hover:text-brand-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                </svg>
                            </a>

                            <a
                                href="{{ route('attendance.my') }}"
                                class="flex items-center gap-3 p-3 rounded-lg border border-zinc-100 hover:border-brand-200 bg-zinc-50/50 hover:bg-brand-50/30 transition-all group"
                            >
                                <div class="w-8 h-8 rounded-lg bg-white border border-zinc-200 flex items-center justify-center text-brand-600 shadow-2xs group-hover:border-brand-300">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                </div>
                                <div class="min-w-0 flex-1">
                                    <h5 class="text-xs font-bold text-zinc-900 group-hover:text-brand-700">Presensi Saya</h5>
                                    <p class="text-[11px] text-zinc-500 mt-0.5">Lihat seluruh riwayat kehadiran Anda.</p>
                                </div>
                                <svg class="w-4 h-4 text-zinc-400 group-hover:text-brand-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                </svg>
                            </a>
                        </div>
                    </x-card>
                </div>
            </div>

        <!-- ================================================================== -->
        <!-- ROLE 2: DASHBOARD GURU (TEACHER VIEW)                              -->
        <!-- ================================================================== -->
        @elseif (auth()->user()->hasRole(\App\Enums\UserRole::TEACHER))
            <!-- 4-Column Teacher Metric Strip -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <!-- Metric 1: Wali Kelas -->
                <div class="bg-white rounded-xl border border-zinc-200 p-5 flex flex-col justify-between transition-all duration-150 hover:border-zinc-300">
                    <div class="flex items-start justify-between">
                        <div>
                            <p class="text-[11px] font-bold uppercase tracking-wider text-zinc-400">Wali Kelas</p>
                            <h3 class="text-lg font-bold text-zinc-900 mt-1 truncate">
                                {{ isset($homeroomClassrooms) && $homeroomClassrooms->isNotEmpty() ? $homeroomClassrooms->pluck('name')->join(', ') : 'Bukan Wali Kelas' }}
                            </h3>
                        </div>
                        <div class="w-10 h-10 rounded-lg bg-zinc-100 text-zinc-600 flex items-center justify-center shrink-0">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                            </svg>
                        </div>
                    </div>
                    <div class="mt-4 pt-3 border-t border-zinc-100 flex items-center justify-between text-xs">
                        <span class="text-zinc-500">Rombel Binaan</span>
                        <span class="inline-flex items-center gap-1 text-emerald-600 font-semibold">
                            {{ isset($homeroomClassrooms) && $homeroomClassrooms->isNotEmpty() ? 'Aktif' : 'Tenaga Pendidik' }}
                        </span>
                    </div>
                </div>

                <!-- Metric 2: Penugasan Mengajar -->
                <div class="bg-white rounded-xl border border-zinc-200 p-5 flex flex-col justify-between transition-all duration-150 hover:border-zinc-300">
                    <div class="flex items-start justify-between">
                        <div>
                            <p class="text-[11px] font-bold uppercase tracking-wider text-zinc-400">Jadwal Mengajar</p>
                            <h3 class="text-2xl font-extrabold text-zinc-900 mt-1 tracking-tight">
                                {{ isset($teachingAssignments) ? $teachingAssignments->count() : 0 }} Jadwal
                            </h3>
                        </div>
                        <div class="w-10 h-10 rounded-lg bg-zinc-100 text-zinc-600 flex items-center justify-center shrink-0">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                            </svg>
                        </div>
                    </div>
                    <div class="mt-4 pt-3 border-t border-zinc-100 flex items-center justify-between text-xs">
                        <span class="text-zinc-500">Mata Pelajaran Diampu</span>
                        <span class="inline-flex items-center gap-1 text-emerald-600 font-semibold">
                            Tersedia
                        </span>
                    </div>
                </div>

                <!-- Metric 3: Total Sesi Presensi -->
                <div class="bg-white rounded-xl border border-zinc-200 p-5 flex flex-col justify-between transition-all duration-150 hover:border-zinc-300">
                    <div class="flex items-start justify-between">
                        <div>
                            <p class="text-[11px] font-bold uppercase tracking-wider text-zinc-400">Sesi Presensi</p>
                            <h3 class="text-2xl font-extrabold text-zinc-900 mt-1 tracking-tight">
                                {{ $totalTeacherSessions ?? 0 }} Sesi
                            </h3>
                        </div>
                        <div class="w-10 h-10 rounded-lg bg-brand-50 text-brand-600 flex items-center justify-center shrink-0">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                            </svg>
                        </div>
                    </div>
                    <div class="mt-4 pt-3 border-t border-zinc-100 flex items-center justify-between text-xs">
                        <span class="text-zinc-500">Final: <strong>{{ $finalizedTeacherSessions ?? 0 }}</strong> &bull; Draft: <strong>{{ $draftTeacherSessions ?? 0 }}</strong></span>
                        <span class="inline-flex items-center gap-1 text-brand-600 font-semibold">
                            Tercatat
                        </span>
                    </div>
                </div>

                <!-- Metric 4: Semester Berjalan -->
                <div class="bg-white rounded-xl border border-zinc-200 p-5 flex flex-col justify-between transition-all duration-150 hover:border-zinc-300">
                    <div class="flex items-start justify-between">
                        <div>
                            <p class="text-[11px] font-bold uppercase tracking-wider text-zinc-400">Semester Berjalan</p>
                            <h3 class="text-xl font-extrabold text-zinc-900 mt-1 tracking-tight">
                                {{ $semester?->name ?? 'Semester Ganjil' }}
                            </h3>
                        </div>
                        <div class="w-10 h-10 rounded-lg bg-zinc-100 text-zinc-600 flex items-center justify-center shrink-0">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                        </div>
                    </div>
                    <div class="mt-4 pt-3 border-t border-zinc-100 flex items-center justify-between text-xs">
                        <span class="text-zinc-500">{{ $academicYear?->name ?? '2025/2026' }}</span>
                        <span class="inline-flex items-center gap-1 text-emerald-600 font-semibold">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                            Aktif
                        </span>
                    </div>
                </div>
            </div>

            <!-- Teacher Main Split Grid -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Left 2 Columns -->
                <div class="lg:col-span-2 space-y-6">
                    <!-- Teacher Sesi Presensi Terbaru -->
                    <x-card title="Sesi Presensi Pembelajaran Terbaru" subtitle="Daftar sesi pertemuan presensi yang Anda buat pada rombongan belajar.">
                        @if (isset($recentTeacherSessions) && $recentTeacherSessions->isNotEmpty())
                            <div class="overflow-x-auto rounded-lg border border-zinc-200" data-scrollable>
                                <table class="w-full text-left text-xs text-zinc-600 min-w-[540px]">
                                    <thead class="bg-zinc-50 text-zinc-500 font-semibold border-b border-zinc-200">
                                        <tr>
                                            <th class="py-2.5 px-3">Pertemuan & Mapel</th>
                                            <th class="py-2.5 px-3">Kelas</th>
                                            <th class="py-2.5 px-3">Tanggal</th>
                                            <th class="py-2.5 px-3">Status</th>
                                            <th class="py-2.5 px-3 text-right">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-zinc-200">
                                        @foreach ($recentTeacherSessions as $sessionItem)
                                            <tr class="hover:bg-zinc-50/50">
                                                <td class="py-2.5 px-3">
                                                    <span class="font-bold text-zinc-900 block">Pertemuan Ke-{{ $sessionItem->meeting_number }}</span>
                                                    <span class="text-[11px] text-brand-600 font-semibold block">{{ $sessionItem->teachingAssignment?->subject?->name ?? 'Mata Pelajaran' }}</span>
                                                </td>
                                                <td class="py-2.5 px-3 font-semibold text-zinc-800">
                                                    {{ $sessionItem->classroom?->name }}
                                                </td>
                                                <td class="py-2.5 px-3 text-zinc-600">
                                                    {{ $sessionItem->date ? $sessionItem->date->translatedFormat('d M Y') : '-' }}
                                                </td>
                                                <td class="py-2.5 px-3">
                                                    @if ($sessionItem->isFinalized())
                                                        <x-badge variant="success" size="sm">Final</x-badge>
                                                    @else
                                                        <x-badge variant="warning" size="sm">Draft</x-badge>
                                                    @endif
                                                </td>
                                                <td class="py-2.5 px-3 text-right">
                                                    <x-button href="{{ route('attendance-sessions.show', $sessionItem) }}" variant="secondary" size="sm">
                                                        Buka &rarr;
                                                    </x-button>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="p-8 text-center rounded-lg border border-dashed border-zinc-200">
                                <p class="text-xs text-zinc-500 mb-3">Belum ada sesi presensi yang dibuat.</p>
                                <x-button href="{{ route('attendance-sessions.create') }}" variant="primary" size="sm">
                                    Buat Sesi Presensi Pertama
                                </x-button>
                            </div>
                        @endif

                        <div class="mt-4 pt-3 border-t border-zinc-100 flex items-center justify-between">
                            <x-button href="{{ route('attendance-sessions.create') }}" variant="primary" size="sm">
                                + Buat Sesi Baru
                            </x-button>
                            <x-button href="{{ route('attendance-sessions.index') }}" variant="secondary" size="sm">
                                Lihat Semua Sesi Presensi &rarr;
                            </x-button>
                        </div>
                    </x-card>

                    <!-- Teacher Teaching Assignments Card -->
                    <x-card title="Jadwal & Penugasan Mengajar Anda" subtitle="Mata pelajaran dan rombongan belajar aktif pada tahun ajaran ini.">
                        @if (isset($teachingAssignments) && $teachingAssignments->isNotEmpty())
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                @foreach ($teachingAssignments as $assignment)
                                    <div class="p-3.5 rounded-lg border border-zinc-200/80 bg-zinc-50/50 hover:bg-white hover:border-zinc-300 transition-all flex items-center justify-between">
                                        <div>
                                            <h4 class="text-sm font-bold text-zinc-900">{{ $assignment->subject?->name ?? 'Mata Pelajaran' }}</h4>
                                            <p class="text-xs text-zinc-500 mt-0.5">Rombel: <strong>{{ $assignment->classroom?->name }}</strong> &bull; {{ $assignment->hours_per_week ?? 2 }} Jam/Minggu</p>
                                        </div>
                                        <x-badge variant="neutral" size="sm">Aktif</x-badge>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="p-6 text-center text-zinc-400 text-xs rounded-lg border border-dashed border-zinc-200">
                                Belum ada penugasan mengajar yang terdaftar untuk akun Anda.
                            </div>
                        @endif
                    </x-card>
                </div>

                <!-- Right 1 Column -->
                <div class="space-y-6">
                    <!-- Teacher Profile Card -->
                    <x-card title="Informasi Tenaga Pendidik">
                        <div class="flex items-center gap-3 pb-4 mb-4 border-b border-zinc-100">
                            <div class="w-12 h-12 rounded-xl bg-brand-600 text-white font-extrabold flex items-center justify-center text-base shadow-xs shrink-0">
                                {{ substr(auth()->user()->name, 0, 1) }}
                            </div>
                            <div class="min-w-0">
                                <h4 class="font-bold text-zinc-900 text-sm truncate">{{ auth()->user()->name }}</h4>
                                <p class="text-xs text-zinc-500 truncate">{{ auth()->user()->email }}</p>
                            </div>
                        </div>

                        <dl class="space-y-2.5 text-xs">
                            <div class="flex items-center justify-between pb-2 border-b border-zinc-100">
                                <dt class="text-zinc-500">Peran Sistem</dt>
                                <dd>
                                    <x-badge :variant="auth()->user()->role->badgeVariant()" size="sm">
                                        {{ auth()->user()->role->label() }}
                                    </x-badge>
                                </dd>
                            </div>
                            <div class="flex items-center justify-between pb-2 border-b border-zinc-100">
                                <dt class="text-zinc-500">Status Akun</dt>
                                <dd>
                                    <x-badge variant="success" size="sm" dot>Aktif</x-badge>
                                </dd>
                            </div>
                            <div class="flex items-center justify-between pb-2 border-b border-zinc-100">
                                <dt class="text-zinc-500">Sekolah</dt>
                                <dd class="font-semibold text-zinc-800 text-right truncate max-w-[150px]" title="{{ $school?->name }}">
                                    {{ $school?->short_name ?? $school?->name ?? 'Nusantara Utama' }}
                                </dd>
                            </div>
                            <div class="flex items-center justify-between">
                                <dt class="text-zinc-500">Akses Presensi</dt>
                                <dd class="font-semibold text-emerald-700">Penuh</dd>
                            </div>
                        </dl>
                    </x-card>

                    <!-- Teacher Quick Action Shortcuts -->
                    <x-card title="Pintasan Guru">
                        <div class="space-y-2.5">
                            <a
                                href="{{ route('learning-materials.index') }}"
                                class="flex items-center gap-3 p-3 rounded-lg border border-zinc-100 hover:border-brand-200 bg-zinc-50/50 hover:bg-brand-50/30 transition-all group"
                            >
                                <div class="w-8 h-8 rounded-lg bg-white border border-zinc-200 flex items-center justify-center text-brand-600 shadow-2xs group-hover:border-brand-300">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                                    </svg>
                                </div>
                                <div class="min-w-0 flex-1">
                                    <h5 class="text-xs font-bold text-zinc-900 group-hover:text-brand-700">Materi Pembelajaran</h5>
                                    <p class="text-[11px] text-zinc-500 mt-0.5">Kelola modul materi, video & dokumen ajar.</p>
                                </div>
                                <svg class="w-4 h-4 text-zinc-400 group-hover:text-brand-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                </svg>
                            </a>

                            <a
                                href="{{ route('assignments.index') }}"
                                class="flex items-center gap-3 p-3 rounded-lg border border-zinc-100 hover:border-brand-200 bg-zinc-50/50 hover:bg-brand-50/30 transition-all group"
                            >
                                <div class="w-8 h-8 rounded-lg bg-white border border-zinc-200 flex items-center justify-center text-brand-600 shadow-2xs group-hover:border-brand-300">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                                    </svg>
                                </div>
                                <div class="min-w-0 flex-1">
                                    <h5 class="text-xs font-bold text-zinc-900 group-hover:text-brand-700">Tugas & Evaluasi</h5>
                                    <p class="text-[11px] text-zinc-500 mt-0.5">Kelola penugasan & berikan penilaian siswa.</p>
                                </div>
                                <svg class="w-4 h-4 text-zinc-400 group-hover:text-brand-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                </svg>
                            </a>

                            <a
                                href="{{ route('attendance-sessions.create') }}"
                                class="flex items-center gap-3 p-3 rounded-lg border border-zinc-100 hover:border-brand-200 bg-zinc-50/50 hover:bg-brand-50/30 transition-all group"
                            >
                                <div class="w-8 h-8 rounded-lg bg-white border border-zinc-200 flex items-center justify-center text-brand-600 shadow-2xs group-hover:border-brand-300">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                    </svg>
                                </div>
                                <div class="min-w-0 flex-1">
                                    <h5 class="text-xs font-bold text-zinc-900 group-hover:text-brand-700">Buat Sesi Presensi</h5>
                                    <p class="text-[11px] text-zinc-500 mt-0.5">Catat kehadiran kelas tatap muka hari ini.</p>
                                </div>
                                <svg class="w-4 h-4 text-zinc-400 group-hover:text-brand-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                </svg>
                            </a>

                            <a
                                href="{{ route('attendance-sessions.index') }}"
                                class="flex items-center gap-3 p-3 rounded-lg border border-zinc-100 hover:border-brand-200 bg-zinc-50/50 hover:bg-brand-50/30 transition-all group"
                            >
                                <div class="w-8 h-8 rounded-lg bg-white border border-zinc-200 flex items-center justify-center text-brand-600 shadow-2xs group-hover:border-brand-300">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                                    </svg>
                                </div>
                                <div class="min-w-0 flex-1">
                                    <h5 class="text-xs font-bold text-zinc-900 group-hover:text-brand-700">Kelola Sesi Presensi</h5>
                                    <p class="text-[11px] text-zinc-500 mt-0.5">Daftar semua sesi presensi dan draft.</p>
                                </div>
                                <svg class="w-4 h-4 text-zinc-400 group-hover:text-brand-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                </svg>
                            </a>

                            <a
                                href="{{ route('attendance.report') }}"
                                class="flex items-center gap-3 p-3 rounded-lg border border-zinc-100 hover:border-brand-200 bg-zinc-50/50 hover:bg-brand-50/30 transition-all group"
                            >
                                <div class="w-8 h-8 rounded-lg bg-white border border-zinc-200 flex items-center justify-center text-brand-600 shadow-2xs group-hover:border-brand-300">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                    </svg>
                                </div>
                                <div class="min-w-0 flex-1">
                                    <h5 class="text-xs font-bold text-zinc-900 group-hover:text-brand-700">Rekap Presensi Siswa</h5>
                                    <p class="text-[11px] text-zinc-500 mt-0.5">Laporan agregat kehadiran per kelas.</p>
                                </div>
                                <svg class="w-4 h-4 text-zinc-400 group-hover:text-brand-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                </svg>
                            </a>
                        </div>
                    </x-card>
                </div>
            </div>

        <!-- ================================================================== -->
        <!-- ROLE 3: DASHBOARD ADMINISTRATOR (SUPER_ADMIN & SCHOOL_ADMIN)      -->
        <!-- ================================================================== -->
        @else
            <!-- 4-Column Admin Metric Strip -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <!-- Metric 1: Sekolah Aktif -->
                <div class="bg-white rounded-xl border border-zinc-200 p-5 flex flex-col justify-between transition-all duration-150 hover:border-zinc-300">
                    <div class="flex items-start justify-between">
                        <div>
                            <p class="text-[11px] font-bold uppercase tracking-wider text-zinc-400">Identitas Sekolah</p>
                            <h3 class="text-base font-bold text-zinc-900 mt-1 truncate" title="{{ $school?->name ?? 'Yayasan Perguruan Nusantara Utama' }}">
                                {{ $school?->short_name ?? $school?->name ?? 'Yayasan Nusantara Utama' }}
                            </h3>
                        </div>
                        <div class="w-10 h-10 rounded-lg bg-zinc-100 text-zinc-600 flex items-center justify-center shrink-0">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                            </svg>
                        </div>
                    </div>
                    <div class="mt-4 pt-3 border-t border-zinc-100 flex items-center justify-between text-xs">
                        <span class="text-zinc-500">NPSN: <strong>{{ $school?->npsn ?? '20104567' }}</strong></span>
                        <span class="inline-flex items-center gap-1 text-emerald-600 font-semibold">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                            Aktif
                        </span>
                    </div>
                </div>

                <!-- Metric 2: Tahun Ajaran Aktif -->
                <div class="bg-white rounded-xl border border-zinc-200 p-5 flex flex-col justify-between transition-all duration-150 hover:border-zinc-300">
                    <div class="flex items-start justify-between">
                        <div>
                            <p class="text-[11px] font-bold uppercase tracking-wider text-zinc-400">Tahun Ajaran</p>
                            <h3 class="text-xl font-extrabold text-zinc-900 mt-1 tracking-tight">
                                {{ $academicYear?->name ?? '2025/2026' }}
                            </h3>
                        </div>
                        <div class="w-10 h-10 rounded-lg bg-zinc-100 text-zinc-600 flex items-center justify-center shrink-0">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                        </div>
                    </div>
                    <div class="mt-4 pt-3 border-t border-zinc-100 flex items-center justify-between text-xs">
                        <span class="text-zinc-500">Periode Akademik</span>
                        <span class="inline-flex items-center gap-1 text-emerald-600 font-semibold">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                            Berjalan
                        </span>
                    </div>
                </div>

                <!-- Metric 3: Peserta Didik & Rombel -->
                <div class="bg-white rounded-xl border border-zinc-200 p-5 flex flex-col justify-between transition-all duration-150 hover:border-zinc-300">
                    <div class="flex items-start justify-between">
                        <div>
                            <p class="text-[11px] font-bold uppercase tracking-wider text-zinc-400">Peserta Didik</p>
                            <h3 class="text-xl font-extrabold text-zinc-900 mt-1 tracking-tight">
                                {{ $totalStudents ?? 0 }} Siswa
                            </h3>
                        </div>
                        <div class="w-10 h-10 rounded-lg bg-zinc-100 text-zinc-600 flex items-center justify-center shrink-0">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                            </svg>
                        </div>
                    </div>
                    <div class="mt-4 pt-3 border-t border-zinc-100 flex items-center justify-between text-xs">
                        <span class="text-zinc-500">Rombel: <strong>{{ $totalClassrooms ?? 0 }} Kelas</strong></span>
                        <span class="inline-flex items-center gap-1 text-emerald-600 font-semibold">
                            Kesiswaan
                        </span>
                    </div>
                </div>

                <!-- Metric 4: Pendaftaran PPDB / SPMB -->
                <div class="bg-white rounded-xl border border-zinc-200 p-5 flex flex-col justify-between transition-all duration-150 hover:border-zinc-300">
                    <div class="flex items-start justify-between">
                        <div>
                            <p class="text-[11px] font-bold uppercase tracking-wider text-zinc-400">Pendaftar PPDB</p>
                            <h3 class="text-xl font-extrabold text-zinc-900 mt-1 tracking-tight">
                                {{ $totalApplicants ?? 0 }} Calon
                            </h3>
                        </div>
                        <div class="w-10 h-10 rounded-lg bg-brand-50 text-brand-600 flex items-center justify-center shrink-0">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                            </svg>
                        </div>
                    </div>
                    <div class="mt-4 pt-3 border-t border-zinc-100 flex items-center justify-between text-xs">
                        <span class="text-zinc-500">Terverifikasi: <strong>{{ $verifiedApplicants ?? 0 }}</strong></span>
                        <span class="inline-flex items-center gap-1 text-brand-600 font-semibold">
                            PPDB / SPMB
                        </span>
                    </div>
                </div>
            </div>

            <!-- Admin Main Split Grid -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Left 2 Columns -->
                <div class="lg:col-span-2 space-y-6">
                    <!-- Modul 1 Status Card -->
                    <x-card title="Fondasi Sistem & Konfigurasi Sekolah (Modul 1)" subtitle="Modul operasional telah terintegrasi dan siap digunakan.">
                        <div class="space-y-3">
                            <!-- Item 1: Profil Sekolah -->
                            <div class="flex items-center justify-between p-3.5 rounded-lg border border-zinc-100 hover:border-zinc-200 bg-zinc-50/50 hover:bg-zinc-50 transition-all">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-lg bg-white border border-zinc-200 flex items-center justify-center text-zinc-700 shadow-2xs">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                                        </svg>
                                    </div>
                                    <div>
                                        <h4 class="text-sm font-bold text-zinc-900 leading-snug">Profil Identitas Sekolah</h4>
                                        <p class="text-xs text-zinc-500">Nama resmi, NPSN unik, kontak, multi-jenjang (SD/SMP/SMA/SMK), dan logo.</p>
                                    </div>
                                </div>
                                <div class="flex items-center gap-2">
                                    <x-badge variant="success" size="sm" dot>Lengkap</x-badge>
                                    <a href="{{ route('school.profile') }}" class="p-1.5 text-zinc-400 hover:text-zinc-700 rounded-lg hover:bg-white transition-colors">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                        </svg>
                                    </a>
                                </div>
                            </div>

                            <!-- Item 2: Tahun Ajaran -->
                            <div class="flex items-center justify-between p-3.5 rounded-lg border border-zinc-100 hover:border-zinc-200 bg-zinc-50/50 hover:bg-zinc-50 transition-all">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-lg bg-white border border-zinc-200 flex items-center justify-center text-zinc-700 shadow-2xs">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                        </svg>
                                    </div>
                                    <div>
                                        <h4 class="text-sm font-bold text-zinc-900 leading-snug">Tahun Ajaran Akademik</h4>
                                        <p class="text-xs text-zinc-500">Aktivasi atomik, validasi rentang tanggal, proteksi penghapusan data aktif.</p>
                                    </div>
                                </div>
                                <div class="flex items-center gap-2">
                                    <x-badge variant="success" size="sm" dot>Aktif ({{ $academicYear?->name ?? '2025/2026' }})</x-badge>
                                    <a href="{{ route('academic-years.index') }}" class="p-1.5 text-zinc-400 hover:text-zinc-700 rounded-lg hover:bg-white transition-colors">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                        </svg>
                                    </a>
                                </div>
                            </div>

                            <!-- Item 3: Semester -->
                            <div class="flex items-center justify-between p-3.5 rounded-lg border border-zinc-100 hover:border-zinc-200 bg-zinc-50/50 hover:bg-zinc-50 transition-all">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-lg bg-white border border-zinc-200 flex items-center justify-center text-zinc-700 shadow-2xs">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                        </svg>
                                    </div>
                                    <div>
                                        <h4 class="text-sm font-bold text-zinc-900 leading-snug">Semester Operasional</h4>
                                        <p class="text-xs text-zinc-500">Semester Ganjil & Genap dengan filter tahun ajaran dan aktivasi atomik.</p>
                                    </div>
                                </div>
                                <div class="flex items-center gap-2">
                                    <x-badge variant="success" size="sm" dot>Aktif ({{ $semester?->name ?? 'Ganjil' }})</x-badge>
                                    <a href="{{ route('semesters.index') }}" class="p-1.5 text-zinc-400 hover:text-zinc-700 rounded-lg hover:bg-white transition-colors">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                        </svg>
                                    </a>
                                </div>
                            </div>

                            <!-- Item 4: Pengaturan Sistem -->
                            <div class="flex items-center justify-between p-3.5 rounded-lg border border-zinc-100 hover:border-zinc-200 bg-zinc-50/50 hover:bg-zinc-50 transition-all">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-lg bg-white border border-zinc-200 flex items-center justify-center text-zinc-700 shadow-2xs">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        </svg>
                                    </div>
                                    <div>
                                        <h4 class="text-sm font-bold text-zinc-900 leading-snug">Pengaturan Dasar Sistem</h4>
                                        <p class="text-xs text-zinc-500">Zona waktu (Asia/Jakarta), bahasa baku (id), format penanggalan (d/m/Y).</p>
                                    </div>
                                </div>
                                <div class="flex items-center gap-2">
                                    <x-badge variant="neutral" size="sm">Terkonfigurasi</x-badge>
                                    <a href="{{ route('settings.system') }}" class="p-1.5 text-zinc-400 hover:text-zinc-700 rounded-lg hover:bg-white transition-colors">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                        </svg>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </x-card>

                    <!-- Academic, Students & PPDB Overview -->
                    <x-card title="Modul Akademik, Kesiswaan & PPDB" subtitle="Pusat kelola rombongan belajar, data siswa, dan penerimaan peserta didik baru.">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs">
                            <a href="{{ route('classrooms.index') }}" class="p-3.5 rounded-lg border border-zinc-200/80 bg-zinc-50/50 hover:bg-white hover:border-brand-200 transition-all flex items-center justify-between group">
                                <div class="flex items-center gap-2.5">
                                    <div class="w-8 h-8 rounded-lg bg-white border border-zinc-200 flex items-center justify-center text-zinc-700 shadow-2xs group-hover:border-brand-300 group-hover:text-brand-600">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                                        </svg>
                                    </div>
                                    <div>
                                        <h5 class="font-bold text-zinc-900 group-hover:text-brand-600">Rombongan Belajar</h5>
                                        <p class="text-[11px] text-zinc-500">Kelola kelas & penempatan</p>
                                    </div>
                                </div>
                                <span class="text-xs font-semibold text-zinc-700">&rarr;</span>
                            </a>

                            <a href="{{ route('students.index') }}" class="p-3.5 rounded-lg border border-zinc-200/80 bg-zinc-50/50 hover:bg-white hover:border-brand-200 transition-all flex items-center justify-between group">
                                <div class="flex items-center gap-2.5">
                                    <div class="w-8 h-8 rounded-lg bg-white border border-zinc-200 flex items-center justify-center text-zinc-700 shadow-2xs group-hover:border-brand-300 group-hover:text-brand-600">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                                        </svg>
                                    </div>
                                    <div>
                                        <h5 class="font-bold text-zinc-900 group-hover:text-brand-600">Data Siswa (Kesiswaan)</h5>
                                        <p class="text-[11px] text-zinc-500">Buku induk & biodata lengkap</p>
                                    </div>
                                </div>
                                <span class="text-xs font-semibold text-zinc-700">&rarr;</span>
                            </a>

                            <a href="{{ route('students.promotion.index') }}" class="p-3.5 rounded-lg border border-zinc-200/80 bg-zinc-50/50 hover:bg-white hover:border-brand-200 transition-all flex items-center justify-between group">
                                <div class="flex items-center gap-2.5">
                                    <div class="w-8 h-8 rounded-lg bg-white border border-zinc-200 flex items-center justify-center text-zinc-700 shadow-2xs group-hover:border-brand-300 group-hover:text-brand-600">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                                        </svg>
                                    </div>
                                    <div>
                                        <h5 class="font-bold text-zinc-900 group-hover:text-brand-600">Kenaikan & Kelulusan</h5>
                                        <p class="text-[11px] text-zinc-500">Pemrosesan batch akhir tahun</p>
                                    </div>
                                </div>
                                <span class="text-xs font-semibold text-zinc-700">&rarr;</span>
                            </a>

                            <a href="{{ route('admissions.dashboard') }}" class="p-3.5 rounded-lg border border-zinc-200/80 bg-zinc-50/50 hover:bg-white hover:border-brand-200 transition-all flex items-center justify-between group">
                                <div class="flex items-center gap-2.5">
                                    <div class="w-8 h-8 rounded-lg bg-white border border-zinc-200 flex items-center justify-center text-zinc-700 shadow-2xs group-hover:border-brand-300 group-hover:text-brand-600">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                                        </svg>
                                    </div>
                                    <div>
                                        <h5 class="font-bold text-zinc-900 group-hover:text-brand-600">Dashboard PPDB / SPMB</h5>
                                        <p class="text-[11px] text-zinc-500">Periode, verifikasi & konversi</p>
                                    </div>
                                </div>
                                <span class="text-xs font-semibold text-zinc-700">&rarr;</span>
                            </a>
                        </div>
                    </x-card>

                    <!-- Interactive Role Verification Card (Only For Super Admin) -->
                    @if (auth()->user()->isSuperAdmin())
                        <x-card title="Pengujian Otorisasi Role (Backend Security Gate)" subtitle="Verifikasi mekanisme pembatasan akses berbasis peran bagi Super Admin.">
                            <p class="text-xs text-zinc-500 mb-4 leading-relaxed">
                                Endpoint pengujian dilindungi middleware <code class="bg-zinc-100 px-1.5 py-0.5 rounded text-[11px] font-mono text-zinc-700">role:super_admin,school_admin</code> untuk memastikan otorisasi ditegakkan pada lapisan server.
                            </p>

                            <div x-data="{
                                testing: false,
                                result: null,
                                error: null,
                                async testAdminRoute() {
                                    this.testing = true;
                                    this.result = null;
                                    this.error = null;
                                    try {
                                        const res = await fetch('{{ route('demo.admin') }}', {
                                            headers: { 'Accept': 'application/json' }
                                        });
                                        const data = await res.json();
                                        if (res.ok) {
                                            this.result = 'Akses DITERIMA (HTTP ' + res.status + '): ' + data.message;
                                        } else {
                                            this.error = 'Akses DITOLAK (HTTP ' + res.status + '): ' + (data.message || 'Peran Anda tidak diizinkan.');
                                        }
                                    } catch (e) {
                                        this.error = 'Kesalahan jaringan: ' + e.message;
                                    } finally {
                                        this.testing = false;
                                    }
                                }
                            }">
                                <x-button
                                    type="button"
                                    size="sm"
                                    variant="outline"
                                    @click="testAdminRoute()"
                                    class="w-full sm:w-auto"
                                >
                                    <span x-show="!testing">Uji Akses Endpoint Admin</span>
                                    <span x-show="testing">Memeriksa Otorisasi...</span>
                                </x-button>

                                <div x-show="result" class="mt-3 p-3 rounded-lg bg-emerald-50 border border-emerald-200 text-xs text-emerald-800 font-medium" style="display: none;">
                                    <span x-text="result"></span>
                                </div>

                                <div x-show="error" class="mt-3 p-3 rounded-lg bg-rose-50 border border-rose-200 text-xs text-rose-800 font-medium" style="display: none;">
                                    <span x-text="error"></span>
                                </div>
                            </div>
                        </x-card>
                    @endif
                </div>

                <!-- Right 1 Column -->
                <div class="space-y-6">
                    <!-- User Profile Session Card -->
                    <x-card title="Informasi Sesi Pengguna">
                        <div class="flex items-center gap-3 pb-4 mb-4 border-b border-zinc-100">
                            <div class="w-12 h-12 rounded-xl bg-brand-600 text-white font-extrabold flex items-center justify-center text-base shadow-xs shrink-0">
                                {{ substr(auth()->user()->name, 0, 1) }}
                            </div>
                            <div class="min-w-0">
                                <h4 class="font-bold text-zinc-900 text-sm truncate">{{ auth()->user()->name }}</h4>
                                <p class="text-xs text-zinc-500 truncate">{{ auth()->user()->email }}</p>
                            </div>
                        </div>

                        <dl class="space-y-2.5 text-xs">
                            <div class="flex items-center justify-between pb-2 border-b border-zinc-100">
                                <dt class="text-zinc-500">Peran Sistem</dt>
                                <dd>
                                    <x-badge :variant="auth()->user()->role->badgeVariant()" size="sm">
                                        {{ auth()->user()->role->label() }}
                                    </x-badge>
                                </dd>
                            </div>
                            <div class="flex items-center justify-between pb-2 border-b border-zinc-100">
                                <dt class="text-zinc-500">Status Akun</dt>
                                <dd>
                                    <x-badge variant="success" size="sm" dot>Aktif</x-badge>
                                </dd>
                            </div>
                            <div class="flex items-center justify-between pb-2 border-b border-zinc-100">
                                <dt class="text-zinc-500">Metode Autentikasi</dt>
                                <dd class="font-semibold text-zinc-700">Session Cookie</dd>
                            </div>
                            <div class="flex items-center justify-between">
                                <dt class="text-zinc-500">Hak Kelola Sekolah</dt>
                                <dd class="font-semibold text-zinc-700">Penuh</dd>
                            </div>
                        </dl>
                    </x-card>

                    <!-- Quick Shortcuts for Admins -->
                    <x-card title="Pintasan Presensi & Kehadiran">
                        <div class="space-y-2.5">
                            <a
                                href="{{ route('attendance-sessions.index') }}"
                                class="flex items-center gap-3 p-3 rounded-lg border border-zinc-100 hover:border-brand-200 bg-zinc-50/50 hover:bg-brand-50/30 transition-all group"
                            >
                                <div class="w-8 h-8 rounded-lg bg-white border border-zinc-200 flex items-center justify-center text-brand-600 shadow-2xs group-hover:border-brand-300">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                                    </svg>
                                </div>
                                <div class="min-w-0 flex-1">
                                    <h5 class="text-xs font-bold text-zinc-900 group-hover:text-brand-700">Sesi Presensi Belajar</h5>
                                    <p class="text-[11px] text-zinc-500 mt-0.5">Kelola seluruh sesi tatap muka sekolah.</p>
                                </div>
                                <svg class="w-4 h-4 text-zinc-400 group-hover:text-brand-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                </svg>
                            </a>

                            <a
                                href="{{ route('attendance.report') }}"
                                class="flex items-center gap-3 p-3 rounded-lg border border-zinc-100 hover:border-brand-200 bg-zinc-50/50 hover:bg-brand-50/30 transition-all group"
                            >
                                <div class="w-8 h-8 rounded-lg bg-white border border-zinc-200 flex items-center justify-center text-brand-600 shadow-2xs group-hover:border-brand-300">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                    </svg>
                                </div>
                                <div class="min-w-0 flex-1">
                                    <h5 class="text-xs font-bold text-zinc-900 group-hover:text-brand-700">Rekap Presensi Sekolah</h5>
                                    <p class="text-[11px] text-zinc-500 mt-0.5">Laporan agregat kehadiran seluruh rombel.</p>
                                </div>
                                <svg class="w-4 h-4 text-zinc-400 group-hover:text-brand-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                </svg>
                            </a>
                        </div>
                    </x-card>

                    <!-- Help & Support Center Card -->
                    <x-card title="Pusat Bantuan & Layanan" subtitle="Panduan operasional & dukungan teknis Eduvia.">
                        <p class="text-xs text-zinc-500 mb-4 leading-relaxed">
                            Akses dokumentasi panduan penggunaan modul administrasi, e-learning, presensi, dan penilaian untuk seluruh staf pengajar dan tenaga kependidikan.
                        </p>
                        <a href="{{ route('school.public.contact', ['school' => $currentSchool?->slug ?? 'nusantara-utama']) }}" class="inline-flex items-center justify-center w-full px-4 py-2 text-xs font-semibold rounded-xl border border-zinc-200 text-zinc-700 hover:bg-zinc-50 hover:text-zinc-900 transition-colors">
                            <span>Hubungi Bantuan Dukungan &rarr;</span>
                        </a>
                    </x-card>
                </div>
            </div>
        @endif

    </div>
</x-layouts.app>
