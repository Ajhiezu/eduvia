<x-layouts.app>
    <x-slot:headerTitle>
        Buku Rapor Digital Anak
    </x-slot:headerTitle>

    <div class="space-y-6">

        <!-- Top Header & Child Switcher -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-4 border-b border-zinc-200">
            <div>
                <div class="flex items-center gap-2">
                    <a href="{{ route('parent.dashboard') }}" class="text-xs text-zinc-500 hover:text-zinc-900">&larr; Beranda Pantauan</a>
                    <span class="text-zinc-300">/</span>
                    <span class="text-xs font-bold text-zinc-800">E-Rapor</span>
                </div>
                <h1 class="text-xl sm:text-2xl font-extrabold text-zinc-900 mt-1">
                    Buku Rapor Hasil Belajar Digital
                </h1>
                <p class="text-xs text-zinc-500 mt-0.5">
                    Laporan hasil evaluasi belajar akhir semester resmi untuk <strong class="text-zinc-800">{{ $activeChild?->name ?? 'Anak' }}</strong>
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
            @if ($reportCards->isNotEmpty())
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
                    @foreach ($reportCards as $report)
                        <div class="bg-white rounded-2xl border border-zinc-200 shadow-xs hover:shadow-md transition-shadow p-5 flex flex-col justify-between">
                            <div>
                                <div class="flex items-center justify-between gap-2 mb-3">
                                    <span class="px-2.5 py-1 rounded-lg text-xs font-bold bg-emerald-50 text-emerald-800 border border-emerald-200 flex items-center gap-1.5">
                                        <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                                        <span>Resmi Diterbitkan</span>
                                    </span>
                                    <span class="text-[11px] text-zinc-400">
                                        {{ $report->published_at ? $report->published_at->format('d/m/Y') : '' }}
                                    </span>
                                </div>

                                <h3 class="font-extrabold text-base text-zinc-900 leading-tight">
                                    {{ $report->academicYear?->name ?? 'Tahun Ajaran' }}
                                </h3>
                                <p class="text-xs text-brand-600 font-bold mt-1">
                                    {{ $report->semester?->name ?? 'Semester' }} &bull; Kelas {{ $report->classroom?->name }}
                                </p>

                                <div class="mt-4 pt-3 border-t border-zinc-100 text-xs text-zinc-500 space-y-1">
                                    <p>Wali Kelas: <strong class="text-zinc-800">{{ $report->homeroomTeacher?->name ?? '-' }}</strong></p>
                                    @if ($report->attendance_summary && is_array($report->attendance_summary))
                                        <p class="text-[11px] text-zinc-600">
                                            Kehadiran: Hadir ({{ $report->attendance_summary['present'] ?? 0 }}), Sakit ({{ $report->attendance_summary['sick'] ?? 0 }}), Izin ({{ $report->attendance_summary['permit'] ?? 0 }}), Alpa ({{ $report->attendance_summary['absent'] ?? 0 }})
                                        </p>
                                    @endif
                                </div>
                            </div>

                            <div class="mt-5 pt-3 border-t border-zinc-100 flex items-center justify-between gap-2">
                                <a
                                    href="{{ route('parent.reports.show', $report) }}"
                                    class="inline-flex items-center justify-center gap-1.5 px-4 py-2 rounded-xl text-xs font-bold bg-[#E11D2E] hover:bg-[#C81423] text-white shadow-xs transition-colors w-full"
                                >
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    <span>Buka Lembar Rapor</span>
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="bg-white rounded-2xl border border-dashed border-zinc-300 p-8 text-center max-w-md mx-auto">
                    <div class="w-12 h-12 rounded-full bg-amber-50 text-amber-600 flex items-center justify-center mx-auto mb-3">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    </div>
                    <h3 class="text-sm font-extrabold text-zinc-900 mb-1">Rapor Belum Diterbitkan</h3>
                    <p class="text-xs text-zinc-500 leading-relaxed">
                        Buku rapor hasil belajar semester untuk siswa ini masih dalam tahap penilaian / verifikasi oleh wali kelas dan belum resmi dipublikasikan oleh sekolah.
                    </p>
                </div>
            @endif
        @endif

    </div>
</x-layouts.app>
