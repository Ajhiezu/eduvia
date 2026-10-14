@props([
    'variant' => 'horizontal', // horizontal, vertical, icon, app-icon, app-icon-dark
    'size' => 'md', // xs, sm, md, lg, xl
    'alt' => 'Eduvia — Learn · Grow · Build Your Future',
    'subtext' => null, // backward compatibility
    'withText' => true, // backward compatibility
])

@php
    $src = match ($variant) {
        'vertical' => asset('images/brand/eduvia-logo-vertical-transparent.png'),
        'icon' => asset('images/brand/eduvia-icon-mark-transparent.png'),
        'app-icon' => asset('images/brand/eduvia-app-icon-light.png'),
        'app-icon-dark' => asset('images/brand/eduvia-app-icon-dark.png'),
        default => asset('images/brand/eduvia-logo-horizontal-transparent.png'),
    };

    $sizeClasses = match ($variant) {
        'vertical' => match ($size) {
            'xs' => 'h-10 w-auto',
            'sm' => 'h-14 w-auto',
            'lg' => 'h-24 w-auto',
            'xl' => 'h-32 w-auto',
            default => 'h-20 w-auto',
        },
        'app-icon', 'app-icon-dark' => match ($size) {
            'xs' => 'w-6 h-6 rounded-lg shadow-2xs',
            'sm' => 'w-8 h-8 rounded-xl shadow-xs',
            'lg' => 'w-14 h-14 rounded-2xl shadow-md',
            'xl' => 'w-20 h-20 rounded-3xl shadow-lg',
            default => 'w-10 h-10 rounded-xl shadow-xs',
        },
        'icon' => match ($size) {
            'xs' => 'h-6 w-auto',
            'sm' => 'h-8 w-auto',
            'lg' => 'h-12 w-auto',
            'xl' => 'h-16 w-auto',
            default => 'h-10 w-auto',
        },
        default => match ($size) {
            'xs' => 'h-6 w-auto',
            'sm' => 'h-8 w-auto',
            'lg' => 'h-12 w-auto',
            'xl' => 'h-16 w-auto',
            default => 'h-9 sm:h-10 w-auto',
        },
    };
@endphp

<div {{ $attributes->merge(['class' => 'inline-flex items-center shrink-0 select-none']) }}>
    <img
        src="{{ $src }}"
        alt="{{ $alt }}"
        class="{{ $sizeClasses }} max-h-full object-contain block shrink-0"
        loading="eager"
        decoding="async"
    />
</div>
