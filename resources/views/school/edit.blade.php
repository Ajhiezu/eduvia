<x-layouts.app>
    <x-slot:headerTitle>
        Ubah Profil Sekolah
    </x-slot:headerTitle>

    <div class="max-w-4xl mx-auto space-y-6">
        <!-- Breadcrumb / Back Link -->
        <div class="flex items-center gap-2 text-xs font-semibold text-slate-500">
            <a href="{{ route('school.profile') }}" class="hover:text-brand-600 transition-colors flex items-center gap-1">
                &larr; Kembali ke Profil Sekolah
            </a>
        </div>

        @if ($errors->any())
            <x-alert variant="danger" title="Terdapat Kesalahan Pengisian Formulir" dismissible>
                {{ $errors->first() }}
            </x-alert>
        @endif

        <form method="POST" action="{{ route('school.profile.update') }}" enctype="multipart/form-data" class="space-y-6">
            @csrf
            @method('PUT')

            <!-- Identitas Utama Sekolah -->
            <x-card title="Identitas Utama Sekolah" padding>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div class="md:col-span-2">
                        <x-input
                            label="Nama Resmi Sekolah"
                            name="name"
                            value="{{ old('name', $school?->name) }}"
                            placeholder="Contoh: Yayasan Perguruan Nusantara Utama"
                            required
                        />
                    </div>

                    <x-input
                        label="Nama Singkat / Alias"
                        name="short_name"
                        value="{{ old('short_name', $school?->short_name) }}"
                        placeholder="Contoh: Nusantara Utama"
                        hint="Digunakan pada kop surat & ringkasan antarmuka."
                    />

                    <x-input
                        label="Nomor Pokok Sekolah Nasional (NPSN)"
                        name="npsn"
                        value="{{ old('npsn', $school?->npsn) }}"
                        placeholder="8 digit angka (contoh: 20104567)"
                        hint="Wajib berupa 8 digit angka unik jika diisi."
                    />

                    <!-- Jenjang Pendidikan Checkboxes -->
                    <div class="md:col-span-2 space-y-2">
                        <label class="text-sm font-semibold text-slate-700 flex items-center justify-between">
                            <span>Jenjang Pendidikan Sekolah <span class="text-rose-500 font-bold">*</span></span>
                        </label>
                        <p class="text-xs text-slate-500">Pilih satu atau lebih jenjang yang diselenggarakan oleh sekolah ini.</p>

                        @php
                            $selectedLevels = old('educational_levels', $school?->educational_levels ?? ['smp', 'sma']);
                        @endphp
                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 pt-1">
                            @foreach (['sd' => 'SD / MI (Sekolah Dasar)', 'smp' => 'SMP / MTs (Menengah Pertama)', 'sma' => 'SMA / MA (Menengah Atas)', 'smk' => 'SMK / MAK (Kejuruan)'] as $val => $label)
                                <label class="flex items-center gap-2.5 p-3 rounded-xl border border-slate-200 hover:border-brand-500 hover:bg-brand-50/40 cursor-pointer transition-colors text-xs font-medium text-slate-800">
                                    <input
                                        type="checkbox"
                                        name="educational_levels[]"
                                        value="{{ $val }}"
                                        {{ in_array($val, $selectedLevels ?? []) ? 'checked' : '' }}
                                        class="w-4 h-4 rounded border-slate-300 text-brand-600 focus:ring-brand-500"
                                    />
                                    <span>{{ $label }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                </div>
            </x-card>

            <!-- Alamat & Kontak Resmi -->
            <x-card title="Alamat & Kontak Resmi Sekolah" padding>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div class="md:col-span-2">
                        <x-input
                            label="Alamat Lengkap Jalan / Gedung"
                            name="address"
                            value="{{ old('address', $school?->address) }}"
                            placeholder="Contoh: Jl. Pendidikan Karakter No. 45"
                        />
                    </div>

                    <x-input
                        label="Kota / Kabupaten"
                        name="city"
                        value="{{ old('city', $school?->city) }}"
                        placeholder="Contoh: Jakarta Selatan"
                    />

                    <x-input
                        label="Provinsi"
                        name="province"
                        value="{{ old('province', $school?->province) }}"
                        placeholder="Contoh: DKI Jakarta"
                    />

                    <x-input
                        label="Kode Pos"
                        name="postal_code"
                        value="{{ old('postal_code', $school?->postal_code) }}"
                        placeholder="Contoh: 12150"
                    />

                    <x-input
                        label="Nomor Telepon Kantor"
                        name="phone"
                        value="{{ old('phone', $school?->phone) }}"
                        placeholder="Contoh: (021) 7890-1234"
                    />

                    <x-input
                        label="Alamat Email Resmi Sekolah"
                        name="email"
                        type="email"
                        value="{{ old('email', $school?->email) }}"
                        placeholder="Contoh: info@nusantarautama.sch.id"
                    />

                    <x-input
                        label="Alamat Website Sekolah"
                        name="website"
                        value="{{ old('website', $school?->website) }}"
                        placeholder="Contoh: https://nusantarautama.sch.id"
                    />
                </div>
            </x-card>

            <!-- Logo Sekolah -->
            <x-card title="Logo Resmi Sekolah" padding>
                <div class="flex flex-col sm:flex-row items-start sm:items-center gap-6">
                    <div class="w-24 h-24 rounded-2xl bg-slate-50 border border-slate-200 flex items-center justify-center overflow-hidden shrink-0 shadow-2xs">
                        @if ($school?->logo_path && $school->logoUrl())
                            <img src="{{ $school->logoUrl() }}" alt="{{ $school->name }}" class="w-full h-full object-contain p-2">
                        @else
                            <div class="text-center p-2 text-slate-400">
                                <svg class="w-8 h-8 mx-auto mb-0.5 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                                <span class="text-[9px] font-semibold">Belum Ada</span>
                            </div>
                        @endif
                    </div>

                    <div class="flex-1 space-y-2">
                        <label for="logoInput" class="text-xs font-semibold text-slate-700 block">
                            Unggah Berkas Logo Baru (PNG, JPG, WEBP, SVG - Maks. 2MB)
                        </label>
                        <input
                            type="file"
                            name="logo"
                            id="logoInput"
                            accept="image/png,image/jpeg,image/webp,image/svg+xml"
                            class="block w-full text-xs text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-brand-50 file:text-brand-700 hover:file:bg-brand-100 cursor-pointer"
                        />
                        <p class="text-[11px] text-slate-400 leading-relaxed">
                            Logo sekolah akan ditampilkan pada kop rapor, laporan akademik, dan identitas institusi. Logo Eduvia platform tetap terjaga terpisah sebagai master brand.
                        </p>
                    </div>
                </div>
            </x-card>

            <!-- Actions Footer -->
            <div class="flex items-center justify-end gap-3 pt-2">
                <x-button href="{{ route('school.profile') }}" variant="outline" size="md">
                    Batal
                </x-button>
                <x-button type="submit" variant="primary" size="md">
                    Simpan Perubahan Profil
                </x-button>
            </div>
        </form>
    </div>
</x-layouts.app>
