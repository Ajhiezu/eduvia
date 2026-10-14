<x-layouts.guest>
    <x-slot:title>
        Pendaftaran Berhasil &mdash; {{ $applicant->registration_number }} &mdash; Eduvia
    </x-slot:title>

    <div class="py-12 bg-zinc-50 min-h-screen">
        <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white rounded-2xl border border-zinc-200 p-6 sm:p-10 shadow-sm text-center space-y-6">
                <!-- Success Icon -->
                <div class="w-16 h-16 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center mx-auto">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                    </svg>
                </div>

                <div class="space-y-2">
                    <h1 class="text-2xl sm:text-3xl font-extrabold text-zinc-900 tracking-tight">
                        Pendaftaran Berhasil Dikirim!
                    </h1>
                    <p class="text-xs sm:text-sm text-zinc-500 max-w-md mx-auto">
                        Terima kasih, berkas pendaftaran calon siswa <strong>{{ $applicant->full_name }}</strong> telah masuk ke dalam sistem panitia PPDB.
                    </p>
                </div>

                <!-- Credential Card (Important) -->
                <div class="p-5 sm:p-6 bg-zinc-50 rounded-2xl border-2 border-dashed border-zinc-300 text-left space-y-4">
                    <div class="flex items-center justify-between pb-3 border-b border-zinc-200">
                        <span class="text-xs font-bold uppercase tracking-wider text-zinc-400">Bukti Tanda Terima Pendaftaran</span>
                        <span class="text-[11px] font-semibold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded-full border border-emerald-200">
                            Status: Terdaftar
                        </span>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <span class="text-xs text-zinc-500 block">Nomor Pendaftaran Resmi:</span>
                            <span class="text-xl font-extrabold text-primary font-mono select-all">
                                {{ $applicant->registration_number }}
                            </span>
                        </div>

                        <div>
                            <span class="text-xs text-zinc-500 block">Kode Tracking Rahasia:</span>
                            <span class="text-xl font-extrabold text-zinc-900 font-mono tracking-widest select-all">
                                {{ $applicant->tracking_code }}
                            </span>
                        </div>
                    </div>

                    <div class="pt-3 border-t border-zinc-200 text-xs text-zinc-600 space-y-1">
                        <div class="flex items-center gap-2">
                            <span class="font-medium text-zinc-500">Nama Calon Siswa:</span>
                            <span class="font-bold text-zinc-800">{{ $applicant->full_name }}</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="font-medium text-zinc-500">Jalur Penerimaan:</span>
                            <span class="font-bold text-zinc-800">{{ $applicant->admissionTrack?->name }}</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="font-medium text-zinc-500">Periode PPDB:</span>
                            <span class="font-semibold text-zinc-700">{{ $applicant->admissionPeriod?->name }}</span>
                        </div>
                    </div>
                </div>

                <!-- Important Notice -->
                <div class="p-4 bg-amber-50 rounded-xl border border-amber-200 text-left flex items-start gap-3">
                    <svg class="w-5 h-5 text-amber-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                    <div class="text-xs text-amber-900 space-y-1">
                        <span class="font-bold block">PENTING: Simpan Bukti Ini!</span>
                        <p>
                            Nomor Pendaftaran dan Kode Tracking di atas diperlukan untuk mengecek hasil verifikasi berkas, pengumuman kelulusan, dan jadwal daftar ulang. Mohon catat atau cetak halaman ini.
                        </p>
                    </div>
                </div>

                <!-- Actions -->
                <div class="flex flex-col sm:flex-row items-center justify-center gap-3 pt-2">
                    <x-button onclick="window.print()" variant="secondary" size="md">
                        <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                        </svg>
                        Cetak Bukti Pendaftaran
                    </x-button>

                    <x-button href="{{ route('ppdb.index') }}" variant="primary" size="md">
                        Selesai & Kembali ke Portal PPDB
                    </x-button>
                </div>
            </div>
        </div>
    </div>
</x-layouts.guest>
