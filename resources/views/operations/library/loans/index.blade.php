<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-zinc-900">Sirkulasi Peminjaman Buku</h1>
                <p class="text-sm text-zinc-500 mt-1">Pencatatan transaksi peminjaman, jatuh tempo, pengembalian, dan denda keterlambatan.</p>
            </div>
            <div class="flex items-center gap-2">
                <x-button href="{{ route('operations.library.reports') }}" variant="secondary" size="sm">
                    Laporan Perpustakaan
                </x-button>
                <x-button href="{{ route('operations.library.loans.create') }}" variant="primary" size="sm">
                    + Pinjamkan Buku
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

        <!-- Metric Strips -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div class="bg-white p-4 rounded-2xl border border-zinc-200 shadow-xs flex items-center justify-between">
                <div>
                    <span class="text-xs font-semibold text-zinc-500 uppercase tracking-wider">Sedang Dipinjam Aktif</span>
                    <h3 class="text-2xl font-bold text-zinc-900 mt-1">{{ number_format($totalActive) }} Eksemplar</h3>
                </div>
                <span class="p-3 bg-blue-50 text-blue-600 rounded-xl">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                    </svg>
                </span>
            </div>

            <div class="bg-white p-4 rounded-2xl border border-zinc-200 shadow-xs flex items-center justify-between">
                <div>
                    <span class="text-xs font-semibold text-zinc-500 uppercase tracking-wider">Melewati Jatuh Tempo (Terlambat)</span>
                    <h3 class="text-2xl font-bold {{ $totalOverdue > 0 ? 'text-rose-600' : 'text-zinc-900' }} mt-1">{{ number_format($totalOverdue) }} Peminjaman</h3>
                </div>
                <span class="p-3 bg-rose-50 text-rose-600 rounded-xl">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </span>
            </div>
        </div>

        <!-- Filter Bar -->
        <div class="bg-white p-4 rounded-2xl border border-zinc-200 shadow-xs">
            <form method="GET" action="{{ route('operations.library.loans.index') }}" class="grid grid-cols-1 sm:grid-cols-4 gap-3">
                <div class="sm:col-span-2">
                    <x-input type="text" name="search" :value="$search" placeholder="Cari kode transaksi, judul buku, barcode, peminjam..." />
                </div>
                <div>
                    <select name="status" class="w-full text-sm rounded-xl border-zinc-300 focus:border-[#E11D2E] focus:ring-[#E11D2E]">
                        <option value="">Semua Status</option>
                        <option value="borrowed" {{ $status === 'borrowed' ? 'selected' : '' }}>Sedang Dipinjam</option>
                        <option value="overdue" {{ $status === 'overdue' ? 'selected' : '' }}>Terlambat</option>
                        <option value="returned" {{ $status === 'returned' ? 'selected' : '' }}>Sudah Dikembalikan</option>
                    </select>
                </div>
                <div>
                    <x-button type="submit" variant="secondary" size="md" class="w-full justify-center">Filter</x-button>
                </div>
            </form>
        </div>

        <!-- Loans Table -->
        <div class="bg-white rounded-2xl border border-zinc-200/80 shadow-xs overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-zinc-50 border-b border-zinc-200 text-zinc-500 uppercase tracking-wider font-semibold">
                        <tr>
                            <th class="px-4 py-3">Kode Pinjam</th>
                            <th class="px-4 py-3">Judul Buku / Barcode</th>
                            <th class="px-4 py-3">Peminjam</th>
                            <th class="px-4 py-3">Tgl Pinjam & Jatuh Tempo</th>
                            <th class="px-4 py-3">Status</th>
                            <th class="px-4 py-3">Denda</th>
                            <th class="px-4 py-3 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-100">
                        @forelse($loans as $loan)
                            <tr class="hover:bg-zinc-50/50">
                                <td class="px-4 py-3 font-mono font-bold text-zinc-900">{{ $loan->loan_code }}</td>
                                <td class="px-4 py-3">
                                    <div class="font-bold text-zinc-900 line-clamp-1">{{ $loan->copy->book->title }}</div>
                                    <div class="text-[11px] font-mono text-zinc-500">Barcode: {{ $loan->copy->barcode }} (#{{ $loan->copy->copy_number }})</div>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="font-semibold text-zinc-900">{{ $loan->borrower->name }}</div>
                                    <div class="text-[11px] text-zinc-400 capitalize">{{ $loan->borrower->role }}</div>
                                </td>
                                <td class="px-4 py-3">
                                    <div>Pinjam: {{ $loan->borrow_date->format('d/m/Y') }}</div>
                                    <div class="{{ $loan->isOverdue() && $loan->status === 'borrowed' ? 'text-rose-600 font-bold' : 'text-zinc-500' }}">
                                        Tempo: {{ $loan->due_date->format('d/m/Y') }}
                                    </div>
                                    @if($loan->return_date)
                                        <div class="text-emerald-600 text-[11px]">Kembali: {{ $loan->return_date->format('d/m/Y') }}</div>
                                    @endif
                                </td>
                                <td class="px-4 py-3">
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-bold {{ $loan->statusVariant() === 'danger' ? 'bg-rose-50 text-rose-700 border border-rose-200' : ($loan->statusVariant() === 'success' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-blue-50 text-blue-700 border border-blue-200') }}">
                                        {{ $loan->statusLabel() }}
                                    </span>
                                </td>
                                <td class="px-4 py-3">
                                    @if($loan->fine_amount > 0)
                                        <span class="font-bold text-rose-600 font-mono">Rp {{ number_format($loan->fine_amount, 0, ',', '.') }}</span>
                                    @else
                                        <span class="text-zinc-400">-</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-right">
                                    @if($loan->status === 'borrowed')
                                        <form action="{{ route('operations.library.loans.return', $loan) }}" method="POST" onsubmit="return confirm('Konfirmasi pengembalian buku ini?')">
                                            @csrf
                                            <x-button type="submit" variant="primary" size="xs">
                                                Kembalikan
                                            </x-button>
                                        </form>
                                    @else
                                        <span class="text-xs text-zinc-400 font-medium">Selesai</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-4 py-8 text-center text-zinc-400">Belum ada riwayat transaksi peminjaman buku.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mt-4">
            {{ $loans->links() }}
        </div>
    </div>
</x-app-layout>
