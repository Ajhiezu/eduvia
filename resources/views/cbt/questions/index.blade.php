<x-layouts.app>
    <x-slot:headerTitle>
        Bank Soal CBT
    </x-slot:headerTitle>

    <div class="space-y-6">
        @if (session('success'))
            <x-alert variant="success" dismissible>
                {{ session('success') }}
            </x-alert>
        @endif

        @if (session('error'))
            <x-alert variant="danger" dismissible>
                {{ session('error') }}
            </x-alert>
        @endif

        <!-- Action & Navigation Header -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h1 class="text-xl sm:text-2xl font-extrabold text-zinc-900 tracking-tight">
                    Bank Soal Ujian (CBT)
                </h1>
                <p class="text-xs text-zinc-500 mt-0.5">
                    Kelola butir soal terstandar, opsi jawaban, kunci jawaban, dan bobot nilai untuk asesmen digital sekolah.
                </p>
            </div>

            <div class="flex items-center gap-2.5">
                <x-button href="{{ route('exam-questions.create') }}" variant="primary" size="sm">
                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Tambah Butir Soal
                </x-button>
            </div>
        </div>

        <!-- 3-Card Summary Strip -->
        <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
            <x-card padding class="border-zinc-200">
                <div class="text-[11px] font-bold uppercase tracking-wider text-zinc-400">Total Soal</div>
                <div class="mt-1 text-2xl font-extrabold text-zinc-900">{{ $stats['total'] }}</div>
                <div class="mt-1 text-xs text-zinc-400">Seluruh bank soal aktif</div>
            </x-card>

            <x-card padding class="border-zinc-200">
                <div class="text-[11px] font-bold uppercase tracking-wider text-blue-600">Pilihan Ganda</div>
                <div class="mt-1 text-2xl font-extrabold text-blue-700">{{ $stats['multiple_choice_single'] + $stats['multiple_choice_multi'] }}</div>
                <div class="mt-1 text-xs text-zinc-400">Single & Multi Choice</div>
            </x-card>

            <x-card padding class="border-zinc-200">
                <div class="text-[11px] font-bold uppercase tracking-wider text-emerald-600">Benar / Salah & Singkat</div>
                <div class="mt-1 text-2xl font-extrabold text-emerald-700">{{ $stats['true_false'] + $stats['short_answer'] }}</div>
                <div class="mt-1 text-xs text-zinc-400">Auto-grading objektif</div>
            </x-card>

            <x-card padding class="border-zinc-200">
                <div class="text-[11px] font-bold uppercase tracking-wider text-purple-600">Esai / Uraian</div>
                <div class="mt-1 text-2xl font-extrabold text-purple-700">{{ $stats['essay'] }}</div>
                <div class="mt-1 text-xs text-zinc-400">Penilaian manual guru</div>
            </x-card>
        </div>

        <!-- Filter & Search Bar -->
        <x-card padding class="border-zinc-200">
            <form action="{{ route('exam-questions.index') }}" method="GET" class="grid grid-cols-1 sm:grid-cols-4 gap-3">
                <div>
                    <label class="block text-xs font-semibold text-zinc-600 mb-1">Cari Pertanyaan</label>
                    <input
                        type="text"
                        name="search"
                        value="{{ request('search') }}"
                        placeholder="Ketik kata kunci soal..."
                        class="w-full text-xs rounded-lg border-zinc-200 focus:border-brand-500 focus:ring-brand-500"
                    />
                </div>

                <div>
                    <label class="block text-xs font-semibold text-zinc-600 mb-1">Mata Pelajaran</label>
                    <select name="subject_id" class="w-full text-xs rounded-lg border-zinc-200 focus:border-brand-500 focus:ring-brand-500">
                        <option value="">Semua Mata Pelajaran</option>
                        @foreach ($subjects as $subj)
                            <option value="{{ $subj->id }}" {{ request('subject_id') == $subj->id ? 'selected' : '' }}>
                                {{ $subj->name }} ({{ $subj->code }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-zinc-600 mb-1">Tipe Soal</label>
                    <select name="type" class="w-full text-xs rounded-lg border-zinc-200 focus:border-brand-500 focus:ring-brand-500">
                        <option value="">Semua Tipe Soal</option>
                        @foreach ($types as $value => $label)
                            <option value="{{ $value }}" {{ request('type') == $value ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="flex items-end gap-2">
                    <x-button type="submit" variant="secondary" size="sm" class="w-full justify-center">
                        <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                        Filter
                    </x-button>
                    @if(request()->anyFilled(['search', 'subject_id', 'type']))
                        <x-button href="{{ route('exam-questions.index') }}" variant="ghost" size="sm" title="Reset Filter">
                            Reset
                        </x-button>
                    @endif
                </div>
            </form>
        </x-card>

        <!-- Questions Table -->
        <x-card padding="none" class="border-zinc-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-zinc-700 divide-y divide-zinc-200">
                    <thead class="bg-zinc-50 text-[11px] font-bold text-zinc-500 uppercase tracking-wider">
                        <tr>
                            <th class="py-3 px-4 w-12 text-center">#</th>
                            <th class="py-3 px-4 min-w-[300px]">Isi Soal & Opsi</th>
                            <th class="py-3 px-4">Tipe Soal</th>
                            <th class="py-3 px-4">Mata Pelajaran</th>
                            <th class="py-3 px-4 text-center">Bobot Default</th>
                            <th class="py-3 px-4 text-center">Digunakan di Ujian</th>
                            <th class="py-3 px-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-200">
                        @forelse ($questions as $index => $q)
                            <tr class="hover:bg-zinc-50/75 transition-colors">
                                <td class="py-3 px-4 text-center text-zinc-400 font-medium">
                                    {{ $questions->firstItem() + $index }}
                                </td>
                                <td class="py-3 px-4">
                                    <div class="font-medium text-zinc-900 line-clamp-2">
                                        {{ strip_tags($q->question) }}
                                    </div>
                                    @if ($q->type === \App\Models\ExamQuestion::TYPE_MULTIPLE_CHOICE_SINGLE && !empty($q->options))
                                        <div class="mt-1 flex flex-wrap gap-1">
                                            @foreach ($q->options as $opt)
                                                <span class="inline-flex items-center text-[10px] px-1.5 py-0.5 rounded {{ ($opt['key'] ?? '') === $q->correct_answer ? 'bg-emerald-100 text-emerald-800 font-bold border border-emerald-300' : 'bg-zinc-100 text-zinc-600' }}">
                                                    {{ $opt['key'] ?? '•' }}. {{ Str::limit($opt['text'] ?? '', 20) }}
                                                </span>
                                            @endforeach
                                        </div>
                                    @endif
                                    <div class="mt-1 text-[11px] text-zinc-400 flex items-center gap-2">
                                        <span>Oleh: {{ $q->creator?->name ?? 'Admin' }}</span>
                                        @if($q->grade_level)
                                            <span>&bull; Kelas: {{ $q->grade_level }}</span>
                                        @endif
                                    </div>
                                </td>
                                <td class="py-3 px-4 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-medium
                                        @if($q->type === \App\Models\ExamQuestion::TYPE_MULTIPLE_CHOICE_SINGLE) bg-blue-50 text-blue-700 border border-blue-200
                                        @elseif($q->type === \App\Models\ExamQuestion::TYPE_MULTIPLE_CHOICE_MULTI) bg-indigo-50 text-indigo-700 border border-indigo-200
                                        @elseif($q->type === \App\Models\ExamQuestion::TYPE_TRUE_FALSE) bg-emerald-50 text-emerald-700 border border-emerald-200
                                        @elseif($q->type === \App\Models\ExamQuestion::TYPE_SHORT_ANSWER) bg-amber-50 text-amber-700 border border-amber-200
                                        @else bg-purple-50 text-purple-700 border border-purple-200
                                        @endif">
                                        {{ $q->typeLabel() }}
                                    </span>
                                </td>
                                <td class="py-3 px-4 whitespace-nowrap font-medium text-zinc-800">
                                    {{ $q->subject?->name ?? '-' }}
                                </td>
                                <td class="py-3 px-4 text-center font-bold text-zinc-900">
                                    {{ $q->default_score }}
                                </td>
                                <td class="py-3 px-4 text-center">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold {{ $q->exams_count > 0 ? 'bg-zinc-100 text-zinc-800' : 'bg-zinc-50 text-zinc-400' }}">
                                        {{ $q->exams_count }} ujian
                                    </span>
                                </td>
                                <td class="py-3 px-4 text-right whitespace-nowrap">
                                    <div class="flex items-center justify-end gap-1.5">
                                        <form action="{{ route('exam-questions.duplicate', $q) }}" method="POST" class="inline">
                                            @csrf
                                            <button type="submit" class="p-1.5 text-zinc-500 hover:text-zinc-800 hover:bg-zinc-100 rounded-md transition-colors" title="Duplikasi Soal">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7v8a2 2 0 002 2h6M8 7V5a2 2 0 012-2h4.586a1 1 0 01.707.293l4.414 4.414a1 1 0 01.293.707V15a2 2 0 01-2 2h-2M8 7H6a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2v-2"/>
                                                </svg>
                                            </button>
                                        </form>

                                        <a href="{{ route('exam-questions.edit', $q) }}" class="p-1.5 text-blue-600 hover:text-blue-800 hover:bg-blue-50 rounded-md transition-colors" title="Edit Soal">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                                            </svg>
                                        </a>

                                        <form action="{{ route('exam-questions.destroy', $q) }}" method="POST" onsubmit="return confirm('Hapus atau arsipkan butir soal ini?');" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="p-1.5 text-red-600 hover:text-red-800 hover:bg-red-50 rounded-md transition-colors" title="Hapus Soal">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                </svg>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="py-12 text-center text-zinc-400">
                                    <svg class="w-12 h-12 mx-auto text-zinc-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                                    </svg>
                                    <p class="font-semibold text-zinc-600">Belum ada butir soal dalam bank soal</p>
                                    <p class="text-xs text-zinc-400 mt-1">Mulai susun butir pertanyaan untuk digunakan pada berbagai ujian.</p>
                                    <div class="mt-4">
                                        <x-button href="{{ route('exam-questions.create') }}" variant="primary" size="sm">
                                            Buat Soal Pertama
                                        </x-button>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($questions->hasPages())
                <div class="p-4 border-t border-zinc-200">
                    {{ $questions->links() }}
                </div>
            @endif
        </x-card>
    </div>
</x-layouts.app>
