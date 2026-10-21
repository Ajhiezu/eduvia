@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    <!-- Header -->
    <div class="flex items-center justify-between pb-4 border-b border-zinc-200">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <a href="{{ route('website.posts.index') }}" class="text-xs font-semibold text-zinc-500 hover:text-zinc-800">&larr; Kembali ke Daftar Konten</a>
            </div>
            <h1 class="text-2xl font-extrabold text-zinc-900 tracking-tight">
                {{ $post->exists ? 'Edit Konten Website' : 'Tulis Konten Baru' }}
            </h1>
            <p class="text-xs sm:text-sm text-zinc-500 mt-0.5">
                Kelola artikel berita, agenda kegiatan, atau pencapaian prestasi sekolah.
            </p>
        </div>
    </div>

    @if ($errors->any())
        <div class="p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-xs space-y-1">
            <strong>Terdapat kesalahan pada input formulir:</strong>
            <ul class="list-disc pl-5 space-y-0.5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ $post->exists ? route('website.posts.update', $post) : route('website.posts.store') }}" enctype="multipart/form-data" class="space-y-6" x-data="{ currentType: '{{ old('type', $post->type ?? $type) }}' }">
        @csrf
        @if ($post->exists)
            @method('PUT')
        @endif

        <div class="bg-white rounded-2xl p-6 border border-zinc-200 shadow-2xs space-y-5">
            
            <!-- Type Selector -->
            <div>
                <label class="block text-sm font-medium text-zinc-700 mb-1.5">Jenis Konten <span class="text-[#E11D2E]">*</span></label>
                <div class="grid grid-cols-3 gap-3">
                    <label class="flex items-center gap-2 p-3 rounded-lg border cursor-pointer transition-colors" :class="currentType === 'news' ? 'border-[#E11D2E] bg-rose-50/50 text-[#E11D2E] font-medium' : 'border-zinc-200 text-zinc-700'">
                        <input type="radio" name="type" value="news" x-model="currentType" class="text-[#E11D2E] focus:ring-[#E11D2E]">
                        <span class="text-sm">Berita Sekolah</span>
                    </label>
                    <label class="flex items-center gap-2 p-3 rounded-lg border cursor-pointer transition-colors" :class="currentType === 'event' ? 'border-emerald-600 bg-emerald-50/50 text-emerald-800 font-medium' : 'border-zinc-200 text-zinc-700'">
                        <input type="radio" name="type" value="event" x-model="currentType" class="text-emerald-600 focus:ring-emerald-600">
                        <span class="text-sm">Agenda Acara</span>
                    </label>
                    <label class="flex items-center gap-2 p-3 rounded-lg border cursor-pointer transition-colors" :class="currentType === 'achievement' ? 'border-amber-600 bg-amber-50/50 text-amber-900 font-medium' : 'border-zinc-200 text-zinc-700'">
                        <input type="radio" name="type" value="achievement" x-model="currentType" class="text-amber-600 focus:ring-amber-600">
                        <span class="text-sm">Prestasi Siswa</span>
                    </label>
                </div>
            </div>

            <!-- Title -->
            <div>
                <label class="block text-sm font-medium text-zinc-700 mb-1.5">Judul Konten <span class="text-[#E11D2E]">*</span></label>
                <input type="text" name="title" value="{{ old('title', $post->title) }}" required placeholder="Masukkan judul artikel / agenda / prestasi..." class="w-full px-3.5 py-2.5 text-base rounded-lg border border-zinc-200 focus:outline-none focus:border-[#E11D2E] font-medium text-zinc-900">
            </div>

            <!-- Category -->
            <div>
                <label class="block text-sm font-medium text-zinc-700 mb-1.5">Kategori / Topik</label>
                <input type="text" name="category" value="{{ old('category', $post->category) }}" placeholder="Contoh: Akademik, Kesiswaan, Teknologi, Keagamaan..." class="w-full px-3.5 py-2.5 text-base rounded-lg border border-zinc-200">
            </div>

            <!-- Conditional Section for Event -->
            <div x-show="currentType === 'event'" x-transition class="p-4 rounded-lg bg-emerald-50/60 border border-emerald-200/80 space-y-4">
                <h3 class="text-sm font-semibold text-emerald-900">Detail Waktu & Lokasi Acara</h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-zinc-700 mb-1.5">Tanggal & Waktu Mulai</label>
                        <input type="datetime-local" name="event_date" value="{{ old('event_date', $post->event_date?->format('Y-m-d\TH:i')) }}" class="w-full px-3.5 py-2.5 text-base rounded-lg border border-zinc-200 bg-white">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-zinc-700 mb-1.5">Tanggal Selesai (Opsional)</label>
                        <input type="datetime-local" name="event_end_date" value="{{ old('event_end_date', $post->event_end_date?->format('Y-m-d\TH:i')) }}" class="w-full px-3.5 py-2.5 text-base rounded-lg border border-zinc-200 bg-white">
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-zinc-700 mb-1.5">Tempat / Lokasi Penyelenggaraan</label>
                    <input type="text" name="event_location" value="{{ old('event_location', $post->event_location) }}" placeholder="Contoh: Auditorium Utama, Hall Olahraga..." class="w-full px-3.5 py-2.5 text-base rounded-lg border border-zinc-200 bg-white">
                </div>
            </div>

            <!-- Conditional Section for Achievement -->
            <div x-show="currentType === 'achievement'" x-transition class="p-4 rounded-lg bg-amber-50/60 border border-amber-200/80 space-y-4">
                <h3 class="text-sm font-semibold text-amber-900">Detail Kompetisi & Penerima Penghargaan</h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-zinc-700 mb-1.5">Nama Kompetisi / Perlombaan</label>
                        <input type="text" name="competition_name" value="{{ old('competition_name', $post->competition_name) }}" placeholder="Contoh: Olimpiade Sains Nasional (OSN)..." class="w-full px-3.5 py-2.5 text-base rounded-lg border border-zinc-200 bg-white">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-zinc-700 mb-1.5">Tingkat Kompetisi</label>
                        <select name="competition_level" class="w-full px-3.5 py-2.5 text-base rounded-lg border border-zinc-200 bg-white">
                            <option value="">Pilih Tingkat</option>
                            <option value="Tingkat Sekolah" {{ old('competition_level', $post->competition_level) === 'Tingkat Sekolah' ? 'selected' : '' }}>Tingkat Sekolah</option>
                            <option value="Tingkat Kecamatan" {{ old('competition_level', $post->competition_level) === 'Tingkat Kecamatan' ? 'selected' : '' }}>Tingkat Kecamatan</option>
                            <option value="Tingkat Kota/Kabupaten" {{ old('competition_level', $post->competition_level) === 'Tingkat Kota/Kabupaten' ? 'selected' : '' }}>Tingkat Kota/Kabupaten</option>
                            <option value="Tingkat Provinsi" {{ old('competition_level', $post->competition_level) === 'Tingkat Provinsi' ? 'selected' : '' }}>Tingkat Provinsi</option>
                            <option value="Tingkat Nasional" {{ old('competition_level', $post->competition_level) === 'Tingkat Nasional' ? 'selected' : '' }}>Tingkat Nasional</option>
                            <option value="Tingkat Internasional" {{ old('competition_level', $post->competition_level) === 'Tingkat Internasional' ? 'selected' : '' }}>Tingkat Internasional</option>
                        </select>
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-zinc-700 mb-1.5">Nama Penerima / Pemenang</label>
                    <input type="text" name="awardee_name" value="{{ old('awardee_name', $post->awardee_name) }}" placeholder="Nama siswa atau tim peraih penghargaan..." class="w-full px-3.5 py-2.5 text-base rounded-lg border border-zinc-200 bg-white">
                </div>
            </div>

            <!-- Excerpt -->
            <div>
                <label class="block text-sm font-medium text-zinc-700 mb-1.5">Ringkasan Singkat (Excerpt)</label>
                <textarea name="excerpt" rows="2" maxlength="500" placeholder="Ringkasan 1-2 kalimat yang tampil pada daftar kartu berita..." class="w-full px-3.5 py-2.5 text-base rounded-lg border border-zinc-200">{{ old('excerpt', $post->excerpt) }}</textarea>
            </div>

            <!-- Content -->
            <div>
                <label class="block text-sm font-medium text-zinc-700 mb-1.5">Isi Lengkap Konten <span class="text-[#E11D2E]">*</span></label>
                <textarea name="content" rows="8" required placeholder="Tuliskan berita atau deskripsi lengkap di sini..." class="w-full px-3.5 py-2.5 text-base rounded-lg border border-zinc-200 leading-relaxed font-sans">{{ old('content', $post->content) }}</textarea>
            </div>

            <!-- Cover Image, Status & Order -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 items-start pt-2 border-t border-zinc-100">
                <div>
                    <label class="block text-sm font-medium text-zinc-700 mb-1.5">Gambar Sampul (Cover Image, Maks 3MB)</label>
                    <input type="file" name="cover_image" accept="image/jpeg,image/png,image/webp" class="w-full text-sm text-zinc-500 file:mr-3 file:py-2 file:px-3.5 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-zinc-100 file:text-zinc-700 hover:file:bg-zinc-200">
                    @if ($post->coverImageUrl())
                        <div class="mt-2 flex items-center justify-between p-2 rounded-lg bg-zinc-50 border border-zinc-200">
                            <div class="flex items-center gap-2">
                                <img src="{{ $post->coverImageUrl() }}" alt="" class="w-16 h-10 object-cover rounded-lg border border-zinc-200">
                                <span class="text-[13px] text-zinc-500 font-medium">Sampul aktif</span>
                            </div>
                            <label class="inline-flex items-center gap-1.5 text-sm text-rose-600 font-medium cursor-pointer">
                                <input type="checkbox" name="remove_cover_image" value="1" class="rounded border-zinc-300 text-[#E11D2E] focus:ring-[#E11D2E]">
                                <span>Hapus gambar ini</span>
                            </label>
                        </div>
                    @endif
                </div>

                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-zinc-700 mb-1.5">Status Publikasi <span class="text-[#E11D2E]">*</span></label>
                        <select name="is_published" class="w-full px-3.5 py-2.5 text-base rounded-lg border border-zinc-200">
                            <option value="1" {{ old('is_published', $post->is_published) ? 'selected' : '' }}>
                                Terbitkan (Tampil di website publik sekolah)
                            </option>
                            <option value="0" {{ !old('is_published', $post->is_published) ? 'selected' : '' }}>
                                Simpan sebagai Draf (Hanya admin yang dapat melihat)
                            </option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-zinc-700 mb-1.5">Urutan Tampilan (Order Index)</label>
                        <input type="number" name="order_index" value="{{ old('order_index', $post->order_index ?? 0) }}" min="0" max="9999" placeholder="0" class="w-full px-3.5 py-2.5 text-base rounded-lg border border-zinc-200">
                        <span class="text-[13px] text-zinc-400 mt-1 block">Urutan lebih kecil (0, 1, 2) tampil lebih awal.</span>
                    </div>
                </div>
            </div>

        </div>

        <!-- Submit Bar -->
        <div class="flex items-center justify-end gap-3">
            <a href="{{ route('website.posts.index') }}" class="px-4 py-2.5 rounded-lg text-sm font-medium text-zinc-600 hover:bg-zinc-100 transition-colors">
                Batal
            </a>
            <button type="submit" class="px-6 py-2.5 rounded-lg text-sm font-semibold bg-[#E11D2E] hover:bg-[#C71827] text-white shadow-xs transition-colors">
                {{ $post->exists ? 'Simpan Perubahan' : 'Terbitkan Konten' }}
            </button>
        </div>

    </form>
</div>
@endsection
