<x-layouts.app>
    <x-slot:headerTitle>
        Manajemen Guru & Tenaga Kependidikan
    </x-slot:headerTitle>

    <div class="space-y-6">
        <!-- Alerts -->
        @if (session('success'))
            <x-alert variant="success" dismissible>
                {{ session('success') }}
            </x-alert>
        @endif

        @if ($errors->any())
            <x-alert variant="danger" dismissible>
                {{ $errors->first() }}
            </x-alert>
        @endif

        <!-- Action & Navigation Header -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h1 class="text-xl sm:text-2xl font-extrabold text-zinc-900 tracking-tight">
                    Pendidik & Tenaga Kependidikan (PTK)
                </h1>
                <p class="text-xs text-zinc-500 mt-0.5">
                    Kelola akun guru, staf kependidikan, penetapan jabatan, aktivasi mandiri, dan penugasan mengajar.
                </p>
            </div>

            <div class="flex items-center gap-2.5 flex-wrap">
                <x-button href="{{ route('staff.import.index') }}" variant="secondary" size="sm">
                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                    </svg>
                    Impor Massal (CSV)
                </x-button>

                <x-button href="{{ route('staff.create') }}" variant="primary" size="sm">
                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                    </svg>
                    Tambah Pegawai Baru
                </x-button>
            </div>
        </div>

        <!-- 5-Card Metis Metric Summary Strip -->
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-2.5 sm:gap-4">
            <x-card padding class="border-zinc-200">
                <div class="text-[11px] font-bold uppercase tracking-wider text-zinc-500">Total Pegawai</div>
                <div class="mt-1 text-2xl font-extrabold text-zinc-900">{{ $totalStaffCount }}</div>
                <div class="mt-0.5 text-xs text-zinc-400">Guru & staf terdaftar</div>
            </x-card>

            <x-card padding class="border-zinc-200">
                <div class="text-[11px] font-bold uppercase tracking-wider text-emerald-600">Guru / Pendidik</div>
                <div class="mt-1 text-2xl font-extrabold text-emerald-700">{{ $teacherCount }}</div>
                <div class="mt-0.5 text-xs text-zinc-400">Tenaga pengajar aktif</div>
            </x-card>

            <x-card padding class="border-zinc-200">
                <div class="text-[11px] font-bold uppercase tracking-wider text-blue-600">Staf Kependidikan</div>
                <div class="mt-1 text-2xl font-extrabold text-blue-700">{{ $educationStaffCount }}</div>
                <div class="mt-0.5 text-xs text-zinc-400">TU, laboran, pustakawan</div>
            </x-card>

            <x-card padding class="border-zinc-200">
                <div class="text-[11px] font-bold uppercase tracking-wider text-emerald-600">Akun Aktif</div>
                <div class="mt-1 text-2xl font-extrabold text-emerald-700">{{ $activeCount }}</div>
                <div class="mt-0.5 text-xs text-zinc-400">Dapat masuk ke portal</div>
            </x-card>

            <x-card padding class="border-zinc-200">
                <div class="text-[11px] font-bold uppercase tracking-wider text-amber-600">Menunggu Aktivasi</div>
                <div class="mt-1 text-2xl font-extrabold text-amber-700">{{ $pendingInvitationCount }}</div>
                <div class="mt-0.5 text-xs text-zinc-400">Tautan undangan terbit</div>
            </x-card>
        </div>

        <!-- Filter & Search Controls -->
        <x-card padding>
            <form method="GET" action="{{ route('staff.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
                <!-- Search Input -->
                <div class="lg:col-span-2">
                    <label for="search" class="block text-[11px] font-semibold text-zinc-600 uppercase tracking-wider mb-1">
                        Pencarian
                    </label>
                    <input
                        type="text"
                        name="search"
                        id="search"
                        value="{{ request('search') }}"
                        placeholder="Cari nama, email, NIP, atau NUPTK..."
                        class="w-full rounded-lg border border-zinc-300 text-zinc-900 text-xs py-2 px-3 focus:border-brand-500 focus:ring-2 focus:ring-brand-100"
                    />
                </div>

                <!-- Role Filter -->
                <div>
                    <label for="role" class="block text-[11px] font-semibold text-zinc-600 uppercase tracking-wider mb-1">
                        Peran Sistem
                    </label>
                    <select
                        name="role"
                        id="role"
                        onchange="this.form.submit()"
                        class="w-full rounded-lg border border-zinc-300 text-zinc-900 text-xs py-2 px-3 focus:border-brand-500 focus:ring-2 focus:ring-brand-100"
                    >
                        <option value="">-- Semua Peran --</option>
                        <option value="teacher" {{ request('role') === 'teacher' ? 'selected' : '' }}>Guru / Pendidik</option>
                        <option value="staff" {{ request('role') === 'staff' ? 'selected' : '' }}>Staf / Tenaga Kependidikan</option>
                        <option value="school_admin" {{ request('role') === 'school_admin' ? 'selected' : '' }}>Admin / Kepala Sekolah</option>
                    </select>
                </div>

                <!-- Position Filter -->
                <div>
                    <label for="position" class="block text-[11px] font-semibold text-zinc-600 uppercase tracking-wider mb-1">
                        Jabatan
                    </label>
                    <select
                        name="position"
                        id="position"
                        onchange="this.form.submit()"
                        class="w-full rounded-lg border border-zinc-300 text-zinc-900 text-xs py-2 px-3 focus:border-brand-500 focus:ring-2 focus:ring-brand-100"
                    >
                        <option value="">-- Semua Jabatan --</option>
                        @foreach ($positions as $pos)
                            <option value="{{ $pos->value }}" {{ request('position') === $pos->value ? 'selected' : '' }}>
                                {{ $pos->label() }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Status Filter & Submit -->
                <div>
                    <label for="status" class="block text-[11px] font-semibold text-zinc-600 uppercase tracking-wider mb-1">
                        Status
                    </label>
                    <div class="flex items-center gap-2">
                        <select
                            name="status"
                            id="status"
                            onchange="this.form.submit()"
                            class="w-full rounded-lg border border-zinc-300 text-zinc-900 text-xs py-2 px-3 focus:border-brand-500 focus:ring-2 focus:ring-brand-100"
                        >
                            <option value="">-- Semua --</option>
                            <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Aktif</option>
                            <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Non-Aktif</option>
                            <option value="pending_invitation" {{ request('status') === 'pending_invitation' ? 'selected' : '' }}>Menunggu Aktivasi</option>
                        </select>

                        <x-button type="submit" variant="secondary" size="sm" class="shrink-0">
                            Cari
                        </x-button>
                    </div>
                </div>
            </form>
        </x-card>

        <!-- Staff Table -->
        <x-card padding="none" class="overflow-hidden">
            @if ($staffMembers->isEmpty())
                <div class="p-8 text-center">
                    <div class="w-12 h-12 rounded-full bg-zinc-100 text-zinc-400 mx-auto flex items-center justify-center mb-3">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                    </div>
                    <h3 class="text-sm font-bold text-zinc-800">Tidak ada pegawai ditemukan</h3>
                    <p class="text-xs text-zinc-400 mt-1 max-w-sm mx-auto">
                        Coba sesuaikan filter pencarian atau tambahkan guru dan tenaga kependidikan baru.
                    </p>
                    <div class="mt-4 flex items-center justify-center gap-2">
                        <x-button href="{{ route('staff.create') }}" variant="primary" size="sm">
                            Tambah Pegawai Baru
                        </x-button>
                        <x-button href="{{ route('staff.import.index') }}" variant="secondary" size="sm">
                            Impor CSV
                        </x-button>
                    </div>
                </div>
            @else
                <div class="overflow-x-auto" data-scrollable>
                    <table class="w-full min-w-[700px] text-left text-xs">
                        <thead class="bg-zinc-50 border-b border-zinc-200 text-zinc-600 font-semibold uppercase tracking-wider text-[11px]">
                            <tr>
                                <th class="px-4 py-3">Nama Pegawai & Kontak</th>
                                <th class="px-4 py-3">NIP / NUPTK</th>
                                <th class="px-4 py-3">Jabatan & Unit</th>
                                <th class="px-4 py-3">Peran Akses</th>
                                <th class="px-4 py-3">Status Akun</th>
                                <th class="px-4 py-3 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-200">
                            @foreach ($staffMembers as $member)
                                <tr class="hover:bg-zinc-50/60 transition-colors">
                                    <td class="px-4 py-3">
                                        <div class="flex items-center gap-3">
                                            <div class="w-9 h-9 rounded-lg bg-zinc-100 text-zinc-700 font-bold flex items-center justify-center text-xs shrink-0 border border-zinc-200">
                                                {{ strtoupper(substr($member->name, 0, 2)) }}
                                            </div>
                                            <div>
                                                <div class="font-bold text-zinc-900">
                                                    <a href="{{ route('staff.show', $member) }}" class="hover:text-brand-600">
                                                        {{ $member->name }}
                                                    </a>
                                                </div>
                                                <div class="text-[11px] text-zinc-500">
                                                    {{ $member->email }}
                                                    @if ($member->phone)
                                                        &bull; {{ $member->phone }}
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="font-mono text-zinc-800 text-[11px]">
                                            {{ $member->staffProfile?->nip ?: ($member->staffProfile?->nuptk ?: '-') }}
                                        </div>
                                        <div class="text-[10px] text-zinc-400">
                                            {{ $member->staffProfile?->employmentStatusLabel() ?? 'GTY' }}
                                        </div>
                                    </td>
                                    <td class="px-4 py-3">
                                        <div>
                                            <x-badge :variant="$member->staffProfile?->position?->badgeVariant() ?? 'neutral'" size="sm">
                                                {{ $member->staffProfile?->position?->label() ?? 'Guru Mapel' }}
                                            </x-badge>
                                        </div>
                                        @if ($member->staffProfile?->work_unit)
                                            <div class="text-[11px] text-zinc-500 mt-0.5">
                                                Unit: {{ $member->staffProfile->work_unit }}
                                            </div>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3">
                                        <x-badge :variant="$member->role->badgeVariant()" size="sm">
                                            {{ $member->role->label() }}
                                        </x-badge>
                                    </td>
                                    <td class="px-4 py-3">
                                        @if ($member->staffProfile?->hasPendingInvitation())
                                            <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full text-[10px] font-semibold bg-amber-50 text-amber-700 border border-amber-200">
                                                <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span>
                                                Menunggu Aktivasi
                                            </span>
                                        @elseif ($member->is_active)
                                            <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full text-[10px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                                Aktif
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full text-[10px] font-semibold bg-rose-50 text-rose-700 border border-rose-200">
                                                <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                                                Non-Aktif
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-right">
                                        <div class="inline-flex items-center gap-1.5">
                                            <a
                                                href="{{ route('staff.show', $member) }}"
                                                class="text-zinc-600 hover:text-brand-600 p-1.5 rounded hover:bg-zinc-100"
                                                title="Lihat Detail Profil & Penugasan"
                                            >
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                                </svg>
                                            </a>

                                            <a
                                                href="{{ route('staff.edit', $member) }}"
                                                class="text-zinc-600 hover:text-brand-600 p-1.5 rounded hover:bg-zinc-100"
                                                title="Edit Profil & Jabatan"
                                            >
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                                </svg>
                                            </a>

                                            <!-- Toggle Active/Inactive Quick Action -->
                                            <form action="{{ route('staff.toggle-status', $member) }}" method="POST" class="inline" onsubmit="return confirm('Apakah Anda yakin ingin mengubah status keaktifan akun {{ $member->name }}?');">
                                                @csrf
                                                <button
                                                    type="submit"
                                                    class="p-1.5 rounded hover:bg-zinc-100 {{ $member->is_active ? 'text-zinc-400 hover:text-rose-600' : 'text-zinc-400 hover:text-emerald-600' }}"
                                                    title="{{ $member->is_active ? 'Nonaktifkan Akun' : 'Aktifkan Akun' }}"
                                                >
                                                    @if ($member->is_active)
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
                                                        </svg>
                                                    @else
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                        </svg>
                                                    @endif
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @if ($staffMembers->hasPages())
                    <div class="px-4 py-3 border-t border-zinc-200">
                        {{ $staffMembers->links() }}
                    </div>
                @endif
            @endif
        </x-card>
    </div>
</x-layouts.app>
