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
    $errorMessage = $error ?? ($name ? $errors->first($name) : null);
    $hasError = $errorMessage !== null && $errorMessage !== '';
    $descriptionId = $hasError ? $selectId . '-error' : ($hint ? $selectId . '-hint' : null);
    $describedBy = trim(($attributes->get('aria-describedby') ?? '') . ' ' . ($descriptionId ?? ''));
@endphp

<div class="min-w-0 w-full text-left">
    @if ($label)
        <label for="{{ $selectId }}" class="block text-sm font-medium text-zinc-700 mb-1.5">
            <span>{{ $label }}</span>
            @if ($required)
                <span class="text-[#E11D2E] font-bold ml-0.5" aria-hidden="true">*</span>
            @endif
        </label>
    @endif

    <div class="relative w-full">
        <select
            id="{{ $selectId }}"
            name="{{ $name }}"
            aria-invalid="{{ $hasError ? 'true' : 'false' }}"
            @if ($describedBy)
                aria-describedby="{{ $describedBy }}"
            @endif
            {{ $required ? 'required' : '' }}
            {{ $disabled ? 'disabled' : '' }}
            {{ $attributes->except('aria-describedby')->merge([
                'class' => 'form-control w-full min-h-[44px] h-[44px] rounded-lg border text-base text-zinc-900 bg-white transition-all pl-3.5 pr-11 py-2.5 appearance-none cursor-pointer '
                    . 'border-zinc-200 hover:border-zinc-300 focus:border-brand-600 focus:ring-2 focus:ring-brand-600/15 '
                    . ($disabled ? ' bg-zinc-100 text-zinc-400 cursor-not-allowed' : '')
            ]) }}
        >
            {{ $slot }}
        </select>
    </div>

    @if ($hasError)
        <p class="form-error font-medium flex items-start gap-1.5" id="{{ $selectId }}-error">
            <svg class="w-4 h-4 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
            </svg>
            {{ $errorMessage }}
        </p>
    @elseif ($hint)
        <p id="{{ $selectId }}-hint" class="form-hint">{{ $hint }}</p>
    @endif
</div>

