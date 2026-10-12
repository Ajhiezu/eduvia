@props([
    'variant' => 'neutral', // primary, accent, success, warning, danger, neutral
    'size' => 'md', // sm, md
    'dot' => false,
])

@php
    $sizes = match ($size) {
        'sm' => 'text-[11px] px-2 py-0.5 gap-1 font-medium',
        default => 'text-xs px-2.5 py-1 gap-1.5 font-semibold',
    };

    $variants = match ($variant) {
        'primary' => 'bg-brand-50 text-brand-700 ring-1 ring-inset ring-brand-600/20',
        'accent' => 'bg-amber-50 text-amber-800 ring-1 ring-inset ring-amber-600/20',
        'success' => 'bg-emerald-50 text-emerald-700 ring-1 ring-inset ring-emerald-600/20',
        'warning' => 'bg-amber-50 text-amber-700 ring-1 ring-inset ring-amber-600/20',
        'danger' => 'bg-rose-50 text-rose-700 ring-1 ring-inset ring-rose-600/20',
        default => 'bg-slate-100 text-slate-700 ring-1 ring-inset ring-slate-500/20',
    };

    $dotColors = match ($variant) {
        'primary' => 'bg-brand-600',
        'accent' => 'bg-amber-500',
        'success' => 'bg-emerald-500',
        'warning' => 'bg-amber-500',
        'danger' => 'bg-rose-500',
        default => 'bg-slate-400',
    };
@endphp

<span {{ $attributes->merge(['class' => "inline-flex items-center rounded-full select-none {$sizes} {$variants}"]) }}>
    @if ($dot)
        <span class="w-1.5 h-1.5 rounded-full {{ $dotColors }}"></span>
    @endif
    {{ $slot }}
</span>
