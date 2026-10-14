<x-layouts.app>
    <x-slot:headerTitle>
        Materi Pembelajaran Saya
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
                    Materi Pembelajaran Saya
                </h1>
                <p class="text-xs text-zinc-500 mt-0.5">
                    Akses materi bacaan, dokumen modul, dan video pembelajaran dari bapak/ibu guru di kelasmu.
                </p>
            </div>
        </div>

        <!-- 3-Card Summary Strip -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <x-card padding class="border-zinc-200">
                <div class="text-[11px] font-bold uppercase tracking-wider text-zinc-400">Total Materi Tersedia</div>
                <div class="mt-1 text-2xl font-extrabold text-zinc-900">{{ $totalCount }}</div>
                <div class="mt-1 text-xs text-zinc-400">Untuk kelas rombelmu</div>
            </x-card>

            <x-card padding class="border-zinc-200">
                <div class="text-[11px] font-bold uppercase tracking-wider text-emerald-600">Sudah Dipelajari</div>
                <div class="mt-1 text-2xl font-extrabold text-emerald-700">{{ $completedCount }}</div>
                <div class="mt-1 text-xs text-zinc-400">Telah ditandai selesai</div>
            </x-card>

            <x-card padding class="border-zinc-200">
                <div class="text-[11px] font-bold uppercase tracking-wider text-amber-600">Perlu Dipelajari</div>
                <div class="mt-1 text-2xl font-extrabold text-amber-700">{{ $uncompletedCount }}</div>
                <div class="mt-1 text-xs text-zinc-400">Belum diselesaikan</div>
            </x-card>
        </div>

        <!-- Search & Filter Card -->
        <x-card padding>
            <form method="GET" action="{{ route('student.materials.index') }}" class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                <div class="sm:col-span-2">
                    <input
                        type="text"
                        name="search"
                        value="{{ request('search') }}"
                        placeholder="Cari materi berdasarkan judul atau topik pembahasan..."
                        class="w-full rounded-lg border border-zinc-300 text-zinc-900 text-xs py-2 px-3 focus:border-brand-500 focus:ring-2 focus:ring-brand-100"
                    />
                </div>
                <div class="flex items-center gap-2">
                    <select
                        name="status"
                        onchange="this.form.submit()"
                        class="w-full rounded-lg border border-zinc-300 text-zinc-900 text-xs py-2 px-3 focus:border-brand-500 focus:ring-2 focus:ring-brand-100"
                    >
                        <option value="">-- Semua Status Progres --</option>
                        <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Sudah Selesai</option>
                        <option value="uncompleted" {{ request('status') === 'uncompleted' ? 'selected' : '' }}>Belum Selesai</option>
                    </select>
                    <x-button type="submit" variant="secondary" size="sm" class="shrink-0">
                        Cari
                    </x-button>
                </div>
            </form>
        </x-card>

        <!-- Materials Grid -->
        @if ($materials->isEmpty())
            <x-card padding="none" class="p-8 text-center">
                <div class="w-12 h-12 rounded-full bg-zinc-100 text-zinc-400 mx-auto flex items-center justify-center mb-3">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                    </svg>
                </div>
                <h3 class="text-sm font-bold text-zinc-800">Belum ada materi pembelajaran</h3>
                <p class="text-xs text-zinc-400 mt-1 max-w-sm mx-auto">
                    Bapak/Ibu guru belum mempublikasikan materi baru untuk kelasmu saat ini. Silakan periksa kembali nanti.
                </p>
            </x-card>
        @else
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach ($materials as $material)
                    @php
                        $prog = $progressMap->get($material->id);
                        $isCompleted = $prog && $prog->is_completed;
                    @endphp
                    <div class="bg-white rounded-xl border border-zinc-200 p-4 flex flex-col justify-between hover:shadow-md hover:border-brand-200 transition-all">
                        <div class="space-y-3">
                            <!-- Top metadata -->
                            <div class="flex items-center justify-between text-xs">
                                <span class="font-bold text-brand-600 bg-brand-50 px-2 py-0.5 rounded text-[11px]">
                                    {{ $material->teachingAssignment?->subject?->name }}
                                </span>
                                @if ($isCompleted)
                                    <span class="inline-flex items-center gap-1 text-[10px] font-bold text-emerald-700 bg-emerald-50 border border-emerald-200 px-2 py-0.5 rounded-full">
                                        <svg class="w-3 h-3 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                        </svg>
                                        Selesai
                                    </span>
                                @else
                                    <span class="text-[10px] font-medium text-amber-700 bg-amber-50 border border-amber-200 px-2 py-0.5 rounded-full">
                                        Belum Selesai
                                    </span>
                                @endif
                            </div>

                            <!-- Title & Topic -->
                            <div>
                                <h3 class="font-bold text-zinc-900 text-sm hover:text-brand-600 transition-colors line-clamp-2">
                                    <a href="{{ route('student.materials.show', $material) }}">
                                        {{ $material->title }}
                                    </a>
                                </h3>
                                @if ($material->topic)
                                    <div class="text-[11px] text-zinc-500 mt-1">
                                        Topik: <span class="font-medium text-zinc-700">{{ $material->topic }}</span>
                                    </div>
                                @endif
                            </div>

                            <!-- Description Snippet -->
                            @if ($material->description)
                                <p class="text-xs text-zinc-500 line-clamp-2">
                                    {{ $material->description }}
                                </p>
                            @endif

                            <!-- Media Indicators -->
                            <div class="flex items-center gap-3 text-[11px] text-zinc-400 pt-1 border-t border-zinc-100">
                                <span>Guru: {{ $material->teacher?->name }}</span>
                                @if ($material->attachments_count > 0)
                                    <span>•</span>
                                    <span>{{ $material->attachments_count }} Berkas</span>
                                @endif
                                @if ($material->video_url)
                                    <span>•</span>
                                    <span class="text-red-600 font-medium">Video</span>
                                @endif
                            </div>
                        </div>

                        <!-- CTA Button -->
                        <div class="mt-4 pt-3 border-t border-zinc-100">
                            <x-button href="{{ route('student.materials.show', $material) }}" variant="secondary" size="sm" class="w-full justify-center">
                                Pelajari Materi &rarr;
                            </x-button>
                        </div>
                    </div>
                @endforeach
            </div>

            @if ($materials->hasPages())
                <div class="pt-2">
                    {{ $materials->links() }}
                </div>
            @endif
        @endif
    </div>
</x-layouts.app>
