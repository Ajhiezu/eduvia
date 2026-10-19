@extends('layouts.school-public')

@section('content')

    <!-- Blueprint Crosshair SVG Helper Macro -->
    @php
        $crosshairSvg = '
            <div class="inline-grid grid-cols-3 gap-3 opacity-30 select-none pointer-events-none">
                <span class="w-2.5 h-2.5 flex items-center justify-center text-zinc-400 font-mono text-[10px] leading-none">+</span>
                <span class="w-2.5 h-2.5 flex items-center justify-center text-zinc-400 font-mono text-[10px] leading-none">+</span>
                <span class="w-2.5 h-2.5 flex items-center justify-center text-zinc-400 font-mono text-[10px] leading-none">+</span>
                <span class="w-2.5 h-2.5 flex items-center justify-center text-zinc-400 font-mono text-[10px] leading-none">+</span>
                <span class="w-2.5 h-2.5 flex items-center justify-center text-zinc-400 font-mono text-[10px] leading-none">+</span>
                <span class="w-2.5 h-2.5 flex items-center justify-center text-zinc-400 font-mono text-[10px] leading-none">+</span>
                <span class="w-2.5 h-2.5 flex items-center justify-center text-zinc-400 font-mono text-[10px] leading-none">+</span>
                <span class="w-2.5 h-2.5 flex items-center justify-center text-zinc-400 font-mono text-[10px] leading-none">+</span>
                <span class="w-2.5 h-2.5 flex items-center justify-center text-zinc-400 font-mono text-[10px] leading-none">+</span>
            </div>
        ';
    @endphp

    <!-- Header Banner -->
    <div class="bg-white py-12 sm:py-16 border-b border-zinc-200/80">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Breadcrumb Navigation -->
            <nav class="flex items-center gap-2 text-xs text-zinc-400 mb-6 font-sans">
                <a href="{{ $school->slug === 'nusantara-utama' ? route('home') : route('school.public.home', ['school' => $school->slug]) }}" class="hover:text-zinc-900 transition-colors">Beranda</a>
                <span>&rsaquo;</span>
                <span class="text-zinc-900 font-semibold">Profil Sekolah</span>
            </nav>

            <span class="text-[11px] font-bold tracking-[0.2em] uppercase text-zinc-400 block mb-3 font-sans">
                ABOUT & INSTITUTIONAL PROFILE
            </span>
            <h1 class="text-4xl sm:text-5xl lg:text-6xl font-serif font-normal text-zinc-900 tracking-tight leading-[1.08]">
                Profil & Identitas {{ $school->name }}
            </h1>
            <p class="text-zinc-600 text-sm sm:text-base mt-4 max-w-2xl font-sans leading-relaxed">
                Mengenal lebih dekat visi keunggulan akademik, kepemimpinan, sejarah pendirian, serta sarana prasarana sekolah.
            </p>
        </div>
    </div>

    <!-- Main Content Container -->
    <div class="bg-[#F8F9FA] py-12 lg:py-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-12 sm:space-y-16">
            
            <!-- Section: Identitas & Akreditasi -->
            <div class="bg-white rounded-3xl p-7 sm:p-9 border border-zinc-200/90 shadow-sm">
                <div class="flex items-center justify-between mb-6 pb-4 border-b border-zinc-100">
                    <div>
                        <span class="text-[10px] font-bold tracking-widest uppercase text-zinc-400 block font-sans">LEGAL IDENTITY</span>
                        <h2 class="text-xl sm:text-2xl font-serif font-normal text-zinc-900 mt-1">Identitas Resmi Lembaga</h2>
                    </div>
                    <div class="hidden sm:block">
                        {!! $crosshairSvg !!}
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-5 text-xs sm:text-sm font-sans">
                    <div class="p-5 rounded-2xl bg-zinc-50 border border-zinc-200/70">
                        <span class="text-zinc-400 text-xs block mb-1">Nama Lembaga</span>
                        <strong class="text-zinc-900 font-bold text-sm sm:text-base">{{ $school->name }}</strong>
                    </div>
                    <div class="p-5 rounded-2xl bg-zinc-50 border border-zinc-200/70">
                        <span class="text-zinc-400 text-xs block mb-1">NPSN</span>
                        <strong class="text-zinc-900 font-bold text-sm sm:text-base">{{ $school->npsn ?: 'Belum diisi' }}</strong>
                    </div>
                    <div class="p-5 rounded-2xl bg-zinc-50 border border-zinc-200/70 flex flex-col justify-between">
                        <div>
                            <span class="text-zinc-400 text-xs block mb-1.5 font-sans">Status Akreditasi</span>
                            <div class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md bg-zinc-900 text-white shadow-2xs font-sans">
                                <svg class="w-3.5 h-3.5 text-amber-400 fill-current shrink-0" viewBox="0 0 20 20" aria-hidden="true">
                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                </svg>
                                <span class="font-semibold text-xs tracking-tight">{{ $setting->accreditation_grade ?: 'Terakreditasi' }}</span>
                            </div>
                        </div>
                        @if ($setting->accreditation_no)
                            <span class="text-[11px] text-zinc-500 block mt-2 font-mono">SK: {{ $setting->accreditation_no }}</span>
                        @endif
                    </div>
                    <div class="p-5 rounded-2xl bg-zinc-50 border border-zinc-200/70">
                        <span class="text-zinc-400 text-xs block mb-1">Jenjang Pendidikan</span>
                        <strong class="text-zinc-900 font-bold text-sm sm:text-base uppercase">{{ is_array($school->educational_levels) ? implode(', ', $school->educational_levels) : 'Formal' }}</strong>
                    </div>
                </div>
            </div>

            <!-- Section: Sambutan Kepala Sekolah -->
            @if ($setting->welcome_message)
                <div class="bg-white rounded-3xl p-7 sm:p-10 border border-zinc-200/90 shadow-sm">
                    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-12 items-center">
                        <div class="lg:col-span-4 text-center">
                            <div class="w-48 h-60 rounded-3xl bg-zinc-100 overflow-hidden mx-auto shadow-md border border-zinc-200">
                                @if ($setting->principalPhotoUrl())
                                    <img src="{{ $setting->principalPhotoUrl() }}" alt="{{ $setting->principal_name }}" class="w-full h-full object-cover">
                                @else
                                    <div class="w-full h-full flex items-center justify-center text-zinc-300">
                                        <svg class="w-16 h-16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                    </div>
                                @endif
                            </div>
                            <h3 class="font-bold text-zinc-900 mt-4 text-base font-sans">{{ $setting->principal_name ?: 'Kepala Sekolah' }}</h3>
                            <p class="text-xs text-zinc-500 font-sans">{{ $setting->principal_title ?: 'Kepala Sekolah ' . $school->name }}</p>
                        </div>
                        <div class="lg:col-span-8 space-y-4">
                            <span class="text-[10px] font-bold tracking-widest uppercase text-zinc-400 block font-sans">MESSAGE FROM PRINCIPAL</span>
                            <h2 class="text-2xl sm:text-3xl font-serif font-normal text-zinc-900 tracking-tight">Sambutan Kepala Sekolah</h2>
                            <div class="text-zinc-600 text-sm sm:text-base leading-relaxed space-y-3 font-sans">
                                {!! nl2br(e($setting->welcome_message)) !!}
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Section: Visi dan Misi -->
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
                <!-- Visi: Dark Navy Card -->
                <div class="lg:col-span-5 bg-[#161F2E] text-white rounded-3xl p-7 sm:p-9 shadow-xl relative overflow-hidden flex flex-col justify-between" style="background-color: #161F2E; color: #ffffff;">
                    <div class="absolute top-6 right-6 opacity-20">
                        {!! $crosshairSvg !!}
                    </div>

                    <div>
                        <span class="text-[10px] font-bold tracking-widest uppercase text-zinc-400 block mb-2 font-sans">OUR VISION</span>
                        <h3 class="text-2xl sm:text-3xl font-serif font-normal text-white mb-4">Visi Sekolah</h3>
                        <p class="text-sm sm:text-base text-zinc-200 leading-relaxed font-sans italic">
                            "{{ $setting->vision ?: 'Menjadi lembaga pendidikan terdepan yang mewujudkan generasi beriman, cerdas, terampil, dan berdaya saing global.' }}"
                        </p>
                    </div>

                    <div class="pt-6 mt-6 border-t border-zinc-700/60 text-xs text-zinc-400 font-sans">
                        Fokus: Karakter, Sains, dan Berpikir Kritis
                    </div>
                </div>

                <!-- Misi: Clean White Card -->
                <div class="lg:col-span-7 bg-white rounded-3xl p-7 sm:p-9 border border-zinc-200/90 shadow-sm flex flex-col justify-between">
                    <div>
                        <span class="text-[10px] font-bold tracking-widest uppercase text-zinc-400 block mb-2 font-sans">OUR MISSION</span>
                        <h3 class="text-2xl sm:text-3xl font-serif font-normal text-zinc-900 mb-4">Misi Sekolah</h3>
                        @if ($setting->mission && is_array($setting->mission) && count($setting->mission) > 0)
                            <ul class="space-y-3 text-sm text-zinc-600 font-sans">
                                @foreach ($setting->mission as $m)
                                    <li class="flex items-start gap-3">
                                        <span class="w-6 h-6 rounded-full bg-zinc-900 text-white text-xs font-bold flex items-center justify-center shrink-0 mt-0.5">
                                            {{ $loop->iteration }}
                                        </span>
                                        <span class="leading-relaxed">{{ $m }}</span>
                                    </li>
                                @endforeach
                            </ul>
                        @else
                            <p class="text-sm text-zinc-500 font-sans">Misi sekolah sedang dalam tahap penataan kurikulum.</p>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Section: Sejarah Singkat -->
            @if ($setting->history)
                <div class="bg-white rounded-3xl p-7 sm:p-10 border border-zinc-200/90 shadow-sm">
                    <span class="text-[10px] font-bold tracking-widest uppercase text-zinc-400 block mb-1 font-sans">HISTORY & HERITAGE</span>
                    <h2 class="text-2xl sm:text-3xl font-serif font-normal text-zinc-900 mb-4">Sejarah & Kilas Balik Pendirian</h2>
                    <div class="text-zinc-600 text-sm sm:text-base leading-relaxed space-y-3 font-sans">
                        {!! nl2br(e($setting->history)) !!}
                    </div>
                </div>
            @endif

            <!-- Section: Peta Lokasi / Peta Google -->
            @if ($setting->maps_embed_url)
                <div class="bg-white rounded-3xl p-7 sm:p-9 border border-zinc-200/90 shadow-sm">
                    <span class="text-[10px] font-bold tracking-widest uppercase text-zinc-400 block mb-1 font-sans">CAMPUS LOCATION</span>
                    <h2 class="text-2xl font-serif font-normal text-zinc-900 mb-4">Lokasi & Denah Kampus</h2>
                    <div class="rounded-2xl overflow-hidden border border-zinc-200 h-80 sm:h-96 w-full">
                        <iframe src="{{ $setting->maps_embed_url }}" width="100%" height="100%" style="border:0;" allowfullscreen="" loading="lazy"></iframe>
                    </div>
                </div>
            @endif

        </div>
    </div>
@endsection
