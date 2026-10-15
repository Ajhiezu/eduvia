<x-layouts.app>
    <x-slot:headerTitle>
        Mulai Percakapan Konsultasi Baru
    </x-slot:headerTitle>

    <div class="max-w-3xl mx-auto space-y-6 sm:space-y-8">

        <div class="flex items-center justify-between pb-4 border-b border-zinc-200">
            <div>
                <a href="{{ route('conversations.index') }}" class="text-xs text-zinc-500 hover:text-zinc-900 inline-flex items-center gap-1.5 font-semibold transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                    <span>Kembali ke Daftar Percakapan</span>
                </a>
                <h1 class="text-xl sm:text-2xl font-extrabold text-zinc-900 mt-2 tracking-tight">
                    Mulai Percakapan Konsultasi
                </h1>
                <p class="text-xs sm:text-sm text-zinc-500 mt-1">
                    Kirimkan pesan konsultasi akademik atau perkembangan siswa secara aman dan terhubung
                </p>
            </div>
        </div>

        <div class="bg-white rounded-3xl border border-zinc-200/90 shadow-sm p-6 sm:p-10">
            <form action="{{ route('conversations.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                @csrf

                @if (auth()->user()->hasRole(\App\Enums\UserRole::PARENT))
                    <!-- Parent Perspective -->
                    <div>
                        <label class="block text-xs sm:text-sm font-bold text-zinc-700 mb-2">
                            Pilih Siswa (Anak yang Dikonsultasikan) <span class="text-[#E11D2E]">*</span>
                        </label>
                        <select name="student_id" required class="w-full text-sm sm:text-base rounded-2xl border border-zinc-200 bg-zinc-50/70 px-4 py-3 sm:py-3.5 text-zinc-900 focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#E11D2E]/20 focus:border-[#E11D2E] transition-all">
                            @foreach ($activeChildren as $ch)
                                <option value="{{ $ch->id }}" {{ request('child_id') == $ch->id ? 'selected' : '' }}>
                                    {{ $ch->name }} (Kelas: {{ $ch->classroomEnrollments->first()?->classroom?->name ?? '-' }})
                                </option>
                            @endforeach
                        </select>
                        @error('student_id')
                            <p class="text-xs text-rose-600 mt-1.5 font-medium">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-xs sm:text-sm font-bold text-zinc-700 mb-2">
                            Pilih Guru Tujuan (Wali Kelas / Guru Pengajar Sah) <span class="text-[#E11D2E]">*</span>
                        </label>
                        <select name="recipient_id" required class="w-full text-sm sm:text-base rounded-2xl border border-zinc-200 bg-zinc-50/70 px-4 py-3 sm:py-3.5 text-zinc-900 focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#E11D2E]/20 focus:border-[#E11D2E] transition-all">
                            <option value="">-- Pilih Tenaga Pendidik --</option>
                            @foreach ($eligibleTeachers as $t)
                                <option value="{{ $t->id }}" {{ old('recipient_id') == $t->id ? 'selected' : '' }}>
                                    {{ $t->name }} ({{ $t->email }})
                                </option>
                            @endforeach
                        </select>
                        @error('recipient_id')
                            <p class="text-xs text-rose-600 mt-1.5 font-medium">{{ $message }}</p>
                        @enderror
                    </div>
                @else
                    <!-- Teacher / Admin Perspective -->
                    <div>
                        <label class="block text-xs sm:text-sm font-bold text-zinc-700 mb-2">
                            Pilih Orang Tua / Wali Murid Terverifikasi <span class="text-[#E11D2E]">*</span>
                        </label>
                        <select name="recipient_id" required class="w-full text-sm sm:text-base rounded-2xl border border-zinc-200 bg-zinc-50/70 px-4 py-3 sm:py-3.5 text-zinc-900 focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#E11D2E]/20 focus:border-[#E11D2E] transition-all">
                            <option value="">-- Pilih Orang Tua Murid --</option>
                            @foreach ($eligibleParents as $rel)
                                <option value="{{ $rel->parent_id }}" {{ old('recipient_id') == $rel->parent_id ? 'selected' : '' }}>
                                    {{ $rel->parent?->name }} (Wali dari: {{ $rel->student?->name }})
                                </option>
                            @endforeach
                        </select>
                        @error('recipient_id')
                            <p class="text-xs text-rose-600 mt-1.5 font-medium">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-xs sm:text-sm font-bold text-zinc-700 mb-2">
                            Pilih Siswa Terkait <span class="text-[#E11D2E]">*</span>
                        </label>
                        <select name="student_id" required class="w-full text-sm sm:text-base rounded-2xl border border-zinc-200 bg-zinc-50/70 px-4 py-3 sm:py-3.5 text-zinc-900 focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#E11D2E]/20 focus:border-[#E11D2E] transition-all">
                            <option value="">-- Pilih Siswa --</option>
                            @foreach ($eligibleParents as $rel)
                                <option value="{{ $rel->student_id }}" {{ old('student_id') == $rel->student_id ? 'selected' : '' }}>
                                    {{ $rel->student?->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('student_id')
                            <p class="text-xs text-rose-600 mt-1.5 font-medium">{{ $message }}</p>
                        @enderror
                    </div>
                @endif

                <div>
                    <label class="block text-xs sm:text-sm font-bold text-zinc-700 mb-2">
                        Subjek / Topik Konsultasi <span class="text-[#E11D2E]">*</span>
                    </label>
                    <input
                        type="text"
                        name="subject"
                        value="{{ old('subject') }}"
                        required
                        placeholder="Contoh: Konsultasi Evaluasi Nilai Tugas Matematika Bab 2"
                        class="w-full text-sm sm:text-base rounded-2xl border border-zinc-200 bg-zinc-50/70 px-4 py-3 sm:py-3.5 text-zinc-900 placeholder:text-zinc-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#E11D2E]/20 focus:border-[#E11D2E] transition-all"
                    >
                    @error('subject')
                        <p class="text-xs text-rose-600 mt-1.5 font-medium">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-xs sm:text-sm font-bold text-zinc-700 mb-2">
                        Isi Pesan Awal <span class="text-[#E11D2E]">*</span>
                    </label>
                    <textarea
                        name="body"
                        rows="5"
                        required
                        placeholder="Tuliskan pertanyaan, tanggapan, atau konsultasi Anda dengan sopan..."
                        class="w-full text-sm sm:text-base rounded-2xl border border-zinc-200 bg-zinc-50/70 px-4 py-3 sm:py-3.5 text-zinc-900 placeholder:text-zinc-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#E11D2E]/20 focus:border-[#E11D2E] transition-all leading-relaxed"
                    >{{ old('body') }}</textarea>
                    @error('body')
                        <p class="text-xs text-rose-600 mt-1.5 font-medium">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-xs sm:text-sm font-bold text-zinc-700 mb-2">
                        Berkas Lampiran Pendukung <span class="text-zinc-400 font-normal">(Opsional - PDF, DOCX, Foto Maks 5MB)</span>
                    </label>
                    <input
                        type="file"
                        name="attachment"
                        class="block w-full text-xs sm:text-sm text-zinc-500 rounded-2xl border border-dashed border-zinc-300 bg-zinc-50/70 p-3 hover:bg-zinc-50 file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-zinc-800 file:text-white hover:file:bg-zinc-900 cursor-pointer transition-colors"
                    >
                    @error('attachment')
                        <p class="text-xs text-rose-600 mt-1.5 font-medium">{{ $message }}</p>
                    @enderror
                </div>

                <div class="pt-6 border-t border-zinc-100 flex items-center justify-end gap-3.5">
                    <a href="{{ route('conversations.index') }}" class="px-5 py-2.5 rounded-xl text-xs sm:text-sm font-semibold text-zinc-600 hover:bg-zinc-100 transition-colors">
                        Batal
                    </a>
                    <button type="submit" class="px-6 py-3 rounded-xl text-xs sm:text-sm font-bold bg-[#E11D2E] hover:bg-[#C81423] text-white shadow-xs transition-colors flex items-center gap-2">
                        <span>Kirim Pesan</span>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                    </button>
                </div>
            </form>
        </div>

    </div>
</x-layouts.app>
