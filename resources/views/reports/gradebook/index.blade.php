<x-layouts.app>
    <x-slot:headerTitle>
        Buku Nilai Semester (Gradebook)
    </x-slot:headerTitle>

    <div class="space-y-6">
        @if (session('success'))
            <x-alert variant="success" dismissible>
                {{ session('success') }}
            </x-alert>
        @endif

        @if (session('warning'))
            <x-alert variant="warning" dismissible>
                {{ session('warning') }}
            </x-alert>
        @endif

        @if (session('info'))
            <x-alert variant="info" dismissible>
                {{ session('info') }}
            </x-alert>
        @endif

        <!-- Action Header -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h1 class="text-xl sm:text-2xl font-extrabold text-zinc-900 tracking-tight">
                    Rekapitulasi Nilai Semester
                </h1>
                <p class="text-xs text-zinc-500 mt-0.5">
                    Kelola rekapitulasi nilai tugas, kuis, ujian CBT, serta capaian pembelajaran per kelas dan mata pelajaran.
                </p>
            </div>

            <div class="flex items-center gap-2.5">
                @if(auth()->user()->isSuperAdmin() || auth()->user()->isSchoolAdmin())
                    <x-button href="{{ route('reports.weights.index') }}" variant="secondary" size="sm">
                        <svg class="w-4 h-4 mr-1.5 text-zinc-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                        Konfigurasi Bobot
                    </x-button>
                @endif

                <x-button href="{{ route('reports.cards.index') }}" variant="secondary" size="sm">
                    <svg class="w-4 h-4 mr-1.5 text-zinc-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                    </svg>
                    E-Rapor Kelas
                </x-button>

                <x-button href="{{ route('reports.gradebook.create') }}" variant="primary" size="sm">
                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Inisialisasi Buku Nilai
                </x-button>
            </div>
        </div>

        <!-- Filter Bar -->
        <x-card padding class="border-zinc-200">
            <form action="{{ route('reports.gradebook.index') }}" method="GET" class="grid grid-cols-1 sm:grid-cols-5 gap-3">
                <div>
                    <x-label for="classroom_id" value="Kelas" />
                    <x-select id="classroom_id" name="classroom_id" class="mt-1">
                        <option value="">Semua Kelas</option>
                        @foreach($classrooms as $c)
                            <option value="{{ $c->id }}" {{ request('classroom_id') == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                        @endforeach
                    </x-select>
                </div>

                <div>
                    <x-label for="subject_id" value="Mata Pelajaran" />
                    <x-select id="subject_id" name="subject_id" class="mt-1">
                        <option value="">Semua Mapel</option>
                        @foreach($subjects as $s)
                            <option value="{{ $s->id }}" {{ request('subject_id') == $s->id ? 'selected' : '' }}>{{ $s->name }}</option>
                        @endforeach
                    </x-select>
                </div>

                <div>
                    <x-label for="academic_year_id" value="Tahun Ajaran" />
                    <x-select id="academic_year_id" name="academic_year_id" class="mt-1">
                        <option value="">Semua Tahun</option>
                        @foreach($academicYears as $ay)
                            <option value="{{ $ay->id }}" {{ request('academic_year_id') == $ay->id ? 'selected' : '' }}>{{ $ay->name }}</option>
                        @endforeach
                    </x-select>
                </div>

                <div>
                    <x-label for="status" value="Status" />
                    <x-select id="status" name="status" class="mt-1">
                        <option value="">Semua Status</option>
                        <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>Draf Guru</option>
                        <option value="submitted" {{ request('status') === 'submitted' ? 'selected' : '' }}>Menunggu Verifikasi</option>
                        <option value="verified" {{ request('status') === 'verified' ? 'selected' : '' }}>Terverifikasi</option>
                        <option value="revision_requested" {{ request('status') === 'revision_requested' ? 'selected' : '' }}>Perlu Revisi</option>
                    </x-select>
                </div>

                <div class="flex items-end gap-2">
                    <x-button type="submit" variant="secondary" size="sm" class="w-full justify-center">
                        Terapkan Filter
                    </x-button>
                    @if(request()->anyFilled(['classroom_id', 'subject_id', 'academic_year_id', 'status']))
                        <x-button href="{{ route('reports.gradebook.index') }}" variant="ghost" size="sm">
                            Reset
                        </x-button>
                    @endif
                </div>
            </form>
        </x-card>

        <!-- Gradebooks Table -->
        <x-card padding="none" class="border-zinc-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-zinc-700">
                    <thead class="bg-zinc-50 border-b border-zinc-200 text-zinc-500 font-bold uppercase text-[10px] tracking-wider">
                        <tr>
                            <th class="py-3 px-4">Kelas & Mata Pelajaran</th>
                            <th class="py-3 px-4">Guru Pengampu</th>
                            <th class="py-3 px-4">Periode Akademik</th>
                            <th class="py-3 px-4 text-center">Jumlah Siswa</th>
                            <th class="py-3 px-4 text-center">Status Rekap</th>
                            <th class="py-3 px-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-200">
                        @forelse($gradebooks as $gb)
                            <tr class="hover:bg-zinc-50/75 transition-colors">
                                <td class="py-3.5 px-4 font-semibold text-zinc-900">
                                    <div class="font-bold text-zinc-900">{{ $gb->subject?->name ?? 'Mata Pelajaran' }}</div>
                                    <div class="text-[11px] text-zinc-500 font-normal mt-0.5">Kelas: {{ $gb->classroom?->name }}</div>
                                </td>
                                <td class="py-3.5 px-4 text-zinc-800">
                                    {{ $gb->teacher?->name ?? 'Belum Ditentukan' }}
                                </td>
                                <td class="py-3.5 px-4 text-zinc-600">
                                    {{ $gb->academicYear?->name }}
                                    <span class="text-zinc-400">•</span>
                                    {{ $gb->semester?->name }}
                                </td>
                                <td class="py-3.5 px-4 text-center font-bold text-zinc-800">
                                    {{ $gb->items_count }} Siswa
                                </td>
                                <td class="py-3.5 px-4 text-center">
                                    <x-badge :variant="$gb->statusBadgeVariant()" size="xs">
                                        {{ $gb->statusLabel() }}
                                    </x-badge>
                                </td>
                                <td class="py-3.5 px-4 text-right whitespace-nowrap">
                                    <x-button href="{{ route('reports.gradebook.show', $gb) }}" variant="secondary" size="xs">
                                        Buka Rekap
                                    </x-button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-10 text-center text-zinc-400">
                                    <div class="max-w-sm mx-auto text-center space-y-2">
                                        <svg class="w-10 h-10 mx-auto text-zinc-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                        </svg>
                                        <p class="text-xs text-zinc-500">Belum ada buku nilai yang diinisialisasi untuk filter ini.</p>
                                        <x-button href="{{ route('reports.gradebook.create') }}" variant="primary" size="xs">
                                            Inisialisasi Buku Nilai Sekarang
                                        </x-button>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($gradebooks->hasPages())
                <div class="p-4 border-t border-zinc-200">
                    {{ $gradebooks->links() }}
                </div>
            @endif
        </x-card>
    </div>
</x-layouts.app>
