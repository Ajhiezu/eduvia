<x-layouts.app>
    <x-slot:headerTitle>
        Tugas & Penilaian
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
                <h1 class="text-xl sm:text-2xl font-extrabold text-zinc-900 tracking-tight">
                    Tugas & Evaluasi Pembelajaran
                </h1>
                <p class="text-xs text-zinc-500 mt-0.5">
                    Buat tugas terstruktur, kelola tenggat waktu pengerjaan, periksa jawaban siswa, dan berikan penilaian serta revisi.
                </p>
            </div>

            <div class="flex items-center gap-2.5">
                <x-button href="{{ route('assignments.create') }}" variant="primary" size="sm">
                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Buat Tugas Baru
                </x-button>
            </div>
        </div>

        <!-- 3-Card Summary Strip (Metis Style) -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <x-card padding class="border-zinc-200">
                <div class="text-[11px] font-bold uppercase tracking-wider text-zinc-400">Total Tugas</div>
                <div class="mt-1 text-2xl font-extrabold text-zinc-900">{{ $totalAssignmentsCount }}</div>
                <div class="mt-1 text-xs text-zinc-400">Semua tugas tersimpan</div>
            </x-card>

            <x-card padding class="border-zinc-200">
                <div class="text-[11px] font-bold uppercase tracking-wider text-emerald-600">Tugas Aktif (Berjalan)</div>
                <div class="mt-1 text-2xl font-extrabold text-emerald-700">{{ $activeCount }}</div>
                <div class="mt-1 text-xs text-zinc-400">Belum melewati tenggat waktu</div>
            </x-card>

            <x-card padding class="border-zinc-200">
                <div class="text-[11px] font-bold uppercase tracking-wider text-red-600">Melewati Tenggat</div>
                <div class="mt-1 text-2xl font-extrabold text-red-700">{{ $pastDueCount }}</div>
                <div class="mt-1 text-xs text-zinc-400">Tenggat pengerjaan telah lewat</div>
            </x-card>
        </div>

        <!-- Filter Card -->
        <x-card padding>
            <form method="GET" action="{{ route('assignments.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                <!-- Search Filter -->
                <div class="lg:col-span-2">
                    <input
                        type="text"
                        name="search"
                        value="{{ request('search') }}"
                        placeholder="Cari judul tugas atau instruksi..."
                        class="w-full rounded-lg border border-zinc-300 text-zinc-900 text-xs py-2 px-3 focus:border-brand-500 focus:ring-2 focus:ring-brand-100"
                    />
                </div>

                <!-- Assignment Filter -->
                <div>
                    <select
                        name="teaching_assignment_id"
                        onchange="this.form.submit()"
                        class="w-full rounded-lg border border-zinc-300 text-zinc-900 text-xs py-2 px-3 focus:border-brand-500 focus:ring-2 focus:ring-brand-100"
                    >
                        <option value="">-- Semua Mapel & Rombel --</option>
                        @foreach ($teachingAssignments as $ta)
                            <option value="{{ $ta->id }}" {{ request('teaching_assignment_id') == $ta->id ? 'selected' : '' }}>
                                {{ $ta->subject?->name }} ({{ $ta->classroom?->name }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Status Filter -->
                <div class="flex items-center gap-2">
                    <select
                        name="status"
                        onchange="this.form.submit()"
                        class="w-full rounded-lg border border-zinc-300 text-zinc-900 text-xs py-2 px-3 focus:border-brand-500 focus:ring-2 focus:ring-brand-100"
                    >
                        <option value="">-- Semua Status --</option>
                        <option value="published" {{ request('status') === 'published' ? 'selected' : '' }}>Terbit</option>
                        <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>Draft</option>
                        <option value="archived" {{ request('status') === 'archived' ? 'selected' : '' }}>Diarsipkan</option>
                    </select>

                    <x-button type="submit" variant="secondary" size="sm" class="shrink-0">
                        Cari
                    </x-button>
                </div>
            </form>
        </x-card>

        <!-- Assignments Table -->
        <x-card padding="none" class="overflow-hidden">
            @if ($assignments->isEmpty())
                <div class="p-8 text-center">
                    <div class="w-12 h-12 rounded-full bg-zinc-100 text-zinc-400 mx-auto flex items-center justify-center mb-3">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                        </svg>
                    </div>
                    <h3 class="text-sm font-bold text-zinc-800">Belum ada tugas pembelajaran</h3>
                    <p class="text-xs text-zinc-400 mt-1 max-w-sm mx-auto">
                        Mulai berikan penugasan kepada rombongan belajar dengan menekan tombol "Buat Tugas Baru".
                    </p>
                    <div class="mt-4">
                        <x-button href="{{ route('assignments.create') }}" variant="primary" size="sm">
                            Buat Tugas Pertama
                        </x-button>
                    </div>
                </div>
            @else
                <div class="overflow-x-auto" data-scrollable>
                    <table class="w-full min-w-[700px] text-left text-xs">
                        <thead class="bg-zinc-50 border-b border-zinc-200 text-zinc-600 font-semibold uppercase tracking-wider text-[11px]">
                            <tr>
                                <th class="px-4 py-3">Judul Tugas</th>
                                <th class="px-4 py-3">Mapel & Kelas</th>
                                <th class="px-4 py-3">Tenggat Waktu</th>
                                <th class="px-4 py-3">Tipe Jawaban</th>
                                <th class="px-4 py-3">Status</th>
                                <th class="px-4 py-3 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-200">
                            @foreach ($assignments as $assignment)
                                <tr class="hover:bg-zinc-50/60 transition-colors">
                                    <td class="px-4 py-3">
                                        <div class="font-bold text-zinc-900 line-clamp-1">
                                            <a href="{{ route('assignments.show', $assignment) }}" class="hover:text-brand-600">
                                                {{ $assignment->title }}
                                            </a>
                                        </div>
                                        <div class="text-[11px] text-zinc-400 mt-0.5">
                                            Maksimal Nilai: <span class="font-semibold text-zinc-700">{{ $assignment->max_score }}</span>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="font-medium text-zinc-900">
                                            {{ $assignment->teachingAssignment?->subject?->name ?? '-' }}
                                        </div>
                                        <div class="text-[11px] text-zinc-500">
                                            {{ $assignment->teachingAssignment?->classroom?->name ?? '-' }}
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
                                            <span class="text-zinc-400">Tidak ada tenggat</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3">
                                        @if ($assignment->submission_type === 'both')
                                            <x-badge variant="info">Teks & Berkas</x-badge>
                                        @elseif ($assignment->submission_type === 'file')
                                            <x-badge variant="neutral">Unggah Berkas</x-badge>
                                        @else
                                            <x-badge variant="neutral">Teks Langsung</x-badge>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3">
                                        @if ($assignment->status === 'published')
                                            <x-badge variant="success">Terbit</x-badge>
                                        @elseif ($assignment->status === 'archived')
                                            <x-badge variant="neutral">Diarsipkan</x-badge>
                                        @else
                                            <x-badge variant="warning">Draft</x-badge>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-right">
                                        <div class="inline-flex items-center gap-1.5">
                                            <a
                                                href="{{ route('assignments.show', $assignment) }}"
                                                class="text-zinc-600 hover:text-brand-600 p-1.5 rounded hover:bg-zinc-100"
                                                title="Periksa Pengumpulan & Nilai"
                                            >
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                                                </svg>
                                            </a>

                                            <a
                                                href="{{ route('assignments.edit', $assignment) }}"
                                                class="text-zinc-600 hover:text-brand-600 p-1.5 rounded hover:bg-zinc-100"
                                                title="Edit Tugas"
                                            >
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                                </svg>
                                            </a>

                                            <form action="{{ route('assignments.destroy', $assignment) }}" method="POST" class="inline" onsubmit="return confirm('Hapus permanen tugas ini beserta berkasnya?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-zinc-400 hover:text-red-600 p-1.5 rounded hover:bg-zinc-100" title="Hapus">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                    </svg>
                                                </button>
                                            </form>
                                        </div>
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
