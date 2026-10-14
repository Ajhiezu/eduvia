<x-layouts.app>
    <x-slot:headerTitle>
        Dossier Calon Siswa &mdash; {{ $applicant->full_name }}
    </x-slot:headerTitle>

    <div class="space-y-6" x-data="{
        verifyModalOpen: false,
        selectedDoc: null,
        statusModalOpen: false,
        convertModalOpen: false,
        openVerifyDoc(doc) {
            this.selectedDoc = doc;
            this.verifyModalOpen = true;
        }
    }">
        <!-- Alerts -->
        @if (session('status'))
            <x-alert variant="success" dismissible>
                {{ session('status') }}
            </x-alert>
        @endif

        @if (session('error'))
            <x-alert variant="danger" dismissible>
                {{ session('error') }}
            </x-alert>
        @endif

        @if ($errors->any())
            <x-alert variant="danger" dismissible>
                {{ $errors->first() }}
            </x-alert>
        @endif

        <!-- Back Navigation & Title -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <div class="flex items-center gap-2">
                    <x-button href="{{ route('admissions.applicants.index') }}" variant="secondary" size="xs">
                        &larr; Kembali ke Daftar
                    </x-button>
                    <span class="text-xs text-zinc-400">/</span>
                    <span class="text-xs font-semibold text-zinc-600">Dossier Pendaftar</span>
                </div>
                <h1 class="text-xl sm:text-2xl font-extrabold text-zinc-900 tracking-tight mt-1.5 flex items-center gap-3">
                    <span>{{ $applicant->full_name }}</span>
                    <x-badge :variant="$applicant->statusBadgeVariant()" size="sm">
                        {{ $applicant->statusLabel() }}
                    </x-badge>
                </h1>
                <p class="text-xs text-zinc-500 mt-0.5 font-mono">
                    No. Registrasi: <strong class="text-primary">{{ $applicant->registration_number }}</strong> &bull; Token Tracking: <span class="text-zinc-700 font-bold">{{ $applicant->tracking_code }}</span>
                </p>
            </div>

            <!-- Header Action Buttons -->
            <div class="flex items-center gap-2 flex-wrap">
                <!-- Status Selection Button -->
                <button
                    type="button"
                    @click="statusModalOpen = true"
                    class="px-3 py-1.5 text-xs font-semibold rounded-lg bg-zinc-100 hover:bg-zinc-200 text-zinc-800 transition-colors inline-flex items-center gap-1.5"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    Ubah Status Seleksi
                </button>

                <!-- Re-Registration Button -->
                @if ($applicant->status === 'accepted')
                    <form action="{{ route('admissions.applicants.re-register', $applicant) }}" method="POST" onsubmit="return confirm('Konfirmasi bahwa calon siswa telah menyelesaikan proses daftar ulang?')">
                        @csrf
                        <x-button type="submit" variant="secondary" size="sm">
                            <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                            </svg>
                            Daftar Ulang
                        </x-button>
                    </form>
                @endif

                <!-- Convert to Student Button -->
                @if (in_array($applicant->status, ['accepted', 're_registered']) && ! $applicant->converted_user_id)
                    <button
                        type="button"
                        @click="convertModalOpen = true"
                        class="px-3.5 py-1.5 text-xs font-semibold rounded-lg bg-primary hover:bg-primary-hover text-white transition-colors inline-flex items-center gap-1.5 shadow-xs"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                        </svg>
                        Konversi ke Siswa Aktif &rarr;
                    </button>
                @elseif ($applicant->converted_user_id)
                    <x-button href="{{ route('students.show', $applicant->converted_user_id) }}" variant="primary" size="sm">
                        Buka Profil Siswa Aktif &rarr;
                    </x-button>
                @endif
            </div>
        </div>

        <!-- Main Dossier Content -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Left 2 Cols: Biodata, Parent, Selection Details -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Biodata Card -->
                <x-card padding class="border-zinc-200">
                    <h2 class="text-sm font-bold text-zinc-900 tracking-tight pb-3 border-b border-zinc-100 flex items-center justify-between">
                        <span>Biodata Calon Peserta Didik</span>
                        <span class="text-xs font-normal text-zinc-500">Terdaftar: {{ $applicant->created_at->format('d M Y H:i') }}</span>
                    </h2>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mt-4 text-xs">
                        <div>
                            <span class="text-zinc-400 block text-[11px]">Nama Lengkap:</span>
                            <span class="font-bold text-zinc-900 text-sm">{{ $applicant->full_name }}</span>
                        </div>

                        <div>
                            <span class="text-zinc-400 block text-[11px]">Jenis Kelamin:</span>
                            <span class="font-semibold text-zinc-800">{{ $applicant->genderLabel() }}</span>
                        </div>

                        <div>
                            <span class="text-zinc-400 block text-[11px]">NISN:</span>
                            <span class="font-mono font-medium text-zinc-800">{{ $applicant->nisn ?: '-' }}</span>
                        </div>

                        <div>
                            <span class="text-zinc-400 block text-[11px]">NIK:</span>
                            <span class="font-mono font-medium text-zinc-800">{{ $applicant->nik ?: '-' }}</span>
                        </div>

                        <div>
                            <span class="text-zinc-400 block text-[11px]">Tempat, Tanggal Lahir:</span>
                            <span class="font-medium text-zinc-800">
                                {{ $applicant->birth_place ?: '-' }}, {{ $applicant->birth_date?->format('d F Y') ?: '-' }}
                            </span>
                        </div>

                        <div>
                            <span class="text-zinc-400 block text-[11px]">Agama:</span>
                            <span class="font-medium text-zinc-800">{{ $applicant->religion ?: '-' }}</span>
                        </div>

                        <div>
                            <span class="text-zinc-400 block text-[11px]">Asal Sekolah:</span>
                            <span class="font-semibold text-zinc-900">{{ $applicant->previous_school ?: '-' }}</span>
                        </div>

                        <div>
                            <span class="text-zinc-400 block text-[11px]">Nomor HP / WhatsApp:</span>
                            <span class="font-medium text-zinc-800">{{ $applicant->phone ?: '-' }}</span>
                        </div>

                        <div class="sm:col-span-2">
                            <span class="text-zinc-400 block text-[11px]">Alamat Lengkap:</span>
                            <span class="font-medium text-zinc-800">{{ $applicant->address ?: '-' }}</span>
                        </div>
                    </div>
                </x-card>

                <!-- Data Orang Tua & Wali -->
                <x-card padding class="border-zinc-200">
                    <h2 class="text-sm font-bold text-zinc-900 tracking-tight pb-3 border-b border-zinc-100">
                        Data Orang Tua / Wali
                    </h2>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mt-4 text-xs">
                        <div>
                            <span class="text-zinc-400 block text-[11px]">Nama Orang Tua / Wali:</span>
                            <span class="font-bold text-zinc-900">{{ $applicant->parent_name }}</span>
                        </div>

                        <div>
                            <span class="text-zinc-400 block text-[11px]">Nomor HP Orang Tua:</span>
                            <span class="font-medium text-zinc-800">{{ $applicant->parent_phone }}</span>
                        </div>

                        <div>
                            <span class="text-zinc-400 block text-[11px]">Hubungan Keluarga:</span>
                            <span class="font-medium text-zinc-800">{{ $applicant->parent_relationship ?: 'Orang Tua' }}</span>
                        </div>
                    </div>
                </x-card>

                <!-- Berkas Persyaratan Digital -->
                <x-card padding class="border-zinc-200">
                    <div class="flex items-center justify-between pb-3 border-b border-zinc-100">
                        <div>
                            <h2 class="text-sm font-bold text-zinc-900 tracking-tight">Dokumen Persyaratan Digital</h2>
                            <p class="text-xs text-zinc-500">File tersimpan aman di direktori privat server.</p>
                        </div>
                        <span class="text-xs text-zinc-400 font-medium">{{ $applicant->documents->count() }} dokumen</span>
                    </div>

                    <div class="divide-y divide-zinc-100 mt-3 text-xs">
                        @forelse ($applicant->documents as $doc)
                            <div class="py-3 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                                <div>
                                    <div class="font-bold text-zinc-900 flex items-center gap-2">
                                        <span>{{ $doc->documentTypeLabel() }}</span>
                                        <x-badge :variant="$doc->statusBadgeVariant()" size="xs">
                                            {{ $doc->statusLabel() }}
                                        </x-badge>
                                    </div>
                                    <div class="text-[11px] text-zinc-400 mt-0.5">
                                        {{ $doc->original_name }} &bull; {{ round($doc->file_size / 1024) }} KB
                                    </div>
                                    @if ($doc->review_notes)
                                        <div class="text-[11px] text-amber-800 mt-1 italic">
                                            Catatan: {{ $doc->review_notes }} (oleh {{ $doc->verifier?->name ?? 'Panitia' }})
                                        </div>
                                    @endif
                                </div>

                                <div class="flex items-center gap-2 shrink-0">
                                    <a
                                        href="{{ route('admissions.documents.download', $doc) }}"
                                        class="px-2.5 py-1 text-xs font-semibold rounded-md border border-zinc-200 hover:bg-zinc-50 text-zinc-700 transition-colors inline-flex items-center gap-1"
                                    >
                                        <svg class="w-3.5 h-3.5 text-zinc-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                                        </svg>
                                        Unduh
                                    </a>

                                    <button
                                        type="button"
                                        @click="openVerifyDoc({{ json_encode($doc) }})"
                                        class="px-2.5 py-1 text-xs font-semibold rounded-md bg-zinc-100 hover:bg-zinc-200 text-zinc-800 transition-colors"
                                    >
                                        Periksa Berkas
                                    </button>
                                </div>
                            </div>
                        @empty
                            <div class="py-6 text-center text-zinc-400">
                                Calon siswa ini belum mengunggah berkas digital.
                            </div>
                        @endforelse
                    </div>
                </x-card>
            </div>

            <!-- Right Col: Seleksi, Jalur, dan Riwayat Konversi -->
            <div class="space-y-6">
                <!-- Seleksi & Penempatan Card -->
                <x-card padding class="border-zinc-200 space-y-4">
                    <h3 class="text-sm font-bold text-zinc-900 tracking-tight pb-3 border-b border-zinc-100">
                        Status Seleksi & PPDB
                    </h3>

                    <div class="space-y-3 text-xs">
                        <div>
                            <span class="text-zinc-400 block text-[11px]">Periode PPDB:</span>
                            <span class="font-bold text-zinc-800">{{ $applicant->admissionPeriod?->name }}</span>
                        </div>

                        <div>
                            <span class="text-zinc-400 block text-[11px]">Jalur Pendaftaran:</span>
                            <span class="font-bold text-primary">{{ $applicant->admissionTrack?->name }}</span>
                        </div>

                        <div>
                            <span class="text-zinc-400 block text-[11px]">Tingkat Kelas Tujuan:</span>
                            <span class="font-medium text-zinc-800">{{ $applicant->targetGradeLevel?->name ?? 'Reguler' }}</span>
                        </div>

                        <div>
                            <span class="text-zinc-400 block text-[11px]">Nilai / Skor Seleksi:</span>
                            <span class="font-extrabold text-base text-zinc-900">
                                {{ $applicant->selection_score !== null ? $applicant->selection_score : '-' }}
                            </span>
                        </div>

                        @if ($applicant->selection_notes)
                            <div>
                                <span class="text-zinc-400 block text-[11px]">Catatan Seleksi:</span>
                                <span class="text-zinc-700 italic">{{ $applicant->selection_notes }}</span>
                            </div>
                        @endif

                        @if ($applicant->rejection_reason)
                            <div class="p-2.5 bg-rose-50 border border-rose-200 rounded-lg text-rose-800">
                                <span class="font-bold block text-[11px]">Alasan Penolakan:</span>
                                {{ $applicant->rejection_reason }}
                            </div>
                        @endif

                        @if ($applicant->re_registered_at)
                            <div class="p-2.5 bg-blue-50 border border-blue-200 rounded-lg text-blue-800">
                                <span class="font-bold block text-[11px]">Daftar Ulang Selesai:</span>
                                {{ $applicant->re_registered_at->format('d M Y H:i') }}
                            </div>
                        @endif

                        @if ($applicant->converted_at)
                            <div class="p-2.5 bg-emerald-50 border border-emerald-200 rounded-lg text-emerald-800">
                                <span class="font-bold block text-[11px]">Telah Dikonversi:</span>
                                {{ $applicant->converted_at->format('d M Y H:i') }}
                            </div>
                        @endif
                    </div>
                </x-card>
            </div>
        </div>

        <!-- ====================================================================== -->
        <!-- MODAL 1: VERIFIKASI DOKUMEN                                            -->
        <!-- ====================================================================== -->
        <div
            x-show="verifyModalOpen"
            x-transition
            class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-zinc-900/50 backdrop-blur-xs"
            style="display: none;"
        >
            <div class="bg-white rounded-2xl border border-zinc-200 shadow-xl max-w-md w-full p-6 space-y-4" @click.outside="verifyModalOpen = false">
                <div class="flex items-center justify-between pb-3 border-b border-zinc-100">
                    <h3 class="text-sm font-bold text-zinc-900">Verifikasi Dokumen Persyaratan</h3>
                    <button type="button" @click="verifyModalOpen = false" class="text-zinc-400 hover:text-zinc-600">&times;</button>
                </div>

                <form :action="selectedDoc ? `{{ url('admissions/applicants/' . $applicant->id . '/verify-document') }}/${selectedDoc.id}` : '#'" method="POST" class="space-y-4">
                    @csrf

                    <div>
                        <span class="text-xs text-zinc-500 block">Jenis Berkas:</span>
                        <div class="font-bold text-sm text-zinc-900" x-text="selectedDoc?.document_type"></div>
                    </div>

                    <div>
                        <x-label for="modal_status" required>Hasil Verifikasi Berkas</x-label>
                        <select
                            id="modal_status"
                            name="status"
                            required
                            class="w-full mt-1 px-3 py-2 text-xs sm:text-sm bg-white border border-zinc-200 rounded-lg text-zinc-800 focus:outline-none focus:ring-2 focus:ring-red-100 focus:border-primary font-bold"
                        >
                            <option value="valid">Valid (Berkas Lengkap & Sah)</option>
                            <option value="invalid">Tidak Sesuai / Ditolak (Perlu Perbaikan)</option>
                            <option value="pending">Belum Diperiksa (Pending)</option>
                        </select>
                    </div>

                    <div>
                        <x-label for="review_notes">Catatan Pemeriksa (Opsional)</x-label>
                        <textarea
                            id="review_notes"
                            name="review_notes"
                            rows="2"
                            placeholder="Tuliskan catatan bila dokumen tidak terbaca / perlu perbaikan..."
                            class="w-full mt-1 px-3 py-2 text-xs bg-white border border-zinc-200 rounded-lg text-zinc-800 placeholder-zinc-400 focus:outline-none focus:ring-2 focus:ring-red-100 focus:border-primary"
                        ></textarea>
                    </div>

                    <div class="flex items-center justify-end gap-2 pt-2 border-t border-zinc-100">
                        <x-button type="button" @click="verifyModalOpen = false" variant="secondary" size="sm">
                            Batal
                        </x-button>
                        <x-button type="submit" variant="primary" size="sm">
                            Simpan Verifikasi
                        </x-button>
                    </div>
                </form>
            </div>
        </div>

        <!-- ====================================================================== -->
        <!-- MODAL 2: UBAH STATUS SELEKSI                                           -->
        <!-- ====================================================================== -->
        <div
            x-show="statusModalOpen"
            x-transition
            class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-zinc-900/50 backdrop-blur-xs"
            style="display: none;"
        >
            <div class="bg-white rounded-2xl border border-zinc-200 shadow-xl max-w-md w-full p-6 space-y-4" @click.outside="statusModalOpen = false">
                <div class="flex items-center justify-between pb-3 border-b border-zinc-100">
                    <h3 class="text-sm font-bold text-zinc-900">Ubah Status Seleksi Calon Siswa</h3>
                    <button type="button" @click="statusModalOpen = false" class="text-zinc-400 hover:text-zinc-600">&times;</button>
                </div>

                <form action="{{ route('admissions.applicants.status', $applicant) }}" method="POST" class="space-y-4" x-data="{ currentStatus: '{{ $applicant->status }}' }">
                    @csrf

                    <div>
                        <x-label for="status_select" required>Pilih Keputusan Status</x-label>
                        <select
                            id="status_select"
                            name="status"
                            x-model="currentStatus"
                            required
                            class="w-full mt-1 px-3 py-2 text-xs sm:text-sm bg-white border border-zinc-200 rounded-lg text-zinc-800 focus:outline-none focus:ring-2 focus:ring-red-100 focus:border-primary font-bold"
                        >
                            <option value="pending">Menunggu Verifikasi</option>
                            <option value="verified">Berkas Terverifikasi</option>
                            <option value="revision_needed">Perlu Perbaikan Berkas</option>
                            <option value="accepted">Diterima (Lulus Seleksi)</option>
                            <option value="rejected">Tidak Diterima</option>
                        </select>
                    </div>

                    <div>
                        <x-label for="selection_score">Nilai / Skor Seleksi (Opsional)</x-label>
                        <x-input
                            id="selection_score"
                            name="selection_score"
                            type="number"
                            step="0.01"
                            :value="old('selection_score', $applicant->selection_score)"
                            placeholder="Contoh: 87.50"
                        />
                    </div>

                    <div x-show="currentStatus === 'rejected'">
                        <x-label for="rejection_reason" required>Alasan Penolakan</x-label>
                        <textarea
                            id="rejection_reason"
                            name="rejection_reason"
                            rows="2"
                            placeholder="Alasan tidak lulus seleksi atau kuota penuh..."
                            class="w-full mt-1 px-3 py-2 text-xs bg-white border border-zinc-200 rounded-lg text-zinc-800 placeholder-zinc-400 focus:outline-none focus:ring-2 focus:ring-red-100 focus:border-primary"
                        >{{ old('rejection_reason', $applicant->rejection_reason) }}</textarea>
                    </div>

                    <div>
                        <x-label for="selection_notes">Catatan Tambahan Panitia</x-label>
                        <textarea
                            id="selection_notes"
                            name="selection_notes"
                            rows="2"
                            placeholder="Catatan hasil verifikasi atau arahan daftar ulang..."
                            class="w-full mt-1 px-3 py-2 text-xs bg-white border border-zinc-200 rounded-lg text-zinc-800 placeholder-zinc-400 focus:outline-none focus:ring-2 focus:ring-red-100 focus:border-primary"
                        >{{ old('selection_notes', $applicant->selection_notes) }}</textarea>
                    </div>

                    <div class="flex items-center justify-end gap-2 pt-2 border-t border-zinc-100">
                        <x-button type="button" @click="statusModalOpen = false" variant="secondary" size="sm">
                            Batal
                        </x-button>
                        <x-button type="submit" variant="primary" size="sm">
                            Simpan Keputusan
                        </x-button>
                    </div>
                </form>
            </div>
        </div>

        <!-- ====================================================================== -->
        <!-- MODAL 3: KONVERSI MENJADI SISWA AKTIF                                  -->
        <!-- ====================================================================== -->
        <div
            x-show="convertModalOpen"
            x-transition
            class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-zinc-900/50 backdrop-blur-xs"
            style="display: none;"
        >
            <div class="bg-white rounded-2xl border border-zinc-200 shadow-xl max-w-md w-full p-6 space-y-4" @click.outside="convertModalOpen = false">
                <div class="flex items-center justify-between pb-3 border-b border-zinc-100">
                    <h3 class="text-sm font-bold text-zinc-900">Konversi ke Peserta Didik Aktif</h3>
                    <button type="button" @click="convertModalOpen = false" class="text-zinc-400 hover:text-zinc-600">&times;</button>
                </div>

                <form action="{{ route('admissions.applicants.convert', $applicant) }}" method="POST" class="space-y-4">
                    @csrf

                    <div class="p-3 bg-zinc-50 rounded-xl border border-zinc-200 text-xs text-zinc-600 space-y-1">
                        <p>Tindakan ini akan secara otomatis:</p>
                        <ul class="list-disc pl-4 space-y-0.5 text-zinc-500 text-[11px]">
                            <li>Membuat akun pengguna (User) dengan role <strong>Siswa</strong>.</li>
                            <li>Membuat profil data induk siswa (StudentProfile).</li>
                            <li>Mencatat audit riwayat status kesiswaan.</li>
                        </ul>
                    </div>

                    <div>
                        <x-label for="classroom_id">Penempatan Awal Rombongan Belajar (Kelas)</x-label>
                        <select
                            id="classroom_id"
                            name="classroom_id"
                            class="w-full mt-1 px-3 py-2 text-xs sm:text-sm bg-white border border-zinc-200 rounded-lg text-zinc-800 focus:outline-none focus:ring-2 focus:ring-red-100 focus:border-primary font-medium"
                        >
                            <option value="">-- Tetapkan Nanti (Belum Ada Rombel) --</option>
                            @foreach ($classrooms as $c)
                                <option value="{{ $c->id }}">
                                    {{ $c->name }} ({{ $c->gradeLevel?->name ?? 'Tingkat ' . $c->grade_level }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="flex items-center justify-end gap-2 pt-2 border-t border-zinc-100">
                        <x-button type="button" @click="convertModalOpen = false" variant="secondary" size="sm">
                            Batal
                        </x-button>
                        <x-button type="submit" variant="primary" size="sm">
                            Proses Konversi Siswa &rarr;
                        </x-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-layouts.app>
