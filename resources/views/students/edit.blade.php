<x-layouts.app>
    <x-slot:headerTitle>
        Edit Profil Siswa
    </x-slot:headerTitle>

    <div class="max-w-3xl mx-auto space-y-6">
        <div>
            <div class="flex items-center gap-2 text-xs text-zinc-500 mb-1">
                <a href="{{ route('students.show', $student) }}" class="hover:text-zinc-800">&larr; Kembali ke Profil Siswa</a>
            </div>
            <h1 class="text-xl sm:text-2xl font-extrabold text-zinc-900 tracking-tight">
                Edit Profil: {{ $student->name }}
            </h1>
            <p class="text-xs text-zinc-500 mt-0.5">
                Perbarui informasi identitas peserta didik, kontak, dan data orang tua/wali.
            </p>
        </div>

        @if ($errors->any())
            <x-alert variant="danger" dismissible>
                {{ $errors->first() }}
            </x-alert>
        @endif

        <form method="POST" action="{{ route('students.update', $student) }}" class="space-y-6">
            @csrf
            @method('PUT')

            <!-- Section 1: Akun & Identitas -->
            <x-card title="Identitas & Kontak Utama" padding>
                <div class="space-y-4">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <x-input
                            label="Nama Lengkap Siswa"
                            name="name"
                            value="{{ old('name', $student->name) }}"
                            required
                        />

                        <x-input
                            type="email"
                            label="Alamat Email"
                            name="email"
                            value="{{ old('email', $student->email) }}"
                            required
                        />
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <x-input
                            label="Nomor Induk Siswa (NIS)"
                            name="nis"
                            value="{{ old('nis', $profile?->nis) }}"
                        />

                        <x-input
                            label="Nomor Induk Siswa Nasional (NISN)"
                            name="nisn"
                            value="{{ old('nisn', $profile?->nisn) }}"
                        />
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <x-select
                            label="Jenis Kelamin"
                            name="gender"
                        >
                            <option value="">-- Pilih --</option>
                            <option value="male" {{ old('gender', $profile?->gender) === 'male' ? 'selected' : '' }}>Laki-laki</option>
                            <option value="female" {{ old('gender', $profile?->gender) === 'female' ? 'selected' : '' }}>Perempuan</option>
                        </x-select>

                        <x-input
                            label="Tempat Lahir"
                            name="birth_place"
                            value="{{ old('birth_place', $profile?->birth_place) }}"
                        />

                        <x-input
                            type="date"
                            label="Tanggal Lahir"
                            name="birth_date"
                            value="{{ old('birth_date', $profile?->birth_date?->format('Y-m-d')) }}"
                        />
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <x-input
                            label="Agama"
                            name="religion"
                            value="{{ old('religion', $profile?->religion) }}"
                        />

                        <x-input
                            label="Nomor Telepon Siswa"
                            name="phone"
                            value="{{ old('phone', $student->phone) }}"
                        />
                    </div>

                    <x-textarea
                        label="Alamat Tempat Tinggal"
                        name="address"
                        rows="2"
                    >{{ old('address', $profile?->address) }}</x-textarea>
                </div>
            </x-card>

            <!-- Section 2: Data Orang Tua / Wali -->
            <x-card title="Data Orang Tua / Wali" padding>
                <div class="space-y-4">
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <x-input
                            label="Nama Orang Tua / Wali"
                            name="parent_name"
                            value="{{ old('parent_name', $profile?->parent_name) }}"
                        />

                        <x-select
                            label="Hubungan Keluarga"
                            name="parent_relationship"
                        >
                            <option value="Ayah" {{ old('parent_relationship', $profile?->parent_relationship) === 'Ayah' ? 'selected' : '' }}>Ayah</option>
                            <option value="Ibu" {{ old('parent_relationship', $profile?->parent_relationship) === 'Ibu' ? 'selected' : '' }}>Ibu</option>
                            <option value="Wali" {{ old('parent_relationship', $profile?->parent_relationship) === 'Wali' ? 'selected' : '' }}>Wali</option>
                        </x-select>

                        <x-input
                            label="Nomor Telepon Wali"
                            name="parent_phone"
                            value="{{ old('parent_phone', $profile?->parent_phone) }}"
                        />
                    </div>

                    <x-textarea
                        label="Catatan Tambahan"
                        name="notes"
                        rows="2"
                    >{{ old('notes', $profile?->notes) }}</x-textarea>
                </div>
            </x-card>

            <div class="flex items-center justify-end gap-3">
                <x-button href="{{ route('students.show', $student) }}" variant="secondary" size="md">
                    Batal
                </x-button>
                <x-button type="submit" variant="primary" size="md">
                    Simpan Perubahan
                </x-button>
            </div>
        </form>
    </div>
</x-layouts.app>
