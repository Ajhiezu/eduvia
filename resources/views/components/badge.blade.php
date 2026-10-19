@props([
    'variant' => 'neutral', // primary, brand, accent, success, warning, danger, info, dark, neutral
    'size' => 'md', // xs, sm, md, lg
    'dot' => false,
    'pill' => false,
    'pulse' => false,
])

@php
    $sizes = match ($size) {
        'xs' => 'text-[10px] leading-3.5 px-1.5 py-0.5 gap-1 font-semibold tracking-wide',
        'sm' => 'text-[11px] leading-4 px-2 py-0.5 gap-1.5 font-medium tracking-tight',
        'lg' => 'text-sm leading-5 px-3 py-1.5 gap-2 font-medium',
        default => 'text-xs leading-4.5 px-2.5 py-0.5 sm:py-1 gap-1.5 font-medium tracking-tight',
    };

    $variants = match ($variant) {
        'primary', 'brand' => 'bg-rose-50/80 text-rose-900 border border-rose-200/80',
        'accent', 'warning' => 'bg-amber-50/90 text-amber-950 border border-amber-200/80',
        'success' => 'bg-emerald-50/80 text-emerald-950 border border-emerald-200/80',
        'danger' => 'bg-rose-50/80 text-rose-900 border border-rose-200/80',
        'info' => 'bg-sky-50/80 text-sky-950 border border-sky-200/80',
        'dark' => 'bg-zinc-900 text-zinc-100 border border-zinc-800',
        default => 'bg-zinc-100/90 text-zinc-800 border border-zinc-200/90',
    };

    $dotColors = match ($variant) {
        'primary', 'brand' => 'bg-[#E11D2E]',
        'accent', 'warning' => 'bg-amber-500',
        'success' => 'bg-emerald-600',
        'danger' => 'bg-rose-600',
        'info' => 'bg-sky-600',
        'dark' => 'bg-zinc-300',
        default => 'bg-zinc-400',
    };

    $shape = $pill ? 'rounded-full' : 'rounded-md';
@endphp

<span {{ $attributes->merge(['class' => "inline-flex items-center {$shape} select-none transition-colors duration-150 shadow-2xs font-sans {$sizes} {$variants}"]) }}>
    @if ($dot)
        <span class="relative flex h-1.5 w-1.5 shrink-0">
            @if ($pulse)
                <span class="animate-ping absolute inline-flex h-full w-full rounded-full opacity-60 {{ $dotColors }}"></span>
            @endif
            <span class="relative inline-flex rounded-full h-1.5 w-1.5 {{ $dotColors }}"></span>
        </span>
    @endif
    {{ $slot }}
</span>
