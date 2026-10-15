@extends('layouts.school-public')

@section('content')

    <!-- Header Banner -->
    <div class="bg-white py-12 sm:py-16 border-b border-zinc-200/80">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <nav class="flex items-center gap-2 text-xs text-zinc-400 mb-6 font-sans">
                <a href="{{ $school->slug === 'nusantara-utama' ? route('home') : route('school.public.home', ['school' => $school->slug]) }}" class="hover:text-zinc-900 transition-colors">Beranda</a>
                <span>&rsaquo;</span>
                <span class="text-zinc-900 font-semibold">Ekstrakurikuler</span>
            </nav>

            <span class="text-[11px] font-bold tracking-[0.2em] uppercase text-zinc-400 block mb-3 font-sans">
                CLUBS & EXTRACURRICULARS
            </span>
            <h1 class="text-4xl sm:text-5xl lg:text-6xl font-serif font-normal text-zinc-900 tracking-tight leading-[1.08]">
                Kegiatan Ekstrakurikuler
            </h1>
            <p class="text-zinc-600 text-sm sm:text-base mt-4 max-w-2xl font-sans leading-relaxed">
                Wadah eksplorasi kepemimpinan, olahraga, seni pertunjukan, sains, teknologi koding, dan bahasa di {{ $school->name }}.
            </p>
        </div>
    </div>

    <div class="bg-[#F8F9FA] py-12 lg:py-16 border-b border-zinc-200/80 font-sans">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            @if ($items->isEmpty())
                <div class="p-16 text-center bg-white rounded-3xl border border-zinc-200/90 shadow-sm text-zinc-400">
                    Belum ada kegiatan ekstrakurikuler yang dipublikasikan.
                </div>
            @else
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 sm:gap-8">
                    @foreach ($items as $extra)
                        <div class="bg-white rounded-3xl p-7 border border-zinc-200/90 shadow-sm hover:shadow-md transition-all flex flex-col justify-between group">
                            <div class="space-y-4">
                                <span class="inline-block px-3 py-1 rounded-full text-[10px] font-bold bg-[#161F2E] text-white">
                                    {{ $extra->category }}
                                </span>
                                <h3 class="text-xl font-bold text-zinc-900 leading-snug group-hover:text-zinc-600 transition-colors">
                                    {{ $extra->name }}
                                </h3>
                                <p class="text-xs sm:text-sm text-zinc-600 leading-relaxed">
                                    {{ $extra->description }}
                                </p>
                            </div>

                            <div class="mt-6 pt-4 border-t border-zinc-100 space-y-2 text-xs text-zinc-600">
                                @if ($extra->coach_name)
                                    <div class="flex items-center gap-2">
                                        <span class="text-zinc-400">Pembina:</span>
                                        <strong class="text-zinc-800">{{ $extra->coach_name }}</strong>
                                    </div>
                                @endif
                                @if ($extra->schedule_info)
                                    <div class="flex items-center gap-2">
                                        <span class="text-zinc-400">Jadwal:</span>
                                        <span class="text-zinc-800 font-medium">{{ $extra->schedule_info }}</span>
                                    </div>
                                @endif
                                @if ($extra->location)
                                    <div class="flex items-center gap-2">
                                        <span class="text-zinc-400">Lokasi:</span>
                                        <span class="text-zinc-800">{{ $extra->location }}</span>
                                    </div>
                                @endif
                                @if ($extra->requirements)
                                    <div class="pt-2 text-[11px] text-zinc-400 italic">
                                        * {{ $extra->requirements }}
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
@endsection
