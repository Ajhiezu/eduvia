@extends('layouts.app')

@section('content')
<div class="space-y-6">

    <!-- Page Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-zinc-200">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <a href="{{ route('website.dashboard') }}" class="text-xs font-semibold text-zinc-500 hover:text-zinc-800">&larr; Ringkasan CMS</a>
            </div>
            <h1 class="text-2xl font-extrabold text-zinc-900 tracking-tight">
                Kelola Konten: Berita, Acara & Prestasi
            </h1>
            <p class="text-xs sm:text-sm text-zinc-500 mt-0.5">
                Daftar seluruh publikasi dan draf konten sekolah.
            </p>
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ route('website.posts.create', ['type' => $type ?: 'news']) }}" class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-xl text-xs font-bold bg-[#E11D2E] hover:bg-[#C71827] text-white shadow-xs transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                <span>Tulis Konten Baru</span>
            </a>
        </div>
    </div>

    @if (session('success'))
        <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-medium flex items-center gap-2">
            <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    <!-- Filter & Search Bar -->
    <div class="bg-white rounded-2xl p-4 border border-zinc-200 shadow-2xs flex flex-col md:flex-row items-center justify-between gap-4">
        <!-- Type Tabs -->
        <div class="flex items-center gap-1.5 flex-wrap w-full md:w-auto">
            <a href="{{ route('website.posts.index') }}" class="px-3 py-1.5 rounded-lg text-xs font-bold transition-colors {{ empty($type) ? 'bg-zinc-900 text-white' : 'bg-zinc-100 text-zinc-600 hover:bg-zinc-200' }}">
                Semua Tipe
            </a>
            <a href="{{ route('website.posts.index', ['type' => 'news']) }}" class="px-3 py-1.5 rounded-lg text-xs font-bold transition-colors {{ $type === 'news' ? 'bg-[#E11D2E] text-white' : 'bg-zinc-100 text-zinc-600 hover:bg-zinc-200' }}">
                Berita Sekolah
            </a>
            <a href="{{ route('website.posts.index', ['type' => 'event']) }}" class="px-3 py-1.5 rounded-lg text-xs font-bold transition-colors {{ $type === 'event' ? 'bg-emerald-600 text-white' : 'bg-zinc-100 text-zinc-600 hover:bg-zinc-200' }}">
                Agenda Acara
            </a>
            <a href="{{ route('website.posts.index', ['type' => 'achievement']) }}" class="px-3 py-1.5 rounded-lg text-xs font-bold transition-colors {{ $type === 'achievement' ? 'bg-amber-600 text-white' : 'bg-zinc-100 text-zinc-600 hover:bg-zinc-200' }}">
                Prestasi Siswa
            </a>
        </div>

        <!-- Status Filter & Search Form -->
        <form method="GET" action="{{ route('website.posts.index') }}" class="flex items-center gap-2 w-full md:w-auto">
            @if ($type)
                <input type="hidden" name="type" value="{{ $type }}">
            @endif
            <select name="status" onchange="this.form.submit()" class="px-3 py-2 text-xs rounded-xl border border-zinc-200">
                <option value="">Semua Status</option>
                <option value="published" {{ $status === 'published' ? 'selected' : '' }}>Terbit</option>
                <option value="draft" {{ $status === 'draft' ? 'selected' : '' }}>Draf</option>
            </select>
            <input type="text" name="search" value="{{ $search }}" placeholder="Cari judul..." class="w-full md:w-48 px-3 py-2 text-xs rounded-xl border border-zinc-200">
        </form>
    </div>

    <!-- Posts Table -->
    <div class="bg-white rounded-2xl border border-zinc-200 shadow-2xs overflow-hidden">
        @if ($posts->isEmpty())
            <div class="p-12 text-center text-zinc-400 text-sm">
                Tidak ada konten yang sesuai dengan filter.
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-zinc-50 text-zinc-500 uppercase tracking-wider font-semibold border-b border-zinc-200">
                        <tr>
                            <th class="px-5 py-3">Konten</th>
                            <th class="px-4 py-3">Tipe</th>
                            <th class="px-4 py-3">Kategori / Detail</th>
                            <th class="px-4 py-3">Status</th>
                            <th class="px-4 py-3">Tanggal</th>
                            <th class="px-5 py-3 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-100 text-zinc-700">
                        @foreach ($posts as $post)
                            <tr class="hover:bg-zinc-50/60 transition-colors">
                                <td class="px-5 py-3.5">
                                    <div class="flex items-center gap-3">
                                        @if ($post->cover_image_path)
                                            <img src="{{ $post->coverImageUrl() }}" alt="" class="w-10 h-10 rounded-lg object-cover bg-zinc-100 shrink-0 border border-zinc-200">
                                        @else
                                            <div class="w-10 h-10 rounded-lg bg-zinc-100 text-zinc-300 flex items-center justify-center shrink-0 border border-zinc-200">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"/></svg>
                                            </div>
                                        @endif
                                        <div class="min-w-0">
                                            <a href="{{ route('website.posts.edit', $post) }}" class="font-bold text-zinc-900 hover:text-[#E11D2E] truncate block max-w-sm">
                                                {{ $post->title }}
                                            </a>
                                            <span class="text-[11px] text-zinc-400 block truncate">/articles/{{ $post->slug }}</span>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-3.5">
                                    @if ($post->type === 'news')
                                        <span class="px-2 py-0.5 rounded font-bold text-[10px] bg-rose-50 text-[#E11D2E]">Berita</span>
                                    @elseif ($post->type === 'event')
                                        <span class="px-2 py-0.5 rounded font-bold text-[10px] bg-emerald-50 text-emerald-700">Acara</span>
                                    @else
                                        <span class="px-2 py-0.5 rounded font-bold text-[10px] bg-amber-50 text-amber-800">Prestasi</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3.5 text-zinc-600">
                                    @if ($post->type === 'achievement' && $post->awardee_name)
                                        <div class="font-semibold text-zinc-900">{{ $post->awardee_name }}</div>
                                        <div class="text-[11px] text-zinc-400">{{ $post->competition_level }}</div>
                                    @elseif ($post->type === 'event' && $post->event_date)
                                        <div class="font-semibold text-zinc-900">{{ $post->event_date->format('d M Y') }}</div>
                                        <div class="text-[11px] text-zinc-400">{{ $post->event_location ?: '-' }}</div>
                                    @else
                                        <span>{{ $post->category ?: '-' }}</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3.5">
                                    @if ($post->is_published)
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                            Terbit
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                            <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                            Draf
                                        </span>
                                    @endif
                                </td>
                                <td class="px-4 py-3.5 text-zinc-500">
                                    {{ $post->published_at ? $post->published_at->format('d M Y') : $post->created_at->format('d M Y') }}
                                </td>
                                <td class="px-5 py-3.5 text-right">
                                    <div class="flex items-center justify-end gap-1.5">
                                        <!-- Toggle Publish Form -->
                                        <form method="POST" action="{{ route('website.posts.toggle-publish', $post) }}">
                                            @csrf
                                            <button type="submit" class="px-2.5 py-1 text-[11px] font-semibold rounded hover:bg-zinc-100 text-zinc-600 transition-colors">
                                                {{ $post->is_published ? 'Tarik' : 'Terbitkan' }}
                                            </button>
                                        </form>

                                        <a href="{{ route('website.posts.edit', $post) }}" class="px-2.5 py-1 text-[11px] font-semibold rounded bg-zinc-100 hover:bg-zinc-200 text-zinc-800 transition-colors">
                                            Edit
                                        </a>

                                        <!-- Delete Form -->
                                        <form method="POST" action="{{ route('website.posts.destroy', $post) }}" onsubmit="return confirm('Apakah Anda yakin ingin menghapus konten ini?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="px-2 py-1 text-[11px] font-semibold rounded hover:bg-rose-50 text-rose-600 transition-colors">
                                                Hapus
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="p-4 border-t border-zinc-100">
                {{ $posts->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
