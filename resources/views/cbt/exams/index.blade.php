<x-layouts.app>
    <x-slot:headerTitle>
        Manajemen Ujian CBT
    </x-slot:headerTitle>

    <div class="space-y-6">
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

        @if (session('warning'))
            <x-alert variant="warning" dismissible>
                {{ session('warning') }}
            </x-alert>
        @endif

        <!-- Action & Navigation Header -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h1 class="text-xl sm:text-2xl font-extrabold text-zinc-900 tracking-tight">
                    Jadwal & Paket Ujian (CBT)
                </h1>
                <p class="text-xs text-zinc-500 mt-0.5">
                    Susun paket soal, tentukan kelas peserta, waktu mulai dan durasi, serta pantau pelaksanaan ujian secara realtime.
                </p>
            </div>

            <div class="flex items-center gap-2.5">
                <x-button href="{{ route('exams.create') }}" variant="primary" size="sm">
                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Buat Ujian Baru
                </x-button>
            </div>
        </div>

        <!-- 4-Card Summary Strip -->
        <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
            <x-card padding class="border-zinc-200">
                <div class="text-[11px] font-bold uppercase tracking-wider text-zinc-400">Total Ujian</div>
                <div class="mt-1 text-2xl font-extrabold text-zinc-900">{{ $stats['total'] }}</div>
                <div class="mt-1 text-xs text-zinc-400">Seluruh paket tersimpan</div>
            </x-card>

            <x-card padding class="border-zinc-200">
                <div class="text-[11px] font-bold uppercase tracking-wider text-emerald-600">Dipublikasikan / Aktif</div>
                <div class="mt-1 text-2xl font-extrabold text-emerald-700">{{ $stats['published'] }}</div>
                <div class="mt-1 text-xs text-zinc-400">Siap atau sedang dikerjakan</div>
            </x-card>

            <x-card padding class="border-zinc-200">
                <div class="text-[11px] font-bold uppercase tracking-wider text-blue-600">Terjadwal</div>
                <div class="mt-1 text-2xl font-extrabold text-blue-700">{{ $stats['scheduled'] }}</div>
                <div class="mt-1 text-xs text-zinc-400">Menunggu waktu pembukaan</div>
            </x-card>

            <x-card padding class="border-zinc-200">
                <div class="text-[11px] font-bold uppercase tracking-wider text-zinc-500">Draft & Ditutup</div>
                <div class="mt-1 text-2xl font-extrabold text-zinc-700">{{ $stats['draft'] + $stats['closed'] }}</div>
                <div class="mt-1 text-xs text-zinc-400">{{ $stats['draft'] }} draft, {{ $stats['closed'] }} ditutup</div>
            </x-card>
        </div>

        <!-- Filter & Search Bar -->
        <x-card padding class="border-zinc-200">
            <form action="{{ route('exams.index') }}" method="GET" class="grid grid-cols-1 sm:grid-cols-4 gap-3">
                <div>
                    <label class="block text-xs font-semibold text-zinc-600 mb-1">Cari Ujian</label>
                    <input
                        type="text"
                        name="search"
                        value="{{ request('search') }}"
                        placeholder="Judul ujian..."
                        class="w-full text-xs rounded-lg border-zinc-200 focus:border-brand-500 focus:ring-brand-500"
                    />
                </div>

                <div>
                    <label class="block text-xs font-semibold text-zinc-600 mb-1">Mata Pelajaran</label>
                    <select name="subject_id" class="w-full text-xs rounded-lg border-zinc-200 focus:border-brand-500 focus:ring-brand-500">
                        <option value="">Semua Mata Pelajaran</option>
                        @foreach ($subjects as $subj)
                            <option value="{{ $subj->id }}" {{ request('subject_id') == $subj->id ? 'selected' : '' }}>
                                {{ $subj->name }} ({{ $subj->code }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-zinc-600 mb-1">Status Ujian</label>
                    <select name="status" class="w-full text-xs rounded-lg border-zinc-200 focus:border-brand-500 focus:ring-brand-500">
                        <option value="">Semua Status</option>
                        @foreach ($statuses as $val => $lbl)
                            <option value="{{ $val }}" {{ request('status') == $val ? 'selected' : '' }}>
                                {{ $lbl }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="flex items-end gap-2">
                    <x-button type="submit" variant="secondary" size="sm" class="w-full justify-center">
                        <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                        Filter
                    </x-button>
                    @if(request()->anyFilled(['search', 'subject_id', 'status']))
                        <x-button href="{{ route('exams.index') }}" variant="ghost" size="sm">
                            Reset
                        </x-button>
                    @endif
                </div>
            </form>
        </x-card>

        <!-- Exams List -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
            @forelse ($exams as $exam)
                <x-card padding class="border-zinc-200 flex flex-col justify-between hover:shadow-sm transition-shadow">
                    <div class="space-y-3">
                        <div class="flex items-start justify-between gap-2">
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold tracking-wider uppercase
                                @if($exam->status === \App\Models\Exam::STATUS_PUBLISHED) bg-emerald-50 text-emerald-700 border border-emerald-200
                                @elseif($exam->status === \App\Models\Exam::STATUS_SCHEDULED) bg-blue-50 text-blue-700 border border-blue-200
                                @elseif($exam->status === \App\Models\Exam::STATUS_CLOSED) bg-zinc-100 text-zinc-600 border border-zinc-200
                                @else bg-amber-50 text-amber-700 border border-amber-200
                                @endif">
                                {{ $exam->statusLabel() }}
                            </span>
                            <div class="text-[11px] font-semibold text-zinc-400">
                                {{ $exam->duration_minutes }} Menit
                            </div>
                        </div>

                        <div>
                            <h2 class="font-bold text-base text-zinc-900 group-hover:text-brand-600 transition-colors line-clamp-1">
                                {{ $exam->title }}
                            </h2>
                            <p class="text-xs text-zinc-500 mt-0.5">
                                {{ $exam->subject?->name ?? 'Mata Pelajaran' }} &bull; Guru: {{ $exam->teacher?->name ?? '-' }}
                            </p>
                        </div>

                        <!-- Classrooms & Badges -->
                        <div class="flex flex-wrap gap-1">
                            @foreach ($exam->classrooms as $cls)
                                <span class="text-[10px] bg-zinc-100 text-zinc-700 px-2 py-0.5 rounded-full font-medium">
                                    {{ $cls->name }}
                                </span>
                            @endforeach
                        </div>

                        <!-- Meta Stats Strip -->
                        <div class="pt-3 border-t border-zinc-100 grid grid-cols-3 gap-2 text-center text-xs">
                            <div>
                                <div class="font-extrabold text-zinc-900">{{ $exam->question_items_count }}</div>
                                <div class="text-[10px] text-zinc-400">Butir Soal</div>
                            </div>
                            <div>
                                <div class="font-extrabold text-zinc-900">{{ (float) $exam->total_score }}</div>
                                <div class="text-[10px] text-zinc-400">Total Skor</div>
                            </div>
                            <div>
                                <div class="font-extrabold text-zinc-900">{{ $exam->attempts_count }}</div>
                                <div class="text-[10px] text-zinc-400">Peserta Mulai</div>
                            </div>
                        </div>

                        <!-- Schedule Timeline -->
                        <div class="text-[11px] text-zinc-500 bg-zinc-50 p-2.5 rounded-lg space-y-1">
                            <div class="flex items-center justify-between">
                                <span class="text-zinc-400">Mulai:</span>
                                <span class="font-medium text-zinc-800">{{ $exam->start_time->format('d M Y, H:i') }}</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-zinc-400">Berakhir:</span>
                                <span class="font-medium text-zinc-800">{{ $exam->end_time->format('d M Y, H:i') }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Action Controls -->
                    <div class="mt-4 pt-3 border-t border-zinc-100 flex items-center justify-between gap-2">
                        <div class="flex items-center gap-1.5">
                            <a href="{{ route('exams.questions', $exam) }}" class="inline-flex items-center text-xs font-bold text-brand-600 hover:text-brand-700 bg-brand-50 px-2.5 py-1.5 rounded-lg transition-colors" title="Kelola Butir Soal">
                                <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                </svg>
                                Soal ({{ $exam->question_items_count }})
                            </a>

                            <a href="{{ route('exams.grading.index', $exam) }}" class="inline-flex items-center text-xs font-semibold text-zinc-700 hover:text-zinc-900 bg-zinc-100 hover:bg-zinc-200 px-2.5 py-1.5 rounded-lg transition-colors" title="Hasil & Penilaian Peserta">
                                Hasil ({{ $exam->attempts_count }})
                            </a>
                        </div>

                        <div class="flex items-center gap-1">
                            <a href="{{ route('exams.edit', $exam) }}" class="p-1.5 text-zinc-500 hover:text-zinc-800 hover:bg-zinc-100 rounded-md" title="Pengaturan Ujian">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                                </svg>
                            </a>

                            @if($exam->status === \App\Models\Exam::STATUS_DRAFT)
                                <form action="{{ route('exams.publish', $exam) }}" method="POST" class="inline">
                                    @csrf
                                    <button type="submit" class="p-1.5 text-emerald-600 hover:text-emerald-800 hover:bg-emerald-50 rounded-md" title="Publikasikan Ujian">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                        </svg>
                                    </button>
                                </form>
                            @elseif($exam->status === \App\Models\Exam::STATUS_PUBLISHED)
                                <form action="{{ route('exams.close', $exam) }}" method="POST" onsubmit="return confirm('Tutup ujian ini sekarang? Siswa tidak akan dapat memulai sesi baru.');" class="inline">
                                    @csrf
                                    <button type="submit" class="p-1.5 text-amber-600 hover:text-amber-800 hover:bg-amber-50 rounded-md" title="Tutup Ujian">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                    </button>
                                </form>
                            @endif

                            <form action="{{ route('exams.destroy', $exam) }}" method="POST" onsubmit="return confirm('Hapus paket ujian ini?');" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="p-1.5 text-red-500 hover:text-red-700 hover:bg-red-50 rounded-md" title="Hapus Ujian">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                </button>
                            </form>
                        </div>
                    </div>
                </x-card>
            @empty
                <div class="col-span-full py-12 text-center text-zinc-400 bg-white rounded-xl border border-zinc-200">
                    <svg class="w-12 h-12 mx-auto text-zinc-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                    <p class="font-semibold text-zinc-600">Belum ada paket ujian yang dibuat</p>
                    <p class="text-xs text-zinc-400 mt-1">Buat paket ujian baru dan hubungkan butir soal dari bank soal.</p>
                    <div class="mt-4">
                        <x-button href="{{ route('exams.create') }}" variant="primary" size="sm">
                            Buat Ujian Pertama
                        </x-button>
                    </div>
                </div>
            @endforelse
        </div>

        @if ($exams->hasPages())
            <div class="p-4 border-t border-zinc-200">
                {{ $exams->links() }}
            </div>
        @endif
    </div>
</x-layouts.app>
