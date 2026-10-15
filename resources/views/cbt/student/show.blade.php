<x-layouts.app>
    <x-slot:headerTitle>
        Instruksi & Konfirmasi Ujian CBT
    </x-slot:headerTitle>

    <div class="max-w-3xl mx-auto space-y-6">
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

        <div>
            <a href="{{ route('student.exams.index') }}" class="inline-flex items-center text-xs font-semibold text-zinc-500 hover:text-zinc-800 transition-colors mb-1">
                <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Kembali ke Portal Ujian
            </a>
            <h1 class="text-xl sm:text-2xl font-extrabold text-zinc-900 tracking-tight">
                {{ $exam->title }}
            </h1>
            <p class="text-xs text-zinc-500 mt-0.5">
                Mata Pelajaran: <span class="font-bold text-zinc-700">{{ $exam->subject?->name }}</span> &bull; Guru Pengampu: {{ $exam->teacher?->name ?? '-' }}
            </p>
        </div>

        <!-- Exam Overview Card -->
        <x-card padding class="border-zinc-200 space-y-5">
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 text-center p-4 bg-zinc-50 rounded-xl border border-zinc-100">
                <div>
                    <span class="text-[10px] text-zinc-400 uppercase font-bold tracking-wider block">Durasi</span>
                    <span class="text-lg font-extrabold text-zinc-900">{{ $exam->duration_minutes }} Menit</span>
                </div>
                <div>
                    <span class="text-[10px] text-zinc-400 uppercase font-bold tracking-wider block">Total Soal</span>
                    <span class="text-lg font-extrabold text-zinc-900">{{ $exam->question_items_count }} Butir</span>
                </div>
                <div>
                    <span class="text-[10px] text-zinc-400 uppercase font-bold tracking-wider block">Nilai KKM</span>
                    <span class="text-lg font-extrabold text-zinc-900">{{ $exam->passing_grade }}</span>
                </div>
                <div>
                    <span class="text-[10px] text-zinc-400 uppercase font-bold tracking-wider block">Sisa Kesempatan</span>
                    <span class="text-lg font-extrabold {{ $remainingAttempts > 0 ? 'text-emerald-700' : 'text-red-600' }}">
                        {{ $remainingAttempts }} / {{ $exam->max_attempts }}
                    </span>
                </div>
            </div>

            <!-- Petunjuk & Aturan Ujian -->
            <div class="space-y-3">
                <h3 class="text-xs font-bold text-zinc-900 uppercase tracking-wider">Petunjuk Pengerjaan & Tata Tertib</h3>
                
                @if ($exam->description)
                    <div class="p-3 bg-zinc-50 rounded-lg text-xs text-zinc-700 whitespace-pre-line border border-zinc-200">
                        {{ $exam->description }}
                    </div>
                @endif

                <div class="space-y-2 text-xs text-zinc-600">
                    <div class="flex items-start gap-2">
                        <svg class="w-4 h-4 text-emerald-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        <span><strong>Timer Otomatis Server:</strong> Waktu ujian dihitung secara resmi oleh server. Jangan menutup browser atau mengganti tanggal perangkat Anda.</span>
                    </div>

                    <div class="flex items-start gap-2">
                        <svg class="w-4 h-4 text-emerald-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        <span><strong>Autosave Realtime:</strong> Setiap kali Anda memilih atau mengubah jawaban, sistem akan otomatis menyimpannya ke server.</span>
                    </div>

                    <div class="flex items-start gap-2">
                        <svg class="w-4 h-4 text-emerald-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        <span><strong>Tandai Ragu-ragu:</strong> Gunakan tombol bendera untuk menandai soal yang ingin ditinjau kembali sebelum waktu berakhir.</span>
                    </div>

                    <div class="flex items-start gap-2">
                        <svg class="w-4 h-4 text-amber-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                        <span><strong>Pengumpulan Akhir:</strong> Setelah Anda menekan tombol "Kumpulkan Ujian", sesi pengerjaan Anda akan terkunci secara permanen.</span>
                    </div>
                </div>
            </div>

            <!-- Start / Resume Button -->
            <div class="pt-4 border-t border-zinc-100 flex items-center justify-between">
                <div class="text-[11px] text-zinc-500">
                    Jadwal Ujian: {{ $exam->start_time->format('d M Y, H:i') }} s.d. {{ $exam->end_time->format('d M Y, H:i') }}
                </div>

                <div>
                    @if ($activeAttempt)
                        <x-button href="{{ route('student.exams.take', [$exam, $activeAttempt]) }}" variant="primary" size="md">
                            Lanjutkan Sesi Ujian Aktif &rarr;
                        </x-button>
                    @elseif ($remainingAttempts <= 0)
                        <div class="text-right">
                            <span class="text-xs font-bold text-red-600 block">Batas kesempatan telah habis</span>
                            @if ($attempts->isNotEmpty())
                                <x-button href="{{ route('student.exams.result', [$exam, $attempts->first()]) }}" variant="secondary" size="sm" class="mt-1">
                                    Lihat Lembar Hasil
                                </x-button>
                            @endif
                        </div>
                    @elseif ($isClosed)
                        <span class="text-xs font-bold text-zinc-400">Ujian Telah Ditutup</span>
                    @elseif (! $isAvailable)
                        <span class="text-xs font-bold text-blue-600">Ujian Belum Dimulai</span>
                    @else
                        <form action="{{ route('student.exams.start', $exam) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin memulai ujian sekarang? Timer akan segera berjalan.');">
                            @csrf
                            <x-button type="submit" variant="primary" size="md">
                                Mulai Mengerjakan Ujian Sekarang &rarr;
                            </x-button>
                        </form>
                    @endif
                </div>
            </div>
        </x-card>
    </div>
</x-layouts.app>
