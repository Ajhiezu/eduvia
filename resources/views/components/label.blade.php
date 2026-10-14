@props([
    'value' => null,
    'required' => false,
])

<label {{ $attributes->merge(['class' => 'block text-xs font-semibold text-zinc-700']) }}>
    {{ $value ?? $slot }}
    @if ($required)
        <span class="text-rose-500 font-bold ml-0.5">*</span>
    @endif
</label>
