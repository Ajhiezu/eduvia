<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <x-button href="{{ route('operations.facilities.reservations.index') }}" variant="ghost" size="sm">
                &larr; Kembali
            </x-button>
            <div>
                <h1 class="text-xl font-bold tracking-tight text-zinc-900">Form Pengajuan Peminjaman Ruangan & Lab</h1>
                <p class="text-xs text-zinc-500">Ajukan permohonan penggunaan laboratorium atau fasilitas sekolah dengan deteksi bentrok otomatis.</p>
            </div>
        </div>
    </x-slot>

    <div class="py-6 max-w-2xl">
        <div class="bg-white rounded-2xl border border-zinc-200/80 shadow-xs p-6 sm:p-8">
            <form action="{{ route('operations.facilities.reservations.store') }}" method="POST" class="space-y-5">
                @csrf

                <div>
                    <label class="block text-sm font-semibold text-zinc-700 mb-1.5">Pilih Ruangan / Laboratorium <span class="text-rose-500">*</span></label>
                    <select name="school_room_id" required class="w-full text-sm rounded-xl border-zinc-300 focus:border-[#E11D2E] focus:ring-[#E11D2E]">
                        <option value="">-- Pilih Fasilitas --</option>
                        @foreach($rooms as $r)
                            <option value="{{ $r->id }}" {{ (old('school_room_id') ?? request('room_id')) == $r->id ? 'selected' : '' }}>
                                {{ $r->name }} ({{ $r->typeLabel() }} &bull; Kapasitas {{ $r->capacity }} orang)
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <x-input label="Tanggal Penggunaan" type="date" name="reservation_date" :value="old('reservation_date', date('Y-m-d'))" required :min="date('Y-m-d')" />
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <x-input label="Jam Mulai" type="time" name="start_time" :value="old('start_time', '08:00')" required />
                    </div>
                    <div>
                        <x-input label="Jam Selesai" type="time" name="end_time" :value="old('end_time', '10:00')" required />
                    </div>
                </div>

                <div>
                    <x-input label="Keperluan / Kegiatan" name="purpose" :value="old('purpose')" required placeholder="Contoh: Praktikum Kimia Asam Basa Kelas 11 SMA / Workshop Robotik SMK" />
                </div>

                <div>
                    <x-input label="Perkiraan Jumlah Peserta (Siswa/Guru)" type="number" name="expected_participants" :value="old('expected_participants', 30)" min="1" max="500" required />
                </div>

                <div>
                    <label class="block text-sm font-semibold text-zinc-700 mb-1.5">Catatan Tambahan / Kebutuhan Alat Khusus</label>
                    <textarea name="notes" rows="2" class="w-full text-sm rounded-xl border-zinc-300 focus:border-[#E11D2E] focus:ring-[#E11D2E]" placeholder="Misal: Mohon disiapkan kabel HDMI dan mic wireless...">{{ old('notes') }}</textarea>
                </div>

                <div class="flex justify-end gap-3 pt-4 border-t border-zinc-100">
                    <x-button href="{{ route('operations.facilities.reservations.index') }}" variant="secondary">Batal</x-button>
                    <x-button type="submit" variant="primary">Ajukan Reservasi</x-button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
