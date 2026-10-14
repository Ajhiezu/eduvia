<x-layouts.app>
    <x-slot:headerTitle>
        Ubah Tahun Ajaran
    </x-slot:headerTitle>

    <div class="max-w-2xl mx-auto space-y-6">
        <div class="flex items-center gap-2 text-xs font-semibold text-slate-500">
            <a href="{{ route('academic-years.index') }}" class="hover:text-brand-600 transition-colors flex items-center gap-1">
                &larr; Kembali ke Daftar Tahun Ajaran
            </a>
        </div>

        @if ($errors->any())
            <x-alert variant="danger" title="Gagal Memperbarui Tahun Ajaran" dismissible>
                {{ $errors->first() }}
            </x-alert>
        @endif

        <x-card title="Ubah Data Tahun Ajaran" padding>
            <form method="POST" action="{{ route('academic-years.update', $academicYear) }}" class="space-y-5">
                @csrf
                @method('PUT')

                <x-input
                    label="Nama / Periode Tahun Ajaran"
                    name="name"
                    value="{{ old('name', $academicYear->name) }}"
                    required
                />

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <x-input
                        label="Tanggal Mulai"
                        name="start_date"
                        type="date"
                        value="{{ old('start_date', $academicYear->start_date->format('Y-m-d')) }}"
                        required
                    />

                    <x-input
                        label="Tanggal Berakhir"
                        name="end_date"
                        type="date"
                        value="{{ old('end_date', $academicYear->end_date->format('Y-m-d')) }}"
                        required
                        hint="Harus setelah tanggal mulai."
                    />
                </div>

                <div class="space-y-1.5">
                    <label for="description" class="text-sm font-semibold text-slate-700">Catatan / Deskripsi</label>
                    <textarea
                        name="description"
                        id="description"
                        rows="2"
                        class="w-full rounded-xl border border-slate-300 text-slate-900 placeholder:text-slate-400 text-sm py-2.5 px-3.5 focus:border-brand-500 focus:ring-2 focus:ring-brand-100 transition-colors"
                    >{{ old('description', $academicYear->description) }}</textarea>
                </div>

                <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-200 flex items-start gap-3">
                    <input
                        type="checkbox"
                        name="is_active"
                        id="isActiveInput"
                        value="1"
                        {{ old('is_active', $academicYear->is_active) ? 'checked' : '' }}
                        class="mt-0.5 w-4 h-4 rounded border-slate-300 text-brand-600 focus:ring-brand-500"
                    />
                    <label for="isActiveInput" class="text-xs text-slate-700 cursor-pointer select-none">
                        <span class="font-bold text-slate-900 block">Jadikan sebagai Tahun Ajaran Aktif Saat Ini</span>
                        <span>Jika diaktifkan, tahun ajaran aktif sebelumnya akan dinonaktifkan secara otomatis.</span>
                    </label>
                </div>

                <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-100">
                    <x-button href="{{ route('academic-years.index') }}" variant="outline" size="md">
                        Batal
                    </x-button>
                    <x-button type="submit" variant="primary" size="md">
                        Simpan Perubahan
                    </x-button>
                </div>
            </form>
        </x-card>
    </div>
</x-layouts.app>
