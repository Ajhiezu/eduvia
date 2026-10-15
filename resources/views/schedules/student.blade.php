<x-layouts.app>
    <x-slot:headerTitle>
        Jadwal Pelajaran
    </x-slot:headerTitle>

    <div class="space-y-6">
        <!-- Header & Class Info -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <div class="flex items-center gap-2">
                    <h1 class="text-xl sm:text-2xl font-extrabold text-zinc-900 tracking-tight">
                        Jadwal Belajar Kelas
                    </h1>
                    @if ($classroom)
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-brand-50 text-brand-700 border border-brand-200">
                            {{ $classroom->name }}
                        </span>
                    @endif
                </div>
                <p class="text-xs text-zinc-500 mt-0.5">
                    @if ($classroom)
                        Wali Kelas: <span class="font-bold text-zinc-700">{{ $classroom->homeroomTeacher?->name ?? 'Belum Ditentukan' }}</span> &bull; Tahun Ajaran: <span class="font-bold text-zinc-700">{{ $classroom->academicYear?->name ?? '2025/2026' }}</span>
                    @else
                        Status Akun: Peserta Didik
                    @endif
                </p>
            </div>

            @if ($classroom)
                <!-- View Switcher Tabs -->
                <div class="inline-flex p-1 bg-zinc-100 rounded-lg text-xs font-semibold self-start sm:self-auto">
                    <a
                        href="{{ request()->fullUrlWithQuery(['view' => 'weekly']) }}"
                        class="px-3 py-1.5 rounded-md transition-all flex items-center gap-1.5 {{ $viewMode === 'weekly' ? 'bg-white text-zinc-900 shadow-2xs font-bold' : 'text-zinc-500 hover:text-zinc-800' }}"
                    >
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                        <span>Mingguan</span>
                    </a>

                    <a
                        href="{{ request()->fullUrlWithQuery(['view' => 'daily']) }}"
                        class="px-3 py-1.5 rounded-md transition-all flex items-center gap-1.5 {{ $viewMode === 'daily' ? 'bg-white text-zinc-900 shadow-2xs font-bold' : 'text-zinc-500 hover:text-zinc-800' }}"
                    >
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <span>Harian</span>
                    </a>
                </div>
            @endif
        </div>

        @if (! $classroom)
            <!-- Empty State: Student Not Assigned to Classroom -->
            <x-card padding>
                <div class="text-center py-12">
                    <div class="w-14 h-14 rounded-full bg-amber-50 text-amber-500 mx-auto flex items-center justify-center mb-4 border border-amber-200">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                    </div>
                    <h2 class="text-base font-extrabold text-zinc-900">Belum Terdaftar di Rombongan Belajar</h2>
                    <p class="text-xs text-zinc-500 mt-1 max-w-md mx-auto">
                        Akun Anda saat ini belum ditempatkan pada kelas (rombongan belajar) aktif di sistem sekolah. Silakan hubungi bagian tata usaha atau wali kelas Anda untuk konfirmasi penempatan kelas.
                    </p>
                </div>
            </x-card>
        @else
            <!-- Today's Lesson Highlights -->
            @if ($ongoingLesson || $nextUpcomingToday)
                <div class="p-4 sm:p-5 rounded-2xl bg-gradient-to-r from-brand-900 via-zinc-900 to-zinc-900 text-white shadow-md relative overflow-hidden">
                    <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-4">
                        <div class="space-y-1">
                            @if ($ongoingLesson)
                                <div class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-extrabold bg-emerald-500/20 text-emerald-300 border border-emerald-500/30">
                                    <span class="w-2 h-2 rounded-full bg-emerald-400 animate-ping"></span>
                                    Pelajaran Sedang Berlangsung
                                </div>
                                <h2 class="text-lg sm:text-xl font-black text-white mt-1">
                                    {{ $ongoingLesson->subject?->name }}
                                </h2>
                                <p class="text-xs text-zinc-300">
                                    Pukul <span class="font-mono font-bold text-white">{{ $ongoingLesson->timeRange() }}</span>
                                    &bull; Guru: <span class="font-bold text-white">{{ $ongoingLesson->teacher?->name ?? '-' }}</span>
                                    @if ($ongoingLesson->room)
                                        &bull; Ruang: <span class="font-bold text-white">{{ $ongoingLesson->room }}</span>
                                    @endif
                                </p>
                            @elseif ($nextUpcomingToday)
                                <div class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-extrabold bg-brand-500/20 text-brand-300 border border-brand-500/30">
                                    Pelajaran Berikutnya Hari Ini
                                </div>
                                <h2 class="text-lg sm:text-xl font-black text-white mt-1">
                                    {{ $nextUpcomingToday->subject?->name }}
                                </h2>
                                <p class="text-xs text-zinc-300">
                                    Pukul <span class="font-mono font-bold text-white">{{ $nextUpcomingToday->timeRange() }}</span>
                                    &bull; Guru: <span class="font-bold text-white">{{ $nextUpcomingToday->teacher?->name ?? '-' }}</span>
                                    @if ($nextUpcomingToday->room)
                                        &bull; Ruang: <span class="font-bold text-white">{{ $nextUpcomingToday->room }}</span>
                                    @endif
                                </p>
                            @endif
                        </div>

                        <div class="flex items-center gap-2">
                            <x-button href="{{ route('student.materials.index') }}" variant="secondary" size="sm">
                                Buka Materi Belajar
                            </x-button>
                        </div>
                    </div>
                </div>
            @endif

            <!-- ============================================================= -->
            <!-- VIEW 1: WEEKLY CALENDAR MATRIX                                -->
            <!-- ============================================================= -->
            @if ($viewMode === 'weekly')
                @if ($schedules->isEmpty())
                    <x-card padding>
                        <div class="text-center py-12">
                            <div class="w-12 h-12 rounded-full bg-zinc-100 text-zinc-400 mx-auto flex items-center justify-center mb-3">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                            <h3 class="text-sm font-bold text-zinc-800">Jadwal Kelas Belum Diterbitkan</h3>
                            <p class="text-xs text-zinc-500 mt-1 max-w-sm mx-auto">
                                Jadwal pelajaran untuk kelas {{ $classroom->name }} belum diterbitkan oleh bagian kurikulum sekolah.
                            </p>
                        </div>
                    </x-card>
                @else
                    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-6 gap-4">
                        @foreach (array_slice($days, 0, 6, true) as $dayNum => $dayLabel)
                            @php
                                $dayItems = $schedulesByDay->get($dayNum, collect());
                                $isToday = $todayIsoDay === $dayNum;
                            @endphp
                            <div class="flex flex-col bg-zinc-50/70 border {{ $isToday ? 'border-brand-300 ring-2 ring-brand-100' : 'border-zinc-200' }} rounded-xl overflow-hidden shadow-2xs">
                                <div class="p-3 bg-white border-b border-zinc-200/80 flex items-center justify-between">
                                    <div class="flex items-center gap-1.5">
                                        <span class="font-extrabold text-sm {{ $isToday ? 'text-brand-600' : 'text-zinc-900' }}">
                                            {{ $dayLabel }}
                                        </span>
                                        @if ($isToday)
                                            <span class="px-1.5 py-0.2 rounded text-[9px] font-bold bg-brand-100 text-brand-700">
                                                Hari Ini
                                            </span>
                                        @endif
                                    </div>
                                    <span class="text-xs font-semibold px-2 py-0.5 rounded-full bg-zinc-100 text-zinc-600">
                                        {{ $dayItems->count() }}
                                    </span>
                                </div>

                                <div class="p-2.5 flex-1 space-y-2.5 min-h-[140px]">
                                    @forelse ($dayItems as $item)
                                        @php
                                            $ta = $item->teachingAssignment;
                                            $isOngoing = $item->isOngoing();
                                        @endphp
                                        <div class="p-3 rounded-lg bg-white border {{ $isOngoing ? 'border-emerald-500 ring-1 ring-emerald-200 shadow-sm' : 'border-zinc-200 hover:border-zinc-300' }} transition-all text-xs space-y-2">
                                            <div class="flex items-center justify-between">
                                                <span class="font-bold text-zinc-700 bg-zinc-100 px-2 py-0.5 rounded text-[11px] font-mono">
                                                    {{ $item->timeRange() }}
                                                </span>
                                                @if ($isOngoing)
                                                    <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-700 animate-pulse">
                                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                                        Sedang Berlangsung
                                                    </span>
                                                @endif
                                            </div>

                                            <div>
                                                <p class="font-extrabold text-zinc-900 leading-snug">
                                                    {{ $ta?->subject?->name ?? 'Mata Pelajaran' }}
                                                </p>
                                                <p class="text-[11px] text-zinc-500 mt-1 truncate" title="{{ $ta?->teacher?->name }}">
                                                    {{ $ta?->teacher?->name ?? 'Guru Pengampu' }}
                                                </p>
                                                @if ($item->room)
                                                    <span class="inline-flex items-center text-[10px] text-zinc-400 mt-0.5">
                                                        <svg class="w-3 h-3 mr-0.5 text-zinc-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                                                        </svg>
                                                        Ruang: {{ $item->room }}
                                                    </span>
                                                @endif
                                            </div>
                                        </div>
                                    @empty
                                        <div class="h-24 border border-dashed border-zinc-200 rounded-lg flex items-center justify-center text-zinc-400 text-xs">
                                            Libur / Tidak ada jadwal
                                        </div>
                                    @endforelse
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif

            <!-- ============================================================= -->
            <!-- VIEW 2: DAILY LIST                                            -->
            <!-- ============================================================= -->
            @elseif ($viewMode === 'daily')
                <div class="space-y-4">
                    <!-- Day Selector Pills -->
                    <div class="flex items-center gap-2 overflow-x-auto pb-2 scrollbar-thin">
                        @foreach ($days as $num => $label)
                            @php
                                $isCurrentDaily = (int) $activeDailyDay === (int) $num;
                                $count = $schedulesByDay->get($num, collect())->count();
                            @endphp
                            <a
                                href="{{ request()->fullUrlWithQuery(['day' => $num, 'view' => 'daily']) }}"
                                class="px-4 py-2 rounded-xl text-xs font-bold transition-all shrink-0 flex items-center gap-2 border {{ $isCurrentDaily ? 'bg-brand-600 text-white border-brand-600 shadow-sm' : 'bg-white text-zinc-700 border-zinc-200 hover:border-zinc-300 hover:bg-zinc-50' }}"
                            >
                                <span>{{ $label }}</span>
                                <span class="px-1.5 py-0.2 rounded-full text-[10px] {{ $isCurrentDaily ? 'bg-brand-700 text-white' : 'bg-zinc-100 text-zinc-600' }}">
                                    {{ $count }}
                                </span>
                            </a>
                        @endforeach
                    </div>

                    @php
                        $dailyItems = $schedulesByDay->get($activeDailyDay, collect())->sortBy('start_time');
                    @endphp

                    @if ($dailyItems->isNotEmpty())
                        <div class="space-y-3">
                            @foreach ($dailyItems as $item)
                                @php
                                    $ta = $item->teachingAssignment;
                                    $isOngoing = $item->isOngoing();
                                    $isUpcoming = $item->isUpcomingToday();
                                @endphp
                                <div class="p-4 sm:p-5 rounded-xl bg-white border {{ $isOngoing ? 'border-emerald-500 ring-2 ring-emerald-100 shadow-sm' : 'border-zinc-200' }} flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                                    <div class="flex items-start sm:items-center gap-4">
                                        <div class="w-24 shrink-0">
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
                                                <h3 class="font-extrabold text-base text-zinc-900">
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

                                            <div class="flex items-center gap-3 mt-1 text-xs text-zinc-600">
                                                <span class="flex items-center gap-1">
                                                    <svg class="w-3.5 h-3.5 text-zinc-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                                    </svg>
                                                    Guru: {{ $ta?->teacher?->name ?? '-' }}
                                                </span>
                                                @if ($item->room)
                                                    <span class="text-zinc-500">
                                                        Ruang: {{ $item->room }}
                                                    </span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>

                                    <div class="flex items-center gap-2 self-end sm:self-center">
                                        <x-button href="{{ route('student.materials.index') }}" variant="outline" size="sm">
                                            Materi
                                        </x-button>
                                        <x-button href="{{ route('student.assignments.index') }}" variant="outline" size="sm">
                                            Tugas
                                        </x-button>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <x-card padding>
                            <div class="text-center py-10 text-zinc-400 text-xs">
                                Tidak ada jadwal pelajaran pada hari {{ $days[$activeDailyDay] ?? '' }}.
                            </div>
                        </x-card>
                    @endif
                </div>
            @endif
        @endif
    </div>
</x-layouts.app>
