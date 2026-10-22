{{--
    Avis clients d'une fiche établissement. Paramètres : $reviews, $model (note moyenne du prestataire).
--}}
@php
    $dxRating = (float) ($model->provider?->rating_avg ?? 0);
    $dxCount = max((int) ($model->provider?->rating_count ?? 0), $reviews->count());
@endphp
<div class="hx-sechead">
    <div>
        <p class="hx-kicker">{{ __('Retours d\'expérience') }}</p>
        <h2 class="hx-h2">{{ __('Avis clients') }}</h2>
    </div>
    @if($dxRating > 0)
        <span class="hx-score"><strong>{{ number_format($dxRating, 1, ',', ' ') }}</strong><span><i class="fas fa-star" aria-hidden="true"></i> {{ trans_choice(':count avis|:count avis', $dxCount) }}</span></span>
    @endif
</div>
<div class="hx-reviews">
    @foreach($reviews as $review)
        @php
            $rvName = $review->user->first_name ?? __('Visiteur');
            $rvRate = (int) ($review->rating_quality ?? 0);
        @endphp
        <article class="hx-review">
            <div class="hx-review__head">
                <span class="hx-review__avatar" aria-hidden="true">{{ mb_strtoupper(mb_substr($rvName, 0, 1)) }}</span>
                <span class="min-w-0">
                    <span class="hx-review__name">{{ $rvName }}</span>
                    @if($review->created_at)<span class="hx-review__date">{{ $review->created_at->translatedFormat('F Y') }}</span>@endif
                </span>
                @if($rvRate > 0)
                    <span class="hx-review__stars" aria-label="{{ $rvRate }}/5">@for($s = 1; $s <= 5; $s++)<i class="fas fa-star {{ $s <= $rvRate ? '' : 'is-off' }}" aria-hidden="true"></i>@endfor</span>
                @endif
            </div>
            <p class="hx-review__text">{{ $review->comment }}</p>
        </article>
    @endforeach
</div>
