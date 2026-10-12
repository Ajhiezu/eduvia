@props([
    'size' => 'md', // sm, md, lg
    'withText' => true,
    'subtext' => 'E-Learning Platform'
])

@php
    $dimensions = match ($size) {
        'sm' => ['icon' => 'w-7 h-7', 'text' => 'text-lg', 'sub' => 'text-[10px]'],
        'lg' => ['icon' => 'w-12 h-12', 'text' => 'text-2xl', 'sub' => 'text-xs'],
        default => ['icon' => 'w-9 h-9', 'text' => 'text-xl', 'sub' => 'text-[11px]'],
    };
@endphp

<div {{ $attributes->merge(['class' => 'inline-flex items-center gap-2.5 select-none']) }}>
    <!-- Brand Icon -->
    <div class="relative flex items-center justify-center {{ $dimensions['icon'] }} rounded-xl bg-gradient-to-br from-brand-600 to-brand-900 text-white shadow-md shadow-brand-500/20 ring-1 ring-white/20">
        <svg class="w-3/5 h-3/5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <!-- Academic Mortarboard / Book -->
            <path d="M22 10v6M2 10l10-5 10 5-10 5z"/>
            <path d="M6 12v5c3 3 9 3 12 0v-5"/>
        </svg>
        <span class="absolute -bottom-0.5 -right-0.5 w-2.5 h-2.5 rounded-full bg-accent-500 ring-2 ring-white"></span>
    </div>

    @if ($withText)
        <div class="flex flex-col text-left">
            <span class="font-extrabold tracking-tight {{ $dimensions['text'] }} text-slate-900 leading-tight">
                Edu<span class="text-brand-600">via</span>
            </span>
            @if ($subtext)
                <span class="{{ $dimensions['sub'] }} font-medium text-slate-500 tracking-wider uppercase leading-none">
                    {{ $subtext }}
                </span>
            @endif
        </div>
    @endif
</div>
