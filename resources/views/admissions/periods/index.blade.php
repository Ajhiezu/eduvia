<x-layouts.app>
    <x-slot:headerTitle>
        Periode PPDB
    </x-slot:headerTitle>

    <div class="space-y-6">
        <!-- Alerts -->
        @if (session('status'))
            <x-alert variant="success" dismissible>
                {{ session('status') }}
            </x-alert>
        @endif

        <!-- Action Header -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h1 class="text-xl sm:text-2xl font-extrabold text-zinc-900 tracking-tight">
                    Periode Pendaftaran (PPDB / SPMB)
                </h1>
                <p class="text-xs text-zinc-500 mt-0.5">
                    Kelola gelombang penerimaan, jadwal seleksi, kuota total, dan jalur pendaftaran siswa baru.
                </p>
            </div>

            <div class="flex items-center gap-2.5 flex-wrap">
                <x-button href="{{ route('admissions.dashboard') }}" variant="secondary" size="sm">
                    Dashboard PPDB
                </x-button>
                <x-button href="{{ route('admissions.periods.create') }}" variant="primary" size="sm">
                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Tambah Periode Baru
                </x-button>
            </div>
        </div>

        <!-- Periods Table Card -->
        <x-card padding class="border-zinc-200">
            <div class="overflow-x-auto" data-scrollable>
                <table class="w-full text-left text-xs border-collapse min-w-[700px]">
                    <thead>
                        <tr class="border-b border-zinc-200 bg-zinc-50/75 text-zinc-600 font-semibold uppercase tracking-wider text-[11px]">
                            <th class="py-3 px-3">Nama Periode</th>
                            <th class="py-3 px-3">Tahun Ajaran</th>
                            <th class="py-3 px-3">Masa Pendaftaran</th>
                            <th class="py-3 px-3 text-center">Kuota Total</th>
                            <th class="py-3 px-3 text-center">Pendaftar</th>
                            <th class="py-3 px-3 text-center">Status</th>
                            <th class="py-3 px-3 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-100">
                        @forelse ($periods as $period)
                            <tr class="hover:bg-zinc-50/50 transition-colors">
                                <td class="py-3 px-3">
                                    <div class="font-bold text-zinc-900">{{ $period->name }}</div>
                                    <div class="text-[11px] text-zinc-400 font-mono">Kode: {{ $period->code }}</div>
                                </td>
                                <td class="py-3 px-3 text-zinc-700 font-medium">
                                    {{ $period->academicYear?->name ?? '-' }}
                                </td>
                                <td class="py-3 px-3 text-zinc-600">
                                    <div>{{ $period->start_date?->format('d/m/Y') }} &ndash; {{ $period->end_date?->format('d/m/Y') }}</div>
                                    <div class="text-[11px] text-zinc-400">Pengumuman: {{ $period->announcement_date?->format('d/m/Y') }}</div>
                                </td>
                                <td class="py-3 px-3 text-center font-bold text-zinc-900">
                                    {{ $period->quota }}
                                </td>
                                <td class="py-3 px-3 text-center">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full font-bold text-blue-800 bg-blue-50 border border-blue-200">
                                        {{ $period->applicants_count }}
                                    </span>
                                </td>
                                <td class="py-3 px-3 text-center">
                                    <x-badge :variant="$period->statusBadgeVariant()" size="xs">
                                        {{ $period->statusLabel() }}
                                    </x-badge>
                                </td>
                                <td class="py-3 px-3 text-right space-x-1">
                                    <a href="{{ route('admissions.periods.show', $period) }}" class="inline-flex items-center text-primary hover:underline font-semibold text-xs">
                                        Detail
                                    </a>
                                    <span class="text-zinc-300">|</span>
                                    <a href="{{ route('admissions.periods.edit', $period) }}" class="inline-flex items-center text-zinc-600 hover:text-zinc-900 font-semibold text-xs">
                                        Edit
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="py-8 text-center text-zinc-400">
                                    Belum ada periode pendaftaran yang dibuat.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($periods->hasPages())
                <div class="pt-4 border-t border-zinc-100">
                    {{ $periods->links() }}
                </div>
            @endif
        </x-card>
    </div>
</x-layouts.app>
