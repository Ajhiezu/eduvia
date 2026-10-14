<x-layouts.app>
    <x-slot:headerTitle>
        Detail Tugas: {{ $assignment->title }}
    </x-slot:headerTitle>

    <div class="space-y-6">
        <!-- Alerts -->
        @if (session('status'))
            <x-alert variant="success" dismissible>
                {{ session('status') }}
            </x-alert>
        @endif

        <!-- Action & Navigation Header -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <a href="{{ route('assignments.index') }}" class="inline-flex items-center gap-1.5 text-xs text-zinc-500 hover:text-brand-600 mb-1">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                    Kembali ke Daftar Tugas
                </a>
                <div class="flex items-center gap-2.5 flex-wrap">
                    <h1 class="text-xl sm:text-2xl font-extrabold text-zinc-900 tracking-tight">
                        {{ $assignment->title }}
                    </h1>
                    @if ($assignment->status === 'published')
                        <x-badge variant="success">Terbit</x-badge>
                    @elseif ($assignment->status === 'archived')
                        <x-badge variant="neutral">Diarsipkan</x-badge>
                    @else
                        <x-badge variant="warning">Draft</x-badge>
                    @endif
                </div>
                <div class="text-xs text-zinc-500 mt-1 flex items-center gap-2 flex-wrap">
                    <span class="font-medium text-zinc-800">{{ $assignment->teachingAssignment?->subject?->name }}</span>
                    <span>•</span>
                    <span>Kelas {{ $assignment->teachingAssignment?->classroom?->name }}</span>
                    <span>•</span>
                    <span>Pengampu: {{ $assignment->teacher?->name }}</span>
                    <span>•</span>
                    <span>Tipe: <span class="font-semibold text-zinc-700">{{ $assignment->submission_type_label }}</span></span>
                </div>
            </div>

            <div class="flex items-center gap-2.5">
                <x-button href="{{ route('assignments.edit', $assignment) }}" variant="secondary" size="sm">
                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                    </svg>
                    Edit Tugas
                </x-button>
            </div>
        </div>

        <!-- 5-Metric Strip -->
        <div class="grid grid-cols-2 sm:grid-cols-5 gap-3">
            <x-card padding class="border-zinc-200">
                <div class="text-[11px] font-bold uppercase tracking-wider text-zinc-400">Total Siswa</div>
                <div class="mt-1 text-2xl font-extrabold text-zinc-900">{{ $totalStudentsCount }}</div>
                <div class="mt-0.5 text-[10px] text-zinc-400">Terdaftar di kelas</div>
            </x-card>

            <x-card padding class="border-zinc-200">
                <div class="text-[11px] font-bold uppercase tracking-wider text-blue-600">Mengumpulkan</div>
                <div class="mt-1 text-2xl font-extrabold text-blue-700">{{ $submittedCount }}</div>
                <div class="mt-0.5 text-[10px] text-zinc-400">Jawaban diterima</div>
            </x-card>

            <x-card padding class="border-zinc-200">
                <div class="text-[11px] font-bold uppercase tracking-wider text-amber-600">Belum Kumpul</div>
                <div class="mt-1 text-2xl font-extrabold text-amber-700">{{ $unsubmittedCount }}</div>
                <div class="mt-0.5 text-[10px] text-zinc-400">Menunggu respon</div>
            </x-card>

            <x-card padding class="border-zinc-200">
                <div class="text-[11px] font-bold uppercase tracking-wider text-red-600">Terlambat</div>
                <div class="mt-1 text-2xl font-extrabold text-red-700">{{ $lateCount }}</div>
                <div class="mt-0.5 text-[10px] text-zinc-400">Lewat tenggat</div>
            </x-card>

            <x-card padding class="border-zinc-200">
                <div class="text-[11px] font-bold uppercase tracking-wider text-emerald-600">Sudah Dinilai</div>
                <div class="mt-1 text-2xl font-extrabold text-emerald-700">{{ $gradedCount }}</div>
                <div class="mt-0.5 text-[10px] text-zinc-400">Telah dievaluasi</div>
            </x-card>
        </div>

        <!-- Assignment Info & Reference Attachments Card -->
        <x-card padding>
            <div class="space-y-4">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 pb-3 border-b border-zinc-100">
                    <div>
                        <h2 class="text-xs font-bold uppercase tracking-wider text-zinc-500">
                            Petunjuk & Ketentuan Tugas
                        </h2>
                    </div>
                    <div class="text-xs text-zinc-600 flex items-center gap-3">
                        <div>
                            Tenggat: <span class="font-bold {{ $assignment->isPastDue() ? 'text-red-600' : 'text-zinc-900' }}">
                                {{ $assignment->due_date ? $assignment->due_date->format('d/m/Y H:i') : 'Tanpa Tenggat' }}
                            </span>
                        </div>
                        <div>
                            Skala Nilai: <span class="font-bold text-zinc-900">{{ $assignment->max_score }}</span>
                        </div>
                    </div>
                </div>

                <div class="text-xs text-zinc-800 leading-relaxed whitespace-pre-line">
                    {!! nl2br(e($assignment->instructions)) !!}
                </div>

                @if ($assignment->attachments->isNotEmpty())
                    <div class="pt-3 border-t border-zinc-100">
                        <span class="block text-xs font-semibold text-zinc-700 mb-2">
                            Lampiran Bahan dari Guru:
                        </span>
                        <div class="flex flex-wrap gap-2">
                            @foreach ($assignment->attachments as $att)
                                <a
                                    href="{{ route('assignments.attachments.download', $att) }}"
                                    class="inline-flex items-center gap-2 text-xs bg-zinc-50 border border-zinc-200 hover:border-brand-500 hover:text-brand-600 px-3 py-1.5 rounded-lg transition-colors"
                                >
                                    <svg class="w-3.5 h-3.5 text-zinc-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/>
                                    </svg>
                                    <span class="font-medium truncate max-w-xs">{{ $att->file_name }}</span>
                                    <span class="text-zinc-400 text-[10px]">({{ $att->formatted_size }})</span>
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        </x-card>

        <!-- Submissions Table Card -->
        <x-card padding="none" class="overflow-hidden">
            <div class="p-4 bg-zinc-50 border-b border-zinc-200 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div>
                    <h2 class="text-sm font-bold text-zinc-900">
                        Daftar Pengumpulan Siswa
                    </h2>
                    <p class="text-xs text-zinc-500 mt-0.5">
                        Pantau status pengumpulan, waktu pengiriman, periksa dokumen jawaban, dan input penilaian.
                    </p>
                </div>
            </div>

            <div class="overflow-x-auto" data-scrollable>
                <table class="w-full min-w-[700px] text-left text-xs">
                    <thead class="bg-zinc-50/70 border-b border-zinc-200 text-zinc-600 font-semibold uppercase tracking-wider text-[11px]">
                        <tr>
                            <th class="px-4 py-3">Nama Siswa</th>
                            <th class="px-4 py-3">Status Pengumpulan</th>
                            <th class="px-4 py-3">Waktu Kirim</th>
                            <th class="px-4 py-3">Respon Jawaban</th>
                            <th class="px-4 py-3">Nilai Siswa</th>
                            <th class="px-4 py-3 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-200">
                        @forelse ($classroomStudents as $student)
                            @php
                                $sub = $submissions->get($student->id);
                            @endphp
                            <tr class="hover:bg-zinc-50/60 transition-colors">
                                <td class="px-4 py-3">
                                    <div class="font-bold text-zinc-900">{{ $student->user?->name ?? 'Siswa' }}</div>
                                    <div class="text-[11px] text-zinc-400">NISN: {{ $student->nisn ?? '-' }}</div>
                                </td>

                                <td class="px-4 py-3">
                                    @if (!$sub || $sub->status === 'draft')
                                        <x-badge variant="neutral">
                                            {{ $sub ? 'Draft Siswa' : 'Belum Kumpul' }}
                                        </x-badge>
                                    @elseif ($sub->status === 'graded')
                                        <x-badge variant="success">Sudah Dinilai</x-badge>
                                    @elseif ($sub->status === 'revision_requested')
                                        <x-badge variant="warning">Perlu Revisi</x-badge>
                                    @elseif ($sub->status === 'resubmitted')
                                        <x-badge variant="info">Revisi Dikirim</x-badge>
                                    @else
                                        @if ($sub->is_late)
                                            <x-badge variant="danger">Terkumpul (Terlambat)</x-badge>
                                        @else
                                            <x-badge variant="info">Terkumpul</x-badge>
                                        @endif
                                    @endif
                                </td>

                                <td class="px-4 py-3">
                                    @if ($sub && $sub->submitted_at)
                                        <div class="font-medium text-zinc-800">
                                            {{ $sub->submitted_at->format('d/m/Y H:i') }}
                                        </div>
                                        <div class="text-[10px] {{ $sub->is_late ? 'text-red-600 font-semibold' : 'text-emerald-600' }}">
                                            {{ $sub->is_late ? 'Terlambat ' . $assignment->due_date?->diffForHumans($sub->submitted_at, true) : 'Tepat Waktu' }}
                                        </div>
                                    @else
                                        <span class="text-zinc-400">-</span>
                                    @endif
                                </td>

                                <td class="px-4 py-3">
                                    @if ($sub && in_array($sub->status, ['submitted', 'resubmitted', 'revision_requested', 'graded']))
                                        <div class="flex items-center gap-2">
                                            @if ($sub->student_notes)
                                                <span class="inline-flex items-center gap-1 text-[11px] bg-zinc-100 text-zinc-700 px-2 py-0.5 rounded" title="{{ $sub->student_notes }}">
                                                    Teks ({{ Str::limit($sub->student_notes, 20) }})
                                                </span>
                                            @endif
                                            @if ($sub->attachments->isNotEmpty())
                                                <span class="inline-flex items-center gap-1 text-[11px] bg-brand-50 text-brand-700 px-2 py-0.5 rounded font-medium">
                                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/>
                                                    </svg>
                                                    {{ $sub->attachments->count() }} Berkas
                                                </span>
                                            @endif
                                        </div>
                                    @else
                                        <span class="text-zinc-400">-</span>
                                    @endif
                                </td>

                                <td class="px-4 py-3">
                                    @if ($sub && $sub->score !== null)
                                        <div class="font-extrabold text-sm text-zinc-900">
                                            {{ $sub->score }} <span class="text-xs font-normal text-zinc-400">/ {{ $assignment->max_score }}</span>
                                        </div>
                                        <div class="text-[10px] {{ $sub->grade_published_at ? 'text-emerald-600 font-semibold' : 'text-amber-600' }}">
                                            {{ $sub->grade_published_at ? 'Dipublikasikan' : 'Draft Nilai' }}
                                        </div>
                                    @else
                                        <span class="text-zinc-400">-</span>
                                    @endif
                                </td>

                                <td class="px-4 py-3 text-right">
                                    @if ($sub && in_array($sub->status, ['submitted', 'resubmitted', 'revision_requested', 'graded']))
                                        <x-button href="{{ route('assignments.submissions.show', [$assignment, $sub]) }}" variant="secondary" size="sm">
                                            <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                            </svg>
                                            Periksa & Nilai
                                        </x-button>
                                    @else
                                        <span class="text-zinc-300 text-xs italic">Menunggu Kumpul</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 py-8 text-center text-zinc-400">
                                    Belum ada siswa yang terdaftar di kelas rombel ini.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-card>
    </div>
</x-layouts.app>
