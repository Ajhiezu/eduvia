<x-layouts.app>
    <x-slot:headerTitle>
        Materi Pembelajaran
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
                    Materi Pembelajaran
                </h1>
                <p class="text-xs text-zinc-500 mt-0.5">
                    Kelola materi modul pembelajaran, dokumen bacaan, video edukasi, dan pantau progres belajar siswa.
                </p>
            </div>

            <div class="flex items-center gap-2.5">
                <x-button href="{{ route('learning-materials.create') }}" variant="primary" size="sm">
                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Tambah Materi Baru
                </x-button>
            </div>
        </div>

        <!-- 3-Card Summary Strip (Metis Style) -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <x-card padding class="border-zinc-200">
                <div class="text-[11px] font-bold uppercase tracking-wider text-zinc-400">Total Materi</div>
                <div class="mt-1 text-2xl font-extrabold text-zinc-900">{{ $totalMaterialsCount }}</div>
                <div class="mt-1 text-xs text-zinc-400">Semua materi tersimpan</div>
            </x-card>

            <x-card padding class="border-zinc-200">
                <div class="text-[11px] font-bold uppercase tracking-wider text-emerald-600">Materi Terbit</div>
                <div class="mt-1 text-2xl font-extrabold text-emerald-700">{{ $publishedCount }}</div>
                <div class="mt-1 text-xs text-zinc-400">Dapat dipelajari siswa</div>
            </x-card>

            <x-card padding class="border-zinc-200">
                <div class="text-[11px] font-bold uppercase tracking-wider text-amber-600">Draft & Terjadwal</div>
                <div class="mt-1 text-2xl font-extrabold text-amber-700">{{ $draftCount }}</div>
                <div class="mt-1 text-xs text-zinc-400">Belum tampil untuk siswa</div>
            </x-card>
        </div>

        <!-- Filter Card -->
        <x-card padding>
            <form method="GET" action="{{ route('learning-materials.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                <!-- Search Filter -->
                <div class="lg:col-span-2">
                    <input
                        type="text"
                        name="search"
                        value="{{ request('search') }}"
                        placeholder="Cari judul, topik, atau kata kunci..."
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

        <!-- Materials Table -->
        <x-card padding="none" class="overflow-hidden">
            @if ($materials->isEmpty())
                <div class="p-8 text-center">
                    <div class="w-12 h-12 rounded-full bg-zinc-100 text-zinc-400 mx-auto flex items-center justify-center mb-3">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                        </svg>
                    </div>
                    <h3 class="text-sm font-bold text-zinc-800">Belum ada materi pembelajaran</h3>
                    <p class="text-xs text-zinc-400 mt-1 max-w-sm mx-auto">
                        Mulai susun materi pembelajaran dengan menekan tombol "Tambah Materi Baru".
                    </p>
                    <div class="mt-4">
                        <x-button href="{{ route('learning-materials.create') }}" variant="primary" size="sm">
                            Buat Materi Pertama
                        </x-button>
                    </div>
                </div>
            @else
                <div class="overflow-x-auto" data-scrollable>
                    <table class="w-full min-w-[700px] text-left text-xs">
                        <thead class="bg-zinc-50 border-b border-zinc-200 text-zinc-600 font-semibold uppercase tracking-wider text-[11px]">
                            <tr>
                                <th class="px-4 py-3">Materi & Topik</th>
                                <th class="px-4 py-3">Mapel & Kelas</th>
                                <th class="px-4 py-3">Pengampu</th>
                                <th class="px-4 py-3">Status</th>
                                <th class="px-4 py-3">Media / Berkas</th>
                                <th class="px-4 py-3 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-200">
                            @foreach ($materials as $material)
                                <tr class="hover:bg-zinc-50/60 transition-colors">
                                    <td class="px-4 py-3">
                                        <div class="font-bold text-zinc-900 line-clamp-1">
                                            <a href="{{ route('learning-materials.show', $material) }}" class="hover:text-brand-600">
                                                {{ $material->title }}
                                            </a>
                                        </div>
                                        @if ($material->topic)
                                            <div class="text-[11px] text-zinc-500 flex items-center gap-1 mt-0.5">
                                                <span class="inline-block w-1.5 h-1.5 rounded-full bg-brand-500"></span>
                                                {{ $material->topic }}
                                            </div>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="font-medium text-zinc-900">
                                            {{ $material->teachingAssignment?->subject?->name ?? '-' }}
                                        </div>
                                        <div class="text-[11px] text-zinc-500">
                                            {{ $material->teachingAssignment?->classroom?->name ?? '-' }}
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 text-zinc-600">
                                        {{ $material->teacher?->name ?? '-' }}
                                    </td>
                                    <td class="px-4 py-3">
                                        @if ($material->status === 'published')
                                            <x-badge variant="success">Terbit</x-badge>
                                        @elseif ($material->status === 'archived')
                                            <x-badge variant="neutral">Diarsipkan</x-badge>
                                        @else
                                            <x-badge variant="warning">Draft</x-badge>
                                        @endif
                                        @if ($material->published_at && $material->status === 'published')
                                            <div class="text-[10px] text-zinc-400 mt-0.5">
                                                {{ $material->published_at->format('d/m/Y H:i') }}
                                            </div>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="flex items-center gap-2 text-zinc-500">
                                            @if ($material->attachments_count > 0)
                                                <span class="inline-flex items-center gap-1 text-[11px] bg-zinc-100 text-zinc-700 px-2 py-0.5 rounded">
                                                    <svg class="w-3.5 h-3.5 text-zinc-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/>
                                                    </svg>
                                                    {{ $material->attachments_count }} Berkas
                                                </span>
                                            @endif
                                            @if ($material->video_url)
                                                <span class="inline-flex items-center gap-1 text-[11px] bg-red-50 text-red-700 px-2 py-0.5 rounded">
                                                    <svg class="w-3.5 h-3.5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/>
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                    </svg>
                                                    Video
                                                </span>
                                            @endif
                                            @if ($material->attachments_count === 0 && !$material->video_url)
                                                <span class="text-zinc-400 text-[11px]">Teks saja</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 text-right">
                                        <div class="inline-flex items-center gap-1.5">
                                            <a
                                                href="{{ route('learning-materials.show', $material) }}"
                                                class="text-zinc-600 hover:text-brand-600 p-1.5 rounded hover:bg-zinc-100"
                                                title="Lihat Detail & Progres"
                                            >
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                                </svg>
                                            </a>

                                            <a
                                                href="{{ route('learning-materials.edit', $material) }}"
                                                class="text-zinc-600 hover:text-brand-600 p-1.5 rounded hover:bg-zinc-100"
                                                title="Edit Materi"
                                            >
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                                </svg>
                                            </a>

                                            @if ($material->status !== 'archived')
                                                <form action="{{ route('learning-materials.archive', $material) }}" method="POST" class="inline" onsubmit="return confirm('Arsipkan materi ini agar tidak aktif?');">
                                                    @csrf
                                                    <button type="submit" class="text-zinc-400 hover:text-amber-600 p-1.5 rounded hover:bg-zinc-100" title="Arsipkan">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/>
                                                        </svg>
                                                    </button>
                                                </form>
                                            @endif

                                            <form action="{{ route('learning-materials.destroy', $material) }}" method="POST" class="inline" onsubmit="return confirm('Hapus permanen materi ini beserta berkasnya?');">
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

                @if ($materials->hasPages())
                    <div class="px-4 py-3 border-t border-zinc-200">
                        {{ $materials->links() }}
                    </div>
                @endif
            @endif
        </x-card>
    </div>
</x-layouts.app>
