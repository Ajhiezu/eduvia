@extends('layouts.school-public')

@section('content')
    <!-- Editorial Header Section -->
    <section class="relative bg-white border-b border-[#E4E7EC] pt-12 pb-16 lg:pt-16 lg:pb-20 overflow-hidden">
        <div class="absolute inset-0 bg-radial from-slate-100/60 via-transparent to-transparent opacity-80 pointer-events-none"></div>

        <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col md:flex-row md:items-end md:justify-between gap-6">
                <div class="max-w-3xl space-y-4">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-[#161F2E]/5 border border-[#161F2E]/10 text-xs font-semibold text-[#161F2E] tracking-wider uppercase">
                        <span class="w-1.5 h-1.5 rounded-full bg-[#161F2E]"></span>
                        Dokumentasi Visual & Kegiatan
                    </div>
                    <h1 class="text-3xl sm:text-4xl lg:text-5xl font-serif font-bold text-[#161F2E] tracking-tight leading-tight">
                        Galeri Foto & Momen Sekolah
                    </h1>
                    <p class="text-[#4B5563] text-base sm:text-lg leading-relaxed max-w-2xl">
                        Rangkaian arsip visual kegiatan pembelajaran, prestasi siswa, ekstrakurikuler, dan momentum kebersamaan di {{ $school->name }}.
                    </p>
                </div>

                @if(!empty($albums) && count($albums) > 0)
                    <div class="text-xs text-[#6B7280] font-medium bg-[#F8F9FA] px-4 py-2 rounded-full border border-[#E4E7EC] self-start md:self-auto">
                        Total {{ count($albums) }} Kategori Album Tersedia
                    </div>
                @endif
            </div>
        </div>
    </section>

    <!-- Main Content & Gallery -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 lg:py-14 space-y-10" x-data="{ previewModal: false, activeImg: '', activeTitle: '', activeCaption: '', activeAlbum: '' }">
        
        <!-- Album Filter Chips -->
        <div class="flex items-center gap-2 flex-wrap">
            <a href="{{ route('school.public.gallery', ['school' => $school->slug]) }}" 
               class="px-5 py-2.5 rounded-full text-xs font-semibold tracking-wide transition-all shadow-2xs {{ empty($album) ? 'bg-[#161F2E] text-white shadow-md' : 'bg-white text-[#4B5563] hover:bg-[#F8F9FA] hover:text-[#161F2E] border border-[#E4E7EC]' }}">
                Semua Album
            </a>
            @foreach ($albums as $alb)
                <a href="{{ route('school.public.gallery', ['school' => $school->slug, 'album' => $alb]) }}" 
                   class="px-5 py-2.5 rounded-full text-xs font-semibold tracking-wide transition-all shadow-2xs {{ $album === $alb ? 'bg-[#161F2E] text-white shadow-md' : 'bg-white text-[#4B5563] hover:bg-[#F8F9FA] hover:text-[#161F2E] border border-[#E4E7EC]' }}">
                    {{ $alb }}
                </a>
            @endforeach
        </div>

        @if ($items->isEmpty())
            <div class="p-16 text-center bg-white rounded-3xl border border-[#E4E7EC] text-[#6B7280] max-w-2xl mx-auto shadow-sm space-y-3">
                <div class="w-14 h-14 rounded-full bg-[#161F2E]/5 text-[#161F2E] flex items-center justify-center mx-auto mb-2">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                </div>
                <h4 class="text-lg font-serif font-bold text-[#161F2E]">Belum Ada Dokumentasi</h4>
                <p class="text-sm">Belum ada foto yang dipublikasikan dalam album ini. Silakan periksa kembali album lainnya.</p>
            </div>
        @else
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
                @foreach ($items as $photo)
                    @php
                        $photoUrl = $photo->mediaUrl();
                        $photoTitle = addslashes($photo->title);
                        $photoCaption = addslashes($photo->caption ?? '');
                        $photoAlbum = addslashes($photo->album_name);
                    @endphp
                    <div class="bg-white rounded-2xl overflow-hidden border border-[#E4E7EC] shadow-2xs hover:shadow-xl hover:border-[#161F2E]/40 transition-all duration-300 group flex flex-col justify-between cursor-pointer"
                         @click="activeImg = '{{ $photoUrl }}'; activeTitle = '{{ $photoTitle }}'; activeCaption = '{{ $photoCaption }}'; activeAlbum = '{{ $photoAlbum }}'; previewModal = true">
                        <div class="aspect-4/3 bg-slate-100 overflow-hidden relative">
                            @if ($photoUrl)
                                <img src="{{ $photoUrl }}" alt="{{ $photo->alt_text ?: $photo->title }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                            @else
                                <div class="w-full h-full flex flex-col items-center justify-center p-4 bg-[#161F2E]/5 text-[#6B7280]">
                                    <svg class="w-10 h-10 mb-1 text-[#9CA3AF]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                </div>
                            @endif
                            <span class="absolute top-3 left-3 px-2.5 py-1 text-[11px] font-semibold tracking-wide rounded-full bg-[#161F2E]/80 text-white backdrop-blur-md shadow-xs">
                                {{ $photo->album_name }}
                            </span>
                            <div class="absolute inset-0 bg-[#161F2E]/20 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
                                <span class="w-10 h-10 rounded-full bg-white/90 text-[#161F2E] flex items-center justify-center shadow-md transform scale-75 group-hover:scale-100 transition-transform">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v3m0 0v3m0-3h3m-3 0H7"/></svg>
                                </span>
                            </div>
                        </div>
                        <div class="p-5 flex-1 flex flex-col justify-between">
                            <div>
                                <h4 class="font-bold text-sm text-[#161F2E] leading-snug line-clamp-1 mb-1.5 group-hover:text-[#161F2E]/80">
                                    {{ $photo->title }}
                                </h4>
                                @if ($photo->caption)
                                    <p class="text-xs text-[#6B7280] line-clamp-2 leading-relaxed">
                                        {{ $photo->caption }}
                                    </p>
                                @endif
                            </div>
                            <div class="mt-4 pt-3 border-t border-[#F1F3F5] flex items-center justify-between text-[11px] text-[#9CA3AF]">
                                <span>Klik untuk memperbesar</span>
                                <svg class="w-3.5 h-3.5 text-[#161F2E]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="mt-8">
                {{ $items->links() }}
            </div>
        @endif

        <!-- Lightbox / Preview Modal -->
        <div x-show="previewModal" 
             x-cloak 
             @keydown.escape.window="previewModal = false"
             class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4 sm:p-6 bg-[#161F2E]/80 backdrop-blur-sm"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0">
            <div class="relative bg-white rounded-3xl max-w-4xl w-full overflow-hidden shadow-2xl border border-white/20"
                 @click.away="previewModal = false">
                <button type="button" @click="previewModal = false" class="absolute top-4 right-4 z-10 w-9 h-9 rounded-full bg-black/60 text-white hover:bg-black flex items-center justify-center transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
                <div class="bg-black flex items-center justify-center max-h-[70vh] overflow-hidden">
                    <img :src="activeImg" :alt="activeTitle" class="max-h-[70vh] w-auto object-contain">
                </div>
                <div class="p-6 bg-white space-y-2">
                    <div class="inline-block px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-[#161F2E]/5 text-[#161F2E]" x-text="activeAlbum"></div>
                    <h3 class="text-lg font-serif font-bold text-[#161F2E]" x-text="activeTitle"></h3>
                    <p class="text-sm text-[#4B5563]" x-text="activeCaption" x-show="activeCaption"></p>
                </div>
            </div>
        </div>

    </div>
@endsection

