<x-layouts.app>
    <x-slot:headerTitle>
        Registrasi Siswa Baru
    </x-slot:headerTitle>

    <div class="max-w-3xl mx-auto space-y-6">
        <div>
            <div class="flex items-center gap-2 text-xs text-zinc-500 mb-1">
                <a href="{{ route('students.index') }}" class="hover:text-zinc-800">&larr; Kembali ke Daftar Siswa</a>
            </div>
            <h1 class="text-xl sm:text-2xl font-extrabold text-zinc-900 tracking-tight">
                Registrasi Peserta Didik Baru
            </h1>
            <p class="text-xs text-zinc-500 mt-0.5">
                Daftarkan akun peserta didik baru, data induk kesiswaan, kontak wali, dan penempatan kelas awal.
            </p>
        </div>

        @if ($errors->any())
            <x-alert variant="danger" dismissible>
                {{ $errors->first() }}
            </x-alert>
        @endif

        <form method="POST" action="{{ route('students.store') }}" class="space-y-6">
            @csrf

            <!-- Section 1: Informasi Akun & Identitas Utama -->
            <x-card title="1. Akun & Identitas Utama" padding>
                <div class="space-y-4">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <x-input
                            label="Nama Lengkap Siswa"
                            name="name"
                            placeholder="Contoh: Rian Pratama Putra"
                            value="{{ old('name') }}"
                            required
                        />

                        <x-input
                            type="email"
                            label="Alamat Email (Akun Login)"
                            name="email"
                            placeholder="siswa@eduvia.sch.id"
                            value="{{ old('email') }}"
                            required
                        />
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <x-input
                            label="Nomor Induk Siswa (NIS)"
                            name="nis"
                            placeholder="Contoh: 202507010"
                            value="{{ old('nis') }}"
                        />

                        <x-input
                            label="Nomor Induk Siswa Nasional (NISN)"
                            name="nisn"
                            placeholder="Contoh: 0098765432"
                            value="{{ old('nisn') }}"
                        />
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <x-select
                            label="Jenis Kelamin"
                            name="gender"
                        >
                            <option value="">-- Pilih --</option>
                            <option value="male" {{ old('gender') === 'male' ? 'selected' : '' }}>Laki-laki</option>
                            <option value="female" {{ old('gender') === 'female' ? 'selected' : '' }}>Perempuan</option>
                        </x-select>

                        <x-input
                            label="Tempat Lahir"
                            name="birth_place"
                            placeholder="Contoh: Bandung"
                            value="{{ old('birth_place') }}"
                        />

                        <x-input
                            type="date"
                            label="Tanggal Lahir"
                            name="birth_date"
                            value="{{ old('birth_date') }}"
                        />
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <x-input
                            label="Agama"
                            name="religion"
                            placeholder="Islam, Kristen, Katolik, Hindu, Buddha, Konghucu"
                            value="{{ old('religion') }}"
                        />

                        <x-input
                            label="Nomor Telepon / WhatsApp Siswa"
                            name="phone"
                            placeholder="08xxxxxxxxxx"
                            value="{{ old('phone') }}"
                        />
                    </div>

                    <x-textarea
                        label="Alamat Tempat Tinggal"
                        name="address"
                        placeholder="Alamat lengkap tempat tinggal siswa..."
                        rows="2"
                    >{{ old('address') }}</x-textarea>
                </div>
            </x-card>

            <!-- Section 2: Data Orang Tua / Wali -->
            <x-card title="2. Orang Tua / Wali & Kontak Darurat" padding>
                <div class="space-y-4">
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <x-input
                            label="Nama Orang Tua / Wali"
                            name="parent_name"
                            placeholder="Nama ayah / ibu / wali"
                            value="{{ old('parent_name') }}"
                        />

                        <x-select
                            label="Hubungan Keluarga"
                            name="parent_relationship"
                        >
                            <option value="Ayah" {{ old('parent_relationship') === 'Ayah' ? 'selected' : '' }}>Ayah</option>
                            <option value="Ibu" {{ old('parent_relationship') === 'Ibu' ? 'selected' : '' }}>Ibu</option>
                            <option value="Wali" {{ old('parent_relationship') === 'Wali' ? 'selected' : '' }}>Wali</option>
                        </x-select>

                        <x-input
                            label="Nomor Telepon Wali"
                            name="parent_phone"
                            placeholder="08xxxxxxxxxx"
                            value="{{ old('parent_phone') }}"
                        />
                    </div>
                </div>
            </x-card>

            <!-- Section 3: Status Kesiswaan & Penempatan Rombel -->
            <x-card title="3. Status Kesiswaan & Penempatan Kelas" padding>
                <div class="space-y-4">
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <x-select
                            label="Status Kesiswaan"
                            name="student_status"
                            required
                        >
                            <option value="active" {{ old('student_status', 'active') === 'active' ? 'selected' : '' }}>Aktif</option>
                            <option value="transferred" {{ old('student_status') === 'transferred' ? 'selected' : '' }}>Pindahan / Mutasi</option>
                            <option value="inactive" {{ old('student_status') === 'inactive' ? 'selected' : '' }}>Non-Aktif</option>
                        </x-select>

                        <x-input
                            type="date"
                            label="Tanggal Terdaftar Masuk"
                            name="enrollment_date"
                            value="{{ old('enrollment_date', date('Y-m-d')) }}"
                        />

                        <x-select
                            label="Penempatan Rombel Awal (Opsional)"
                            name="classroom_id"
                            hint="Dapat ditempatkan sekarang atau nanti."
                        >
                            <option value="">-- Belum Ditempatkan --</option>
                            @foreach ($classrooms as $c)
                                <option value="{{ $c->id }}" {{ old('classroom_id') == $c->id ? 'selected' : '' }}>
                                    {{ $c->name }} ({{ $c->gradeLevel?->displayLabel() }})
                                </option>
                            @endforeach
                        </x-select>
                    </div>

                    <x-textarea
                        label="Catatan Tambahan"
                        name="notes"
                        placeholder="Keterangan jalur masuk, beasiswa, atau catatan khusus..."
                        rows="2"
                    >{{ old('notes') }}</x-textarea>
                </div>
            </x-card>

            <div class="flex items-center justify-end gap-3">
                <x-button href="{{ route('students.index') }}" variant="secondary" size="md">
                    Batal
                </x-button>
                <x-button type="submit" variant="primary" size="md">
                    Simpan & Daftarkan Siswa &rarr;
                </x-button>
            </div>
        </form>
    </div>
</x-layouts.app>
