<x-app-layout>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8" x-data="{ showRejectModal: false, activePaymentId: null, activePaymentNumber: '' }">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-6 border-b border-zinc-200">
            <div>
                <nav class="flex items-center gap-2 text-xs text-zinc-500 mb-2">
                    <a href="{{ route('finance.payments.index') }}" class="hover:text-zinc-800">Riwayat Pembayaran</a>
                    <span>&bull;</span>
                    <span class="text-zinc-800 font-semibold">Antrean Verifikasi Bukti</span>
                </nav>
                <div class="flex items-center gap-3">
                    <h1 class="text-2xl font-bold text-zinc-900 tracking-tight">Antrean Verifikasi Pembayaran</h1>
                    <span class="px-2.5 py-0.5 rounded-full bg-amber-50 text-amber-700 border border-amber-200 text-xs font-bold">
                        {{ $pendingPayments->total() }} Menunggu
                    </span>
                </div>
                <p class="text-sm text-zinc-500 mt-1">Periksa bukti mutasi atau struk transfer bank yang dikirimkan orang tua siswa sebelum menerbitkan kuitansi resmi.</p>
            </div>
            <a href="{{ route('finance.payments.index') }}" class="px-3.5 py-1.5 rounded-lg border border-zinc-300 text-zinc-700 text-xs font-semibold hover:bg-zinc-50">
                Kembali ke Riwayat
            </a>
        </div>

        @if (session('success'))
            <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm">
                {{ session('success') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-sm">
                <ul class="list-disc list-inside space-y-0.5">
                    @foreach ($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Queue Table -->
        <div class="bg-white rounded-2xl border border-zinc-200 shadow-xs overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-zinc-50/80 text-zinc-500 font-semibold border-b border-zinc-200 uppercase tracking-wider text-[11px]">
                        <tr>
                            <th class="px-5 py-3.5">No. Pengajuan</th>
                            <th class="px-5 py-3.5">Tanggal Bayar</th>
                            <th class="px-5 py-3.5">Nama Siswa</th>
                            <th class="px-5 py-3.5">Tagihan & Sisa</th>
                            <th class="px-5 py-3.5">Nominal Disetor</th>
                            <th class="px-5 py-3.5">Bukti Transfer</th>
                            <th class="px-5 py-3.5 text-right">Keputusan Verifikasi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-200">
                        @forelse ($pendingPayments as $item)
                            <tr class="hover:bg-zinc-50/60 transition-colors">
                                <td class="px-5 py-3.5">
                                    <div class="font-mono font-bold text-zinc-900">{{ $item->payment_number }}</div>
                                    <div class="text-[11px] text-zinc-400">Pengirim: {{ $item->payer?->name ?? 'Wali Murid' }}</div>
                                </td>
                                <td class="px-5 py-3.5 text-zinc-700">
                                    {{ $item->payment_date ? $item->payment_date->format('d/m/Y') : '-' }}
                                    @if ($item->reference_number)
                                        <div class="text-[10px] text-zinc-400 font-mono">Ref: {{ $item->reference_number }}</div>
                                    @endif
                                </td>
                                <td class="px-5 py-3.5">
                                    <div class="font-bold text-zinc-900">{{ $item->student?->name }}</div>
                                    <div class="text-[11px] text-zinc-400">NIS: {{ $item->student?->studentProfile?->nis ?? '-' }}</div>
                                </td>
                                <td class="px-5 py-3.5">
                                    <div class="font-semibold text-zinc-800">{{ $item->invoice?->title }}</div>
                                    <div class="text-[11px] text-zinc-500">
                                        Total: {{ $item->invoice?->formatted_total }} &bull; Sisa: <span class="text-rose-600 font-semibold">{{ $item->invoice?->formatted_remaining }}</span>
                                    </div>
                                </td>
                                <td class="px-5 py-3.5 font-mono font-bold text-emerald-700 text-sm">
                                    {{ $item->formatted_amount }}
                                </td>
                                <td class="px-5 py-3.5">
                                    @if ($item->proof_path)
                                        <a
                                            href="{{ route('finance.payments.proof', $item) }}"
                                            target="_blank"
                                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-blue-50 text-blue-700 border border-blue-200 text-xs font-semibold hover:bg-blue-100 transition-colors"
                                        >
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                            <span>Buka Lampiran</span>
                                        </a>
                                    @else
                                        <span class="text-zinc-400 text-xs italic">Tanpa lampiran</span>
                                    @endif
                                </td>
                                <td class="px-5 py-3.5 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <!-- Approve Form -->
                                        <form method="POST" action="{{ route('finance.payments.verify', $item) }}" onsubmit="return confirm('Verifikasi pembayaran ini? Kuitansi resmi akan diterbitkan otomatis.');">
                                            @csrf
                                            <button
                                                type="submit"
                                                class="px-3 py-1.5 rounded-lg bg-emerald-600 text-white font-semibold text-xs hover:bg-emerald-700 transition-colors shadow-2xs"
                                            >
                                                Terima (Setujui)
                                            </button>
                                        </form>

                                        <!-- Reject Trigger -->
                                        <button
                                            type="button"
                                            @click="activePaymentId = {{ $item->id }}; activePaymentNumber = '{{ $item->payment_number }}'; showRejectModal = true"
                                            class="px-3 py-1.5 rounded-lg border border-rose-200 bg-rose-50 text-rose-700 font-semibold text-xs hover:bg-rose-100 transition-colors"
                                        >
                                            Tolak
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-5 py-12 text-center text-zinc-400">
                                    Tidak ada antrean pembayaran yang menunggu verifikasi saat ini. Seluruh pengajuan telah diproses.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($pendingPayments->hasPages())
                <div class="px-5 py-3 border-t border-zinc-200">
                    {{ $pendingPayments->links() }}
                </div>
            @endif
        </div>

        <!-- MODAL: TOLAK PEMBAYARAN -->
        <div x-show="showRejectModal" class="fixed inset-0 z-50 overflow-y-auto bg-zinc-900/60 backdrop-blur-xs flex items-center justify-center p-4" style="display: none;">
            <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-xl border border-zinc-200" @click.outside="showRejectModal = false">
                <h3 class="text-base font-bold text-zinc-900">Tolak Konfirmasi Pembayaran</h3>
                <p class="text-xs text-zinc-600 mt-1">
                    Anda akan menolak pengajuan pembayaran <span class="font-mono font-bold" x-text="activePaymentNumber"></span>. Berikan alasan penolakan yang jelas agar orang tua dapat mengoreksi bukti setoran mereka.
                </p>

                <form :action="'/finance/payments/' + activePaymentId + '/reject'" method="POST" class="mt-4 space-y-4 text-xs">
                    @csrf
                    <div>
                        <label class="block font-semibold text-zinc-700 mb-1">Alasan Penolakan <span class="text-rose-500">*</span></label>
                        <textarea
                            name="rejection_reason"
                            rows="3"
                            required
                            placeholder="Contoh: Bukti transfer buram / mutasi rekening tidak ditemukan / nominal tidak sesuai..."
                            class="w-full px-3 py-2 rounded-lg border border-zinc-300 focus:ring-2 focus:ring-brand-500"
                        ></textarea>
                    </div>

                    <div class="flex justify-end gap-2 pt-2 border-t border-zinc-100">
                        <button type="button" @click="showRejectModal = false" class="px-4 py-2 rounded-lg border border-zinc-300 text-zinc-700 font-semibold hover:bg-zinc-50">Batal</button>
                        <button type="submit" class="px-4 py-2 rounded-lg bg-rose-600 text-white font-semibold hover:bg-rose-700">Kirim Penolakan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
