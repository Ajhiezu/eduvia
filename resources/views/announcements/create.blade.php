<x-layouts.app>
    <x-slot:headerTitle>
        Buat Pengumuman Sekolah Baru
    </x-slot:headerTitle>

    <div class="max-w-3xl mx-auto space-y-6">

        <div class="flex items-center justify-between pb-4 border-b border-zinc-200">
            <div>
                <a href="{{ route('announcements.index') }}" class="text-xs text-zinc-500 hover:text-zinc-900 inline-flex items-center gap-1 font-semibold">
                    &larr; Kembali ke Daftar Pengumuman
                </a>
                <h1 class="text-xl sm:text-2xl font-extrabold text-zinc-900 mt-1">
                    Buat Pengumuman Baru
                </h1>
                <p class="text-xs text-zinc-500">
                    Sampaikan informasi penting kepada seluruh warga sekolah, orang tua, atau siswa
                </p>
            </div>
        </div>

        <div class="bg-white rounded-2xl border border-zinc-200 shadow-xs p-6">
            <form action="{{ route('announcements.store') }}" method="POST" enctype="multipart/form-data" class="space-y-5">
                @csrf

                <div>
                    <label class="block text-xs font-semibold text-zinc-700 mb-1">Judul Pengumuman *</label>
                    <input
                        type="text"
                        name="title"
                        value="{{ old('title') }}"
                        required
                        placeholder="Contoh: Jadwal Penilaian Akhir Semester Ganjil 2026/2027"
                        class="w-full text-xs rounded-xl border-zinc-300 focus:border-[#E11D2E] focus:ring-[#E11D2E]"
                    >
                    @error('title')
                        <p class="text-xs text-rose-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-zinc-700 mb-1">Kategori Pengumuman *</label>
                        <select name="category" required class="w-full text-xs rounded-xl border-zinc-300 focus:border-[#E11D2E] focus:ring-[#E11D2E]">
                            @foreach (\App\Models\SchoolAnnouncement::CATEGORIES as $key => $label)
                                <option value="{{ $key }}" {{ old('category') === $key ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-zinc-700 mb-1">Sasaran Penerima *</label>
                        <select name="target_audience" required class="w-full text-xs rounded-xl border-zinc-300 focus:border-[#E11D2E] focus:ring-[#E11D2E]">
                            @foreach (\App\Models\SchoolAnnouncement::AUDIENCES as $key => $label)
                                <option value="{{ $key }}" {{ old('target_audience') === $key ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-zinc-700 mb-1">Target Jenjang (Opsional)</label>
                        <select name="grade_level_id" class="w-full text-xs rounded-xl border-zinc-300 focus:border-[#E11D2E] focus:ring-[#E11D2E]">
                            <option value="">Semua Tingkat / Jenjang</option>
                            @foreach ($gradeLevels as $lvl)
                                <option value="{{ $lvl->id }}" {{ old('grade_level_id') == $lvl->id ? 'selected' : '' }}>{{ $lvl->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-zinc-700 mb-1">Target Kelas Spesifik (Opsional)</label>
                        <select name="classroom_id" class="w-full text-xs rounded-xl border-zinc-300 focus:border-[#E11D2E] focus:ring-[#E11D2E]">
                            <option value="">Semua Rombongan Belajar</option>
                            @foreach ($classrooms as $cls)
                                <option value="{{ $cls->id }}" {{ old('classroom_id') == $cls->id ? 'selected' : '' }}>{{ $cls->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-zinc-700 mb-1">Isi Lengkap Pengumuman *</label>
                    <textarea
                        name="content"
                        rows="6"
                        required
                        placeholder="Tuliskan isi pengumuman secara rinci dan jelas..."
                        class="w-full text-xs rounded-xl border-zinc-300 focus:border-[#E11D2E] focus:ring-[#E11D2E]"
                    >{{ old('content') }}</textarea>
                    @error('content')
                        <p class="text-xs text-rose-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-xs font-semibold text-zinc-700 mb-1">Berkas Lampiran Dokumen (Opsional - PDF/DOC/Gambar Maks 5MB)</label>
                    <input
                        type="file"
                        name="attachment"
                        class="block w-full text-xs text-zinc-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-zinc-100 file:text-zinc-700 hover:file:bg-zinc-200"
                    >
                    @error('attachment')
                        <p class="text-xs text-rose-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex items-center gap-6 pt-2">
                    <label class="inline-flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="is_pinned" value="1" {{ old('is_pinned') ? 'checked' : '' }} class="rounded border-zinc-300 text-[#E11D2E] focus:ring-[#E11D2E]">
                        <span class="text-xs font-bold text-zinc-800">Sematkan di Atas (Pinned Announcement)</span>
                    </label>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-2">
                    <div>
                        <label class="block text-xs font-semibold text-zinc-700 mb-1">Status Publikasi</label>
                        <select name="status" class="w-full text-xs rounded-xl border-zinc-300 focus:border-[#E11D2E] focus:ring-[#E11D2E]">
                            <option value="published">Langsung Terbitkan</option>
                            <option value="draft">Simpan Sebagai Draf</option>
                        </select>
                    </div>
                </div>

                <div class="pt-4 border-t border-zinc-100 flex items-center justify-end gap-3">
                    <a href="{{ route('announcements.index') }}" class="px-4 py-2 rounded-xl text-xs font-semibold text-zinc-600 hover:bg-zinc-100">Batal</a>
                    <button type="submit" class="px-5 py-2.5 rounded-xl text-xs font-bold bg-[#E11D2E] hover:bg-[#C81423] text-white shadow-xs transition-colors">
                        Simpan & Terbitkan
                    </button>
                </div>
            </form>
        </div>

    </div>
</x-layouts.app>
