<x-layouts.app>
    <x-slot:headerTitle>
        Hasil & Lembar Evaluasi CBT
    </x-slot:headerTitle>

    <div class="max-w-4xl mx-auto space-y-6">
        @if (session('success'))
            <x-alert variant="success" dismissible>
                {{ session('success') }}
            </x-alert>
        @endif

        @if (session('info'))
            <x-alert variant="info" dismissible>
                {{ session('info') }}
            </x-alert>
        @endif

        <div class="flex items-center justify-between">
            <div>
                <a href="{{ route('student.exams.index') }}" class="inline-flex items-center text-xs font-semibold text-zinc-500 hover:text-zinc-800 transition-colors mb-1">
                    <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                    Kembali ke Portal Ujian
                </a>
                <h1 class="text-xl sm:text-2xl font-extrabold text-zinc-900 tracking-tight">
                    Lembar Hasil: {{ $exam->title }}
                </h1>
                <p class="text-xs text-zinc-500 mt-0.5">
                    Mata Pelajaran: <span class="font-bold text-zinc-700">{{ $exam->subject?->name }}</span> &bull; Percobaan ke-{{ $attempt->attempt_number }}
                </p>
            </div>

            <x-button href="{{ route('student.exams.index') }}" variant="secondary" size="sm">
                Selesai & Keluar
            </x-button>
        </div>

        <!-- Grade Card -->
        @if ($canViewScore)
            <x-card padding class="border-zinc-200">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-6">
                    <div class="flex items-center gap-4">
                        <div class="w-16 h-16 rounded-2xl flex items-center justify-center font-black text-2xl shadow-xs
                            {{ $attempt->isPassed() ? 'bg-emerald-100 text-emerald-700' : 'bg-red-100 text-red-700' }}">
                            {{ round((float) $attempt->grade_percentage) }}%
                        </div>
                        <div class="space-y-1">
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-bold
                                {{ $attempt->isPassed() ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-red-50 text-red-700 border border-red-200' }}">
                                {{ $attempt->isPassed() ? 'LULUS KKM' : 'BELUM MEMENUHI KKM' }}
                            </span>
                            <h2 class="text-base font-extrabold text-zinc-900">
                                Total Nilai: {{ (float) $attempt->total_score }} / {{ (float) $exam->total_score }}
                            </h2>
                            <p class="text-xs text-zinc-400">
                                Nilai KKM: {{ $exam->passing_grade }} &bull; Waktu Kumpul: {{ $attempt->submitted_at?->format('d M Y, H:i') }}
                            </p>
                        </div>
                    </div>

                    <div class="text-right text-xs space-y-1 text-zinc-500 border-t sm:border-t-0 sm:border-l border-zinc-100 sm:pl-6 pt-3 sm:pt-0">
                        <div>Status Ujian: <span class="font-bold text-zinc-800">{{ $attempt->statusLabel() }}</span></div>
                        @if ($attempt->status === \App\Models\ExamAttempt::STATUS_SUBMITTED)
                            <div class="text-amber-600 font-semibold">* Terdapat butir esai yang sedang dinilai oleh guru</div>
                        @endif
                    </div>
                </div>
            </x-card>
        @else
            <x-card padding class="border-zinc-200 text-center py-8">
                <div class="w-12 h-12 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center mx-auto mb-3">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <h3 class="font-bold text-zinc-800 text-sm">Nilai Belum Dipublikasikan</h3>
                <p class="text-xs text-zinc-500 mt-1 max-w-md mx-auto">
                    Jawaban Anda telah berhasil tersimpan dan dikumpulkan. Hasil nilai akhir akan dipublikasikan oleh guru pengampu setelah batas waktu ujian berakhir atau penilaian manual selesai.
                </p>
            </x-card>
        @endif

        <!-- Discussion & Answer Review -->
        @if ($canViewExplanation)
            <div class="space-y-4">
                <div class="flex items-center justify-between">
                    <h3 class="text-sm font-bold text-zinc-900">Ulasan Butir Soal & Pembahasan</h3>
                    <span class="text-xs text-zinc-400">{{ count($questionsSnapshot) }} Soal</span>
                </div>

                @foreach ($questionsSnapshot as $idx => $qData)
                    @php
                        $questionId = $qData['id'] ?? null;
                        $answer = $answersByQuestionId->get($questionId);
                        $studentAns = $answer?->student_answer;
                        $isCorrect = $answer?->is_correct;
                        $score = $answer ? (float) $answer->score : 0.0;
                        $maxScore = (float) ($qData['score'] ?? 10.0);
                    @endphp

                    <x-card padding class="border-zinc-200 space-y-3">
                        <div class="flex items-start justify-between gap-3">
                            <div class="flex items-center gap-2">
                                <span class="w-6 h-6 rounded bg-zinc-100 text-zinc-800 font-bold flex items-center justify-center text-xs">
                                    {{ $idx + 1 }}
                                </span>
                                <span class="text-[10px] font-bold px-2 py-0.5 rounded
                                    @if ($isCorrect) bg-emerald-50 text-emerald-700
                                    @elseif (($qData['type'] ?? '') === \App\Models\ExamQuestion::TYPE_ESSAY) bg-purple-50 text-purple-700
                                    @else bg-red-50 text-red-700
                                    @endif">
                                    @if (($qData['type'] ?? '') === \App\Models\ExamQuestion::TYPE_ESSAY)
                                        Esai (Dinilai Guru)
                                    @elseif ($isCorrect)
                                        Benar
                                    @else
                                        Kurang Tepat
                                    @endif
                                </span>
                            </div>

                            <div class="text-xs font-extrabold text-zinc-900">
                                Skor: {{ $score }} / {{ $maxScore }}
                            </div>
                        </div>

                        <!-- Question Text -->
                        <div class="text-xs text-zinc-900 font-medium">
                            {{ strip_tags($qData['question'] ?? '') }}
                        </div>

                        <!-- Options Display -->
                        @if (!empty($qData['options']))
                            <div class="space-y-1.5 pl-2 text-xs">
                                @foreach ($qData['options'] as $opt)
                                    @php
                                        $optKey = $opt['key'] ?? '';
                                        $isStudentChoice = is_array($studentAns) ? in_array($optKey, $studentAns) : ($studentAns === $optKey);
                                    @endphp
                                    <div class="p-2 rounded flex items-center justify-between {{ $isStudentChoice ? 'bg-zinc-100 font-semibold' : 'text-zinc-600' }}">
                                        <span><strong class="w-4 inline-block">{{ $optKey }}.</strong> {{ $opt['text'] ?? '' }}</span>
                                        @if ($isStudentChoice)
                                            <span class="text-[10px] text-zinc-500 font-bold">Jawaban Anda</span>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="p-2.5 bg-zinc-50 rounded-lg text-xs text-zinc-800">
                                <span class="text-zinc-400 block text-[10px] font-bold">Jawaban Anda:</span>
                                {{ $studentAns ?: '(Kosong / Tidak dijawab)' }}
                            </div>
                        @endif

                        <!-- Explanation / Pembahasan -->
                        @if (!empty($qData['explanation']))
                            <div class="p-3 bg-blue-50/70 rounded-lg border border-blue-200 text-xs text-blue-900 space-y-1">
                                <span class="font-bold flex items-center gap-1 text-blue-800">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    Pembahasan Soal:
                                </span>
                                <p class="text-blue-950 whitespace-pre-line">{{ $qData['explanation'] }}</p>
                            </div>
                        @endif

                        @if ($answer?->feedback)
                            <div class="p-3 bg-purple-50/70 rounded-lg border border-purple-200 text-xs text-purple-900 space-y-1">
                                <span class="font-bold block text-purple-800">Catatan Guru:</span>
                                <p class="whitespace-pre-line">{{ $answer->feedback }}</p>
                            </div>
                        @endif
                    </x-card>
                @endforeach
            </div>
        @else
            <div class="p-4 bg-zinc-50 rounded-xl border border-zinc-200 text-center text-xs text-zinc-500">
                Kunci jawaban dan pembahasan soal belum diizinkan untuk ditampilkan oleh guru pengampu ujian ini.
            </div>
        @endif
    </div>
</x-layouts.app>
