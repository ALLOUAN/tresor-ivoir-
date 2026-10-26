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
        background-image: linear-gradient(rgba(0,107,63, 0.89), rgba(0,107,63, 0.89)), var(--footer-bg-image, none);
        background-size: cover;
        background-position: center;
        background-repeat: no-repeat;
        background-color: #006B3F;
    }
    .footer-ultra::before { content: ''; position: absolute; inset: 0; background-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 256 256' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='fn'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.85' numOctaves='3' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23fn)' opacity='0.03'/%3E%3C/svg%3E"); pointer-events: none; opacity: .9; }
    .footer-ultra-inner { position: relative; z-index: 1; }
    .footer-v2-link { display: block; padding: .32rem 0; font-size: .9375rem; color: rgba(255,255,255,.72); transition: color .15s ease; }
    .footer-v2-link:hover { color: #F7C396; text-decoration: underline; text-underline-offset: 3px; }
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
    html .footer-ultra .footer-bottom-row a.hover\:text-white:hover { color: #F7C396 !important; }
    html .footer-ultra .footer-links-row .border-white\/5,
    html .footer-ultra .footer-links-row .border-white\/10,
    html .footer-ultra .footer-bottom-row .border-white\/10 { border-color: rgba(255,255,255,.12) !important; }
    html .footer-ultra .footer-links-row h4.text-orange-400\/90,
    html .footer-ultra .footer-bottom-row a.text-orange-400\/90 { color: #EB6E00 !important; }
    html .footer-ultra .footer-bottom-row a.hover\:text-orange-300:hover { color: #F7C396 !important; }
    html .footer-ultra .social-icon-wa { color: #00874F !important; border-color: rgba(0,135,79,.3) !important; background-color: rgba(0,135,79,.08) !important; }
    html .footer-ultra .social-icon-wa:hover { color: #DFF0E7 !important; }

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
                html .footer-ultra .nlx { position: relative; overflow: hidden; display: grid; grid-template-columns: minmax(0, 5fr) minmax(0, 7fr); border-radius: 24px; background: #fff; color: #0E3B2A; font-family: 'Montserrat', sans-serif; }
                html .footer-ultra .nlx-pitch { position: relative; overflow: hidden; padding: 34px 32px 38px; background: #00874F; color: #fff; }
                html .footer-ultra .nlx-pitch .ivm-g { opacity: .1 !important; }
                html .footer-ultra .nlx-pitch .ivm-g > * { stroke: #F7C396 !important; }
                html .footer-ultra .nlx-pitch > :not(.ivm) { position: relative; z-index: 1; }
                html .footer-ultra .nlx-kick { display: inline-flex; align-items: center; gap: 7px; padding: 5px 11px; border-radius: 999px; background: rgba(255,255,255,.12); color: #F7C396 !important; font-size: 11px; font-weight: 800; letter-spacing: .1em; text-transform: uppercase; }
                html .footer-ultra .nlx-title { margin: 12px 0 0; font-family: 'Montserrat', serif; font-size: clamp(24px, 2.4vw, 30px); font-weight: 700; line-height: 1.15; color: #fff !important; }
                html .footer-ultra .nlx-lead { margin: 8px 0 0; font-size: 14.5px; line-height: 1.55; color: #D8E9E0 !important; max-width: 34ch; }
                html .footer-ultra .nlx-perks { display: flex; flex-wrap: wrap; gap: 8px; margin: 18px 0 0; padding: 0; list-style: none; }
                html .footer-ultra .nlx-perks li { display: inline-flex; align-items: center; gap: 7px; height: 32px; padding: 0 12px; border-radius: 999px; background: rgba(255,255,255,.1); color: #fff !important; font-size: 12.5px; font-weight: 600; }
                html .footer-ultra .nlx-perks li .ico { width: 15px; height: 15px; color: #F7C396; background: none !important; border: 0 !important; padding: 0 !important; }
                html .footer-ultra .nlx-form-col { display: flex; flex-direction: column; justify-content: center; padding: 34px 36px 40px; }
                html .footer-ultra .nlx-label { display: block; margin: 0 0 8px; font-size: 14px; font-weight: 700; color: #0E3B2A !important; }
                html .footer-ultra .nlx-row { display: flex; gap: 8px; padding: 6px; border: 1.5px solid #D8E9E0; border-radius: 999px; background: #FFFFFF; transition: border-color .15s ease, background .15s ease; }
                html .footer-ultra .nlx-row:focus-within { border-color: #EB6E00; background: #fff; box-shadow: 0 0 0 4px rgba(235,110,0,.12); }
                html .footer-ultra .nlx-row input { flex: 1; min-width: 0; height: 46px; padding: 0 14px; border: 0 !important; background: transparent !important; color: #0E3B2A !important; font: inherit; font-size: 15px; outline: none; box-shadow: none !important; }
                html .footer-ultra .nlx-row input::placeholder { color: #557466; }
                html .footer-ultra .nlx-send { flex: none; display: inline-flex; align-items: center; gap: 8px; height: 46px; padding: 0 22px; border: 0; border-radius: 999px; background: #EB6E00 !important; color: #fff !important; font-size: 14.5px; font-weight: 800; cursor: pointer; }
                html .footer-ultra .nlx-send:hover { background: #B85600 !important; }
                html .footer-ultra .nlx-send .ico { width: 16px; height: 16px; background: none !important; border: 0 !important; padding: 0 !important; color: #fff !important; }
                html .footer-ultra .nlx-send:focus-visible { outline: 3px solid #EB6E00; outline-offset: 2px; }
                html .footer-ultra .nlx-note { display: flex; align-items: center; gap: 7px; margin: 12px 0 0; font-size: 12.5px; color: #557466 !important; }
                html .footer-ultra .nlx-note .ico { width: 15px; height: 15px; color: #00874F; background: none !important; border: 0 !important; padding: 0 !important; }
                html .footer-ultra .nlx-msg { margin: 0 0 14px; padding: 11px 14px; border-radius: 14px; font-size: 13.5px; font-weight: 600; }
                html .footer-ultra .nlx-msg.is-ok { background: #F3F8F5; color: #00874F !important; border: 1px solid #D8E9E0; }
                html .footer-ultra .nlx-msg.is-info { background: #F3F8F5; color: #B85600 !important; border: 1px solid #D8E9E0; }
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

        {{-- Pied de page : panneau vert profond, colonnes repliables sur mobile (<details>), coordonnées toujours visibles, grand logotype en filigrane. --}}
        <style>
            html .footer-ultra .fx { position: relative; overflow: hidden; border-radius: 28px; background: rgba(0,90,53,.9); border: 1px solid rgba(255,255,255,.12); color: #fff; font-family: 'Montserrat', sans-serif; }
            html .footer-ultra .fx-grid { display: grid; grid-template-columns: minmax(0,1.35fr) repeat(3, minmax(0,1fr)); gap: 40px; padding: 44px 44px 8px; }
            html .footer-ultra .fx-logo { display: inline-block; padding: 12px 16px; border-radius: 18px; background: #fff; }
            html .footer-ultra .fx-logo img { display: block; width: 200px; height: auto; }
            html .footer-ultra .fx-blurb { margin: 18px 0 0; max-width: 34ch; font-size: 15px; line-height: 1.6; color: rgba(255,255,255,.8) !important; }
            html .footer-ultra .fx-actions { display: flex; flex-wrap: wrap; gap: 10px; margin-top: 22px; }
            html .footer-ultra .fx-cta, html .footer-ultra .fx-ghost { display: inline-flex; align-items: center; gap: 8px; height: 44px; padding: 0 18px; border-radius: 999px; font-size: 14px; font-weight: 700; transition: background-color .15s ease, border-color .15s ease, color .15s ease; }
            html .footer-ultra .fx-cta { background: #EB6E00; color: #fff !important; }
            html .footer-ultra .fx-cta:hover { background: #B85600; }
            html .footer-ultra .fx-cta i { transition: transform .15s ease; }
            html .footer-ultra .fx-cta:hover i { transform: translateX(3px); }
            html .footer-ultra .fx-ghost { border: 1.5px solid rgba(255,255,255,.35); color: #fff !important; }
            html .footer-ultra .fx-ghost:hover { border-color: #fff; background: rgba(255,255,255,.08); }
            html .footer-ultra .fx-social { display: flex; flex-wrap: wrap; gap: 8px; margin-top: 22px; }
            html .footer-ultra .fx-social a { width: 40px; height: 40px; display: inline-flex; align-items: center; justify-content: center; border-radius: 999px; background: rgba(255,255,255,.1); color: #fff !important; font-size: 15px; transition: background-color .15s ease, color .15s ease; }
            html .footer-ultra .fx-social a:hover { background: #fff; color: #00874F !important; }
            html .footer-ultra .fx-col summary { list-style: none; display: flex; align-items: center; gap: 10px; margin-bottom: 16px; font-size: 12.5px; font-weight: 800; letter-spacing: .14em; text-transform: uppercase; color: #fff !important; cursor: default; }
            html .footer-ultra .fx-col summary::-webkit-details-marker { display: none; }
            html .footer-ultra .fx-h__ic { width: 30px; height: 30px; flex: none; display: inline-flex; align-items: center; justify-content: center; border-radius: 10px; background: #EB6E00; color: #fff; font-size: 15px; }
            html .footer-ultra .fx-h__chev { display: none; margin-left: auto; font-size: 12px; transition: transform .2s ease; }
            html .footer-ultra .fx-list { margin: 0; padding: 0; list-style: none; }
            html .footer-ultra .fx-link { position: relative; display: inline-flex; align-items: center; padding: 6px 0; font-size: 14.5px; color: rgba(255,255,255,.78) !important; transition: color .15s ease, transform .15s ease; }
            html .footer-ultra .fx-link::before { content: ''; width: 0; height: 2px; margin-right: 0; border-radius: 2px; background: #EB6E00; transition: width .18s ease, margin-right .18s ease; }
            html .footer-ultra .fx-link:hover, html .footer-ultra .fx-link:focus-visible { color: #fff !important; }
            html .footer-ultra .fx-link:hover::before, html .footer-ultra .fx-link:focus-visible::before { width: 12px; margin-right: 8px; }
            html .footer-ultra .fx-contact { margin-top: 18px; padding-top: 18px; border-top: 1px solid rgba(255,255,255,.14); display: grid; gap: 10px; }
            html .footer-ultra .fx-contact a, html .footer-ultra .fx-contact p { display: flex; align-items: flex-start; gap: 10px; margin: 0; font-size: 13.5px; line-height: 1.45; color: rgba(255,255,255,.85) !important; word-break: break-word; }
            html .footer-ultra .fx-contact a:hover { color: #fff !important; text-decoration: underline; text-underline-offset: 3px; }
            html .footer-ultra .fx-contact i { width: 18px; flex: none; margin-top: 2px; text-align: center; color: #F7C396; }
            html .footer-ultra .fx-mark { margin: 8px 0 -0.14em; padding: 0 24px; text-align: center; white-space: nowrap; overflow: hidden; font-size: clamp(40px, 8.6vw, 128px); font-weight: 800; line-height: 1; letter-spacing: .04em; text-transform: uppercase; -webkit-text-fill-color: transparent; opacity: .4; user-select: none; }
            html .footer-ultra .fx-mark span { -webkit-text-stroke: 1.5px #FFFFFF; }
            html .footer-ultra .fx-mark span + span { -webkit-text-stroke-color: #EB6E00; }
            html .footer-ultra .fx-bottom { display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 14px 24px; padding: 18px 44px; background: rgba(0,0,0,.14); border-top: 1px solid rgba(255,255,255,.1); font-size: 13px; color: rgba(255,255,255,.72) !important; }
            html .footer-ultra .fx-bottom nav { display: flex; flex-wrap: wrap; align-items: center; gap: 6px 20px; }
            html .footer-ultra .fx-bottom a { color: rgba(255,255,255,.8) !important; }
            html .footer-ultra .fx-bottom a:hover { color: #fff !important; text-decoration: underline; text-underline-offset: 3px; }
            html .footer-ultra .fx-top { width: 44px; height: 44px; display: inline-flex; align-items: center; justify-content: center; border: 0; border-radius: 999px; background: #fff; color: #00874F; cursor: pointer; transition: background-color .15s ease, color .15s ease; }
            html .footer-ultra .fx-top:hover { background: #EB6E00; color: #fff; }
            html .footer-ultra .fx a:focus-visible, html .footer-ultra .fx button:focus-visible, html .footer-ultra .fx summary:focus-visible { outline: 3px solid #F7C396; outline-offset: 3px; border-radius: 8px; }
            @media (max-width: 1023px) {
                html .footer-ultra .fx-grid { grid-template-columns: repeat(3, minmax(0,1fr)); padding: 36px 32px 8px; }
                html .footer-ultra .fx-brand { grid-column: 1 / -1; }
                html .footer-ultra .fx-bottom { padding: 16px 32px; }
            }
            @media (max-width: 767px) {
                html .footer-ultra .fx { border-radius: 22px; }
                html .footer-ultra .fx-grid { grid-template-columns: minmax(0,1fr); gap: 0; padding: 24px 18px 4px; }
                html .footer-ultra .fx-brand { padding-bottom: 22px; }
                html .footer-ultra .fx-logo img { width: 180px; }
                html .footer-ultra .fx-actions > a { flex: 1 1 auto; justify-content: center; }
                html .footer-ultra .fx-colwrap { border-top: 1px solid rgba(255,255,255,.14); }
                html .footer-ultra .fx-col summary { margin: 0; min-height: 56px; cursor: pointer; }
                html .footer-ultra .fx-h__chev { display: inline-block; }
                html .footer-ultra .fx-col[open] .fx-h__chev { transform: rotate(180deg); }
                html .footer-ultra .fx-col[open] .fx-list { padding-bottom: 12px; }
                html .footer-ultra .fx-contact { margin: 0 0 18px; }
                html .footer-ultra .fx-bottom { flex-direction: column; align-items: flex-start; padding: 16px 18px; }
                html .footer-ultra .fx-top { position: absolute; right: 18px; bottom: 16px; }
            }
            @media (prefers-reduced-motion: reduce) { html .footer-ultra .fx * { transition: none !important; } }
        </style>

        <div class="fx">
            <div class="fx-grid">
                <div class="fx-brand">
                    {{-- Logo officiel avec signature sur bandeau blanc (charte : pas de version négative fournie). --}}
                    <a href="{{ route('home') }}" class="fx-logo" aria-label="{{ $siteBrand['site_name'] }}">
                        <img src="{{ $siteBrand['logo_horizontal_url'] ?? $siteBrand['logo_url'] }}" alt="{{ $siteBrand['site_name'] }}" width="1200" height="449">
                    </a>
                    <p class="fx-blurb">{{ $footerBlurb }}</p>
                    <div class="fx-actions">
                        <a href="{{ route('register') }}" class="fx-cta">{{ __('Devenir prestataire') }} <i class="fas fa-arrow-right" aria-hidden="true"></i></a>
                        <a href="{{ route('login') }}" class="fx-ghost"><i class="fas fa-user" aria-hidden="true"></i> {{ __('Espace membres') }}</a>
                    </div>
                    @if(count($socialRows) > 0 || $waHref)
                        <div class="fx-social" aria-label="{{ __('Réseaux') }}">
                            @foreach($socialRows as $row)
                                <a href="{{ $row['url'] }}" target="_blank" rel="noopener noreferrer" title="{{ $row['label'] }}" aria-label="{{ $row['label'] }}"><i class="fab {{ $row['icon'] }}" aria-hidden="true"></i></a>
                            @endforeach
                            @if($waHref)
                                <a href="{{ $waHref }}" target="_blank" rel="noopener noreferrer" title="{{ __('WhatsApp') }}" aria-label="{{ __('WhatsApp') }}"><i class="fab fa-whatsapp" aria-hidden="true"></i></a>
                            @endif
                        </div>
                    @endif
                </div>

                <div class="fx-colwrap">
                    <details class="fx-col" open data-fx-col>
                        <summary><span class="fx-h__ic"><x-icon name="book" /></span>{{ __('Magazine') }}<i class="fas fa-chevron-down fx-h__chev" aria-hidden="true"></i></summary>
                        <ul class="fx-list">
                            <li><a href="{{ route('articles.index') }}" class="fx-link">{{ __('Tous les articles') }}</a></li>
                            <li><a href="{{ route('discoveries.index') }}" class="fx-link">{{ __('Découvertes') }}</a></li>
                            <li><a href="{{ route('events.index') }}" class="fx-link">{{ __('Événements') }}</a></li>
                            <li><a href="{{ route('bundles.index') }}" class="fx-link">{{ __('Offres groupées') }}</a></li>
                            @if(config('ai.enabled', true))
                                <li><a href="{{ route('trip.create') }}" class="fx-link">{{ __('Planifier mon séjour') }}</a></li>
                                <li><a href="{{ route('ai.for-you') }}" class="fx-link">{{ __('Pour vous') }}</a></li>
                            @endif
                            @foreach($homeCategories->take(3) as $cat)
                                <li><a href="{{ route('articles.index', ['categorie' => $cat->slug]) }}" class="fx-link">{{ $cat->localized('name') }}</a></li>
                            @endforeach
                        </ul>
                    </details>
                </div>

                <div class="fx-colwrap">
                    <details class="fx-col" open data-fx-col>
                        <summary><span class="fx-h__ic"><x-icon name="shop" /></span>{{ __('Annuaire') }}<i class="fas fa-chevron-down fx-h__chev" aria-hidden="true"></i></summary>
                        <ul class="fx-list">
                            <li><a href="{{ route('providers.index') }}" class="fx-link">{{ __('Tous les prestataires') }}</a></li>
                            <li><a href="{{ route('accommodations.index') }}" class="fx-link">{{ __('Résidences & Hôtels') }}</a></li>
                            <li><a href="{{ route('art.index') }}" class="fx-link">{{ __('Art & Créations') }}</a></li>
                            @foreach($homeProviderCategories->take(4) as $pc)
                                <li><a href="{{ route('providers.index', ['categorie' => $pc->slug]) }}" class="fx-link">{{ $pc->localized('name') }}</a></li>
                            @endforeach
                        </ul>
                    </details>
                </div>

                <div class="fx-colwrap">
                    <details class="fx-col" open data-fx-col>
                        <summary><span class="fx-h__ic"><x-icon name="chat-round-dots" /></span>{{ __('Contact') }}<i class="fas fa-chevron-down fx-h__chev" aria-hidden="true"></i></summary>
                        <ul class="fx-list">
                            @foreach($infoPages as $infoPage)
                                <li><a href="{{ route('information.show', $infoPage) }}" class="fx-link">{{ $infoPage->localized('title') }}</a></li>
                            @endforeach
                        </ul>
                    </details>
                    {{-- Coordonnées hors du repli : toujours visibles, cliquables sur mobile. --}}
                    @if(!empty($c['phone_1']) || !empty($c['phone_2']) || !empty($c['email_primary']) || !empty($c['email_secondary']) || !empty($c['contact_form_email']) || !empty($c['address']) || !empty($c['opening_hours']))
                        <div class="fx-contact">
                            @if(!empty($c['phone_1']))<a href="tel:{{ preg_replace('/\s+/', '', $c['phone_1']) }}"><i class="fas fa-phone" aria-hidden="true"></i>{{ $c['phone_1'] }}</a>@endif
                            @if(!empty($c['phone_2']))<a href="tel:{{ preg_replace('/\s+/', '', $c['phone_2']) }}"><i class="fas fa-phone" aria-hidden="true"></i>{{ $c['phone_2'] }}</a>@endif
                            @if(!empty($c['email_primary']))<a href="mailto:{{ $c['email_primary'] }}"><i class="fas fa-envelope" aria-hidden="true"></i>{{ $c['email_primary'] }}</a>@endif
                            @if(!empty($c['email_secondary']))<a href="mailto:{{ $c['email_secondary'] }}"><i class="fas fa-envelope" aria-hidden="true"></i>{{ $c['email_secondary'] }}</a>@endif
                            @if(!empty($c['contact_form_email']))<a href="mailto:{{ $c['contact_form_email'] }}"><i class="fas fa-paper-plane" aria-hidden="true"></i>{{ $c['contact_form_email'] }}</a>@endif
                            @if(!empty($c['address']))
                                @if($mapHref)<a href="{{ $mapHref }}" target="_blank" rel="noopener noreferrer"><i class="fas fa-location-dot" aria-hidden="true"></i>{{ $c['address'] }}</a>@else<p><i class="fas fa-location-dot" aria-hidden="true"></i>{{ $c['address'] }}</p>@endif
                            @endif
                            @if(!empty($c['opening_hours']))<p class="whitespace-pre-line"><i class="fas fa-clock" aria-hidden="true"></i>{{ $c['opening_hours'] }}</p>@endif
                        </div>
                    @endif
                </div>
            </div>

            <div class="fx-mark" aria-hidden="true"><span>Trésors</span> <span>d'Ivoire</span></div>

            <div class="fx-bottom">
                <p style="margin:0">&copy; {{ date('Y') }} {{ $siteBrand['site_name'] }} — {{ __('Tous droits réservés') }}</p>
                <nav aria-label="{{ __('Informations légales') }}">
                    @if($infoLegal)<a href="{{ route('information.show', $infoLegal) }}">{{ __('Mentions légales') }}</a>@endif
                    @if($infoGuide)<a href="{{ route('information.show', $infoGuide) }}">{{ __('CGU') }}</a>@endif
                </nav>
                <button type="button" class="fx-top" onclick="window.scrollTo({ top: 0, behavior: 'smooth' })" aria-label="{{ __('Haut de page') }}" title="{{ __('Haut de page') }}"><i class="fas fa-arrow-up" aria-hidden="true"></i></button>
            </div>
        </div>
        <script>
            // Mobile : colonnes de liens repliées par défaut (les coordonnées restent visibles) ; ordinateur : toujours ouvertes.
            (function () {
                var mq = window.matchMedia('(max-width: 767px)');
                function sync() { document.querySelectorAll('[data-fx-col]').forEach(function (d) { d.open = !mq.matches; }); }
                sync(); mq.addEventListener ? mq.addEventListener('change', sync) : mq.addListener(sync);
                // Logotype en filigrane : taille ajustée pour tenir sur une ligne, quelle que soit la longueur du nom.
                var mark = document.querySelector('.footer-ultra .fx-mark');
                function fit() {
                    if (!mark) return;
                    mark.style.fontSize = '';
                    var room = mark.clientWidth - 48, need = mark.scrollWidth - 48, size = parseFloat(getComputedStyle(mark).fontSize);
                    if (need > room && need > 0) mark.style.fontSize = Math.floor(size * room / need) + 'px';
                }
                fit(); window.addEventListener('resize', fit);
                if (document.fonts && document.fonts.ready) document.fonts.ready.then(fit);
                document.querySelectorAll('[data-fx-col] > summary').forEach(function (s) {
                    s.addEventListener('click', function (e) { if (!mq.matches) e.preventDefault(); });
                });
            })();
        </script>
    </div>
</footer>

