<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $metaTitle ?? ($school->name . ' - Website Resmi Sekolah') }}</title>
    <meta name="description" content="{{ $metaDescription ?? ($school->websiteSetting?->meta_description ?? 'Situs resmi ' . $school->name . ' didukung oleh platform manajemen pendidikan Eduvia.') }}">
    @if (!empty($school->websiteSetting?->meta_keywords))
        <meta name="keywords" content="{{ $school->websiteSetting->meta_keywords }}">
    @endif

    <!-- Open Graph Metadata -->
    <meta property="og:title" content="{{ $metaTitle ?? $school->name }}">
    <meta property="og:description" content="{{ $metaDescription ?? ($school->websiteSetting?->meta_description ?? 'Situs resmi ' . $school->name) }}">
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ url()->current() }}">
    @if ($school->websiteSetting?->hero_image_path)
        <meta property="og:image" content="{{ $school->websiteSetting->heroImageUrl() }}">
    @elseif ($school->logo_path)
        <meta property="og:image" content="{{ $school->logoUrl() }}">
    @endif

    <!-- Favicon & Brand Icons -->
    <link rel="icon" type="image/x-icon" href="{{ asset('Desain tanpa judul (7).ico') }}">
    <link rel="shortcut icon" type="image/x-icon" href="{{ asset('Desain tanpa judul (7).ico') }}">

    <!-- Google Fonts: Plus Jakarta Sans, Inter & Playfair Display (Editorial High-School Aesthetic) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Playfair+Display:ital,wght@0,400;0,500;0,600;0,700;0,800;1,400;1,600&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Tailwind & Vite Assets -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @php
        $rawTheme = $school->websiteSetting?->theme_color;
        $isValidHex = $rawTheme && preg_match('/^#([a-fA-F0-9]{6}|[a-fA-F0-9]{3})$/', $rawTheme);
        $themeColor = $isValidHex ? $rawTheme : '#E11D2E';
    @endphp

    <link rel="canonical" href="{{ url()->current() }}">
    <link rel="sitemap" type="application/xml" title="Sitemap {{ $school->name }}" href="{{ route('school.public.sitemap', ['school' => $school->slug]) }}">

    <style>
        :root {
            --brand-primary: {{ $themeColor }};
            --brand-primary-hover: {{ $themeColor }};
            --brand-dark: #1F2937;
            --brand-canvas: #F8FAFC;
        }
        body {
            font-family: 'Plus Jakarta Sans', 'Inter', system-ui, -apple-system, sans-serif;
            color: #1F2937;
            background-color: #F8FAFC;
        }
        .font-serif, .font-display {
            font-family: 'Playfair Display', Georgia, Cambria, "Times New Roman", Times, serif;
        }
        .btn-brand-primary {
            background-color: var(--brand-primary) !important;
            color: #ffffff !important;
        }
        .btn-brand-primary:hover {
            filter: brightness(0.9);
        }
        .text-brand-primary {
            color: var(--brand-primary) !important;
        }
        .bg-brand-primary {
            background-color: var(--brand-primary) !important;
        }
        .border-brand-primary {
            border-color: var(--brand-primary) !important;
        }
    </style>
</head>
<body class="min-h-screen flex flex-col antialiased selection:bg-[#E11D2E] selection:text-white" x-data="{ mobileMenuOpen: false }">

    @if (!empty($isPreview) && $isPreview)
        <!-- Protected Preview Notice Banner -->
        <div class="bg-amber-500 text-amber-950 px-4 py-2 text-xs md:text-sm font-semibold flex items-center justify-between sticky top-0 z-50 border-b border-amber-600 shadow-xs">
            <div class="max-w-7xl mx-auto w-full flex items-center justify-between gap-4">
                <div class="flex items-center gap-2">
                    <svg class="w-4 h-4 text-amber-950 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                    </svg>
                    <span><strong>MODE PRATINJAU DRAF:</strong> Anda melihat konten draf yang terlindungi. Publik tidak dapat melihat draf ini.</span>
                </div>
                <a href="{{ route('website.dashboard') }}" class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-amber-950 text-white rounded text-xs hover:bg-black transition-colors shrink-0">
                    <span>Kembali ke CMS</span>
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </a>
            </div>
        </div>
    @elseif (! $school->public_website_enabled)
        <div class="bg-zinc-800 text-zinc-200 px-4 py-2 text-xs text-center sticky top-0 z-50">
            Situs ini sedang dinonaktifkan untuk publik. Terlihat oleh Anda karena Anda login sebagai administrator sekolah.
        </div>
    @endif

    @php
        $isLandingPage = request()->routeIs('home', 'school.public.home');
    @endphp

    <!-- ====================================================================== -->
    <!-- UNIFIED MASTER NAVBAR (EdmunHigh Editorial Aesthetic)                   -->
    <!-- ====================================================================== -->
    <!-- INSTITUTIONAL NAVBAR & HEADER (BALANCED, CLEAN & PRODUCTION-READY)      -->
    <!-- ====================================================================== -->
    <header class="bg-white/95 backdrop-blur-md border-b border-zinc-200/80 sticky top-0 z-40 transition-all duration-200 shadow-2xs">
        <div class="max-w-7xl 2xl:max-w-screen-2xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-18 sm:h-20 lg:h-22 gap-3 sm:gap-4">
                
                <!-- Left: Brand / School Wordmark -->
                <a href="{{ $school->slug === 'nusantara-utama' ? route('home') : route('school.public.home', ['school' => $school->slug]) }}" class="flex items-center gap-2.5 sm:gap-3 shrink-0 py-2 group min-w-0">
                    @php
                        $schoolLogo = (file_exists(public_path('Desain tanpa judul (7).png')) ? asset('Desain tanpa judul (7).png') : null) ?: $school->logoUrl();
                    @endphp
                    @if ($schoolLogo)
                        <img src="{{ $schoolLogo }}" alt="{{ $school->name }}" class="w-9 h-9 sm:w-11 sm:h-11 rounded-xl object-contain bg-white border border-zinc-200/80 p-0.5 shrink-0 group-hover:scale-105 transition-transform shadow-2xs">
                    @else
                        <div class="w-9 h-9 sm:w-11 sm:h-11 rounded-xl bg-[#161F2E] text-white flex items-center justify-center font-bold text-sm sm:text-base shadow-xs shrink-0 group-hover:bg-black transition-colors">
                            {{ substr($school->short_name ?: $school->name, 0, 2) }}
                        </div>
                    @endif
                    <div class="flex flex-col justify-center min-w-0">
                        <span class="font-bold text-xs sm:text-sm md:text-base lg:text-[15px] xl:text-[16px] text-zinc-900 tracking-tight font-sans truncate sm:whitespace-nowrap group-hover:text-zinc-700 transition-colors">
                            {{ $school->name }}
                        </span>
                        <div class="flex items-center gap-1 sm:gap-1.5 mt-0.5">
                            @if ($school->educational_levels && is_array($school->educational_levels))
                                @foreach ($school->educational_levels as $lvl)
                                    <span class="px-1.5 py-0.5 text-[9px] font-bold uppercase tracking-wider rounded-md bg-zinc-100 text-zinc-800 border border-zinc-200/90 shadow-2xs">
                                        {{ $lvl }}
                                    </span>
                                @endforeach
                            @endif
                            <span class="text-[9px] sm:text-[10px] font-semibold uppercase tracking-wider text-zinc-400 hidden md:inline">&bull; Situs Resmi Yayasan</span>
                        </div>
                    </div>
                </a>

                <!-- Center: Navigation Links (Visible on Large Tablet / Desktop lg+) -->
                @if ($isLandingPage)
                    <!-- Landing Page Anchor Links -->
                    <nav class="hidden lg:flex items-center gap-3 xl:gap-5 2xl:gap-8 text-xs xl:text-sm font-medium text-zinc-600">
                        <a href="#about" class="hover:text-zinc-950 transition-colors whitespace-nowrap">About</a>
                        <a href="#programs" class="hover:text-zinc-950 transition-colors whitespace-nowrap">Programs</a>
                        <a href="#community" class="hover:text-zinc-950 transition-colors whitespace-nowrap">Community</a>
                        <a href="#campus-life" class="hover:text-zinc-950 transition-colors whitespace-nowrap">Campus Life</a>
                        <a href="#news" class="hover:text-zinc-950 transition-colors whitespace-nowrap">Blog</a>
                        <a href="#events" class="hover:text-zinc-950 transition-colors whitespace-nowrap">Events</a>
                    </nav>
                @else
                    <!-- Sub-pages Multi-Page Route Links -->
                    <nav class="hidden lg:flex items-center gap-2 xl:gap-3.5 2xl:gap-5 text-xs xl:text-[13px] 2xl:text-sm font-medium text-zinc-600">
                        <a href="{{ $school->slug === 'nusantara-utama' ? route('home') : route('school.public.home', ['school' => $school->slug]) }}" class="transition-colors hover:text-zinc-950 whitespace-nowrap">
                            Beranda
                        </a>
                        <a href="{{ route('school.public.profile', ['school' => $school->slug]) }}" class="transition-colors whitespace-nowrap {{ request()->routeIs('school.public.profile') ? 'text-zinc-950 font-bold border-b-2 border-zinc-900 pb-0.5' : 'hover:text-zinc-950' }}">
                            Profil
                        </a>
                        <a href="{{ route('school.public.articles', ['school' => $school->slug]) }}" class="transition-colors whitespace-nowrap {{ request()->routeIs('school.public.articles', 'school.public.article-detail') ? 'text-zinc-950 font-bold border-b-2 border-zinc-900 pb-0.5' : 'hover:text-zinc-950' }}">
                            Berita
                        </a>
                        <a href="{{ route('school.public.events', ['school' => $school->slug]) }}" class="transition-colors whitespace-nowrap {{ request()->routeIs('school.public.events') ? 'text-zinc-950 font-bold border-b-2 border-zinc-900 pb-0.5' : 'hover:text-zinc-950' }}">
                            Agenda
                        </a>
                        <a href="{{ route('school.public.achievements', ['school' => $school->slug]) }}" class="transition-colors whitespace-nowrap {{ request()->routeIs('school.public.achievements') ? 'text-zinc-950 font-bold border-b-2 border-zinc-900 pb-0.5' : 'hover:text-zinc-950' }}">
                            Prestasi
                        </a>
                        <a href="{{ route('school.public.extracurriculars', ['school' => $school->slug]) }}" class="transition-colors whitespace-nowrap {{ request()->routeIs('school.public.extracurriculars') ? 'text-zinc-950 font-bold border-b-2 border-zinc-900 pb-0.5' : 'hover:text-zinc-950' }}">
                            Ekstrakurikuler
                        </a>
                        <a href="{{ route('school.public.facilities', ['school' => $school->slug]) }}" class="transition-colors whitespace-nowrap {{ request()->routeIs('school.public.facilities') ? 'text-zinc-950 font-bold border-b-2 border-zinc-900 pb-0.5' : 'hover:text-zinc-950' }}">
                            Fasilitas
                        </a>
                        <a href="{{ route('school.public.gallery', ['school' => $school->slug]) }}" class="transition-colors whitespace-nowrap {{ request()->routeIs('school.public.gallery') ? 'text-zinc-950 font-bold border-b-2 border-zinc-900 pb-0.5' : 'hover:text-zinc-950' }}">
                            Galeri
                        </a>
                    </nav>
                @endif

                <!-- Right: Action CTAs (Desktop lg+) -->
                <div class="hidden lg:flex items-center gap-2 xl:gap-2.5 shrink-0">
                    <a 
                        href="{{ route('school.public.contact', ['school' => $school->slug]) }}" 
                        class="inline-flex items-center justify-center px-3.5 xl:px-4 py-2 rounded-full text-xs font-semibold border border-zinc-900 transition-all duration-200 whitespace-nowrap {{ request()->routeIs('school.public.contact') ? 'bg-[#161F2E] text-white shadow-xs' : 'text-zinc-900 hover:bg-zinc-900 hover:text-white' }}"
                    >
                        Contact Us
                    </a>

                    @auth
                        <a href="{{ route('dashboard') }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-full text-xs font-bold bg-[#161F2E] hover:bg-black text-white shadow-xs transition-colors whitespace-nowrap">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>
                            <span>Dashboard</span>
                        </a>
                    @else
                        <a href="{{ route('login') }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-full text-xs font-semibold text-zinc-700 hover:text-zinc-950 hover:bg-zinc-100 transition-colors whitespace-nowrap">
                            <svg class="w-3.5 h-3.5 text-zinc-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/></svg>
                            <span>Masuk Portal</span>
                        </a>
                    @endauth
                </div>

                <!-- Mobile / Tablet Hamburger Button (Visible on < lg) -->
                <div class="flex lg:hidden items-center gap-2">
                    <a 
                        href="{{ route('school.public.contact', ['school' => $school->slug]) }}" 
                        class="hidden sm:inline-flex items-center justify-center px-3 py-1.5 rounded-full text-xs font-semibold border border-zinc-900 transition-all duration-200 whitespace-nowrap {{ request()->routeIs('school.public.contact') ? 'bg-[#161F2E] text-white shadow-xs' : 'text-zinc-900 hover:bg-zinc-900 hover:text-white' }}"
                    >
                        Contact
                    </a>
                    <button
                        type="button"
                        @click="mobileMenuOpen = !mobileMenuOpen"
                        class="p-2 rounded-xl text-zinc-700 hover:text-zinc-950 hover:bg-zinc-100 transition-colors focus:outline-none"
                        aria-label="Menu"
                    >
                        <svg x-show="!mobileMenuOpen" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                        <svg x-show="mobileMenuOpen" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="display: none;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
            </div>
        </div>

        <!-- Mobile & Tablet Navigation Menu Dropdown -->
        <div x-show="mobileMenuOpen" x-transition class="lg:hidden border-t border-zinc-200 bg-white px-4 pt-3 pb-6 space-y-1 text-sm font-medium shadow-lg" style="display: none;">
            @if ($isLandingPage)
                <a @click="mobileMenuOpen = false" href="#about" class="block px-3 py-2 rounded-lg text-zinc-700 hover:bg-zinc-50">About</a>
                <a @click="mobileMenuOpen = false" href="#programs" class="block px-3 py-2 rounded-lg text-zinc-700 hover:bg-zinc-50">Programs</a>
                <a @click="mobileMenuOpen = false" href="#community" class="block px-3 py-2 rounded-lg text-zinc-700 hover:bg-zinc-50">Community</a>
                <a @click="mobileMenuOpen = false" href="#campus-life" class="block px-3 py-2 rounded-lg text-zinc-700 hover:bg-zinc-50">Campus Life</a>
                <a @click="mobileMenuOpen = false" href="#news" class="block px-3 py-2 rounded-lg text-zinc-700 hover:bg-zinc-50">Blog</a>
                <a @click="mobileMenuOpen = false" href="#events" class="block px-3 py-2 rounded-lg text-zinc-700 hover:bg-zinc-50">Events</a>
            @else
                <a @click="mobileMenuOpen = false" href="{{ $school->slug === 'nusantara-utama' ? route('home') : route('school.public.home', ['school' => $school->slug]) }}" class="block px-3 py-2 rounded-lg {{ request()->routeIs('home', 'school.public.home') ? 'bg-zinc-100 text-zinc-900 font-bold' : 'text-zinc-700 hover:bg-zinc-50' }}">
                    Beranda
                </a>
                <a @click="mobileMenuOpen = false" href="{{ route('school.public.profile', ['school' => $school->slug]) }}" class="block px-3 py-2 rounded-lg {{ request()->routeIs('school.public.profile') ? 'bg-zinc-100 text-zinc-900 font-bold' : 'text-zinc-700 hover:bg-zinc-50' }}">
                    Profil Sekolah
                </a>
                <a @click="mobileMenuOpen = false" href="{{ route('school.public.articles', ['school' => $school->slug]) }}" class="block px-3 py-2 rounded-lg {{ request()->routeIs('school.public.articles', 'school.public.article-detail') ? 'bg-zinc-100 text-zinc-900 font-bold' : 'text-zinc-700 hover:bg-zinc-50' }}">
                    Berita & Informasi
                </a>
                <a @click="mobileMenuOpen = false" href="{{ route('school.public.events', ['school' => $school->slug]) }}" class="block px-3 py-2 rounded-lg {{ request()->routeIs('school.public.events') ? 'bg-zinc-100 text-zinc-900 font-bold' : 'text-zinc-700 hover:bg-zinc-50' }}">
                    Agenda & Acara
                </a>
                <a @click="mobileMenuOpen = false" href="{{ route('school.public.achievements', ['school' => $school->slug]) }}" class="block px-3 py-2 rounded-lg {{ request()->routeIs('school.public.achievements') ? 'bg-zinc-100 text-zinc-900 font-bold' : 'text-zinc-700 hover:bg-zinc-50' }}">
                    Prestasi Siswa
                </a>
                <a @click="mobileMenuOpen = false" href="{{ route('school.public.extracurriculars', ['school' => $school->slug]) }}" class="block px-3 py-2 rounded-lg {{ request()->routeIs('school.public.extracurriculars') ? 'bg-zinc-100 text-zinc-900 font-bold' : 'text-zinc-700 hover:bg-zinc-50' }}">
                    Ekstrakurikuler
                </a>
                <a @click="mobileMenuOpen = false" href="{{ route('school.public.facilities', ['school' => $school->slug]) }}" class="block px-3 py-2 rounded-lg {{ request()->routeIs('school.public.facilities') ? 'bg-zinc-100 text-zinc-900 font-bold' : 'text-zinc-700 hover:bg-zinc-50' }}">
                    Fasilitas & Program
                </a>
                <a @click="mobileMenuOpen = false" href="{{ route('school.public.gallery', ['school' => $school->slug]) }}" class="block px-3 py-2 rounded-lg {{ request()->routeIs('school.public.gallery') ? 'bg-zinc-100 text-zinc-900 font-bold' : 'text-zinc-700 hover:bg-zinc-50' }}">
                    Galeri Foto
                </a>
            @endif

            <div class="pt-3 border-t border-zinc-100 flex flex-col gap-2">
                <a @click="mobileMenuOpen = false" href="{{ route('school.public.contact', ['school' => $school->slug]) }}" class="w-full text-center py-2.5 rounded-full font-semibold text-xs border border-zinc-900 transition-colors {{ request()->routeIs('school.public.contact') ? 'bg-[#161F2E] text-white' : 'text-zinc-900 hover:bg-zinc-900 hover:text-white' }}">
                    Contact Us
                </a>
                @auth
                    <a href="{{ route('dashboard') }}" class="w-full text-center py-2.5 rounded-full font-bold text-xs bg-[#161F2E] text-white">
                        Dashboard Eduvia
                    </a>
                @else
                    <a href="{{ route('login') }}" class="w-full text-center py-2.5 rounded-full font-semibold text-xs bg-zinc-100 text-zinc-800 hover:bg-zinc-200">
                        Masuk Portal Eduvia
                    </a>
                @endauth
            </div>
        </div>
    </header>

    <!-- ====================================================================== -->
    <!-- CONTENT SLOT                                                           -->
    <!-- ====================================================================== -->
    <main class="flex-1">
        @yield('content')
    </main>

    <!-- ====================================================================== -->
    <!-- INSTITUTIONAL FOOTER (EDMUNHIGH EDITORIAL & BALANCED DESIGN)            -->
    <!-- ====================================================================== -->
    <footer class="relative bg-[#111827] text-slate-400 text-sm mt-16 border-t border-slate-800/80 overflow-hidden font-sans">
        <!-- Subtle Radial Background Glow -->
        <div class="absolute top-0 right-1/4 w-96 h-96 bg-white/[0.02] rounded-full blur-3xl pointer-events-none"></div>

        <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 lg:py-16">
            
            <!-- Top Horizontal Highlight Band: PPDB & Academic Callout -->
            <div class="border-b border-white/10 pb-10 mb-12 flex flex-col md:flex-row items-start md:items-center justify-between gap-6">
                <div class="space-y-2 max-w-2xl">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/5 border border-white/10 text-xs font-medium text-slate-300">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                        Penerimaan Peserta Didik Baru (PPDB)
                    </div>
                    <h3 class="text-xl sm:text-2xl font-serif font-bold text-white tracking-tight">
                        Bergabung dengan Lingkungan Belajar {{ $school->name }}
                    </h3>
                    <p class="text-xs sm:text-sm text-slate-400 leading-relaxed">
                        Membentuk generasi pembelajar berprestasi, berintegritas, dan siap bersaing di kancah nasional maupun global.
                    </p>
                </div>
                <div class="flex flex-wrap items-center gap-3 shrink-0">
                    <a href="{{ route('ppdb.index') }}" class="px-5 py-2.5 rounded-full text-xs font-semibold bg-white text-[#111827] hover:bg-slate-100 transition-all shadow-xs inline-flex items-center gap-1.5">
                        <span>Kunjungi Portal PPDB</span>
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                    </a>
                    <a href="{{ route('school.public.contact', ['school' => $school->slug]) }}" class="px-5 py-2.5 rounded-full text-xs font-semibold text-white border border-white/20 hover:bg-white/10 transition-colors">
                        Hubungi Sekretariat
                    </a>
                </div>
            </div>

            <!-- Balanced 4-Column Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8 lg:gap-10">
                
                <!-- Col 1: School Identity & Brand -->
                <div class="space-y-4">
                    <div class="flex items-center gap-3">
                        @php
                            $footerLogo = (file_exists(public_path('Desain tanpa judul (7).png')) ? asset('Desain tanpa judul (7).png') : null) ?: $school->logoUrl();
                        @endphp
                        @if ($footerLogo)
                            <img src="{{ $footerLogo }}" alt="{{ $school->name }}" class="w-11 h-11 rounded-xl object-contain bg-white p-1 border border-white/20 shrink-0">
                        @else
                            <div class="w-11 h-11 rounded-xl bg-white/10 border border-white/10 text-white font-bold flex items-center justify-center text-sm">
                                {{ substr($school->short_name ?: $school->name, 0, 2) }}
                            </div>
                        @endif
                        <div class="min-w-0">
                            <h4 class="font-serif font-bold text-white text-base leading-snug">{{ $school->name }}</h4>
                            <span class="inline-block text-[11px] font-medium text-slate-400">Situs Resmi &bull; NPSN: {{ $school->npsn ?? '-' }}</span>
                        </div>
                    </div>
                    <p class="text-xs text-slate-400 leading-relaxed">
                        {{ $school->websiteSetting?->hero_headline ?? 'Lembaga pendidikan unggul dan berkarakter, mengintegrasikan kurikulum berstandar nasional dengan platform pembelajaran digital terpadu.' }}
                    </p>
                    <div class="flex items-center gap-2 pt-1 text-slate-300">
                        @if (!empty($school->websiteSetting?->social_links['facebook']))
                            <a href="{{ $school->websiteSetting->social_links['facebook'] }}" target="_blank" class="w-8 h-8 rounded-full bg-white/5 hover:bg-white/15 text-slate-300 hover:text-white border border-white/10 flex items-center justify-center transition-colors" title="Facebook">
                                <svg class="w-3.5 h-3.5 fill-current" viewBox="0 0 24 24"><path d="M22 12c0-5.523-4.477-10-10-10S2 6.477 2 12c0 4.991 3.657 9.128 8.438 9.878v-6.987h-2.54V12h2.54V9.797c0-2.506 1.492-3.89 3.777-3.89 1.094 0 2.238.195 2.238.195v2.46h-1.26c-1.243 0-1.63.771-1.63 1.562V12h2.773l-.443 2.89h-2.33v6.988C18.343 21.128 22 16.991 22 12z"/></svg>
                            </a>
                        @endif
                        @if (!empty($school->websiteSetting?->social_links['instagram']))
                            <a href="{{ $school->websiteSetting->social_links['instagram'] }}" target="_blank" class="w-8 h-8 rounded-full bg-white/5 hover:bg-white/15 text-slate-300 hover:text-white border border-white/10 flex items-center justify-center transition-colors" title="Instagram">
                                <svg class="w-3.5 h-3.5 fill-current" viewBox="0 0 24 24"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/></svg>
                            </a>
                        @endif
                        @if (!empty($school->websiteSetting?->social_links['youtube']))
                            <a href="{{ $school->websiteSetting->social_links['youtube'] }}" target="_blank" class="w-8 h-8 rounded-full bg-white/5 hover:bg-white/15 text-slate-300 hover:text-white border border-white/10 flex items-center justify-center transition-colors" title="YouTube">
                                <svg class="w-3.5 h-3.5 fill-current" viewBox="0 0 24 24"><path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/></svg>
                            </a>
                        @endif
                    </div>
                </div>

                <!-- Col 2: Profil & Akademik Links -->
                <div class="space-y-3">
                    <h5 class="font-bold text-white text-xs uppercase tracking-wider">Profil & Informasi</h5>
                    <ul class="space-y-2 text-xs">
                        <li>
                            <a href="{{ route('school.public.profile', ['school' => $school->slug]) }}" class="hover:text-white transition-colors inline-flex items-center gap-1.5">
                                <span class="text-slate-600">&rsaquo;</span>
                                <span>Visi, Misi & Kepemimpinan</span>
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('school.public.articles', ['school' => $school->slug]) }}" class="hover:text-white transition-colors inline-flex items-center gap-1.5">
                                <span class="text-slate-600">&rsaquo;</span>
                                <span>Warta & Berita Sekolah</span>
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('school.public.events', ['school' => $school->slug]) }}" class="hover:text-white transition-colors inline-flex items-center gap-1.5">
                                <span class="text-slate-600">&rsaquo;</span>
                                <span>Agenda & Kalender Kegiatan</span>
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('school.public.achievements', ['school' => $school->slug]) }}" class="hover:text-white transition-colors inline-flex items-center gap-1.5">
                                <span class="text-slate-600">&rsaquo;</span>
                                <span>Catatan Prestasi Siswa</span>
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('school.public.gallery', ['school' => $school->slug]) }}" class="hover:text-white transition-colors inline-flex items-center gap-1.5">
                                <span class="text-slate-600">&rsaquo;</span>
                                <span>Dokumentasi & Galeri Foto</span>
                            </a>
                        </li>
                    </ul>
                </div>

                <!-- Col 3: Kesiswaan & Program -->
                <div class="space-y-3">
                    <h5 class="font-bold text-white text-xs uppercase tracking-wider">Kehidupan Sekolah</h5>
                    <ul class="space-y-2 text-xs">
                        <li>
                            <a href="{{ route('school.public.extracurriculars', ['school' => $school->slug]) }}" class="hover:text-white transition-colors inline-flex items-center gap-1.5">
                                <span class="text-slate-600">&rsaquo;</span>
                                <span>Kegiatan Ekstrakurikuler</span>
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('school.public.facilities', ['school' => $school->slug]) }}" class="hover:text-white transition-colors inline-flex items-center gap-1.5">
                                <span class="text-slate-600">&rsaquo;</span>
                                <span>Sarana & Fasilitas Belajar</span>
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('ppdb.index') }}" class="hover:text-white transition-colors inline-flex items-center gap-1.5">
                                <span class="text-slate-600">&rsaquo;</span>
                                <span>Jalur Penerimaan Siswa (PPDB)</span>
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('login') }}" class="hover:text-white transition-colors inline-flex items-center gap-1.5">
                                <span class="text-slate-600">&rsaquo;</span>
                                <span>Portal Pembelajaran (LMS)</span>
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('school.public.contact', ['school' => $school->slug]) }}" class="hover:text-white transition-colors inline-flex items-center gap-1.5">
                                <span class="text-slate-600">&rsaquo;</span>
                                <span>Buku Tamu & Formulir Kontak</span>
                            </a>
                        </li>
                    </ul>
                </div>

                <!-- Col 4: Secretariat & Operational Hours -->
                <div class="space-y-3">
                    <h5 class="font-bold text-white text-xs uppercase tracking-wider">Sekretariat & Layanan</h5>
                    <div class="space-y-2.5 text-xs text-slate-400">
                        <div class="flex items-start gap-2">
                            <svg class="w-4 h-4 text-slate-400 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            <p class="leading-relaxed">
                                {{ $school->address ?? 'Alamat kampus sekolah resmi' }}<br>
                                {{ $school->city ?? '' }}, {{ $school->province ?? '' }} {{ $school->postal_code ?? '' }}
                            </p>
                        </div>
                        @if ($school->phone)
                            <div class="flex items-center gap-2">
                                <svg class="w-4 h-4 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                                <span>{{ $school->phone }}</span>
                            </div>
                        @endif
                        @if ($school->email)
                            <div class="flex items-center gap-2">
                                <svg class="w-4 h-4 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                                <span class="break-all">{{ $school->email }}</span>
                            </div>
                        @endif
                        <div class="pt-2">
                            <span class="block text-[11px] text-slate-500 font-medium mb-1">Jam Operasional:</span>
                            <span class="inline-block px-3 py-1 rounded-md bg-white/5 border border-white/10 text-[11px] text-slate-300">
                                Senin &ndash; Jumat, 07:30 &ndash; 15:30 WIB
                            </span>
                        </div>
                    </div>
                </div>

            </div>

            <!-- Bottom Disclaimer, Copyright & Eduvia Badge -->
            <div class="mt-12 pt-8 border-t border-white/10 flex flex-col sm:flex-row items-center justify-between gap-4 text-xs text-slate-400">
                <p>&copy; {{ date('Y') }} {{ $school->name }}. Hak Cipta Dilindungi Undang-Undang.</p>
                <div class="flex items-center gap-2 text-slate-400">
                    <span>Didukung oleh</span>
                    <a href="{{ url('/') }}" class="inline-flex items-center gap-2 font-bold text-white hover:text-slate-200 transition-colors group">
                        <img src="{{ asset('Desain tanpa judul (7).png') }}" alt="{{ config('app.name', 'Eduvia') }}" class="w-5 h-5 rounded-md object-contain shrink-0 shadow-xs group-hover:scale-105 transition-transform">
                        <span class="tracking-tight text-white font-semibold">Eduvia Platform</span>
                    </a>
                </div>
            </div>
        </div>
    </footer>

</body>
</html>
