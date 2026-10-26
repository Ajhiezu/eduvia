@props([
    'currentPage' => 1,
    'totalPages' => 1,
    'totalItems' => 0,
    'perPage' => 10,
])

<div class="w-full flex flex-col sm:flex-row items-center justify-between gap-3 px-5 sm:px-6 py-3.5 text-xs text-zinc-500 select-none">
    <div>
        <p class="text-xs text-zinc-500 font-medium">
            Menampilkan <span class="font-bold text-zinc-900">{{ min(1 + (($currentPage - 1) * $perPage), $totalItems) }}</span> &ndash; <span class="font-bold text-zinc-900">{{ min($currentPage * $perPage, $totalItems) }}</span> dari <span class="font-bold text-zinc-900">{{ $totalItems }}</span> data
        </p>
    </div>

    <div class="inline-flex items-center gap-1.5">
        <button
            type="button"
            {{ $currentPage <= 1 ? 'disabled' : '' }}
            class="w-8 h-8 rounded-lg flex items-center justify-center border border-zinc-200 bg-white text-zinc-600 hover:bg-zinc-50 hover:text-zinc-900 hover:border-zinc-300 disabled:border-zinc-200/60 disabled:bg-zinc-50 disabled:text-zinc-300 disabled:cursor-not-allowed transition-colors shadow-2xs"
            title="Halaman Sebelumnya"
        >
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
        </button>

        @for ($page = 1; $page <= min($totalPages, 5); $page++)
            <button
                type="button"
                class="w-8 h-8 rounded-lg flex items-center justify-center text-xs transition-colors {{ $page === (int)$currentPage ? 'font-bold text-white bg-brand-600 border border-brand-600 shadow-2xs' : 'font-medium text-zinc-700 bg-white border border-zinc-200 hover:bg-zinc-50 hover:text-zinc-900 hover:border-zinc-300 shadow-2xs' }}"
            >
                {{ $page }}
            </button>
        @endfor

        <button
            type="button"
            {{ $currentPage >= $totalPages ? 'disabled' : '' }}
            class="w-8 h-8 rounded-lg flex items-center justify-center border border-zinc-200 bg-white text-zinc-600 hover:bg-zinc-50 hover:text-zinc-900 hover:border-zinc-300 disabled:border-zinc-200/60 disabled:bg-zinc-50 disabled:text-zinc-300 disabled:cursor-not-allowed transition-colors shadow-2xs"
            title="Halaman Berikutnya"
        >
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
            </svg>
        </button>
    </div>
</div>
