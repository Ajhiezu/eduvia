<x-layouts.app>
    <x-slot:headerTitle>
        Capaian Nilai & Hasil CBT Anak
    </x-slot:headerTitle>

    <div class="space-y-6">

        <!-- Top Header & Child Switcher -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-4 border-b border-zinc-200">
            <div>
                <div class="flex items-center gap-2">
                    <a href="{{ route('parent.dashboard') }}" class="text-xs text-zinc-500 hover:text-zinc-900">&larr; Beranda Pantauan</a>
                    <span class="text-zinc-300">/</span>
                    <span class="text-xs font-bold text-zinc-800">Capaian Nilai</span>
                </div>
                <h1 class="text-xl sm:text-2xl font-extrabold text-zinc-900 mt-1">
                    Capaian Nilai & Ujian CBT
                </h1>
                <p class="text-xs text-zinc-500 mt-0.5">
                    Transparansi hasil ujian Computer-Based Test (CBT) dan penilaian tugas untuk <strong class="text-zinc-800">{{ $activeChild?->name ?? 'Anak' }}</strong>
                </p>
            </div>

            @if ($activeChildren->count() > 1)
                <div class="flex items-center gap-1.5 bg-zinc-100 p-1 rounded-xl">
                    @foreach ($activeChildren as $child)
                        <a
                            href="{{ route('parent.dashboard', ['child_id' => $child->id]) }}"
                            class="px-3 py-1.5 rounded-lg text-xs font-bold transition-all {{ $activeChild && $activeChild->id === $child->id ? 'bg-[#E11D2E] text-white shadow-xs' : 'text-zinc-600 hover:text-zinc-900' }}"
                        >
                            {{ $child->name }}
                        </a>
                    @endforeach
                </div>
            @endif
        </div>

        @if (! $activeChild)
            <div class="p-8 text-center bg-white rounded-2xl border border-zinc-200">
                <p class="text-sm text-zinc-500">Belum ada akun anak yang terhubung. <a href="{{ route('parent.children.index') }}" class="text-brand-600 font-bold">Tautkan akun anak</a></p>
            </div>
        @else
            <!-- Section 1: Hasil Ujian CBT -->
            <div class="bg-white rounded-2xl border border-zinc-200 shadow-xs overflow-hidden">
                <div class="px-5 py-4 border-b border-zinc-100 flex items-center justify-between">
                    <div>
                        <h3 class="font-extrabold text-sm text-zinc-900">Hasil Ujian CBT (Computer Based Test)</h3>
                        <p class="text-[11px] text-zinc-500">Ujian yang telah selesai dan hasil penilaian telah dipublikasikan</p>
                    </div>
                    <span class="text-xs font-bold text-purple-700 bg-purple-50 px-2.5 py-1 rounded-lg border border-purple-200">
                        {{ $examAttempts->total() }} Ujian
                    </span>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs text-zinc-700">
                        <thead class="bg-zinc-50 border-b border-zinc-200 text-zinc-500 uppercase tracking-wider font-bold">
                            <tr>
                                <th class="px-4 py-3">Ujian & Mata Pelajaran</th>
                                <th class="px-4 py-3">Waktu Pengerjaan</th>
                                <th class="px-4 py-3">Status</th>
                                <th class="px-4 py-3">Nilai Akhir</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-100">
                            @forelse ($examAttempts as $attempt)
                                <tr class="hover:bg-zinc-50/60 transition-colors">
                                    <td class="px-4 py-3.5">
                                        <div class="font-bold text-zinc-900">{{ $attempt->exam?->title }}</div>
                                        <div class="text-[11px] text-brand-600 font-semibold">{{ $attempt->exam?->subject?->name ?? 'Mata Pelajaran' }}</div>
                                    </td>
                                    <td class="px-4 py-3.5 whitespace-nowrap text-zinc-600">
                                        {{ $attempt->submitted_at ? $attempt->submitted_at->format('d M Y, H:i') : '-' }}
                                    </td>
                                    <td class="px-4 py-3.5 whitespace-nowrap">
                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-50 text-emerald-800 border border-emerald-200">
                                            Selesai Dikerjakan
                                        </span>
                                    </td>
                                    <td class="px-4 py-3.5 whitespace-nowrap">
                                        <span class="text-base font-extrabold text-zinc-900">
                                            {{ $attempt->final_score !== null ? round($attempt->final_score, 1) : '-' }}
                                        </span>
                                        <span class="text-[10px] text-zinc-400">/ 100</span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-4 py-8 text-center text-zinc-400">
                                        Belum ada riwayat hasil ujian CBT yang dipublikasikan.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if ($examAttempts->hasPages())
                    <div class="p-4 border-t border-zinc-200 bg-zinc-50/50">
                        {{ $examAttempts->links() }}
                    </div>
                @endif
            </div>

            <!-- Section 2: Nilai Tugas & Evaluasi -->
            <div class="bg-white rounded-2xl border border-zinc-200 shadow-xs overflow-hidden">
                <div class="px-5 py-4 border-b border-zinc-100 flex items-center justify-between">
                    <div>
                        <h3 class="font-extrabold text-sm text-zinc-900">Nilai Tugas & Evaluasi Berkala</h3>
                        <p class="text-[11px] text-zinc-500">Tugas yang telah selesai dinilai oleh tenaga pengajar</p>
                    </div>
                    <span class="text-xs font-bold text-blue-700 bg-blue-50 px-2.5 py-1 rounded-lg border border-blue-200">
                        {{ $assignmentGrades->total() }} Tugas Dinilai
                    </span>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs text-zinc-700">
                        <thead class="bg-zinc-50 border-b border-zinc-200 text-zinc-500 uppercase tracking-wider font-bold">
                            <tr>
                                <th class="px-4 py-3">Mata Pelajaran & Judul Tugas</th>
                                <th class="px-4 py-3">Guru Penilai</th>
                                <th class="px-4 py-3">Feedback / Catatan Guru</th>
                                <th class="px-4 py-3">Nilai</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-100">
                            @forelse ($assignmentGrades as $task)
                                @php $sub = $task->submissions->first(); @endphp
                                <tr class="hover:bg-zinc-50/60 transition-colors">
                                    <td class="px-4 py-3.5">
                                        <div class="font-bold text-zinc-900">{{ $task->title }}</div>
                                        <div class="text-[11px] text-brand-600 font-semibold">{{ $task->subject?->name }}</div>
                                    </td>
                                    <td class="px-4 py-3.5 text-zinc-600">
                                        {{ $task->teacher?->name }}
                                    </td>
                                    <td class="px-4 py-3.5 text-zinc-600 text-[11px]">
                                        {{ $sub?->teacher_feedback ?: '-' }}
                                    </td>
                                    <td class="px-4 py-3.5 whitespace-nowrap">
                                        <span class="text-base font-extrabold text-zinc-900">
                                            {{ $sub?->score ?? '-' }}
                                        </span>
                                        <span class="text-[10px] text-zinc-400">/ {{ $task->max_score }}</span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-4 py-8 text-center text-zinc-400">
                                        Belum ada nilai tugas yang tersedia.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if ($assignmentGrades->hasPages())
                    <div class="p-4 border-t border-zinc-200 bg-zinc-50/50">
                        {{ $assignmentGrades->links() }}
                    </div>
                @endif
            </div>
        @endif

    </div>
</x-layouts.app>
