<x-layouts.guest>
    <x-slot:title>
        Hasil Status Pendaftaran &mdash; {{ $applicant->registration_number }} &mdash; Eduvia
    </x-slot:title>

    <div class="py-10 bg-zinc-50 min-h-screen">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            <!-- Navigation -->
            <div class="flex items-center justify-between">
                <a href="{{ route('ppdb.check-status') }}" class="inline-flex items-center text-xs font-semibold text-zinc-500 hover:text-zinc-800 transition-colors">
                    &larr; Cek Nomor Pendaftaran Lain
                </a>
                <span class="text-xs text-zinc-400">Pembaruan Terakhir: {{ $applicant->updated_at->format('d M Y H:i') }}</span>
            </div>

            <!-- Main Status Dossier Card -->
            <x-card padding class="border-zinc-200 space-y-6">
                <!-- Header with Status Badge -->
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-5 border-b border-zinc-100">
                    <div>
                        <span class="text-xs text-zinc-400 font-mono">No. Registrasi: {{ $applicant->registration_number }}</span>
                        <h1 class="text-xl sm:text-2xl font-extrabold text-zinc-900 tracking-tight mt-0.5">
                            {{ $applicant->full_name }}
                        </h1>
                        <p class="text-xs text-zinc-500 mt-0.5">
                            Jalur: <strong>{{ $applicant->admissionTrack?->name }}</strong> &bull; Periode: {{ $applicant->admissionPeriod?->name }}
                        </p>
                    </div>

                    <div class="shrink-0 text-left sm:text-right">
                        <span class="text-xs text-zinc-400 block mb-1">Status Saat Ini:</span>
                        <x-badge :variant="$applicant->statusBadgeVariant()" size="md">
                            {{ $applicant->statusLabel() }}
                        </x-badge>
                    </div>
                </div>

                <!-- Status Progress Steps -->
                <div class="space-y-2">
                    <h2 class="text-xs font-bold uppercase tracking-wider text-zinc-400">Tahapan Proses Pendaftaran:</h2>
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-2 text-center text-xs">
                        <!-- Step 1: Submit -->
                        <div class="p-2.5 rounded-xl border {{ in_array($applicant->status, ['pending', 'verified', 'revision_needed', 'accepted', 'rejected', 're_registered', 'converted']) ? 'bg-emerald-50 border-emerald-200 text-emerald-800 font-bold' : 'bg-zinc-50 border-zinc-200 text-zinc-400' }}">
                            1. Formulir Terkirim
                        </div>

                        <!-- Step 2: Verification -->
                        <div class="p-2.5 rounded-xl border {{ in_array($applicant->status, ['verified', 'accepted', 'rejected', 're_registered', 'converted']) ? 'bg-emerald-50 border-emerald-200 text-emerald-800 font-bold' : ($applicant->status === 'revision_needed' ? 'bg-amber-50 border-amber-200 text-amber-800 font-bold' : 'bg-zinc-50 border-zinc-200 text-zinc-400') }}">
                            2. Verifikasi Berkas
                        </div>

                        <!-- Step 3: Selection -->
                        <div class="p-2.5 rounded-xl border {{ in_array($applicant->status, ['accepted', 'rejected', 're_registered', 'converted']) ? ($applicant->status === 'rejected' ? 'bg-rose-50 border-rose-200 text-rose-800 font-bold' : 'bg-emerald-50 border-emerald-200 text-emerald-800 font-bold') : 'bg-zinc-50 border-zinc-200 text-zinc-400' }}">
                            3. Pengumuman Hasil
                        </div>

                        <!-- Step 4: Re-registration & Active -->
                        <div class="p-2.5 rounded-xl border {{ in_array($applicant->status, ['re_registered', 'converted']) ? 'bg-emerald-50 border-emerald-200 text-emerald-800 font-bold' : 'bg-zinc-50 border-zinc-200 text-zinc-400' }}">
                            4. Daftar Ulang
                        </div>
                    </div>
                </div>

                <!-- Status Details / Feedback Alert -->
                @if ($applicant->status === 'accepted')
                    <div class="p-4 bg-emerald-50 rounded-xl border border-emerald-200 text-xs text-emerald-900 space-y-2">
                        <div class="font-bold text-sm flex items-center gap-1.5 text-emerald-800">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            Selamat! Anda Dinyatakan LULUS Seleksi PPDB
                        </div>
                        <p>
                            Silakan lakukan daftar ulang ke sekolah atau ikuti petunjuk verifikasi fisik pada tanggal:
                            <strong>{{ $applicant->admissionPeriod?->re_registration_start_date?->format('d M Y') }} s/d {{ $applicant->admissionPeriod?->re_registration_end_date?->format('d M Y') }}</strong>.
                        </p>
                        @if ($applicant->selection_notes)
                            <div class="pt-1 text-emerald-800/90 font-medium">
                                Catatan Panitia: {{ $applicant->selection_notes }}
                            </div>
                        @endif
                    </div>
                @elseif ($applicant->status === 'rejected')
                    <div class="p-4 bg-rose-50 rounded-xl border border-rose-200 text-xs text-rose-900 space-y-1.5">
                        <div class="font-bold text-sm flex items-center gap-1.5 text-rose-800">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            Mohon Maaf, Pendaftaran Belum Diterima
                        </div>
                        <p>
                            Berdasarkan hasil seleksi dan kuota penerimaan jalur {{ $applicant->admissionTrack?->name }}, Anda belum memenuhi kualifikasi penempatan.
                        </p>
                        @if ($applicant->rejection_reason)
                            <div class="pt-1 text-rose-800 font-semibold">
                                Alasan: {{ $applicant->rejection_reason }}
                            </div>
                        @endif
                    </div>
                @elseif ($applicant->status === 'revision_needed')
                    <div class="p-4 bg-amber-50 rounded-xl border border-amber-200 text-xs text-amber-900 space-y-1.5">
                        <div class="font-bold text-sm flex items-center gap-1.5 text-amber-800">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                            </svg>
                            Perlu Perbaikan Berkas / Dokumen
                        </div>
                        <p>
                            Beberapa dokumen yang diunggah belum sesuai ketentuan panitia. Mohon hubungi panitia PPDB sekolah untuk menyerahkan berkas perbaikan.
                        </p>
                    </div>
                @elseif ($applicant->status === 're_registered' || $applicant->status === 'converted')
                    <div class="p-4 bg-blue-50 rounded-xl border border-blue-200 text-xs text-blue-900 space-y-1.5">
                        <div class="font-bold text-sm flex items-center gap-1.5 text-blue-800">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            Daftar Ulang Terkonfirmasi
                        </div>
                        <p>
                            Calon peserta didik telah menyelesaikan proses administrasi daftar ulang dan resmi terdaftar di database sekolah.
                        </p>
                    </div>
                @endif

                <!-- Document Verification Summary -->
                <div class="space-y-3 pt-2">
                    <h3 class="text-xs font-bold text-zinc-900 tracking-tight">Status Dokumen yang Diunggah</h3>
                    <div class="divide-y divide-zinc-100 border border-zinc-200 rounded-xl overflow-hidden text-xs">
                        @forelse ($applicant->documents as $doc)
                            <div class="p-3 flex items-center justify-between gap-3 bg-white">
                                <div>
                                    <span class="font-bold text-zinc-800">{{ $doc->documentTypeLabel() }}</span>
                                    <div class="text-[11px] text-zinc-400 mt-0.5">{{ $doc->original_name }}</div>
                                    @if ($doc->review_notes)
                                        <div class="text-[11px] text-amber-700 mt-1 italic">
                                            Catatan Panitia: {{ $doc->review_notes }}
                                        </div>
                                    @endif
                                </div>
                                <div class="shrink-0">
                                    <x-badge :variant="$doc->statusBadgeVariant()" size="xs">
                                        {{ $doc->statusLabel() }}
                                    </x-badge>
                                </div>
                            </div>
                        @empty
                            <div class="p-4 text-center text-zinc-400 bg-zinc-50">
                                Tidak ada dokumen digital yang tersimpan.
                            </div>
                        @endforelse
                    </div>
                </div>

                <!-- Summary Info -->
                <div class="grid grid-cols-2 sm:grid-cols-3 gap-3 pt-3 border-t border-zinc-100 text-xs">
                    <div>
                        <span class="text-zinc-400 block text-[11px]">Asal Sekolah:</span>
                        <span class="font-medium text-zinc-800">{{ $applicant->previous_school }}</span>
                    </div>
                    <div>
                        <span class="text-zinc-400 block text-[11px]">Nama Orang Tua:</span>
                        <span class="font-medium text-zinc-800">{{ $applicant->parent_name }}</span>
                    </div>
                    <div>
                        <span class="text-zinc-400 block text-[11px]">Tanggal Pengumuman:</span>
                        <span class="font-semibold text-zinc-800">{{ $applicant->admissionPeriod?->announcement_date?->format('d M Y') }}</span>
                    </div>
                </div>
            </x-card>
        </div>
    </div>
</x-layouts.guest>
