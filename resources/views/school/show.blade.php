<x-layouts.app>
    <x-slot:headerTitle>
        Profil Sekolah
    </x-slot:headerTitle>

    <div class="space-y-6">
        <!-- Status / Flash Message -->
        @if (session('status'))
            <x-alert variant="success" dismissible>
                {{ session('status') }}
            </x-alert>
        @endif

        <!-- Header Card with School Brand Overview -->
        <div class="p-4 sm:p-6 rounded-xl bg-white border border-zinc-200 shadow-none flex flex-col md:flex-row items-start md:items-center justify-between gap-5 sm:gap-6">
            <div class="flex flex-col sm:flex-row items-start gap-4 sm:gap-5">
                <div class="w-16 h-16 sm:w-20 sm:h-20 rounded-xl bg-zinc-50 border border-zinc-200 flex items-center justify-center overflow-hidden shrink-0 shadow-2xs">
                    @if ($school?->logo_path)
                        <img src="{{ $school->logoUrl() }}" alt="{{ $school->name }}" class="w-full h-full object-contain p-2">
                    @else
                        <div class="text-center p-2 text-zinc-400">
                            <svg class="w-8 h-8 mx-auto mb-0.5 text-zinc-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                            </svg>
                            <span class="text-[9px] font-semibold uppercase tracking-wider">Logo Sekolah</span>
                        </div>
                    @endif
                </div>

                <div>
                    <div class="flex items-center gap-2 mb-1">
                        <h1 class="text-xl sm:text-2xl font-extrabold text-zinc-900 tracking-tight">
                            {{ $school?->name ?? 'Yayasan Perguruan Nusantara Utama' }}
                        </h1>
                        @if ($school?->is_active)
                            <x-badge variant="success" size="sm" dot>Aktif</x-badge>
                        @else
                            <x-badge variant="danger" size="sm" dot>Non-Aktif</x-badge>
                        @endif
                    </div>
                    <p class="text-xs text-zinc-500 font-medium">
                        NPSN: <span class="font-bold text-zinc-700 font-mono">{{ $school?->npsn ?? '20104567' }}</span>
                        @if ($school?->short_name)
                            &bull; Alias: <span class="text-zinc-700">{{ $school->short_name }}</span>
                        @endif
                    </p>

                    <!-- Jenjang Badges -->
                    <div class="mt-2.5 flex flex-wrap gap-1.5 items-center">
                        @if (!empty($school?->educational_levels))
                            @foreach ($school->educational_levels as $lvl)
                                <x-badge variant="primary" size="sm">
                                    {{ strtoupper($lvl) }}
                                </x-badge>
                            @endforeach
                        @else
                            <x-badge variant="primary" size="sm">SMP</x-badge>
                            <x-badge variant="primary" size="sm">SMA</x-badge>
                        @endif
                    </div>
                </div>
            </div>

            <div class="flex items-center gap-2.5 shrink-0 w-full sm:w-auto">
                <x-button href="{{ route('school.profile.edit') }}" variant="primary" size="md" class="w-full sm:w-auto">
                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                    </svg>
                    Ubah Profil Sekolah
                </x-button>
            </div>
        </div>

        <!-- Detail Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Informasi Kontak & Lokasi -->
            <x-card title="Informasi Alamat & Kontak Resmi">
                <dl class="space-y-3.5 text-xs">
                    <div class="flex flex-col sm:flex-row sm:justify-between pb-2.5 border-b border-slate-100 gap-1">
                        <dt class="text-slate-500 font-medium">Alamat Lengkap</dt>
                        <dd class="font-semibold text-slate-800 text-right sm:max-w-xs">{{ $school?->address ?? 'Jl. Pendidikan Karakter No. 45' }}</dd>
                    </div>
                    <div class="flex items-center justify-between pb-2.5 border-b border-slate-100">
                        <dt class="text-slate-500 font-medium">Kota / Kabupaten</dt>
                        <dd class="font-semibold text-slate-800">{{ $school?->city ?? 'Jakarta Selatan' }}</dd>
                    </div>
                    <div class="flex items-center justify-between pb-2.5 border-b border-slate-100">
                        <dt class="text-slate-500 font-medium">Provinsi</dt>
                        <dd class="font-semibold text-slate-800">{{ $school?->province ?? 'DKI Jakarta' }}</dd>
                    </div>
                    <div class="flex items-center justify-between pb-2.5 border-b border-slate-100">
                        <dt class="text-slate-500 font-medium">Kode Pos</dt>
                        <dd class="font-semibold text-slate-800 font-mono">{{ $school?->postal_code ?? '12150' }}</dd>
                    </div>
                    <div class="flex items-center justify-between pb-2.5 border-b border-slate-100">
                        <dt class="text-slate-500 font-medium">Telepon Sekolah</dt>
                        <dd class="font-semibold text-slate-800">{{ $school?->phone ?? '-' }}</dd>
                    </div>
                    <div class="flex items-center justify-between pb-2.5 border-b border-slate-100">
                        <dt class="text-slate-500 font-medium">Email Resmi</dt>
                        <dd class="font-semibold text-brand-600">{{ $school?->email ?? '-' }}</dd>
                    </div>
                    <div class="flex items-center justify-between">
                        <dt class="text-slate-500 font-medium">Website</dt>
                        <dd>
                            @if ($school?->website)
                                <a href="{{ $school->website }}" target="_blank" class="font-semibold text-brand-600 hover:underline">
                                    {{ $school->website }}
                                </a>
                            @else
                                <span class="text-slate-400">-</span>
                            @endif
                        </dd>
                    </div>
                </dl>
            </x-card>

            <!-- Status Akademik Terhubung -->
            <x-card title="Periode Akademik Aktif">
                <div class="space-y-4">
                    <div class="p-4 rounded-xl bg-brand-50 border border-brand-200">
                        <div class="flex items-center justify-between mb-1.5">
                            <span class="text-xs font-bold text-brand-900 uppercase tracking-wider">Tahun Ajaran Aktif</span>
                            <x-badge variant="primary" size="sm" dot>Berjalan</x-badge>
                        </div>
                        <h4 class="text-xl font-extrabold text-brand-700">
                            {{ $school?->activeAcademicYear?->name ?? 'Belum Ditentukan' }}
                        </h4>
                        @if ($school?->activeAcademicYear)
                            <p class="text-[11px] text-brand-800 mt-1">
                                Periode: {{ $school->activeAcademicYear->start_date->format('d/m/Y') }} s/d {{ $school->activeAcademicYear->end_date->format('d/m/Y') }}
                            </p>
                        @endif
                    </div>

                    <div class="p-4 rounded-xl bg-slate-50 border border-slate-200">
                        <div class="flex items-center justify-between mb-1.5">
                            <span class="text-xs font-bold text-slate-700 uppercase tracking-wider">Semester Aktif</span>
                            @if ($school?->activeAcademicYear?->activeSemester)
                                <x-badge variant="success" size="sm" dot>Aktif</x-badge>
                            @else
                                <x-badge variant="neutral" size="sm">Belum Ada</x-badge>
                            @endif
                        </div>
                        <h4 class="text-lg font-bold text-slate-900">
                            {{ $school?->activeAcademicYear?->activeSemester?->name ?? 'Belum Ditentukan' }}
                        </h4>
                        @if ($school?->activeAcademicYear?->activeSemester)
                            <p class="text-[11px] text-slate-500 mt-1">
                                Periode: {{ $school->activeAcademicYear->activeSemester->start_date->format('d/m/Y') }} s/d {{ $school->activeAcademicYear->activeSemester->end_date->format('d/m/Y') }}
                            </p>
                        @endif
                    </div>

                    <div class="flex items-center justify-between pt-2">
                        <x-button href="{{ route('academic-years.index') }}" variant="outline" size="sm">
                            Kelola Tahun Ajaran &rarr;
                        </x-button>
                        <x-button href="{{ route('semesters.index') }}" variant="outline" size="sm">
                            Kelola Semester &rarr;
                        </x-button>
                    </div>
                </div>
            </x-card>
        </div>
    </div>
</x-layouts.app>
