@extends('layouts.app')

@section('content')
<div class="space-y-6" x-data="{
    selectedIds: [],
    selectAll: false,
    toggleAll() {
        if (this.selectAll) {
            this.selectedIds = Array.from(document.querySelectorAll('.message-checkbox')).map(el => el.value);
        } else {
            this.selectedIds = [];
        }
    }
}">

    <!-- Page Header & Actions -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-zinc-200">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <a href="{{ route('website.dashboard') }}" class="text-xs font-semibold text-zinc-500 hover:text-zinc-800">&larr; Ringkasan CMS</a>
                <span class="text-zinc-300">&bull;</span>
                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider bg-rose-50 text-[#E11D2E] border border-rose-100">
                    Buku Tamu & Pesan
                </span>
            </div>
            <h1 class="text-2xl font-extrabold text-zinc-900 tracking-tight">
                Pesan Pengunjung & Pertanyaan Publik
            </h1>
            <p class="text-xs sm:text-sm text-zinc-500 mt-0.5">
                Kelola pertanyaan, informasi, kritik, dan saran yang dikirimkan pengunjung website resmi {{ $school->name }}.
            </p>
        </div>

        <div class="flex items-center gap-2.5 shrink-0">
            <a href="{{ route('school.public.contact', ['school' => $school->slug]) }}" target="_blank" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-xs font-bold border border-zinc-300 bg-white hover:bg-zinc-50 text-zinc-800 transition-colors">
                <svg class="w-3.5 h-3.5 text-zinc-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                <span>Halaman Kontak Publik</span>
            </a>
        </div>
    </div>

    <!-- Alert Notifications -->
    @if (session('success'))
        <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-semibold flex items-center justify-between">
            <div class="flex items-center gap-2">
                <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                <span>{{ session('success') }}</span>
            </div>
        </div>
    @endif
    @if (session('error'))
        <div class="p-4 rounded-xl bg-rose-50 border border-rose-200 text-[#E11D2E] text-xs font-semibold">
            {{ session('error') }}
        </div>
    @endif

    <!-- Metric Stats Cards -->
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3.5">
        <div class="bg-white rounded-2xl p-4 border border-zinc-200 shadow-2xs">
            <span class="text-[11px] font-medium text-zinc-500 block">Total Pesan</span>
            <strong class="text-2xl font-black text-zinc-900">{{ $stats['total'] }}</strong>
            <span class="text-[10px] text-zinc-400 block mt-1">Keseluruhan korespondensi</span>
        </div>
        <div class="bg-white rounded-2xl p-4 border border-rose-200 shadow-2xs bg-rose-50/20">
            <span class="text-[11px] font-bold text-[#E11D2E] block">Belum Dibaca</span>
            <strong class="text-2xl font-black text-[#E11D2E]">{{ $stats['unread'] }}</strong>
            <span class="text-[10px] text-[#E11D2E]/70 block mt-1">Perlu ditinjau</span>
        </div>
        <div class="bg-white rounded-2xl p-4 border border-amber-200 shadow-2xs">
            <span class="text-[11px] font-bold text-amber-700 block">Pesan Baru</span>
            <strong class="text-2xl font-black text-amber-700">{{ $stats['new'] }}</strong>
            <span class="text-[10px] text-amber-600/70 block mt-1">Menunggu tindak lanjut</span>
        </div>
        <div class="bg-white rounded-2xl p-4 border border-blue-200 shadow-2xs">
            <span class="text-[11px] font-bold text-blue-700 block">Ditindaklanjuti</span>
            <strong class="text-2xl font-black text-blue-700">{{ $stats['in_progress'] }}</strong>
            <span class="text-[10px] text-blue-600/70 block mt-1">Sedang diproses staf</span>
        </div>
        <div class="bg-white rounded-2xl p-4 border border-emerald-200 shadow-2xs">
            <span class="text-[11px] font-bold text-emerald-700 block">Selesai</span>
            <strong class="text-2xl font-black text-emerald-700">{{ $stats['resolved'] }}</strong>
            <span class="text-[10px] text-emerald-600/70 block mt-1">Telah dijawab/selesai</span>
        </div>
        <div class="bg-white rounded-2xl p-4 border border-zinc-200 shadow-2xs">
            <span class="text-[11px] font-medium text-zinc-500 block">Diarsipkan</span>
            <strong class="text-2xl font-black text-zinc-700">{{ $stats['archived'] }}</strong>
            <span class="text-[10px] text-zinc-400 block mt-1">Penyimpanan riwayat</span>
        </div>
    </div>

    <!-- Filter & Search Toolbar -->
    <div class="bg-white rounded-2xl p-4 sm:p-5 border border-zinc-200 shadow-2xs">
        <form method="GET" action="{{ route('website.messages.index') }}" class="space-y-3">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
                
                <!-- Search Input -->
                <div class="lg:col-span-2">
                    <label class="block text-[11px] font-bold text-zinc-500 uppercase tracking-wider mb-1">Pencarian</label>
                    <div class="relative">
                        <input
                            type="text"
                            name="search"
                            value="{{ request('search') }}"
                            placeholder="Cari nama, email, subjek, ref..."
                            class="w-full text-xs rounded-xl border border-zinc-200 pl-8 pr-3 py-2 text-zinc-900 placeholder:text-zinc-400 focus:outline-none focus:ring-2 focus:ring-[#E11D2E]/20 focus:border-[#E11D2E]"
                        >
                        <svg class="w-4 h-4 text-zinc-400 absolute left-2.5 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    </div>
                </div>

                <!-- Category Filter -->
                <div>
                    <label class="block text-[11px] font-bold text-zinc-500 uppercase tracking-wider mb-1">Kategori</label>
                    <select
                        name="category"
                        class="w-full text-xs rounded-xl border border-zinc-200 px-3 py-2 text-zinc-900 bg-white focus:outline-none focus:ring-2 focus:ring-[#E11D2E]/20 focus:border-[#E11D2E]"
                    >
                        <option value="">Semua Kategori</option>
                        @foreach (\App\Models\SchoolMessage::CATEGORIES as $key => $lbl)
                            <option value="{{ $key }}" {{ request('category') === $key ? 'selected' : '' }}>
                                {{ $lbl }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Status Filter -->
                <div>
                    <label class="block text-[11px] font-bold text-zinc-500 uppercase tracking-wider mb-1">Status</label>
                    <select
                        name="status"
                        class="w-full text-xs rounded-xl border border-zinc-200 px-3 py-2 text-zinc-900 bg-white focus:outline-none focus:ring-2 focus:ring-[#E11D2E]/20 focus:border-[#E11D2E]"
                    >
                        <option value="">Semua Status</option>
                        @foreach (\App\Models\SchoolMessage::STATUSES as $key => $lbl)
                            <option value="{{ $key }}" {{ request('status') === $key ? 'selected' : '' }}>
                                {{ $lbl }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Sort Filter -->
                <div>
                    <label class="block text-[11px] font-bold text-zinc-500 uppercase tracking-wider mb-1">Urutan</label>
                    <select
                        name="sort"
                        class="w-full text-xs rounded-xl border border-zinc-200 px-3 py-2 text-zinc-900 bg-white focus:outline-none focus:ring-2 focus:ring-[#E11D2E]/20 focus:border-[#E11D2E]"
                    >
                        <option value="newest" {{ request('sort') !== 'oldest' ? 'selected' : '' }}>Pesan Terbaru</option>
                        <option value="oldest" {{ request('sort') === 'oldest' ? 'selected' : '' }}>Pesan Terlama</option>
                    </select>
                </div>

            </div>

            <!-- Second Row: Date range & Quick toggles -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pt-2 border-t border-zinc-100">
                <div class="flex flex-wrap items-center gap-3">
                    <div class="flex items-center gap-2 text-xs text-zinc-600">
                        <span>Dari:</span>
                        <input
                            type="date"
                            name="date_from"
                            value="{{ request('date_from') }}"
                            class="text-xs rounded-lg border border-zinc-200 px-2.5 py-1 text-zinc-900 focus:outline-none focus:ring-1 focus:ring-[#E11D2E]"
                        >
                        <span>Sampai:</span>
                        <input
                            type="date"
                            name="date_to"
                            value="{{ request('date_to') }}"
                            class="text-xs rounded-lg border border-zinc-200 px-2.5 py-1 text-zinc-900 focus:outline-none focus:ring-1 focus:ring-[#E11D2E]"
                        >
                    </div>

                    <label class="inline-flex items-center gap-1.5 text-xs text-zinc-700 cursor-pointer">
                        <input
                            type="checkbox"
                            name="unread_only"
                            value="1"
                            {{ request('unread_only') ? 'checked' : '' }}
                            class="rounded border-zinc-300 text-[#E11D2E] focus:ring-[#E11D2E]"
                        >
                        <span>Hanya Belum Dibaca</span>
                    </label>
                </div>

                <div class="flex items-center gap-2">
                    @if (request()->hasAny(['search', 'category', 'status', 'unread_only', 'date_from', 'date_to', 'sort']))
                        <a href="{{ route('website.messages.index') }}" class="px-3 py-1.5 rounded-xl text-xs font-semibold text-zinc-500 hover:text-zinc-800 transition-colors">
                            Reset Filter
                        </a>
                    @endif
                    <button type="submit" class="px-4 py-1.5 rounded-xl text-xs font-bold bg-[#E11D2E] hover:bg-[#C71827] text-white shadow-xs transition-colors">
                        Terapkan Filter
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- Messages List & Bulk Action Form -->
    <div class="bg-white rounded-2xl border border-zinc-200 shadow-2xs overflow-hidden">
        
        <!-- Bulk Action Bar -->
        <form id="bulkForm" method="POST" action="{{ route('website.messages.bulk') }}" class="p-3.5 bg-zinc-50 border-b border-zinc-200 flex flex-wrap items-center justify-between gap-3">
            @csrf
            <div class="flex items-center gap-3">
                <label class="inline-flex items-center gap-2 text-xs font-semibold text-zinc-700 cursor-pointer">
                    <input
                        type="checkbox"
                        x-model="selectAll"
                        @change="toggleAll()"
                        class="rounded border-zinc-300 text-[#E11D2E] focus:ring-[#E11D2E]"
                    >
                    <span>Pilih Semua Halaman Ini</span>
                </label>
                <span class="text-xs text-zinc-400" x-show="selectedIds.length > 0" style="display: none;">
                    (<span x-text="selectedIds.length"></span> dipilih)
                </span>
            </div>

            <div class="flex items-center gap-2" x-show="selectedIds.length > 0" style="display: none;">
                <!-- Hidden inputs for selected IDs -->
                <template x-for="id in selectedIds" :key="id">
                    <input type="hidden" name="message_ids[]" :value="id">
                </template>

                <select
                    name="action"
                    required
                    class="text-xs rounded-lg border border-zinc-300 px-2.5 py-1 bg-white text-zinc-800 focus:outline-none focus:ring-1 focus:ring-[#E11D2E]"
                >
                    <option value="" disabled selected>Pilih Aksi Massal...</option>
                    <option value="mark_read">Tandai Sudah Dibaca</option>
                    <option value="mark_unread">Tandai Belum Dibaca</option>
                    <option value="mark_resolved">Tandai Selesai</option>
                    <option value="archive">Arsipkan Pesan</option>
                    <option value="delete">Hapus Pesan Terpilih</option>
                </select>

                <button
                    type="submit"
                    onclick="return confirm('Apakah Anda yakin ingin memproses aksi massal pada pesan terpilih?')"
                    class="px-3 py-1 rounded-lg text-xs font-bold bg-zinc-800 hover:bg-black text-white transition-colors"
                >
                    Jalankan
                </button>
            </div>
        </form>

        @if ($messages->isEmpty())
            <div class="py-16 px-4 text-center space-y-3">
                <div class="w-12 h-12 bg-zinc-100 rounded-full flex items-center justify-center mx-auto text-zinc-400">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/></svg>
                </div>
                <div class="space-y-1">
                    <h3 class="text-sm font-bold text-zinc-800">Tidak ada pesan yang ditemukan</h3>
                    <p class="text-xs text-zinc-500 max-w-sm mx-auto">
                        @if (request()->hasAny(['search', 'category', 'status', 'unread_only', 'date_from', 'date_to']))
                            Tidak ada pesan yang sesuai dengan kriteria filter Anda. Coba sesuaikan filter pencarian.
                        @else
                            Belum ada pesan yang masuk dari formulir kontak website sekolah.
                        @endif
                    </p>
                </div>
            </div>

        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-zinc-50 text-zinc-500 uppercase tracking-wider font-semibold border-b border-zinc-200">
                        <tr>
                            <th class="px-4 py-3 w-10"></th>
                            <th class="px-4 py-3">Pengirim</th>
                            <th class="px-4 py-3">Subjek & Referensi</th>
                            <th class="px-4 py-3">Kategori</th>
                            <th class="px-4 py-3">Status</th>
                            <th class="px-4 py-3">Tanggal</th>
                            <th class="px-5 py-3 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-100 text-zinc-700">
                        @foreach ($messages as $msg)
                            <tr class="hover:bg-zinc-50/70 transition-colors {{ ! $msg->is_read ? 'bg-rose-50/20 font-medium' : '' }}">
                                <!-- Checkbox -->
                                <td class="px-4 py-3.5">
                                    <input
                                        type="checkbox"
                                        value="{{ $msg->id }}"
                                        x-model="selectedIds"
                                        class="message-checkbox rounded border-zinc-300 text-[#E11D2E] focus:ring-[#E11D2E]"
                                    >
                                </td>

                                <!-- Sender Info -->
                                <td class="px-4 py-3.5">
                                    <div class="flex items-center gap-2">
                                        @if (! $msg->is_read)
                                            <span class="w-2 h-2 rounded-full bg-[#E11D2E] shrink-0" title="Belum dibaca"></span>
                                        @endif
                                        <div class="min-w-0">
                                            <span class="font-bold text-zinc-900 block truncate max-w-xs">
                                                {{ $msg->name }}
                                            </span>
                                            <span class="text-[11px] text-zinc-500 block truncate">
                                                {{ $msg->email }}
                                            </span>
                                            @if ($msg->phone)
                                                <span class="text-[10px] text-zinc-400 block">
                                                    {{ $msg->phone }}
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                </td>

                                <!-- Subject & Reference -->
                                <td class="px-4 py-3.5">
                                    <a href="{{ route('website.messages.show', $msg) }}" class="font-bold text-zinc-900 hover:text-[#E11D2E] transition-colors block truncate max-w-sm" title="{{ $msg->subject }}">
                                        {{ $msg->subject }}
                                    </a>
                                    <div class="flex items-center gap-2 mt-0.5">
                                        <span class="font-mono text-[10px] text-zinc-400">
                                            {{ $msg->reference_number }}
                                        </span>
                                        @if ($msg->internal_notes)
                                            <span class="inline-flex items-center gap-1 text-[10px] text-amber-700 bg-amber-50 px-1.5 py-0.2 rounded border border-amber-200">
                                                <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"/></svg>
                                                <span>Ada catatan</span>
                                            </span>
                                        @endif
                                    </div>
                                </td>

                                <!-- Category -->
                                <td class="px-4 py-3.5">
                                    <span class="px-2 py-0.5 rounded-md font-semibold text-[11px] bg-zinc-100 text-zinc-700">
                                        {{ $msg->categoryLabel() }}
                                    </span>
                                </td>

                                <!-- Status -->
                                <td class="px-4 py-3.5">
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold border {{ $msg->statusBadgeClasses() }}">
                                        {{ $msg->statusLabel() }}
                                    </span>
                                </td>

                                <!-- Received Date -->
                                <td class="px-4 py-3.5 text-zinc-500 whitespace-nowrap text-[11px]">
                                    <div>{{ $msg->created_at->format('d M Y') }}</div>
                                    <div class="text-[10px] text-zinc-400">{{ $msg->created_at->format('H:i') }} WIB</div>
                                </td>

                                <!-- Actions -->
                                <td class="px-5 py-3.5 text-right whitespace-nowrap">
                                    <div class="flex items-center justify-end gap-1.5">
                                        <a
                                            href="{{ route('website.messages.show', $msg) }}"
                                            class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-[11px] font-bold bg-zinc-100 hover:bg-zinc-200 text-zinc-800 transition-colors"
                                        >
                                            <span>Buka</span>
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                        </a>

                                        <form method="POST" action="{{ route('website.messages.destroy', $msg) }}" onsubmit="return confirm('Apakah Anda yakin ingin menghapus pesan ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button
                                                type="submit"
                                                class="p-1.5 rounded-lg text-zinc-400 hover:text-[#E11D2E] hover:bg-rose-50 transition-colors"
                                                title="Hapus pesan"
                                            >
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Pagination Footer -->
            <div class="p-4 border-t border-zinc-200 bg-zinc-50/50">
                {{ $messages->links() }}
            </div>
        @endif

    </div>

</div>
@endsection
