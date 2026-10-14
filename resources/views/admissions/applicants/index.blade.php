<x-layouts.app>
    <x-slot:headerTitle>
        Data Calon Siswa PPDB
    </x-slot:headerTitle>

    <div class="space-y-6">
        <!-- Alerts -->
        @if (session('status'))
            <x-alert variant="success" dismissible>
                {{ session('status') }}
            </x-alert>
        @endif

        @if ($errors->any())
            <x-alert variant="danger" dismissible>
                {{ $errors->first() }}
            </x-alert>
        @endif

        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h1 class="text-xl sm:text-2xl font-extrabold text-zinc-900 tracking-tight">
                    Data Calon Siswa (Pendaftar PPDB)
                </h1>
                <p class="text-xs text-zinc-500 mt-0.5">
                    Daftar seluruh pendaftar, verifikasi kelengkapan berkas fisik/digital, seleksi kelulusan, dan konversi ke siswa aktif.
                </p>
            </div>

            <div class="flex items-center gap-2.5 flex-wrap">
                <x-button href="{{ route('admissions.dashboard') }}" variant="secondary" size="sm">
                    Dashboard PPDB
                </x-button>
                <x-button href="{{ route('admissions.periods.index') }}" variant="secondary" size="sm">
                    Periode Pendaftaran
                </x-button>
            </div>
        </div>

        <!-- 4-Card Metric Summary Strip -->
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-2.5 sm:gap-4">
            <x-card padding class="border-zinc-200">
                <div class="text-[11px] font-bold uppercase tracking-wider text-zinc-500">Total Pendaftar</div>
                <div class="mt-1 text-2xl font-extrabold text-zinc-900">{{ number_format($totalApplicantsCount) }}</div>
                <div class="mt-1 text-xs text-zinc-400">Seluruh berkas masuk</div>
            </x-card>

            <x-card padding class="border-zinc-200">
                <div class="text-[11px] font-bold uppercase tracking-wider text-blue-600">Berkas Terverifikasi</div>
                <div class="mt-1 text-2xl font-extrabold text-blue-700">{{ number_format($totalVerifiedCount) }}</div>
                <div class="mt-1 text-xs text-zinc-400">Berkas sah panitia</div>
            </x-card>

            <x-card padding class="border-zinc-200">
                <div class="text-[11px] font-bold uppercase tracking-wider text-emerald-600">Dinyatakan Lulus</div>
                <div class="mt-1 text-2xl font-extrabold text-emerald-700">{{ number_format($totalAcceptedCount) }}</div>
                <div class="mt-1 text-xs text-zinc-400">Lulus seleksi kuota</div>
            </x-card>

            <x-card padding class="border-zinc-200">
                <div class="text-[11px] font-bold uppercase tracking-wider text-primary">Daftar Ulang</div>
                <div class="mt-1 text-2xl font-extrabold text-primary">{{ number_format($totalReRegisteredCount) }}</div>
                <div class="mt-1 text-xs text-zinc-400">Siap / telah aktif</div>
            </x-card>
        </div>

        <!-- Filter Bar -->
        <x-card padding class="border-zinc-200">
            <form method="GET" action="{{ route('admissions.applicants.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
                <!-- Search -->
                <div class="lg:col-span-2">
                    <x-label for="search" class="text-[11px]">Cari Calon Siswa</x-label>
                    <x-input
                        id="search"
                        name="search"
                        :value="$search"
                        placeholder="Nama, No. Registrasi, NISN, No. HP..."
                        class="text-xs"
                    />
                </div>

                <!-- Periode -->
                <div>
                    <x-label for="period_id" class="text-[11px]">Periode PPDB</x-label>
                    <select
                        id="period_id"
                        name="period_id"
                        class="w-full mt-1 px-3 py-2 text-xs bg-white border border-zinc-200 rounded-lg text-zinc-800 focus:outline-none focus:ring-2 focus:ring-red-100 focus:border-primary"
                    >
                        <option value="">-- Semua Periode --</option>
                        @foreach ($periods as $p)
                            <option value="{{ $p->id }}" @selected($periodId == $p->id)>
                                {{ $p->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Jalur -->
                <div>
                    <x-label for="track_id" class="text-[11px]">Jalur Pendaftaran</x-label>
                    <select
                        id="track_id"
                        name="track_id"
                        class="w-full mt-1 px-3 py-2 text-xs bg-white border border-zinc-200 rounded-lg text-zinc-800 focus:outline-none focus:ring-2 focus:ring-red-100 focus:border-primary"
                    >
                        <option value="">-- Semua Jalur --</option>
                        @foreach ($tracks as $t)
                            <option value="{{ $t->id }}" @selected($trackId == $t->id)>
                                {{ $t->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Status -->
                <div>
                    <x-label for="status" class="text-[11px]">Status Seleksi</x-label>
                    <select
                        id="status"
                        name="status"
                        class="w-full mt-1 px-3 py-2 text-xs bg-white border border-zinc-200 rounded-lg text-zinc-800 focus:outline-none focus:ring-2 focus:ring-red-100 focus:border-primary"
                    >
                        <option value="">-- Semua Status --</option>
                        <option value="pending" @selected($status == 'pending')>Menunggu Verifikasi</option>
                        <option value="verified" @selected($status == 'verified')>Berkas Terverifikasi</option>
                        <option value="revision_needed" @selected($status == 'revision_needed')>Perlu Perbaikan</option>
                        <option value="accepted" @selected($status == 'accepted')>Diterima (Lulus)</option>
                        <option value="rejected" @selected($status == 'rejected')>Tidak Diterima</option>
                        <option value="re_registered" @selected($status == 're_registered')>Sudah Daftar Ulang</option>
                        <option value="converted" @selected($status == 'converted')>Siswa Aktif</option>
                    </select>
                </div>

                <div class="lg:col-span-5 flex items-center justify-end gap-2 pt-1 border-t border-zinc-100">
                    <x-button href="{{ route('admissions.applicants.index') }}" variant="secondary" size="xs">
                        Reset Filter
                    </x-button>
                    <x-button type="submit" variant="primary" size="xs">
                        Terapkan Filter
                    </x-button>
                </div>
            </form>
        </x-card>

        <!-- Applicants Table -->
        <x-card padding class="border-zinc-200">
            <div class="overflow-x-auto" data-scrollable>
                <table class="w-full text-left text-xs border-collapse min-w-[760px]">
                    <thead>
                        <tr class="border-b border-zinc-200 bg-zinc-50/75 text-zinc-600 font-semibold uppercase tracking-wider text-[11px]">
                            <th class="py-3 px-3">No. Registrasi</th>
                            <th class="py-3 px-3">Nama Calon Siswa</th>
                            <th class="py-3 px-3">Jalur & Periode</th>
                            <th class="py-3 px-3">Asal Sekolah</th>
                            <th class="py-3 px-3 text-center">Berkas</th>
                            <th class="py-3 px-3 text-center">Status</th>
                            <th class="py-3 px-3 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-100">
                        @forelse ($applicants as $applicant)
                            <tr class="hover:bg-zinc-50/50 transition-colors">
                                <td class="py-3 px-3 font-mono font-bold text-primary">
                                    {{ $applicant->registration_number }}
                                    <div class="text-[10px] text-zinc-400 font-normal">Token: {{ $applicant->tracking_code }}</div>
                                </td>
                                <td class="py-3 px-3">
                                    <div class="font-bold text-zinc-900">{{ $applicant->full_name }}</div>
                                    <div class="text-[11px] text-zinc-400">
                                        {{ $applicant->genderLabel() }} &bull; NISN: {{ $applicant->nisn ?? '-' }}
                                    </div>
                                </td>
                                <td class="py-3 px-3">
                                    <div class="font-semibold text-zinc-800">{{ $applicant->admissionTrack?->name }}</div>
                                    <div class="text-[11px] text-zinc-400">{{ $applicant->admissionPeriod?->name }}</div>
                                </td>
                                <td class="py-3 px-3 text-zinc-600">
                                    {{ $applicant->previous_school }}
                                </td>
                                <td class="py-3 px-3 text-center">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-medium bg-zinc-100 text-zinc-700">
                                        {{ $applicant->documents->count() }} file
                                    </span>
                                </td>
                                <td class="py-3 px-3 text-center">
                                    <x-badge :variant="$applicant->statusBadgeVariant()" size="xs">
                                        {{ $applicant->statusLabel() }}
                                    </x-badge>
                                </td>
                                <td class="py-3 px-3 text-right">
                                    <a href="{{ route('admissions.applicants.show', $applicant) }}" class="inline-flex items-center text-primary hover:underline font-semibold text-xs">
                                        Buka Dossier &rarr;
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="py-8 text-center text-zinc-400">
                                    Tidak ada data pendaftar yang sesuai dengan filter.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($applicants->hasPages())
                <div class="pt-4 border-t border-zinc-100">
                    {{ $applicants->links() }}
                </div>
            @endif
        </x-card>
    </div>
</x-layouts.app>
