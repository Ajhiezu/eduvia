@extends('layouts.school-public')

@section('content')
    <!-- Header Banner -->
    <div class="bg-zinc-900 text-white py-14 sm:py-20 border-b border-zinc-800">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <span class="inline-block px-3 py-1 rounded-full text-xs font-bold bg-[#E11D2E] text-white mb-3">
                Pengembangan Minat & Bakat
            </span>
            <h1 class="text-3xl sm:text-4xl lg:text-5xl font-extrabold tracking-tight">
                Kegiatan Ekstrakurikuler
            </h1>
            <p class="text-zinc-400 text-sm sm:text-base mt-2 max-w-2xl">
                Wadah eksplorasi kepemimpinan, olahraga, seni, sains, teknologi, dan bahasa di {{ $school->name }}.
            </p>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 lg:py-16">
        @if ($items->isEmpty())
            <div class="p-12 text-center bg-white rounded-3xl border border-zinc-200 text-zinc-400">
                Belum ada kegiatan ekstrakurikuler yang dipublikasikan.
            </div>
        @else
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 sm:gap-8">
                @foreach ($items as $extra)
                    <div class="bg-white rounded-3xl p-6 border border-zinc-200 shadow-2xs hover:shadow-md transition-shadow flex flex-col justify-between">
                        <div class="space-y-4">
                            <span class="inline-block px-3 py-1 rounded-full text-xs font-bold bg-rose-50 text-[#E11D2E] border border-rose-100">
                                {{ $extra->category }}
                            </span>
                            <h3 class="text-xl font-extrabold text-zinc-900 leading-tight">
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
                                    <span class="text-zinc-800">{{ $extra->schedule_info }}</span>
                                </div>
                            @endif
                            @if ($extra->location)
                                <div class="flex items-center gap-2">
                                    <span class="text-zinc-400">Lokasi:</span>
                                    <span class="text-zinc-800">{{ $extra->location }}</span>
                                </div>
                            @endif
                            @if ($extra->requirements)
                                <div class="pt-2 text-[11px] text-zinc-500 italic">
                                    * {{ $extra->requirements }}
                                </div>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
@endsection
