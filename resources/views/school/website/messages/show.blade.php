@extends('layouts.app')

@section('content')
<div class="space-y-6 max-w-5xl">

    <!-- Breadcrumb & Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-zinc-200">
        <div>
            <div class="flex items-center gap-2 mb-1 text-xs">
                <a href="{{ route('website.dashboard') }}" class="text-zinc-500 hover:text-zinc-800">CMS Website</a>
                <span class="text-zinc-300">&rsaquo;</span>
                <a href="{{ route('website.messages.index') }}" class="text-zinc-500 hover:text-zinc-800">Pesan Pengunjung</a>
                <span class="text-zinc-300">&rsaquo;</span>
                <span class="font-mono text-zinc-800 font-semibold">{{ $message->reference_number }}</span>
            </div>
            <h1 class="text-2xl font-extrabold text-zinc-900 tracking-tight flex items-center gap-3">
                <span>Detail Pesan Pengunjung</span>
                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold border {{ $message->statusBadgeClasses() }}">
                    {{ $message->statusLabel() }}
                </span>
            </h1>
        </div>

        <div class="flex items-center gap-2.5">
            <a href="{{ route('website.messages.index') }}" class="inline-flex items-center gap-1 px-3.5 py-2 rounded-xl text-xs font-bold border border-zinc-300 bg-white hover:bg-zinc-50 text-zinc-800 transition-colors">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                <span>Daftar Pesan</span>
            </a>

            <form method="POST" action="{{ route('website.messages.destroy', $message) }}" onsubmit="return confirm('Apakah Anda yakin ingin menghapus pesan ini secara permanen?')">
                @csrf
                @method('DELETE')
                <button type="submit" class="inline-flex items-center gap-1 px-3 py-2 rounded-xl text-xs font-bold border border-rose-200 bg-rose-50 hover:bg-rose-100 text-[#E11D2E] transition-colors">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    <span>Hapus</span>
                </button>
            </form>
        </div>
    </div>

    <!-- Alert Notifications -->
    @if (session('success'))
        <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-semibold flex items-center gap-2">
            <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">

        <!-- Left / Main: Message Content (8 cols) -->
        <div class="lg:col-span-8 space-y-6">

            <!-- Message Body Card -->
            <div class="bg-white rounded-2xl p-6 sm:p-7 border border-zinc-200 shadow-2xs space-y-5">
                
                <!-- Subject Header -->
                <div class="pb-4 border-b border-zinc-100 space-y-2">
                    <div class="flex items-center gap-2">
                        <span class="px-2.5 py-0.5 rounded-md font-semibold text-xs bg-zinc-100 text-zinc-700">
                            {{ $message->categoryLabel() }}
                        </span>
                        <span class="font-mono text-xs text-zinc-400">
                            Ref: {{ $message->reference_number }}
                        </span>
                    </div>
                    <h2 class="text-xl sm:text-2xl font-black text-zinc-900 leading-snug">
                        {{ $message->subject }}
                    </h2>
                </div>

                <!-- Message Body (Plain text escaped with line breaks) -->
                <div>
                    <h3 class="text-xs font-bold uppercase tracking-wider text-zinc-400 mb-2">Isi Pesan Pengunjung:</h3>
                    <div class="p-5 rounded-xl bg-zinc-50/80 border border-zinc-200 text-sm text-zinc-800 leading-relaxed font-normal whitespace-pre-wrap selection:bg-[#E11D2E] selection:text-white">
                        {!! nl2br(e($message->message)) !!}
                    </div>
                </div>

                <!-- Timestamp & Read Metadata -->
                <div class="pt-4 border-t border-zinc-100 flex flex-wrap items-center justify-between gap-3 text-xs text-zinc-400">
                    <div class="flex items-center gap-1.5">
                        <svg class="w-4 h-4 text-zinc-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span>Diterima pada: <strong>{{ $message->created_at->format('d F Y, H:i') }} WIB</strong></span>
                    </div>
                    @if ($message->read_at)
                        <div class="flex items-center gap-1.5 text-emerald-600">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            <span>Pertama kali dibuka: {{ $message->read_at->format('d/m/Y H:i') }}</span>
                        </div>
                    @endif
                </div>

            </div>

            <!-- Internal Follow-up Notes Card -->
            <div class="bg-white rounded-2xl p-6 border border-zinc-200 shadow-2xs space-y-4">
                <div class="flex items-center justify-between pb-3 border-b border-zinc-100">
                    <div class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                        <h3 class="text-sm font-bold text-zinc-900">Catatan Tindak Lanjut Internal</h3>
                    </div>
                    <span class="text-[10px] font-bold uppercase tracking-wider text-amber-800 bg-amber-50 px-2 py-0.5 rounded border border-amber-200">
                        Hanya Terlihat Staf / Admin
                    </span>
                </div>

                <form method="POST" action="{{ route('website.messages.update-notes', $message) }}" class="space-y-3">
                    @csrf
                    @method('PUT')

                    <textarea
                        name="internal_notes"
                        rows="4"
                        placeholder="Tuliskan catatan internal mengenai pesan ini (misal: telah dihubungi via WhatsApp oleh Bu Ani pada 14/10/2026, berkas PPDB terkonfirmasi)..."
                        class="w-full text-xs sm:text-sm rounded-xl border border-zinc-200 px-3.5 py-2.5 text-zinc-900 placeholder:text-zinc-400 focus:outline-none focus:ring-2 focus:ring-[#E11D2E]/20 focus:border-[#E11D2E] leading-relaxed"
                    >{{ old('internal_notes', $message->internal_notes) }}</textarea>

                    <div class="flex items-center justify-between">
                        <span class="text-[11px] text-zinc-400">
                            Catatan ini tidak akan pernah ditampilkan di website publik atau dibagikan ke pengunjung.
                        </span>
                        <button type="submit" class="px-4 py-2 rounded-xl text-xs font-bold bg-zinc-800 hover:bg-black text-white transition-colors">
                            Simpan Catatan
                        </button>
                    </div>
                </form>
            </div>

        </div>

        <!-- Right: Status Update & Sender Profile (4 cols) -->
        <div class="lg:col-span-4 space-y-6">

            <!-- Status Management Card -->
            <div class="bg-white rounded-2xl p-5 border border-zinc-200 shadow-2xs space-y-4">
                <h3 class="text-xs font-bold uppercase tracking-wider text-zinc-400 pb-2 border-b border-zinc-100">
                    Status Korespondensi
                </h3>

                <form method="POST" action="{{ route('website.messages.update-status', $message) }}" class="space-y-3">
                    @csrf
                    @method('PUT')

                    <div>
                        <label for="status" class="block text-xs font-bold text-zinc-700 mb-1">
                            Ubah Status Pesan:
                        </label>
                        <select
                            id="status"
                            name="status"
                            class="w-full text-xs rounded-xl border border-zinc-200 px-3 py-2 text-zinc-900 bg-white focus:outline-none focus:ring-2 focus:ring-[#E11D2E]/20 focus:border-[#E11D2E]"
                        >
                            @foreach (\App\Models\SchoolMessage::STATUSES as $val => $lbl)
                                <option value="{{ $val }}" {{ $message->status === $val ? 'selected' : '' }}>
                                    {{ $lbl }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <button type="submit" class="w-full py-2 rounded-xl text-xs font-bold bg-[#E11D2E] hover:bg-[#C71827] text-white shadow-xs transition-colors">
                        Perbarui Status
                    </button>
                </form>

                @if ($message->resolved_at)
                    <div class="p-3 rounded-xl bg-emerald-50 border border-emerald-200 text-[11px] text-emerald-800">
                        <strong>Diselesaikan:</strong> {{ $message->resolved_at->format('d M Y, H:i') }} WIB
                    </div>
                @endif
            </div>

            <!-- Sender Information Card -->
            <div class="bg-white rounded-2xl p-5 border border-zinc-200 shadow-2xs space-y-4">
                <h3 class="text-xs font-bold uppercase tracking-wider text-zinc-400 pb-2 border-b border-zinc-100">
                    Data Pengirim
                </h3>

                <div class="space-y-3 text-xs">
                    <div>
                        <span class="text-zinc-400 block text-[11px]">Nama Lengkap</span>
                        <strong class="text-zinc-900 text-sm font-bold block mt-0.5">{{ $message->name }}</strong>
                    </div>

                    <div>
                        <span class="text-zinc-400 block text-[11px]">Alamat Email</span>
                        <a href="mailto:{{ $message->email }}?subject=Re:%20{{ urlencode($message->subject) }}" class="text-[#E11D2E] hover:underline font-semibold block mt-0.5 break-all">
                            {{ $message->email }} &rarr;
                        </a>
                    </div>

                    @if ($message->phone)
                        <div>
                            <span class="text-zinc-400 block text-[11px]">Nomor Telepon / WhatsApp</span>
                            <div class="flex items-center gap-2 mt-0.5">
                                <a href="tel:{{ $message->phone }}" class="text-zinc-800 font-semibold hover:underline">
                                    {{ $message->phone }}
                                </a>
                                @php
                                    $waNumber = preg_replace('/[^0-9]/', '', $message->phone);
                                    if (str_starts_with($waNumber, '0')) {
                                        $waNumber = '62' . substr($waNumber, 1);
                                    }
                                @endphp
                                <a href="https://wa.me/{{ $waNumber }}" target="_blank" class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-50 text-emerald-700 hover:bg-emerald-100 border border-emerald-200 transition-colors">
                                    WhatsApp &rarr;
                                </a>
                            </div>
                        </div>
                    @endif

                    @if ($message->ip_address)
                        <div class="pt-2 border-t border-zinc-100 text-[11px] text-zinc-400">
                            <span>Alamat IP: <code>{{ $message->ip_address }}</code></span>
                        </div>
                    @endif
                </div>

                <div class="pt-3 border-t border-zinc-100">
                    <a
                        href="mailto:{{ $message->email }}?subject=Tanggapan%20{{ urlencode($school->name) }}:%20{{ urlencode($message->subject) }}%20(Ref:%20{{ $message->reference_number }})"
                        class="w-full inline-flex items-center justify-center gap-1.5 py-2 rounded-xl text-xs font-bold bg-zinc-800 hover:bg-black text-white transition-colors"
                    >
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                        <span>Balas via Email Klien</span>
                    </a>
                </div>
            </div>

        </div>

    </div>

</div>
@endsection
