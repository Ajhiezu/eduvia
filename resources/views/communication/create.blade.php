<x-layouts.app>
    <x-slot:headerTitle>
        Mulai Percakapan Konsultasi Baru
    </x-slot:headerTitle>

    <div class="max-w-3xl mx-auto space-y-6">

        <div class="flex items-center justify-between pb-4 border-b border-zinc-200">
            <div>
                <a href="{{ route('conversations.index') }}" class="text-xs text-zinc-500 hover:text-zinc-900 inline-flex items-center gap-1 font-semibold">
                    &larr; Kembali ke Daftar Percakapan
                </a>
                <h1 class="text-xl sm:text-2xl font-extrabold text-zinc-900 mt-1">
                    Mulai Percakapan Konsultasi
                </h1>
                <p class="text-xs text-zinc-500">
                    Kirimkan pesan konsultasi akademik atau perkembangan siswa secara aman dan terhubung
                </p>
            </div>
        </div>

        <div class="bg-white rounded-2xl border border-zinc-200 shadow-xs p-6">
            <form action="{{ route('conversations.store') }}" method="POST" enctype="multipart/form-data" class="space-y-5">
                @csrf

                @if (auth()->user()->hasRole(\App\Enums\UserRole::PARENT))
                    <!-- Parent Perspective -->
                    <div>
                        <label class="block text-xs font-semibold text-zinc-700 mb-1">Pilih Siswa (Anak yang Dikonsultasikan) *</label>
                        <select name="student_id" required class="w-full text-xs rounded-xl border-zinc-300 focus:border-[#E11D2E] focus:ring-[#E11D2E]">
                            @foreach ($activeChildren as $ch)
                                <option value="{{ $ch->id }}" {{ request('child_id') == $ch->id ? 'selected' : '' }}>
                                    {{ $ch->name }} (Kelas: {{ $ch->classroomEnrollments->first()?->classroom?->name ?? '-' }})
                                </option>
                            @endforeach
                        </select>
                        @error('student_id')
                            <p class="text-xs text-rose-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-zinc-700 mb-1">Pilih Guru Tujuan (Wali Kelas / Guru Pengajar Sah) *</label>
                        <select name="recipient_id" required class="w-full text-xs rounded-xl border-zinc-300 focus:border-[#E11D2E] focus:ring-[#E11D2E]">
                            <option value="">-- Pilih Tenaga Pendidik --</option>
                            @foreach ($eligibleTeachers as $t)
                                <option value="{{ $t->id }}" {{ old('recipient_id') == $t->id ? 'selected' : '' }}>
                                    {{ $t->name }} ({{ $t->email }})
                                </option>
                            @endforeach
                        </select>
                        @error('recipient_id')
                            <p class="text-xs text-rose-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                @else
                    <!-- Teacher / Admin Perspective -->
                    <div>
                        <label class="block text-xs font-semibold text-zinc-700 mb-1">Pilih Orang Tua / Wali Murid Terverifikasi *</label>
                        <select name="recipient_id" required class="w-full text-xs rounded-xl border-zinc-300 focus:border-[#E11D2E] focus:ring-[#E11D2E]">
                            <option value="">-- Pilih Orang Tua Murid --</option>
                            @foreach ($eligibleParents as $rel)
                                <option value="{{ $rel->parent_id }}" {{ old('recipient_id') == $rel->parent_id ? 'selected' : '' }}>
                                    {{ $rel->parent?->name }} (Wali dari: {{ $rel->student?->name }})
                                </option>
                            @endforeach
                        </select>
                        @error('recipient_id')
                            <p class="text-xs text-rose-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-zinc-700 mb-1">Pilih Siswa Terkait *</label>
                        <select name="student_id" required class="w-full text-xs rounded-xl border-zinc-300 focus:border-[#E11D2E] focus:ring-[#E11D2E]">
                            <option value="">-- Pilih Siswa --</option>
                            @foreach ($eligibleParents as $rel)
                                <option value="{{ $rel->student_id }}" {{ old('student_id') == $rel->student_id ? 'selected' : '' }}>
                                    {{ $rel->student?->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('student_id')
                            <p class="text-xs text-rose-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                @endif

                <div>
                    <label class="block text-xs font-semibold text-zinc-700 mb-1">Subjek / Topik Konsultasi *</label>
                    <input
                        type="text"
                        name="subject"
                        value="{{ old('subject') }}"
                        required
                        placeholder="Contoh: Konsultasi Evaluasi Nilai Tugas Matematika Bab 2"
                        class="w-full text-xs rounded-xl border-zinc-300 focus:border-[#E11D2E] focus:ring-[#E11D2E]"
                    >
                    @error('subject')
                        <p class="text-xs text-rose-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-xs font-semibold text-zinc-700 mb-1">Isi Pesan Awal *</label>
                    <textarea
                        name="body"
                        rows="5"
                        required
                        placeholder="Tuliskan pertanyaan, tanggapan, atau konsultasi Anda dengan sopan..."
                        class="w-full text-xs rounded-xl border-zinc-300 focus:border-[#E11D2E] focus:ring-[#E11D2E]"
                    >{{ old('body') }}</textarea>
                    @error('body')
                        <p class="text-xs text-rose-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-xs font-semibold text-zinc-700 mb-1">Berkas Lampiran Pendukung (Opsional - Dokumen/Foto Maks 5MB)</label>
                    <input
                        type="file"
                        name="attachment"
                        class="block w-full text-xs text-zinc-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-zinc-100 file:text-zinc-700 hover:file:bg-zinc-200"
                    >
                    @error('attachment')
                        <p class="text-xs text-rose-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="pt-4 border-t border-zinc-100 flex items-center justify-end gap-3">
                    <a href="{{ route('conversations.index') }}" class="px-4 py-2 rounded-xl text-xs font-semibold text-zinc-600 hover:bg-zinc-100">Batal</a>
                    <button type="submit" class="px-5 py-2.5 rounded-xl text-xs font-bold bg-[#E11D2E] hover:bg-[#C81423] text-white shadow-xs transition-colors">
                        Kirim Pesan
                    </button>
                </div>
            </form>
        </div>

    </div>
</x-layouts.app>
