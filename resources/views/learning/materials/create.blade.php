<x-layouts.app>
    <x-slot:headerTitle>
        Tambah Materi Pembelajaran
    </x-slot:headerTitle>

    <div class="max-w-4xl mx-auto space-y-6">
        <!-- Breadcrumbs & Header -->
        <div class="flex items-center justify-between">
            <div>
                <a href="{{ route('learning-materials.index') }}" class="inline-flex items-center gap-1.5 text-xs text-zinc-500 hover:text-brand-600 mb-1">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                    Kembali ke Daftar Materi
                </a>
                <h1 class="text-xl sm:text-2xl font-extrabold text-zinc-900 tracking-tight">
                    Tambah Materi Pembelajaran
                </h1>
                <p class="text-xs text-zinc-500 mt-0.5">
                    Susun materi belajar, unggah dokumen ajar atau tautan video untuk rombel yang diampu.
                </p>
            </div>
        </div>

        @if ($errors->any())
            <x-alert variant="danger" dismissible>
                {{ $errors->first() }}
            </x-alert>
        @endif

        <form action="{{ route('learning-materials.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
            @csrf

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
                            <option value="">-- Pilih Mata Pelajaran & Kelas --</option>
                            @foreach ($teachingAssignments as $ta)
                                <option value="{{ $ta->id }}" {{ old('teaching_assignment_id') == $ta->id ? 'selected' : '' }}>
                                    {{ $ta->subject?->name }} — {{ $ta->classroom?->name }} ({{ $ta->academicYear?->name ?? 'Aktif' }})
                                </option>
                            @endforeach
                        </select>
                        <p class="text-[11px] text-zinc-400 mt-1">Hanya rombongan belajar dan mata pelajaran yang Anda ampu yang dapat dipilih.</p>
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
                                :value="old('topic')"
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
                                :value="old('title')"
                                placeholder="Contoh: Hukum Newton I, II, dan III Beserta Penerapannya"
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
                        >{{ old('description') }}</x-textarea>
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
                            placeholder="Tuliskan materi pembelajaran secara lengkap, penjelasan konsep, rumus, petunjuk bacaan, dsb..."
                        >{{ old('content') }}</x-textarea>
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
                            :value="old('video_url')"
                            placeholder="https://www.youtube.com/watch?v=... atau https://loom.com/..."
                        />
                        <p class="text-[11px] text-zinc-400 mt-1">Sertakan tautan YouTube atau platform streaming edukasi untuk melengkapi materi.</p>
                    </div>

                    <!-- File Attachments -->
                    <div>
                        <x-label for="attachments">
                            Unggah Berkas Lampiran (Modul PDF, Slide Presentasi, Lembar Kerja)
                        </x-label>
                        <input
                            type="file"
                            id="attachments"
                            name="attachments[]"
                            multiple
                            accept=".pdf,.doc,.docx,.ppt,.pptx,.xls,.xlsx,.zip,.rar,.jpg,.jpeg,.png,.mp4"
                            class="block w-full text-xs text-zinc-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-zinc-100 file:text-zinc-700 hover:file:bg-zinc-200 cursor-pointer border border-zinc-200 rounded-lg p-1"
                        />
                        <p class="text-[11px] text-zinc-400 mt-1">Format didukung: PDF, Word, PowerPoint, Excel, ZIP, JPG, PNG, MP4. Maksimal 25MB per berkas (Maks 5 berkas sekaligus).</p>
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
                                <option value="published" {{ old('status', 'published') === 'published' ? 'selected' : '' }}>Publikasikan Langsung</option>
                                <option value="draft" {{ old('status') === 'draft' ? 'selected' : '' }}>Simpan sebagai Draft (Hanya Guru)</option>
                            </select>
                        </div>

                        <div>
                            <x-label for="published_at">
                                Waktu Publikasi (Opsional)
                            </x-label>
                            <x-input
                                id="published_at"
                                name="published_at"
                                type="datetime-local"
                                :value="old('published_at', now()->format('Y-m-d\TH:i'))"
                            />
                            <p class="text-[11px] text-zinc-400 mt-1">Kosongkan jika materi berstatus draft atau langsung terbit sekarang.</p>
                        </div>
                    </div>
                </div>
            </x-card>

            <!-- Action Buttons -->
            <div class="flex items-center justify-end gap-3 pt-2">
                <x-button href="{{ route('learning-materials.index') }}" variant="secondary" size="md">
                    Batal
                </x-button>
                <x-button type="submit" variant="primary" size="md">
                    Simpan Materi
                </x-button>
            </div>
        </form>
    </div>
</x-layouts.app>
