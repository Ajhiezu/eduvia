@props([
    'title' => null,
    'subtitle' => null,
    'hover' => false,
    'padding' => true,
])

@php
    $paddingClasses = match ($padding) {
        false, 'none' => '',
        'sm' => 'p-4',
        'lg' => 'p-6 sm:p-8',
        default => 'p-4 sm:p-6',
    };
@endphp

<div {{ $attributes->merge([
    'class' => 'app-card min-w-0 bg-white rounded-2xl border border-zinc-200 overflow-hidden transition-all duration-150 '
        . ($hover ? 'hover:border-brand-600/30 hover:shadow-sm ' : '')
]) }}>
    @if ($title || isset($action) || isset($header))
        <div class="px-4 sm:px-5 py-3.5 sm:py-4 border-b border-zinc-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3 sm:gap-4">
            @if (isset($header))
                {{ $header }}
            @else
                <div class="min-w-0">
                    @if ($title)
                        <h3 class="font-bold text-zinc-900 text-base leading-snug">{{ $title }}</h3>
                    @endif
                    @if ($subtitle)
                        <p class="text-[13px] text-zinc-500 mt-1">{{ $subtitle }}</p>
                    @endif
                </div>
                @if (isset($action))
                    <div class="flex items-center gap-2 flex-wrap shrink-0">
                        {{ $action }}
                    </div>
                @endif
            @endif
        </div>
    @endif

    <div class="{{ $paddingClasses }}">
        {{ $slot }}
    </div>

    @if (isset($footer))
        <div class="px-4 sm:px-5 py-3 sm:py-3.5 bg-zinc-50/80 border-t border-zinc-100 text-[13px] text-zinc-600 flex flex-col sm:flex-row sm:items-center justify-between gap-2.5">
            {{ $footer }}
        </div>
    @endif
</div>
