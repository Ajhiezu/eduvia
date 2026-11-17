@props(['children', 'activeChild' => null, 'returnUrl'])

@if ($children->count() > 1)
    <nav x-data="childSwitcher" data-child-menu="{{ $returnUrl }}" aria-label="Pilih anak" class="flex flex-wrap items-center gap-1.5 rounded-xl bg-zinc-100 p-1">
        @foreach ($children as $child)
            <a
                href="{{ route('parent.switch-child', ['child' => $child->id]) }}"
                data-child-id="{{ $child->id }}"
                @click.prevent="selectChild($el.href, $el.dataset.childId, $root.dataset.childMenu)"
                :aria-busy="(busyChildId === '{{ $child->id }}').toString()"
                :aria-disabled="(busyChildId !== null).toString()"
                @if ($activeChild?->id === $child->id) aria-current="true" @endif
                class="inline-flex max-w-full items-center gap-2 rounded-lg px-3 py-2 text-xs font-semibold transition-colors {{ $activeChild?->id === $child->id ? 'bg-white text-brand-600 shadow-xs' : 'text-zinc-600 hover:bg-white/60 hover:text-zinc-900' }}"
            >
                <span x-show="busyChildId === '{{ $child->id }}'" x-cloak class="app-button-spinner" aria-hidden="true"></span>
                <span class="break-words">{{ $child->name }}</span>
            </a>
        @endforeach
        <p x-show="errorMessage" x-text="errorMessage" x-cloak role="alert" class="w-full px-3 py-2 text-xs text-red-600"></p>
    </nav>
@endif
