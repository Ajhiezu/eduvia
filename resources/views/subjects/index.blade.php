<x-layouts.app>
    <x-slot:headerTitle>
        Mata Pelajaran & Kurikulum
    </x-slot:headerTitle>

    <div class="space-y-6">
        <!-- Status & Error Alerts -->
        @if (session('status'))
            <x-alert variant="success" dismissible>
                {{ session('status') }}
            </x-alert>
        @endif

        @if ($errors->any())
            <x-alert variant="danger" dismissible>
                {{ $errors->first() }}
            </x-alert>
        @endif

        <!-- Action & Navigation Header -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h1 class="text-xl sm:text-2xl font-extrabold text-zinc-900 tracking-tight">
                    Mata Pelajaran & Struktur Kurikulum
                </h1>
                <p class="text-xs text-zinc-500 mt-0.5">
                    Kelola data master mata pelajaran, alokasi JP/SKS, kelompok mapel, serta peminatan SMA dan kejuruan SMK.
                </p>
            </div>

            <div class="flex items-center gap-2.5">
                <x-button href="{{ route('schedules.index') }}" variant="outline" size="sm">
                    Jadwal Pelajaran
                </x-button>
                <x-button href="{{ route('subjects.create') }}" variant="primary" size="sm">
                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Tambah Mata Pelajaran
                </x-button>
            </div>
        </div>

        <!-- Metric Cards -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
            <x-card padding class="bg-white">
                <p class="text-xs font-medium text-zinc-500">Total Mata Pelajaran</p>
                <p class="text-2xl font-bold text-zinc-900 mt-1">{{ number_format($totalCount) }}</p>
                <p class="text-[11px] text-zinc-400 mt-0.5">Semua kelompok mapel terdaftar</p>
            </x-card>
            <x-card padding class="bg-white">
                <p class="text-xs font-medium text-emerald-700">Mapel Aktif Diajarkan</p>
                <p class="text-2xl font-bold text-emerald-800 mt-1">{{ number_format($activeCount) }}</p>
                <p class="text-[11px] text-emerald-600 mt-0.5">Tersedia pada jadwal & penilaian</p>
            </x-card>
            <x-card padding class="bg-white">
                <p class="text-xs font-medium text-indigo-700">Muatan Umum / Wajib</p>
                <p class="text-2xl font-bold text-indigo-800 mt-1">{{ number_format($umumCount) }}</p>
                <p class="text-[11px] text-indigo-600 mt-0.5">Kurikulum Nasional / Inti</p>
            </x-card>
            <x-card padding class="bg-white">
                <p class="text-xs font-medium text-amber-700">Peminatan / Kejuruan</p>
                <p class="text-2xl font-bold text-amber-800 mt-1">{{ number_format($peminatanKejuruanCount) }}</p>
                <p class="text-[11px] text-amber-600 mt-0.5">Keahlian SMA/SMK spesifik</p>
            </x-card>
        </div>

        <!-- Search & Filter Card -->
        <x-card padding>
            <form method="GET" action="{{ route('subjects.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
                <!-- Search Input -->
                <div class="lg:col-span-2">
                    <input
                        type="search"
                        name="search"
                        value="{{ $search }}"
                        placeholder="Cari nama, kode mapel, atau jurusan..."
                        class="w-full rounded-lg border border-zinc-300 text-zinc-900 text-xs py-2 px-3 focus:border-brand-500 focus:ring-2 focus:ring-brand-100 placeholder-zinc-400"
                    >
                </div>

                <!-- Group Filter -->
                <div>
                    <select
                        name="group"
                        class="w-full rounded-lg border border-zinc-300 text-zinc-900 text-xs py-2 px-3 focus:border-brand-500 focus:ring-2 focus:ring-brand-100"
                    >
                        <option value="">Semua Kelompok</option>
                        <option value="Umum" @selected($group === 'Umum')>Umum / Wajib</option>
                        <option value="Peminatan" @selected($group === 'Peminatan')>Peminatan (SMA)</option>
                        <option value="Kejuruan" @selected($group === 'Kejuruan')>Kejuruan (SMK)</option>
                        <option value="Muatan Lokal" @selected($group === 'Muatan Lokal')>Muatan Lokal</option>
                    </select>
                </div>

                <!-- Educational Level Filter -->
                <div>
                    <select
                        name="educational_level"
                        class="w-full rounded-lg border border-zinc-300 text-zinc-900 text-xs py-2 px-3 focus:border-brand-500 focus:ring-2 focus:ring-brand-100"
                    >
                        <option value="">Semua Jenjang</option>
                        <option value="sd" @selected($level === 'sd')>SD / MI</option>
                        <option value="smp" @selected($level === 'smp')>SMP / MTs</option>
                        <option value="sma" @selected($level === 'sma')>SMA / MA</option>
                        <option value="smk" @selected($level === 'smk')>SMK / MAK</option>
                    </select>
                </div>

                <!-- Action Buttons -->
                <div class="flex items-center gap-2">
                    <x-button type="submit" variant="primary" size="sm" class="w-full justify-center">
                        Filter
                    </x-button>
                    @if ($search || $group || $level || $curriculum || $status !== null)
                        <x-button href="{{ route('subjects.index') }}" variant="ghost" size="sm" class="px-2" title="Reset Filter">
                            Reset
                        </x-button>
                    @endif
                </div>
            </form>
        </x-card>

        <!-- Subjects Table Card -->
        <x-card padding="none">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-zinc-200 bg-zinc-50/75 text-[11px] font-bold uppercase tracking-wider text-zinc-500">
                            <th class="py-3 px-4 w-12 text-center">No</th>
                            <th class="py-3 px-4">Kode & Nama Mapel</th>
                            <th class="py-3 px-4">Kelompok</th>
                            <th class="py-3 px-4">Jenjang & Kurikulum</th>
                            <th class="py-3 px-4 text-center">Beban (JP)</th>
                            <th class="py-3 px-4 text-center">Pengajar Aktif</th>
                            <th class="py-3 px-4 text-center">Status</th>
                            <th class="py-3 px-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-200 text-xs">
                        @forelse ($subjects as $index => $subject)
                            <tr class="hover:bg-zinc-50/60 transition-colors">
                                <td class="py-3.5 px-4 text-center text-zinc-400 font-mono">
                                    {{ $subjects->firstItem() + $index }}
                                </td>
                                <td class="py-3.5 px-4">
                                    <div class="flex items-center gap-2">
                                        @if ($subject->code)
                                            <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-mono font-bold bg-zinc-100 text-zinc-800 border border-zinc-300">
                                                {{ $subject->code }}
                                            </span>
                                        @endif
                                        <span class="font-bold text-zinc-900">{{ $subject->name }}</span>
                                    </div>
                                    @if ($subject->academic_program)
                                        <p class="text-[11px] text-brand-700 mt-0.5 font-medium">
                                            Konsentrasi: {{ $subject->academic_program }}
                                        </p>
                                    @endif
                                </td>
                                <td class="py-3.5 px-4">
                                    @if ($subject->group === 'Umum')
                                        <x-badge variant="neutral" size="sm">Umum</x-badge>
                                    @elseif ($subject->group === 'Peminatan')
                                        <x-badge variant="info" size="sm">Peminatan SMA</x-badge>
                                    @elseif ($subject->group === 'Kejuruan')
                                        <x-badge variant="warning" size="sm">Kejuruan SMK</x-badge>
                                    @else
                                        <x-badge variant="neutral" size="sm">{{ $subject->group }}</x-badge>
                                    @endif
                                </td>
                                <td class="py-3.5 px-4">
                                    <div class="flex items-center gap-1.5 flex-wrap">
                                        <span class="text-zinc-700 font-medium uppercase text-[11px]">
                                            {{ $subject->educational_level ?? 'Semua Jenjang' }}
                                        </span>
                                        <span class="text-zinc-300">•</span>
                                        <span class="text-zinc-500 capitalize text-[11px]">
                                            Kurikulum {{ $subject->curriculum ?? 'Merdeka' }}
                                        </span>
                                    </div>
                                </td>
                                <td class="py-3.5 px-4 text-center font-semibold text-zinc-800">
                                    {{ $subject->credit_hours ?? 2 }} JP
                                </td>
                                <td class="py-3.5 px-4 text-center">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $subject->teaching_assignments_count > 0 ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-zinc-100 text-zinc-500' }}">
                                        {{ $subject->teaching_assignments_count }} rombel
                                    </span>
                                </td>
                                <td class="py-3.5 px-4 text-center">
                                    @if ($subject->is_active)
                                        <x-badge variant="success" size="sm">Aktif</x-badge>
                                    @else
                                        <x-badge variant="danger" size="sm">Non-Aktif</x-badge>
                                    @endif
                                </td>
                                <td class="py-3.5 px-4 text-right">
                                    <div class="inline-flex items-center gap-1.5">
                                        <x-button href="{{ route('subjects.edit', $subject) }}" variant="outline" size="sm">
                                            Edit
                                        </x-button>
                                        <form method="POST" action="{{ route('subjects.destroy', $subject) }}" onsubmit="return confirm('Apakah Anda yakin ingin menghapus mata pelajaran ini?');" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="p-1.5 text-zinc-400 hover:text-red-600 transition-colors rounded-lg hover:bg-red-50" title="Hapus Mapel">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                </svg>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="py-12 text-center text-zinc-500">
                                    <svg class="w-12 h-12 mx-auto text-zinc-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                                    </svg>
                                    <p class="font-medium text-sm text-zinc-800">Tidak ada mata pelajaran ditemukan</p>
                                    <p class="text-xs text-zinc-400 mt-1">Coba sesuaikan kata kunci pencarian atau tambahkan mata pelajaran baru.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($subjects->hasPages())
                <div class="p-4 border-t border-zinc-200">
                    {{ $subjects->links() }}
                </div>
            @endif
        </x-card>
    </div>
</x-layouts.app>
