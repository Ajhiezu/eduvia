<x-layouts.app>
    <x-slot:headerTitle>
        Pesan & Konsultasi Guru - Orang Tua
    </x-slot:headerTitle>

    <div class="space-y-6">

        <!-- Top Header & Actions -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-4 border-b border-zinc-200">
            <div>
                <h1 class="text-xl sm:text-2xl font-extrabold text-zinc-900 tracking-tight">
                    Komunikasi & Konsultasi
                </h1>
                <p class="text-xs text-zinc-500 mt-0.5">
                    Ruang komunikasi resmi, aman, dan terotorisasi antara pihak sekolah, tenaga pendidik, dan wali murid
                </p>
            </div>

            <div class="flex items-center gap-2">
                <a
                    href="{{ route('conversations.create') }}"
                    class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-xs font-bold bg-[#E11D2E] hover:bg-[#C81423] text-white shadow-xs transition-colors"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    <span>Mulai Percakapan Baru</span>
                </a>
            </div>
        </div>

        @if (session('success'))
            <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-xs text-emerald-800 font-bold flex items-center gap-2">
                <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        <!-- Filter & Search Controls -->
        <form action="{{ route('conversations.index') }}" method="GET" class="bg-white p-4 rounded-2xl border border-zinc-200/80 shadow-xs flex flex-wrap items-center justify-between gap-3">
            <div class="flex flex-wrap items-center gap-2 w-full sm:w-auto">
                <input
                    type="text"
                    name="search"
                    value="{{ request('search') }}"
                    placeholder="Cari subjek / nama..."
                    class="text-xs rounded-xl border-zinc-300 focus:border-[#E11D2E] focus:ring-[#E11D2E] w-48 sm:w-64"
                >
                <select name="status" class="text-xs rounded-xl border-zinc-300 focus:border-[#E11D2E] focus:ring-[#E11D2E]">
                    <option value="">Semua Status Percakapan</option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Aktif</option>
                    <option value="closed" {{ request('status') === 'closed' ? 'selected' : '' }}>Selesai / Ditutup</option>
                </select>
                <button type="submit" class="px-3.5 py-2 text-xs font-bold rounded-xl bg-zinc-800 text-white hover:bg-zinc-900 transition-colors">
                    Cari
                </button>
                @if (request()->hasAny(['search', 'status']))
                    <a href="{{ route('conversations.index') }}" class="text-xs font-semibold text-zinc-500 hover:text-zinc-800">Reset</a>
                @endif
            </div>

            <span class="text-xs text-zinc-500 font-semibold">
                {{ $conversations->total() }} Percakapan
            </span>
        </form>

        <!-- Conversations List -->
        <div class="bg-white rounded-2xl border border-zinc-200 shadow-xs overflow-hidden divide-y divide-zinc-100">
            @forelse ($conversations as $conv)
                @php
                    $isParent = auth()->user()->id === $conv->parent_id;
                    $partner = $isParent ? $conv->teacher : $conv->parent;
                    $unread = $conv->unreadCountFor(auth()->user());
                @endphp
                <a
                    href="{{ route('conversations.show', $conv) }}"
                    class="p-4 sm:p-5 flex flex-col sm:flex-row sm:items-center justify-between gap-4 hover:bg-zinc-50/80 transition-colors block {{ $unread > 0 ? 'bg-rose-50/20' : '' }}"
                >
                    <div class="flex items-start gap-3.5 min-w-0">
                        <div class="w-12 h-12 rounded-xl {{ $isParent ? 'bg-blue-50 text-blue-700' : 'bg-brand-50 text-brand-600' }} flex items-center justify-center font-extrabold text-base shrink-0">
                            {{ substr($partner?->name ?? 'G', 0, 2) }}
                        </div>

                        <div class="min-w-0">
                            <div class="flex items-center gap-2 flex-wrap mb-1">
                                <h3 class="font-extrabold text-sm text-zinc-900 truncate">
                                    {{ $partner?->name ?? 'Pengguna Sekolah' }}
                                </h3>
                                <span class="px-2 py-0.2 rounded text-[10px] font-bold {{ $isParent ? 'bg-blue-100 text-blue-800' : 'bg-emerald-100 text-emerald-800' }}">
                                    {{ $isParent ? 'Tenaga Pengajar' : 'Wali Murid' }}
                                </span>
                                <span class="text-xs text-zinc-400">&bull;</span>
                                <span class="text-xs text-zinc-600 font-semibold">
                                    Siswa: <strong class="text-zinc-800">{{ $conv->student?->name }}</strong>
                                </span>
                                @if ($conv->status === 'closed')
                                    <span class="px-2 py-0.2 rounded text-[10px] font-bold bg-zinc-100 text-zinc-600 border border-zinc-200">
                                        Ditutup
                                    </span>
                                @endif
                            </div>

                            <h4 class="font-bold text-xs text-zinc-800 truncate">{{ $conv->subject }}</h4>
                            <p class="text-xs text-zinc-500 mt-0.5 line-clamp-1">
                                {{ $conv->latestMessage?->body ?? 'Belum ada pesan.' }}
                            </p>
                        </div>
                    </div>

                    <div class="flex sm:flex-col items-center sm:items-end justify-between sm:justify-center gap-2 shrink-0 text-right">
                        <span class="text-[11px] text-zinc-400">
                            {{ $conv->last_message_at ? $conv->last_message_at->diffForHumans() : $conv->created_at->diffForHumans() }}
                        </span>
                        @if ($unread > 0)
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-[#E11D2E] text-white animate-pulse">
                                {{ $unread }} baru
                            </span>
                        @endif
                    </div>
                </a>
            @empty
                <div class="py-12 text-center text-zinc-400">
                    <svg class="w-10 h-10 mx-auto mb-2 text-zinc-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                    <p class="text-xs text-zinc-500 font-medium">Belum ada riwayat percakapan konsultasi</p>
                </div>
            @endforelse
        </div>

        @if ($conversations->hasPages())
            <div class="p-4 bg-white rounded-2xl border border-zinc-200 shadow-xs">
                {{ $conversations->links() }}
            </div>
        @endif

    </div>
</x-layouts.app>
