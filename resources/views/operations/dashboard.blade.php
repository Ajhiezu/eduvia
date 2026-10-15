<x-app-layout>
    <div class="space-y-6">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-4 border-b border-zinc-200">
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-zinc-900">Pusat Operasional Sekolah</h1>
                <p class="text-sm text-zinc-500 mt-1">Ikhtisar tata kelola operasional: PPDB, perpustakaan, aset sarpras, fasilitas/lab, dan persuratan resmi.</p>
            </div>
            <div class="flex items-center gap-2">
                <x-button href="{{ route('operations.correspondence.certificate.create') }}" variant="secondary" size="sm">
                    <svg class="w-4 h-4 mr-1.5 text-zinc-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    Buat Surat Siswa
                </x-button>
                <x-button href="{{ route('operations.facilities.reservations.create') }}" variant="primary" size="sm">
                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                    Pinjam Ruangan / Lab
                </x-button>
            </div>
        </div>
        @if (session('status'))
            <x-alert type="success" :message="session('status')" dismissible />
        @endif

        <!-- Quick Operational Metrics Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <!-- PPDB Summary -->
            <div class="bg-white rounded-2xl p-5 border border-zinc-200/80 shadow-xs hover:border-red-200 transition-all">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold text-zinc-500 uppercase tracking-wider">PPDB / SPMB</span>
                    <span class="p-2 bg-red-50 text-[#E11D2E] rounded-xl">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path>
                        </svg>
                    </span>
                </div>
                <div class="mt-3">
                    <span class="text-2xl font-bold text-zinc-900">{{ number_format($ppdbTotal) }}</span>
                    <span class="text-xs text-zinc-500 ml-1">Pendaftar</span>
                </div>
                <div class="mt-2 text-xs flex items-center justify-between text-zinc-600 pt-2 border-t border-zinc-100">
                    <span>{{ $ppdbAccepted }} Diterima</span>
                    <span class="text-emerald-600 font-semibold">{{ $ppdbEnrolled }} Terdaftar</span>
                </div>
                <a href="{{ route('admissions.dashboard') }}" class="block mt-3 text-xs font-semibold text-[#E11D2E] hover:underline">Kelola PPDB &rarr;</a>
            </div>

            <!-- Library Summary -->
            <div class="bg-white rounded-2xl p-5 border border-zinc-200/80 shadow-xs hover:border-red-200 transition-all">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold text-zinc-500 uppercase tracking-wider">Perpustakaan</span>
                    <span class="p-2 bg-blue-50 text-blue-600 rounded-xl">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                        </svg>
                    </span>
                </div>
                <div class="mt-3">
                    <span class="text-2xl font-bold text-zinc-900">{{ number_format($booksCount) }}</span>
                    <span class="text-xs text-zinc-500 ml-1">Judul Buku</span>
                </div>
                <div class="mt-2 text-xs flex items-center justify-between text-zinc-600 pt-2 border-t border-zinc-100">
                    <span>{{ $activeLoansCount }} Dipinjam</span>
                    <span class="{{ $overdueLoansCount > 0 ? 'text-rose-600 font-bold' : 'text-zinc-500' }}">{{ $overdueLoansCount }} Terlambat</span>
                </div>
                <a href="{{ route('operations.library.books.index') }}" class="block mt-3 text-xs font-semibold text-blue-600 hover:underline">Katalog Buku &rarr;</a>
            </div>

            <!-- Asset & Sarpras Summary -->
            <div class="bg-white rounded-2xl p-5 border border-zinc-200/80 shadow-xs hover:border-red-200 transition-all">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold text-zinc-500 uppercase tracking-wider">Aset & Sarpras</span>
                    <span class="p-2 bg-amber-50 text-amber-600 rounded-xl">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                        </svg>
                    </span>
                </div>
                <div class="mt-3">
                    <span class="text-2xl font-bold text-zinc-900">{{ number_format($assetsCount) }}</span>
                    <span class="text-xs text-zinc-500 ml-1">Total Aset</span>
                </div>
                <div class="mt-2 text-xs flex items-center justify-between text-zinc-600 pt-2 border-t border-zinc-100">
                    <span>{{ $damagedAssetsCount }} Perlu Perbaikan</span>
                    @if($lowStockConsumables > 0)
                        <span class="text-rose-600 font-semibold">{{ $lowStockConsumables }} Stok Menipis</span>
                    @else
                        <span class="text-emerald-600">Stok Cukup</span>
                    @endif
                </div>
                <a href="{{ route('operations.inventory.assets.index') }}" class="block mt-3 text-xs font-semibold text-amber-600 hover:underline">Daftar Aset &rarr;</a>
            </div>

            <!-- Facilities & Laboratories Summary -->
            <div class="bg-white rounded-2xl p-5 border border-zinc-200/80 shadow-xs hover:border-red-200 transition-all">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold text-zinc-500 uppercase tracking-wider">Fasilitas & Lab</span>
                    <span class="p-2 bg-emerald-50 text-emerald-600 rounded-xl">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                        </svg>
                    </span>
                </div>
                <div class="mt-3">
                    <span class="text-2xl font-bold text-zinc-900">{{ number_format($roomsCount) }}</span>
                    <span class="text-xs text-zinc-500 ml-1">Ruang / Lab</span>
                </div>
                <div class="mt-2 text-xs flex items-center justify-between text-zinc-600 pt-2 border-t border-zinc-100">
                    <span>{{ $pendingReservations }} Menunggu Izin</span>
                    <span class="text-emerald-600 font-semibold">{{ $todayReservations->count() }} Digunakan Hari Ini</span>
                </div>
                <a href="{{ route('operations.facilities.rooms.index') }}" class="block mt-3 text-xs font-semibold text-emerald-600 hover:underline">Jadwal Fasilitas &rarr;</a>
            </div>
        </div>

        <!-- Main Dashboard 2-Column Section -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Left: Jadwal Fasilitas & Lab Hari Ini -->
            <div class="bg-white rounded-2xl p-6 border border-zinc-200/80 shadow-xs">
                <div class="flex items-center justify-between pb-4 border-b border-zinc-100">
                    <div>
                        <h2 class="text-base font-bold text-zinc-900">Jadwal Penggunaan Ruang & Lab Hari Ini</h2>
                        <p class="text-xs text-zinc-500 mt-0.5">{{ \Carbon\Carbon::today()->translatedFormat('l, d F Y') }}</p>
                    </div>
                    <x-button href="{{ route('operations.facilities.reservations.index') }}" variant="secondary" size="xs">
                        Semua Jadwal
                    </x-button>
                </div>

                <div class="mt-4 space-y-3">
                    @forelse($todayReservations as $res)
                        <div class="p-3.5 rounded-xl border border-zinc-100 bg-zinc-50/50 flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <span class="w-10 h-10 rounded-xl bg-emerald-100 text-emerald-700 flex items-center justify-center font-bold text-xs">
                                    {{ substr($res->start_time, 0, 5) }}
                                </span>
                                <div>
                                    <h4 class="text-sm font-semibold text-zinc-900">{{ $res->room->name }}</h4>
                                    <p class="text-xs text-zinc-500">{{ $res->purpose }} &bull; Oleh: {{ $res->user->name }}</p>
                                </div>
                            </div>
                            <span class="text-xs font-medium text-zinc-600 px-2.5 py-1 rounded-full bg-white border border-zinc-200">
                                {{ substr($res->start_time, 0, 5) }} - {{ substr($res->end_time, 0, 5) }} WIB
                            </span>
                        </div>
                    @empty
                        <div class="text-center py-8 text-zinc-400">
                            <svg class="w-10 h-10 mx-auto text-zinc-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                            <p class="text-sm font-medium">Tidak ada jadwal reservasi ruangan aktif untuk hari ini.</p>
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- Right: Arsip Persuratan Resmi Terbaru -->
            <div class="bg-white rounded-2xl p-6 border border-zinc-200/80 shadow-xs">
                <div class="flex items-center justify-between pb-4 border-b border-zinc-100">
                    <div>
                        <h2 class="text-base font-bold text-zinc-900">Arsip Persuratan & Agenda Sekolah</h2>
                        <p class="text-xs text-zinc-500 mt-0.5">Surat masuk dinas dan surat keluar yang baru diterbitkan</p>
                    </div>
                    <x-button href="{{ route('operations.correspondence.index') }}" variant="secondary" size="xs">
                        Buka Agenda
                    </x-button>
                </div>

                <div class="mt-4 space-y-3">
                    @forelse($recentLetters as $letter)
                        <div class="p-3.5 rounded-xl border border-zinc-100 hover:border-zinc-300 transition-colors flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <span class="px-2.5 py-1 text-xs font-bold rounded-lg {{ $letter->type === 'incoming' ? 'bg-sky-50 text-sky-700 border border-sky-200' : 'bg-purple-50 text-purple-700 border border-purple-200' }}">
                                    {{ $letter->typeLabel() }}
                                </span>
                                <div>
                                    <h4 class="text-sm font-semibold text-zinc-900 line-clamp-1">{{ $letter->subject }}</h4>
                                    <p class="text-xs text-zinc-500">No: {{ $letter->reference_number }} &bull; {{ $letter->letter_date->format('d/m/Y') }}</p>
                                </div>
                            </div>
                            <a href="{{ route('operations.correspondence.show', $letter) }}" class="text-xs font-semibold text-zinc-600 hover:text-zinc-900 px-2 py-1 bg-zinc-100 rounded-md">
                                Lihat
                            </a>
                        </div>
                    @empty
                        <div class="text-center py-8 text-zinc-400">
                            <svg class="w-10 h-10 mx-auto text-zinc-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                            </svg>
                            <p class="text-sm font-medium">Belum ada arsip persuratan tercatat.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Operational Modules Quick Navigation Grid -->
        <div class="bg-gradient-to-br from-zinc-900 to-zinc-800 text-white rounded-3xl p-6 sm:p-8">
            <h3 class="text-lg font-bold">Akses Navigasi Cepat Modul Operasional</h3>
            <p class="text-xs text-zinc-400 mt-1">Pilih modul kerja untuk mengelola aset, perpustakaan, reservasi lab, atau persuratan resmi sekolah.</p>

            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3 mt-6">
                <a href="{{ route('operations.library.books.index') }}" class="p-4 bg-white/5 hover:bg-white/10 rounded-2xl border border-white/10 transition-colors flex flex-col items-center text-center group">
                    <span class="w-10 h-10 rounded-xl bg-blue-500/20 text-blue-400 flex items-center justify-center mb-2 group-hover:scale-110 transition-transform">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                        </svg>
                    </span>
                    <span class="text-xs font-semibold">Katalog Buku</span>
                </a>

                <a href="{{ route('operations.library.loans.index') }}" class="p-4 bg-white/5 hover:bg-white/10 rounded-2xl border border-white/10 transition-colors flex flex-col items-center text-center group">
                    <span class="w-10 h-10 rounded-xl bg-indigo-500/20 text-indigo-400 flex items-center justify-center mb-2 group-hover:scale-110 transition-transform">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path>
                        </svg>
                    </span>
                    <span class="text-xs font-semibold">Sirkulasi Pinjam</span>
                </a>

                <a href="{{ route('operations.inventory.assets.index') }}" class="p-4 bg-white/5 hover:bg-white/10 rounded-2xl border border-white/10 transition-colors flex flex-col items-center text-center group">
                    <span class="w-10 h-10 rounded-xl bg-amber-500/20 text-amber-400 flex items-center justify-center mb-2 group-hover:scale-110 transition-transform">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                        </svg>
                    </span>
                    <span class="text-xs font-semibold">Aset Sarpras</span>
                </a>

                <a href="{{ route('operations.inventory.consumables.index') }}" class="p-4 bg-white/5 hover:bg-white/10 rounded-2xl border border-white/10 transition-colors flex flex-col items-center text-center group">
                    <span class="w-10 h-10 rounded-xl bg-orange-500/20 text-orange-400 flex items-center justify-center mb-2 group-hover:scale-110 transition-transform">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                        </svg>
                    </span>
                    <span class="text-xs font-semibold">Habis Pakai</span>
                </a>

                <a href="{{ route('operations.facilities.reservations.index') }}" class="p-4 bg-white/5 hover:bg-white/10 rounded-2xl border border-white/10 transition-colors flex flex-col items-center text-center group">
                    <span class="w-10 h-10 rounded-xl bg-emerald-500/20 text-emerald-400 flex items-center justify-center mb-2 group-hover:scale-110 transition-transform">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                        </svg>
                    </span>
                    <span class="text-xs font-semibold">Reservasi Lab</span>
                </a>

                <a href="{{ route('operations.correspondence.index') }}" class="p-4 bg-white/5 hover:bg-white/10 rounded-2xl border border-white/10 transition-colors flex flex-col items-center text-center group">
                    <span class="w-10 h-10 rounded-xl bg-purple-500/20 text-purple-400 flex items-center justify-center mb-2 group-hover:scale-110 transition-transform">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                        </svg>
                    </span>
                    <span class="text-xs font-semibold">Persuratan Tata Usaha</span>
                </a>
            </div>
        </div>
    </div>
</x-app-layout>
