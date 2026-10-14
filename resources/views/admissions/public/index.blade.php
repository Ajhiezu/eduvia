<x-layouts.guest>
    <x-slot:title>
        Penerimaan Peserta Didik Baru (PPDB / SPMB) &mdash; Eduvia
    </x-slot:title>

    <div class="py-10 sm:py-16 bg-gradient-to-b from-white via-zinc-50 to-zinc-100">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 space-y-10">
            <!-- Header Banner -->
            <div class="text-center max-w-3xl mx-auto space-y-3">
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-red-50 border border-red-200 text-xs font-bold text-primary">
                    <span class="w-2 h-2 rounded-full bg-primary animate-ping"></span>
                    Portal Resmi PPDB / SPMB Online
                </div>
                <h1 class="text-3xl sm:text-4xl font-extrabold text-zinc-900 tracking-tight">
                    Penerimaan Peserta Didik Baru
                </h1>
                <p class="text-sm sm:text-base text-zinc-600">
                    Selamat datang di portal pendaftaran online resmi {{ $school?->name ?? 'Sekolah Nusantara' }}.
                    Silakan pilih periode pendaftaran aktif atau pantau status berkas Anda secara berkala.
                </p>
                <div class="flex items-center justify-center gap-3 pt-2">
                    <x-button href="{{ route('ppdb.check-status') }}" variant="secondary" size="md">
                        <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                        Cek Status Pendaftaran
                    </x-button>
                </div>
            </div>

            <!-- Alerts -->
            @if (session('error'))
                <x-alert variant="danger" dismissible>
                    {{ session('error') }}
                </x-alert>
            @endif

            <!-- Admission Periods Grid -->
            <div class="space-y-6">
                <div class="flex items-center justify-between border-b border-zinc-200 pb-3">
                    <h2 class="text-lg font-bold text-zinc-900 tracking-tight">
                        Periode Pendaftaran Aktif
                    </h2>
                    <span class="text-xs text-zinc-500 font-medium">
                        {{ $openPeriods->count() }} Periode Tersedia
                    </span>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    @forelse ($openPeriods as $period)
                        <div class="bg-white rounded-2xl border border-zinc-200 p-6 shadow-sm hover:shadow-md transition-all flex flex-col justify-between">
                            <div class="space-y-4">
                                <div class="flex items-start justify-between gap-3">
                                    <div>
                                        <x-badge :variant="$period->statusBadgeVariant()" size="sm">
                                            {{ $period->statusLabel() }}
                                        </x-badge>
                                        <h3 class="text-lg font-bold text-zinc-900 mt-2">
                                            {{ $period->name }}
                                        </h3>
                                        <p class="text-xs text-zinc-500 mt-0.5">
                                            Tahun Ajaran: {{ $period->academicYear?->name ?? '-' }} &bull; Kode: {{ $period->code }}
                                        </p>
                                    </div>
                                    <div class="text-right">
                                        <div class="text-xs text-zinc-400">Total Kuota</div>
                                        <div class="text-xl font-extrabold text-zinc-900">{{ $period->quota }}</div>
                                        <div class="text-[11px] text-zinc-500">siswa</div>
                                    </div>
                                </div>

                                @if ($period->description)
                                    <p class="text-xs text-zinc-600 line-clamp-2">
                                        {{ $period->description }}
                                    </p>
                                @endif

                                <!-- Timeline -->
                                <div class="grid grid-cols-2 gap-3 p-3 bg-zinc-50 rounded-xl border border-zinc-100 text-xs">
                                    <div>
                                        <span class="text-[11px] text-zinc-400 block font-medium">Masa Pendaftaran:</span>
                                        <span class="font-semibold text-zinc-800">
                                            {{ $period->start_date?->format('d M') }} &ndash; {{ $period->end_date?->format('d M Y') }}
                                        </span>
                                    </div>
                                    <div>
                                        <span class="text-[11px] text-zinc-400 block font-medium">Pengumuman:</span>
                                        <span class="font-semibold text-zinc-800">
                                            {{ $period->announcement_date?->format('d M Y') }}
                                        </span>
                                    </div>
                                </div>

                                <!-- Jalur Tersedia -->
                                <div class="space-y-1.5">
                                    <div class="text-xs font-bold text-zinc-700">Jalur Pendaftaran:</div>
                                    <div class="flex flex-wrap gap-1.5">
                                        @foreach ($period->tracks as $track)
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-medium bg-zinc-100 text-zinc-700 border border-zinc-200">
                                                {{ $track->name }} ({{ $track->quota }} kursi)
                                            </span>
                                        @endforeach
                                    </div>
                                </div>
                            </div>

                            <div class="pt-5 mt-5 border-t border-zinc-100 flex items-center justify-between">
                                <span class="text-xs text-zinc-400">
                                    {{ $period->applicants_count }} Calon Terdaftar
                                </span>

                                @if ($period->isOpen())
                                    <x-button href="{{ route('ppdb.register', $period) }}" variant="primary" size="sm">
                                        Daftar Sekarang &rarr;
                                    </x-button>
                                @else
                                    <span class="text-xs font-semibold text-zinc-400 italic">
                                        Pendaftaran Ditutup
                                    </span>
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="col-span-full py-12 text-center bg-white rounded-2xl border border-zinc-200 p-8">
                            <svg class="w-12 h-12 text-zinc-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                            <h3 class="text-sm font-bold text-zinc-700">Belum Ada Periode Pendaftaran Dibuka</h3>
                            <p class="text-xs text-zinc-500 mt-1">Silakan kunjungi halaman ini kembali saat masa pendaftaran telah resmi dibuka oleh pihak sekolah.</p>
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- Petunjuk & Alur Pendaftaran -->
            <div class="bg-white rounded-2xl border border-zinc-200 p-6 sm:p-8 space-y-6">
                <h3 class="text-base font-bold text-zinc-900 tracking-tight">
                    Alur & Tata Cara Pendaftaran Calon Peserta Didik
                </h3>

                <div class="grid grid-cols-1 sm:grid-cols-4 gap-6">
                    <div class="space-y-2">
                        <div class="w-8 h-8 rounded-full bg-red-100 text-primary font-bold text-sm flex items-center justify-center">1</div>
                        <h4 class="text-xs font-bold text-zinc-900">Isi Formulir Online</h4>
                        <p class="text-xs text-zinc-500 leading-relaxed">
                            Lengkapi biodata calon siswa, data orang tua, dan pilih jalur pendaftaran yang sesuai.
                        </p>
                    </div>

                    <div class="space-y-2">
                        <div class="w-8 h-8 rounded-full bg-red-100 text-primary font-bold text-sm flex items-center justify-center">2</div>
                        <h4 class="text-xs font-bold text-zinc-900">Upload Dokumen</h4>
                        <p class="text-xs text-zinc-500 leading-relaxed">
                            Unggah berkas Kartu Keluarga, Akta Kelahiran, dan dokumen pendukung dalam format PDF atau foto jelas.
                        </p>
                    </div>

                    <div class="space-y-2">
                        <div class="w-8 h-8 rounded-full bg-red-100 text-primary font-bold text-sm flex items-center justify-center">3</div>
                        <h4 class="text-xs font-bold text-zinc-900">Simpan Bukti Pendaftaran</h4>
                        <p class="text-xs text-zinc-500 leading-relaxed">
                            Catat dan simpan Nomor Pendaftaran serta Kode Tracking rahasia untuk memantau status berkas Anda.
                        </p>
                    </div>

                    <div class="space-y-2">
                        <div class="w-8 h-8 rounded-full bg-red-100 text-primary font-bold text-sm flex items-center justify-center">4</div>
                        <h4 class="text-xs font-bold text-zinc-900">Verifikasi & Pengumuman</h4>
                        <p class="text-xs text-zinc-500 leading-relaxed">
                            Panitia memeriksa berkas. Hasil seleksi dan jadwal daftar ulang dapat dicek secara mandiri.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-layouts.guest>
