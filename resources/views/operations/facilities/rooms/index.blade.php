<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-zinc-900">Laboratorium & Fasilitas Sekolah</h1>
                <p class="text-sm text-zinc-500 mt-1">Daftar ruangan khusus, laboratorium IPA/Komputer/Bahasa, bengkel kejuruan, dan sarana umum.</p>
            </div>
            <div class="flex items-center gap-2">
                <x-button href="{{ route('operations.facilities.reservations.index') }}" variant="secondary" size="sm">
                    Jadwal Reservasi
                </x-button>
                <x-button href="{{ route('operations.facilities.rooms.create') }}" variant="primary" size="sm">
                    + Daftarkan Ruangan / Lab
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

        <!-- Filter bar -->
        <div class="bg-white p-4 rounded-2xl border border-zinc-200 shadow-xs">
            <form method="GET" action="{{ route('operations.facilities.rooms.index') }}" class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                <div class="sm:col-span-2">
                    <x-input type="text" name="search" :value="$search" placeholder="Cari nama fasilitas, kode, lokasi gedung..." />
                </div>
                <div class="flex gap-2">
                    <select name="type" class="w-full text-sm rounded-xl border-zinc-300 focus:border-[#E11D2E] focus:ring-[#E11D2E]">
                        <option value="">Semua Jenis Fasilitas</option>
                        @foreach($roomTypes as $k => $label)
                            <option value="{{ $k }}" {{ $type === $k ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                    <x-button type="submit" variant="secondary" size="md">Filter</x-button>
                </div>
            </form>
        </div>

        <!-- Rooms Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            @forelse($rooms as $room)
                <div class="bg-white rounded-2xl border border-zinc-200/80 shadow-xs p-5 flex flex-col justify-between hover:border-emerald-300 transition-all">
                    <div>
                        <div class="flex items-center justify-between">
                            <span class="px-2.5 py-1 rounded-lg text-xs font-bold font-mono bg-zinc-100 text-zinc-800">
                                {{ $room->code }}
                            </span>
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold {{ $room->is_active ? 'bg-emerald-50 text-emerald-700' : 'bg-zinc-100 text-zinc-500' }}">
                                {{ $room->is_active ? 'Aktif' : 'Nonaktif' }}
                            </span>
                        </div>

                        <h3 class="text-base font-bold text-zinc-900 mt-3">{{ $room->name }}</h3>
                        <p class="text-xs text-zinc-500 mt-0.5">{{ $room->typeLabel() }} &bull; {{ $room->location ?? 'Gedung Sekolah' }}</p>

                        <div class="mt-4 pt-3 border-t border-zinc-100 space-y-1.5 text-xs text-zinc-600">
                            <div class="flex justify-between">
                                <span class="text-zinc-400">Kapasitas Maks:</span>
                                <span class="font-semibold text-zinc-800">{{ $room->capacity }} Peserta</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-zinc-400">Penanggung Jawab:</span>
                                <span class="font-medium text-zinc-800">{{ $room->manager_name ?? '-' }}</span>
                            </div>
                        </div>

                        @if($room->facilities_description)
                            <p class="mt-3 text-xs text-zinc-500 bg-zinc-50 p-2.5 rounded-xl line-clamp-2">{{ $room->facilities_description }}</p>
                        @endif
                    </div>

                    <div class="mt-5 pt-3 border-t border-zinc-100 flex items-center justify-between">
                        <x-button href="{{ route('operations.facilities.reservations.create', ['room_id' => $room->id]) }}" variant="secondary" size="xs">
                            Pinjam Ruangan
                        </x-button>
                        <a href="{{ route('operations.facilities.rooms.show', $room) }}" class="text-xs font-bold text-emerald-700 hover:underline">
                            Rincian Jadwal &rarr;
                        </a>
                    </div>
                </div>
            @empty
                <div class="col-span-full bg-white rounded-2xl p-12 text-center text-zinc-400 border border-zinc-200">
                    <p class="text-sm font-medium">Belum ada fasilitas atau ruangan terdaftar.</p>
                </div>
            @endforelse
        </div>

        <div class="mt-4">
            {{ $rooms->links() }}
        </div>
    </div>
</x-app-layout>
