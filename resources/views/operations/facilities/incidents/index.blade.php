<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div class="flex items-center gap-3">
                <x-button href="{{ route('operations.facilities.rooms.index') }}" variant="ghost" size="sm">
                    &larr; Fasilitas
                </x-button>
                <div>
                    <h1 class="text-2xl font-bold tracking-tight text-zinc-900">Laporan Kerusakan & Insiden Fasilitas</h1>
                    <p class="text-sm text-zinc-500 mt-1">Pencatatan insiden sarana prasarana, AC rusak, peralatan lab bermasalah, dan riwayat penanganan servis.</p>
                </div>
            </div>
        </div>
    </x-slot>

    <div class="py-6 space-y-6">
        @if (session('status'))
            <x-alert type="success" :message="session('status')" dismissible />
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Report Form -->
            <div class="bg-white rounded-2xl border border-zinc-200/80 shadow-xs p-6 h-fit">
                <h3 class="text-sm font-bold text-zinc-900 mb-1">Laporkan Kerusakan Fasilitas</h3>
                <p class="text-xs text-zinc-500 mb-4">Sampaikan kendala teknis agar dapat segera diperbaiki oleh tim sarpras sekolah.</p>

                <form action="{{ route('operations.facilities.incidents.store') }}" method="POST" class="space-y-4">
                    @csrf

                    <div>
                        <label class="block text-xs font-semibold text-zinc-700 mb-1">Ruangan / Laboratorium <span class="text-rose-500">*</span></label>
                        <select name="school_room_id" required class="w-full text-xs rounded-xl border-zinc-300">
                            @foreach($rooms as $r)
                                <option value="{{ $r->id }}">{{ $r->name }} ({{ $r->code }})</option>
                            @endforeach
                        </select>
                    </div>

                    <x-input name="title" label="Judul Masalah" placeholder="Contoh: AC Bocor Air / Proyektor Mati Total" required />

                    <div>
                        <label class="block text-xs font-semibold text-zinc-700 mb-1">Tingkat Prioritas <span class="text-rose-500">*</span></label>
                        <select name="priority" required class="w-full text-xs rounded-xl border-zinc-300">
                            <option value="low">Rendah (Dapat ditunda)</option>
                            <option value="medium" selected>Sedang (Perlu servis minggu ini)</option>
                            <option value="high">Tinggi (Mengganggu kegiatan KBM)</option>
                            <option value="urgent">Darurat (Berbahaya / Kritis)</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-zinc-700 mb-1">Deskripsi Kerusakan <span class="text-rose-500">*</span></label>
                        <textarea name="description" rows="3" required class="w-full text-xs rounded-xl border-zinc-300" placeholder="Jelaskan detail kendala, unit PC nomor berapa, atau indikasi awal..."></textarea>
                    </div>

                    <x-button type="submit" variant="primary" size="md" class="w-full justify-center">
                        Kirim Laporan
                    </x-button>
                </form>
            </div>

            <!-- Incidents List -->
            <div class="lg:col-span-2 space-y-4">
                <div class="bg-white rounded-2xl border border-zinc-200/80 shadow-xs overflow-hidden">
                    <div class="p-4 border-b border-zinc-100">
                        <h3 class="text-sm font-bold text-zinc-900">Daftar Laporan Penanganan Sarpras</h3>
                    </div>

                    <div class="divide-y divide-zinc-100">
                        @forelse($incidents as $inc)
                            <div class="p-4 space-y-2 text-xs">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-2">
                                        <span class="font-bold text-sm text-zinc-900">{{ $inc->title }}</span>
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold {{ $inc->priorityVariant() === 'danger' ? 'bg-rose-50 text-rose-700' : 'bg-amber-50 text-amber-700' }}">
                                            Prioritas: {{ $inc->priorityLabel() }}
                                        </span>
                                    </div>
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-bold {{ $inc->statusVariant() === 'success' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-zinc-100 text-zinc-600' }}">
                                        {{ $inc->statusLabel() }}
                                    </span>
                                </div>

                                <p class="text-zinc-600">{{ $inc->description }}</p>
                                <div class="text-[11px] text-zinc-400">
                                    Ruangan: <strong>{{ $inc->room->name }}</strong> &bull; Dilaporkan oleh: {{ $inc->reporter->name ?? '-' }} &bull; {{ $inc->created_at->format('d/m/Y H:i') }}
                                </div>

                                @if($inc->resolution_notes)
                                    <div class="mt-2 p-2.5 bg-emerald-50 rounded-xl text-emerald-800 text-[11px]">
                                        <strong>Catatan Penanganan:</strong> {{ $inc->resolution_notes }}
                                    </div>
                                @endif

                                @if($inc->status !== 'resolved' && auth()->user()->hasRole(['super_admin', 'school_admin', 'staff']))
                                    <form action="{{ route('operations.facilities.incidents.update', $inc) }}" method="POST" class="pt-2 flex items-center gap-2">
                                        @csrf
                                        @method('PUT')
                                        <input type="text" name="resolution_notes" placeholder="Tuliskan tindakan perbaikan..." class="text-xs py-1 px-2.5 rounded-xl border-zinc-200 flex-1">
                                        <select name="status" class="text-xs py-1 px-2.5 rounded-xl border-zinc-200">
                                            <option value="in_progress">Dalam Proses</option>
                                            <option value="resolved">Terselesaikan</option>
                                        </select>
                                        <x-button type="submit" variant="secondary" size="xs">Simpan Status</x-button>
                                    </form>
                                @endif
                            </div>
                        @empty
                            <div class="p-8 text-center text-zinc-400 text-xs">Belum ada laporan kerusakan atau insiden tercatat.</div>
                        @endforelse
                    </div>
                </div>

                <div class="mt-4">
                    {{ $incidents->links() }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
