<x-layouts.app>
    <x-slot:headerTitle>
        Pengumuman & Warta Sekolah
    </x-slot:headerTitle>

    <div class="space-y-6">

        <!-- Top Header & Actions -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-4 border-b border-zinc-200">
            <div>
                <h1 class="text-xl sm:text-2xl font-extrabold text-zinc-900 tracking-tight">
                    Pengumuman & Warta Sekolah
                </h1>
                <p class="text-xs text-zinc-500 mt-0.5">
                    Informasi resmi, kalender kegiatan, dan pemberitahuan penting institusi sekolah
                </p>
            </div>

            @if (auth()->user()->hasRole(\App\Enums\UserRole::SUPER_ADMIN, \App\Enums\UserRole::SCHOOL_ADMIN, \App\Enums\UserRole::TEACHER))
                <div class="flex items-center gap-2">
                    <a
                        href="{{ route('announcements.create') }}"
                        class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-xs font-bold bg-[#E11D2E] hover:bg-[#C81423] text-white shadow-xs transition-colors"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        <span>Buat Pengumuman</span>
                    </a>
                </div>
            @endif
        </div>

        @if (session('success'))
            <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-xs text-emerald-800 font-bold flex items-center gap-2">
                <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        <!-- Filter & Search Controls -->
        <form action="{{ route('announcements.index') }}" method="GET" class="bg-white p-4 rounded-2xl border border-zinc-200/80 shadow-xs flex flex-wrap items-center justify-between gap-3">
            <div class="flex flex-wrap items-center gap-2 w-full sm:w-auto">
                <input
                    type="text"
                    name="search"
                    value="{{ request('search') }}"
                    placeholder="Cari kata kunci..."
                    class="text-xs rounded-xl border-zinc-300 focus:border-[#E11D2E] focus:ring-[#E11D2E] w-48 sm:w-64"
                >
                <select name="category" class="text-xs rounded-xl border-zinc-300 focus:border-[#E11D2E] focus:ring-[#E11D2E]">
                    <option value="">Semua Kategori</option>
                    @foreach (\App\Models\SchoolAnnouncement::CATEGORIES as $key => $label)
                        <option value="{{ $key }}" {{ request('category') === $key ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>

                @if (auth()->user()->hasRole(\App\Enums\UserRole::SUPER_ADMIN, \App\Enums\UserRole::SCHOOL_ADMIN, \App\Enums\UserRole::TEACHER))
                    <select name="status" class="text-xs rounded-xl border-zinc-300 focus:border-[#E11D2E] focus:ring-[#E11D2E]">
                        <option value="">Semua Status</option>
                        <option value="published" {{ request('status') === 'published' ? 'selected' : '' }}>Diterbitkan</option>
                        <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>Draf</option>
                    </select>
                @endif

                <button type="submit" class="px-3.5 py-2 text-xs font-bold rounded-xl bg-zinc-800 text-white hover:bg-zinc-900 transition-colors">
                    Filter
                </button>
                @if (request()->hasAny(['search', 'category', 'status']))
                    <a href="{{ route('announcements.index') }}" class="text-xs font-semibold text-zinc-500 hover:text-zinc-800">Reset</a>
                @endif
            </div>

            <span class="text-xs text-zinc-500 font-semibold">
                {{ $announcements->total() }} Pengumuman
            </span>
        </form>

        <!-- Announcements Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
            @forelse ($announcements as $ann)
                <div class="bg-white rounded-2xl border {{ $ann->is_pinned ? 'border-amber-300 ring-1 ring-amber-200' : 'border-zinc-200' }} shadow-xs hover:shadow-md transition-shadow p-5 flex flex-col justify-between">
                    <div>
                        <!-- Category, Target & Pin Badges -->
                        <div class="flex items-center gap-1.5 mb-3 flex-wrap">
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold border {{ $ann->categoryBadgeClasses() }}">
                                {{ $ann->categoryLabel() }}
                            </span>
                            @if ($ann->is_pinned)
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-amber-50 text-amber-800 border border-amber-200 flex items-center gap-1">
                                    <svg class="w-3 h-3 text-amber-600" fill="currentColor" viewBox="0 0 20 20"><path d="M5 4a2 2 0 012-2h6a2 2 0 012 2v14l-5-2.5L5 18V4z"/></svg>
                                    <span>Penting</span>
                                </span>
                            @endif
                            @if ($ann->status === 'draft')
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-zinc-100 text-zinc-600 border border-zinc-200">
                                    Draf
                                </span>
                            @endif
                        </div>

                        <a href="{{ route('announcements.show', $ann) }}" class="block group">
                            <h3 class="font-extrabold text-base text-zinc-900 group-hover:text-brand-600 transition-colors line-clamp-2">
                                {{ $ann->title }}
                            </h3>
                            <p class="text-xs text-zinc-500 mt-2 line-clamp-3 leading-relaxed">
                                {{ strip_tags($ann->content) }}
                            </p>
                        </a>
                    </div>

                    <div class="mt-5 pt-3 border-t border-zinc-100 flex items-center justify-between text-[11px] text-zinc-400">
                        <div class="flex items-center gap-1.5 truncate">
                            <span>Oleh: <strong class="text-zinc-700">{{ $ann->author?->name ?? 'Admin' }}</strong></span>
                        </div>
                        <span class="shrink-0">{{ $ann->published_at ? $ann->published_at->diffForHumans() : $ann->created_at->diffForHumans() }}</span>
                    </div>
                </div>
            @empty
                <div class="col-span-full py-12 text-center text-zinc-400 bg-white rounded-2xl border border-zinc-200">
                    <svg class="w-10 h-10 mx-auto mb-2 text-zinc-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"/></svg>
                    <p class="text-xs text-zinc-500 font-medium">Tidak ada pengumuman yang sesuai</p>
                </div>
            @endforelse
        </div>

        @if ($announcements->hasPages())
            <div class="p-4 bg-white rounded-2xl border border-zinc-200 shadow-xs">
                {{ $announcements->links() }}
            </div>
        @endif

    </div>
</x-layouts.app>
