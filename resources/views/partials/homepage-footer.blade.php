@php
    $c = $siteBrand['contact'] ?? [];
    $s = $siteBrand['social'] ?? [];
    $socialRows = array_values(array_filter([
        ['url' => $s['facebook_url'] ?? null, 'icon' => 'fa-facebook-f', 'label' => 'Facebook'],
        ['url' => $s['instagram_url'] ?? null, 'icon' => 'fa-instagram', 'label' => 'Instagram'],
        ['url' => $s['twitter_url'] ?? null, 'icon' => 'fa-twitter', 'label' => 'Twitter / X'],
        ['url' => $s['linkedin_url'] ?? null, 'icon' => 'fa-linkedin-in', 'label' => 'LinkedIn'],
        ['url' => $s['youtube_url'] ?? null, 'icon' => 'fa-youtube', 'label' => 'YouTube'],
    ], fn ($row) => !empty($row['url'])));
    $waHref = \App\Models\SiteSetting::whatsappHref($s['whatsapp_phone'] ?? null);
    $mapHref = (!empty($c['latitude']) && !empty($c['longitude']))
        ? 'https://www.google.com/maps?q='.urlencode($c['latitude'].','.$c['longitude'])
        : null;
    $infoPages = $informationPages ?? (\Illuminate\Support\Facades\Schema::hasTable('information_pages')
        ? \App\Models\InformationPage::query()->orderBy('sort_order')->orderBy('id')->get()
        : collect());
    $homeCategories = $homeCategories ?? (\Illuminate\Support\Facades\Schema::hasTable('article_categories')
        ? \App\Models\ArticleCategory::query()->where('is_active', true)->orderBy('sort_order')->get()
        : collect());
    $homeProviderCategories = $homeProviderCategories ?? (\Illuminate\Support\Facades\Schema::hasTable('provider_categories')
        ? \App\Models\ProviderCategory::query()->where('is_active', true)->whereNull('parent_id')->orderBy('sort_order')->limit(6)->get()
        : collect());
    $infoLegal = $infoPages->firstWhere('slug', 'legal-notice');
    $infoGuide = $infoPages->firstWhere('slug', 'user-guide');
    $footerBlurb = !empty($siteBrand['site_description'])
        ? \Illuminate\Support\Str::limit(strip_tags($siteBrand['site_description']), 220)
        : 'Le magazine de référence pour explorer la culture, l\'art de vivre et le tourisme en Côte d\'Ivoire.';
@endphp

<style>
    .footer-ultra { position: relative; background: linear-gradient(105deg, #fa9a3c 0%, #f2790f 50%, #f97316 100%); }
    .footer-ultra::before { content: ''; position: absolute; inset: 0; background-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 256 256' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='fn'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.85' numOctaves='3' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23fn)' opacity='0.03'/%3E%3C/svg%3E"); pointer-events: none; opacity: .9; }
    .footer-ultra-inner { position: relative; z-index: 1; }
    .footer-v2-link { display: block; padding: .32rem 0; font-size: .8125rem; color: rgba(28,25,21,.88); transition: color .18s ease, padding-left .18s ease; }
    .footer-v2-link:hover { color: #1c1915; padding-left: .35rem; }
    .social-links-wrap { position: relative; }
    .social-icon-ultra { position: relative; overflow: hidden; border-radius: .62rem; backdrop-filter: blur(8px); transition: transform .28s cubic-bezier(.2,.8,.2,1),color .25s ease,border-color .25s ease,box-shadow .3s ease,background-color .25s ease; isolation: isolate; }
    .social-icon-ultra::before { content: ''; position: absolute; inset: -1px; border-radius: inherit; background: conic-gradient(from 180deg, rgba(0,0,0,0), rgba(0,0,0,.35), rgba(0,0,0,.1), rgba(0,0,0,0)); opacity: 0; transform: rotate(0deg); transition: opacity .28s ease; pointer-events: none; z-index: 0; }
    .social-icon-ultra::after { content: ''; position: absolute; inset: 1px; border-radius: calc(.62rem - 1px); background: linear-gradient(180deg, rgba(0,0,0,.06), rgba(0,0,0,.01)); opacity: 0; transition: opacity .25s ease; z-index: 0; }
    .social-icon-ultra i { position: relative; z-index: 1; transition: transform .25s ease; }
    .social-icon-ultra:hover { transform: translateY(-3px) scale(1.06); box-shadow: 0 10px 28px rgba(0,0,0,.2), 0 0 18px rgba(0,0,0,.15); }
    .social-icon-ultra:hover::before { opacity: .9; animation: socialRingSpin 1.2s linear infinite; }
    .social-icon-ultra:hover::after { opacity: 1; }
    .social-icon-ultra:hover i { transform: scale(1.1); }
    .social-icon-ultra.social-icon-wa:hover { box-shadow: 0 10px 28px rgba(0,0,0,.2), 0 0 18px rgba(16,110,80,.35); }
    .social-icon-ultra.social-icon-wa::before { background: conic-gradient(from 180deg, rgba(6,95,70,0), rgba(6,95,70,.6), rgba(236,253,245,.25), rgba(6,95,70,0)); }
    .footer-logo-link { display: inline-flex; align-items: flex-start; gap: .75rem; }
    .footer-logo-ring { position: relative; border-radius: .7rem; padding: 2px; background: linear-gradient(135deg, rgba(28,25,21,.4), rgba(255,255,255,.25), rgba(28,25,21,.25)); box-shadow: 0 8px 26px rgba(0,0,0,.22), 0 0 0 1px rgba(255,255,255,.08) inset; transition: transform .32s cubic-bezier(.2,.8,.2,1), box-shadow .32s ease; animation: footerLogoFloat 5.4s ease-in-out infinite; }
    .footer-logo-ring::before { content: ''; position: absolute; inset: -3px; border-radius: inherit; background: conic-gradient(from 180deg, rgba(28,25,21,0), rgba(28,25,21,.35), rgba(255,255,255,.15), rgba(28,25,21,0)); opacity: .4; filter: blur(5px); animation: footerLogoSpin 8s linear infinite; pointer-events: none; }
    .footer-logo-inner { border-radius: calc(.7rem - 2px); overflow: hidden; background: rgba(233, 229, 217, .95); transition: transform .32s cubic-bezier(.2,.8,.2,1), filter .32s ease; }
    .footer-logo-link:hover .footer-logo-ring { transform: translateY(-2px) scale(1.04); box-shadow: 0 12px 30px rgba(0,0,0,.28), 0 0 22px rgba(0,0,0,.18), 0 0 0 1px rgba(255,255,255,.1) inset; }
    .footer-logo-link:hover .footer-logo-ring::before { opacity: .82; }
    .footer-logo-link:hover .footer-logo-inner { transform: scale(1.04); filter: brightness(1.08) saturate(1.08); }
    @keyframes footerLogoFloat { 0%,100% { transform: translateY(0); } 50% { transform: translateY(-2px); } }
    @keyframes footerLogoSpin { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }
    @keyframes socialRingSpin { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }

    /* ── Texte foncé sur le fond orange vif (hors carte newsletter blanche) ──
       Sélecteurs préfixés par "html .footer-ultra" pour dépasser la
       spécificité des règles globales html:not(.dark) du pont de thème. ── */
    .footer-links-row, .footer-bottom-row { color: rgba(28,25,21,.85); }
    html .footer-ultra .footer-links-row .text-white,
    html .footer-ultra .footer-bottom-row .text-white { color: #1c1915 !important; }
    html .footer-ultra .footer-links-row .text-gray-300,
    html .footer-ultra .footer-links-row .text-gray-400,
    html .footer-ultra .footer-links-row .text-gray-500,
    html .footer-ultra .footer-links-row .text-gray-600,
    html .footer-ultra .footer-bottom-row .text-gray-500,
    html .footer-ultra .footer-bottom-row .text-gray-600 { color: rgba(28,25,21,.88) !important; }
    html .footer-ultra .footer-links-row a.hover\:text-white:hover,
    html .footer-ultra .footer-links-row a.hover\:text-orange-300:hover,
    html .footer-ultra .footer-bottom-row a.hover\:text-white:hover { color: #1c1915 !important; }
    html .footer-ultra .footer-links-row .border-white\/5,
    html .footer-ultra .footer-links-row .border-white\/10,
    html .footer-ultra .footer-bottom-row .border-white\/10 { border-color: rgba(28,25,21,.16) !important; }
    html .footer-ultra .footer-links-row h4.text-orange-400\/90,
    html .footer-ultra .footer-bottom-row a.text-orange-400\/90 { color: #1c1915 !important; }
    html .footer-ultra .footer-bottom-row a.hover\:text-orange-300:hover { color: #3d2b12 !important; }
    html .footer-ultra .social-icon-wa { color: #0a3324 !important; border-color: rgba(10,51,36,.3) !important; background-color: rgba(10,51,36,.08) !important; }
    html .footer-ultra .social-icon-wa:hover { color: #082a1d !important; }
</style>

<footer class="footer-ultra border-t border-white/[0.07]">
    <div class="footer-ultra-inner max-w-7xl mx-auto px-4 sm:px-6 py-12 sm:py-16">
        <div id="newsletter-footer" class="mb-12 sm:mb-14 scroll-mt-28">
            <div class="overflow-hidden rounded-2xl border border-white/[0.09] bg-[#ffffff] shadow-2xl shadow-green-950/50 reveal visible">
                <div class="grid lg:grid-cols-2 divide-y lg:divide-y-0 lg:divide-x divide-white/10">
                    <div class="relative p-6 sm:p-8 lg:p-10">
                        <div class="absolute left-0 top-8 bottom-8 w-1 rounded-full bg-gradient-to-b from-orange-300/70 via-orange-400/50 to-orange-600/30 pointer-events-none" aria-hidden="true"></div>
                        <div class="pl-5 sm:pl-6">
                            <p class="text-[10px] font-plus font-bold uppercase tracking-[0.28em] text-orange-400/85 mb-4">Inscription</p>
                            <h2 class="font-serif text-2xl sm:text-3xl font-semibold text-white mb-3 leading-tight tracking-tight">Ne manquez rien de l'Ivoire</h2>
                            <p class="text-gray-500 text-sm sm:text-[0.9375rem] font-plus leading-relaxed max-w-md">Articles, adresses et événements sélectionnés pour vous, directement dans votre boîte mail.</p>
                        </div>
                    </div>
                    <div class="p-6 sm:p-8 lg:p-10 bg-white/[0.02] flex flex-col justify-center">
                        @if (session('newsletter_success'))
                            <div class="rounded-lg border border-emerald-500/35 bg-emerald-500/[0.08] px-4 py-3 text-sm text-emerald-100 mb-4 font-plus">{{ session('newsletter_success') }}</div>
                        @endif
                        @if (session('newsletter_info'))
                            <div class="rounded-lg border border-orange-500/35 bg-orange-500/[0.08] px-4 py-3 text-sm text-orange-50 mb-4 font-plus">{{ session('newsletter_info') }}</div>
                        @endif
                        @if (session('newsletter_error'))
                            <div class="rounded-lg border border-rose-500/35 bg-rose-500/[0.08] px-4 py-3 text-sm text-rose-100 mb-4 font-plus">{{ session('newsletter_error') }}</div>
                        @endif
                        <form method="post" action="{{ route('newsletter.subscribe') }}" class="space-y-3">
                            @csrf
                            <label for="newsletter-email" class="sr-only">Adresse e-mail</label>
                            <input type="email" name="newsletter_email" id="newsletter-email" required maxlength="255" value="{{ old('newsletter_email') }}" placeholder="votre@email.com" autocomplete="email" class="w-full rounded-lg border border-white/12 bg-green-950/50 px-4 py-3.5 text-sm text-white placeholder:text-gray-600 outline-none transition focus:border-orange-400/40 focus:ring-1 focus:ring-orange-500/25 font-plus">
                            <button type="submit" class="w-full sm:w-auto inline-flex items-center justify-center gap-2 rounded-lg text-dark-900 px-6 py-3.5 text-sm font-bold bg-gradient-to-r from-gold-400 via-gold-500 to-orange-500 hover:from-gold-300 hover:to-orange-400 transition-all duration-300 shadow-lg shadow-gold-500/25 hover:-translate-y-0.5 font-plus">
                                <i class="fas fa-arrow-right text-xs"></i>
                                S'abonner
                            </button>
                        </form>
                        @error('newsletter_email')
                            <p class="text-rose-400 text-xs mt-2 font-plus">{{ $message }}</p>
                        @enderror
                        <p class="text-gray-600 text-[11px] mt-4 font-plus leading-relaxed">Pas de spam — désinscription en un clic à tout moment.</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="footer-links-row grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-10 lg:gap-0 mb-12 pt-2 border-t border-white/5">
            <div class="lg:col-span-3 lg:pr-8 lg:border-r border-white/5">
                <a href="{{ route('home') }}" class="footer-logo-link mb-4 group">
                    @if(!empty($siteBrand['logo_url']))
                        <div class="footer-logo-ring shrink-0"><div class="footer-logo-inner h-16 w-16 border border-white/10 bg-white/[0.04] flex items-center justify-center p-0.5"><img src="{{ $siteBrand['logo_url'] }}" alt="" class="max-w-full max-h-full object-contain"></div></div>
                    @else
                        <div class="footer-logo-ring shrink-0"><div class="footer-logo-inner h-16 w-16 bg-white flex items-center justify-center"><i class="fas fa-gem text-orange-500 text-sm"></i></div></div>
                    @endif
                    <div class="min-w-0">
                        <p class="text-white font-serif font-semibold leading-tight truncate">{{ $siteBrand['site_name'] }}</p>
                        <p class="text-gray-600 text-[10px] tracking-[0.16em] uppercase truncate font-plus mt-1">{{ $siteBrand['site_slogan'] ?: 'Magazine Premium' }}</p>
                    </div>
                </a>
                <p class="text-gray-500 text-xs leading-relaxed mb-5 font-plus">{{ $footerBlurb }}</p>
                @if(count($socialRows) > 0 || $waHref)
                    <p class="text-gray-600 text-[9px] uppercase tracking-[0.18em] mb-2.5 font-plus font-semibold">Réseaux</p>
                    <div class="social-links-wrap flex flex-wrap gap-2">
                        @foreach($socialRows as $row)
                            <a href="{{ $row['url'] }}" target="_blank" rel="noopener noreferrer" title="{{ $row['label'] }}" class="social-icon-ultra h-10 w-10 rounded-md border border-white/10 bg-white/[0.03] flex items-center justify-center text-gray-500 hover:text-white hover:border-white/25 hover:bg-white/[0.06] transition text-sm"><i class="fab {{ $row['icon'] }}"></i></a>
                        @endforeach
                        @if($waHref)
                            <a href="{{ $waHref }}" target="_blank" rel="noopener noreferrer" title="WhatsApp" class="social-icon-ultra social-icon-wa h-10 w-10 rounded-md border border-emerald-500/25 bg-emerald-500/[0.07] flex items-center justify-center text-emerald-400/90 hover:text-emerald-300 transition text-sm"><i class="fab fa-whatsapp"></i></a>
                        @endif
                    </div>
                @endif
            </div>

            <div class="lg:col-span-3 lg:px-8 lg:border-r border-white/5">
                <h4 class="font-plus text-[11px] font-bold uppercase tracking-[0.2em] text-orange-400/90 mb-4">Magazine</h4>
                <ul class="space-y-0.5 font-plus">
                    <li><a href="{{ route('articles.index') }}" class="footer-v2-link">Tous les articles</a></li>
                    <li><a href="{{ route('discoveries.index') }}" class="footer-v2-link">Découvertes</a></li>
                    <li><a href="{{ route('events.index') }}" class="footer-v2-link">Événements</a></li>
                    @foreach($homeCategories->take(3) as $cat)
                        <li><a href="{{ route('articles.index', ['categorie' => $cat->slug]) }}" class="footer-v2-link">{{ $cat->name_fr }}</a></li>
                    @endforeach
                </ul>
            </div>

            <div class="lg:col-span-3 lg:px-8 lg:border-r border-white/5">
                <h4 class="font-plus text-[11px] font-bold uppercase tracking-[0.2em] text-orange-400/90 mb-4">Annuaire</h4>
                <ul class="space-y-0.5 font-plus">
                    <li><a href="{{ route('providers.index') }}" class="footer-v2-link">Tous les prestataires</a></li>
                    @foreach($homeProviderCategories->take(4) as $pc)
                        <li><a href="{{ route('providers.index', ['categorie' => $pc->slug]) }}" class="footer-v2-link">{{ $pc->name_fr }}</a></li>
                    @endforeach
                    <li><a href="{{ route('register') }}" class="footer-v2-link">Devenir prestataire</a></li>
                </ul>
            </div>

            <div class="lg:col-span-3 lg:pl-8">
                <h4 class="font-plus text-[11px] font-bold uppercase tracking-[0.2em] text-orange-400/90 mb-4">Contact</h4>
                <ul class="space-y-0.5 font-plus mb-5">
                    @forelse($infoPages as $infoPage)
                        <li><a href="{{ route('information.show', $infoPage) }}" class="footer-v2-link">{{ $infoPage->title_fr }}</a></li>
                    @empty
                        <li class="text-gray-600 italic text-xs py-1">Pages d'information à configurer.</li>
                    @endforelse
                </ul>
                <div class="space-y-2.5 text-[11px] text-gray-500 font-plus border-t border-white/5 pt-4">
                    @if(!empty($c['phone_1']))<p><span class="text-gray-600">Tél.</span> <a href="tel:{{ preg_replace('/\s+/', '', $c['phone_1']) }}" class="text-gray-300 hover:text-orange-300 transition break-all">{{ $c['phone_1'] }}</a></p>@endif
                    @if(!empty($c['phone_2']))<p><span class="text-gray-600">Tél. 2</span> <a href="tel:{{ preg_replace('/\s+/', '', $c['phone_2']) }}" class="text-gray-300 hover:text-orange-300 transition break-all">{{ $c['phone_2'] }}</a></p>@endif
                    @if(!empty($c['email_primary']))<p><a href="mailto:{{ $c['email_primary'] }}" class="text-gray-300 hover:text-orange-300 transition break-all">{{ $c['email_primary'] }}</a></p>@endif
                    @if(!empty($c['email_secondary']))<p><a href="mailto:{{ $c['email_secondary'] }}" class="text-gray-300 hover:text-orange-300 transition break-all">{{ $c['email_secondary'] }}</a></p>@endif
                    @if(!empty($c['contact_form_email']))<p><span class="text-gray-600">Formulaire</span> <a href="mailto:{{ $c['contact_form_email'] }}" class="text-gray-300 hover:text-orange-300 transition break-all">{{ $c['contact_form_email'] }}</a></p>@endif
                    @if(!empty($c['address']))<p class="text-gray-400 leading-snug">@if($mapHref)<a href="{{ $mapHref }}" target="_blank" rel="noopener noreferrer" class="hover:text-orange-300 transition">{{ $c['address'] }}</a>@else{{ $c['address'] }}@endif</p>@endif
                    @if(!empty($c['opening_hours']))<p class="whitespace-pre-line text-gray-500 leading-relaxed">{{ $c['opening_hours'] }}</p>@endif
                    @if(empty($c['phone_1']) && empty($c['phone_2']) && empty($c['email_primary']) && empty($c['email_secondary']) && empty($c['contact_form_email']) && empty($c['address']) && empty($c['opening_hours']))
                        <p class="text-gray-600 italic text-xs">Coordonnées à renseigner dans l'administration.</p>
                    @endif
                </div>
            </div>
        </div>

        <div class="footer-bottom-row flex flex-col sm:flex-row items-center justify-between gap-4 pt-6 border-t border-white/10">
            <p class="text-[11px] text-gray-600 font-plus tracking-wide text-center sm:text-left">&copy; {{ date('Y') }} {{ $siteBrand['site_name'] }} — Tous droits réservés</p>
            <div class="flex flex-wrap items-center justify-center gap-x-5 gap-y-2 text-[11px] font-plus">
                @if($infoLegal)
                    <a href="{{ route('information.show', $infoLegal) }}" class="text-gray-500 hover:text-white transition underline-offset-4 hover:underline">Mentions légales</a>
                @endif
                @if($infoGuide)
                    <a href="{{ route('information.show', $infoGuide) }}" class="text-gray-500 hover:text-white transition underline-offset-4 hover:underline">CGU</a>
                @endif
                <a href="{{ route('login') }}" class="text-orange-400/90 hover:text-orange-300 font-semibold transition">Espace membres →</a>
            </div>
        </div>
    </div>
</footer>
