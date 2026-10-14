<x-layouts.app>
    <x-slot:headerTitle>
        Buat Tugas Baru
    </x-slot:headerTitle>

    <div class="max-w-4xl mx-auto space-y-6">
        <!-- Breadcrumbs & Header -->
        <div class="flex items-center justify-between">
            <div>
                <a href="{{ route('assignments.index') }}" class="inline-flex items-center gap-1.5 text-xs text-zinc-500 hover:text-brand-600 mb-1">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                    Kembali ke Daftar Tugas
                </a>
                <h1 class="text-xl sm:text-2xl font-extrabold text-zinc-900 tracking-tight">
                    Buat Tugas Pembelajaran
                </h1>
                <p class="text-xs text-zinc-500 mt-0.5">
                    Tentukan instruksi tugas, tipe respon siswa, batas waktu, dan lampiran soal atau rubrik.
                </p>
            </div>
        </div>

        @if ($errors->any())
            <x-alert variant="danger" dismissible>
                {{ $errors->first() }}
            </x-alert>
        @endif

        <form action="{{ route('assignments.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
            @csrf

            <!-- Form Card 1: Informasi Dasar -->
            <x-card padding>
                <div class="space-y-4">
                    <h2 class="text-sm font-bold text-zinc-900 uppercase tracking-wider text-xs">
                        Informasi Penugasan
                    </h2>

                    <!-- Teaching Assignment -->
                    <div>
                        <x-label for="teaching_assignment_id" required>
                            Mata Pelajaran & Kelas Rombel
                        </x-label>
                        <select
                            id="teaching_assignment_id"
                            name="teaching_assignment_id"
                            required
                            class="w-full rounded-lg border border-zinc-300 text-zinc-900 text-xs py-2 px-3 focus:border-brand-500 focus:ring-2 focus:ring-brand-100"
                        >
                            <option value="">-- Pilih Mata Pelajaran & Kelas --</option>
                            @foreach ($teachingAssignments as $ta)
                                <option value="{{ $ta->id }}" {{ old('teaching_assignment_id') == $ta->id ? 'selected' : '' }}>
                                    {{ $ta->subject?->name }} — {{ $ta->classroom?->name }} ({{ $ta->academicYear?->name ?? 'Aktif' }})
                                </option>
                            @endforeach
                        </select>
                        <p class="text-[11px] text-zinc-400 mt-1">Hanya rombel yang Anda ampu yang dapat dipilih.</p>
                    </div>

                    <!-- Title -->
                    <div>
                        <x-label for="title" required>
                            Judul Tugas
                        </x-label>
                        <x-input
                            id="title"
                            name="title"
                            type="text"
                            :value="old('title')"
                            placeholder="Contoh: Analisis Penerapan Hukum Newton dalam Teknologi Transportasi"
                            required
                        />
                    </div>

                    <!-- Instructions -->
                    <div>
                        <x-label for="instructions" required>
                            Petunjuk & Instruksi Pengerjaan
                        </x-label>
                        <x-textarea
                            id="instructions"
                            name="instructions"
                            rows="6"
                            required
                            placeholder="Tuliskan petunjuk pengerjaan tugas secara rinci, kriteria penilaian, dan format yang diharapkan..."
                        >{{ old('instructions') }}</x-textarea>
                    </div>

                    <!-- Teacher Reference Attachments -->
                    <div>
                        <x-label for="attachments">
                            Unggah Lampiran Bahan / Lembar Soal (Opsional)
                        </x-label>
                        <input
                            type="file"
                            id="attachments"
                            name="attachments[]"
                            multiple
                            accept=".pdf,.doc,.docx,.ppt,.pptx,.xls,.xlsx,.zip,.rar,.jpg,.jpeg,.png"
                            class="block w-full text-xs text-zinc-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-zinc-100 file:text-zinc-700 hover:file:bg-zinc-200 cursor-pointer border border-zinc-200 rounded-lg p-1"
                        />
                        <p class="text-[11px] text-zinc-400 mt-1">Format: PDF, Word, PowerPoint, Excel, ZIP, JPG, PNG (Maksimal 25MB per berkas).</p>
                    </div>
                </div>
            </x-card>

            <!-- Form Card 2: Pengaturan Pengumpulan & Berkas -->
            <x-card padding>
                <div class="space-y-4">
                    <h2 class="text-sm font-bold text-zinc-900 uppercase tracking-wider text-xs">
                        Ketentuan Pengumpulan Siswa
                    </h2>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <!-- Submission Type -->
                        <div>
                            <x-label for="submission_type" required>
                                Tipe Jawaban yang Diizinkan
                            </x-label>
                            <select
                                id="submission_type"
                                name="submission_type"
                                required
                                class="w-full rounded-lg border border-zinc-300 text-zinc-900 text-xs py-2 px-3 focus:border-brand-500 focus:ring-2 focus:ring-brand-100"
                            >
                                <option value="both" {{ old('submission_type', 'both') === 'both' ? 'selected' : '' }}>Kombinasi (Teks dan/atau Unggah Berkas)</option>
                                <option value="file" {{ old('submission_type') === 'file' ? 'selected' : '' }}>Hanya Unggah Berkas (PDF/Dokumen)</option>
                                <option value="text" {{ old('submission_type') === 'text' ? 'selected' : '' }}>Hanya Teks Langsung</option>
                            </select>
                        </div>

                        <!-- Max Score -->
                        <div>
                            <x-label for="max_score" required>
                                Skala / Nilai Maksimal
                            </x-label>
                            <x-input
                                id="max_score"
                                name="max_score"
                                type="number"
                                min="1"
                                max="1000"
                                :value="old('max_score', 100)"
                                required
                            />
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <!-- Allowed Types -->
                        <div>
                            <x-label for="allowed_file_types">
                                Format Berkas Diizinkan
                            </x-label>
                            <x-input
                                id="allowed_file_types"
                                name="allowed_file_types"
                                type="text"
                                :value="old('allowed_file_types', 'pdf,doc,docx,zip,jpg,png')"
                                placeholder="pdf,doc,docx,zip,jpg,png"
                            />
                        </div>

                        <!-- Max File Size -->
                        <div>
                            <x-label for="max_file_size_mb">
                                Batas Ukuran Berkas (MB)
                            </x-label>
                            <x-input
                                id="max_file_size_mb"
                                name="max_file_size_mb"
                                type="number"
                                min="1"
                                max="50"
                                :value="old('max_file_size_mb', 10)"
                            />
                        </div>

                        <!-- Max Attachments -->
                        <div>
                            <x-label for="max_attachments">
                                Maksimal Jumlah Berkas
                            </x-label>
                            <x-input
                                id="max_attachments"
                                name="max_attachments"
                                type="number"
                                min="1"
                                max="10"
                                :value="old('max_attachments', 3)"
                            />
                        </div>
                    </div>
                </div>
            </x-card>

            <!-- Form Card 3: Jadwal & Publikasi -->
            <x-card padding>
                <div class="space-y-4">
                    <h2 class="text-sm font-bold text-zinc-900 uppercase tracking-wider text-xs">
                        Jadwal & Waktu
                    </h2>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <!-- Due Date -->
                        <div class="sm:col-span-1">
                            <x-label for="due_date">
                                Tenggat Waktu Pengumpulan (Deadline)
                            </x-label>
                            <x-input
                                id="due_date"
                                name="due_date"
                                type="datetime-local"
                                :value="old('due_date', now()->addDays(7)->format('Y-m-d\TH:i'))"
                            />
                            <p class="text-[11px] text-zinc-400 mt-1">Siswa tetap dapat mengumpulkan setelah tenggat, namun ditandai terlambat.</p>
                        </div>

                        <!-- Status -->
                        <div class="sm:col-span-1">
                            <x-label for="status" required>
                                Status Tugas
                            </x-label>
                            <select
                                id="status"
                                name="status"
                                required
                                class="w-full rounded-lg border border-zinc-300 text-zinc-900 text-xs py-2 px-3 focus:border-brand-500 focus:ring-2 focus:ring-brand-100"
                            >
                                <option value="published" {{ old('status', 'published') === 'published' ? 'selected' : '' }}>Publikasikan Langsung</option>
                                <option value="draft" {{ old('status') === 'draft' ? 'selected' : '' }}>Simpan sebagai Draft</option>
                            </select>
                        </div>

                        <!-- Published At -->
                        <div class="sm:col-span-1">
                            <x-label for="published_at">
                                Waktu Publikasi
                            </x-label>
                            <x-input
                                id="published_at"
                                name="published_at"
                                type="datetime-local"
                                :value="old('published_at', now()->format('Y-m-d\TH:i'))"
                            />
                        </div>
                    </div>
                </div>
            </x-card>

            <!-- Action Buttons -->
            <div class="flex items-center justify-end gap-3 pt-2">
                <x-button href="{{ route('assignments.index') }}" variant="secondary" size="md">
                    Batal
                </x-button>
                <x-button type="submit" variant="primary" size="md">
                    Simpan Tugas
                </x-button>
            </div>
        </form>
    </div>
</x-layouts.app>
