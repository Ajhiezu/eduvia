@extends('layouts.app')

@section('content')
<div class="space-y-6">

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-zinc-200">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <a href="{{ route('website.dashboard') }}" class="text-xs font-semibold text-zinc-500 hover:text-zinc-800">&larr; Ringkasan CMS</a>
            </div>
            <h1 class="text-2xl font-extrabold text-zinc-900 tracking-tight">
                Kelola Galeri Media & Album Foto
            </h1>
            <p class="text-xs sm:text-sm text-zinc-500 mt-0.5">
                Unggah dan atur album kegiatan sekolah yang tampil pada galeri publik.
            </p>
        </div>
    </div>

    @if (session('success'))
        <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-medium flex items-center gap-2">
            <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    @if ($errors->any())
        <div class="p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-xs space-y-1">
            <strong>Gagal mengunggah foto:</strong>
            <ul class="list-disc pl-5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Upload Card -->
    <div class="bg-white rounded-2xl p-6 border border-zinc-200 shadow-2xs space-y-4">
        <h2 class="text-base font-bold text-zinc-900 flex items-center gap-2 border-b border-zinc-100 pb-3">
            <span class="w-2.5 h-2.5 rounded-full bg-[#E11D2E]"></span>
            <span>Unggah Foto ke Album Galeri</span>
        </h2>

        <form method="POST" action="{{ route('website.galleries.store') }}" enctype="multipart/form-data" class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
            @csrf
            <div>
                <label class="block text-xs font-bold text-zinc-700 mb-1">Nama Album <span class="text-rose-500">*</span></label>
                <input type="text" name="album_name" required placeholder="Contoh: Upacara Bendera, Praktikum Lab..." class="w-full px-3 py-2 text-xs rounded-xl border border-zinc-200">
            </div>
            <div>
                <label class="block text-xs font-bold text-zinc-700 mb-1">Judul Foto <span class="text-rose-500">*</span></label>
                <input type="text" name="title" required placeholder="Judul ringkas dokumentasi..." class="w-full px-3 py-2 text-xs rounded-xl border border-zinc-200">
            </div>
            <div>
                <label class="block text-xs font-bold text-zinc-700 mb-1">Pilih File Foto (Maks 5MB) <span class="text-rose-500">*</span></label>
                <input type="file" name="media_file" required accept="image/jpeg,image/png,image/webp" class="w-full text-xs text-zinc-500 file:mr-2 file:py-1.5 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-zinc-100 file:text-zinc-700 hover:file:bg-zinc-200">
            </div>
            <div>
                <button type="submit" class="w-full py-2 px-4 rounded-xl text-xs font-bold bg-[#E11D2E] hover:bg-[#C71827] text-white shadow-xs transition-colors">
                    Unggah Foto
                </button>
            </div>
        </form>
    </div>

    <!-- Albums Display -->
    @if ($galleries->isEmpty())
        <div class="bg-white rounded-2xl p-12 text-center text-zinc-400 text-sm border border-zinc-200">
            Belum ada foto yang diunggah ke galeri sekolah.
        </div>
    @else
        <div class="space-y-8">
            @foreach ($galleries as $albumName => $photos)
                <div class="bg-white rounded-2xl p-6 border border-zinc-200 shadow-2xs space-y-4">
                    <div class="flex items-center justify-between border-b border-zinc-100 pb-3">
                        <h3 class="font-extrabold text-base text-zinc-900 flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                            <span>Album: {{ $albumName }}</span>
                            <span class="text-xs font-normal text-zinc-400">({{ count($photos) }} foto)</span>
                        </h3>
                    </div>

                    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-4">
                        @foreach ($photos as $photo)
                            <div class="bg-zinc-50 rounded-xl overflow-hidden border border-zinc-200 flex flex-col justify-between group">
                                <div class="aspect-square bg-zinc-100 overflow-hidden relative">
                                    <img src="{{ $photo->mediaUrl() }}" alt="{{ $photo->title }}" class="w-full h-full object-cover">
                                </div>
                                <div class="p-2.5 flex items-center justify-between gap-1">
                                    <span class="text-[11px] font-semibold text-zinc-800 truncate" title="{{ $photo->title }}">
                                        {{ $photo->title }}
                                    </span>
                                    <form method="POST" action="{{ route('website.galleries.destroy', $photo) }}" onsubmit="return confirm('Hapus foto ini?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-rose-500 hover:text-rose-700 p-1 text-xs shrink-0" title="Hapus foto">
                                            &times;
                                        </button>
                                    </form>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>
    @endif

</div>
@endsection
