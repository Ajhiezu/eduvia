<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-slate-50">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? config('app.name', 'Eduvia') }} — E-Learning Platform Sekolah Indonesia</title>

    <!-- Meta SEO & Access -->
    <meta name="description" content="Eduvia — Fondasi platform e-learning modern untuk jenjang SD, SMP, SMA, dan SMK di Indonesia.">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Favicon & Brand Icons -->
    <link rel="icon" type="image/x-icon" href="{{ asset('Desain tanpa judul (7).ico') }}">
    <link rel="shortcut icon" type="image/x-icon" href="{{ asset('Desain tanpa judul (7).ico') }}">

    <!-- Scripts & Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-full flex flex-col font-sans text-slate-900 bg-slate-50 antialiased selection:bg-brand-500 selection:text-white">

    <!-- Global Toast Component -->
    <x-toast />

    <!-- Top Navigation Bar -->
    <header class="sticky top-0 z-40 bg-white/90 backdrop-blur-md border-b border-slate-200 shadow-2xs">
        <div class="max-w-7xl mx-auto px-3 sm:px-6 lg:px-8 h-16 flex items-center justify-between gap-2 sm:gap-4">
            <a href="{{ route('home') }}" class="flex items-center gap-2 group focus-visible:ring-2 focus-visible:ring-brand-500 rounded-lg shrink-0 max-h-12">
                <x-application-logo size="sm" class="sm:hidden" />
                <x-application-logo size="md" class="hidden sm:inline-flex" />
            </a>

            <nav class="flex items-center gap-1.5 sm:gap-4 shrink-0">
                @if (!request()->routeIs('login'))
                    <a href="{{ route('home') }}" class="text-xs sm:text-sm font-semibold text-slate-600 hover:text-brand-600 transition-colors px-2 py-1.5 rounded-lg">
                        Beranda
                    </a>
                    <a href="{{ route('school.public.home', ['school' => 'nusantara-utama']) }}" class="hidden sm:inline-block text-xs sm:text-sm font-semibold text-slate-600 hover:text-brand-600 transition-colors px-2 py-1.5 rounded-lg">
                        Portal Sekolah
                    </a>
                @endif

                @auth
                    <x-button href="{{ route('dashboard') }}" size="sm" variant="primary">
                        Buka Dashboard
                    </x-button>
                @else
                    @if (!request()->routeIs('login'))
                        <x-button href="{{ route('login') }}" size="sm" variant="outline">
                            Masuk Sistem
                        </x-button>
                    @endif
                @endauth
            </nav>
        </div>
    </header>

    <!-- Main Content -->
    <main class="flex-1 flex flex-col">
        {{ $slot }}
    </main>

    <!-- Institutional Footer -->
    <footer class="bg-white border-t border-slate-200 py-8 text-xs text-slate-500">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col sm:flex-row items-center justify-between gap-4">
            <div class="flex items-center gap-2 text-slate-600 font-medium">
                <span class="w-2 h-2 rounded-full bg-brand-600"></span>
                <span>Eduvia Platform &copy; {{ date('Y') }}. Seluruh Hak Cipta Dilindungi.</span>
            </div>
            <div class="flex items-center gap-4">
                <span class="text-slate-400">Platform E-Learning & Manajemen Sekolah Terpadu</span>
                <span class="text-slate-300">&bull;</span>
                <span class="text-slate-500 font-medium">Jenjang SD &bull; SMP &bull; SMA &bull; SMK</span>
            </div>
        </div>
    </footer>

</body>
</html>
