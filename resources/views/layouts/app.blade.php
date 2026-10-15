<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-zinc-50">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? 'Dashboard' }} — {{ config('app.name', 'Eduvia') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Favicon & Brand Icons -->
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('images/brand/eduvia-app-icon-light.png') }}">

    <!-- Scripts & Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body
    x-data="{
        sidebarOpen: false,
        sidebarCollapsed: false,
        userDropdownOpen: false,
        notificationsOpen: false,
        isFullscreen: false,
        toggleFullscreen() {
            if (!document.fullscreenElement) {
                document.documentElement.requestFullscreen().then(() => this.isFullscreen = true).catch(() => {});
            } else {
                document.exitFullscreen().then(() => this.isFullscreen = false).catch(() => {});
            }
        }
    }"
    class="min-h-full flex flex-col font-sans text-zinc-900 bg-zinc-50 antialiased selection:bg-brand-600 selection:text-white"
    x-init="$watch('sidebarOpen', val => document.body.classList.toggle('overflow-hidden', val))"
    @keydown.escape="userDropdownOpen = false; notificationsOpen = false; sidebarOpen = false"
>
    <!-- Global Notification Toasts -->
    <x-toast />

    @php
        $currentSchool = \App\Models\School::current();
        $activeYear = $currentSchool?->activeAcademicYear;
        $activeSemester = $activeYear?->activeSemester;
    @endphp

    <!-- ====================================================================== -->
    <!-- METIS MASTER TOP HEADER (Fixed Across Entire Screen)                  -->
    <!-- ====================================================================== -->
    <header class="fixed top-0 inset-x-0 z-40 h-16 bg-white border-b border-zinc-200/90 px-4 sm:px-6 flex items-center justify-between gap-4">
        <!-- Left: Brand Logo + Sidebar Toggle -->
        <div class="flex items-center gap-3 shrink-0">
            <!-- Mobile Drawer Toggle -->
            <button
                type="button"
                @click="sidebarOpen = !sidebarOpen; if (sidebarOpen) sidebarCollapsed = false"
                class="p-2 rounded-lg text-zinc-500 hover:text-zinc-800 hover:bg-zinc-100 lg:hidden focus:outline-none"
                aria-label="Toggle navigation drawer"
            >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                </svg>
            </button>

            <!-- Brand Logo -->
            <a href="{{ route('dashboard') }}" class="flex items-center gap-2 focus:outline-none group">
                <x-application-logo size="sm" subtext="Platform E-Learning" />
            </a>

            <!-- Desktop Collapse Toggle -->
            <button
                type="button"
                @click="sidebarCollapsed = !sidebarCollapsed"
                class="hidden lg:flex p-1.5 ml-2 rounded-lg text-zinc-400 hover:text-zinc-700 hover:bg-zinc-100 transition-colors focus:outline-none"
                title="Sembunyikan / Luaskan Navigasi"
            >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7"/>
                </svg>
            </button>
        </div>

        <!-- Center: Metis Omnibox Global Search -->
        <div class="hidden md:flex flex-1 max-w-md mx-4">
            <div class="relative w-full">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-zinc-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </div>
                <input
                    type="search"
                    placeholder="Cari di Eduvia... (Ctrl+K)"
                    class="w-full pl-9 pr-14 py-2 text-xs sm:text-sm bg-zinc-50/80 hover:bg-white focus:bg-white text-zinc-800 placeholder-zinc-400 border border-zinc-200 rounded-lg transition-colors focus:border-brand-600 focus:ring-2 focus:ring-brand-100 focus:outline-none"
                    @keydown.window.prevent.ctrl.k="$el.focus()"
                    @keydown.window.prevent.meta.k="$el.focus()"
                >
                <div class="absolute inset-y-0 right-0 pr-2.5 flex items-center pointer-events-none">
                    <kbd class="px-1.5 py-0.5 text-[10px] font-semibold text-zinc-400 bg-white border border-zinc-200 rounded shadow-2xs">Ctrl K</kbd>
                </div>
            </div>
        </div>

        <!-- Right: Metis Quick Utilities & User Menu -->
        <div class="flex items-center gap-2 sm:gap-3 shrink-0">
            <!-- School Operational Context Pill -->
            <div class="hidden xl:flex items-center gap-2 px-3 py-1.5 rounded-full bg-zinc-100/80 border border-zinc-200 text-xs text-zinc-700">
                <span class="w-2 h-2 rounded-full {{ $currentSchool?->is_active ? 'bg-emerald-500' : 'bg-zinc-400' }}"></span>
                <span class="font-semibold text-zinc-800 truncate max-w-[150px]" title="{{ $currentSchool?->name }}">
                    {{ $currentSchool?->name ?? 'SMP/SMA Nusantara Utama' }}
                </span>
                <span class="text-zinc-300">|</span>
                <span class="text-zinc-500 text-[11px]">
                    {{ $activeYear?->name ?? '2025/2026' }} &bull; {{ $activeSemester?->name ?? 'Ganjil' }}
                </span>
            </div>

            <!-- Fullscreen Toggle Button -->
            <button
                type="button"
                @click="toggleFullscreen()"
                class="hidden sm:inline-flex p-2 rounded-lg text-zinc-500 hover:text-zinc-800 hover:bg-zinc-100 transition-colors focus:outline-none"
                title="Beralih Layar Penuh"
            >
                <svg x-show="!isFullscreen" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"/>
                </svg>
                <svg x-show="isFullscreen" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="display: none;">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>

            <!-- Notifications Dropdown -->
            <div class="relative" x-data="{ open: false }">
                <button
                    type="button"
                    @click="open = !open"
                    @click.outside="open = false"
                    class="relative p-2 rounded-lg text-zinc-500 hover:text-zinc-800 hover:bg-zinc-100 transition-colors focus:outline-none"
                    aria-label="Pemberitahuan"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                    </svg>
                    @if (auth()->check() && auth()->user()->unreadNotifications->count() > 0)
                        <span class="absolute top-1 right-1 w-2.5 h-2.5 rounded-full bg-brand-600 ring-2 ring-white animate-pulse"></span>
                    @endif
                </button>

                <!-- Notifications Dropdown Menu -->
                <div
                    x-show="open"
                    x-transition:enter="transition ease-out duration-100"
                    x-transition:enter-start="opacity-0 scale-95"
                    x-transition:enter-end="opacity-100 scale-100"
                    x-transition:leave="transition ease-in duration-75"
                    x-transition:leave-start="opacity-100 scale-100"
                    x-transition:leave-end="opacity-0 scale-95"
                    class="absolute right-0 mt-2 w-80 bg-white rounded-xl border border-zinc-200 shadow-xl py-2 z-50 text-xs"
                    style="display: none;"
                >
                    <div class="px-4 py-2 border-b border-zinc-100 font-bold text-zinc-900 flex items-center justify-between">
                        <span>Pemberitahuan</span>
                        @if (auth()->check() && auth()->user()->unreadNotifications->count() > 0)
                            <span class="text-[10px] font-bold px-1.5 py-0.5 rounded-full bg-rose-50 text-brand-600">
                                {{ auth()->user()->unreadNotifications->count() }} baru
                            </span>
                        @endif
                    </div>
                    <div class="py-1 max-h-72 overflow-y-auto">
                        @if (auth()->check() && auth()->user()->unreadNotifications->isNotEmpty())
                            @foreach (auth()->user()->unreadNotifications->take(4) as $notif)
                                <form action="{{ route('notifications.read', $notif->id) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="w-full text-left px-4 py-2.5 hover:bg-zinc-50 border-b border-zinc-50 transition-colors block">
                                        <p class="font-semibold text-zinc-800 line-clamp-1">{{ $notif->data['title'] ?? 'Pemberitahuan Eduvia' }}</p>
                                        <p class="text-[11px] text-zinc-500 mt-0.5 line-clamp-2">{{ $notif->data['message'] ?? '' }}</p>
                                        <span class="text-[10px] text-zinc-400 mt-1 block">{{ $notif->created_at->diffForHumans() }}</span>
                                    </button>
                                </form>
                            @endforeach
                        @else
                            <div class="px-4 py-6 text-center text-zinc-400">
                                <svg class="w-8 h-8 mx-auto mb-1.5 text-zinc-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                                <p class="text-xs text-zinc-500 font-medium">Tidak ada pemberitahuan baru</p>
                            </div>
                        @endif
                    </div>
                    <div class="px-4 pt-2 border-t border-zinc-100 flex items-center justify-between text-[11px]">
                        <a href="{{ route('notifications.index') }}" class="font-bold text-brand-600 hover:text-brand-700">Lihat Semua</a>
                        @if (auth()->check() && auth()->user()->unreadNotifications->count() > 0)
                            <form action="{{ route('notifications.mark-all-read') }}" method="POST">
                                @csrf
                                <button type="submit" class="text-zinc-500 hover:text-zinc-800">Tandai semua dibaca</button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>

            <!-- User Menu Dropdown -->
            @auth
                <div class="relative" x-data="{ open: false }">
                    <button
                        type="button"
                        @click="open = !open"
                        @click.outside="open = false"
                        class="flex items-center gap-2 p-1.5 rounded-lg hover:bg-zinc-100 transition-colors focus:outline-none"
                    >
                        <!-- User Initial Avatar with Eduvia Red Accent -->
                        <div class="w-8 h-8 rounded-lg bg-brand-600 text-white font-bold flex items-center justify-center text-xs shadow-2xs shrink-0">
                            {{ substr(auth()->user()->name, 0, 1) }}
                        </div>
                        <div class="hidden sm:block text-left pr-1">
                            <p class="text-xs font-bold text-zinc-800 leading-tight truncate max-w-[120px]">{{ auth()->user()->name }}</p>
                            <p class="text-[10px] text-zinc-400 leading-tight">{{ auth()->user()->role->label() }}</p>
                        </div>
                        <svg class="w-3.5 h-3.5 text-zinc-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </button>

                    <!-- Dropdown Content -->
                    <div
                        x-show="open"
                        x-transition:enter="transition ease-out duration-100"
                        x-transition:enter-start="opacity-0 scale-95"
                        x-transition:enter-end="opacity-100 scale-100"
                        x-transition:leave="transition ease-in duration-75"
                        x-transition:leave-start="opacity-100 scale-100"
                        x-transition:leave-end="opacity-0 scale-95"
                        class="absolute right-0 mt-2 w-56 bg-white rounded-xl border border-zinc-200 shadow-lg py-1.5 z-50 text-xs"
                        style="display: none;"
                    >
                        <div class="px-4 py-2.5 border-b border-zinc-100">
                            <p class="font-bold text-zinc-900 truncate">{{ auth()->user()->name }}</p>
                            <p class="text-[11px] text-zinc-500 truncate mt-0.5">{{ auth()->user()->email }}</p>
                            <div class="mt-1.5">
                                <x-badge :variant="auth()->user()->role->badgeVariant()" size="sm">
                                    {{ auth()->user()->role->label() }}
                                </x-badge>
                            </div>
                        </div>

                        <div class="py-1">
                            <a href="{{ route('dashboard') }}" class="flex items-center gap-2 px-4 py-2 text-zinc-700 hover:bg-zinc-50 hover:text-zinc-900 transition-colors">
                                <svg class="w-4 h-4 text-zinc-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                                </svg>
                                <span>Dashboard</span>
                            </a>

                            @if (auth()->user()->hasRole(\App\Enums\UserRole::SUPER_ADMIN, \App\Enums\UserRole::SCHOOL_ADMIN))
                                <a href="{{ route('school.profile') }}" class="flex items-center gap-2 px-4 py-2 text-zinc-700 hover:bg-zinc-50 hover:text-zinc-900 transition-colors">
                                    <svg class="w-4 h-4 text-zinc-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                                    </svg>
                                    <span>Profil Sekolah</span>
                                </a>

                                <a href="{{ route('settings.system') }}" class="flex items-center gap-2 px-4 py-2 text-zinc-700 hover:bg-zinc-50 hover:text-zinc-900 transition-colors">
                                    <svg class="w-4 h-4 text-zinc-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    </svg>
                                    <span>Pengaturan Sistem</span>
                                </a>
                            @elseif (auth()->user()->hasRole(\App\Enums\UserRole::TEACHER))
                                <a href="{{ route('learning-materials.index') }}" class="flex items-center gap-2 px-4 py-2 text-zinc-700 hover:bg-zinc-50 hover:text-zinc-900 transition-colors">
                                    <svg class="w-4 h-4 text-zinc-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                                    </svg>
                                    <span>Materi Belajar</span>
                                </a>

                                <a href="{{ route('assignments.index') }}" class="flex items-center gap-2 px-4 py-2 text-zinc-700 hover:bg-zinc-50 hover:text-zinc-900 transition-colors">
                                    <svg class="w-4 h-4 text-zinc-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                                    </svg>
                                    <span>Tugas & Penilaian</span>
                                </a>

                                <a href="{{ route('attendance-sessions.index') }}" class="flex items-center gap-2 px-4 py-2 text-zinc-700 hover:bg-zinc-50 hover:text-zinc-900 transition-colors">
                                    <svg class="w-4 h-4 text-zinc-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                                    </svg>
                                    <span>Sesi Presensi</span>
                                </a>

                                <a href="{{ route('attendance.report') }}" class="flex items-center gap-2 px-4 py-2 text-zinc-700 hover:bg-zinc-50 hover:text-zinc-900 transition-colors">
                                    <svg class="w-4 h-4 text-zinc-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                    </svg>
                                    <span>Rekap Presensi</span>
                                </a>
                            @elseif (auth()->user()->hasRole(\App\Enums\UserRole::STUDENT))
                                <a href="{{ route('student.materials.index') }}" class="flex items-center gap-2 px-4 py-2 text-zinc-700 hover:bg-zinc-50 hover:text-zinc-900 transition-colors">
                                    <svg class="w-4 h-4 text-zinc-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                                    </svg>
                                    <span>Materi Pelajaran</span>
                                </a>

                                <a href="{{ route('student.assignments.index') }}" class="flex items-center gap-2 px-4 py-2 text-zinc-700 hover:bg-zinc-50 hover:text-zinc-900 transition-colors">
                                    <svg class="w-4 h-4 text-zinc-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                                    </svg>
                                    <span>Tugas & Evaluasi</span>
                                </a>

                                <a href="{{ route('attendance.my') }}" class="flex items-center gap-2 px-4 py-2 text-zinc-700 hover:bg-zinc-50 hover:text-zinc-900 transition-colors">
                                    <svg class="w-4 h-4 text-zinc-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    <span>Presensi Saya</span>
                                </a>
                            @endif

                            @if (auth()->user()->hasRole(\App\Enums\UserRole::SUPER_ADMIN, \App\Enums\UserRole::SCHOOL_ADMIN))
                                <a href="{{ route('design-system') }}" class="flex items-center gap-2 px-4 py-2 text-zinc-700 hover:bg-zinc-50 hover:text-zinc-900 transition-colors">
                                    <svg class="w-4 h-4 text-zinc-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01"/>
                                    </svg>
                                    <span>Design System</span>
                                </a>
                            @endif
                        </div>

                        <div class="pt-1 border-t border-zinc-100">
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="w-full flex items-center gap-2 px-4 py-2 text-rose-600 hover:bg-rose-50 text-left font-semibold transition-colors">
                                    <svg class="w-4 h-4 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                                    </svg>
                                    <span>Keluar dari Akun</span>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            @endauth
        </div>
    </header>

    <!-- ====================================================================== -->
    <!-- MOBILE BACKDROP OVERLAY                                                -->
    <!-- ====================================================================== -->
    <div
        x-show="sidebarOpen"
        x-transition:enter="transition-opacity ease-linear duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition-opacity ease-linear duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 z-50 bg-zinc-900/60 backdrop-blur-xs lg:hidden"
        @click="sidebarOpen = false"
        style="display: none;"
    ></div>

    <!-- ====================================================================== -->
    <!-- METIS SIDEBAR NAVIGATION (Fixed Full Height on Mobile, Beneath Top Header on Desktop) -->
    <!-- ====================================================================== -->
    <aside
        :class="{
            'translate-x-0': sidebarOpen,
            '-translate-x-full lg:translate-x-0': !sidebarOpen,
            'lg:w-20': sidebarCollapsed,
            'lg:w-64': !sidebarCollapsed
        }"
        class="fixed top-0 lg:top-16 left-0 bottom-0 z-50 lg:z-30 w-72 sm:w-80 lg:w-64 max-w-[85vw] bg-white border-r border-zinc-200 flex flex-col transition-all duration-200 shadow-2xl lg:shadow-none"
    >
        <!-- Mobile Drawer Header with Logo & Close Button -->
        <div class="lg:hidden flex items-center justify-between px-4 h-16 border-b border-zinc-200 bg-white shrink-0">
            <x-application-logo size="sm" />
            <button
                type="button"
                @click="sidebarOpen = false"
                class="p-2 rounded-lg text-zinc-500 hover:text-zinc-800 hover:bg-zinc-100 focus:outline-none"
                aria-label="Tutup navigasi"
            >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        <!-- Sidebar School Context Summary -->
        <div class="p-3 border-b border-zinc-100 bg-zinc-50/50" x-show="!sidebarCollapsed">
            <div class="flex items-center justify-between text-zinc-500 mb-1">
                <span class="font-bold text-[10px] uppercase tracking-wider text-zinc-400">Konteks Sekolah</span>
                <span class="w-1.5 h-1.5 rounded-full {{ $currentSchool?->is_active ? 'bg-emerald-500' : 'bg-zinc-300' }}"></span>
            </div>
            <div class="font-bold text-zinc-800 text-xs truncate" title="{{ $currentSchool?->name }}">
                {{ $currentSchool?->name ?? 'SMP/SMA Nusantara Utama' }}
            </div>
            <div class="text-[10px] text-zinc-500 mt-0.5 truncate">
                {{ $activeYear?->name ?? 'Tahun Ajaran' }} &bull; {{ $activeSemester?->name ?? 'Fase 1' }}
            </div>
        </div>

        <!-- Navigation Scrollable Links -->
        <nav
            class="flex-1 px-3 py-3 space-y-1 overflow-y-auto"
            @click="if (window.innerWidth < 1024 && $event.target.closest('a')) sidebarOpen = false"
        >
            <!-- Section: Menu Utama -->
            <div class="pt-1 pb-1 px-2" x-show="!sidebarCollapsed">
                <span class="text-[10px] font-bold text-zinc-400 uppercase tracking-wider">Menu Utama</span>
            </div>

            <a
                href="{{ route('dashboard') }}"
                class="metis-nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}"
                :title="sidebarCollapsed ? 'Dashboard' : ''"
            >
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                </svg>
                <span x-show="!sidebarCollapsed" class="truncate">Dashboard</span>
            </a>

            <!-- Section: Modul 1 (Konfigurasi Sekolah & Sistem) -->
            @if (auth()->check() && auth()->user()->hasRole(\App\Enums\UserRole::SUPER_ADMIN, \App\Enums\UserRole::SCHOOL_ADMIN))
                <div class="pt-4 pb-1 px-2" x-show="!sidebarCollapsed">
                    <span class="text-[10px] font-bold text-zinc-400 uppercase tracking-wider">Modul 1: Sekolah & Sistem</span>
                </div>

                <a
                    href="{{ route('school.profile') }}"
                    class="metis-nav-item {{ request()->routeIs('school.*') ? 'active' : '' }}"
                    :title="sidebarCollapsed ? 'Profil Sekolah' : ''"
                >
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                    </svg>
                    <span x-show="!sidebarCollapsed" class="truncate">Profil Sekolah</span>
                </a>

                <a
                    href="{{ route('academic-years.index') }}"
                    class="metis-nav-item {{ request()->routeIs('academic-years.*') ? 'active' : '' }}"
                    :title="sidebarCollapsed ? 'Tahun Ajaran' : ''"
                >
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    <span x-show="!sidebarCollapsed" class="truncate">Tahun Ajaran</span>
                </a>

                <a
                    href="{{ route('semesters.index') }}"
                    class="metis-nav-item {{ request()->routeIs('semesters.*') ? 'active' : '' }}"
                    :title="sidebarCollapsed ? 'Semester' : ''"
                >
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                    <span x-show="!sidebarCollapsed" class="truncate">Semester</span>
                </a>

                <a
                    href="{{ route('settings.system') }}"
                    class="metis-nav-item {{ request()->routeIs('settings.*') ? 'active' : '' }}"
                    :title="sidebarCollapsed ? 'Pengaturan Sistem' : ''"
                >
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                    <span x-show="!sidebarCollapsed" class="truncate">Pengaturan Sistem</span>
                </a>

                <!-- Section: Modul 6 (Struktur Pembelajaran) -->
                <div class="pt-4 pb-1 px-2" x-show="!sidebarCollapsed">
                    <span class="text-[10px] font-bold text-zinc-400 uppercase tracking-wider">Struktur Belajar</span>
                </div>

                <a
                    href="{{ route('classrooms.index') }}"
                    class="metis-nav-item {{ request()->routeIs('classrooms.*') ? 'active' : '' }}"
                    :title="sidebarCollapsed ? 'Rombongan Belajar' : ''"
                >
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                    </svg>
                    <span x-show="!sidebarCollapsed" class="truncate">Rombongan Belajar</span>
                </a>

                <a
                    href="{{ route('schedules.index') }}"
                    class="metis-nav-item {{ request()->routeIs('schedules.*') ? 'active' : '' }}"
                    :title="sidebarCollapsed ? 'Jadwal Pelajaran' : ''"
                >
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span x-show="!sidebarCollapsed" class="truncate">Jadwal Pelajaran</span>
                </a>

                <a
                    href="{{ route('subjects.index') }}"
                    class="metis-nav-item {{ request()->routeIs('subjects.*') ? 'active' : '' }}"
                    :title="sidebarCollapsed ? 'Mata Pelajaran' : ''"
                >
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                    </svg>
                    <span x-show="!sidebarCollapsed" class="truncate">Mata Pelajaran</span>
                </a>

                <!-- Section: Kepegawaian (Pendidik & Staf) -->
                <div class="pt-4 pb-1 px-2" x-show="!sidebarCollapsed">
                    <span class="text-[10px] font-bold text-zinc-400 uppercase tracking-wider">Kepegawaian</span>
                </div>

                <a
                    href="{{ route('staff.index') }}"
                    class="metis-nav-item {{ request()->routeIs('staff.index', 'staff.create', 'staff.show', 'staff.edit') ? 'active' : '' }}"
                    :title="sidebarCollapsed ? 'Guru & Tenaga Kependidikan' : ''"
                >
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                    <span x-show="!sidebarCollapsed" class="truncate">Guru & Staf (PTK)</span>
                </a>

                <a
                    href="{{ route('staff.import.index') }}"
                    class="metis-nav-item {{ request()->routeIs('staff.import.*') ? 'active' : '' }}"
                    :title="sidebarCollapsed ? 'Impor Massal PTK' : ''"
                >
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                    </svg>
                    <span x-show="!sidebarCollapsed" class="truncate">Impor Massal PTK</span>
                </a>

                <!-- Section: Modul 17 (Manajemen Kesiswaan) -->
                <div class="pt-4 pb-1 px-2" x-show="!sidebarCollapsed">
                    <span class="text-[10px] font-bold text-zinc-400 uppercase tracking-wider">Kesiswaan</span>
                </div>

                <a
                    href="{{ route('students.index') }}"
                    class="metis-nav-item {{ request()->routeIs('students.index', 'students.create', 'students.show', 'students.edit') ? 'active' : '' }}"
                    :title="sidebarCollapsed ? 'Data Siswa' : ''"
                >
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                    </svg>
                    <span x-show="!sidebarCollapsed" class="truncate">Data Siswa</span>
                </a>

                <a
                    href="{{ route('students.import.index') }}"
                    class="metis-nav-item {{ request()->routeIs('students.import.*') ? 'active' : '' }}"
                    :title="sidebarCollapsed ? 'Impor Siswa (CSV)' : ''"
                >
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                    </svg>
                    <span x-show="!sidebarCollapsed" class="truncate">Impor Siswa (CSV)</span>
                </a>

                <a
                    href="{{ route('students.promotion.index') }}"
                    class="metis-nav-item {{ request()->routeIs('students.promotion.*') ? 'active' : '' }}"
                    :title="sidebarCollapsed ? 'Kenaikan & Kelulusan' : ''"
                >
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                    </svg>
                    <span x-show="!sidebarCollapsed" class="truncate">Kenaikan & Kelulusan</span>
                </a>

                <a
                    href="{{ route('students.report.index') }}"
                    class="metis-nav-item {{ request()->routeIs('students.report.*') ? 'active' : '' }}"
                    :title="sidebarCollapsed ? 'Laporan Kesiswaan' : ''"
                >
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                    <span x-show="!sidebarCollapsed" class="truncate">Laporan Kesiswaan</span>
                </a>

                <!-- Section: Modul 25 (PPDB / SPMB) -->
                <div class="pt-4 pb-1 px-2" x-show="!sidebarCollapsed">
                    <span class="text-[10px] font-bold text-zinc-400 uppercase tracking-wider">PPDB / SPMB</span>
                </div>

                <a
                    href="{{ route('admissions.dashboard') }}"
                    class="metis-nav-item {{ request()->routeIs('admissions.dashboard') ? 'active' : '' }}"
                    :title="sidebarCollapsed ? 'Dashboard PPDB' : ''"
                >
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                    <span x-show="!sidebarCollapsed" class="truncate">Dashboard PPDB</span>
                </a>

                <a
                    href="{{ route('admissions.periods.index') }}"
                    class="metis-nav-item {{ request()->routeIs('admissions.periods.*') ? 'active' : '' }}"
                    :title="sidebarCollapsed ? 'Periode PPDB' : ''"
                >
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    <span x-show="!sidebarCollapsed" class="truncate">Periode Pendaftaran</span>
                </a>

                <a
                    href="{{ route('admissions.applicants.index') }}"
                    class="metis-nav-item {{ request()->routeIs('admissions.applicants.*') ? 'active' : '' }}"
                    :title="sidebarCollapsed ? 'Calon Siswa' : ''"
                >
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                    <span x-show="!sidebarCollapsed" class="truncate">Calon Siswa (PPDB)</span>
                </a>
            @endif

            <!-- Section: CMS Website Sekolah -->
            @if (auth()->check() && auth()->user()->hasRole(\App\Enums\UserRole::SUPER_ADMIN, \App\Enums\UserRole::SCHOOL_ADMIN))
                <div class="pt-4 pb-1 px-2" x-show="!sidebarCollapsed">
                    <span class="text-[10px] font-bold text-zinc-400 uppercase tracking-wider">Website Sekolah</span>
                </div>

                <a
                    href="{{ route('website.dashboard') }}"
                    class="metis-nav-item {{ request()->routeIs('website.dashboard') ? 'active' : '' }}"
                    :title="sidebarCollapsed ? 'Ringkasan CMS' : ''"
                >
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                    </svg>
                    <span x-show="!sidebarCollapsed" class="truncate">Ringkasan Website</span>
                </a>

                <a
                    href="{{ route('website.settings') }}"
                    class="metis-nav-item {{ request()->routeIs('website.settings') ? 'active' : '' }}"
                    :title="sidebarCollapsed ? 'Pengaturan Website' : ''"
                >
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                    <span x-show="!sidebarCollapsed" class="truncate">Pengaturan & Profil</span>
                </a>

                <a
                    href="{{ route('website.posts.index') }}"
                    class="metis-nav-item {{ request()->routeIs('website.posts.*') ? 'active' : '' }}"
                    :title="sidebarCollapsed ? 'Berita & Konten' : ''"
                >
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"/>
                    </svg>
                    <span x-show="!sidebarCollapsed" class="truncate">Berita, Acara, Prestasi</span>
                </a>

                <a
                    href="{{ route('website.extracurriculars.index') }}"
                    class="metis-nav-item {{ request()->routeIs('website.extracurriculars.*') ? 'active' : '' }}"
                    :title="sidebarCollapsed ? 'Ekstrakurikuler' : ''"
                >
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                    <span x-show="!sidebarCollapsed" class="truncate">Ekstrakurikuler</span>
                </a>

                <a
                    href="{{ route('website.facilities.index') }}"
                    class="metis-nav-item {{ request()->routeIs('website.facilities.*') ? 'active' : '' }}"
                    :title="sidebarCollapsed ? 'Fasilitas & Program' : ''"
                >
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                    </svg>
                    <span x-show="!sidebarCollapsed" class="truncate">Fasilitas & Program</span>
                </a>

                <a
                    href="{{ route('website.galleries.index') }}"
                    class="metis-nav-item {{ request()->routeIs('website.galleries.*') ? 'active' : '' }}"
                    :title="sidebarCollapsed ? 'Galeri Foto' : ''"
                >
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    <span x-show="!sidebarCollapsed" class="truncate">Galeri Media</span>
                </a>

                <a
                    href="{{ route('website.messages.index') }}"
                    class="metis-nav-item {{ request()->routeIs('website.messages.*') ? 'active' : '' }}"
                    :title="sidebarCollapsed ? 'Pesan Pengunjung' : ''"
                >
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                    </svg>
                    <span x-show="!sidebarCollapsed" class="truncate">Pesan & Buku Tamu</span>
                </a>
            @endif

            <!-- Section: Modul Keuangan Sekolah (Fase 9) -->
            @if (auth()->check() && auth()->user()->hasRole(\App\Enums\UserRole::SUPER_ADMIN, \App\Enums\UserRole::SCHOOL_ADMIN, \App\Enums\UserRole::STAFF))
                <div class="pt-4 pb-1 px-2" x-show="!sidebarCollapsed">
                    <span class="text-[10px] font-bold text-zinc-400 uppercase tracking-wider">Keuangan Sekolah</span>
                </div>

                <a
                    href="{{ route('finance.invoices.index') }}"
                    class="metis-nav-item {{ request()->routeIs('finance.invoices.*') ? 'active' : '' }}"
                    :title="sidebarCollapsed ? 'Tagihan Siswa' : ''"
                >
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z"/>
                    </svg>
                    <span x-show="!sidebarCollapsed" class="truncate">Tagihan Siswa</span>
                </a>

                <a
                    href="{{ route('finance.payments.index') }}"
                    class="metis-nav-item {{ request()->routeIs('finance.payments.index') || request()->routeIs('finance.payments.create') ? 'active' : '' }}"
                    :title="sidebarCollapsed ? 'Riwayat Pembayaran' : ''"
                >
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                    <span x-show="!sidebarCollapsed" class="truncate">Riwayat Pembayaran</span>
                </a>

                <a
                    href="{{ route('finance.payments.queue') }}"
                    class="metis-nav-item {{ request()->routeIs('finance.payments.queue') ? 'active' : '' }}"
                    :title="sidebarCollapsed ? 'Verifikasi Pembayaran' : ''"
                >
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span x-show="!sidebarCollapsed" class="truncate">Verifikasi Pembayaran</span>
                </a>

                <a
                    href="{{ route('finance.categories.index') }}"
                    class="metis-nav-item {{ request()->routeIs('finance.categories.*') ? 'active' : '' }}"
                    :title="sidebarCollapsed ? 'Tarif & Beasiswa' : ''"
                >
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span x-show="!sidebarCollapsed" class="truncate">Tarif & Beasiswa</span>
                </a>

                <a
                    href="{{ route('finance.reports.index') }}"
                    class="metis-nav-item {{ request()->routeIs('finance.reports.*') ? 'active' : '' }}"
                    :title="sidebarCollapsed ? 'Laporan Keuangan' : ''"
                >
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    <span x-show="!sidebarCollapsed" class="truncate">Laporan Keuangan</span>
                </a>
            @endif

            <!-- Section: Operasional Sekolah (Phase 10) for Admins & Staff -->
            @if (auth()->check() && auth()->user()->hasRole(\App\Enums\UserRole::SUPER_ADMIN, \App\Enums\UserRole::SCHOOL_ADMIN, \App\Enums\UserRole::STAFF))
                <div class="pt-4 pb-1 px-2" x-show="!sidebarCollapsed">
                    <span class="text-[10px] font-bold text-zinc-400 uppercase tracking-wider">Operasional Sekolah</span>
                </div>

                <a
                    href="{{ route('operations.dashboard') }}"
                    class="metis-nav-item {{ request()->routeIs('operations.dashboard') ? 'active' : '' }}"
                    :title="sidebarCollapsed ? 'Pusat Operasional' : ''"
                >
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    </svg>
                    <span x-show="!sidebarCollapsed" class="truncate">Pusat Operasional</span>
                </a>

                <a
                    href="{{ route('operations.library.books.index') }}"
                    class="metis-nav-item {{ request()->routeIs('operations.library.*') ? 'active' : '' }}"
                    :title="sidebarCollapsed ? 'Perpustakaan' : ''"
                >
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                    </svg>
                    <span x-show="!sidebarCollapsed" class="truncate">Perpustakaan</span>
                </a>

                <a
                    href="{{ route('operations.inventory.assets.index') }}"
                    class="metis-nav-item {{ request()->routeIs('operations.inventory.*') ? 'active' : '' }}"
                    :title="sidebarCollapsed ? 'Aset & Sarpras' : ''"
                >
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                    </svg>
                    <span x-show="!sidebarCollapsed" class="truncate">Aset & Sarpras</span>
                </a>

                <a
                    href="{{ route('operations.facilities.rooms.index') }}"
                    class="metis-nav-item {{ request()->routeIs('operations.facilities.*') ? 'active' : '' }}"
                    :title="sidebarCollapsed ? 'Fasilitas & Lab' : ''"
                >
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                    </svg>
                    <span x-show="!sidebarCollapsed" class="truncate">Fasilitas & Lab</span>
                </a>

                <a
                    href="{{ route('operations.correspondence.index') }}"
                    class="metis-nav-item {{ request()->routeIs('operations.correspondence.*') ? 'active' : '' }}"
                    :title="sidebarCollapsed ? 'Persuratan TU' : ''"
                >
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                    </svg>
                    <span x-show="!sidebarCollapsed" class="truncate">Persuratan TU</span>
                </a>
            @endif


            <!-- Section: Pembelajaran & Tugas for Teachers and Admins -->
            @if (auth()->check() && auth()->user()->hasRole(\App\Enums\UserRole::SUPER_ADMIN, \App\Enums\UserRole::SCHOOL_ADMIN, \App\Enums\UserRole::TEACHER))
                <div class="pt-4 pb-1 px-2" x-show="!sidebarCollapsed">
                    <span class="text-[10px] font-bold text-zinc-400 uppercase tracking-wider">Pembelajaran & Tugas</span>
                </div>

                <a
                    href="{{ route('teacher.schedule.index') }}"
                    class="metis-nav-item {{ request()->routeIs('teacher.schedule.*') ? 'active' : '' }}"
                    :title="sidebarCollapsed ? 'Jadwal Mengajar' : ''"
                >
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span x-show="!sidebarCollapsed" class="truncate">Jadwal Mengajar</span>
                </a>

                <a
                    href="{{ route('learning-materials.index') }}"
                    class="metis-nav-item {{ request()->routeIs('learning-materials.*') ? 'active' : '' }}"
                    :title="sidebarCollapsed ? 'Materi Belajar' : ''"
                >
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                    </svg>
                    <span x-show="!sidebarCollapsed" class="truncate">Materi Belajar</span>
                </a>

                <a
                    href="{{ route('assignments.index') }}"
                    class="metis-nav-item {{ request()->routeIs('assignments.*') ? 'active' : '' }}"
                    :title="sidebarCollapsed ? 'Tugas & Penilaian' : ''"
                >
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                    </svg>
                    <span x-show="!sidebarCollapsed" class="truncate">Tugas & Penilaian</span>
                </a>

                <!-- Section: Modul CBT & Bank Soal -->
                <div class="pt-4 pb-1 px-2" x-show="!sidebarCollapsed">
                    <span class="text-[10px] font-bold text-zinc-400 uppercase tracking-wider">Ujian CBT & Asesmen</span>
                </div>

                <a
                    href="{{ route('exams.index') }}"
                    class="metis-nav-item {{ request()->routeIs('exams.*') ? 'active' : '' }}"
                    :title="sidebarCollapsed ? 'Ujian CBT' : ''"
                >
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                    </svg>
                    <span x-show="!sidebarCollapsed" class="truncate">Ujian CBT</span>
                </a>

                <a
                    href="{{ route('exam-questions.index') }}"
                    class="metis-nav-item {{ request()->routeIs('exam-questions.*') ? 'active' : '' }}"
                    :title="sidebarCollapsed ? 'Bank Soal' : ''"
                >
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                    </svg>
                    <span x-show="!sidebarCollapsed" class="truncate">Bank Soal</span>
                </a>

                <!-- Section: Modul 11 (Presensi & Kehadiran) for Teachers and Admins -->
                <div class="pt-4 pb-1 px-2" x-show="!sidebarCollapsed">
                    <span class="text-[10px] font-bold text-zinc-400 uppercase tracking-wider">Presensi Belajar</span>
                </div>

                <a
                    href="{{ route('attendance-sessions.index') }}"
                    class="metis-nav-item {{ request()->routeIs('attendance-sessions.*') ? 'active' : '' }}"
                    :title="sidebarCollapsed ? 'Sesi Presensi' : ''"
                >
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                    </svg>
                    <span x-show="!sidebarCollapsed" class="truncate">Sesi Presensi</span>
                </a>

                <a
                    href="{{ route('attendance.report') }}"
                    class="metis-nav-item {{ request()->routeIs('attendance.report') ? 'active' : '' }}"
                    :title="sidebarCollapsed ? 'Rekap Presensi' : ''"
                >
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    <span x-show="!sidebarCollapsed" class="truncate">Rekap Presensi</span>
                </a>

                <!-- Section: E-Rapor & Penilaian Semester -->
                <div class="pt-4 pb-1 px-2" x-show="!sidebarCollapsed">
                    <span class="text-[10px] font-bold text-zinc-400 uppercase tracking-wider">E-Rapor & Penilaian</span>
                </div>

                <a
                    href="{{ route('reports.gradebook.index') }}"
                    class="metis-nav-item {{ request()->routeIs('reports.gradebook.*') || request()->routeIs('reports.weights.*') ? 'active' : '' }}"
                    :title="sidebarCollapsed ? 'Buku Nilai (Gradebook)' : ''"
                >
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                    </svg>
                    <span x-show="!sidebarCollapsed" class="truncate">Buku Nilai Mapel</span>
                </a>

                <a
                    href="{{ route('reports.cards.index') }}"
                    class="metis-nav-item {{ request()->routeIs('reports.cards.*') ? 'active' : '' }}"
                    :title="sidebarCollapsed ? 'E-Rapor Kelas' : ''"
                >
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                    </svg>
                    <span x-show="!sidebarCollapsed" class="truncate">E-Rapor Digital</span>
                </a>
            @endif

            <!-- Section: Fitur Siswa (Aktivitas Belajar) -->
            @if (auth()->check() && auth()->user()->hasRole(\App\Enums\UserRole::STUDENT))
                <div class="pt-4 pb-1 px-2" x-show="!sidebarCollapsed">
                    <span class="text-[10px] font-bold text-zinc-400 uppercase tracking-wider">Aktivitas Belajar</span>
                </div>

                <a
                    href="{{ route('student.schedule.index') }}"
                    class="metis-nav-item {{ request()->routeIs('student.schedule.*') ? 'active' : '' }}"
                    :title="sidebarCollapsed ? 'Jadwal Pelajaran' : ''"
                >
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span x-show="!sidebarCollapsed" class="truncate">Jadwal Pelajaran</span>
                </a>

                <a
                    href="{{ route('student.materials.index') }}"
                    class="metis-nav-item {{ request()->routeIs('student.materials.*') ? 'active' : '' }}"
                    :title="sidebarCollapsed ? 'Materi Pelajaran' : ''"
                >
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                    </svg>
                    <span x-show="!sidebarCollapsed" class="truncate">Materi Pelajaran</span>
                </a>

                <a
                    href="{{ route('student.assignments.index') }}"
                    class="metis-nav-item {{ request()->routeIs('student.assignments.*') ? 'active' : '' }}"
                    :title="sidebarCollapsed ? 'Tugas & Evaluasi' : ''"
                >
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                    </svg>
                    <span x-show="!sidebarCollapsed" class="truncate">Tugas & Evaluasi</span>
                </a>

                <a
                    href="{{ route('student.exams.index') }}"
                    class="metis-nav-item {{ request()->routeIs('student.exams.*') ? 'active' : '' }}"
                    :title="sidebarCollapsed ? 'Ujian CBT' : ''"
                >
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                    </svg>
                    <span x-show="!sidebarCollapsed" class="truncate">Ujian CBT</span>
                </a>

                <a
                    href="{{ route('attendance.my') }}"
                    class="metis-nav-item {{ request()->routeIs('attendance.my') ? 'active' : '' }}"
                    :title="sidebarCollapsed ? 'Presensi Saya' : ''"
                >
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span x-show="!sidebarCollapsed" class="truncate">Presensi Saya</span>
                </a>

                <a
                    href="{{ route('student.reports.index') }}"
                    class="metis-nav-item {{ request()->routeIs('student.reports.*') ? 'active' : '' }}"
                    :title="sidebarCollapsed ? 'Rapor Belajar Saya' : ''"
                >
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                    </svg>
                    <span x-show="!sidebarCollapsed" class="truncate">Rapor Saya</span>
                </a>

                <a
                    href="{{ route('student.finance.index') }}"
                    class="metis-nav-item {{ request()->routeIs('student.finance.*') ? 'active' : '' }}"
                    :title="sidebarCollapsed ? 'Tagihan Saya' : ''"
                >
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z"/>
                    </svg>
                    <span x-show="!sidebarCollapsed" class="truncate">Tagihan Saya</span>
                </a>
            @endif


            <!-- Section: Fitur Orang Tua / Wali Murid (Fase 7) -->
            @if (auth()->check() && auth()->user()->hasRole(\App\Enums\UserRole::PARENT))
                <div class="pt-4 pb-1 px-2" x-show="!sidebarCollapsed">
                    <span class="text-[10px] font-bold text-zinc-400 uppercase tracking-wider">Portal Orang Tua</span>
                </div>

                <a
                    href="{{ route('parent.dashboard') }}"
                    class="metis-nav-item {{ request()->routeIs('parent.dashboard') ? 'active' : '' }}"
                    :title="sidebarCollapsed ? 'Dashboard Pantauan' : ''"
                >
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                    </svg>
                    <span x-show="!sidebarCollapsed" class="truncate">Dashboard Anak</span>
                </a>

                <a
                    href="{{ route('parent.attendance') }}"
                    class="metis-nav-item {{ request()->routeIs('parent.attendance') ? 'active' : '' }}"
                    :title="sidebarCollapsed ? 'Presensi Anak' : ''"
                >
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span x-show="!sidebarCollapsed" class="truncate">Presensi Kehadiran</span>
                </a>

                <a
                    href="{{ route('parent.assignments') }}"
                    class="metis-nav-item {{ request()->routeIs('parent.assignments') ? 'active' : '' }}"
                    :title="sidebarCollapsed ? 'Tugas Anak' : ''"
                >
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                    </svg>
                    <span x-show="!sidebarCollapsed" class="truncate">Tugas & Evaluasi</span>
                </a>

                <a
                    href="{{ route('parent.grades') }}"
                    class="metis-nav-item {{ request()->routeIs('parent.grades') ? 'active' : '' }}"
                    :title="sidebarCollapsed ? 'Nilai & CBT' : ''"
                >
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                    <span x-show="!sidebarCollapsed" class="truncate">Nilai & Ujian CBT</span>
                </a>

                <a
                    href="{{ route('parent.reports') }}"
                    class="metis-nav-item {{ request()->routeIs('parent.reports*') ? 'active' : '' }}"
                    :title="sidebarCollapsed ? 'E-Rapor Anak' : ''"
                >
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                    </svg>
                    <span x-show="!sidebarCollapsed" class="truncate">E-Rapor Digital</span>
                </a>

                <a
                    href="{{ route('parent.children.index') }}"
                    class="metis-nav-item {{ request()->routeIs('parent.children.*') ? 'active' : '' }}"
                    :title="sidebarCollapsed ? 'Tautkan Akun Anak' : ''"
                >
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                    </svg>
                    <span x-show="!sidebarCollapsed" class="truncate">Data Anak Saya</span>
                </a>

                <a
                    href="{{ route('parent.finance.index') }}"
                    class="metis-nav-item {{ request()->routeIs('parent.finance.*') ? 'active' : '' }}"
                    :title="sidebarCollapsed ? 'Keuangan & SPP' : ''"
                >
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                    <span x-show="!sidebarCollapsed" class="truncate">Keuangan & SPP</span>
                </a>
            @endif


            <!-- Section: Komunikasi & Pengumuman Sekolah -->
            <div class="pt-4 pb-1 px-2" x-show="!sidebarCollapsed">
                <span class="text-[10px] font-bold text-zinc-400 uppercase tracking-wider">Komunikasi Sekolah</span>
            </div>

            <a
                href="{{ route('announcements.index') }}"
                class="metis-nav-item {{ request()->routeIs('announcements.*') ? 'active' : '' }}"
                :title="sidebarCollapsed ? 'Pengumuman' : ''"
            >
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"/>
                </svg>
                <span x-show="!sidebarCollapsed" class="truncate">Pengumuman</span>
            </a>

            @if (auth()->check() && ! auth()->user()->hasRole(\App\Enums\UserRole::STUDENT))
                <a
                    href="{{ route('conversations.index') }}"
                    class="metis-nav-item {{ request()->routeIs('conversations.*') ? 'active' : '' }}"
                    :title="sidebarCollapsed ? 'Pesan Konsultasi' : ''"
                >
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                    </svg>
                    <span x-show="!sidebarCollapsed" class="truncate">Pesan Konsultasi</span>
                </a>
            @endif

            @if (auth()->check() && auth()->user()->hasRole(\App\Enums\UserRole::SUPER_ADMIN, \App\Enums\UserRole::SCHOOL_ADMIN))
                <a
                    href="{{ route('admin.parents.index') }}"
                    class="metis-nav-item {{ request()->routeIs('admin.parents.*') ? 'active' : '' }}"
                    :title="sidebarCollapsed ? 'Relasi Orang Tua' : ''"
                >
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                    <span x-show="!sidebarCollapsed" class="truncate">Relasi Orang Tua</span>
                </a>
            @endif

            <!-- Section: Bantuan & Komponen (Khusus Administrator) -->
            @if (auth()->check() && auth()->user()->hasRole(\App\Enums\UserRole::SUPER_ADMIN, \App\Enums\UserRole::SCHOOL_ADMIN))
                <div class="pt-4 pb-1 px-2" x-show="!sidebarCollapsed">
                    <span class="text-[10px] font-bold text-zinc-400 uppercase tracking-wider">Bantuan & Desain</span>
                </div>

                <a
                    href="{{ route('design-system') }}"
                    class="metis-nav-item {{ request()->routeIs('design-system') ? 'active' : '' }}"
                    :title="sidebarCollapsed ? 'Design System' : ''"
                >
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01"/>
                    </svg>
                    <span x-show="!sidebarCollapsed" class="truncate">Design System</span>
                    <span x-show="!sidebarCollapsed" class="ml-auto text-[10px] font-bold px-1.5 py-0.5 rounded bg-zinc-100 text-zinc-600">v1.0</span>
                </a>
            @endif
        </nav>

        <!-- Sidebar Footer Status -->
        <div class="p-3 border-t border-zinc-100 bg-zinc-50/40 text-center" x-show="!sidebarCollapsed">
            <p class="text-[10px] text-zinc-400 font-medium">Eduvia &bull; Fase 5 School Operations</p>
        </div>
    </aside>

    <!-- ====================================================================== -->
    <!-- MAIN CONTENT VIEWPORT (Responsive Padding Matching Sidebar)           -->
    <!-- ====================================================================== -->
    <div
        :class="{
            'lg:pl-20': sidebarCollapsed,
            'lg:pl-64': !sidebarCollapsed
        }"
        class="flex-1 flex flex-col min-w-0 pt-16 transition-all duration-200"
    >
        <!-- Top Sub-Bar (Breadcrumbs & Actions) -->
        <div class="min-h-12 py-1.5 sm:py-0 h-auto sm:h-12 bg-white/70 backdrop-blur-xs border-b border-zinc-200/80 px-3 sm:px-6 lg:px-8 flex items-center justify-between gap-2 sm:gap-4">
            <div class="flex items-center gap-1.5 sm:gap-2 text-xs text-zinc-500 truncate min-w-0 flex-1">
                @if (isset($breadcrumbs))
                    {{ $breadcrumbs }}
                @else
                    <a href="{{ route('dashboard') }}" class="hover:text-zinc-900 transition-colors shrink-0">Eduvia</a>
                    <span class="text-zinc-300 shrink-0">/</span>
                    <span class="font-bold text-zinc-900 truncate">{{ $headerTitle ?? 'Dashboard' }}</span>
                @endif
            </div>

            <div class="flex items-center gap-2 sm:gap-3 shrink-0">
                @auth
                    <x-badge :variant="auth()->user()->role->badgeVariant()" size="sm" dot>
                        {{ auth()->user()->role->label() }}
                    </x-badge>
                @endauth
            </div>
        </div>

        <!-- Main Slot Body -->
        <main class="flex-1 p-3 sm:p-6 lg:p-8 max-w-7xl w-full mx-auto min-w-0">
            @isset($slot)
                {{ $slot }}
            @else
                @yield('content')
            @endisset
        </main>

        <!-- Clean Editorial Footer -->
        <footer class="border-t border-zinc-200/80 bg-white py-3.5 px-3 sm:px-6 lg:px-8 text-center sm:flex sm:items-center sm:justify-between text-xs text-zinc-500 gap-2">
            <p>&copy; {{ date('Y') }} <strong>Eduvia</strong> &mdash; Learn &middot; Grow &middot; Build Your Future. Hak cipta dilindungi.</p>
            <p class="mt-1 sm:mt-0 text-[11px] text-zinc-400">Template Metis Refined Neutral &bull; SD, SMP, SMA, SMK</p>
        </footer>
    </div>
</body>
</html>
