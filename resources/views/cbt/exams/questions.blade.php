<x-layouts.app>
    <x-slot:headerTitle>
        Penyusunan Butir Soal Ujian
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
                <a href="{{ route('exams.index') }}" class="inline-flex items-center text-xs font-semibold text-zinc-500 hover:text-zinc-800 transition-colors mb-1">
                    <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                    Kembali ke Daftar Ujian
                </a>
                <h1 class="text-xl sm:text-2xl font-extrabold text-zinc-900 tracking-tight">
                    Komposisi Soal: {{ $exam->title }}
                </h1>
                <p class="text-xs text-zinc-500 mt-0.5">
                    Mata Pelajaran: <span class="font-bold text-zinc-700">{{ $exam->subject?->name }}</span> &bull; Status: <span class="font-bold">{{ $exam->statusLabel() }}</span>
                </p>
            </div>

            <div class="flex items-center gap-2.5">
                @if ($exam->status === \App\Models\Exam::STATUS_DRAFT)
                    <form action="{{ route('exams.publish', $exam) }}" method="POST">
                        @csrf
                        <x-button type="submit" variant="primary" size="sm" :disabled="!$validationStatus['is_valid']">
                            <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            Publikasikan Ujian
                        </x-button>
                    </form>
                @elseif ($exam->status === \App\Models\Exam::STATUS_PUBLISHED)
                    <form action="{{ route('exams.close', $exam) }}" method="POST" onsubmit="return confirm('Tutup ujian ini?');">
                        @csrf
                        <x-button type="submit" variant="secondary" size="sm">
                            Tutup Ujian
                        </x-button>
                    </form>
                @endif
            </div>
        </div>

        <!-- Pre-publishing Validation Banner -->
        @if (! $validationStatus['is_valid'])
            <div class="p-4 bg-amber-50 rounded-xl border border-amber-200">
                <div class="flex items-start gap-3">
                    <svg class="w-5 h-5 text-amber-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                    <div>
                        <h4 class="text-xs font-bold text-amber-900">Ujian Belum Memenuhi Syarat Publikasi</h4>
                        <p class="text-[11px] text-amber-700 mt-0.5">Lengkapi butir berikut sebelum ujian dapat dipublikasikan untuk siswa:</p>
                        <ul class="list-disc list-inside mt-2 text-xs text-amber-800 space-y-0.5">
                            @foreach ($validationStatus['errors'] as $err)
                                <li>{{ $err }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        @else
            <div class="p-4 bg-emerald-50 rounded-xl border border-emerald-200 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <div>
                        <h4 class="text-xs font-bold text-emerald-900">Validasi Lengkap: Siap Dipublikasikan</h4>
                        <p class="text-[11px] text-emerald-700">Seluruh butir soal, kunci penilaian, kelas sasaran, dan jadwal telah terverifikasi aman.</p>
                    </div>
                </div>
                <div class="text-right">
                    <span class="text-xs font-extrabold text-emerald-800">{{ $currentItems->count() }} Soal &bull; Total Skor: {{ (float) $exam->total_score }}</span>
                </div>
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Left 2 Cols: Current Assigned Questions -->
            <div class="lg:col-span-2 space-y-4">
                <div class="flex items-center justify-between">
                    <h2 class="text-sm font-bold text-zinc-900 flex items-center gap-2">
                        Daftar Soal Terpilih
                        <span class="text-xs px-2 py-0.5 rounded-full bg-zinc-100 text-zinc-700 font-semibold">{{ $currentItems->count() }}</span>
                    </h2>
                    <span class="text-xs text-zinc-500 font-semibold">Total Nilai: <span class="text-brand-600 font-extrabold">{{ (float) $exam->total_score }}</span></span>
                </div>

                <div class="space-y-3">
                    @forelse ($currentItems as $index => $item)
                        @php
                            $q = $item->question;
                        @endphp
                        <x-card padding class="border-zinc-200 hover:border-zinc-300 transition-colors">
                            <div class="flex items-start justify-between gap-3">
                                <div class="flex items-start gap-3">
                                    <div class="w-7 h-7 rounded-lg bg-zinc-100 text-zinc-800 font-extrabold flex items-center justify-center text-xs shrink-0">
                                        {{ $index + 1 }}
                                    </div>
                                    <div class="space-y-1">
                                        <div class="flex items-center gap-2">
                                            <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-semibold
                                                @if($q->type === \App\Models\ExamQuestion::TYPE_MULTIPLE_CHOICE_SINGLE) bg-blue-50 text-blue-700
                                                @elseif($q->type === \App\Models\ExamQuestion::TYPE_MULTIPLE_CHOICE_MULTI) bg-indigo-50 text-indigo-700
                                                @elseif($q->type === \App\Models\ExamQuestion::TYPE_TRUE_FALSE) bg-emerald-50 text-emerald-700
                                                @elseif($q->type === \App\Models\ExamQuestion::TYPE_SHORT_ANSWER) bg-amber-50 text-amber-700
                                                @else bg-purple-50 text-purple-700
                                                @endif">
                                                {{ $q->typeLabel() }}
                                            </span>
                                            <span class="text-[11px] text-zinc-400">ID #{{ $q->id }}</span>
                                        </div>

                                        <p class="text-xs text-zinc-900 font-medium">
                                            {{ strip_tags($q->question) }}
                                        </p>

                                        @if ($q->type === \App\Models\ExamQuestion::TYPE_MULTIPLE_CHOICE_SINGLE && !empty($q->options))
                                            <div class="grid grid-cols-2 gap-1.5 pt-1">
                                                @foreach ($q->options as $opt)
                                                    <div class="text-[11px] px-2 py-1 rounded {{ ($opt['key'] ?? '') === $q->correct_answer ? 'bg-emerald-50 text-emerald-800 font-bold border border-emerald-200' : 'bg-zinc-50 text-zinc-600' }}">
                                                        {{ $opt['key'] ?? '' }}. {{ Str::limit($opt['text'] ?? '', 25) }}
                                                    </div>
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>
                                </div>

                                <div class="text-right flex flex-col items-end gap-2 shrink-0">
                                    <div class="text-xs font-bold text-zinc-900 bg-zinc-50 px-2 py-1 rounded-md border border-zinc-200">
                                        Skor: {{ (float) $item->score }}
                                    </div>

                                    @if ($exam->status !== \App\Models\Exam::STATUS_CLOSED)
                                        <form action="{{ route('exams.questions.remove', [$exam, $item]) }}" method="POST" onsubmit="return confirm('Hapus soal ini dari paket ujian?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="p-1 text-zinc-400 hover:text-red-600 rounded transition-colors" title="Keluarkan Soal">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                </svg>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </div>
                        </x-card>
                    @empty
                        <div class="p-8 text-center bg-white rounded-xl border border-dashed border-zinc-300 text-zinc-400">
                            <p class="font-bold text-zinc-600 text-xs">Belum ada butir soal pada ujian ini</p>
                            <p class="text-[11px] mt-1">Pilih soal dari bank soal di panel kanan untuk menambahkannya ke ujian.</p>
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- Right 1 Col: Add From Bank Soal -->
            <div class="space-y-4">
                <div class="flex items-center justify-between">
                    <h2 class="text-sm font-bold text-zinc-900">Pilih dari Bank Soal</h2>
                    <a href="{{ route('exam-questions.create') }}" target="_blank" class="text-xs font-bold text-brand-600 hover:text-brand-700">
                        + Buat Baru
                    </a>
                </div>

                <x-card padding class="border-zinc-200 space-y-3">
                    <p class="text-[11px] text-zinc-500">
                        Hanya menampilkan soal aktif untuk mata pelajaran <span class="font-bold text-zinc-800">{{ $exam->subject?->name }}</span> yang belum dimasukkan ke paket ini.
                    </p>

                    <div class="space-y-2.5 max-h-[600px] overflow-y-auto pr-1">
                        @forelse ($availableQuestions as $aq)
                            <div class="p-2.5 rounded-lg border border-zinc-200 hover:border-brand-300 bg-white transition-all space-y-2">
                                <div class="flex items-center justify-between text-[10px]">
                                    <span class="font-bold text-zinc-500">{{ $aq->typeLabel() }}</span>
                                    <span class="font-semibold text-zinc-400">Bobot: {{ $aq->default_score }}</span>
                                </div>

                                <p class="text-xs text-zinc-800 font-medium line-clamp-2">
                                    {{ strip_tags($aq->question) }}
                                </p>

                                <form action="{{ route('exams.questions.add', $exam) }}" method="POST" class="pt-1 border-t border-zinc-100 flex items-center justify-between">
                                    @csrf
                                    <input type="hidden" name="exam_question_id" value="{{ $aq->id }}" />
                                    <div class="flex items-center gap-1.5">
                                        <label class="text-[10px] text-zinc-400">Skor:</label>
                                        <input
                                            type="number"
                                            name="score"
                                            value="{{ $aq->default_score }}"
                                            step="0.5"
                                            min="0.5"
                                            class="w-16 text-[11px] py-0.5 px-1.5 rounded border-zinc-200"
                                        />
                                    </div>
                                    <button type="submit" class="inline-flex items-center text-xs font-bold text-white bg-brand-600 hover:bg-brand-700 px-2 py-1 rounded transition-colors">
                                        + Tambahkan
                                    </button>
                                </form>
                            </div>
                        @empty
                            <div class="p-4 text-center text-zinc-400 text-xs">
                                Tidak ada soal yang tersedia untuk ditambahkan.
                            </div>
                        @endforelse
                    </div>
                </x-card>
            </div>
        </div>
    </div>
</x-layouts.app>
