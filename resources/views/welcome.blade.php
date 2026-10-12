<x-layouts.guest>
    <x-slot:title>
        Eduvia — Fondasi Platform E-Learning Sekolah Indonesia
    </x-slot:title>

    <div class="py-12 sm:py-20 px-4 sm:px-6 lg:px-8 max-w-7xl mx-auto flex-1 flex flex-col justify-center">
        <!-- Hero Section -->
        <div class="text-center max-w-3xl mx-auto mb-16">
            <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-brand-50 border border-brand-200 text-brand-700 text-xs font-semibold mb-6 shadow-2xs">
                <span class="w-2 h-2 rounded-full bg-brand-600 animate-pulse"></span>
                <span>Fase 0: Project Foundation & Architecture Selesai</span>
            </div>

            <h1 class="text-3xl sm:text-5xl lg:text-6xl font-extrabold text-slate-900 tracking-tight leading-[1.15]">
                Fondasi E-Learning Modern untuk Sekolah Indonesia
            </h1>

            <p class="mt-6 text-base sm:text-lg text-slate-600 leading-relaxed">
                Dirancang khusus untuk mendukung kontinuitas pendidikan dari jenjang <strong class="text-slate-800">SD, SMP, SMA, hingga SMK</strong> dengan arsitektur bersih, keamanan tinggi, dan antarmuka berstandar institusi akademik.
            </p>

            <!-- Education Level Badges -->
            <div class="mt-6 flex flex-wrap items-center justify-center gap-2">
                <x-badge variant="primary" size="md">SD / MI</x-badge>
                <x-badge variant="primary" size="md">SMP / MTs</x-badge>
                <x-badge variant="primary" size="md">SMA / MA</x-badge>
                <x-badge variant="accent" size="md">SMK / MAK</x-badge>
            </div>

            <!-- Action CTA Buttons -->
            <div class="mt-10 flex flex-wrap items-center justify-center gap-3">
                <x-button href="{{ route('login') }}" size="lg" variant="primary">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/>
                    </svg>
                    Masuk ke Sistem
                </x-button>

                <x-button href="{{ route('design-system') }}" size="lg" variant="outline">
                    <svg class="w-5 h-5 text-brand-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01"/>
                    </svg>
                    Lihat Design System UI
                </x-button>

                <x-button href="{{ url('/api/v1/health') }}" target="_blank" size="lg" variant="ghost">
                    API Healthcheck &rarr;
                </x-button>
            </div>
        </div>

        <!-- Architecture Pillars Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <x-card hover padding>
                <div class="w-10 h-10 rounded-xl bg-brand-50 text-brand-600 flex items-center justify-center mb-4">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                    </svg>
                </div>
                <h3 class="font-bold text-slate-900 text-base mb-1.5">Arsitektur Modular</h3>
                <p class="text-xs text-slate-500 leading-relaxed">
                    Pemisahan lapis presentasi, domain logic, dan data access berbasis standar modern Laravel untuk keandalan jangka panjang.
                </p>
            </x-card>

            <x-card hover padding>
                <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center mb-4">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                    </svg>
                </div>
                <h3 class="font-bold text-slate-900 text-base mb-1.5">Keamanan Sejak Awal</h3>
                <p class="text-xs text-slate-500 leading-relaxed">
                    Defense-in-depth security headers, proteksi CSRF & XSS, rate-limiting login throttle, dan hashing Bcrypt berstandar tinggi.
                </p>
            </x-card>

            <x-card hover padding>
                <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center mb-4">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                    </svg>
                </div>
                <h3 class="font-bold text-slate-900 text-base mb-1.5">Kesiapan Multi-Sekolah</h3>
                <p class="text-xs text-slate-500 leading-relaxed">
                    Fondasi isolasi data terstruktur yang mendukung implementasi single-school maupun perluasan multi-school di masa depan.
                </p>
            </x-card>

            <x-card hover padding>
                <div class="w-10 h-10 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center mb-4">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                    </svg>
                </div>
                <h3 class="font-bold text-slate-900 text-base mb-1.5">UI/UX Responsif</h3>
                <p class="text-xs text-slate-500 leading-relaxed">
                    Design tokens terpadu, komponen reusable, aksesibilitas tinggi, serta optimasi layar dari 320px mobile hingga desktop 1440px.
                </p>
            </x-card>
        </div>
    </div>
</x-layouts.guest>
