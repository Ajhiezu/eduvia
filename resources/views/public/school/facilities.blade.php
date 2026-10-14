@extends('layouts.school-public')

@section('content')
    <!-- Header Banner -->
    <div class="bg-zinc-900 text-white py-14 sm:py-20 border-b border-zinc-800">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <span class="inline-block px-3 py-1 rounded-full text-xs font-bold bg-emerald-600 text-white mb-3">
                Sarana & Program
            </span>
            <h1 class="text-3xl sm:text-4xl lg:text-5xl font-extrabold tracking-tight">
                Fasilitas & Program Unggulan
            </h1>
            <p class="text-zinc-400 text-sm sm:text-base mt-2 max-w-2xl">
                Sarana pembelajaran modern dan kurikulum unggulan untuk mendukung capaian prestasi siswa {{ $school->name }}.
            </p>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 lg:py-16 space-y-16">
        
        <!-- Section 1: Program Unggulan -->
        @if ($programs->isNotEmpty())
            <div>
                <div class="flex items-center gap-2 mb-8">
                    <span class="w-3 h-3 rounded-full bg-[#E11D2E]"></span>
                    <h2 class="text-2xl font-extrabold text-zinc-900">Program Unggulan Sekolah</h2>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    @foreach ($programs as $prog)
                        <div class="bg-white rounded-3xl p-6 border border-zinc-200/80 shadow-2xs hover:shadow-md transition-shadow">
                            <div class="w-12 h-12 rounded-2xl bg-rose-50 text-[#E11D2E] flex items-center justify-center font-bold mb-4">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/></svg>
                            </div>
                            <h3 class="text-lg font-extrabold text-zinc-900 mb-2">{{ $prog->name }}</h3>
                            <p class="text-xs sm:text-sm text-zinc-600 leading-relaxed">{{ $prog->description }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        <!-- Section 2: Sarana & Fasilitas -->
        @if ($facilities->isNotEmpty())
            <div>
                <div class="flex items-center gap-2 mb-8">
                    <span class="w-3 h-3 rounded-full bg-emerald-500"></span>
                    <h2 class="text-2xl font-extrabold text-zinc-900">Sarana & Prasarana Kampus</h2>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                    @foreach ($facilities as $fac)
                        <div class="bg-white rounded-2xl overflow-hidden border border-zinc-200/80 shadow-2xs hover:shadow-md transition-all flex flex-col justify-between">
                            <div class="h-44 bg-zinc-100 overflow-hidden relative">
                                @if ($fac->coverImageUrl())
                                    <img src="{{ $fac->coverImageUrl() }}" alt="{{ $fac->name }}" class="w-full h-full object-cover">
                                @else
                                    <div class="w-full h-full flex items-center justify-center bg-zinc-100 text-zinc-300">
                                        <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                                    </div>
                                @endif
                            </div>
                            <div class="p-5 flex-1">
                                <h3 class="font-bold text-base text-zinc-900 mb-2">{{ $fac->name }}</h3>
                                <p class="text-xs text-zinc-600 leading-relaxed">{{ $fac->description }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

    </div>
@endsection
