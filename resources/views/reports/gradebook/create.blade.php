<x-layouts.app>
    <x-slot:headerTitle>
        Inisialisasi Buku Nilai Semester
    </x-slot:headerTitle>

    <div class="max-w-4xl mx-auto space-y-6">
        <div>
            <a href="{{ route('reports.gradebook.index') }}" class="inline-flex items-center text-xs font-semibold text-zinc-500 hover:text-zinc-800 transition-colors mb-2">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Kembali ke Daftar Buku Nilai
            </a>
            <h1 class="text-xl sm:text-2xl font-extrabold text-zinc-900 tracking-tight">
                Inisialisasi Rekap Nilai Mata Pelajaran
            </h1>
            <p class="text-xs text-zinc-500 mt-0.5">
                Pilih rombongan belajar dan mata pelajaran yang Anda ampu untuk mengagregasikan nilai tugas dan ujian CBT siswa secara otomatis.
            </p>
        </div>

        <!-- Selection Card -->
        <x-card padding class="border-zinc-200">
            <form action="{{ route('reports.gradebook.create') }}" method="GET" class="space-y-4">
                <div>
                    <x-label for="teaching_assignment_id" value="Pilih Penugasan Mengajar (Kelas & Mata Pelajaran)" required />
                    <x-select id="teaching_assignment_id" name="teaching_assignment_id" class="mt-1" onchange="this.form.submit()" required>
                        <option value="">-- Pilih Kelas dan Mata Pelajaran --</option>
                        @foreach($teachingAssignments as $ta)
                            <option value="{{ $ta->id }}" {{ request('teaching_assignment_id') == $ta->id ? 'selected' : '' }}>
                                {{ $ta->classroom?->name }} — {{ $ta->subject?->name }} ({{ $ta->academicYear?->name }} {{ $ta->semester?->name }})
                            </option>
                        @endforeach
                    </x-select>
                </div>
            </form>
        </x-card>

        @if($teachingAssignment)
            <!-- Preview of Students & Source Score Aggregation -->
            <form action="{{ route('reports.gradebook.store') }}" method="POST">
                @csrf
                <input type="hidden" name="teaching_assignment_id" value="{{ $teachingAssignment->id }}">

                <x-card padding class="border-zinc-200 space-y-5">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 pb-4 border-b border-zinc-200">
                        <div>
                            <div class="text-sm font-bold text-zinc-900">
                                {{ $teachingAssignment->subject?->name }} — Kelas {{ $teachingAssignment->classroom?->name }}
                            </div>
                            <div class="text-xs text-zinc-500 mt-0.5">
                                Guru: {{ $teachingAssignment->teacher?->name }} • {{ $teachingAssignment->academicYear?->name }} {{ $teachingAssignment->semester?->name }}
                            </div>
                        </div>

                        <div class="text-xs font-semibold text-zinc-600 bg-zinc-100 px-3 py-1.5 rounded-lg">
                            {{ $teachingAssignment->classroom?->activeStudents->count() }} Siswa Terdaftar
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs text-zinc-700">
                            <thead class="bg-zinc-50 border-b border-zinc-200 text-zinc-500 font-bold uppercase text-[10px] tracking-wider">
                                <tr>
                                    <th class="py-2.5 px-3">No</th>
                                    <th class="py-2.5 px-3">Nama Siswa</th>
                                    <th class="py-2.5 px-3">NIS</th>
                                    <th class="py-2.5 px-3 text-center">Status Siswa</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-zinc-200">
                                @forelse($teachingAssignment->classroom?->activeStudents ?? [] as $index => $enrollment)
                                    <tr>
                                        <td class="py-2.5 px-3 text-zinc-500">{{ $index + 1 }}</td>
                                        <td class="py-2.5 px-3 font-semibold text-zinc-900">{{ $enrollment->student?->name }}</td>
                                        <td class="py-2.5 px-3 text-zinc-600">{{ $enrollment->student?->studentProfile?->nis ?? '-' }}</td>
                                        <td class="py-2.5 px-3 text-center">
                                            <x-badge variant="success" size="xs">Aktif</x-badge>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="py-6 text-center text-zinc-400">
                                            Tidak ada siswa aktif terdaftar pada kelas ini.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="p-3 bg-blue-50 border border-blue-200 rounded-lg text-xs text-blue-800 leading-relaxed">
                        <strong>Catatan Otomatisasi:</strong> Saat buku nilai dibuat, sistem Eduvia akan langsung menarik seluruh riwayat nilai tugas yang telah dinilai dan hasil ujian CBT siswa pada mata pelajaran ini secara otomatis tanpa membuat data duplikat.
                    </div>

                    <div class="pt-3 flex items-center justify-end gap-3">
                        <x-button href="{{ route('reports.gradebook.index') }}" variant="ghost" size="sm">
                            Batal
                        </x-button>
                        <x-button type="submit" variant="primary" size="sm">
                            Buat Rekap Nilai Sekarang
                        </x-button>
                    </div>
                </x-card>
            </form>
        @endif
    </div>
</x-layouts.app>
