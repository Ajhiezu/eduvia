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
                Kelola Kegiatan Ekstrakurikuler
            </h1>
            <p class="text-xs sm:text-sm text-zinc-500 mt-0.5">
                Daftar program ekstrakurikuler, pembina, jadwal, dan persyaratan yang tampil pada profil publik sekolah.
            </p>
        </div>

        <a href="{{ route('website.extracurriculars.create') }}" class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-xl text-xs font-bold bg-[#E11D2E] hover:bg-[#C71827] text-white shadow-xs transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            <span>Tambah Ekstrakurikuler</span>
        </a>
    </div>

    @if (session('success'))
        <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-medium flex items-center gap-2">
            <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    <div class="bg-white rounded-2xl border border-zinc-200 shadow-2xs overflow-hidden">
        @if ($items->isEmpty())
            <div class="p-12 text-center text-zinc-400 text-sm">
                Belum ada data ekstrakurikuler yang ditambahkan.
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-zinc-50 text-zinc-500 uppercase tracking-wider font-semibold border-b border-zinc-200">
                        <tr>
                            <th class="px-5 py-3">Nama Ekstrakurikuler</th>
                            <th class="px-4 py-3">Kategori</th>
                            <th class="px-4 py-3">Pembina</th>
                            <th class="px-4 py-3">Jadwal & Lokasi</th>
                            <th class="px-4 py-3">Status</th>
                            <th class="px-5 py-3 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-100 text-zinc-700">
                        @foreach ($items as $item)
                            <tr class="hover:bg-zinc-50/60 transition-colors">
                                <td class="px-5 py-3.5 font-bold text-zinc-900">
                                    {{ $item->name }}
                                </td>
                                <td class="px-4 py-3.5">
                                    <span class="px-2 py-0.5 rounded font-semibold text-[10px] bg-zinc-100 text-zinc-700">
                                        {{ $item->category }}
                                    </span>
                                </td>
                                <td class="px-4 py-3.5 text-zinc-600">
                                    {{ $item->coach_name ?: '-' }}
                                </td>
                                <td class="px-4 py-3.5 text-zinc-600">
                                    <div>{{ $item->schedule_info ?: '-' }}</div>
                                    @if ($item->location)
                                        <div class="text-[11px] text-zinc-400">{{ $item->location }}</div>
                                    @endif
                                </td>
                                <td class="px-4 py-3.5">
                                    @if ($item->is_published)
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Terbit
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-50 text-amber-700">
                                            <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span> Draf
                                        </span>
                                    @endif
                                </td>
                                <td class="px-5 py-3.5 text-right">
                                    <div class="flex items-center justify-end gap-1.5">
                                        <a href="{{ route('website.extracurriculars.edit', $item) }}" class="px-2.5 py-1 text-[11px] font-semibold rounded bg-zinc-100 hover:bg-zinc-200 text-zinc-800 transition-colors">
                                            Edit
                                        </a>
                                        <form method="POST" action="{{ route('website.extracurriculars.destroy', $item) }}" onsubmit="return confirm('Hapus ekstrakurikuler ini?');">
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
        @endif
    </div>

</div>
@endsection
