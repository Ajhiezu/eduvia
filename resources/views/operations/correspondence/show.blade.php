<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div class="flex items-center gap-3">
                <x-button href="{{ route('operations.correspondence.index', ['type' => $letter->type]) }}" variant="ghost" size="sm">
                    &larr; Buku Agenda
                </x-button>
                <div>
                    <h1 class="text-xl font-bold tracking-tight text-zinc-900">{{ $letter->subject }}</h1>
                    <p class="text-xs text-zinc-500 font-mono">No: {{ $letter->reference_number }} &bull; {{ $letter->typeLabel() }}</p>
                </div>
            </div>
            <div class="flex items-center gap-2">
                @if($letter->file_path)
                    <x-button href="{{ route('operations.correspondence.download', $letter) }}" variant="secondary" size="sm">
                        <svg class="w-4 h-4 mr-1 text-zinc-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                        </svg>
                        Unduh Berkas Lampiran
                    </x-button>
                @endif
                <x-button href="{{ route('operations.correspondence.edit', $letter) }}" variant="secondary" size="sm">
                    Edit Surat
                </x-button>
                <form action="{{ route('operations.correspondence.destroy', $letter) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus arsip surat ini?')">
                    @csrf
                    @method('DELETE')
                    <x-button type="submit" variant="danger" size="sm">Hapus</x-button>
                </form>
            </div>
        </div>
    </x-slot>

    <div class="py-6 max-w-4xl space-y-6">
        @if (session('status'))
            <x-alert type="success" :message="session('status')" dismissible />
        @endif
        @if (session('error'))
            <x-alert type="error" :message="session('error')" dismissible />
        @endif

        <div class="bg-white rounded-2xl border border-zinc-200/80 shadow-xs p-6 sm:p-8 space-y-6">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between pb-6 border-b border-zinc-100 gap-4">
                <div>
                    <span class="text-xs font-bold uppercase tracking-wider text-zinc-400">Klasifikasi Kategori</span>
                    <h3 class="text-lg font-bold text-zinc-900 mt-0.5">{{ $letter->category }}</h3>
                </div>
                <div class="flex items-center gap-2">
                    <span class="px-3 py-1 rounded-full text-xs font-bold {{ $letter->statusVariant() === 'warning' ? 'bg-amber-50 text-amber-700 border border-amber-200' : 'bg-zinc-100 text-zinc-700' }}">
                        {{ $letter->statusLabel() }}
                    </span>
                    <span class="px-3 py-1 rounded-full text-xs font-bold {{ $letter->type === 'incoming' ? 'bg-sky-50 text-sky-700' : 'bg-purple-50 text-purple-700' }}">
                        {{ $letter->typeLabel() }}
                    </span>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-xs">
                <div class="p-4 rounded-xl bg-zinc-50 border border-zinc-100 space-y-2">
                    <div class="flex justify-between">
                        <span class="text-zinc-500">Nomor Surat:</span>
                        <span class="font-mono font-bold text-zinc-900">{{ $letter->reference_number }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-zinc-500">Tanggal Surat:</span>
                        <span class="font-semibold text-zinc-800">{{ $letter->letter_date->format('d/m/Y') }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-zinc-500">{{ $letter->type === 'incoming' ? 'Tanggal Diterima TU:' : 'Tanggal Pengiriman:' }}</span>
                        <span class="font-semibold text-zinc-800">{{ $letter->received_or_sent_date->format('d/m/Y') }}</span>
                    </div>
                </div>

                <div class="p-4 rounded-xl bg-zinc-50 border border-zinc-100 space-y-2">
                    <div class="flex justify-between">
                        <span class="text-zinc-500">{{ $letter->type === 'incoming' ? 'Pihak Pengirim:' : 'Pihak Penerima:' }}</span>
                        <span class="font-bold text-zinc-900">{{ $letter->sender_or_recipient }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-zinc-500">Batas Waktu Tindak Lanjut:</span>
                        <span class="{{ $letter->action_deadline ? 'text-amber-700 font-bold' : 'text-zinc-400' }}">
                            {{ $letter->action_deadline ? $letter->action_deadline->format('d/m/Y') : '-' }}
                        </span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-zinc-500">Petugas Pencatat:</span>
                        <span class="font-medium text-zinc-700">{{ $letter->creator->name ?? 'Sistem' }}</span>
                    </div>
                </div>
            </div>

            @if($letter->notes)
                <div class="p-4 rounded-xl border border-zinc-100 bg-zinc-50/50">
                    <span class="text-xs font-bold text-zinc-700 uppercase tracking-wider block mb-1">Catatan Disposisi / Ringkasan Isi</span>
                    <p class="text-xs text-zinc-600 leading-relaxed whitespace-pre-line">{{ $letter->notes }}</p>
                </div>
            @endif

            @if($letter->file_path)
                <div class="p-4 rounded-xl border border-blue-200 bg-blue-50/50 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <span class="p-2.5 bg-blue-100 text-blue-700 rounded-xl">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                        </span>
                        <div>
                            <h4 class="text-xs font-bold text-zinc-900">{{ $letter->file_original_name ?? 'Berkas Lampiran Surat' }}</h4>
                            <p class="text-[11px] text-zinc-500">Tersimpan secara privat dan terproteksi di storage server</p>
                        </div>
                    </div>
                    <x-button href="{{ route('operations.correspondence.download', $letter) }}" variant="secondary" size="xs">
                        Unduh Berkas
                    </x-button>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
