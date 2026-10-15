<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-zinc-900">Inventaris & Sarana Prasarana</h1>
                <p class="text-sm text-zinc-500 mt-1">Registrasi aset, mutasi lokasi, inspeksi kondisi fisik, dan nilai perolehan sarpras sekolah.</p>
            </div>
            <div class="flex items-center gap-2">
                <x-button href="{{ route('operations.inventory.consumables.index') }}" variant="secondary" size="sm">
                    Barang Habis Pakai
                </x-button>
                <x-button href="{{ route('operations.inventory.assets.create') }}" variant="primary" size="sm">
                    + Daftarkan Aset Baru
                </x-button>
            </div>
        </div>
    </x-slot>

    <div class="py-6 space-y-6">
        @if (session('status'))
            <x-alert type="success" :message="session('status')" dismissible />
        @endif
        @if (session('error'))
            <x-alert type="error" :message="session('error')" dismissible />
        @endif

        <!-- Metric Strips -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div class="bg-white p-4 rounded-2xl border border-zinc-200 shadow-xs">
                <span class="text-xs font-semibold text-zinc-500 uppercase">Total Aset Terdata</span>
                <h3 class="text-2xl font-bold text-zinc-900 mt-1">{{ number_format($totalAssets) }} Unit</h3>
            </div>
            <div class="bg-white p-4 rounded-2xl border border-zinc-200 shadow-xs">
                <span class="text-xs font-semibold text-zinc-500 uppercase">Perlu Perbaikan / Rusak</span>
                <h3 class="text-2xl font-bold {{ $totalDamaged > 0 ? 'text-amber-600' : 'text-zinc-900' }} mt-1">{{ number_format($totalDamaged) }} Unit</h3>
            </div>
            <div class="bg-white p-4 rounded-2xl border border-zinc-200 shadow-xs">
                <span class="text-xs font-semibold text-zinc-500 uppercase">Estimasi Nilai Perolehan</span>
                <h3 class="text-2xl font-bold text-zinc-900 mt-1 font-mono">Rp {{ number_format($totalCost, 0, ',', '.') }}</h3>
            </div>
        </div>

        <!-- Filter Bar -->
        <div class="bg-white p-4 rounded-2xl border border-zinc-200 shadow-xs">
            <form method="GET" action="{{ route('operations.inventory.assets.index') }}" class="grid grid-cols-1 sm:grid-cols-4 gap-3">
                <div class="sm:col-span-2">
                    <x-input type="text" name="search" :value="$search" placeholder="Cari nama aset, kode, lokasi ruangan, merk..." />
                </div>
                <div>
                    <select name="condition" class="w-full text-sm rounded-xl border-zinc-300 focus:border-[#E11D2E] focus:ring-[#E11D2E]">
                        <option value="">Semua Kondisi</option>
                        <option value="good" {{ $condition === 'good' ? 'selected' : '' }}>Baik</option>
                        <option value="minor_damage" {{ $condition === 'minor_damage' ? 'selected' : '' }}>Rusak Ringan</option>
                        <option value="heavy_damage" {{ $condition === 'heavy_damage' ? 'selected' : '' }}>Rusak Berat</option>
                        <option value="disposed" {{ $condition === 'disposed' ? 'selected' : '' }}>Afkir / Dihapuskan</option>
                    </select>
                </div>
                <div>
                    <x-button type="submit" variant="secondary" size="md" class="w-full justify-center">Filter</x-button>
                </div>
            </form>
        </div>

        <!-- Assets Table -->
        <div class="bg-white rounded-2xl border border-zinc-200/80 shadow-xs overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-zinc-50 border-b border-zinc-200 text-zinc-500 uppercase tracking-wider font-semibold">
                        <tr>
                            <th class="px-4 py-3">Kode Aset</th>
                            <th class="px-4 py-3">Nama Aset / Spesifikasi</th>
                            <th class="px-4 py-3">Kategori</th>
                            <th class="px-4 py-3">Lokasi Ruangan</th>
                            <th class="px-4 py-3">Kondisi</th>
                            <th class="px-4 py-3">Nilai Perolehan</th>
                            <th class="px-4 py-3 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-100">
                        @forelse($assets as $asset)
                            <tr class="hover:bg-zinc-50/50">
                                <td class="px-4 py-3 font-mono font-bold text-zinc-900">{{ $asset->asset_code }}</td>
                                <td class="px-4 py-3">
                                    <div class="font-bold text-zinc-900">{{ $asset->name }}</div>
                                    <div class="text-[11px] text-zinc-500">{{ $asset->brand ?? '-' }} {{ $asset->model_number ? '('.$asset->model_number.')' : '' }}</div>
                                </td>
                                <td class="px-4 py-3 text-zinc-600 font-medium">{{ $asset->category->name }}</td>
                                <td class="px-4 py-3 font-medium text-zinc-800">{{ $asset->room_location }}</td>
                                <td class="px-4 py-3">
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold {{ $asset->condition === 'good' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : ($asset->condition === 'minor_damage' ? 'bg-amber-50 text-amber-700 border border-amber-200' : 'bg-rose-50 text-rose-700 border border-rose-200') }}">
                                        {{ $asset->conditionLabel() }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 font-mono text-zinc-700">{{ $asset->formattedCost() }}</td>
                                <td class="px-4 py-3 text-right">
                                    <a href="{{ route('operations.inventory.assets.show', $asset) }}" class="text-xs font-bold text-[#E11D2E] hover:underline">
                                        Rincian &rarr;
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-4 py-8 text-center text-zinc-400">Belum ada aset sarana prasarana yang terdaftar.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mt-4">
            {{ $assets->links() }}
        </div>
    </div>
</x-app-layout>
