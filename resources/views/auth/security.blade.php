<x-layouts.app>
    <x-slot:headerTitle>Keamanan Akun</x-slot:headerTitle>

    <div class="max-w-2xl mx-auto space-y-6">
        <div>
            <h1 class="text-2xl font-bold text-zinc-900">Keamanan Akun</h1>
            <p class="mt-2 text-sm text-zinc-600">Lindungi akun dengan kode tambahan dari aplikasi autentikator.</p>
        </div>

        @if (session('status'))
            <x-alert variant="success">{{ session('status') }}</x-alert>
        @endif
        @if ($errors->any())
            <x-alert variant="danger">{{ $errors->first() }}</x-alert>
        @endif

        @if (session('recovery_codes'))
            <x-card title="Simpan kode pemulihan" padding>
                <p class="mb-4 text-sm text-zinc-600">Kode ini hanya ditampilkan sekali. Simpan di tempat aman. Setiap kode dapat dipakai sekali jika autentikator tidak tersedia.</p>
                <ul class="grid grid-cols-2 gap-3 font-mono text-sm text-zinc-900">
                    @foreach (session('recovery_codes') as $code)
                        <li class="rounded-lg bg-zinc-100 p-3">{{ $code }}</li>
                    @endforeach
                </ul>
            </x-card>
        @endif

        <x-card :title="$isEnabled ? 'Autentikasi dua faktor aktif' : 'Aktifkan autentikasi dua faktor'" padding>
            @if ($isEnabled)
                <form method="POST" action="{{ route('account.two-factor.destroy') }}" class="space-y-5">
                    @csrf
                    @method('DELETE')
                    <p class="text-sm text-zinc-600">Untuk menonaktifkan perlindungan ini, verifikasi kata sandi dan kode autentikator atau kode pemulihan Anda.</p>
                    <x-input name="password" type="password" label="Kata sandi saat ini" autocomplete="current-password" required />
                    <x-input name="two_factor_code" label="Kode autentikator atau pemulihan" autocomplete="one-time-code" maxlength="64" required />
                    <x-button type="submit" variant="secondary">Nonaktifkan dua faktor</x-button>
                </form>
            @elseif ($secret)
                <form method="POST" action="{{ route('account.two-factor.confirm') }}" class="space-y-5">
                    @csrf
                    <p class="text-sm text-zinc-600">Tambahkan akun di aplikasi autentikator menggunakan kunci berikut. Kemudian masukkan kode enam digit. Selesaikan dalam 10 menit.</p>
                    <p class="rounded-lg bg-zinc-100 p-4 break-all font-mono text-sm text-zinc-900">{{ $secret }}</p>
                    <a href="{{ $otpAuthUrl }}" class="text-sm font-medium text-brand-600 hover:underline">Buka aplikasi autentikator</a>
                    <x-input name="two_factor_code" label="Kode autentikator" inputmode="numeric" autocomplete="one-time-code" maxlength="6" required />
                    <x-button type="submit" variant="primary">Verifikasi dan aktifkan</x-button>
                </form>
            @else
                <form method="POST" action="{{ route('account.two-factor.setup') }}" class="space-y-5">
                    @csrf
                    <p class="text-sm text-zinc-600">Siapkan aplikasi autentikator di ponsel Anda. Setelah aktif, login memerlukan kata sandi dan kode autentikator atau kode pemulihan.</p>
                    <x-input name="password" type="password" label="Kata sandi saat ini" autocomplete="current-password" required />
                    <x-button type="submit" variant="primary">Mulai aktivasi</x-button>
                </form>
            @endif
        </x-card>
    </div>
</x-layouts.app>
