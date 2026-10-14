<x-layouts.app>
    <x-slot:headerTitle>
        Tambah Rombongan Belajar
    </x-slot:headerTitle>

    <div class="max-w-3xl mx-auto space-y-6">
        <div>
            <div class="flex items-center gap-2 text-xs text-zinc-500 mb-1">
                <a href="{{ route('classrooms.index') }}" class="hover:text-zinc-800">&larr; Kembali ke Daftar Rombel</a>
            </div>
            <h1 class="text-xl sm:text-2xl font-extrabold text-zinc-900 tracking-tight">
                Buat Rombongan Belajar Baru
            </h1>
            <p class="text-xs text-zinc-500 mt-0.5">
                Definisikan kelompok kelas operasional, tentukan tingkat kelas, tahun ajaran, dan penugasan wali kelas.
            </p>
        </div>

        @if ($errors->any())
            <x-alert variant="danger" dismissible>
                {{ $errors->first() }}
            </x-alert>
        @endif

        <form method="POST" action="{{ route('classrooms.store') }}" class="space-y-6">
            @csrf

            <x-card title="Informasi Rombongan Belajar" padding>
                <div class="space-y-4">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <!-- Nama Rombel -->
                        <x-input
                            label="Nama Rombongan Belajar"
                            name="name"
                            placeholder="Contoh: VII-A, X-MIPA-1, XII-RPL-1"
                            value="{{ old('name') }}"
                            required
                        />

                        <!-- Kode Rombel -->
                        <x-input
                            label="Kode Rombel (Opsional)"
                            name="code"
                            placeholder="Contoh: CLS-7A"
                            value="{{ old('code') }}"
                        />
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <!-- Tahun Ajaran -->
                        <x-select
                            label="Tahun Ajaran"
                            name="academic_year_id"
                            required
                        >
                            <option value="">-- Pilih Tahun Ajaran --</option>
                            @foreach ($academicYears as $year)
                                <option value="{{ $year->id }}" {{ old('academic_year_id') == $year->id || (old('academic_year_id') === null && $year->is_active) ? 'selected' : '' }}>
                                    {{ $year->name }} {{ $year->is_active ? '(Aktif Saat Ini)' : '' }}
                                </option>
                            @endforeach
                        </x-select>

                        <!-- Tingkat Kelas -->
                        <x-select
                            label="Tingkat Kelas (Grade Level)"
                            name="grade_level_id"
                            required
                        >
                            <option value="">-- Pilih Tingkat Kelas --</option>
                            @foreach ($gradeLevels as $gl)
                                <option value="{{ $gl->id }}" {{ old('grade_level_id') == $gl->id ? 'selected' : '' }}>
                                    {{ $gl->displayLabel() }}
                                </option>
                            @endforeach
                        </x-select>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <!-- Wali Kelas -->
                        <x-select
                            label="Wali Kelas (Homeroom Teacher)"
                            name="homeroom_teacher_id"
                            hint="Dapat ditetapkan sekarang atau disesuaikan nanti."
                        >
                            <option value="">-- Belum Ditetapkan --</option>
                            @foreach ($teachers as $teacher)
                                <option value="{{ $teacher->id }}" {{ old('homeroom_teacher_id') == $teacher->id ? 'selected' : '' }}>
                                    {{ $teacher->name }} ({{ $teacher->email }})
                                </option>
                            @endforeach
                        </x-select>

                        <!-- Kapasitas Maksimal -->
                        <x-input
                            type="number"
                            label="Kapasitas Maksimal Siswa"
                            name="capacity"
                            value="{{ old('capacity', 36) }}"
                            min="1"
                            max="100"
                            hint="Batas daya tampung siswa (bawaan: 36 siswa)."
                        />
                    </div>

                    <!-- Catatan -->
                    <x-textarea
                        label="Catatan / Keterangan Tambahan"
                        name="notes"
                        placeholder="Informasi peminatan, ruangan kelas, atau kebijakan rombel..."
                        rows="3"
                    >{{ old('notes') }}</x-textarea>

                    <!-- Status Aktif -->
                    <div class="pt-2">
                        <label class="inline-flex items-center gap-2 cursor-pointer">
                            <input
                                type="checkbox"
                                name="is_active"
                                value="1"
                                {{ old('is_active', true) ? 'checked' : '' }}
                                class="rounded border-zinc-300 text-brand-600 focus:ring-brand-500 w-4 h-4"
                            >
                            <span class="text-xs font-semibold text-zinc-700">Rombongan belajar aktif operasional</span>
                        </label>
                    </div>
                </div>
            </x-card>

            <div class="flex items-center justify-end gap-3">
                <x-button href="{{ route('classrooms.index') }}" variant="secondary" size="md">
                    Batal
                </x-button>
                <x-button type="submit" variant="primary" size="md">
                    Simpan Rombongan Belajar
                </x-button>
            </div>
        </form>
    </div>
</x-layouts.app>
