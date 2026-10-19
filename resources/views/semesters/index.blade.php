<x-layouts.app>
    <x-slot:headerTitle>
        Semester
    </x-slot:headerTitle>

    <div class="space-y-6">
        <!-- Flash status / errors -->
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

        <!-- Action Header -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h1 class="text-xl sm:text-2xl font-extrabold text-zinc-900 tracking-tight">
                    Daftar Semester
                </h1>
                <p class="text-xs text-zinc-500 mt-0.5">
                    Kelola pembagian semester (Ganjil & Genap) pada masing-masing tahun ajaran.
                </p>
            </div>

            <div class="flex items-center gap-2.5 flex-wrap">
                <x-button href="{{ route('academic-years.index') }}" variant="outline" size="sm">
                    &larr; Kelola Tahun Ajaran
                </x-button>
                <x-button href="{{ route('semesters.create', ['academic_year_id' => $academicYearId]) }}" variant="primary" size="sm">
                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Tambah Semester
                </x-button>
            </div>
        </div>

        <!-- Filter Card by Academic Year -->
        <x-card padding>
            <form method="GET" action="{{ route('semesters.index') }}" class="flex flex-col sm:flex-row sm:items-center gap-3">
                <div class="flex-1">
                    <select
                        name="academic_year_id"
                        onchange="this.form.submit()"
                        class="w-full rounded-lg border border-zinc-200 text-zinc-900 text-sm py-2.5 px-3 focus:border-[#E11D2E] focus:ring-2 focus:ring-[#E11D2E]/20"
                    >
                        <option value="">Semua Tahun Ajaran</option>
                        @foreach ($academicYears as $year)
                            <option value="{{ $year->id }}" {{ $academicYearId == $year->id ? 'selected' : '' }}>
                                Tahun Ajaran {{ $year->name }} {{ $year->is_active ? '(Aktif Saat Ini)' : '' }}
                            </option>
                        @endforeach
                    </select>
                </div>

                @if ($academicYearId)
                    <a href="{{ route('semesters.index') }}" class="text-xs text-zinc-500 hover:text-zinc-800 font-semibold px-2 py-1">
                        Reset Filter
                    </a>
                @endif
            </form>
        </x-card>

        <!-- Semesters Table -->
        @if ($semesters->isEmpty())
            <x-empty-state
                title="Belum Ada Data Semester"
                description="Semester mengatur rentang waktu penilaian, rapor, dan kegiatan belajar mengajar aktif."
            >
                <x-slot:action>
                    <x-button href="{{ route('semesters.create', ['academic_year_id' => $academicYearId]) }}" variant="primary" size="sm">
                        Tambah Semester Baru
                    </x-button>
                </x-slot:action>
            </x-empty-state>
        @else
            <x-table>
                <x-slot:header>
                    <th scope="col" class="px-5 py-3">Nama Semester</th>
                    <th scope="col" class="px-5 py-3">Tahun Ajaran</th>
                    <th scope="col" class="px-5 py-3">Jenis</th>
                    <th scope="col" class="px-5 py-3">Rentang Periode</th>
                    <th scope="col" class="px-5 py-3">Status</th>
                    <th scope="col" class="px-5 py-3 text-right">Aksi</th>
                </x-slot:header>

                @foreach ($semesters as $semester)
                    <tr class="hover:bg-zinc-50/80 transition-colors {{ $semester->is_active ? 'bg-brand-50/20' : '' }}">
                        <td class="px-5 py-3.5 font-bold text-zinc-900">
                            <div class="flex items-center gap-2">
                                <span>{{ $semester->name }}</span>
                                @if ($semester->is_active)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-brand-100 text-brand-700">
                                        Aktif Berjalan
                                    </span>
                                @endif
                            </div>
                        </td>
                        <td class="px-5 py-3.5 text-xs text-zinc-700 font-medium">
                            <a href="{{ route('academic-years.edit', $semester->academicYear) }}" class="hover:text-brand-600 hover:underline">
                                {{ $semester->academicYear->name }}
                            </a>
                        </td>
                        <td class="px-5 py-3.5">
                            <x-badge variant="{{ $semester->semester_type === 'ganjil' ? 'primary' : 'neutral' }}" size="sm">
                                {{ ucfirst($semester->semester_type) }}
                            </x-badge>
                        </td>
                        <td class="px-5 py-3.5 text-xs text-zinc-600 font-mono">
                            {{ $semester->start_date->format('d/m/Y') }} s/d {{ $semester->end_date->format('d/m/Y') }}
                        </td>
                        <td class="px-5 py-3.5">
                            @if ($semester->is_active)
                                <x-badge variant="success" size="sm" dot>Aktif</x-badge>
                            @else
                                <x-badge variant="neutral" size="sm">Non-Aktif</x-badge>
                            @endif
                        </td>
                        <td class="px-5 py-3.5 text-right">
                            <div class="inline-flex items-center gap-1.5">
                                @if (! $semester->is_active)
                                    <form method="POST" action="{{ route('semesters.activate', $semester) }}" class="inline">
                                        @csrf
                                        <button
                                            type="submit"
                                            class="inline-flex items-center px-2 py-1 text-xs font-semibold rounded-lg bg-emerald-50 text-emerald-700 hover:bg-emerald-100 transition-colors"
                                            title="Jadikan Semester Aktif"
                                        >
                                            Aktifkan
                                        </button>
                                    </form>
                                @endif

                                <x-button href="{{ route('semesters.edit', $semester) }}" variant="ghost" size="sm">
                                    Ubah
                                </x-button>

                                @if (! $semester->is_active)
                                    <form method="POST" action="{{ route('semesters.destroy', $semester) }}" onsubmit="return confirm('Apakah Anda yakin ingin menghapus semester ini?');" class="inline">
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

                @if ($semesters->hasPages())
                    <x-slot:footer>
                        {{ $semesters->links() }}
                    </x-slot:footer>
                @endif
            </x-table>
        @endif
    </div>
</x-layouts.app>
