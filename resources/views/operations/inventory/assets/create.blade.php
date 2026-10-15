<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <x-button href="{{ route('operations.inventory.assets.index') }}" variant="ghost" size="sm">
                &larr; Kembali
            </x-button>
            <div>
                <h1 class="text-xl font-bold tracking-tight text-zinc-900">Registrasi Aset Baru</h1>
                <p class="text-xs text-zinc-500">Daftarkan barang sarana dan prasarana sekolah ke dalam buku inventaris.</p>
            </div>
        </div>
    </x-slot>

    <div class="py-6 max-w-4xl">
        <div class="bg-white rounded-2xl border border-zinc-200/80 shadow-xs p-6 sm:p-8">
            <form action="{{ route('operations.inventory.assets.store') }}" method="POST" class="space-y-6">
                @csrf

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-semibold text-zinc-700 mb-1.5">Kategori Aset <span class="text-rose-500">*</span></label>
                        <select name="inventory_category_id" required class="w-full text-sm rounded-xl border-zinc-300 focus:border-[#E11D2E] focus:ring-[#E11D2E]">
                            <option value="">-- Pilih Kategori --</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}" {{ old('inventory_category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }} ({{ $cat->code }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <x-input label="Nama Aset / Barang" name="name" :value="old('name')" required placeholder="Contoh: Laptop Guru Core i5 / Meja Siswa Kayu Jati" />
                    </div>

                    <div>
                        <x-input label="Merk / Brand" name="brand" :value="old('brand')" placeholder="Contoh: ASUS / Olympic / Epson" />
                    </div>

                    <div>
                        <x-input label="Nomor Model / Seri" name="model_number" :value="old('model_number')" placeholder="Contoh: Vivobook 14 / L3110" />
                    </div>

                    <div>
                        <x-input label="Nomor Seri Fisik (Serial No)" name="serial_number" :value="old('serial_number')" placeholder="S/N pabrik jika ada" />
                    </div>

                    <div>
                        <x-input label="Lokasi Ruangan Aset" name="room_location" :value="old('room_location')" required placeholder="Contoh: Lab Komputer 1 / Ruang Guru" />
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-zinc-700 mb-1.5">Kondisi Fisik Aset <span class="text-rose-500">*</span></label>
                        <select name="condition" required class="w-full text-sm rounded-xl border-zinc-300 focus:border-[#E11D2E] focus:ring-[#E11D2E]">
                            <option value="good" {{ old('condition') === 'good' ? 'selected' : '' }}>Baik (Berfungsi Normal)</option>
                            <option value="minor_damage" {{ old('condition') === 'minor_damage' ? 'selected' : '' }}>Rusak Ringan</option>
                            <option value="heavy_damage" {{ old('condition') === 'heavy_damage' ? 'selected' : '' }}>Rusak Berat</option>
                            <option value="disposed" {{ old('condition') === 'disposed' ? 'selected' : '' }}>Afkir / Dihapuskan</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-zinc-700 mb-1.5">Status Penggunaan <span class="text-rose-500">*</span></label>
                        <select name="status" required class="w-full text-sm rounded-xl border-zinc-300 focus:border-[#E11D2E] focus:ring-[#E11D2E]">
                            <option value="in_use" {{ old('status') === 'in_use' ? 'selected' : '' }}>Sedang Digunakan</option>
                            <option value="in_storage" {{ old('status') === 'in_storage' ? 'selected' : '' }}>Tersimpan di Gudang</option>
                            <option value="maintenance" {{ old('status') === 'maintenance' ? 'selected' : '' }}>Dalam Perbaikan / Servis</option>
                            <option value="disposed" {{ old('status') === 'disposed' ? 'selected' : '' }}>Afkir / Dihapuskan</option>
                        </select>
                    </div>

                    <div>
                        <x-input label="Tanggal Perolehan / Pembelian" type="date" name="acquisition_date" :value="old('acquisition_date', date('Y-m-d'))" />
                    </div>

                    <div>
                        <x-input label="Nilai Perolehan (Rupiah)" type="number" name="purchase_cost" :value="old('purchase_cost', 0)" min="0" placeholder="Biaya beli aset per unit" />
                    </div>

                    <div class="md:col-span-2">
                        <x-input label="Penanggung Jawab / Guru Piket Lab" name="responsible_person" :value="old('responsible_person')" placeholder="Nama guru penanggung jawab ruangan / sarpras" />
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-sm font-semibold text-zinc-700 mb-1.5">Catatan Spesifikasi Aset</label>
                        <textarea name="notes" rows="3" class="w-full text-sm rounded-xl border-zinc-300 focus:border-[#E11D2E] focus:ring-[#E11D2E]" placeholder="Spesifikasi RAM, garansi, sumber dana BOS/Yayasan...">{{ old('notes') }}</textarea>
                    </div>
                </div>

                <div class="flex justify-end gap-3 pt-4 border-t border-zinc-100">
                    <x-button href="{{ route('operations.inventory.assets.index') }}" variant="secondary">Batal</x-button>
                    <x-button type="submit" variant="primary">Daftarkan Aset</x-button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
