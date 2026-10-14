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

    <link rel="icon" type="image/x-icon" href="{{ asset('images/brand/eduvia-favicon.png') }}">

    <!-- Google Fonts: Plus Jakarta Sans & Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@500;600;700;800&display=swap" rel="stylesheet">

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

    <!-- ====================================================================== -->
    <!-- TOP NOTIFICATION & ACCREDITATION STRIP                                 -->
    <!-- ====================================================================== -->
    <div class="bg-zinc-900 text-zinc-300 text-[11px] sm:text-xs py-1.5 px-4 border-b border-zinc-800">
        <div class="max-w-7xl mx-auto flex flex-col sm:flex-row items-center justify-between gap-1.5">
            <div class="flex items-center gap-3 truncate">
                @if ($school->npsn)
                    <span class="inline-flex items-center gap-1">
                        <span class="text-zinc-400">NPSN:</span>
                        <span class="font-medium text-white">{{ $school->npsn }}</span>
                    </span>
                @endif
                @if ($school->websiteSetting?->accreditation_grade)
                    <span class="hidden sm:inline text-zinc-600">&bull;</span>
                    <span class="inline-flex items-center gap-1">
                        <span class="text-zinc-400">Akreditasi:</span>
                        <span class="font-bold text-emerald-400">{{ $school->websiteSetting->accreditation_grade }}</span>
                    </span>
                @endif
                @if ($school->city)
                    <span class="hidden md:inline text-zinc-600">&bull;</span>
                    <span class="hidden md:inline text-zinc-400">{{ $school->city }}, {{ $school->province }}</span>
                @endif
            </div>
            <div class="flex items-center gap-3">
                @if ($school->phone)
                    <a href="tel:{{ $school->phone }}" class="hover:text-white transition-colors flex items-center gap-1">
                        <svg class="w-3 h-3 text-zinc-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                        <span>{{ $school->phone }}</span>
                    </a>
                @endif
                @if ($school->email)
                    <a href="mailto:{{ $school->email }}" class="hidden sm:inline hover:text-white transition-colors">
                        {{ $school->email }}
                    </a>
                @endif
            </div>
        </div>
    </div>

    <!-- ====================================================================== -->
    <!-- MAIN NAVBAR                                                            -->
    <!-- ====================================================================== -->
    <header class="bg-white/95 backdrop-blur-md border-b border-zinc-200/80 sticky top-0 z-40 transition-shadow duration-200 shadow-2xs">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-20">
                
                <!-- School Brand Identity -->
                <a href="{{ route('school.public.home', ['school' => $school->slug]) }}" class="flex items-center gap-3.5 group min-w-0 py-2">
                    @if ($school->logo_path)
                        <img src="{{ $school->logoUrl() }}" alt="{{ $school->name }}" class="w-12 h-12 rounded-xl object-contain bg-zinc-50 border border-zinc-200/80 p-1 shrink-0 group-hover:scale-105 transition-transform">
                    @else
                        <div class="w-12 h-12 rounded-xl bg-[#E11D2E] text-white flex items-center justify-center font-bold text-xl shadow-xs shrink-0 group-hover:bg-[#C71827] transition-colors">
                            {{ substr($school->short_name ?: $school->name, 0, 2) }}
                        </div>
                    @endif
                    <div class="min-w-0">
                        <span class="block font-extrabold text-base sm:text-lg text-zinc-900 leading-tight truncate group-hover:text-[#E11D2E] transition-colors">
                            {{ $school->name }}
                        </span>
                        <div class="flex items-center gap-1.5 mt-0.5">
                            @if ($school->educational_levels && is_array($school->educational_levels))
                                @foreach ($school->educational_levels as $lvl)
                                    <span class="px-1.5 py-0.2 text-[10px] font-bold uppercase rounded bg-zinc-100 text-zinc-600 border border-zinc-200">
                                        {{ $lvl }}
                                    </span>
                                @endforeach
                            @endif
                            <span class="text-[11px] text-zinc-400 hidden sm:inline">&bull; Situs Resmi Sekolah</span>
                        </div>
                    </div>
                </a>

                <!-- Desktop Navigation Links -->
                <nav class="hidden lg:flex items-center gap-1 text-sm font-medium text-zinc-700">
                    <a href="{{ route('school.public.home', ['school' => $school->slug]) }}" class="px-3 py-2 rounded-lg transition-colors {{ request()->routeIs('school.public.home') ? 'text-[#E11D2E] font-bold bg-rose-50' : 'hover:text-zinc-900 hover:bg-zinc-100' }}">
                        Beranda
                    </a>
                    <a href="{{ route('school.public.profile', ['school' => $school->slug]) }}" class="px-3 py-2 rounded-lg transition-colors {{ request()->routeIs('school.public.profile') ? 'text-[#E11D2E] font-bold bg-rose-50' : 'hover:text-zinc-900 hover:bg-zinc-100' }}">
                        Profil
                    </a>
                    <a href="{{ route('school.public.articles', ['school' => $school->slug]) }}" class="px-3 py-2 rounded-lg transition-colors {{ request()->routeIs('school.public.articles', 'school.public.article-detail') ? 'text-[#E11D2E] font-bold bg-rose-50' : 'hover:text-zinc-900 hover:bg-zinc-100' }}">
                        Berita
                    </a>
                    <a href="{{ route('school.public.events', ['school' => $school->slug]) }}" class="px-3 py-2 rounded-lg transition-colors {{ request()->routeIs('school.public.events') ? 'text-[#E11D2E] font-bold bg-rose-50' : 'hover:text-zinc-900 hover:bg-zinc-100' }}">
                        Agenda
                    </a>
                    <a href="{{ route('school.public.achievements', ['school' => $school->slug]) }}" class="px-3 py-2 rounded-lg transition-colors {{ request()->routeIs('school.public.achievements') ? 'text-[#E11D2E] font-bold bg-rose-50' : 'hover:text-zinc-900 hover:bg-zinc-100' }}">
                        Prestasi
                    </a>
                    <a href="{{ route('school.public.extracurriculars', ['school' => $school->slug]) }}" class="px-3 py-2 rounded-lg transition-colors {{ request()->routeIs('school.public.extracurriculars') ? 'text-[#E11D2E] font-bold bg-rose-50' : 'hover:text-zinc-900 hover:bg-zinc-100' }}">
                        Ekstrakurikuler
                    </a>
                    <a href="{{ route('school.public.facilities', ['school' => $school->slug]) }}" class="px-3 py-2 rounded-lg transition-colors {{ request()->routeIs('school.public.facilities') ? 'text-[#E11D2E] font-bold bg-rose-50' : 'hover:text-zinc-900 hover:bg-zinc-100' }}">
                        Fasilitas
                    </a>
                    <a href="{{ route('school.public.gallery', ['school' => $school->slug]) }}" class="px-3 py-2 rounded-lg transition-colors {{ request()->routeIs('school.public.gallery') ? 'text-[#E11D2E] font-bold bg-rose-50' : 'hover:text-zinc-900 hover:bg-zinc-100' }}">
                        Galeri
                    </a>
                </nav>

                <!-- Action CTAs: PPDB & Portal Login -->
                <div class="hidden sm:flex items-center gap-2.5 shrink-0">
                    <a href="{{ route('ppdb.index') }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-xs font-bold bg-emerald-600 hover:bg-emerald-700 text-white shadow-xs transition-colors">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                        <span>Info PPDB</span>
                    </a>

                    @auth
                        <a href="{{ route('dashboard') }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-xs font-bold bg-[#E11D2E] hover:bg-[#C71827] text-white shadow-xs transition-colors">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>
                            <span>Dashboard Saya</span>
                        </a>
                    @else
                        <a href="{{ route('login') }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-xs font-bold border border-zinc-300 hover:border-zinc-400 bg-white hover:bg-zinc-50 text-zinc-800 transition-colors">
                            <svg class="w-3.5 h-3.5 text-zinc-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/></svg>
                            <span>Masuk Portal</span>
                        </a>
                    @endauth
                </div>

                <!-- Mobile Hamburger Button -->
                <button
                    type="button"
                    @click="mobileMenuOpen = !mobileMenuOpen"
                    class="lg:hidden p-2 rounded-xl text-zinc-600 hover:text-zinc-900 hover:bg-zinc-100 transition-colors"
                    aria-label="Menu"
                >
                    <svg x-show="!mobileMenuOpen" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                    <svg x-show="mobileMenuOpen" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="display: none;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
        </div>

        <!-- Mobile Navigation Menu -->
        <div x-show="mobileMenuOpen" x-transition class="lg:hidden border-t border-zinc-200 bg-white px-4 pt-3 pb-6 space-y-1 text-sm font-medium shadow-lg" style="display: none;">
            <a href="{{ route('school.public.home', ['school' => $school->slug]) }}" class="block px-3 py-2 rounded-lg {{ request()->routeIs('school.public.home') ? 'bg-rose-50 text-[#E11D2E] font-bold' : 'text-zinc-700 hover:bg-zinc-50' }}">
                Beranda
            </a>
            <a href="{{ route('school.public.profile', ['school' => $school->slug]) }}" class="block px-3 py-2 rounded-lg {{ request()->routeIs('school.public.profile') ? 'bg-rose-50 text-[#E11D2E] font-bold' : 'text-zinc-700 hover:bg-zinc-50' }}">
                Profil Sekolah
            </a>
            <a href="{{ route('school.public.articles', ['school' => $school->slug]) }}" class="block px-3 py-2 rounded-lg {{ request()->routeIs('school.public.articles', 'school.public.article-detail') ? 'bg-rose-50 text-[#E11D2E] font-bold' : 'text-zinc-700 hover:bg-zinc-50' }}">
                Berita & Informasi
            </a>
            <a href="{{ route('school.public.events', ['school' => $school->slug]) }}" class="block px-3 py-2 rounded-lg {{ request()->routeIs('school.public.events') ? 'bg-rose-50 text-[#E11D2E] font-bold' : 'text-zinc-700 hover:bg-zinc-50' }}">
                Agenda & Acara
            </a>
            <a href="{{ route('school.public.achievements', ['school' => $school->slug]) }}" class="block px-3 py-2 rounded-lg {{ request()->routeIs('school.public.achievements') ? 'bg-rose-50 text-[#E11D2E] font-bold' : 'text-zinc-700 hover:bg-zinc-50' }}">
                Prestasi Siswa
            </a>
            <a href="{{ route('school.public.extracurriculars', ['school' => $school->slug]) }}" class="block px-3 py-2 rounded-lg {{ request()->routeIs('school.public.extracurriculars') ? 'bg-rose-50 text-[#E11D2E] font-bold' : 'text-zinc-700 hover:bg-zinc-50' }}">
                Ekstrakurikuler
            </a>
            <a href="{{ route('school.public.facilities', ['school' => $school->slug]) }}" class="block px-3 py-2 rounded-lg {{ request()->routeIs('school.public.facilities') ? 'bg-rose-50 text-[#E11D2E] font-bold' : 'text-zinc-700 hover:bg-zinc-50' }}">
                Fasilitas & Program
            </a>
            <a href="{{ route('school.public.gallery', ['school' => $school->slug]) }}" class="block px-3 py-2 rounded-lg {{ request()->routeIs('school.public.gallery') ? 'bg-rose-50 text-[#E11D2E] font-bold' : 'text-zinc-700 hover:bg-zinc-50' }}">
                Galeri Foto
            </a>

            <div class="pt-4 border-t border-zinc-100 flex flex-col gap-2">
                <a href="{{ route('ppdb.index') }}" class="w-full text-center py-2.5 rounded-xl font-bold text-xs bg-emerald-600 text-white hover:bg-emerald-700">
                    Informasi PPDB Online
                </a>
                @auth
                    <a href="{{ route('dashboard') }}" class="w-full text-center py-2.5 rounded-xl font-bold text-xs bg-[#E11D2E] text-white hover:bg-[#C71827]">
                        Buka Dashboard Eduvia
                    </a>
                @else
                    <a href="{{ route('login') }}" class="w-full text-center py-2.5 rounded-xl font-bold text-xs border border-zinc-300 text-zinc-800 hover:bg-zinc-50">
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
    <!-- INSTITUTIONAL FOOTER                                                   -->
    <!-- ====================================================================== -->
    <footer class="bg-zinc-900 text-zinc-400 text-sm mt-16 border-t border-zinc-800">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 lg:py-16">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8 lg:gap-12">
                
                <!-- Col 1: School Identity -->
                <div class="space-y-4">
                    <div class="flex items-center gap-3">
                        @if ($school->logo_path)
                            <img src="{{ $school->logoUrl() }}" alt="{{ $school->name }}" class="w-10 h-10 rounded-lg object-contain bg-zinc-800 p-1">
                        @else
                            <div class="w-10 h-10 rounded-lg bg-[#E11D2E] text-white font-bold flex items-center justify-center">
                                {{ substr($school->short_name ?: $school->name, 0, 2) }}
                            </div>
                        @endif
                        <div>
                            <h3 class="font-bold text-white text-base leading-tight">{{ $school->name }}</h3>
                            <span class="text-xs text-zinc-400">NPSN: {{ $school->npsn ?? '-' }}</span>
                        </div>
                    </div>
                    <p class="text-xs text-zinc-400 leading-relaxed">
                        {{ $school->websiteSetting?->hero_headline ?? 'Lembaga pendidikan unggul dan berkarakter, mengintegrasikan kurikulum berstandar nasional dengan platform pembelajaran digital terpadu.' }}
                    </p>
                    <div class="flex items-center gap-3 text-white">
                        @if (!empty($school->websiteSetting?->social_links['facebook']))
                            <a href="{{ $school->websiteSetting->social_links['facebook'] }}" target="_blank" class="p-2 rounded-lg bg-zinc-800 hover:bg-[#E11D2E] transition-colors" title="Facebook">
                                <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24"><path d="M22 12c0-5.523-4.477-10-10-10S2 6.477 2 12c0 4.991 3.657 9.128 8.438 9.878v-6.987h-2.54V12h2.54V9.797c0-2.506 1.492-3.89 3.777-3.89 1.094 0 2.238.195 2.238.195v2.46h-1.26c-1.243 0-1.63.771-1.63 1.562V12h2.773l-.443 2.89h-2.33v6.988C18.343 21.128 22 16.991 22 12z"/></svg>
                            </a>
                        @endif
                        @if (!empty($school->websiteSetting?->social_links['instagram']))
                            <a href="{{ $school->websiteSetting->social_links['instagram'] }}" target="_blank" class="p-2 rounded-lg bg-zinc-800 hover:bg-[#E11D2E] transition-colors" title="Instagram">
                                <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/></svg>
                            </a>
                        @endif
                        @if (!empty($school->websiteSetting?->social_links['youtube']))
                            <a href="{{ $school->websiteSetting->social_links['youtube'] }}" target="_blank" class="p-2 rounded-lg bg-zinc-800 hover:bg-[#E11D2E] transition-colors" title="YouTube">
                                <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24"><path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/></svg>
                            </a>
                        @endif
                    </div>
                </div>

                <!-- Col 2: Navigation Links -->
                <div class="space-y-3">
                    <h4 class="font-bold text-white text-sm uppercase tracking-wider">Jelajahi Sekolah</h4>
                    <ul class="space-y-2 text-xs">
                        <li><a href="{{ route('school.public.profile', ['school' => $school->slug]) }}" class="hover:text-white transition-colors">Visi, Misi & Sambutan Kepala Sekolah</a></li>
                        <li><a href="{{ route('school.public.articles', ['school' => $school->slug]) }}" class="hover:text-white transition-colors">Warta & Berita Terkini</a></li>
                        <li><a href="{{ route('school.public.events', ['school' => $school->slug]) }}" class="hover:text-white transition-colors">Kalender Agenda & Acara</a></li>
                        <li><a href="{{ route('school.public.achievements', ['school' => $school->slug]) }}" class="hover:text-white transition-colors">Catatan Prestasi Siswa</a></li>
                        <li><a href="{{ route('school.public.extracurriculars', ['school' => $school->slug]) }}" class="hover:text-white transition-colors">Kegiatan Ekstrakurikuler</a></li>
                        <li><a href="{{ route('school.public.facilities', ['school' => $school->slug]) }}" class="hover:text-white transition-colors">Fasilitas & Sarana Belajar</a></li>
                        <li><a href="{{ route('school.public.gallery', ['school' => $school->slug]) }}" class="hover:text-white transition-colors">Dokumentasi & Galeri Foto</a></li>
                    </ul>
                </div>

                <!-- Col 3: Contact & Location -->
                <div class="space-y-3">
                    <h4 class="font-bold text-white text-sm uppercase tracking-wider">Kontak & Alamat</h4>
                    <div class="space-y-2 text-xs text-zinc-400">
                        <p class="leading-relaxed">
                            <strong class="text-zinc-300">Alamat:</strong><br>
                            {{ $school->address ?? 'Alamat sekolah terdaftar' }}<br>
                            {{ $school->city ?? '' }}, {{ $school->province ?? '' }} {{ $school->postal_code ?? '' }}
                        </p>
                        @if ($school->phone)
                            <p><strong class="text-zinc-300">Telepon:</strong> {{ $school->phone }}</p>
                        @endif
                        @if ($school->email)
                            <p><strong class="text-zinc-300">Email:</strong> {{ $school->email }}</p>
                        @endif
                    </div>
                </div>

                <!-- Col 4: Platform Eduvia & PPDB CTA -->
                <div class="space-y-4">
                    <h4 class="font-bold text-white text-sm uppercase tracking-wider">Platform Eduvia</h4>
                    <p class="text-xs text-zinc-400 leading-relaxed">
                        Website publik ini terintegrasi langsung dengan ekosistem Learning Management System (LMS) dan Administrasi Sekolah Eduvia.
                    </p>
                    <div class="p-3.5 rounded-xl bg-zinc-800/80 border border-zinc-700/60">
                        <span class="text-[11px] font-bold text-emerald-400 block mb-1">Penerimaan Siswa Baru</span>
                        <p class="text-xs text-zinc-300 mb-2.5">Daftarkan putra-putri Anda melalui portal pendaftaran resmi.</p>
                        <a href="{{ route('ppdb.index') }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-bold bg-emerald-600 hover:bg-emerald-500 text-white transition-colors">
                            <span>Kunjungi Portal PPDB</span>
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                        </a>
                    </div>
                </div>

            </div>

            <!-- Bottom Disclaimer & Copyright -->
            <div class="mt-12 pt-8 border-t border-zinc-800 flex flex-col sm:flex-row items-center justify-between gap-4 text-xs">
                <p>&copy; {{ date('Y') }} {{ $school->name }}. Hak Cipta Dilindungi Undang-Undang.</p>
                <div class="flex items-center gap-2 text-zinc-400">
                    <span>Didukung oleh</span>
                    <a href="{{ url('/') }}" class="inline-flex items-center gap-1.5 font-bold text-white hover:text-[#E11D2E] transition-colors">
                        <img src="{{ asset('images/brand/eduvia-icon-mark-transparent.png') }}" alt="Eduvia" class="h-4 w-auto">
                        <span>Eduvia Platform</span>
                    </a>
                </div>
            </div>
        </div>
    </footer>

</body>
</html>
