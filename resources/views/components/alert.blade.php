@props([
    'variant' => 'info', // info, success, warning, danger
    'title' => null,
    'dismissible' => false,
])

@php
    $variants = match ($variant) {
        'success' => [
            'box' => 'bg-emerald-50/80 border-emerald-200 text-emerald-900',
            'icon' => 'text-emerald-600',
            'svg' => '<path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>',
        ],
        'warning' => [
            'box' => 'bg-amber-50/80 border-amber-200 text-amber-900',
            'icon' => 'text-amber-600',
            'svg' => '<path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>',
        ],
        'danger' => [
            'box' => 'bg-rose-50/80 border-rose-200 text-rose-900',
            'icon' => 'text-rose-600',
            'svg' => '<path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>',
        ],
        default => [
            'box' => 'bg-brand-50/80 border-brand-200 text-brand-900',
            'icon' => 'text-brand-600',
            'svg' => '<path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>',
        ],
    };
@endphp

<div
    x-data="{ show: true }"
    x-show="show"
    x-transition:leave="transition ease-in duration-200"
    x-transition:leave-start="opacity-100 scale-100"
    x-transition:leave-end="opacity-0 scale-95"
    {{ $attributes->merge(['class' => "p-4 rounded-xl border flex items-start gap-3 text-sm {$variants['box']}"]) }}
    role="alert"
>
    <div class="shrink-0 mt-0.5 {{ $variants['icon'] }}">
        <svg class="w-5 h-5" viewBox="0 0 20 20" fill="currentColor">
            {!! $variants['svg'] !!}
        </svg>
    </div>

    <div class="flex-1">
        @if ($title)
            <h4 class="font-bold text-sm mb-0.5 leading-snug">{{ $title }}</h4>
        @endif
        <div class="text-xs sm:text-sm leading-relaxed opacity-95">
            {{ $slot }}
        </div>
    </div>

    @if ($dismissible)
        <button
            type="button"
            @click="show = false"
            class="shrink-0 p-1 rounded-lg opacity-60 hover:opacity-100 hover:bg-black/5 transition-opacity"
            aria-label="Tutup notifikasi"
        >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>
    @endif
</div>
