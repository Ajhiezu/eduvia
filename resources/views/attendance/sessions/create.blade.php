<x-layouts.app>
    <x-slot:headerTitle>
        Buat Sesi Pertemuan Presensi
    </x-slot:headerTitle>

    <div class="max-w-3xl mx-auto space-y-6">
        <div>
            <div class="flex items-center gap-2 text-xs text-zinc-500 mb-1">
                <a href="{{ route('attendance-sessions.index') }}" class="hover:text-zinc-800">&larr; Kembali ke Daftar Sesi</a>
            </div>
            <h1 class="text-xl sm:text-2xl font-extrabold text-zinc-900 tracking-tight">
                Buka Sesi Pertemuan Presensi Baru
            </h1>
            <p class="text-xs text-zinc-500 mt-0.5">
                Pilih mata pelajaran dan rombongan belajar, lalu tentukan tanggal dan nomor pertemuan untuk mencatat kehadiran peserta didik.
            </p>
        </div>

        @if ($errors->any())
            <x-alert variant="danger" dismissible>
                {{ $errors->first() }}
            </x-alert>
        @endif

        <form method="POST" action="{{ route('attendance-sessions.store') }}" class="space-y-6">
            @csrf

            <x-card title="Informasi Sesi Pertemuan Pembelajaran" padding>
                <div class="space-y-4">
                    <!-- Penugasan Mengajar -->
                    <div>
                        <x-select
                            label="Mata Pelajaran & Rombongan Belajar"
                            name="teaching_assignment_id"
                            required
                        >
                            <option value="">-- Pilih Mata Pelajaran & Kelas --</option>
                            @foreach ($assignments as $a)
                                <option value="{{ $a->id }}" {{ old('teaching_assignment_id') == $a->id ? 'selected' : '' }}>
                                    {{ $a->subject?->name }} &bull; Kelas {{ $a->classroom?->name }} (T.A. {{ $a->academicYear?->name }})
                                </option>
                            @endforeach
                        </x-select>
                        @if ($assignments->isEmpty())
                            <p class="text-xs text-amber-600 mt-1">
                                Belum ada penugasan mengajar aktif untuk akun ini. Hubungi administrator sekolah untuk penugasan kelas.
                            </p>
                        @endif
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <!-- Nomor Pertemuan -->
                        <x-input
                            type="number"
                            label="Pertemuan Ke-"
                            name="meeting_number"
                            value="{{ old('meeting_number', 1) }}"
                            min="1"
                            max="100"
                            required
                            hint="Nomor urut tatap muka dalam semester ini."
                        />

                        <!-- Tanggal Pertemuan -->
                        <x-input
                            type="date"
                            label="Tanggal Pertemuan"
                            name="date"
                            value="{{ old('date', date('Y-m-d')) }}"
                            required
                        />
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <!-- Jam Mulai -->
                        <x-input
                            type="time"
                            label="Jam Mulai"
                            name="start_time"
                            value="{{ old('start_time', '07:30') }}"
                        />

                        <!-- Jam Selesai -->
                        <x-input
                            type="time"
                            label="Jam Selesai"
                            name="end_time"
                            value="{{ old('end_time', '09:00') }}"
                        />
                    </div>

                    <!-- Topik / Materi Pembelajaran -->
                    <x-input
                        label="Topik / Pokok Pembahasan (Opsional)"
                        name="topic"
                        placeholder="Contoh: Operasi Hitung Campuran, Teks Eksposisi Bab 1..."
                        value="{{ old('topic') }}"
                    />

                    <!-- Catatan Tambahan -->
                    <x-textarea
                        label="Catatan Sesi (Opsional)"
                        name="notes"
                        placeholder="Catatan pelaksanaan sesi, kendala, atau observasi..."
                        rows="2"
                    >{{ old('notes') }}</x-textarea>
                </div>
            </x-card>

            <div class="flex items-center justify-end gap-3">
                <x-button href="{{ route('attendance-sessions.index') }}" variant="secondary" size="md">
                    Batal
                </x-button>
                <x-button type="submit" variant="primary" size="md" :disabled="$assignments->isEmpty()">
                    Buka Sesi Presensi &rarr;
                </x-button>
            </div>
        </form>
    </div>
</x-layouts.app>
