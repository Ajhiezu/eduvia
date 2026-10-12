<x-layouts.app>
    <x-slot:headerTitle>
        Dashboard
    </x-slot:headerTitle>

    <div class="space-y-6">

        <!-- Welcome Banner -->
        <div class="p-6 rounded-2xl bg-gradient-to-r from-brand-900 via-brand-800 to-brand-700 text-white shadow-md relative overflow-hidden">
            <div class="relative z-10 max-w-2xl">
                <div class="inline-flex items-center gap-2 px-2.5 py-1 rounded-full bg-white/10 text-white text-xs font-medium mb-3 backdrop-blur-xs">
                    <span class="w-2 h-2 rounded-full bg-accent-400"></span>
                    <span>Fondasi Sesi Autentikasi Aktif</span>
                </div>
                <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight">
                    Selamat Datang, {{ auth()->user()->name }}!
                </h1>
                <p class="mt-2 text-xs sm:text-sm text-brand-100 leading-relaxed">
                    Anda berhasil masuk dengan peran <strong class="text-white">{{ auth()->user()->role->label() }}</strong>. Anda berada pada modul fondasi aplikasi Eduvia (Fase 0).
                </p>
            </div>
            <!-- Decorative Background Graphic -->
            <div class="absolute -right-8 -bottom-8 w-48 h-48 rounded-full bg-white/5 pointer-events-none"></div>
        </div>

        <!-- Verification Cards Grid -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
            <!-- Card 1: Profil Pengguna -->
            <x-card title="Informasi Sesi Pengguna">
                <dl class="space-y-2.5 text-xs">
                    <div class="flex items-center justify-between pb-2 border-b border-slate-100">
                        <dt class="text-slate-500 font-medium">Nama Lengkap</dt>
                        <dd class="font-bold text-slate-800">{{ auth()->user()->name }}</dd>
                    </div>
                    <div class="flex items-center justify-between pb-2 border-b border-slate-100">
                        <dt class="text-slate-500 font-medium">Email</dt>
                        <dd class="font-bold text-slate-800">{{ auth()->user()->email }}</dd>
                    </div>
                    <div class="flex items-center justify-between pb-2 border-b border-slate-100">
                        <dt class="text-slate-500 font-medium">Peran Sistem</dt>
                        <dd>
                            <x-badge :variant="auth()->user()->role->badgeVariant()" size="sm">
                                {{ auth()->user()->role->label() }}
                            </x-badge>
                        </dd>
                    </div>
                    <div class="flex items-center justify-between">
                        <dt class="text-slate-500 font-medium">Status Akun</dt>
                        <dd>
                            <x-badge variant="success" size="sm" dot>Aktif</x-badge>
                        </dd>
                    </div>
                </dl>
            </x-card>

            <!-- Card 2: Status Fondasi Keamanan & Arsitektur -->
            <x-card title="Status Fondasi Keamanan">
                <dl class="space-y-2.5 text-xs">
                    <div class="flex items-center justify-between pb-2 border-b border-slate-100">
                        <dt class="text-slate-500 font-medium">CSRF Protection</dt>
                        <dd><x-badge variant="success" size="sm">Aktif & Terproteksi</x-badge></dd>
                    </div>
                    <div class="flex items-center justify-between pb-2 border-b border-slate-100">
                        <dt class="text-slate-500 font-medium">Security Headers</dt>
                        <dd><x-badge variant="success" size="sm">Active (SAMEORIGIN)</x-badge></dd>
                    </div>
                    <div class="flex items-center justify-between pb-2 border-b border-slate-100">
                        <dt class="text-slate-500 font-medium">Database Dialect</dt>
                        <dd class="font-semibold text-slate-700">MySQL / MariaDB</dd>
                    </div>
                    <div class="flex items-center justify-between">
                        <dt class="text-slate-500 font-medium">Password Hashing</dt>
                        <dd class="font-semibold text-slate-700">Bcrypt (Cost 12)</dd>
                    </div>
                </dl>
            </x-card>

            <!-- Card 3: Verifikasi Otorisasi Role -->
            <x-card title="Pengujian Otorisasi Role">
                <p class="text-xs text-slate-500 mb-4 leading-relaxed">
                    Uji coba endpoint yang dilindungi middleware <code class="bg-slate-100 px-1 py-0.5 rounded text-[11px] font-mono">role:super_admin,school_admin</code> untuk membuktikan bahwa otorisasi ditangani di backend.
                </p>

                <div x-data="{
                    testing: false,
                    result: null,
                    error: null,
                    async testAdminRoute() {
                        this.testing = true;
                        this.result = null;
                        this.error = null;
                        try {
                            const res = await fetch('{{ route('demo.admin') }}', {
                                headers: { 'Accept': 'application/json' }
                            });
                            const data = await res.json();
                            if (res.ok) {
                                this.result = 'Akses DITERIMA (HTTP ' + res.status + '): ' + data.message;
                            } else {
                                this.error = 'Akses DITOLAK (HTTP ' + res.status + '): ' + (data.message || 'Peran Anda tidak diizinkan.');
                            }
                        } catch (e) {
                            this.error = 'Kesalahan jaringan: ' + e.message;
                        } finally {
                            this.testing = false;
                        }
                    }
                }">
                    <x-button
                        type="button"
                        size="sm"
                        variant="outline"
                        @click="testAdminRoute()"
                        class="w-full"
                    >
                        <span x-show="!testing">Uji Akses Endpoint Admin</span>
                        <span x-show="testing">Memeriksa Otorisasi...</span>
                    </x-button>

                    <div x-show="result" class="mt-3 p-2.5 rounded-lg bg-emerald-50 border border-emerald-200 text-xs text-emerald-800 font-medium" style="display: none;">
                        <span x-text="result"></span>
                    </div>

                    <div x-show="error" class="mt-3 p-2.5 rounded-lg bg-rose-50 border border-rose-200 text-xs text-rose-800 font-medium" style="display: none;">
                        <span x-text="error"></span>
                    </div>
                </div>
            </x-card>
        </div>

        <!-- Design System Showcase Banner -->
        <x-card padding class="bg-slate-50/50 border-dashed border-2 border-slate-300">
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                <div>
                    <h3 class="font-bold text-slate-900 text-base">Eksplorasi Design System Eduvia</h3>
                    <p class="text-xs sm:text-sm text-slate-500 mt-1 max-w-xl">
                        Lihat katalog lengkap komponen antarmuka: tombol, formulir input, modal interaktif, tabel terformat, badge status, dan skeleton loading state.
                    </p>
                </div>
                <x-button href="{{ route('design-system') }}" variant="primary" size="md" class="shrink-0">
                    Buka Living Design System &rarr;
                </x-button>
            </div>
        </x-card>

    </div>
</x-layouts.app>
