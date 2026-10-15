<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div class="flex items-center gap-3">
                <x-button href="{{ route('operations.inventory.assets.index') }}" variant="ghost" size="sm">
                    &larr; Inventaris
                </x-button>
                <div>
                    <h1 class="text-xl font-bold tracking-tight text-zinc-900">{{ $asset->name }}</h1>
                    <p class="text-xs text-zinc-500 font-mono">Kode: {{ $asset->asset_code }} &bull; Kategori: {{ $asset->category->name }}</p>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <x-button href="{{ route('operations.inventory.assets.edit', $asset) }}" variant="secondary" size="sm">
                    Edit Aset
                </x-button>
                <form action="{{ route('operations.inventory.assets.destroy', $asset) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus catatan aset ini?')">
                    @csrf
                    @method('DELETE')
                    <x-button type="submit" variant="danger" size="sm">Hapus</x-button>
                </form>
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

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Left Info Panel -->
            <div class="bg-white rounded-2xl border border-zinc-200/80 shadow-xs p-6 space-y-4">
                <div class="flex items-center justify-between pb-3 border-b border-zinc-100">
                    <span class="text-xs font-semibold text-zinc-500">Status Kondisi</span>
                    <span class="px-2.5 py-1 rounded-full text-xs font-bold {{ $asset->condition === 'good' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-amber-50 text-amber-700 border border-amber-200' }}">
                        {{ $asset->conditionLabel() }}
                    </span>
                </div>

                <div class="space-y-2.5 text-xs">
                    <div class="flex justify-between py-1 border-b border-zinc-50">
                        <span class="text-zinc-500">Lokasi Ruangan</span>
                        <span class="font-bold text-zinc-900">{{ $asset->room_location }}</span>
                    </div>
                    <div class="flex justify-between py-1 border-b border-zinc-50">
                        <span class="text-zinc-500">Merk / Model</span>
                        <span class="font-medium text-zinc-800">{{ $asset->brand ?? '-' }} {{ $asset->model_number ? '('.$asset->model_number.')' : '' }}</span>
                    </div>
                    <div class="flex justify-between py-1 border-b border-zinc-50">
                        <span class="text-zinc-500">Nomor Seri</span>
                        <span class="font-mono text-zinc-800">{{ $asset->serial_number ?? '-' }}</span>
                    </div>
                    <div class="flex justify-between py-1 border-b border-zinc-50">
                        <span class="text-zinc-500">Tgl Perolehan</span>
                        <span class="font-medium text-zinc-800">{{ $asset->acquisition_date?->format('d/m/Y') ?? '-' }}</span>
                    </div>
                    <div class="flex justify-between py-1 border-b border-zinc-50">
                        <span class="text-zinc-500">Nilai Perolehan</span>
                        <span class="font-mono font-bold text-zinc-900">{{ $asset->formattedCost() }}</span>
                    </div>
                    <div class="flex justify-between py-1 border-b border-zinc-50">
                        <span class="text-zinc-500">Penanggung Jawab</span>
                        <span class="font-medium text-zinc-800">{{ $asset->responsible_person ?? '-' }}</span>
                    </div>
                    <div class="flex justify-between py-1">
                        <span class="text-zinc-500">Status Operasional</span>
                        <span class="font-semibold text-zinc-800">{{ $asset->statusLabel() }}</span>
                    </div>
                </div>

                @if($asset->notes)
                    <div class="border-t border-zinc-100 pt-3">
                        <span class="text-[11px] font-bold text-zinc-500 uppercase tracking-wider block mb-1">Catatan Spesifikasi</span>
                        <p class="text-xs text-zinc-600 leading-relaxed">{{ $asset->notes }}</p>
                    </div>
                @endif
            </div>

            <!-- Right: Lifecycle Operations & History Logs -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Action Cards Grid -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <!-- Mutasi Ruangan Form -->
                    <div class="bg-white rounded-2xl border border-zinc-200/80 shadow-xs p-5">
                        <h4 class="text-sm font-bold text-zinc-900 mb-1">Mutasi Ruangan Aset</h4>
                        <p class="text-xs text-zinc-500 mb-3">Pindahkan lokasi pencatatan aset ke ruangan lain.</p>
                        <form action="{{ route('operations.inventory.assets.transfer', $asset) }}" method="POST" class="space-y-3">
                            @csrf
                            <x-input name="new_location" label="Ruangan Tujuan Baru" required placeholder="Contoh: Lab Komputer 2" />
                            <x-input name="notes" label="Alasan Mutasi (Opsional)" placeholder="Misal: Penambahan fasilitas kelas baru" />
                            <x-button type="submit" variant="secondary" size="sm" class="w-full justify-center">Simpan Mutasi</x-button>
                        </form>
                    </div>

                    <!-- Update Kondisi Form -->
                    <div class="bg-white rounded-2xl border border-zinc-200/80 shadow-xs p-5">
                        <h4 class="text-sm font-bold text-zinc-900 mb-1">Inspeksi & Update Kondisi</h4>
                        <p class="text-xs text-zinc-500 mb-3">Ubah status kondisi fisik hasil pemeriksaan berkala.</p>
                        <form action="{{ route('operations.inventory.assets.condition', $asset) }}" method="POST" class="space-y-3">
                            @csrf
                            <div>
                                <label class="block text-xs font-semibold text-zinc-700 mb-1">Kondisi Fisik Baru</label>
                                <select name="new_condition" required class="w-full text-xs rounded-xl border-zinc-300">
                                    <option value="good" {{ $asset->condition === 'good' ? 'selected' : '' }}>Baik</option>
                                    <option value="minor_damage" {{ $asset->condition === 'minor_damage' ? 'selected' : '' }}>Rusak Ringan</option>
                                    <option value="heavy_damage" {{ $asset->condition === 'heavy_damage' ? 'selected' : '' }}>Rusak Berat</option>
                                    <option value="disposed" {{ $asset->condition === 'disposed' ? 'selected' : '' }}>Afkir / Dihapuskan</option>
                                </select>
                            </div>
                            <x-input name="notes" label="Catatan Kerusakan / Servis" placeholder="Misal: Keyboard rusak tombol spasi" />
                            <x-button type="submit" variant="primary" size="sm" class="w-full justify-center">Perbarui Kondisi</x-button>
                        </form>
                    </div>
                </div>

                <!-- History Logs Timeline -->
                <div class="bg-white rounded-2xl border border-zinc-200/80 shadow-xs overflow-hidden">
                    <div class="p-4 border-b border-zinc-100">
                        <h3 class="text-sm font-bold text-zinc-900">Riwayat Mutasi & Perubahan Aset (Audit Trail)</h3>
                    </div>
                    <div class="divide-y divide-zinc-100">
                        @forelse($asset->logs as $log)
                            <div class="p-4 flex items-start gap-3 text-xs">
                                <span class="w-2 h-2 rounded-full bg-blue-500 mt-1.5 shrink-0"></span>
                                <div class="flex-1">
                                    <div class="flex items-center justify-between">
                                        <span class="font-bold text-zinc-900 capitalize">{{ str_replace('_', ' ', $log->action) }}</span>
                                        <span class="text-zinc-400 text-[11px]">{{ $log->created_at->format('d/m/Y H:i') }}</span>
                                    </div>
                                    <p class="text-zinc-600 mt-0.5">{{ $log->notes }}</p>
                                    <span class="text-[11px] text-zinc-400 block mt-1">Dicatat oleh: {{ $log->performer->name ?? 'Sistem' }}</span>
                                </div>
                            </div>
                        @empty
                            <div class="p-8 text-center text-zinc-400 text-xs">Belum ada riwayat tercatat.</div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
