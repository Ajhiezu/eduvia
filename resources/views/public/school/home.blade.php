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

    <!-- ====================================================================== -->
    <!-- 1. HERO SECTION (EdmunHigh Homepage V1 Composition)                    -->
    <!-- ====================================================================== -->
    <section class="relative bg-white pt-10 sm:pt-16 pb-14 sm:pb-20 border-b border-zinc-200/80 overflow-hidden">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            
            <!-- Top Split Header (Headline & Description CTA) -->
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-12 items-end mb-10 sm:mb-14">
                
                <!-- Left Column: Big Editorial Headline -->
                <div class="lg:col-span-7">
                    <div class="inline-flex flex-wrap items-center gap-2 mb-4">
                        <span class="text-[11px] font-bold tracking-widest uppercase text-zinc-400">
                            {{ $school->short_name ?: $school->name }} &bull; Situs Resmi
                        </span>
                        @if (!empty($setting->hero_headline))
                            <span class="hidden sm:inline text-zinc-300">&bull;</span>
                            <span class="text-xs font-semibold text-zinc-600">{{ $setting->hero_headline }}</span>
                        @endif
                        @if ($setting->accreditation_grade)
                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                Akreditasi {{ $setting->accreditation_grade }}
                            </span>
                        @endif
                    </div>

                    <h1 class="text-4xl sm:text-6xl lg:text-[76px] font-serif font-normal tracking-tight text-zinc-900 leading-[1.06]">
                        Discover Your <br>
                        <span class="font-sans font-extrabold text-zinc-900">Education</span> Potential
                    </h1>
                </div>

                <!-- Right Column: Description & Explore Pill CTA Button -->
                <div class="lg:col-span-5 flex flex-col justify-between space-y-6">
                    <p class="text-sm sm:text-base text-zinc-600 leading-relaxed">
                        {{ $setting->hero_subheadline ?: 'Our school provides dynamic environment that empowers students to new heights. Membina generasi unggul dengan kurikulum terpadu dan teknologi modern.' }}
                    </p>

                    <div class="flex items-center gap-3">
                        <a 
                            href="{{ $setting->hero_cta_url ?: route('school.public.profile', ['school' => $school->slug]) }}" 
                            class="inline-flex items-center justify-center px-7 py-3.5 rounded-full text-sm font-semibold bg-[#161F2E] hover:bg-black text-white shadow-sm transition-all duration-200 hover:-translate-y-0.5"
                            style="background-color: #161F2E; color: #ffffff;"
                        >
                            {{ $setting->hero_cta_text ?: 'Explore More' }}
                        </a>

                        <a 
                            href="{{ route('ppdb.index') }}" 
                            class="w-11 h-11 rounded-full border border-zinc-900 flex items-center justify-center text-zinc-900 hover:bg-zinc-900 hover:text-white transition-all duration-200 group"
                            title="Pendaftaran PPDB Online"
                        >
                            <svg class="w-4 h-4 transform group-hover:translate-x-0.5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                            </svg>
                        </a>

                        @if ($activeAdmissionPeriod)
                            <span class="hidden sm:inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                PPDB Dibuka
                            </span>
                        @endif
                    </div>
                </div>

            </div>

            <!-- Full-Width Panoramic Hero Photo Banner -->
            <div class="relative rounded-3xl overflow-hidden shadow-2xl border border-zinc-200/90 aspect-[16/8] sm:aspect-[16/7] lg:aspect-[16/6.5] bg-zinc-100 group">
                <img 
                    src="{{ asset('images/edmunhigh/hero-students.jpg') }}" 
                    alt="{{ $school->name }} Students" 
                    class="w-full h-full object-cover object-center group-hover:scale-[1.02] transition-transform duration-700 ease-out"
                >
                <div class="absolute inset-0 bg-gradient-to-t from-black/50 via-transparent to-transparent"></div>

                <!-- Floating Bottom Caption Badge -->
                <div class="absolute bottom-4 left-4 sm:bottom-6 sm:left-6 flex flex-wrap items-center gap-2">
                    <div class="backdrop-blur-md bg-white/90 text-zinc-900 px-4 py-2 rounded-2xl text-xs font-bold shadow-md flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-[#E11D2E]"></span>
                        <span>{{ $school->name }} &bull; Jenjang {{ strtoupper(implode(' / ', $school->educational_levels ?? ['SMA'])) }}</span>
                    </div>

                    @if ($activeAdmissionPeriod)
                        <a href="{{ route('ppdb.register', ['period' => $activeAdmissionPeriod->code]) }}" class="backdrop-blur-md bg-[#E11D2E] text-white hover:bg-rose-700 px-4 py-2 rounded-2xl text-xs font-bold shadow-md transition-colors flex items-center gap-1.5">
                            <span>Daftar PPDB {{ date('Y') }}</span>
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                        </a>
                    @endif
                </div>
            </div>

        </div>
    </section>

    <!-- ====================================================================== -->
    <!-- 2. ABOUT & MISSION SECTION (Asymmetric Composition)                    -->
    <!-- ====================================================================== -->
    <section class="py-16 sm:py-24 bg-white border-b border-zinc-200/80 relative">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-center mb-16 sm:mb-20">
                
                <!-- Left: Dark Navy Statement Block -->
                <div class="lg:col-span-5 bg-[#161F2E] text-white rounded-3xl p-8 sm:p-12 shadow-xl flex flex-col justify-between min-h-[380px] relative overflow-hidden" style="background-color: #161F2E; color: #ffffff;">
                    <!-- Subtle Decorative Crosshair inside navy card -->
                    <div class="absolute top-6 right-6 opacity-20">
                        {!! $crosshairSvg !!}
                    </div>

                    <div>
                        <span class="text-[11px] font-bold tracking-widest uppercase text-zinc-400 block mb-6">
                            ABOUT
                        </span>
                        <h2 class="text-3xl sm:text-4xl lg:text-[42px] font-serif font-normal leading-[1.15] text-white">
                            Unleash students <br>
                            possibilities with us
                        </h2>
                        @if (!empty($setting->principal_name))
                            <p class="text-xs text-zinc-400 mt-4">
                                Dipimpin oleh: <strong class="text-white">{{ $setting->principal_name }}</strong>
                            </p>
                        @endif
                    </div>

                    <div class="pt-8 border-t border-zinc-700/60 flex items-center justify-between">
                        <span class="text-xs text-zinc-400">Komitmen Pendidikan Unggul</span>
                        <a href="{{ route('school.public.profile', ['school' => $school->slug]) }}" class="text-xs font-semibold text-white hover:text-red-400 transition-colors flex items-center gap-1">
                            <span>Visi & Misi</span>
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                        </a>
                    </div>
                </div>

                <!-- Center & Bottom Photo Collage -->
                <div class="lg:col-span-4 grid grid-cols-2 gap-4 items-center">
                    <div class="space-y-4">
                        <div class="rounded-2xl overflow-hidden shadow-md aspect-4/3 bg-zinc-100">
                            <img 
                                src="{{ asset('images/edmunhigh/small-group.jpg') }}" 
                                alt="Students Discussion" 
                                class="w-full h-full object-cover"
                            >
                        </div>
                        <div class="p-4 bg-zinc-50 rounded-2xl border border-zinc-200/80 text-center">
                            <span class="text-2xl font-serif font-bold text-zinc-900 block">100%</span>
                            <span class="text-[11px] text-zinc-500">Kelulusan & Lanjut Studi</span>
                        </div>
                    </div>

                    <div class="rounded-3xl overflow-hidden shadow-lg aspect-3/4 bg-zinc-100">
                        <img 
                            src="{{ asset('images/edmunhigh/student-portrait.jpg') }}" 
                            alt="Student Portrait" 
                            class="w-full h-full object-cover"
                        >
                    </div>
                </div>

                <!-- Right: Editorial Text & Crosshair Grid -->
                <div class="lg:col-span-3 space-y-6">
                    <div class="hidden lg:block">
                        {!! $crosshairSvg !!}
                    </div>

                    <p class="text-sm text-zinc-600 leading-relaxed">
                        @if (!empty($setting->welcome_message))
                            {{ Str::limit(strip_tags($setting->welcome_message), 180) }}
                        @else
                            Unlocking every student's potential by offering a stimulating and supportive environment is what we offer. Our innovative education encourages students to explore, collaborate, and excel.
                        @endif
                    </p>

                    <div>
                        <a 
                            href="{{ route('school.public.profile', ['school' => $school->slug]) }}" 
                            class="inline-flex items-center gap-1.5 text-xs font-bold uppercase tracking-wider text-zinc-900 hover:text-[#E11D2E] border-b border-zinc-900 hover:border-[#E11D2E] pb-1 transition-all"
                        >
                            <span>Learn More</span>
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                            </svg>
                        </a>
                    </div>
                </div>

            </div>

            <!-- Bottom: 3 Core Pillars (Academic Excellence, Faculty, Supportive Community) -->
            <div class="pt-12 border-t border-zinc-200/80 grid grid-cols-1 md:grid-cols-3 gap-8">
                
                <!-- Pillar 1 -->
                <div class="space-y-3">
                    <div class="w-12 h-12 rounded-full border border-zinc-300 flex items-center justify-center text-zinc-800">
                        <svg class="w-6 h-6 text-zinc-800" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M12 14l9-5-9-5-9 5 9 5z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"/>
                        </svg>
                    </div>
                    <h3 class="font-bold text-base text-zinc-900">Academic Excellence</h3>
                    <p class="text-xs sm:text-sm text-zinc-500 leading-relaxed">
                        Our rigorous curriculum ensures students are well-prepared for college and life beyond.
                    </p>
                </div>

                <!-- Pillar 2 -->
                <div class="space-y-3">
                    <div class="w-12 h-12 rounded-full border border-zinc-300 flex items-center justify-center text-zinc-800">
                        <svg class="w-6 h-6 text-zinc-800" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"/>
                        </svg>
                    </div>
                    <h3 class="font-bold text-base text-zinc-900">Experienced Faculty</h3>
                    <p class="text-xs sm:text-sm text-zinc-500 leading-relaxed">
                        Our dedicated teachers are passionate about education and committed to student success.
                    </p>
                </div>

                <!-- Pillar 3 -->
                <div class="space-y-3">
                    <div class="w-12 h-12 rounded-full border border-zinc-300 flex items-center justify-center text-zinc-800">
                        <svg class="w-6 h-6 text-zinc-800" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                    </div>
                    <h3 class="font-bold text-base text-zinc-900">Supportive Community</h3>
                    <p class="text-xs sm:text-sm text-zinc-500 leading-relaxed">
                        We foster a strong sense of community, character, and belonging for all students on board.
                    </p>
                </div>

            </div>

        </div>
    </section>

    <!-- ====================================================================== -->
    <!-- 3. STUDENT PROGRAMS SECTION (Interactive Split Layout)                 -->
    <!-- ====================================================================== -->
    <section class="py-16 sm:py-24 bg-[#F8F9FA] border-b border-zinc-200/80">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-10 items-center">
                
                <!-- Left Column: Title & Programs List -->
                <div class="lg:col-span-6 space-y-8">
                    <div>
                        <span class="text-[11px] font-bold tracking-widest uppercase text-zinc-400 block mb-2">
                            PROGRAMS
                        </span>
                        <h2 class="text-3xl sm:text-4xl lg:text-5xl font-serif font-normal text-zinc-900 leading-[1.12]">
                            Student programs <br>
                            you can register here
                        </h2>
                    </div>

                    <!-- Interactive Program Items List -->
                    <div class="space-y-4 pt-2">
                        <div class="flex items-center gap-3 text-sm sm:text-base font-bold text-zinc-900 cursor-pointer group">
                            <span class="text-[#E11D2E] font-bold">&mdash;</span>
                            <span class="border-b border-zinc-900 pb-0.5">College AP & STEM Program</span>
                        </div>

                        <div class="flex items-center gap-3 text-sm sm:text-base font-medium text-zinc-400 hover:text-zinc-800 transition-colors cursor-pointer group">
                            <span class="opacity-0 group-hover:opacity-100 transition-opacity text-zinc-400">&mdash;</span>
                            <span>Peminatan IPA / Sains & Riset Bioteknologi</span>
                        </div>

                        <div class="flex items-center gap-3 text-sm sm:text-base font-medium text-zinc-400 hover:text-zinc-800 transition-colors cursor-pointer group">
                            <span class="opacity-0 group-hover:opacity-100 transition-opacity text-zinc-400">&mdash;</span>
                            <span>Peminatan IPS / Social & Business Economics</span>
                        </div>

                        <div class="flex items-center gap-3 text-sm sm:text-base font-medium text-zinc-400 hover:text-zinc-800 transition-colors cursor-pointer group">
                            <span class="opacity-0 group-hover:opacity-100 transition-opacity text-zinc-400">&mdash;</span>
                            <span>Program Bahasa & Komunikasi Global (TOEFL/IELTS)</span>
                        </div>

                        <div class="flex items-center gap-3 text-sm sm:text-base font-medium text-zinc-400 hover:text-zinc-800 transition-colors cursor-pointer group">
                            <span class="opacity-0 group-hover:opacity-100 transition-opacity text-zinc-400">&mdash;</span>
                            <span>Program Vokasi Digital (Software Engineering & AI)</span>
                        </div>

                        <div class="flex items-center gap-3 text-sm sm:text-base font-medium text-zinc-400 hover:text-zinc-800 transition-colors cursor-pointer group">
                            <span class="opacity-0 group-hover:opacity-100 transition-opacity text-zinc-400">&mdash;</span>
                            <span>Humanities & Leadership Development</span>
                        </div>
                    </div>

                    <!-- Blueprint Crosshair Motif -->
                    <div class="pt-6">
                        {!! $crosshairSvg !!}
                    </div>
                </div>

                <!-- Right Column: Programs Featured Classroom Image & Floating Card -->
                <div class="lg:col-span-6 relative">
                    <div class="rounded-3xl overflow-hidden shadow-2xl border border-zinc-200/90 aspect-4/3 bg-zinc-100">
                        <img 
                            src="{{ asset('images/edmunhigh/programs-classroom.jpg') }}" 
                            alt="Classroom AP Learning" 
                            class="w-full h-full object-cover"
                        >
                    </div>

                    <!-- Floating Dark Navy Overlay Card -->
                    <div class="sm:absolute -bottom-6 -right-6 mt-4 sm:mt-0 bg-[#161F2E] text-white p-6 sm:p-8 rounded-3xl shadow-2xl max-w-sm border border-zinc-700/50" style="background-color: #161F2E; color: #ffffff;">
                        <p class="text-xs sm:text-sm text-zinc-300 leading-relaxed">
                            College-level courses and exams that allow students to earn college credit, certifications, and national academic honors.
                        </p>
                        <div class="mt-4 pt-4 border-t border-zinc-700/60 flex items-center justify-between">
                            <span class="text-[11px] text-zinc-400">Kurikulum Merdeka Unggul</span>
                            <a href="{{ route('school.public.profile', ['school' => $school->slug]) }}" class="text-xs font-bold text-white hover:text-red-400 transition-colors">
                                Detail Kurikulum &rarr;
                            </a>
                        </div>
                    </div>
                </div>

            </div>

        </div>
    </section>

    <!-- ====================================================================== -->
    <!-- 4. CAMPUS LIFE CINEMATIC BANNER (Video Tour Highlight)                 -->
    <!-- ====================================================================== -->
    <section class="py-14 sm:py-20 bg-white border-b border-zinc-200/80">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            
            <div class="relative rounded-3xl overflow-hidden shadow-2xl aspect-[16/8] sm:aspect-[16/7] lg:aspect-[16/6.5] bg-zinc-900 group">
                <img 
                    src="{{ asset('images/edmunhigh/campus-life-video.jpg') }}" 
                    alt="Campus Life at {{ $school->name }}" 
                    class="w-full h-full object-cover object-center group-hover:scale-105 transition-transform duration-700 ease-out opacity-85"
                >
                <div class="absolute inset-0 bg-black/35 group-hover:bg-black/25 transition-colors"></div>

                <!-- Center Large Glowing Play Button -->
                <div class="absolute inset-0 flex flex-col items-center justify-center p-6 text-center text-white">
                    <a 
                        href="{{ $setting->hero_video_url ?: '#' }}" 
                        @if ($setting->hero_video_url) target="_blank" @endif
                        class="w-20 h-20 sm:w-24 sm:h-24 rounded-full bg-white/90 hover:bg-white text-zinc-900 flex items-center justify-center shadow-2xl hover:scale-110 active:scale-95 transition-all duration-300 backdrop-blur-xs group/btn"
                    >
                        <svg class="w-8 h-8 sm:w-10 sm:h-10 fill-current text-zinc-900 ml-1.5" viewBox="0 0 24 24">
                            <path d="M8 5v14l11-7z"/>
                        </svg>
                    </a>
                    <span class="mt-4 text-xs sm:text-sm font-bold tracking-wider uppercase text-white drop-shadow-md">
                        Tur Virtual Kampus & Suasana Pembelajaran
                    </span>
                    <span class="text-[11px] text-zinc-200 drop-shadow-sm mt-0.5">
                        Lihat fasilitas laboratorium, perpustakaan modern, dan aktivitas kolaborasi siswa
                    </span>
                </div>
            </div>

        </div>
    </section>

    <!-- ====================================================================== -->
    <!-- 5. NEWS & ARTICLES SECTION (Matching Real Published Content)          -->
    <!-- ====================================================================== -->
    @if ($latestNews->isNotEmpty())
        <section class="py-14 sm:py-20 bg-zinc-50 border-b border-zinc-200/80">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex items-center justify-between mb-8">
                    <div>
                        <span class="text-[11px] font-bold tracking-widest uppercase text-zinc-400 block mb-1">NEWS</span>
                        <h2 class="text-2xl sm:text-3xl font-serif text-zinc-900">Warta & Berita Terkini</h2>
                    </div>
                    <a href="{{ route('school.public.articles', ['school' => $school->slug]) }}" class="text-xs font-bold uppercase tracking-wider text-zinc-900 hover:text-[#E11D2E] inline-flex items-center gap-1">
                        <span>Lihat Semua</span>
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                    </a>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach ($latestNews as $news)
                        <article class="bg-white rounded-2xl overflow-hidden border border-zinc-200 shadow-2xs hover:shadow-md transition-all flex flex-col">
                            <div class="h-44 bg-zinc-100 overflow-hidden relative">
                                @if ($news->coverImageUrl())
                                    <img src="{{ $news->coverImageUrl() }}" alt="{{ $news->title }}" class="w-full h-full object-cover">
                                @else
                                    <div class="w-full h-full flex items-center justify-center bg-zinc-100 text-zinc-300">
                                        <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"/></svg>
                                    </div>
                                @endif
                                @if ($news->category)
                                    <span class="absolute top-2.5 left-2.5 px-2 py-0.5 text-[10px] font-bold rounded bg-zinc-900/80 text-white backdrop-blur-xs">
                                        {{ $news->category }}
                                    </span>
                                @endif
                            </div>
                            <div class="p-5 flex-1 flex flex-col justify-between">
                                <div>
                                    <span class="text-[11px] text-zinc-400 block mb-1">
                                        {{ $news->published_at ? $news->published_at->format('d M Y') : 'Terbaru' }}
                                    </span>
                                    <h3 class="font-bold text-sm text-zinc-900 leading-snug line-clamp-2 mb-2 hover:text-[#E11D2E] transition-colors">
                                        <a href="{{ route('school.public.article-detail', ['school' => $school->slug, 'slug' => $news->slug]) }}">
                                            {{ $news->title }}
                                        </a>
                                    </h3>
                                    <p class="text-xs text-zinc-500 line-clamp-2 leading-relaxed">
                                        {{ $news->excerpt ?: Str::limit(strip_tags($news->content), 90) }}
                                    </p>
                                </div>
                                <div class="pt-3 mt-3 border-t border-zinc-100">
                                    <a href="{{ route('school.public.article-detail', ['school' => $school->slug, 'slug' => $news->slug]) }}" class="text-xs font-bold text-[#E11D2E] hover:underline inline-flex items-center gap-1">
                                        <span>Baca Artikel</span>
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                    </a>
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    <!-- ====================================================================== -->
    <!-- 6. EVENTS & AGENDA SECTION (Join our next upcoming events)             -->
    <!-- ====================================================================== -->
    <section class="py-16 sm:py-24 bg-white border-b border-zinc-200/80">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-10">
                
                <!-- Left: Section Title & Blueprint Grid -->
                <div class="lg:col-span-5 space-y-6">
                    <div>
                        <span class="text-[11px] font-bold tracking-widest uppercase text-zinc-400 block mb-2">
                            EVENTS
                        </span>
                        <h2 class="text-3xl sm:text-4xl lg:text-5xl font-serif font-normal text-zinc-900 leading-[1.15]">
                            Join our next <br>
                            upcoming events
                        </h2>
                    </div>

                    <p class="text-xs sm:text-sm text-zinc-500 leading-relaxed max-w-sm">
                        Ikuti berbagai agenda akademik, pameran karya siswa, kompetisi sains, serta open house penerimaan siswa baru.
                    </p>

                    <div class="pt-6 hidden sm:block">
                        {!! $crosshairSvg !!}
                    </div>

                    <div class="pt-2">
                        <a href="{{ route('school.public.events', ['school' => $school->slug]) }}" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-full text-xs font-bold border border-zinc-300 hover:border-zinc-900 text-zinc-800 transition-colors">
                            <span>Lihat Kalender Seluruh Acara</span>
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                        </a>
                    </div>
                </div>

                <!-- Right: Sleek Event List Cards -->
                <div class="lg:col-span-7 space-y-4">
                    
                    @if ($upcomingEvents->isNotEmpty())
                        @foreach ($upcomingEvents as $evt)
                            <div class="bg-zinc-50/70 hover:bg-zinc-100/80 p-5 rounded-2xl border border-zinc-200/80 transition-all flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 group">
                                <div class="flex items-center gap-4 min-w-0">
                                    <div class="w-16 h-16 rounded-xl overflow-hidden bg-zinc-200 shrink-0">
                                        @if ($evt->coverImageUrl())
                                            <img src="{{ $evt->coverImageUrl() }}" alt="{{ $evt->title }}" class="w-full h-full object-cover">
                                        @else
                                            <div class="w-full h-full bg-[#161F2E] text-white flex flex-col items-center justify-center text-center p-1">
                                                <span class="text-sm font-bold leading-none">{{ $evt->event_date?->format('d') ?? '12' }}</span>
                                                <span class="text-[9px] uppercase font-mono mt-0.5">{{ $evt->event_date?->format('M') ?? 'OKT' }}</span>
                                            </div>
                                        @endif
                                    </div>

                                    <div class="min-w-0">
                                        <h3 class="font-bold text-sm sm:text-base text-zinc-900 leading-snug group-hover:text-[#E11D2E] transition-colors line-clamp-1">
                                            {{ $evt->title }}
                                        </h3>
                                        <div class="flex flex-wrap items-center gap-3 text-xs text-zinc-500 mt-1">
                                            <span class="flex items-center gap-1">
                                                <svg class="w-3.5 h-3.5 text-zinc-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                                <span>{{ $evt->event_date ? $evt->event_date->format('d F Y') : '18 Oktober 2026' }}</span>
                                            </span>
                                            @if ($evt->event_location)
                                                <span>&bull;</span>
                                                <span class="flex items-center gap-1">
                                                    <svg class="w-3.5 h-3.5 text-zinc-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                                    <span class="truncate">{{ $evt->event_location }}</span>
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                </div>

                                <a 
                                    href="{{ route('school.public.events', ['school' => $school->slug]) }}" 
                                    class="text-xs font-bold uppercase tracking-wider text-zinc-900 group-hover:text-[#E11D2E] inline-flex items-center gap-1 shrink-0"
                                >
                                    <span>Learn More</span>
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                                </a>
                            </div>
                        @endforeach
                    @else
                        <!-- Default Curated EdmunHigh Style Events List -->
                        <div class="bg-zinc-50/70 hover:bg-zinc-100/80 p-5 rounded-2xl border border-zinc-200/80 transition-all flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 group">
                            <div class="flex items-center gap-4 min-w-0">
                                <div class="w-16 h-16 rounded-xl overflow-hidden bg-zinc-200 shrink-0">
                                    <img src="{{ asset('images/edmunhigh/small-group.jpg') }}" alt="Senior Fine Arts Showcase" class="w-full h-full object-cover">
                                </div>
                                <div>
                                    <h3 class="font-bold text-sm sm:text-base text-zinc-900 group-hover:text-[#E11D2E] transition-colors">
                                        Senior Fine Arts & Technology Showcase
                                    </h3>
                                    <div class="flex items-center gap-3 text-xs text-zinc-500 mt-1">
                                        <span>5 &ndash; 6 November 2026</span>
                                        <span>&bull;</span>
                                        <span>Edmun Auditorium & Art Studio</span>
                                    </div>
                                </div>
                            </div>
                            <span class="text-xs font-bold uppercase tracking-wider text-zinc-900 group-hover:text-[#E11D2E] inline-flex items-center gap-1 shrink-0">
                                <span>Learn More</span>
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                            </span>
                        </div>

                        <div class="bg-zinc-50/70 hover:bg-zinc-100/80 p-5 rounded-2xl border border-zinc-200/80 transition-all flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 group">
                            <div class="flex items-center gap-4 min-w-0">
                                <div class="w-16 h-16 rounded-xl overflow-hidden bg-zinc-200 shrink-0">
                                    <img src="{{ asset('images/edmunhigh/programs-classroom.jpg') }}" alt="Spring High Science Fair" class="w-full h-full object-cover">
                                </div>
                                <div>
                                    <h3 class="font-bold text-sm sm:text-base text-zinc-900 group-hover:text-[#E11D2E] transition-colors">
                                        National High School Science & Robotics Fair
                                    </h3>
                                    <div class="flex items-center gap-3 text-xs text-zinc-500 mt-1">
                                        <span>18 November 2026</span>
                                        <span>&bull;</span>
                                        <span>Innovation & STEM Laboratory</span>
                                    </div>
                                </div>
                            </div>
                            <span class="text-xs font-bold uppercase tracking-wider text-zinc-900 group-hover:text-[#E11D2E] inline-flex items-center gap-1 shrink-0">
                                <span>Learn More</span>
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                            </span>
                        </div>

                        <div class="bg-zinc-50/70 hover:bg-zinc-100/80 p-5 rounded-2xl border border-zinc-200/80 transition-all flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 group">
                            <div class="flex items-center gap-4 min-w-0">
                                <div class="w-16 h-16 rounded-xl overflow-hidden bg-zinc-200 shrink-0">
                                    <img src="{{ asset('images/edmunhigh/student-portrait.jpg') }}" alt="Public College and Career Day" class="w-full h-full object-cover">
                                </div>
                                <div>
                                    <h3 class="font-bold text-sm sm:text-base text-zinc-900 group-hover:text-[#E11D2E] transition-colors">
                                        Public College & Career Preparation Day
                                    </h3>
                                    <div class="flex items-center gap-3 text-xs text-zinc-500 mt-1">
                                        <span>25 November 2026</span>
                                        <span>&bull;</span>
                                        <span>Main Library & Exhibition Hall</span>
                                    </div>
                                </div>
                            </div>
                            <span class="text-xs font-bold uppercase tracking-wider text-zinc-900 group-hover:text-[#E11D2E] inline-flex items-center gap-1 shrink-0">
                                <span>Learn More</span>
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                            </span>
                        </div>
                    @endif

                </div>

            </div>

        </div>
    </section>

    <!-- ====================================================================== -->
    <!-- 7. PPDB HIGHLIGHT STRIP & ENROLLMENT CTA                               -->
    <!-- ====================================================================== -->
    <section class="py-14 sm:py-20 bg-[#161F2E] text-white relative overflow-hidden" style="background-color: #161F2E; color: #ffffff;">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
            <div class="flex flex-col lg:flex-row items-center justify-between gap-8">
                
                <div class="space-y-3 max-w-2xl text-center lg:text-left">
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-[#E11D2E] text-white shadow-xs">
                        <span class="w-1.5 h-1.5 rounded-full bg-white animate-pulse"></span>
                        Penerimaan Peserta Didik Baru (PPDB / SPMB)
                    </span>
                    <h2 class="text-3xl sm:text-4xl font-serif leading-tight">
                        @if ($activeAdmissionPeriod)
                            {{ $activeAdmissionPeriod->name }}
                        @else
                            Bergabunglah dengan Komunitas Pembelajar Berprestasi
                        @endif
                    </h2>
                    <p class="text-xs sm:text-sm text-zinc-300 leading-relaxed">
                        @if ($activeAdmissionPeriod)
                            Periode Pendaftaran: <strong>{{ $activeAdmissionPeriod->start_date->format('d M Y') }}</strong> s/d <strong>{{ $activeAdmissionPeriod->end_date->format('d M Y') }}</strong>
                            @if ($activeAdmissionPeriod->tracks->isNotEmpty())
                                &bull; Jalur: {{ $activeAdmissionPeriod->tracks->pluck('name')->implode(', ') }}
                            @endif
                        @else
                            Daftarkan putra-putri Anda untuk menempuh pendidikan berkualitas tinggi dengan pembinaan karakter, fasilitas modern, dan peluang beasiswa prestasi.
                        @endif
                    </p>
                </div>

                <div class="flex flex-wrap items-center justify-center gap-4 shrink-0">
                    @if ($activeAdmissionPeriod)
                        <a 
                            href="{{ route('ppdb.register', ['period' => $activeAdmissionPeriod->code]) }}" 
                            class="px-8 py-4 rounded-full font-bold text-sm bg-[#E11D2E] hover:bg-rose-700 text-white shadow-xl transition-all duration-200 hover:-translate-y-0.5"
                        >
                            Daftar Sekarang &rarr;
                        </a>
                        <a 
                            href="{{ route('ppdb.check-status') }}" 
                            class="px-6 py-4 rounded-full font-semibold text-sm bg-white/10 hover:bg-white/20 text-white border border-white/20 transition-colors"
                        >
                            Cek Status Seleksi
                        </a>
                    @else
                        <a 
                            href="{{ route('ppdb.index') }}" 
                            class="px-8 py-4 rounded-full font-bold text-sm bg-[#E11D2E] hover:bg-rose-700 text-white shadow-xl transition-all duration-200 hover:-translate-y-0.5"
                        >
                            Informasi Jalur Masuk PPDB &rarr;
                        </a>
                        <a 
                            href="{{ route('school.public.contact', ['school' => $school->slug]) }}" 
                            class="px-6 py-4 rounded-full font-semibold text-sm bg-white/10 hover:bg-white/20 text-white border border-white/20 transition-colors"
                        >
                            Hubungi Admin PPDB
                        </a>
                    @endif
                </div>

            </div>
        </div>
    </section>

@endsection
