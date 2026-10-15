<x-layouts.app>
    <x-slot:headerTitle>
        Pratinjau Impor Peserta Didik
    </x-slot:headerTitle>

    <div class="space-y-6">
        <!-- Breadcrumb & Header -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <div class="flex items-center gap-2 text-xs text-zinc-500 mb-1">
                    <a href="{{ route('students.index') }}" class="hover:text-brand-600 transition-colors">Peserta Didik</a>
                    <span>/</span>
                    <a href="{{ route('students.import.index') }}" class="hover:text-brand-600 transition-colors">Impor Massal</a>
                    <span>/</span>
                    <span class="text-zinc-800 font-medium">Hasil Pratinjau</span>
                </div>
                <h1 class="text-xl sm:text-2xl font-extrabold text-zinc-900 tracking-tight">
                    Pratinjau & Validasi Data Siswa
                </h1>
                <p class="text-xs text-zinc-500 mt-0.5">
                    Tinjau kelengkapan identitas siswa sebelum akun resmi dibuat ke dalam sistem.
                </p>
            </div>

            <div class="flex items-center gap-2">
                <x-button href="{{ route('students.import.index') }}" variant="outline" size="sm">
                    Unggah Ulang
                </x-button>
            </div>
        </div>

        <!-- 3-Card Summary Strip -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <x-card padding class="border-zinc-200">
                <div class="text-[11px] font-bold uppercase tracking-wider text-zinc-500">Total Baris Terbaca</div>
                <div class="mt-1 text-2xl font-extrabold text-zinc-900">{{ count($rows) }}</div>
                <div class="mt-0.5 text-xs text-zinc-400">Baris data dari berkas CSV</div>
            </x-card>

            <x-card padding class="border-zinc-200">
                <div class="text-[11px] font-bold uppercase tracking-wider text-emerald-600">Baris Valid (Siap Impor)</div>
                <div class="mt-1 text-2xl font-extrabold text-emerald-700">{{ $validCount }}</div>
                <div class="mt-0.5 text-xs text-zinc-400">Identitas lengkap & email unik</div>
            </x-card>

            <x-card padding class="border-zinc-200">
                <div class="text-[11px] font-bold uppercase tracking-wider text-rose-600">Baris Tidak Valid</div>
                <div class="mt-1 text-2xl font-extrabold text-rose-700">{{ $invalidCount }}</div>
                <div class="mt-0.5 text-xs text-zinc-400">Ditolak karena duplikasi/format</div>
            </x-card>
        </div>

        @if ($selectedClassroom)
            <div class="p-3 bg-blue-50 border border-blue-200 rounded-xl text-xs text-blue-800 flex items-center gap-2">
                <svg class="w-4 h-4 text-blue-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span>Siswa yang valid akan otomatis ditempatkan ke rombongan belajar: <strong>{{ $selectedClassroom->name }}</strong>.</span>
            </div>
        @endif

        @if ($invalidCount > 0)
            <x-alert variant="warning">
                <div class="font-bold text-xs">Perhatian: Ditemukan {{ $invalidCount }} baris data tidak valid.</div>
                <p class="text-xs mt-0.5">
                    Hanya {{ $validCount }} baris data yang valid yang akan diproses. Anda dapat melanjutkan proses untuk baris yang valid, atau kembali mengunggah berkas yang telah diperbaiki.
                </p>
            </x-alert>
        @endif

        <!-- Table of previewed rows -->
        <x-card padding="none">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-zinc-200 bg-zinc-50/75 text-[11px] font-bold uppercase tracking-wider text-zinc-500">
                            <th class="py-3 px-4 w-14 text-center">Baris</th>
                            <th class="py-3 px-4">Nama Lengkap</th>
                            <th class="py-3 px-4">Email Akun</th>
                            <th class="py-3 px-4">NIS / NISN</th>
                            <th class="py-3 px-4">L/P</th>
                            <th class="py-3 px-4">Orang Tua / Wali</th>
                            <th class="py-3 px-4 text-center">Status Validasi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-200 text-xs">
                        @foreach ($rows as $row)
                            <tr class="{{ !empty($row['errors']) ? 'bg-rose-50/40' : 'hover:bg-zinc-50/60' }}">
                                <td class="py-3 px-4 text-center font-mono text-zinc-400">
                                    {{ $row['line'] }}
                                </td>
                                <td class="py-3 px-4 font-semibold text-zinc-900">
                                    {{ $row['name'] ?: '-' }}
                                </td>
                                <td class="py-3 px-4 font-mono text-zinc-600">
                                    {{ $row['email'] ?: '-' }}
                                </td>
                                <td class="py-3 px-4 font-mono text-zinc-600">
                                    {{ $row['nis'] ?: '-' }} / {{ $row['nisn'] ?: '-' }}
                                </td>
                                <td class="py-3 px-4 uppercase text-zinc-600">
                                    {{ $row['gender'] === 'female' ? 'P' : 'L' }}
                                </td>
                                <td class="py-3 px-4 text-zinc-700">
                                    {{ $row['parent_name'] ?: '-' }}
                                    @if ($row['parent_phone'])
                                        <span class="block text-[11px] text-zinc-400 font-mono">{{ $row['parent_phone'] }}</span>
                                    @endif
                                </td>
                                <td class="py-3 px-4 text-center">
                                    @if (empty($row['errors']))
                                        <x-badge variant="success" size="sm">Valid</x-badge>
                                    @else
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-medium bg-rose-100 text-rose-800" title="{{ implode('; ', $row['errors']) }}">
                                            Gagal: {{ $row['errors'][0] }}
                                        </span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Footer Action Form -->
            <div class="p-4 border-t border-zinc-200 bg-zinc-50 flex items-center justify-between">
                <span class="text-xs text-zinc-500">
                    {{ $validCount }} dari {{ count($rows) }} peserta didik siap diimpor.
                </span>

                <form method="POST" action="{{ route('students.import.process') }}">
                    @csrf
                    <input type="hidden" name="students" value="{{ json_encode($rows) }}">
                    <input type="hidden" name="classroom_id" value="{{ $classroomId }}">
                    <x-button type="submit" variant="primary" size="sm" :disabled="$validCount === 0">
                        Proses Impor {{ $validCount }} Peserta Didik
                    </x-button>
                </form>
            </div>
        </x-card>
    </div>
</x-layouts.app>
