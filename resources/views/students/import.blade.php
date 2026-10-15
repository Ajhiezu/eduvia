<x-layouts.app>
    <x-slot:headerTitle>
        Impor Massal Peserta Didik
    </x-slot:headerTitle>

    <div class="max-w-4xl mx-auto space-y-6">
        <!-- Breadcrumb & Header -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div>
                <div class="flex items-center gap-2 text-xs text-zinc-500 mb-1">
                    <a href="{{ route('students.index') }}" class="hover:text-brand-600 transition-colors">Peserta Didik</a>
                    <span>/</span>
                    <span class="text-zinc-800 font-medium">Impor Massal (CSV)</span>
                </div>
                <h1 class="text-xl sm:text-2xl font-extrabold text-zinc-900 tracking-tight">
                    Impor Data Peserta Didik dari CSV
                </h1>
                <p class="text-xs text-zinc-500 mt-0.5">
                    Unggah daftar peserta didik dalam jumlah banyak sekaligus menggunakan template spreadsheet resmi.
                </p>
            </div>

            <x-button href="{{ route('students.index') }}" variant="outline" size="sm" class="self-start sm:self-auto shrink-0">
                Kembali ke Daftar
            </x-button>
        </div>

        @if ($errors->any())
            <x-alert variant="danger" dismissible>
                {{ $errors->first() }}
            </x-alert>
        @endif

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- Left 2 Cols: Upload Box -->
            <div class="md:col-span-2 space-y-4">
                <x-card padding>
                    <form method="POST" action="{{ route('students.import.preview') }}" enctype="multipart/form-data" class="space-y-4">
                        @csrf
                        <div class="border-2 border-dashed border-zinc-300 hover:border-brand-500 rounded-2xl p-8 text-center transition-colors bg-zinc-50/50">
                            <div class="w-12 h-12 rounded-full bg-brand-50 text-brand-600 mx-auto flex items-center justify-center mb-3">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                                </svg>
                            </div>
                            <h3 class="text-sm font-bold text-zinc-900">Pilih Berkas CSV Peserta Didik</h3>
                            <p class="text-xs text-zinc-500 mt-1 max-w-sm mx-auto">
                                Pastikan berkas berformat .csv dengan pemisah koma dan encoding UTF-8 (maksimal 5 MB).
                            </p>

                            <div class="mt-4">
                                <input
                                    type="file"
                                    name="file"
                                    id="file"
                                    accept=".csv,text/csv"
                                    required
                                    class="text-xs text-zinc-500 file:mr-3 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-brand-600 file:text-white hover:file:bg-brand-700 cursor-pointer"
                                />
                            </div>
                        </div>

                        <!-- Optional Classroom Enrollment -->
                        <div>
                            <label for="classroom_id" class="block text-xs font-semibold text-zinc-700 mb-1">
                                Penempatan Rombongan Belajar Langsung <span class="text-zinc-400 font-normal">(Opsional)</span>
                            </label>
                            <select
                                id="classroom_id"
                                name="classroom_id"
                                class="w-full rounded-lg border border-zinc-300 text-zinc-900 text-xs py-2 px-3 focus:border-brand-500 focus:ring-2 focus:ring-brand-100"
                            >
                                <option value="">-- Jangan tempatkan langsung ke kelas (atur nanti) --</option>
                                @foreach ($classrooms as $classroom)
                                    <option value="{{ $classroom->id }}">
                                        {{ $classroom->name }} ({{ $classroom->academicYear?->name ?? 'Tahun Aktif' }})
                                    </option>
                                @endforeach
                            </select>
                            <p class="text-[11px] text-zinc-400 mt-1">
                                Jika dipilih, semua siswa yang diimpor akan langsung terdaftar aktif pada rombel ini.
                            </p>
                        </div>

                        <div class="flex items-center justify-between pt-2">
                            <span class="text-[11px] text-zinc-500">Tahap 1 dari 2: Validasi & Pratinjau</span>
                            <x-button type="submit" variant="primary" size="sm">
                                Lanjut ke Pratinjau
                            </x-button>
                        </div>
                    </form>
                </x-card>
            </div>

            <!-- Right 1 Col: Download Template & Instructions -->
            <div class="space-y-4">
                <x-card padding class="bg-gradient-to-br from-brand-500/5 to-transparent border-brand-200">
                    <h3 class="text-xs font-bold text-brand-900 uppercase tracking-wider mb-2">Template Resmi</h3>
                    <p class="text-xs text-zinc-600 mb-4 leading-relaxed">
                        Unduh template CSV standar Eduvia dengan format kolom yang telah disesuaikan agar proses verifikasi berjalan lancar.
                    </p>
                    <x-button href="{{ route('students.import.template') }}" variant="outline" size="sm" class="w-full justify-center">
                        <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                        </svg>
                        Unduh Template (.CSV)
                    </x-button>
                </x-card>

                <x-card padding>
                    <h3 class="text-xs font-bold text-zinc-900 uppercase tracking-wider mb-2">Aturan Format Kolom</h3>
                    <ul class="text-xs text-zinc-600 space-y-2 list-disc list-inside">
                        <li><strong>nama_lengkap</strong>: Wajib diisi (maks 150 karakter).</li>
                        <li><strong>email</strong>: Wajib diisi, unik, format email valid.</li>
                        <li><strong>nis</strong>: Nomor Induk Siswa lokal sekolah (unik).</li>
                        <li><strong>nisn</strong>: Nomor Induk Siswa Nasional (10 digit).</li>
                        <li><strong>jenis_kelamin</strong>: male / female (atau L / P).</li>
                        <li><strong>tanggal_lahir</strong>: Format YYYY-MM-DD.</li>
                    </ul>
                </x-card>
            </div>
        </div>
    </div>
</x-layouts.app>
