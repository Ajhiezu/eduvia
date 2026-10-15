<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <x-button href="{{ route('operations.library.books.show', $book) }}" variant="ghost" size="sm">
                &larr; Kembali
            </x-button>
            <div>
                <h1 class="text-xl font-bold tracking-tight text-zinc-900">Edit Data Buku</h1>
                <p class="text-xs text-zinc-500">Perbarui metadata katalog buku.</p>
            </div>
        </div>
    </x-slot>

    <div class="py-6 max-w-4xl">
        <div class="bg-white rounded-2xl border border-zinc-200/80 shadow-xs p-6 sm:p-8">
            <form action="{{ route('operations.library.books.update', $book) }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="md:col-span-2">
                        <x-input label="Judul Buku" name="title" :value="old('title', $book->title)" required />
                    </div>

                    <div>
                        <x-input label="Nama Penulis / Pengarang" name="author" :value="old('author', $book->author)" required />
                    </div>

                    <div>
                        <x-input label="Penerbit" name="publisher" :value="old('publisher', $book->publisher)" />
                    </div>

                    <div>
                        <x-input label="Tahun Terbit" type="number" name="publication_year" :value="old('publication_year', $book->publication_year)" min="1900" :max="date('Y')+1" />
                    </div>

                    <div>
                        <x-input label="Nomor ISBN" name="isbn" :value="old('isbn', $book->isbn)" />
                    </div>

                    <div>
                        <x-input label="Nomor Panggil / DDC" name="call_number" :value="old('call_number', $book->call_number)" />
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-zinc-700 mb-1.5">Kategori Buku <span class="text-rose-500">*</span></label>
                        <select name="category" required class="w-full text-sm rounded-xl border-zinc-300 focus:border-[#E11D2E] focus:ring-[#E11D2E]">
                            @foreach($categories as $cat)
                                <option value="{{ $cat }}" {{ old('category', $book->category) === $cat ? 'selected' : '' }}>{{ $cat }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <x-input label="Bahasa" name="language" :value="old('language', $book->language)" required />
                    </div>

                    <div>
                        <x-input label="Lokasi Rak Fisik" name="location_shelf" :value="old('location_shelf', $book->location_shelf)" />
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-zinc-700 mb-1.5">Ganti Sampul Buku (Gambar)</label>
                        <input type="file" name="cover_image" accept="image/*" class="w-full text-xs text-zinc-500 file:mr-3 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-zinc-100 hover:file:bg-zinc-200">
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-zinc-700 mb-1.5">Ganti Berkas Digital E-Book (PDF/EPUB)</label>
                        <input type="file" name="digital_file" accept=".pdf,.epub" class="w-full text-xs text-zinc-500 file:mr-3 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-sm font-semibold text-zinc-700 mb-1.5">Sinopsis / Deskripsi Buku</label>
                        <textarea name="description" rows="3" class="w-full text-sm rounded-xl border-zinc-300 focus:border-[#E11D2E] focus:ring-[#E11D2E]">{{ old('description', $book->description) }}</textarea>
                    </div>
                </div>

                <div class="flex justify-end gap-3 pt-4 border-t border-zinc-100">
                    <x-button href="{{ route('operations.library.books.show', $book) }}" variant="secondary">Batal</x-button>
                    <x-button type="submit" variant="primary">Simpan Perubahan</x-button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
