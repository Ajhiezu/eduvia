<x-layouts.app>
    <x-slot:headerTitle>
        Edit Nilai — {{ $gradebook->subject?->name }} ({{ $gradebook->classroom?->name }})
    </x-slot:headerTitle>

    <div class="space-y-6">
        <div>
            <a href="{{ route('reports.gradebook.show', $gradebook) }}" class="inline-flex items-center text-xs font-semibold text-zinc-500 hover:text-zinc-800 transition-colors mb-1.5">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Kembali ke Rekap Nilai
            </a>
            <h1 class="text-xl sm:text-2xl font-extrabold text-zinc-900 tracking-tight">
                Edit Nilai & Capaian Pembelajaran Siswa
            </h1>
            <p class="text-xs text-zinc-500 mt-0.5">
                Sesuaikan nilai tugas, kuis, UTS, UAS dan narasi capaian pembelajaran. Nilai akhir akan dihitung otomatis saat formulir disimpan.
            </p>
        </div>

        <form action="{{ route('reports.gradebook.update', $gradebook) }}" method="POST">
            @csrf
            @method('PUT')

            <x-card padding="none" class="border-zinc-200 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs text-zinc-700">
                        <thead class="bg-zinc-50 border-b border-zinc-200 text-zinc-500 font-bold uppercase text-[10px] tracking-wider">
                            <tr>
                                <th class="py-3 px-3 w-10 text-center">No</th>
                                <th class="py-3 px-3 w-48">Nama Siswa</th>
                                <th class="py-3 px-2 text-center w-24">Tugas</th>
                                <th class="py-3 px-2 text-center w-24">Kuis</th>
                                <th class="py-3 px-2 text-center w-24">UTS</th>
                                <th class="py-3 px-2 text-center w-24">UAS</th>
                                <th class="py-3 px-3 text-center w-20">Nilai Akhir Saat Ini</th>
                                <th class="py-3 px-4">Deskripsi Capaian Pembelajaran</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-200">
                            @foreach($gradebook->items as $index => $item)
                                <tr class="hover:bg-zinc-50/75 transition-colors">
                                    <td class="py-3 px-3 text-center text-zinc-400 font-medium">{{ $index + 1 }}</td>
                                    <td class="py-3 px-3 font-semibold text-zinc-900">
                                        <input type="hidden" name="items[{{ $index }}][id]" value="{{ $item->id }}">
                                        {{ $item->student?->name }}
                                        <div class="text-[11px] text-zinc-400 font-normal">NIS: {{ $item->student?->studentProfile?->nis ?? '-' }}</div>
                                    </td>
                                    <td class="py-2 px-2">
                                        <input
                                            type="number"
                                            step="0.01"
                                            min="0"
                                            max="100"
                                            name="items[{{ $index }}][assignment_avg]"
                                            value="{{ old("items.$index.assignment_avg", $item->assignment_avg) }}"
                                            class="w-full text-center text-xs font-bold py-1.5 px-2 border border-zinc-200 rounded-md focus:border-brand-500 focus:ring-1 focus:ring-brand-500"
                                            required
                                        >
                                    </td>
                                    <td class="py-2 px-2">
                                        <input
                                            type="number"
                                            step="0.01"
                                            min="0"
                                            max="100"
                                            name="items[{{ $index }}][quiz_avg]"
                                            value="{{ old("items.$index.quiz_avg", $item->quiz_avg) }}"
                                            class="w-full text-center text-xs font-bold py-1.5 px-2 border border-zinc-200 rounded-md focus:border-brand-500 focus:ring-1 focus:ring-brand-500"
                                            required
                                        >
                                    </td>
                                    <td class="py-2 px-2">
                                        <input
                                            type="number"
                                            step="0.01"
                                            min="0"
                                            max="100"
                                            name="items[{{ $index }}][midterm_score]"
                                            value="{{ old("items.$index.midterm_score", $item->midterm_score) }}"
                                            class="w-full text-center text-xs font-bold py-1.5 px-2 border border-zinc-200 rounded-md focus:border-brand-500 focus:ring-1 focus:ring-brand-500"
                                            required
                                        >
                                    </td>
                                    <td class="py-2 px-2">
                                        <input
                                            type="number"
                                            step="0.01"
                                            min="0"
                                            max="100"
                                            name="items[{{ $index }}][final_score_raw]"
                                            value="{{ old("items.$index.final_score_raw", $item->final_score_raw) }}"
                                            class="w-full text-center text-xs font-bold py-1.5 px-2 border border-zinc-200 rounded-md focus:border-brand-500 focus:ring-1 focus:ring-brand-500"
                                            required
                                        >
                                    </td>
                                    <td class="py-2 px-3 text-center font-extrabold text-sm text-zinc-800">
                                        {{ number_format($item->final_score, 1) }}
                                    </td>
                                    <td class="py-2 px-4">
                                        <textarea
                                            name="items[{{ $index }}][achievement_description]"
                                            rows="2"
                                            class="w-full text-xs py-1.5 px-2.5 border border-zinc-200 rounded-md focus:border-brand-500 focus:ring-1 focus:ring-brand-500"
                                            placeholder="Deskripsi pencapaian kompetensi siswa..."
                                        >{{ old("items.$index.achievement_description", $item->achievement_description) }}</textarea>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="p-4 bg-zinc-50 border-t border-zinc-200 flex items-center justify-between">
                    <span class="text-xs text-zinc-500">
                        Pastikan seluruh nilai berada dalam rentang 0 hingga 100.
                    </span>

                    <div class="flex items-center gap-3">
                        <x-button href="{{ route('reports.gradebook.show', $gradebook) }}" variant="ghost" size="sm">
                            Batal
                        </x-button>
                        <x-button type="submit" variant="primary" size="sm">
                            Simpan Perubahan Nilai
                        </x-button>
                    </div>
                </div>
            </x-card>
        </form>
    </div>
</x-layouts.app>
