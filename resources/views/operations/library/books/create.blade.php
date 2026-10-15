<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <x-button href="{{ route('operations.library.books.index') }}" variant="ghost" size="sm">
                &larr; Kembali
            </x-button>
            <div>
                <h1 class="text-xl font-bold tracking-tight text-zinc-900">Tambah Koleksi Buku Baru</h1>
                <p class="text-xs text-zinc-500">Daftarkan metadata buku dan tentukan jumlah eksemplar fisik awal.</p>
            </div>
        </div>
    </x-slot>

    <div class="py-6 max-w-4xl">
        <div class="bg-white rounded-2xl border border-zinc-200/80 shadow-xs p-6 sm:p-8">
            <form action="{{ route('operations.library.books.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                @csrf

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="md:col-span-2">
                        <x-input label="Judul Buku" name="title" :value="old('title')" required placeholder="Contoh: Matematika Dasar Kelas 7 Kurikulum Merdeka" />
                    </div>

                    <div>
                        <x-input label="Nama Penulis / Pengarang" name="author" :value="old('author')" required placeholder="Nama penulis atau tim penyusun" />
                    </div>

                    <div>
                        <x-input label="Penerbit" name="publisher" :value="old('publisher')" placeholder="Contoh: Kemendikbudristek / Erlangga" />
                    </div>

                    <div>
                        <x-input label="Tahun Terbit" type="number" name="publication_year" :value="old('publication_year', date('Y'))" min="1900" :max="date('Y')+1" />
                    </div>

                    <div>
                        <x-input label="Nomor ISBN" name="isbn" :value="old('isbn')" placeholder="978-602-xxx-xxx-x" />
                    </div>

                    <div>
                        <x-input label="Nomor Panggil / DDC" name="call_number" :value="old('call_number')" placeholder="Contoh: 510 MAT k.1" />
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-zinc-700 mb-1.5">Kategori Buku <span class="text-rose-500">*</span></label>
                        <select name="category" required class="w-full text-sm rounded-xl border-zinc-300 focus:border-[#E11D2E] focus:ring-[#E11D2E]">
                            @foreach($categories as $cat)
                                <option value="{{ $cat }}" {{ old('category') === $cat ? 'selected' : '' }}>{{ $cat }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <x-input label="Bahasa" name="language" :value="old('language', 'Indonesia')" required />
                    </div>

                    <div>
                        <x-input label="Lokasi Rak Fisik" name="location_shelf" :value="old('location_shelf')" placeholder="Contoh: Rak B-02 / Lantai 1" />
                    </div>

                    <div>
                        <x-input label="Jumlah Eksemplar Fisik Awal" type="number" name="initial_copies" :value="old('initial_copies', 1)" min="1" max="50" required />
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-zinc-700 mb-1.5">Sampul Buku (Gambar)</label>
                        <input type="file" name="cover_image" accept="image/*" class="w-full text-xs text-zinc-500 file:mr-3 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-zinc-100 hover:file:bg-zinc-200">
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-sm font-semibold text-zinc-700 mb-1.5">Berkas Digital E-Book (Opsional, PDF/EPUB maks 20MB)</label>
                        <input type="file" name="digital_file" accept=".pdf,.epub" class="w-full text-xs text-zinc-500 file:mr-3 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-sm font-semibold text-zinc-700 mb-1.5">Sinopsis / Deskripsi Buku</label>
                        <textarea name="description" rows="3" class="w-full text-sm rounded-xl border-zinc-300 focus:border-[#E11D2E] focus:ring-[#E11D2E]" placeholder="Ringkasan isi buku atau catatan kurikulum...">{{ old('description') }}</textarea>
                    </div>
                </div>

                <div class="flex justify-end gap-3 pt-4 border-t border-zinc-100">
                    <x-button href="{{ route('operations.library.books.index') }}" variant="secondary">Batal</x-button>
                    <x-button type="submit" variant="primary">Simpan ke Katalog</x-button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
