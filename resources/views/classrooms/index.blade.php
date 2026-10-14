<x-layouts.app>
    <x-slot:headerTitle>
        Rombongan Belajar
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
                    Rombongan Belajar & Kelas
                </h1>
                <p class="text-xs text-zinc-500 mt-0.5">
                    Kelola pembagian rombongan belajar operasional, wali kelas, kapasitas, dan penempatan peserta didik.
                </p>
            </div>

            <div class="flex items-center gap-2.5">
                <x-button href="{{ route('academic-years.index') }}" variant="outline" size="sm">
                    Kalender Akademik
                </x-button>
                <x-button href="{{ route('classrooms.create') }}" variant="primary" size="sm">
                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Tambah Rombongan Belajar
                </x-button>
            </div>
        </div>

        <!-- Filter & Search Card (Metis Hairline Style) -->
        <x-card padding>
            <form method="GET" action="{{ route('classrooms.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
                <!-- Search Input -->
                <div class="lg:col-span-2">
                    <input
                        type="search"
                        name="search"
                        value="{{ $search }}"
                        placeholder="Cari nama rombel, kode, atau wali kelas..."
                        class="w-full rounded-lg border border-zinc-300 text-zinc-900 text-xs py-2 px-3 focus:border-brand-500 focus:ring-2 focus:ring-brand-100 placeholder-zinc-400"
                    >
                </div>

                <!-- Academic Year Filter -->
                <div>
                    <select
                        name="academic_year_id"
                        onchange="this.form.submit()"
                        class="w-full rounded-lg border border-zinc-300 text-zinc-900 text-xs py-2 px-3 focus:border-brand-500 focus:ring-2 focus:ring-brand-100"
                    >
                        <option value="">-- Semua Tahun Ajaran --</option>
                        @foreach ($academicYears as $year)
                            <option value="{{ $year->id }}" {{ $academicYearId == $year->id ? 'selected' : '' }}>
                                T.A. {{ $year->name }} {{ $year->is_active ? '(Aktif)' : '' }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Grade Level Filter -->
                <div>
                    <select
                        name="grade_level_id"
                        onchange="this.form.submit()"
                        class="w-full rounded-lg border border-zinc-300 text-zinc-900 text-xs py-2 px-3 focus:border-brand-500 focus:ring-2 focus:ring-brand-100"
                    >
                        <option value="">-- Semua Tingkat Kelas --</option>
                        @foreach ($gradeLevels as $gl)
                            <option value="{{ $gl->id }}" {{ $gradeLevelId == $gl->id ? 'selected' : '' }}>
                                {{ $gl->displayLabel() }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Submit & Reset -->
                <div class="flex items-center gap-2">
                    <x-button type="submit" variant="secondary" size="sm" class="w-full">
                        Filter Data
                    </x-button>
                    @if ($search || $academicYearId || $gradeLevelId || $status !== null)
                        <a href="{{ route('classrooms.index') }}" class="text-xs text-zinc-500 hover:text-zinc-800 font-semibold px-2 py-1 shrink-0">
                            Reset
                        </a>
                    @endif
                </div>
            </form>
        </x-card>

        <!-- Classrooms Table -->
        @if ($classrooms->isEmpty())
            <x-empty-state
                title="Belum Ada Rombongan Belajar"
                description="Rombongan belajar menghubungkan siswa dengan kurikulum, wali kelas, jadwal, dan guru pengampu."
            >
                <x-slot:action>
                    <x-button href="{{ route('classrooms.create') }}" variant="primary" size="sm">
                        Buat Rombongan Belajar Baru
                    </x-button>
                </x-slot:action>
            </x-empty-state>
        @else
            <x-table>
                <x-slot:header>
                    <th scope="col" class="px-5 py-3">Nama Rombel</th>
                    <th scope="col" class="px-5 py-3">Tingkat Kelas</th>
                    <th scope="col" class="px-5 py-3">Tahun Ajaran</th>
                    <th scope="col" class="px-5 py-3">Wali Kelas</th>
                    <th scope="col" class="px-5 py-3 text-center">Anggota Siswa</th>
                    <th scope="col" class="px-5 py-3">Status</th>
                    <th scope="col" class="px-5 py-3 text-right">Aksi</th>
                </x-slot:header>

                @foreach ($classrooms as $cls)
                    <tr class="hover:bg-zinc-50/80 transition-colors">
                        <td class="px-5 py-3.5 font-bold text-zinc-900">
                            <a href="{{ route('classrooms.show', $cls) }}" class="hover:text-brand-600 transition-colors flex items-center gap-2">
                                <span>{{ $cls->name }}</span>
                                @if ($cls->code)
                                    <span class="text-[10px] font-mono px-1.5 py-0.5 rounded bg-zinc-100 text-zinc-600 font-normal">
                                        {{ $cls->code }}
                                    </span>
                                @endif
                            </a>
                            @if ($cls->notes)
                                <div class="text-[11px] font-normal text-zinc-400 mt-0.5 truncate max-w-xs">{{ $cls->notes }}</div>
                            @endif
                        </td>
                        <td class="px-5 py-3.5 text-xs text-zinc-700">
                            <span class="font-semibold">{{ $cls->gradeLevel?->name }}</span>
                            <span class="text-zinc-400">({{ strtoupper($cls->gradeLevel?->educational_level ?? '-') }})</span>
                        </td>
                        <td class="px-5 py-3.5 text-xs text-zinc-600 font-mono">
                            {{ $cls->academicYear?->name }}
                        </td>
                        <td class="px-5 py-3.5 text-xs">
                            @if ($cls->homeroomTeacher)
                                <div class="font-semibold text-zinc-800">{{ $cls->homeroomTeacher->name }}</div>
                            @else
                                <span class="italic text-zinc-400">Belum Ditentukan</span>
                            @endif
                        </td>
                        <td class="px-5 py-3.5 text-xs text-center font-semibold">
                            <span class="text-zinc-900">{{ $cls->active_students_count }}</span>
                            <span class="text-zinc-400">/ {{ $cls->capacity ?? '∞' }}</span>
                        </td>
                        <td class="px-5 py-3.5">
                            @if ($cls->is_active)
                                <x-badge variant="success" size="sm" dot>Aktif</x-badge>
                            @else
                                <x-badge variant="neutral" size="sm">Non-Aktif</x-badge>
                            @endif
                        </td>
                        <td class="px-5 py-3.5 text-right">
                            <div class="inline-flex items-center gap-1.5">
                                <x-button href="{{ route('classrooms.show', $cls) }}" variant="ghost" size="sm">
                                    Detail
                                </x-button>
                                <x-button href="{{ route('classrooms.edit', $cls) }}" variant="ghost" size="sm">
                                    Ubah
                                </x-button>

                                <form method="POST" action="{{ route('classrooms.destroy', $cls) }}" onsubmit="return confirm('Apakah Anda yakin ingin menghapus rombongan belajar ini?');" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button
                                        type="submit"
                                        class="p-1.5 rounded-lg text-zinc-400 hover:text-rose-600 hover:bg-rose-50 transition-colors"
                                        title="Hapus"
                                    >
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @endforeach

                @if ($classrooms->hasPages())
                    <x-slot:footer>
                        {{ $classrooms->links() }}
                    </x-slot:footer>
                @endif
            </x-table>
        @endif
    </div>
</x-layouts.app>
