<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <x-button href="{{ route('operations.inventory.assets.show', $asset) }}" variant="ghost" size="sm">
                &larr; Kembali
            </x-button>
            <div>
                <h1 class="text-xl font-bold tracking-tight text-zinc-900">Edit Data Aset</h1>
                <p class="text-xs text-zinc-500">Perbarui informasi administratif dan nilai perolehan barang.</p>
            </div>
        </div>
    </x-slot>

    <div class="py-6 max-w-4xl">
        <div class="bg-white rounded-2xl border border-zinc-200/80 shadow-xs p-6 sm:p-8">
            <form action="{{ route('operations.inventory.assets.update', $asset) }}" method="POST" class="space-y-6">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-semibold text-zinc-700 mb-1.5">Kategori Aset <span class="text-rose-500">*</span></label>
                        <select name="inventory_category_id" required class="w-full text-sm rounded-xl border-zinc-300 focus:border-[#E11D2E] focus:ring-[#E11D2E]">
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}" {{ old('inventory_category_id', $asset->inventory_category_id) == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <x-input label="Nama Aset / Barang" name="name" :value="old('name', $asset->name)" required />
                    </div>

                    <div>
                        <x-input label="Merk / Brand" name="brand" :value="old('brand', $asset->brand)" />
                    </div>

                    <div>
                        <x-input label="Nomor Model / Seri" name="model_number" :value="old('model_number', $asset->model_number)" />
                    </div>

                    <div>
                        <x-input label="Nomor Seri Fisik (Serial No)" name="serial_number" :value="old('serial_number', $asset->serial_number)" />
                    </div>

                    <div>
                        <x-input label="Lokasi Ruangan Aset" name="room_location" :value="old('room_location', $asset->room_location)" required />
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-zinc-700 mb-1.5">Status Penggunaan <span class="text-rose-500">*</span></label>
                        <select name="status" required class="w-full text-sm rounded-xl border-zinc-300 focus:border-[#E11D2E] focus:ring-[#E11D2E]">
                            <option value="in_use" {{ old('status', $asset->status) === 'in_use' ? 'selected' : '' }}>Sedang Digunakan</option>
                            <option value="in_storage" {{ old('status', $asset->status) === 'in_storage' ? 'selected' : '' }}>Tersimpan di Gudang</option>
                            <option value="maintenance" {{ old('status', $asset->status) === 'maintenance' ? 'selected' : '' }}>Dalam Perbaikan</option>
                            <option value="disposed" {{ old('status', $asset->status) === 'disposed' ? 'selected' : '' }}>Afkir / Dihapuskan</option>
                        </select>
                    </div>

                    <div>
                        <x-input label="Tanggal Perolehan / Pembelian" type="date" name="acquisition_date" :value="old('acquisition_date', $asset->acquisition_date?->format('Y-m-d'))" />
                    </div>

                    <div>
                        <x-input label="Nilai Perolehan (Rupiah)" type="number" name="purchase_cost" :value="old('purchase_cost', $asset->purchase_cost)" min="0" />
                    </div>

                    <div>
                        <x-input label="Penanggung Jawab" name="responsible_person" :value="old('responsible_person', $asset->responsible_person)" />
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-sm font-semibold text-zinc-700 mb-1.5">Catatan Spesifikasi Aset</label>
                        <textarea name="notes" rows="3" class="w-full text-sm rounded-xl border-zinc-300 focus:border-[#E11D2E] focus:ring-[#E11D2E]">{{ old('notes', $asset->notes) }}</textarea>
                    </div>
                </div>

                <div class="flex justify-end gap-3 pt-4 border-t border-zinc-100">
                    <x-button href="{{ route('operations.inventory.assets.show', $asset) }}" variant="secondary">Batal</x-button>
                    <x-button type="submit" variant="primary">Simpan Perubahan</x-button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
