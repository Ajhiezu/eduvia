<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <x-button href="{{ route('operations.library.loans.index') }}" variant="ghost" size="sm">
                &larr; Sirkulasi
            </x-button>
            <div>
                <h1 class="text-xl font-bold tracking-tight text-zinc-900">Laporan & Statistik Perpustakaan</h1>
                <p class="text-xs text-zinc-500">Ringkasan koleksi buku, eksemplar fisik, sirkulasi peminjaman, dan riwayat pengembalian.</p>
            </div>
        </div>
    </x-slot>

    <div class="py-6 space-y-6 max-w-5xl">
        <!-- KPI Cards -->
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
            <div class="bg-white p-5 rounded-2xl border border-zinc-200/80 shadow-xs">
                <span class="text-xs font-semibold text-zinc-500 uppercase">Total Judul</span>
                <h3 class="text-2xl font-bold text-zinc-900 mt-1">{{ number_format($totalTitles) }}</h3>
            </div>
            <div class="bg-white p-5 rounded-2xl border border-zinc-200/80 shadow-xs">
                <span class="text-xs font-semibold text-zinc-500 uppercase">Total Eksemplar</span>
                <h3 class="text-2xl font-bold text-zinc-900 mt-1">{{ number_format($totalCopies) }}</h3>
            </div>
            <div class="bg-white p-5 rounded-2xl border border-zinc-200/80 shadow-xs">
                <span class="text-xs font-semibold text-zinc-500 uppercase">Tersedia di Rak</span>
                <h3 class="text-2xl font-bold text-emerald-600 mt-1">{{ number_format($availableCopies) }}</h3>
            </div>
            <div class="bg-white p-5 rounded-2xl border border-zinc-200/80 shadow-xs">
                <span class="text-xs font-semibold text-zinc-500 uppercase">Sedang Dipinjam</span>
                <h3 class="text-2xl font-bold text-blue-600 mt-1">{{ number_format($borrowedCopies) }}</h3>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Category Breakdown -->
            <div class="bg-white rounded-2xl border border-zinc-200/80 shadow-xs p-6">
                <h3 class="text-sm font-bold text-zinc-900 mb-4 pb-2 border-b border-zinc-100">Distribusi Kategori Koleksi</h3>
                <div class="space-y-3">
                    @forelse($categoryDistribution as $cat)
                        <div class="flex items-center justify-between text-xs">
                            <span class="font-medium text-zinc-700">{{ $cat->category }}</span>
                            <span class="font-bold text-zinc-900 px-2 py-0.5 bg-zinc-100 rounded-md">{{ $cat->count }} Judul</span>
                        </div>
                    @empty
                        <p class="text-xs text-zinc-400">Belum ada data koleksi.</p>
                    @endforelse
                </div>
            </div>

            <!-- Recent Returns -->
            <div class="bg-white rounded-2xl border border-zinc-200/80 shadow-xs p-6">
                <h3 class="text-sm font-bold text-zinc-900 mb-4 pb-2 border-b border-zinc-100">Pengembalian Buku Terakhir</h3>
                <div class="space-y-3">
                    @forelse($recentReturns as $ret)
                        <div class="flex items-center justify-between text-xs py-1 border-b border-zinc-50">
                            <div>
                                <h4 class="font-semibold text-zinc-900 line-clamp-1">{{ $ret->copy->book->title }}</h4>
                                <p class="text-[11px] text-zinc-500">{{ $ret->borrower->name }} &bull; Tgl: {{ $ret->return_date?->format('d/m/Y') }}</p>
                            </div>
                            @if($ret->fine_amount > 0)
                                <span class="text-rose-600 font-bold font-mono">Denda: Rp {{ number_format($ret->fine_amount, 0, ',', '.') }}</span>
                            @else
                                <span class="text-emerald-600 font-semibold">Tepat Waktu</span>
                            @endif
                        </div>
                    @empty
                        <p class="text-xs text-zinc-400">Belum ada data pengembalian.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
