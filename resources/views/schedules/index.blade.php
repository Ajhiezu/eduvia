<x-layouts.app>
    <x-slot:headerTitle>
        Jadwal Pelajaran
    </x-slot:headerTitle>

    <div class="space-y-6">
        <!-- Status & Flash Alerts -->
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

        @if ($errors->any())
            <x-alert variant="danger" dismissible>
                {{ $errors->first() }}
            </x-alert>
        @endif

        <!-- Action & Navigation Header -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <div class="flex items-center gap-2">
                    <h1 class="text-xl sm:text-2xl font-extrabold text-zinc-900 tracking-tight">
                        Jadwal Pelajaran & Kalender Belajar
                    </h1>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-brand-50 text-brand-700 border border-brand-200">
                        Fase 3
                    </span>
                </div>
                <p class="text-xs text-zinc-500 mt-0.5">
                    Kelola jadwal pelajaran mingguan, pemetaan guru dan kelas, alokasi ruang, dan deteksi bentrok otomatis.
                </p>
            </div>

            <div class="flex items-center gap-2.5">
                <x-button href="{{ route('classrooms.index') }}" variant="outline" size="sm">
                    <svg class="w-4 h-4 mr-1.5 text-zinc-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                    </svg>
                    Data Kelas
                </x-button>
                <x-button href="{{ route('schedules.create') }}" variant="primary" size="sm">
                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Buat Jadwal Baru
                </x-button>
            </div>
        </div>

        <!-- Metric Summary Cards -->
        @php
            $flatSchedules = $viewMode === 'list' ? $schedules->items() : $schedules;
            $totalCount = $viewMode === 'list' ? $schedules->total() : $schedules->count();
            $activeCount = collect($flatSchedules)->where('is_active', true)->count();
            $distinctClassrooms = collect($flatSchedules)->pluck('teachingAssignment.classroom_id')->filter()->unique()->count();
            $distinctTeachers = collect($flatSchedules)->pluck('teachingAssignment.teacher_id')->filter()->unique()->count();
        @endphp
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">
            <x-card padding="sm">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-[11px] font-semibold text-zinc-400 uppercase tracking-wider">Total Jadwal</p>
                        <p class="text-xl font-extrabold text-zinc-900 mt-0.5">{{ $totalCount }}</p>
                    </div>
                    <div class="w-9 h-9 rounded-lg bg-brand-50 text-brand-600 flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                </div>
            </x-card>

            <x-card padding="sm">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-[11px] font-semibold text-zinc-400 uppercase tracking-wider">Kelas Terjadwal</p>
                        <p class="text-xl font-extrabold text-zinc-900 mt-0.5">{{ $distinctClassrooms }}</p>
                    </div>
                    <div class="w-9 h-9 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                        </svg>
                    </div>
                </div>
            </x-card>

            <x-card padding="sm">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-[11px] font-semibold text-zinc-400 uppercase tracking-wider">Guru Pengampu</p>
                        <p class="text-xl font-extrabold text-zinc-900 mt-0.5">{{ $distinctTeachers }}</p>
                    </div>
                    <div class="w-9 h-9 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                    </div>
                </div>
            </x-card>

            <x-card padding="sm">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-[11px] font-semibold text-zinc-400 uppercase tracking-wider">Status Aktif</p>
                        <p class="text-xl font-extrabold text-emerald-600 mt-0.5">{{ $activeCount }}</p>
                    </div>
                    <div class="w-9 h-9 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                </div>
            </x-card>
        </div>

        @php
            $groupedClassrooms = $classrooms->groupBy(fn ($c) => strtoupper($c->gradeLevel?->educational_level ?? 'UMUM'));
            $levelLabels = [
                'SD' => 'Jenjang SD (Kelas 1 - 6)',
                'SMP' => 'Jenjang SMP (Kelas 7 - 9)',
                'SMA' => 'Jenjang SMA (Kelas 10 - 12)',
            ];
            $groupedSubjects = $subjects->groupBy(fn ($s) => strtoupper($s->educational_level ?? 'UMUM'));
            $subjectLevelLabels = [
                'SD' => 'Mata Pelajaran SD',
                'SMP' => 'Mata Pelajaran SMP',
                'SMA' => 'Mata Pelajaran SMA',
                'UMUM' => 'Mata Pelajaran Umum',
            ];
            $hasActiveFilters = $classroomId || $teacherId || $subjectId || $dayOfWeek || $educationalLevel || $semesterId || $status !== null || $search;
        @endphp

        <!-- Filter & Search Controls -->
        <x-card padding>
            <form method="GET" action="{{ route('schedules.index') }}" class="space-y-4">
                <input type="hidden" name="view" value="{{ $viewMode }}">

                <!-- Row 1: Search & Context Filters -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-3">
                    <!-- Search Input -->
                    <div class="sm:col-span-2 lg:col-span-4">
                        <label class="block text-[11px] font-bold text-zinc-600 mb-1">Pencarian Cepat</label>
                        <div class="relative">
                            <input
                                type="search"
                                name="search"
                                value="{{ $search }}"
                                placeholder="Cari mapel, guru, kelas, ruang..."
                                class="w-full h-10 rounded-lg border border-zinc-300 text-zinc-900 text-xs pl-9 pr-3 focus:border-brand-500 focus:ring-2 focus:ring-brand-100 placeholder-zinc-400 bg-white"
                            >
                            <svg class="w-4 h-4 text-zinc-400 absolute left-3 top-3 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                        </div>
                    </div>

                    <!-- Academic Year Filter -->
                    <div class="lg:col-span-3">
                        <label class="block text-[11px] font-bold text-zinc-600 mb-1">Tahun Ajaran</label>
                        <select
                            name="academic_year_id"
                            class="w-full h-10 rounded-lg border border-zinc-300 text-zinc-900 text-xs px-3 focus:border-brand-500 focus:ring-2 focus:ring-brand-100 bg-white"
                            onchange="this.form.submit()"
                        >
                            @foreach ($academicYears as $year)
                                <option value="{{ $year->id }}" @selected((string) $academicYearId === (string) $year->id)>
                                    T.A. {{ $year->name }} {{ $year->is_active ? '(Aktif)' : '' }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Semester Filter -->
                    <div class="lg:col-span-3">
                        <label class="block text-[11px] font-bold text-zinc-600 mb-1">Semester</label>
                        <select
                            name="semester_id"
                            class="w-full h-10 rounded-lg border border-zinc-300 text-zinc-900 text-xs px-3 focus:border-brand-500 focus:ring-2 focus:ring-brand-100 bg-white"
                            onchange="this.form.submit()"
                        >
                            <option value="">Semua Semester</option>
                            @foreach ($semesters as $sem)
                                <option value="{{ $sem->id }}" @selected((string) $semesterId === (string) $sem->id)>
                                    {{ $sem->name }} {{ $sem->is_active ? '(Aktif)' : '' }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Educational Level Filter -->
                    <div class="lg:col-span-2">
                        <label class="block text-[11px] font-bold text-zinc-600 mb-1">Jenjang</label>
                        <select
                            name="educational_level"
                            class="w-full h-10 rounded-lg border border-zinc-300 text-zinc-900 text-xs px-3 focus:border-brand-500 focus:ring-2 focus:ring-brand-100 bg-white"
                            onchange="this.form.submit()"
                        >
                            <option value="">Semua Jenjang</option>
                            <option value="sd" @selected($educationalLevel === 'sd')>SD</option>
                            <option value="smp" @selected($educationalLevel === 'smp')>SMP</option>
                            <option value="sma" @selected($educationalLevel === 'sma')>SMA</option>
                        </select>
                    </div>
                </div>

                <!-- Row 2: Classroom, Subject, Teacher, Day, and Status Filters -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-3 pt-1">
                    <!-- Classroom Filter -->
                    <div class="lg:col-span-3">
                        <label class="block text-[11px] font-bold text-zinc-600 mb-1">Kelas (Rombel)</label>
                        <select
                            name="classroom_id"
                            class="w-full h-10 rounded-lg border border-zinc-300 text-zinc-900 text-xs px-3 focus:border-brand-500 focus:ring-2 focus:ring-brand-100 bg-white"
                            onchange="this.form.submit()"
                        >
                            <option value="">Semua Kelas ({{ $classrooms->count() }} Rombel)</option>
                            @foreach ($groupedClassrooms as $level => $clsList)
                                <optgroup label="{{ $levelLabels[$level] ?? 'Jenjang ' . $level }}">
                                    @foreach ($clsList as $cls)
                                        <option value="{{ $cls->id }}" @selected((string) $classroomId === (string) $cls->id)>
                                            {{ $cls->name }} ({{ $cls->gradeLevel?->name ?? 'Tingkat' }})
                                        </option>
                                    @endforeach
                                </optgroup>
                            @endforeach
                        </select>
                    </div>

                    <!-- Subject Filter -->
                    <div class="lg:col-span-3">
                        <label class="block text-[11px] font-bold text-zinc-600 mb-1">Mata Pelajaran</label>
                        <select
                            name="subject_id"
                            class="w-full h-10 rounded-lg border border-zinc-300 text-zinc-900 text-xs px-3 focus:border-brand-500 focus:ring-2 focus:ring-brand-100 bg-white"
                            onchange="this.form.submit()"
                        >
                            <option value="">Semua Mata Pelajaran ({{ $subjects->count() }})</option>
                            @foreach ($groupedSubjects as $lvl => $subList)
                                <optgroup label="{{ $subjectLevelLabels[$lvl] ?? 'Mapel ' . $lvl }}">
                                    @foreach ($subList as $sbj)
                                        <option value="{{ $sbj->id }}" @selected((string) $subjectId === (string) $sbj->id)>
                                            {{ $sbj->code }} - {{ $sbj->name }}
                                        </option>
                                    @endforeach
                                </optgroup>
                            @endforeach
                        </select>
                    </div>

                    <!-- Teacher Filter -->
                    <div class="lg:col-span-3">
                        <label class="block text-[11px] font-bold text-zinc-600 mb-1">Guru Pengampu</label>
                        <select
                            name="teacher_id"
                            class="w-full h-10 rounded-lg border border-zinc-300 text-zinc-900 text-xs px-3 focus:border-brand-500 focus:ring-2 focus:ring-brand-100 bg-white"
                            onchange="this.form.submit()"
                        >
                            <option value="">Semua Guru ({{ $teachers->count() }})</option>
                            @foreach ($teachers as $t)
                                <option value="{{ $t->id }}" @selected((string) $teacherId === (string) $t->id)>
                                    {{ $t->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Day Filter -->
                    <div class="sm:col-span-1 lg:col-span-2">
                        <label class="block text-[11px] font-bold text-zinc-600 mb-1">Hari</label>
                        <select
                            name="day_of_week"
                            class="w-full h-10 rounded-lg border border-zinc-300 text-zinc-900 text-xs px-3 focus:border-brand-500 focus:ring-2 focus:ring-brand-100 bg-white"
                            onchange="this.form.submit()"
                        >
                            <option value="">Semua Hari</option>
                            @foreach ($days as $num => $name)
                                <option value="{{ $num }}" @selected((string) $dayOfWeek === (string) $num)>
                                    {{ $name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Status Filter -->
                    <div class="sm:col-span-1 lg:col-span-1">
                        <label class="block text-[11px] font-bold text-zinc-600 mb-1">Status</label>
                        <select
                            name="status"
                            class="w-full h-10 rounded-lg border border-zinc-300 text-zinc-900 text-xs px-3 focus:border-brand-500 focus:ring-2 focus:ring-brand-100 bg-white"
                            onchange="this.form.submit()"
                        >
                            <option value="">Semua</option>
                            <option value="1" @selected((string) $status === '1')>Aktif</option>
                            <option value="0" @selected((string) $status === '0')>Nonaktif</option>
                        </select>
                    </div>
                </div>

                <!-- Secondary Filter Bar & View Mode Toggle -->
                <div class="flex flex-col sm:flex-row items-center justify-between gap-3 pt-3 border-t border-zinc-100">
                    <div class="flex items-center gap-2 w-full sm:w-auto">
                        @if ($hasActiveFilters)
                            <a
                                href="{{ route('schedules.index', ['view' => $viewMode]) }}"
                                class="inline-flex items-center text-xs text-brand-600 hover:text-brand-700 font-semibold px-3 py-1.5 rounded-lg border border-brand-200 hover:bg-brand-50 transition-colors"
                            >
                                <svg class="w-3.5 h-3.5 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                </svg>
                                Reset Filter
                            </a>
                        @endif
                        <span class="text-xs text-zinc-500">
                            Menampilkan <strong class="text-zinc-900">{{ $totalCount }}</strong> jadwal sesuai filter
                        </span>
                    </div>

                    <!-- View Switcher Tabs -->
                    <div class="inline-flex p-1 bg-zinc-100 rounded-lg text-xs font-semibold self-end sm:self-auto">
                        <a
                            href="{{ request()->fullUrlWithQuery(['view' => 'weekly']) }}"
                            class="px-3 py-1.5 rounded-md transition-all flex items-center gap-1.5 {{ $viewMode === 'weekly' ? 'bg-white text-zinc-900 shadow-2xs font-bold' : 'text-zinc-500 hover:text-zinc-800' }}"
                        >
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                            <span>Matriks Mingguan</span>
                        </a>

                        <a
                            href="{{ request()->fullUrlWithQuery(['view' => 'daily']) }}"
                            class="px-3 py-1.5 rounded-md transition-all flex items-center gap-1.5 {{ $viewMode === 'daily' ? 'bg-white text-zinc-900 shadow-2xs font-bold' : 'text-zinc-500 hover:text-zinc-800' }}"
                        >
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <span>Jadwal Harian</span>
                        </a>

                        <a
                            href="{{ request()->fullUrlWithQuery(['view' => 'list']) }}"
                            class="px-3 py-1.5 rounded-md transition-all flex items-center gap-1.5 {{ $viewMode === 'list' ? 'bg-white text-zinc-900 shadow-2xs font-bold' : 'text-zinc-500 hover:text-zinc-800' }}"
                        >
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/>
                            </svg>
                            <span>Daftar Tabel</span>
                        </a>
                    </div>
                </div>
            </form>
        </x-card>

        <!-- ================================================================= -->
        <!-- VIEW 1: WEEKLY TIMETABLE GRID                                     -->
        <!-- ================================================================= -->
        @if ($viewMode === 'weekly')
            <div class="space-y-4">
                @if ($classroomId)
                    @php
                        $selectedCls = $classrooms->firstWhere('id', (int) $classroomId);
                    @endphp
                    <div class="p-3.5 bg-brand-50 border border-brand-200 rounded-xl text-xs text-brand-950 flex flex-col sm:flex-row sm:items-center justify-between gap-3 shadow-2xs">
                        <div class="flex items-center gap-2.5">
                            <span class="w-2.5 h-2.5 rounded-full bg-brand-600 shrink-0"></span>
                            <div>
                                <span class="font-extrabold text-sm text-brand-900">
                                    Jadwal Mingguan: {{ $selectedCls?->name ?? 'Kelas' }}
                                </span>
                                <span class="text-zinc-500 ml-1.5">
                                    ({{ $selectedCls?->gradeLevel?->name ?? 'Tingkat' }} • {{ $totalCount }} Sesi Pelajaran Teralokasi)
                                </span>
                            </div>
                        </div>
                        <a
                            href="{{ request()->fullUrlWithQuery(['classroom_id' => null]) }}"
                            class="inline-flex items-center text-xs font-bold text-brand-700 hover:text-brand-900 hover:underline shrink-0"
                        >
                            Tampilkan Semua Kelas &rarr;
                        </a>
                    </div>
                @else
                    <div class="p-3.5 bg-zinc-50 border border-zinc-200 rounded-xl text-xs text-zinc-800 flex flex-col md:flex-row md:items-center justify-between gap-3 shadow-2xs">
                        <div class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-brand-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <span>
                                Menampilkan seluruh jadwal gabungan <strong>{{ $totalCount }} sesi</strong> dari <strong>{{ $distinctClassrooms }} rombel</strong>. Pilih kelas tertentu pada filter untuk melihat kalender jadwal khusus satu rombel.
                            </span>
                        </div>
                        <div class="flex flex-wrap items-center gap-1.5 shrink-0">
                            <span class="text-zinc-500 text-[11px] font-semibold">Pilih Cepat:</span>
                            @if ($c1 = $classrooms->first())
                                <a href="{{ request()->fullUrlWithQuery(['classroom_id' => $c1->id]) }}" class="px-2.5 py-1 rounded bg-white border border-zinc-300 hover:border-brand-500 hover:text-brand-600 font-bold text-[11px] text-zinc-800 shadow-2xs transition-colors">
                                    {{ $c1->name }} (SD)
                                </a>
                            @endif
                            @if ($c2 = $classrooms->firstWhere('gradeLevel.educational_level', 'smp'))
                                <a href="{{ request()->fullUrlWithQuery(['classroom_id' => $c2->id]) }}" class="px-2.5 py-1 rounded bg-white border border-zinc-300 hover:border-brand-500 hover:text-brand-600 font-bold text-[11px] text-zinc-800 shadow-2xs transition-colors">
                                    {{ $c2->name }} (SMP)
                                </a>
                            @endif
                            @if ($c3 = $classrooms->firstWhere('gradeLevel.educational_level', 'sma'))
                                <a href="{{ request()->fullUrlWithQuery(['classroom_id' => $c3->id]) }}" class="px-2.5 py-1 rounded bg-white border border-zinc-300 hover:border-brand-500 hover:text-brand-600 font-bold text-[11px] text-zinc-800 shadow-2xs transition-colors">
                                    {{ $c3->name }} (SMA)
                                </a>
                            @endif
                        </div>
                    </div>
                @endif
                <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-6 gap-4">
                    @foreach (array_slice($days, 0, 6, true) as $dayNum => $dayLabel)
                        @php
                            $dayItems = $schedulesByDay->get($dayNum, collect());
                            $isToday = \Carbon\Carbon::now()->dayOfWeekIso === $dayNum;
                        @endphp
                        <div class="flex flex-col bg-zinc-50/70 border {{ $isToday ? 'border-brand-300 ring-2 ring-brand-100' : 'border-zinc-200' }} rounded-xl overflow-hidden shadow-2xs">
                            <!-- Day Column Header -->
                            <div class="p-3 bg-white border-b border-zinc-200/80 flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <span class="font-extrabold text-sm {{ $isToday ? 'text-brand-600' : 'text-zinc-900' }}">
                                        {{ $dayLabel }}
                                    </span>
                                    @if ($isToday)
                                        <span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-brand-100 text-brand-700">
                                            Hari Ini
                                        </span>
                                    @endif
                                </div>
                                <span class="text-xs font-semibold px-2 py-0.5 rounded-full bg-zinc-100 text-zinc-600">
                                    {{ $dayItems->count() }}
                                </span>
                            </div>

                            <!-- Schedule Items in Column -->
                            <div class="p-2.5 flex-1 space-y-2.5 min-h-[160px]">
                                @forelse ($dayItems as $item)
                                    @php
                                        $ta = $item->teachingAssignment;
                                        $isOngoing = $item->isOngoing();
                                    @endphp
                                    <div class="p-3 rounded-lg bg-white border {{ $isOngoing ? 'border-emerald-500 shadow-sm ring-1 ring-emerald-200' : ($item->is_active ? 'border-zinc-200 hover:border-zinc-300 hover:shadow-2xs' : 'border-zinc-200 opacity-60 bg-zinc-50') }} transition-all text-xs space-y-2 group">
                                        <!-- Time & Ongoing Pill -->
                                        <div class="flex items-center justify-between gap-1">
                                            <span class="font-bold text-zinc-700 bg-zinc-100 px-2 py-0.5 rounded text-[11px] font-mono">
                                                {{ $item->timeRange() }}
                                            </span>
                                            @if ($isOngoing)
                                                <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-700 animate-pulse">
                                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                                    Aktif
                                                </span>
                                            @elseif (! $item->is_active)
                                                <span class="px-1.5 py-0.5 rounded text-[10px] font-semibold bg-zinc-200 text-zinc-600">
                                                    Nonaktif
                                                </span>
                                            @endif
                                        </div>

                                        <!-- Subject & Classroom -->
                                        <div>
                                            <p class="font-extrabold text-zinc-900 leading-snug line-clamp-2">
                                                {{ $ta?->subject?->name ?? 'Mata Pelajaran' }}
                                            </p>
                                            <div class="flex items-center gap-1.5 mt-1">
                                                <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-bold bg-brand-50 text-brand-700 border border-brand-100">
                                                    {{ $ta?->classroom?->name ?? 'Kelas' }}
                                                </span>
                                                @if ($item->room)
                                                    <span class="inline-flex items-center text-[10px] text-zinc-500 truncate max-w-[110px]" title="{{ $item->room }}">
                                                        <svg class="w-3 h-3 mr-0.5 text-zinc-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                                                        </svg>
                                                        {{ $item->room }}
                                                    </span>
                                                @endif
                                            </div>
                                        </div>

                                        <!-- Teacher Name -->
                                        <div class="pt-1.5 border-t border-zinc-100 flex items-center justify-between text-zinc-500 text-[11px]">
                                            <span class="truncate max-w-[120px]" title="{{ $ta?->teacher?->name }}">
                                                {{ $ta?->teacher?->name ?? 'Guru Pengampu' }}
                                            </span>
                                            <div class="flex items-center gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                                                <a href="{{ route('schedules.edit', $item) }}" class="p-1 rounded text-zinc-400 hover:text-brand-600 hover:bg-zinc-100" title="Edit Jadwal">
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                                    </svg>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                @empty
                                    <div class="h-28 border border-dashed border-zinc-200 rounded-lg flex flex-col items-center justify-center p-3 text-center text-zinc-400">
                                        <p class="text-xs">Tidak ada jadwal</p>
                                        <a href="{{ route('schedules.create', ['day_of_week' => $dayNum, 'classroom_id' => $classroomId]) }}" class="mt-1 text-[11px] text-brand-600 hover:underline font-semibold">
                                            + Tambah
                                        </a>
                                    </div>
                                @endforelse
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

        <!-- ================================================================= -->
        <!-- VIEW 2: DAILY SCHEDULE VIEW                                       -->
        <!-- ================================================================= -->
        @elseif ($viewMode === 'daily')
            <div class="space-y-4">
                <!-- Day Pills Navigation -->
                <div class="flex items-center gap-2 overflow-x-auto pb-2 scrollbar-thin">
                    @foreach ($days as $num => $label)
                        @php
                            $isCurrentDaily = (int) $selectedDailyDay === (int) $num;
                            $count = $schedulesByDay->get($num, collect())->count();
                        @endphp
                        <a
                            href="{{ request()->fullUrlWithQuery(['day_of_week' => $num, 'view' => 'daily']) }}"
                            class="px-4 py-2 rounded-xl text-xs font-bold transition-all shrink-0 flex items-center gap-2 border {{ $isCurrentDaily ? 'bg-brand-600 text-white border-brand-600 shadow-sm' : 'bg-white text-zinc-700 border-zinc-200 hover:border-zinc-300 hover:bg-zinc-50' }}"
                        >
                            <span>{{ $label }}</span>
                            <span class="px-1.5 py-0.2 rounded-full text-[10px] {{ $isCurrentDaily ? 'bg-brand-700 text-white' : 'bg-zinc-100 text-zinc-600' }}">
                                {{ $count }}
                            </span>
                        </a>
                    @endforeach
                </div>

                <!-- Daily Schedule List -->
                @php
                    $dailyItems = $schedulesByDay->get($selectedDailyDay, collect())->sortBy('start_time');
                @endphp

                @if ($dailyItems->isNotEmpty())
                    <div class="space-y-3">
                        @foreach ($dailyItems as $item)
                            @php
                                $ta = $item->teachingAssignment;
                                $isOngoing = $item->isOngoing();
                                $isPast = $item->isPastToday();
                                $isUpcoming = $item->isUpcomingToday();
                            @endphp
                            <div class="p-4 sm:p-5 rounded-xl bg-white border {{ $isOngoing ? 'border-emerald-500 ring-2 ring-emerald-100 shadow-sm' : 'border-zinc-200' }} flex flex-col sm:flex-row sm:items-center justify-between gap-4 transition-all">
                                <!-- Time & Meta -->
                                <div class="flex items-start sm:items-center gap-4">
                                    <div class="w-24 shrink-0 text-center sm:text-left">
                                        <span class="font-extrabold text-sm text-zinc-900 font-mono">
                                            {{ $item->timeRange() }}
                                        </span>
                                        <p class="text-[11px] text-zinc-400 font-medium">
                                            {{ $item->durationMinutes() }} Menit
                                        </p>
                                    </div>

                                    <div class="hidden sm:block w-px h-10 bg-zinc-200"></div>

                                    <div>
                                        <div class="flex items-center gap-2">
                                            <h3 class="font-extrabold text-sm sm:text-base text-zinc-900">
                                                {{ $ta?->subject?->name ?? 'Mata Pelajaran' }}
                                            </h3>
                                            @if ($isOngoing)
                                                <span class="px-2 py-0.5 rounded-full text-[11px] font-extrabold bg-emerald-100 text-emerald-800 border border-emerald-300 flex items-center gap-1 animate-pulse">
                                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-600"></span>
                                                    Sedang Berlangsung
                                                </span>
                                            @elseif ($isUpcoming)
                                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-blue-50 text-blue-700 border border-blue-200">
                                                    Akan Datang
                                                </span>
                                            @endif
                                        </div>

                                        <div class="flex flex-wrap items-center gap-3 mt-1.5 text-xs text-zinc-600">
                                            <span class="font-semibold px-2 py-0.5 rounded bg-zinc-100 text-zinc-800">
                                                Kelas: {{ $ta?->classroom?->name ?? '-' }}
                                            </span>
                                            <span class="flex items-center gap-1">
                                                <svg class="w-3.5 h-3.5 text-zinc-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                                </svg>
                                                {{ $ta?->teacher?->name ?? 'Guru Pengampu' }}
                                            </span>
                                            @if ($item->room)
                                                <span class="flex items-center gap-1 text-zinc-500">
                                                    <svg class="w-3.5 h-3.5 text-zinc-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                                                    </svg>
                                                    Ruang: {{ $item->room }}
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                </div>

                                <!-- Actions -->
                                <div class="flex items-center gap-2 self-end sm:self-center">
                                    <form method="POST" action="{{ route('schedules.toggle-status', $item) }}">
                                        @csrf
                                        <button
                                            type="submit"
                                            class="px-2.5 py-1 rounded-lg text-xs font-semibold border transition-colors {{ $item->is_active ? 'border-zinc-200 text-zinc-600 hover:bg-zinc-50' : 'border-amber-200 bg-amber-50 text-amber-700' }}"
                                        >
                                            {{ $item->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                        </button>
                                    </form>

                                    <x-button href="{{ route('schedules.edit', $item) }}" variant="outline" size="sm">
                                        Edit
                                    </x-button>

                                    <form method="POST" action="{{ route('schedules.destroy', $item) }}" onsubmit="return confirm('Apakah Anda yakin ingin menghapus jadwal ini?');">
                                        @csrf
                                        @method('DELETE')
                                        <button
                                            type="submit"
                                            class="p-1.5 rounded-lg text-zinc-400 hover:text-rose-600 hover:bg-rose-50 transition-colors"
                                            title="Hapus Jadwal"
                                        >
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                            </svg>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <x-card padding>
                        <div class="text-center py-12">
                            <div class="w-12 h-12 rounded-full bg-zinc-100 text-zinc-400 mx-auto flex items-center justify-center mb-3">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                            </div>
                            <h3 class="text-sm font-bold text-zinc-800">Tidak ada jadwal pada hari {{ $days[$selectedDailyDay] ?? '' }}</h3>
                            <p class="text-xs text-zinc-500 mt-1">Tambahkan slot jadwal untuk kelas atau guru pada hari ini.</p>
                            <div class="mt-4">
                                <x-button href="{{ route('schedules.create', ['day_of_week' => $selectedDailyDay, 'classroom_id' => $classroomId]) }}" variant="primary" size="sm">
                                    + Tambah Jadwal Hari Ini
                                </x-button>
                            </div>
                        </div>
                    </x-card>
                @endif
            </div>

        <!-- ================================================================= -->
        <!-- VIEW 3: TABULAR LIST VIEW                                         -->
        <!-- ================================================================= -->
        @else
            <x-card>
                <div class="overflow-x-auto">
                    <x-table>
                        <x-slot:thead>
                            <tr>
                                <th>Hari & Jam</th>
                                <th>Kelas</th>
                                <th>Mata Pelajaran</th>
                                <th>Guru Pengampu</th>
                                <th>Ruang</th>
                                <th>Status</th>
                                <th class="text-right">Aksi</th>
                            </tr>
                        </x-slot:thead>
                        <tbody>
                            @forelse ($schedules as $item)
                                @php
                                    $ta = $item->teachingAssignment;
                                @endphp
                                <tr class="hover:bg-zinc-50/80 transition-colors">
                                    <td>
                                        <div class="font-extrabold text-xs text-zinc-900">
                                            {{ $item->dayName() }}
                                        </div>
                                        <div class="text-[11px] font-mono text-zinc-500 mt-0.5">
                                            {{ $item->timeRange() }} ({{ $item->durationMinutes() }}m)
                                        </div>
                                    </td>
                                    <td>
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-bold bg-zinc-100 text-zinc-800">
                                            {{ $ta?->classroom?->name ?? '-' }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="font-bold text-xs text-zinc-900">
                                            {{ $ta?->subject?->name ?? '-' }}
                                        </div>
                                        <div class="text-[11px] text-zinc-400">
                                            {{ $ta?->subject?->code ?? '-' }}
                                        </div>
                                    </td>
                                    <td>
                                        <div class="text-xs font-medium text-zinc-800">
                                            {{ $ta?->teacher?->name ?? '-' }}
                                        </div>
                                    </td>
                                    <td>
                                        @if ($item->room)
                                            <span class="text-xs text-zinc-600 bg-zinc-100 px-2 py-0.5 rounded font-medium">
                                                {{ $item->room }}
                                            </span>
                                        @else
                                            <span class="text-xs text-zinc-400 italic">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if ($item->is_active)
                                            <x-badge variant="success" size="sm">Aktif</x-badge>
                                        @else
                                            <x-badge variant="neutral" size="sm">Nonaktif</x-badge>
                                        @endif
                                    </td>
                                    <td class="text-right">
                                        <div class="flex items-center justify-end gap-1.5">
                                            <form method="POST" action="{{ route('schedules.toggle-status', $item) }}">
                                                @csrf
                                                <button
                                                    type="submit"
                                                    class="p-1 rounded text-zinc-400 hover:text-zinc-700 hover:bg-zinc-100"
                                                    title="{{ $item->is_active ? 'Nonaktifkan' : 'Aktifkan' }}"
                                                >
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
                                                    </svg>
                                                </button>
                                            </form>

                                            <a
                                                href="{{ route('schedules.edit', $item) }}"
                                                class="p-1 rounded text-zinc-400 hover:text-brand-600 hover:bg-zinc-100"
                                                title="Edit Jadwal"
                                            >
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                                </svg>
                                            </a>

                                            <form method="POST" action="{{ route('schedules.destroy', $item) }}" onsubmit="return confirm('Apakah Anda yakin ingin menghapus jadwal ini?');">
                                                @csrf
                                                @method('DELETE')
                                                <button
                                                    type="submit"
                                                    class="p-1 rounded text-zinc-400 hover:text-rose-600 hover:bg-rose-50"
                                                    title="Hapus Jadwal"
                                                >
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                    </svg>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center py-10 text-zinc-400 text-xs">
                                        Tidak ada data jadwal yang cocok dengan filter.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </x-table>
                </div>

                @if ($schedules->hasPages())
                    <div class="p-4 border-t border-zinc-100">
                        {{ $schedules->links() }}
                    </div>
                @endif
            </x-card>
        @endif
    </div>
</x-layouts.app>
