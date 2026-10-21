<x-layouts.app>
    <x-slot:headerTitle>
        Profil Pegawai: {{ $staff->name }}
    </x-slot:headerTitle>

    <div class="space-y-6" x-data="{ activeTab: 'profile', showAssignModal: false }">
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

        <!-- Quick Copy Flash Activation Banner (If just created or resent) -->
        @if (session('flash_activation_url') || ($staff->staffProfile?->hasPendingInvitation()))
            @php
                $actUrl = session('flash_activation_url') ?? route('staff.activate', ['token' => $staff->staffProfile->invitation_token]);
            @endphp
            <div class="p-4 rounded-xl bg-amber-50 border border-amber-200 text-amber-900" x-data="{ copied: false }">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                    <div class="flex items-start gap-2.5">
                        <svg class="w-5 h-5 text-amber-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <div>
                            <div class="text-xs font-bold text-amber-900">Tautan Aktivasi Akun Mandiri Siap Digunakan</div>
                            <p class="text-xs text-amber-700 mt-0.5">
                                Bagikan tautan berikut kepada <strong>{{ $staff->name }}</strong> agar dapat mengatur kata sandi pertamanya.
                            </p>
                            <div class="mt-2 flex items-center gap-2">
                                <input
                                    type="text"
                                    readonly
                                    value="{{ $actUrl }}"
                                    class="w-full sm:w-96 text-xs bg-white border border-amber-300 rounded px-2.5 py-1 text-zinc-700 select-all font-mono"
                                />
                                <button
                                    type="button"
                                    @click="navigator.clipboard.writeText('{{ $actUrl }}'); copied = true; setTimeout(() => copied = false, 2500)"
                                    class="px-3 py-1 bg-amber-600 hover:bg-amber-700 text-white font-semibold text-xs rounded transition-colors"
                                >
                                    <span x-show="!copied">Salin Tautan</span>
                                    <span x-show="copied" style="display: none;">Tersalin!</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <!-- Profile Header Card -->
        <x-card padding class="border-zinc-200">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div class="flex items-center gap-4">
                    <div class="w-16 h-16 rounded-2xl bg-brand-600 text-white font-extrabold flex items-center justify-center text-xl shadow-xs shrink-0">
                        {{ strtoupper(substr($staff->name, 0, 2)) }}
                    </div>
                    <div>
                        <div class="flex items-center gap-2 flex-wrap">
                            <h1 class="text-xl sm:text-2xl font-extrabold text-zinc-900 tracking-tight">
                                {{ $staff->name }}
                            </h1>
                            <x-badge :variant="$staff->staffProfile?->position?->badgeVariant() ?? 'neutral'" size="sm">
                                {{ $staff->staffProfile?->position?->label() ?? 'Guru' }}
                            </x-badge>
                            <x-badge :variant="$staff->role->badgeVariant()" size="sm">
                                {{ $staff->role->label() }}
                            </x-badge>
                        </div>
                        <p class="text-xs text-zinc-500 mt-1 flex items-center gap-3 flex-wrap">
                            <span>Email: <strong class="text-zinc-700">{{ $staff->email }}</strong></span>
                            @if ($staff->staffProfile?->nip)
                                <span>&bull; NIP: <strong class="text-zinc-700">{{ $staff->staffProfile->nip }}</strong></span>
                            @endif
                            @if ($staff->staffProfile?->work_unit)
                                <span>&bull; Unit: <strong class="text-zinc-700">{{ $staff->staffProfile->work_unit }}</strong></span>
                            @endif
                        </p>
                    </div>
                </div>

                <!-- Header Actions -->
                <div class="flex items-center gap-2 flex-wrap">
                    @if ($staff->staffProfile?->hasPendingInvitation())
                        <form action="{{ route('staff.resend-invitation', $staff) }}" method="POST" class="inline">
                            @csrf
                            <x-button type="submit" variant="secondary" size="sm">
                                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                </svg>
                                Buat Ulang Undangan
                            </x-button>
                        </form>
                    @endif

                    <x-button href="{{ route('staff.edit', $staff) }}" variant="secondary" size="sm">
                        <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                        </svg>
                        Edit Profil
                    </x-button>

                    <!-- Toggle Status Button -->
                    @if (auth()->id() !== $staff->id)
                        <form action="{{ route('staff.toggle-status', $staff) }}" method="POST" class="inline" onsubmit="return confirm('Apakah Anda yakin ingin mengubah status akun {{ $staff->name }}?');">
                            @csrf
                            @if ($staff->is_active)
                                <x-button type="submit" variant="danger" size="sm">
                                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
                                    </svg>
                                    Nonaktifkan Akun
                                </x-button>
                            @else
                                <x-button type="submit" variant="success" size="sm">
                                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    Aktifkan Akun
                                </x-button>
                            @endif
                        </form>
                    @endif
                </div>
            </div>

            <!-- Operational Dependency Summary -->
            @if ($dependencyCount['teachingAssignments'] > 0 || $dependencyCount['homeroomClassrooms'] > 0 || $dependencyCount['learningMaterials'] > 0 || $dependencyCount['assignments'] > 0)
                <div class="mt-4 pt-3 border-t border-zinc-100 flex items-center gap-4 text-xs text-zinc-500 flex-wrap">
                    <span class="font-semibold text-zinc-700">Tanggung Jawab Akademik:</span>
                    <span class="bg-zinc-100 px-2 py-0.5 rounded text-zinc-700 font-medium">
                        {{ $dependencyCount['teachingAssignments'] }} Penugasan Mengajar
                    </span>
                    <span class="bg-zinc-100 px-2 py-0.5 rounded text-zinc-700 font-medium">
                        {{ $dependencyCount['homeroomClassrooms'] }} Rombel Wali Kelas
                    </span>
                    <span class="bg-zinc-100 px-2 py-0.5 rounded text-zinc-700 font-medium">
                        {{ $dependencyCount['learningMaterials'] }} Materi Belajar
                    </span>
                    <span class="bg-zinc-100 px-2 py-0.5 rounded text-zinc-700 font-medium">
                        {{ $dependencyCount['assignments'] }} Tugas Siswa
                    </span>
                </div>
            @endif
        </x-card>

        <!-- Navigation Tabs -->
        <div class="border-b border-zinc-200 overflow-x-auto" data-scrollable>
            <nav class="flex space-x-4 sm:space-x-6 whitespace-nowrap min-w-full">
                <button
                    type="button"
                    @click="activeTab = 'profile'"
                    :class="activeTab === 'profile' ? 'border-brand-600 text-brand-600 font-bold' : 'border-transparent text-zinc-500 hover:text-zinc-700 font-medium'"
                    class="py-3 px-1 border-b-2 text-xs transition-colors shrink-0"
                >
                    Informasi Profil & Kepegawaian
                </button>

                <button
                    type="button"
                    @click="activeTab = 'assignments'"
                    :class="activeTab === 'assignments' ? 'border-brand-600 text-brand-600 font-bold' : 'border-transparent text-zinc-500 hover:text-zinc-700 font-medium'"
                    class="py-3 px-1 border-b-2 text-xs transition-colors"
                >
                    Penugasan Mengajar ({{ $staff->teachingAssignments->count() }})
                </button>

                <button
                    type="button"
                    @click="activeTab = 'history'"
                    :class="activeTab === 'history' ? 'border-brand-600 text-brand-600 font-bold' : 'border-transparent text-zinc-500 hover:text-zinc-700 font-medium'"
                    class="py-3 px-1 border-b-2 text-xs transition-colors"
                >
                    Riwayat Akun & Audit ({{ $staff->staffStatusHistories->count() }})
                </button>
            </nav>
        </div>

        <!-- TAB 1: Profile Information -->
        <div x-show="activeTab === 'profile'" class="space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Personal & Official Details -->
                <x-card padding>
                    <h2 class="text-xs font-bold uppercase tracking-wider text-zinc-400 mb-3">
                        Identitas & Nomor Kedinasan
                    </h2>
                    <dl class="divide-y divide-zinc-100 text-xs">
                        <div class="py-2.5 flex justify-between">
                            <dt class="text-zinc-500">NIP</dt>
                            <dd class="font-semibold text-zinc-900 font-mono">{{ $staff->staffProfile?->nip ?: '-' }}</dd>
                        </div>
                        <div class="py-2.5 flex justify-between">
                            <dt class="text-zinc-500">NUPTK</dt>
                            <dd class="font-semibold text-zinc-900 font-mono">{{ $staff->staffProfile?->nuptk ?: '-' }}</dd>
                        </div>
                        <div class="py-2.5 flex justify-between">
                            <dt class="text-zinc-500">NIK (KTP)</dt>
                            <dd class="font-semibold text-zinc-900 font-mono">{{ $staff->staffProfile?->nik ?: '-' }}</dd>
                        </div>
                        <div class="py-2.5 flex justify-between">
                            <dt class="text-zinc-500">Jenis Kelamin</dt>
                            <dd class="font-semibold text-zinc-900">{{ $staff->staffProfile?->genderLabel() }}</dd>
                        </div>
                        <div class="py-2.5 flex justify-between">
                            <dt class="text-zinc-500">Tempat, Tanggal Lahir</dt>
                            <dd class="font-semibold text-zinc-900">
                                {{ $staff->staffProfile?->birth_place ?? '-' }},
                                {{ $staff->staffProfile?->birth_date ? $staff->staffProfile->birth_date->format('d/m/Y') : '-' }}
                            </dd>
                        </div>
                        <div class="py-2.5 flex justify-between">
                            <dt class="text-zinc-500">Pendidikan Terakhir</dt>
                            <dd class="font-semibold text-zinc-900">{{ $staff->staffProfile?->last_education ?: '-' }}</dd>
                        </div>
                    </dl>
                </x-card>

                <!-- Employment & School Details -->
                <x-card padding>
                    <h2 class="text-xs font-bold uppercase tracking-wider text-zinc-400 mb-3">
                        Status Kepegawaian & Penempatan
                    </h2>
                    <dl class="divide-y divide-zinc-100 text-xs">
                        <div class="py-2.5 flex justify-between">
                            <dt class="text-zinc-500">Jabatan Sekolah</dt>
                            <dd class="font-semibold text-zinc-900">{{ $staff->staffProfile?->position?->label() ?? '-' }}</dd>
                        </div>
                        <div class="py-2.5 flex justify-between">
                            <dt class="text-zinc-500">Unit Kerja</dt>
                            <dd class="font-semibold text-zinc-900">{{ $staff->staffProfile?->work_unit ?: '-' }}</dd>
                        </div>
                        <div class="py-2.5 flex justify-between">
                            <dt class="text-zinc-500">Status Kepegawaian</dt>
                            <dd class="font-semibold text-zinc-900">{{ $staff->staffProfile?->employmentStatusLabel() ?? '-' }}</dd>
                        </div>
                        <div class="py-2.5 flex justify-between">
                            <dt class="text-zinc-500">Mulai Tugas (Join Date)</dt>
                            <dd class="font-semibold text-zinc-900">
                                {{ $staff->staffProfile?->join_date ? $staff->staffProfile->join_date->format('d/m/Y') : '-' }}
                            </dd>
                        </div>
                        <div class="py-2.5 flex justify-between">
                            <dt class="text-zinc-500">Status Akun Portal</dt>
                            <dd>
                                <x-badge :variant="$staff->is_active ? 'success' : 'danger'" size="sm">
                                    {{ $staff->is_active ? 'Aktif' : 'Non-Aktif' }}
                                </x-badge>
                            </dd>
                        </div>
                        <div class="py-2.5 flex justify-between">
                            <dt class="text-zinc-500">Aktivasi Akun</dt>
                            <dd class="font-semibold text-zinc-900">
                                {{ $staff->staffProfile?->activated_at ? $staff->staffProfile->activated_at->format('d/m/Y H:i') : ($staff->email_verified_at ? 'Terverifikasi' : 'Menunggu Aktivasi') }}
                            </dd>
                        </div>
                    </dl>
                </x-card>

                <!-- Contact & Address -->
                <x-card padding class="md:col-span-2">
                    <h2 class="text-xs font-bold uppercase tracking-wider text-zinc-400 mb-3">
                        Kontak & Alamat Domisili
                    </h2>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                        <div>
                            <span class="text-zinc-500 block mb-1">Alamat Tempat Tinggal:</span>
                            <p class="font-medium text-zinc-800 bg-zinc-50 p-2.5 rounded-lg border border-zinc-200">
                                {{ $staff->staffProfile?->address ?: 'Belum diisi' }}
                            </p>
                        </div>
                        <div>
                            <span class="text-zinc-500 block mb-1">Catatan Khusus:</span>
                            <p class="font-medium text-zinc-800 bg-zinc-50 p-2.5 rounded-lg border border-zinc-200">
                                {{ $staff->staffProfile?->notes ?: 'Tidak ada catatan tambahan.' }}
                            </p>
                        </div>
                    </div>
                </x-card>
            </div>
        </div>

        <!-- TAB 2: Teaching Assignments -->
        <div x-show="activeTab === 'assignments'" style="display: none;" class="space-y-6">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div>
                    <h2 class="text-sm font-bold text-zinc-900">Penugasan Mengajar Guru</h2>
                    <p class="text-xs text-zinc-500">
                        Daftar mata pelajaran dan rombongan belajar yang resmi diampu oleh {{ $staff->name }}.
                    </p>
                </div>

                <x-button type="button" @click="showAssignModal = !showAssignModal" variant="primary" size="sm" class="shrink-0">
                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Tambah Penugasan Mengajar
                </x-button>
            </div>

            <!-- Form Modal / Inline Box for Adding Assignment -->
            <div x-show="showAssignModal" x-transition class="p-4 rounded-xl bg-zinc-50 border border-zinc-200">
                <form method="POST" action="{{ route('staff.teaching-assignments.store', $staff) }}" class="space-y-4">
                    @csrf
                    <div class="text-xs font-bold text-zinc-900 border-b border-zinc-200 pb-2">
                        Formulir Penetapan Penugasan Mengajar Baru
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
                        <div>
                            <label for="classroom_id" class="block text-sm font-medium text-zinc-700 mb-1.5">
                                Rombel / Kelas <span class="text-[#E11D2E]">*</span>
                            </label>
                            <select name="classroom_id" id="classroom_id" required class="w-full rounded-lg border-zinc-200 text-base py-2.5 pl-3.5 pr-11">
                                <option value="">-- Pilih Kelas --</option>
                                @foreach ($classrooms as $c)
                                    <option value="{{ $c->id }}">{{ $c->name }} ({{ $c->gradeLevel?->name }})</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label for="subject_id" class="block text-sm font-medium text-zinc-700 mb-1.5">
                                Mata Pelajaran <span class="text-[#E11D2E]">*</span>
                            </label>
                            <select name="subject_id" id="subject_id" required class="w-full rounded-lg border-zinc-200 text-base py-2.5 pl-3.5 pr-11">
                                <option value="">-- Pilih Mapel --</option>
                                @foreach ($subjects as $s)
                                    <option value="{{ $s->id }}">{{ $s->name }} ({{ $s->code }})</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label for="academic_year_id" class="block text-sm font-medium text-zinc-700 mb-1.5">
                                Tahun Ajaran <span class="text-[#E11D2E]">*</span>
                            </label>
                            <select name="academic_year_id" id="academic_year_id" required class="w-full rounded-lg border-zinc-200 text-base py-2.5 pl-3.5 pr-11">
                                @foreach ($academicYears as $ay)
                                    <option value="{{ $ay->id }}" {{ $ay->is_active ? 'selected' : '' }}>{{ $ay->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label for="hours_per_week" class="block text-sm font-medium text-zinc-700 mb-1.5">
                                Jam / Minggu <span class="text-[#E11D2E]">*</span>
                            </label>
                            <input type="number" name="hours_per_week" id="hours_per_week" value="4" min="1" max="50" required class="w-full rounded-lg border-zinc-200 text-base py-2.5 px-3.5 font-mono">
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-3">
                        <x-button type="button" @click="showAssignModal = false" variant="secondary" size="md">Batal</x-button>
                        <x-button type="submit" variant="primary" size="md">Simpan Penugasan</x-button>
                    </div>
                </form>
            </div>

            <!-- List of Assignments Table -->
            <x-card padding="none" class="overflow-hidden">
                @if ($staff->teachingAssignments->isEmpty())
                    <div class="p-8 text-center">
                        <p class="text-xs text-zinc-400">Guru ini belum memiliki penugasan mengajar aktif.</p>
                    </div>
                @else
                    <div class="overflow-x-auto" data-scrollable>
                        <table class="w-full min-w-[640px] text-left text-xs">
                        <thead class="bg-zinc-50 border-b border-zinc-200 text-zinc-600 font-semibold uppercase tracking-wider text-[11px]">
                            <tr>
                                <th class="px-4 py-3">Mata Pelajaran</th>
                                <th class="px-4 py-3">Rombel / Kelas</th>
                                <th class="px-4 py-3">Tahun Ajaran</th>
                                <th class="px-4 py-3">Beban Jam</th>
                                <th class="px-4 py-3">Status</th>
                                <th class="px-4 py-3 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-200">
                            @foreach ($staff->teachingAssignments as $ta)
                                <tr class="hover:bg-zinc-50/60 transition-colors">
                                    <td class="px-4 py-3 font-bold text-zinc-900">
                                        {{ $ta->subject?->name ?? '-' }}
                                        <span class="text-[10px] text-zinc-400 block font-normal">{{ $ta->subject?->code }}</span>
                                    </td>
                                    <td class="px-4 py-3 font-semibold text-zinc-800">
                                        {{ $ta->classroom?->name ?? '-' }}
                                    </td>
                                    <td class="px-4 py-3 text-zinc-600">
                                        {{ $ta->academicYear?->name ?? '-' }}
                                    </td>
                                    <td class="px-4 py-3 text-zinc-700">
                                        {{ $ta->hours_per_week }} Jam/Minggu
                                    </td>
                                    <td class="px-4 py-3">
                                        <x-badge :variant="$ta->is_active ? 'success' : 'neutral'" size="sm">
                                            {{ $ta->is_active ? 'Aktif' : 'Non-Aktif' }}
                                        </x-badge>
                                    </td>
                                    <td class="px-4 py-3 text-right">
                                        <form action="{{ route('staff.teaching-assignments.destroy', $ta) }}" method="POST" class="inline" onsubmit="return confirm('Hapus / nonaktifkan penugasan mengajar ini?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="p-1 rounded text-zinc-400 hover:text-rose-600 hover:bg-zinc-100" title="Hapus Penugasan">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                </svg>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    </div>
                @endif
            </x-card>

            <!-- Homeroom Classrooms Info -->
            @if ($staff->homeroomClassrooms->isNotEmpty())
                <x-card padding>
                    <h3 class="text-xs font-bold uppercase tracking-wider text-zinc-500 mb-2">Tugas Tambahan: Wali Kelas</h3>
                    <div class="flex items-center gap-2 flex-wrap">
                        @foreach ($staff->homeroomClassrooms as $hc)
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg bg-emerald-50 text-emerald-800 text-xs font-semibold border border-emerald-200">
                                Wali Kelas {{ $hc->name }} (Kapasitas: {{ $hc->capacity }} siswa)
                            </span>
                        @endforeach
                    </div>
                </x-card>
            @endif
        </div>

        <!-- TAB 3: Audit Trail & Account Histories -->
        <div x-show="activeTab === 'history'" style="display: none;" class="space-y-6">
            <x-card padding="none" class="overflow-hidden">
                @if ($staff->staffStatusHistories->isEmpty())
                    <div class="p-8 text-center text-xs text-zinc-400">
                        Belum ada riwayat perubahan status pada akun ini.
                    </div>
                @else
                    <div class="overflow-x-auto" data-scrollable>
                        <table class="w-full min-w-[600px] text-left text-xs">
                        <thead class="bg-zinc-50 border-b border-zinc-200 text-zinc-600 font-semibold uppercase tracking-wider text-[11px]">
                            <tr>
                                <th class="px-4 py-3">Waktu Perubahan</th>
                                <th class="px-4 py-3">Aksi / Tindakan</th>
                                <th class="px-4 py-3">Pelaku Perubahan</th>
                                <th class="px-4 py-3">Keterangan / Alasan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-200">
                            @foreach ($staff->staffStatusHistories as $history)
                                <tr class="hover:bg-zinc-50/60 transition-colors">
                                    <td class="px-4 py-3 font-mono text-zinc-600">
                                        {{ $history->created_at->format('d/m/Y H:i:s') }}
                                    </td>
                                    <td class="px-4 py-3 font-semibold text-zinc-900">
                                        {{ $history->actionLabel() }}
                                    </td>
                                    <td class="px-4 py-3 text-zinc-700">
                                        {{ $history->actor?->name ?? 'Sistem' }}
                                    </td>
                                    <td class="px-4 py-3 text-zinc-500">
                                        {{ $history->reason ?: '-' }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    </div>
                @endif
            </x-card>
        </div>
    </div>
</x-layouts.app>
