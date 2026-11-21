<x-app-layout>
    <x-slot:headerTitle>AI Agent</x-slot:headerTitle>
    <x-slot name="header">
        <div class="app-page-header flex flex-wrap items-end justify-between gap-4">
            <div><p class="text-xs font-semibold uppercase tracking-widest text-zinc-500 mb-2">Ruang kerja cerdas</p><h1 class="text-3xl font-bold tracking-tight text-zinc-900">AI Agent</h1><p class="mt-2 text-sm text-zinc-500">Lebih sedikit pekerjaan berulang. Lebih banyak waktu untuk pendidikan.</p></div>
            @if (\App\Services\AI\AiAgentSettings::canManage(auth()->user()))
                <x-button :href="route('ai-agent.settings')" variant="secondary" size="sm">Pengaturan Gemini</x-button>
            @endif
        </div>
    </x-slot>
    <div class="ai-workspace space-y-6">
        <section class="ai-intro">
            <div class="ai-intro-copy">
                <span class="ai-eyebrow">ASISTEN SEKOLAH · GEMINI</span>
                <h2>Cukup perintahkan.<br><span>Pekerjaan langsung dijalankan.</span></h2>
                <p>Cari data, tambahkan soal, perbarui informasi, atau hapus data lewat satu percakapan. Agen bekerja lintas menu melalui fitur aplikasi sesuai hak akses Anda.</p>
                <x-button @click="$dispatch('open-ai-agent', {})">Mulai percakapan <span aria-hidden="true">↗</span></x-button>
                <p class="ai-service-state"><span class="ai-state-dot {{ $status['enabled'] && $status['configured'] ? 'is-ready' : '' }}"></span>{{ $status['enabled'] && $status['configured'] ? 'Konfigurasi tersedia · koneksi diperiksa saat digunakan' : 'Menunggu pengaturan Gemini oleh admin sekolah' }}</p>
            </div>
            <div class="ai-example" aria-label="Contoh penggunaan">
                <span class="ai-eyebrow">CONTOH ALUR KERJA</span>
                <div class="ai-example-request">Tambahkan satu soal pecahan kelas 5 ke bank soal, lengkap dengan pilihan dan pembahasannya.</div>
                <div class="ai-example-answer"><span class="ai-state-dot is-ready"></span><strong>Soal berhasil ditambahkan</strong><p>Agen mengisi dan menyimpan melalui formulir bank soal. Hasil terkonfirmasi oleh aplikasi.</p></div>
                <div class="ai-example-steps"><span>01 Perintahkan</span><span>02 Dikerjakan</span><span>03 Selesai</span></div>
            </div>
        </section>
        <section aria-labelledby="ai-start-title">
            <div class="flex items-center justify-between gap-3 mb-4"><h2 id="ai-start-title" class="font-bold text-lg text-zinc-900">Apa yang ingin Anda kerjakan?</h2><span class="text-xs text-zinc-500">Pilih untuk memulai</span></div>
            <div class="grid md:grid-cols-3 gap-4">
                <button type="button" class="ai-starter" @click="$dispatch('open-ai-agent', { prompt: 'Tambahkan soal ke bank soal. Tanyakan mata pelajaran, kelas, topik, dan jumlah soal terlebih dahulu.' })"><span class="ai-starter-number">01 / PEMBELAJARAN</span><strong>Tambahkan soal ke bank <span aria-hidden="true">↗</span></strong><span>Agen menyusun dan menyimpan soal melalui bank soal sekolah.</span></button>
                <button type="button" class="ai-starter" @click="$dispatch('open-ai-agent', { prompt: 'Buat dan simpan pengumuman sekolah. Tanyakan judul, isi, tujuan, serta status terbit terlebih dahulu.' })"><span class="ai-starter-number">02 / KOMUNIKASI</span><strong>Kelola pengumuman <span aria-hidden="true">↗</span></strong><span>Buat atau perbarui pengumuman sesuai instruksi dan hak akses.</span></button>
                <button type="button" class="ai-starter" @click="$dispatch('open-ai-agent', { prompt: 'Saya ingin mencari dan mengubah data sekolah. Jelaskan menu yang tersedia untuk akun saya.' })"><span class="ai-starter-number">03 / ADMINISTRASI</span><strong>Cari, edit, dan hapus data <span aria-hidden="true">↗</span></strong><span>Sebutkan menu dan targetnya. Agen mencari data sebelum bertindak.</span></button>
            </div>
        </section>
        <div class="ai-footnote"><strong>Perintah Anda menjalankan tindakan nyata.</strong> Agen memakai validasi dan hak akses aplikasi. Jika target atau isian belum jelas, agen bertanya di chat. Kuota mengikuti ketersediaan Google.</div>
    </div>
</x-app-layout>
