<x-layouts.app>
    <x-slot:headerTitle>
        Pratinjau Impor Guru & Staf
    </x-slot:headerTitle>

    <div class="space-y-6">
        <!-- Breadcrumb & Header -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <div class="flex items-center gap-2 text-xs text-zinc-500 mb-1">
                    <a href="{{ route('staff.index') }}" class="hover:text-brand-600 transition-colors">Pendidik & Staf</a>
                    <span>/</span>
                    <a href="{{ route('staff.import.index') }}" class="hover:text-brand-600 transition-colors">Impor Massal</a>
                    <span>/</span>
                    <span class="text-zinc-800 font-medium">Hasil Pratinjau</span>
                </div>
                <h1 class="text-xl sm:text-2xl font-extrabold text-zinc-900 tracking-tight">
                    Pratinjau & Validasi Data Pegawai
                </h1>
                <p class="text-xs text-zinc-500 mt-0.5">
                    Tinjau hasil pembacaan berkas sebelum akun resmi dibuat ke dalam sistem.
                </p>
            </div>

            <div class="flex items-center gap-2">
                <x-button href="{{ route('staff.import.index') }}" variant="secondary" size="sm">
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
                <div class="mt-0.5 text-xs text-zinc-400">Data lengkap & email unik</div>
            </x-card>

            <x-card padding class="border-zinc-200">
                <div class="text-[11px] font-bold uppercase tracking-wider text-rose-600">Baris Tidak Valid</div>
                <div class="mt-1 text-2xl font-extrabold text-rose-700">{{ $invalidCount }}</div>
                <div class="mt-0.5 text-xs text-zinc-400">Memerlukan perbaikan berkas</div>
            </x-card>
        </div>

        <!-- Warning if invalid rows exist -->
        @if ($invalidCount > 0)
            <x-alert variant="warning">
                <div class="font-bold text-xs">Perhatian: Ditemukan {{ $invalidCount }} baris data tidak valid.</div>
                <p class="text-xs mt-0.5">
                    Hanya {{ $validCount }} baris data yang valid yang akan diproses. Anda dapat melanjutkan proses untuk baris yang valid, atau kembali mengunggah berkas yang telah diperbaiki.
                </p>
            </x-alert>
        @endif

        <!-- Preview Table -->
        <x-card padding="none" class="overflow-hidden">
            <div class="overflow-x-auto" data-scrollable>
                <table class="w-full min-w-[700px] text-left text-xs">
                    <thead class="bg-zinc-50 border-b border-zinc-200 text-zinc-600 font-semibold uppercase tracking-wider text-[11px]">
                        <tr>
                            <th class="px-4 py-3">Baris</th>
                            <th class="px-4 py-3">Nama Pegawai</th>
                            <th class="px-4 py-3">Email</th>
                            <th class="px-4 py-3">Peran & Jabatan</th>
                            <th class="px-4 py-3">NIP / NUPTK</th>
                            <th class="px-4 py-3">Status Validasi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-200">
                        @foreach ($rows as $r)
                            <tr class="{{ $r['is_valid'] ? 'hover:bg-zinc-50/60' : 'bg-rose-50/30' }}">
                                <td class="px-4 py-3 font-mono text-zinc-400">
                                    #{{ $r['row_number'] }}
                                </td>
                                <td class="px-4 py-3 font-bold text-zinc-900">
                                    {{ $r['name'] ?: '-' }}
                                </td>
                                <td class="px-4 py-3 text-zinc-600 font-mono">
                                    {{ $r['email'] ?: '-' }}
                                </td>
                                <td class="px-4 py-3">
                                    <span class="inline-block px-1.5 py-0.5 rounded text-[10px] font-semibold bg-zinc-100 text-zinc-800">
                                        {{ $r['role'] }}
                                    </span>
                                    <span class="inline-block text-[11px] text-zinc-500 ml-1">
                                        {{ $r['position'] }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-zinc-600 font-mono">
                                    {{ $r['nip'] ?: ($r['nuptk'] ?: '-') }}
                                </td>
                                <td class="px-4 py-3">
                                    @if ($r['is_valid'])
                                        <span class="inline-flex items-center gap-1 text-[11px] font-semibold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded border border-emerald-200">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                            </svg>
                                            Valid
                                        </span>
                                    @else
                                        <div class="text-rose-700">
                                            <span class="inline-flex items-center gap-1 text-[11px] font-semibold bg-rose-50 px-2 py-0.5 rounded border border-rose-200">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                                </svg>
                                                Gagal
                                            </span>
                                            <ul class="text-[10px] mt-1 space-y-0.5 list-disc list-inside">
                                                @foreach ($r['errors'] as $err)
                                                    <li>{{ $err }}</li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Bottom Action Footer -->
            @php
                $validItems = array_values(array_filter($rows, fn ($r) => $r['is_valid']));
            @endphp

            <div class="p-4 bg-zinc-50 border-t border-zinc-200 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div class="text-xs text-zinc-600">
                    Siap memproses <strong class="text-zinc-900">{{ count($validItems) }}</strong> pegawai baru ke basis data.
                </div>

                <div class="flex flex-col sm:flex-row sm:items-center gap-2 w-full sm:w-auto">
                    <x-button href="{{ route('staff.import.index') }}" variant="secondary" size="md" class="w-full sm:w-auto justify-center">
                        Batal & Kembali
                    </x-button>

                    @if (count($validItems) > 0)
                        <form method="POST" action="{{ route('staff.import.process') }}" onsubmit="return confirm('Proses impor sebanyak {{ count($validItems) }} pegawai?');" class="w-full sm:w-auto">
                            @csrf
                            <input type="hidden" name="items" value="{{ json_encode($validItems) }}">
                            <x-button type="submit" variant="primary" size="md" class="w-full sm:w-auto justify-center">
                                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                                Konfirmasi & Impor ({{ count($validItems) }})
                            </x-button>
                        </form>
                    @endif
                </div>
            </div>
        </x-card>
    </div>
</x-layouts.app>
