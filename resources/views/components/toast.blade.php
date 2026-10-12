<div
    x-data="{
        toasts: [],
        add(message, type = 'info') {
            const id = Date.now() + Math.random();
            this.toasts.push({ id, message, type });
            setTimeout(() => this.remove(id), 5000);
        },
        remove(id) {
            this.toasts = this.toasts.filter(t => t.id !== id);
        }
    }"
    x-init="
        @if (session('success') || session('status'))
            add('{{ session('success') ?? session('status') }}', 'success');
        @endif
        @if (session('error'))
            add('{{ session('error') }}', 'danger');
        @endif
        @if (session('warning'))
            add('{{ session('warning') }}', 'warning');
        @endif
    "
    x-on:notify.window="add($event.detail.message, $event.detail.type)"
    class="fixed bottom-5 right-5 z-50 flex flex-col gap-2.5 max-w-sm w-full pointer-events-none px-4 sm:px-0"
    role="region"
    aria-label="Notifikasi"
>
    <template x-for="toast in toasts" :key="toast.id">
        <div
            x-show="true"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 translate-y-3 scale-95"
            x-transition:enter-end="opacity-100 translate-y-0 scale-100"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-90"
            class="pointer-events-auto rounded-xl p-3.5 shadow-xl border flex items-center justify-between gap-3 text-sm font-medium bg-white"
            :class="{
                'border-emerald-200 text-emerald-900 shadow-emerald-500/10': toast.type === 'success',
                'border-rose-200 text-rose-900 shadow-rose-500/10': toast.type === 'danger',
                'border-amber-200 text-amber-900 shadow-amber-500/10': toast.type === 'warning',
                'border-brand-200 text-brand-900 shadow-brand-500/10': toast.type === 'info' || !toast.type
            }"
        >
            <div class="flex items-center gap-2.5">
                <span
                    class="w-2 h-2 rounded-full shrink-0"
                    :class="{
                        'bg-emerald-500': toast.type === 'success',
                        'bg-rose-500': toast.type === 'danger',
                        'bg-amber-500': toast.type === 'warning',
                        'bg-brand-600': toast.type === 'info' || !toast.type
                    }"
                ></span>
                <span x-text="toast.message" class="text-xs sm:text-sm"></span>
            </div>

            <button
                type="button"
                @click="remove(toast.id)"
                class="text-slate-400 hover:text-slate-600 p-1 rounded-lg hover:bg-slate-100 transition-colors shrink-0"
                aria-label="Tutup notifikasi"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
    </template>
</div>
