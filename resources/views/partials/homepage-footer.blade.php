@php
    $c = $siteBrand['contact'] ?? [];
    $s = $siteBrand['social'] ?? [];
    $socialRows = array_values(array_filter([
        ['url' => $s['facebook_url'] ?? null, 'icon' => 'fa-facebook-f', 'label' => __('Facebook')],
        ['url' => $s['instagram_url'] ?? null, 'icon' => 'fa-instagram', 'label' => __('Instagram')],
        ['url' => $s['twitter_url'] ?? null, 'icon' => 'fa-twitter', 'label' => __('Twitter / X')],
        ['url' => $s['linkedin_url'] ?? null, 'icon' => 'fa-linkedin-in', 'label' => __('LinkedIn')],
        ['url' => $s['youtube_url'] ?? null, 'icon' => 'fa-youtube', 'label' => __('YouTube')],
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
        : __('Le magazine de référence pour explorer la culture, l\'art de vivre et le tourisme en Côte d\'Ivoire.');
    $footerImage = $footerImage ?? (\Illuminate\Support\Facades\Schema::hasTable('footer_images')
        ? \App\Models\FooterImage::query()->find(1)
        : null);
@endphp

<style>
    .footer-ultra {
        position: relative;
        background-image: linear-gradient(rgba(7, 22, 13, 0.89), rgba(7, 22, 13, 0.89)), var(--footer-bg-image, none);
        background-size: cover;
        background-position: center;
        background-repeat: no-repeat;
        background-color: #0c1a12;
    }
    .footer-ultra::before { content: ''; position: absolute; inset: 0; background-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 256 256' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='fn'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.85' numOctaves='3' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23fn)' opacity='0.03'/%3E%3C/svg%3E"); pointer-events: none; opacity: .9; }
    .footer-ultra-inner { position: relative; z-index: 1; }
    .footer-v2-link { display: block; padding: .32rem 0; font-size: .9375rem; color: rgba(255,255,255,.72); transition: color .15s ease; }
    .footer-v2-link:hover { color: #fdba74; text-decoration: underline; text-underline-offset: 3px; }
    .social-links-wrap { position: relative; }
    .social-icon-ultra { position: relative; border-radius: .6rem; transition: color .15s ease, border-color .15s ease, background-color .15s ease; }
    .social-icon-ultra i { position: relative; }
    .social-icon-ultra:hover { border-color: rgba(255,255,255,.35) !important; background-color: rgba(255,255,255,.08) !important; }
    .footer-logo-link { display: inline-flex; align-items: flex-start; gap: .75rem; }
    .footer-logo-ring { position: relative; border-radius: .7rem; padding: 1px; background: rgba(255,255,255,.18); }
    .footer-logo-inner { border-radius: calc(.7rem - 1px); overflow: hidden; background: #ffffff; }

    /* ── Texte clair sur le fond sombre (hors carte newsletter blanche) ──
       Sélecteurs préfixés par "html .footer-ultra" pour dépasser la
       spécificité des règles globales html:not(.dark) du pont de thème. ── */
    .footer-links-row, .footer-bottom-row { color: rgba(255,255,255,.78); }
    html .footer-ultra .footer-links-row .text-white,
    html .footer-ultra .footer-bottom-row .text-white { color: #ffffff !important; }
    html .footer-ultra .footer-links-row .text-gray-300,
    html .footer-ultra .footer-links-row .text-gray-400,
    html .footer-ultra .footer-links-row .text-gray-500,
    html .footer-ultra .footer-links-row .text-gray-600,
    html .footer-ultra .footer-bottom-row .text-gray-500,
    html .footer-ultra .footer-bottom-row .text-gray-600 { color: rgba(255,255,255,.6) !important; }
    html .footer-ultra .footer-links-row a.hover\:text-white:hover,
    html .footer-ultra .footer-links-row a.hover\:text-orange-300:hover,
    html .footer-ultra .footer-bottom-row a.hover\:text-white:hover { color: #fdba74 !important; }
    html .footer-ultra .footer-links-row .border-white\/5,
    html .footer-ultra .footer-links-row .border-white\/10,
    html .footer-ultra .footer-bottom-row .border-white\/10 { border-color: rgba(255,255,255,.12) !important; }
    html .footer-ultra .footer-links-row h4.text-orange-400\/90,
    html .footer-ultra .footer-bottom-row a.text-orange-400\/90 { color: #fb923c !important; }
    html .footer-ultra .footer-bottom-row a.hover\:text-orange-300:hover { color: #fdba74 !important; }
    html .footer-ultra .social-icon-wa { color: #6ee7b7 !important; border-color: rgba(16,185,129,.3) !important; background-color: rgba(16,185,129,.08) !important; }
    html .footer-ultra .social-icon-wa:hover { color: #a7f3d0 !important; }

    /* Bloc d'inscription plus compact sur mobile (il occupait presque tout l'écran). */
    @media (max-width: 640px) {
        .footer-ultra-inner { padding-top: 32px; }
        #newsletter-footer { margin-bottom: 32px; }
    }
</style>

<footer class="footer-ultra border-t border-white/[0.07]"
    @if($footerImage && $footerImage->isVisible())
        style="--footer-bg-image: url('{{ $footerImage->image_url }}');"
    @endif
>
    <div class="footer-ultra-inner max-w-7xl mx-auto px-4 sm:px-6 py-12 sm:py-16">
        <div id="newsletter-footer" class="mb-12 sm:mb-14 scroll-mt-28">
            <style>
                /* Encart newsletter : panneau vert à motifs (promesse + contenu), formulaire en ligne. Aplats, ombre au survol seulement. */
                html .footer-ultra .nlx { position: relative; overflow: hidden; display: grid; grid-template-columns: minmax(0, 5fr) minmax(0, 7fr); border-radius: 24px; background: #fff; color: #1d1a16; font-family: 'Inter', sans-serif; }
                html .footer-ultra .nlx-pitch { position: relative; overflow: hidden; padding: 34px 32px 38px; background: #123a25; color: #fff; }
                html .footer-ultra .nlx-pitch .ivm-g { opacity: .1 !important; }
                html .footer-ultra .nlx-pitch .ivm-g > * { stroke: #f6c38f !important; }
                html .footer-ultra .nlx-pitch > :not(.ivm) { position: relative; z-index: 1; }
                html .footer-ultra .nlx-kick { display: inline-flex; align-items: center; gap: 7px; padding: 5px 11px; border-radius: 999px; background: rgba(255,255,255,.12); color: #f6c38f !important; font-size: 11px; font-weight: 800; letter-spacing: .1em; text-transform: uppercase; }
                html .footer-ultra .nlx-title { margin: 12px 0 0; font-family: 'Playfair Display', serif; font-size: clamp(24px, 2.4vw, 30px); font-weight: 700; line-height: 1.15; color: #fff !important; }
                html .footer-ultra .nlx-lead { margin: 8px 0 0; font-size: 14.5px; line-height: 1.55; color: #cfe6d7 !important; max-width: 34ch; }
                html .footer-ultra .nlx-perks { display: flex; flex-wrap: wrap; gap: 8px; margin: 18px 0 0; padding: 0; list-style: none; }
                html .footer-ultra .nlx-perks li { display: inline-flex; align-items: center; gap: 7px; height: 32px; padding: 0 12px; border-radius: 999px; background: rgba(255,255,255,.1); color: #fff !important; font-size: 12.5px; font-weight: 600; }
                html .footer-ultra .nlx-perks li .ico { width: 15px; height: 15px; color: #f6c38f; background: none !important; border: 0 !important; padding: 0 !important; }
                html .footer-ultra .nlx-form-col { display: flex; flex-direction: column; justify-content: center; padding: 34px 36px 40px; }
                html .footer-ultra .nlx-label { display: block; margin: 0 0 8px; font-size: 14px; font-weight: 700; color: #1d1a16 !important; }
                html .footer-ultra .nlx-row { display: flex; gap: 8px; padding: 6px; border: 1.5px solid #e6dccb; border-radius: 999px; background: #fbf9f5; transition: border-color .15s ease, background .15s ease; }
                html .footer-ultra .nlx-row:focus-within { border-color: #b9500a; background: #fff; box-shadow: 0 0 0 4px rgba(185,80,10,.12); }
                html .footer-ultra .nlx-row input { flex: 1; min-width: 0; height: 46px; padding: 0 14px; border: 0 !important; background: transparent !important; color: #1d1a16 !important; font: inherit; font-size: 15px; outline: none; box-shadow: none !important; }
                html .footer-ultra .nlx-row input::placeholder { color: #9a8f7c; }
                html .footer-ultra .nlx-send { flex: none; display: inline-flex; align-items: center; gap: 8px; height: 46px; padding: 0 22px; border: 0; border-radius: 999px; background: #b9500a !important; color: #fff !important; font-size: 14.5px; font-weight: 800; cursor: pointer; }
                html .footer-ultra .nlx-send:hover { background: #9a4308 !important; }
                html .footer-ultra .nlx-send .ico { width: 16px; height: 16px; background: none !important; border: 0 !important; padding: 0 !important; color: #fff !important; }
                html .footer-ultra .nlx-send:focus-visible { outline: 3px solid #ef7c2b; outline-offset: 2px; }
                html .footer-ultra .nlx-note { display: flex; align-items: center; gap: 7px; margin: 12px 0 0; font-size: 12.5px; color: #6f6656 !important; }
                html .footer-ultra .nlx-note .ico { width: 15px; height: 15px; color: #1f8079; background: none !important; border: 0 !important; padding: 0 !important; }
                html .footer-ultra .nlx-msg { margin: 0 0 14px; padding: 11px 14px; border-radius: 14px; font-size: 13.5px; font-weight: 600; }
                html .footer-ultra .nlx-msg.is-ok { background: #eef6ef; color: #1f5a33 !important; border: 1px solid #cfe4d3; }
                html .footer-ultra .nlx-msg.is-info { background: #fdf4ec; color: #9a4308 !important; border: 1px solid #f1dcc4; }
                html .footer-ultra .nlx-msg.is-err { background: #fdf0ee; color: #b42318 !important; border: 1px solid #f4cfc9; }
                html .footer-ultra .nlx-err { margin: 8px 0 0 14px; font-size: 12.5px; font-weight: 600; color: #b42318 !important; }
                @media (max-width: 1023px) {
                    html .footer-ultra .nlx { grid-template-columns: minmax(0, 1fr); }
                    html .footer-ultra .nlx-lead { max-width: none; }
                }
                @media (max-width: 640px) {
                    html .footer-ultra .nlx { border-radius: 20px; }
                    html .footer-ultra .nlx-pitch { padding: 20px 18px 22px; }
                    html .footer-ultra .nlx-title { font-size: 22px; margin-top: 10px; }
                    html .footer-ultra .nlx-lead { font-size: 14px; }
                    html .footer-ultra .nlx-perks { margin-top: 12px; gap: 6px; }
                    html .footer-ultra .nlx-perks li { height: 28px; padding: 0 10px; font-size: 12px; }
                    html .footer-ultra .nlx-form-col { padding: 18px 18px 24px; }
                    html .footer-ultra .nlx-row { flex-direction: column; border-radius: 18px; }
                    html .footer-ultra .nlx-send { justify-content: center; border-radius: 14px; }
                }
            </style>
            <div class="nlx nl-card reveal visible">
                <div class="nlx-pitch">
                    @include('partials.ivorian-motifs', ['culture' => 'newsletter', 'band' => false])
                    <span class="nlx-kick"><x-icon name="star-shine" />{{ __('Inscription') }}</span>
                    <h2 class="nlx-title">{{ __('Ne manquez rien de l\'Ivoire') }}</h2>
                    <p class="nlx-lead">{{ __('Articles, adresses et événements sélectionnés pour vous, directement dans votre boîte mail.') }}</p>
                    <ul class="nlx-perks">
                        <li><x-icon name="book" />{{ __('Articles') }}</li>
                        <li><x-icon name="map-point" />{{ __('Adresses') }}</li>
                        <li><x-icon name="calendar" />{{ __('Événements') }}</li>
                    </ul>
                </div>
                <div class="nlx-form-col">
                    @if (session('newsletter_success'))
                        <div class="nlx-msg is-ok" role="status">{{ session('newsletter_success') }}</div>
                    @endif
                    @if (session('newsletter_info'))
                        <div class="nlx-msg is-info" role="status">{{ session('newsletter_info') }}</div>
                    @endif
                    @if (session('newsletter_error'))
                        <div class="nlx-msg is-err" role="alert">{{ session('newsletter_error') }}</div>
                    @endif
                    <form method="post" action="{{ route('newsletter.subscribe') }}">
                        @csrf
                        <label for="newsletter-email" class="nlx-label">{{ __('Adresse e-mail') }}</label>
                        <div class="nlx-row">
                            <input type="email" name="newsletter_email" id="newsletter-email" required maxlength="255" value="{{ old('newsletter_email') }}" placeholder="{{ __('votre@email.com') }}" autocomplete="email" @error('newsletter_email') aria-invalid="true" aria-describedby="newsletter-email-error" @enderror>
                            <button type="submit" class="nlx-send"><x-icon name="plane" />{{ __('S\'abonner') }}</button>
                        </div>
                    </form>
                    @error('newsletter_email')
                        <p id="newsletter-email-error" class="nlx-err">{{ $message }}</p>
                    @enderror
                    <p class="nlx-note"><x-icon name="shield-check" />{{ __('Pas de spam — désinscription en un clic à tout moment.') }}</p>
                </div>
                <div class="nl-kita" aria-hidden="true"></div>
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
                        <p class="text-gray-600 text-[10px] tracking-[0.16em] uppercase truncate font-plus mt-1">{{ $siteBrand['site_slogan'] ?: __('Magazine Premium') }}</p>
                    </div>
                </a>
                <p class="text-[rgba(255,255,255,.72)] text-xl font-elegant font-light leading-relaxed mb-5">{{ $footerBlurb }}</p>
                @if(count($socialRows) > 0 || $waHref)
                    <p class="text-gray-600 text-[9px] uppercase tracking-[0.18em] mb-2.5 font-plus font-semibold">{{ __('Réseaux') }}</p>
                    <div class="social-links-wrap flex flex-wrap gap-2">
                        @foreach($socialRows as $row)
                            <a href="{{ $row['url'] }}" target="_blank" rel="noopener noreferrer" title="{{ $row['label'] }}" class="social-icon-ultra h-10 w-10 rounded-md border border-white/10 bg-white/[0.03] flex items-center justify-center text-gray-500 hover:text-white hover:border-white/25 hover:bg-white/[0.06] transition text-sm"><i class="fab {{ $row['icon'] }}"></i></a>
                        @endforeach
                        @if($waHref)
                            <a href="{{ $waHref }}" target="_blank" rel="noopener noreferrer" title="{{ __('WhatsApp') }}" class="social-icon-ultra social-icon-wa h-10 w-10 rounded-md border border-emerald-500/25 bg-emerald-500/[0.07] flex items-center justify-center text-emerald-400/90 hover:text-emerald-300 transition text-sm"><i class="fab fa-whatsapp"></i></a>
                        @endif
                    </div>
                @endif
            </div>

            <div class="lg:col-span-3 lg:px-8 lg:border-r border-white/5">
                <h4 class="font-plus text-sm font-bold uppercase tracking-[0.2em] text-orange-400/90 mb-4">{{ __('Magazine') }}</h4>
                <ul class="space-y-0.5 font-plus">
                    <li><a href="{{ route('articles.index') }}" class="footer-v2-link">{{ __('Tous les articles') }}</a></li>
                    <li><a href="{{ route('discoveries.index') }}" class="footer-v2-link">{{ __('Découvertes') }}</a></li>
                    <li><a href="{{ route('events.index') }}" class="footer-v2-link">{{ __('Événements') }}</a></li>
                    <li><a href="{{ route('bundles.index') }}" class="footer-v2-link">{{ __('Offres groupées') }}</a></li>
                    @if(config('ai.enabled', true))
                        <li><a href="{{ route('trip.create') }}" class="footer-v2-link">{{ __('Planifier mon séjour') }}</a></li>
                        <li><a href="{{ route('ai.for-you') }}" class="footer-v2-link">{{ __('Pour vous') }}</a></li>
                    @endif
                    @foreach($homeCategories->take(3) as $cat)
                        <li><a href="{{ route('articles.index', ['categorie' => $cat->slug]) }}" class="footer-v2-link">{{ $cat->localized('name') }}</a></li>
                    @endforeach
                </ul>
            </div>

            <div class="lg:col-span-3 lg:px-8 lg:border-r border-white/5">
                <h4 class="font-plus text-sm font-bold uppercase tracking-[0.2em] text-orange-400/90 mb-4">{{ __('Annuaire') }}</h4>
                <ul class="space-y-0.5 font-plus">
                    <li><a href="{{ route('providers.index') }}" class="footer-v2-link">{{ __('Tous les prestataires') }}</a></li>
                    <li><a href="{{ route('accommodations.index') }}" class="footer-v2-link">{{ __('Résidences & Hôtels') }}</a></li>
                    <li><a href="{{ route('art.index') }}" class="footer-v2-link">{{ __('Art & Créations') }}</a></li>
                    @foreach($homeProviderCategories->take(4) as $pc)
                        <li><a href="{{ route('providers.index', ['categorie' => $pc->slug]) }}" class="footer-v2-link">{{ $pc->localized('name') }}</a></li>
                    @endforeach
                    <li><a href="{{ route('register') }}" class="footer-v2-link">{{ __('Devenir prestataire') }}</a></li>
                </ul>
            </div>

            <div class="lg:col-span-3 lg:pl-8">
                <h4 class="font-plus text-sm font-bold uppercase tracking-[0.2em] text-orange-400/90 mb-4">{{ __('Contact') }}</h4>
                <ul class="space-y-0.5 font-plus mb-5">
                    @forelse($infoPages as $infoPage)
                        <li><a href="{{ route('information.show', $infoPage) }}" class="footer-v2-link">{{ $infoPage->localized('title') }}</a></li>
                    @empty
                        <li class="text-gray-600 italic text-xs py-1">{{ __('Pages d\'information à configurer.') }}</li>
                    @endforelse
                </ul>
                <div class="space-y-2.5 text-[11px] text-gray-500 font-plus border-t border-white/5 pt-4">
                    @if(!empty($c['phone_1']))<p><span class="text-gray-600">{{ __('Tél.') }}</span> <a href="tel:{{ preg_replace('/\s+/', '', $c['phone_1']) }}" class="text-gray-300 hover:text-orange-300 transition break-all">{{ $c['phone_1'] }}</a></p>@endif
                    @if(!empty($c['phone_2']))<p><span class="text-gray-600">{{ __('Tél. 2') }}</span> <a href="tel:{{ preg_replace('/\s+/', '', $c['phone_2']) }}" class="text-gray-300 hover:text-orange-300 transition break-all">{{ $c['phone_2'] }}</a></p>@endif
                    @if(!empty($c['email_primary']))<p><a href="mailto:{{ $c['email_primary'] }}" class="text-gray-300 hover:text-orange-300 transition break-all">{{ $c['email_primary'] }}</a></p>@endif
                    @if(!empty($c['email_secondary']))<p><a href="mailto:{{ $c['email_secondary'] }}" class="text-gray-300 hover:text-orange-300 transition break-all">{{ $c['email_secondary'] }}</a></p>@endif
                    @if(!empty($c['contact_form_email']))<p><span class="text-gray-600">{{ __('Formulaire') }}</span> <a href="mailto:{{ $c['contact_form_email'] }}" class="text-gray-300 hover:text-orange-300 transition break-all">{{ $c['contact_form_email'] }}</a></p>@endif
                    @if(!empty($c['address']))<p class="text-gray-400 leading-snug">@if($mapHref)<a href="{{ $mapHref }}" target="_blank" rel="noopener noreferrer" class="hover:text-orange-300 transition">{{ $c['address'] }}</a>@else{{ $c['address'] }}@endif</p>@endif
                    @if(!empty($c['opening_hours']))<p class="whitespace-pre-line text-gray-500 leading-relaxed">{{ $c['opening_hours'] }}</p>@endif
                    @if(empty($c['phone_1']) && empty($c['phone_2']) && empty($c['email_primary']) && empty($c['email_secondary']) && empty($c['contact_form_email']) && empty($c['address']) && empty($c['opening_hours']))
                        <p class="text-gray-600 italic text-xs">{{ __('Coordonnées à renseigner dans l\'administration.') }}</p>
                    @endif
                </div>
            </div>
        </div>

        <div class="footer-bottom-row flex flex-col sm:flex-row items-center justify-between gap-4 pt-6 border-t border-white/10">
            <p class="text-[11px] text-gray-600 font-plus tracking-wide text-center sm:text-left">&copy; {{ date('Y') }} {{ $siteBrand['site_name'] }} — {{ __('Tous droits réservés') }}</p>
            <div class="flex flex-wrap items-center justify-center gap-x-5 gap-y-2 text-[11px] font-plus">
                @if($infoLegal)
                    <a href="{{ route('information.show', $infoLegal) }}" class="text-gray-500 hover:text-white transition underline-offset-4 hover:underline">{{ __('Mentions légales') }}</a>
                @endif
                @if($infoGuide)
                    <a href="{{ route('information.show', $infoGuide) }}" class="text-gray-500 hover:text-white transition underline-offset-4 hover:underline">{{ __('CGU') }}</a>
                @endif
                <a href="{{ route('login') }}" class="text-orange-400/90 hover:text-orange-300 font-semibold transition">{{ __('Espace membres →') }}</a>
            </div>
        </div>
    </div>
</footer>
