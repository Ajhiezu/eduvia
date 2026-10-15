<x-layouts.app>
    <x-slot:headerTitle>
        Presensi Kehadiran Anak
    </x-slot:headerTitle>

    <div class="space-y-6">

        <!-- Top Header & Child Switcher -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-4 border-b border-zinc-200">
            <div>
                <div class="flex items-center gap-2">
                    <a href="{{ route('parent.dashboard') }}" class="text-xs text-zinc-500 hover:text-zinc-900">&larr; Beranda Pantauan</a>
                    <span class="text-zinc-300">/</span>
                    <span class="text-xs font-bold text-zinc-800">Presensi</span>
                </div>
                <h1 class="text-xl sm:text-2xl font-extrabold text-zinc-900 mt-1">
                    Presensi Kehadiran Belajar
                </h1>
                <p class="text-xs text-zinc-500 mt-0.5">
                    Memantau catatan hadir, sakit, izin, terlambat, dan alpa untuk <strong class="text-zinc-800">{{ $activeChild?->name ?? 'Anak' }}</strong>
                </p>
            </div>

            @if ($activeChildren->count() > 1)
                <div class="flex items-center gap-1.5 bg-zinc-100 p-1 rounded-xl">
                    @foreach ($activeChildren as $child)
                        <a
                            href="{{ route('parent.dashboard', ['child_id' => $child->id]) }}"
                            class="px-3 py-1.5 rounded-lg text-xs font-bold transition-all {{ $activeChild && $activeChild->id === $child->id ? 'bg-[#E11D2E] text-white shadow-xs' : 'text-zinc-600 hover:text-zinc-900' }}"
                        >
                            {{ $child->name }}
                        </a>
                    @endforeach
                </div>
            @endif
        </div>

        @if (! $activeChild)
            <div class="p-8 text-center bg-white rounded-2xl border border-zinc-200">
                <p class="text-sm text-zinc-500">Belum ada akun anak yang terhubung. <a href="{{ route('parent.children.index') }}" class="text-brand-600 font-bold">Tautkan akun anak</a></p>
            </div>
        @else
            <!-- Summary Stats Grid -->
            @if ($summary)
                <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3">
                    <div class="bg-white p-4 rounded-xl border border-zinc-200 shadow-2xs">
                        <span class="text-[10px] font-bold text-zinc-400 uppercase">Tingkat Hadir</span>
                        <div class="text-xl font-extrabold text-emerald-600 mt-1">{{ $summary['percentage'] }}%</div>
                    </div>
                    <div class="bg-white p-4 rounded-xl border border-zinc-200 shadow-2xs">
                        <span class="text-[10px] font-bold text-zinc-400 uppercase">Total Sesi</span>
                        <div class="text-xl font-extrabold text-zinc-800 mt-1">{{ $summary['total'] }}</div>
                    </div>
                    <div class="bg-white p-4 rounded-xl border border-zinc-200 shadow-2xs">
                        <span class="text-[10px] font-bold text-zinc-400 uppercase">Hadir Tepat</span>
                        <div class="text-xl font-extrabold text-emerald-600 mt-1">{{ $summary['present'] }}</div>
                    </div>
                    <div class="bg-white p-4 rounded-xl border border-zinc-200 shadow-2xs">
                        <span class="text-[10px] font-bold text-zinc-400 uppercase">Terlambat</span>
                        <div class="text-xl font-extrabold text-amber-600 mt-1">{{ $summary['late'] }}</div>
                    </div>
                    <div class="bg-white p-4 rounded-xl border border-zinc-200 shadow-2xs">
                        <span class="text-[10px] font-bold text-zinc-400 uppercase">Sakit & Izin</span>
                        <div class="text-xl font-extrabold text-blue-600 mt-1">{{ $summary['sick'] + $summary['permit'] }}</div>
                    </div>
                    <div class="bg-white p-4 rounded-xl border border-zinc-200 shadow-2xs">
                        <span class="text-[10px] font-bold text-zinc-400 uppercase">Alpa / Tanpa Ket</span>
                        <div class="text-xl font-extrabold text-rose-600 mt-1">{{ $summary['absent'] }}</div>
                    </div>
                </div>
            @endif

            <!-- Filter Controls -->
            <form action="{{ route('parent.attendance') }}" method="GET" class="bg-white p-4 rounded-xl border border-zinc-200/80 shadow-xs flex flex-wrap items-center gap-3">
                <div class="w-full sm:w-auto">
                    <select name="status" class="w-full text-xs font-semibold rounded-lg border-zinc-300 focus:border-[#E11D2E] focus:ring-[#E11D2E]">
                        <option value="">Semua Status Presensi</option>
                        <option value="present" {{ request('status') === 'present' ? 'selected' : '' }}>Hadir</option>
                        <option value="late" {{ request('status') === 'late' ? 'selected' : '' }}>Terlambat</option>
                        <option value="sick" {{ request('status') === 'sick' ? 'selected' : '' }}>Sakit</option>
                        <option value="permit" {{ request('status') === 'permit' ? 'selected' : '' }}>Izin</option>
                        <option value="absent" {{ request('status') === 'absent' ? 'selected' : '' }}>Alpa</option>
                    </select>
                </div>
                <div class="flex items-center gap-2">
                    <input type="date" name="start_date" value="{{ request('start_date') }}" class="text-xs rounded-lg border-zinc-300 focus:border-[#E11D2E] focus:ring-[#E11D2E]">
                    <span class="text-xs text-zinc-400">s/d</span>
                    <input type="date" name="end_date" value="{{ request('end_date') }}" class="text-xs rounded-lg border-zinc-300 focus:border-[#E11D2E] focus:ring-[#E11D2E]">
                </div>
                <button type="submit" class="px-3.5 py-2 text-xs font-bold rounded-lg bg-zinc-800 text-white hover:bg-zinc-900 transition-colors">
                    Saring
                </button>
                @if (request()->hasAny(['status', 'start_date', 'end_date']))
                    <a href="{{ route('parent.attendance') }}" class="text-xs font-semibold text-zinc-500 hover:text-zinc-800">Reset</a>
                @endif
            </form>

            <!-- Attendance Records Table -->
            <div class="bg-white rounded-2xl border border-zinc-200 shadow-xs overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs text-zinc-700">
                        <thead class="bg-zinc-50 border-b border-zinc-200 text-zinc-500 uppercase tracking-wider font-bold">
                            <tr>
                                <th class="px-4 py-3">Tanggal & Waktu</th>
                                <th class="px-4 py-3">Mata Pelajaran</th>
                                <th class="px-4 py-3">Guru Pengajar</th>
                                <th class="px-4 py-3">Status</th>
                                <th class="px-4 py-3">Catatan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-100">
                            @forelse ($records as $rec)
                                <tr class="hover:bg-zinc-50/60 transition-colors">
                                    <td class="px-4 py-3.5 whitespace-nowrap">
                                        <div class="font-bold text-zinc-900">
                                            {{ $rec->session?->date ? \Carbon\Carbon::parse($rec->session->date)->format('d M Y') : '-' }}
                                        </div>
                                        <div class="text-[11px] text-zinc-400">
                                            {{ $rec->session?->start_time ? substr($rec->session->start_time, 0, 5) : '' }}
                                        </div>
                                    </td>
                                    <td class="px-4 py-3.5">
                                        <span class="font-semibold text-zinc-900">
                                            {{ $rec->session?->teachingAssignment?->subject?->name ?? 'Mata Pelajaran' }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3.5 text-zinc-600">
                                        {{ $rec->session?->teacher?->name ?? '-' }}
                                    </td>
                                    <td class="px-4 py-3.5 whitespace-nowrap">
                                        @php
                                            $badgeClass = match($rec->status) {
                                                'present' => 'bg-emerald-50 text-emerald-800 border-emerald-200',
                                                'late' => 'bg-amber-50 text-amber-800 border-amber-200',
                                                'sick', 'permit' => 'bg-blue-50 text-blue-800 border-blue-200',
                                                'absent' => 'bg-rose-50 text-rose-800 border-rose-200',
                                                default => 'bg-zinc-100 text-zinc-700 border-zinc-200',
                                            };
                                        @endphp
                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold border {{ $badgeClass }}">
                                            {{ $rec->statusLabel() }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3.5 text-zinc-500 text-[11px]">
                                        {{ $rec->notes ?: '-' }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-4 py-8 text-center text-zinc-400">
                                        Tidak ada riwayat presensi yang sesuai kriteria pencarian.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if ($records->hasPages())
                    <div class="p-4 border-t border-zinc-200 bg-zinc-50/50">
                        {{ $records->links() }}
                    </div>
                @endif
            </div>
        @endif

    </div>
</x-layouts.app>
