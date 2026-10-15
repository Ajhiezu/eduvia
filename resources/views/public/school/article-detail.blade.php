@extends('layouts.school-public')

@section('content')
    <article class="py-12 sm:py-16 bg-[#F8F9FA] border-b border-zinc-200/80">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            
            <!-- Breadcrumbs -->
            <nav class="flex items-center gap-2 text-xs text-zinc-400 mb-8 font-sans">
                <a href="{{ $school->slug === 'nusantara-utama' ? route('home') : route('school.public.home', ['school' => $school->slug]) }}" class="hover:text-zinc-900 transition-colors">Beranda</a>
                <span>&rsaquo;</span>
                <a href="{{ route('school.public.articles', ['school' => $school->slug]) }}" class="hover:text-zinc-900 transition-colors">Warta Berita</a>
                <span>&rsaquo;</span>
                <span class="text-zinc-900 font-semibold truncate max-w-xs">{{ $post->title }}</span>
            </nav>

            <!-- Card Wrapper -->
            <div class="bg-white rounded-3xl p-7 sm:p-12 border border-zinc-200/90 shadow-sm">
                
                <!-- Metadata Header -->
                <div class="space-y-4 mb-8">
                    <div class="flex items-center gap-2">
                        @if ($post->category)
                            <span class="px-3 py-1 rounded-full text-[10px] font-bold bg-[#161F2E] text-white font-sans">
                                {{ $post->category }}
                            </span>
                        @endif
                        @if (! $post->is_published)
                            <span class="px-3 py-1 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800 border border-amber-200 font-sans">
                                Draf Internal (Pratinjau)
                            </span>
                        @endif
                    </div>

                    <h1 class="text-3xl sm:text-4xl md:text-5xl font-serif font-normal text-zinc-900 tracking-tight leading-[1.12]">
                        {{ $post->title }}
                    </h1>

                    <div class="flex flex-wrap items-center gap-4 text-xs text-zinc-400 pt-3 border-b border-zinc-100 pb-5 font-sans">
                        <span>Dipublikasikan: <strong class="text-zinc-700">{{ $post->published_at ? $post->published_at->format('d F Y') : 'Draf' }}</strong></span>
                        <span>&bull;</span>
                        <span>Lembaga: <strong class="text-zinc-700">{{ $school->name }}</strong></span>
                        <span>&bull;</span>
                        <span class="font-mono">{{ $post->view_count }} views</span>
                    </div>
                </div>

                <!-- Cover Image -->
                @if ($post->coverImageUrl())
                    <div class="rounded-3xl overflow-hidden mb-8 border border-zinc-200 shadow-sm max-h-[500px]">
                        <img src="{{ $post->coverImageUrl() }}" alt="{{ $post->title }}" class="w-full h-full object-cover">
                    </div>
                @endif

                <!-- Excerpt Callout -->
                @if ($post->excerpt)
                    <div class="p-6 rounded-2xl bg-zinc-50 border-l-4 border-[#161F2E] text-zinc-700 text-sm sm:text-base leading-relaxed italic mb-8 font-sans">
                        {{ $post->excerpt }}
                    </div>
                @endif

                <!-- Article Body Content -->
                <div class="prose prose-zinc max-w-none text-zinc-700 text-sm sm:text-base leading-relaxed space-y-4 font-sans">
                    {!! nl2br(e($post->content)) !!}
                </div>

                <!-- Back & Share Bar -->
                <div class="mt-12 pt-6 border-t border-zinc-100 flex items-center justify-between font-sans">
                    <a href="{{ route('school.public.articles', ['school' => $school->slug]) }}" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-full text-xs font-semibold border border-zinc-900 text-zinc-900 hover:bg-zinc-900 hover:text-white transition-all">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                        <span>Kembali ke Indeks Berita</span>
                    </a>
                </div>

                <!-- Related Articles -->
                @if ($relatedPosts->isNotEmpty())
                    <div class="mt-14 pt-8 border-t border-zinc-100 font-sans">
                        <span class="text-[10px] font-bold tracking-widest uppercase text-zinc-400 block mb-1">RECOMMENDED</span>
                        <h3 class="text-xl sm:text-2xl font-serif font-normal text-zinc-900 mb-6">Artikel Terkait Lainnya</h3>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                            @foreach ($relatedPosts as $rel)
                                <a href="{{ route('school.public.article-detail', ['school' => $school->slug, 'slug' => $rel->slug]) }}" class="bg-zinc-50 rounded-2xl p-5 border border-zinc-200/80 hover:bg-white hover:shadow-sm transition-all flex flex-col justify-between group">
                                    <div>
                                        <span class="text-[10px] text-zinc-400 block mb-1 font-mono">{{ $rel->published_at ? $rel->published_at->format('d M Y') : 'Terbaru' }}</span>
                                        <h4 class="font-bold text-xs sm:text-sm text-zinc-900 group-hover:text-zinc-600 line-clamp-2 leading-snug">{{ $rel->title }}</h4>
                                    </div>
                                    <span class="text-xs font-semibold text-zinc-900 mt-4 inline-flex items-center gap-1 group-hover:translate-x-0.5 transition-transform">
                                        <span>Baca Artikel</span>
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7m0 0l-7 7"/></svg>
                                    </span>
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endif

            </div>

        </div>
    </article>
@endsection
