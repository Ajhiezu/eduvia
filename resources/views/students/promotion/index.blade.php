<x-layouts.app>
    <x-slot:headerTitle>
        Kenaikan Kelas & Kelulusan
    </x-slot:headerTitle>

    <div
        x-data="{
            action: 'promote',
            selectAll: false,

            toggleSelectAll() {
                const checkboxes = document.querySelectorAll('input[type=checkbox][name=\'student_ids[]\']');
                checkboxes.forEach(cb => {
                    cb.checked = this.selectAll;
                });
            }
        }"
        class="space-y-6"
    >
        <!-- Alerts -->
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

        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <div class="flex items-center gap-2 text-xs text-zinc-500 mb-1">
                    <a href="{{ route('students.index') }}" class="hover:text-zinc-800">&larr; Kembali ke Daftar Siswa</a>
                </div>
                <h1 class="text-xl sm:text-2xl font-extrabold text-zinc-900 tracking-tight">
                    Proses Kenaikan Kelas & Kelulusan Massal
                </h1>
                <p class="text-xs text-zinc-500 mt-0.5">
                    Proses promosi kenaikan rombel peserta didik ke tahun ajaran baru atau penetapan kelulusan akhir jenjang.
                </p>
            </div>
        </div>

        <!-- Filter Source Classroom Card -->
        <x-card padding>
            <form method="GET" action="{{ route('students.promotion.index') }}" class="flex flex-col sm:flex-row items-end gap-3">
                <div class="flex-1 w-full">
                    <label class="block text-xs font-bold text-zinc-700 mb-1">
                        Pilih Rombongan Belajar Asal yang Akan Diproses:
                    </label>
                    <select
                        name="source_classroom_id"
                        class="w-full rounded-lg border border-zinc-300 text-zinc-900 text-xs py-2 px-3 focus:border-brand-500 focus:ring-2 focus:ring-brand-100"
                        required
                    >
                        <option value="">-- Pilih Rombel Asal --</option>
                        @foreach ($classrooms as $c)
                            <option value="{{ $c->id }}" {{ $sourceClassroomId == $c->id ? 'selected' : '' }}>
                                {{ $c->name }} ({{ $c->gradeLevel?->displayLabel() }} &bull; T.A. {{ $c->academicYear?->name }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <x-button type="submit" variant="primary" size="md">
                    Muat Daftar Siswa
                </x-button>
            </form>
        </x-card>

        @if ($selectedClassroom)
            <form method="POST" action="{{ route('students.promotion.process') }}" class="space-y-6">
                @csrf
                <input type="hidden" name="source_classroom_id" value="{{ $selectedClassroom->id }}">

                <!-- Action Configuration Card -->
                <x-card title="Konfigurasi Proses Kenaikan / Kelulusan" padding>
                    <div class="space-y-4">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <!-- Jenis Aksi -->
                            <div>
                                <label class="block text-xs font-bold text-zinc-700 mb-1">Jenis Aksi Pemrosesan</label>
                                <div class="grid grid-cols-2 gap-2">
                                    <label class="cursor-pointer border border-zinc-300 rounded-lg p-2.5 text-xs flex items-center gap-2 has-checked:border-brand-500 has-checked:bg-brand-50/40">
                                        <input
                                            type="radio"
                                            name="action"
                                            value="promote"
                                            x-model="action"
                                            class="text-brand-600 focus:ring-brand-500"
                                        >
                                        <span class="font-bold text-zinc-800">Kenaikan Kelas</span>
                                    </label>

                                    <label class="cursor-pointer border border-zinc-300 rounded-lg p-2.5 text-xs flex items-center gap-2 has-checked:border-brand-500 has-checked:bg-brand-50/40">
                                        <input
                                            type="radio"
                                            name="action"
                                            value="graduate"
                                            x-model="action"
                                            class="text-brand-600 focus:ring-brand-500"
                                        >
                                        <span class="font-bold text-zinc-800">Kelulusan Jenjang</span>
                                    </label>
                                </div>
                            </div>

                            <!-- Tanggal Efektif -->
                            <x-input
                                type="date"
                                label="Tanggal Efektif"
                                name="effective_date"
                                value="{{ date('Y-m-d') }}"
                                required
                            />
                        </div>

                        <!-- Target Classroom & Year (Shown only when promote) -->
                        <div x-show="action === 'promote'" class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-2 border-t border-zinc-100">
                            <x-select
                                label="Tahun Ajaran Tujuan"
                                name="target_academic_year_id"
                                hint="Tahun ajaran baru tempat rombel tujuan berada."
                            >
                                <option value="">-- Pilih Tahun Ajaran Baru --</option>
                                @foreach ($academicYears as $year)
                                    <option value="{{ $year->id }}">
                                        {{ $year->name }} {{ $year->is_active ? '(Aktif)' : '' }}
                                    </option>
                                @endforeach
                            </x-select>

                            <x-select
                                label="Rombongan Belajar Tujuan"
                                name="target_classroom_id"
                                hint="Pilih kelas tujuan kenaikan kelas."
                            >
                                <option value="">-- Pilih Rombel Tujuan --</option>
                                @foreach ($classrooms->where('id', '!=', $selectedClassroom->id) as $tc)
                                    <option value="{{ $tc->id }}">
                                        {{ $tc->name }} ({{ $tc->gradeLevel?->displayLabel() }} &bull; T.A. {{ $tc->academicYear?->name }})
                                    </option>
                                @endforeach
                            </x-select>
                        </div>

                        <!-- Catatan -->
                        <x-input
                            label="Keterangan / Catatan Proses (Opsional)"
                            name="notes"
                            placeholder="Contoh: Kenaikan kelas tahun ajaran 2026/2027..."
                        />
                    </div>
                </x-card>

                <!-- Students Selection Checklist Table -->
                <x-card :padding="false">
                    <div class="p-4 border-b border-zinc-200 flex items-center justify-between bg-zinc-50/50">
                        <div>
                            <h3 class="text-sm font-bold text-zinc-900">
                                Daftar Siswa Rombel {{ $selectedClassroom->name }} ({{ $students->count() }} Siswa)
                            </h3>
                            <p class="text-[11px] text-zinc-500">
                                Beri centang pada peserta didik yang memenuhi syarat untuk diproses.
                            </p>
                        </div>

                        <label class="inline-flex items-center gap-2 cursor-pointer text-xs font-semibold text-zinc-700">
                            <input
                                type="checkbox"
                                x-model="selectAll"
                                x-on:change="toggleSelectAll()"
                                class="rounded border-zinc-300 text-brand-600 focus:ring-brand-500 w-4 h-4"
                            >
                            <span>Pilih Semua Siswa</span>
                        </label>
                    </div>

                    @if ($students->isEmpty())
                        <div class="p-8 text-center text-xs text-zinc-400">
                            Tidak ada peserta didik aktif yang terdaftar di rombel {{ $selectedClassroom->name }}.
                        </div>
                    @else
                        <div class="overflow-x-auto" data-scrollable>
                            <table class="w-full text-left text-xs text-zinc-600 min-w-[500px]">
                                <thead class="bg-zinc-50/80 text-zinc-500 font-semibold border-b border-zinc-200">
                                    <tr>
                                        <th class="py-3 px-4 w-12 text-center">Pilih</th>
                                        <th class="py-3 px-4">Nama Peserta Didik</th>
                                        <th class="py-3 px-4">NIS / NISN</th>
                                        <th class="py-3 px-4">Status Saat Ini</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-zinc-200">
                                    @foreach ($students as $student)
                                        <tr class="hover:bg-zinc-50/50 transition-colors">
                                            <td class="py-3 px-4 text-center">
                                                <input
                                                    type="checkbox"
                                                    name="student_ids[]"
                                                    value="{{ $student->id }}"
                                                    class="rounded border-zinc-300 text-brand-600 focus:ring-brand-500 w-4 h-4 cursor-pointer"
                                                >
                                            </td>
                                            <td class="py-3 px-4 font-bold text-zinc-900">
                                                {{ $student->name }}
                                                <span class="text-[10px] text-zinc-400 font-normal block">{{ $student->email }}</span>
                                            </td>
                                            <td class="py-3 px-4 font-mono text-zinc-600">
                                                {{ $student->studentProfile?->nis ?: '-' }} / {{ $student->studentProfile?->nisn ?: '-' }}
                                            </td>
                                            <td class="py-3 px-4">
                                                <x-badge :variant="$student->studentProfile?->statusBadgeVariant() ?? 'success'" size="sm">
                                                    {{ $student->studentProfile?->statusLabel() ?? 'Aktif' }}
                                                </x-badge>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="p-4 border-t border-zinc-200 bg-white flex items-center justify-end">
                            <x-button type="submit" variant="primary" size="md" onclick="return confirm('Apakah Anda yakin ingin memproses data siswa terpilih secara massal?')">
                                Eksekusi Pemrosesan Massal &rarr;
                            </x-button>
                        </div>
                    @endif
                </x-card>
            </form>
        @endif
    </div>
</x-layouts.app>
