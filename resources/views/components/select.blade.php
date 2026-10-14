@props([
    'label' => null,
    'name' => null,
    'id' => null,
    'required' => false,
    'disabled' => false,
    'hint' => null,
    'error' => null,
])

@php
    $selectId = $id ?? ($name ? $name . '_' . uniqid() : 'select_' . uniqid());
    $hasError = $name ? $errors->has($name) : !empty($error);
    $errorMessage = $error ?? ($name ? $errors->first($name) : null);
@endphp

<div class="w-full flex flex-col gap-1.5 text-left">
    @if ($label)
        <label for="{{ $selectId }}" class="text-sm font-semibold text-slate-700 flex items-center justify-between">
            <span>
                {{ $label }}
                @if ($required)
                    <span class="text-rose-500 font-bold ml-0.5">*</span>
                @endif
            </span>
        </label>
    @endif

    <div class="relative rounded-xl shadow-xs">
        <select
            id="{{ $selectId }}"
            name="{{ $name }}"
            {{ $required ? 'required' : '' }}
            {{ $disabled ? 'disabled' : '' }}
            {{ $attributes->merge([
                'class' => 'w-full rounded-xl border bg-white text-slate-900 text-base sm:text-sm transition-colors py-2.5 px-3.5 pr-10 appearance-none '
                    . ($hasError
                        ? 'border-rose-300 focus:border-rose-500 focus:ring-2 focus:ring-rose-200'
                        : 'border-slate-300 hover:border-slate-400 focus:border-brand-500 focus:ring-2 focus:ring-brand-100')
                    . ($disabled ? ' bg-slate-50 text-slate-500 cursor-not-allowed' : '')
            ]) }}
        >
            {{ $slot }}
        </select>

        <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-slate-400">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
            </svg>
        </div>
    </div>

    @if ($hasError)
        <p class="text-xs font-medium text-rose-600 flex items-center gap-1 mt-0.5">
            <svg class="w-3.5 h-3.5 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
            </svg>
            {{ $errorMessage }}
        </p>
    @elseif ($hint)
        <p class="text-xs text-slate-500 mt-0.5">{{ $hint }}</p>
    @endif
</div>
