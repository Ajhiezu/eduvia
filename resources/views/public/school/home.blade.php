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
                    <div class="flex flex-wrap items-center gap-2.5 sm:gap-3 mb-5">
                        <div class="inline-flex items-center gap-2 px-2.5 py-1 rounded-md bg-zinc-100 text-zinc-800 border border-zinc-200/80 shadow-2xs font-sans">
                            <span class="w-1.5 h-1.5 rounded-full bg-zinc-700 shrink-0"></span>
                            <span class="text-[11px] font-bold tracking-wider uppercase">
                                {{ $school->short_name ?: $school->name }} &bull; Situs Resmi
                            </span>
                        </div>

                        @if ($setting->accreditation_grade)
                            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-md bg-zinc-900 text-white shadow-xs font-sans">
                                <svg class="w-3.5 h-3.5 text-amber-400 fill-current shrink-0" viewBox="0 0 20 20" aria-hidden="true">
                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                </svg>
                                <span class="text-[11px] font-semibold tracking-tight">Akreditasi {{ $setting->accreditation_grade }}</span>
                                <span class="text-[9px] uppercase tracking-wider text-zinc-400 font-bold border-l border-zinc-700 pl-2 hidden sm:inline">BAN-S/M</span>
                            </div>
                        @endif

                        @if (!empty($setting->hero_headline))
                            <span class="hidden md:inline-block text-xs font-medium text-zinc-500 font-sans italic border-l border-zinc-200 pl-3">
                                {{ $setting->hero_headline }}
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
                    <p class="text-sm sm:text-base text-zinc-600 leading-relaxed font-sans">
                        {{ $setting->hero_subheadline ?: 'Membina generasi pembelajar berkarakter luhur, berdaya saing global, dan adaptif terhadap masa depan melalui kurikulum terpadu serta ekosistem pendidikan yang holistik.' }}
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
                            <span class="hidden sm:inline-flex items-center gap-2 px-3 py-1.5 rounded-md text-xs font-medium bg-emerald-50 text-emerald-950 border border-emerald-200 shadow-2xs font-sans">
                                <span class="relative flex h-2 w-2 shrink-0">
                                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-60"></span>
                                    <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-600"></span>
                                </span>
                                <span class="font-semibold tracking-tight">PPDB Dibuka</span>
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
                    <div class="backdrop-blur-md bg-white/95 text-zinc-900 px-4 py-2 rounded-2xl text-xs font-bold shadow-md flex items-center gap-2 border border-zinc-200/50">
                        <span class="w-2 h-2 rounded-full bg-[#161F2E]"></span>
                        <span>{{ $school->name }} &bull; Jenjang {{ strtoupper(implode(' / ', $school->educational_levels ?? ['SMA'])) }}</span>
                    </div>

                    @if ($activeAdmissionPeriod)
                        <a href="{{ route('ppdb.register', ['period' => $activeAdmissionPeriod->code]) }}" class="backdrop-blur-md bg-[#161F2E] text-white hover:bg-black px-4 py-2 rounded-2xl text-xs font-semibold shadow-md transition-colors flex items-center gap-1.5">
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
    <section id="about" class="py-16 sm:py-24 bg-white border-b border-zinc-200/80 relative">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-center mb-16 sm:mb-20">
                
                <!-- Left: Dark Navy Statement Block -->
                <div class="lg:col-span-5 bg-[#161F2E] text-white rounded-3xl p-8 sm:p-12 shadow-xl flex flex-col justify-between min-h-[380px] relative overflow-hidden" style="background-color: #161F2E; color: #ffffff;">
                    <!-- Subtle Decorative Crosshair inside navy card -->
                    <div class="absolute top-6 right-6 opacity-20">
                        {!! $crosshairSvg !!}
                    </div>

                    <div>
                        <span class="text-[11px] font-bold tracking-widest uppercase text-zinc-400 block mb-6 font-sans">
                            ABOUT
                        </span>
                        <h2 class="text-3xl sm:text-4xl lg:text-[42px] font-serif font-normal leading-[1.15] text-white">
                            Unleash students’ <br>
                            possibilities with us
                        </h2>
                        @if (!empty($setting->principal_name))
                            <p class="text-xs text-zinc-300 mt-4 font-sans">
                                Dipimpin oleh: <strong class="text-white">{{ $setting->principal_name }}</strong>
                            </p>
                        @endif
                    </div>

                    <div class="pt-8 border-t border-zinc-700/60 flex items-center justify-between">
                        <span class="text-xs text-zinc-400 font-sans">Komitmen Keunggulan & Integritas</span>
                        <a href="{{ route('school.public.profile', ['school' => $school->slug]) }}" class="text-xs font-semibold text-white/90 hover:text-white transition-colors flex items-center gap-1 font-sans">
                            <span>Visi & Misi</span>
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                        </a>
                    </div>
                </div>

                <!-- Center & Bottom Photo Collage -->
                <div class="lg:col-span-4 grid grid-cols-2 gap-4 items-center">
                    <div class="space-y-4">
                        <div class="rounded-2xl overflow-hidden shadow-md aspect-4/3 bg-zinc-100 border border-zinc-200/70">
                            <img 
                                src="{{ asset('images/edmunhigh/small-group.jpg') }}" 
                                alt="Students Discussion" 
                                class="w-full h-full object-cover"
                            >
                        </div>
                        <div class="p-4 bg-zinc-50 rounded-2xl border border-zinc-200/80 text-center">
                            <span class="text-2xl sm:text-3xl font-serif font-bold text-zinc-900 block">100%</span>
                            <span class="text-[11px] font-medium text-zinc-500 font-sans">Kelulusan & Penyaluran Studi</span>
                        </div>
                    </div>

                    <div class="rounded-3xl overflow-hidden shadow-lg aspect-3/4 bg-zinc-100 border border-zinc-200/70">
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

                    <p class="text-sm text-zinc-600 leading-relaxed font-sans">
                        @if (!empty($setting->welcome_message))
                            {{ Str::limit(strip_tags($setting->welcome_message), 210) }}
                        @else
                            Kami mendedikasikan lingkungan belajar yang kondusif, berpusat pada siswa, dan mendorong eksplorasi keilmuan secara mendalam. Setiap siswa dibimbing untuk mengenali potensinya, bernalar kritis, serta berkontribusi nyata bagi masyarakat.
                        @endif
                    </p>

                    <div>
                        <a 
                            href="{{ route('school.public.profile', ['school' => $school->slug]) }}" 
                            class="inline-flex items-center gap-1.5 text-xs font-bold uppercase tracking-wider text-zinc-900 hover:text-zinc-600 border-b border-zinc-900 hover:border-zinc-600 pb-1 transition-all"
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
            <div id="community" class="pt-12 border-t border-zinc-200/80 grid grid-cols-1 md:grid-cols-3 gap-8">
                
                <!-- Pillar 1 -->
                <div class="space-y-3">
                    <div class="w-12 h-12 rounded-full border border-zinc-300 flex items-center justify-center text-zinc-800">
                        <svg class="w-6 h-6 text-zinc-800" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M12 14l9-5-9-5-9 5 9 5z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"/>
                        </svg>
                    </div>
                    <h3 class="font-bold text-base text-zinc-900 font-sans">Academic Excellence</h3>
                    <p class="text-xs sm:text-sm text-zinc-500 leading-relaxed font-sans">
                        Kurikulum komprehensif berstandar unggul yang menstimulasi daya analitis, pemecahan masalah, dan penguasaan sains modern.
                    </p>
                </div>

                <!-- Pillar 2 -->
                <div class="space-y-3">
                    <div class="w-12 h-12 rounded-full border border-zinc-300 flex items-center justify-center text-zinc-800">
                        <svg class="w-6 h-6 text-zinc-800" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"/>
                        </svg>
                    </div>
                    <h3 class="font-bold text-base text-zinc-900 font-sans">Experienced Faculty</h3>
                    <p class="text-xs sm:text-sm text-zinc-500 leading-relaxed font-sans">
                        Didampingi tenaga pendidik profesional, bersertifikasi, dan berdedikasi membimbing potensi intelektual serta karakter siswa.
                    </p>
                </div>

                <!-- Pillar 3 -->
                <div class="space-y-3">
                    <div class="w-12 h-12 rounded-full border border-zinc-300 flex items-center justify-center text-zinc-800">
                        <svg class="w-6 h-6 text-zinc-800" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                    </div>
                    <h3 class="font-bold text-base text-zinc-900 font-sans">Supportive Community</h3>
                    <p class="text-xs sm:text-sm text-zinc-500 leading-relaxed font-sans">
                        Ekosistem sekolah yang hangat, inklusif, dan kolaboratif, menumbuhkan jiwa kepemimpinan serta integritas etika sosial.
                    </p>
                </div>

            </div>

        </div>
    </section>

    <!-- ====================================================================== -->
    <!-- 3. STUDENT PROGRAMS SECTION (Interactive Split Layout)                 -->
    <!-- ====================================================================== -->
    <section id="programs" class="py-16 sm:py-24 bg-[#F8F9FA] border-b border-zinc-200/80">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-10 items-center">
                
                <!-- Left Column: Title & Programs List -->
                <div class="lg:col-span-6 space-y-8">
                    <div>
                        <span class="text-[11px] font-bold tracking-widest uppercase text-zinc-400 block mb-2 font-sans">
                            PROGRAMS
                        </span>
                        <h2 class="text-3xl sm:text-4xl lg:text-5xl font-serif font-normal text-zinc-900 leading-[1.12]">
                            Student programs <br>
                            you can register here
                        </h2>
                    </div>

                    <!-- Interactive Program Items List -->
                    <div class="space-y-4 pt-2 font-sans">
                        <div class="flex items-center gap-3 text-sm sm:text-base font-bold text-zinc-900 cursor-pointer group">
                            <span class="text-zinc-900 font-bold">&mdash;</span>
                            <span class="border-b border-zinc-900 pb-0.5">Program Penguatan Sains & Riset Teknologi (STEM)</span>
                        </div>

                        <div class="flex items-center gap-3 text-sm sm:text-base font-medium text-zinc-400 hover:text-zinc-800 transition-colors cursor-pointer group">
                            <span class="opacity-0 group-hover:opacity-100 transition-opacity text-zinc-400">&mdash;</span>
                            <span>Peminatan MIPA (Matematika & Ilmu Pengetahuan Alam Terapan)</span>
                        </div>

                        <div class="flex items-center gap-3 text-sm sm:text-base font-medium text-zinc-400 hover:text-zinc-800 transition-colors cursor-pointer group">
                            <span class="opacity-0 group-hover:opacity-100 transition-opacity text-zinc-400">&mdash;</span>
                            <span>Peminatan Sosial Humaniora & Studi Ekonomi Global</span>
                        </div>

                        <div class="flex items-center gap-3 text-sm sm:text-base font-medium text-zinc-400 hover:text-zinc-800 transition-colors cursor-pointer group">
                            <span class="opacity-0 group-hover:opacity-100 transition-opacity text-zinc-400">&mdash;</span>
                            <span>Program Bahasa & Diplomasi Komunikasi Internasional</span>
                        </div>

                        <div class="flex items-center gap-3 text-sm sm:text-base font-medium text-zinc-400 hover:text-zinc-800 transition-colors cursor-pointer group">
                            <span class="opacity-0 group-hover:opacity-100 transition-opacity text-zinc-400">&mdash;</span>
                            <span>Inovasi Digital, Rekayasa Perangkat Lunak & Kecerdasan Buatan</span>
                        </div>

                        <div class="flex items-center gap-3 text-sm sm:text-base font-medium text-zinc-400 hover:text-zinc-800 transition-colors cursor-pointer group">
                            <span class="opacity-0 group-hover:opacity-100 transition-opacity text-zinc-400">&mdash;</span>
                            <span>Pengembangan Kepemimpinan Siswa & Seni Budaya</span>
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
                        <p class="text-xs sm:text-sm text-zinc-300 leading-relaxed font-sans">
                            Pembelajaran berbasis riset dan proyek kolaboratif nyata yang membekali siswa dengan portofolio akademik unggul, sertifikasi kompetensi, serta kesiapan menembus perguruan tinggi terkemuka.
                        </p>
                        <div class="mt-4 pt-4 border-t border-zinc-700/60 flex items-center justify-between font-sans">
                            <span class="text-[11px] text-zinc-400">Kurikulum Unggul Nasional</span>
                            <a href="{{ route('school.public.profile', ['school' => $school->slug]) }}" class="text-xs font-semibold text-white/90 hover:text-white transition-colors">
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
    <section id="campus-life" class="py-14 sm:py-20 bg-white border-b border-zinc-200/80">
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
                        class="w-20 h-20 sm:w-24 sm:h-24 rounded-full bg-white/95 hover:bg-white text-zinc-900 flex items-center justify-center shadow-2xl hover:scale-105 active:scale-95 transition-all duration-300 backdrop-blur-xs group/btn"
                    >
                        <svg class="w-8 h-8 sm:w-10 sm:h-10 fill-current text-zinc-900 ml-1.5" viewBox="0 0 24 24">
                            <path d="M8 5v14l11-7z"/>
                        </svg>
                    </a>
                    <span class="mt-4 text-xs sm:text-sm font-bold tracking-wider uppercase text-white drop-shadow-md font-sans">
                        Tur Virtual Kampus & Suasana Pembelajaran
                    </span>
                    <span class="text-[11px] text-zinc-200 drop-shadow-sm mt-0.5 font-sans">
                        Eksplorasi ruang belajar modern, laboratorium sains terpadu, perpustakaan digital, serta sarana olahraga
                    </span>
                </div>
            </div>

        </div>
    </section>

    <!-- ====================================================================== -->
    <!-- 5. NEWS & ARTICLES SECTION (Matching Real Published Content)          -->
    <!-- ====================================================================== -->
    @if ($latestNews->isNotEmpty())
        <section id="news" class="py-14 sm:py-20 bg-zinc-50 border-b border-zinc-200/80">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex items-center justify-between mb-8">
                    <div>
                        <span class="text-[11px] font-bold tracking-widest uppercase text-zinc-400 block mb-1 font-sans">NEWS</span>
                        <h2 class="text-2xl sm:text-3xl font-serif text-zinc-900">Warta & Berita Terkini</h2>
                    </div>
                    <a href="{{ route('school.public.articles', ['school' => $school->slug]) }}" class="text-xs font-bold uppercase tracking-wider text-zinc-900 hover:text-zinc-600 inline-flex items-center gap-1 font-sans">
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
                                    <span class="absolute top-2.5 left-2.5 px-2 py-0.5 text-[10px] font-bold rounded bg-zinc-900/80 text-white backdrop-blur-xs font-sans">
                                        {{ $news->category }}
                                    </span>
                                @endif
                            </div>
                            <div class="p-5 flex-1 flex flex-col justify-between">
                                <div>
                                    <span class="text-[11px] text-zinc-400 block mb-1 font-sans">
                                        {{ $news->published_at ? $news->published_at->format('d M Y') : 'Terbaru' }}
                                    </span>
                                    <h3 class="font-bold text-sm text-zinc-900 leading-snug line-clamp-2 mb-2 hover:text-zinc-700 transition-colors font-sans">
                                        <a href="{{ route('school.public.article-detail', ['school' => $school->slug, 'slug' => $news->slug]) }}">
                                            {{ $news->title }}
                                        </a>
                                    </h3>
                                    <p class="text-xs text-zinc-500 line-clamp-2 leading-relaxed font-sans">
                                        {{ $news->excerpt ?: Str::limit(strip_tags($news->content), 90) }}
                                    </p>
                                </div>
                                <div class="pt-3 mt-3 border-t border-zinc-100 font-sans">
                                    <a href="{{ route('school.public.article-detail', ['school' => $school->slug, 'slug' => $news->slug]) }}" class="text-xs font-semibold text-zinc-900 hover:underline inline-flex items-center gap-1">
                                        <span>Baca Artikel</span>
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7m0 0l-7 7"/></svg>
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
    <section id="events" class="py-16 sm:py-24 bg-white border-b border-zinc-200/80">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-10">
                
                <!-- Left: Section Title & Blueprint Grid -->
                <div class="lg:col-span-5 space-y-6">
                    <div>
                        <span class="text-[11px] font-bold tracking-widest uppercase text-zinc-400 block mb-2 font-sans">
                            EVENTS
                        </span>
                        <h2 class="text-3xl sm:text-4xl lg:text-5xl font-serif font-normal text-zinc-900 leading-[1.15]">
                            Join our next <br>
                            upcoming events
                        </h2>
                    </div>

                    <p class="text-xs sm:text-sm text-zinc-500 leading-relaxed max-w-sm font-sans">
                        Ikuti agenda akademik terdekat, simposium sains, pameran inovasi seni & teknologi, serta sosialisasi penerimaan siswa baru.
                    </p>

                    <div class="pt-6 hidden sm:block">
                        {!! $crosshairSvg !!}
                    </div>

                    <div class="pt-2">
                        <a href="{{ route('school.public.events', ['school' => $school->slug]) }}" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-full text-xs font-bold border border-zinc-300 hover:border-zinc-900 text-zinc-800 transition-colors font-sans">
                            <span>Lihat Kalender Seluruh Acara</span>
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                        </a>
                    </div>
                </div>

                <!-- Right: Sleek Event List Cards -->
                <div class="lg:col-span-7 space-y-4 font-sans">
                    
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
                                        <h3 class="font-bold text-sm sm:text-base text-zinc-900 leading-snug group-hover:text-zinc-700 transition-colors line-clamp-1">
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
                                    class="text-xs font-bold uppercase tracking-wider text-zinc-900 group-hover:text-zinc-600 inline-flex items-center gap-1 shrink-0"
                                >
                                    <span>Learn More</span>
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                                </a>
                            </div>
                        @endforeach
                    @else
                        <!-- Default Curated Institutional Events List -->
                        <div class="bg-zinc-50/70 hover:bg-zinc-100/80 p-5 rounded-2xl border border-zinc-200/80 transition-all flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 group">
                            <div class="flex items-center gap-4 min-w-0">
                                <div class="w-16 h-16 rounded-xl overflow-hidden bg-zinc-200 shrink-0">
                                    <img src="{{ asset('images/edmunhigh/small-group.jpg') }}" alt="Pentas Seni & Gelar Inovasi Siswa" class="w-full h-full object-cover">
                                </div>
                                <div>
                                    <h3 class="font-bold text-sm sm:text-base text-zinc-900 group-hover:text-zinc-700 transition-colors">
                                        Pentas Seni & Gelar Inovasi Siswa
                                    </h3>
                                    <div class="flex items-center gap-3 text-xs text-zinc-500 mt-1">
                                        <span>5 &ndash; 6 November 2026</span>
                                        <span>&bull;</span>
                                        <span>Auditorium Utama & Gedung Seni Kampus</span>
                                    </div>
                                </div>
                            </div>
                            <a href="{{ route('school.public.events', ['school' => $school->slug]) }}" class="text-xs font-bold uppercase tracking-wider text-zinc-900 group-hover:text-zinc-600 inline-flex items-center gap-1 shrink-0">
                                <span>Lihat Rincian</span>
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                            </a>
                        </div>

                        <div class="bg-zinc-50/70 hover:bg-zinc-100/80 p-5 rounded-2xl border border-zinc-200/80 transition-all flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 group">
                            <div class="flex items-center gap-4 min-w-0">
                                <div class="w-16 h-16 rounded-xl overflow-hidden bg-zinc-200 shrink-0">
                                    <img src="{{ asset('images/edmunhigh/programs-classroom.jpg') }}" alt="Pameran Riset Ilmiah & Inovasi Teknologi" class="w-full h-full object-cover">
                                </div>
                                <div>
                                    <h3 class="font-bold text-sm sm:text-base text-zinc-900 group-hover:text-zinc-700 transition-colors">
                                        Pameran Riset Ilmiah & Inovasi Teknologi Siswa
                                    </h3>
                                    <div class="flex items-center gap-3 text-xs text-zinc-500 mt-1">
                                        <span>18 November 2026</span>
                                        <span>&bull;</span>
                                        <span>Laboratorium Inovasi Sains & STEM</span>
                                    </div>
                                </div>
                            </div>
                            <a href="{{ route('school.public.events', ['school' => $school->slug]) }}" class="text-xs font-bold uppercase tracking-wider text-zinc-900 group-hover:text-zinc-600 inline-flex items-center gap-1 shrink-0">
                                <span>Lihat Rincian</span>
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                            </a>
                        </div>

                        <div class="bg-zinc-50/70 hover:bg-zinc-100/80 p-5 rounded-2xl border border-zinc-200/80 transition-all flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 group">
                            <div class="flex items-center gap-4 min-w-0">
                                <div class="w-16 h-16 rounded-xl overflow-hidden bg-zinc-200 shrink-0">
                                    <img src="{{ asset('images/edmunhigh/student-portrait.jpg') }}" alt="Edukasi & Pembekalan Karir Perguruan Tinggi" class="w-full h-full object-cover">
                                </div>
                                <div>
                                    <h3 class="font-bold text-sm sm:text-base text-zinc-900 group-hover:text-zinc-700 transition-colors">
                                        Edukasi & Pembekalan Karir Perguruan Tinggi
                                    </h3>
                                    <div class="flex items-center gap-3 text-xs text-zinc-500 mt-1">
                                        <span>25 November 2026</span>
                                        <span>&bull;</span>
                                        <span>Perpustakaan Utama & Ruang Pameran</span>
                                    </div>
                                </div>
                            </div>
                            <a href="{{ route('school.public.events', ['school' => $school->slug]) }}" class="text-xs font-bold uppercase tracking-wider text-zinc-900 group-hover:text-zinc-600 inline-flex items-center gap-1 shrink-0">
                                <span>Lihat Rincian</span>
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                            </a>
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
                    <span class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-md text-xs font-medium bg-white/10 text-white border border-white/20 backdrop-blur-xs font-sans shadow-2xs">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 shrink-0"></span>
                        <span class="font-semibold tracking-tight">Penerimaan Peserta Didik Baru (PPDB / SPMB)</span>
                    </span>
                    <h2 class="text-3xl sm:text-4xl font-serif leading-tight">
                        @if ($activeAdmissionPeriod)
                            {{ $activeAdmissionPeriod->name }}
                        @else
                            Bergabunglah dengan Komunitas Pembelajar Berprestasi
                        @endif
                    </h2>
                    <p class="text-xs sm:text-sm text-zinc-300 leading-relaxed font-sans">
                        @if ($activeAdmissionPeriod)
                            Periode Pendaftaran: <strong>{{ $activeAdmissionPeriod->start_date->format('d M Y') }}</strong> s/d <strong>{{ $activeAdmissionPeriod->end_date->format('d M Y') }}</strong>
                            @if ($activeAdmissionPeriod->tracks->isNotEmpty())
                                &bull; Jalur: {{ $activeAdmissionPeriod->tracks->pluck('name')->implode(', ') }}
                            @endif
                        @else
                            Daftarkan putra-putri Anda untuk menempuh pendidikan berkualitas tinggi dengan pembinaan karakter, fasilitas berstandar internasional, dan peluang beasiswa prestasi.
                        @endif
                    </p>
                </div>

                <div class="flex flex-wrap items-center justify-center gap-4 shrink-0 font-sans">
                    @if ($activeAdmissionPeriod)
                        <a 
                            href="{{ route('ppdb.register', ['period' => $activeAdmissionPeriod->code]) }}" 
                            class="px-8 py-4 rounded-full font-bold text-sm bg-white hover:bg-zinc-100 text-zinc-900 shadow-xl transition-all duration-200 hover:-translate-y-0.5"
                        >
                            Daftar Sekarang &rarr;
                        </a>
                        <a 
                            href="{{ route('ppdb.check-status') }}" 
                            class="px-6 py-4 rounded-full font-semibold text-sm bg-transparent hover:bg-white/10 text-white border border-white/30 transition-colors"
                        >
                            Cek Status Seleksi
                        </a>
                    @else
                        <a 
                            href="{{ route('ppdb.index') }}" 
                            class="px-8 py-4 rounded-full font-bold text-sm bg-white hover:bg-zinc-100 text-zinc-900 shadow-xl transition-all duration-200 hover:-translate-y-0.5"
                        >
                            Informasi Jalur Masuk PPDB &rarr;
                        </a>
                        <a 
                            href="{{ route('school.public.contact', ['school' => $school->slug]) }}" 
                            class="px-6 py-4 rounded-full font-semibold text-sm bg-transparent hover:bg-white/10 text-white border border-white/30 transition-colors"
                        >
                            Hubungi Admin PPDB
                        </a>
                    @endif
                </div>

            </div>
        </div>
    </section>

@endsection
