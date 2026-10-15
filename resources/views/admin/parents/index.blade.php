<x-layouts.app>
    <x-slot:headerTitle>
        Manajemen Relasi Orang Tua & Siswa
    </x-slot:headerTitle>

    <div class="space-y-6">

        <!-- Top Header & Actions -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-4 border-b border-zinc-200">
            <div>
                <h1 class="text-xl sm:text-2xl font-extrabold text-zinc-900 tracking-tight">
                    Relasi Orang Tua & Siswa
                </h1>
                <p class="text-xs text-zinc-500 mt-0.5">
                    Kelola otorisasi akses orang tua, verifikasi penautan akun, dan buat kode undangan penautan mandiri
                </p>
            </div>

            <div class="flex items-center gap-2">
                <a
                    href="{{ route('admin.parents.create') }}"
                    class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-xs font-bold bg-[#E11D2E] hover:bg-[#C81423] text-white shadow-xs transition-colors"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    <span>Tautkan Manual</span>
                </a>
            </div>
        </div>

        @if (session('success'))
            <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-xs text-emerald-800 font-bold flex items-center gap-2">
                <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        <!-- Card: Buat Kode Penautan Baru untuk Siswa -->
        <div class="bg-white rounded-2xl border border-zinc-200 shadow-xs p-5">
            <h3 class="font-extrabold text-sm text-zinc-900 mb-1">Generate Kode Penautan Mandiri Siswa</h3>
            <p class="text-xs text-zinc-500 mb-4">Buat kode acak berbatas waktu untuk diberikan kepada wali murid agar dapat menautkan akun secara mandiri.</p>

            <form action="{{ route('admin.parents.generate-code') }}" method="POST" class="grid grid-cols-1 sm:grid-cols-4 gap-3 items-end">
                @csrf
                <div class="sm:col-span-2">
                    <label class="block text-xs font-semibold text-zinc-700 mb-1">Pilih Siswa</label>
                    <select name="student_id" required class="w-full text-xs rounded-xl border-zinc-300 focus:border-[#E11D2E] focus:ring-[#E11D2E]">
                        <option value="">-- Pilih Siswa Terdaftar --</option>
                        @foreach (\App\Models\User::where('role', \App\Enums\UserRole::STUDENT)->where('is_active', true)->orderBy('name')->get() as $st)
                            <option value="{{ $st->id }}">{{ $st->name }} (NISN: {{ $st->studentProfile?->nisn ?? '-' }})</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-zinc-700 mb-1">Jenis Hubungan</label>
                    <select name="relationship_type" class="w-full text-xs rounded-xl border-zinc-300 focus:border-[#E11D2E] focus:ring-[#E11D2E]">
                        <option value="guardian">Wali Murid</option>
                        <option value="father">Ayah Kandung</option>
                        <option value="mother">Ibu Kandung</option>
                        <option value="other">Keluarga / Lainnya</option>
                    </select>
                </div>
                <div>
                    <button type="submit" class="w-full px-4 py-2.5 rounded-xl text-xs font-bold bg-zinc-800 hover:bg-zinc-900 text-white transition-colors">
                        Buat Kode Unik
                    </button>
                </div>
            </form>

            @if ($activeCodes->isNotEmpty())
                <div class="mt-4 pt-4 border-t border-zinc-100">
                    <span class="text-[11px] font-bold text-zinc-400 uppercase tracking-wider block mb-2">Kode Aktif Belum Digunakan:</span>
                    <div class="flex flex-wrap gap-2">
                        @foreach ($activeCodes as $code)
                            <div class="p-2 rounded-lg bg-zinc-50 border border-zinc-200 text-xs flex items-center gap-2">
                                <span class="font-mono font-bold text-brand-600">{{ $code->code }}</span>
                                <span class="text-zinc-500">&bull; {{ $code->student?->name }}</span>
                                <span class="text-[10px] text-zinc-400">(Kedaluwarsa: {{ $code->expires_at->format('d/m') }})</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>

        <!-- Filter & Search Table -->
        <div class="bg-white rounded-2xl border border-zinc-200 shadow-xs overflow-hidden">
            <div class="p-4 border-b border-zinc-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <form action="{{ route('admin.parents.index') }}" method="GET" class="flex flex-wrap items-center gap-2 w-full sm:w-auto">
                    <input
                        type="text"
                        name="search"
                        value="{{ request('search') }}"
                        placeholder="Cari orang tua / siswa..."
                        class="text-xs rounded-xl border-zinc-300 focus:border-[#E11D2E] focus:ring-[#E11D2E] w-48 sm:w-64"
                    >
                    <select name="status" class="text-xs rounded-xl border-zinc-300 focus:border-[#E11D2E] focus:ring-[#E11D2E]">
                        <option value="">Semua Status</option>
                        <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Aktif</option>
                        <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Menunggu Verifikasi</option>
                        <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Ditolak</option>
                    </select>
                    <button type="submit" class="px-3.5 py-2 text-xs font-bold rounded-xl bg-zinc-800 text-white hover:bg-zinc-900 transition-colors">
                        Filter
                    </button>
                    @if (request()->hasAny(['search', 'status']))
                        <a href="{{ route('admin.parents.index') }}" class="text-xs font-semibold text-zinc-500 hover:text-zinc-800">Reset</a>
                    @endif
                </form>

                <span class="text-xs text-zinc-500 font-semibold shrink-0">
                    Total: {{ $relations->total() }} Relasi
                </span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-zinc-700">
                    <thead class="bg-zinc-50 border-b border-zinc-200 text-zinc-500 uppercase tracking-wider font-bold">
                        <tr>
                            <th class="px-4 py-3">Nama Orang Tua / Wali</th>
                            <th class="px-4 py-3">Siswa (Anak)</th>
                            <th class="px-4 py-3">Hubungan</th>
                            <th class="px-4 py-3">Status</th>
                            <th class="px-4 py-3">Waktu Verifikasi</th>
                            <th class="px-4 py-3 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-100">
                        @forelse ($relations as $rel)
                            <tr class="hover:bg-zinc-50/60 transition-colors">
                                <td class="px-4 py-3.5">
                                    <div class="font-bold text-zinc-900 text-sm">{{ $rel->parent?->name }}</div>
                                    <div class="text-[11px] text-zinc-500">{{ $rel->parent?->email }}</div>
                                </td>
                                <td class="px-4 py-3.5">
                                    <div class="font-bold text-zinc-900 text-sm">{{ $rel->student?->name }}</div>
                                    <div class="text-[11px] text-zinc-500">NISN: {{ $rel->student?->studentProfile?->nisn ?? '-' }}</div>
                                </td>
                                <td class="px-4 py-3.5 font-semibold text-zinc-700">
                                    {{ $rel->relationshipLabel() }}
                                </td>
                                <td class="px-4 py-3.5 whitespace-nowrap">
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold border {{ $rel->statusBadgeClasses() }}">
                                        {{ $rel->statusLabel() }}
                                    </span>
                                </td>
                                <td class="px-4 py-3.5 text-zinc-500 whitespace-nowrap text-[11px]">
                                    @if ($rel->verified_at)
                                        <div>{{ $rel->verified_at->format('d/m/Y H:i') }}</div>
                                        <div class="text-[10px] text-zinc-400">Oleh: {{ $rel->verifier?->name ?? 'Sistem' }}</div>
                                    @else
                                        <span class="text-zinc-400">-</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3.5 text-right whitespace-nowrap">
                                    @if ($rel->status === 'pending')
                                        <div class="flex items-center justify-end gap-1.5">
                                            <form action="{{ route('admin.parents.verify', $rel) }}" method="POST">
                                                @csrf
                                                <button type="submit" class="px-2.5 py-1 rounded-lg text-xs font-bold bg-emerald-600 text-white hover:bg-emerald-700 transition-colors">
                                                    Setujui
                                                </button>
                                            </form>
                                            <form action="{{ route('admin.parents.reject', $rel) }}" method="POST">
                                                @csrf
                                                <button type="submit" class="px-2.5 py-1 rounded-lg text-xs font-bold bg-rose-50 text-rose-700 hover:bg-rose-100 transition-colors">
                                                    Tolak
                                                </button>
                                            </form>
                                        </div>
                                    @else
                                        <span class="text-zinc-400 text-[11px]">Tuntas</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 py-8 text-center text-zinc-400">
                                    Belum ada relasi orang tua dan siswa terdaftar.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($relations->hasPages())
                <div class="p-4 border-t border-zinc-200 bg-zinc-50/50">
                    {{ $relations->links() }}
                </div>
            @endif
        </div>

    </div>
</x-layouts.app>
