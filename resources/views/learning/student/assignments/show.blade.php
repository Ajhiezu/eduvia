<x-layouts.app>
    <x-slot:headerTitle>
        Tugas: {{ $assignment->title }}
    </x-slot:headerTitle>

    <div class="max-w-4xl mx-auto space-y-6">
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

        <!-- Navigation & Breadcrumb -->
        <div>
            <a href="{{ route('student.assignments.index') }}" class="inline-flex items-center gap-1.5 text-xs text-zinc-500 hover:text-brand-600 mb-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Kembali ke Daftar Tugas
            </a>
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div>
                    <div class="flex items-center gap-2 flex-wrap mb-1">
                        <span class="text-xs font-bold text-brand-600 bg-brand-50 px-2.5 py-0.5 rounded">
                            {{ $assignment->teachingAssignment?->subject?->name }}
                        </span>
                        <span class="text-xs font-semibold text-zinc-600 bg-zinc-100 px-2.5 py-0.5 rounded">
                            Maks. Nilai: {{ $assignment->max_score }}
                        </span>
                    </div>
                    <h1 class="text-xl sm:text-2xl font-extrabold text-zinc-900 tracking-tight">
                        {{ $assignment->title }}
                    </h1>
                    <div class="text-xs text-zinc-500 mt-1">
                        Guru Pengampu: <span class="font-semibold text-zinc-700">{{ $assignment->teacher?->name }}</span> • Tenggat:
                        <span class="font-bold {{ $assignment->isPastDue() ? 'text-red-600' : 'text-zinc-800' }}">
                            {{ $assignment->due_date ? $assignment->due_date->format('d/m/Y H:i') : 'Tanpa Batas' }}
                        </span>
                    </div>
                </div>

                <!-- Status Badge -->
                <div class="shrink-0">
                    @if (!$submission)
                        <x-badge variant="neutral">Belum Dikerjakan</x-badge>
                    @elseif ($submission->status === 'draft')
                        <x-badge variant="warning">Draft Tersimpan</x-badge>
                    @elseif ($submission->status === 'revision_requested')
                        <x-badge variant="warning">Perlu Revisi Jawaban</x-badge>
                    @elseif ($submission->status === 'resubmitted')
                        <x-badge variant="info">Revisi Terkirim</x-badge>
                    @elseif ($submission->status === 'graded')
                        <x-badge variant="success">Sudah Dinilai</x-badge>
                    @else
                        @if ($submission->is_late)
                            <x-badge variant="danger">Terkumpul (Terlambat)</x-badge>
                        @else
                            <x-badge variant="info">Terkumpul</x-badge>
                        @endif
                    @endif
                </div>
            </div>
        </div>

        <!-- Grade & Teacher Feedback Banner (Only if published!) -->
        @if ($submission && $submission->isGradePublished())
            <div class="p-4 bg-emerald-50 border border-emerald-200 rounded-xl space-y-2">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <div class="w-8 h-8 rounded-full bg-emerald-100 text-emerald-700 flex items-center justify-center font-bold">
                            ✓
                        </div>
                        <div>
                            <h3 class="text-xs font-bold text-emerald-900 uppercase tracking-wider">Hasil Evaluasi Guru</h3>
                            <div class="text-[11px] text-emerald-700">Tugas ini telah dinilai oleh bapak/ibu guru pengampu.</div>
                        </div>
                    </div>
                    <div class="text-right">
                        <div class="text-2xl font-extrabold text-emerald-800">
                            {{ $submission->score }} <span class="text-xs font-normal text-emerald-600">/ {{ $assignment->max_score }}</span>
                        </div>
                    </div>
                </div>

                @if ($submission->teacher_feedback)
                    <div class="mt-2 pt-2 border-t border-emerald-200/60 text-xs text-emerald-900 leading-relaxed">
                        <span class="font-bold">Ulasan Guru:</span> {{ $submission->teacher_feedback }}
                    </div>
                @endif
            </div>
        @endif

        <!-- Revision Notice (If revision requested) -->
        @if ($submission && $submission->status === 'revision_requested')
            <div class="p-4 bg-amber-50 border border-amber-300 rounded-xl space-y-2">
                <div class="flex items-start gap-2.5">
                    <svg class="w-5 h-5 text-amber-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                    <div>
                        <h3 class="text-xs font-bold text-amber-900 uppercase tracking-wider">Permintaan Revisi Dari Guru</h3>
                        <p class="text-xs text-amber-800 mt-1 font-medium">
                            {{ $submission->teacher_feedback }}
                        </p>
                        <p class="text-[11px] text-amber-600 mt-1">
                            Silakan perbaiki jawaban atau unggah kembali berkas perbaikan di bawah ini.
                        </p>
                    </div>
                </div>
            </div>
        @endif

        <!-- Instructions Card -->
        <x-card padding>
            <div class="space-y-4">
                <h2 class="text-xs font-bold uppercase tracking-wider text-zinc-500 pb-2 border-b border-zinc-100">
                    Instruksi & Petunjuk Pengerjaan
                </h2>
                <div class="text-xs text-zinc-800 leading-relaxed whitespace-pre-line">
                    {!! nl2br(e($assignment->instructions)) !!}
                </div>

                <!-- Teacher Attachments -->
                @if ($assignment->attachments->isNotEmpty())
                    <div class="pt-3 border-t border-zinc-100">
                        <span class="block text-xs font-semibold text-zinc-700 mb-2">
                            Bahan / Lembar Soal dari Guru ({{ $assignment->attachments->count() }}):
                        </span>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                            @foreach ($assignment->attachments as $att)
                                <div class="flex items-center justify-between p-2.5 rounded-lg border border-zinc-200 bg-zinc-50/70">
                                    <div class="truncate text-xs font-medium text-zinc-800 mr-2">
                                        {{ $att->file_name }} <span class="text-[10px] text-zinc-400">({{ $att->formatted_size }})</span>
                                    </div>
                                    <a
                                        href="{{ route('student.assignments.attachments.download', $att) }}"
                                        class="inline-flex items-center gap-1 text-xs font-bold text-brand-600 hover:text-brand-700 shrink-0"
                                    >
                                        Unduh
                                    </a>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        </x-card>

        <!-- Student Submission Workspace -->
        @php
            $isLocked = $submission && $submission->status === 'graded' && $submission->isGradePublished();
        @endphp

        <x-card padding>
            <div class="space-y-4">
                <div class="flex items-center justify-between pb-3 border-b border-zinc-100">
                    <h2 class="text-xs font-bold uppercase tracking-wider text-zinc-900">
                        Lembar Jawaban Siswa
                    </h2>
                    @if ($submission && $submission->submitted_at)
                        <div class="text-[11px] text-zinc-400">
                            Waktu Pengiriman: {{ $submission->submitted_at->format('d/m/Y H:i') }} (Percobaan ke-{{ $submission->attempt_number }})
                        </div>
                    @endif
                </div>

                @if ($isLocked)
                    <!-- Read-only submitted answer -->
                    <div class="space-y-4">
                        @if ($submission->student_notes)
                            <div>
                                <span class="block text-xs font-semibold text-zinc-700 mb-1">Teks Jawaban Kamu:</span>
                                <div class="p-3 bg-zinc-50 border border-zinc-200 rounded-lg text-xs text-zinc-800 whitespace-pre-line leading-relaxed">
                                    {!! nl2br(e($submission->student_notes)) !!}
                                </div>
                            </div>
                        @endif

                        @if ($submission->attachments->isNotEmpty())
                            <div>
                                <span class="block text-xs font-semibold text-zinc-700 mb-2">Berkas yang Telah Kamu Unggah:</span>
                                <div class="space-y-2">
                                    @foreach ($submission->attachments as $att)
                                        <div class="flex items-center justify-between p-2.5 rounded-lg border border-zinc-200 bg-zinc-50 text-xs">
                                            <span class="font-medium text-zinc-800 truncate">{{ $att->file_name }} ({{ $att->formatted_size }})</span>
                                            <a
                                                href="{{ route('student.assignments.my-submission-attachments.download', $att) }}"
                                                class="text-xs font-bold text-brand-600 hover:text-brand-700"
                                            >
                                                Unduh
                                            </a>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        <div class="p-3 bg-zinc-100 rounded-lg text-center text-xs text-zinc-500">
                            Tugas ini telah dievaluasi dan dikunci. Jawaban tidak dapat diubah lagi.
                        </div>
                    </div>
                @else
                    <!-- Editable Submission Form -->
                    <form action="{{ route('student.assignments.submit', $assignment) }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                        @csrf

                        <!-- Existing Submitted Files (if any) -->
                        @if ($submission && $submission->attachments->isNotEmpty())
                            <div>
                                <span class="block text-xs font-semibold text-zinc-700 mb-1.5">
                                    Berkas Terunggah Saat Ini:
                                </span>
                                <div class="space-y-1.5 mb-2">
                                    @foreach ($submission->attachments as $att)
                                        <div class="flex items-center justify-between p-2 rounded border border-zinc-200 bg-zinc-50 text-xs">
                                            <span class="text-zinc-700 truncate">{{ $att->file_name }} ({{ $att->formatted_size }})</span>
                                            <a
                                                href="{{ route('student.assignments.my-submission-attachments.download', $att) }}"
                                                class="text-xs font-semibold text-brand-600 hover:text-brand-700"
                                            >
                                                Unduh
                                            </a>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        <!-- Student Notes / Text Answer -->
                        @if (in_array($assignment->submission_type, ['text', 'both']))
                            <div>
                                <x-label for="student_notes" :required="$assignment->submission_type === 'text'">
                                    Teks Jawaban / Keterangan
                                </x-label>
                                <x-textarea
                                    id="student_notes"
                                    name="student_notes"
                                    rows="6"
                                    placeholder="Tuliskan jawaban atau penjelasan tugasmu di sini..."
                                >{{ old('student_notes', $submission?->student_notes) }}</x-textarea>
                            </div>
                        @endif

                        <!-- File Upload Input -->
                        @if (in_array($assignment->submission_type, ['file', 'both']))
                            <div>
                                <x-label for="attachments" :required="$assignment->submission_type === 'file' && (!$submission || $submission->attachments->isEmpty())">
                                    Unggah Berkas Jawaban
                                </x-label>
                                <input
                                    type="file"
                                    id="attachments"
                                    name="attachments[]"
                                    multiple
                                    accept="{{ '.' . str_replace(',', ',.', $assignment->allowed_file_types ?? 'pdf,doc,docx,zip,jpg,png') }}"
                                    class="block w-full text-xs text-zinc-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-zinc-100 file:text-zinc-700 hover:file:bg-zinc-200 cursor-pointer border border-zinc-200 rounded-lg p-1"
                                />
                                <p class="text-[11px] text-zinc-400 mt-1">
                                    Format: {{ $assignment->allowed_file_types ?? 'pdf,doc,docx,zip,jpg,png' }} • Maksimal {{ $assignment->max_file_size_mb }}MB per berkas (Maksimal {{ $assignment->max_attachments }} berkas).
                                </p>
                            </div>
                        @endif

                        <!-- Deadline Warning Notice -->
                        @if ($assignment->isPastDue())
                            <div class="p-3 bg-red-50 border border-red-200 rounded-lg text-xs text-red-700">
                                <span class="font-bold">Perhatian:</span> Batas tenggat waktu pengumpulan telah lewat. Jawaban yang kamu kirimkan sekarang akan ditandai sebagai terlambat.
                            </div>
                        @endif

                        <!-- Action Buttons -->
                        <div class="flex items-center justify-end gap-3 pt-3 border-t border-zinc-100">
                            <button
                                type="submit"
                                name="status"
                                value="draft"
                                class="inline-flex items-center justify-center px-4 py-2 border border-zinc-300 rounded-lg shadow-2xs text-xs font-semibold text-zinc-700 bg-white hover:bg-zinc-50"
                            >
                                Simpan sebagai Draft
                            </button>

                            <button
                                type="submit"
                                name="status"
                                value="submitted"
                                class="inline-flex items-center justify-center px-5 py-2 border border-transparent rounded-lg shadow-2xs text-xs font-bold text-white bg-brand-600 hover:bg-brand-700"
                                onclick="return confirm('Apakah kamu yakin ingin mengirimkan jawaban tugas ini?');"
                            >
                                Kirim Jawaban Sekarang
                            </button>
                        </div>
                    </form>
                @endif
            </div>
        </x-card>
    </div>
</x-layouts.app>
