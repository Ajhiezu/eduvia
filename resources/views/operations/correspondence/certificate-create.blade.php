<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <x-button href="{{ route('operations.correspondence.index') }}" variant="ghost" size="sm">
                &larr; Kembali
            </x-button>
            <div>
                <h1 class="text-xl font-bold tracking-tight text-zinc-900">Penerbitan Surat Keterangan Siswa Aktif</h1>
                <p class="text-xs text-zinc-500">Buat dokumen surat keterangan resmi kepala sekolah berformat standar A4 PDF.</p>
            </div>
        </div>
    </x-slot>

    <div class="py-6 max-w-2xl">
        <div class="bg-white rounded-2xl border border-zinc-200/80 shadow-xs p-6 sm:p-8">
            <form action="{{ route('operations.correspondence.certificate.pdf') }}" method="POST" target="_blank" class="space-y-5">
                @csrf

                <div>
                    <label class="block text-sm font-semibold text-zinc-700 mb-1.5">Pilih Peserta Didik Aktif <span class="text-rose-500">*</span></label>
                    <select name="student_id" required class="w-full text-sm rounded-xl border-zinc-300 focus:border-[#E11D2E] focus:ring-[#E11D2E]">
                        <option value="">-- Pilih Siswa --</option>
                        @foreach($students as $st)
                            <option value="{{ $st->id }}">
                                {{ $st->name }} (NISN: {{ $st->studentProfile->nisn ?? '-' }} &bull; NIS: {{ $st->studentProfile->nis ?? '-' }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <x-input label="Keperluan Penerbitan Surat" name="purpose" required placeholder="Contoh: Persyaratan Pengurusan Paspor / Tunjangan Gaji Orang Tua / Beasiswa" value="Persyaratan Tunjangan Gaji Orang Tua (KP4)" />
                    <p class="text-[11px] text-zinc-400 mt-1">Keperluan ini akan dicetak langsung pada paragraf resmi surat keterangan.</p>
                </div>

                <div class="p-4 rounded-xl bg-zinc-50 border border-zinc-200 text-xs text-zinc-600 space-y-1">
                    <p><strong>Informasi Penerbitan:</strong></p>
                    <p>&bull; Dokumen akan di-render dalam format resmi kertas <strong>A4 Portrait</strong>.</p>
                    <p>&bull; Nomor surat otomatis diterbitkan dan diarsipkan ke dalam Buku Agenda Surat Keluar.</p>
                    <p>&bull; Berkas dapat langsung dicetak atau diunduh oleh staf Tata Usaha.</p>
                </div>

                <div class="flex justify-end gap-3 pt-4 border-t border-zinc-100">
                    <x-button href="{{ route('operations.correspondence.index') }}" variant="secondary">Batal</x-button>
                    <x-button type="submit" variant="primary">
                        <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                        </svg>
                        Cetak Surat Resmi (PDF)
                    </x-button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
