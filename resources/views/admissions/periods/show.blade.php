<x-layouts.app>
    <x-slot:headerTitle>
        Detail Periode PPDB &mdash; {{ $period->name }}
    </x-slot:headerTitle>

    <div class="space-y-6">
        <!-- Alerts -->
        @if (session('status'))
            <x-alert variant="success" dismissible>
                {{ session('status') }}
            </x-alert>
        @endif

        <!-- Back Navigation & Title -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <div class="flex items-center gap-2">
                    <x-button href="{{ route('admissions.periods.index') }}" variant="secondary" size="xs">
                        &larr; Kembali
                    </x-button>
                    <span class="text-xs text-zinc-400">/</span>
                    <span class="text-xs font-semibold text-zinc-600">Detail Periode</span>
                </div>
                <h1 class="text-xl sm:text-2xl font-extrabold text-zinc-900 tracking-tight mt-1.5 flex items-center gap-3">
                    <span>{{ $period->name }}</span>
                    <x-badge :variant="$period->statusBadgeVariant()" size="sm">
                        {{ $period->statusLabel() }}
                    </x-badge>
                </h1>
                <p class="text-xs text-zinc-500 mt-0.5">
                    Kode: <span class="font-mono font-bold">{{ $period->code }}</span> &bull; Tahun Ajaran Target: <strong>{{ $period->academicYear?->name ?? '-' }}</strong>
                </p>
            </div>

            <div class="flex items-center gap-2.5 flex-wrap">
                <x-button href="{{ route('admissions.periods.edit', $period) }}" variant="secondary" size="sm">
                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                    </svg>
                    Edit Periode
                </x-button>

                <x-button href="{{ route('admissions.applicants.index', ['period_id' => $period->id]) }}" variant="primary" size="sm">
                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                    </svg>
                    Lihat Calon Siswa ({{ $totalApplicants }})
                </x-button>
            </div>
        </div>

        <!-- 4-Card Metis Metric Summary Strip -->
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
            <x-card padding class="border-zinc-200">
                <div class="text-[11px] font-bold uppercase tracking-wider text-zinc-500">Total Pendaftar</div>
                <div class="mt-1 text-2xl font-extrabold text-zinc-900">{{ number_format($totalApplicants) }}</div>
                <div class="mt-1 text-xs text-zinc-400">Dari kuota {{ $period->quota }} siswa</div>
            </x-card>

            <x-card padding class="border-zinc-200">
                <div class="text-[11px] font-bold uppercase tracking-wider text-blue-600">Berkas Terverifikasi</div>
                <div class="mt-1 text-2xl font-extrabold text-blue-700">{{ number_format($verifiedCount) }}</div>
                <div class="mt-1 text-xs text-zinc-400">Berkas lengkap & sah</div>
            </x-card>

            <x-card padding class="border-zinc-200">
                <div class="text-[11px] font-bold uppercase tracking-wider text-emerald-600">Lulus Seleksi</div>
                <div class="mt-1 text-2xl font-extrabold text-emerald-700">{{ number_format($acceptedCount) }}</div>
                <div class="mt-1 text-xs text-zinc-400">Dinyatakan diterima</div>
            </x-card>

            <x-card padding class="border-zinc-200">
                <div class="text-[11px] font-bold uppercase tracking-wider text-primary">Daftar Ulang</div>
                <div class="mt-1 text-2xl font-extrabold text-primary">{{ number_format($reRegisteredCount) }}</div>
                <div class="mt-1 text-xs text-zinc-400">Terkonfirmasi aktif</div>
            </x-card>
        </div>

        <!-- Timeline & Information Detail -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- Timeline Card -->
            <x-card padding class="border-zinc-200 md:col-span-1">
                <h2 class="text-sm font-bold text-zinc-900 tracking-tight pb-3 border-b border-zinc-100">
                    Jadwal & Agenda
                </h2>

                <div class="space-y-3 mt-4 text-xs">
                    <div>
                        <span class="text-zinc-400 block text-[11px]">Masa Pendaftaran:</span>
                        <span class="font-bold text-zinc-800">
                            {{ $period->start_date?->format('d M Y') }} &ndash; {{ $period->end_date?->format('d M Y') }}
                        </span>
                    </div>

                    <div>
                        <span class="text-zinc-400 block text-[11px]">Pengumuman Kelulusan:</span>
                        <span class="font-bold text-zinc-800">
                            {{ $period->announcement_date?->format('d M Y') }}
                        </span>
                    </div>

                    <div>
                        <span class="text-zinc-400 block text-[11px]">Masa Daftar Ulang:</span>
                        <span class="font-bold text-zinc-800">
                            {{ $period->re_registration_start_date?->format('d M Y') }} &ndash; {{ $period->re_registration_end_date?->format('d M Y') }}
                        </span>
                    </div>

                    <div class="pt-3 border-t border-zinc-100">
                        <span class="text-zinc-400 block text-[11px]">URL Publik Pendaftaran:</span>
                        <a href="{{ route('ppdb.register', $period) }}" target="_blank" class="text-primary hover:underline font-mono text-[11px] break-all">
                            {{ route('ppdb.register', $period) }} &nearr;
                        </a>
                    </div>
                </div>
            </x-card>

            <!-- Tracks Breakdown Table -->
            <x-card padding class="border-zinc-200 md:col-span-2">
                <h2 class="text-sm font-bold text-zinc-900 tracking-tight pb-3 border-b border-zinc-100 flex items-center justify-between">
                    <span>Jalur Pendaftaran & Kuota</span>
                    <span class="text-xs text-zinc-400">{{ $period->tracks->count() }} jalur aktif</span>
                </h2>

                <div class="overflow-x-auto mt-4" data-scrollable>
                    <table class="w-full text-left text-xs min-w-[550px]">
                        <thead>
                            <tr class="border-b border-zinc-200 bg-zinc-50/75 text-zinc-600 font-semibold uppercase tracking-wider text-[11px]">
                                <th class="py-2.5 px-3">Nama Jalur</th>
                                <th class="py-2.5 px-3">Kode</th>
                                <th class="py-2.5 px-3 text-center">Kuota</th>
                                <th class="py-2.5 px-3 text-center">Pendaftar</th>
                                <th class="py-2.5 px-3 text-center">Sisa</th>
                                <th class="py-2.5 px-3 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-100">
                            @forelse ($period->tracks as $track)
                                <tr class="hover:bg-zinc-50/50">
                                    <td class="py-2.5 px-3 font-bold text-zinc-900">
                                        {{ $track->name }}
                                        @if ($track->description)
                                            <div class="text-[11px] text-zinc-400 font-normal">{{ $track->description }}</div>
                                        @endif
                                    </td>
                                    <td class="py-2.5 px-3 font-mono text-zinc-600">
                                        {{ $track->code }}
                                    </td>
                                    <td class="py-2.5 px-3 text-center font-bold text-zinc-800">
                                        {{ $track->quota }}
                                    </td>
                                    <td class="py-2.5 px-3 text-center">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full font-bold text-blue-800 bg-blue-50 border border-blue-200">
                                            {{ $track->applicants_count }}
                                        </span>
                                    </td>
                                    <td class="py-2.5 px-3 text-center font-semibold {{ ($track->quota - $track->applicants_count) <= 0 ? 'text-amber-600' : 'text-emerald-700' }}">
                                        {{ max(0, $track->quota - $track->applicants_count) }}
                                    </td>
                                    <td class="py-2.5 px-3 text-right">
                                        <a href="{{ route('admissions.applicants.index', ['period_id' => $period->id, 'track_id' => $track->id]) }}" class="text-primary hover:underline font-semibold text-xs">
                                            Filter Pendaftar &rarr;
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="py-6 text-center text-zinc-400">
                                        Belum ada jalur seleksi untuk periode ini.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </x-card>
        </div>
    </div>
</x-layouts.app>
