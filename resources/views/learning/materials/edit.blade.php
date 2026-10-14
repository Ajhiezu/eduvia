<x-layouts.app>
    <x-slot:headerTitle>
        Edit Materi Pembelajaran
    </x-slot:headerTitle>

    <div class="max-w-4xl mx-auto space-y-6">
        <!-- Breadcrumbs & Header -->
        <div class="flex items-center justify-between">
            <div>
                <a href="{{ route('learning-materials.show', $material) }}" class="inline-flex items-center gap-1.5 text-xs text-zinc-500 hover:text-brand-600 mb-1">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                    Kembali ke Detail Materi
                </a>
                <h1 class="text-xl sm:text-2xl font-extrabold text-zinc-900 tracking-tight">
                    Edit Materi Pembelajaran
                </h1>
                <p class="text-xs text-zinc-500 mt-0.5">
                    Perbarui isi modul materi, kelola lampiran dokumen bacaan, atau sesuaikan status publikasi.
                </p>
            </div>
        </div>

        @if ($errors->any())
            <x-alert variant="danger" dismissible>
                {{ $errors->first() }}
            </x-alert>
        @endif

        <form action="{{ route('learning-materials.update', $material) }}" method="POST" enctype="multipart/form-data" class="space-y-6">
            @csrf
            @method('PUT')

            <!-- Form Card 1: Metadata & Rombel -->
            <x-card padding>
                <div class="space-y-4">
                    <h2 class="text-sm font-bold text-zinc-900 uppercase tracking-wider text-xs">
                        Informasi Kelas & Mata Pelajaran
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
                                <option value="{{ $ta->id }}" {{ old('teaching_assignment_id', $material->teaching_assignment_id) == $ta->id ? 'selected' : '' }}>
                                    {{ $ta->subject?->name }} — {{ $ta->classroom?->name }} ({{ $ta->academicYear?->name ?? 'Aktif' }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Topic & Title -->
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div class="sm:col-span-1">
                            <x-label for="topic">
                                Bab / Topik Pokok
                            </x-label>
                            <x-input
                                id="topic"
                                name="topic"
                                type="text"
                                :value="old('topic', $material->topic)"
                                placeholder="Contoh: Bab 2 - Dinamika Gerak"
                            />
                        </div>

                        <div class="sm:col-span-2">
                            <x-label for="title" required>
                                Judul Materi Pembelajaran
                            </x-label>
                            <x-input
                                id="title"
                                name="title"
                                type="text"
                                :value="old('title', $material->title)"
                                placeholder="Contoh: Hukum Newton I, II, dan III"
                                required
                            />
                        </div>
                    </div>

                    <!-- Short Description -->
                    <div>
                        <x-label for="description">
                            Ringkasan / Sinopsis Singkat
                        </x-label>
                        <x-textarea
                            id="description"
                            name="description"
                            rows="2"
                            placeholder="Tuliskan gambaran ringkas mengenai apa yang akan dipelajari siswa..."
                        >{{ old('description', $material->description) }}</x-textarea>
                    </div>
                </div>
            </x-card>

            <!-- Form Card 2: Isi Lengkap & Media -->
            <x-card padding>
                <div class="space-y-4">
                    <h2 class="text-sm font-bold text-zinc-900 uppercase tracking-wider text-xs">
                        Isi Materi & Media Belajar
                    </h2>

                    <!-- Full Content -->
                    <div>
                        <x-label for="content" required>
                            Uraian Lengkap Materi
                        </x-label>
                        <x-textarea
                            id="content"
                            name="content"
                            rows="8"
                            required
                            placeholder="Tuliskan materi pembelajaran secara lengkap..."
                        >{{ old('content', $material->content) }}</x-textarea>
                    </div>

                    <!-- Video URL -->
                    <div>
                        <x-label for="video_url">
                            Tautan Video Pembelajaran (Opsional)
                        </x-label>
                        <x-input
                            id="video_url"
                            name="video_url"
                            type="url"
                            :value="old('video_url', $material->video_url)"
                            placeholder="https://www.youtube.com/watch?v=..."
                        />
                    </div>

                    <!-- Existing Attachments -->
                    @if ($material->attachments->isNotEmpty())
                        <div>
                            <span class="block text-xs font-semibold text-zinc-700 mb-2">
                                Berkas Lampiran Saat Ini
                            </span>
                            <div class="space-y-2">
                                @foreach ($material->attachments as $att)
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
                            Tambah Berkas Lampiran Baru
                        </x-label>
                        <input
                            type="file"
                            id="attachments"
                            name="attachments[]"
                            multiple
                            accept=".pdf,.doc,.docx,.ppt,.pptx,.xls,.xlsx,.zip,.rar,.jpg,.jpeg,.png,.mp4"
                            class="block w-full text-xs text-zinc-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-zinc-100 file:text-zinc-700 hover:file:bg-zinc-200 cursor-pointer border border-zinc-200 rounded-lg p-1"
                        />
                        <p class="text-[11px] text-zinc-400 mt-1">Format didukung: PDF, Word, PowerPoint, Excel, ZIP, JPG, PNG, MP4. Maksimal 25MB per berkas.</p>
                    </div>
                </div>
            </x-card>

            <!-- Form Card 3: Status & Publikasi -->
            <x-card padding>
                <div class="space-y-4">
                    <h2 class="text-sm font-bold text-zinc-900 uppercase tracking-wider text-xs">
                        Pengaturan Publikasi
                    </h2>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <x-label for="status" required>
                                Status Materi
                            </x-label>
                            <select
                                id="status"
                                name="status"
                                required
                                class="w-full rounded-lg border border-zinc-300 text-zinc-900 text-xs py-2 px-3 focus:border-brand-500 focus:ring-2 focus:ring-brand-100"
                            >
                                <option value="published" {{ old('status', $material->status) === 'published' ? 'selected' : '' }}>Publikasikan</option>
                                <option value="draft" {{ old('status', $material->status) === 'draft' ? 'selected' : '' }}>Draft (Hanya Guru)</option>
                                <option value="archived" {{ old('status', $material->status) === 'archived' ? 'selected' : '' }}>Diarsipkan (Nonaktif)</option>
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
                                :value="old('published_at', $material->published_at ? $material->published_at->format('Y-m-d\TH:i') : '')"
                            />
                        </div>
                    </div>
                </div>
            </x-card>

            <!-- Action Buttons -->
            <div class="flex items-center justify-end gap-3 pt-2">
                <x-button href="{{ route('learning-materials.show', $material) }}" variant="secondary" size="md">
                    Batal
                </x-button>
                <x-button type="submit" variant="primary" size="md">
                    Simpan Perubahan
                </x-button>
            </div>
        </form>
    </div>
</x-layouts.app>
