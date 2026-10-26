@props([
    'striped' => false,
    'hoverable' => true,
])

<div class="w-full overflow-hidden rounded-lg border border-zinc-200 bg-white shadow-none">
    <div class="overflow-x-auto" data-scrollable>
        <table {{ $attributes->merge(['class' => 'w-full min-w-[600px] text-left text-sm text-zinc-700']) }}>
            @if (isset($header))
                <thead class="bg-zinc-50 border-b border-zinc-200 text-xs font-semibold uppercase tracking-wider text-zinc-500 select-none">
                    <tr>
                        {{ $header }}
                    </tr>
                </thead>
            @endif

            <tbody class="divide-y divide-zinc-100">
                @if (isset($empty) && empty(trim($slot)))
                    <tr>
                        <td colspan="100%" class="p-8 text-center text-zinc-500">
                            {{ $empty }}
                        </td>
                    </tr>
                @else
                    {{ $slot }}
                @endif
            </tbody>
        </table>
    </div>

    @if (isset($footer))
        <div class="border-t border-zinc-200 bg-white">
            {{ $footer }}
        </div>
    @endif
</div>
