@extends('layouts.school-public')

@section('content')
    <!-- Header Banner -->
    <div class="bg-zinc-900 text-white py-14 sm:py-20 border-b border-zinc-800">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <span class="inline-block px-3 py-1 rounded-full text-xs font-bold bg-[#E11D2E] text-white mb-3">
                Warta Sekolah
            </span>
            <h1 class="text-3xl sm:text-4xl lg:text-5xl font-extrabold tracking-tight">
                Berita, Informasi & Artikel
            </h1>
            <p class="text-zinc-400 text-sm sm:text-base mt-2 max-w-2xl">
                Kabar terbaru seputar kegiatan akademik, prestasi, dan pengumuman resmi {{ $school->name }}.
            </p>
        </div>
    </div>

    <!-- Filter & Content -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 lg:py-14 space-y-8">
        
        <!-- Search & Filter Controls -->
        <div class="bg-white rounded-2xl p-4 border border-zinc-200/80 shadow-2xs flex flex-col md:flex-row items-center justify-between gap-4">
            <!-- Category Chips -->
            <div class="flex items-center gap-1.5 flex-wrap w-full md:w-auto">
                <a href="{{ route('school.public.articles', ['school' => $school->slug]) }}" class="px-3 py-1.5 rounded-lg text-xs font-semibold transition-colors {{ empty($category) ? 'bg-[#E11D2E] text-white' : 'bg-zinc-100 text-zinc-600 hover:bg-zinc-200' }}">
                    Semua Kategori
                </a>
                @foreach ($categories as $cat)
                    <a href="{{ route('school.public.articles', ['school' => $school->slug, 'category' => $cat]) }}" class="px-3 py-1.5 rounded-lg text-xs font-semibold transition-colors {{ $category === $cat ? 'bg-[#E11D2E] text-white' : 'bg-zinc-100 text-zinc-600 hover:bg-zinc-200' }}">
                        {{ $cat }}
                    </a>
                @endforeach
            </div>

            <!-- Search Input -->
            <form method="GET" action="{{ route('school.public.articles', ['school' => $school->slug]) }}" class="w-full md:w-72">
                @if ($category)
                    <input type="hidden" name="category" value="{{ $category }}">
                @endif
                <div class="relative">
                    <input type="text" name="search" value="{{ $search }}" placeholder="Cari judul berita..." class="w-full pl-9 pr-3 py-2 text-xs rounded-xl border border-zinc-200 focus:outline-none focus:border-[#E11D2E] focus:ring-1 focus:ring-[#E11D2E]">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-zinc-400">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    </div>
                </div>
            </form>
        </div>

        <!-- Articles Grid -->
        @if ($articles->isEmpty())
            <div class="p-12 text-center bg-white rounded-3xl border border-zinc-200 text-zinc-400 space-y-2">
                <svg class="w-12 h-12 mx-auto text-zinc-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"/></svg>
                <p class="font-bold text-zinc-600 text-base">Tidak ada artikel yang ditemukan</p>
                <p class="text-xs">Coba ubah kata kunci pencarian atau pilih kategori lain.</p>
            </div>
        @else
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach ($articles as $art)
                    <article class="bg-white rounded-2xl overflow-hidden border border-zinc-200/80 shadow-2xs hover:shadow-md transition-all flex flex-col">
                        <div class="h-48 bg-zinc-100 overflow-hidden relative">
                            @if ($art->coverImageUrl())
                                <img src="{{ $art->coverImageUrl() }}" alt="{{ $art->title }}" class="w-full h-full object-cover">
                            @else
                                <div class="w-full h-full flex items-center justify-center bg-zinc-100 text-zinc-300">
                                    <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"/></svg>
                                </div>
                            @endif
                            @if ($art->category)
                                <span class="absolute top-3 left-3 px-2 py-0.5 text-[10px] font-bold rounded bg-zinc-900/80 text-white backdrop-blur-xs">
                                    {{ $art->category }}
                                </span>
                            @endif
                        </div>
                        <div class="p-5 flex-1 flex flex-col justify-between">
                            <div>
                                <span class="text-[11px] text-zinc-400 block mb-1">
                                    {{ $art->published_at ? $art->published_at->format('d F Y') : 'Baru' }}
                                </span>
                                <h3 class="font-bold text-base text-zinc-900 leading-snug line-clamp-2 mb-2 hover:text-[#E11D2E] transition-colors">
                                    <a href="{{ route('school.public.article-detail', ['school' => $school->slug, 'slug' => $art->slug]) }}">
                                        {{ $art->title }}
                                    </a>
                                </h3>
                                <p class="text-xs text-zinc-500 line-clamp-3 leading-relaxed">
                                    {{ $art->excerpt ?: Str::limit(strip_tags($art->content), 120) }}
                                </p>
                            </div>
                            <div class="pt-4 mt-4 border-t border-zinc-100 flex items-center justify-between">
                                <a href="{{ route('school.public.article-detail', ['school' => $school->slug, 'slug' => $art->slug]) }}" class="text-xs font-bold text-[#E11D2E] hover:underline inline-flex items-center gap-1">
                                    <span>Baca Selengkapnya</span>
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                </a>
                                <span class="text-[11px] text-zinc-400">{{ $art->view_count }} tayangan</span>
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>

            <!-- Pagination -->
            <div class="mt-8">
                {{ $articles->links() }}
            </div>
        @endif

    </div>
@endsection
