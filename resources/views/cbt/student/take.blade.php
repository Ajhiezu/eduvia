<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-zinc-100">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $exam->title }} — Ujian CBT Eduvia</title>
    
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=plus-jakarta-sans:400,500,600,700,800|inter:400,500,600,700&display=swap" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full flex flex-col font-sans text-zinc-800 antialiased selection:bg-brand-500 selection:text-white"
      x-data="cbtExamApp({
          examId: {{ $exam->id }},
          attemptId: {{ $attempt->id }},
          initialRemainingSeconds: {{ $remainingSeconds }},
          saveUrl: '{{ route('student.exams.save-answer', [$exam, $attempt]) }}',
          submitUrl: '{{ route('student.exams.submit', [$exam, $attempt]) }}',
          csrfToken: '{{ csrf_token() }}',
          questions: {{ json_encode($questions) }},
          initialAnswers: {{ json_encode($savedAnswers) }}
      })"
      @keydown.window="handleKeydown($event)">

    <!-- Top CBT Examination Header -->
    <header class="bg-white border-b border-zinc-200 sticky top-0 z-30 shadow-2xs">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-lg bg-brand-600 text-white font-extrabold flex items-center justify-center text-sm shadow-xs">
                    E
                </div>
                <div>
                    <h1 class="font-extrabold text-sm sm:text-base text-zinc-900 leading-tight truncate max-w-[200px] sm:max-w-md">
                        {{ $exam->title }}
                    </h1>
                    <p class="text-[11px] text-zinc-400">
                        {{ $exam->subject?->name }} &bull; {{ auth()->user()->name }}
                    </p>
                </div>
            </div>

            <!-- Authoritative Server Timer & Autosave Status -->
            <div class="flex items-center gap-4">
                <!-- Autosave indicator -->
                <div class="hidden sm:flex items-center gap-1.5 text-xs">
                    <template x-if="saveState === 'saving'">
                        <span class="inline-flex items-center text-zinc-500 gap-1 animate-pulse">
                            <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                            Menyimpan...
                        </span>
                    </template>
                    <template x-if="saveState === 'saved'">
                        <span class="inline-flex items-center text-emerald-600 gap-1 font-medium">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            Tersimpan (<span x-text="lastSavedTime"></span>)
                        </span>
                    </template>
                    <template x-if="saveState === 'error'">
                        <span class="inline-flex items-center text-red-600 gap-1 font-bold">
                            <span class="w-2 h-2 rounded-full bg-red-600"></span>
                            Gagal simpan (Mencoba ulang...)
                        </span>
                    </template>
                </div>

                <!-- Timer Badge -->
                <div class="flex items-center gap-2 px-3 py-1.5 rounded-lg border text-xs sm:text-sm font-extrabold tracking-wider"
                     :class="remainingSeconds <= 300 ? 'bg-red-50 text-red-700 border-red-200 animate-pulse' : 'bg-zinc-50 text-zinc-800 border-zinc-200'">
                    <svg class="w-4 h-4 text-zinc-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span x-text="formattedTimer">00:00:00</span>
                </div>

                <!-- Submit Button Trigger -->
                <button
                    type="button"
                    @click="showSubmitModal = true"
                    class="inline-flex items-center text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-700 px-3 py-2 rounded-lg transition-colors shadow-2xs"
                >
                    <svg class="w-4 h-4 mr-1 hidden sm:inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    Selesaikan Ujian
                </button>
            </div>
        </div>
    </header>

    <!-- Main Workspace -->
    <main class="flex-1 max-w-7xl w-full mx-auto p-4 sm:p-6 lg:p-8 flex flex-col lg:flex-row gap-6">
        <!-- Left: Active Question Screen -->
        <div class="flex-1 bg-white rounded-2xl border border-zinc-200 shadow-xs p-6 flex flex-col justify-between">
            <template x-if="currentQuestion">
                <div class="space-y-6">
                    <!-- Question Header & Review Toggle -->
                    <div class="flex items-center justify-between pb-4 border-b border-zinc-100">
                        <div class="flex items-center gap-2.5">
                            <span class="w-8 h-8 rounded-lg bg-zinc-900 text-white font-black flex items-center justify-center text-sm"
                                  x-text="currentIndex + 1">
                            </span>
                            <div>
                                <span class="text-xs font-bold text-zinc-800" x-text="getQuestionTypeLabel(currentQuestion.type)"></span>
                                <span class="text-[11px] text-zinc-400 block">Bobot Skor: <span x-text="currentQuestion.score"></span></span>
                            </div>
                        </div>

                        <!-- Flag / Ragu-ragu checkbox -->
                        <label class="inline-flex items-center gap-2 cursor-pointer px-3 py-1.5 rounded-lg border transition-colors"
                               :class="isCurrentMarkedForReview ? 'bg-amber-50 border-amber-300 text-amber-800' : 'bg-zinc-50 border-zinc-200 text-zinc-600 hover:bg-zinc-100'">
                            <input
                                type="checkbox"
                                :checked="isCurrentMarkedForReview"
                                @change="toggleReviewFlag()"
                                class="rounded text-amber-500 focus:ring-amber-500"
                            />
                            <span class="text-xs font-bold">Ragu-Ragu</span>
                        </label>
                    </div>

                    <!-- Question Prompt -->
                    <div class="text-sm sm:text-base text-zinc-900 leading-relaxed font-medium whitespace-pre-line"
                         x-text="currentQuestion.question">
                    </div>

                    <!-- Dynamic Options / Input based on question type -->
                    <div class="pt-4 border-t border-zinc-100 space-y-3">
                        <!-- Single Choice (Radio) -->
                        <template x-if="currentQuestion.type === 'multiple_choice_single'">
                            <div class="space-y-2.5">
                                <template x-for="opt in currentQuestion.options" :key="opt.key">
                                    <label class="flex items-start gap-3 p-3.5 rounded-xl border cursor-pointer transition-all"
                                           :class="answers[currentQuestion.id] === opt.key ? 'border-brand-500 bg-brand-50/50 text-brand-950 ring-1 ring-brand-500' : 'border-zinc-200 hover:border-zinc-300 bg-white text-zinc-800'">
                                        <input
                                            type="radio"
                                            :name="`question_${currentQuestion.id}`"
                                            :value="opt.key"
                                            :checked="answers[currentQuestion.id] === opt.key"
                                            @change="setAnswer(opt.key)"
                                            class="mt-0.5 text-brand-600 focus:ring-brand-500"
                                        />
                                        <div class="text-xs sm:text-sm font-semibold flex items-start gap-2">
                                            <span class="font-extrabold w-4" x-text="opt.key + '.'"></span>
                                            <span x-text="opt.text"></span>
                                        </div>
                                    </label>
                                </template>
                            </div>
                        </template>

                        <!-- Multiple Choice Multi (Checkboxes) -->
                        <template x-if="currentQuestion.type === 'multiple_choice_multi'">
                            <div class="space-y-2.5">
                                <p class="text-[11px] text-zinc-400 font-semibold mb-2">Pilih semua opsi yang benar:</p>
                                <template x-for="opt in currentQuestion.options" :key="opt.key">
                                    <label class="flex items-start gap-3 p-3.5 rounded-xl border cursor-pointer transition-all"
                                           :class="isMultiOptionSelected(opt.key) ? 'border-brand-500 bg-brand-50/50 text-brand-950 ring-1 ring-brand-500' : 'border-zinc-200 hover:border-zinc-300 bg-white text-zinc-800'">
                                        <input
                                            type="checkbox"
                                            :value="opt.key"
                                            :checked="isMultiOptionSelected(opt.key)"
                                            @change="toggleMultiAnswer(opt.key)"
                                            class="mt-0.5 rounded text-brand-600 focus:ring-brand-500"
                                        />
                                        <div class="text-xs sm:text-sm font-semibold flex items-start gap-2">
                                            <span class="font-extrabold w-4" x-text="opt.key + '.'"></span>
                                            <span x-text="opt.text"></span>
                                        </div>
                                    </label>
                                </template>
                            </div>
                        </template>

                        <!-- True / False -->
                        <template x-if="currentQuestion.type === 'true_false'">
                            <div class="grid grid-cols-2 gap-4">
                                <label class="flex items-center justify-center gap-2 p-4 rounded-xl border cursor-pointer font-bold text-sm transition-all"
                                       :class="answers[currentQuestion.id] === 'true' ? 'border-emerald-500 bg-emerald-50 text-emerald-800 ring-1 ring-emerald-500' : 'border-zinc-200 hover:border-zinc-300 text-zinc-700'">
                                    <input
                                        type="radio"
                                        :name="`question_${currentQuestion.id}`"
                                        value="true"
                                        :checked="answers[currentQuestion.id] === 'true'"
                                        @change="setAnswer('true')"
                                        class="text-emerald-600 focus:ring-emerald-500"
                                    />
                                    Benar (True)
                                </label>

                                <label class="flex items-center justify-center gap-2 p-4 rounded-xl border cursor-pointer font-bold text-sm transition-all"
                                       :class="answers[currentQuestion.id] === 'false' ? 'border-red-500 bg-red-50 text-red-800 ring-1 ring-red-500' : 'border-zinc-200 hover:border-zinc-300 text-zinc-700'">
                                    <input
                                        type="radio"
                                        :name="`question_${currentQuestion.id}`"
                                        value="false"
                                        :checked="answers[currentQuestion.id] === 'false'"
                                        @change="setAnswer('false')"
                                        class="text-red-600 focus:ring-red-500"
                                    />
                                    Salah (False)
                                </label>
                            </div>
                        </template>

                        <!-- Short Answer -->
                        <template x-if="currentQuestion.type === 'short_answer'">
                            <div>
                                <label class="block text-xs font-bold text-zinc-700 mb-1">Ketik Jawaban Singkat:</label>
                                <input
                                    type="text"
                                    :value="answers[currentQuestion.id] || ''"
                                    @input.debounce.400ms="setAnswer($event.target.value)"
                                    placeholder="Tuliskan jawaban Anda di sini..."
                                    class="w-full text-sm rounded-xl border-zinc-200 focus:border-brand-500 focus:ring-brand-500"
                                />
                            </div>
                        </template>

                        <!-- Essay -->
                        <template x-if="currentQuestion.type === 'essay'">
                            <div>
                                <label class="block text-xs font-bold text-zinc-700 mb-1">Tulis Jawaban Uraian / Esai:</label>
                                <textarea
                                    rows="6"
                                    :value="answers[currentQuestion.id] || ''"
                                    @input.debounce.500ms="setAnswer($event.target.value)"
                                    placeholder="Ketik uraian jawaban lengkap Anda di sini..."
                                    class="w-full text-sm rounded-xl border-zinc-200 focus:border-brand-500 focus:ring-brand-500"
                                ></textarea>
                            </div>
                        </template>
                    </div>
                </div>
            </template>

            <!-- Navigation Controls (Previous, Question Indicator, Next) -->
            <div class="mt-8 pt-4 border-t border-zinc-100 flex items-center justify-between">
                <button
                    type="button"
                    @click="prevQuestion()"
                    :disabled="currentIndex === 0"
                    class="inline-flex items-center text-xs font-bold px-4 py-2 rounded-lg border border-zinc-200 text-zinc-700 hover:bg-zinc-50 disabled:opacity-40 disabled:cursor-not-allowed transition-colors"
                >
                    &larr; Soal Sebelumnya
                </button>

                <div class="text-xs text-zinc-400 font-medium">
                    Soal <span class="font-bold text-zinc-800" x-text="currentIndex + 1"></span> dari <span class="font-bold text-zinc-800" x-text="questions.length"></span>
                </div>

                <button
                    type="button"
                    @click="nextQuestion()"
                    :disabled="currentIndex === questions.length - 1"
                    class="inline-flex items-center text-xs font-bold px-4 py-2 rounded-lg bg-zinc-900 hover:bg-zinc-800 text-white disabled:opacity-40 disabled:cursor-not-allowed transition-colors"
                >
                    Soal Berikutnya &rarr;
                </button>
            </div>
        </div>

        <!-- Right: Question Palette / Nav Drawer -->
        <aside class="w-full lg:w-80 bg-white rounded-2xl border border-zinc-200 shadow-xs p-5 flex flex-col justify-between">
            <div class="space-y-4">
                <div class="flex items-center justify-between pb-3 border-b border-zinc-100">
                    <h2 class="font-extrabold text-xs text-zinc-900 uppercase tracking-wider">
                        Navigasi Nomor Soal
                    </h2>
                    <span class="text-xs text-zinc-400 font-semibold" x-text="getAnsweredCount() + '/' + questions.length"></span>
                </div>

                <!-- Palette Legend -->
                <div class="grid grid-cols-2 gap-2 text-[11px] text-zinc-500 bg-zinc-50 p-2.5 rounded-xl border border-zinc-100">
                    <div class="flex items-center gap-1.5">
                        <span class="w-3 h-3 rounded bg-emerald-600"></span>
                        <span>Sudah Dijawab</span>
                    </div>
                    <div class="flex items-center gap-1.5">
                        <span class="w-3 h-3 rounded bg-amber-500"></span>
                        <span>Ragu-Ragu</span>
                    </div>
                    <div class="flex items-center gap-1.5">
                        <span class="w-3 h-3 rounded bg-white border border-zinc-300"></span>
                        <span>Belum Dijawab</span>
                    </div>
                    <div class="flex items-center gap-1.5">
                        <span class="w-3 h-3 rounded border-2 border-brand-500"></span>
                        <span>Sedang Dibuka</span>
                    </div>
                </div>

                <!-- Palette Numbers Grid -->
                <div class="grid grid-cols-5 gap-2 max-h-80 overflow-y-auto p-1">
                    <template x-for="(q, idx) in questions" :key="q.id">
                        <button
                            type="button"
                            @click="goToQuestion(idx)"
                            class="h-10 rounded-lg text-xs font-extrabold flex items-center justify-center relative transition-all"
                            :class="getPaletteClass(q.id, idx)"
                        >
                            <span x-text="idx + 1"></span>
                            <!-- Review dot indicator -->
                            <template x-if="reviewFlags[q.id]">
                                <span class="absolute top-1 right-1 w-2 h-2 rounded-full bg-amber-400 ring-1 ring-white"></span>
                            </template>
                        </button>
                    </template>
                </div>
            </div>

            <!-- Mobile/Quick Summary Strip -->
            <div class="mt-6 pt-4 border-t border-zinc-100">
                <button
                    type="button"
                    @click="showSubmitModal = true"
                    class="w-full text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-700 py-2.5 rounded-xl transition-colors shadow-xs"
                >
                    Kumpulkan Ujian Sekarang
                </button>
            </div>
        </aside>
    </main>

    <!-- Final Submission Confirmation Modal -->
    <div
        x-show="showSubmitModal"
        x-cloak
        class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4 bg-zinc-900/60 backdrop-blur-xs"
    >
        <div class="bg-white rounded-2xl max-w-md w-full p-6 space-y-5 shadow-xl border border-zinc-100"
             @click.outside="showSubmitModal = false">
            <div class="text-center space-y-2">
                <div class="w-12 h-12 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center mx-auto">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                </div>
                <h3 class="text-base font-extrabold text-zinc-900">Konfirmasi Pengumpulan Ujian</h3>
                <p class="text-xs text-zinc-500">
                    Pastikan Anda telah memeriksa kembali seluruh jawaban sebelum mengumpulkan sesi ujian.
                </p>
            </div>

            <!-- Summary Table of Attempt Status -->
            <div class="grid grid-cols-3 gap-2 text-center p-3 bg-zinc-50 rounded-xl border border-zinc-200 text-xs">
                <div>
                    <span class="text-[10px] text-zinc-400 block font-semibold">Terjawab</span>
                    <span class="text-base font-extrabold text-emerald-700" x-text="getAnsweredCount()"></span>
                </div>
                <div>
                    <span class="text-[10px] text-zinc-400 block font-semibold">Ragu-Ragu</span>
                    <span class="text-base font-extrabold text-amber-600" x-text="getReviewCount()"></span>
                </div>
                <div>
                    <span class="text-[10px] text-zinc-400 block font-semibold">Belum Dijawab</span>
                    <span class="text-base font-extrabold text-red-600" x-text="questions.length - getAnsweredCount()"></span>
                </div>
            </div>

            <template x-if="getReviewCount() > 0 || (questions.length - getAnsweredCount()) > 0">
                <p class="text-[11px] text-amber-700 bg-amber-50 p-2.5 rounded-lg border border-amber-200">
                    Peringatan: Masih ada butir soal yang ditandai ragu-ragu atau belum Anda jawab. Jawaban kosong bernilai 0.
                </p>
            </template>

            <form :action="submitUrl" method="POST" class="flex items-center gap-3 pt-2">
                <input type="hidden" name="_token" :value="csrfToken" />
                <button
                    type="button"
                    @click="showSubmitModal = false"
                    class="flex-1 text-xs font-semibold py-2.5 rounded-xl border border-zinc-200 text-zinc-700 hover:bg-zinc-50 transition-colors"
                >
                    Kembali Periksa
                </button>
                <button
                    type="submit"
                    class="flex-1 text-xs font-bold py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white transition-colors shadow-xs"
                >
                    Ya, Kumpulkan Ujian
                </button>
            </form>
        </div>
    </div>

    <!-- JavaScript CBT Engine -->
    <script>
        function cbtExamApp(config) {
            return {
                examId: config.examId,
                attemptId: config.attemptId,
                remainingSeconds: config.initialRemainingSeconds,
                saveUrl: config.saveUrl,
                submitUrl: config.submitUrl,
                csrfToken: config.csrfToken,
                questions: config.questions || [],
                currentIndex: 0,
                answers: {},
                reviewFlags: {},
                saveState: 'saved', // 'saving', 'saved', 'error'
                lastSavedTime: new Date().toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', second: '2-digit' }),
                showSubmitModal: false,
                timerInterval: null,

                init() {
                    // Populate initial answers & review flags from backend
                    if (config.initialAnswers) {
                        Object.keys(config.initialAnswers).forEach(qId => {
                            const ansObj = config.initialAnswers[qId];
                            this.answers[qId] = ansObj.student_answer;
                            if (ansObj.is_marked_for_review) {
                                this.reviewFlags[qId] = true;
                            }
                        });
                    }

                    // Start authoritative timer countdown
                    this.startTimer();
                },

                get currentQuestion() {
                    return this.questions[this.currentIndex] || null;
                },

                get isCurrentMarkedForReview() {
                    if (!this.currentQuestion) return false;
                    return !!this.reviewFlags[this.currentQuestion.id];
                },

                get formattedTimer() {
                    if (this.remainingSeconds <= 0) return '00:00:00';
                    const h = Math.floor(this.remainingSeconds / 3600);
                    const m = Math.floor((this.remainingSeconds % 3600) / 60);
                    const s = this.remainingSeconds % 60;
                    return [h, m, s].map(v => String(v).padStart(2, '0')).join(':');
                },

                startTimer() {
                    this.timerInterval = setInterval(() => {
                        if (this.remainingSeconds > 0) {
                            this.remainingSeconds--;
                        } else {
                            clearInterval(this.timerInterval);
                            this.handleTimeExpired();
                        }
                    }, 1000);
                },

                handleTimeExpired() {
                    alert('Waktu ujian telah berakhir! Sistem akan otomatis mengumpulkan jawaban Anda.');
                    window.location.reload();
                },

                goToQuestion(index) {
                    if (index >= 0 && index < this.questions.length) {
                        this.currentIndex = index;
                    }
                },

                nextQuestion() {
                    if (this.currentIndex < this.questions.length - 1) {
                        this.currentIndex++;
                    }
                },

                prevQuestion() {
                    if (this.currentIndex > 0) {
                        this.currentIndex--;
                    }
                },

                handleKeydown(e) {
                    // Ignore if typing in an input/textarea
                    if (['INPUT', 'TEXTAREA'].includes(e.target.tagName)) return;
                    if (e.key === 'ArrowRight') this.nextQuestion();
                    if (e.key === 'ArrowLeft') this.prevQuestion();
                },

                getQuestionTypeLabel(type) {
                    switch(type) {
                        case 'multiple_choice_single': return 'Pilihan Ganda';
                        case 'multiple_choice_multi': return 'Pilihan Ganda (Banyak Jawaban)';
                        case 'true_false': return 'Benar / Salah';
                        case 'short_answer': return 'Jawaban Singkat';
                        case 'essay': return 'Esai / Uraian';
                        default: return 'Soal';
                    }
                },

                isMultiOptionSelected(key) {
                    if (!this.currentQuestion) return false;
                    const ans = this.answers[this.currentQuestion.id];
                    return Array.isArray(ans) && ans.includes(key);
                },

                toggleMultiAnswer(key) {
                    if (!this.currentQuestion) return;
                    const qId = this.currentQuestion.id;
                    let currentList = Array.isArray(this.answers[qId]) ? [...this.answers[qId]] : [];
                    const idx = currentList.indexOf(key);
                    if (idx > -1) {
                        currentList.splice(idx, 1);
                    } else {
                        currentList.push(key);
                    }
                    this.setAnswer(currentList);
                },

                setAnswer(value) {
                    if (!this.currentQuestion) return;
                    const qId = this.currentQuestion.id;
                    this.answers[qId] = value;
                    this.persistAnswer(qId, value, !!this.reviewFlags[qId]);
                },

                toggleReviewFlag() {
                    if (!this.currentQuestion) return;
                    const qId = this.currentQuestion.id;
                    this.reviewFlags[qId] = !this.reviewFlags[qId];
                    this.persistAnswer(qId, this.answers[qId] || null, !!this.reviewFlags[qId]);
                },

                async persistAnswer(questionId, answer, isMarked) {
                    this.saveState = 'saving';
                    try {
                        const response = await fetch(this.saveUrl, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': this.csrfToken,
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify({
                                question_id: questionId,
                                answer: answer,
                                is_marked_for_review: isMarked
                            })
                        });

                        const data = await response.json();

                        if (response.ok && data.status === 'success') {
                            this.saveState = 'saved';
                            this.lastSavedTime = data.saved_at || new Date().toLocaleTimeString('id-ID');
                            if (typeof data.remaining_seconds === 'number') {
                                this.remainingSeconds = data.remaining_seconds;
                            }
                        } else if (data.status === 'expired') {
                            alert('Waktu ujian telah habis.');
                            window.location.href = data.redirect_url || this.submitUrl;
                        } else {
                            this.saveState = 'error';
                        }
                    } catch (err) {
                        console.error('Autosave failure:', err);
                        this.saveState = 'error';
                    }
                },

                getAnsweredCount() {
                    return Object.keys(this.answers).filter(k => {
                        const val = this.answers[k];
                        if (Array.isArray(val)) return val.length > 0;
                        return val !== null && val !== undefined && String(val).trim() !== '';
                    }).length;
                },

                getReviewCount() {
                    return Object.keys(this.reviewFlags).filter(k => !!this.reviewFlags[k]).length;
                },

                getPaletteClass(questionId, idx) {
                    const isCurrent = (this.currentIndex === idx);
                    const ans = this.answers[questionId];
                    const isAnswered = Array.isArray(ans) ? ans.length > 0 : (ans !== null && ans !== undefined && String(ans).trim() !== '');
                    const isReview = !!this.reviewFlags[questionId];

                    let classes = [];

                    if (isCurrent) {
                        classes.push('ring-2 ring-brand-500 scale-105 z-10');
                    }

                    if (isReview) {
                        classes.push('bg-amber-500 text-white font-bold');
                    } else if (isAnswered) {
                        classes.push('bg-emerald-600 text-white font-bold');
                    } else {
                        classes.push('bg-white border border-zinc-200 text-zinc-700 hover:bg-zinc-50');
                    }

                    return classes.join(' ');
                }
            };
        }
    </script>
</body>
</html>
