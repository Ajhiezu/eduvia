<x-layouts.guest>
    <x-slot:title>
        Masuk ke Sistem — Eduvia Platform
    </x-slot:title>

    <div class="flex-1 flex items-center justify-center py-10 px-4 sm:px-6 lg:px-8">
        <div class="w-full max-w-md">

            <!-- Card Container -->
            <x-card padding class="shadow-xl border-slate-200">
                <div class="text-center mb-6">
                    <x-application-logo variant="vertical" size="md" class="justify-center mb-4" />
                    <h2 class="text-xl font-extrabold text-slate-900 tracking-tight">
                        Masuk ke Akun Anda
                    </h2>
                    <p class="text-xs text-slate-500 mt-1">
                        Masukkan kredensial terdaftar untuk mengakses platform pembelajaran.
                    </p>
                </div>

                <!-- Session / Status Alert -->
                @if (session('status'))
                    <x-alert variant="info" class="mb-5">
                        {{ session('status') }}
                    </x-alert>
                @endif

                <!-- Validation Errors Alert -->
                @if ($errors->any())
                    <x-alert variant="danger" title="Gagal Masuk" class="mb-5">
                        {{ $errors->first() }}
                    </x-alert>
                @endif

                <!-- Login Form -->
                <form method="POST" action="{{ route('login') }}" class="space-y-4" id="loginForm">
                    @csrf

                    <x-input
                        label="Alamat Email"
                        name="email"
                        type="email"
                        id="emailInput"
                        placeholder="nama@sekolah.sch.id"
                        value="{{ old('email') }}"
                        required
                        autofocus
                    >
                        <x-slot:prefixIcon>
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.206"/>
                            </svg>
                        </x-slot:prefixIcon>
                    </x-input>

                    <div x-data="{ showPassword: false }">
                        <x-input
                            label="Kata Sandi"
                            name="password"
                            ::type="showPassword ? 'text' : 'password'"
                            type="password"
                            id="passwordInput"
                            placeholder="••••••••••••"
                            required
                        >
                            <x-slot:prefixIcon>
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                </svg>
                            </x-slot:prefixIcon>
                            <x-slot:suffixIcon>
                                <button
                                    type="button"
                                    @click="showPassword = !showPassword"
                                    class="text-zinc-400 hover:text-zinc-700 focus:outline-none p-1 transition-colors cursor-pointer"
                                    tabindex="-1"
                                    aria-label="Tampilkan atau sembunyikan kata sandi"
                                >
                                    <svg x-show="!showPassword" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                    </svg>
                                    <svg x-show="showPassword" x-cloak class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18" />
                                    </svg>
                                </button>
                            </x-slot:suffixIcon>
                        </x-input>
                    </div>

                    <div class="flex items-center justify-between text-xs">
                        <label class="flex items-center gap-2 cursor-pointer select-none">
                            <input
                                type="checkbox"
                                name="remember"
                                class="w-4 h-4 rounded border-slate-300 text-brand-600 focus:ring-brand-500"
                            >
                            <span class="text-slate-600 font-medium">Ingat saya</span>
                        </label>
                    </div>

                    <div class="pt-2">
                        <x-button type="submit" variant="primary" class="w-full" size="md">
                            <span>Masuk ke Eduvia</span>
                        </x-button>
                    </div>
                </form>

                <!-- Security & Help Notice -->
                <div class="mt-8 pt-6 border-t border-slate-100 flex items-center justify-between text-xs text-slate-400">
                    <span class="inline-flex items-center gap-1.5">
                        <svg class="w-4 h-4 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                        <span>Koneksi Terenkripsi SSL</span>
                    </span>
                    <a href="{{ route('home') }}" class="text-brand-600 hover:text-brand-700 font-medium transition-colors">
                        Kembali ke Beranda &rarr;
                    </a>
                </div>
            </x-card>

        </div>
    </div>
</x-layouts.guest>
