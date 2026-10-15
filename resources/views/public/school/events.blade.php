@extends('layouts.school-public')

@section('content')

    <!-- Header Banner -->
    <div class="bg-white py-12 sm:py-16 border-b border-zinc-200/80">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <nav class="flex items-center gap-2 text-xs text-zinc-400 mb-6 font-sans">
                <a href="{{ $school->slug === 'nusantara-utama' ? route('home') : route('school.public.home', ['school' => $school->slug]) }}" class="hover:text-zinc-900 transition-colors">Beranda</a>
                <span>&rsaquo;</span>
                <span class="text-zinc-900 font-semibold">Agenda & Kegiatan</span>
            </nav>

            <span class="text-[11px] font-bold tracking-[0.2em] uppercase text-zinc-400 block mb-3 font-sans">
                CALENDAR & EVENTS
            </span>
            <h1 class="text-4xl sm:text-5xl lg:text-6xl font-serif font-normal text-zinc-900 tracking-tight leading-[1.08]">
                Agenda Acara & Kegiatan
            </h1>
            <p class="text-zinc-600 text-sm sm:text-base mt-4 max-w-2xl font-sans leading-relaxed">
                Jadwal kegiatan akademik, simposium sains, pameran seni siswa, dan dokumentasi acara yang telah diselenggarakan di {{ $school->name }}.
            </p>
        </div>
    </div>

    <div class="bg-[#F8F9FA] py-12 lg:py-16 border-b border-zinc-200/80">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-16">
            
            <!-- Section 1: Upcoming Events -->
            <div>
                <div class="mb-8">
                    <span class="text-[10px] font-bold tracking-widest uppercase text-zinc-400 block mb-1 font-sans">UPCOMING</span>
                    <h2 class="text-2xl sm:text-3xl font-serif font-normal text-zinc-900">Agenda Acara Mendatang</h2>
                </div>

                @if ($upcomingEvents->isEmpty())
                    <div class="p-12 text-center bg-white rounded-3xl border border-zinc-200/90 shadow-sm text-zinc-400 text-sm font-sans">
                        Saat ini belum ada agenda acara mendatang yang dijadwalkan.
                    </div>
                @else
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 sm:gap-8 font-sans">
                        @foreach ($upcomingEvents as $evt)
                            <div class="bg-white rounded-3xl p-6 sm:p-7 border border-zinc-200/90 shadow-sm hover:shadow-md transition-all flex flex-col justify-between group">
                                <div class="space-y-4">
                                    <div class="flex items-center justify-between">
                                        <span class="px-3 py-1 rounded-full text-[10px] font-bold bg-[#161F2E] text-white">
                                            {{ $evt->category ?: 'Kegiatan' }}
                                        </span>
                                        <span class="text-xs font-mono text-zinc-400">
                                            {{ $evt->event_date?->format('H:i') }} WIB
                                        </span>
                                    </div>
                                    <h3 class="text-lg font-bold text-zinc-900 leading-snug group-hover:text-zinc-600 transition-colors">
                                        {{ $evt->title }}
                                    </h3>
                                    <p class="text-xs text-zinc-500 leading-relaxed line-clamp-3">
                                        {{ $evt->excerpt ?: Str::limit(strip_tags($evt->content), 120) }}
                                    </p>
                                </div>
                                <div class="mt-6 pt-4 border-t border-zinc-100 space-y-2 text-xs text-zinc-600">
                                    <div class="flex items-center gap-2">
                                        <svg class="w-4 h-4 text-zinc-900 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                        <strong class="text-zinc-800">{{ $evt->event_date?->format('d F Y') }}</strong>
                                    </div>
                                    @if ($evt->event_location)
                                        <div class="flex items-center gap-2 truncate text-zinc-500">
                                            <svg class="w-4 h-4 text-zinc-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                            <span class="truncate">{{ $evt->event_location }}</span>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            <!-- Section 2: Past Events Documentation -->
            <div>
                <div class="mb-8">
                    <span class="text-[10px] font-bold tracking-widest uppercase text-zinc-400 block mb-1 font-sans">PAST ARCHIVE</span>
                    <h2 class="text-2xl sm:text-3xl font-serif font-normal text-zinc-900">Dokumentasi Acara Selesai</h2>
                </div>

                @if ($pastEvents->isEmpty())
                    <div class="p-12 text-center bg-white rounded-3xl border border-zinc-200/90 shadow-sm text-zinc-400 text-sm font-sans">
                        Belum ada arsip acara selesai.
                    </div>
                @else
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 font-sans">
                        @foreach ($pastEvents as $evt)
                            <div class="bg-white rounded-3xl p-6 border border-zinc-200/80 shadow-2xs flex flex-col justify-between">
                                <div class="space-y-2">
                                    <span class="text-[11px] text-zinc-400 block font-mono">Telah berlangsung: {{ $evt->event_date?->format('d M Y') }}</span>
                                    <h3 class="font-bold text-sm text-zinc-800 leading-snug">
                                        {{ $evt->title }}
                                    </h3>
                                    <p class="text-xs text-zinc-500 line-clamp-2">
                                        {{ $evt->excerpt ?: Str::limit(strip_tags($evt->content), 90) }}
                                    </p>
                                </div>
                                @if ($evt->event_location)
                                    <div class="mt-4 pt-3 border-t border-zinc-100 text-[11px] text-zinc-500 truncate">
                                        Lokasi: {{ $evt->event_location }}
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

        </div>
    </div>
@endsection
