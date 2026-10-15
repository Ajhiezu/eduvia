<x-layouts.app>
    <x-slot:headerTitle>
        Edit Catatan Wali Kelas — {{ $reportCard->student?->name }}
    </x-slot:headerTitle>

    <div class="max-w-4xl mx-auto space-y-6">
        <div>
            <a href="{{ route('reports.cards.show', $reportCard) }}" class="inline-flex items-center text-xs font-semibold text-zinc-500 hover:text-zinc-800 transition-colors mb-1.5">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Kembali ke Rapor Siswa
            </a>
            <h1 class="text-xl sm:text-2xl font-extrabold text-zinc-900 tracking-tight">
                Penilaian Nonakademik & Catatan Wali Kelas
            </h1>
            <p class="text-xs text-zinc-500 mt-0.5">
                Siswa: <span class="font-semibold text-zinc-800">{{ $reportCard->student?->name }}</span> ({{ $reportCard->classroom?->name }}) • Periode: {{ $reportCard->academicYear?->name }} ({{ $reportCard->semester?->name }})
            </p>
        </div>

        <form action="{{ route('reports.cards.update', $reportCard) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="space-y-6">
                <!-- 1. Attendance Summary -->
                <x-card padding class="border-zinc-200 space-y-4">
                    <div class="flex items-center justify-between pb-3 border-b border-zinc-200">
                        <div>
                            <h2 class="text-sm font-bold text-zinc-900">1. Rekapitulasi Presensi Semester</h2>
                            <p class="text-xs text-zinc-500 mt-0.5">Angka kehadiran ditarik secara otomatis dari modul presensi dan dapat disesuaikan bila perlu.</p>
                        </div>
                    </div>

                    @php
                        $att = $reportCard->attendance_summary ?? [];
                    @endphp
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                        <div>
                            <x-label for="att_present" value="Hadir (Sesi)" />
                            <x-input id="att_present" name="attendance_summary[present]" type="number" min="0" class="mt-1 text-center font-bold" value="{{ old('attendance_summary.present', $att['present'] ?? 0) }}" />
                        </div>
                        <div>
                            <x-label for="att_sick" value="Sakit (Hari)" />
                            <x-input id="att_sick" name="attendance_summary[sick]" type="number" min="0" class="mt-1 text-center font-bold" value="{{ old('attendance_summary.sick', $att['sick'] ?? 0) }}" />
                        </div>
                        <div>
                            <x-label for="att_permit" value="Izin (Hari)" />
                            <x-input id="att_permit" name="attendance_summary[permit]" type="number" min="0" class="mt-1 text-center font-bold" value="{{ old('attendance_summary.permit', $att['permit'] ?? 0) }}" />
                        </div>
                        <div>
                            <x-label for="att_absent" value="Tanpa Keterangan / Alpa (Hari)" />
                            <x-input id="att_absent" name="attendance_summary[absent]" type="number" min="0" class="mt-1 text-center font-bold text-red-600" value="{{ old('attendance_summary.absent', $att['absent'] ?? 0) }}" />
                        </div>
                    </div>
                </x-card>

                <!-- 2. Extracurriculars -->
                <x-card padding class="border-zinc-200 space-y-4">
                    <div class="flex items-center justify-between pb-3 border-b border-zinc-200">
                        <div>
                            <h2 class="text-sm font-bold text-zinc-900">2. Kegiatan Ekstrakurikuler</h2>
                            <p class="text-xs text-zinc-500 mt-0.5">Penilaian keikutsertaan kegiatan ekstrakurikuler yang diikuti siswa.</p>
                        </div>
                    </div>

                    @php
                        $ekskuls = $reportCard->extracurricular_evaluations ?? [];
                        // Ensure at least 2 rows for input
                        while(count($ekskuls) < 2) {
                            $ekskuls[] = ['name' => '', 'predicate' => 'A', 'description' => ''];
                        }
                    @endphp

                    <div class="space-y-3">
                        @foreach($ekskuls as $idx => $ekskul)
                            <div class="grid grid-cols-1 sm:grid-cols-12 gap-3 items-center p-3 rounded-lg bg-zinc-50 border border-zinc-200">
                                <div class="sm:col-span-5">
                                    <x-label :value="'Nama Ekstrakurikuler #'.($idx+1)" />
                                    <x-input name="extracurriculars[{{ $idx }}][name]" type="text" class="mt-1" placeholder="Contoh: Pramuka, PMR, Futsal" value="{{ old("extracurriculars.$idx.name", $ekskul['name'] ?? '') }}" />
                                </div>
                                <div class="sm:col-span-2">
                                    <x-label value="Predikat" />
                                    <x-select name="extracurriculars[{{ $idx }}][predicate]" class="mt-1 text-center font-bold">
                                        <option value="A" {{ ($ekskul['predicate'] ?? 'A') === 'A' ? 'selected' : '' }}>A (Sangat Baik)</option>
                                        <option value="B" {{ ($ekskul['predicate'] ?? '') === 'B' ? 'selected' : '' }}>B (Baik)</option>
                                        <option value="C" {{ ($ekskul['predicate'] ?? '') === 'C' ? 'selected' : '' }}>C (Cukup)</option>
                                    </x-select>
                                </div>
                                <div class="sm:col-span-5">
                                    <x-label value="Keterangan / Aktivitas" />
                                    <x-input name="extracurriculars[{{ $idx }}][description]" type="text" class="mt-1" placeholder="Contoh: Aktif mengikuti latihan rutin mingguan..." value="{{ old("extracurriculars.$idx.description", $ekskul['description'] ?? '') }}" />
                                </div>
                            </div>
                        @endforeach
                    </div>
                </x-card>

                <!-- 3. Homeroom Notes -->
                <x-card padding class="border-zinc-200 space-y-4">
                    <div class="flex items-center justify-between pb-3 border-b border-zinc-200">
                        <div>
                            <h2 class="text-sm font-bold text-zinc-900">3. Catatan Wali Kelas & Perkembangan Karakter</h2>
                            <p class="text-xs text-zinc-500 mt-0.5">Tuliskan pesan motivasi, apresiasi capaian, atau saran pengembangan karakter siswa.</p>
                        </div>
                    </div>

                    <div>
                        <x-textarea
                            name="homeroom_notes"
                            rows="4"
                            class="w-full text-xs"
                            placeholder="Contoh: Ananda memiliki antusiasme belajar yang sangat baik, tingkatkan ketelitian dalam pengerjaan soal eksakta dan terus kembangkan kepemimpinan..."
                        >{{ old('homeroom_notes', $reportCard->homeroom_notes) }}</x-textarea>
                    </div>
                </x-card>

                <div class="flex items-center justify-end gap-3">
                    <x-button href="{{ route('reports.cards.show', $reportCard) }}" variant="ghost" size="sm">
                        Batal
                    </x-button>
                    <x-button type="submit" variant="primary" size="sm">
                        Simpan Catatan & Nonakademik
                    </x-button>
                </div>
            </div>
        </form>
    </div>
</x-layouts.app>
