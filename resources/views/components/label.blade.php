@props([
    'value' => null,
    'required' => false,
])

<label {{ $attributes->merge(['class' => 'block text-xs sm:text-sm font-bold text-zinc-700 mb-2']) }}>
    {{ $value ?? $slot }}
    @if ($required)
        <span class="text-[#E11D2E] font-bold ml-0.5">*</span>
    @endif
</label>
