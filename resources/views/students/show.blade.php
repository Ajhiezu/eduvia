<x-layouts.app>
    <x-slot:headerTitle>
        Profil Peserta Didik
    </x-slot:headerTitle>

    <div
        x-data="{
            changeStatusModalOpen: false,
            transferModalOpen: false,
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

        <!-- Header Bar -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <div class="flex items-center gap-2 text-xs text-zinc-500 mb-1">
                    <a href="{{ route('students.index') }}" class="hover:text-zinc-800">&larr; Kembali ke Daftar Siswa</a>
                    <span>&bull;</span>
                    <span>Status: {{ $profile?->statusLabel() ?? 'Aktif' }}</span>
                </div>
                <div class="flex items-center gap-3">
                    <h1 class="text-xl sm:text-2xl font-extrabold text-zinc-900 tracking-tight">
                        {{ $student->name }}
                    </h1>
                    <x-badge :variant="$profile?->statusBadgeVariant() ?? 'success'" size="sm">
                        {{ $profile?->statusLabel() ?? 'Aktif' }}
                    </x-badge>
                </div>
                <p class="text-xs text-zinc-500 mt-0.5">
                    Email: <strong>{{ $student->email }}</strong> &bull;
                    NIS: <strong>{{ $profile?->nis ?: '-' }}</strong> &bull;
                    NISN: <strong>{{ $profile?->nisn ?: '-' }}</strong>
                </p>
            </div>

            <div class="flex items-center gap-2 flex-wrap">
                <x-button
                    type="button"
                    variant="secondary"
                    size="sm"
                    x-on:click="$dispatch('open-modal', 'change-status-modal')"
                >
                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                    </svg>
                    Ubah Status Siswa
                </x-button>

                @if ($currentEnrollment)
                    <x-button
                        type="button"
                        variant="secondary"
                        size="sm"
                        x-on:click="$dispatch('open-modal', 'transfer-classroom-modal')"
                    >
                        <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
                        </svg>
                        Pindah Kelas
                    </x-button>
                @endif

                <x-button href="{{ route('students.edit', $student) }}" variant="primary" size="sm">
                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                    </svg>
                    Edit Profil
                </x-button>
            </div>
        </div>

        <!-- 4-Card Summary Strip -->
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
            <x-card padding class="border-zinc-200">
                <div class="text-[11px] font-bold uppercase tracking-wider text-zinc-400">Status Kesiswaan</div>
                <div class="mt-1 text-base font-extrabold text-zinc-900">
                    {{ $profile?->statusLabel() ?? 'Aktif' }}
                </div>
                <div class="mt-1 text-xs text-zinc-400">
                    Akun Login: {{ $student->isActive() ? 'Aktif' : 'Non-Aktif' }}
                </div>
            </x-card>

            <x-card padding class="border-zinc-200">
                <div class="text-[11px] font-bold uppercase tracking-wider text-zinc-400">Kelas Aktif Saat Ini</div>
                <div class="mt-1 text-base font-extrabold text-zinc-900 truncate">
                    {{ $currentEnrollment?->classroom?->name ?? 'Belum Ada' }}
                </div>
                <div class="mt-1 text-xs text-zinc-400 truncate">
                    {{ $currentEnrollment?->academicYear?->name ? "T.A. {$currentEnrollment->academicYear->name}" : 'Belum Terdaftar Rombel' }}
                </div>
            </x-card>

            <x-card padding class="border-zinc-200">
                <div class="text-[11px] font-bold uppercase tracking-wider text-brand-600">Kehadiran Presensi</div>
                <div class="mt-1 text-2xl font-extrabold text-brand-700">
                    {{ $attendanceRate }}%
                </div>
                <div class="mt-1 text-[11px] text-zinc-400">
                    {{ $present + $late }}/{{ $totalSessions }} sesi pertemuan
                </div>
            </x-card>

            <x-card padding class="border-zinc-200">
                <div class="text-[11px] font-bold uppercase tracking-wider text-zinc-400">Tanggal Masuk</div>
                <div class="mt-1 text-base font-extrabold text-zinc-900">
                    {{ $profile?->enrollment_date ? $profile->enrollment_date->translatedFormat('d M Y') : '-' }}
                </div>
                <div class="mt-1 text-xs text-zinc-400">Jalur Terdaftar</div>
            </x-card>
        </div>

        <!-- SECTION 1: DATA INDUK & KONTAK LENGKAP -->
        <x-card title="Data Induk Kesiswaan & Kontak" padding>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 text-xs">
                <div>
                    <span class="text-zinc-400 block font-medium">Jenis Kelamin</span>
                    <span class="text-zinc-900 font-bold text-sm">{{ $profile?->genderLabel() ?: '-' }}</span>
                </div>

                <div>
                    <span class="text-zinc-400 block font-medium">Tempat, Tanggal Lahir</span>
                    <span class="text-zinc-900 font-bold text-sm">
                        {{ $profile?->birth_place ?: '-' }}, {{ $profile?->birth_date ? $profile->birth_date->translatedFormat('d F Y') : '-' }}
                    </span>
                </div>

                <div>
                    <span class="text-zinc-400 block font-medium">Agama</span>
                    <span class="text-zinc-900 font-bold text-sm">{{ $profile?->religion ?: '-' }}</span>
                </div>

                <div>
                    <span class="text-zinc-400 block font-medium">Nama Orang Tua / Wali</span>
                    <span class="text-zinc-900 font-bold text-sm">{{ $profile?->parent_name ?: '-' }}</span>
                    @if ($profile?->parent_relationship)
                        <span class="text-zinc-500 text-[11px]">({{ $profile->parent_relationship }})</span>
                    @endif
                </div>

                <div>
                    <span class="text-zinc-400 block font-medium">Telepon Siswa / Wali</span>
                    <span class="text-zinc-900 font-bold text-sm font-mono">
                        {{ $profile?->parent_phone ?: ($student->phone ?: '-') }}
                    </span>
                </div>

                <div>
                    <span class="text-zinc-400 block font-medium">Tanggal Kelulusan</span>
                    <span class="text-zinc-900 font-bold text-sm">
                        {{ $profile?->graduation_date ? $profile->graduation_date->translatedFormat('d M Y') : 'Belum Lulus' }}
                    </span>
                </div>

                <div class="sm:col-span-2 lg:col-span-3 pt-2 border-t border-zinc-100">
                    <span class="text-zinc-400 block font-medium">Alamat Tempat Tinggal</span>
                    <span class="text-zinc-900 font-semibold">{{ $profile?->address ?: '-' }}</span>
                </div>

                @if ($profile?->notes)
                    <div class="sm:col-span-2 lg:col-span-3">
                        <span class="text-zinc-400 block font-medium">Catatan Siswa</span>
                        <span class="text-zinc-700 italic">{{ $profile->notes }}</span>
                    </div>
                @endif
            </div>
        </x-card>

        <!-- SECTION 2: RIWAYAT KELAS & ROMBONGAN BELAJAR -->
        <div class="space-y-3">
            <div>
                <h2 class="text-base font-extrabold text-zinc-900 tracking-tight">
                    Riwayat Rombongan Belajar
                </h2>
                <p class="text-xs text-zinc-500">
                    Rekam jejak penempatan dan mutasi kelas peserta didik dari waktu ke waktu.
                </p>
            </div>

            <x-card :padding="false">
                @if ($student->classroomEnrollments->isEmpty())
                    <div class="p-6 text-center text-xs text-zinc-400">
                        Belum ada riwayat penempatan kelas untuk siswa ini.
                    </div>
                @else
                    <div class="overflow-x-auto" data-scrollable>
                        <table class="w-full min-w-[650px] text-left text-xs text-zinc-600">
                            <thead class="bg-zinc-50/80 text-zinc-500 font-semibold border-b border-zinc-200">
                                <tr>
                                    <th class="py-3 px-4">Tahun Ajaran</th>
                                    <th class="py-3 px-4">Rombongan Belajar</th>
                                    <th class="py-3 px-4">Tingkat Kelas</th>
                                    <th class="py-3 px-4">Tanggal Masuk</th>
                                    <th class="py-3 px-4">Tanggal Keluar</th>
                                    <th class="py-3 px-4">Status Penempatan</th>
                                    <th class="py-3 px-4">Keterangan</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-zinc-200">
                                @foreach ($student->classroomEnrollments as $enrollment)
                                    <tr class="hover:bg-zinc-50/50">
                                        <td class="py-3 px-4 font-semibold text-zinc-900">
                                            {{ $enrollment->academicYear?->name }}
                                        </td>
                                        <td class="py-3 px-4 font-bold text-zinc-800">
                                            {{ $enrollment->classroom?->name }}
                                        </td>
                                        <td class="py-3 px-4 text-zinc-600">
                                            {{ $enrollment->classroom?->gradeLevel?->displayLabel() }}
                                        </td>
                                        <td class="py-3 px-4 text-zinc-600">
                                            {{ $enrollment->joined_at ? $enrollment->joined_at->translatedFormat('d M Y') : '-' }}
                                        </td>
                                        <td class="py-3 px-4 text-zinc-600">
                                            {{ $enrollment->left_at ? $enrollment->left_at->translatedFormat('d M Y') : '-' }}
                                        </td>
                                        <td class="py-3 px-4">
                                            <x-badge :variant="$enrollment->statusBadgeVariant()" size="sm">
                                                {{ $enrollment->statusLabel() }}
                                            </x-badge>
                                        </td>
                                        <td class="py-3 px-4 text-zinc-500 italic max-w-xs truncate" title="{{ $enrollment->notes }}">
                                            {{ $enrollment->notes ?: '-' }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </x-card>
        </div>

        <!-- SECTION 3: RIWAYAT STATUS & MUTASI KESISWAAN -->
        <div class="space-y-3 pt-2">
            <div>
                <h2 class="text-base font-extrabold text-zinc-900 tracking-tight">
                    Riwayat Perubahan Status Kesiswaan
                </h2>
                <p class="text-xs text-zinc-500">
                    Catatan audit setiap mutasi status (Aktif, Pindah, Lulus, Keluar) beserta alasan resmi.
                </p>
            </div>

            <x-card :padding="false">
                @if ($student->studentStatusHistories->isEmpty())
                    <div class="p-6 text-center text-xs text-zinc-400">
                        Belum ada riwayat perubahan status tercatat.
                    </div>
                @else
                    <div class="overflow-x-auto" data-scrollable>
                        <table class="w-full min-w-[550px] text-left text-xs text-zinc-600">
                            <thead class="bg-zinc-50/80 text-zinc-500 font-semibold border-b border-zinc-200">
                                <tr>
                                    <th class="py-3 px-4">Tanggal Efektif</th>
                                    <th class="py-3 px-4">Perubahan Status</th>
                                    <th class="py-3 px-4">Alasan / Keterangan</th>
                                    <th class="py-3 px-4">Petugas / Aktor</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-zinc-200">
                                @foreach ($student->studentStatusHistories as $history)
                                    <tr class="hover:bg-zinc-50/50">
                                        <td class="py-3 px-4 font-mono text-zinc-700">
                                            {{ $history->effective_date->translatedFormat('d M Y') }}
                                        </td>
                                        <td class="py-3 px-4">
                                            <span class="inline-flex items-center gap-1.5 font-bold">
                                                <span class="px-2 py-0.5 rounded bg-zinc-100 text-zinc-700">{{ $history->previousStatusLabel() }}</span>
                                                <span class="text-zinc-400">&rarr;</span>
                                                <span class="px-2 py-0.5 rounded bg-emerald-100 text-emerald-800">{{ $history->newStatusLabel() }}</span>
                                            </span>
                                        </td>
                                        <td class="py-3 px-4 text-zinc-700">
                                            {{ $history->reason ?: '-' }}
                                        </td>
                                        <td class="py-3 px-4 text-zinc-600">
                                            {{ $history->actor?->name ?? 'Sistem' }}
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
        <!-- MODAL 1: UBAH STATUS SISWA                                             -->
        <!-- ====================================================================== -->
        <x-modal name="change-status-modal" title="Ubah Status Kesiswaan" maxWidth="md">
            <form method="POST" action="{{ route('students.status.update', $student) }}" class="space-y-4">
                @csrf

                <div class="space-y-3">
                    <p class="text-xs text-zinc-600">
                        Ubah status kesiswaan peserta didik <strong>{{ $student->name }}</strong>. Histori akademik dan nilai tidak akan terhapus.
                    </p>

                    <x-select
                        label="Status Kesiswaan Baru"
                        name="status"
                        required
                    >
                        <option value="active" {{ $profile?->student_status === 'active' ? 'selected' : '' }}>Aktif</option>
                        <option value="transferred" {{ $profile?->student_status === 'transferred' ? 'selected' : '' }}>Pindah / Mutasi</option>
                        <option value="graduated" {{ $profile?->student_status === 'graduated' ? 'selected' : '' }}>Lulus</option>
                        <option value="dropped" {{ $profile?->student_status === 'dropped' ? 'selected' : '' }}>Keluar / Dropped</option>
                        <option value="inactive" {{ $profile?->student_status === 'inactive' ? 'selected' : '' }}>Non-Aktif</option>
                    </x-select>

                    <x-input
                        type="date"
                        label="Tanggal Efektif"
                        name="effective_date"
                        value="{{ date('Y-m-d') }}"
                        required
                    />

                    <x-textarea
                        label="Alasan Perubahan Status"
                        name="reason"
                        placeholder="Contoh: Pindah sekolah ke luar provinsi, surat pengunduran diri orang tua..."
                        rows="2"
                        required
                    />
                </div>

                <div class="pt-2 flex items-center justify-end gap-2 border-t border-zinc-100">
                    <x-button
                        type="button"
                        variant="secondary"
                        size="sm"
                        x-on:click="$dispatch('close-modal', 'change-status-modal')"
                    >
                        Batal
                    </x-button>

                    <x-button type="submit" variant="primary" size="sm">
                        Simpan Status Baru
                    </x-button>
                </div>
            </form>
        </x-modal>

        <!-- ====================================================================== -->
        <!-- MODAL 2: PINDAH KELAS (MUTASI ROMBEL)                                  -->
        <!-- ====================================================================== -->
        @if ($currentEnrollment)
            <x-modal name="transfer-classroom-modal" title="Pindah Rombongan Belajar" maxWidth="md">
                <form method="POST" action="{{ route('classrooms.students.transfer', [$currentEnrollment->classroom_id, $student]) }}" class="space-y-4">
                    @csrf

                    <div class="space-y-3">
                        <p class="text-xs text-zinc-600">
                            Pindahkan <strong>{{ $student->name }}</strong> dari kelas <strong>{{ $currentEnrollment->classroom?->name }}</strong> ke rombel tujuan.
                        </p>

                        <x-select
                            label="Rombongan Belajar Tujuan"
                            name="target_classroom_id"
                            required
                        >
                            <option value="">-- Pilih Rombel Tujuan --</option>
                            @foreach ($otherClassrooms as $tc)
                                <option value="{{ $tc->id }}">
                                    {{ $tc->name }} ({{ $tc->gradeLevel?->displayLabel() }}) &bull; Sisa {{ max(0, $tc->capacity - $tc->activeStudentCount()) }} kursi
                                </option>
                            @endforeach
                        </x-select>

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
                            placeholder="Alasan mutasi kelas..."
                            required
                        />
                    </div>

                    <div class="pt-2 flex items-center justify-end gap-2 border-t border-zinc-100">
                        <x-button
                            type="button"
                            variant="secondary"
                            size="sm"
                            x-on:click="$dispatch('close-modal', 'transfer-classroom-modal')"
                        >
                            Batal
                        </x-button>

                        <x-button type="submit" variant="primary" size="sm">
                            Konfirmasi Pemindahan
                        </x-button>
                    </div>
                </form>
            </x-modal>
        @endif
    </div>
</x-layouts.app>
