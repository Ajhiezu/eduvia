<x-layouts.app :attributes="$attributes">
    @isset($headerTitle)
        <x-slot:headerTitle>{{ $headerTitle }}</x-slot:headerTitle>
    @endisset
    @isset($header)
        <div class="app-page-header mb-6 pb-4 border-b border-zinc-200">
            {{ $header }}
        </div>
    @endisset
    {{ $slot }}
</x-layouts.app>
