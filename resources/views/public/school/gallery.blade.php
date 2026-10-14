@extends('layouts.school-public')

@section('content')
    <!-- Header Banner -->
    <div class="bg-zinc-900 text-white py-14 sm:py-20 border-b border-zinc-800">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <span class="inline-block px-3 py-1 rounded-full text-xs font-bold bg-[#E11D2E] text-white mb-3">
                Dokumentasi Visual
            </span>
            <h1 class="text-3xl sm:text-4xl lg:text-5xl font-extrabold tracking-tight">
                Galeri Foto & Dokumentasi
            </h1>
            <p class="text-zinc-400 text-sm sm:text-base mt-2 max-w-2xl">
                Koleksi momen terbaik proses belajar, kegiatan siswa, upacara, dan perayaan di {{ $school->name }}.
            </p>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 lg:py-14 space-y-8">
        
        <!-- Album Filter Chips -->
        <div class="flex items-center gap-1.5 flex-wrap">
            <a href="{{ route('school.public.gallery', ['school' => $school->slug]) }}" class="px-3.5 py-2 rounded-xl text-xs font-bold transition-colors {{ empty($album) ? 'bg-[#E11D2E] text-white shadow-xs' : 'bg-white text-zinc-700 hover:bg-zinc-100 border border-zinc-200' }}">
                Semua Album
            </a>
            @foreach ($albums as $alb)
                <a href="{{ route('school.public.gallery', ['school' => $school->slug, 'album' => $alb]) }}" class="px-3.5 py-2 rounded-xl text-xs font-bold transition-colors {{ $album === $alb ? 'bg-[#E11D2E] text-white shadow-xs' : 'bg-white text-zinc-700 hover:bg-zinc-100 border border-zinc-200' }}">
                    {{ $alb }}
                </a>
            @endforeach
        </div>

        @if ($items->isEmpty())
            <div class="p-12 text-center bg-white rounded-3xl border border-zinc-200 text-zinc-400">
                Belum ada foto yang dipublikasikan dalam album ini.
            </div>
        @else
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
                @foreach ($items as $photo)
                    <div class="bg-white rounded-2xl overflow-hidden border border-zinc-200/80 shadow-2xs hover:shadow-md transition-all group flex flex-col justify-between">
                        <div class="aspect-4/3 bg-zinc-100 overflow-hidden relative">
                            @if ($photo->mediaUrl())
                                <img src="{{ $photo->mediaUrl() }}" alt="{{ $photo->alt_text ?: $photo->title }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                            @else
                                <div class="w-full h-full flex flex-col items-center justify-center p-4 bg-zinc-800 text-zinc-400">
                                    <svg class="w-10 h-10 mb-1 text-zinc-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                </div>
                            @endif
                            <span class="absolute top-2.5 left-2.5 px-2 py-0.5 text-[10px] font-bold rounded bg-black/60 text-white backdrop-blur-xs">
                                {{ $photo->album_name }}
                            </span>
                        </div>
                        <div class="p-4">
                            <h4 class="font-bold text-sm text-zinc-900 leading-snug line-clamp-1 mb-1">
                                {{ $photo->title }}
                            </h4>
                            @if ($photo->caption)
                                <p class="text-xs text-zinc-500 line-clamp-2 leading-relaxed">
                                    {{ $photo->caption }}
                                </p>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="mt-8">
                {{ $items->links() }}
            </div>
        @endif

    </div>
@endsection
