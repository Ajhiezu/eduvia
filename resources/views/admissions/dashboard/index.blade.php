<x-layouts.app>
    <x-slot:headerTitle>
        Dashboard PPDB / SPMB
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
                    Dashboard & Analitik PPDB / SPMB
                </h1>
                <p class="text-xs text-zinc-500 mt-0.5">
                    Ringkasan performa penerimaan peserta didik baru, kuota per jalur, dan progres daftar ulang.
                </p>
            </div>

            <div class="flex items-center gap-2.5 flex-wrap">
                <!-- Period Selector Form -->
                <form method="GET" action="{{ route('admissions.dashboard') }}" class="flex items-center gap-2">
                    <select
                        name="period_id"
                        onchange="this.form.submit()"
                        class="px-3 py-1.5 text-xs bg-white border border-zinc-200 rounded-lg text-zinc-800 focus:outline-none focus:ring-2 focus:ring-red-100 focus:border-primary font-medium"
                    >
                        @foreach ($periods as $p)
                            <option value="{{ $p->id }}" @selected($selectedPeriod?->id == $p->id)>
                                {{ $p->name }} ({{ $p->statusLabel() }})
                            </option>
                        @endforeach
                    </select>
                </form>

                <x-button href="{{ route('admissions.periods.index') }}" variant="secondary" size="sm">
                    Kelola Periode
                </x-button>

                <x-button href="{{ route('admissions.applicants.index') }}" variant="primary" size="sm">
                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                    </svg>
                    Data Calon Siswa
                </x-button>
            </div>
        </div>

        @if (! $selectedPeriod)
            <x-card padding class="border-zinc-200 text-center py-12">
                <p class="text-sm text-zinc-500">Belum ada periode PPDB yang aktif. Silakan buat periode baru.</p>
                <div class="mt-4">
                    <x-button href="{{ route('admissions.periods.create') }}" variant="primary" size="sm">
                        + Tambah Periode PPDB
                    </x-button>
                </div>
            </x-card>
        @else
            <!-- 4-Card Metric Summary Strip -->
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                <x-card padding class="border-zinc-200">
                    <div class="text-[11px] font-bold uppercase tracking-wider text-zinc-500">Total Pendaftar</div>
                    <div class="mt-1 text-2xl font-extrabold text-zinc-900">{{ number_format($totalApplicants) }}</div>
                    <div class="mt-1 text-xs text-zinc-400">Berkas masuk sistem</div>
                </x-card>

                <x-card padding class="border-zinc-200">
                    <div class="text-[11px] font-bold uppercase tracking-wider text-blue-600">Terverifikasi</div>
                    <div class="mt-1 text-2xl font-extrabold text-blue-700">{{ number_format($verifiedCount) }}</div>
                    <div class="mt-1 text-xs text-zinc-400">Berkas valid panitia</div>
                </x-card>

                <x-card padding class="border-zinc-200">
                    <div class="text-[11px] font-bold uppercase tracking-wider text-emerald-600">Dinyatakan Lulus</div>
                    <div class="mt-1 text-2xl font-extrabold text-emerald-700">{{ number_format($acceptedCount) }}</div>
                    <div class="mt-1 text-xs text-zinc-400">Lulus seleksi penerimaan</div>
                </x-card>

                <x-card padding class="border-zinc-200">
                    <div class="text-[11px] font-bold uppercase tracking-wider text-primary">Daftar Ulang</div>
                    <div class="mt-1 text-2xl font-extrabold text-primary">{{ number_format($reRegisteredCount) }}</div>
                    <div class="mt-1 text-xs text-zinc-400">Terkonfirmasi aktif</div>
                </x-card>
            </div>

            <!-- Quota Utilization Progress Banner -->
            <x-card padding class="border-zinc-200">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 pb-2">
                    <div>
                        <h2 class="text-xs font-bold uppercase tracking-wider text-zinc-400">Utilisasi Kuota Penerimaan</h2>
                        <div class="text-base font-extrabold text-zinc-900 mt-0.5">
                            {{ $totalApplicants }} dari {{ $quota }} Kursi Terisi ({{ $quotaFulfilledPercent }}%)
                        </div>
                    </div>
                    <div class="text-xs text-zinc-500">
                        Sisa Kapasitas: <strong class="text-zinc-800">{{ max(0, $quota - $totalApplicants) }}</strong> kursi
                    </div>
                </div>

                <div class="w-full bg-zinc-100 rounded-full h-2.5 overflow-hidden mt-2">
                    <div
                        class="h-2.5 rounded-full {{ $quotaFulfilledPercent >= 100 ? 'bg-amber-500' : 'bg-primary' }} transition-all duration-300"
                        style="width: {{ $quotaFulfilledPercent }}%"
                    ></div>
                </div>
            </x-card>

            <!-- Distribution by Track & Status Overview -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Track Breakdown -->
                <x-card padding class="border-zinc-200">
                    <h3 class="text-sm font-bold text-zinc-900 tracking-tight pb-3 border-b border-zinc-100 flex items-center justify-between">
                        <span>Pendaftar Berdasarkan Jalur</span>
                        <span class="text-xs font-normal text-zinc-500">{{ $tracks->count() }} jalur aktif</span>
                    </h3>

                    <div class="space-y-3 mt-4">
                        @forelse ($tracks as $track)
                            @php
                                $trackPercent = $track->quota > 0 ? min(100, round(($track->applicants_count / $track->quota) * 100)) : 0;
                            @endphp
                            <div class="space-y-1">
                                <div class="flex items-center justify-between text-xs">
                                    <span class="font-bold text-zinc-800">{{ $track->name }}</span>
                                    <span class="text-zinc-500 font-medium">
                                        {{ $track->applicants_count }} / {{ $track->quota }} ({{ $trackPercent }}%)
                                    </span>
                                </div>
                                <div class="w-full bg-zinc-100 rounded-full h-1.5 overflow-hidden">
                                    <div class="bg-zinc-700 h-1.5 rounded-full" style="width: {{ $trackPercent }}%"></div>
                                </div>
                            </div>
                        @empty
                            <p class="text-xs text-zinc-400 py-3 text-center">Belum ada jalur terdaftar pada periode ini.</p>
                        @endforelse
                    </div>
                </x-card>

                <!-- Status Distribution -->
                <x-card padding class="border-zinc-200">
                    <h3 class="text-sm font-bold text-zinc-900 tracking-tight pb-3 border-b border-zinc-100">
                        Status Seleksi Calon Siswa
                    </h3>

                    <div class="mt-4 space-y-2.5 text-xs">
                        <div class="flex items-center justify-between py-1.5 border-b border-zinc-100">
                            <span class="text-zinc-600 font-medium flex items-center gap-2">
                                <span class="w-2 h-2 rounded-full bg-zinc-400"></span> Menunggu Verifikasi
                            </span>
                            <span class="font-bold text-zinc-900">{{ number_format($pendingCount) }}</span>
                        </div>

                        <div class="flex items-center justify-between py-1.5 border-b border-zinc-100">
                            <span class="text-zinc-600 font-medium flex items-center gap-2">
                                <span class="w-2 h-2 rounded-full bg-blue-500"></span> Berkas Terverifikasi
                            </span>
                            <span class="font-bold text-zinc-900">{{ number_format($verifiedCount) }}</span>
                        </div>

                        <div class="flex items-center justify-between py-1.5 border-b border-zinc-100">
                            <span class="text-zinc-600 font-medium flex items-center gap-2">
                                <span class="w-2 h-2 rounded-full bg-emerald-500"></span> Dinyatakan Diterima (Lulus)
                            </span>
                            <span class="font-bold text-zinc-900">{{ number_format($acceptedCount) }}</span>
                        </div>

                        <div class="flex items-center justify-between py-1.5 border-b border-zinc-100">
                            <span class="text-zinc-600 font-medium flex items-center gap-2">
                                <span class="w-2 h-2 rounded-full bg-rose-500"></span> Tidak Diterima
                            </span>
                            <span class="font-bold text-zinc-900">{{ number_format($rejectedCount) }}</span>
                        </div>

                        <div class="flex items-center justify-between py-1.5">
                            <span class="text-zinc-600 font-medium flex items-center gap-2">
                                <span class="w-2 h-2 rounded-full bg-primary"></span> Sudah Daftar Ulang
                            </span>
                            <span class="font-bold text-zinc-900">{{ number_format($reRegisteredCount) }}</span>
                        </div>
                    </div>
                </x-card>
            </div>

            <!-- Recent Applicants Table -->
            <x-card padding class="border-zinc-200">
                <div class="flex items-center justify-between pb-3 border-b border-zinc-100">
                    <h3 class="text-sm font-bold text-zinc-900 tracking-tight">Pendaftar Terbaru</h3>
                    <a href="{{ route('admissions.applicants.index', ['period_id' => $selectedPeriod->id]) }}" class="text-xs font-semibold text-primary hover:underline">
                        Lihat Semua Pendaftar &rarr;
                    </a>
                </div>

                <div class="overflow-x-auto mt-3" data-scrollable>
                    <table class="w-full text-left text-xs min-w-[640px]">
                        <thead>
                            <tr class="border-b border-zinc-200 bg-zinc-50/75 text-zinc-600 font-semibold uppercase tracking-wider text-[11px]">
                                <th class="py-2.5 px-3">No. Registrasi</th>
                                <th class="py-2.5 px-3">Nama Calon Siswa</th>
                                <th class="py-2.5 px-3">Jalur</th>
                                <th class="py-2.5 px-3">Status</th>
                                <th class="py-2.5 px-3">Waktu Daftar</th>
                                <th class="py-2.5 px-3 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-100">
                            @forelse ($recentApplicants as $applicant)
                                <tr class="hover:bg-zinc-50/50 transition-colors">
                                    <td class="py-2.5 px-3 font-mono font-bold text-primary">
                                        {{ $applicant->registration_number }}
                                    </td>
                                    <td class="py-2.5 px-3 font-semibold text-zinc-900">
                                        {{ $applicant->full_name }}
                                        <div class="text-[11px] text-zinc-400 font-normal">NISN: {{ $applicant->nisn ?? '-' }}</div>
                                    </td>
                                    <td class="py-2.5 px-3 text-zinc-700">
                                        {{ $applicant->admissionTrack?->name }}
                                    </td>
                                    <td class="py-2.5 px-3">
                                        <x-badge :variant="$applicant->statusBadgeVariant()" size="xs">
                                            {{ $applicant->statusLabel() }}
                                        </x-badge>
                                    </td>
                                    <td class="py-2.5 px-3 text-zinc-500">
                                        {{ $applicant->created_at->format('d/m/Y H:i') }}
                                    </td>
                                    <td class="py-2.5 px-3 text-right">
                                        <a href="{{ route('admissions.applicants.show', $applicant) }}" class="text-xs font-semibold text-primary hover:underline">
                                            Dossier &rarr;
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="py-8 text-center text-zinc-400">
                                        Belum ada pendaftar pada periode ini.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </x-card>
        @endif
    </div>
</x-layouts.app>
