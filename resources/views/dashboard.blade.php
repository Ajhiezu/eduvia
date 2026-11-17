<x-layouts.app>
    <x-slot:headerTitle>Dashboard</x-slot:headerTitle>
    @php
        $dashboardUser = auth()->user();
        $isStudentDashboard = $dashboardUser->hasRole(\App\Enums\UserRole::STUDENT);
        $isTeacherDashboard = $dashboardUser->hasRole(\App\Enums\UserRole::TEACHER);
        $isAdminDashboard = $dashboardUser->hasRole(\App\Enums\UserRole::SUPER_ADMIN, \App\Enums\UserRole::SCHOOL_ADMIN);
        $dashboardLabel = match (true) {
            $isStudentDashboard => 'Dashboard Siswa',
            $isTeacherDashboard => 'Dashboard Guru',
            $isAdminDashboard => 'Dashboard Administrator',
            default => 'Dashboard Staf',
        };
        $heroRoute = $isStudentDashboard ? 'student.schedule.index' : ($isTeacherDashboard ? 'teacher.schedule.index' : 'dashboard');
        $summaryStats = match (true) {
            $isStudentDashboard => [
                ['Rombongan Belajar', $enrollment?->classroom?->name ?? 'Belum terdaftar'],
                ['Tingkat Kehadiran', ($attendanceSummary['total'] ?? 0) > 0 ? ($attendanceSummary['rate'].'%') : 'Belum ada data'],
                ['Tahun Ajaran', $academicYear?->name ?? 'Belum diatur'],
                ['Semester', $semester?->name ?? 'Belum diatur'],
            ],
            $isTeacherDashboard => [
                ['Penugasan Mengajar', ($teachingAssignments ?? collect())->count()],
                ['Sesi Presensi', $totalTeacherSessions ?? 0],
                ['Sesi Selesai', $finalizedTeacherSessions ?? 0],
                ['Wali Kelas', ($homeroomClassrooms ?? collect())->count()],
            ],
            $isAdminDashboard => [
                ['Peserta Didik', number_format($totalStudents ?? 0, 0, ',', '.')],
                ['Tenaga Pendidik', number_format($totalTeachers ?? 0, 0, ',', '.')],
                ['Rombongan Belajar', $totalClassrooms ?? 0],
                ['Pendaftar Baru', $totalApplicants ?? 0],
            ],
            default => [],
        };
    @endphp

    <div class="dashboard-workspace">
        <div class="dashboard-primary">
            <div class="dashboard-toolbar">
                <h2>Beranda <span>/</span> {{ $dashboardLabel }}</h2>
                <a href="{{ route('dashboard') }}" aria-label="Segarkan data dashboard">Segarkan data &circlearrowright;</a>
            </div>

            <section class="dashboard-hero" aria-label="Ruang akademik Eduvia">
                <img src="{{ asset('images/edmunhigh/hero-students.jpg') }}" alt="" fetchpriority="high" />
                <div class="dashboard-hero-content">
                    <p>{{ $academicYear?->name ?? 'Eduvia' }} &middot; {{ $semester?->name ?? 'Ruang Akademik' }}</p>
                    <h2>{{ $isTeacherDashboard ? 'Mendampingi belajar, membangun masa depan.' : ($isAdminDashboard ? 'Ruang tumbuh untuk seluruh warga sekolah.' : 'Selamat belajar. Saatnya melangkah lebih jauh.') }}</h2>
                    @if ($isAdminDashboard)
                        <a href="{{ route('school.profile') }}">Kelola Profil Sekolah <span aria-hidden="true">&nearr;</span></a>
                    @elseif ($isStudentDashboard || $isTeacherDashboard)
                        <a href="{{ route($heroRoute) }}">Lihat jadwal {{ $isTeacherDashboard ? 'mengajar' : 'pelajaran' }} <span aria-hidden="true">&nearr;</span></a>
                    @endif
                </div>
            </section>

            @if ($summaryStats)
                <dl class="dashboard-stat-strip">
                    @foreach ($summaryStats as [$label, $value])
                        <div><dt>{{ $label }}</dt><dd>{{ $value }}</dd></div>
                    @endforeach
                </dl>
            @endif

            @if ($isStudentDashboard)
                <div class="dashboard-overview">
                    <section class="dashboard-panel">
                        <div class="dashboard-section-heading"><h2>Presensi</h2><a href="{{ route('attendance.my') }}" class="dashboard-text-link">Lihat semua &nearr;</a></div>
                        @foreach (['present' => 'Hadir', 'late' => 'Terlambat', 'permit' => 'Izin', 'sick' => 'Sakit', 'absent' => 'Tanpa keterangan'] as $status => $label)
                            <div class="dashboard-attendance-line"><span class="dashboard-attendance-mark" aria-hidden="true">{{ mb_substr($label, 0, 1) }}</span><strong>{{ $label }}</strong><b>{{ $attendanceSummary[$status] ?? 0 }}</b></div>
                        @endforeach
                    </section>
                    <section class="dashboard-panel">
                        <div class="dashboard-section-heading"><h2>Ringkasan Kehadiran</h2></div>
                        @if (($attendanceSummary['total'] ?? 0) > 0)
                            <div class="dashboard-donut" style="--progress: {{ max(0, min(100, (float) $attendanceSummary['rate'])) }}" role="img" aria-label="Kehadiran {{ $attendanceSummary['rate'] }} persen">
                                <div><strong>{{ $attendanceSummary['rate'] }}%</strong><span>kehadiran</span></div>
                            </div>
                            <p class="dashboard-donut-note">{{ ($attendanceSummary['present'] ?? 0) + ($attendanceSummary['late'] ?? 0) }} dari {{ $attendanceSummary['total'] }} sesi tercatat hadir.<br>Termasuk kehadiran terlambat.</p>
                        @else
                            <div class="dashboard-donut" aria-hidden="true"><div><strong>&mdash;</strong><span>belum ada data</span></div></div>
                            <p class="dashboard-donut-note">Ringkasan akan tampil setelah<br>presensi pertama Anda dicatat.</p>
                        @endif
                    </section>
                </div>
            @elseif ($isAdminDashboard)
                <section class="dashboard-panel">
                    <div class="dashboard-section-heading"><h2>Aktivitas Presensi Terbaru</h2><a href="{{ route('attendance-sessions.index') }}" class="dashboard-text-link">Lihat semua &nearr;</a></div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs">
                            <thead><tr class="border-b border-zinc-200 text-zinc-500"><th class="pb-4 font-medium">Kelas</th><th class="pb-4 font-medium">Guru</th><th class="pb-4 font-medium">Tanggal</th><th class="pb-4 font-medium">Status</th></tr></thead>
                            <tbody>
                                @forelse (($recentSessions ?? collect()) as $sessionItem)
                                    <tr class="border-b border-zinc-100"><td class="py-4 pr-4 font-medium">{{ $sessionItem->classroom?->name ?? '-' }}</td><td class="py-4 pr-4">{{ $sessionItem->teacher?->name ?? '-' }}</td><td class="py-4 pr-4 whitespace-nowrap">{{ $sessionItem->date?->translatedFormat('d M Y') ?? '-' }}</td><td class="py-4"><x-badge :variant="$sessionItem->status === 'finalized' ? 'success' : 'neutral'">{{ $sessionItem->status === 'finalized' ? 'Selesai' : 'Draf' }}</x-badge></td></tr>
                                @empty
                                    <tr><td colspan="4" class="dashboard-empty">Belum ada aktivitas presensi yang tercatat.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </section>
            @endif

            <div class="dashboard-existing">
        @if (auth()->user()->hasRole(\App\Enums\UserRole::STUDENT))
            <!-- Student Main Split Grid -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Left 2 Columns -->
                <div class="lg:col-span-2 space-y-6">
                    <!-- Student Academic Profile Card -->
                    <details class="dashboard-disclosure"><summary>Biodata & Status Siswa</summary>
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
</details>

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


            </div>

        <!-- ================================================================== -->
        <!-- ROLE 2: DASHBOARD GURU (TEACHER VIEW)                              -->
        <!-- ================================================================== -->
        @elseif (auth()->user()->hasRole(\App\Enums\UserRole::TEACHER))
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


            </div>

        <!-- ================================================================== -->
        <!-- ROLE 3: DASHBOARD ADMINISTRATOR (SUPER_ADMIN & SCHOOL_ADMIN)      -->
        <!-- ================================================================== -->
        @elseif ($isAdminDashboard)
            <!-- Admin Main Split Grid -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Left 2 Columns -->
                <div class="lg:col-span-2 space-y-6">
                    <!-- Modul 1 Status Card -->
                    <details class="dashboard-disclosure"><summary>Pengaturan & Informasi Sekolah</summary>
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
</details>

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
                        <details class="dashboard-disclosure"><summary>Pemeriksaan Akses Administrator</summary>
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
</details>
                    @endif
                </div>


            </div>
        @else
            <x-card title="Ruang Kerja Staf">
                <p class="text-sm text-zinc-500">Gunakan menu di samping untuk mengakses layanan sesuai penugasan Anda.</p>
            </x-card>
        @endif
            </div>
        </div>
        <x-dashboard-profile :user="$dashboardUser" :school="$school" :academic-year="$academicYear" :semester="$semester" :enrollment="$enrollment ?? null" :teaching-assignments="$teachingAssignments ?? null" />
    </div>
</x-layouts.app>
