<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div class="flex items-center gap-3">
                <x-button href="{{ route('operations.inventory.assets.index') }}" variant="ghost" size="sm">
                    &larr; Aset Sarpras
                </x-button>
                <div>
                    <h1 class="text-2xl font-bold tracking-tight text-zinc-900">Barang Habis Pakai (ATK & Logistik)</h1>
                    <p class="text-sm text-zinc-500 mt-1">Kelola stok persediaan kertas, ATK, bahan praktikum, dan tinta printer sekolah.</p>
                </div>
            </div>
            <x-button href="{{ route('operations.inventory.reports') }}" variant="secondary" size="sm">
                Laporan Sarpras
            </x-button>
        </div>
    </x-slot>

    <div class="py-6 space-y-6">
        @if (session('status'))
            <x-alert type="success" :message="session('status')" dismissible />
        @endif
        @if (session('error'))
            <x-alert type="error" :message="session('error')" dismissible />
        @endif

        @if($lowStockCount > 0)
            <div class="p-4 rounded-2xl bg-amber-50 border border-amber-200 flex items-center justify-between text-amber-800">
                <div class="flex items-center gap-3 text-xs">
                    <svg class="w-5 h-5 text-amber-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                    </svg>
                    <span><strong>Peringatan Logistik:</strong> Terdapat <strong>{{ $lowStockCount }} barang</strong> dengan stok berada di bawah ambang batas minimum. Harap lakukan pengadaan ulang stok ATK.</span>
                </div>
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Add Consumable Form -->
            <div class="bg-white rounded-2xl border border-zinc-200/80 shadow-xs p-6 h-fit">
                <h3 class="text-sm font-bold text-zinc-900 mb-1">Tambah Master Barang Habis Pakai</h3>
                <p class="text-xs text-zinc-500 mb-4">Daftarkan item konsumsi baru.</p>

                <form action="{{ route('operations.inventory.consumables.store') }}" method="POST" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-xs font-semibold text-zinc-700 mb-1">Kategori Logistik <span class="text-rose-500">*</span></label>
                        <select name="inventory_category_id" required class="w-full text-xs rounded-xl border-zinc-300">
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <x-input name="item_code" label="Kode Barang" placeholder="Contoh: ATK-HVS-A4" required />
                    <x-input name="name" label="Nama Barang" placeholder="Contoh: Kertas HVS PaperOne 75gr A4" required />
                    <x-input name="unit" label="Satuan" placeholder="Rim, Box, Pcs, Pack" required value="Pcs" />

                    <div class="grid grid-cols-2 gap-3">
                        <x-input type="number" name="current_stock" label="Stok Awal" min="0" required value="10" />
                        <x-input type="number" name="minimum_stock" label="Ambang Batas Min." min="1" required value="5" />
                    </div>

                    <x-button type="submit" variant="primary" size="md" class="w-full justify-center">
                        + Tambah Barang
                    </x-button>
                </form>
            </div>

            <!-- Stock Table with In/Out Adjust Modal/Forms -->
            <div class="lg:col-span-2 space-y-6">
                <div class="bg-white rounded-2xl border border-zinc-200/80 shadow-xs overflow-hidden">
                    <div class="p-4 border-b border-zinc-100 flex items-center justify-between">
                        <h3 class="text-sm font-bold text-zinc-900">Persediaan Barang Habis Pakai</h3>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs">
                            <thead class="bg-zinc-50 border-b border-zinc-200 text-zinc-500 uppercase tracking-wider font-semibold">
                                <tr>
                                    <th class="px-4 py-3">Kode & Nama</th>
                                    <th class="px-4 py-3">Satuan</th>
                                    <th class="px-4 py-3">Stok Saat Ini</th>
                                    <th class="px-4 py-3">Ambang Min.</th>
                                    <th class="px-4 py-3 text-right">Penyesuaian Stok</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-zinc-100">
                                @forelse($items as $item)
                                    <tr class="hover:bg-zinc-50/50">
                                        <td class="px-4 py-3">
                                            <div class="font-bold text-zinc-900">{{ $item->name }}</div>
                                            <div class="text-[11px] font-mono text-zinc-500">{{ $item->item_code }} &bull; {{ $item->category->name }}</div>
                                        </td>
                                        <td class="px-4 py-3 font-medium text-zinc-700">{{ $item->unit }}</td>
                                        <td class="px-4 py-3">
                                            <span class="font-bold text-sm {{ $item->isLowStock() ? 'text-rose-600' : 'text-zinc-900' }}">
                                                {{ $item->current_stock }}
                                            </span>
                                            @if($item->isLowStock())
                                                <span class="ml-1 text-[10px] font-bold px-1.5 py-0.5 rounded-full bg-rose-50 text-rose-700 border border-rose-200">Menipis</span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3 text-zinc-500">{{ $item->minimum_stock }} {{ $item->unit }}</td>
                                        <td class="px-4 py-3 text-right">
                                            <!-- Inline Stock Out / In Form -->
                                            <form action="{{ route('operations.inventory.consumables.adjust', $item) }}" method="POST" class="inline-flex items-center gap-1.5 justify-end">
                                                @csrf
                                                <select name="type" class="text-[11px] py-1 px-2 rounded-lg border-zinc-200">
                                                    <option value="out">Keluar</option>
                                                    <option value="in">Masuk</option>
                                                </select>
                                                <input type="number" name="quantity" value="1" min="1" class="w-14 text-xs py-1 px-1.5 rounded-lg border-zinc-200">
                                                <input type="hidden" name="purpose" value="Pengambilan operasional sekolah">
                                                <x-button type="submit" variant="secondary" size="xs">Update</x-button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="px-4 py-8 text-center text-zinc-400">Belum ada barang habis pakai terdaftar.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="mt-4">
                    {{ $items->links() }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
