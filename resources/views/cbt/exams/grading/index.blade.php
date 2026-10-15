<x-layouts.app>
    <x-slot:headerTitle>
        Hasil & Penilaian Ujian CBT
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
                <a href="{{ route('exams.index') }}" class="inline-flex items-center text-xs font-semibold text-zinc-500 hover:text-zinc-800 transition-colors mb-1">
                    <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                    Kembali ke Daftar Ujian
                </a>
                <h1 class="text-xl sm:text-2xl font-extrabold text-zinc-900 tracking-tight">
                    Hasil & Penilaian: {{ $exam->title }}
                </h1>
                <p class="text-xs text-zinc-500 mt-0.5">
                    Mata Pelajaran: <span class="font-bold text-zinc-800">{{ $exam->subject?->name }}</span> &bull; KKM: <span class="font-bold text-zinc-800">{{ $exam->passing_grade }}</span>
                </p>
            </div>

            <div class="flex items-center gap-2">
                <a href="{{ route('exams.questions', $exam) }}" class="inline-flex items-center text-xs font-semibold text-zinc-700 bg-white border border-zinc-200 hover:bg-zinc-50 px-3 py-2 rounded-lg transition-colors">
                    Lihat Paket Soal
                </a>
            </div>
        </div>

        <!-- 4-Card Summary Strip -->
        <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
            <x-card padding class="border-zinc-200">
                <div class="text-[11px] font-bold uppercase tracking-wider text-zinc-400">Total Percobaan</div>
                <div class="mt-1 text-2xl font-extrabold text-zinc-900">{{ $stats['total_attempts'] }}</div>
                <div class="mt-1 text-xs text-zinc-400">Siswa yang memulai sesi</div>
            </x-card>

            <x-card padding class="border-zinc-200">
                <div class="text-[11px] font-bold uppercase tracking-wider text-amber-600">Perlu Dinilai Manual</div>
                <div class="mt-1 text-2xl font-extrabold text-amber-700">{{ $stats['submitted'] }}</div>
                <div class="mt-1 text-xs text-zinc-400">Menunggu penilaian esai guru</div>
            </x-card>

            <x-card padding class="border-zinc-200">
                <div class="text-[11px] font-bold uppercase tracking-wider text-emerald-600">Selesai Dinilai</div>
                <div class="mt-1 text-2xl font-extrabold text-emerald-700">{{ $stats['graded'] }}</div>
                <div class="mt-1 text-xs text-zinc-400">Nilai final telah ditetapkan</div>
            </x-card>

            <x-card padding class="border-zinc-200">
                <div class="text-[11px] font-bold uppercase tracking-wider text-brand-600">Rata-Rata Nilai</div>
                <div class="mt-1 text-2xl font-extrabold text-brand-700">{{ $stats['average_score'] }}</div>
                <div class="mt-1 text-xs text-zinc-400">Tertinggi: {{ $stats['highest_score'] }} &bull; Terendah: {{ $stats['lowest_score'] }}</div>
            </x-card>
        </div>

        <!-- Filter Bar -->
        <x-card padding class="border-zinc-200">
            <form action="{{ route('exams.grading.index', $exam) }}" method="GET" class="grid grid-cols-1 sm:grid-cols-4 gap-3">
                <div>
                    <label class="block text-xs font-semibold text-zinc-600 mb-1">Cari Nama / Email</label>
                    <input
                        type="text"
                        name="search"
                        value="{{ request('search') }}"
                        placeholder="Nama siswa..."
                        class="w-full text-xs rounded-lg border-zinc-200 focus:border-brand-500 focus:ring-brand-500"
                    />
                </div>

                <div>
                    <label class="block text-xs font-semibold text-zinc-600 mb-1">Kelas</label>
                    <select name="classroom_id" class="w-full text-xs rounded-lg border-zinc-200 focus:border-brand-500 focus:ring-brand-500">
                        <option value="">Semua Kelas</option>
                        @foreach ($classrooms as $cls)
                            <option value="{{ $cls->id }}" {{ request('classroom_id') == $cls->id ? 'selected' : '' }}>
                                {{ $cls->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-zinc-600 mb-1">Status Pengerjaan</label>
                    <select name="status" class="w-full text-xs rounded-lg border-zinc-200 focus:border-brand-500 focus:ring-brand-500">
                        <option value="">Semua Status</option>
                        <option value="in_progress" {{ request('status') == 'in_progress' ? 'selected' : '' }}>Sedang Dikerjakan</option>
                        <option value="submitted" {{ request('status') == 'submitted' ? 'selected' : '' }}>Terkumpul (Perlu Dinilai)</option>
                        <option value="graded" {{ request('status') == 'graded' ? 'selected' : '' }}>Selesai Dinilai</option>
                        <option value="expired" {{ request('status') == 'expired' ? 'selected' : '' }}>Waktu Habis</option>
                    </select>
                </div>

                <div class="flex items-end gap-2">
                    <x-button type="submit" variant="secondary" size="sm" class="w-full justify-center">
                        Filter
                    </x-button>
                    @if(request()->anyFilled(['search', 'classroom_id', 'status']))
                        <x-button href="{{ route('exams.grading.index', $exam) }}" variant="ghost" size="sm">
                            Reset
                        </x-button>
                    @endif
                </div>
            </form>
        </x-card>

        <!-- Attempts Table -->
        <x-card padding="none" class="border-zinc-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-zinc-700 divide-y divide-zinc-200">
                    <thead class="bg-zinc-50 text-[11px] font-bold text-zinc-500 uppercase tracking-wider">
                        <tr>
                            <th class="py-3 px-4 w-12 text-center">#</th>
                            <th class="py-3 px-4">Nama Siswa</th>
                            <th class="py-3 px-4">Kelas</th>
                            <th class="py-3 px-4 text-center">Percobaan Ke</th>
                            <th class="py-3 px-4">Waktu Pengerjaan</th>
                            <th class="py-3 px-4 text-center">Status</th>
                            <th class="py-3 px-4 text-center">Skor / Total</th>
                            <th class="py-3 px-4 text-center">Persentase</th>
                            <th class="py-3 px-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-200">
                        @forelse ($attempts as $index => $att)
                            <tr class="hover:bg-zinc-50/75 transition-colors">
                                <td class="py-3 px-4 text-center text-zinc-400 font-medium">
                                    {{ $attempts->firstItem() + $index }}
                                </td>
                                <td class="py-3 px-4">
                                    <div class="font-bold text-zinc-900">{{ $att->student?->name }}</div>
                                    <div class="text-[11px] text-zinc-400">{{ $att->student?->email }}</div>
                                </td>
                                <td class="py-3 px-4 whitespace-nowrap">
                                    {{ $att->student?->classroom?->name ?? '-' }}
                                </td>
                                <td class="py-3 px-4 text-center font-semibold text-zinc-700">
                                    {{ $att->attempt_number }}
                                </td>
                                <td class="py-3 px-4 whitespace-nowrap text-[11px] text-zinc-500">
                                    <div>Mulai: {{ $att->started_at?->format('d/m/Y H:i') }}</div>
                                    <div>Kumpul: {{ $att->submitted_at?->format('d/m/Y H:i') ?? '-' }}</div>
                                </td>
                                <td class="py-3 px-4 text-center whitespace-nowrap">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold
                                        @if($att->status === \App\Models\ExamAttempt::STATUS_GRADED) bg-emerald-50 text-emerald-700 border border-emerald-200
                                        @elseif($att->status === \App\Models\ExamAttempt::STATUS_SUBMITTED) bg-amber-50 text-amber-700 border border-amber-200
                                        @elseif($att->status === \App\Models\ExamAttempt::STATUS_IN_PROGRESS) bg-blue-50 text-blue-700 border border-blue-200
                                        @else bg-zinc-100 text-zinc-600 border border-zinc-200
                                        @endif">
                                        {{ $att->statusLabel() }}
                                    </span>
                                </td>
                                <td class="py-3 px-4 text-center whitespace-nowrap font-extrabold text-zinc-900">
                                    {{ (float) $att->total_score }} / {{ (float) $exam->total_score }}
                                </td>
                                <td class="py-3 px-4 text-center whitespace-nowrap">
                                    <span class="font-bold {{ $att->grade_percentage >= $exam->passing_grade ? 'text-emerald-700' : 'text-red-600' }}">
                                        {{ (float) $att->grade_percentage }}%
                                    </span>
                                    @if ($att->isPassed())
                                        <span class="text-[10px] block text-emerald-600 font-semibold">Lulus</span>
                                    @elseif ($att->status === \App\Models\ExamAttempt::STATUS_GRADED)
                                        <span class="text-[10px] block text-red-500 font-semibold">Di bawah KKM</span>
                                    @endif
                                </td>
                                <td class="py-3 px-4 text-right whitespace-nowrap">
                                    <a href="{{ route('exams.grading.show', [$exam, $att]) }}" class="inline-flex items-center text-xs font-bold text-brand-600 hover:text-brand-700 bg-brand-50 hover:bg-brand-100 px-2.5 py-1.5 rounded-lg transition-colors">
                                        Periksa & Nilai &rarr;
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="py-12 text-center text-zinc-400">
                                    <p class="font-semibold text-zinc-600">Belum ada siswa yang mengerjakan ujian ini</p>
                                    <p class="text-xs text-zinc-400 mt-1">Hasil pengerjaan akan muncul otomatis setelah siswa memulai sesi.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($attempts->hasPages())
                <div class="p-4 border-t border-zinc-200">
                    {{ $attempts->links() }}
                </div>
            @endif
        </x-card>
    </div>
</x-layouts.app>
