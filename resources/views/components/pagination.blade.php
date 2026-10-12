@props([
    'currentPage' => 1,
    'totalPages' => 1,
    'totalItems' => 0,
    'perPage' => 10,
])

<div class="flex flex-col sm:flex-row items-center justify-between gap-4 py-3 text-xs sm:text-sm text-slate-600">
    <div class="text-slate-500">
        Menampilkan <span class="font-semibold text-slate-900">{{ min(1 + (($currentPage - 1) * $perPage), $totalItems) }}</span> - <span class="font-semibold text-slate-900">{{ min($currentPage * $perPage, $totalItems) }}</span> dari <span class="font-semibold text-slate-900">{{ $totalItems }}</span> data
    </div>

    <div class="inline-flex items-center gap-1.5 select-none">
        <button
            type="button"
            {{ $currentPage <= 1 ? 'disabled' : '' }}
            class="px-3 py-1.5 rounded-lg border border-slate-200 bg-white text-slate-700 hover:bg-slate-50 disabled:opacity-40 disabled:pointer-events-none transition-colors shadow-xs"
        >
            Sebelumnya
        </button>

        @for ($page = 1; $page <= min($totalPages, 5); $page++)
            <button
                type="button"
                class="w-8 h-8 rounded-lg font-semibold text-xs flex items-center justify-center transition-colors {{ $page === $currentPage ? 'bg-brand-600 text-white shadow-xs' : 'border border-slate-200 bg-white text-slate-700 hover:bg-slate-50' }}"
            >
                {{ $page }}
            </button>
        @endfor

        <button
            type="button"
            {{ $currentPage >= $totalPages ? 'disabled' : '' }}
            class="px-3 py-1.5 rounded-lg border border-slate-200 bg-white text-slate-700 hover:bg-slate-50 disabled:opacity-40 disabled:pointer-events-none transition-colors shadow-xs"
        >
            Selanjutnya
        </button>
    </div>
</div>
