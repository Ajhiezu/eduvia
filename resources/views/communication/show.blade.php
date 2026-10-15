<x-layouts.app>
    <x-slot:headerTitle>
        {{ $conversation->subject }}
    </x-slot:headerTitle>

    <div class="max-w-4xl mx-auto space-y-6">

        <!-- Top Header & Thread Meta -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-4 border-b border-zinc-200">
            <div>
                <a href="{{ route('conversations.index') }}" class="text-xs text-zinc-500 hover:text-zinc-900 inline-flex items-center gap-1 font-semibold">
                    &larr; Kembali ke Daftar Percakapan
                </a>
                <div class="flex items-center gap-2 mt-1 flex-wrap">
                    <h1 class="text-lg sm:text-2xl font-extrabold text-zinc-900 tracking-tight">
                        {{ $conversation->subject }}
                    </h1>
                    @if ($conversation->status === 'closed')
                        <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-zinc-100 text-zinc-600 border border-zinc-200">
                            Percakapan Ditutup
                        </span>
                    @else
                        <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-50 text-emerald-800 border border-emerald-200">
                            Aktif
                        </span>
                    @endif
                </div>
                <p class="text-xs text-zinc-500 mt-0.5">
                    Peserta: <strong class="text-zinc-800">{{ $conversation->parent?->name }}</strong> (Wali Murid) &bull;
                    <strong class="text-zinc-800">{{ $conversation->teacher?->name }}</strong> (Guru) &bull;
                    Siswa: <span class="text-brand-600 font-semibold">{{ $conversation->student?->name }}</span>
                </p>
            </div>

            @if ($conversation->status !== 'closed')
                <form action="{{ route('conversations.close', $conversation) }}" method="POST" onsubmit="return confirm('Apakah Anda ingin menutup percakapan ini?');">
                    @csrf
                    <button type="submit" class="px-3 py-1.5 rounded-xl text-xs font-bold border border-zinc-300 hover:bg-zinc-100 text-zinc-700 transition-colors">
                        Tandai Selesai / Tutup
                    </button>
                </form>
            @endif
        </div>

        @if (session('success'))
            <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-xs text-emerald-800 font-bold flex items-center gap-2">
                <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        <!-- Message Thread Stream -->
        <div class="bg-white rounded-2xl border border-zinc-200 shadow-xs p-4 sm:p-6 space-y-5">
            <div class="space-y-4">
                @foreach ($conversation->messages as $msg)
                    @php
                        $isMe = (int) $msg->sender_id === (int) auth()->id();
                    @endphp
                    <div class="flex {{ $isMe ? 'justify-end' : 'justify-start' }}">
                        <div class="max-w-[85%] sm:max-w-xl {{ $isMe ? 'bg-brand-50 border border-brand-200 text-zinc-900' : 'bg-zinc-100 text-zinc-800' }} rounded-2xl p-4 shadow-2xs">
                            <div class="flex items-center justify-between gap-3 mb-1 text-[11px] {{ $isMe ? 'text-brand-700 font-bold' : 'text-zinc-500 font-semibold' }}">
                                <span>{{ $isMe ? 'Saya' : $msg->sender?->name }} ({{ $msg->sender?->role->label() }})</span>
                                <span class="font-normal text-[10px] text-zinc-400">{{ $msg->created_at->format('d M H:i') }}</span>
                            </div>

                            <p class="text-xs leading-relaxed whitespace-pre-line">{{ $msg->body }}</p>

                            @if ($msg->attachment_path)
                                <div class="mt-3 pt-2 border-t {{ $isMe ? 'border-brand-200/60' : 'border-zinc-200' }}">
                                    <a
                                        href="{{ $msg->attachmentUrl() }}"
                                        target="_blank"
                                        download
                                        class="inline-flex items-center gap-1.5 text-[11px] font-bold text-zinc-800 hover:text-brand-600 transition-colors"
                                    >
                                        <svg class="w-3.5 h-3.5 text-zinc-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                        <span class="truncate max-w-[200px]">{{ $msg->attachment_name ?: 'Lampiran Berkas' }}</span>
                                    </a>
                                </div>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Reply Box (If Active) -->
            @if ($conversation->status !== 'closed')
                <div class="pt-6 border-t border-zinc-200 mt-6">
                    <form action="{{ route('conversations.reply', $conversation) }}" method="POST" enctype="multipart/form-data" class="space-y-3">
                        @csrf
                        <div>
                            <label class="block text-xs font-bold text-zinc-700 mb-1">Kirim Balasan Pesan</label>
                            <textarea
                                name="body"
                                rows="3"
                                required
                                placeholder="Ketik balasan pesan Anda..."
                                class="w-full text-xs rounded-xl border-zinc-300 focus:border-[#E11D2E] focus:ring-[#E11D2E]"
                            ></textarea>
                            @error('body')
                                <p class="text-xs text-rose-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                            <div>
                                <input
                                    type="file"
                                    name="attachment"
                                    class="block w-full text-xs text-zinc-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-xl file:border-0 file:text-[11px] file:font-semibold file:bg-zinc-100 file:text-zinc-700 hover:file:bg-zinc-200"
                                >
                            </div>

                            <button
                                type="submit"
                                class="inline-flex items-center justify-center gap-1.5 px-5 py-2.5 rounded-xl text-xs font-bold bg-[#E11D2E] hover:bg-[#C81423] text-white shadow-xs transition-colors shrink-0"
                            >
                                <span>Kirim Balasan</span>
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
                            </button>
                        </div>
                    </form>
                </div>
            @else
                <div class="p-4 rounded-xl bg-zinc-50 border border-zinc-200 text-center text-xs text-zinc-500">
                    Percakapan ini telah ditutup. Untuk konsultasi lebih lanjut, silakan mulai percakapan baru.
                </div>
            @endif
        </div>

    </div>
</x-layouts.app>
