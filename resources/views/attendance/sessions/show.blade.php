<x-layouts.app>
    <x-slot:headerTitle>
        Lembar Presensi Pertemuan Ke-{{ $session->meeting_number }}
    </x-slot:headerTitle>

    <div
        x-data="{
            reopenModalOpen: false,
            correctionModalOpen: false,

            markAllPresent() {
                const inputs = document.querySelectorAll('input[type=radio][value=present]');
                inputs.forEach(input => {
                    input.checked = true;
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

        <!-- Session Header Bar -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <div class="flex items-center gap-2 text-xs text-zinc-500 mb-1">
                    <a href="{{ route('attendance-sessions.index') }}" class="hover:text-zinc-800">&larr; Kembali ke Daftar Sesi</a>
                    <span>&bull;</span>
                    <span>Kelas {{ $session->classroom?->name }}</span>
                    <span>&bull;</span>
                    <span>T.A. {{ $session->academicYear?->name }}</span>
                </div>
                <div class="flex items-center gap-3">
                    <h1 class="text-xl sm:text-2xl font-extrabold text-zinc-900 tracking-tight">
                        Pertemuan Ke-{{ $session->meeting_number }}: {{ $session->teachingAssignment?->subject?->name }}
                    </h1>
                    @if ($session->isFinalized())
                        <x-badge variant="success" size="sm">Terfinalisasi</x-badge>
                    @else
                        <x-badge variant="warning" size="sm">Draft</x-badge>
                    @endif
                </div>
                <p class="text-xs text-zinc-500 mt-1">
                    Tanggal: <strong>{{ $session->date->translatedFormat('l, d F Y') }}</strong>
                    @if ($session->start_time)
                        &bull; Waktu: <strong>{{ substr($session->start_time, 0, 5) }} - {{ substr($session->end_time ?? '', 0, 5) }} WIB</strong>
                    @endif
                    &bull; Pengampu: <strong>{{ $session->teacher?->name }}</strong>
                </p>
            </div>

            <div class="flex items-center gap-2">
                @if ($session->isFinalized())
                    <x-button
                        type="button"
                        variant="secondary"
                        size="sm"
                        x-on:click="$dispatch('open-modal', 'reopen-session-modal')"
                    >
                        Buka Ulang Sesi
                    </x-button>
                @endif

                <x-button href="{{ route('attendance.report', ['teaching_assignment_id' => $session->teaching_assignment_id]) }}" variant="outline" size="sm">
                    Lihat Rekap Kelas
                </x-button>
            </div>
        </div>

        <!-- 5-Card Metis Metric Summary Strip -->
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3">
            <x-card padding class="border-zinc-200">
                <div class="text-[10px] font-bold uppercase tracking-wider text-emerald-600">Hadir</div>
                <div class="mt-1 text-2xl font-extrabold text-emerald-700">{{ $summary['present'] }}</div>
                <div class="text-[10px] text-zinc-400">Siswa hadir</div>
            </x-card>

            <x-card padding class="border-zinc-200">
                <div class="text-[10px] font-bold uppercase tracking-wider text-amber-600">Terlambat</div>
                <div class="mt-1 text-2xl font-extrabold text-amber-700">{{ $summary['late'] }}</div>
                <div class="text-[10px] text-zinc-400">Masuk terlambat</div>
            </x-card>

            <x-card padding class="border-zinc-200">
                <div class="text-[10px] font-bold uppercase tracking-wider text-blue-600">Izin</div>
                <div class="mt-1 text-2xl font-extrabold text-blue-700">{{ $summary['permit'] }}</div>
                <div class="text-[10px] text-zinc-400">Keterangan izin</div>
            </x-card>

            <x-card padding class="border-zinc-200">
                <div class="text-[10px] font-bold uppercase tracking-wider text-purple-600">Sakit</div>
                <div class="mt-1 text-2xl font-extrabold text-purple-700">{{ $summary['sick'] }}</div>
                <div class="text-[10px] text-zinc-400">Surat keterangan</div>
            </x-card>

            <x-card padding class="border-zinc-200">
                <div class="text-[10px] font-bold uppercase tracking-wider text-rose-600">Alpa</div>
                <div class="mt-1 text-2xl font-extrabold text-rose-700">{{ $summary['absent'] }}</div>
                <div class="text-[10px] text-zinc-400">Tanpa keterangan</div>
            </x-card>

            <x-card padding class="border-zinc-200">
                <div class="text-[10px] font-bold uppercase tracking-wider text-brand-600">Tingkat Kehadiran</div>
                <div class="mt-1 text-2xl font-extrabold text-brand-700">{{ $summary['rate'] }}%</div>
                <div class="text-[10px] text-zinc-400">{{ $summary['present'] + $summary['late'] }}/{{ $summary['total'] }} siswa</div>
            </x-card>
        </div>

        @if ($session->topic)
            <div class="p-3 bg-zinc-50 border border-zinc-200 rounded-xl text-xs text-zinc-700 flex items-start gap-2.5">
                <svg class="w-4 h-4 text-zinc-400 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                </svg>
                <div>
                    <span class="font-bold text-zinc-900">Pokok Bahasan / Materi:</span> {{ $session->topic }}
                    @if ($session->notes)
                        <span class="text-zinc-500 block mt-0.5">{{ $session->notes }}</span>
                    @endif
                </div>
            </div>
        @endif

        @if ($session->isFinalized())
            <div class="p-3 bg-emerald-50 border border-emerald-200 rounded-xl text-xs text-emerald-800 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span>Sesi ini telah difinalisasi pada <strong>{{ $session->finalized_at?->translatedFormat('d F Y H:i') }}</strong> oleh <strong>{{ $session->finalizer?->name ?? 'Pengampu' }}</strong>. Untuk mengoreksi presensi, gunakan form di bawah dan sertakan alasan koreksi.</span>
                </div>
            </div>
        @endif

        <!-- Attendance Sheet Form -->
        <form method="POST" action="{{ route('attendance-sessions.update', $session) }}" class="space-y-6">
            @csrf
            @method('PUT')

            <x-card :padding="false">
                <div class="p-4 border-b border-zinc-200 flex flex-col sm:flex-row sm:items-center justify-between gap-3 bg-zinc-50/50">
                    <div>
                        <h2 class="text-sm font-bold text-zinc-900">
                            Lembar Absensi Peserta Didik ({{ $records->count() }} Siswa)
                        </h2>
                        <p class="text-[11px] text-zinc-500">
                            Pilih status kehadiran setiap peserta didik pada sesi pertemuan ini.
                        </p>
                    </div>

                    <button
                        type="button"
                        x-on:click="markAllPresent()"
                        class="px-3 py-1.5 text-xs font-semibold rounded-lg bg-emerald-50 text-emerald-700 border border-emerald-200 hover:bg-emerald-100 transition-colors inline-flex items-center gap-1.5 self-start sm:self-auto"
                    >
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        Tandai Semua Hadir
                    </button>
                </div>

                <div class="overflow-x-auto" data-scrollable>
                    <table class="w-full min-w-[680px] text-left text-xs text-zinc-600">
                        <thead class="bg-zinc-50/80 text-zinc-500 font-semibold border-b border-zinc-200">
                            <tr>
                                <th class="py-3 px-4 w-12 text-center">No</th>
                                <th class="py-3 px-4">Nama Peserta Didik</th>
                                <th class="py-3 px-4">NISN / Identitas</th>
                                <th class="py-3 px-4">Status Kehadiran</th>
                                <th class="py-3 px-4">Catatan / Keterangan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-200">
                            @foreach ($records as $index => $record)
                                <tr class="hover:bg-zinc-50/50 transition-colors">
                                    <td class="py-3 px-4 text-center text-zinc-400 font-medium">
                                        {{ $index + 1 }}
                                        <input type="hidden" name="records[{{ $index }}][student_id]" value="{{ $record->student_id }}">
                                    </td>
                                    <td class="py-3 px-4">
                                        <div class="flex items-center gap-2">
                                            <div class="w-6 h-6 rounded-full bg-zinc-200 text-zinc-700 font-bold flex items-center justify-center text-[10px] shrink-0">
                                                {{ substr($record->student->name, 0, 1) }}
                                            </div>
                                            <div>
                                                <span class="font-bold text-zinc-900 block">{{ $record->student->name }}</span>
                                                <span class="text-[10px] text-zinc-400 block">{{ $record->student->email }}</span>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="py-3 px-4 font-mono text-zinc-600">
                                        {{ $record->student->nisn ?? $record->student->username ?? '-' }}
                                    </td>
                                    <td class="py-3 px-4">
                                        <div class="inline-flex items-center gap-1.5 flex-wrap">
                                            <!-- Hadir (H) -->
                                            <label class="cursor-pointer inline-flex items-center gap-1 px-2.5 py-1 rounded-md border border-zinc-200 text-zinc-700 has-checked:bg-emerald-50 has-checked:border-emerald-500 has-checked:text-emerald-800 transition-colors">
                                                <input
                                                    type="radio"
                                                    name="records[{{ $index }}][status]"
                                                    value="present"
                                                    {{ $record->status === 'present' ? 'checked' : '' }}
                                                    class="text-emerald-600 focus:ring-emerald-500 w-3 h-3"
                                                >
                                                <span class="font-bold">H</span>
                                                <span class="text-[10px] text-zinc-400">Hadir</span>
                                            </label>

                                            <!-- Terlambat (T) -->
                                            <label class="cursor-pointer inline-flex items-center gap-1 px-2.5 py-1 rounded-md border border-zinc-200 text-zinc-700 has-checked:bg-amber-50 has-checked:border-amber-500 has-checked:text-amber-800 transition-colors">
                                                <input
                                                    type="radio"
                                                    name="records[{{ $index }}][status]"
                                                    value="late"
                                                    {{ $record->status === 'late' ? 'checked' : '' }}
                                                    class="text-amber-600 focus:ring-amber-500 w-3 h-3"
                                                >
                                                <span class="font-bold">T</span>
                                                <span class="text-[10px] text-zinc-400">Terlambat</span>
                                            </label>

                                            <!-- Izin (I) -->
                                            <label class="cursor-pointer inline-flex items-center gap-1 px-2.5 py-1 rounded-md border border-zinc-200 text-zinc-700 has-checked:bg-blue-50 has-checked:border-blue-500 has-checked:text-blue-800 transition-colors">
                                                <input
                                                    type="radio"
                                                    name="records[{{ $index }}][status]"
                                                    value="permit"
                                                    {{ $record->status === 'permit' ? 'checked' : '' }}
                                                    class="text-blue-600 focus:ring-blue-500 w-3 h-3"
                                                >
                                                <span class="font-bold">I</span>
                                                <span class="text-[10px] text-zinc-400">Izin</span>
                                            </label>

                                            <!-- Sakit (S) -->
                                            <label class="cursor-pointer inline-flex items-center gap-1 px-2.5 py-1 rounded-md border border-zinc-200 text-zinc-700 has-checked:bg-purple-50 has-checked:border-purple-500 has-checked:text-purple-800 transition-colors">
                                                <input
                                                    type="radio"
                                                    name="records[{{ $index }}][status]"
                                                    value="sick"
                                                    {{ $record->status === 'sick' ? 'checked' : '' }}
                                                    class="text-purple-600 focus:ring-purple-500 w-3 h-3"
                                                >
                                                <span class="font-bold">S</span>
                                                <span class="text-[10px] text-zinc-400">Sakit</span>
                                            </label>

                                            <!-- Alpa (A) -->
                                            <label class="cursor-pointer inline-flex items-center gap-1 px-2.5 py-1 rounded-md border border-zinc-200 text-zinc-700 has-checked:bg-rose-50 has-checked:border-rose-500 has-checked:text-rose-800 transition-colors">
                                                <input
                                                    type="radio"
                                                    name="records[{{ $index }}][status]"
                                                    value="absent"
                                                    {{ $record->status === 'absent' ? 'checked' : '' }}
                                                    class="text-rose-600 focus:ring-rose-500 w-3 h-3"
                                                >
                                                <span class="font-bold">A</span>
                                                <span class="text-[10px] text-zinc-400">Alpa</span>
                                            </label>
                                        </div>
                                    </td>
                                    <td class="py-3 px-4">
                                        <input
                                            type="text"
                                            name="records[{{ $index }}][notes]"
                                            value="{{ $record->notes }}"
                                            placeholder="Catatan surat / alasan..."
                                            class="w-full text-xs rounded border border-zinc-200 py-1 px-2 text-zinc-800 placeholder-zinc-400 focus:border-brand-500 focus:ring-1 focus:ring-brand-500"
                                        >
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @if ($session->isFinalized())
                    <!-- Input Alasan Koreksi Jika Sesi Sudah Final -->
                    <div class="p-4 bg-zinc-50 border-t border-zinc-200">
                        <label class="block text-xs font-bold text-zinc-800 mb-1">
                            Alasan Koreksi Presensi (Wajib jika mengubah status pada sesi final):
                        </label>
                        <input
                            type="text"
                            name="correction_reason"
                            placeholder="Contoh: Konfirmasi surat sakit dari orang tua siswa, ralat kehadiran..."
                            class="w-full text-xs rounded-lg border border-zinc-300 py-2 px-3 text-zinc-800 placeholder-zinc-400 focus:border-brand-500 focus:ring-1 focus:ring-brand-500"
                        >
                    </div>
                @endif

                <div class="p-4 border-t border-zinc-200 bg-white flex flex-col sm:flex-row items-center justify-between gap-3">
                    <div class="text-xs text-zinc-500">
                        {{ $session->isFinalized() ? 'Perubahan pada sesi final akan dicatat pada riwayat audit.' : 'Sesi draft dapat disimpan berkali-kali sebelum difinalisasi.' }}
                    </div>

                    <div class="flex items-center gap-2">
                        @if (! $session->isFinalized())
                            <x-button type="submit" name="action" value="save_draft" variant="secondary" size="md">
                                Simpan Draft
                            </x-button>
                            <x-button type="submit" name="action" value="finalize" variant="primary" size="md">
                                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                                Simpan & Finalisasi
                            </x-button>
                        @else
                            <x-button type="submit" name="action" value="finalize" variant="primary" size="md">
                                Simpan Koreksi Presensi
                            </x-button>
                        @endif
                    </div>
                </div>
            </x-card>
        </form>

        <!-- SECTION RIWAYAT KOREKSI / AUDIT TRAIL -->
        @if ($auditLogs->isNotEmpty())
            <div class="space-y-3 pt-4">
                <div>
                    <h2 class="text-base font-extrabold text-zinc-900 tracking-tight">
                        Riwayat Koreksi Presensi (Audit Trail)
                    </h2>
                    <p class="text-xs text-zinc-500">
                        Catatan historis perubahan status kehadiran setelah presensi difinalisasi.
                    </p>
                </div>

                <x-card :padding="false">
                    <div class="overflow-x-auto" data-scrollable>
                        <table class="w-full text-left text-xs text-zinc-600 min-w-[620px]">
                            <thead class="bg-zinc-50/80 text-zinc-500 font-semibold border-b border-zinc-200">
                                <tr>
                                    <th class="py-3 px-4">Waktu Koreksi</th>
                                    <th class="py-3 px-4">Peserta Didik</th>
                                    <th class="py-3 px-4">Perubahan Status</th>
                                    <th class="py-3 px-4">Pengubah</th>
                                    <th class="py-3 px-4">Alasan Koreksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-zinc-200">
                                @foreach ($auditLogs as $log)
                                    <tr class="hover:bg-zinc-50/50">
                                        <td class="py-3 px-4 font-mono text-zinc-500">
                                            {{ $log->created_at->translatedFormat('d M Y H:i:s') }}
                                        </td>
                                        <td class="py-3 px-4 font-bold text-zinc-800">
                                            {{ $log->record?->student?->name ?? 'Siswa' }}
                                        </td>
                                        <td class="py-3 px-4">
                                            <span class="inline-flex items-center gap-1.5">
                                                <span class="px-2 py-0.5 rounded bg-zinc-100 text-zinc-700 font-semibold">{{ ucfirst($log->previous_status) }}</span>
                                                <span class="text-zinc-400">&rarr;</span>
                                                <span class="px-2 py-0.5 rounded bg-emerald-100 text-emerald-800 font-semibold">{{ ucfirst($log->new_status) }}</span>
                                            </span>
                                        </td>
                                        <td class="py-3 px-4 text-zinc-700">
                                            {{ $log->user?->name }}
                                        </td>
                                        <td class="py-3 px-4 text-zinc-600 italic">
                                            {{ $log->reason ?: '-' }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </x-card>
            </div>
        @endif

        <!-- ====================================================================== -->
        <!-- MODAL BUKA ULANG SESI (REOPEN)                                         -->
        <!-- ====================================================================== -->
        <x-modal name="reopen-session-modal" title="Buka Ulang Sesi Presensi" maxWidth="md">
            <form method="POST" action="{{ route('attendance-sessions.reopen', $session) }}" class="space-y-4">
                @csrf

                <div class="space-y-3">
                    <p class="text-xs text-zinc-600">
                        Membuka kembali sesi presensi <strong>Pertemuan Ke-{{ $session->meeting_number }}</strong> akan mengembalikan status sesi menjadi <strong>Draft</strong>.
                    </p>

                    <x-input
                        label="Alasan Buka Ulang Sesi"
                        name="reason"
                        placeholder="Contoh: Perbaikan data massal, siswa susulan..."
                        required
                    />
                </div>

                <div class="pt-2 flex items-center justify-end gap-2 border-t border-zinc-100">
                    <x-button
                        type="button"
                        variant="secondary"
                        size="sm"
                        x-on:click="$dispatch('close-modal', 'reopen-session-modal')"
                    >
                        Batal
                    </x-button>

                    <x-button type="submit" variant="primary" size="sm">
                        Konfirmasi Buka Ulang
                    </x-button>
                </div>
            </form>
        </x-modal>
    </div>
</x-layouts.app>
