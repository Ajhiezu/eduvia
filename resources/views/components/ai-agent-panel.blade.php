@php
    $navigationPages = [];
    $agentModules = app(\App\Services\AI\AiAgentToolCatalog::class)->forUser(auth()->user());
    foreach ($agentModules as $module) {
        if (isset($module['routes']['create'])) {
            $navigationPages[] = ['label' => 'Tambah '.$module['label'], 'url' => $module['routes']['create']];
        }
    }
@endphp

<div data-ai-agent x-data="aiAgentChat(@js(route('ai-agent.run', [], false)), @js(route('ai-agent.status', [], false)), @js(auth()->id()), @js(\App\Models\School::current()?->id), @js($navigationPages), @js($agentModules))" @open-ai-agent.window="openPanel($event.detail)">
    <dialog x-ref="dialog" class="ai-dialog" aria-labelledby="ai-dialog-title" @cancel.prevent="closePanel()" @click="if ($event.target === $refs.dialog) closePanel()">
        <div class="ai-dialog-shell">
            <header class="ai-dialog-header">
                <div><span class="ai-eyebrow">EDUVIA · GEMINI</span><h2 id="ai-dialog-title">AI Agent</h2></div>
                <div class="flex items-center gap-2"><button type="button" @click="newConversation()" :disabled="busy" class="ai-chat-reset" aria-label="Mulai percakapan baru">New Chat</button><button type="button" @click="closePanel()" class="ai-close" aria-label="Tutup AI Agent">×</button></div>
            </header>
            <div class="ai-dialog-body ai-chat-body" x-ref="conversation" role="log" aria-label="Percakapan dengan AI Agent" aria-live="polite">
                <div x-show="!messages.length" class="ai-chat-welcome">
                    <span class="ai-chat-mark" aria-hidden="true">↗</span>
                    <h3>Ada yang bisa<br>saya bantu?</h3>
                    <p>Saya bisa membuka halaman, mencari data, menambahkan, mengedit, dan menghapus melalui menu yang diizinkan untuk akun Anda.</p>
                    <button type="button" class="ai-chat-result-link mt-4 w-full" @click="prompt = 'Buka halaman Dashboard'; send()">Buka halaman Dashboard <span aria-hidden="true">↗</span></button>
                    <div class="ai-chat-examples"><button type="button" @click="prompt = 'Cari mata pelajaran Matematika yang tersedia di sekolah.'">Cari data sekolah <span aria-hidden="true">↗</span></button><button type="button" @click="prompt = 'Tambahkan soal ke bank soal. Tanyakan kelas, mata pelajaran, dan topiknya terlebih dahulu.'">Tambahkan soal ke bank <span aria-hidden="true">↗</span></button></div>
                </div>
                <template x-for="(message, index) in messages" :key="index">
                    <div class="ai-chat-message" :class="{ 'is-user': message.role === 'user', 'is-action': message.role === 'action', 'is-failed': message.role === 'action' && !message.ok }">
                        <span class="ai-chat-author" x-text="message.role === 'user' ? 'ANDA' : message.role === 'action' ? (message.ok ? 'TINDAKAN BERHASIL' : 'PERLU DIPERIKSA') : 'AI AGENT'"></span>
                        <p x-text="message.text"></p>
                    </div>
                </template>
                <div x-cloak x-show="progress" class="ai-chat-progress" role="status"><span class="ai-state-dot" :class="busy ? 'is-working' : ''"></span><p x-text="progress"></p></div>
                <p x-cloak x-show="conversationNotice" x-text="conversationNotice" role="status" class="ai-notice"></p>
                <p x-cloak x-show="storageError" x-text="storageError" role="status" class="ai-notice"></p>
                <div x-cloak x-show="error"><p x-text="error" role="alert" class="ai-error"></p><button x-show="!status" type="button" @click="checkStatus()" :disabled="checking" class="text-xs underline mt-2">Coba lagi</button></div>
                <a x-cloak x-show="lastUrl" :href="lastUrl" class="ai-chat-result-link"><span x-text="changed ? 'Lihat data yang diperbarui' : 'Buka hasil pencarian'"></span><span aria-hidden="true">↗</span></a>
            </div>
            <div class="ai-chat-composer">
                <p x-show="checking" role="status" class="text-xs text-zinc-500 mb-3">Memeriksa koneksi sekolah…</p>
                <div x-cloak x-show="status && (!status.enabled || !status.configured)" class="ai-notice mb-3"><p>Gemini belum siap digunakan.</p>@if (\App\Services\AI\AiAgentSettings::canManage(auth()->user()))<a class="underline font-semibold" href="{{ route('ai-agent.settings') }}">Atur koneksi sekolah</a>@else<p>Hubungi admin sekolah untuk mengaktifkan koneksi.</p>@endif</div>
                <form @submit.prevent="send()" data-ai-ignore>
                    <label for="ai-prompt" class="ai-label">Pesan Anda</label>
                    <textarea id="ai-prompt" x-ref="prompt" x-model="prompt" class="ai-input" rows="3" maxlength="3000" required :disabled="busy" @keydown="handleComposerKeydown($event)" placeholder="Contoh: cari mata pelajaran Matematika, lalu ubah deskripsinya menjadi…"></textarea>
                    <div class="flex items-center justify-between gap-3 mt-3"><span class="text-[10px] text-zinc-500">Enter: kirim · Shift + Enter: baris baru</span><x-button type="submit" size="sm" x-bind:disabled="busy || checking || !prompt.trim() || (status && (!status.enabled || !status.configured))">Kirim <span aria-hidden="true">↑</span></x-button></div>
                </form>
                <button x-cloak x-show="busy" type="button" @click="stop()" class="ai-chat-stop">Hentikan setelah langkah ini</button>
            </div>
            <footer class="ai-dialog-footer">Perintah tambah, edit, dan hapus mengubah data nyata sesuai izin akun Anda. Kuota mengikuti Google. Data yang diperlukan untuk perintah dikirim ke Gemini.</footer>
        </div>
    </dialog>
</div>
