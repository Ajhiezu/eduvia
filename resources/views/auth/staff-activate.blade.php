<x-layouts.guest>
    <x-slot:title>
        Aktivasi Akun Guru & Tenaga Kependidikan — Eduvia Platform
    </x-slot:title>

    <div class="flex-1 flex items-center justify-center py-10 px-4 sm:px-6 lg:px-8">
        <div class="w-full max-w-md">
            <x-card padding class="shadow-xl border-slate-200">
                <div class="text-center mb-6">
                    <x-application-logo variant="vertical" size="md" class="justify-center mb-4" />
                    <h2 class="text-xl font-extrabold text-slate-900 tracking-tight">
                        Aktivasi Akun Pegawai
                    </h2>
                    <p class="text-xs text-slate-500 mt-1">
                        Selamat datang di Eduvia! Atur kata sandi pribadi Anda untuk mulai menggunakan sistem.
                    </p>
                </div>

                <!-- Account Context Card -->
                <div class="p-3.5 rounded-xl bg-zinc-50 border border-zinc-200 mb-5 text-xs space-y-1.5">
                    <div class="flex justify-between">
                        <span class="text-zinc-500">Nama:</span>
                        <strong class="text-zinc-900">{{ $user->name }}</strong>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-zinc-500">Email:</span>
                        <span class="text-zinc-800 font-mono">{{ $user->email }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-zinc-500">Sekolah:</span>
                        <strong class="text-zinc-800">{{ $school?->name ?? 'Eduvia School' }}</strong>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-zinc-500">Jabatan:</span>
                        <x-badge :variant="$profile->position?->badgeVariant() ?? 'primary'" size="sm">
                            {{ $profile->position?->label() ?? 'Guru' }}
                        </x-badge>
                    </div>
                </div>

                @if ($errors->any())
                    <x-alert variant="danger" title="Aktivasi Gagal" class="mb-5">
                        {{ $errors->first() }}
                    </x-alert>
                @endif

                <form method="POST" action="{{ route('staff.activate.submit', ['token' => $token]) }}" class="space-y-4">
                    @csrf

                    <div>
                        <label for="password" class="block text-xs font-semibold text-zinc-700 mb-1">
                            Kata Sandi Baru <span class="text-rose-500">*</span>
                        </label>
                        <input
                            type="password"
                            name="password"
                            id="password"
                            required
                            placeholder="Minimal 8 karakter"
                            class="w-full rounded-lg border border-zinc-300 text-zinc-900 text-xs py-2.5 px-3 focus:border-brand-500 focus:ring-2 focus:ring-brand-100"
                        />
                    </div>

                    <div>
                        <label for="password_confirmation" class="block text-xs font-semibold text-zinc-700 mb-1">
                            Konfirmasi Kata Sandi Baru <span class="text-rose-500">*</span>
                        </label>
                        <input
                            type="password"
                            name="password_confirmation"
                            id="password_confirmation"
                            required
                            placeholder="Ulangi kata sandi baru"
                            class="w-full rounded-lg border border-zinc-300 text-zinc-900 text-xs py-2.5 px-3 focus:border-brand-500 focus:ring-2 focus:ring-brand-100"
                        />
                    </div>

                    <div class="pt-2">
                        <x-button type="submit" variant="primary" size="md" class="w-full justify-center">
                            <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            Aktifkan Akun & Masuk ke Portal
                        </x-button>
                    </div>
                </form>

                <div class="mt-6 text-center">
                    <a href="{{ route('login') }}" class="text-xs text-brand-600 hover:text-brand-700 font-semibold">
                        Sudah pernah mengaktifkan akun? Masuk di sini
                    </a>
                </div>
            </x-card>
        </div>
    </div>
</x-layouts.guest>
