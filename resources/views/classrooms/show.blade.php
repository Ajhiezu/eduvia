<x-layouts.app>
    <x-slot:headerTitle>
        Detail Rombongan Belajar
    </x-slot:headerTitle>

    <div
        x-data="{
            classroomId: {{ $classroom->id }},
            transferModalOpen: false,
            removeModalOpen: false,
            activeStudentId: null,
            activeStudentName: '',
            transferAction: '',
            removeAction: '',

            openTransfer(studentId, studentName) {
                this.activeStudentId = studentId;
                this.activeStudentName = studentName;
                this.transferAction = '{{ url('/classrooms/' . $classroom->id . '/students') }}/' + studentId + '/transfer';
                this.$dispatch('open-modal', 'transfer-student-modal');
            },

            openRemove(studentId, studentName) {
                this.activeStudentId = studentId;
                this.activeStudentName = studentName;
                this.removeAction = '{{ url('/classrooms/' . $classroom->id . '/students') }}/' + studentId;
                this.$dispatch('open-modal', 'remove-student-modal');
            }
        }"
        class="space-y-6"
    >
        <!-- Flash Alerts -->
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

        <!-- Breadcrumb & Top Bar -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <div class="flex items-center gap-2 text-xs text-zinc-500 mb-1">
                    <a href="{{ route('classrooms.index') }}" class="hover:text-zinc-800">&larr; Kembali ke Daftar Rombel</a>
                    <span>&bull;</span>
                    <span>T.A. {{ $classroom->academicYear->name }}</span>
                    <span>&bull;</span>
                    <span>{{ $classroom->gradeLevel?->displayLabel() ?? 'Tingkat Kelas' }}</span>
                </div>
                <div class="flex items-center gap-3">
                    <h1 class="text-xl sm:text-2xl font-extrabold text-zinc-900 tracking-tight">
                        Rombongan Belajar: {{ $classroom->name }}
                    </h1>
                    @if ($classroom->code)
                        <span class="px-2 py-0.5 text-xs font-mono font-semibold rounded bg-zinc-100 text-zinc-700 border border-zinc-200">
                            {{ $classroom->code }}
                        </span>
                    @endif
                    @if ($classroom->is_active)
                        <x-badge variant="success" size="sm">Aktif</x-badge>
                    @else
                        <x-badge variant="neutral" size="sm">Non-Aktif</x-badge>
                    @endif
                </div>
            </div>

            <div class="flex items-center gap-2 flex-wrap">
                <x-button
                    type="button"
                    variant="primary"
                    size="sm"
                    x-on:click="$dispatch('open-modal', 'add-student-modal')"
                    :disabled="$classroom->isFull()"
                >
                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                    </svg>
                    Tempatkan Siswa
                </x-button>

                <x-button href="{{ route('classrooms.edit', $classroom) }}" variant="secondary" size="sm">
                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                    </svg>
                    Edit Rombel
                </x-button>

                @if ($activeStudents->isEmpty())
                    <form
                        method="POST"
                        action="{{ route('classrooms.destroy', $classroom) }}"
                        onsubmit="return confirm('Apakah Anda yakin ingin menghapus rombongan belajar ini?')"
                    >
                        @csrf
                        @method('DELETE')
                        <x-button type="submit" variant="danger" size="sm">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
                            Hapus
                        </x-button>
                    </form>
                @endif
            </div>
        </div>

        <!-- 4-Card Metis Metric Summary Strip -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <x-card padding class="border-zinc-200">
                <div class="text-[11px] font-bold uppercase tracking-wider text-zinc-400">Total Siswa Aktif</div>
                <div class="mt-1 flex items-baseline justify-between">
                    <span class="text-2xl font-extrabold text-zinc-900">{{ $activeStudents->count() }}</span>
                    <span class="text-xs text-zinc-500">dari {{ $classroom->capacity }} kursi</span>
                </div>
                <div class="mt-2 w-full bg-zinc-100 rounded-full h-1.5 overflow-hidden">
                    @php
                        $percentage = $classroom->capacity > 0 ? min(100, round(($activeStudents->count() / $classroom->capacity) * 100)) : 0;
                    @endphp
                    <div
                        class="h-full rounded-full {{ $percentage >= 100 ? 'bg-amber-500' : 'bg-brand-600' }}"
                        style="width: {{ $percentage }}%"
                    ></div>
                </div>
            </x-card>

            <x-card padding class="border-zinc-200">
                <div class="text-[11px] font-bold uppercase tracking-wider text-zinc-400">Sisa Kapasitas</div>
                <div class="mt-1 flex items-baseline gap-1">
                    <span class="text-2xl font-extrabold {{ $classroom->isFull() ? 'text-amber-600' : 'text-emerald-600' }}">
                        {{ max(0, $classroom->capacity - $activeStudents->count()) }}
                    </span>
                    <span class="text-xs text-zinc-500">kursi tersedia</span>
                </div>
                <div class="mt-2 text-[11px] text-zinc-400">
                    {{ $classroom->isFull() ? 'Kapasitas kelas telah penuh' : 'Dapat menerima siswa baru' }}
                </div>
            </x-card>

            <x-card padding class="border-zinc-200">
                <div class="text-[11px] font-bold uppercase tracking-wider text-zinc-400">Tingkat Kelas</div>
                <div class="mt-1 text-sm font-bold text-zinc-800 truncate" title="{{ $classroom->gradeLevel?->displayLabel() }}">
                    {{ $classroom->gradeLevel?->displayLabel() ?? '-' }}
                </div>
                <div class="mt-2 text-[11px] text-zinc-400">
                    Jenjang: {{ strtoupper($classroom->gradeLevel?->educational_level ?? 'Umum') }}
                </div>
            </x-card>

            <x-card padding class="border-zinc-200">
                <div class="text-[11px] font-bold uppercase tracking-wider text-zinc-400">Wali Kelas</div>
                <div class="mt-1 text-sm font-bold text-zinc-800 truncate" title="{{ $classroom->homeroomTeacher?->name ?? 'Belum Ditugaskan' }}">
                    {{ $classroom->homeroomTeacher?->name ?? 'Belum Ditugaskan' }}
                </div>
                <div class="mt-2 text-[11px] text-zinc-400 truncate">
                    {{ $classroom->homeroomTeacher?->email ?? 'Silakan tentukan wali kelas' }}
                </div>
            </x-card>
        </div>

        @if ($classroom->notes)
            <div class="p-3.5 bg-zinc-50 border border-zinc-200 rounded-xl text-xs text-zinc-700 flex items-start gap-2.5">
                <svg class="w-4 h-4 text-zinc-400 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <div>
                    <span class="font-bold text-zinc-800">Catatan Rombel:</span> {{ $classroom->notes }}
                </div>
            </div>
        @endif

        <!-- SECTION 1: DAFTAR SISWA AKTIF -->
        <div class="space-y-3">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-base font-extrabold text-zinc-900 tracking-tight">
                        Daftar Peserta Didik Aktif
                    </h2>
                    <p class="text-xs text-zinc-500">
                        {{ $activeStudents->count() }} siswa terdaftar aktif pada tahun ajaran ini.
                    </p>
                </div>
            </div>

            <x-card :padding="false">
                @if ($activeStudents->isEmpty())
                    <div class="p-8 text-center">
                        <div class="w-12 h-12 mx-auto rounded-full bg-zinc-100 flex items-center justify-center text-zinc-400 mb-3">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                            </svg>
                        </div>
                        <h4 class="text-sm font-bold text-zinc-800">Belum Ada Siswa Terdaftar</h4>
                        <p class="text-xs text-zinc-500 max-w-sm mx-auto mt-1 mb-4">
                            Rombongan belajar ini belum memiliki peserta didik aktif. Tempatkan siswa yang belum terdaftar pada tahun ajaran ini.
                        </p>
                        <x-button
                            type="button"
                            variant="primary"
                            size="sm"
                            x-on:click="$dispatch('open-modal', 'add-student-modal')"
                        >
                            Tempatkan Siswa Sekarang
                        </x-button>
                    </div>
                @else
                    <div class="overflow-x-auto" data-scrollable>
                        <table class="w-full text-left text-xs text-zinc-600 min-w-[640px]">
                            <thead class="bg-zinc-50/80 text-zinc-500 font-semibold border-b border-zinc-200">
                                <tr>
                                    <th class="py-3 px-4 w-12 text-center">No</th>
                                    <th class="py-3 px-4">Nama Peserta Didik</th>
                                    <th class="py-3 px-4">NISN / Identitas</th>
                                    <th class="py-3 px-4">Tanggal Masuk</th>
                                    <th class="py-3 px-4">Status</th>
                                    <th class="py-3 px-4 text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-zinc-200">
                                @foreach ($activeStudents as $index => $item)
                                    <tr class="hover:bg-zinc-50/50 transition-colors">
                                        <td class="py-3 px-4 text-center text-zinc-400 font-medium">
                                            {{ $index + 1 }}
                                        </td>
                                        <td class="py-3 px-4">
                                            <div class="flex items-center gap-2.5">
                                                <div class="w-7 h-7 rounded-full bg-zinc-200 text-zinc-700 font-bold flex items-center justify-center text-xs shrink-0">
                                                    {{ substr($item->student->name, 0, 1) }}
                                                </div>
                                                <div>
                                                    <span class="font-bold text-zinc-900 block">{{ $item->student->name }}</span>
                                                    <span class="text-[11px] text-zinc-400 block">{{ $item->student->email }}</span>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="py-3 px-4 font-mono text-zinc-600">
                                            {{ $item->student->nisn ?? $item->student->username ?? '-' }}
                                        </td>
                                        <td class="py-3 px-4 text-zinc-600">
                                            {{ $item->joined_at ? $item->joined_at->translatedFormat('d M Y') : '-' }}
                                        </td>
                                        <td class="py-3 px-4">
                                            <x-badge variant="success" size="sm">
                                                {{ $item->statusLabel() }}
                                            </x-badge>
                                        </td>
                                        <td class="py-3 px-4 text-right">
                                            <div class="inline-flex items-center gap-2">
                                                <!-- Pindah Rombel -->
                                                <button
                                                    type="button"
                                                    class="px-2 py-1 text-xs font-semibold rounded border border-zinc-200 text-zinc-700 hover:bg-zinc-100 hover:text-zinc-900 transition-colors"
                                                    x-on:click="openTransfer({{ $item->student->id }}, '{{ addslashes($item->student->name) }}')"
                                                    title="Pindahkan siswa ke rombongan belajar lain"
                                                >
                                                    Pindah Kelas
                                                </button>

                                                <!-- Keluarkan Siswa -->
                                                <button
                                                    type="button"
                                                    class="px-2 py-1 text-xs font-semibold rounded border border-rose-200 text-rose-600 hover:bg-rose-50 transition-colors"
                                                    x-on:click="openRemove({{ $item->student->id }}, '{{ addslashes($item->student->name) }}')"
                                                    title="Keluarkan siswa dari rombongan belajar"
                                                >
                                                    Keluarkan
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </x-card>
        </div>

        <!-- SECTION 2: RIWAYAT PENEMPATAN & MUTASI KELAS -->
        <div class="space-y-3 pt-4">
            <div>
                <h2 class="text-base font-extrabold text-zinc-900 tracking-tight">
                    Riwayat Penempatan & Mutasi
                </h2>
                <p class="text-xs text-zinc-500">
                    Catatan historis peserta didik yang telah dipindahkan, mutasi, atau menyelesaikan masa belajar di kelas ini.
                </p>
            </div>

            <x-card :padding="false">
                @if ($historyStudents->isEmpty())
                    <div class="p-6 text-center text-xs text-zinc-400">
                        Belum ada riwayat mutasi atau pengeluaran siswa pada rombongan belajar ini.
                    </div>
                @else
                    <div class="overflow-x-auto" data-scrollable>
                        <table class="w-full text-left text-xs text-zinc-600 min-w-[550px]">
                            <thead class="bg-zinc-50/80 text-zinc-500 font-semibold border-b border-zinc-200">
                                <tr>
                                    <th class="py-3 px-4">Nama Peserta Didik</th>
                                    <th class="py-3 px-4">Status Akhir</th>
                                    <th class="py-3 px-4">Tanggal Masuk</th>
                                    <th class="py-3 px-4">Tanggal Keluar</th>
                                    <th class="py-3 px-4">Keterangan / Alasan</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-zinc-200">
                                @foreach ($historyStudents as $history)
                                    <tr class="hover:bg-zinc-50/50">
                                        <td class="py-3 px-4">
                                            <span class="font-bold text-zinc-800 block">{{ $history->student->name }}</span>
                                            <span class="text-[11px] text-zinc-400 block">{{ $history->student->email }}</span>
                                        </td>
                                        <td class="py-3 px-4">
                                            <x-badge :variant="$history->statusBadgeVariant()" size="sm">
                                                {{ $history->statusLabel() }}
                                            </x-badge>
                                        </td>
                                        <td class="py-3 px-4 text-zinc-600">
                                            {{ $history->joined_at ? $history->joined_at->translatedFormat('d M Y') : '-' }}
                                        </td>
                                        <td class="py-3 px-4 text-zinc-600">
                                            {{ $history->left_at ? $history->left_at->translatedFormat('d M Y') : '-' }}
                                        </td>
                                        <td class="py-3 px-4 text-zinc-500 italic">
                                            {{ $history->notes ?: '-' }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </x-card>
        </div>

        <!-- ====================================================================== -->
        <!-- MODAL 1: TEMPATKAN SISWA BARU                                          -->
        <!-- ====================================================================== -->
        <x-modal name="add-student-modal" title="Tempatkan Peserta Didik Baru" maxWidth="lg">
            <form method="POST" action="{{ route('classrooms.students.store', $classroom) }}" class="space-y-4">
                @csrf

                <div>
                    <p class="text-xs text-zinc-500 mb-3">
                        Tempatkan siswa ke dalam rombongan belajar <strong>{{ $classroom->name }}</strong> untuk Tahun Ajaran <strong>{{ $classroom->academicYear->name }}</strong>.
                    </p>

                    @if ($availableStudents->isEmpty())
                        <div class="p-3 bg-amber-50 border border-amber-200 rounded-lg text-xs text-amber-800">
                            Tidak ada siswa yang siap ditempatkan. Semua siswa aktif telah memiliki rombongan belajar pada tahun ajaran ini, atau belum ada akun siswa aktif terdaftar.
                        </div>
                    @else
                        <!-- Pilih Siswa -->
                        <x-select
                            label="Pilih Siswa"
                            name="student_id"
                            required
                            hint="Hanya menampilkan siswa yang belum memiliki rombel aktif di T.A. ini."
                        >
                            <option value="">-- Pilih Peserta Didik --</option>
                            @foreach ($availableStudents as $std)
                                <option value="{{ $std->id }}">
                                    {{ $std->name }} ({{ $std->email }})
                                </option>
                            @endforeach
                        </x-select>
                    @endif
                </div>

                @if ($availableStudents->isNotEmpty())
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <!-- Tanggal Masuk -->
                        <x-input
                            type="date"
                            label="Tanggal Penempatan"
                            name="joined_at"
                            value="{{ date('Y-m-d') }}"
                            required
                        />

                        <!-- Catatan -->
                        <x-input
                            label="Catatan (Opsional)"
                            name="notes"
                            placeholder="Catatan penempatan..."
                        />
                    </div>
                @endif

                <div class="pt-2 flex items-center justify-end gap-2 border-t border-zinc-100">
                    <x-button
                        type="button"
                        variant="secondary"
                        size="sm"
                        x-on:click="$dispatch('close-modal', 'add-student-modal')"
                    >
                        Batal
                    </x-button>

                    @if ($availableStudents->isNotEmpty())
                        <x-button type="submit" variant="primary" size="sm">
                            Simpan Penempatan
                        </x-button>
                    @endif
                </div>
            </form>
        </x-modal>

        <!-- ====================================================================== -->
        <!-- MODAL 2: PINDAHKAN SISWA KE KELAS LAIN                                  -->
        <!-- ====================================================================== -->
        <x-modal name="transfer-student-modal" title="Pindahkan Peserta Didik" maxWidth="lg">
            <form method="POST" :action="transferAction" class="space-y-4">
                @csrf

                <div>
                    <p class="text-xs text-zinc-600 mb-3">
                        Pindahkan peserta didik <strong x-text="activeStudentName"></strong> dari kelas <strong>{{ $classroom->name }}</strong> ke rombongan belajar lain pada Tahun Ajaran yang sama.
                    </p>

                    @if ($otherClassrooms->isEmpty())
                        <div class="p-3 bg-amber-50 border border-amber-200 rounded-lg text-xs text-amber-800">
                            Tidak ada rombongan belajar lain yang aktif pada tahun ajaran <strong>{{ $classroom->academicYear->name }}</strong>. Buat rombel baru terlebih dahulu untuk memindahkan siswa.
                        </div>
                    @else
                        <!-- Rombel Tujuan -->
                        <x-select
                            label="Rombongan Belajar Tujuan"
                            name="target_classroom_id"
                            required
                        >
                            <option value="">-- Pilih Rombel Tujuan --</option>
                            @foreach ($otherClassrooms as $targetClass)
                                <option value="{{ $targetClass->id }}">
                                    {{ $targetClass->name }} ({{ $targetClass->gradeLevel?->displayLabel() ?? '-' }}) &bull; Sisa {{ max(0, $targetClass->capacity - $targetClass->activeStudents()->count()) }} kursi
                                </option>
                            @endforeach
                        </x-select>
                    @endif
                </div>

                @if ($otherClassrooms->isNotEmpty())
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <x-input
                            type="date"
                            label="Tanggal Efektif Pindah"
                            name="effective_date"
                            value="{{ date('Y-m-d') }}"
                            required
                        />

                        <x-input
                            label="Alasan Pemindahan"
                            name="notes"
                            placeholder="Alasan kepindahan kelas..."
                            required
                        />
                    </div>
                @endif

                <div class="pt-2 flex items-center justify-end gap-2 border-t border-zinc-100">
                    <x-button
                        type="button"
                        variant="secondary"
                        size="sm"
                        x-on:click="$dispatch('close-modal', 'transfer-student-modal')"
                    >
                        Batal
                    </x-button>

                    @if ($otherClassrooms->isNotEmpty())
                        <x-button type="submit" variant="primary" size="sm">
                            Konfirmasi Pemindahan
                        </x-button>
                    @endif
                </div>
            </form>
        </x-modal>

        <!-- ====================================================================== -->
        <!-- MODAL 3: KELUARKAN SISWA DARI KELAS                                    -->
        <!-- ====================================================================== -->
        <x-modal name="remove-student-modal" title="Keluarkan Peserta Didik" maxWidth="md">
            <form method="POST" :action="removeAction" class="space-y-4">
                @csrf
                @method('DELETE')

                <div class="space-y-3">
                    <p class="text-xs text-zinc-600">
                        Anda akan mengeluarkan <strong x-text="activeStudentName"></strong> dari keanggotaan aktif kelas <strong>{{ $classroom->name }}</strong>. Catatan historis akan tetap tersimpan di riwayat penempatan.
                    </p>

                    <x-select
                        label="Status Pengeluaran"
                        name="status"
                        required
                    >
                        <option value="dropped">Dikeluarkan / Mengundurkan Diri (Dropped)</option>
                        <option value="completed">Telah Menyelesaikan / Lulus (Completed)</option>
                    </x-select>

                    <x-input
                        label="Alasan / Catatan Pengeluaran"
                        name="notes"
                        placeholder="Contoh: Mengundurkan diri, pindah sekolah..."
                    />
                </div>

                <div class="pt-2 flex items-center justify-end gap-2 border-t border-zinc-100">
                    <x-button
                        type="button"
                        variant="secondary"
                        size="sm"
                        x-on:click="$dispatch('close-modal', 'remove-student-modal')"
                    >
                        Batal
                    </x-button>

                    <x-button type="submit" variant="danger" size="sm">
                        Keluarkan dari Kelas
                    </x-button>
                </div>
            </form>
        </x-modal>
    </div>
</x-layouts.app>
