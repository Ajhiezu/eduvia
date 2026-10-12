@props([
    'title' => null,
    'subtitle' => null,
    'hover' => false,
    'padding' => true,
])

<div {{ $attributes->merge([
    'class' => 'bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden transition-all duration-200 '
        . ($hover ? 'hover:shadow-md hover:border-brand-200 hover:-translate-y-0.5 ' : '')
]) }}>
    @if ($title || isset($action) || isset($header))
        <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between gap-4">
            @if (isset($header))
                {{ $header }}
            @else
                <div>
                    @if ($title)
                        <h3 class="font-bold text-slate-900 text-base leading-snug">{{ $title }}</h3>
                    @endif
                    @if ($subtitle)
                        <p class="text-xs text-slate-500 mt-0.5">{{ $subtitle }}</p>
                    @endif
                </div>
                @if (isset($action))
                    <div class="flex items-center gap-2">
                        {{ $action }}
                    </div>
                @endif
            @endif
        </div>
    @endif

    <div class="{{ $padding ? 'p-5 sm:p-6' : '' }}">
        {{ $slot }}
    </div>

    @if (isset($footer))
        <div class="px-5 py-3.5 bg-slate-50/70 border-t border-slate-100 text-xs text-slate-600 flex items-center justify-between">
            {{ $footer }}
        </div>
    @endif
</div>
