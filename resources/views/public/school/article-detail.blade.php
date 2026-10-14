@extends('layouts.school-public')

@section('content')
    <article class="py-12 sm:py-16">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            
            <!-- Breadcrumbs -->
            <nav class="flex items-center gap-2 text-xs text-zinc-500 mb-6">
                <a href="{{ route('school.public.home', ['school' => $school->slug]) }}" class="hover:text-zinc-900">Beranda</a>
                <span>/</span>
                <a href="{{ route('school.public.articles', ['school' => $school->slug]) }}" class="hover:text-zinc-900">Warta Berita</a>
                <span>/</span>
                <span class="text-zinc-400 truncate max-w-xs">{{ $post->title }}</span>
            </nav>

            <!-- Metadata Header -->
            <div class="space-y-4 mb-8">
                <div class="flex items-center gap-2">
                    @if ($post->category)
                        <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-rose-50 text-[#E11D2E] border border-rose-100">
                            {{ $post->category }}
                        </span>
                    @endif
                    @if (! $post->is_published)
                        <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-amber-100 text-amber-800 border border-amber-200">
                            Draf Internal (Pratinjau)
                        </span>
                    @endif
                </div>

                <h1 class="text-2xl sm:text-3xl md:text-4xl font-extrabold text-zinc-900 leading-tight">
                    {{ $post->title }}
                </h1>

                <div class="flex flex-wrap items-center gap-4 text-xs text-zinc-500 pt-2 border-b border-zinc-200 pb-4">
                    <span>Dipublikasikan: <strong>{{ $post->published_at ? $post->published_at->format('d F Y') : 'Draf' }}</strong></span>
                    <span>&bull;</span>
                    <span>Lembaga: <strong>{{ $school->name }}</strong></span>
                    <span>&bull;</span>
                    <span>{{ $post->view_count }} kali dibaca</span>
                </div>
            </div>

            <!-- Cover Image -->
            @if ($post->coverImageUrl())
                <div class="rounded-3xl overflow-hidden mb-8 border border-zinc-200 shadow-sm max-h-[480px]">
                    <img src="{{ $post->coverImageUrl() }}" alt="{{ $post->title }}" class="w-full h-full object-cover">
                </div>
            @endif

            <!-- Excerpt Callout -->
            @if ($post->excerpt)
                <div class="p-5 rounded-2xl bg-zinc-50 border-l-4 border-[#E11D2E] text-zinc-700 text-sm sm:text-base leading-relaxed italic mb-8">
                    {{ $post->excerpt }}
                </div>
            @endif

            <!-- Article Body Content -->
            <div class="prose prose-zinc max-w-none text-zinc-800 text-sm sm:text-base leading-relaxed space-y-4">
                {!! nl2br(e($post->content)) !!}
            </div>

            <!-- Back & Share Bar -->
            <div class="mt-12 pt-6 border-t border-zinc-200 flex items-center justify-between">
                <a href="{{ route('school.public.articles', ['school' => $school->slug]) }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-bold bg-zinc-100 hover:bg-zinc-200 text-zinc-800 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                    <span>Kembali ke Indeks Berita</span>
                </a>
            </div>

            <!-- Related Articles -->
            @if ($relatedPosts->isNotEmpty())
                <div class="mt-16 pt-10 border-t border-zinc-200">
                    <h3 class="text-xl font-bold text-zinc-900 mb-6">Artikel Terkait Lainnya</h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        @foreach ($relatedPosts as $rel)
                            <a href="{{ route('school.public.article-detail', ['school' => $school->slug, 'slug' => $rel->slug]) }}" class="bg-white rounded-2xl p-4 border border-zinc-200 hover:border-rose-200 hover:shadow-xs transition-all flex flex-col justify-between">
                                <div>
                                    <span class="text-[10px] text-zinc-400 block mb-1">{{ $rel->published_at ? $rel->published_at->format('d M Y') : 'Terbaru' }}</span>
                                    <h4 class="font-bold text-xs sm:text-sm text-zinc-900 line-clamp-2">{{ $rel->title }}</h4>
                                </div>
                                <span class="text-xs font-semibold text-[#E11D2E] mt-3">Baca &rarr;</span>
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif

        </div>
    </article>
@endsection
