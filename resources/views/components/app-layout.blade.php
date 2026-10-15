<x-layouts.app :attributes="$attributes">
    @isset($header)
        <div class="mb-6 pb-4 border-b border-zinc-200">
            {{ $header }}
        </div>
    @endisset
    {{ $slot }}
</x-layouts.app>
