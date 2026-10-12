@props([
    'type' => 'line', // line, circle, card, table
    'lines' => 3,
])

<div {{ $attributes->merge(['class' => 'animate-pulse']) }}>
    @if ($type === 'circle')
        <div class="w-10 h-10 rounded-full bg-slate-200"></div>
    @elseif ($type === 'card')
        <div class="rounded-2xl border border-slate-200 bg-white p-5 space-y-4">
            <div class="h-4 bg-slate-200 rounded w-1/3"></div>
            <div class="space-y-2">
                <div class="h-3 bg-slate-200 rounded"></div>
                <div class="h-3 bg-slate-200 rounded w-5/6"></div>
            </div>
            <div class="h-8 bg-slate-200 rounded-xl w-1/4"></div>
        </div>
    @elseif ($type === 'table')
        <div class="space-y-3">
            <div class="h-10 bg-slate-100 rounded-xl"></div>
            @for ($i = 0; $i < $lines; $i++)
                <div class="h-12 bg-slate-50 border border-slate-100 rounded-xl"></div>
            @endfor
        </div>
    @else
        <div class="space-y-2.5">
            @for ($i = 0; $i < $lines; $i++)
                <div class="h-3.5 bg-slate-200 rounded {{ $i === $lines - 1 ? 'w-2/3' : 'w-full' }}"></div>
            @endfor
        </div>
    @endif
</div>
