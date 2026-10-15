<x-layouts.app>
    <x-slot:headerTitle>
        Portal Ujian CBT Siswa
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

        @if (session('info'))
            <x-alert variant="info" dismissible>
                {{ session('info') }}
            </x-alert>
        @endif

        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h1 class="text-xl sm:text-2xl font-extrabold text-zinc-900 tracking-tight">
                    Portal Ujian CBT (Computer Based Test)
                </h1>
                <p class="text-xs text-zinc-500 mt-0.5">
                    Ikuti asesmen digital terpadu, ujian harian, sumatif tengah semester, dan penilaian akhir sekolah.
                </p>
            </div>
        </div>

        <!-- Tab Navigation -->
        <div class="flex items-center gap-2 border-b border-zinc-200">
            <a href="{{ route('student.exams.index', ['tab' => 'available']) }}"
               class="px-4 py-2.5 text-xs font-bold border-b-2 transition-colors {{ $filter === 'available' ? 'border-brand-600 text-brand-600' : 'border-transparent text-zinc-500 hover:text-zinc-800' }}">
                Tersedia & Sedang Berlangsung
            </a>
            <a href="{{ route('student.exams.index', ['tab' => 'upcoming']) }}"
               class="px-4 py-2.5 text-xs font-bold border-b-2 transition-colors {{ $filter === 'upcoming' ? 'border-brand-600 text-brand-600' : 'border-transparent text-zinc-500 hover:text-zinc-800' }}">
                Akan Datang (Terjadwal)
            </a>
            <a href="{{ route('student.exams.index', ['tab' => 'completed']) }}"
               class="px-4 py-2.5 text-xs font-bold border-b-2 transition-colors {{ $filter === 'completed' ? 'border-brand-600 text-brand-600' : 'border-transparent text-zinc-500 hover:text-zinc-800' }}">
                Riwayat Selesai
            </a>
        </div>

        <!-- Exam Cards Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
            @forelse ($exams as $exam)
                @php
                    $attemptsForThisExam = $myAttempts->get($exam->id, collect());
                    $activeAttempt = $attemptsForThisExam->firstWhere('status', \App\Models\ExamAttempt::STATUS_IN_PROGRESS);
                    $attemptsUsed = $attemptsForThisExam->count();
                    $remainingAttempts = max(0, $exam->max_attempts - $attemptsUsed);
                    $bestAttempt = $attemptsForThisExam->whereIn('status', [\App\Models\ExamAttempt::STATUS_SUBMITTED, \App\Models\ExamAttempt::STATUS_GRADED])->sortByDesc('total_score')->first();
                @endphp

                <x-card padding class="border-zinc-200 flex flex-col justify-between hover:border-brand-300 transition-all hover:shadow-xs">
                    <div class="space-y-3">
                        <div class="flex items-start justify-between gap-2">
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold tracking-wider uppercase
                                @if($activeAttempt) bg-amber-50 text-amber-700 border border-amber-200
                                @elseif($exam->isAvailableNow()) bg-emerald-50 text-emerald-700 border border-emerald-200
                                @else bg-zinc-100 text-zinc-600 border border-zinc-200
                                @endif">
                                {{ $activeAttempt ? 'Sesi Sedang Berjalan' : ($exam->isAvailableNow() ? 'Tersedia' : 'Terjadwal') }}
                            </span>

                            <div class="text-[11px] font-bold text-zinc-600 bg-zinc-100 px-2 py-0.5 rounded">
                                {{ $exam->duration_minutes }} Menit
                            </div>
                        </div>

                        <div>
                            <h2 class="font-bold text-base text-zinc-900 line-clamp-1">
                                {{ $exam->title }}
                            </h2>
                            <p class="text-xs text-zinc-500 mt-0.5">
                                {{ $exam->subject?->name }} &bull; Guru: {{ $exam->teacher?->name ?? '-' }}
                            </p>
                        </div>

                        <div class="pt-2 border-t border-zinc-100 grid grid-cols-2 gap-2 text-xs">
                            <div>
                                <span class="text-[10px] text-zinc-400 block">Jumlah Butir Soal</span>
                                <span class="font-extrabold text-zinc-800">{{ $exam->question_items_count }} Soal</span>
                            </div>
                            <div>
                                <span class="text-[10px] text-zinc-400 block">Sisa Percobaan</span>
                                <span class="font-extrabold text-zinc-800">{{ $remainingAttempts }} / {{ $exam->max_attempts }} kali</span>
                            </div>
                        </div>

                        <div class="text-[11px] text-zinc-500 bg-zinc-50 p-2 rounded-lg space-y-1">
                            <div class="flex items-center justify-between">
                                <span class="text-zinc-400">Jadwal Mulai:</span>
                                <span class="font-medium text-zinc-700">{{ $exam->start_time->format('d M, H:i') }}</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-zinc-400">Batas Akhir:</span>
                                <span class="font-medium text-zinc-700">{{ $exam->end_time->format('d M, H:i') }}</span>
                            </div>
                        </div>

                        @if ($bestAttempt && $exam->show_result_immediately)
                            <div class="p-2 rounded-lg bg-emerald-50 border border-emerald-200 text-xs text-emerald-800 flex items-center justify-between">
                                <span class="font-medium">Skor Terbaik Anda:</span>
                                <span class="font-extrabold">{{ (float) $bestAttempt->total_score }} ({{ (float) $bestAttempt->grade_percentage }}%)</span>
                            </div>
                        @endif
                    </div>

                    <div class="mt-4 pt-3 border-t border-zinc-100 flex items-center justify-between">
                        @if ($activeAttempt)
                            <x-button href="{{ route('student.exams.take', [$exam, $activeAttempt]) }}" variant="primary" size="sm" class="w-full justify-center">
                                Lanjutkan Pengerjaan &rarr;
                            </x-button>
                        @elseif ($bestAttempt && $remainingAttempts <= 0)
                            <x-button href="{{ route('student.exams.result', [$exam, $bestAttempt]) }}" variant="secondary" size="sm" class="w-full justify-center">
                                Lihat Hasil Ujian
                            </x-button>
                        @else
                            <x-button href="{{ route('student.exams.show', $exam) }}" variant="primary" size="sm" class="w-full justify-center">
                                Masuk ke Ujian &rarr;
                            </x-button>
                        @endif
                    </div>
                </x-card>
            @empty
                <div class="col-span-full py-12 text-center text-zinc-400 bg-white rounded-xl border border-zinc-200">
                    <svg class="w-12 h-12 mx-auto text-zinc-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                    <p class="font-semibold text-zinc-600">Tidak ada ujian pada kategori ini</p>
                    <p class="text-xs text-zinc-400 mt-1">Ujian yang dijadwalkan oleh guru Anda akan tampil otomatis di sini.</p>
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
