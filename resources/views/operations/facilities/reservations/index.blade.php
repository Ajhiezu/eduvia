<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-zinc-900">Jadwal & Reservasi Fasilitas</h1>
                <p class="text-sm text-zinc-500 mt-1">Kelola permohonan peminjaman laboratorium, bengkel praktik, aula, dan ruang rapat.</p>
            </div>
            <div class="flex items-center gap-2">
                <x-button href="{{ route('operations.facilities.rooms.index') }}" variant="secondary" size="sm">
                    Daftar Ruangan
                </x-button>
                <x-button href="{{ route('operations.facilities.reservations.create') }}" variant="primary" size="sm">
                    + Ajukan Peminjaman
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

        <!-- Filter Bar -->
        <div class="bg-white p-4 rounded-2xl border border-zinc-200 shadow-xs">
            <form method="GET" action="{{ route('operations.facilities.reservations.index') }}" class="grid grid-cols-1 sm:grid-cols-4 gap-3">
                <div>
                    <select name="room_id" class="w-full text-sm rounded-xl border-zinc-300 focus:border-[#E11D2E] focus:ring-[#E11D2E]">
                        <option value="">Semua Ruangan / Lab</option>
                        @foreach($rooms as $r)
                            <option value="{{ $r->id }}" {{ $roomId == $r->id ? 'selected' : '' }}>{{ $r->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <select name="status" class="w-full text-sm rounded-xl border-zinc-300 focus:border-[#E11D2E] focus:ring-[#E11D2E]">
                        <option value="">Semua Status</option>
                        <option value="pending" {{ $status === 'pending' ? 'selected' : '' }}>Menunggu Persetujuan</option>
                        <option value="approved" {{ $status === 'approved' ? 'selected' : '' }}>Disetujui</option>
                        <option value="rejected" {{ $status === 'rejected' ? 'selected' : '' }}>Ditolak</option>
                        <option value="cancelled" {{ $status === 'cancelled' ? 'selected' : '' }}>Dibatalkan</option>
                    </select>
                </div>
                <div>
                    <input type="date" name="date" value="{{ $date }}" class="w-full text-sm rounded-xl border-zinc-300 focus:border-[#E11D2E] focus:ring-[#E11D2E]">
                </div>
                <div>
                    <x-button type="submit" variant="secondary" size="md" class="w-full justify-center">Filter</x-button>
                </div>
            </form>
        </div>

        <!-- Reservations Table -->
        <div class="bg-white rounded-2xl border border-zinc-200/80 shadow-xs overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-zinc-50 border-b border-zinc-200 text-zinc-500 uppercase tracking-wider font-semibold">
                        <tr>
                            <th class="px-4 py-3">Ruangan & Lokasi</th>
                            <th class="px-4 py-3">Tanggal & Waktu</th>
                            <th class="px-4 py-3">Keperluan / Kegiatan</th>
                            <th class="px-4 py-3">Pemohon</th>
                            <th class="px-4 py-3">Status</th>
                            <th class="px-4 py-3 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-100">
                        @forelse($reservations as $res)
                            <tr class="hover:bg-zinc-50/50">
                                <td class="px-4 py-3">
                                    <div class="font-bold text-zinc-900">{{ $res->room->name }}</div>
                                    <div class="text-[11px] font-mono text-zinc-500">{{ $res->room->code }} &bull; {{ $res->room->location }}</div>
                                </td>
                                <td class="px-4 py-3 font-medium">
                                    <div class="text-zinc-900 font-semibold">{{ $res->reservation_date->format('d/m/Y') }}</div>
                                    <div class="text-emerald-700 font-mono text-[11px]">{{ substr($res->start_time, 0, 5) }} - {{ substr($res->end_time, 0, 5) }} WIB</div>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="font-semibold text-zinc-800">{{ $res->purpose }}</div>
                                    <div class="text-zinc-500 text-[11px]">Kapasitas: {{ $res->expected_participants }} Peserta</div>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="font-semibold text-zinc-900">{{ $res->user->name }}</div>
                                    <div class="text-[11px] text-zinc-400 capitalize">{{ $res->user->role }}</div>
                                </td>
                                <td class="px-4 py-3">
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-bold {{ $res->statusVariant() === 'success' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : ($res->statusVariant() === 'warning' ? 'bg-amber-50 text-amber-700 border border-amber-200' : ($res->statusVariant() === 'danger' ? 'bg-rose-50 text-rose-700 border border-rose-200' : 'bg-zinc-100 text-zinc-600')) }}">
                                        {{ $res->statusLabel() }}
                                    </span>
                                    @if($res->rejection_reason)
                                        <p class="text-[10px] text-rose-500 mt-0.5 line-clamp-1">Alasan: {{ $res->rejection_reason }}</p>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <div class="flex items-center justify-end gap-1.5">
                                        @if($res->status === 'pending' && auth()->user()->hasRole(['super_admin', 'school_admin', 'staff']))
                                            <form action="{{ route('operations.facilities.reservations.approve', $res) }}" method="POST">
                                                @csrf
                                                <x-button type="submit" variant="primary" size="xs">Setujui</x-button>
                                            </form>

                                            <!-- Simple Reject Form -->
                                            <form action="{{ route('operations.facilities.reservations.reject', $res) }}" method="POST" onsubmit="return confirm('Tolak permohonan reservasi ini?')">
                                                @csrf
                                                <input type="hidden" name="rejection_reason" value="Jadwal ruangan bertabrakan atau ruangan sedang dalam pemeliharaan berkala.">
                                                <x-button type="submit" variant="secondary" size="xs">Tolak</x-button>
                                            </form>
                                        @elseif($res->status === 'approved')
                                            <form action="{{ route('operations.facilities.reservations.cancel', $res) }}" method="POST" onsubmit="return confirm('Batalkan jadwal peminjaman ini?')">
                                                @csrf
                                                <x-button type="submit" variant="ghost" size="xs" class="text-zinc-500 hover:text-rose-600">Batalkan</x-button>
                                            </form>
                                        @else
                                            <span class="text-zinc-400 text-[11px]">-</span>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 py-8 text-center text-zinc-400">Belum ada permohonan jadwal reservasi fasilitas.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mt-4">
            {{ $reservations->links() }}
        </div>
    </div>
</x-app-layout>
