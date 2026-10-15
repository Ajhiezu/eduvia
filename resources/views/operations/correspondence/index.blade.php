<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-zinc-900">Tata Usaha & Persuratan Resmi</h1>
                <p class="text-sm text-zinc-500 mt-1">Buku agenda surat masuk, surat dinas keluar, penomoran resmi, dan arsip dokumen sekolah.</p>
            </div>
            <div class="flex items-center gap-2">
                <x-button href="{{ route('operations.correspondence.certificate.create') }}" variant="secondary" size="sm">
                    Buat Surat Siswa
                </x-button>
                <x-button href="{{ route('operations.correspondence.create', ['type' => 'incoming']) }}" variant="secondary" size="sm">
                    + Surat Masuk
                </x-button>
                <x-button href="{{ route('operations.correspondence.create', ['type' => 'outgoing']) }}" variant="primary" size="sm">
                    + Terbitkan Surat Keluar
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

        <!-- Tabs: Surat Masuk vs Surat Keluar -->
        <div class="flex items-center gap-2 border-b border-zinc-200 pb-3">
            <a href="{{ route('operations.correspondence.index', ['type' => 'incoming']) }}" class="px-4 py-2 rounded-xl text-xs font-bold transition-colors {{ $type === 'incoming' ? 'bg-[#E11D2E] text-white shadow-xs' : 'text-zinc-600 hover:bg-zinc-100' }}">
                Surat Masuk ({{ $incomingCount }})
                @if($actionNeededCount > 0)
                    <span class="ml-1.5 px-1.5 py-0.5 rounded-full text-[10px] bg-white text-rose-600 font-extrabold">{{ $actionNeededCount }}</span>
                @endif
            </a>
            <a href="{{ route('operations.correspondence.index', ['type' => 'outgoing']) }}" class="px-4 py-2 rounded-xl text-xs font-bold transition-colors {{ $type === 'outgoing' ? 'bg-[#E11D2E] text-white shadow-xs' : 'text-zinc-600 hover:bg-zinc-100' }}">
                Surat Keluar Resmi ({{ $outgoingCount }})
            </a>
        </div>

        <!-- Filter Bar -->
        <div class="bg-white p-4 rounded-2xl border border-zinc-200 shadow-xs">
            <form method="GET" action="{{ route('operations.correspondence.index') }}" class="grid grid-cols-1 sm:grid-cols-4 gap-3">
                <input type="hidden" name="type" value="{{ $type }}">
                <div class="sm:col-span-2">
                    <x-input type="text" name="search" :value="$search" placeholder="Cari nomor surat, perihal, instansi pengirim/tujuan..." />
                </div>
                <div>
                    <select name="category" class="w-full text-sm rounded-xl border-zinc-300 focus:border-[#E11D2E] focus:ring-[#E11D2E]">
                        <option value="">Semua Kategori</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat }}" {{ $category === $cat ? 'selected' : '' }}>{{ $cat }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <x-button type="submit" variant="secondary" size="md" class="w-full justify-center">Filter</x-button>
                </div>
            </form>
        </div>

        <!-- Letters Table -->
        <div class="bg-white rounded-2xl border border-zinc-200/80 shadow-xs overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-zinc-50 border-b border-zinc-200 text-zinc-500 uppercase tracking-wider font-semibold">
                        <tr>
                            <th class="px-4 py-3">Nomor Surat</th>
                            <th class="px-4 py-3">Tanggal</th>
                            <th class="px-4 py-3">Perihal / Subjek</th>
                            <th class="px-4 py-3">{{ $type === 'incoming' ? 'Pengirim (Instansi)' : 'Tujuan Penerima' }}</th>
                            <th class="px-4 py-3">Kategori & Status</th>
                            <th class="px-4 py-3 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-100">
                        @forelse($letters as $letter)
                            <tr class="hover:bg-zinc-50/50">
                                <td class="px-4 py-3 font-mono font-bold text-zinc-900">{{ $letter->reference_number }}</td>
                                <td class="px-4 py-3">
                                    <div class="font-medium text-zinc-800">{{ $letter->letter_date->format('d/m/Y') }}</div>
                                    <div class="text-[11px] text-zinc-400">Dicatat: {{ $letter->received_or_sent_date->format('d/m/Y') }}</div>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="font-bold text-zinc-900">{{ $letter->subject }}</div>
                                    @if($letter->file_path)
                                        <span class="inline-flex items-center text-[10px] text-blue-600 font-medium mt-0.5">
                                            <svg class="w-3 h-3 mr-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"></path>
                                            </svg>
                                            Lampiran Berkas
                                        </span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 font-medium text-zinc-800">{{ $letter->sender_or_recipient }}</td>
                                <td class="px-4 py-3">
                                    <span class="font-medium text-zinc-600 block">{{ $letter->category }}</span>
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold {{ $letter->statusVariant() === 'warning' ? 'bg-amber-50 text-amber-700 border border-amber-200' : 'bg-zinc-100 text-zinc-700' }}">
                                        {{ $letter->statusLabel() }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <a href="{{ route('operations.correspondence.show', $letter) }}" class="text-xs font-bold text-[#E11D2E] hover:underline">
                                        Buka Arsip &rarr;
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 py-8 text-center text-zinc-400">Belum ada arsip surat {{ $type === 'incoming' ? 'masuk' : 'keluar' }} tercatat.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mt-4">
            {{ $letters->links() }}
        </div>
    </div>
</x-app-layout>
