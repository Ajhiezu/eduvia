<x-layouts.app>
    <x-slot:headerTitle>
        Pengaturan Sistem
    </x-slot:headerTitle>

    <div class="max-w-3xl mx-auto space-y-6">
        <div>
            <h1 class="text-xl sm:text-2xl font-extrabold text-zinc-900 tracking-tight">
                Pengaturan Dasar Sistem & Platform
            </h1>
            <p class="text-xs text-zinc-500 mt-0.5">
                Konfigurasi preferensi operasional sekolah meliputi zona waktu, bahasa antarmuka, dan format penanggalan.
            </p>
        </div>

        @if (session('status'))
            <x-alert variant="success" dismissible>
                {{ session('status') }}
            </x-alert>
        @endif

        @if ($errors->any())
            <x-alert variant="danger" dismissible>
                {{ $errors->first() }}
            </x-alert>
        @endif

        <form method="POST" action="{{ route('settings.system.update') }}" class="space-y-6">
            @csrf
            @method('PUT')

            <x-card title="Lokalisasi & Waktu Operasional" padding>
                <div class="space-y-5">
                    <!-- Zona Waktu -->
                    <x-select
                        label="Zona Waktu Operasional (Timezone)"
                        name="timezone"
                        required
                        hint="Mempengaruhi jadwal pelajaran, batas pengumpulan tugas, dan catatan log presensi."
                    >
                        <option value="Asia/Jakarta" {{ old('timezone', $settings['timezone']) === 'Asia/Jakarta' ? 'selected' : '' }}>
                            WIB — Waktu Indonesia Barat (Asia/Jakarta, UTC+7)
                        </option>
                        <option value="Asia/Makassar" {{ old('timezone', $settings['timezone']) === 'Asia/Makassar' ? 'selected' : '' }}>
                            WITA — Waktu Indonesia Tengah (Asia/Makassar, UTC+8)
                        </option>
                        <option value="Asia/Jayapura" {{ old('timezone', $settings['timezone']) === 'Asia/Jayapura' ? 'selected' : '' }}>
                            WIT — Waktu Indonesia Timur (Asia/Jayapura, UTC+9)
                        </option>
                        <option value="UTC" {{ old('timezone', $settings['timezone']) === 'UTC' ? 'selected' : '' }}>
                            UTC — Standar Universal Terkoordinasi (UTC+0)
                        </option>
                    </x-select>

                    <!-- Bahasa Antarmuka -->
                    <x-select
                        label="Bahasa Baku Antarmuka (Default Locale)"
                        name="locale"
                        required
                        hint="Bahasa bawaan yang digunakan untuk seluruh label, validasi, dan notifikasi sistem."
                    >
                        <option value="id" {{ old('locale', $settings['locale']) === 'id' ? 'selected' : '' }}>
                            Bahasa Indonesia (Resmi)
                        </option>
                        <option value="en" {{ old('locale', $settings['locale']) === 'en' ? 'selected' : '' }}>
                            English (Bilingual Mode)
                        </option>
                    </x-select>

                    <!-- Format Tanggal -->
                    <x-select
                        label="Format Tampilan Tanggal"
                        name="date_format"
                        required
                        hint="Format tanggal yang ditampilkan pada tabel, sertifikat, dan laporan rapor."
                    >
                        <option value="d/m/Y" {{ old('date_format', $settings['date_format']) === 'd/m/Y' ? 'selected' : '' }}>
                            dd/mm/yyyy (Contoh: {{ date('d/m/Y') }}) — Standar Indonesia
                        </option>
                        <option value="d-m-Y" {{ old('date_format', $settings['date_format']) === 'd-m-Y' ? 'selected' : '' }}>
                            dd-mm-yyyy (Contoh: {{ date('d-m-Y') }})
                        </option>
                        <option value="d M Y" {{ old('date_format', $settings['date_format']) === 'd M Y' ? 'selected' : '' }}>
                            dd Mmm yyyy (Contoh: {{ date('d M Y') }})
                        </option>
                        <option value="Y-m-d" {{ old('date_format', $settings['date_format']) === 'Y-m-d' ? 'selected' : '' }}>
                            yyyy-mm-dd (Contoh: {{ date('Y-m-d') }}) — Standar ISO
                        </option>
                    </x-select>
                </div>
            </x-card>

            <!-- Keamanan & Kredensial Environment Info -->
            <x-card title="Kebijakan Rahasia & Kredensial Sistem" padding>
                <div class="flex items-start gap-3 text-xs text-zinc-600">
                    <div class="w-8 h-8 rounded-xl bg-brand-50 text-brand-600 flex items-center justify-center shrink-0">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                        </svg>
                    </div>
                    <div>
                        <p class="font-bold text-zinc-800 mb-1">Penyimpanan Terisolasi Sesuai Prinsip Keamanan</p>
                        <p class="leading-relaxed text-zinc-500">
                            Kunci rahasia API, kunci enkripsi aplikasi (<code class="bg-zinc-100 px-1 py-0.5 rounded font-mono text-[11px] text-zinc-700">APP_KEY</code>), dan kredensial basis data dikelola secara ketat melalui berkas environment server (<code class="bg-zinc-100 px-1 py-0.5 rounded font-mono text-[11px] text-zinc-700">.env</code>) dan tidak pernah disimpan atau ditampilkan secara terbuka di antarmuka web.
                        </p>
                    </div>
                </div>
            </x-card>

            <div class="flex items-center justify-end gap-3 pt-2">
                <x-button type="submit" variant="primary" size="md">
                    Simpan Pengaturan Sistem
                </x-button>
            </div>
        </form>
    </div>
</x-layouts.app>
