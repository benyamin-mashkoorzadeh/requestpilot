@if ($paginator->hasPages())
    <nav role="navigation" aria-label="Pagination navigation" class="flex items-center justify-between gap-4">
        <div class="flex flex-1 justify-between sm:hidden">
            @if ($paginator->onFirstPage())
                <span class="inline-flex cursor-default items-center rounded-lg border border-slate-300 bg-slate-200 px-3 py-2 text-sm font-semibold text-slate-500">Previous</span>
            @else
                <button type="button" wire:click="previousPage('{{ $paginator->getPageName() }}')" wire:loading.attr="disabled" class="inline-flex items-center rounded-lg border border-slate-400 bg-slate-200 px-3 py-2 text-sm font-semibold text-slate-800 hover:border-indigo-500 hover:text-indigo-700 focus:outline-none focus:ring-4 focus:ring-indigo-300/60">Previous</button>
            @endif

            @if ($paginator->hasMorePages())
                <button type="button" wire:click="nextPage('{{ $paginator->getPageName() }}')" wire:loading.attr="disabled" class="inline-flex items-center rounded-lg border border-slate-400 bg-slate-200 px-3 py-2 text-sm font-semibold text-slate-800 hover:border-indigo-500 hover:text-indigo-700 focus:outline-none focus:ring-4 focus:ring-indigo-300/60">Next</button>
            @else
                <span class="inline-flex cursor-default items-center rounded-lg border border-slate-300 bg-slate-200 px-3 py-2 text-sm font-semibold text-slate-500">Next</span>
            @endif
        </div>

        <p class="hidden text-sm text-slate-700 sm:block">
            Showing <span class="font-bold text-slate-900">{{ $paginator->firstItem() }}</span>
            to <span class="font-bold text-slate-900">{{ $paginator->lastItem() }}</span>
            of <span class="font-bold text-slate-900">{{ $paginator->total() }}</span>
        </p>

        <div class="hidden items-center sm:flex">
            @if ($paginator->onFirstPage())
                <span class="inline-flex size-9 cursor-default items-center justify-center rounded-l-lg border border-slate-300 bg-slate-200 text-slate-500" aria-disabled="true" aria-label="Previous page">
                    <span aria-hidden="true">‹</span>
                </span>
            @else
                <button type="button" wire:click="previousPage('{{ $paginator->getPageName() }}')" wire:loading.attr="disabled" class="inline-flex size-9 items-center justify-center rounded-l-lg border border-slate-400 bg-slate-200 text-slate-700 hover:z-10 hover:border-indigo-500 hover:text-indigo-700 focus:z-10 focus:outline-none focus:ring-4 focus:ring-indigo-300/60" aria-label="Previous page">‹</button>
            @endif

            @foreach ($elements as $element)
                @if (is_string($element))
                    <span class="-ml-px inline-flex h-9 items-center border border-slate-400 bg-slate-200 px-3 text-sm font-semibold text-slate-600">{{ $element }}</span>
                @endif

                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page === $paginator->currentPage())
                            <span wire:key="page-{{ $page }}" class="-ml-px inline-flex size-9 items-center justify-center border border-indigo-600 bg-indigo-600 text-sm font-bold text-white" aria-current="page">{{ $page }}</span>
                        @else
                            <button type="button" wire:key="page-{{ $page }}" wire:click="gotoPage({{ $page }}, '{{ $paginator->getPageName() }}')" wire:loading.attr="disabled" class="-ml-px inline-flex size-9 items-center justify-center border border-slate-400 bg-slate-200 text-sm font-semibold text-slate-700 hover:z-10 hover:border-indigo-500 hover:text-indigo-700 focus:z-10 focus:outline-none focus:ring-4 focus:ring-indigo-300/60" aria-label="Go to page {{ $page }}">{{ $page }}</button>
                        @endif
                    @endforeach
                @endif
            @endforeach

            @if ($paginator->hasMorePages())
                <button type="button" wire:click="nextPage('{{ $paginator->getPageName() }}')" wire:loading.attr="disabled" class="-ml-px inline-flex size-9 items-center justify-center rounded-r-lg border border-slate-400 bg-slate-200 text-slate-700 hover:z-10 hover:border-indigo-500 hover:text-indigo-700 focus:z-10 focus:outline-none focus:ring-4 focus:ring-indigo-300/60" aria-label="Next page">›</button>
            @else
                <span class="-ml-px inline-flex size-9 cursor-default items-center justify-center rounded-r-lg border border-slate-300 bg-slate-200 text-slate-500" aria-disabled="true" aria-label="Next page">
                    <span aria-hidden="true">›</span>
                </span>
            @endif
        </div>
    </nav>
@endif
