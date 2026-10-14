@extends('layouts.school-public')

@section('content')
    <!-- Header Banner -->
    <div class="bg-zinc-900 text-white py-14 sm:py-20 border-b border-zinc-800">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <span class="inline-block px-3 py-1 rounded-full text-xs font-bold bg-[#E11D2E] text-white mb-3">
                Prestasi & Penghargaan
            </span>
            <h1 class="text-3xl sm:text-4xl lg:text-5xl font-extrabold tracking-tight">
                Galeri Prestasi Siswa
            </h1>
            <p class="text-zinc-400 text-sm sm:text-base mt-2 max-w-2xl">
                Daftar pencapaian gemilang siswa {{ $school->name }} pada tingkat kota, provinsi, nasional, dan internasional.
            </p>
        </div>
    </div>

    <!-- Filter & Content -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 lg:py-14 space-y-8">
        
        <!-- Filter Bar -->
        <div class="bg-white rounded-2xl p-4 border border-zinc-200/80 shadow-2xs flex flex-col md:flex-row items-center justify-between gap-4">
            <div class="flex items-center gap-1.5 flex-wrap w-full md:w-auto">
                <a href="{{ route('school.public.achievements', ['school' => $school->slug]) }}" class="px-3 py-1.5 rounded-lg text-xs font-semibold transition-colors {{ empty($level) ? 'bg-[#E11D2E] text-white' : 'bg-zinc-100 text-zinc-600 hover:bg-zinc-200' }}">
                    Semua Tingkat
                </a>
                @foreach ($levels as $lvl)
                    <a href="{{ route('school.public.achievements', ['school' => $school->slug, 'level' => $lvl]) }}" class="px-3 py-1.5 rounded-lg text-xs font-semibold transition-colors {{ $level === $lvl ? 'bg-[#E11D2E] text-white' : 'bg-zinc-100 text-zinc-600 hover:bg-zinc-200' }}">
                        {{ $lvl }}
                    </a>
                @endforeach
            </div>

            <form method="GET" action="{{ route('school.public.achievements', ['school' => $school->slug]) }}" class="w-full md:w-72">
                @if ($level)
                    <input type="hidden" name="level" value="{{ $level }}">
                @endif
                <div class="relative">
                    <input type="text" name="search" value="{{ $search }}" placeholder="Cari prestasi, siswa, lomba..." class="w-full pl-9 pr-3 py-2 text-xs rounded-xl border border-zinc-200 focus:outline-none focus:border-[#E11D2E] focus:ring-1 focus:ring-[#E11D2E]">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-zinc-400">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    </div>
                </div>
            </form>
        </div>

        @if ($achievements->isEmpty())
            <div class="p-12 text-center bg-white rounded-3xl border border-zinc-200 text-zinc-400 space-y-2">
                <p class="font-bold text-zinc-600 text-base">Tidak ada data prestasi yang cocok</p>
                <p class="text-xs">Silakan pilih tingkat kompetisi lain atau bersihkan pencarian.</p>
            </div>
        @else
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach ($achievements as $ach)
                    <div class="bg-white rounded-2xl p-6 border border-zinc-200 shadow-2xs hover:shadow-md transition-shadow flex flex-col justify-between">
                        <div class="space-y-3">
                            <div class="flex items-center justify-between">
                                <span class="px-2.5 py-1 rounded-lg text-xs font-bold bg-amber-50 text-amber-800 border border-amber-200">
                                    {{ $ach->competition_level ?: 'Prestasi' }}
                                </span>
                                @if ($ach->published_at)
                                    <span class="text-xs text-zinc-400">{{ $ach->published_at->format('M Y') }}</span>
                                @endif
                            </div>
                            <h3 class="text-base font-bold text-zinc-900 leading-snug">
                                {{ $ach->title }}
                            </h3>
                            @if ($ach->awardee_name)
                                <p class="text-xs font-semibold text-[#E11D2E]">
                                    Penerima: {{ $ach->awardee_name }}
                                </p>
                            @endif
                            @if ($ach->competition_name)
                                <p class="text-[11px] text-zinc-500 font-medium">
                                    Ajang: {{ $ach->competition_name }}
                                </p>
                            @endif
                            <p class="text-xs text-zinc-600 leading-relaxed pt-2">
                                {{ $ach->excerpt ?: Str::limit(strip_tags($ach->content), 140) }}
                            </p>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="mt-8">
                {{ $achievements->links() }}
            </div>
        @endif

    </div>
@endsection
