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

<div class="w-full text-left">
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
            {{ $required ? 'required' : '' }}
            {{ $disabled ? 'disabled' : '' }}
            {{ $attributes->merge([
                'class' => 'w-full min-h-[44px] h-[44px] rounded-lg border text-base text-zinc-900 bg-white transition-all pl-3.5 pr-11 py-2.5 appearance-none cursor-pointer '
                    . ($hasError
                        ? 'border-rose-400 focus:border-rose-500 focus:ring-2 focus:ring-rose-200'
                        : 'border-zinc-200 hover:border-zinc-300 focus:border-[#E11D2E] focus:ring-2 focus:ring-[#E11D2E]/20')
                    . ($disabled ? ' bg-zinc-100 text-zinc-400 cursor-not-allowed' : '')
            ]) }}
        >
            {{ $slot }}
        </select>

        <div class="absolute inset-y-0 right-0 w-11 flex items-center justify-center pointer-events-none text-zinc-400">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
            </svg>
        </div>
    </div>

    @if ($hasError)
        <p class="text-[13px] font-medium text-rose-600 flex items-center gap-1.5 mt-1.5" id="{{ $selectId }}-error">
            <svg class="w-4 h-4 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
            </svg>
            {{ $errorMessage }}
        </p>
    @elseif ($hint)
        <p class="text-[13px] text-zinc-500 mt-1.5">{{ $hint }}</p>
    @endif
</div>

