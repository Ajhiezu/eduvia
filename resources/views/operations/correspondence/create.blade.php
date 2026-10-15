<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <x-button href="{{ route('operations.correspondence.index', ['type' => $type]) }}" variant="ghost" size="sm">
                &larr; Kembali
            </x-button>
            <div>
                <h1 class="text-xl font-bold tracking-tight text-zinc-900">
                    {{ $type === 'incoming' ? 'Catat Surat Masuk Baru' : 'Penerbitan Surat Keluar Resmi' }}
                </h1>
                <p class="text-xs text-zinc-500">
                    {{ $type === 'incoming' ? 'Catat surat dinas, edaran, atau undangan yang diterima sekolah.' : 'Terbitkan surat keluar dengan nomor agenda resmi otomatis.' }}
                </p>
            </div>
        </div>
    </x-slot>

    <div class="py-6 max-w-4xl">
        <div class="bg-white rounded-2xl border border-zinc-200/80 shadow-xs p-6 sm:p-8">
            <form action="{{ route('operations.correspondence.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                @csrf
                <input type="hidden" name="type" value="{{ $type }}">

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <x-input label="Nomor Surat Resmi" name="reference_number" :value="old('reference_number')" :placeholder="$type === 'outgoing' ? 'Kosongkan untuk penomoran otomatis sistem' : 'Nomor yang tertera pada surat fisik'" />
                        @if($type === 'outgoing')
                            <p class="text-[11px] text-zinc-400 mt-1">Kosongkan kolom ini jika ingin sistem meng-generate nomor surat otomatis sesuai format baku.</p>
                        @endif
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-zinc-700 mb-1.5">Klasifikasi / Kategori Surat <span class="text-rose-500">*</span></label>
                        <select name="category" required class="w-full text-sm rounded-xl border-zinc-300 focus:border-[#E11D2E] focus:ring-[#E11D2E]">
                            @foreach($categories as $cat)
                                <option value="{{ $cat }}" {{ old('category') === $cat ? 'selected' : '' }}>{{ $cat }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <x-input label="Tanggal Tertulis pada Surat" type="date" name="letter_date" :value="old('letter_date', date('Y-m-d'))" required />
                    </div>

                    <div>
                        <x-input label="{{ $type === 'incoming' ? 'Tanggal Surat Diterima di TU' : 'Tanggal Surat Dikirimkan' }}" type="date" name="received_or_sent_date" :value="old('received_or_sent_date', date('Y-m-d'))" required />
                    </div>

                    <div class="md:col-span-2">
                        <x-input label="{{ $type === 'incoming' ? 'Instansi / Pihak Pengirim' : 'Pihak / Instansi Tujuan Penerima' }}" name="sender_or_recipient" :value="old('sender_or_recipient')" required placeholder="Contoh: Dinas Pendidikan Provinsi DKI Jakarta / Orang Tua Siswa Kelas 9" />
                    </div>

                    <div class="md:col-span-2">
                        <x-input label="Perihal / Judul Surat" name="subject" :value="old('subject')" required placeholder="Contoh: Undangan Rapat Koordinasi Kurikulum Merdeka Jenjang Menengah" />
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-zinc-700 mb-1.5">Status Tindak Lanjut <span class="text-rose-500">*</span></label>
                        <select name="status" required class="w-full text-sm rounded-xl border-zinc-300 focus:border-[#E11D2E] focus:ring-[#E11D2E]">
                            @if($type === 'incoming')
                                <option value="received" {{ old('status') === 'received' ? 'selected' : '' }}>Diterima (Hanya Informasi)</option>
                                <option value="action_needed" {{ old('status') === 'action_needed' ? 'selected' : '' }}>Perlu Tindak Lanjut / Disposisi</option>
                                <option value="archived" {{ old('status') === 'archived' ? 'selected' : '' }}>Diarsipkan Selesai</option>
                            @else
                                <option value="draft" {{ old('status') === 'draft' ? 'selected' : '' }}>Draft (Konsep)</option>
                                <option value="sent" {{ old('status', 'sent') === 'sent' ? 'selected' : '' }}>Terkirim Resmi</option>
                                <option value="archived" {{ old('status') === 'archived' ? 'selected' : '' }}>Diarsipkan</option>
                            @endif
                        </select>
                    </div>

                    <div>
                        <x-input label="Batas Waktu Tindak Lanjut (Opsional)" type="date" name="action_deadline" :value="old('action_deadline')" />
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-sm font-semibold text-zinc-700 mb-1.5">Unggah Berkas Pindaian / Lampiran (PDF / Gambar, maks 10MB)</label>
                        <input type="file" name="attachment" accept=".pdf,image/*" class="w-full text-xs text-zinc-500 file:mr-3 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-zinc-100 hover:file:bg-zinc-200">
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-sm font-semibold text-zinc-700 mb-1.5">Catatan Disposisi / Ringkasan Isi Surat</label>
                        <textarea name="notes" rows="3" class="w-full text-sm rounded-xl border-zinc-300 focus:border-[#E11D2E] focus:ring-[#E11D2E]" placeholder="Instruksi kepala sekolah atau catatan penerima...">{{ old('notes') }}</textarea>
                    </div>
                </div>

                <div class="flex justify-end gap-3 pt-4 border-t border-zinc-100">
                    <x-button href="{{ route('operations.correspondence.index', ['type' => $type]) }}" variant="secondary">Batal</x-button>
                    <x-button type="submit" variant="primary">Simpan ke Buku Agenda</x-button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
