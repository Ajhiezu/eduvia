<x-layouts.app>
    <x-slot:headerTitle>
        Pemeriksaan Lembar Jawaban Siswa
    </x-slot:headerTitle>

    <div class="max-w-4xl mx-auto space-y-6">
        @if (session('success'))
            <x-alert variant="success" dismissible>
                {{ session('success') }}
            </x-alert>
        @endif

        @if ($errors->any())
            <x-alert variant="danger" dismissible>
                <ul class="list-disc list-inside text-xs">
                    @foreach ($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </x-alert>
        @endif

        <!-- Action & Navigation Header -->
        <div class="flex items-center justify-between">
            <div>
                <a href="{{ route('exams.grading.index', $exam) }}" class="inline-flex items-center text-xs font-semibold text-zinc-500 hover:text-zinc-800 transition-colors mb-1">
                    <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                    Kembali ke Rekap Nilai
                </a>
                <h1 class="text-xl sm:text-2xl font-extrabold text-zinc-900 tracking-tight">
                    Lembar Jawaban: {{ $attempt->student?->name }}
                </h1>
                <p class="text-xs text-zinc-500 mt-0.5">
                    Ujian: <span class="font-bold text-zinc-800">{{ $exam->title }}</span> &bull; Percobaan ke-{{ $attempt->attempt_number }}
                </p>
            </div>

            <div class="text-right">
                <div class="text-2xl font-extrabold text-zinc-900">
                    {{ (float) $attempt->total_score }} <span class="text-xs font-normal text-zinc-400">/ {{ (float) $exam->total_score }}</span>
                </div>
                <div class="text-xs font-bold {{ $attempt->isPassed() ? 'text-emerald-600' : 'text-zinc-500' }}">
                    Persentase: {{ (float) $attempt->grade_percentage }}% (KKM: {{ $exam->passing_grade }})
                </div>
            </div>
        </div>

        <!-- Student Attempt Metadata Banner -->
        <x-card padding class="border-zinc-200 grid grid-cols-2 sm:grid-cols-4 gap-4 text-xs">
            <div>
                <span class="text-zinc-400 block text-[11px]">Nama Siswa:</span>
                <span class="font-bold text-zinc-800">{{ $attempt->student?->name }}</span>
            </div>
            <div>
                <span class="text-zinc-400 block text-[11px]">Kelas:</span>
                <span class="font-bold text-zinc-800">{{ $attempt->student?->classroom?->name ?? '-' }}</span>
            </div>
            <div>
                <span class="text-zinc-400 block text-[11px]">Waktu Mulai:</span>
                <span class="font-medium text-zinc-800">{{ $attempt->started_at?->format('d/m/Y H:i:s') }}</span>
            </div>
            <div>
                <span class="text-zinc-400 block text-[11px]">Waktu Selesai:</span>
                <span class="font-medium text-zinc-800">{{ $attempt->submitted_at?->format('d/m/Y H:i:s') ?? '-' }}</span>
            </div>
        </x-card>

        <form action="{{ route('exams.grading.grade', [$exam, $attempt]) }}" method="POST">
            @csrf

            <div class="space-y-4">
                @foreach ($questionsSnapshot as $idx => $qData)
                    @php
                        $questionId = $qData['id'] ?? null;
                        $answer = $answersByQuestionId->get($questionId);
                        $studentAns = $answer?->student_answer;
                        $maxScore = (float) ($qData['score'] ?? 10.0);
                        $currentScore = $answer ? (float) $answer->score : 0.0;
                        $isObjective = in_array($qData['type'] ?? '', [
                            \App\Models\ExamQuestion::TYPE_MULTIPLE_CHOICE_SINGLE,
                            \App\Models\ExamQuestion::TYPE_MULTIPLE_CHOICE_MULTI,
                            \App\Models\ExamQuestion::TYPE_TRUE_FALSE,
                            \App\Models\ExamQuestion::TYPE_SHORT_ANSWER
                        ]);
                    @endphp

                    <x-card padding class="border-zinc-200 space-y-3">
                        <div class="flex items-start justify-between gap-3">
                            <div class="flex items-center gap-2">
                                <span class="w-6 h-6 rounded bg-zinc-100 text-zinc-800 font-bold flex items-center justify-center text-xs">
                                    {{ $idx + 1 }}
                                </span>
                                <span class="text-[10px] font-bold px-2 py-0.5 rounded
                                    @if(($qData['type'] ?? '') === \App\Models\ExamQuestion::TYPE_ESSAY) bg-purple-50 text-purple-700
                                    @else bg-blue-50 text-blue-700
                                    @endif">
                                    {{ ($qData['type'] ?? '') === \App\Models\ExamQuestion::TYPE_ESSAY ? 'Esai / Uraian' : 'Objektif' }}
                                </span>
                            </div>

                            <div class="text-xs font-semibold text-zinc-500">
                                Skor Maksimal: <span class="font-bold text-zinc-800">{{ $maxScore }}</span>
                            </div>
                        </div>

                        <!-- Question Text -->
                        <div class="text-xs text-zinc-900 font-medium bg-zinc-50/50 p-3 rounded-lg border border-zinc-100">
                            {{ strip_tags($qData['question'] ?? '') }}
                        </div>

                        <!-- Options for Multiple Choice -->
                        @if (!empty($qData['options']))
                            <div class="space-y-1.5 pl-2">
                                @foreach ($qData['options'] as $opt)
                                    @php
                                        $optKey = $opt['key'] ?? '';
                                        $isChosen = is_array($studentAns) ? in_array($optKey, $studentAns) : ($studentAns === $optKey);
                                    @endphp
                                    <div class="text-xs flex items-center gap-2 p-1.5 rounded {{ $isChosen ? 'bg-blue-50 border border-blue-200 text-blue-900 font-semibold' : 'text-zinc-600' }}">
                                        <span class="w-5 text-center font-bold">{{ $optKey }}.</span>
                                        <span>{{ $opt['text'] ?? '' }}</span>
                                        @if ($isChosen)
                                            <span class="ml-auto text-[10px] text-blue-600 font-bold">Dipilih Siswa</span>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        @endif

                        <!-- Student Answer Display -->
                        <div class="pt-2 border-t border-zinc-100">
                            <label class="block text-[11px] font-bold text-zinc-500 mb-1">Jawaban Siswa:</label>
                            @if (($qData['type'] ?? '') === \App\Models\ExamQuestion::TYPE_ESSAY)
                                <div class="p-3 bg-zinc-50 rounded-lg border border-zinc-200 text-xs text-zinc-900 whitespace-pre-line min-h-[60px]">
                                    {{ $studentAns ?: '(Tidak dijawab oleh siswa)' }}
                                </div>
                            @elseif (!empty($qData['options']))
                                <div class="text-xs font-bold {{ $answer?->is_correct ? 'text-emerald-700' : 'text-red-600' }}">
                                    Jawaban Terpilih: {{ is_array($studentAns) ? implode(', ', $studentAns) : ($studentAns ?: '-') }}
                                    @if ($answer?->is_correct)
                                        <span class="ml-2 text-emerald-600 text-[11px] font-semibold">(Benar &bull; Auto-graded)</span>
                                    @else
                                        <span class="ml-2 text-red-500 text-[11px] font-semibold">(Kurang tepat &bull; Auto-graded)</span>
                                    @endif
                                </div>
                            @else
                                <div class="text-xs font-bold text-zinc-800">
                                    {{ $studentAns ?: '(Kosong)' }}
                                </div>
                            @endif
                        </div>

                        <!-- Grading Controls -->
                        <div class="pt-3 border-t border-zinc-100 bg-zinc-50/80 -mx-4 -mb-4 p-4 rounded-b-xl flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                            <div class="flex-1">
                                <label class="block text-[11px] font-bold text-zinc-600 mb-1">Catatan / Feedback Guru:</label>
                                <input
                                    type="text"
                                    name="feedbacks[{{ $questionId }}]"
                                    value="{{ $answer?->feedback }}"
                                    placeholder="Berikan umpan balik atau apresiasi..."
                                    class="w-full text-xs rounded-lg border-zinc-200 focus:border-brand-500 focus:ring-brand-500"
                                />
                            </div>

                            <div class="flex items-center gap-2 shrink-0">
                                <label class="text-xs font-bold text-zinc-700">Skor Diperoleh:</label>
                                <input
                                    type="number"
                                    name="scores[{{ $questionId }}]"
                                    value="{{ $currentScore }}"
                                    step="0.5"
                                    min="0"
                                    max="{{ $maxScore }}"
                                    class="w-20 text-xs font-extrabold text-zinc-900 rounded-lg border-zinc-200 focus:border-brand-500 focus:ring-brand-500 text-center"
                                />
                                <span class="text-xs text-zinc-400">/ {{ $maxScore }}</span>
                            </div>
                        </div>
                    </x-card>
                @endforeach
            </div>

            <div class="mt-6 flex items-center justify-between p-4 bg-white rounded-xl border border-zinc-200 shadow-sm sticky bottom-4 z-10">
                <div class="text-xs text-zinc-500">
                    Pastikan seluruh skor esai dan masukan telah terisi dengan adil dan akurat.
                </div>
                <div class="flex items-center gap-3">
                    <x-button href="{{ route('exams.grading.index', $exam) }}" variant="ghost" size="sm">
                        Batal
                    </x-button>
                    <x-button type="submit" variant="primary" size="sm">
                        Simpan & Tetapkan Nilai Akhir
                    </x-button>
                </div>
            </div>
        </form>
    </div>
</x-layouts.app>
