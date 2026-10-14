<x-layouts.app>
    <x-slot:headerTitle>
        Presensi Pembelajaran
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
                    Presensi & Kehadiran Pembelajaran
                </h1>
                <p class="text-xs text-zinc-500 mt-0.5">
                    Kelola pencatatan kehadiran per pertemuan pelajaran, finalisasi sesi, dan riwayat koreksi presensi.
                </p>
            </div>

            <div class="flex items-center gap-2.5">
                <x-button href="{{ route('attendance.report') }}" variant="secondary" size="sm">
                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    Rekap Presensi
                </x-button>

                <x-button href="{{ route('attendance-sessions.create') }}" variant="primary" size="sm">
                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Buat Sesi Pertemuan
                </x-button>
            </div>
        </div>

        <!-- 3-Card Summary Strip (Metis Style) -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <x-card padding class="border-zinc-200">
                <div class="text-[11px] font-bold uppercase tracking-wider text-zinc-400">Total Sesi Pertemuan</div>
                <div class="mt-1 text-2xl font-extrabold text-zinc-900">{{ $totalSessionsCount }}</div>
                <div class="mt-1 text-xs text-zinc-400">Semua sesi tercatat</div>
            </x-card>

            <x-card padding class="border-zinc-200">
                <div class="text-[11px] font-bold uppercase tracking-wider text-emerald-600">Sesi Terfinalisasi</div>
                <div class="mt-1 text-2xl font-extrabold text-emerald-700">{{ $finalizedSessionsCount }}</div>
                <div class="mt-1 text-xs text-zinc-400">Presensi resmi tervalidasi</div>
            </x-card>

            <x-card padding class="border-zinc-200">
                <div class="text-[11px] font-bold uppercase tracking-wider text-amber-600">Draft Belum Final</div>
                <div class="mt-1 text-2xl font-extrabold text-amber-700">{{ $draftSessionsCount }}</div>
                <div class="mt-1 text-xs text-zinc-400">Perlu pemeriksaan pengampu</div>
            </x-card>
        </div>

        <!-- Filter Card -->
        <x-card padding>
            <form method="GET" action="{{ route('attendance-sessions.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
                <!-- Classroom Filter -->
                <div>
                    <select
                        name="classroom_id"
                        onchange="this.form.submit()"
                        class="w-full rounded-lg border border-zinc-300 text-zinc-900 text-xs py-2 px-3 focus:border-brand-500 focus:ring-2 focus:ring-brand-100"
                    >
                        <option value="">-- Semua Kelas --</option>
                        @foreach ($classrooms as $c)
                            <option value="{{ $c->id }}" {{ $classroomId == $c->id ? 'selected' : '' }}>
                                {{ $c->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Assignment Filter -->
                <div class="lg:col-span-2">
                    <select
                        name="teaching_assignment_id"
                        onchange="this.form.submit()"
                        class="w-full rounded-lg border border-zinc-300 text-zinc-900 text-xs py-2 px-3 focus:border-brand-500 focus:ring-2 focus:ring-brand-100"
                    >
                        <option value="">-- Semua Mata Pelajaran / Rombel --</option>
                        @foreach ($assignments as $a)
                            <option value="{{ $a->id }}" {{ $assignmentId == $a->id ? 'selected' : '' }}>
                                {{ $a->subject?->name }} ({{ $a->classroom?->name }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Status Filter -->
                <div>
                    <select
                        name="status"
                        onchange="this.form.submit()"
                        class="w-full rounded-lg border border-zinc-300 text-zinc-900 text-xs py-2 px-3 focus:border-brand-500 focus:ring-2 focus:ring-brand-100"
                    >
                        <option value="">-- Semua Status --</option>
                        <option value="draft" {{ $status === 'draft' ? 'selected' : '' }}>Draft</option>
                        <option value="finalized" {{ $status === 'finalized' ? 'selected' : '' }}>Terfinalisasi</option>
                    </select>
                </div>

                <!-- Date Filter -->
                <div>
                    <input
                        type="date"
                        name="date"
                        value="{{ $date }}"
                        onchange="this.form.submit()"
                        class="w-full rounded-lg border border-zinc-300 text-zinc-900 text-xs py-2 px-3 focus:border-brand-500 focus:ring-2 focus:ring-brand-100"
                    >
                </div>
            </form>
        </x-card>

        <!-- Sessions Table Card -->
        <x-card :padding="false">
            @if ($sessions->isEmpty())
                <div class="p-8 text-center">
                    <div class="w-12 h-12 mx-auto rounded-full bg-zinc-100 flex items-center justify-center text-zinc-400 mb-3">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                        </svg>
                    </div>
                    <h4 class="text-sm font-bold text-zinc-800">Tidak Ada Sesi Presensi Ditemukan</h4>
                    <p class="text-xs text-zinc-500 max-w-sm mx-auto mt-1 mb-4">
                        Belum ada sesi pertemuan pembelajaran yang sesuai dengan filter pencarian.
                    </p>
                    <x-button href="{{ route('attendance-sessions.create') }}" variant="primary" size="sm">
                        Buat Sesi Pertemuan Baru
                    </x-button>
                </div>
            @else
                <div class="overflow-x-auto" data-scrollable>
                    <table class="w-full min-w-[720px] text-left text-xs text-zinc-600">
                        <thead class="bg-zinc-50/80 text-zinc-500 font-semibold border-b border-zinc-200">
                            <tr>
                                <th class="py-3 px-4">Pertemuan & Mapel</th>
                                <th class="py-3 px-4">Kelas</th>
                                <th class="py-3 px-4">Tanggal & Waktu</th>
                                <th class="py-3 px-4">Topik Pembahasan</th>
                                <th class="py-3 px-4">Guru Pengampu</th>
                                <th class="py-3 px-4">Status</th>
                                <th class="py-3 px-4 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-200">
                            @foreach ($sessions as $item)
                                <tr class="hover:bg-zinc-50/50 transition-colors">
                                    <td class="py-3 px-4">
                                        <div class="font-bold text-zinc-900">
                                            Pertemuan Ke-{{ $item->meeting_number }}
                                        </div>
                                        <div class="text-[11px] text-brand-600 font-semibold">
                                            {{ $item->teachingAssignment?->subject?->name ?? 'Mata Pelajaran' }}
                                        </div>
                                    </td>
                                    <td class="py-3 px-4 font-semibold text-zinc-800">
                                        {{ $item->classroom?->name }}
                                    </td>
                                    <td class="py-3 px-4 text-zinc-600">
                                        <div>{{ $item->date->translatedFormat('d M Y') }}</div>
                                        @if ($item->start_time)
                                            <div class="text-[10px] text-zinc-400">
                                                {{ substr($item->start_time, 0, 5) }} - {{ substr($item->end_time ?? '', 0, 5) }}
                                            </div>
                                        @endif
                                    </td>
                                    <td class="py-3 px-4 text-zinc-700 max-w-xs truncate" title="{{ $item->topic }}">
                                        {{ $item->topic ?: '-' }}
                                    </td>
                                    <td class="py-3 px-4 text-zinc-700">
                                        {{ $item->teacher?->name }}
                                    </td>
                                    <td class="py-3 px-4">
                                        @if ($item->isFinalized())
                                            <x-badge variant="success" size="sm">Final</x-badge>
                                        @else
                                            <x-badge variant="warning" size="sm">Draft</x-badge>
                                        @endif
                                    </td>
                                    <td class="py-3 px-4 text-right">
                                        <x-button href="{{ route('attendance-sessions.show', $item) }}" variant="secondary" size="sm">
                                            Lembar Presensi &rarr;
                                        </x-button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @if ($sessions->hasPages())
                    <div class="p-4 border-t border-zinc-200">
                        {{ $sessions->links() }}
                    </div>
                @endif
            @endif
        </x-card>
    </div>
</x-layouts.app>
