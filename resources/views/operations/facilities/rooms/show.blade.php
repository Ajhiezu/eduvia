<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div class="flex items-center gap-3">
                <x-button href="{{ route('operations.facilities.rooms.index') }}" variant="ghost" size="sm">
                    &larr; Fasilitas
                </x-button>
                <div>
                    <h1 class="text-xl font-bold tracking-tight text-zinc-900">{{ $room->name }}</h1>
                    <p class="text-xs text-zinc-500 font-mono">Kode: {{ $room->code }} &bull; {{ $room->typeLabel() }} &bull; {{ $room->location }}</p>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <x-button href="{{ route('operations.facilities.reservations.create', ['room_id' => $room->id]) }}" variant="primary" size="sm">
                    + Ajukan Peminjaman
                </x-button>
                <x-button href="{{ route('operations.facilities.rooms.edit', $room) }}" variant="secondary" size="sm">
                    Edit Fasilitas
                </x-button>
            </div>
        </div>
    </x-slot>

    <div class="py-6 space-y-6">
        @if (session('status'))
            <x-alert type="success" :message="session('status')" dismissible />
        @endif
        @if (session('error'))
            <x-alert type="error" :message="session('error')" dismissible />
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Left Info Panel -->
            <div class="bg-white rounded-2xl border border-zinc-200/80 shadow-xs p-6 space-y-4">
                <h3 class="text-sm font-bold text-zinc-900 pb-2 border-b border-zinc-100">Spesifikasi Fasilitas</h3>

                <div class="space-y-2 text-xs">
                    <div class="flex justify-between py-1 border-b border-zinc-50">
                        <span class="text-zinc-500">Jenis Fasilitas</span>
                        <span class="font-semibold text-zinc-900">{{ $room->typeLabel() }}</span>
                    </div>
                    <div class="flex justify-between py-1 border-b border-zinc-50">
                        <span class="text-zinc-500">Kapasitas Maksimal</span>
                        <span class="font-semibold text-zinc-900">{{ $room->capacity }} Peserta</span>
                    </div>
                    <div class="flex justify-between py-1 border-b border-zinc-50">
                        <span class="text-zinc-500">Lokasi Gedung</span>
                        <span class="font-semibold text-zinc-900">{{ $room->location ?? '-' }}</span>
                    </div>
                    <div class="flex justify-between py-1 border-b border-zinc-50">
                        <span class="text-zinc-500">Penanggung Jawab</span>
                        <span class="font-semibold text-zinc-900">{{ $room->manager_name ?? '-' }}</span>
                    </div>
                    <div class="flex justify-between py-1">
                        <span class="text-zinc-500">Status Operasional</span>
                        <span class="font-bold {{ $room->is_active ? 'text-emerald-600' : 'text-zinc-400' }}">
                            {{ $room->is_active ? 'Aktif Digunakan' : 'Nonaktif' }}
                        </span>
                    </div>
                </div>

                @if($room->facilities_description)
                    <div class="border-t border-zinc-100 pt-3">
                        <span class="text-[11px] font-bold text-zinc-500 uppercase tracking-wider block mb-1">Daftar Peralatan & Sarana</span>
                        <p class="text-xs text-zinc-600 leading-relaxed whitespace-pre-line">{{ $room->facilities_description }}</p>
                    </div>
                @endif
            </div>

            <!-- Right Schedule & Incidents -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Upcoming Reservations Schedule -->
                <div class="bg-white rounded-2xl border border-zinc-200/80 shadow-xs overflow-hidden">
                    <div class="p-4 border-b border-zinc-100 flex items-center justify-between">
                        <h3 class="text-sm font-bold text-zinc-900">Jadwal Pemakaian Ruangan Mendatang ({{ $upcomingReservations->count() }})</h3>
                    </div>

                    <div class="divide-y divide-zinc-100">
                        @forelse($upcomingReservations as $res)
                            <div class="p-4 flex items-center justify-between text-xs">
                                <div>
                                    <h4 class="font-bold text-zinc-900">{{ $res->purpose }}</h4>
                                    <p class="text-zinc-500 mt-0.5">Pemohon: {{ $res->user->name }} &bull; Estimasi: {{ $res->expected_participants }} Peserta</p>
                                </div>
                                <div class="text-right">
                                    <span class="font-semibold text-zinc-900 block">{{ $res->reservation_date->format('d/m/Y') }}</span>
                                    <span class="text-emerald-700 font-medium text-[11px]">{{ substr($res->start_time, 0, 5) }} - {{ substr($res->end_time, 0, 5) }} WIB</span>
                                </div>
                            </div>
                        @empty
                            <div class="p-8 text-center text-zinc-400 text-xs">Belum ada jadwal pemakaian mendatang untuk ruangan ini.</div>
                        @endforelse
                    </div>
                </div>

                <!-- Incidents and Maintenance -->
                <div class="bg-white rounded-2xl border border-zinc-200/80 shadow-xs overflow-hidden">
                    <div class="p-4 border-b border-zinc-100 flex items-center justify-between">
                        <h3 class="text-sm font-bold text-zinc-900">Laporan Kerusakan & Pemeliharaan</h3>
                        <x-button href="{{ route('operations.facilities.incidents.index') }}" variant="secondary" size="xs">
                            Semua Laporan
                        </x-button>
                    </div>

                    <div class="divide-y divide-zinc-100">
                        @forelse($incidents as $inc)
                            <div class="p-4 flex items-center justify-between text-xs">
                                <div>
                                    <h4 class="font-bold text-zinc-900">{{ $inc->title }}</h4>
                                    <p class="text-zinc-500 mt-0.5">{{ $inc->description }}</p>
                                    <span class="text-[11px] text-zinc-400">Pelapor: {{ $inc->reporter->name ?? '-' }} &bull; {{ $inc->created_at->format('d/m/Y H:i') }}</span>
                                </div>
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-bold {{ $inc->statusVariant() === 'success' ? 'bg-emerald-50 text-emerald-700' : 'bg-amber-50 text-amber-700' }}">
                                    {{ $inc->statusLabel() }}
                                </span>
                            </div>
                        @empty
                            <div class="p-6 text-center text-zinc-400 text-xs">Tidak ada catatan kerusakan fasilitas.</div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
