<x-layouts.app>
    <x-slot:headerTitle>
        Tambah Semester
    </x-slot:headerTitle>

    <div class="max-w-2xl mx-auto space-y-6">
        <div class="flex items-center gap-2 text-xs font-semibold text-slate-500">
            <a href="{{ route('semesters.index') }}" class="hover:text-brand-600 transition-colors flex items-center gap-1">
                &larr; Kembali ke Daftar Semester
            </a>
        </div>

        @if ($errors->any())
            <x-alert variant="danger" title="Gagal Menyimpan Semester" dismissible>
                {{ $errors->first() }}
            </x-alert>
        @endif

        <x-card title="Informasi Semester Baru" padding>
            <form method="POST" action="{{ route('semesters.store') }}" class="space-y-5">
                @csrf

                <x-select
                    label="Tahun Ajaran Terkait"
                    name="academic_year_id"
                    required
                >
                    <option value="">-- Pilih Tahun Ajaran --</option>
                    @foreach ($academicYears as $year)
                        <option value="{{ $year->id }}" {{ (old('academic_year_id', $selectedYearId) == $year->id) ? 'selected' : '' }}>
                            {{ $year->name }} {{ $year->is_active ? '(Aktif Saat Ini)' : '' }}
                        </option>
                    @endforeach
                </x-select>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <x-input
                        label="Nama Semester"
                        name="name"
                        value="{{ old('name', 'Semester Ganjil') }}"
                        placeholder="Contoh: Semester Ganjil"
                        required
                    />

                    <x-select
                        label="Jenis Semester"
                        name="semester_type"
                        required
                    >
                        <option value="ganjil" {{ old('semester_type') === 'ganjil' ? 'selected' : '' }}>Semester Ganjil (1)</option>
                        <option value="genap" {{ old('semester_type') === 'genap' ? 'selected' : '' }}>Semester Genap (2)</option>
                    </x-select>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <x-input
                        label="Tanggal Mulai Semester"
                        name="start_date"
                        type="date"
                        value="{{ old('start_date') }}"
                        required
                    />

                    <x-input
                        label="Tanggal Berakhir Semester"
                        name="end_date"
                        type="date"
                        value="{{ old('end_date') }}"
                        required
                        hint="Harus setelah tanggal mulai."
                    />
                </div>

                <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-200 flex items-start gap-3">
                    <input
                        type="checkbox"
                        name="is_active"
                        id="isActiveSemester"
                        value="1"
                        {{ old('is_active') ? 'checked' : '' }}
                        class="mt-0.5 w-4 h-4 rounded border-slate-300 text-brand-600 focus:ring-brand-500"
                    />
                    <label for="isActiveSemester" class="text-xs text-slate-700 cursor-pointer select-none">
                        <span class="font-bold text-slate-900 block">Jadikan sebagai Semester Aktif</span>
                        <span>Jika diaktifkan, tahun ajaran terkait akan otomatis diaktifkan dan semester lain dinonaktifkan.</span>
                    </label>
                </div>

                <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-100">
                    <x-button href="{{ route('semesters.index') }}" variant="outline" size="md">
                        Batal
                    </x-button>
                    <x-button type="submit" variant="primary" size="md">
                        Simpan Semester
                    </x-button>
                </div>
            </form>
        </x-card>
    </div>
</x-layouts.app>
