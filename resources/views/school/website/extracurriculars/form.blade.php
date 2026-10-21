@extends('layouts.app')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">

    <!-- Header -->
    <div class="flex items-center justify-between pb-4 border-b border-zinc-200">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <a href="{{ route('website.extracurriculars.index') }}" class="text-xs font-semibold text-zinc-500 hover:text-zinc-800">&larr; Kembali ke Daftar Ekstrakurikuler</a>
            </div>
            <h1 class="text-2xl font-extrabold text-zinc-900 tracking-tight">
                {{ $item->exists ? 'Edit Ekstrakurikuler' : 'Tambah Ekstrakurikuler Baru' }}
            </h1>
        </div>
    </div>

    @if ($errors->any())
        <div class="p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-xs space-y-1">
            <strong>Terdapat kesalahan pada formulir:</strong>
            <ul class="list-disc pl-5 space-y-0.5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ $item->exists ? route('website.extracurriculars.update', $item) : route('website.extracurriculars.store') }}" enctype="multipart/form-data" class="space-y-6">
        @csrf
        @if ($item->exists)
            @method('PUT')
        @endif

        <div class="bg-white rounded-2xl p-6 border border-zinc-200 shadow-2xs space-y-5">
            
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-zinc-700 mb-1.5">Nama Kegiatan <span class="text-[#E11D2E]">*</span></label>
                    <input type="text" name="name" value="{{ old('name', $item->name) }}" required placeholder="Contoh: Robotika Club, PMR, Futsal..." class="w-full px-3.5 py-2.5 text-base rounded-lg border border-zinc-200">
                </div>
                <div>
                    <label class="block text-sm font-medium text-zinc-700 mb-1.5">Kategori <span class="text-[#E11D2E]">*</span></label>
                    <input type="text" name="category" value="{{ old('category', $item->category) }}" required placeholder="Olahraga, Seni, Sains, Bahasa, dll." class="w-full px-3.5 py-2.5 text-base rounded-lg border border-zinc-200">
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-zinc-700 mb-1.5">Deskripsi Kegiatan</label>
                <textarea name="description" rows="3" placeholder="Jelaskan tujuan dan kegiatan rutin..." class="w-full px-3.5 py-2.5 text-base rounded-lg border border-zinc-200">{{ old('description', $item->description) }}</textarea>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-zinc-700 mb-1.5">Nama Pembina / Pelatih</label>
                    <input type="text" name="coach_name" value="{{ old('coach_name', $item->coach_name) }}" placeholder="Nama pelatih..." class="w-full px-3.5 py-2.5 text-base rounded-lg border border-zinc-200">
                </div>
                <div>
                    <label class="block text-sm font-medium text-zinc-700 mb-1.5">Jadwal Rutin</label>
                    <input type="text" name="schedule_info" value="{{ old('schedule_info', $item->schedule_info) }}" placeholder="Contoh: Setiap Jumat, 15:30 WIB" class="w-full px-3.5 py-2.5 text-base rounded-lg border border-zinc-200">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-zinc-700 mb-1.5">Tempat / Lokasi</label>
                    <input type="text" name="location" value="{{ old('location', $item->location) }}" placeholder="Contoh: Lapangan Utama, Ruang Lab..." class="w-full px-3.5 py-2.5 text-base rounded-lg border border-zinc-200">
                </div>
                <div>
                    <label class="block text-sm font-medium text-zinc-700 mb-1.5">Persyaratan Partisipasi</label>
                    <input type="text" name="requirements" value="{{ old('requirements', $item->requirements) }}" placeholder="Peralatan yang wajib dibawa..." class="w-full px-3.5 py-2.5 text-base rounded-lg border border-zinc-200">
                </div>
            </div>

            <div class="pt-3 border-t border-zinc-100 grid grid-cols-1 md:grid-cols-2 gap-4 items-start">
                <div>
                    <label class="block text-sm font-medium text-zinc-700 mb-1.5">Foto Sampul (Opsional, Maks 3MB)</label>
                    <input type="file" name="cover_image" accept="image/jpeg,image/png,image/webp" class="w-full text-sm text-zinc-500 file:mr-3 file:py-2 file:px-3.5 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-zinc-100 file:text-zinc-700 hover:file:bg-zinc-200">
                    @if ($item->coverImageUrl())
                        <div class="mt-2 flex items-center justify-between p-2 rounded-lg bg-zinc-50 border border-zinc-200">
                            <div class="flex items-center gap-2">
                                <img src="{{ $item->coverImageUrl() }}" alt="" class="w-16 h-10 object-cover rounded-lg border border-zinc-200">
                                <span class="text-[13px] text-zinc-500 font-medium">Gambar aktif</span>
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
                        <label class="block text-sm font-medium text-zinc-700 mb-1.5">Status Publikasi</label>
                        <select name="is_published" class="w-full px-3.5 py-2.5 text-base rounded-lg border border-zinc-200">
                            <option value="1" {{ old('is_published', $item->is_published) ? 'selected' : '' }}>Terbitkan di Profil Publik</option>
                            <option value="0" {{ !old('is_published', $item->is_published) ? 'selected' : '' }}>Simpan sebagai Draf</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-zinc-700 mb-1.5">Urutan Tampilan (Order Index)</label>
                        <input type="number" name="order_index" value="{{ old('order_index', $item->order_index ?? 0) }}" min="0" max="9999" placeholder="0" class="w-full px-3.5 py-2.5 text-base rounded-lg border border-zinc-200">
                        <span class="text-[13px] text-zinc-400 mt-1 block">Urutan lebih kecil tampil lebih awal.</span>
                    </div>
                </div>
            </div>

        </div>

        <div class="flex items-center justify-end gap-3">
            <a href="{{ route('website.extracurriculars.index') }}" class="px-4 py-2.5 rounded-lg text-sm font-medium text-zinc-600 hover:bg-zinc-100">
                Batal
            </a>
            <button type="submit" class="px-6 py-2.5 rounded-lg text-sm font-semibold bg-[#E11D2E] hover:bg-[#C71827] text-white shadow-xs">
                {{ $item->exists ? 'Simpan Perubahan' : 'Tambahkan Ekstrakurikuler' }}
            </button>
        </div>

    </form>
</div>
@endsection
