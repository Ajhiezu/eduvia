<x-layouts.app>
    <x-slot:headerTitle>
        Presensi Saya
    </x-slot:headerTitle>

    <div class="space-y-6">
        <div>
            <h1 class="text-xl sm:text-2xl font-extrabold text-zinc-900 tracking-tight">
                Riwayat & Rekapitulasi Presensi Saya
            </h1>
            <p class="text-xs text-zinc-500 mt-0.5">
                Pantau catatan kehadiran belajar Anda pada seluruh mata pelajaran dan sesi tatap muka.
            </p>
        </div>

        <!-- 6-Card Metis Student Metric Strip -->
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3">
            <x-card padding class="border-zinc-200">
                <div class="text-[10px] font-bold uppercase tracking-wider text-emerald-600">Hadir</div>
                <div class="mt-1 text-2xl font-extrabold text-emerald-700">{{ $present }}</div>
                <div class="text-[10px] text-zinc-400">Tepat waktu</div>
            </x-card>

            <x-card padding class="border-zinc-200">
                <div class="text-[10px] font-bold uppercase tracking-wider text-amber-600">Terlambat</div>
                <div class="mt-1 text-2xl font-extrabold text-amber-700">{{ $late }}</div>
                <div class="text-[10px] text-zinc-400">Masuk terlambat</div>
            </x-card>

            <x-card padding class="border-zinc-200">
                <div class="text-[10px] font-bold uppercase tracking-wider text-blue-600">Izin</div>
                <div class="mt-1 text-2xl font-extrabold text-blue-700">{{ $permit }}</div>
                <div class="text-[10px] text-zinc-400">Keterangan izin</div>
            </x-card>

            <x-card padding class="border-zinc-200">
                <div class="text-[10px] font-bold uppercase tracking-wider text-purple-600">Sakit</div>
                <div class="mt-1 text-2xl font-extrabold text-purple-700">{{ $sick }}</div>
                <div class="text-[10px] text-zinc-400">Surat dokter</div>
            </x-card>

            <x-card padding class="border-zinc-200">
                <div class="text-[10px] font-bold uppercase tracking-wider text-rose-600">Alpa</div>
                <div class="mt-1 text-2xl font-extrabold text-rose-700">{{ $absent }}</div>
                <div class="text-[10px] text-zinc-400">Tanpa keterangan</div>
            </x-card>

            <x-card padding class="border-zinc-200">
                <div class="text-[10px] font-bold uppercase tracking-wider text-brand-600">Kehadiran</div>
                <div class="mt-1 text-2xl font-extrabold text-brand-700">{{ $rate }}%</div>
                <div class="text-[10px] text-zinc-400">{{ $present + $late }}/{{ $total }} sesi</div>
            </x-card>
        </div>

        <!-- Student Attendance Records Table -->
        <x-card :padding="false">
            @if ($records->isEmpty())
                <div class="p-8 text-center text-zinc-400 text-xs">
                    Belum ada catatan presensi pembelajaran untuk akun Anda.
                </div>
            @else
                <div class="overflow-x-auto" data-scrollable>
                    <table class="w-full text-left text-xs text-zinc-600 min-w-[640px]">
                        <thead class="bg-zinc-50/80 text-zinc-500 font-semibold border-b border-zinc-200">
                            <tr>
                                <th class="py-3 px-4">Tanggal Pertemuan</th>
                                <th class="py-3 px-4">Mata Pelajaran</th>
                                <th class="py-3 px-4">Pertemuan</th>
                                <th class="py-3 px-4">Guru Pengampu</th>
                                <th class="py-3 px-4">Status Kehadiran</th>
                                <th class="py-3 px-4">Keterangan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-200">
                            @foreach ($records as $item)
                                <tr class="hover:bg-zinc-50/50 transition-colors">
                                    <td class="py-3 px-4 text-zinc-800 font-medium">
                                        {{ $item->session?->date ? $item->session->date->translatedFormat('d M Y') : '-' }}
                                    </td>
                                    <td class="py-3 px-4 font-bold text-zinc-900">
                                        {{ $item->session?->teachingAssignment?->subject?->name ?? 'Mata Pelajaran' }}
                                    </td>
                                    <td class="py-3 px-4 text-zinc-600">
                                        Pertemuan Ke-{{ $item->session?->meeting_number }}
                                    </td>
                                    <td class="py-3 px-4 text-zinc-600">
                                        {{ $item->session?->teacher?->name ?? '-' }}
                                    </td>
                                    <td class="py-3 px-4">
                                        <x-badge :variant="$item->statusBadgeVariant()" size="sm">
                                            {{ $item->statusLabel() }}
                                        </x-badge>
                                    </td>
                                    <td class="py-3 px-4 text-zinc-500 italic">
                                        {{ $item->notes ?: '-' }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @if ($records->hasPages())
                    <div class="p-4 border-t border-zinc-200">
                        {{ $records->links() }}
                    </div>
                @endif
            @endif
        </x-card>
    </div>
</x-layouts.app>
