<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-slate-50">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? 'Dashboard' }} — {{ config('app.name', 'Eduvia') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Scripts & Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body
    x-data="{ sidebarOpen: false }"
    class="min-h-full flex flex-col font-sans text-slate-900 bg-slate-50 antialiased selection:bg-brand-500 selection:text-white"
>
    <!-- Global Notification Toasts -->
    <x-toast />

    <div class="flex h-screen overflow-hidden bg-slate-50">

        <!-- Mobile Sidebar Backdrop Overlay -->
        <div
            x-show="sidebarOpen"
            x-transition:enter="transition-opacity ease-linear duration-200"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition-opacity ease-linear duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 z-40 bg-slate-900/50 backdrop-blur-xs lg:hidden"
            @click="sidebarOpen = false"
            style="display: none;"
        ></div>

        <!-- Sidebar Navigation Drawer -->
        <aside
            :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
            class="fixed inset-y-0 left-0 z-50 w-64 bg-white border-r border-slate-200 flex flex-col transition-transform duration-300 ease-in-out lg:static lg:translate-x-0 shadow-lg lg:shadow-none"
        >
            <!-- Sidebar Header / Logo -->
            <div class="h-16 px-5 border-b border-slate-100 flex items-center justify-between">
                <a href="{{ route('dashboard') }}" class="focus:outline-none">
                    <x-application-logo size="sm" subtext="Platform E-Learning" />
                </a>
                <button
                    type="button"
                    @click="sidebarOpen = false"
                    class="p-1 rounded-lg text-slate-400 hover:text-slate-600 hover:bg-slate-100 lg:hidden"
                    aria-label="Tutup navigasi"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <!-- School Context Banner (Multi-School Readiness) -->
            <div class="p-3 mx-3 mt-3 rounded-xl bg-slate-50 border border-slate-200/80 text-xs">
                <div class="flex items-center justify-between text-slate-500 mb-1">
                    <span class="font-medium text-[10px] uppercase tracking-wider">Konteks Sekolah</span>
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                </div>
                <div class="font-bold text-slate-800 truncate">
                    SMP/SMA Nusantara Utama
                </div>
                <div class="text-[10px] text-slate-500 mt-0.5">
                    Mode Single-School (Fase 0)
                </div>
            </div>

            <!-- Navigation Links -->
            <nav class="flex-1 px-3 py-4 space-y-1 overflow-y-auto">
                <a
                    href="{{ route('dashboard') }}"
                    class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-semibold transition-colors {{ request()->routeIs('dashboard') ? 'bg-brand-50 text-brand-700 font-bold' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}"
                >
                    <svg class="w-5 h-5 {{ request()->routeIs('dashboard') ? 'text-brand-600' : 'text-slate-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                    </svg>
                    <span>Dashboard</span>
                </a>

                <a
                    href="{{ route('design-system') }}"
                    class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-semibold transition-colors {{ request()->routeIs('design-system') ? 'bg-brand-50 text-brand-700 font-bold' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}"
                >
                    <svg class="w-5 h-5 {{ request()->routeIs('design-system') ? 'text-brand-600' : 'text-slate-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01"/>
                    </svg>
                    <span>Design System</span>
                </a>

                <!-- Roadmap Phase Modules Disabled Indicators -->
                <div class="pt-4 pb-2 px-3">
                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Modul Akademik (Fase 1+)</span>
                </div>

                <div class="space-y-1 opacity-55 select-none" title="Tersedia pada Fase berikutnya sesuai Roadmap">
                    <div class="flex items-center justify-between px-3.5 py-2 rounded-xl text-xs text-slate-400">
                        <span class="flex items-center gap-2">
                            <span class="w-1.5 h-1.5 rounded-full bg-slate-300"></span>
                            Data Sekolah & Kelas
                        </span>
                        <span class="text-[10px] bg-slate-100 text-slate-500 px-1.5 py-0.5 rounded">Fase 1</span>
                    </div>
                    <div class="flex items-center justify-between px-3.5 py-2 rounded-xl text-xs text-slate-400">
                        <span class="flex items-center gap-2">
                            <span class="w-1.5 h-1.5 rounded-full bg-slate-300"></span>
                            Materi & Pembelajaran
                        </span>
                        <span class="text-[10px] bg-slate-100 text-slate-500 px-1.5 py-0.5 rounded">Fase 2</span>
                    </div>
                    <div class="flex items-center justify-between px-3.5 py-2 rounded-xl text-xs text-slate-400">
                        <span class="flex items-center gap-2">
                            <span class="w-1.5 h-1.5 rounded-full bg-slate-300"></span>
                            Ujian CBT & Tugas
                        </span>
                        <span class="text-[10px] bg-slate-100 text-slate-500 px-1.5 py-0.5 rounded">Fase 3</span>
                    </div>
                </div>
            </nav>

            <!-- Sidebar User Profile Footer -->
            <div class="p-3 border-t border-slate-100 bg-slate-50/60">
                @auth
                    <div class="flex items-center gap-3 p-2 rounded-xl">
                        <div class="w-9 h-9 rounded-full bg-brand-600 text-white font-bold flex items-center justify-center text-sm shadow-xs shrink-0">
                            {{ substr(auth()->user()->name, 0, 1) }}
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-xs font-bold text-slate-900 truncate">{{ auth()->user()->name }}</p>
                            <p class="text-[11px] text-slate-500 truncate">{{ auth()->user()->email }}</p>
                        </div>
                    </div>
                @endauth
            </div>
        </aside>

        <!-- Main Body Wrapper -->
        <div class="flex-1 flex flex-col min-w-0 overflow-y-auto">

            <!-- Top Header Shell -->
            <header class="sticky top-0 z-30 h-16 bg-white/90 backdrop-blur-md border-b border-slate-200 px-4 sm:px-6 lg:px-8 flex items-center justify-between gap-4">
                <div class="flex items-center gap-3">
                    <button
                        type="button"
                        @click="sidebarOpen = true"
                        class="p-2 rounded-xl text-slate-500 hover:text-slate-700 hover:bg-slate-100 lg:hidden"
                        aria-label="Buka navigasi menu"
                    >
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                        </svg>
                    </button>

                    @if (isset($breadcrumbs))
                        {{ $breadcrumbs }}
                    @else
                        <div class="flex items-center gap-2 text-xs font-medium text-slate-500">
                            <span>Eduvia</span>
                            <span>/</span>
                            <span class="font-bold text-slate-900">{{ $headerTitle ?? 'Dashboard' }}</span>
                        </div>
                    @endif
                </div>

                <!-- Right Utility Icons & Logout -->
                <div class="flex items-center gap-3">
                    @auth
                        <x-badge :variant="auth()->user()->role->badgeVariant()" size="sm" dot>
                            {{ auth()->user()->role->label() }}
                        </x-badge>

                        <form method="POST" action="{{ route('logout') }}" class="inline">
                            @csrf
                            <button
                                type="submit"
                                class="inline-flex items-center gap-1.5 text-xs font-semibold text-rose-600 hover:text-rose-700 hover:bg-rose-50 px-3 py-1.5 rounded-lg transition-colors"
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                                </svg>
                                <span class="hidden sm:inline">Keluar</span>
                            </button>
                        </form>
                    @endauth
                </div>
            </header>

            <!-- Page Content Body -->
            <main class="flex-1 p-4 sm:p-6 lg:p-8 max-w-7xl w-full mx-auto">
                {{ $slot }}
            </main>

        </div>
    </div>
</body>
</html>
