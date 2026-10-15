@extends('layouts.school-public')

@section('content')

    <!-- Header Banner -->
    <div class="bg-white py-12 sm:py-16 border-b border-zinc-200/80">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <nav class="flex items-center gap-2 text-xs text-zinc-400 mb-6 font-sans">
                <a href="{{ $school->slug === 'nusantara-utama' ? route('home') : route('school.public.home', ['school' => $school->slug]) }}" class="hover:text-zinc-900 transition-colors">Beranda</a>
                <span>&rsaquo;</span>
                <span class="text-zinc-900 font-semibold">Prestasi Siswa</span>
            </nav>

            <span class="text-[11px] font-bold tracking-[0.2em] uppercase text-zinc-400 block mb-3 font-sans">
                HONORS & ACCOLADES
            </span>
            <h1 class="text-4xl sm:text-5xl lg:text-6xl font-serif font-normal text-zinc-900 tracking-tight leading-[1.08]">
                Galeri Prestasi Siswa
            </h1>
            <p class="text-zinc-600 text-sm sm:text-base mt-4 max-w-2xl font-sans leading-relaxed">
                Daftar pencapaian gemilang siswa {{ $school->name }} pada kejuaraan sains, seni, teknologi, dan olahraga tingkat kota, provinsi, nasional, hingga internasional.
            </p>
        </div>
    </div>

    <!-- Filter & Content -->
    <div class="bg-[#F8F9FA] py-10 lg:py-14 border-b border-zinc-200/80">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8 font-sans">
            
            <!-- Filter Bar -->
            <div class="bg-white rounded-3xl p-4 sm:p-5 border border-zinc-200/90 shadow-sm flex flex-col md:flex-row items-center justify-between gap-4">
                <div class="flex items-center gap-2 flex-wrap w-full md:w-auto">
                    <a href="{{ route('school.public.achievements', ['school' => $school->slug]) }}" class="px-4 py-2 rounded-full text-xs font-semibold transition-all {{ empty($level) ? 'bg-[#161F2E] text-white shadow-xs' : 'bg-zinc-100 text-zinc-600 hover:bg-zinc-200' }}">
                        Semua Tingkat
                    </a>
                    @foreach ($levels as $lvl)
                        <a href="{{ route('school.public.achievements', ['school' => $school->slug, 'level' => $lvl]) }}" class="px-4 py-2 rounded-full text-xs font-semibold transition-all {{ $level === $lvl ? 'bg-[#161F2E] text-white shadow-xs' : 'bg-zinc-100 text-zinc-600 hover:bg-zinc-200' }}">
                            {{ $lvl }}
                        </a>
                    @endforeach
                </div>

                <form method="GET" action="{{ route('school.public.achievements', ['school' => $school->slug]) }}" class="w-full md:w-80">
                    @if ($level)
                        <input type="hidden" name="level" value="{{ $level }}">
                    @endif
                    <div class="relative">
                        <input 
                            type="text" 
                            name="search" 
                            value="{{ $search }}" 
                            placeholder="Cari prestasi, nama siswa, lomba..." 
                            class="w-full pl-10 pr-4 py-2.5 text-xs rounded-full border border-zinc-200 bg-zinc-50/50 focus:bg-white focus:outline-none focus:border-zinc-900 focus:ring-2 focus:ring-zinc-900/10 transition-all text-zinc-900 placeholder:text-zinc-400"
                        >
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-zinc-400">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        </div>
                    </div>
                </form>
            </div>

            @if ($achievements->isEmpty())
                <div class="p-16 text-center bg-white rounded-3xl border border-zinc-200/90 shadow-sm text-zinc-400 space-y-2">
                    <p class="font-bold text-zinc-700 text-base">Tidak ada data prestasi yang cocok</p>
                    <p class="text-xs text-zinc-500">Silakan pilih tingkat kompetisi lain atau bersihkan kata kunci pencarian.</p>
                </div>
            @else
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 sm:gap-8">
                    @foreach ($achievements as $ach)
                        <div class="bg-white rounded-3xl p-6 sm:p-7 border border-zinc-200/90 shadow-sm hover:shadow-md transition-all flex flex-col justify-between group">
                            <div class="space-y-3.5">
                                <div class="flex items-center justify-between">
                                    <span class="px-3 py-1 rounded-full text-[10px] font-bold bg-amber-50 text-amber-800 border border-amber-200">
                                        {{ $ach->competition_level ?: 'Prestasi' }}
                                    </span>
                                    @if ($ach->published_at)
                                        <span class="text-xs font-mono text-zinc-400">{{ $ach->published_at->format('M Y') }}</span>
                                    @endif
                                </div>
                                <h3 class="text-lg font-bold text-zinc-900 leading-snug group-hover:text-zinc-600 transition-colors">
                                    {{ $ach->title }}
                                </h3>
                                @if ($ach->awardee_name)
                                    <p class="text-xs font-semibold text-zinc-800 flex items-center gap-1.5">
                                        <span class="w-1.5 h-1.5 rounded-full bg-zinc-900"></span>
                                        <span>Penerima: {{ $ach->awardee_name }}</span>
                                    </p>
                                @endif
                                @if ($ach->competition_name)
                                    <p class="text-[11px] text-zinc-500 font-medium">
                                        Ajang: {{ $ach->competition_name }}
                                    </p>
                                @endif
                                <p class="text-xs text-zinc-600 leading-relaxed pt-1">
                                    {{ $ach->excerpt ?: Str::limit(strip_tags($ach->content), 140) }}
                                </p>
                            </div>
                        </div>
                    @endforeach
                </div>

                @if ($achievements->hasPages())
                    <div class="mt-8">
                        {{ $achievements->links() }}
                    </div>
                @endif
            @endif

        </div>
    </div>
@endsection
