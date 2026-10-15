<x-layouts.app>
    <x-slot:headerTitle>
        Portal Orang Tua & Wali Murid
    </x-slot:headerTitle>

    <div class="space-y-6">

        <!-- ================================================================== -->
        <!-- TOP WELCOME & CHILD SWITCHER BAR                                   -->
        <!-- ================================================================== -->
        <div class="bg-white rounded-2xl border border-zinc-200/80 p-4 sm:p-6 shadow-xs">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                <div class="flex items-center gap-3.5">
                    <div class="w-12 h-12 rounded-2xl bg-brand-50 border border-brand-200 text-brand-600 flex items-center justify-center font-extrabold text-xl shadow-xs shrink-0">
                        {{ substr($parent->name, 0, 1) }}
                    </div>
                    <div>
                        <div class="flex items-center gap-2 flex-wrap">
                            <h1 class="text-xl sm:text-2xl font-extrabold text-zinc-900 tracking-tight">
                                Selamat Datang, {{ $parent->name }}
                            </h1>
                            <span class="px-2 py-0.5 text-[11px] font-bold rounded-full bg-zinc-100 text-zinc-700 border border-zinc-200">
                                Wali Murid Resmi
                            </span>
                        </div>
                        <p class="text-xs text-zinc-500 mt-1">
                            Pantau kemajuan belajar, presensi, tugas, nilai, dan komunikasi sekolah secara transparan & terpadu.
                        </p>
                    </div>
                </div>

                <!-- Child Picker Dropdown / Pill Switcher -->
                @if ($activeChildren->isNotEmpty())
                    <div class="flex items-center gap-2 self-start lg:self-auto flex-wrap bg-zinc-50 p-1.5 rounded-xl border border-zinc-200">
                        <span class="text-[11px] font-bold text-zinc-500 px-2 shrink-0">Fokus Pantau:</span>
                        @foreach ($activeChildren as $child)
                            <a
                                href="{{ route('parent.dashboard', ['child_id' => $child->id]) }}"
                                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-bold transition-all {{ $activeChild && $activeChild->id === $child->id ? 'bg-[#E11D2E] text-white shadow-xs' : 'text-zinc-700 hover:bg-white hover:text-zinc-900' }}"
                            >
                                <span>{{ $child->name }}</span>
                                @if ($child->classroomEnrollments->first()?->classroom)
                                    <span class="text-[10px] opacity-80">({{ $child->classroomEnrollments->first()->classroom->name }})</span>
                                @endif
                            </a>
                        @endforeach
                        <a
                            href="{{ route('parent.children.index') }}"
                            class="p-1.5 text-zinc-400 hover:text-zinc-700 transition-colors"
                            title="Kelola Data Anak / Tambah Anak"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        </a>
                    </div>
                @else
                    <a
                        href="{{ route('parent.children.index') }}"
                        class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-bold bg-[#E11D2E] hover:bg-[#C81423] text-white shadow-xs transition-colors shrink-0"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        <span>Tautkan Akun Anak</span>
                    </a>
                @endif
            </div>
        </div>

        @if (! $activeChild)
            <!-- ============================================================== -->
            <!-- EMPTY STATE: BELUM ADA ANAK TERHUBUNG                          -->
            <!-- ============================================================== -->
            <div class="bg-white rounded-2xl border border-dashed border-zinc-300 p-8 sm:p-12 text-center max-w-xl mx-auto shadow-xs">
                <div class="w-16 h-16 rounded-full bg-brand-50 text-brand-600 flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                </div>
                <h3 class="text-base font-extrabold text-zinc-900 mb-2">Belum Ada Akun Anak yang Terhubung</h3>
                <p class="text-xs text-zinc-500 mb-6 leading-relaxed">
                    Untuk mulai memantau kehadiran, nilai rapor, tugas, dan berkomunikasi dengan guru, hubungkan akun Anda dengan akun anak menggunakan kode penautan resmi dari sekolah.
                </p>
                <div class="flex flex-col sm:flex-row items-center justify-center gap-3">
                    <a
                        href="{{ route('parent.children.index') }}"
                        class="inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl text-xs font-bold bg-[#E11D2E] hover:bg-[#C81423] text-white shadow-xs transition-colors w-full sm:w-auto"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/></svg>
                        <span>Masukkan Kode Penautan</span>
                    </a>
                </div>
            </div>
        @else
            <!-- ============================================================== -->
            <!-- ACTIVE CHILD PROFILE BANNER & ACADEMIC STATUS                  -->
            <!-- ============================================================== -->
            <div class="bg-linear-to-r from-zinc-900 via-zinc-800 to-zinc-900 text-white rounded-2xl p-5 sm:p-6 shadow-md relative overflow-hidden">
                <div class="absolute -right-10 -bottom-10 w-48 h-48 bg-brand-600/10 rounded-full blur-2xl pointer-events-none"></div>

                <div class="relative z-10 flex flex-col md:flex-row md:items-center md:justify-between gap-5">
                    <div class="flex items-center gap-4">
                        <div class="w-16 h-16 rounded-2xl bg-white/10 border border-white/20 text-white flex items-center justify-center font-extrabold text-2xl shadow-inner shrink-0">
                            {{ substr($activeChild->name, 0, 2) }}
                        </div>
                        <div>
                            <div class="flex items-center gap-2 flex-wrap">
                                <h2 class="text-lg sm:text-xl font-extrabold tracking-tight">{{ $activeChild->name }}</h2>
                                <span class="px-2 py-0.5 rounded text-[11px] font-bold bg-brand-600 text-white">
                                    {{ $activeChild->studentProfile?->student_status === 'active' ? 'Siswa Aktif' : ucfirst($activeChild->studentProfile?->student_status ?? 'Aktif') }}
                                </span>
                            </div>
                            <div class="flex items-center gap-2 sm:gap-4 text-xs text-zinc-300 mt-1 flex-wrap">
                                <span>NIS: <strong class="text-white">{{ $activeChild->studentProfile?->nis ?? '-' }}</strong></span>
                                <span>&bull;</span>
                                <span>NISN: <strong class="text-white">{{ $activeChild->studentProfile?->nisn ?? '-' }}</strong></span>
                                <span>&bull;</span>
                                <span>Kelas: <strong class="text-emerald-400">{{ $enrollment?->classroom?->name ?? 'Belum Ditentukan' }}</strong></span>
                            </div>
                            <p class="text-[11px] text-zinc-400 mt-1">
                                Wali Kelas: <span class="text-zinc-200 font-semibold">{{ $enrollment?->classroom?->homeroomTeacher?->name ?? 'Belum Ditugaskan' }}</span>
                                &bull; {{ $enrollment?->classroom?->academicYear?->name ?? 'Tahun Ajaran Aktif' }}
                            </p>
                        </div>
                    </div>

                    <div class="flex items-center gap-2 shrink-0 flex-wrap">
                        <a
                            href="{{ route('conversations.create', ['child_id' => $activeChild->id]) }}"
                            class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-xs font-bold bg-white text-zinc-900 hover:bg-zinc-100 shadow-xs transition-colors"
                        >
                            <svg class="w-4 h-4 text-brand-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                            <span>Konsultasi Guru</span>
                        </a>
                        <a
                            href="{{ route('parent.reports') }}"
                            class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-xs font-bold bg-[#E11D2E] hover:bg-[#C81423] text-white shadow-xs transition-colors"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                            <span>Buku Rapor</span>
                        </a>
                    </div>
                </div>
            </div>

            <!-- ============================================================== -->
            <!-- STATS QUICK METRICS GRID (4 CARDS)                             -->
            <!-- ============================================================== -->
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
                <!-- 1. Tingkat Presensi -->
                <div class="bg-white rounded-2xl border border-zinc-200/80 p-4 sm:p-5 shadow-xs flex flex-col justify-between">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-xs font-bold text-zinc-500 uppercase tracking-wider">Tingkat Presensi</span>
                        <div class="w-8 h-8 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </div>
                    </div>
                    <div>
                        <div class="text-2xl font-extrabold text-zinc-900">
                            {{ $attendanceSummary['percentage'] ?? 100 }}%
                        </div>
                        <div class="text-[11px] text-zinc-500 mt-1 flex items-center gap-1.5 flex-wrap">
                            <span class="text-emerald-600 font-bold">{{ $attendanceSummary['present'] ?? 0 }} Hadir</span> &bull;
                            <span class="text-amber-600 font-bold">{{ $attendanceSummary['sick'] ?? 0 }} Sakit</span> &bull;
                            <span class="text-rose-600 font-bold">{{ $attendanceSummary['absent'] ?? 0 }} Alpa</span>
                        </div>
                    </div>
                    <a href="{{ route('parent.attendance') }}" class="text-[11px] font-bold text-brand-600 hover:text-brand-700 mt-3 inline-flex items-center gap-1">
                        <span>Lihat Rincian Presensi</span>
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </a>
                </div>

                <!-- 2. Tugas & Evaluasi -->
                <div class="bg-white rounded-2xl border border-zinc-200/80 p-4 sm:p-5 shadow-xs flex flex-col justify-between">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-xs font-bold text-zinc-500 uppercase tracking-wider">Tugas Aktif</span>
                        <div class="w-8 h-8 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>
                        </div>
                    </div>
                    <div>
                        <div class="text-2xl font-extrabold text-zinc-900">
                            {{ $assignments->count() }}
                        </div>
                        <p class="text-[11px] text-zinc-500 mt-1">
                            Tugas dan evaluasi kelas yang sedang berjalan
                        </p>
                    </div>
                    <a href="{{ route('parent.assignments') }}" class="text-[11px] font-bold text-brand-600 hover:text-brand-700 mt-3 inline-flex items-center gap-1">
                        <span>Daftar Tugas Anak</span>
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </a>
                </div>

                <!-- 3. Ujian CBT & Nilai -->
                <div class="bg-white rounded-2xl border border-zinc-200/80 p-4 sm:p-5 shadow-xs flex flex-col justify-between">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-xs font-bold text-zinc-500 uppercase tracking-wider">Hasil CBT</span>
                        <div class="w-8 h-8 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                        </div>
                    </div>
                    <div>
                        <div class="text-2xl font-extrabold text-zinc-900">
                            {{ $examResults->count() }}
                        </div>
                        <p class="text-[11px] text-zinc-500 mt-1">
                            Ujian CBT yang telah selesai & diterbitkan
                        </p>
                    </div>
                    <a href="{{ route('parent.grades') }}" class="text-[11px] font-bold text-brand-600 hover:text-brand-700 mt-3 inline-flex items-center gap-1">
                        <span>Lihat Capaian Nilai</span>
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </a>
                </div>

                <!-- 4. E-Rapor Digital -->
                <div class="bg-white rounded-2xl border border-zinc-200/80 p-4 sm:p-5 shadow-xs flex flex-col justify-between">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-xs font-bold text-zinc-500 uppercase tracking-wider">Status E-Rapor</span>
                        <div class="w-8 h-8 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                        </div>
                    </div>
                    <div>
                        <div class="text-base font-extrabold text-zinc-900">
                            @if ($reportCards->isNotEmpty())
                                <span class="text-emerald-600 flex items-center gap-1">
                                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                                    <span>Tersedia ({{ $reportCards->count() }})</span>
                                </span>
                            @else
                                <span class="text-zinc-500">Belum Terbit</span>
                            @endif
                        </div>
                        <p class="text-[11px] text-zinc-500 mt-1">
                            Rapor hasil belajar semester resmi
                        </p>
                    </div>
                    <a href="{{ route('parent.reports') }}" class="text-[11px] font-bold text-brand-600 hover:text-brand-700 mt-3 inline-flex items-center gap-1">
                        <span>Lihat Buku Rapor</span>
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </a>
                </div>
            </div>

            <!-- ============================================================== -->
            <!-- 2-COLUMN MAIN CONTENT: ACADEMIC PROGRESS & COMMUNICATION       -->
            <!-- ============================================================== -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

                <!-- Left 2 Cols: Assignments, Recent Attendance & Schedules -->
                <div class="lg:col-span-2 space-y-6">

                    <!-- Card 1: Tugas & Tenggat Waktu Anak -->
                    <div class="bg-white rounded-2xl border border-zinc-200/80 p-5 sm:p-6 shadow-xs">
                        <div class="flex items-center justify-between pb-4 border-b border-zinc-100 mb-4">
                            <div>
                                <h3 class="font-extrabold text-base text-zinc-900">Tugas & Evaluasi Belajar</h3>
                                <p class="text-xs text-zinc-500">Pantau tugas dan tenggat waktu pengerjaan anak Anda</p>
                            </div>
                            <a href="{{ route('parent.assignments') }}" class="text-xs font-bold text-brand-600 hover:text-brand-700">Semua Tugas &rsaquo;</a>
                        </div>

                        @if ($assignments->isNotEmpty())
                            <div class="space-y-3">
                                @foreach ($assignments as $task)
                                    @php
                                        $submission = $task->submissions->first();
                                        $isOverdue = $task->due_date && $task->due_date->isPast() && ! $submission;
                                    @endphp
                                    <div class="p-3.5 rounded-xl border border-zinc-100 hover:border-zinc-200 bg-zinc-50/40 flex items-center justify-between gap-4 transition-colors">
                                        <div class="min-w-0">
                                            <div class="flex items-center gap-2 mb-1 flex-wrap">
                                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-zinc-200 text-zinc-800">
                                                    {{ $task->subject?->name ?? 'Mata Pelajaran' }}
                                                </span>
                                                @if ($submission)
                                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-50 text-emerald-800 border border-emerald-200">
                                                        Sudah Dikumpulkan
                                                    </span>
                                                    @if ($submission->score !== null)
                                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-blue-50 text-blue-800 border border-blue-200">
                                                            Nilai: {{ $submission->score }}/{{ $task->max_score }}
                                                        </span>
                                                    @endif
                                                @elseif ($isOverdue)
                                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-rose-50 text-rose-800 border border-rose-200">
                                                        Terlewat Tenggat
                                                    </span>
                                                @else
                                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-amber-50 text-amber-800 border border-amber-200">
                                                        Belum Dikumpulkan
                                                    </span>
                                                @endif
                                            </div>
                                            <h4 class="font-bold text-sm text-zinc-900 truncate">{{ $task->title }}</h4>
                                            <p class="text-[11px] text-zinc-500 mt-0.5">
                                                Guru: {{ $task->teacher?->name ?? 'Tenaga Pengajar' }} &bull;
                                                Tenggat: <strong class="{{ $isOverdue ? 'text-rose-600' : 'text-zinc-700' }}">{{ $task->due_date ? $task->due_date->format('d M Y, H:i') : 'Tidak ditentukan' }}</strong>
                                            </p>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="py-8 text-center text-zinc-400">
                                <svg class="w-10 h-10 mx-auto mb-2 text-zinc-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                                <p class="text-xs text-zinc-500 font-medium">Tidak ada tugas aktif saat ini</p>
                            </div>
                        @endif
                    </div>

                    <!-- Card 2: Jadwal Pelajaran Hari Ini -->
                    <div class="bg-white rounded-2xl border border-zinc-200/80 p-5 sm:p-6 shadow-xs">
                        <div class="flex items-center justify-between pb-4 border-b border-zinc-100 mb-4">
                            <div>
                                <h3 class="font-extrabold text-base text-zinc-900">Jadwal Pelajaran Hari Ini</h3>
                                <p class="text-xs text-zinc-500">{{ \Carbon\Carbon::now()->isoFormat('dddd, D MMMM Y') }}</p>
                            </div>
                        </div>

                        @if ($todaySchedules->isNotEmpty())
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                @foreach ($todaySchedules as $sched)
                                    <div class="p-3.5 rounded-xl border border-zinc-200 bg-white flex items-center justify-between gap-3 shadow-2xs">
                                        <div>
                                            <span class="text-[10px] font-bold text-brand-600 uppercase">
                                                {{ substr($sched->start_time, 0, 5) }} - {{ substr($sched->end_time, 0, 5) }}
                                            </span>
                                            <h4 class="font-bold text-sm text-zinc-900 mt-0.5">{{ $sched->subject?->name }}</h4>
                                            <p class="text-[11px] text-zinc-500">Guru: {{ $sched->teacher?->name }}</p>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="py-8 text-center text-zinc-400">
                                <svg class="w-10 h-10 mx-auto mb-2 text-zinc-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                <p class="text-xs text-zinc-500 font-medium">Tidak ada jadwal kegiatan belajar mengajar hari ini</p>
                            </div>
                        @endif
                    </div>

                </div>

                <!-- Right Col: Announcements & Messages -->
                <div class="space-y-6">

                    <!-- Pengumuman Sekolah -->
                    <div class="bg-white rounded-2xl border border-zinc-200/80 p-5 sm:p-6 shadow-xs">
                        <div class="flex items-center justify-between pb-4 border-b border-zinc-100 mb-4">
                            <div>
                                <h3 class="font-extrabold text-base text-zinc-900">Warta & Pengumuman</h3>
                                <p class="text-xs text-zinc-500">Informasi resmi dari pihak sekolah</p>
                            </div>
                            <a href="{{ route('announcements.index') }}" class="text-xs font-bold text-brand-600 hover:text-brand-700">Semua &rsaquo;</a>
                        </div>

                        @if ($announcements->isNotEmpty())
                            <div class="space-y-3">
                                @foreach ($announcements as $ann)
                                    <a href="{{ route('announcements.show', $ann) }}" class="block p-3 rounded-xl border border-zinc-100 hover:border-zinc-200 hover:bg-zinc-50 transition-colors">
                                        <div class="flex items-center gap-1.5 mb-1">
                                            <span class="px-1.5 py-0.5 rounded text-[10px] font-bold border {{ $ann->categoryBadgeClasses() }}">
                                                {{ $ann->categoryLabel() }}
                                            </span>
                                            @if ($ann->is_pinned)
                                                <span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-amber-50 text-amber-800 border border-amber-200">
                                                    Disematkan
                                                </span>
                                            @endif
                                        </div>
                                        <h4 class="font-bold text-xs text-zinc-900 line-clamp-1">{{ $ann->title }}</h4>
                                        <p class="text-[11px] text-zinc-400 mt-1">{{ $ann->published_at ? $ann->published_at->diffForHumans() : $ann->created_at->diffForHumans() }}</p>
                                    </a>
                                @endforeach
                            </div>
                        @else
                            <div class="py-6 text-center text-zinc-400">
                                <p class="text-xs text-zinc-500 font-medium">Belum ada pengumuman terbaru</p>
                            </div>
                        @endif
                    </div>

                    <!-- Percakapan Konsultasi Guru Terakhir -->
                    <div class="bg-white rounded-2xl border border-zinc-200/80 p-5 sm:p-6 shadow-xs">
                        <div class="flex items-center justify-between pb-4 border-b border-zinc-100 mb-4">
                            <div>
                                <h3 class="font-extrabold text-base text-zinc-900">Konsultasi Guru</h3>
                                <p class="text-xs text-zinc-500">Komunikasi resmi dengan tenaga pendidik</p>
                            </div>
                            <a href="{{ route('conversations.index') }}" class="text-xs font-bold text-brand-600 hover:text-brand-700">Semua &rsaquo;</a>
                        </div>

                        @if ($recentConversations->isNotEmpty())
                            <div class="space-y-3">
                                @foreach ($recentConversations as $conv)
                                    <a href="{{ route('conversations.show', $conv) }}" class="block p-3 rounded-xl border border-zinc-100 hover:border-zinc-200 hover:bg-zinc-50 transition-colors">
                                        <div class="flex items-center justify-between gap-2 mb-1">
                                            <span class="font-bold text-xs text-zinc-900 truncate">{{ $conv->teacher?->name }}</span>
                                            @if ($conv->unreadCountFor($parent) > 0)
                                                <span class="w-2 h-2 rounded-full bg-brand-600 shrink-0"></span>
                                            @endif
                                        </div>
                                        <h4 class="font-semibold text-xs text-zinc-700 truncate">{{ $conv->subject }}</h4>
                                        <p class="text-[11px] text-zinc-400 mt-0.5 line-clamp-1">{{ $conv->latestMessage?->body }}</p>
                                    </a>
                                @endforeach
                            </div>
                        @else
                            <div class="py-6 text-center text-zinc-400">
                                <p class="text-xs text-zinc-500 font-medium">Belum ada percakapan konsultasi</p>
                                <a
                                    href="{{ route('conversations.create', ['child_id' => $activeChild->id]) }}"
                                    class="inline-flex items-center gap-1.5 mt-3 px-3 py-1.5 rounded-lg text-xs font-bold bg-brand-50 text-brand-600 hover:bg-brand-100 transition-colors"
                                >
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                    <span>Mulai Konsultasi Baru</span>
                                </a>
                            </div>
                        @endif
                    </div>

                </div>

            </div>
        @endif

    </div>
</x-layouts.app>
