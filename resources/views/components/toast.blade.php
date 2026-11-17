<div
    x-data="toastNotifications({
        success: {{ Illuminate\Support\Js::from(session('success') ?? session('status')) }},
        danger: {{ Illuminate\Support\Js::from(session('error')) }},
        warning: {{ Illuminate\Support\Js::from(session('warning')) }}
    })"
    x-on:notify.window="add($event.detail.message, $event.detail.type)"
    class="fixed bottom-4 left-4 right-4 z-[60] flex flex-col gap-2.5 sm:left-auto sm:w-96 max-w-[calc(100vw-2rem)] pointer-events-none"
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
            @mouseenter="pause(toast.id)"
            @mouseleave="if (!$el.contains(document.activeElement)) resume(toast)"
            @focusin="pause(toast.id)"
            @focusout="if (!$el.contains($event.relatedTarget) && !$el.matches(':hover')) resume(toast)"
            :role="toast.type === 'danger' ? 'alert' : 'status'"
            aria-atomic="true"
            class="pointer-events-auto min-w-0 rounded-xl p-3.5 shadow-lg border flex items-start justify-between gap-3 text-sm font-medium bg-white"
            :class="{
                'border-emerald-200 text-emerald-900 shadow-emerald-500/10': toast.type === 'success',
                'border-rose-200 text-rose-900 shadow-rose-500/10': toast.type === 'danger',
                'border-amber-200 text-amber-900 shadow-amber-500/10': toast.type === 'warning',
                'border-blue-200 text-blue-900 shadow-blue-500/10': toast.type === 'info' || !toast.type
            }"
        >
            <div class="min-w-0 flex items-start gap-2.5">
                <span
                    class="mt-1.5 w-2 h-2 rounded-full shrink-0"
                    aria-hidden="true"
                    :class="{
                        'bg-emerald-500': toast.type === 'success',
                        'bg-rose-500': toast.type === 'danger',
                        'bg-amber-500': toast.type === 'warning',
                        'bg-blue-600': toast.type === 'info' || !toast.type
                    }"
                ></span>
                <span x-text="toast.message" class="text-sm leading-relaxed break-words [overflow-wrap:anywhere]"></span>
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
