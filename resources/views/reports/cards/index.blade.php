<x-layouts.app>
    <x-slot:headerTitle>
        Buku Rapor Digital (E-Rapor)
    </x-slot:headerTitle>

    <div class="space-y-6">
        @if (session('success'))
            <x-alert variant="success" dismissible>
                {{ session('success') }}
            </x-alert>
        @endif

        @if (session('error'))
            <x-alert variant="danger" dismissible>
                {{ session('error') }}
            </x-alert>
        @endif

        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h1 class="text-xl sm:text-2xl font-extrabold text-zinc-900 tracking-tight">
                    E-Rapor & Administrasi Wali Kelas
                </h1>
                <p class="text-xs text-zinc-500 mt-0.5">
                    Pilih rombongan belajar untuk mengelola kompilasi nilai akademik, kehadiran, ekstrakurikuler, catatan perkembangan, dan publikasi rapor resmi.
                </p>
            </div>

            <div class="flex items-center gap-2.5">
                <x-button href="{{ route('reports.gradebook.index') }}" variant="secondary" size="sm">
                    Buku Nilai Mapel
                </x-button>
            </div>
        </div>

        <!-- Filter Year -->
        <div class="flex items-center justify-between">
            <form action="{{ route('reports.cards.index') }}" method="GET" class="flex items-center gap-3">
                <x-label for="academic_year_id" value="Tahun Ajaran:" class="text-xs whitespace-nowrap" />
                <x-select id="academic_year_id" name="academic_year_id" class="text-xs py-1.5" onchange="this.form.submit()">
                    @foreach($academicYears as $year)
                        <option value="{{ $year->id }}" {{ $selectedYearId == $year->id ? 'selected' : '' }}>
                            {{ $year->name }} {{ $year->is_active ? '(Aktif)' : '' }}
                        </option>
                    @endforeach
                </x-select>
            </form>
        </div>

        <!-- Classrooms Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
            @forelse($classrooms as $classroom)
                <x-card padding class="border-zinc-200 hover:border-brand-300 hover:shadow-sm transition-all flex flex-col justify-between">
                    <div>
                        <div class="flex items-start justify-between gap-2">
                            <div>
                                <span class="text-[10px] font-bold uppercase tracking-wider text-zinc-400">
                                    Tingkat {{ $classroom->gradeLevel?->name ?? '-' }}
                                </span>
                                <h3 class="text-lg font-extrabold text-zinc-900 mt-0.5">
                                    Kelas {{ $classroom->name }}
                                </h3>
                            </div>

                            @if(auth()->user()->id === $classroom->homeroom_teacher_id)
                                <x-badge variant="brand" size="xs">Kelas Anda</x-badge>
                            @endif
                        </div>

                        <div class="mt-4 space-y-2 text-xs text-zinc-600">
                            <div class="flex items-center justify-between">
                                <span class="text-zinc-500">Wali Kelas:</span>
                                <span class="font-semibold text-zinc-800">{{ $classroom->homeroomTeacher?->name ?? 'Belum Ditugaskan' }}</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-zinc-500">Total Siswa Aktif:</span>
                                <span class="font-bold text-zinc-900">{{ $classroom->active_students_count }} Siswa</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-zinc-500">Tahun Ajaran:</span>
                                <span class="text-zinc-700">{{ $classroom->academicYear?->name }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="mt-5 pt-4 border-t border-zinc-100 flex items-center justify-end">
                        <x-button href="{{ route('reports.cards.classroom', $classroom) }}" variant="primary" size="sm" class="w-full justify-center">
                            Kelola Rapor Kelas
                        </x-button>
                    </div>
                </x-card>
            @empty
                <div class="col-span-3 py-12 text-center text-zinc-400">
                    <p class="text-xs">Tidak ada kelas yang ditemukan untuk tahun ajaran ini.</p>
                </div>
            @endforelse
        </div>

        @if($classrooms->hasPages())
            <div class="mt-4">
                {{ $classrooms->links() }}
            </div>
        @endif
    </div>
</x-layouts.app>
