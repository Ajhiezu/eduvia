<x-layouts.app>
    <x-slot:headerTitle>
        Materi: {{ $material->title }}
    </x-slot:headerTitle>

    <div class="max-w-4xl mx-auto space-y-6">
        <!-- Alerts -->
        @if (session('status'))
            <x-alert variant="success" dismissible>
                {{ session('status') }}
            </x-alert>
        @endif

        <!-- Navigation & Breadcrumb -->
        <div>
            <a href="{{ route('student.materials.index') }}" class="inline-flex items-center gap-1.5 text-xs text-zinc-500 hover:text-brand-600 mb-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Kembali ke Daftar Materi
            </a>
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div>
                    <div class="flex items-center gap-2 flex-wrap mb-1">
                        <span class="text-xs font-bold text-brand-600 bg-brand-50 px-2.5 py-0.5 rounded">
                            {{ $material->teachingAssignment?->subject?->name }}
                        </span>
                        @if ($material->topic)
                            <span class="text-xs font-medium text-zinc-600 bg-zinc-100 px-2.5 py-0.5 rounded">
                                {{ $material->topic }}
                            </span>
                        @endif
                    </div>
                    <h1 class="text-xl sm:text-2xl font-extrabold text-zinc-900 tracking-tight">
                        {{ $material->title }}
                    </h1>
                    <div class="text-xs text-zinc-500 mt-1">
                        Guru Pengampu: <span class="font-semibold text-zinc-700">{{ $material->teacher?->name }}</span> • Dipublikasikan: {{ $material->published_at ? $material->published_at->format('d/m/Y') : '-' }}
                    </div>
                </div>

                <!-- Completion Status Pill -->
                <div class="shrink-0">
                    @if ($progress && $progress->is_completed)
                        <div class="inline-flex items-center gap-1.5 bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-bold px-3 py-1.5 rounded-lg">
                            <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            Selesai Dipelajari
                        </div>
                    @else
                        <div class="inline-flex items-center gap-1.5 bg-amber-50 border border-amber-200 text-amber-800 text-xs font-medium px-3 py-1.5 rounded-lg">
                            <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                            Sedang Dipelajari
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Description Box -->
        @if ($material->description)
            <div class="p-3.5 bg-zinc-50 border-l-4 border-brand-500 rounded-r-lg text-xs text-zinc-700 leading-relaxed italic">
                {{ $material->description }}
            </div>
        @endif

        <!-- Video Player (If Available) -->
        @if ($material->video_url)
            <x-card padding>
                <div class="space-y-3">
                    <h2 class="text-xs font-bold uppercase tracking-wider text-zinc-600 flex items-center gap-1.5">
                        <svg class="w-4 h-4 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        Video Pembelajaran
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
                                <span class="font-bold">Tautan Media:</span> {{ $material->video_url }}
                            </div>
                            <a href="{{ $material->video_url }}" target="_blank" rel="noopener noreferrer" class="shrink-0 text-xs font-bold text-red-700 hover:text-red-900 underline">
                                Tonton Video &rarr;
                            </a>
                        </div>
                    @endif
                </div>
            </x-card>
        @endif

        <!-- Main Content Reading Card -->
        <x-card padding>
            <div class="prose prose-sm max-w-none text-zinc-900 leading-relaxed whitespace-pre-line text-sm">
                {!! nl2br(e($material->content)) !!}
            </div>
        </x-card>

        <!-- Downloadable Attachments -->
        @if ($material->attachments->isNotEmpty())
            <x-card padding>
                <div class="space-y-3">
                    <h2 class="text-xs font-bold uppercase tracking-wider text-zinc-600 flex items-center gap-1.5">
                        <svg class="w-4 h-4 text-zinc-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/>
                        </svg>
                        Berkas Lampiran Pembelajaran ({{ $material->attachments->count() }})
                    </h2>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        @foreach ($material->attachments as $att)
                            <div class="flex items-center justify-between p-3 rounded-lg border border-zinc-200 bg-zinc-50/70 hover:bg-zinc-100 transition-colors">
                                <div class="flex items-center gap-2.5 overflow-hidden">
                                    <div class="w-8 h-8 rounded-lg bg-zinc-200 flex items-center justify-center text-zinc-600 shrink-0 uppercase font-extrabold text-[10px]">
                                        {{ pathinfo($att->file_name, PATHINFO_EXTENSION) }}
                                    </div>
                                    <div class="truncate">
                                        <div class="font-semibold text-xs text-zinc-900 truncate">{{ $att->file_name }}</div>
                                        <div class="text-[10px] text-zinc-400">{{ $att->formatted_size }}</div>
                                    </div>
                                </div>
                                <a
                                    href="{{ route('student.materials.attachments.download', $att) }}"
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
                </div>
            </x-card>
        @endif

        <!-- Completion Action Bar -->
        <x-card padding class="border-zinc-200 bg-zinc-50/50">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h3 class="text-sm font-bold text-zinc-900">
                        Penyelesaian Pembelajaran
                    </h3>
                    <p class="text-xs text-zinc-500 mt-0.5">
                        Tandai materi ini setelah kamu selesai membaca seluruh instruksi dan modul belajar.
                    </p>
                </div>

                <div>
                    @if ($progress && $progress->is_completed)
                        <div class="text-xs text-emerald-700 font-semibold flex items-center gap-1.5">
                            <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            Selesai pada {{ $progress->completed_at ? $progress->completed_at->format('d/m/Y H:i') : '-' }}
                        </div>
                    @else
                        <form action="{{ route('student.materials.complete', $material) }}" method="POST">
                            @csrf
                            <x-button type="submit" variant="primary" size="md">
                                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                                Tandai Selesai Dipelajari
                            </x-button>
                        </form>
                    @endif
                </div>
            </div>
        </x-card>
    </div>
</x-layouts.app>
