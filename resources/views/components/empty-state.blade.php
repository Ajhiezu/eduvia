@props([
    'title' => 'Belum Ada Data',
    'description' => 'Tidak ada entri data yang dapat ditampilkan untuk kriteria saat ini.',
])

<div {{ $attributes->merge(['class' => 'flex flex-col items-center justify-center text-center p-8 sm:p-12 rounded-2xl border-2 border-dashed border-slate-200 bg-slate-50/50']) }}>
    <div class="w-14 h-14 rounded-2xl bg-white border border-slate-200 shadow-xs flex items-center justify-center text-slate-400 mb-4">
        @if (isset($icon))
            {{ $icon }}
        @else
            <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
            </svg>
        @endif
    </div>

    <h4 class="text-base font-bold text-slate-800 leading-snug">{{ $title }}</h4>
    <p class="text-xs sm:text-sm text-slate-500 max-w-sm mt-1 mb-5 leading-relaxed">{{ $description }}</p>

    @if (isset($action))
        <div>
            {{ $action }}
        </div>
    @endif
</div>
