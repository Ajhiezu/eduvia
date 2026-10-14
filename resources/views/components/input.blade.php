@props([
    'label' => null,
    'name' => null,
    'id' => null,
    'type' => 'text',
    'value' => null,
    'placeholder' => null,
    'required' => false,
    'disabled' => false,
    'hint' => null,
    'error' => null,
])

@php
    $inputId = $id ?? ($name ? $name . '_' . uniqid() : 'input_' . uniqid());
    $hasError = $name ? $errors->has($name) : !empty($error);
    $errorMessage = $error ?? ($name ? $errors->first($name) : null);
@endphp

<div class="w-full flex flex-col gap-1.5 text-left">
    @if ($label)
        <label for="{{ $inputId }}" class="text-sm font-semibold text-slate-700 flex items-center justify-between">
            <span>
                {{ $label }}
                @if ($required)
                    <span class="text-rose-500 font-bold ml-0.5">*</span>
                @endif
            </span>
        </label>
    @endif

    <div class="relative rounded-xl shadow-xs">
        @if (isset($prefixIcon))
            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                {{ $prefixIcon }}
            </div>
        @endif

        <input
            type="{{ $type }}"
            id="{{ $inputId }}"
            name="{{ $name }}"
            value="{{ old($name, $value) }}"
            placeholder="{{ $placeholder }}"
            {{ $required ? 'required' : '' }}
            {{ $disabled ? 'disabled' : '' }}
            {{ $attributes->merge([
                'class' => 'w-full rounded-xl border bg-white text-slate-900 placeholder:text-slate-400 text-base sm:text-sm transition-colors py-2.5 '
                    . (isset($prefixIcon) ? 'pl-10 ' : 'pl-3.5 ')
                    . (isset($suffixIcon) ? 'pr-10 ' : 'pr-3.5 ')
                    . ($hasError
                        ? 'border-rose-300 focus:border-rose-500 focus:ring-2 focus:ring-rose-200'
                        : 'border-slate-300 hover:border-slate-400 focus:border-brand-500 focus:ring-2 focus:ring-brand-100')
                    . ($disabled ? ' bg-slate-50 text-slate-500 cursor-not-allowed' : '')
            ]) }}
        />

        @if (isset($suffixIcon))
            <div class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-400">
                {{ $suffixIcon }}
            </div>
        @endif
    </div>

    @if ($hasError)
        <p class="text-xs font-medium text-rose-600 flex items-center gap-1 mt-0.5" id="{{ $inputId }}-error">
            <svg class="w-3.5 h-3.5 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
            </svg>
            {{ $errorMessage }}
        </p>
    @elseif ($hint)
        <p class="text-xs text-slate-500 mt-0.5">
            {{ $hint }}
        </p>
    @endif
</div>
