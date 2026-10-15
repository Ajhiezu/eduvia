<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <x-button href="{{ route('operations.inventory.assets.index') }}" variant="ghost" size="sm">
                &larr; Inventaris
            </x-button>
            <div>
                <h1 class="text-xl font-bold tracking-tight text-zinc-900">Laporan & Rekapitulasi Sarana Prasarana</h1>
                <p class="text-xs text-zinc-500">Statistik kondisi fisik barang, sebaran lokasi ruangan, dan peringatan stok bahan habis pakai.</p>
            </div>
        </div>
    </x-slot>

    <div class="py-6 space-y-6 max-w-5xl">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Condition Breakdown -->
            <div class="bg-white rounded-2xl border border-zinc-200/80 shadow-xs p-6">
                <h3 class="text-sm font-bold text-zinc-900 mb-4 pb-2 border-b border-zinc-100">Kondisi Fisik Seluruh Aset ({{ $totalAssets }} Unit)</h3>
                <div class="space-y-3">
                    @forelse($conditionStats as $cond)
                        <div class="flex items-center justify-between text-xs">
                            <span class="font-medium text-zinc-700 capitalize">{{ $cond->condition === 'good' ? 'Baik' : ($cond->condition === 'minor_damage' ? 'Rusak Ringan' : ($cond->condition === 'heavy_damage' ? 'Rusak Berat' : 'Dihapuskan')) }}</span>
                            <span class="font-bold text-zinc-900 px-2 py-0.5 bg-zinc-100 rounded-md">{{ $cond->count }} Unit</span>
                        </div>
                    @empty
                        <p class="text-xs text-zinc-400">Belum ada data aset.</p>
                    @endforelse
                </div>
            </div>

            <!-- Location Breakdown -->
            <div class="bg-white rounded-2xl border border-zinc-200/80 shadow-xs p-6">
                <h3 class="text-sm font-bold text-zinc-900 mb-4 pb-2 border-b border-zinc-100">Sebaran Aset Berdasarkan Ruangan</h3>
                <div class="space-y-3">
                    @forelse($locationStats as $loc)
                        <div class="flex items-center justify-between text-xs">
                            <span class="font-medium text-zinc-700">{{ $loc->room_location }}</span>
                            <span class="font-bold text-zinc-900 px-2 py-0.5 bg-zinc-100 rounded-md">{{ $loc->count }} Unit</span>
                        </div>
                    @empty
                        <p class="text-xs text-zinc-400">Belum ada data sebaran lokasi.</p>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Low Stock Items Alert Table -->
        <div class="bg-white rounded-2xl border border-zinc-200/80 shadow-xs overflow-hidden">
            <div class="p-4 border-b border-zinc-100">
                <h3 class="text-sm font-bold text-zinc-900">Daftar Barang Habis Pakai yang Perlu Pengadaan Segera</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-zinc-50 border-b border-zinc-200 text-zinc-500 uppercase font-semibold">
                        <tr>
                            <th class="px-4 py-3">Kode & Nama Barang</th>
                            <th class="px-4 py-3">Satuan</th>
                            <th class="px-4 py-3">Stok Saat Ini</th>
                            <th class="px-4 py-3">Ambang Batas Minimum</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-100">
                        @forelse($lowStockItems as $low)
                            <tr>
                                <td class="px-4 py-3 font-semibold text-zinc-900">{{ $low->name }} ({{ $low->item_code }})</td>
                                <td class="px-4 py-3 text-zinc-600">{{ $low->unit }}</td>
                                <td class="px-4 py-3 font-bold text-rose-600">{{ $low->current_stock }}</td>
                                <td class="px-4 py-3 text-zinc-500">{{ $low->minimum_stock }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-4 py-6 text-center text-zinc-400">Semua stok barang habis pakai berada dalam batas aman.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
