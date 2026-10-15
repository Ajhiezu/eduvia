<x-layouts.app>
    <x-slot:headerTitle>
        Buku Rapor Saya
    </x-slot:headerTitle>

    <div class="space-y-6">
        <div>
            <h1 class="text-xl sm:text-2xl font-extrabold text-zinc-900 tracking-tight">
                Rapor Hasil Belajar Digital
            </h1>
            <p class="text-xs text-zinc-500 mt-0.5">
                Daftar buku rapor resmi yang telah dipublikasikan oleh pihak sekolah untuk akun Anda.
            </p>
        </div>

        @if($reportCards->isEmpty())
            <x-card padding class="border-zinc-200 text-center py-12">
                <div class="max-w-md mx-auto space-y-3">
                    <div class="w-12 h-12 bg-zinc-100 rounded-full flex items-center justify-center mx-auto text-zinc-400">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                        </svg>
                    </div>
                    <h3 class="text-sm font-bold text-zinc-900">Belum Ada Rapor yang Diterbitkan</h3>
                    <p class="text-xs text-zinc-500 leading-relaxed">
                        Buku rapor semester Anda saat ini masih dalam proses penyusunan atau verifikasi oleh wali kelas dan pihak sekolah. Rapor akan muncul di sini setelah resmi diterbitkan.
                    </p>
                </div>
            </x-card>
        @else
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                @foreach($reportCards as $report)
                    @php
                        $avgScore = $report->calculateAverageScore();
                    @endphp
                    <x-card padding class="border-zinc-200 hover:border-brand-300 transition-all flex flex-col justify-between">
                        <div>
                            <div class="flex items-start justify-between gap-2">
                                <div>
                                    <span class="text-[10px] font-bold uppercase tracking-wider text-zinc-400">
                                        Kelas {{ $report->classroom?->name }}
                                    </span>
                                    <h3 class="text-base font-extrabold text-zinc-900 mt-0.5">
                                        {{ $report->academicYear?->name }} — {{ $report->semester?->name }}
                                    </h3>
                                </div>

                                <x-badge variant="success" size="xs">Resmi Diterbitkan</x-badge>
                            </div>

                            <div class="mt-4 grid grid-cols-2 gap-3 p-3 bg-zinc-50 rounded-lg border border-zinc-100 text-xs">
                                <div>
                                    <span class="text-zinc-500 text-[11px]">Rata-rata Nilai:</span>
                                    <div class="text-lg font-extrabold text-brand-700 mt-0.5">{{ number_format($avgScore, 1) }}</div>
                                </div>
                                <div>
                                    <span class="text-zinc-500 text-[11px]">Tanggal Terbit:</span>
                                    <div class="font-semibold text-zinc-800 mt-1">
                                        {{ $report->published_at ? $report->published_at->translatedFormat('d F Y') : '-' }}
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mt-5 pt-4 border-t border-zinc-100 flex items-center justify-end gap-2">
                            <x-button href="{{ route('student.reports.show', $report) }}" variant="secondary" size="sm">
                                Buka Rapor
                            </x-button>
                            <x-button href="{{ route('reports.cards.pdf', $report) }}" target="_blank" variant="primary" size="sm">
                                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                                </svg>
                                Cetak PDF
                            </x-button>
                        </div>
                    </x-card>
                @endforeach
            </div>
        @endif
    </div>
</x-layouts.app>
