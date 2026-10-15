<x-layouts.app>
    <x-slot:headerTitle>
        Tautkan Hubungan Orang Tua & Siswa Manual
    </x-slot:headerTitle>

    <div class="max-w-2xl mx-auto space-y-6">

        <div class="flex items-center justify-between pb-4 border-b border-zinc-200">
            <div>
                <a href="{{ route('admin.parents.index') }}" class="text-xs text-zinc-500 hover:text-zinc-900 inline-flex items-center gap-1 font-semibold">
                    &larr; Kembali ke Daftar Relasi
                </a>
                <h1 class="text-xl sm:text-2xl font-extrabold text-zinc-900 mt-1">
                    Tautkan Hubungan Manual
                </h1>
                <p class="text-xs text-zinc-500">
                    Pilih akun orang tua dan siswa yang terdaftar untuk membuat relasi resmi
                </p>
            </div>
        </div>

        <div class="bg-white rounded-2xl border border-zinc-200 shadow-xs p-6">
            <form action="{{ route('admin.parents.store') }}" method="POST" class="space-y-4">
                @csrf

                <div>
                    <label class="block text-xs font-semibold text-zinc-700 mb-1">Pilih Akun Orang Tua / Wali *</label>
                    <select name="parent_id" required class="w-full text-xs rounded-xl border-zinc-300 focus:border-[#E11D2E] focus:ring-[#E11D2E]">
                        <option value="">-- Pilih Orang Tua / Wali --</option>
                        @foreach ($parents as $p)
                            <option value="{{ $p->id }}" {{ old('parent_id') == $p->id ? 'selected' : '' }}>
                                {{ $p->name }} ({{ $p->email }})
                            </option>
                        @endforeach
                    </select>
                    @error('parent_id')
                        <p class="text-xs text-rose-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-xs font-semibold text-zinc-700 mb-1">Pilih Akun Siswa (Anak) *</label>
                    <select name="student_id" required class="w-full text-xs rounded-xl border-zinc-300 focus:border-[#E11D2E] focus:ring-[#E11D2E]">
                        <option value="">-- Pilih Siswa --</option>
                        @foreach ($students as $s)
                            <option value="{{ $s->id }}" {{ old('student_id') == $s->id ? 'selected' : '' }}>
                                {{ $s->name }} (NISN: {{ $s->studentProfile?->nisn ?? '-' }})
                            </option>
                        @endforeach
                    </select>
                    @error('student_id')
                        <p class="text-xs text-rose-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-zinc-700 mb-1">Jenis Hubungan *</label>
                        <select name="relationship_type" required class="w-full text-xs rounded-xl border-zinc-300 focus:border-[#E11D2E] focus:ring-[#E11D2E]">
                            <option value="father" {{ old('relationship_type') === 'father' ? 'selected' : '' }}>Ayah Kandung</option>
                            <option value="mother" {{ old('relationship_type') === 'mother' ? 'selected' : '' }}>Ibu Kandung</option>
                            <option value="guardian" {{ old('relationship_type') === 'guardian' ? 'selected' : '' }}>Wali Murid</option>
                            <option value="other" {{ old('relationship_type') === 'other' ? 'selected' : '' }}>Keluarga / Lainnya</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-zinc-700 mb-1">Status Verifikasi *</label>
                        <select name="status" required class="w-full text-xs rounded-xl border-zinc-300 focus:border-[#E11D2E] focus:ring-[#E11D2E]">
                            <option value="active" {{ old('status') === 'active' ? 'selected' : '' }}>Langsung Aktif & Terverifikasi</option>
                            <option value="pending" {{ old('status') === 'pending' ? 'selected' : '' }}>Menunggu Verifikasi</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-zinc-700 mb-1">Catatan Tambahan (Opsional)</label>
                    <textarea name="notes" rows="2" placeholder="Catatan verifikasi dokumen / keterangan keluarga..." class="w-full text-xs rounded-xl border-zinc-300 focus:border-[#E11D2E] focus:ring-[#E11D2E]">{{ old('notes') }}</textarea>
                </div>

                <div class="pt-4 border-t border-zinc-100 flex items-center justify-end gap-3">
                    <a href="{{ route('admin.parents.index') }}" class="px-4 py-2 rounded-xl text-xs font-semibold text-zinc-600 hover:bg-zinc-100">Batal</a>
                    <button type="submit" class="px-5 py-2.5 rounded-xl text-xs font-bold bg-[#E11D2E] hover:bg-[#C81423] text-white shadow-xs transition-colors">
                        Simpan Relasi
                    </button>
                </div>
            </form>
        </div>

    </div>
</x-layouts.app>
