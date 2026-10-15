<x-layouts.app>
    <x-slot:headerTitle>
        Tambah Skema Bobot Penilaian
    </x-slot:headerTitle>

    <div class="max-w-3xl mx-auto space-y-6">
        <div>
            <a href="{{ route('reports.weights.index') }}" class="inline-flex items-center text-xs font-semibold text-zinc-500 hover:text-zinc-800 transition-colors mb-2">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Kembali ke Daftar Bobot
            </a>
            <h1 class="text-xl sm:text-2xl font-extrabold text-zinc-900 tracking-tight">
                Buat Skema Bobot Penilaian
            </h1>
            <p class="text-xs text-zinc-500 mt-0.5">
                Pastikan total penjumlahan bobot (Tugas + Kuis + UTS + UAS) berjumlah tepat 100%.
            </p>
        </div>

        <form action="{{ route('reports.weights.store') }}" method="POST" x-data="{
            tugas: 30,
            kuis: 20,
            uts: 25,
            uas: 25,
            get total() {
                return (parseFloat(this.tugas) || 0) + (parseFloat(this.kuis) || 0) + (parseFloat(this.uts) || 0) + (parseFloat(this.uas) || 0);
            }
        }">
            @csrf

            <x-card padding class="border-zinc-200 space-y-5">
                <div>
                    <x-label for="name" value="Nama Skema Bobot" required />
                    <x-input id="name" name="name" type="text" class="mt-1" placeholder="Misal: Standar Umum SMA / Kurikulum Merdeka" value="{{ old('name') }}" required />
                    @error('name')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <x-label for="academic_year_id" value="Tahun Ajaran (Opsional)" />
                        <x-select id="academic_year_id" name="academic_year_id" class="mt-1">
                            <option value="">Semua Tahun Ajaran</option>
                            @foreach($academicYears as $year)
                                <option value="{{ $year->id }}" {{ old('academic_year_id') == $year->id ? 'selected' : '' }}>{{ $year->name }}</option>
                            @endforeach
                        </x-select>
                    </div>

                    <div>
                        <x-label for="semester_id" value="Semester (Opsional)" />
                        <x-select id="semester_id" name="semester_id" class="mt-1">
                            <option value="">Semua Semester</option>
                            @foreach($semesters as $semester)
                                <option value="{{ $semester->id }}" {{ old('semester_id') == $semester->id ? 'selected' : '' }}>{{ $semester->name }}</option>
                            @endforeach
                        </x-select>
                    </div>

                    <div>
                        <x-label for="subject_id" value="Mata Pelajaran (Opsional)" />
                        <x-select id="subject_id" name="subject_id" class="mt-1">
                            <option value="">Semua Mata Pelajaran</option>
                            @foreach($subjects as $subject)
                                <option value="{{ $subject->id }}" {{ old('subject_id') == $subject->id ? 'selected' : '' }}>{{ $subject->name }}</option>
                            @endforeach
                        </x-select>
                    </div>
                </div>

                <div class="pt-4 border-t border-zinc-200">
                    <h2 class="text-sm font-bold text-zinc-900 mb-3">Rincian Persentase Bobot Komponen</h2>

                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                        <div>
                            <x-label for="assignment_weight" value="Bobot Tugas (%)" required />
                            <x-input id="assignment_weight" name="assignment_weight" type="number" step="0.01" min="0" max="100" class="mt-1 text-center font-bold" x-model.number="tugas" required />
                            @error('assignment_weight')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <x-label for="quiz_weight" value="Bobot Kuis (%)" required />
                            <x-input id="quiz_weight" name="quiz_weight" type="number" step="0.01" min="0" max="100" class="mt-1 text-center font-bold" x-model.number="kuis" required />
                            @error('quiz_weight')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <x-label for="midterm_weight" value="Bobot UTS (%)" required />
                            <x-input id="midterm_weight" name="midterm_weight" type="number" step="0.01" min="0" max="100" class="mt-1 text-center font-bold" x-model.number="uts" required />
                            @error('midterm_weight')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <x-label for="final_weight" value="Bobot UAS (%)" required />
                            <x-input id="final_weight" name="final_weight" type="number" step="0.01" min="0" max="100" class="mt-1 text-center font-bold" x-model.number="uas" required />
                            @error('final_weight')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- Live Total Calculation Bar -->
                    <div class="mt-4 p-3 rounded-lg flex items-center justify-between" :class="total === 100 ? 'bg-emerald-50 text-emerald-800 border border-emerald-200' : 'bg-red-50 text-red-800 border border-red-200'">
                        <div class="text-xs font-semibold">
                            Total Penjumlahan Bobot: <span class="text-sm font-extrabold" x-text="total + '%'"></span>
                        </div>
                        <div class="text-xs">
                            <span x-show="total === 100" class="font-bold text-emerald-700">✓ Sesuai (100%)</span>
                            <span x-show="total !== 100" class="font-bold text-red-700" x-text="'Selisih: ' + (100 - total) + '%'"></span>
                        </div>
                    </div>
                </div>

                <div class="pt-4 border-t border-zinc-200 grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <x-label for="passing_grade" value="Batas Ketuntasan Minimal (KKM)" required />
                        <x-input id="passing_grade" name="passing_grade" type="number" step="0.01" min="0" max="100" class="mt-1 text-center font-bold text-brand-700" value="{{ old('passing_grade', 75) }}" required />
                        @error('passing_grade')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex items-center pt-6">
                        <label class="inline-flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" name="is_active" value="1" class="rounded border-zinc-300 text-brand-600 focus:ring-brand-500" checked>
                            <span class="text-xs font-semibold text-zinc-700">Aktifkan skema bobot ini</span>
                        </label>
                    </div>
                </div>

                <div class="pt-4 flex items-center justify-end gap-3">
                    <x-button href="{{ route('reports.weights.index') }}" variant="ghost" size="sm">
                        Batal
                    </x-button>
                    <x-button type="submit" variant="primary" size="sm">
                        Simpan Konfigurasi
                    </x-button>
                </div>
            </x-card>
        </form>
    </div>
</x-layouts.app>
