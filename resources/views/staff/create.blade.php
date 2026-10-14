<x-layouts.app>
    <x-slot:headerTitle>
        Tambah Pegawai Baru
    </x-slot:headerTitle>

    <div class="max-w-4xl mx-auto space-y-6" x-data="{ creationMode: '{{ old('creation_mode', 'invitation') }}' }">
        <!-- Breadcrumb & Header -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div>
                <div class="flex items-center gap-2 text-xs text-zinc-500 mb-1">
                    <a href="{{ route('staff.index') }}" class="hover:text-brand-600 transition-colors">Pendidik & Staf</a>
                    <span>/</span>
                    <span class="text-zinc-800 font-medium">Registrasi Akun Baru</span>
                </div>
                <h1 class="text-xl sm:text-2xl font-extrabold text-zinc-900 tracking-tight">
                    Tambah Guru & Tenaga Kependidikan
                </h1>
                <p class="text-xs text-zinc-500 mt-0.5">
                    Daftarkan akun pendidik atau staf sekolah dengan penetapan jabatan dan peran akses aplikasi yang sesuai.
                </p>
            </div>

            <x-button href="{{ route('staff.index') }}" variant="secondary" size="sm" class="self-start sm:self-auto shrink-0">
                Kembali
            </x-button>
        </div>

        @if ($errors->any())
            <x-alert variant="danger" dismissible>
                <div class="font-bold text-xs">Terdapat kesalahan pada input formulir:</div>
                <ul class="list-disc list-inside text-xs mt-1 space-y-0.5">
                    @foreach ($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </x-alert>
        @endif

        <form method="POST" action="{{ route('staff.store') }}" class="space-y-6">
            @csrf

            <!-- Section 1: Informasi Akun & Akses Aplikasi -->
            <x-card padding>
                <div class="border-b border-zinc-100 pb-3 mb-4">
                    <h2 class="text-sm font-bold text-zinc-900 flex items-center gap-2">
                        <span class="w-6 h-6 rounded-full bg-brand-50 text-brand-600 font-extrabold flex items-center justify-center text-xs">1</span>
                        Informasi Akun & Akses Aplikasi
                    </h2>
                    <p class="text-xs text-zinc-500 mt-0.5">
                        Kredensial login, peran otorisasi sistem, dan penempatan jabatan sekolah.
                    </p>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <!-- Nama Lengkap -->
                    <div class="sm:col-span-2">
                        <label for="name" class="block text-xs font-semibold text-zinc-700 mb-1">
                            Nama Lengkap & Gelar <span class="text-rose-500">*</span>
                        </label>
                        <input
                            type="text"
                            name="name"
                            id="name"
                            value="{{ old('name') }}"
                            required
                            placeholder="Contoh: Dra. Sri Wahyuni, M.Pd."
                            class="w-full rounded-lg border border-zinc-300 text-zinc-900 text-xs py-2 px-3 focus:border-brand-500 focus:ring-2 focus:ring-brand-100"
                        />
                    </div>

                    <!-- Email Resmi -->
                    <div>
                        <label for="email" class="block text-xs font-semibold text-zinc-700 mb-1">
                            Alamat Email (Unik) <span class="text-rose-500">*</span>
                        </label>
                        <input
                            type="email"
                            name="email"
                            id="email"
                            value="{{ old('email') }}"
                            required
                            placeholder="nama.pegawai@sekolah.sch.id"
                            class="w-full rounded-lg border border-zinc-300 text-zinc-900 text-xs py-2 px-3 focus:border-brand-500 focus:ring-2 focus:ring-brand-100"
                        />
                        <p class="text-[10px] text-zinc-400 mt-1">Digunakan untuk login dan pengiriman aktivasi akun.</p>
                    </div>

                    <!-- Nomor Telepon -->
                    <div>
                        <label for="phone" class="block text-xs font-semibold text-zinc-700 mb-1">
                            Nomor WhatsApp / Telepon
                        </label>
                        <input
                            type="text"
                            name="phone"
                            id="phone"
                            value="{{ old('phone') }}"
                            placeholder="Contoh: 081234567890"
                            class="w-full rounded-lg border border-zinc-300 text-zinc-900 text-xs py-2 px-3 focus:border-brand-500 focus:ring-2 focus:ring-brand-100"
                        />
                    </div>

                    <!-- Peran Akses Sistem (Role) -->
                    <div>
                        <label for="role" class="block text-xs font-semibold text-zinc-700 mb-1">
                            Peran Akses Sistem (Role) <span class="text-rose-500">*</span>
                        </label>
                        <select
                            name="role"
                            id="role"
                            required
                            class="w-full rounded-lg border border-zinc-300 text-zinc-900 text-xs py-2 px-3 focus:border-brand-500 focus:ring-2 focus:ring-brand-100"
                        >
                            <option value="">-- Pilih Peran Akses --</option>
                            @foreach ($allowedRoles as $r)
                                <option value="{{ $r->value }}" {{ old('role') === $r->value ? 'selected' : '' }}>
                                    {{ $r->label() }}
                                </option>
                            @endforeach
                        </select>
                        <p class="text-[10px] text-zinc-400 mt-1">Menentukan menu dan hak akses fitur yang dapat dibuka.</p>
                    </div>

                    <!-- Jabatan Kepegawaian (Position) -->
                    <div>
                        <label for="position" class="block text-xs font-semibold text-zinc-700 mb-1">
                            Jabatan Kepegawaian <span class="text-rose-500">*</span>
                        </label>
                        <select
                            name="position"
                            id="position"
                            required
                            class="w-full rounded-lg border border-zinc-300 text-zinc-900 text-xs py-2 px-3 focus:border-brand-500 focus:ring-2 focus:ring-brand-100"
                        >
                            <option value="">-- Pilih Jabatan Sekolah --</option>
                            @foreach ($positions as $pos)
                                <option value="{{ $pos->value }}" {{ old('position') === $pos->value ? 'selected' : '' }}>
                                    {{ $pos->label() }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Unit Kerja -->
                    <div class="sm:col-span-2">
                        <label for="work_unit" class="block text-xs font-semibold text-zinc-700 mb-1">
                            Unit Kerja / Divisi
                        </label>
                        <input
                            type="text"
                            name="work_unit"
                            id="work_unit"
                            value="{{ old('work_unit') }}"
                            placeholder="Contoh: Bidang Kurikulum, Laboratorium Komputer, Tata Usaha, dsb."
                            class="w-full rounded-lg border border-zinc-300 text-zinc-900 text-xs py-2 px-3 focus:border-brand-500 focus:ring-2 focus:ring-brand-100"
                        />
                    </div>
                </div>
            </x-card>

            <!-- Section 2: Identitas & Riwayat Kepegawaian -->
            <x-card padding>
                <div class="border-b border-zinc-100 pb-3 mb-4">
                    <h2 class="text-sm font-bold text-zinc-900 flex items-center gap-2">
                        <span class="w-6 h-6 rounded-full bg-zinc-100 text-zinc-700 font-extrabold flex items-center justify-center text-xs">2</span>
                        Identitas & Status Kepegawaian
                    </h2>
                    <p class="text-xs text-zinc-500 mt-0.5">
                        Nomor identitas kedinasan, jenis kelamin, dan riwayat status kepegawaian resmi.
                    </p>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <!-- NIP -->
                    <div>
                        <label for="nip" class="block text-xs font-semibold text-zinc-700 mb-1">
                            NIP (Nomor Induk Pegawai)
                        </label>
                        <input
                            type="text"
                            name="nip"
                            id="nip"
                            value="{{ old('nip') }}"
                            placeholder="18 digit NIP resmi"
                            class="w-full rounded-lg border border-zinc-300 text-zinc-900 text-xs py-2 px-3 focus:border-brand-500 focus:ring-2 focus:ring-brand-100"
                        />
                    </div>

                    <!-- NUPTK -->
                    <div>
                        <label for="nuptk" class="block text-xs font-semibold text-zinc-700 mb-1">
                            NUPTK
                        </label>
                        <input
                            type="text"
                            name="nuptk"
                            id="nuptk"
                            value="{{ old('nuptk') }}"
                            placeholder="16 digit NUPTK pendidik"
                            class="w-full rounded-lg border border-zinc-300 text-zinc-900 text-xs py-2 px-3 focus:border-brand-500 focus:ring-2 focus:ring-brand-100"
                        />
                    </div>

                    <!-- NIK -->
                    <div>
                        <label for="nik" class="block text-xs font-semibold text-zinc-700 mb-1">
                            NIK (KTP)
                        </label>
                        <input
                            type="text"
                            name="nik"
                            id="nik"
                            value="{{ old('nik') }}"
                            placeholder="16 digit NIK kependudukan"
                            class="w-full rounded-lg border border-zinc-300 text-zinc-900 text-xs py-2 px-3 focus:border-brand-500 focus:ring-2 focus:ring-brand-100"
                        />
                    </div>

                    <!-- Jenis Kelamin -->
                    <div>
                        <label for="gender" class="block text-xs font-semibold text-zinc-700 mb-1">
                            Jenis Kelamin <span class="text-rose-500">*</span>
                        </label>
                        <select
                            name="gender"
                            id="gender"
                            required
                            class="w-full rounded-lg border border-zinc-300 text-zinc-900 text-xs py-2 px-3 focus:border-brand-500 focus:ring-2 focus:ring-brand-100"
                        >
                            <option value="male" {{ old('gender') === 'male' ? 'selected' : '' }}>Laki-laki</option>
                            <option value="female" {{ old('gender') === 'female' ? 'selected' : '' }}>Perempuan</option>
                        </select>
                    </div>

                    <!-- Tempat Lahir -->
                    <div>
                        <label for="birth_place" class="block text-xs font-semibold text-zinc-700 mb-1">
                            Tempat Lahir
                        </label>
                        <input
                            type="text"
                            name="birth_place"
                            id="birth_place"
                            value="{{ old('birth_place') }}"
                            placeholder="Kota / Kabupaten"
                            class="w-full rounded-lg border border-zinc-300 text-zinc-900 text-xs py-2 px-3 focus:border-brand-500 focus:ring-2 focus:ring-brand-100"
                        />
                    </div>

                    <!-- Tanggal Lahir -->
                    <div>
                        <label for="birth_date" class="block text-xs font-semibold text-zinc-700 mb-1">
                            Tanggal Lahir
                        </label>
                        <input
                            type="date"
                            name="birth_date"
                            id="birth_date"
                            value="{{ old('birth_date') }}"
                            class="w-full rounded-lg border border-zinc-300 text-zinc-900 text-xs py-2 px-3 focus:border-brand-500 focus:ring-2 focus:ring-brand-100"
                        />
                    </div>

                    <!-- Status Kepegawaian -->
                    <div>
                        <label for="employment_status" class="block text-xs font-semibold text-zinc-700 mb-1">
                            Status Kepegawaian <span class="text-rose-500">*</span>
                        </label>
                        <select
                            name="employment_status"
                            id="employment_status"
                            required
                            class="w-full rounded-lg border border-zinc-300 text-zinc-900 text-xs py-2 px-3 focus:border-brand-500 focus:ring-2 focus:ring-brand-100"
                        >
                            <option value="gty" {{ old('employment_status') === 'gty' ? 'selected' : '' }}>Guru Tetap Yayasan (GTY)</option>
                            <option value="gtt" {{ old('employment_status') === 'gtt' ? 'selected' : '' }}>Guru Tidak Tetap (GTT)</option>
                            <option value="pns" {{ old('employment_status') === 'pns' ? 'selected' : '' }}>Pegawai Negeri Sipil (PNS)</option>
                            <option value="p3k" {{ old('employment_status') === 'p3k' ? 'selected' : '' }}>PPPK / P3K</option>
                            <option value="pty" {{ old('employment_status') === 'pty' ? 'selected' : '' }}>Pegawai Tetap Yayasan (PTY)</option>
                            <option value="ptt" {{ old('employment_status') === 'ptt' ? 'selected' : '' }}>Pegawai Tidak Tetap (PTT)</option>
                            <option value="honor" {{ old('employment_status') === 'honor' ? 'selected' : '' }}>Tenaga Honorer</option>
                        </select>
                    </div>

                    <!-- Tanggal Mulai Tugas -->
                    <div>
                        <label for="join_date" class="block text-xs font-semibold text-zinc-700 mb-1">
                            Tanggal Mulai Tugas
                        </label>
                        <input
                            type="date"
                            name="join_date"
                            id="join_date"
                            value="{{ old('join_date') }}"
                            class="w-full rounded-lg border border-zinc-300 text-zinc-900 text-xs py-2 px-3 focus:border-brand-500 focus:ring-2 focus:ring-brand-100"
                        />
                    </div>

                    <!-- Pendidikan Terakhir -->
                    <div>
                        <label for="last_education" class="block text-xs font-semibold text-zinc-700 mb-1">
                            Pendidikan Terakhir
                        </label>
                        <input
                            type="text"
                            name="last_education"
                            id="last_education"
                            value="{{ old('last_education') }}"
                            placeholder="Contoh: S1 Pendidikan Matematika"
                            class="w-full rounded-lg border border-zinc-300 text-zinc-900 text-xs py-2 px-3 focus:border-brand-500 focus:ring-2 focus:ring-brand-100"
                        />
                    </div>

                    <!-- Alamat Lengkap -->
                    <div class="sm:col-span-3">
                        <label for="address" class="block text-xs font-semibold text-zinc-700 mb-1">
                            Alamat Domisili
                        </label>
                        <textarea
                            name="address"
                            id="address"
                            rows="2"
                            placeholder="Alamat tempat tinggal pegawai..."
                            class="w-full rounded-lg border border-zinc-300 text-zinc-900 text-xs py-2 px-3 focus:border-brand-500 focus:ring-2 focus:ring-brand-100"
                        >{{ old('address') }}</textarea>
                    </div>

                    <!-- Catatan Tambahan -->
                    <div class="sm:col-span-3">
                        <label for="notes" class="block text-xs font-semibold text-zinc-700 mb-1">
                            Catatan Tambahan
                        </label>
                        <textarea
                            name="notes"
                            id="notes"
                            rows="2"
                            placeholder="Keterangan SK, penugasan khusus, sertifikasi pendidik, dll."
                            class="w-full rounded-lg border border-zinc-300 text-zinc-900 text-xs py-2 px-3 focus:border-brand-500 focus:ring-2 focus:ring-brand-100"
                        >{{ old('notes') }}</textarea>
                    </div>
                </div>
            </x-card>

            <!-- Section 3: Alur Aktivasi & Kata Sandi Akun -->
            <x-card padding>
                <div class="border-b border-zinc-100 pb-3 mb-4">
                    <h2 class="text-sm font-bold text-zinc-900 flex items-center gap-2">
                        <span class="w-6 h-6 rounded-full bg-zinc-100 text-zinc-700 font-extrabold flex items-center justify-center text-xs">3</span>
                        Opsi Pembuatan & Aktivasi Akun
                    </h2>
                    <p class="text-xs text-zinc-500 mt-0.5">
                        Pilih metode pengaturan kata sandi yang aman tanpa membagikan kata sandi plaintext bersama.
                    </p>
                </div>

                <div class="space-y-4">
                    <!-- Option A: Invitation Token -->
                    <label class="flex items-start gap-3 p-3 rounded-xl border transition-colors cursor-pointer"
                           :class="creationMode === 'invitation' ? 'border-brand-500 bg-brand-50/30' : 'border-zinc-200 hover:bg-zinc-50'">
                        <input
                            type="radio"
                            name="creation_mode"
                            value="invitation"
                            x-model="creationMode"
                            class="mt-1 text-brand-600 focus:ring-brand-500"
                        />
                        <div class="flex-1">
                            <div class="text-xs font-bold text-zinc-900 flex items-center gap-2">
                                <span>Kirim Tautan Undangan Aktivasi Mandiri</span>
                                <span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-emerald-100 text-emerald-800">Direkomendasikan</span>
                            </div>
                            <p class="text-xs text-zinc-500 mt-0.5">
                                Sistem akan menerbitkan token aktivasi unik. Anda dapat langsung menyalin tautan aktivasi untuk dibagikan ke pegawai (via WhatsApp/Email). Pegawai akan mengatur kata sandi pribadinya sendiri secara aman.
                            </p>
                        </div>
                    </label>

                    <!-- Option B: Direct Password Setup -->
                    <label class="flex items-start gap-3 p-3 rounded-xl border transition-colors cursor-pointer"
                           :class="creationMode === 'direct_password' ? 'border-brand-500 bg-brand-50/30' : 'border-zinc-200 hover:bg-zinc-50'">
                        <input
                            type="radio"
                            name="creation_mode"
                            value="direct_password"
                            x-model="creationMode"
                            class="mt-1 text-brand-600 focus:ring-brand-500"
                        />
                        <div class="flex-1">
                            <div class="text-xs font-bold text-zinc-900">
                                Tetapkan Kata Sandi Awal Khusus Langsung
                            </div>
                            <p class="text-xs text-zinc-500 mt-0.5">
                                Tentukan kata sandi awal mandiri khusus untuk pegawai ini (minimal 8 karakter). Akun langsung berstatus aktif dan dapat langsung digunakan untuk login.
                            </p>
                        </div>
                    </label>

                    <!-- Direct Password Inputs (Shown only if direct_password selected) -->
                    <div x-show="creationMode === 'direct_password'" x-transition class="pt-3 border-t border-zinc-100 grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label for="password" class="block text-xs font-semibold text-zinc-700 mb-1">
                                Kata Sandi Awal <span class="text-rose-500">*</span>
                            </label>
                            <input
                                type="password"
                                name="password"
                                id="password"
                                placeholder="Minimal 8 karakter"
                                class="w-full rounded-lg border border-zinc-300 text-zinc-900 text-xs py-2 px-3 focus:border-brand-500 focus:ring-2 focus:ring-brand-100"
                            />
                        </div>

                        <div>
                            <label for="password_confirmation" class="block text-xs font-semibold text-zinc-700 mb-1">
                                Konfirmasi Kata Sandi Awal <span class="text-rose-500">*</span>
                            </label>
                            <input
                                type="password"
                                name="password_confirmation"
                                id="password_confirmation"
                                placeholder="Ulangi kata sandi"
                                class="w-full rounded-lg border border-zinc-300 text-zinc-900 text-xs py-2 px-3 focus:border-brand-500 focus:ring-2 focus:ring-brand-100"
                            />
                        </div>
                    </div>
                </div>
            </x-card>

            <!-- Submit Button Bar -->
            <div class="flex flex-col-reverse sm:flex-row sm:items-center justify-end gap-2.5 sm:gap-3 pt-2">
                <x-button href="{{ route('staff.index') }}" variant="secondary" size="md" class="w-full sm:w-auto justify-center">
                    Batal
                </x-button>

                <x-button type="submit" variant="primary" size="md" class="w-full sm:w-auto justify-center">
                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    Simpan & Daftarkan Pegawai
                </x-button>
            </div>
        </form>
    </div>
</x-layouts.app>
