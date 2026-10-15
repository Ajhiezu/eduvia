@extends('layouts.school-public')

@section('content')

    <!-- Header Banner -->
    <div class="bg-white py-12 sm:py-16 border-b border-zinc-200/80">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <nav class="flex items-center gap-2 text-xs text-zinc-400 mb-6 font-sans">
                <a href="{{ $school->slug === 'nusantara-utama' ? route('home') : route('school.public.home', ['school' => $school->slug]) }}" class="hover:text-zinc-900 transition-colors">Beranda</a>
                <span>&rsaquo;</span>
                <span class="text-zinc-900 font-semibold">Fasilitas & Sarana</span>
            </nav>

            <span class="text-[11px] font-bold tracking-[0.2em] uppercase text-zinc-400 block mb-3 font-sans">
                CAMPUS & FACILITIES
            </span>
            <h1 class="text-4xl sm:text-5xl lg:text-6xl font-serif font-normal text-zinc-900 tracking-tight leading-[1.08]">
                Fasilitas & Sarana Belajar
            </h1>
            <p class="text-zinc-600 text-sm sm:text-base mt-4 max-w-2xl font-sans leading-relaxed">
                Sarana pembelajaran modern, laboratorium sains terpadu, studio seni, dan kurikulum unggulan untuk mendukung capaian prestasi siswa {{ $school->name }}.
            </p>
        </div>
    </div>

    <div class="bg-[#F8F9FA] py-12 lg:py-16 border-b border-zinc-200/80 font-sans">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-16">
            
            <!-- Section 1: Program Unggulan -->
            @if ($programs->isNotEmpty())
                <div>
                    <div class="mb-8">
                        <span class="text-[10px] font-bold tracking-widest uppercase text-zinc-400 block mb-1">SIGNATURE PROGRAMS</span>
                        <h2 class="text-2xl sm:text-3xl font-serif font-normal text-zinc-900">Program Unggulan Sekolah</h2>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 sm:gap-8">
                        @foreach ($programs as $prog)
                            <div class="bg-white rounded-3xl p-7 border border-zinc-200/90 shadow-sm hover:shadow-md transition-all group">
                                <div class="w-12 h-12 rounded-2xl bg-[#161F2E] text-white flex items-center justify-center font-bold mb-5 shadow-xs">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/></svg>
                                </div>
                                <h3 class="text-lg font-bold text-zinc-900 mb-2 group-hover:text-zinc-600 transition-colors">{{ $prog->name }}</h3>
                                <p class="text-xs sm:text-sm text-zinc-600 leading-relaxed">{{ $prog->description }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- Section 2: Sarana & Fasilitas -->
            @if ($facilities->isNotEmpty())
                <div>
                    <div class="mb-8">
                        <span class="text-[10px] font-bold tracking-widest uppercase text-zinc-400 block mb-1">INFRASTRUCTURE</span>
                        <h2 class="text-2xl sm:text-3xl font-serif font-normal text-zinc-900">Sarana & Prasarana Kampus</h2>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                        @foreach ($facilities as $fac)
                            <div class="bg-white rounded-3xl overflow-hidden border border-zinc-200/90 shadow-sm hover:shadow-md transition-all flex flex-col justify-between group">
                                <div class="h-48 bg-zinc-100 overflow-hidden relative">
                                    @if ($fac->coverImageUrl())
                                        <img src="{{ $fac->coverImageUrl() }}" alt="{{ $fac->name }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                                    @else
                                        <div class="w-full h-full flex items-center justify-center bg-zinc-100 text-zinc-300">
                                            <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                                        </div>
                                    @endif
                                </div>
                                <div class="p-6 flex-1">
                                    <h3 class="font-bold text-base text-zinc-900 mb-2 group-hover:text-zinc-600 transition-colors">{{ $fac->name }}</h3>
                                    <p class="text-xs text-zinc-500 leading-relaxed">{{ $fac->description }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

        </div>
    </div>
@endsection
