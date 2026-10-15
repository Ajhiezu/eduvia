<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <x-button href="{{ route('operations.library.loans.index') }}" variant="ghost" size="sm">
                &larr; Kembali
            </x-button>
            <div>
                <h1 class="text-xl font-bold tracking-tight text-zinc-900">Catat Peminjaman Buku</h1>
                <p class="text-xs text-zinc-500">Pilih eksemplar buku yang tersedia dan tentukan peminjam serta batas pengembalian.</p>
            </div>
        </div>
    </x-slot>

    <div class="py-6 max-w-2xl">
        <div class="bg-white rounded-2xl border border-zinc-200/80 shadow-xs p-6 sm:p-8">
            <form action="{{ route('operations.library.loans.store') }}" method="POST" class="space-y-5">
                @csrf

                <div>
                    <label class="block text-sm font-semibold text-zinc-700 mb-1.5">Pilih Eksemplar Buku Tersedia <span class="text-rose-500">*</span></label>
                    <select name="library_book_copy_id" required class="w-full text-sm rounded-xl border-zinc-300 focus:border-[#E11D2E] focus:ring-[#E11D2E]">
                        <option value="">-- Pilih Buku & Barcode --</option>
                        @foreach($availableCopies as $copy)
                            <option value="{{ $copy->id }}" {{ old('library_book_copy_id') == $copy->id ? 'selected' : '' }}>
                                [{{ $copy->barcode }}] {{ $copy->book->title }} (Eks #{{ $copy->copy_number }})
                            </option>
                        @endforeach
                    </select>
                    @if($availableCopies->isEmpty())
                        <p class="text-xs text-rose-500 mt-1">Tidak ada eksemplar buku yang sedang berstatus tersedia di perpustakaan.</p>
                    @endif
                </div>

                <div>
                    <label class="block text-sm font-semibold text-zinc-700 mb-1.5">Peminjam (Siswa / Guru / Staf) <span class="text-rose-500">*</span></label>
                    <select name="user_id" required class="w-full text-sm rounded-xl border-zinc-300 focus:border-[#E11D2E] focus:ring-[#E11D2E]">
                        <option value="">-- Pilih Pengguna --</option>
                        @foreach($users as $u)
                            <option value="{{ $u->id }}" {{ old('user_id') == $u->id ? 'selected' : '' }}>
                                {{ $u->name }} ({{ ucfirst($u->role) }} &bull; {{ $u->email }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <x-input label="Tanggal Jatuh Tempo Pengembalian" type="date" name="due_date" :value="old('due_date', \Carbon\Carbon::today()->addDays(7)->toDateString())" required :min="\Carbon\Carbon::today()->toDateString()" />
                    <p class="text-xs text-zinc-500 mt-1">Standar batas waktu peminjaman adalah 7 hari kalender.</p>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-zinc-700 mb-1.5">Catatan Tambahan (Opsional)</label>
                    <textarea name="notes" rows="2" class="w-full text-sm rounded-xl border-zinc-300 focus:border-[#E11D2E] focus:ring-[#E11D2E]" placeholder="Misal: Kondisi buku sebelum dipinjam ada sedikit coretan...">{{ old('notes') }}</textarea>
                </div>

                <div class="flex justify-end gap-3 pt-4 border-t border-zinc-100">
                    <x-button href="{{ route('operations.library.loans.index') }}" variant="secondary">Batal</x-button>
                    <x-button type="submit" variant="primary">Terbitkan Peminjaman</x-button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
