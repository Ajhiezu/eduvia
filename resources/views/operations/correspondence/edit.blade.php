<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <x-button href="{{ route('operations.correspondence.show', $letter) }}" variant="ghost" size="sm">
                &larr; Kembali
            </x-button>
            <div>
                <h1 class="text-xl font-bold tracking-tight text-zinc-900">Edit Arsip Surat</h1>
                <p class="text-xs text-zinc-500">Perbarui data nomor, tanggal, status, atau lampiran dokumen.</p>
            </div>
        </div>
    </x-slot>

    <div class="py-6 max-w-4xl">
        <div class="bg-white rounded-2xl border border-zinc-200/80 shadow-xs p-6 sm:p-8">
            <form action="{{ route('operations.correspondence.update', $letter) }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <x-input label="Nomor Surat Resmi" name="reference_number" :value="old('reference_number', $letter->reference_number)" required />
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-zinc-700 mb-1.5">Klasifikasi / Kategori Surat <span class="text-rose-500">*</span></label>
                        <select name="category" required class="w-full text-sm rounded-xl border-zinc-300 focus:border-[#E11D2E] focus:ring-[#E11D2E]">
                            @foreach($categories as $cat)
                                <option value="{{ $cat }}" {{ old('category', $letter->category) === $cat ? 'selected' : '' }}>{{ $cat }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <x-input label="Tanggal Tertulis pada Surat" type="date" name="letter_date" :value="old('letter_date', $letter->letter_date->format('Y-m-d'))" required />
                    </div>

                    <div>
                        <x-input label="Tanggal Diterima / Dikirimkan" type="date" name="received_or_sent_date" :value="old('received_or_sent_date', $letter->received_or_sent_date->format('Y-m-d'))" required />
                    </div>

                    <div class="md:col-span-2">
                        <x-input label="Pihak Pengirim / Penerima" name="sender_or_recipient" :value="old('sender_or_recipient', $letter->sender_or_recipient)" required />
                    </div>

                    <div class="md:col-span-2">
                        <x-input label="Perihal / Judul Surat" name="subject" :value="old('subject', $letter->subject)" required />
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-zinc-700 mb-1.5">Status Tindak Lanjut <span class="text-rose-500">*</span></label>
                        <select name="status" required class="w-full text-sm rounded-xl border-zinc-300 focus:border-[#E11D2E] focus:ring-[#E11D2E]">
                            <option value="draft" {{ old('status', $letter->status) === 'draft' ? 'selected' : '' }}>Draft</option>
                            <option value="received" {{ old('status', $letter->status) === 'received' ? 'selected' : '' }}>Diterima</option>
                            <option value="sent" {{ old('status', $letter->status) === 'sent' ? 'selected' : '' }}>Terkirim</option>
                            <option value="action_needed" {{ old('status', $letter->status) === 'action_needed' ? 'selected' : '' }}>Perlu Tindak Lanjut</option>
                            <option value="archived" {{ old('status', $letter->status) === 'archived' ? 'selected' : '' }}>Diarsipkan</option>
                        </select>
                    </div>

                    <div>
                        <x-input label="Batas Waktu Tindak Lanjut" type="date" name="action_deadline" :value="old('action_deadline', $letter->action_deadline?->format('Y-m-d'))" />
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-sm font-semibold text-zinc-700 mb-1.5">Ganti Berkas Lampiran (Opsional, PDF/Gambar)</label>
                        <input type="file" name="attachment" accept=".pdf,image/*" class="w-full text-xs text-zinc-500 file:mr-3 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-zinc-100 hover:file:bg-zinc-200">
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-sm font-semibold text-zinc-700 mb-1.5">Catatan Disposisi / Ringkasan Isi</label>
                        <textarea name="notes" rows="3" class="w-full text-sm rounded-xl border-zinc-300 focus:border-[#E11D2E] focus:ring-[#E11D2E]">{{ old('notes', $letter->notes) }}</textarea>
                    </div>
                </div>

                <div class="flex justify-end gap-3 pt-4 border-t border-zinc-100">
                    <x-button href="{{ route('operations.correspondence.show', $letter) }}" variant="secondary">Batal</x-button>
                    <x-button type="submit" variant="primary">Simpan Perubahan</x-button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
