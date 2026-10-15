<x-layouts.app>
    <x-slot:headerTitle>
        Tugas & Evaluasi Belajar Anak
    </x-slot:headerTitle>

    <div class="space-y-6">

        <!-- Top Header & Child Switcher -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-4 border-b border-zinc-200">
            <div>
                <div class="flex items-center gap-2">
                    <a href="{{ route('parent.dashboard') }}" class="text-xs text-zinc-500 hover:text-zinc-900">&larr; Beranda Pantauan</a>
                    <span class="text-zinc-300">/</span>
                    <span class="text-xs font-bold text-zinc-800">Tugas & Evaluasi</span>
                </div>
                <h1 class="text-xl sm:text-2xl font-extrabold text-zinc-900 mt-1">
                    Tugas & Evaluasi Belajar
                </h1>
                <p class="text-xs text-zinc-500 mt-0.5">
                    Memantau status penugasan dan pengumpulan untuk <strong class="text-zinc-800">{{ $activeChild?->name ?? 'Anak' }}</strong>
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
            <!-- Assignments List Table -->
            <div class="bg-white rounded-2xl border border-zinc-200 shadow-xs overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs text-zinc-700">
                        <thead class="bg-zinc-50 border-b border-zinc-200 text-zinc-500 uppercase tracking-wider font-bold">
                            <tr>
                                <th class="px-4 py-3">Mata Pelajaran & Judul</th>
                                <th class="px-4 py-3">Guru Pengajar</th>
                                <th class="px-4 py-3">Tenggat Waktu</th>
                                <th class="px-4 py-3">Status Pengumpulan</th>
                                <th class="px-4 py-3">Nilai</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-100">
                            @forelse ($assignments as $task)
                                @php
                                    $sub = $task->submissions->first();
                                    $isOverdue = $task->due_date && $task->due_date->isPast() && ! $sub;
                                @endphp
                                <tr class="hover:bg-zinc-50/60 transition-colors">
                                    <td class="px-4 py-3.5">
                                        <div class="font-bold text-zinc-900 text-sm">
                                            {{ $task->title }}
                                        </div>
                                        <div class="text-[11px] text-brand-600 font-semibold mt-0.5">
                                            {{ $task->subject?->name ?? 'Mata Pelajaran' }}
                                        </div>
                                    </td>
                                    <td class="px-4 py-3.5 text-zinc-600">
                                        {{ $task->teacher?->name ?? '-' }}
                                    </td>
                                    <td class="px-4 py-3.5 whitespace-nowrap">
                                        @if ($task->due_date)
                                            <div class="font-bold {{ $isOverdue ? 'text-rose-600' : 'text-zinc-800' }}">
                                                {{ $task->due_date->format('d M Y') }}
                                            </div>
                                            <div class="text-[10px] text-zinc-400">
                                                {{ $task->due_date->format('H:i') }} WIB
                                            </div>
                                        @else
                                            <span class="text-zinc-400">-</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3.5 whitespace-nowrap">
                                        @if ($sub)
                                            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-50 text-emerald-800 border border-emerald-200">
                                                Terkumpul ({{ $sub->submitted_at ? $sub->submitted_at->format('d/m H:i') : 'Selesai' }})
                                            </span>
                                        @elseif ($isOverdue)
                                            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-rose-50 text-rose-800 border border-rose-200">
                                                Terlambat
                                            </span>
                                        @else
                                            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-amber-50 text-amber-800 border border-amber-200">
                                                Belum Dikumpulkan
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3.5 whitespace-nowrap">
                                        @if ($sub && $sub->score !== null)
                                            <span class="font-extrabold text-sm text-zinc-900">
                                                {{ $sub->score }}
                                            </span>
                                            <span class="text-[10px] text-zinc-400">/ {{ $task->max_score }}</span>
                                        @elseif ($sub)
                                            <span class="text-[11px] text-zinc-500 italic">Menunggu Penilaian</span>
                                        @else
                                            <span class="text-zinc-400">-</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-4 py-8 text-center text-zinc-400">
                                        Tidak ada tugas terdaftar untuk rombongan belajar anak saat ini.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if ($assignments->hasPages())
                    <div class="p-4 border-t border-zinc-200 bg-zinc-50/50">
                        {{ $assignments->links() }}
                    </div>
                @endif
            </div>
        @endif

    </div>
</x-layouts.app>
