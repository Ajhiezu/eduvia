<x-layouts.app>
    <x-slot:headerTitle>
        Periksa Tugas: {{ $submission->student?->user?->name ?? 'Siswa' }}
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
                <a href="{{ route('assignments.show', $assignment) }}" class="inline-flex items-center gap-1.5 text-xs text-zinc-500 hover:text-brand-600 mb-1">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                    Kembali ke Daftar Pengumpulan Tugas
                </a>
                <div class="flex items-center gap-2.5 flex-wrap">
                    <h1 class="text-xl sm:text-2xl font-extrabold text-zinc-900 tracking-tight">
                        Pemeriksaan Jawaban: {{ $submission->student?->user?->name }}
                    </h1>
                    @if ($submission->status === 'graded')
                        <x-badge variant="success">Sudah Dinilai</x-badge>
                    @elseif ($submission->status === 'revision_requested')
                        <x-badge variant="warning">Menunggu Revisi Siswa</x-badge>
                    @elseif ($submission->status === 'resubmitted')
                        <x-badge variant="info">Revisi Diterima</x-badge>
                    @else
                        @if ($submission->is_late)
                            <x-badge variant="danger">Terkumpul Terlambat</x-badge>
                        @else
                            <x-badge variant="info">Terkumpul</x-badge>
                        @endif
                    @endif
                </div>
                <div class="text-xs text-zinc-500 mt-1 flex items-center gap-2 flex-wrap">
                    <span class="font-medium text-zinc-800">{{ $assignment->title }}</span>
                    <span>•</span>
                    <span>{{ $assignment->teachingAssignment?->subject?->name }} ({{ $assignment->teachingAssignment?->classroom?->name }})</span>
                    <span>•</span>
                    <span>Pengiriman ke-{{ $submission->attempt_number }}</span>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Left 2 Cols: Student Submission Content & Attachments & Revision History -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Current Submission Card -->
                <x-card padding>
                    <div class="space-y-4">
                        <div class="flex items-center justify-between pb-3 border-b border-zinc-100">
                            <div>
                                <h2 class="text-xs font-bold uppercase tracking-wider text-zinc-500">
                                    Respon Jawaban Siswa
                                </h2>
                                <div class="text-[11px] text-zinc-400 mt-0.5">
                                    Dikirim pada {{ $submission->submitted_at ? $submission->submitted_at->format('d/m/Y H:i') : '-' }}
                                    @if ($submission->is_late)
                                        <span class="text-red-600 font-semibold ml-1">(Terlambat)</span>
                                    @else
                                        <span class="text-emerald-600 font-medium ml-1">(Tepat Waktu)</span>
                                    @endif
                                </div>
                            </div>
                            <div class="text-xs font-semibold text-zinc-600 bg-zinc-100 px-2.5 py-1 rounded">
                                Percobaan #{{ $submission->attempt_number }}
                            </div>
                        </div>

                        <!-- Student Notes / Text Answer -->
                        <div>
                            <span class="block text-xs font-semibold text-zinc-700 mb-1.5">
                                Teks Jawaban / Catatan Siswa:
                            </span>
                            @if ($submission->student_notes)
                                <div class="p-3.5 bg-zinc-50 rounded-lg text-xs text-zinc-800 whitespace-pre-line leading-relaxed border border-zinc-200">
                                    {!! nl2br(e($submission->student_notes)) !!}
                                </div>
                            @else
                                <p class="text-xs text-zinc-400 italic">Siswa tidak menyertakan teks catatan tambahan.</p>
                            @endif
                        </div>

                        <!-- Student Uploaded Files -->
                        <div>
                            <span class="block text-xs font-semibold text-zinc-700 mb-2">
                                Berkas Lampiran Siswa ({{ $submission->attachments->count() }}):
                            </span>
                            @if ($submission->attachments->isEmpty())
                                <p class="text-xs text-zinc-400 italic">Tidak ada berkas yang diunggah.</p>
                            @else
                                <div class="space-y-2">
                                    @foreach ($submission->attachments as $att)
                                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2.5 p-3 rounded-lg border border-zinc-200 bg-zinc-50/70 hover:bg-zinc-100 transition-colors">
                                            <div class="flex items-center gap-3 overflow-hidden min-w-0">
                                                <div class="w-8 h-8 rounded-lg bg-zinc-200 flex items-center justify-center text-zinc-600 shrink-0 uppercase font-extrabold text-[10px]">
                                                    {{ pathinfo($att->file_name, PATHINFO_EXTENSION) }}
                                                </div>
                                                <div class="truncate min-w-0">
                                                    <div class="font-semibold text-xs text-zinc-900 truncate">{{ $att->file_name }}</div>
                                                    <div class="text-[11px] text-zinc-400">{{ $att->formatted_size }}</div>
                                                </div>
                                            </div>
                                            <a
                                                href="{{ route('assignments.submissions.attachments.download', $att) }}"
                                                class="inline-flex items-center justify-center gap-1.5 text-xs font-semibold text-brand-600 hover:text-brand-700 bg-white border border-zinc-200 px-3 py-1.5 rounded-lg shadow-2xs shrink-0 self-start sm:self-auto"
                                            >
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                                                </svg>
                                                Unduh & Periksa
                                            </a>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>
                </x-card>

                <!-- Revision Histories (If Any) -->
                @if ($submission->histories->isNotEmpty())
                    <x-card padding>
                        <div class="space-y-3">
                            <h2 class="text-xs font-bold uppercase tracking-wider text-zinc-500 flex items-center gap-1.5">
                                <svg class="w-4 h-4 text-zinc-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                Riwayat Percobaan Sebelumnya ({{ $submission->histories->count() }})
                            </h2>

                            <div class="space-y-3">
                                @foreach ($submission->histories as $history)
                                    <div class="p-3 bg-zinc-50 border border-zinc-200 rounded-lg text-xs space-y-2">
                                        <div class="flex items-center justify-between text-zinc-600 font-semibold">
                                            <span>Percobaan ke-{{ $history->attempt_number }}</span>
                                            <span class="text-zinc-400 text-[11px]">{{ $history->created_at->format('d/m/Y H:i') }}</span>
                                        </div>
                                        @if ($history->student_notes)
                                            <div class="text-zinc-700 italic border-l-2 border-zinc-300 pl-2">
                                                "{{ $history->student_notes }}"
                                            </div>
                                        @endif
                                        @if ($history->attachments->isNotEmpty())
                                            <div class="text-[11px] text-zinc-500">
                                                Berkas terunggah: {{ $history->attachments->pluck('file_name')->join(', ') }}
                                            </div>
                                        @endif
                                        @if ($history->teacher_feedback)
                                            <div class="text-amber-700 bg-amber-50 p-2 rounded text-[11px]">
                                                <span class="font-bold">Catatan Guru:</span> {{ $history->teacher_feedback }}
                                            </div>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </x-card>
                @endif
            </div>

            <!-- Right 1 Col: Grading & Feedback Controls -->
            <div class="space-y-6">
                <!-- Grading Form Card -->
                <x-card padding>
                    <form action="{{ route('assignments.submissions.grade', [$assignment, $submission]) }}" method="POST" class="space-y-4">
                        @csrf
                        <div class="pb-2 border-b border-zinc-100 flex items-center justify-between">
                            <h2 class="text-xs font-bold uppercase tracking-wider text-zinc-900">
                                Penilaian Guru
                            </h2>
                            <span class="text-[11px] text-zinc-400">Skala 0 - {{ $assignment->max_score }}</span>
                        </div>

                        <!-- Score Input -->
                        <div>
                            <x-label for="score" required>
                                Nilai Angka
                            </x-label>
                            <div class="relative mt-1">
                                <input
                                    type="number"
                                    id="score"
                                    name="score"
                                    min="0"
                                    max="{{ $assignment->max_score }}"
                                    step="0.5"
                                    value="{{ old('score', $submission->score) }}"
                                    required
                                    class="w-full text-lg font-bold rounded-lg border border-zinc-300 text-zinc-900 py-2.5 px-3 focus:border-brand-500 focus:ring-2 focus:ring-brand-100"
                                    placeholder="Contoh: 85"
                                />
                                <div class="absolute right-3 top-3 text-xs text-zinc-400 font-semibold">
                                    / {{ $assignment->max_score }}
                                </div>
                            </div>
                        </div>

                        <!-- Feedback Input -->
                        <div>
                            <x-label for="teacher_feedback">
                                Catatan & Masukan Guru (Feedback)
                            </x-label>
                            <x-textarea
                                id="teacher_feedback"
                                name="teacher_feedback"
                                rows="4"
                                placeholder="Tuliskan ulasan pengerjaan, apresiasi, atau poin perbaikan untuk siswa..."
                            >{{ old('teacher_feedback', $submission->teacher_feedback) }}</x-textarea>
                        </div>

                        <!-- Publish Checkbox -->
                        <div class="pt-2">
                            <label class="flex items-start gap-2 text-xs text-zinc-700 cursor-pointer">
                                <input
                                    type="checkbox"
                                    name="publish_grade"
                                    value="1"
                                    {{ old('publish_grade', $submission->grade_published_at ? true : false) ? 'checked' : '' }}
                                    class="rounded text-brand-600 focus:ring-brand-500 mt-0.5"
                                />
                                <div>
                                    <span class="font-semibold text-zinc-900">Publikasikan nilai langsung ke siswa</span>
                                    <p class="text-[11px] text-zinc-400">Jika dicentang, siswa dapat langsung melihat nilai dan umpan balik ini di portalnya.</p>
                                </div>
                            </label>
                        </div>

                        <!-- Submit Button -->
                        <div class="pt-2">
                            <x-button type="submit" variant="primary" size="md" class="w-full justify-center">
                                Simpan Penilaian
                            </x-button>
                        </div>
                    </form>
                </x-card>

                <!-- Direct Publish Grade Action (If graded but unpublished) -->
                @if ($submission->score !== null && !$submission->grade_published_at)
                    <x-card padding class="border-amber-200 bg-amber-50/40">
                        <div class="space-y-3">
                            <div class="flex items-start gap-2">
                                <svg class="w-5 h-5 text-amber-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                </svg>
                                <div>
                                    <h3 class="text-xs font-bold text-amber-900">Nilai Tersimpan Sebagai Draft</h3>
                                    <p class="text-[11px] text-amber-700 mt-0.5">Nilai ({{ $submission->score }}) saat ini belum dipublikasikan ke siswa.</p>
                                </div>
                            </div>
                            <form action="{{ route('assignments.submissions.publish-grade', [$assignment, $submission]) }}" method="POST">
                                @csrf
                                <x-button type="submit" variant="primary" size="sm" class="w-full justify-center">
                                    Publikasikan Nilai Sekarang
                                </x-button>
                            </form>
                        </div>
                    </x-card>
                @endif

                <!-- Request Revision Card -->
                <x-card padding class="border-zinc-200">
                    <form action="{{ route('assignments.submissions.request-revision', [$assignment, $submission]) }}" method="POST" class="space-y-3" onsubmit="return confirm('Minta siswa mengulang atau merevisi jawaban tugas ini?');">
                        @csrf
                        <div class="pb-2 border-b border-zinc-100">
                            <h2 class="text-xs font-bold uppercase tracking-wider text-amber-700 flex items-center gap-1.5">
                                <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                </svg>
                                Minta Revisi Jawaban Siswa
                            </h2>
                            <p class="text-[11px] text-zinc-400 mt-0.5">
                                Jika jawaban belum memenuhi kriteria, Anda dapat meminta siswa melakukan perbaikan.
                            </p>
                        </div>

                        <div>
                            <x-label for="revision_feedback" required>
                                Catatan Arahan Revisi
                            </x-label>
                            <x-textarea
                                id="revision_feedback"
                                name="teacher_feedback"
                                rows="3"
                                required
                                placeholder="Jelaskan bagian apa yang harus diperbaiki oleh siswa..."
                            ></x-textarea>
                        </div>

                        <x-button type="submit" variant="secondary" size="sm" class="w-full justify-center text-amber-700 border-amber-300 hover:bg-amber-50">
                            Kirim Permintaan Revisi
                        </x-button>
                    </form>
                </x-card>
            </div>
        </div>
    </div>
</x-layouts.app>
