<x-layouts.app>
    <x-slot:headerTitle>
        Edit Tugas: {{ $assignment->title }}
    </x-slot:headerTitle>

    <div class="max-w-4xl mx-auto space-y-6">
        <!-- Breadcrumbs & Header -->
        <div class="flex items-center justify-between">
            <div>
                <a href="{{ route('assignments.show', $assignment) }}" class="inline-flex items-center gap-1.5 text-xs text-zinc-500 hover:text-brand-600 mb-1">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                    Kembali ke Detail Tugas
                </a>
                <h1 class="text-xl sm:text-2xl font-extrabold text-zinc-900 tracking-tight">
                    Edit Tugas Pembelajaran
                </h1>
                <p class="text-xs text-zinc-500 mt-0.5">
                    Perbarui petunjuk pengerjaan, tenggat waktu, atau kelola lampiran bahan penugasan.
                </p>
            </div>
        </div>

        @if ($errors->any())
            <x-alert variant="danger" dismissible>
                {{ $errors->first() }}
            </x-alert>
        @endif

        <form action="{{ route('assignments.update', $assignment) }}" method="POST" enctype="multipart/form-data" class="space-y-6">
            @csrf
            @method('PUT')

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
                            @foreach ($teachingAssignments as $ta)
                                <option value="{{ $ta->id }}" {{ old('teaching_assignment_id', $assignment->teaching_assignment_id) == $ta->id ? 'selected' : '' }}>
                                    {{ $ta->subject?->name }} — {{ $ta->classroom?->name }} ({{ $ta->academicYear?->name ?? 'Aktif' }})
                                </option>
                            @endforeach
                        </select>
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
                            :value="old('title', $assignment->title)"
                            placeholder="Contoh: Analisis Penerapan Hukum Newton"
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
                            placeholder="Tuliskan petunjuk pengerjaan tugas secara rinci..."
                        >{{ old('instructions', $assignment->instructions) }}</x-textarea>
                    </div>

                    <!-- Existing Attachments -->
                    @if ($assignment->attachments->isNotEmpty())
                        <div>
                            <span class="block text-xs font-semibold text-zinc-700 mb-2">
                                Berkas Lampiran Bahan Saat Ini
                            </span>
                            <div class="space-y-2">
                                @foreach ($assignment->attachments as $att)
                                    <div class="flex items-center justify-between p-2.5 rounded-lg border border-zinc-200 bg-zinc-50/70 text-xs">
                                        <div class="flex items-center gap-2 overflow-hidden">
                                            <svg class="w-4 h-4 text-zinc-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/>
                                            </svg>
                                            <span class="font-medium text-zinc-800 truncate">{{ $att->file_name }}</span>
                                            <span class="text-zinc-400 text-[11px] shrink-0">({{ $att->formatted_size }})</span>
                                        </div>
                                        <label class="flex items-center gap-1.5 text-red-600 hover:text-red-700 cursor-pointer text-xs shrink-0 ml-2">
                                            <input type="checkbox" name="remove_attachment_ids[]" value="{{ $att->id }}" class="rounded text-red-600 focus:ring-red-500">
                                            <span>Hapus berkas ini</span>
                                        </label>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <!-- New Attachments -->
                    <div>
                        <x-label for="attachments">
                            Tambah Berkas Lampiran Bahan Baru
                        </x-label>
                        <input
                            type="file"
                            id="attachments"
                            name="attachments[]"
                            multiple
                            accept=".pdf,.doc,.docx,.ppt,.pptx,.xls,.xlsx,.zip,.rar,.jpg,.jpeg,.png"
                            class="block w-full text-xs text-zinc-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-zinc-100 file:text-zinc-700 hover:file:bg-zinc-200 cursor-pointer border border-zinc-200 rounded-lg p-1"
                        />
                    </div>
                </div>
            </x-card>

            <!-- Form Card 2: Ketentuan Pengumpulan -->
            <x-card padding>
                <div class="space-y-4">
                    <h2 class="text-sm font-bold text-zinc-900 uppercase tracking-wider text-xs">
                        Ketentuan Pengumpulan Siswa
                    </h2>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
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
                                <option value="both" {{ old('submission_type', $assignment->submission_type) === 'both' ? 'selected' : '' }}>Kombinasi (Teks dan/atau Unggah Berkas)</option>
                                <option value="file" {{ old('submission_type', $assignment->submission_type) === 'file' ? 'selected' : '' }}>Hanya Unggah Berkas</option>
                                <option value="text" {{ old('submission_type', $assignment->submission_type) === 'text' ? 'selected' : '' }}>Hanya Teks Langsung</option>
                            </select>
                        </div>

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
                                :value="old('max_score', $assignment->max_score)"
                                required
                            />
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div>
                            <x-label for="allowed_file_types">
                                Format Berkas Diizinkan
                            </x-label>
                            <x-input
                                id="allowed_file_types"
                                name="allowed_file_types"
                                type="text"
                                :value="old('allowed_file_types', $assignment->allowed_file_types)"
                            />
                        </div>

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
                                :value="old('max_file_size_mb', $assignment->max_file_size_mb)"
                            />
                        </div>

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
                                :value="old('max_attachments', $assignment->max_attachments)"
                            />
                        </div>
                    </div>
                </div>
            </x-card>

            <!-- Form Card 3: Jadwal & Status -->
            <x-card padding>
                <div class="space-y-4">
                    <h2 class="text-sm font-bold text-zinc-900 uppercase tracking-wider text-xs">
                        Jadwal & Status
                    </h2>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div>
                            <x-label for="due_date">
                                Tenggat Waktu (Deadline)
                            </x-label>
                            <x-input
                                id="due_date"
                                name="due_date"
                                type="datetime-local"
                                :value="old('due_date', $assignment->due_date ? $assignment->due_date->format('Y-m-d\TH:i') : '')"
                            />
                        </div>

                        <div>
                            <x-label for="status" required>
                                Status Tugas
                            </x-label>
                            <select
                                id="status"
                                name="status"
                                required
                                class="w-full rounded-lg border border-zinc-300 text-zinc-900 text-xs py-2 px-3 focus:border-brand-500 focus:ring-2 focus:ring-brand-100"
                            >
                                <option value="published" {{ old('status', $assignment->status) === 'published' ? 'selected' : '' }}>Publikasikan</option>
                                <option value="draft" {{ old('status', $assignment->status) === 'draft' ? 'selected' : '' }}>Draft</option>
                                <option value="archived" {{ old('status', $assignment->status) === 'archived' ? 'selected' : '' }}>Diarsipkan</option>
                            </select>
                        </div>

                        <div>
                            <x-label for="published_at">
                                Waktu Publikasi
                            </x-label>
                            <x-input
                                id="published_at"
                                name="published_at"
                                type="datetime-local"
                                :value="old('published_at', $assignment->published_at ? $assignment->published_at->format('Y-m-d\TH:i') : '')"
                            />
                        </div>
                    </div>
                </div>
            </x-card>

            <!-- Action Buttons -->
            <div class="flex items-center justify-end gap-3 pt-2">
                <x-button href="{{ route('assignments.show', $assignment) }}" variant="secondary" size="md">
                    Batal
                </x-button>
                <x-button type="submit" variant="primary" size="md">
                    Simpan Perubahan
                </x-button>
            </div>
        </form>
    </div>
</x-layouts.app>
