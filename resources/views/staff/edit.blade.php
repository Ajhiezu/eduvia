<x-layouts.app>
    <x-slot:headerTitle>
        Edit Data Pegawai: {{ $staff->name }}
    </x-slot:headerTitle>

    <div class="max-w-4xl mx-auto space-y-6" x-data="{ changePassword: false }">
        <!-- Breadcrumb & Header -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div>
                <div class="flex items-center gap-2 text-xs text-zinc-500 mb-1">
                    <a href="{{ route('staff.index') }}" class="hover:text-brand-600 transition-colors">Pendidik & Staf</a>
                    <span>/</span>
                    <a href="{{ route('staff.show', $staff) }}" class="hover:text-brand-600 transition-colors">{{ $staff->name }}</a>
                    <span>/</span>
                    <span class="text-zinc-800 font-medium">Edit Profil</span>
                </div>
                <h1 class="text-xl sm:text-2xl font-extrabold text-zinc-900 tracking-tight">
                    Edit Data Pegawai
                </h1>
                <p class="text-xs text-zinc-500 mt-0.5">
                    Perbarui profil kepegawaian, jabatan, status keaktifan, dan kredensial akun.
                </p>
            </div>

            <x-button href="{{ route('staff.show', $staff) }}" variant="secondary" size="sm" class="self-start sm:self-auto shrink-0">
                Batal & Kembali
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

        <form method="POST" action="{{ route('staff.update', $staff) }}" class="space-y-6">
            @csrf
            @method('PUT')

            <!-- Section 1: Akun & Hak Akses -->
            <x-card padding>
                <div class="border-b border-zinc-100 pb-3 mb-4">
                    <h2 class="text-sm font-bold text-zinc-900">
                        1. Data Akun & Hak Akses
                    </h2>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="sm:col-span-2">
                        <label for="name" class="block text-sm font-medium text-zinc-700 mb-1.5">
                            Nama Lengkap & Gelar <span class="text-rose-500">*</span>
                        </label>
                        <input
                            type="text"
                            name="name"
                            id="name"
                            value="{{ old('name', $staff->name) }}"
                            required
                            class="w-full rounded-lg border border-zinc-300 text-zinc-900 text-xs py-2 px-3 focus:border-brand-500 focus:ring-2 focus:ring-brand-100"
                        />
                    </div>

                    <div>
                        <label for="email" class="block text-sm font-medium text-zinc-700 mb-1.5">
                            Alamat Email <span class="text-rose-500">*</span>
                        </label>
                        <input
                            type="email"
                            name="email"
                            id="email"
                            value="{{ old('email', $staff->email) }}"
                            required
                            class="w-full rounded-lg border border-zinc-300 text-zinc-900 text-xs py-2 px-3 focus:border-brand-500 focus:ring-2 focus:ring-brand-100"
                        />
                    </div>

                    <div>
                        <label for="phone" class="block text-sm font-medium text-zinc-700 mb-1.5">
                            Nomor Telepon / WhatsApp
                        </label>
                        <input
                            type="text"
                            name="phone"
                            id="phone"
                            value="{{ old('phone', $staff->phone) }}"
                            class="w-full rounded-lg border border-zinc-300 text-zinc-900 text-xs py-2 px-3 focus:border-brand-500 focus:ring-2 focus:ring-brand-100"
                        />
                    </div>

                    <div>
                        <label for="role" class="block text-sm font-medium text-zinc-700 mb-1.5">
                            Peran Akses Sistem (Role) <span class="text-rose-500">*</span>
                        </label>
                        <select
                            name="role"
                            id="role"
                            required
                            {{ auth()->id() === $staff->id ? 'disabled' : '' }}
                            class="w-full rounded-lg border border-zinc-300 text-zinc-900 text-xs py-2 px-3 focus:border-brand-500 focus:ring-2 focus:ring-brand-100"
                        >
                            @foreach ($allowedRoles as $r)
                                <option value="{{ $r->value }}" {{ old('role', $staff->role->value) === $r->value ? 'selected' : '' }}>
                                    {{ $r->label() }}
                                </option>
                            @endforeach
                        </select>
                        @if (auth()->id() === $staff->id)
                            <input type="hidden" name="role" value="{{ $staff->role->value }}">
                            <p class="text-[10px] text-zinc-400 mt-1">Anda tidak dapat mengubah peran akun Anda sendiri.</p>
                        @endif
                    </div>

                    <div>
                        <label for="position" class="block text-sm font-medium text-zinc-700 mb-1.5">
                            Jabatan Kepegawaian <span class="text-rose-500">*</span>
                        </label>
                        <select
                            name="position"
                            id="position"
                            required
                            class="w-full rounded-lg border border-zinc-300 text-zinc-900 text-xs py-2 px-3 focus:border-brand-500 focus:ring-2 focus:ring-brand-100"
                        >
                            @foreach ($positions as $pos)
                                <option value="{{ $pos->value }}" {{ old('position', $staff->staffProfile?->position?->value) === $pos->value ? 'selected' : '' }}>
                                    {{ $pos->label() }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="work_unit" class="block text-sm font-medium text-zinc-700 mb-1.5">
                            Unit Kerja / Divisi
                        </label>
                        <input
                            type="text"
                            name="work_unit"
                            id="work_unit"
                            value="{{ old('work_unit', $staff->staffProfile?->work_unit) }}"
                            class="w-full rounded-lg border border-zinc-300 text-zinc-900 text-xs py-2 px-3 focus:border-brand-500 focus:ring-2 focus:ring-brand-100"
                        />
                    </div>

                    <div>
                        <label for="status" class="block text-sm font-medium text-zinc-700 mb-1.5">
                            Status Keaktifan Akun <span class="text-rose-500">*</span>
                        </label>
                        <select
                            name="status"
                            id="status"
                            required
                            class="w-full rounded-lg border border-zinc-300 text-zinc-900 text-xs py-2 px-3 focus:border-brand-500 focus:ring-2 focus:ring-brand-100"
                        >
                            <option value="active" {{ old('status', $staff->staffProfile?->status ?? ($staff->is_active ? 'active' : 'inactive')) === 'active' ? 'selected' : '' }}>Aktif</option>
                            <option value="inactive" {{ old('status', $staff->staffProfile?->status) === 'inactive' ? 'selected' : '' }}>Non-Aktif</option>
                            <option value="on_leave" {{ old('status', $staff->staffProfile?->status) === 'on_leave' ? 'selected' : '' }}>Cuti</option>
                            <option value="resigned" {{ old('status', $staff->staffProfile?->status) === 'resigned' ? 'selected' : '' }}>Berhenti / Mutasi</option>
                        </select>
                    </div>
                </div>
            </x-card>

            <!-- Section 2: Identitas Kepegawaian Lengkap -->
            <x-card padding>
                <div class="border-b border-zinc-100 pb-3 mb-4">
                    <h2 class="text-sm font-bold text-zinc-900">
                        2. Identitas & Berkas Kepegawaian
                    </h2>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label for="nip" class="block text-sm font-medium text-zinc-700 mb-1.5">NIP</label>
                        <input
                            type="text"
                            name="nip"
                            id="nip"
                            value="{{ old('nip', $staff->staffProfile?->nip) }}"
                            class="w-full rounded-lg border border-zinc-300 text-zinc-900 text-xs py-2 px-3 focus:border-brand-500 focus:ring-2 focus:ring-brand-100"
                        />
                    </div>

                    <div>
                        <label for="nuptk" class="block text-sm font-medium text-zinc-700 mb-1.5">NUPTK</label>
                        <input
                            type="text"
                            name="nuptk"
                            id="nuptk"
                            value="{{ old('nuptk', $staff->staffProfile?->nuptk) }}"
                            class="w-full rounded-lg border border-zinc-300 text-zinc-900 text-xs py-2 px-3 focus:border-brand-500 focus:ring-2 focus:ring-brand-100"
                        />
                    </div>

                    <div>
                        <label for="nik" class="block text-sm font-medium text-zinc-700 mb-1.5">NIK</label>
                        <input
                            type="text"
                            name="nik"
                            id="nik"
                            value="{{ old('nik', $staff->staffProfile?->nik) }}"
                            class="w-full rounded-lg border border-zinc-300 text-zinc-900 text-xs py-2 px-3 focus:border-brand-500 focus:ring-2 focus:ring-brand-100"
                        />
                    </div>

                    <div>
                        <label for="gender" class="block text-sm font-medium text-zinc-700 mb-1.5">Jenis Kelamin <span class="text-rose-500">*</span></label>
                        <select
                            name="gender"
                            id="gender"
                            required
                            class="w-full rounded-lg border border-zinc-300 text-zinc-900 text-xs py-2 px-3 focus:border-brand-500 focus:ring-2 focus:ring-brand-100"
                        >
                            <option value="male" {{ old('gender', $staff->staffProfile?->gender) === 'male' ? 'selected' : '' }}>Laki-laki</option>
                            <option value="female" {{ old('gender', $staff->staffProfile?->gender) === 'female' ? 'selected' : '' }}>Perempuan</option>
                        </select>
                    </div>

                    <div>
                        <label for="birth_place" class="block text-sm font-medium text-zinc-700 mb-1.5">Tempat Lahir</label>
                        <input
                            type="text"
                            name="birth_place"
                            id="birth_place"
                            value="{{ old('birth_place', $staff->staffProfile?->birth_place) }}"
                            class="w-full rounded-lg border border-zinc-300 text-zinc-900 text-xs py-2 px-3 focus:border-brand-500 focus:ring-2 focus:ring-brand-100"
                        />
                    </div>

                    <div>
                        <label for="birth_date" class="block text-sm font-medium text-zinc-700 mb-1.5">Tanggal Lahir</label>
                        <input
                            type="date"
                            name="birth_date"
                            id="birth_date"
                            value="{{ old('birth_date', $staff->staffProfile?->birth_date?->format('Y-m-d')) }}"
                            class="w-full rounded-lg border border-zinc-300 text-zinc-900 text-xs py-2 px-3 focus:border-brand-500 focus:ring-2 focus:ring-brand-100"
                        />
                    </div>

                    <div>
                        <label for="employment_status" class="block text-sm font-medium text-zinc-700 mb-1.5">Status Kepegawaian <span class="text-rose-500">*</span></label>
                        <select
                            name="employment_status"
                            id="employment_status"
                            required
                            class="w-full rounded-lg border border-zinc-300 text-zinc-900 text-xs py-2 px-3 focus:border-brand-500 focus:ring-2 focus:ring-brand-100"
                        >
                            <option value="gty" {{ old('employment_status', $staff->staffProfile?->employment_status) === 'gty' ? 'selected' : '' }}>Guru Tetap Yayasan (GTY)</option>
                            <option value="gtt" {{ old('employment_status', $staff->staffProfile?->employment_status) === 'gtt' ? 'selected' : '' }}>Guru Tidak Tetap (GTT)</option>
                            <option value="pns" {{ old('employment_status', $staff->staffProfile?->employment_status) === 'pns' ? 'selected' : '' }}>Pegawai Negeri Sipil (PNS)</option>
                            <option value="p3k" {{ old('employment_status', $staff->staffProfile?->employment_status) === 'p3k' ? 'selected' : '' }}>PPPK / P3K</option>
                            <option value="pty" {{ old('employment_status', $staff->staffProfile?->employment_status) === 'pty' ? 'selected' : '' }}>Pegawai Tetap Yayasan (PTY)</option>
                            <option value="ptt" {{ old('employment_status', $staff->staffProfile?->employment_status) === 'ptt' ? 'selected' : '' }}>Pegawai Tidak Tetap (PTT)</option>
                            <option value="honor" {{ old('employment_status', $staff->staffProfile?->employment_status) === 'honor' ? 'selected' : '' }}>Tenaga Honorer</option>
                        </select>
                    </div>

                    <div>
                        <label for="join_date" class="block text-sm font-medium text-zinc-700 mb-1.5">Tanggal Mulai Tugas</label>
                        <input
                            type="date"
                            name="join_date"
                            id="join_date"
                            value="{{ old('join_date', $staff->staffProfile?->join_date?->format('Y-m-d')) }}"
                            class="w-full rounded-lg border border-zinc-300 text-zinc-900 text-xs py-2 px-3 focus:border-brand-500 focus:ring-2 focus:ring-brand-100"
                        />
                    </div>

                    <div>
                        <label for="last_education" class="block text-sm font-medium text-zinc-700 mb-1.5">Pendidikan Terakhir</label>
                        <input
                            type="text"
                            name="last_education"
                            id="last_education"
                            value="{{ old('last_education', $staff->staffProfile?->last_education) }}"
                            class="w-full rounded-lg border border-zinc-300 text-zinc-900 text-xs py-2 px-3 focus:border-brand-500 focus:ring-2 focus:ring-brand-100"
                        />
                    </div>

                    <div class="sm:col-span-3">
                        <label for="address" class="block text-sm font-medium text-zinc-700 mb-1.5">Alamat Domisili</label>
                        <textarea
                            name="address"
                            id="address"
                            rows="2"
                            class="w-full rounded-lg border border-zinc-300 text-zinc-900 text-xs py-2 px-3 focus:border-brand-500 focus:ring-2 focus:ring-brand-100"
                        >{{ old('address', $staff->staffProfile?->address) }}</textarea>
                    </div>

                    <div class="sm:col-span-3">
                        <label for="notes" class="block text-sm font-medium text-zinc-700 mb-1.5">Catatan Tambahan</label>
                        <textarea
                            name="notes"
                            id="notes"
                            rows="2"
                            class="w-full rounded-lg border border-zinc-300 text-zinc-900 text-xs py-2 px-3 focus:border-brand-500 focus:ring-2 focus:ring-brand-100"
                        >{{ old('notes', $staff->staffProfile?->notes) }}</textarea>
                    </div>
                </div>
            </x-card>

            <!-- Section 3: Reset Kata Sandi (Opsional) -->
            <x-card padding>
                <div class="flex items-center justify-between border-b border-zinc-100 pb-3 mb-4">
                    <div>
                        <h2 class="text-sm font-bold text-zinc-900">
                            3. Pengaturan Kata Sandi
                        </h2>
                        <p class="text-xs text-zinc-500 mt-0.5">
                            Ubah kata sandi akun jika pegawai lupa kata sandi atau memerlukan reset manual.
                        </p>
                    </div>

                    <label class="flex items-center gap-2 text-xs font-semibold text-zinc-700 cursor-pointer">
                        <input type="checkbox" name="change_password" value="1" x-model="changePassword" class="rounded text-brand-600 focus:ring-brand-500">
                        Ubah Kata Sandi
                    </label>
                </div>

                <div x-show="changePassword" x-transition class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="password" class="block text-sm font-medium text-zinc-700 mb-1.5">
                            Kata Sandi Baru <span class="text-rose-500">*</span>
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
                        <label for="password_confirmation" class="block text-sm font-medium text-zinc-700 mb-1.5">
                            Konfirmasi Kata Sandi Baru <span class="text-rose-500">*</span>
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
            </x-card>

            <!-- Submit Button Bar -->
            <div class="flex flex-col-reverse sm:flex-row sm:items-center justify-end gap-2.5 sm:gap-3 pt-2">
                <x-button href="{{ route('staff.show', $staff) }}" variant="secondary" size="md" class="w-full sm:w-auto justify-center">
                    Batal
                </x-button>

                <x-button type="submit" variant="primary" size="md" class="w-full sm:w-auto justify-center">
                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    Perbarui Data Pegawai
                </x-button>
            </div>
        </form>
    </div>
</x-layouts.app>
