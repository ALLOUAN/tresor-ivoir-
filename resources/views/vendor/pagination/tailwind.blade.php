{{--
    Pagination par défaut (->links()), en français. Même structure et mêmes
    liens que la vue Tailwind de Laravel ; l'apparence publique est portée par
    .ti-pagination dans partials.ui-kit, les classes ci-dessous servent de
    rendu neutre partout ailleurs (back-office).
--}}
@if ($paginator->hasPages())
    <nav role="navigation" aria-label="{{ __('Navigation entre les pages') }}" class="ti-pagination flex flex-col-reverse items-center gap-3 sm:flex-row sm:justify-between">
        <p class="text-sm text-gray-500">
            @if ($paginator->firstItem())
                {{ __('Résultats') }} <span class="font-semibold text-gray-700">{{ $paginator->firstItem() }}</span>
                à <span class="font-semibold text-gray-700">{{ $paginator->lastItem() }}</span>
                {{ __('sur') }} <span class="font-semibold text-gray-700">{{ $paginator->total() }}</span>
            @else
                {{ $paginator->count() }} {{ __('résultat') }}{{ $paginator->count() > 1 ? 's' : '' }}
            @endif
        </p>

        <div class="flex flex-wrap items-center gap-1.5">
            @if ($paginator->onFirstPage())
                <span aria-disabled="true" aria-label="{{ __('Page précédente') }}">
                    <span class="inline-flex h-9 min-w-9 items-center justify-center rounded-lg border border-gray-200 px-3 text-sm text-gray-400" aria-hidden="true"><i class="fas fa-chevron-left text-[11px]"></i></span>
                </span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" rel="prev" aria-label="{{ __('Page précédente') }}"
                   class="inline-flex h-9 min-w-9 items-center justify-center rounded-lg border border-gray-200 bg-white px-3 text-sm text-gray-700 hover:border-gray-300">
                    <i class="fas fa-chevron-left text-[11px]"></i>
                </a>
            @endif

            @foreach ($elements as $element)
                @if (is_string($element))
                    <span aria-disabled="true">
                        <span class="inline-flex h-9 min-w-9 items-center justify-center px-2 text-sm text-gray-400">{{ $element }}</span>
                    </span>
                @endif

                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <span aria-current="page">
                                <span class="inline-flex h-9 min-w-9 items-center justify-center rounded-lg border border-gray-800 bg-gray-800 px-3 text-sm font-semibold text-white">{{ $page }}</span>
                            </span>
                        @else
                            <a href="{{ $url }}" aria-label="Aller à la page {{ $page }}"
                               class="inline-flex h-9 min-w-9 items-center justify-center rounded-lg border border-gray-200 bg-white px-3 text-sm text-gray-700 hover:border-gray-300">{{ $page }}</a>
                        @endif
                    @endforeach
                @endif
            @endforeach

            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" rel="next" aria-label="{{ __('Page suivante') }}"
                   class="inline-flex h-9 min-w-9 items-center justify-center rounded-lg border border-gray-200 bg-white px-3 text-sm text-gray-700 hover:border-gray-300">
                    <i class="fas fa-chevron-right text-[11px]"></i>
                </a>
            @else
                <span aria-disabled="true" aria-label="{{ __('Page suivante') }}">
                    <span class="inline-flex h-9 min-w-9 items-center justify-center rounded-lg border border-gray-200 px-3 text-sm text-gray-400" aria-hidden="true"><i class="fas fa-chevron-right text-[11px]"></i></span>
                </span>
            @endif
        </div>
    </nav>
@endif
