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
    $baseStyles = "inline-flex items-center justify-center font-semibold transition-all duration-150 rounded-xl focus:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 active:scale-[0.98] disabled:opacity-50 disabled:pointer-events-none select-none";

    $sizes = match ($size) {
        'sm' => "text-xs px-3 py-1.5 gap-1.5",
        'lg' => "text-base px-6 py-3.5 gap-2.5",
        default => "text-sm px-4 py-2.5 gap-2",
    };

    $variants = match ($variant) {
        'secondary' => "bg-slate-100 text-slate-800 hover:bg-slate-200 focus-visible:ring-slate-400 border border-slate-200",
        'outline' => "bg-white text-slate-700 hover:bg-slate-50 border border-slate-300 hover:border-slate-400 focus-visible:ring-brand-500 shadow-xs",
        'ghost' => "bg-transparent text-slate-600 hover:bg-slate-100 hover:text-slate-900 focus-visible:ring-slate-400",
        'danger' => "bg-rose-600 text-white hover:bg-rose-700 focus-visible:ring-rose-500 shadow-sm shadow-rose-600/20",
        'success' => "bg-emerald-600 text-white hover:bg-emerald-700 focus-visible:ring-emerald-500 shadow-sm shadow-emerald-600/20",
        'accent' => "bg-accent-500 text-slate-950 hover:bg-accent-400 focus-visible:ring-accent-500 shadow-sm shadow-accent-500/20",
        default => "bg-brand-600 text-white hover:bg-brand-700 focus-visible:ring-brand-500 shadow-sm shadow-brand-600/20",
    };

    $classes = "{$baseStyles} {$sizes} {$variants}";
@endphp

@if ($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>
        {{ $slot }}
    </a>
@else
    <button type="{{ $type }}" {{ $disabled ? 'disabled' : '' }} {{ $attributes->merge(['class' => $classes]) }}>
        @if ($loading)
            <svg class="animate-spin -ml-0.5 mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
            </svg>
        @endif
        {{ $slot }}
    </button>
@endif
