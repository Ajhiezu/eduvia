@extends('layouts.school-public')

@section('content')
    <!-- Header Banner -->
    <div class="bg-zinc-900 text-white py-14 sm:py-20 border-b border-zinc-800">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <span class="inline-block px-3 py-1 rounded-full text-xs font-bold bg-emerald-600 text-white mb-3">
                Kalender Akademik & Kesiswaan
            </span>
            <h1 class="text-3xl sm:text-4xl lg:text-5xl font-extrabold tracking-tight">
                Agenda Acara & Kegiatan
            </h1>
            <p class="text-zinc-400 text-sm sm:text-base mt-2 max-w-2xl">
                Jadwal kegiatan mendatang dan dokumentasi acara yang telah diselenggarakan di {{ $school->name }}.
            </p>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 lg:py-16 space-y-16">
        
        <!-- Section 1: Upcoming Events -->
        <div>
            <div class="flex items-center gap-2 mb-6">
                <span class="w-3 h-3 rounded-full bg-emerald-500"></span>
                <h2 class="text-2xl font-extrabold text-zinc-900">Agenda Acara Mendatang</h2>
            </div>

            @if ($upcomingEvents->isEmpty())
                <div class="p-8 text-center bg-white rounded-3xl border border-zinc-200 text-zinc-400 text-sm">
                    Saat ini belum ada agenda acara mendatang yang dijadwalkan.
                </div>
            @else
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach ($upcomingEvents as $evt)
                        <div class="bg-white rounded-2xl p-6 border border-zinc-200 shadow-2xs hover:shadow-md transition-shadow flex flex-col justify-between">
                            <div class="space-y-3">
                                <div class="flex items-center justify-between">
                                    <span class="px-2.5 py-1 rounded-lg text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        {{ $evt->category ?: 'Kegiatan' }}
                                    </span>
                                    <span class="text-xs font-semibold text-zinc-400">
                                        {{ $evt->event_date?->format('H:i') }} WIB
                                    </span>
                                </div>
                                <h3 class="text-base font-bold text-zinc-900 leading-snug">
                                    {{ $evt->title }}
                                </h3>
                                <p class="text-xs text-zinc-500 leading-relaxed line-clamp-3">
                                    {{ $evt->excerpt ?: Str::limit(strip_tags($evt->content), 120) }}
                                </p>
                            </div>
                            <div class="mt-6 pt-4 border-t border-zinc-100 space-y-1.5 text-xs text-zinc-600">
                                <div class="flex items-center gap-2">
                                    <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                    <strong>{{ $evt->event_date?->format('d F Y') }}</strong>
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
            <div class="flex items-center gap-2 mb-6">
                <span class="w-3 h-3 rounded-full bg-zinc-400"></span>
                <h2 class="text-2xl font-extrabold text-zinc-900">Dokumentasi Acara Selesai</h2>
            </div>

            @if ($pastEvents->isEmpty())
                <div class="p-8 text-center bg-white rounded-3xl border border-zinc-200 text-zinc-400 text-sm">
                    Belum ada arsip acara selesai.
                </div>
            @else
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach ($pastEvents as $evt)
                        <div class="bg-zinc-50/80 rounded-2xl p-5 border border-zinc-200 flex flex-col justify-between">
                            <div class="space-y-2">
                                <span class="text-[11px] text-zinc-400 block">Telah berlangsung: {{ $evt->event_date?->format('d M Y') }}</span>
                                <h3 class="font-bold text-sm text-zinc-800 leading-snug">
                                    {{ $evt->title }}
                                </h3>
                                <p class="text-xs text-zinc-500 line-clamp-2">
                                    {{ $evt->excerpt ?: Str::limit(strip_tags($evt->content), 90) }}
                                </p>
                            </div>
                            @if ($evt->event_location)
                                <div class="mt-4 pt-3 border-t border-zinc-200/60 text-[11px] text-zinc-500 truncate">
                                    Lokasi: {{ $evt->event_location }}
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
                <div class="mt-6">
                    {{ $pastEvents->links() }}
                </div>
            @endif
        </div>

    </div>
@endsection
