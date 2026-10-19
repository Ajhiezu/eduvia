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

<div class="w-full flex flex-col gap-2 text-left">
    @if ($label)
        <label for="{{ $inputId }}" class="text-xs sm:text-sm font-bold text-zinc-700 flex items-center justify-between">
            <span>
                {{ $label }}
                @if ($required)
                    <span class="text-[#E11D2E] font-bold ml-0.5">*</span>
                @endif
            </span>
        </label>
    @endif

    <div class="relative rounded-2xl shadow-2xs group">
        @if (isset($prefixIcon))
            <div class="absolute inset-y-0 left-0 pl-3.5 sm:pl-4 flex items-center pointer-events-none text-zinc-400 group-focus-within:text-[#E11D2E] transition-colors z-10">
                {{ $prefixIcon }}
            </div>
        @endif

        <input
            type="{{ $type }}"
            id="{{ $inputId }}"
            name="{{ $name }}"
            value="{{ old($name, $value) }}"
            placeholder="{{ $placeholder }}"
            @if (isset($prefixIcon)) data-has-prefix="true" @endif
            @if (isset($suffixIcon)) data-has-suffix="true" @endif
            {{ $required ? 'required' : '' }}
            {{ $disabled ? 'disabled' : '' }}
            @if (isset($prefixIcon) && isset($suffixIcon))
                style="padding-left: 2.75rem !important; padding-right: 2.75rem !important;"
            @elseif (isset($prefixIcon))
                style="padding-left: 2.75rem !important;"
            @elseif (isset($suffixIcon))
                style="padding-right: 2.75rem !important;"
            @endif
            {{ $attributes->merge([
                'class' => 'w-full rounded-2xl border bg-zinc-50/70 hover:bg-white focus:bg-white text-zinc-900 placeholder:text-zinc-400 text-sm sm:text-base transition-all py-3 sm:py-3.5 '
                    . (isset($prefixIcon) ? 'has-prefix-icon pl-11 !pl-11 ' : 'px-4 ')
                    . (isset($suffixIcon) ? 'has-suffix-icon pr-11 !pr-11 ' : 'pr-4 ')
                    . ($hasError
                        ? 'border-rose-300 focus:border-rose-500 focus:ring-2 focus:ring-rose-200'
                        : 'border-zinc-200 hover:border-zinc-300 focus:border-[#E11D2E] focus:ring-2 focus:ring-[#E11D2E]/20')
                    . ($disabled ? ' bg-zinc-100 text-zinc-400 cursor-not-allowed' : '')
            ]) }}
        />

        @if (isset($suffixIcon))
            <div class="absolute inset-y-0 right-0 pr-3.5 sm:pr-4 flex items-center text-zinc-400 group-focus-within:text-zinc-600 transition-colors z-10">
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
