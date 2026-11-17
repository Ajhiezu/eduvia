<dialog id="application-confirm-dialog" class="app-confirm-dialog" aria-labelledby="application-confirm-title" aria-describedby="application-confirm-message">
    <div class="p-5 sm:p-6">
        <div class="mb-4 flex h-11 w-11 items-center justify-center rounded-xl bg-brand-50 text-brand-600" aria-hidden="true">
            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 9v4m0 4h.01M10.3 3.9 2.2 18a2 2 0 001.7 3h16.2a2 2 0 001.7-3L13.7 3.9a2 2 0 00-3.4 0Z"/></svg>
        </div>
        <h2 id="application-confirm-title" class="text-lg font-semibold text-zinc-900">Konfirmasi tindakan</h2>
        <p id="application-confirm-message" data-confirm-message class="mt-2 text-sm leading-relaxed text-zinc-600 break-words"></p>
    </div>
    <div class="flex flex-wrap justify-end gap-3 border-t border-zinc-100 bg-zinc-50 px-5 py-4 sm:px-6">
        <x-button variant="secondary" data-confirm-cancel autofocus>Batal</x-button>
        <x-button variant="primary" data-confirm-accept>Ya, lanjutkan</x-button>
    </div>
</dialog>
