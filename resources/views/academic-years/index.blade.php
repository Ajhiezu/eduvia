<x-layouts.app>
    <x-slot:headerTitle>
        Tahun Ajaran
    </x-slot:headerTitle>

    <div class="space-y-6">
        <!-- Status / Flash Alert -->
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
                    Daftar Tahun Ajaran
                </h1>
                <p class="text-xs text-zinc-500 mt-0.5">
                    Kelola kalender periode tahun ajaran aktif dan riwayat akademik sekolah.
                </p>
            </div>

            <div class="flex items-center gap-2.5 flex-wrap">
                <x-button href="{{ route('semesters.index') }}" variant="outline" size="sm">
                    Lihat Semua Semester &rarr;
                </x-button>
                <x-button href="{{ route('academic-years.create') }}" variant="primary" size="sm">
                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Tambah Tahun Ajaran
                </x-button>
            </div>
        </div>

        <!-- Table Card -->
        @if ($academicYears->isEmpty())
            <x-empty-state
                title="Belum Ada Data Tahun Ajaran"
                description="Tahun ajaran mendefinisikan batas waktu kalender akademik, jadwal pembelajaran, dan penempatan kelas."
            >
                <x-slot:action>
                    <x-button href="{{ route('academic-years.create') }}" variant="primary" size="sm">
                        Buat Tahun Ajaran Baru
                    </x-button>
                </x-slot:action>
            </x-empty-state>
        @else
            <x-table>
                <x-slot:header>
                    <th scope="col" class="px-5 py-3">Tahun Ajaran</th>
                    <th scope="col" class="px-5 py-3">Tanggal Mulai</th>
                    <th scope="col" class="px-5 py-3">Tanggal Berakhir</th>
                    <th scope="col" class="px-5 py-3">Semester</th>
                    <th scope="col" class="px-5 py-3">Status</th>
                    <th scope="col" class="px-5 py-3 text-right">Aksi</th>
                </x-slot:header>

                @foreach ($academicYears as $year)
                    <tr class="hover:bg-zinc-50/80 transition-colors {{ $year->is_active ? 'bg-brand-50/20' : '' }}">
                        <td class="px-5 py-3.5 font-bold text-zinc-900">
                            <div class="flex items-center gap-2">
                                <span>{{ $year->name }}</span>
                                @if ($year->is_active)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-brand-100 text-brand-700">
                                        Aktif Berjalan
                                    </span>
                                @endif
                            </div>
                            @if ($year->description)
                                <div class="text-[11px] font-normal text-zinc-400 mt-0.5">{{ $year->description }}</div>
                            @endif
                        </td>
                        <td class="px-5 py-3.5 text-xs text-zinc-600 font-mono">
                            {{ $year->start_date->format('d/m/Y') }}
                        </td>
                        <td class="px-5 py-3.5 text-xs text-zinc-600 font-mono">
                            {{ $year->end_date->format('d/m/Y') }}
                        </td>
                        <td class="px-5 py-3.5 text-xs">
                            <a href="{{ route('semesters.index', ['academic_year_id' => $year->id]) }}" class="inline-flex items-center gap-1 font-semibold text-brand-600 hover:text-brand-700">
                                <span>{{ $year->semesters->count() }} Semester</span>
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                </svg>
                            </a>
                        </td>
                        <td class="px-5 py-3.5">
                            @if ($year->is_active)
                                <x-badge variant="success" size="sm" dot>Aktif</x-badge>
                            @else
                                <x-badge variant="neutral" size="sm">Non-Aktif</x-badge>
                            @endif
                        </td>
                        <td class="px-5 py-3.5 text-right">
                            <div class="inline-flex items-center gap-1.5">
                                @if (! $year->is_active)
                                    <form method="POST" action="{{ route('academic-years.activate', $year) }}" class="inline">
                                        @csrf
                                        <button
                                            type="submit"
                                            class="inline-flex items-center px-2 py-1 text-xs font-semibold rounded-lg bg-emerald-50 text-emerald-700 hover:bg-emerald-100 transition-colors"
                                            title="Jadikan Tahun Ajaran Aktif"
                                        >
                                            Aktifkan
                                        </button>
                                    </form>
                                @endif

                                <x-button href="{{ route('academic-years.edit', $year) }}" variant="ghost" size="sm">
                                    Ubah
                                </x-button>

                                @if (! $year->is_active)
                                    <form method="POST" action="{{ route('academic-years.destroy', $year) }}" onsubmit="return confirm('Apakah Anda yakin ingin menghapus tahun ajaran ini? Tindakan ini tidak dapat dibatalkan.');" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button
                                            type="submit"
                                            class="p-1.5 rounded-lg text-slate-400 hover:text-rose-600 hover:bg-rose-50 transition-colors"
                                            title="Hapus"
                                        >
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                            </svg>
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @endforeach

                @if ($academicYears->hasPages())
                    <x-slot:footer>
                        {{ $academicYears->links() }}
                    </x-slot:footer>
                @endif
            </x-table>
        @endif
    </div>
</x-layouts.app>
