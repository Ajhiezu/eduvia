<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $metaTitle ?? ($school->name . ' | Sekolah untuk Masa Depan') }}</title>
    <meta name="description" content="{{ $metaDescription ?? ($school->websiteSetting?->meta_description ?: 'Kenali ' . $school->name . '. Temukan program pendidikan, kehidupan sekolah, berita, dan informasi penerimaan peserta didik.') }}">
    @if (!empty($school->websiteSetting?->meta_keywords))
        <meta name="keywords" content="{{ $school->websiteSetting->meta_keywords }}">
    @endif
    <meta property="og:title" content="{{ $metaTitle ?? $school->name }}">
    <meta property="og:description" content="{{ $metaDescription ?? ($school->websiteSetting?->meta_description ?: 'Situs resmi ' . $school->name) }}">
    <meta property="og:type" content="website">
    <meta name="twitter:card" content="summary_large_image">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:image" content="{{ $school->websiteSetting?->heroImageUrl() ?: ($school->logoUrl() ?: asset('images/brand/eduvia-logo-horizontal.png')) }}">
    <link rel="canonical" href="{{ url()->current() }}">
    <link rel="sitemap" type="application/xml" title="Sitemap {{ $school->name }}" href="{{ route('school.public.sitemap', ['school' => $school->slug]) }}">
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <link rel="preload" href="{{ asset('fonts/plus-jakarta-sans-400_800-normal.woff2') }}" as="font" type="font/woff2" crossorigin>
    @if (request()->routeIs('home', 'school.public.home') && ! $school->websiteSetting?->heroImageUrl())
        <link rel="preload" as="image" href="{{ asset('images/edmunhigh/hero-students-web.webp') }}" imagesrcset="{{ asset('images/edmunhigh/hero-students-mobile.webp') }} 800w, {{ asset('images/edmunhigh/hero-students-web.webp') }} 1200w" imagesizes="(max-width: 767px) calc(100vw - 40px), (max-width: 1312px) 50vw, 640px" fetchpriority="high">
    @endif
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @php
        $rawTheme = $school->websiteSetting?->theme_color;
        $isValidHex = $rawTheme && preg_match('/^#([a-fA-F0-9]{6}|[a-fA-F0-9]{3})$/', $rawTheme);
        $themeColor = $isValidHex ? $rawTheme : '#E11D2E';
        $schoolHomeUrl = $school->slug === 'nusantara-utama' ? route('home') : route('school.public.home', ['school' => $school->slug]);
        $schoolLogo = $school->slug === 'nusantara-utama' ? asset('images/brand/eduvia-icon-mark-web.webp') : ($school->logoUrl() ?: asset('images/brand/eduvia-icon-mark-web.webp'));
        $isLandingPage = request()->routeIs('home', 'school.public.home');
        $navigation = $isLandingPage ? [
            ['label' => 'Beranda', 'url' => $schoolHomeUrl, 'active' => true],
            ['label' => 'Tentang kami', 'url' => '#about', 'active' => false],
            ['label' => 'Pendidikan', 'url' => '#programs', 'active' => false],
            ['label' => 'Kehidupan sekolah', 'url' => '#campus-life', 'active' => false],
            ['label' => 'Berita', 'url' => '#news', 'active' => false],
        ] : [
            ['label' => 'Beranda', 'url' => $schoolHomeUrl, 'active' => false],
            ['label' => 'Profil', 'url' => route('school.public.profile', ['school' => $school->slug]), 'active' => request()->routeIs('school.public.profile')],
            ['label' => 'Berita', 'url' => route('school.public.articles', ['school' => $school->slug]), 'active' => request()->routeIs('school.public.articles', 'school.public.article-detail')],
            ['label' => 'Agenda', 'url' => route('school.public.events', ['school' => $school->slug]), 'active' => request()->routeIs('school.public.events')],
            ['label' => 'Galeri', 'url' => route('school.public.gallery', ['school' => $school->slug]), 'active' => request()->routeIs('school.public.gallery')],
        ];
    @endphp
    <style>
        @font-face { font-family: 'Plus Jakarta Sans'; src: url('{{ asset('fonts/plus-jakarta-sans-400_800-normal.woff2') }}') format('woff2'); font-weight: 400 800; font-style: normal; font-display: swap; }
        @font-face { font-family: 'Inter'; src: url('{{ asset('fonts/inter-400_700-normal.woff2') }}') format('woff2'); font-weight: 400 700; font-style: normal; font-display: swap; }
        :root { --brand-primary: {{ $themeColor }}; --brand-primary-hover: {{ $themeColor }}; --brand-dark: #1F2937; --brand-canvas: #F3F4F6; }
        .btn-brand-primary { background-color: var(--brand-primary) !important; color: #fff !important; }
        .btn-brand-primary:hover { filter: brightness(.9); }
        .text-brand-primary { color: var(--brand-primary) !important; }
        .bg-brand-primary { background-color: var(--brand-primary) !important; }
        .border-brand-primary { border-color: var(--brand-primary) !important; }
    </style>
</head>
<body class="school-site antialiased" x-data="{ mobileMenuOpen: false }" @keydown.escape.window="if (mobileMenuOpen) { mobileMenuOpen = false; $refs.menuToggle.focus(); }">
    <a class="school-skip-link" href="#main-content">Langsung ke konten</a>
    @if (!empty($isPreview) && $isPreview)
        <div class="school-preview"><strong>MODE PRATINJAU DRAF:</strong> Konten draf ini hanya dapat dilihat administrator. <a href="{{ route('website.dashboard') }}">Kembali ke CMS</a></div>
    @elseif (! $school->public_website_enabled)
        <div class="school-preview">Situs dinonaktifkan untuk publik. Anda memiliki akses sebagai administrator sekolah.</div>
    @endif

    <div class="school-utility"><div class="school-container"><span>Situs Resmi {{ $school->short_name ?: $school->name }}</span><div><a href="{{ route('school.public.events', ['school' => $school->slug]) }}">Kalender sekolah</a><span aria-hidden="true">/</span><a href="{{ route('school.public.contact', ['school' => $school->slug]) }}">Hubungi kami</a></div></div></div>
    <header class="school-header">
        <div class="school-container school-header-inner">
            <a href="{{ $schoolHomeUrl }}" class="school-brand"><img src="{{ $schoolLogo }}" alt="" width="48" height="48"><span><strong>{{ $school->name }}</strong><small>Belajar · Bertumbuh · Membangun masa depan</small></span></a>
            <nav class="school-desktop-nav" aria-label="Navigasi utama">
                @foreach ($navigation as $item)
                    <a href="{{ $item['url'] }}" @if ($item['active']) aria-current="page" @endif>{{ $item['label'] }}</a>
                @endforeach
            </nav>
            <div class="school-header-actions"><x-button :href="auth()->check() ? route('dashboard') : route('login')" size="sm">{{ auth()->check() ? 'Dashboard' : 'Masuk portal' }}</x-button></div>
            <button type="button" class="school-menu-toggle" x-ref="menuToggle" @click="mobileMenuOpen = !mobileMenuOpen" :aria-expanded="mobileMenuOpen.toString()" aria-controls="school-mobile-nav" aria-label="Buka atau tutup menu navigasi"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true"><path x-show="!mobileMenuOpen" d="M4 7h16M4 12h16M4 17h16"/><path x-cloak x-show="mobileMenuOpen" d="m6 6 12 12M6 18 18 6"/></svg></button>
        </div>
        <nav id="school-mobile-nav" class="school-mobile-nav school-container" aria-label="Navigasi seluler" x-cloak x-show="mobileMenuOpen" @click.outside="mobileMenuOpen = false" x-transition.opacity>
            @foreach ($navigation as $item)
                <a href="{{ $item['url'] }}" @click="mobileMenuOpen = false" @if ($item['active']) aria-current="page" @endif>{{ $item['label'] }}</a>
            @endforeach
            <a href="{{ route('school.public.events', ['school' => $school->slug]) }}">Agenda sekolah</a>
            <a href="{{ route('school.public.contact', ['school' => $school->slug]) }}">Hubungi sekolah</a>
        </nav>
    </header>

    <main id="main-content" tabindex="-1">@yield('content')</main>

    <footer class="school-footer"><div class="school-container">
        <div class="footer-grid">
            <div class="footer-identity"><a href="{{ $schoolHomeUrl }}" class="school-brand"><img src="{{ $schoolLogo }}" alt="" width="48" height="48"><span><strong>{{ $school->name }}</strong><small>Pendidikan yang menumbuhkan potensi.</small></span></a><p>Ruang belajar dan bertumbuh bersama. Mendampingi setiap langkah menuju masa depan.</p><div class="footer-eduvia"><span>Ekosistem pendidikan oleh</span><img src="{{ asset('images/brand/eduvia-logo-horizontal-web.webp') }}" alt="Eduvia" width="112" height="40" loading="lazy"></div></div>
            <div><h2>Jelajahi sekolah</h2><a href="{{ route('school.public.profile', ['school' => $school->slug]) }}">Profil, visi & misi</a><a href="{{ route('school.public.facilities', ['school' => $school->slug]) }}">Fasilitas belajar</a><a href="{{ route('school.public.extracurriculars', ['school' => $school->slug]) }}">Ekstrakurikuler</a><a href="{{ route('school.public.achievements', ['school' => $school->slug]) }}">Prestasi siswa</a><a href="{{ route('school.public.gallery', ['school' => $school->slug]) }}">Galeri sekolah</a></div>
            <div><h2>Informasi & layanan</h2><a href="{{ route('ppdb.index') }}">Penerimaan siswa baru</a><a href="{{ route('ppdb.check-status') }}">Status pendaftaran</a><a href="{{ route('school.public.articles', ['school' => $school->slug]) }}">Berita sekolah</a><a href="{{ route('school.public.events', ['school' => $school->slug]) }}">Kalender kegiatan</a><a href="{{ route('login') }}">Portal pembelajaran</a></div>
            <div><h2>Mari terhubung</h2>
                @if ($school->address)<p>{{ $school->address }}<br>{{ implode(', ', array_filter([$school->city, $school->province])) }} {{ $school->postal_code }}</p>@endif
                @if ($school->phone)<a href="tel:{{ preg_replace('/[^+0-9]/', '', $school->phone) }}">{{ $school->phone }}</a>@endif
                @if ($school->email)<a href="mailto:{{ $school->email }}">{{ $school->email }}</a>@endif
                <a class="school-text-link" href="{{ route('school.public.contact', ['school' => $school->slug]) }}">Hubungi sekretariat</a>
                <div class="footer-socials">@foreach (['instagram' => 'Instagram', 'facebook' => 'Facebook', 'youtube' => 'YouTube'] as $key => $label)@if (!empty($school->websiteSetting?->social_links[$key]))<a href="{{ $school->websiteSetting->social_links[$key] }}" target="_blank" rel="noopener noreferrer">{{ $label }}<span class="sr-only"> (tab baru)</span></a>@endif @endforeach</div>
            </div>
        </div>
        <div class="footer-bottom"><p>&copy; {{ date('Y') }} {{ $school->name }}. Seluruh hak cipta dilindungi.</p><span>Learn · Grow · Build Your Future</span></div>
    </div></footer>
</body>
</html>
