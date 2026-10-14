<x-layouts.app>
    <x-slot:headerTitle>
        Detail Materi: {{ $material->title }}
    </x-slot:headerTitle>

    <div class="space-y-6">
        <!-- Alerts -->
        @if (session('status'))
            <x-alert variant="success" dismissible>
                {{ session('status') }}
            </x-alert>
        @endif

        <!-- Header & Action Bar -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <a href="{{ route('learning-materials.index') }}" class="inline-flex items-center gap-1.5 text-xs text-zinc-500 hover:text-brand-600 mb-1">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                    Kembali ke Daftar Materi
                </a>
                <div class="flex items-center gap-2.5 flex-wrap">
                    <h1 class="text-xl sm:text-2xl font-extrabold text-zinc-900 tracking-tight">
                        {{ $material->title }}
                    </h1>
                    @if ($material->status === 'published')
                        <x-badge variant="success">Terbit</x-badge>
                    @elseif ($material->status === 'archived')
                        <x-badge variant="neutral">Diarsipkan</x-badge>
                    @else
                        <x-badge variant="warning">Draft</x-badge>
                    @endif
                </div>
                <div class="text-xs text-zinc-500 mt-1 flex items-center gap-2 flex-wrap">
                    <span class="font-medium text-zinc-800">{{ $material->teachingAssignment?->subject?->name }}</span>
                    <span>•</span>
                    <span>Kelas {{ $material->teachingAssignment?->classroom?->name }}</span>
                    <span>•</span>
                    <span>Pengampu: {{ $material->teacher?->name }}</span>
                    @if ($material->topic)
                        <span>•</span>
                        <span class="text-brand-600 font-semibold">{{ $material->topic }}</span>
                    @endif
                </div>
            </div>

            <div class="flex items-center gap-2.5">
                <x-button href="{{ route('learning-materials.edit', $material) }}" variant="secondary" size="sm">
                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                    </svg>
                    Edit Materi
                </x-button>

                @if ($material->status !== 'archived')
                    <form action="{{ route('learning-materials.archive', $material) }}" method="POST" onsubmit="return confirm('Arsipkan materi ini?');">
                        @csrf
                        <x-button type="submit" variant="secondary" size="sm">
                            Arsipkan
                        </x-button>
                    </form>
                @endif
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Left / Main Content: Materi, Media & Lampiran -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Content Card -->
                <x-card padding>
                    <div class="space-y-4">
                        @if ($material->description)
                            <div class="p-3 bg-zinc-50 border-l-4 border-brand-500 rounded-r text-xs text-zinc-700 italic">
                                {{ $material->description }}
                            </div>
                        @endif

                        <div class="prose prose-sm max-w-none text-zinc-800 leading-relaxed whitespace-pre-line text-sm border-t border-zinc-100 pt-3">
                            {!! nl2br(e($material->content)) !!}
                        </div>
                    </div>
                </x-card>

                <!-- Video Preview Card (If any) -->
                @if ($material->video_url)
                    <x-card padding>
                        <div class="space-y-3">
                            <h2 class="text-xs font-bold uppercase tracking-wider text-zinc-500 flex items-center gap-1.5">
                                <svg class="w-4 h-4 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                Video Pembelajaran Pendukung
                            </h2>
                            @php
                                $youtubeId = null;
                                if (preg_match('/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/ ]{11})/', $material->video_url, $matches)) {
                                    $youtubeId = $matches[1];
                                }
                            @endphp
                            @if ($youtubeId)
                                <div class="aspect-video rounded-xl overflow-hidden bg-black shadow-sm">
                                    <iframe
                                        class="w-full h-full"
                                        src="https://www.youtube.com/embed/{{ $youtubeId }}"
                                        title="Video Pembelajaran"
                                        frameborder="0"
                                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                                        allowfullscreen
                                    ></iframe>
                                </div>
                            @else
                                <div class="p-3 bg-red-50 border border-red-200 rounded-lg flex items-center justify-between">
                                    <div class="text-xs text-red-800 truncate mr-3">
                                        <span class="font-bold">Tautan Video:</span> {{ $material->video_url }}
                                    </div>
                                    <a href="{{ $material->video_url }}" target="_blank" rel="noopener noreferrer" class="shrink-0 text-xs font-bold text-red-700 hover:text-red-900 underline">
                                        Buka Video &rarr;
                                    </a>
                                </div>
                            @endif
                        </div>
                    </x-card>
                @endif

                <!-- Lampiran Dokumen Card -->
                <x-card padding>
                    <div class="space-y-3">
                        <div class="flex items-center justify-between">
                            <h2 class="text-xs font-bold uppercase tracking-wider text-zinc-500 flex items-center gap-1.5">
                                <svg class="w-4 h-4 text-zinc-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/>
                                </svg>
                                Berkas Lampiran Pembelajaran ({{ $material->attachments->count() }})
                            </h2>
                        </div>

                        @if ($material->attachments->isEmpty())
                            <p class="text-xs text-zinc-400 italic">Tidak ada berkas lampiran yang diunggah.</p>
                        @else
                            <div class="space-y-2">
                                @foreach ($material->attachments as $att)
                                    <div class="flex items-center justify-between p-3 rounded-lg border border-zinc-200 bg-zinc-50/70 hover:bg-zinc-100 transition-colors">
                                        <div class="flex items-center gap-3 overflow-hidden">
                                            <div class="w-8 h-8 rounded-lg bg-zinc-200/80 flex items-center justify-center text-zinc-600 shrink-0 uppercase font-extrabold text-[10px]">
                                                {{ pathinfo($att->file_name, PATHINFO_EXTENSION) }}
                                            </div>
                                            <div class="truncate">
                                                <div class="font-semibold text-xs text-zinc-900 truncate">{{ $att->file_name }}</div>
                                                <div class="text-[11px] text-zinc-400">{{ $att->formatted_size }} • {{ $att->created_at->format('d/m/Y') }}</div>
                                            </div>
                                        </div>
                                        <a
                                            href="{{ route('learning-materials.attachments.download', $att) }}"
                                            class="inline-flex items-center gap-1.5 text-xs font-semibold text-brand-600 hover:text-brand-700 bg-white border border-zinc-200 px-3 py-1.5 rounded-lg shadow-2xs shrink-0"
                                        >
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                                            </svg>
                                            Unduh
                                        </a>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </x-card>
            </div>

            <!-- Right Sidebar: Progres Belajar Siswa -->
            <div class="space-y-6">
                <!-- Progress Summary Card -->
                <x-card padding>
                    <div class="space-y-3">
                        <div class="text-xs font-bold uppercase tracking-wider text-zinc-500">
                            Progres Belajar Rombel
                        </div>
                        <div class="flex items-baseline justify-between">
                            <div class="text-2xl font-extrabold text-zinc-900">
                                {{ $completedCount }} <span class="text-sm font-normal text-zinc-400">/ {{ $totalStudentsCount }} Siswa</span>
                            </div>
                            <div class="text-xs font-bold text-emerald-600">
                                {{ $completionPercentage }}%
                            </div>
                        </div>

                        <!-- Progress Bar -->
                        <div class="w-full bg-zinc-200 rounded-full h-2 overflow-hidden">
                            <div class="bg-emerald-500 h-2 rounded-full transition-all duration-500" style="width: {{ $completionPercentage }}%"></div>
                        </div>

                        <p class="text-[11px] text-zinc-400">
                            Siswa yang telah membuka materi dan menandai sebagai selesai dipelajari.
                        </p>
                    </div>
                </x-card>

                <!-- Students Progress List -->
                <x-card padding="none" class="overflow-hidden">
                    <div class="p-3 bg-zinc-50 border-b border-zinc-200 flex items-center justify-between">
                        <h3 class="text-xs font-bold text-zinc-800">Daftar Siswa Kelas {{ $material->teachingAssignment?->classroom?->name }}</h3>
                        <span class="text-[11px] text-zinc-500">{{ $classroomStudents->count() }} Terdaftar</span>
                    </div>

                    <div class="divide-y divide-zinc-100 max-h-96 overflow-y-auto">
                        @forelse ($classroomStudents as $student)
                            @php
                                $prog = $progressMap->get($student->id);
                                $isCompleted = $prog && $prog->is_completed;
                            @endphp
                            <div class="p-3 flex items-center justify-between text-xs hover:bg-zinc-50">
                                <div>
                                    <div class="font-medium text-zinc-900">{{ $student->user?->name ?? 'Siswa' }}</div>
                                    <div class="text-[10px] text-zinc-400">NISN: {{ $student->nisn ?? '-' }}</div>
                                </div>
                                <div class="text-right">
                                    @if ($isCompleted)
                                        <span class="inline-flex items-center gap-1 text-[10px] font-bold text-emerald-700 bg-emerald-50 border border-emerald-200 px-2 py-0.5 rounded-full">
                                            <svg class="w-3 h-3 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                            </svg>
                                            Selesai
                                        </span>
                                        <div class="text-[9px] text-zinc-400 mt-0.5">
                                            {{ $prog->completed_at ? $prog->completed_at->format('d/m H:i') : '' }}
                                        </div>
                                    @else
                                        <span class="inline-block text-[10px] font-medium text-zinc-400 bg-zinc-100 px-2 py-0.5 rounded-full">
                                            Belum Selesai
                                        </span>
                                    @endif
                                </div>
                            </div>
                        @empty
                            <div class="p-4 text-center text-xs text-zinc-400 italic">
                                Belum ada siswa yang terdaftar di kelas ini.
                            </div>
                        @endforelse
                    </div>
                </x-card>
            </div>
        </div>
    </div>
</x-layouts.app>
