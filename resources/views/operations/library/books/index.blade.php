<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-zinc-900">Katalog Buku Perpustakaan</h1>
                <p class="text-sm text-zinc-500 mt-1">Daftar koleksi buku fisik dan sumber daya digital perpustakaan sekolah.</p>
            </div>
            <div class="flex items-center gap-2">
                <x-button href="{{ route('operations.library.loans.create') }}" variant="secondary" size="sm">
                    Pinjamkan Buku
                </x-button>
                <x-button href="{{ route('operations.library.books.create') }}" variant="primary" size="sm">
                    + Tambah Buku Baru
                </x-button>
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

        <!-- Filter bar -->
        <div class="bg-white p-4 rounded-2xl border border-zinc-200 shadow-xs">
            <form method="GET" action="{{ route('operations.library.books.index') }}" class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                <div class="sm:col-span-2">
                    <x-input type="text" name="search" :value="$search" placeholder="Cari judul buku, penulis, ISBN, atau nomor panggil..." />
                </div>
                <div class="flex gap-2">
                    <select name="category" class="w-full text-sm rounded-xl border-zinc-300 focus:border-[#E11D2E] focus:ring-[#E11D2E]">
                        <option value="">Semua Kategori</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat }}" {{ $category === $cat ? 'selected' : '' }}>{{ $cat }}</option>
                        @endforeach
                    </select>
                    <x-button type="submit" variant="secondary" size="md">Filter</x-button>
                </div>
            </form>
        </div>

        <!-- Books Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
            @forelse($books as $book)
                <div class="bg-white rounded-2xl border border-zinc-200/80 shadow-xs overflow-hidden flex flex-col hover:border-blue-300 transition-all">
                    <div class="h-44 bg-zinc-100 relative flex items-center justify-center overflow-hidden">
                        @if($book->cover_image_path)
                            <img src="{{ asset('storage/' . $book->cover_image_path) }}" alt="{{ $book->title }}" class="w-full h-full object-cover">
                        @else
                            <div class="text-zinc-400 flex flex-col items-center">
                                <svg class="w-12 h-12 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                                </svg>
                                <span class="text-xs font-semibold uppercase tracking-wider">Tanpa Sampul</span>
                            </div>
                        @endif
                        <span class="absolute top-2 left-2 px-2 py-0.5 rounded-lg text-[10px] font-bold bg-white/90 backdrop-blur-xs text-zinc-800 shadow-xs">
                            {{ $book->category }}
                        </span>
                        @if($book->digital_file_path)
                            <span class="absolute top-2 right-2 px-2 py-0.5 rounded-lg text-[10px] font-bold bg-blue-600 text-white shadow-xs">
                                E-Book
                            </span>
                        @endif
                    </div>
                    <div class="p-4 flex-1 flex flex-col justify-between">
                        <div>
                            <h3 class="text-sm font-bold text-zinc-900 line-clamp-2 leading-snug">{{ $book->title }}</h3>
                            <p class="text-xs text-zinc-500 mt-1 line-clamp-1">Penulis: {{ $book->author }}</p>
                            @if($book->call_number)
                                <p class="text-[11px] text-zinc-400 mt-0.5 font-mono">Rak/DDC: {{ $book->call_number }}</p>
                            @endif
                        </div>

                        <div class="mt-4 pt-3 border-t border-zinc-100 flex items-center justify-between">
                            <span class="text-xs {{ $book->available_copies > 0 ? 'text-emerald-700 font-semibold' : 'text-rose-600 font-bold' }}">
                                {{ $book->available_copies }} / {{ $book->total_copies }} Eksemplar
                            </span>
                            <a href="{{ route('operations.library.books.show', $book) }}" class="text-xs font-bold text-blue-600 hover:text-blue-800">
                                Rincian &rarr;
                            </a>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-span-full bg-white rounded-2xl p-12 text-center text-zinc-400 border border-zinc-200">
                    <p class="text-sm font-medium">Belum ada buku dalam katalog perpustakaan sesuai kriteria pencarian.</p>
                </div>
            @endforelse
        </div>

        <div class="mt-4">
            {{ $books->links() }}
        </div>
    </div>
</x-app-layout>
