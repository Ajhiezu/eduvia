@extends('layouts.school-public')

@section('content')

    <!-- Header Banner -->
    <div class="bg-white py-12 sm:py-16 border-b border-zinc-200/80">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <nav class="flex items-center gap-2 text-xs text-zinc-400 mb-6 font-sans">
                <a href="{{ $school->slug === 'nusantara-utama' ? route('home') : route('school.public.home', ['school' => $school->slug]) }}" class="hover:text-zinc-900 transition-colors">Beranda</a>
                <span>&rsaquo;</span>
                <span class="text-zinc-900 font-semibold">Berita & Artikel</span>
            </nav>

            <span class="text-[11px] font-bold tracking-[0.2em] uppercase text-zinc-400 block mb-3 font-sans">
                NEWS & INSIGHTS
            </span>
            <h1 class="text-4xl sm:text-5xl lg:text-6xl font-serif font-normal text-zinc-900 tracking-tight leading-[1.08]">
                Berita, Informasi & Artikel
            </h1>
            <p class="text-zinc-600 text-sm sm:text-base mt-4 max-w-2xl font-sans leading-relaxed">
                Kabar terbaru seputar prestasi siswa, inovasi riset, kegiatan akademik, serta pengumuman resmi {{ $school->name }}.
            </p>
        </div>
    </div>

    <!-- Filter & Content -->
    <div class="bg-[#F8F9FA] py-10 lg:py-14 border-b border-zinc-200/80">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">
            
            <!-- Search & Filter Controls -->
            <div class="bg-white rounded-3xl p-4 sm:p-5 border border-zinc-200/90 shadow-sm flex flex-col md:flex-row items-center justify-between gap-4 font-sans">
                <!-- Category Chips -->
                <div class="flex items-center gap-2 flex-wrap w-full md:w-auto">
                    <a href="{{ route('school.public.articles', ['school' => $school->slug]) }}" class="px-4 py-2 rounded-full text-xs font-semibold transition-all {{ empty($category) ? 'bg-[#161F2E] text-white shadow-xs' : 'bg-zinc-100 text-zinc-600 hover:bg-zinc-200' }}">
                        Semua Kategori
                    </a>
                    @foreach ($categories as $cat)
                        <a href="{{ route('school.public.articles', ['school' => $school->slug, 'category' => $cat]) }}" class="px-4 py-2 rounded-full text-xs font-semibold transition-all {{ $category === $cat ? 'bg-[#161F2E] text-white shadow-xs' : 'bg-zinc-100 text-zinc-600 hover:bg-zinc-200' }}">
                            {{ $cat }}
                        </a>
                    @endforeach
                </div>

                <!-- Search Input -->
                <form method="GET" action="{{ route('school.public.articles', ['school' => $school->slug]) }}" class="w-full md:w-80">
                    @if ($category)
                        <input type="hidden" name="category" value="{{ $category }}">
                    @endif
                    <div class="relative">
                        <input 
                            type="text" 
                            name="search" 
                            value="{{ $search }}" 
                            placeholder="Cari topik atau judul artikel..." 
                            class="w-full pl-10 pr-4 py-2.5 text-xs rounded-full border border-zinc-200 bg-zinc-50/50 focus:bg-white focus:outline-none focus:border-zinc-900 focus:ring-2 focus:ring-zinc-900/10 transition-all text-zinc-900 placeholder:text-zinc-400"
                        >
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-zinc-400">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Articles Grid -->
            @if ($articles->isEmpty())
                <div class="p-16 text-center bg-white rounded-3xl border border-zinc-200/90 shadow-sm text-zinc-400 space-y-3 font-sans">
                    <svg class="w-12 h-12 mx-auto text-zinc-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"/></svg>
                    <p class="font-bold text-zinc-700 text-base">Tidak ada artikel yang ditemukan</p>
                    <p class="text-xs text-zinc-500">Coba ubah kata kunci pencarian atau pilih kategori lain.</p>
                </div>
            @else
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 sm:gap-8 font-sans">
                    @foreach ($articles as $art)
                        <article class="bg-white rounded-3xl overflow-hidden border border-zinc-200/80 shadow-sm hover:shadow-md transition-all flex flex-col group">
                            <div class="h-48 sm:h-52 bg-zinc-100 overflow-hidden relative">
                                @if ($art->coverImageUrl())
                                    <img src="{{ $art->coverImageUrl() }}" alt="{{ $art->title }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                                @else
                                    <div class="w-full h-full flex items-center justify-center bg-zinc-100 text-zinc-300">
                                        <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"/></svg>
                                    </div>
                                @endif
                                @if ($art->category)
                                    <span class="absolute top-3.5 left-3.5 px-3 py-1 text-[10px] font-bold rounded-full bg-zinc-900/80 text-white backdrop-blur-xs">
                                        {{ $art->category }}
                                    </span>
                                @endif
                            </div>
                            <div class="p-6 flex-1 flex flex-col justify-between">
                                <div>
                                    <span class="text-[11px] text-zinc-400 block mb-2 font-mono">
                                        {{ $art->published_at ? $art->published_at->format('d F Y') : 'Terbaru' }}
                                    </span>
                                    <h3 class="font-bold text-base text-zinc-900 leading-snug line-clamp-2 mb-2 group-hover:text-zinc-600 transition-colors font-sans">
                                        <a href="{{ route('school.public.article-detail', ['school' => $school->slug, 'slug' => $art->slug]) }}">
                                            {{ $art->title }}
                                        </a>
                                    </h3>
                                    <p class="text-xs text-zinc-500 line-clamp-3 leading-relaxed">
                                        {{ $art->excerpt ?: Str::limit(strip_tags($art->content), 120) }}
                                    </p>
                                </div>
                                <div class="pt-4 mt-4 border-t border-zinc-100 flex items-center justify-between">
                                    <a href="{{ route('school.public.article-detail', ['school' => $school->slug, 'slug' => $art->slug]) }}" class="text-xs font-semibold text-zinc-900 hover:underline inline-flex items-center gap-1">
                                        <span>Baca Selengkapnya</span>
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7m0 0l-7 7"/></svg>
                                    </a>
                                    <span class="text-[11px] text-zinc-400 font-mono">{{ $art->view_count }} views</span>
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>

                <!-- Pagination -->
                @if ($articles->hasPages())
                    <div class="pt-6 font-sans">
                        {{ $articles->links() }}
                    </div>
                @endif
            @endif

        </div>
    </div>
@endsection
