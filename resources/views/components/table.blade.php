@props([
    'striped' => false,
    'hoverable' => true,
])

<div class="w-full overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-xs">
    <div class="overflow-x-auto">
        <table {{ $attributes->merge(['class' => 'w-full text-left text-sm text-slate-600']) }}>
            @if (isset($header))
                <thead class="bg-slate-50/80 border-b border-slate-200 text-xs font-bold uppercase tracking-wider text-slate-700 select-none">
                    <tr>
                        {{ $header }}
                    </tr>
                </thead>
            @endif

            <tbody class="divide-y divide-slate-100">
                @if (isset($empty) && empty(trim($slot)))
                    <tr>
                        <td colspan="100%" class="p-8 text-center text-slate-500">
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
        <div class="px-5 py-3.5 bg-slate-50 border-t border-slate-100 text-xs text-slate-500 flex items-center justify-between">
            {{ $footer }}
        </div>
    @endif
</div>
