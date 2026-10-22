{{--
    Sources documentaires d'une fiche (peuple, élément culturel, site, ville).
    Paramètre : $sources — liste de ['label' => …, 'url' => …|null]. Rien n'est affiché si vide.
--}}
@php $srcList = collect($sources ?? [])->filter(fn ($s) => ! empty($s['label'])); @endphp
@if($srcList->isNotEmpty())
<section class="ti-sources {{ empty($inline) ? 'max-w-6xl mx-auto px-4 sm:px-6 pb-10' : '' }}" aria-labelledby="ti-sources-title">
    <div class="ti-sources__box">
        <h2 id="ti-sources-title" class="ti-sources__title"><i class="fas fa-book-open" aria-hidden="true"></i>{{ __('Sources') }}</h2>
        <ol class="ti-sources__list">
            @foreach($srcList as $s)
                <li>
                    @if(! empty($s['url']))
                        <a href="{{ $s['url'] }}" target="_blank" rel="noopener noreferrer">{{ $s['label'] }}<i class="fas fa-arrow-up-right-from-square" aria-hidden="true"></i></a>
                    @else
                        {{ $s['label'] }}
                    @endif
                </li>
            @endforeach
        </ol>
    </div>
</section>
@once
<style>
    .ti-sources__box { padding: 18px 20px; border-radius: 22px; background: #fff; border: 1px solid #ece5d6; }
    .ti-sources__title { display: flex; align-items: center; gap: 10px; margin: 0 0 10px; font-size: 13px; font-weight: 700; letter-spacing: .12em; text-transform: uppercase; color: #14532d; }
    .ti-sources__title i { color: #b9500a; }
    .ti-sources__list { margin: 0; padding-left: 20px; list-style: decimal; display: flex; flex-direction: column; gap: 6px; font-size: 13.5px; line-height: 1.5; color: #4a443a; }
    .ti-sources__list a { color: #9a4308; text-decoration: underline; text-underline-offset: 2px; }
    .ti-sources__list a i { margin-left: 6px; font-size: 10px; }
</style>
@endonce
@endif
