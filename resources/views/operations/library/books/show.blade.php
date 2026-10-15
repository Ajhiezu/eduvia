<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div class="flex items-center gap-3">
                <x-button href="{{ route('operations.library.books.index') }}" variant="ghost" size="sm">
                    &larr; Katalog
                </x-button>
                <div>
                    <h1 class="text-xl font-bold tracking-tight text-zinc-900">{{ $book->title }}</h1>
                    <p class="text-xs text-zinc-500">Penulis: {{ $book->author }} &bull; Kategori: {{ $book->category }}</p>
                </div>
            </div>
            <div class="flex items-center gap-2">
                @if($book->digital_file_path)
                    <x-button href="{{ route('operations.library.books.digital', $book) }}" variant="secondary" size="sm">
                        <svg class="w-4 h-4 mr-1 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                        </svg>
                        Unduh E-Book
                    </x-button>
                @endif
                <x-button href="{{ route('operations.library.books.edit', $book) }}" variant="secondary" size="sm">
                    Edit Buku
                </x-button>
                <form action="{{ route('operations.library.books.destroy', $book) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus buku ini beserta seluruh eksemplarnya?')">
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
                <div class="h-60 rounded-xl bg-zinc-100 flex items-center justify-center overflow-hidden">
                    @if($book->cover_image_path)
                        <img src="{{ asset('storage/' . $book->cover_image_path) }}" alt="{{ $book->title }}" class="w-full h-full object-cover">
                    @else
                        <div class="text-zinc-400 text-center">
                            <svg class="w-16 h-16 mx-auto mb-1 text-zinc-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                            </svg>
                            <span class="text-xs">Tidak ada sampul</span>
                        </div>
                    @endif
                </div>

                <div class="space-y-2 text-xs border-t border-zinc-100 pt-4">
                    <div class="flex justify-between py-1 border-b border-zinc-50">
                        <span class="text-zinc-500">Penerbit</span>
                        <span class="font-semibold text-zinc-900">{{ $book->publisher ?? '-' }}</span>
                    </div>
                    <div class="flex justify-between py-1 border-b border-zinc-50">
                        <span class="text-zinc-500">Tahun Terbit</span>
                        <span class="font-semibold text-zinc-900">{{ $book->publication_year ?? '-' }}</span>
                    </div>
                    <div class="flex justify-between py-1 border-b border-zinc-50">
                        <span class="text-zinc-500">ISBN</span>
                        <span class="font-semibold text-zinc-900 font-mono">{{ $book->isbn ?? '-' }}</span>
                    </div>
                    <div class="flex justify-between py-1 border-b border-zinc-50">
                        <span class="text-zinc-500">Nomor Panggil / DDC</span>
                        <span class="font-semibold text-zinc-900 font-mono">{{ $book->call_number ?? '-' }}</span>
                    </div>
                    <div class="flex justify-between py-1 border-b border-zinc-50">
                        <span class="text-zinc-500">Lokasi Rak</span>
                        <span class="font-semibold text-zinc-900">{{ $book->location_shelf ?? '-' }}</span>
                    </div>
                    <div class="flex justify-between py-1 border-b border-zinc-50">
                        <span class="text-zinc-500">Bahasa</span>
                        <span class="font-semibold text-zinc-900">{{ $book->language }}</span>
                    </div>
                    <div class="flex justify-between py-1">
                        <span class="text-zinc-500">Ketersediaan</span>
                        <span class="font-bold {{ $book->available_copies > 0 ? 'text-emerald-600' : 'text-rose-600' }}">
                            {{ $book->available_copies }} dari {{ $book->total_copies }} Eksemplar
                        </span>
                    </div>
                </div>

                @if($book->description)
                    <div class="border-t border-zinc-100 pt-4">
                        <h4 class="text-xs font-bold text-zinc-800 uppercase tracking-wider mb-1">Sinopsis / Catatan</h4>
                        <p class="text-xs text-zinc-600 leading-relaxed">{{ $book->description }}</p>
                    </div>
                @endif
            </div>

            <!-- Right: Copies List & Add Copies Form -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Add Copies Card -->
                <div class="bg-white rounded-2xl border border-zinc-200/80 shadow-xs p-5">
                    <form action="{{ route('operations.library.books.add-copies', $book) }}" method="POST" class="flex flex-col sm:flex-row items-center gap-3">
                        @csrf
                        <div class="flex-1 w-full">
                            <h4 class="text-sm font-bold text-zinc-900">Tambah Eksemplar Fisik</h4>
                            <p class="text-xs text-zinc-500">Sistem akan secara otomatis membuat nomor barcode seri baru.</p>
                        </div>
                        <div class="flex items-center gap-2 w-full sm:w-auto">
                            <input type="number" name="count" value="1" min="1" max="20" class="w-20 text-sm rounded-xl border-zinc-300">
                            <x-button type="submit" variant="secondary" size="md">Tambah Eksemplar</x-button>
                        </div>
                    </form>
                </div>

                <!-- Copies Table -->
                <div class="bg-white rounded-2xl border border-zinc-200/80 shadow-xs overflow-hidden">
                    <div class="p-4 border-b border-zinc-100 flex items-center justify-between">
                        <h3 class="text-sm font-bold text-zinc-900">Daftar Eksemplar Fisik ({{ $book->copies->count() }})</h3>
                        <x-button href="{{ route('operations.library.loans.create') }}" variant="primary" size="xs">
                            + Pinjamkan Buku
                        </x-button>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs">
                            <thead class="bg-zinc-50 border-b border-zinc-200 text-zinc-500 uppercase tracking-wider font-semibold">
                                <tr>
                                    <th class="px-4 py-3">No. Eks</th>
                                    <th class="px-4 py-3">Kode Barcode</th>
                                    <th class="px-4 py-3">Status</th>
                                    <th class="px-4 py-3">Peminjam Saat Ini</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-zinc-100">
                                @forelse($book->copies as $copy)
                                    <tr class="hover:bg-zinc-50/50">
                                        <td class="px-4 py-3 font-bold text-zinc-900">#{{ $copy->copy_number }}</td>
                                        <td class="px-4 py-3 font-mono font-medium text-zinc-800">{{ $copy->barcode }}</td>
                                        <td class="px-4 py-3">
                                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold {{ $copy->status === 'available' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-amber-50 text-amber-700 border border-amber-200' }}">
                                                {{ $copy->statusLabel() }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-3 text-zinc-600">
                                            @if($copy->activeLoan)
                                                <span class="font-semibold text-zinc-900">{{ $copy->activeLoan->borrower->name }}</span>
                                                <span class="text-zinc-400 text-[11px] block">Jatuh Tempo: {{ $copy->activeLoan->due_date->format('d/m/Y') }}</span>
                                            @else
                                                <span class="text-zinc-400 italic">- Tersedia di rak -</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="px-4 py-6 text-center text-zinc-400">Belum ada eksemplar terdaftar.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
