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

                    <div>
                        <x-input
                            label="Kata Sandi"
                            name="password"
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

                <!-- Demo Credentials Helper for QA & Testing -->
                <div class="mt-8 pt-6 border-t border-slate-100">
                    <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400 text-center mb-3">
                        Akun Uji Coba Cepat (Fase 0 Foundation)
                    </p>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 text-xs">
                        <button
                            type="button"
                            onclick="fillDemo('superadmin@eduvia.test', 'Password123!')"
                            class="p-2 rounded-xl border border-slate-200 hover:border-brand-500 hover:bg-brand-50/50 text-left transition-colors"
                        >
                            <div class="font-bold text-slate-800">Super Admin</div>
                            <div class="text-[10px] text-slate-500 truncate">superadmin@eduvia.test</div>
                        </button>

                        <button
                            type="button"
                            onclick="fillDemo('admin.sekolah@eduvia.test', 'Password123!')"
                            class="p-2 rounded-xl border border-slate-200 hover:border-brand-500 hover:bg-brand-50/50 text-left transition-colors"
                        >
                            <div class="font-bold text-slate-800">Admin Sekolah</div>
                            <div class="text-[10px] text-slate-500 truncate">admin.sekolah@...</div>
                        </button>

                        <button
                            type="button"
                            onclick="fillDemo('guru@eduvia.test', 'Password123!')"
                            class="p-2 rounded-xl border border-slate-200 hover:border-brand-500 hover:bg-brand-50/50 text-left transition-colors"
                        >
                            <div class="font-bold text-slate-800">Guru / Pengajar</div>
                            <div class="text-[10px] text-slate-500 truncate">guru@eduvia.test</div>
                        </button>

                        <button
                            type="button"
                            onclick="fillDemo('siswa@eduvia.test', 'Password123!')"
                            class="p-2 rounded-xl border border-slate-200 hover:border-brand-500 hover:bg-brand-50/50 text-left transition-colors"
                        >
                            <div class="font-bold text-slate-800">Siswa / Pelajar</div>
                            <div class="text-[10px] text-slate-500 truncate">siswa@eduvia.test</div>
                        </button>
                    </div>
                </div>
            </x-card>

        </div>
    </div>

    <script>
        function fillDemo(email, pass) {
            document.getElementById('emailInput').value = email;
            document.getElementById('passwordInput').value = pass;
            if (window.toast) {
                window.toast('Kredensial akun uji coba (' + email + ') telah terisi otomatis.', 'info');
            }
        }
    </script>
</x-layouts.guest>
