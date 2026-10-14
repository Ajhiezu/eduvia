<x-layouts.guest>
    <x-slot:title>
        Formulir Pendaftaran &mdash; {{ $period->name }} &mdash; Eduvia
    </x-slot:title>

    <div class="py-10 bg-zinc-50 min-h-screen">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            <!-- Navigation Back -->
            <div>
                <a href="{{ route('ppdb.index') }}" class="inline-flex items-center text-xs font-semibold text-zinc-500 hover:text-zinc-800 transition-colors">
                    &larr; Kembali ke Daftar Periode PPDB
                </a>
                <h1 class="text-2xl sm:text-3xl font-extrabold text-zinc-900 tracking-tight mt-2">
                    Formulir Pendaftaran Siswa Baru
                </h1>
                <p class="text-xs text-zinc-500 mt-1">
                    {{ $period->name }} &bull; Tahun Ajaran: {{ $period->academicYear?->name ?? '-' }}
                </p>
            </div>

            <!-- Error Alerts -->
            @if ($errors->any())
                <x-alert variant="danger" dismissible>
                    <div class="font-bold">Mohon perbaiki kesalahan berikut sebelum mengirim formulir:</div>
                    <ul class="list-disc pl-5 mt-1 space-y-0.5 text-xs">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </x-alert>
            @endif

            <form action="{{ route('ppdb.register.submit', $period) }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                @csrf

                <!-- Section 1: Jalur Pendaftaran & Tingkat -->
                <x-card padding class="border-zinc-200">
                    <h2 class="text-sm font-bold text-zinc-900 tracking-tight pb-3 border-b border-zinc-100 flex items-center gap-2">
                        <span class="w-5 h-5 rounded-full bg-red-100 text-primary font-bold text-xs flex items-center justify-center">1</span>
                        Jalur & Tingkat Pendaftaran
                    </h2>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mt-4">
                        <div>
                            <x-label for="admission_track_id" required>Pilih Jalur Pendaftaran</x-label>
                            <select
                                id="admission_track_id"
                                name="admission_track_id"
                                required
                                class="w-full mt-1 px-3 py-2 text-xs sm:text-sm bg-white border border-zinc-200 rounded-lg text-zinc-800 focus:outline-none focus:ring-2 focus:ring-red-100 focus:border-primary"
                            >
                                <option value="">-- Pilih Jalur --</option>
                                @foreach ($period->tracks as $track)
                                    <option value="{{ $track->id }}" @selected(old('admission_track_id') == $track->id)>
                                        {{ $track->name }} (Kuota: {{ $track->quota }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <x-label for="target_grade_level_id">Tingkat Kelas Tujuan</x-label>
                            <select
                                id="target_grade_level_id"
                                name="target_grade_level_id"
                                class="w-full mt-1 px-3 py-2 text-xs sm:text-sm bg-white border border-zinc-200 rounded-lg text-zinc-800 focus:outline-none focus:ring-2 focus:ring-red-100 focus:border-primary"
                            >
                                <option value="">-- Sesuai Jenjang Penerimaan --</option>
                                @foreach ($gradeLevels as $grade)
                                    <option value="{{ $grade->id }}" @selected(old('target_grade_level_id') == $grade->id)>
                                        {{ $grade->name }} ({{ strtoupper($grade->educational_level) }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </x-card>

                <!-- Section 2: Biodata Calon Peserta Didik -->
                <x-card padding class="border-zinc-200">
                    <h2 class="text-sm font-bold text-zinc-900 tracking-tight pb-3 border-b border-zinc-100 flex items-center gap-2">
                        <span class="w-5 h-5 rounded-full bg-red-100 text-primary font-bold text-xs flex items-center justify-center">2</span>
                        Biodata Calon Peserta Didik
                    </h2>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mt-4">
                        <div class="sm:col-span-2">
                            <x-label for="full_name" required>Nama Lengkap (Sesuai Akta Kelahiran)</x-label>
                            <x-input
                                id="full_name"
                                name="full_name"
                                :value="old('full_name')"
                                placeholder="Masukkan nama lengkap calon siswa"
                                required
                            />
                        </div>

                        <div>
                            <x-label for="nisn">NISN (Nomor Induk Siswa Nasional)</x-label>
                            <x-input
                                id="nisn"
                                name="nisn"
                                :value="old('nisn')"
                                placeholder="Contoh: 0098765432"
                                maxlength="20"
                            />
                        </div>

                        <div>
                            <x-label for="nik">NIK (Nomor Induk Kependudukan)</x-label>
                            <x-input
                                id="nik"
                                name="nik"
                                :value="old('nik')"
                                placeholder="16 digit NIK calon siswa"
                                maxlength="20"
                            />
                        </div>

                        <div>
                            <x-label for="gender" required>Jenis Kelamin</x-label>
                            <select
                                id="gender"
                                name="gender"
                                required
                                class="w-full mt-1 px-3 py-2 text-xs sm:text-sm bg-white border border-zinc-200 rounded-lg text-zinc-800 focus:outline-none focus:ring-2 focus:ring-red-100 focus:border-primary"
                            >
                                <option value="">-- Pilih Jenis Kelamin --</option>
                                <option value="male" @selected(old('gender') == 'male')>Laki-laki</option>
                                <option value="female" @selected(old('gender') == 'female')>Perempuan</option>
                            </select>
                        </div>

                        <div>
                            <x-label for="religion" required>Agama</x-label>
                            <select
                                id="religion"
                                name="religion"
                                required
                                class="w-full mt-1 px-3 py-2 text-xs sm:text-sm bg-white border border-zinc-200 rounded-lg text-zinc-800 focus:outline-none focus:ring-2 focus:ring-red-100 focus:border-primary"
                            >
                                <option value="Islam" @selected(old('religion', 'Islam') == 'Islam')>Islam</option>
                                <option value="Kristen" @selected(old('religion') == 'Kristen')>Kristen</option>
                                <option value="Katolik" @selected(old('religion') == 'Katolik')>Katolik</option>
                                <option value="Hindu" @selected(old('religion') == 'Hindu')>Hindu</option>
                                <option value="Buddha" @selected(old('religion') == 'Buddha')>Buddha</option>
                                <option value="Konghucu" @selected(old('religion') == 'Konghucu')>Konghucu</option>
                            </select>
                        </div>

                        <div>
                            <x-label for="birth_place" required>Tempat Lahir</x-label>
                            <x-input
                                id="birth_place"
                                name="birth_place"
                                :value="old('birth_place')"
                                placeholder="Contoh: Jakarta"
                                required
                            />
                        </div>

                        <div>
                            <x-label for="birth_date" required>Tanggal Lahir</x-label>
                            <x-input
                                id="birth_date"
                                name="birth_date"
                                type="date"
                                :value="old('birth_date')"
                                required
                            />
                        </div>

                        <div>
                            <x-label for="previous_school" required>Asal Sekolah (SD/MI/SMP/Sederajat)</x-label>
                            <x-input
                                id="previous_school"
                                name="previous_school"
                                :value="old('previous_school')"
                                placeholder="Contoh: SD Negeri 01 Pagi"
                                required
                            />
                        </div>

                        <div>
                            <x-label for="phone" required>Nomor WhatsApp / HP Aktif</x-label>
                            <x-input
                                id="phone"
                                name="phone"
                                :value="old('phone')"
                                placeholder="Contoh: 081234567890"
                                required
                            />
                        </div>

                        <div class="sm:col-span-2">
                            <x-label for="email">Alamat Email (Opsional)</x-label>
                            <x-input
                                id="email"
                                name="email"
                                type="email"
                                :value="old('email')"
                                placeholder="calon.siswa@example.com"
                            />
                        </div>

                        <div class="sm:col-span-2">
                            <x-label for="address" required>Alamat Domisili Lengkap</x-label>
                            <textarea
                                id="address"
                                name="address"
                                rows="2"
                                required
                                placeholder="Nama jalan, RT/RW, Kelurahan, Kecamatan, Kota/Kabupaten"
                                class="w-full mt-1 px-3 py-2 text-xs sm:text-sm bg-white border border-zinc-200 rounded-lg text-zinc-800 placeholder-zinc-400 focus:outline-none focus:ring-2 focus:ring-red-100 focus:border-primary"
                            >{{ old('address') }}</textarea>
                        </div>
                    </div>
                </x-card>

                <!-- Section 3: Data Orang Tua / Wali -->
                <x-card padding class="border-zinc-200">
                    <h2 class="text-sm font-bold text-zinc-900 tracking-tight pb-3 border-b border-zinc-100 flex items-center gap-2">
                        <span class="w-5 h-5 rounded-full bg-red-100 text-primary font-bold text-xs flex items-center justify-center">3</span>
                        Data Orang Tua / Wali
                    </h2>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mt-4">
                        <div class="sm:col-span-1">
                            <x-label for="parent_name" required>Nama Orang Tua / Wali</x-label>
                            <x-input
                                id="parent_name"
                                name="parent_name"
                                :value="old('parent_name')"
                                placeholder="Nama ayah / ibu / wali"
                                required
                            />
                        </div>

                        <div class="sm:col-span-1">
                            <x-label for="parent_phone" required>Nomor HP Orang Tua</x-label>
                            <x-input
                                id="parent_phone"
                                name="parent_phone"
                                :value="old('parent_phone')"
                                placeholder="08xxxxxxxxxx"
                                required
                            />
                        </div>

                        <div class="sm:col-span-1">
                            <x-label for="parent_relationship" required>Hubungan Keluarga</x-label>
                            <select
                                id="parent_relationship"
                                name="parent_relationship"
                                required
                                class="w-full mt-1 px-3 py-2 text-xs sm:text-sm bg-white border border-zinc-200 rounded-lg text-zinc-800 focus:outline-none focus:ring-2 focus:ring-red-100 focus:border-primary"
                            >
                                <option value="Ayah" @selected(old('parent_relationship') == 'Ayah')>Ayah Kandung</option>
                                <option value="Ibu" @selected(old('parent_relationship') == 'Ibu')>Ibu Kandung</option>
                                <option value="Wali" @selected(old('parent_relationship') == 'Wali')>Wali</option>
                            </select>
                        </div>
                    </div>
                </x-card>

                <!-- Section 4: Unggah Dokumen Persyaratan -->
                <x-card padding class="border-zinc-200">
                    <h2 class="text-sm font-bold text-zinc-900 tracking-tight pb-3 border-b border-zinc-100 flex items-center gap-2">
                        <span class="w-5 h-5 rounded-full bg-red-100 text-primary font-bold text-xs flex items-center justify-center">4</span>
                        Unggah Berkas Persyaratan (File Privat)
                    </h2>
                    <p class="text-xs text-zinc-500 mt-1">
                        Format file yang didukung: PDF, JPG, PNG (Maksimal 3 MB per file). Dokumen tersimpan aman pada server privat.
                    </p>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mt-4">
                        <div class="p-3 bg-zinc-50/80 rounded-xl border border-zinc-200/80">
                            <x-label for="document_kk">Kartu Keluarga (KK)</x-label>
                            <input
                                type="file"
                                id="document_kk"
                                name="document_kk"
                                accept=".pdf,.jpg,.jpeg,.png"
                                class="w-full mt-1 text-xs text-zinc-600 file:mr-2 file:py-1 file:px-2.5 file:rounded-md file:border-0 file:text-xs file:font-semibold file:bg-zinc-200 file:text-zinc-700 hover:file:bg-zinc-300"
                            >
                        </div>

                        <div class="p-3 bg-zinc-50/80 rounded-xl border border-zinc-200/80">
                            <x-label for="document_akta">Akta Kelahiran</x-label>
                            <input
                                type="file"
                                id="document_akta"
                                name="document_akta"
                                accept=".pdf,.jpg,.jpeg,.png"
                                class="w-full mt-1 text-xs text-zinc-600 file:mr-2 file:py-1 file:px-2.5 file:rounded-md file:border-0 file:text-xs file:font-semibold file:bg-zinc-200 file:text-zinc-700 hover:file:bg-zinc-300"
                            >
                        </div>

                        <div class="p-3 bg-zinc-50/80 rounded-xl border border-zinc-200/80">
                            <x-label for="document_ijazah">Ijazah / Surat Keterangan Lulus (SKL)</x-label>
                            <input
                                type="file"
                                id="document_ijazah"
                                name="document_ijazah"
                                accept=".pdf,.jpg,.jpeg,.png"
                                class="w-full mt-1 text-xs text-zinc-600 file:mr-2 file:py-1 file:px-2.5 file:rounded-md file:border-0 file:text-xs file:font-semibold file:bg-zinc-200 file:text-zinc-700 hover:file:bg-zinc-300"
                            >
                        </div>

                        <div class="p-3 bg-zinc-50/80 rounded-xl border border-zinc-200/80">
                            <x-label for="document_rapor">Buku Rapor Terakhir</x-label>
                            <input
                                type="file"
                                id="document_rapor"
                                name="document_rapor"
                                accept=".pdf,.jpg,.jpeg,.png"
                                class="w-full mt-1 text-xs text-zinc-600 file:mr-2 file:py-1 file:px-2.5 file:rounded-md file:border-0 file:text-xs file:font-semibold file:bg-zinc-200 file:text-zinc-700 hover:file:bg-zinc-300"
                            >
                        </div>

                        <div class="p-3 bg-zinc-50/80 rounded-xl border border-zinc-200/80">
                            <x-label for="document_sertifikat">Sertifikat Prestasi (Jalur Prestasi)</x-label>
                            <input
                                type="file"
                                id="document_sertifikat"
                                name="document_sertifikat"
                                accept=".pdf,.jpg,.jpeg,.png"
                                class="w-full mt-1 text-xs text-zinc-600 file:mr-2 file:py-1 file:px-2.5 file:rounded-md file:border-0 file:text-xs file:font-semibold file:bg-zinc-200 file:text-zinc-700 hover:file:bg-zinc-300"
                            >
                        </div>

                        <div class="p-3 bg-zinc-50/80 rounded-xl border border-zinc-200/80">
                            <x-label for="document_foto">Pas Foto Berwarna Terbaru</x-label>
                            <input
                                type="file"
                                id="document_foto"
                                name="document_foto"
                                accept=".jpg,.jpeg,.png"
                                class="w-full mt-1 text-xs text-zinc-600 file:mr-2 file:py-1 file:px-2.5 file:rounded-md file:border-0 file:text-xs file:font-semibold file:bg-zinc-200 file:text-zinc-700 hover:file:bg-zinc-300"
                            >
                        </div>
                    </div>
                </x-card>

                <!-- Section 5: Konfirmasi & Kirim -->
                <div class="p-5 bg-white rounded-2xl border border-zinc-200 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div class="text-xs text-zinc-500">
                        <span class="font-bold text-zinc-800 block">Pernyataan Kebenaran Data:</span>
                        Saya menyatakan data yang diisikan adalah benar dan bersedia mengikuti ketentuan PPDB resmi.
                    </div>

                    <div class="flex items-center gap-3 shrink-0">
                        <x-button href="{{ route('ppdb.index') }}" variant="secondary" size="md">
                            Batal
                        </x-button>
                        <x-button type="submit" variant="primary" size="md">
                            Kirim Formulir Pendaftaran &rarr;
                        </x-button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</x-layouts.guest>
