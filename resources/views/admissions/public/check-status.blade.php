<x-layouts.guest>
    <x-slot:title>
        Cek Status Pendaftaran PPDB / SPMB &mdash; Eduvia
    </x-slot:title>

    <div class="py-12 bg-zinc-50 min-h-screen">
        <div class="max-w-xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            <div class="text-center space-y-2">
                <a href="{{ route('ppdb.index') }}" class="inline-flex items-center text-xs font-semibold text-zinc-500 hover:text-zinc-800 transition-colors">
                    &larr; Kembali ke Beranda PPDB
                </a>
                <h1 class="text-2xl sm:text-3xl font-extrabold text-zinc-900 tracking-tight mt-1">
                    Cek Status Pendaftaran Siswa
                </h1>
                <p class="text-xs sm:text-sm text-zinc-500 max-w-sm mx-auto">
                    Masukkan Nomor Pendaftaran dan Kode Tracking yang Anda terima saat mengirim formulir pendaftaran.
                </p>
            </div>

            <!-- Error Alerts -->
            @if ($errors->any())
                <x-alert variant="danger" dismissible>
                    {{ $errors->first() }}
                </x-alert>
            @endif

            <x-card padding class="border-zinc-200">
                <form action="{{ route('ppdb.check-status.process') }}" method="POST" class="space-y-4">
                    @csrf

                    <div>
                        <x-label for="registration_number" required>Nomor Pendaftaran Resmi</x-label>
                        <x-input
                            id="registration_number"
                            name="registration_number"
                            :value="old('registration_number')"
                            placeholder="Contoh: PPDB-2026-0001"
                            required
                            autofocus
                        />
                        <span class="text-[11px] text-zinc-400 mt-1 block">Tercantum pada tanda terima pendaftaran online.</span>
                    </div>

                    <div>
                        <x-label for="tracking_code" required>Kode Tracking Rahasia (8 Karakter)</x-label>
                        <x-input
                            id="tracking_code"
                            name="tracking_code"
                            :value="old('tracking_code')"
                            placeholder="Contoh: TRACK001"
                            maxlength="20"
                            required
                            class="uppercase font-mono"
                        />
                        <span class="text-[11px] text-zinc-400 mt-1 block">Kode verifikasi pengaman identitas pendaftar.</span>
                    </div>

                    <div class="pt-2">
                        <x-button type="submit" variant="primary" size="md" class="w-full justify-center">
                            <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                            Periksa Status Berkas & Seleksi
                        </x-button>
                    </div>
                </form>
            </x-card>

            <div class="p-4 bg-zinc-100 rounded-xl text-center text-xs text-zinc-500">
                Lupa Nomor Pendaftaran atau Kode Tracking? Silakan hubungi Sekretariat Panitia PPDB di sekolah atau kirimkan pesan kepada admin sekolah.
            </div>
        </div>
    </div>
</x-layouts.guest>
