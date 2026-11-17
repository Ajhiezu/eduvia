@props([
    'striped' => false,
    'hoverable' => true,
])

<div class="app-table min-w-0 w-full overflow-hidden rounded-2xl border border-zinc-200 bg-white shadow-none">
    <p class="border-b border-zinc-100 px-4 py-2 text-xs text-zinc-500 sm:hidden">Geser tabel ke samping untuk melihat semua kolom.</p>
    <div class="overflow-x-auto" data-scrollable tabindex="0" role="region" aria-label="Tabel data, dapat digeser secara horizontal">
        <table {{ $attributes->merge(['class' => 'w-full min-w-[600px] text-left text-sm text-zinc-700']) }}>
            @if (isset($header))
                <thead class="bg-white border-b border-zinc-200 text-xs font-medium text-zinc-500 select-none">
                    <tr>
                        {{ $header }}
                    </tr>
                </thead>
            @endif

            <tbody class="divide-y divide-zinc-100 {{ $hoverable ? 'app-table-hoverable' : '' }} {{ $striped ? 'app-table-striped' : '' }}">
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
