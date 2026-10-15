<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <x-button href="{{ route('operations.facilities.rooms.show', $room) }}" variant="ghost" size="sm">
                &larr; Kembali
            </x-button>
            <div>
                <h1 class="text-xl font-bold tracking-tight text-zinc-900">Edit Data Fasilitas</h1>
                <p class="text-xs text-zinc-500">Perbarui spesifikasi ruangan atau laboratorium.</p>
            </div>
        </div>
    </x-slot>

    <div class="py-6 max-w-3xl">
        <div class="bg-white rounded-2xl border border-zinc-200/80 shadow-xs p-6 sm:p-8">
            <form action="{{ route('operations.facilities.rooms.update', $room) }}" method="POST" class="space-y-5">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="md:col-span-2">
                        <x-input label="Nama Fasilitas / Ruangan" name="name" :value="old('name', $room->name)" required />
                    </div>

                    <div>
                        <x-input label="Kode Ruangan" name="code" :value="old('code', $room->code)" required />
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-zinc-700 mb-1.5">Jenis Fasilitas <span class="text-rose-500">*</span></label>
                        <select name="type" required class="w-full text-sm rounded-xl border-zinc-300 focus:border-[#E11D2E] focus:ring-[#E11D2E]">
                            @foreach($roomTypes as $k => $label)
                                <option value="{{ $k }}" {{ old('type', $room->type) === $k ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <x-input label="Kapasitas Maksimal (Peserta)" type="number" name="capacity" :value="old('capacity', $room->capacity)" min="1" max="500" required />
                    </div>

                    <div>
                        <x-input label="Lokasi Gedung / Lantai" name="location" :value="old('location', $room->location)" />
                    </div>

                    <div>
                        <x-input label="Penanggung Jawab Ruangan" name="manager_name" :value="old('manager_name', $room->manager_name)" />
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-zinc-700 mb-1.5">Status Aktif <span class="text-rose-500">*</span></label>
                        <select name="is_active" required class="w-full text-sm rounded-xl border-zinc-300 focus:border-[#E11D2E] focus:ring-[#E11D2E]">
                            <option value="1" {{ old('is_active', $room->is_active) ? 'selected' : '' }}>Aktif</option>
                            <option value="0" {{ !old('is_active', $room->is_active) ? 'selected' : '' }}>Nonaktif</option>
                        </select>
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-sm font-semibold text-zinc-700 mb-1.5">Deskripsi Fasilitas & Peralatan Tersedia</label>
                        <textarea name="facilities_description" rows="3" class="w-full text-sm rounded-xl border-zinc-300 focus:border-[#E11D2E] focus:ring-[#E11D2E]">{{ old('facilities_description', $room->facilities_description) }}</textarea>
                    </div>
                </div>

                <div class="flex justify-end gap-3 pt-4 border-t border-zinc-100">
                    <x-button href="{{ route('operations.facilities.rooms.show', $room) }}" variant="secondary">Batal</x-button>
                    <x-button type="submit" variant="primary">Simpan Perubahan</x-button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
