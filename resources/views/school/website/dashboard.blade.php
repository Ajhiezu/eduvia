@extends('layouts.app')

@section('content')
<div class="space-y-6">

    <!-- Page Header & Actions -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-zinc-200">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold uppercase tracking-wider bg-rose-50 text-[#E11D2E] border border-rose-100">
                    CMS Website Publik
                </span>
                <span class="w-1.5 h-1.5 rounded-full {{ $school->public_website_enabled ? 'bg-emerald-500' : 'bg-amber-500' }}"></span>
                <span class="text-xs font-semibold text-zinc-500">
                    {{ $school->public_website_enabled ? 'Situs Aktif' : 'Situs Dinonaktifkan' }}
                </span>
            </div>
            <h1 class="text-2xl font-extrabold text-zinc-900 tracking-tight">
                Pengelolaan Website Sekolah: {{ $school->name }}
            </h1>
            <p class="text-xs sm:text-sm text-zinc-500 mt-0.5">
                Kelola profil, berita, agenda, prestasi, ekstrakurikuler, fasilitas, dan galeri yang tampil pada halaman publik.
            </p>
        </div>

        <div class="flex items-center gap-2.5 shrink-0">
            <!-- Toggle Status Form -->
            <form method="POST" action="{{ route('website.toggle-status') }}">
                @csrf
                <button type="submit" class="px-3.5 py-2 rounded-xl text-xs font-bold border transition-colors {{ $school->public_website_enabled ? 'border-amber-200 bg-amber-50 text-amber-800 hover:bg-amber-100' : 'border-emerald-200 bg-emerald-50 text-emerald-800 hover:bg-emerald-100' }}">
                    {{ $school->public_website_enabled ? 'Nonaktifkan Situs' : 'Aktifkan Situs' }}
                </button>
            </form>

            <!-- Preview Link -->
            <a href="{{ route('school.public.preview', ['school' => $school->slug]) }}" target="_blank" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-xs font-bold bg-zinc-800 hover:bg-zinc-900 text-white transition-colors">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                <span>Pratinjau Draf</span>
            </a>

            <!-- Public Link -->
            <a href="{{ route('school.public.home', ['school' => $school->slug]) }}" target="_blank" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-xs font-bold bg-[#E11D2E] hover:bg-[#C71827] text-white shadow-xs transition-colors">
                <span>Buka Website Publik</span>
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
            </a>
        </div>
    </div>

    <!-- Website URL Alert Card -->
    <div class="bg-zinc-900 rounded-2xl p-4 sm:p-5 text-white flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div class="space-y-1">
            <span class="text-[11px] font-bold uppercase tracking-wider text-zinc-400">Alamat URL Publik Sekolah</span>
            <div class="font-mono text-sm sm:text-base font-bold text-rose-300 break-all">
                {{ route('school.public.home', ['school' => $school->slug]) }}
            </div>
            <p class="text-xs text-zinc-400">
                Slug: <strong>{{ $school->slug }}</strong> &bull; Publik dapat mengakses informasi sekolah secara mandiri tanpa harus login.
            </p>
        </div>
        <a href="{{ route('website.settings') }}" class="px-4 py-2 rounded-xl text-xs font-bold bg-white/10 hover:bg-white/20 text-white border border-white/20 transition-colors shrink-0">
            Kelola Pengaturan & Slug
        </a>
    </div>

    <!-- Metric Stats Cards -->
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-7 gap-3.5">
        <div class="bg-white rounded-2xl p-4 border border-zinc-200 shadow-2xs">
            <span class="text-[11px] font-medium text-zinc-500 block">Total Konten</span>
            <strong class="text-2xl font-black text-zinc-900">{{ $stats['total_posts'] }}</strong>
            <span class="text-[10px] text-zinc-400 block mt-1">Berita, acara, prestasi</span>
        </div>
        <div class="bg-white rounded-2xl p-4 border border-zinc-200 shadow-2xs">
            <span class="text-[11px] font-medium text-emerald-600 block">Konten Terbit</span>
            <strong class="text-2xl font-black text-emerald-700">{{ $stats['published_posts'] }}</strong>
            <span class="text-[10px] text-emerald-600/70 block mt-1">Tampil di publik</span>
        </div>
        <div class="bg-white rounded-2xl p-4 border border-zinc-200 shadow-2xs">
            <span class="text-[11px] font-medium text-amber-600 block">Konten Draf</span>
            <strong class="text-2xl font-black text-amber-700">{{ $stats['draft_posts'] }}</strong>
            <span class="text-[10px] text-amber-600/70 block mt-1">Belum dipublikasikan</span>
        </div>
        <div class="bg-white rounded-2xl p-4 border border-zinc-200 shadow-2xs">
            <span class="text-[11px] font-medium text-zinc-500 block">Ekstrakurikuler</span>
            <strong class="text-2xl font-black text-zinc-900">{{ $stats['extracurricular_count'] }}</strong>
            <span class="text-[10px] text-zinc-400 block mt-1">Klub & pembinaan</span>
        </div>
        <div class="bg-white rounded-2xl p-4 border border-zinc-200 shadow-2xs">
            <span class="text-[11px] font-medium text-zinc-500 block">Fasilitas & Program</span>
            <strong class="text-2xl font-black text-zinc-900">{{ $stats['facility_count'] + $stats['program_count'] }}</strong>
            <span class="text-[10px] text-zinc-400 block mt-1">Sarana prasarana</span>
        </div>
        <div class="bg-white rounded-2xl p-4 border border-zinc-200 shadow-2xs">
            <span class="text-[11px] font-medium text-zinc-500 block">Galeri Foto</span>
            <strong class="text-2xl font-black text-zinc-900">{{ $stats['gallery_count'] }}</strong>
            <span class="text-[10px] text-zinc-400 block mt-1">Foto dokumentasi</span>
        </div>
        <a href="{{ route('website.messages.index') }}" class="bg-white rounded-2xl p-4 border border-rose-200 shadow-2xs bg-rose-50/20 hover:bg-rose-50/40 transition-colors block">
            <span class="text-[11px] font-bold text-[#E11D2E] block">Pesan Masuk</span>
            <strong class="text-2xl font-black text-[#E11D2E]">{{ $stats['unread_messages'] ?? 0 }}</strong>
            <span class="text-[10px] text-[#E11D2E]/80 block mt-1">Belum dibaca &rarr;</span>
        </a>
    </div>

    <!-- Shortcuts & Quick Actions -->
    <div class="bg-white rounded-2xl p-5 border border-zinc-200 shadow-2xs">
        <h2 class="text-xs font-bold text-zinc-400 uppercase tracking-wider mb-3">Tindakan Cepat & Tambah Konten</h2>
        <div class="flex flex-wrap items-center gap-2.5">
            <a href="{{ route('website.posts.create', ['type' => 'news']) }}" class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl text-xs font-bold bg-rose-50 text-[#E11D2E] hover:bg-rose-100 transition-colors border border-rose-100">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                <span>Tulis Berita Baru</span>
            </a>
            <a href="{{ route('website.posts.create', ['type' => 'event']) }}" class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl text-xs font-bold bg-emerald-50 text-emerald-700 hover:bg-emerald-100 transition-colors border border-emerald-100">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                <span>Tambah Agenda Acara</span>
            </a>
            <a href="{{ route('website.posts.create', ['type' => 'achievement']) }}" class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl text-xs font-bold bg-amber-50 text-amber-800 hover:bg-amber-100 transition-colors border border-amber-100">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/></svg>
                <span>Catat Prestasi Siswa</span>
            </a>
            <a href="{{ route('website.extracurriculars.create') }}" class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl text-xs font-bold bg-zinc-100 text-zinc-700 hover:bg-zinc-200 transition-colors">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                <span>Tambah Ekstrakurikuler</span>
            </a>
            <a href="{{ route('website.facilities.create') }}" class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl text-xs font-bold bg-zinc-100 text-zinc-700 hover:bg-zinc-200 transition-colors">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                <span>Tambah Fasilitas/Program</span>
            </a>
            <a href="{{ route('website.galleries.index') }}" class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl text-xs font-bold bg-zinc-100 text-zinc-700 hover:bg-zinc-200 transition-colors">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                <span>Unggah Foto Galeri</span>
            </a>
            <a href="{{ route('website.messages.index') }}" class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl text-xs font-bold bg-rose-50 text-[#E11D2E] hover:bg-rose-100 border border-rose-100 transition-colors">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                <span>Kelola Pesan Pengunjung</span>
            </a>
        </div>
    </div>

    <!-- Recent Content Table -->
    <div class="bg-white rounded-2xl border border-zinc-200 shadow-2xs overflow-hidden">
        <div class="p-5 border-b border-zinc-100 flex items-center justify-between">
            <div>
                <h3 class="font-bold text-base text-zinc-900">Konten Terbaru</h3>
                <p class="text-xs text-zinc-500">Daftar publikasi dan draf terbaru di website sekolah.</p>
            </div>
            <a href="{{ route('website.posts.index') }}" class="text-xs font-bold text-[#E11D2E] hover:underline">
                Lihat Semua Konten &rarr;
            </a>
        </div>

        @if ($recentPosts->isEmpty())
            <div class="p-8 text-center text-zinc-400 text-sm">
                Belum ada konten yang dibuat. Mulai dengan membuat berita atau agenda acara!
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-zinc-50 text-zinc-500 uppercase tracking-wider font-semibold border-b border-zinc-200">
                        <tr>
                            <th class="px-5 py-3">Judul Konten</th>
                            <th class="px-4 py-3">Tipe</th>
                            <th class="px-4 py-3">Status</th>
                            <th class="px-4 py-3">Tanggal</th>
                            <th class="px-5 py-3 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-100 text-zinc-700">
                        @foreach ($recentPosts as $p)
                            <tr class="hover:bg-zinc-50/60 transition-colors">
                                <td class="px-5 py-3 font-semibold text-zinc-900">
                                    <div class="truncate max-w-sm" title="{{ $p->title }}">
                                        {{ $p->title }}
                                    </div>
                                    <span class="text-[11px] text-zinc-400 font-normal">/articles/{{ $p->slug }}</span>
                                </td>
                                <td class="px-4 py-3">
                                    @if ($p->type === 'news')
                                        <span class="px-2 py-0.5 rounded font-bold text-[10px] bg-rose-50 text-[#E11D2E]">Berita</span>
                                    @elseif ($p->type === 'event')
                                        <span class="px-2 py-0.5 rounded font-bold text-[10px] bg-emerald-50 text-emerald-700">Acara</span>
                                    @else
                                        <span class="px-2 py-0.5 rounded font-bold text-[10px] bg-amber-50 text-amber-800">Prestasi</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3">
                                    @if ($p->is_published)
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                            Terbit
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-50 text-amber-700">
                                            <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                            Draf
                                        </span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-zinc-500">
                                    {{ $p->published_at ? $p->published_at->format('d M Y') : $p->created_at->format('d M Y') }}
                                </td>
                                <td class="px-5 py-3 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <form method="POST" action="{{ route('website.posts.toggle-publish', $p) }}">
                                            @csrf
                                            <button type="submit" class="px-2 py-1 text-[11px] font-semibold rounded hover:bg-zinc-100 text-zinc-600 transition-colors" title="{{ $p->is_published ? 'Tarik ke draf' : 'Terbitkan' }}">
                                                {{ $p->is_published ? 'Tarik Draf' : 'Publikasikan' }}
                                            </button>
                                        </form>
                                        <a href="{{ route('website.posts.edit', $p) }}" class="px-2 py-1 text-[11px] font-semibold rounded bg-zinc-100 hover:bg-zinc-200 text-zinc-800 transition-colors">
                                            Edit
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    <!-- Recent Guest Messages Table -->
    <div class="bg-white rounded-2xl border border-zinc-200 shadow-2xs overflow-hidden">
        <div class="p-5 border-b border-zinc-100 flex items-center justify-between">
            <div>
                <h3 class="font-bold text-base text-zinc-900">Pesan Pengunjung Terbaru</h3>
                <p class="text-xs text-zinc-500">Pertanyaan dan buku tamu yang dikirim pengunjung melalui website resmi.</p>
            </div>
            <a href="{{ route('website.messages.index') }}" class="text-xs font-bold text-[#E11D2E] hover:underline">
                Lihat Semua Pesan &rarr;
            </a>
        </div>

        @if (empty($recentMessages) || $recentMessages->isEmpty())
            <div class="p-8 text-center text-zinc-400 text-sm">
                Belum ada pesan yang masuk dari pengunjung website sekolah.
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-zinc-50 text-zinc-500 uppercase tracking-wider font-semibold border-b border-zinc-200">
                        <tr>
                            <th class="px-5 py-3">Pengirim</th>
                            <th class="px-4 py-3">Subjek & Referensi</th>
                            <th class="px-4 py-3">Kategori</th>
                            <th class="px-4 py-3">Status</th>
                            <th class="px-4 py-3">Waktu</th>
                            <th class="px-5 py-3 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-100 text-zinc-700">
                        @foreach ($recentMessages as $msg)
                            <tr class="hover:bg-zinc-50/60 transition-colors {{ ! $msg->is_read ? 'bg-rose-50/20 font-medium' : '' }}">
                                <td class="px-5 py-3">
                                    <div class="flex items-center gap-2">
                                        @if (! $msg->is_read)
                                            <span class="w-2 h-2 rounded-full bg-[#E11D2E] shrink-0" title="Belum dibaca"></span>
                                        @endif
                                        <div>
                                            <span class="font-bold text-zinc-900 block truncate max-w-xs">{{ $msg->name }}</span>
                                            <span class="text-[11px] text-zinc-400 block">{{ $msg->email }}</span>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="truncate max-w-sm font-semibold text-zinc-900" title="{{ $msg->subject }}">
                                        {{ $msg->subject }}
                                    </div>
                                    <span class="font-mono text-[10px] text-zinc-400">{{ $msg->reference_number }}</span>
                                </td>
                                <td class="px-4 py-3">
                                    <span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-zinc-100 text-zinc-700">
                                        {{ $msg->categoryLabel() }}
                                    </span>
                                </td>
                                <td class="px-4 py-3">
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold border {{ $msg->statusBadgeClasses() }}">
                                        {{ $msg->statusLabel() }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-zinc-500 whitespace-nowrap text-[11px]">
                                    {{ $msg->created_at->format('d M Y, H:i') }}
                                </td>
                                <td class="px-5 py-3 text-right">
                                    <a href="{{ route('website.messages.show', $msg) }}" class="px-2.5 py-1 text-[11px] font-semibold rounded bg-zinc-100 hover:bg-zinc-200 text-zinc-800 transition-colors">
                                        Buka Detail
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

</div>
@endsection
