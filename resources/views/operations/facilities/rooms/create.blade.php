<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <x-button href="{{ route('operations.facilities.rooms.index') }}" variant="ghost" size="sm">
                &larr; Kembali
            </x-button>
            <div>
                <h1 class="text-xl font-bold tracking-tight text-zinc-900">Daftarkan Fasilitas / Ruangan Baru</h1>
                <p class="text-xs text-zinc-500">Tambahkan laboratorium, bengkel kejuruan, ruang rapat, atau fasilitas olahraga.</p>
            </div>
        </div>
    </x-slot>

    <div class="py-6 max-w-3xl">
        <div class="bg-white rounded-2xl border border-zinc-200/80 shadow-xs p-6 sm:p-8">
            <form action="{{ route('operations.facilities.rooms.store') }}" method="POST" class="space-y-5">
                @csrf

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="md:col-span-2">
                        <x-input label="Nama Fasilitas / Ruangan" name="name" :value="old('name')" required placeholder="Contoh: Laboratorium Komputer Multimedia A" />
                    </div>

                    <div>
                        <x-input label="Kode Ruangan" name="code" :value="old('code')" required placeholder="Contoh: LAB-KOMP-A" />
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-zinc-700 mb-1.5">Jenis Fasilitas <span class="text-rose-500">*</span></label>
                        <select name="type" required class="w-full text-sm rounded-xl border-zinc-300 focus:border-[#E11D2E] focus:ring-[#E11D2E]">
                            @foreach($roomTypes as $k => $label)
                                <option value="{{ $k }}" {{ old('type') === $k ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <x-input label="Kapasitas Maksimal (Peserta)" type="number" name="capacity" :value="old('capacity', 36)" min="1" max="500" required />
                    </div>

                    <div>
                        <x-input label="Lokasi Gedung / Lantai" name="location" :value="old('location')" placeholder="Contoh: Gedung B Lantai 2" />
                    </div>

                    <div class="md:col-span-2">
                        <x-input label="Penanggung Jawab Ruangan / Kepala Lab" name="manager_name" :value="old('manager_name')" placeholder="Nama guru / laboran penanggung jawab" />
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-sm font-semibold text-zinc-700 mb-1.5">Deskripsi Fasilitas & Peralatan Tersedia</label>
                        <textarea name="facilities_description" rows="3" class="w-full text-sm rounded-xl border-zinc-300 focus:border-[#E11D2E] focus:ring-[#E11D2E]" placeholder="Contoh: 36 Unit PC Core i5, 2 AC 2PK, Proyektor HDMI, Whiteboard, Sound System...">{{ old('facilities_description') }}</textarea>
                    </div>
                </div>

                <div class="flex justify-end gap-3 pt-4 border-t border-zinc-100">
                    <x-button href="{{ route('operations.facilities.rooms.index') }}" variant="secondary">Batal</x-button>
                    <x-button type="submit" variant="primary">Daftarkan Fasilitas</x-button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
