@props([
    'value' => null,
    'required' => false,
])

<label {{ $attributes->merge(['class' => 'block text-sm font-medium text-zinc-700 mb-1.5']) }}>
    {{ $value ?? $slot }}
    @if ($required)
        <span class="text-[#E11D2E] font-bold ml-0.5" aria-hidden="true">*</span>
    @endif
</label>
