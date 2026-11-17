<x-layouts.app>
    <x-slot:headerTitle>
        Tugas & Evaluasi Saya
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
        <div class="app-page-header flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h1 class="text-xl sm:text-2xl font-extrabold text-zinc-900 tracking-tight">
                    Tugas & Evaluasi Pembelajaran
                </h1>
                <p class="text-xs text-zinc-500 mt-0.5">
                    Lihat tugas yang diberikan bapak/ibu guru, perhatikan batas tenggat waktu, kirim respon jawaban, dan pantau hasil evaluasi.
                </p>
            </div>
        </div>

        <!-- 3-Card Summary Strip -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-2.5 sm:gap-4">
            <x-card padding class="border-zinc-200">
                <div class="text-[11px] font-bold uppercase tracking-wider text-zinc-400">Total Tugas Diberikan</div>
                <div class="mt-1 text-2xl font-extrabold text-zinc-900">{{ $totalCount ?? ($assignments->total() ?? 0) }}</div>
                <div class="mt-1 text-xs text-zinc-400">Semua tugas aktif di kelasmu</div>
            </x-card>

            <x-card padding class="border-zinc-200">
                <div class="text-[11px] font-bold uppercase tracking-wider text-amber-600">Perlu Dikerjakan</div>
                <div class="mt-1 text-2xl font-extrabold text-amber-700">{{ $pendingCount ?? 0 }}</div>
                <div class="mt-1 text-xs text-zinc-400">Belum dikirim atau perlu revisi</div>
            </x-card>

            <x-card padding class="border-zinc-200">
                <div class="text-[11px] font-bold uppercase tracking-wider text-emerald-600">Sudah Terkumpul</div>
                <div class="mt-1 text-2xl font-extrabold text-emerald-700">{{ $submittedCount ?? 0 }}</div>
                <div class="mt-1 text-xs text-zinc-400">Jawaban telah diserahkan ke guru</div>
            </x-card>
        </div>

        <!-- Filter Card -->
        <x-card padding>
            <form method="GET" action="{{ route('student.assignments.index') }}" class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                <div class="sm:col-span-2">
                    <input
                        type="text"
                        name="search"
                        value="{{ request('search') }}"
                        placeholder="Cari tugas berdasarkan judul atau nama mata pelajaran..."
                        class="w-full rounded-lg border border-zinc-300 text-zinc-900 text-xs py-2 px-3 focus:border-brand-500 focus:ring-2 focus:ring-brand-100"
                    />
                </div>
                <div class="flex items-center gap-2">
                    <select
                        name="status"
                        onchange="this.form.submit()"
                        class="w-full rounded-lg border border-zinc-300 text-zinc-900 text-xs py-2 px-3 focus:border-brand-500 focus:ring-2 focus:ring-brand-100"
                    >
                        <option value="">-- Semua Status Tugas --</option>
                        <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Perlu Dikerjakan</option>
                        <option value="submitted" {{ request('status') === 'submitted' ? 'selected' : '' }}>Sudah Dikumpulkan</option>
                        <option value="graded" {{ request('status') === 'graded' ? 'selected' : '' }}>Sudah Dinilai</option>
                    </select>
                    <x-button type="submit" variant="secondary" size="sm" class="shrink-0">
                        Cari
                    </x-button>
                </div>
            </form>
        </x-card>

        <!-- Assignments Table / Cards -->
        <x-card :padding="false" class="overflow-hidden">
            @if ($assignments->isEmpty())
                <x-empty-state
                    :title="request('search') ? 'Tugas tidak ditemukan' : 'Belum ada tugas untukmu'"
                    :description="request('search') ? 'Coba kata kunci lain atau hapus pencarian untuk melihat semua tugas.' : 'Tugas dari guru akan muncul di sini. Periksa kembali setelah kegiatan pembelajaran berikutnya.'"
                    class="border-0"
                >
                    @if (request('search'))
                        <x-slot:action>
                            <x-button href="{{ route('student.assignments.index') }}" variant="secondary" size="sm">Hapus pencarian</x-button>
                        </x-slot:action>
                    @endif
                </x-empty-state>
            @else
                <div class="overflow-x-auto" data-scrollable>
                    <table class="w-full text-left text-xs min-w-[640px]">
                        <thead class="bg-zinc-50 border-b border-zinc-200 text-zinc-600 font-semibold uppercase tracking-wider text-[11px]">
                            <tr>
                                <th class="px-4 py-3">Judul Tugas & Mapel</th>
                                <th class="px-4 py-3">Tenggat Waktu</th>
                                <th class="px-4 py-3">Status Pengerjaan</th>
                                <th class="px-4 py-3">Hasil / Nilai</th>
                                <th class="px-4 py-3 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-200">
                            @foreach ($assignments as $assignment)
                                @php
                                    $sub = ($submissionsMap ?? $mySubmissions ?? collect())->get($assignment->id);
                                @endphp
                                <tr class="hover:bg-zinc-50/60 transition-colors">
                                    <td class="px-4 py-3">
                                        <div class="font-bold text-zinc-900 line-clamp-1">
                                            <a href="{{ route('student.assignments.show', $assignment) }}" class="hover:text-brand-600">
                                                {{ $assignment->title }}
                                            </a>
                                        </div>
                                        <div class="text-[11px] text-zinc-500 mt-0.5 flex items-center gap-2">
                                            <span class="font-medium text-brand-600">{{ $assignment->subject?->name ?? $assignment->teachingAssignment?->subject?->name ?? 'Mata Pelajaran' }}</span>
                                            <span>•</span>
                                            <span>Guru: {{ $assignment->teacher?->name ?? 'Guru Pengampu' }}</span>
                                        </div>
                                    </td>

                                    <td class="px-4 py-3">
                                        @if ($assignment->due_date)
                                            <div class="font-semibold {{ $assignment->isPastDue() ? 'text-red-600' : 'text-zinc-800' }}">
                                                {{ $assignment->due_date->format('d/m/Y H:i') }}
                                            </div>
                                            <div class="text-[10px] {{ $assignment->isPastDue() ? 'text-red-500 font-medium' : 'text-zinc-400' }}">
                                                {{ $assignment->due_date->diffForHumans() }}
                                            </div>
                                        @else
                                            <span class="text-zinc-400">Tidak ada batas waktu</span>
                                        @endif
                                    </td>

                                    <td class="px-4 py-3">
                                        @if (!$sub)
                                            @if ($assignment->isPastDue())
                                                <x-badge variant="danger">Belum Dikerjakan (Lewat)</x-badge>
                                            @else
                                                <x-badge variant="neutral">Belum Dikerjakan</x-badge>
                                            @endif
                                        @elseif ($sub->status === 'draft')
                                            <x-badge variant="warning">Tersimpan Draft</x-badge>
                                        @elseif ($sub->status === 'revision_requested')
                                            <x-badge variant="warning">Perlu Revisi</x-badge>
                                        @elseif ($sub->status === 'resubmitted')
                                            <x-badge variant="info">Revisi Terkirim</x-badge>
                                        @elseif ($sub->status === 'graded')
                                            <x-badge variant="success">Sudah Dinilai</x-badge>
                                        @else
                                            @if ($sub->is_late)
                                                <x-badge variant="danger">Terkumpul (Terlambat)</x-badge>
                                            @else
                                                <x-badge variant="info">Terkumpul</x-badge>
                                            @endif
                                        @endif
                                    </td>

                                    <td class="px-4 py-3">
                                        @if ($sub && $sub->isGradePublished())
                                            <div class="font-extrabold text-sm text-emerald-700">
                                                {{ $sub->score }} <span class="text-xs font-normal text-zinc-400">/ {{ $assignment->max_score }}</span>
                                            </div>
                                            <div class="text-[10px] text-emerald-600 font-semibold">Telah Dinilai</div>
                                        @elseif ($sub && in_array($sub->status, ['submitted', 'resubmitted', 'graded']))
                                            <span class="text-zinc-400 text-[11px] italic">Sedang diperiksa</span>
                                        @else
                                            <span class="text-zinc-400">-</span>
                                        @endif
                                    </td>

                                    <td class="px-4 py-3 text-right">
                                        <x-button href="{{ route('student.assignments.show', $assignment) }}" variant="secondary" size="sm">
                                            @if (!$sub || $sub->status === 'draft' || $sub->status === 'revision_requested')
                                                Kerjakan &rarr;
                                            @else
                                                Lihat Jawaban
                                            @endif
                                        </x-button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @if ($assignments->hasPages())
                    <div class="px-4 py-3 border-t border-zinc-200">
                        {{ $assignments->links() }}
                    </div>
                @endif
            @endif
        </x-card>
    </div>
</x-layouts.app>
