@if ($paginator->hasPages())
    <nav role="navigation" aria-label="{{ __('Pagination Navigation') }}" class="w-full flex flex-col sm:flex-row items-center justify-between gap-3 px-5 sm:px-6 py-3.5 text-xs text-zinc-500 select-none">
        {{-- Mobile View (Compact) --}}
        <div class="flex items-center justify-between gap-2 sm:hidden w-full">
            @if ($paginator->onFirstPage())
                <span class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-medium text-zinc-300 bg-zinc-50 border border-zinc-200/60 rounded-lg cursor-not-allowed">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                    <span>Sebelumnya</span>
                </span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-semibold text-zinc-700 bg-white border border-zinc-200 rounded-lg hover:bg-zinc-50 hover:text-zinc-900 transition-colors shadow-2xs">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                    <span>Sebelumnya</span>
                </a>
            @endif

            <span class="text-xs font-medium text-zinc-600">
                Hal. <span class="font-bold text-zinc-900">{{ $paginator->currentPage() }}</span> dari <span class="font-bold text-zinc-900">{{ $paginator->lastPage() }}</span>
            </span>

            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-semibold text-zinc-700 bg-white border border-zinc-200 rounded-lg hover:bg-zinc-50 hover:text-zinc-900 transition-colors shadow-2xs">
                    <span>Berikutnya</span>
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </a>
            @else
                <span class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-medium text-zinc-300 bg-zinc-50 border border-zinc-200/60 rounded-lg cursor-not-allowed">
                    <span>Berikutnya</span>
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </span>
            @endif
        </div>

        {{-- Desktop View (Full Width Justified) --}}
        <div class="hidden sm:flex sm:flex-1 sm:items-center sm:justify-between w-full">
            <div>
                <p class="text-xs text-zinc-500 font-medium">
                    Menampilkan <span class="font-bold text-zinc-900">{{ $paginator->firstItem() }}</span> &ndash; <span class="font-bold text-zinc-900">{{ $paginator->lastItem() }}</span> dari <span class="font-bold text-zinc-900">{{ $paginator->total() }}</span> data
                </p>
            </div>

            <div class="inline-flex items-center gap-1.5">
                {{-- Previous Page Link --}}
                @if ($paginator->onFirstPage())
                    <span aria-disabled="true" class="w-8 h-8 rounded-lg flex items-center justify-center border border-zinc-200/60 bg-zinc-50 text-zinc-300 cursor-not-allowed">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                        </svg>
                    </span>
                @else
                    <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="w-8 h-8 rounded-lg flex items-center justify-center border border-zinc-200 bg-white text-zinc-600 hover:bg-zinc-50 hover:text-zinc-900 hover:border-zinc-300 transition-colors shadow-2xs" title="Halaman Sebelumnya">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                        </svg>
                    </a>
                @endif

                {{-- Pagination Elements --}}
                @foreach ($elements as $element)
                    {{-- "Three Dots" Separator --}}
                    @if (is_string($element))
                        <span class="w-8 h-8 flex items-center justify-center text-xs font-semibold text-zinc-400">
                            {{ $element }}
                        </span>
                    @endif

                    {{-- Array Of Links --}}
                    @if (is_array($element))
                        @foreach ($element as $page => $url)
                            @if ($page == $paginator->currentPage())
                                <span class="w-8 h-8 rounded-lg flex items-center justify-center text-xs font-bold text-white bg-brand-600 border border-brand-600 shadow-2xs">
                                    {{ $page }}
                                </span>
                            @else
                                <a href="{{ $url }}" class="w-8 h-8 rounded-lg flex items-center justify-center text-xs font-medium text-zinc-700 bg-white border border-zinc-200 hover:bg-zinc-50 hover:text-zinc-900 hover:border-zinc-300 transition-colors shadow-2xs">
                                    {{ $page }}
                                </a>
                            @endif
                        @endforeach
                    @endif
                @endforeach

                {{-- Next Page Link --}}
                @if ($paginator->hasMorePages())
                    <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="w-8 h-8 rounded-lg flex items-center justify-center border border-zinc-200 bg-white text-zinc-600 hover:bg-zinc-50 hover:text-zinc-900 hover:border-zinc-300 transition-colors shadow-2xs" title="Halaman Berikutnya">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </a>
                @else
                    <span aria-disabled="true" class="w-8 h-8 rounded-lg flex items-center justify-center border border-zinc-200/60 bg-zinc-50 text-zinc-300 cursor-not-allowed">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </span>
                @endif
            </div>
        </div>
    </nav>
@endif
