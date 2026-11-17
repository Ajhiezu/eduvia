@props([
    'variant' => 'primary', // primary, secondary, outline, ghost, danger, success, accent
    'size' => 'md', // sm, md, lg
    'type' => 'button',
    'href' => null,
    'disabled' => false,
    'loading' => false,
    'icon' => null,
])

@php
    $isUnavailable = $disabled || $loading;
    $baseStyles = "app-button inline-flex items-center justify-center font-semibold transition-all duration-150 rounded-lg focus:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 active:scale-[0.98] disabled:opacity-50 disabled:pointer-events-none aria-disabled:opacity-50 aria-disabled:pointer-events-none select-none touch-manipulation";

    $sizes = match ($size) {
        'sm' => "text-xs sm:text-sm px-3.5 py-2 min-h-[40px] gap-1.5",
        'lg' => "text-base px-5 py-3 min-h-[48px] gap-2.5",
        default => "text-sm sm:text-base px-4 py-2.5 min-h-[44px] gap-2",
    };

    $variants = match ($variant) {
        'secondary' => "bg-white text-zinc-800 hover:bg-zinc-50 focus-visible:ring-brand-600 border border-zinc-200",
        'outline' => "bg-white text-zinc-700 hover:bg-zinc-50 border border-zinc-200 hover:border-zinc-300 focus-visible:ring-brand-600",
        'ghost' => "bg-transparent text-zinc-600 hover:bg-zinc-100 hover:text-zinc-900 focus-visible:ring-zinc-400",
        'danger' => "bg-rose-600 text-white hover:bg-rose-700 focus-visible:ring-rose-500 shadow-xs",
        'success' => "bg-emerald-600 text-white hover:bg-emerald-700 focus-visible:ring-emerald-500 shadow-xs",
        'accent' => "bg-accent-500 text-zinc-950 hover:bg-accent-400 focus-visible:ring-accent-500 shadow-xs",
        default => "bg-brand-600 text-white hover:bg-brand-700 focus-visible:ring-brand-500 shadow-xs",
    };

    $classes = "{$baseStyles} {$sizes} {$variants}";
@endphp

@if ($href)
    <a @if (! $isUnavailable) href="{{ $href }}" @else aria-disabled="true" tabindex="-1" @endif @if ($loading) aria-busy="true" @endif {{ $attributes->merge(['class' => $classes]) }}>
        @if ($loading)
            <span class="app-button-spinner" aria-hidden="true"></span>
            <span class="sr-only">Sedang memproses.</span>
        @endif
        {{ $slot }}
    </a>
@else
    <button type="{{ $type }}" {{ $isUnavailable ? 'disabled' : '' }} @if ($loading) aria-busy="true" @endif {{ $attributes->merge(['class' => $classes]) }}>
        @if ($loading)
            <span class="app-button-spinner" aria-hidden="true"></span>
            <span class="sr-only">Sedang memproses.</span>
        @endif
        {{ $slot }}
    </button>
@endif
