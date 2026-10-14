<x-layouts.app>
    <x-slot:headerTitle>
        Impor Massal Guru & Tenaga Kependidikan
    </x-slot:headerTitle>

    <div class="max-w-4xl mx-auto space-y-6">
        <!-- Breadcrumb & Header -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div>
                <div class="flex items-center gap-2 text-xs text-zinc-500 mb-1">
                    <a href="{{ route('staff.index') }}" class="hover:text-brand-600 transition-colors">Pendidik & Staf</a>
                    <span>/</span>
                    <span class="text-zinc-800 font-medium">Impor Massal (CSV)</span>
                </div>
                <h1 class="text-xl sm:text-2xl font-extrabold text-zinc-900 tracking-tight">
                    Impor Data Guru & Staf dari CSV
                </h1>
                <p class="text-xs text-zinc-500 mt-0.5">
                    Unggah daftar pegawai dalam jumlah banyak sekaligus menggunakan template spreadsheet resmi.
                </p>
            </div>

            <x-button href="{{ route('staff.index') }}" variant="secondary" size="sm" class="self-start sm:self-auto shrink-0">
                Kembali
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
                    <form method="POST" action="{{ route('staff.import.preview') }}" enctype="multipart/form-data" class="space-y-4">
                        @csrf
                        <div class="border-2 border-dashed border-zinc-300 hover:border-brand-500 rounded-2xl p-8 text-center transition-colors bg-zinc-50/50">
                            <div class="w-12 h-12 rounded-full bg-brand-50 text-brand-600 mx-auto flex items-center justify-center mb-3">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                                </svg>
                            </div>
                            <h3 class="text-sm font-bold text-zinc-900">Pilih Berkas CSV Pegawai</h3>
                            <p class="text-xs text-zinc-500 mt-1 max-w-sm mx-auto">
                                Pastikan berkas berformat .csv dengan pemisah koma dan encoding UTF-8 (maksimal 2 MB).
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

                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pt-2">
                            <span class="text-[11px] text-zinc-400">
                                Berkas akan diperiksa sebelum akun dibuat.
                            </span>

                            <x-button type="submit" variant="primary" size="md" class="w-full sm:w-auto justify-center">
                                Lanjutkan ke Pratinjau & Validasi
                            </x-button>
                        </div>
                    </form>
                </x-card>
            </div>

            <!-- Right Col: Template & Guidelines -->
            <div class="space-y-4">
                <x-card padding class="border-zinc-200">
                    <h2 class="text-xs font-bold uppercase tracking-wider text-zinc-500 mb-2">
                        Unduh Template Resmi
                    </h2>
                    <p class="text-xs text-zinc-500 mb-3">
                        Gunakan berkas template CSV berikut untuk memastikan nama kolom dan format data sesuai dengan sistem Eduvia.
                    </p>
                    <x-button href="{{ route('staff.import.template') }}" variant="secondary" size="sm" class="w-full justify-center">
                        <svg class="w-4 h-4 mr-1.5 text-zinc-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                        </svg>
                        Unduh Template (.CSV)
                    </x-button>
                </x-card>

                <x-card padding class="border-zinc-200 bg-zinc-50/50">
                    <h2 class="text-xs font-bold uppercase tracking-wider text-zinc-500 mb-2">
                        Petunjuk Kolom
                    </h2>
                    <ul class="text-[11px] text-zinc-600 space-y-1.5 list-disc list-inside">
                        <li><strong>nama_lengkap:</strong> Nama dan gelar resmi.</li>
                        <li><strong>email:</strong> Email unik yang belum pernah terdaftar.</li>
                        <li><strong>peran:</strong> <code>teacher</code>, <code>staff</code>, atau <code>school_admin</code>.</li>
                        <li><strong>jabatan:</strong> <code>guru_mapel</code>, <code>tata_usaha</code>, <code>pustakawan</code>, dsb.</li>
                        <li><strong>jenis_kelamin:</strong> <code>male</code> atau <code>female</code> (atau L/P).</li>
                        <li><strong>status_kepegawaian:</strong> <code>gty</code>, <code>gtt</code>, <code>pns</code>, <code>p3k</code>, <code>honor</code>.</li>
                    </ul>
                </x-card>
            </div>
        </div>
    </div>
</x-layouts.app>
