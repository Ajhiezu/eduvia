<x-layouts.app>
    <x-slot:headerTitle>
        {{ $announcement->title }}
    </x-slot:headerTitle>

    <div class="max-w-4xl mx-auto space-y-6">

        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-4 border-b border-zinc-200">
            <div>
                <a href="{{ route('announcements.index') }}" class="text-xs text-zinc-500 hover:text-zinc-900 inline-flex items-center gap-1 font-semibold">
                    &larr; Kembali ke Daftar Pengumuman
                </a>
            </div>

            @if (auth()->user()->hasRole(\App\Enums\UserRole::SUPER_ADMIN, \App\Enums\UserRole::SCHOOL_ADMIN) || $announcement->created_by === auth()->id())
                <div class="flex items-center gap-2">
                    <a
                        href="{{ route('announcements.edit', $announcement) }}"
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-bold border border-zinc-300 hover:bg-zinc-50 text-zinc-700 transition-colors"
                    >
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                        <span>Edit</span>
                    </a>
                    <form action="{{ route('announcements.destroy', $announcement) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus pengumuman ini?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-bold bg-rose-50 hover:bg-rose-100 text-rose-700 transition-colors">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            <span>Hapus</span>
                        </button>
                    </form>
                </div>
            @endif
        </div>

        @if (session('success'))
            <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-xs text-emerald-800 font-bold flex items-center gap-2">
                <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        <article class="bg-white rounded-2xl border border-zinc-200 shadow-xs p-6 sm:p-10 space-y-6">

            <!-- Badges & Meta -->
            <div class="space-y-3 pb-6 border-b border-zinc-100">
                <div class="flex items-center gap-2 flex-wrap">
                    <span class="px-2.5 py-1 rounded-lg text-xs font-bold border {{ $announcement->categoryBadgeClasses() }}">
                        {{ $announcement->categoryLabel() }}
                    </span>
                    <span class="px-2.5 py-1 rounded-lg text-xs font-semibold bg-zinc-100 text-zinc-700">
                        Sasaran: {{ $announcement->targetAudienceLabel() }}
                    </span>
                    @if ($announcement->classroom)
                        <span class="px-2.5 py-1 rounded-lg text-xs font-semibold bg-emerald-50 text-emerald-800">
                            Kelas: {{ $announcement->classroom->name }}
                        </span>
                    @endif
                    @if ($announcement->is_pinned)
                        <span class="px-2.5 py-1 rounded-lg text-xs font-bold bg-amber-50 text-amber-800 border border-amber-200">
                            Disematkan
                        </span>
                    @endif
                </div>

                <h1 class="text-xl sm:text-3xl font-extrabold text-zinc-900 tracking-tight leading-tight">
                    {{ $announcement->title }}
                </h1>

                <div class="flex items-center gap-4 text-xs text-zinc-500 pt-1 flex-wrap">
                    <div class="flex items-center gap-1.5">
                        <div class="w-5 h-5 rounded-full bg-zinc-200 text-zinc-700 flex items-center justify-center font-bold text-[10px]">
                            {{ substr($announcement->author?->name ?? 'A', 0, 1) }}
                        </div>
                        <span class="font-semibold text-zinc-800">{{ $announcement->author?->name ?? 'Administrator' }}</span>
                    </div>
                    <span>&bull;</span>
                    <span>Diterbitkan: {{ $announcement->published_at ? $announcement->published_at->isoFormat('D MMMM Y, HH:mm') : $announcement->created_at->isoFormat('D MMMM Y, HH:mm') }} WIB</span>
                    <span>&bull;</span>
                    <span>Dibaca {{ $announcement->view_count }} kali</span>
                </div>
            </div>

            <!-- Content Body -->
            <div class="text-sm text-zinc-800 leading-relaxed space-y-4 whitespace-pre-line font-normal">
                {{ $announcement->content }}
            </div>

            <!-- Attachment Section (If Any) -->
            @if ($announcement->attachment_path)
                <div class="pt-6 border-t border-zinc-100">
                    <h4 class="text-xs font-bold text-zinc-500 uppercase tracking-wider mb-2">Dokumen Lampiran</h4>
                    <div class="p-4 rounded-xl border border-zinc-200 bg-zinc-50 flex items-center justify-between gap-4">
                        <div class="flex items-center gap-3 min-w-0">
                            <div class="w-10 h-10 rounded-xl bg-white border border-zinc-200 flex items-center justify-center text-brand-600 shrink-0">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            </div>
                            <div class="min-w-0">
                                <p class="text-xs font-bold text-zinc-800 truncate">{{ $announcement->attachment_name ?: 'Dokumen Lampiran' }}</p>
                                <p class="text-[10px] text-zinc-400">Berkas resmi terverifikasi</p>
                            </div>
                        </div>
                        <a
                            href="{{ $announcement->attachmentUrl() }}"
                            target="_blank"
                            download
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-bold bg-zinc-800 hover:bg-zinc-900 text-white transition-colors shrink-0"
                        >
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                            <span>Unduh</span>
                        </a>
                    </div>
                </div>
            @endif

        </article>

    </div>
</x-layouts.app>
