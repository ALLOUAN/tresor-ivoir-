<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="robots" content="noindex, nofollow">
    <title>{{ __('Fiche d\'enregistrement') }} — {{ $reservation->accommodation_name }}</title>
    @include('partials.theme-init')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    @php
        $acc = $reservation->accommodation;
        $accImg = $acc?->cover_image ?? $acc?->thumbnail;
        $nights = (int) ($reservation->nights ?: ($reservation->check_in && $reservation->check_out ? $reservation->check_in->diffInDays($reservation->check_out) : 0));
        $prefill = $prefill ?? [];
        $countryCodes = ['+225' => 'Côte d\'Ivoire', '+221' => 'Sénégal', '+223' => 'Mali', '+224' => 'Guinée', '+226' => 'Burkina Faso', '+227' => 'Niger', '+228' => 'Togo', '+229' => 'Bénin', '+231' => 'Liberia', '+233' => 'Ghana', '+234' => 'Nigeria', '+237' => 'Cameroun', '+212' => 'Maroc', '+33' => 'France', '+32' => 'Belgique', '+41' => 'Suisse', '+49' => 'Allemagne', '+44' => 'Royaume-Uni', '+1' => 'USA / Canada', '+961' => 'Liban', '+86' => 'Chine'];
        $nationalities = ['Ivoirienne', 'Burkinabè', 'Malienne', 'Guinéenne', 'Sénégalaise', 'Ghanéenne', 'Nigériane', 'Togolaise', 'Béninoise', 'Nigérienne', 'Libérienne', 'Camerounaise', 'Marocaine', 'Libanaise', 'Française', 'Belge', 'Suisse', 'Allemande', 'Britannique', 'Américaine', 'Canadienne', 'Chinoise'];
        $docIcons = ['cni' => 'fa-id-card', 'passeport' => 'fa-passport', 'permis_conduire' => 'fa-car', 'carte_consulaire' => 'fa-building-flag'];
        $reasonIcons = ['tourisme' => 'fa-umbrella-beach', 'affaires' => 'fa-briefcase', 'visite_familiale' => 'fa-people-roof', 'conference_seminaire' => 'fa-chalkboard-user', 'sante' => 'fa-heart-pulse', 'autre' => 'fa-ellipsis'];
        // Étape où se trouve chaque champ : après une erreur serveur, on rouvre la bonne étape.
        $stepOf = ['travel_type' => 1, 'travel_reason' => 1, 'coming_from' => 1, 'going_to' => 1, 'hotel_check_in_date' => 1, 'hotel_check_in_time' => 1, 'hotel_check_out_date' => 1, 'hotel_check_out_time' => 1,
            'document_type' => 2, 'document_number' => 2, 'document_expiry_date' => 2, 'document_scan_front' => 2, 'document_scan_back' => 2, 'selfie' => 2,
            'first_name' => 3, 'last_name' => 3, 'nationality' => 3, 'birth_date' => 3, 'birth_place' => 3, 'father_name' => 3, 'mother_name' => 3, 'profession' => 3, 'home_address' => 3, 'children_count' => 3, 'emergency_contact_name' => 3, 'emergency_contact_phone' => 3,
            'phone_country_code' => 4, 'phone_number' => 4, 'email' => 4, 'data_confirmed' => 4, 'signature_image' => 4];
        $startStep = 1;
        foreach ($stepOf as $field => $step) { if ($errors->has($field)) { $startStep = $step; break; } }
        $err = fn (string $f) => $errors->first($f);
    @endphp
    <style>
        /* Fiche d'enregistrement voyageur : 4 étapes courtes, récapitulatif du séjour à côté, pièces envoyées
           en privé. Charte du site (crème, vert forêt, orange), aplats, ombre au survol seulement. */
        * { box-sizing: border-box; }
        body { margin: 0; background: #f6f3ed; color: #1d1a16; font-family: 'Inter', system-ui, sans-serif; }
        .gr-stage { position: relative; overflow: hidden; }
        .gr-stage > .ivm .ivm-g { opacity: .1 !important; }
        @media (max-width: 640px) { .gr-stage > .ivm .ivm-g { opacity: .14 !important; } }
        .gr { position: relative; z-index: 1; max-width: 1120px; margin: 0 auto; padding: 108px 20px 64px; }
        .gr a { color: inherit; }
        .gr-head { position: relative; overflow: hidden; display: grid; grid-template-columns: minmax(0, 1fr) 360px; gap: 28px; align-items: center; padding: 30px 30px 34px; border-radius: 28px; background: #123a25; color: #fff; }
        .gr-where { display: flex; align-items: center; gap: 7px; margin: 6px 0 0; font-size: 14px; font-weight: 600; color: #f6c38f; }
        .gr-law { display: flex; align-items: center; gap: 8px; margin: 14px 0 0; font-size: 12.5px; color: #a9cdb6; }
        .gr-law i { color: #f6c38f; }
        /* Carte « À préparer » : ce qu'il faut avoir sous la main (comme un enregistrement en ligne d'hôtel ou de vol) */
        .gr-prep { padding: 16px 16px 14px; border-radius: 20px; background: #fff; color: #1d1a16; }
        .gr-prep__title { display: flex; align-items: center; justify-content: space-between; margin: 0 0 6px; font-size: 12px; font-weight: 800; letter-spacing: .1em; text-transform: uppercase; color: #8a7f6b; }
        .gr-prep__title span { display: inline-flex; align-items: center; gap: 6px; height: 26px; padding: 0 10px; border-radius: 999px; background: #fdf4ec; color: #9a4308; font-size: 12px; letter-spacing: 0; text-transform: none; }
        .gr-prep ul { display: grid; gap: 2px; margin: 0; padding: 0; list-style: none; }
        .gr-prep li { display: flex; align-items: center; gap: 12px; padding: 9px 0; border-top: 1px solid #f0eadf; }
        .gr-prep li:first-child { border-top: 0; }
        .gr-prep__ico { flex: none; width: 38px; height: 38px; border-radius: 12px; display: inline-flex; align-items: center; justify-content: center; background: #eef6ef; color: #1f6b3a; font-size: 15px; }
        .gr-prep li:nth-child(2) .gr-prep__ico { background: #fdf4ec; color: #b9500a; }
        .gr-prep li:nth-child(3) .gr-prep__ico { background: #e8f4f3; color: #1f8079; }
        .gr-prep b { display: block; font-size: 14px; line-height: 1.3; }
        .gr-prep small { display: block; margin-top: 1px; font-size: 12.5px; line-height: 1.4; color: #6b6458; }
        /* Icônes en ligne du bandeau : sans la pastille crème automatique du site */
        .gr-head p > i { width: auto; height: auto; margin: 0; padding: 0; border: 0; border-radius: 0; background: none; font-size: 13px; }
        .gr-kick i { color: #f6c38f; }
        .gr-head .ivm-g { opacity: .08 !important; }
        .gr-head .ivm-g > * { stroke: #f6c38f !important; }
        .gr-head .ivm-band { height: 6px; }
        .gr-head > :not(.ivm):not(.ivm-band):not(.nl-kita) { position: relative; z-index: 1; }
        .gr-head > .nl-kita { position: absolute; left: 0; right: 0; bottom: 0; z-index: 2; height: 8px; }
        .gr-kick { display: inline-flex; align-items: center; gap: 8px; margin: 0; padding: 5px 12px; border-radius: 999px; background: rgba(255,255,255,.12); color: #f6c38f; font-size: 11.5px; font-weight: 800; letter-spacing: .1em; text-transform: uppercase; }
        .gr-title { margin: 12px 0 0; font-family: 'Playfair Display', serif; font-size: clamp(25px, 3.2vw, 36px); font-weight: 700; line-height: 1.12; color: #fff; }
        .gr-sub { margin: 8px 0 0; max-width: 62ch; font-size: 14.5px; line-height: 1.55; color: #cfe6d7; }
        .gr-facts { display: flex; flex-wrap: wrap; gap: 6px 16px; margin: 14px 0 0; padding: 0; list-style: none; font-size: 13px; color: #e7f3ec; }
        .gr-facts li { display: inline-flex; align-items: center; gap: 7px; }
        .gr-facts i { color: #f6c38f; }

        .gr-layout { display: grid; grid-template-columns: minmax(0, 1fr) 330px; gap: 22px; align-items: start; margin-top: 20px; }
        .gr-card { background: #fff; border: 1px solid #ece5d6; border-radius: 26px; }

        /* Progression */
        .gr-steps { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 6px; padding: 8px; margin: 0 0 14px; list-style: none; }
        .gr-steps li { display: flex; align-items: center; gap: 10px; padding: 8px 10px; border-radius: 16px; color: #8a7f6b; font-size: 13px; font-weight: 700; }
        .gr-steps b { flex: none; width: 30px; height: 30px; border-radius: 999px; display: inline-flex; align-items: center; justify-content: center; background: #f1ebdf; color: #8a7f6b; font-size: 13px; }
        .gr-steps li.is-current { background: #fdf4ec; color: #9a4308; }
        .gr-steps li.is-current b { background: #b9500a; color: #fff; }
        .gr-steps li.is-done { color: #2f6a17; }
        .gr-steps li.is-done b { background: #4a8a2a; color: #fff; }
        .gr-bar { height: 4px; margin: 0 14px 0; border-radius: 4px; background: #f1ebdf; overflow: hidden; }
        .gr-bar i { display: block; height: 100%; width: 25%; background: #b9500a; transition: width .3s ease; }

        .gr-form { padding: 24px 26px 26px; }
        .gr-panel[hidden] { display: none; }
        .gr-h2 { display: flex; align-items: center; gap: 12px; margin: 0; font-family: 'Playfair Display', serif; font-size: 23px; font-weight: 700; color: #1d1a16; }
        .gr-h2 span { flex: none; width: 40px; height: 40px; border-radius: 13px; display: inline-flex; align-items: center; justify-content: center; background: #fdf4ec; color: #b9500a; font-size: 16px; }
        .gr-intro { margin: 6px 0 18px 52px; font-size: 13.5px; line-height: 1.5; color: #6b6458; }
        .gr-sec { margin-top: 20px; }
        .gr-sec > p { margin: 0 0 10px; font-size: 12px; font-weight: 800; letter-spacing: .08em; text-transform: uppercase; color: #8a7f6b; }
        .gr-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 14px; }
        .gr-grid .is-wide { grid-column: 1 / -1; }
        .gr-label { display: flex; align-items: baseline; justify-content: space-between; gap: 8px; margin: 0 0 6px; font-size: 13.5px; font-weight: 700; color: #1d1a16; }
        .gr-label em { font-style: normal; font-weight: 500; font-size: 12px; color: #8a7f6b; }
        .gr-label .req { margin-right: auto; color: #b42318; }
        .gr-h2:focus { outline: none; }
        .gr-input { width: 100%; height: 48px; padding: 0 14px; border-radius: 14px; border: 1.5px solid #e1d7c4; background: #fbf9f5; color: #1d1a16; font: inherit; font-size: 15px; outline: none; transition: border-color .15s ease, background .15s ease, box-shadow .15s ease; }
        .gr-input:hover { border-color: #cfc2a8; }
        .gr-input:focus { border-color: #b9500a; background: #fff; box-shadow: 0 0 0 4px rgba(185,80,10,.12); }
        .gr-input[aria-invalid="true"] { border-color: #c0362c; background: #fff; }
        select.gr-input { appearance: none; padding-right: 38px; background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='14' height='14' viewBox='0 0 24 24' fill='none' stroke='%238a7f6b' stroke-width='2.5' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'/%3E%3C/svg%3E"); background-repeat: no-repeat; background-position: right 14px center; }
        .gr-pair { display: grid; grid-template-columns: minmax(0, 1.3fr) minmax(0, 1fr); gap: 8px; }
        .gr-phone { display: grid; grid-template-columns: 150px minmax(0, 1fr); gap: 8px; }
        .gr-err { margin: 6px 0 0; font-size: 12.5px; font-weight: 600; color: #b42318; }
        .gr-err:empty { display: none; }
        .gr-hint { margin: 6px 0 0; font-size: 12.5px; color: #8a7f6b; }

        /* Choix en pastilles / cartes (boutons radio accessibles) */
        .gr-chips { display: flex; flex-wrap: wrap; gap: 8px; }
        .gr-chip { position: relative; }
        .gr-chip input { position: absolute; opacity: 0; pointer-events: none; }
        .gr-chip span { display: inline-flex; align-items: center; gap: 8px; height: 42px; padding: 0 15px; border-radius: 999px; border: 1.5px solid #e6dccb; background: #fff; color: #3a2f22; font-size: 14px; font-weight: 600; cursor: pointer; transition: border-color .15s ease, background .15s ease; }
        .gr-chip span i { color: #b9500a; }
        .gr-chip input:checked + span { border-color: #b9500a; background: #fdf4ec; color: #9a4308; }
        .gr-chip input:focus-visible + span { outline: 3px solid #ef7c2b; outline-offset: 2px; }
        .gr-docs { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 10px; }
        .gr-doc span { flex-direction: column; justify-content: center; gap: 8px; width: 100%; height: 96px; border-radius: 18px; text-align: center; line-height: 1.25; font-size: 13px; }
        .gr-doc span i { font-size: 22px; }

        /* Dépôt des pièces */
        .gr-uploads { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 12px; max-width: 560px; }
        .gr-up { position: relative; display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 8px; min-height: 168px; padding: 14px; border-radius: 20px; border: 2px dashed #e1d7c4; background: #fbf9f5; text-align: center; cursor: pointer; transition: border-color .15s ease, background .15s ease; }
        .gr-up:hover, .gr-up:focus-within { border-color: #b9500a; background: #fff; }
        .gr-up input[type="file"] { position: absolute; inset: 0; opacity: 0; cursor: pointer; }
        .gr-up__ico { width: 46px; height: 46px; border-radius: 14px; display: flex; align-items: center; justify-content: center; background: #fdf4ec; color: #b9500a; font-size: 18px; }
        .gr-up b { font-size: 14px; color: #1d1a16; }
        .gr-up small { font-size: 12px; color: #8a7f6b; }
        .gr-up img { position: absolute; inset: 6px; width: calc(100% - 12px); height: calc(100% - 12px); object-fit: cover; border-radius: 14px; }
        .gr-up.is-filled { border-style: solid; border-color: #4a8a2a; }
        .gr-up__ok { position: absolute; z-index: 2; right: 10px; top: 10px; display: none; align-items: center; gap: 6px; height: 28px; padding: 0 10px; border-radius: 999px; background: #fff; color: #2f6a17; font-size: 12px; font-weight: 800; }
        .gr-up.is-filled .gr-up__ok { display: inline-flex; }
        .gr-up.is-busy::after { content: attr(data-busy); position: absolute; inset: 0; display: flex; align-items: center; justify-content: center; border-radius: 18px; background: rgba(255,255,255,.85); font-size: 13px; font-weight: 700; color: #9a4308; }
        .gr-up[aria-invalid="true"] { border-color: #c0362c; }
        .gr-tips { display: flex; flex-wrap: wrap; gap: 6px 16px; margin: 12px 0 0; padding: 0; list-style: none; font-size: 12.5px; color: #6b6458; }
        .gr-tips li { display: inline-flex; align-items: center; gap: 6px; }
        .gr-tips i { color: #1f8079; }

        /* Selfie (caméra en direct, ou import) */
        .gr-selfie { display: grid; grid-template-columns: 168px minmax(0, 1fr); gap: 14px; align-items: center; margin-top: 12px; padding: 12px; border-radius: 20px; border: 1.5px solid #ece5d6; background: #fff; }
        .gr-selfie__frame { position: relative; width: 168px; aspect-ratio: 1; border-radius: 16px; overflow: hidden; background: #1d1a16; display: flex; align-items: center; justify-content: center; color: #8a7f6b; font-size: 34px; }
        .gr-selfie__frame video, .gr-selfie__frame img { position: absolute; inset: 0; width: 100%; height: 100%; object-fit: cover; }
        .gr-selfie__frame video { transform: scaleX(-1); }
        .gr-selfie__frame::after { content: ''; position: absolute; inset: 14% 22%; border: 2px dashed rgba(255,255,255,.55); border-radius: 50%; pointer-events: none; }
        .gr-selfie__frame.has-photo::after { display: none; }
        .gr-selfie b { display: block; font-size: 14.5px; }
        .gr-selfie p { margin: 4px 0 10px; font-size: 13px; color: #6b6458; }
        .gr-btns { display: flex; flex-wrap: wrap; gap: 8px; }

        /* Boutons */
        .gr-btn { display: inline-flex; align-items: center; justify-content: center; gap: 8px; height: 46px; padding: 0 20px; border: 0; border-radius: 999px; background: #b9500a; color: #fff; font: inherit; font-size: 14.5px; font-weight: 800; text-decoration: none; cursor: pointer; transition: background .15s ease; }
        .gr-btn:hover { background: #9a4308; }
        .gr-btn.is-ghost { background: #fff; color: #3a2f22; border: 1.5px solid #e1d7c4; }
        .gr-btn.is-ghost:hover { border-color: #b9500a; color: #9a4308; }
        .gr-btn.is-small { height: 38px; padding: 0 14px; font-size: 13px; }
        .gr-btn[disabled] { opacity: .6; cursor: progress; }
        .gr-nav { display: flex; align-items: center; justify-content: space-between; gap: 10px; margin-top: 26px; padding-top: 18px; border-top: 1px solid #f0eadf; }
        .gr-nav .gr-save { font-size: 12.5px; color: #8a7f6b; }

        /* Récapitulatif + signature */
        .gr-review { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 0; border: 1px solid #ece5d6; border-radius: 18px; overflow: hidden; }
        .gr-review div { padding: 10px 14px; border-bottom: 1px solid #f0eadf; }
        .gr-review div:nth-child(odd) { border-right: 1px solid #f0eadf; }
        .gr-review dt { font-size: 11.5px; font-weight: 700; letter-spacing: .04em; text-transform: uppercase; color: #8a7f6b; }
        .gr-review dd { margin: 3px 0 0; font-size: 14px; font-weight: 600; color: #1d1a16; word-break: break-word; }
        .gr-edit { border: 0; padding: 0; background: none; font: inherit; font-size: 12.5px; font-weight: 700; color: #9a4308; cursor: pointer; }
        .gr-sign { position: relative; border-radius: 18px; border: 2px dashed #d8cdb8; background: #fbf9f5; }
        .gr-sign canvas { display: block; width: 100%; height: 190px; touch-action: none; cursor: crosshair; }
        .gr-sign__ph { position: absolute; inset: 0; display: flex; align-items: center; justify-content: center; gap: 8px; color: #a89e8b; font-size: 14px; pointer-events: none; }
        .gr-sign.is-signed .gr-sign__ph { display: none; }
        .gr-sign.is-signed { border-style: solid; border-color: #4a8a2a; background: #fff; }
        .gr-check { display: flex; align-items: flex-start; gap: 10px; padding: 14px; border-radius: 16px; background: #fbf9f5; border: 1px solid #ece5d6; font-size: 14px; line-height: 1.5; cursor: pointer; }
        .gr-check input { flex: none; width: 19px; height: 19px; margin-top: 2px; accent-color: #b9500a; }
        .gr-alert { display: flex; gap: 10px; margin: 0 0 16px; padding: 12px 14px; border-radius: 14px; background: #fdeceb; border: 1px solid #f5c2bd; color: #8a241c; font-size: 14px; }
        .gr-alert.is-ok { background: #eef6ef; border-color: #cfe4d3; color: #1f5a33; }

        /* Colonne de droite */
        .gr-side { position: sticky; top: 96px; display: grid; gap: 14px; }
        .gr-stay { overflow: hidden; }
        .gr-stay__img { height: 140px; background: #e9e1d2; }
        .gr-stay__img img { width: 100%; height: 100%; object-fit: cover; }
        .gr-stay__body { padding: 16px 18px 18px; }
        .gr-stay__body small { font-size: 11.5px; font-weight: 800; letter-spacing: .08em; text-transform: uppercase; color: #1f8079; }
        .gr-stay__body h3 { margin: 4px 0 0; font-family: 'Playfair Display', serif; font-size: 19px; line-height: 1.25; }
        .gr-stay dl { display: grid; gap: 8px; margin: 14px 0 0; font-size: 13.5px; }
        .gr-stay dl div { display: flex; justify-content: space-between; gap: 10px; }
        .gr-stay dt { color: #6b6458; }
        .gr-stay dd { margin: 0; font-weight: 700; text-align: right; }
        .gr-stay__dep { margin-top: 12px; padding-top: 12px; border-top: 1px solid #f0eadf; }
        .gr-stay__dep dd { color: #9a4308; }
        .gr-why { padding: 16px 18px; }
        .gr-why b { display: flex; align-items: center; gap: 8px; font-size: 14px; }
        .gr-why b i { color: #1f8079; }
        .gr-why ul { display: grid; gap: 8px; margin: 10px 0 0; padding: 0; list-style: none; font-size: 13px; line-height: 1.5; color: #4a443a; }
        .gr-why li { display: flex; gap: 8px; }
        .gr-why li i { margin-top: 3px; color: #b9500a; }

        /* Fiche déjà envoyée */
        .gr-done { padding: 26px; }
        .gr-done__top { display: flex; align-items: center; gap: 14px; }
        .gr-done__ico { flex: none; width: 54px; height: 54px; border-radius: 16px; display: flex; align-items: center; justify-content: center; background: #eef6ef; color: #2f6a17; font-size: 22px; }
        .gr-done h2 { margin: 0; font-family: 'Playfair Display', serif; font-size: 24px; }
        .gr-done p { margin: 3px 0 0; font-size: 13.5px; color: #6b6458; }
        .gr-thumbs { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 10px; margin-top: 16px; }
        .gr-thumbs a { display: block; aspect-ratio: 4 / 3; overflow: hidden; border-radius: 14px; border: 1px solid #ece5d6; background: #fbf9f5; }
        .gr-thumbs img { width: 100%; height: 100%; object-fit: cover; }

        .gr button:focus-visible, .gr a:focus-visible, .gr .gr-input:focus-visible { outline: 3px solid #ef7c2b; outline-offset: 2px; }
        .sr-only { position: absolute; width: 1px; height: 1px; overflow: hidden; clip: rect(0 0 0 0); white-space: nowrap; }

        @media (max-width: 960px) {
            .gr-head { grid-template-columns: minmax(0, 1fr); }
            .gr-layout { grid-template-columns: minmax(0, 1fr); }
            .gr-side { position: static; order: -1; grid-template-columns: minmax(0, 1fr); }
            .gr-stay { display: grid; grid-template-columns: 120px minmax(0, 1fr); }
            .gr-stay__img { height: auto; }
            .gr-why { display: none; }
        }
        @media (max-width: 640px) {
            .gr { padding: 84px 12px 40px; }
            .gr-head { padding: 20px 16px 22px; border-radius: 22px; gap: 16px; }
            .gr-prep { padding: 12px 14px 10px; border-radius: 16px; }
            .gr-prep li { padding: 7px 0; }
            .gr-steps { grid-template-columns: repeat(4, minmax(0, 1fr)); padding: 6px; }
            .gr-steps li { flex-direction: column; gap: 4px; padding: 6px 2px; font-size: 11px; text-align: center; }
            .gr-form { padding: 18px 16px 20px; }
            .gr-intro { margin-left: 0; }
            .gr-grid, .gr-review { grid-template-columns: minmax(0, 1fr); }
            .gr-review div:nth-child(odd) { border-right: 0; }
            .gr-docs { grid-template-columns: repeat(2, minmax(0, 1fr)); }
            .gr-uploads { grid-template-columns: repeat(2, minmax(0, 1fr)); }
            .gr-selfie { grid-template-columns: minmax(0, 1fr); justify-items: center; text-align: center; }
            .gr-selfie .gr-btns { justify-content: center; }
            .gr-phone { grid-template-columns: 120px minmax(0, 1fr); }
            .gr-stay { grid-template-columns: 96px minmax(0, 1fr); }
            /* Boutons pleine largeur sous l'étape (la barre d'onglets du site occupe déjà le bas de l'écran) */
            .gr-nav .gr-save { display: none; }
            .gr-nav .gr-btn { flex: 1; }
            .gr-thumbs { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        }
        @media (prefers-reduced-motion: reduce) { .gr-bar i, .gr-up, .gr-input { transition: none; } }
    </style>
    <link rel="stylesheet" href="{{ asset('css/tw-99914b93.css') }}?v={{ @filemtime(public_path('css/tw-99914b93.css')) }}">
</head>
<body>
@include('partials.public-top-nav')

{{-- Fond à motifs ivoiriens (discrets) derrière toute la page --}}
<div class="gr-stage">
@include('partials.ivorian-motifs', ['culture' => 'national', 'band' => false])
<main class="gr">
    <header class="gr-head">
        @include('partials.ivorian-motifs', ['culture' => 'national', 'band' => false])
        {{-- Lisière kita en bas du bandeau : même frise que l'encart newsletter --}}
        <div class="nl-kita" aria-hidden="true"></div>
        <div class="gr-head__main">
            <p class="gr-kick"><i class="fas fa-passport" aria-hidden="true"></i>{{ __('Enregistrement en ligne') }}</p>
            <h1 class="gr-title">{{ $reservation->accommodation_name }}</h1>
            @if($acc?->city)
                <p class="gr-where"><i class="fas fa-location-dot" aria-hidden="true"></i>{{ $acc->city->name }}, {{ __('Côte d\'Ivoire') }}</p>
            @endif
            <p class="gr-sub">{{ __('Remplissez votre fiche voyageur maintenant : à l\'arrivée, il ne vous restera qu\'à récupérer votre clé.') }}</p>
            <p class="gr-law"><i class="fas fa-scale-balanced" aria-hidden="true"></i>{{ __('Fiche exigée de tout établissement hôtelier en Côte d\'Ivoire.') }}</p>
        </div>
        <div class="gr-prep" aria-label="{{ __('À préparer') }}">
            <p class="gr-prep__title">{{ __('À préparer') }} <span><i class="fas fa-clock" aria-hidden="true"></i>{{ __('4 min') }}</span></p>
            <ul>
                <li><span class="gr-prep__ico"><i class="fas fa-id-card" aria-hidden="true"></i></span><span><b>{{ __('Votre pièce d\'identité') }}</b><small>{{ __('CNI, passeport, permis ou carte consulaire') }}</small></span></li>
                <li><span class="gr-prep__ico"><i class="fas fa-mobile-screen" aria-hidden="true"></i></span><span><b>{{ __('Votre téléphone') }}</b><small>{{ __('Pour photographier la pièce et faire un selfie') }}</small></span></li>
                <li><span class="gr-prep__ico"><i class="fas fa-lock" aria-hidden="true"></i></span><span><b>{{ __('Rien d\'autre') }}</b><small>{{ __('Vos pièces restent privées, visibles par l\'hôtel seulement') }}</small></span></li>
            </ul>
        </div>
    </header>

    <div class="gr-layout">
        <div>
        @if($registration)
            {{-- ── Fiche déjà envoyée : récapitulatif ── --}}
            <section class="gr-card gr-done" aria-labelledby="gr-done-title">
                @if(session('success') || session('info'))
                    <div class="gr-alert is-ok" role="status"><i class="fas fa-circle-check" aria-hidden="true"></i>{{ session('success') ?? session('info') }}</div>
                @endif
                <div class="gr-done__top">
                    <span class="gr-done__ico"><i class="fas fa-check" aria-hidden="true"></i></span>
                    <div>
                        <h2 id="gr-done-title">{{ __('Fiche transmise') }}</h2>
                        <p>{{ __('Envoyée le :d — l\'établissement l\'a reçue.', ['d' => $registration->submitted_at?->format('d/m/Y à H:i')]) }}</p>
                    </div>
                </div>
                <dl class="gr-review" style="margin-top:18px">
                    <div><dt>{{ __('Voyageur') }}</dt><dd>{{ $registration->full_name }}</dd></div>
                    <div><dt>{{ __('Nationalité') }}</dt><dd>{{ $registration->nationality ?: '—' }}</dd></div>
                    <div><dt>{{ __('Pièce') }}</dt><dd>{{ $registration->labelForDocumentType() }} · {{ $registration->document_number }}</dd></div>
                    <div><dt>{{ __('Motif du voyage') }}</dt><dd>{{ $registration->labelForTravelReason() }}</dd></div>
                    <div><dt>{{ __('Arrivée') }}</dt><dd>{{ $registration->hotel_check_in_date->format('d/m/Y') }} · {{ substr((string) $registration->hotel_check_in_time, 0, 5) }}</dd></div>
                    <div><dt>{{ __('Départ') }}</dt><dd>{{ $registration->hotel_check_out_date->format('d/m/Y') }} · {{ substr((string) $registration->hotel_check_out_time, 0, 5) }}</dd></div>
                </dl>
                <div class="gr-thumbs">
                    @foreach(['recto' => __('Pièce recto'), 'verso' => __('Pièce verso'), 'selfie' => __('Selfie')] as $kind => $label)
                        @if($url = $registration->fileUrl($kind))
                            <a href="{{ $url }}" target="_blank" rel="noopener" aria-label="{{ $label }}"><img src="{{ $url }}" alt="{{ $label }}" loading="lazy"></a>
                        @endif
                    @endforeach
                </div>
                <div class="gr-nav" style="position:static;border-top:1px solid #f0eadf">
                    <span class="gr-save"><i class="fas fa-lock" aria-hidden="true"></i> {{ __('Pièces visibles uniquement par l\'établissement') }}</span>
                    @if($reservation->payment_status !== \App\Models\Reservation::PAYMENT_DEPOSIT_PAID)
                        <a href="{{ $reservation->paymentUrl() }}" class="gr-btn"><i class="fas fa-credit-card" aria-hidden="true"></i>{{ __('Payer l\'acompte') }}</a>
                    @else
                        <a href="{{ $reservation->confirmationUrl() }}" class="gr-btn">{{ __('Voir ma confirmation') }} <i class="fas fa-arrow-right" aria-hidden="true"></i></a>
                    @endif
                </div>
            </section>
        @else
            {{-- ── Formulaire en 4 étapes ── --}}
            <div class="gr-card">
                <ol class="gr-steps" aria-label="{{ __('Étapes') }}">
                    @foreach([__('Séjour'), __('Pièce d\'identité'), __('Identité'), __('Vérification')] as $i => $label)
                        <li data-step-tab="{{ $i + 1 }}"><b>{{ $i + 1 }}</b><span>{{ $label }}</span></li>
                    @endforeach
                </ol>
                <div class="gr-bar" aria-hidden="true"><i data-step-bar></i></div>

                <form id="gr-form" class="gr-form" method="POST" action="{{ $reservation->guestRegistrationUrl() }}" enctype="multipart/form-data" novalidate data-start-step="{{ $startStep }}" data-draft-key="tiv_gr_{{ $reservation->id }}">
                    @csrf
                    {{-- « signature_image » et non « signature » : ce nom est déjà le paramètre du lien signé --}}
                    <input type="file" name="signature_image" id="gr-signature-input" class="sr-only" tabindex="-1" aria-hidden="true">

                    @if($errors->any())
                        <div class="gr-alert" role="alert"><i class="fas fa-circle-exclamation" aria-hidden="true"></i>
                            <span>{{ trans_choice('Un champ est à corriger.|:count champs sont à corriger.', $errors->count(), ['count' => $errors->count()]) }} {{ __('Nous vous avons ramené à l\'étape concernée. Les photos doivent être ajoutées à nouveau.') }}</span>
                        </div>
                    @endif

                    {{-- Étape 1 : séjour --}}
                    <section class="gr-panel" data-step="1" aria-labelledby="gr-s1">
                        <h2 class="gr-h2" id="gr-s1"><span><i class="fas fa-plane-arrival" aria-hidden="true"></i></span>{{ __('Votre séjour') }}</h2>
                        <p class="gr-intro">{{ __('Dates préremplies d\'après votre réservation : ajustez l\'heure d\'arrivée prévue.') }}</p>

                        <div class="gr-sec">
                            <p>{{ __('Type de voyage') }}</p>
                            <div class="gr-chips" role="radiogroup" aria-label="{{ __('Type de voyage') }}">
                                @foreach(\App\Models\GuestRegistration::TRAVEL_TYPE_LABELS as $value => $label)
                                    <label class="gr-chip"><input type="radio" name="travel_type" value="{{ $value }}" required @checked(old('travel_type', 'domestique') === $value)><span><i class="fas {{ $value === 'international' ? 'fa-earth-africa' : 'fa-map-location-dot' }}" aria-hidden="true"></i>{{ __($label) }}</span></label>
                                @endforeach
                            </div>
                        </div>
                        <div class="gr-sec">
                            <p>{{ __('Motif du voyage') }}</p>
                            <div class="gr-chips" role="radiogroup" aria-label="{{ __('Motif du voyage') }}">
                                @foreach(\App\Models\GuestRegistration::REASON_LABELS as $value => $label)
                                    <label class="gr-chip"><input type="radio" name="travel_reason" value="{{ $value }}" required @checked(old('travel_reason') === $value)><span><i class="fas {{ $reasonIcons[$value] ?? 'fa-circle' }}" aria-hidden="true"></i>{{ __($label) }}</span></label>
                                @endforeach
                            </div>
                            <p class="gr-err" data-err-for="travel_reason">{{ $err('travel_reason') }}</p>
                        </div>

                        <div class="gr-sec gr-grid">
                            <div>
                                <label class="gr-label" for="hotel_check_in_date">{{ __('Arrivée') }} <span class="req" aria-hidden="true">*</span></label>
                                <div class="gr-pair">
                                    <input class="gr-input" type="date" id="hotel_check_in_date" name="hotel_check_in_date" required value="{{ old('hotel_check_in_date', $reservation->check_in?->format('Y-m-d')) }}" @if($err('hotel_check_in_date')) aria-invalid="true" @endif>
                                    <input class="gr-input" type="time" name="hotel_check_in_time" required value="{{ old('hotel_check_in_time', '15:00') }}" aria-label="{{ __('Heure d\'arrivée') }}">
                                </div>
                                <p class="gr-err" data-err-for="hotel_check_in_date">{{ $err('hotel_check_in_date') ?: $err('hotel_check_in_time') }}</p>
                            </div>
                            <div>
                                <label class="gr-label" for="hotel_check_out_date">{{ __('Départ') }} <span class="req" aria-hidden="true">*</span></label>
                                <div class="gr-pair">
                                    <input class="gr-input" type="date" id="hotel_check_out_date" name="hotel_check_out_date" required value="{{ old('hotel_check_out_date', $reservation->check_out?->format('Y-m-d')) }}" @if($err('hotel_check_out_date')) aria-invalid="true" @endif>
                                    <input class="gr-input" type="time" name="hotel_check_out_time" required value="{{ old('hotel_check_out_time', '11:00') }}" aria-label="{{ __('Heure de départ') }}">
                                </div>
                                <p class="gr-err" data-err-for="hotel_check_out_date">{{ $err('hotel_check_out_date') ?: $err('hotel_check_out_time') }}</p>
                            </div>
                            <div>
                                <label class="gr-label" for="coming_from">{{ __('Vous venez de') }} <em>{{ __('facultatif') }}</em></label>
                                <input class="gr-input" type="text" id="coming_from" name="coming_from" maxlength="150" value="{{ old('coming_from') }}" placeholder="{{ __('Ville, pays') }}" autocomplete="off">
                            </div>
                            <div>
                                <label class="gr-label" for="going_to">{{ __('Vous allez ensuite à') }} <em>{{ __('facultatif') }}</em></label>
                                <input class="gr-input" type="text" id="going_to" name="going_to" maxlength="150" value="{{ old('going_to') }}" placeholder="{{ __('Ville, pays') }}" autocomplete="off">
                            </div>
                        </div>
                    </section>

                    {{-- Étape 2 : pièce d'identité --}}
                    <section class="gr-panel" data-step="2" aria-labelledby="gr-s2" hidden>
                        <h2 class="gr-h2" id="gr-s2"><span><i class="fas fa-id-card" aria-hidden="true"></i></span>{{ __('Votre pièce d\'identité') }}</h2>
                        <p class="gr-intro">{{ __('Prenez la pièce en photo à plat, sans reflet. Le numéro et la date de validité sont lus automatiquement : vérifiez-les.') }}</p>

                        <div class="gr-sec">
                            <p>{{ __('Type de pièce') }}</p>
                            <div class="gr-docs" role="radiogroup" aria-label="{{ __('Type de pièce') }}">
                                @foreach(\App\Models\GuestRegistration::DOCUMENT_TYPE_LABELS as $value => $label)
                                    <label class="gr-chip gr-doc"><input type="radio" name="document_type" value="{{ $value }}" required @checked(old('document_type', 'cni') === $value)><span><i class="fas {{ $docIcons[$value] ?? 'fa-id-card' }}" aria-hidden="true"></i>{{ __($label) }}</span></label>
                                @endforeach
                            </div>
                        </div>

                        <div class="gr-sec">
                            <p>{{ __('Photos') }}</p>
                            <div class="gr-uploads">
                                <label class="gr-up" data-up="document_scan_front" @if($err('document_scan_front')) aria-invalid="true" @endif data-busy="{{ __('Préparation…') }}">
                                    <input type="file" name="document_scan_front" id="gr-front" accept="image/*" required>
                                    <span class="gr-up__ico"><i class="fas fa-id-card" aria-hidden="true"></i></span>
                                    <b>{{ __('Recto') }}</b><small>{{ __('Face avec la photo') }}</small>
                                    <span class="gr-up__ok"><i class="fas fa-check" aria-hidden="true"></i>{{ __('Ajouté') }}</span>
                                </label>
                                <label class="gr-up" data-up="document_scan_back" data-back @if($err('document_scan_back')) aria-invalid="true" @endif data-busy="{{ __('Préparation…') }}">
                                    <input type="file" name="document_scan_back" id="gr-back" accept="image/*">
                                    <span class="gr-up__ico"><i class="fas fa-rotate" aria-hidden="true"></i></span>
                                    <b>{{ __('Verso') }}</b><small>{{ __('Face arrière') }}</small>
                                    <span class="gr-up__ok"><i class="fas fa-check" aria-hidden="true"></i>{{ __('Ajouté') }}</span>
                                </label>
                            </div>
                            <p class="gr-err" data-err-for="document_scan_front">{{ $err('document_scan_front') }}</p>
                            <p class="gr-err" data-err-for="document_scan_back">{{ $err('document_scan_back') }}</p>
                            <ul class="gr-tips">
                                <li><i class="fas fa-sun" aria-hidden="true"></i>{{ __('Bonne lumière, sans flash') }}</li>
                                <li><i class="fas fa-crop-simple" aria-hidden="true"></i>{{ __('Toute la pièce dans le cadre') }}</li>
                                <li><i class="fas fa-passport" aria-hidden="true"></i>{{ __('Passeport : la page photo suffit') }}</li>
                            </ul>
                        </div>

                        <div class="gr-sec gr-grid">
                            <div>
                                <label class="gr-label" for="document_number">{{ __('Numéro de la pièce') }} <span class="req" aria-hidden="true">*</span></label>
                                <input class="gr-input" type="text" id="document_number" name="document_number" required maxlength="100" value="{{ old('document_number') }}" autocomplete="off" autocapitalize="characters" spellcheck="false" @if($err('document_number')) aria-invalid="true" @endif>
                                <p class="gr-hint" id="gr-ocr" hidden></p>
                                <p class="gr-err" data-err-for="document_number">{{ $err('document_number') }}</p>
                            </div>
                            <div>
                                <label class="gr-label" for="document_expiry_date">{{ __('Valable jusqu\'au') }} <span class="req" aria-hidden="true">*</span></label>
                                <input class="gr-input" type="date" id="document_expiry_date" name="document_expiry_date" required value="{{ old('document_expiry_date') }}" @if($err('document_expiry_date')) aria-invalid="true" @endif>
                                <p class="gr-hint" id="gr-ocr-exp" hidden></p>
                                <p class="gr-err" data-err-for="document_expiry_date">{{ $err('document_expiry_date') }}</p>
                            </div>
                        </div>

                        <div class="gr-sec">
                            <p>{{ __('Selfie') }}</p>
                            <div class="gr-selfie" data-up="selfie" @if($err('selfie')) aria-invalid="true" @endif>
                                <div class="gr-selfie__frame" id="gr-selfie-frame"><i class="fas fa-user" aria-hidden="true"></i></div>
                                <div>
                                    <b>{{ __('Une photo de votre visage') }}</b>
                                    <p>{{ __('Elle permet à l\'établissement de vérifier que la pièce est bien la vôtre.') }}</p>
                                    <div class="gr-btns">
                                        <button type="button" class="gr-btn is-small" id="gr-cam-start"><i class="fas fa-camera" aria-hidden="true"></i>{{ __('Prendre le selfie') }}</button>
                                        <button type="button" class="gr-btn is-small" id="gr-cam-snap" hidden><i class="fas fa-circle-dot" aria-hidden="true"></i>{{ __('Capturer') }}</button>
                                        <label class="gr-btn is-ghost is-small" style="position:relative;overflow:hidden">
                                            <i class="fas fa-image" aria-hidden="true"></i><span id="gr-selfie-pick">{{ __('Importer une photo') }}</span>
                                            <input type="file" name="selfie" id="gr-selfie" accept="image/*" capture="user" style="position:absolute;inset:0;opacity:0;cursor:pointer">
                                        </label>
                                    </div>
                                    <p class="gr-err" data-err-for="selfie">{{ $err('selfie') }}</p>
                                </div>
                            </div>
                        </div>
                    </section>

                    {{-- Étape 3 : identité --}}
                    <section class="gr-panel" data-step="3" aria-labelledby="gr-s3" hidden>
                        <h2 class="gr-h2" id="gr-s3"><span><i class="fas fa-user" aria-hidden="true"></i></span>{{ __('Vos informations') }}</h2>
                        <p class="gr-intro">{{ __('Comme indiqué sur votre pièce d\'identité.') }}</p>
                        <div class="gr-grid">
                            @foreach([
                                ['first_name', __('Prénom(s)'), 'given-name', $prefill['first_name'] ?? ''],
                                ['last_name', __('Nom de famille'), 'family-name', $prefill['last_name'] ?? ''],
                            ] as [$name, $label, $ac, $default])
                                <div>
                                    <label class="gr-label" for="{{ $name }}">{{ $label }} <span class="req" aria-hidden="true">*</span></label>
                                    <input class="gr-input" type="text" id="{{ $name }}" name="{{ $name }}" required maxlength="150" value="{{ old($name, $default) }}" autocomplete="{{ $ac }}" @if($err($name)) aria-invalid="true" @endif>
                                    <p class="gr-err" data-err-for="{{ $name }}">{{ $err($name) }}</p>
                                </div>
                            @endforeach
                            <div>
                                <label class="gr-label" for="nationality">{{ __('Nationalité') }} <span class="req" aria-hidden="true">*</span></label>
                                <input class="gr-input" type="text" id="nationality" name="nationality" required maxlength="80" list="gr-nationalities" value="{{ old('nationality', 'Ivoirienne') }}" autocomplete="off" @if($err('nationality')) aria-invalid="true" @endif>
                                <datalist id="gr-nationalities">@foreach($nationalities as $n)<option value="{{ $n }}">@endforeach</datalist>
                                <p class="gr-err" data-err-for="nationality">{{ $err('nationality') }}</p>
                            </div>
                            <div>
                                <label class="gr-label" for="birth_date">{{ __('Date de naissance') }} <span class="req" aria-hidden="true">*</span></label>
                                <input class="gr-input" type="date" id="birth_date" name="birth_date" required max="{{ now()->subDay()->format('Y-m-d') }}" value="{{ old('birth_date') }}" autocomplete="bday" @if($err('birth_date')) aria-invalid="true" @endif>
                                <p class="gr-err" data-err-for="birth_date">{{ $err('birth_date') }}</p>
                            </div>
                            @foreach([
                                ['birth_place', __('Lieu de naissance'), ''],
                                ['profession', __('Profession'), 'organization-title'],
                                ['father_name', __('Nom du père'), ''],
                                ['mother_name', __('Nom de la mère'), ''],
                            ] as [$name, $label, $ac])
                                <div>
                                    <label class="gr-label" for="{{ $name }}">{{ $label }} <span class="req" aria-hidden="true">*</span></label>
                                    <input class="gr-input" type="text" id="{{ $name }}" name="{{ $name }}" required maxlength="150" value="{{ old($name) }}" @if($ac) autocomplete="{{ $ac }}" @endif @if($err($name)) aria-invalid="true" @endif>
                                    <p class="gr-err" data-err-for="{{ $name }}">{{ $err($name) }}</p>
                                </div>
                            @endforeach
                            <div class="is-wide">
                                <label class="gr-label" for="home_address">{{ __('Adresse du domicile') }} <span class="req" aria-hidden="true">*</span></label>
                                <input class="gr-input" type="text" id="home_address" name="home_address" required maxlength="255" value="{{ old('home_address') }}" autocomplete="street-address" placeholder="{{ __('Rue, quartier, ville, pays') }}" @if($err('home_address')) aria-invalid="true" @endif>
                                <p class="gr-err" data-err-for="home_address">{{ $err('home_address') }}</p>
                            </div>
                            <div>
                                <label class="gr-label" for="children_count">{{ __('Enfants de moins de 15 ans avec vous') }}</label>
                                <input class="gr-input" type="number" id="children_count" name="children_count" min="0" max="20" inputmode="numeric" value="{{ old('children_count', 0) }}">
                            </div>
                        </div>
                        <div class="gr-sec">
                            <p>{{ __('Personne à prévenir en cas d\'urgence') }} <span style="text-transform:none;letter-spacing:0;font-weight:500">({{ __('facultatif') }})</span></p>
                            <div class="gr-grid">
                                <div>
                                    <label class="gr-label" for="emergency_contact_name">{{ __('Nom') }}</label>
                                    <input class="gr-input" type="text" id="emergency_contact_name" name="emergency_contact_name" maxlength="150" value="{{ old('emergency_contact_name') }}">
                                </div>
                                <div>
                                    <label class="gr-label" for="emergency_contact_phone">{{ __('Téléphone') }}</label>
                                    <input class="gr-input" type="tel" id="emergency_contact_phone" name="emergency_contact_phone" maxlength="30" inputmode="tel" value="{{ old('emergency_contact_phone') }}">
                                </div>
                            </div>
                        </div>
                    </section>

                    {{-- Étape 4 : contact, vérification, signature --}}
                    <section class="gr-panel" data-step="4" aria-labelledby="gr-s4" hidden>
                        <h2 class="gr-h2" id="gr-s4"><span><i class="fas fa-file-signature" aria-hidden="true"></i></span>{{ __('Vérification et signature') }}</h2>
                        <p class="gr-intro">{{ __('Relisez vos informations, puis signez avec le doigt ou la souris.') }}</p>

                        <div class="gr-grid">
                            <div>
                                <label class="gr-label" for="phone_number">{{ __('Téléphone') }} <span class="req" aria-hidden="true">*</span></label>
                                <div class="gr-phone">
                                    <select class="gr-input" name="phone_country_code" aria-label="{{ __('Indicatif') }}" required>
                                        @foreach($countryCodes as $code => $country)
                                            <option value="{{ $code }}" @selected(old('phone_country_code', '+225') === $code)>{{ $code }} {{ $country }}</option>
                                        @endforeach
                                    </select>
                                    <input class="gr-input" type="tel" id="phone_number" name="phone_number" required maxlength="30" inputmode="tel" autocomplete="tel-national" value="{{ old('phone_number', $prefill['phone_number'] ?? '') }}" @if($err('phone_number')) aria-invalid="true" @endif>
                                </div>
                                <p class="gr-err" data-err-for="phone_number">{{ $err('phone_number') }}</p>
                            </div>
                            <div>
                                <label class="gr-label" for="gr-email">{{ __('E-mail') }} <em>{{ __('facultatif') }}</em></label>
                                <input class="gr-input" type="email" id="gr-email" name="email" maxlength="255" inputmode="email" autocomplete="email" value="{{ old('email', $prefill['email'] ?? '') }}" @if($err('email')) aria-invalid="true" @endif>
                                <p class="gr-err" data-err-for="email">{{ $err('email') }}</p>
                            </div>
                        </div>

                        <div class="gr-sec">
                            <p style="display:flex;justify-content:space-between;align-items:center">{{ __('Récapitulatif') }} <button type="button" class="gr-edit" data-goto="3">{{ __('Modifier') }}</button></p>
                            <dl class="gr-review" id="gr-review"></dl>
                        </div>

                        <div class="gr-sec">
                            <p style="display:flex;justify-content:space-between;align-items:center">{{ __('Signature') }} <button type="button" class="gr-edit" id="gr-sign-clear">{{ __('Effacer') }}</button></p>
                            <div class="gr-sign" id="gr-sign">
                                <canvas id="gr-sign-pad" aria-label="{{ __('Zone de signature') }}" role="img"></canvas>
                                <span class="gr-sign__ph"><i class="fas fa-pen-nib" aria-hidden="true"></i>{{ __('Signez ici') }}</span>
                            </div>
                            <p class="gr-err" data-err-for="signature_image">{{ $err('signature_image') }}</p>
                        </div>

                        <div class="gr-sec">
                            <label class="gr-check">
                                <input type="checkbox" name="data_confirmed" value="1" required @checked(old('data_confirmed'))>
                                <span>{{ __('Je certifie l\'exactitude de ces informations et j\'accepte qu\'elles soient transmises à l\'établissement pour mon enregistrement.') }}</span>
                            </label>
                            <p class="gr-err" data-err-for="data_confirmed">{{ $err('data_confirmed') }}</p>
                        </div>
                    </section>

                    <div class="gr-nav">
                        <button type="button" class="gr-btn is-ghost" data-back hidden><i class="fas fa-arrow-left" aria-hidden="true"></i>{{ __('Retour') }}</button>
                        <span class="gr-save" data-saved></span>
                        <button type="button" class="gr-btn" data-next>{{ __('Continuer') }} <i class="fas fa-arrow-right" aria-hidden="true"></i></button>
                        <button type="button" class="gr-btn" data-submit hidden><i class="fas fa-paper-plane" aria-hidden="true"></i>{{ __('Envoyer ma fiche') }}</button>
                    </div>
                </form>
            </div>
        @endif
        </div>

        {{-- ── Colonne : séjour et confiance ── --}}
        <aside class="gr-side" aria-label="{{ __('Votre réservation') }}">
            <div class="gr-card gr-stay">
                <div class="gr-stay__img">@if($accImg)<img src="{{ $accImg }}" alt="" loading="lazy">@endif</div>
                <div class="gr-stay__body">
                    <small>{{ __('Votre réservation') }} · {{ $reservation->reference }}</small>
                    <h3>{{ $reservation->accommodation_name }}</h3>
                    <dl>
                        <div><dt>{{ __('Arrivée') }}</dt><dd>{{ $reservation->check_in?->translatedFormat('D d M Y') }}</dd></div>
                        <div><dt>{{ __('Départ') }}</dt><dd>{{ $reservation->check_out?->translatedFormat('D d M Y') }}</dd></div>
                        <div><dt>{{ __('Séjour') }}</dt><dd>{{ trans_choice(':count nuit|:count nuits', $nights, ['count' => $nights]) }} · {{ $reservation->guests_count }} {{ __('pers.') }}</dd></div>
                        @if($reservation->room_name)<div><dt>{{ __('Chambre') }}</dt><dd>{{ $reservation->room_name }}</dd></div>@endif
                        @if($reservation->payment_status !== \App\Models\Reservation::PAYMENT_DEPOSIT_PAID && (int) $reservation->deposit_amount_xof > 0)
                            <div class="gr-stay__dep"><dt>{{ __('Acompte à régler ensuite') }}</dt><dd>{{ number_format((int) $reservation->deposit_amount_xof, 0, ',', ' ') }} FCFA</dd></div>
                        @endif
                    </dl>
                </div>
            </div>
            <div class="gr-card gr-why">
                <b><i class="fas fa-shield-halved" aria-hidden="true"></i>{{ __('Vos données sont protégées') }}</b>
                <ul>
                    <li><i class="fas fa-lock" aria-hidden="true"></i>{{ __('Pièces et selfie rangés dans un espace privé, jamais accessibles par un lien public.') }}</li>
                    <li><i class="fas fa-user-check" aria-hidden="true"></i>{{ __('Consultables uniquement par l\'établissement et notre équipe, via des liens temporaires.') }}</li>
                    <li><i class="fas fa-scale-balanced" aria-hidden="true"></i>{{ __('Collecte limitée à l\'enregistrement hôtelier (loi n° 2013-450 sur les données personnelles).') }}</li>
                </ul>
            </div>
        </aside>
    </div>
</main>
</div>

@unless($registration)
<script src="https://cdnjs.cloudflare.com/ajax/libs/tesseract.js/5.1.1/tesseract.min.js" defer></script>
<script>
(function () {
    var form = document.getElementById('gr-form');
    if (!form) return;
    var T = {{ \Illuminate\Support\Js::from([
        'required' => __('Ce champ est obligatoire.'),
        'choose' => __('Faites un choix.'),
        'front' => __('Ajoutez le recto de votre pièce.'),
        'back' => __('Ajoutez le verso de votre pièce.'),
        'selfie' => __('Ajoutez un selfie.'),
        'sign' => __('Signez dans le cadre.'),
        'confirm' => __('Cochez la case pour certifier vos informations.'),
        'expired' => __('Votre pièce doit être valide jusqu\'à la fin du séjour.'),
        'email' => __('Adresse e-mail invalide.'),
        'saved' => __('Brouillon enregistré sur cet appareil'),
        'ocrRun' => __('Lecture du numéro…'),
        'ocrOk' => __('Numéro lu automatiquement : vérifiez-le.'),
        'ocrKo' => __('Numéro non lu : saisissez-le.'),
        'expOk' => __('Date lue sur votre pièce : vérifiez-la.'),
        'expKo' => __('Date non lue : saisissez-la (elle figure sur la pièce).'),
        'camErr' => __('Caméra indisponible : importez une photo.'),
        'retake' => __('Reprendre'),
        'sending' => __('Envoi en cours…'),
        'imgErr' => __('Cette image n\'a pas pu être lue. Essayez une autre photo.'),
    ]) }};
    var panels = Array.prototype.slice.call(form.querySelectorAll('[data-step]'));
    var tabs = document.querySelectorAll('[data-step-tab]'), bar = document.querySelector('[data-step-bar]');
    var btnBack = form.querySelector('[data-back]'), btnNext = form.querySelector('[data-next]'), btnSubmit = form.querySelector('[data-submit]');
    var step = parseInt(form.dataset.startStep || '1', 10);
    var draftKey = form.dataset.draftKey;
    var files = {}; // champs fichier préparés (compressés)

    // ── Étapes ──
    function show(n) {
        step = Math.max(1, Math.min(4, n));
        panels.forEach(function (p) { p.hidden = +p.dataset.step !== step; });
        tabs.forEach(function (t) { var i = +t.dataset.stepTab; t.classList.toggle('is-current', i === step); t.classList.toggle('is-done', i < step); if (i === step) t.setAttribute('aria-current', 'step'); else t.removeAttribute('aria-current'); });
        bar.style.width = (step * 25) + '%';
        btnBack.hidden = step === 1; btnNext.hidden = step === 4; btnSubmit.hidden = step !== 4;
        if (step === 2) loadOcr();
        if (step === 4) { review(); sizePad(); }
        var h = panels[step - 1].querySelector('h2'); if (h) { h.setAttribute('tabindex', '-1'); h.focus({ preventScroll: true }); }
        window.scrollTo({ top: form.closest('.gr-card').getBoundingClientRect().top + window.scrollY - 90, behavior: 'smooth' });
    }

    // ── Erreurs en ligne ──
    function setErr(name, msg) {
        var p = form.querySelector('[data-err-for="' + name + '"]'); if (p) p.textContent = msg || '';
        var f = form.elements[name]; var holder = form.querySelector('[data-up="' + name + '"]');
        if (holder) { if (msg) holder.setAttribute('aria-invalid', 'true'); else holder.removeAttribute('aria-invalid'); }
        else if (f && f.setAttribute && f.type !== 'radio') { if (msg) f.setAttribute('aria-invalid', 'true'); else f.removeAttribute('aria-invalid'); }
    }
    function isPassport() { var c = form.querySelector('input[name="document_type"]:checked'); return c && c.value === 'passeport'; }

    function validate(n) {
        var first = null, panel = panels[n - 1];
        function bad(name, msg, el) { setErr(name, msg); if (!first) first = el || form.elements[name]; }
        panel.querySelectorAll('input[required], select[required]').forEach(function (el) {
            if (el.type === 'file' || el.type === 'checkbox') return;
            if (el.type === 'radio') { if (!form.querySelector('input[name="' + el.name + '"]:checked')) bad(el.name, T.choose, el); return; }
            if (!el.value.trim()) bad(el.name, T.required, el); else if (!el.checkValidity()) bad(el.name, el.validationMessage, el); else setErr(el.name, '');
        });
        if (n === 2) {
            if (!files.document_scan_front) bad('document_scan_front', T.front, form.querySelector('[data-up="document_scan_front"]'));
            else setErr('document_scan_front', '');
            if (!isPassport() && !files.document_scan_back) bad('document_scan_back', T.back, form.querySelector('[data-up="document_scan_back"]'));
            else setErr('document_scan_back', '');
            if (!files.selfie) bad('selfie', T.selfie, form.querySelector('[data-up="selfie"]')); else setErr('selfie', '');
            var exp = form.elements.document_expiry_date.value, out = form.elements.hotel_check_out_date.value;
            if (exp && out && exp < out) bad('document_expiry_date', T.expired);
        }
        if (n === 4) {
            var em = form.elements.email; if (em.value && !em.checkValidity()) bad('email', T.email, em);
            if (!signed) bad('signature_image', T.sign, document.getElementById('gr-sign')); else setErr('signature_image', '');
            if (!form.elements.data_confirmed.checked) bad('data_confirmed', T.confirm, form.elements.data_confirmed); else setErr('data_confirmed', '');
        }
        if (first) { (first.focus ? first : first.querySelector('input, button'))?.focus({ preventScroll: false }); first.scrollIntoView && first.scrollIntoView({ block: 'center', behavior: 'smooth' }); }
        return !first;
    }
    form.addEventListener('input', function (e) { if (e.target.name) setErr(e.target.name, ''); saveDraft(); });
    form.addEventListener('change', function (e) { if (e.target.name) setErr(e.target.name, ''); if (e.target.name === 'document_type') syncBack(); saveDraft(); });

    btnNext.addEventListener('click', function () { if (validate(step)) show(step + 1); });
    btnBack.addEventListener('click', function () { show(step - 1); });
    form.querySelectorAll('[data-goto]').forEach(function (b) { b.addEventListener('click', function () { show(+b.dataset.goto); }); });

    // Passeport : pas de verso demandé.
    function syncBack() { var tile = form.querySelector('[data-up="document_scan_back"]'); tile.hidden = isPassport(); if (isPassport()) setErr('document_scan_back', ''); }

    // ── Brouillon (champs texte seulement, effacé à l'envoi et à la fermeture de l'onglet) ──
    var savedEl = form.querySelector('[data-saved]'), saveT = null;
    function saveDraft() {
        clearTimeout(saveT);
        saveT = setTimeout(function () {
            var data = {};
            Array.prototype.forEach.call(form.elements, function (el) {
                if (!el.name || el.type === 'file' || el.type === 'hidden' || el.name === '_token') return;
                if (el.type === 'radio' || el.type === 'checkbox') { if (el.checked) data[el.name] = el.value; return; }
                data[el.name] = el.value;
            });
            try { sessionStorage.setItem(draftKey, JSON.stringify(data)); savedEl.innerHTML = '<i class="fas fa-cloud" aria-hidden="true"></i> ' + T.saved; } catch (e) {}
        }, 400);
    }
    (function restore() {
        if (form.querySelector('.gr-alert')) return; // après une erreur serveur, les valeurs renvoyées priment
        var data = null; try { data = JSON.parse(sessionStorage.getItem(draftKey) || 'null'); } catch (e) {}
        if (!data) return;
        Object.keys(data).forEach(function (k) {
            var els = form.querySelectorAll('[name="' + k + '"]');
            els.forEach(function (el) { if (el.type === 'radio' || el.type === 'checkbox') el.checked = el.value === data[k]; else el.value = data[k]; });
        });
    })();

    // ── Photos : réduites dans le navigateur (2000 px, JPEG) avant l'envoi — plus rapide, et les photos
    //    iPhone (HEIC) sont converties quand le navigateur sait les lire. ──
    function prepare(file) {
        return new Promise(function (resolve, reject) {
            var url = URL.createObjectURL(file), img = new Image();
            img.onload = function () {
                var max = 2000, w = img.naturalWidth, h = img.naturalHeight, r = Math.min(1, max / Math.max(w, h));
                var c = document.createElement('canvas'); c.width = Math.round(w * r); c.height = Math.round(h * r);
                c.getContext('2d').drawImage(img, 0, 0, c.width, c.height);
                URL.revokeObjectURL(url);
                c.toBlob(function (b) { b ? resolve({ file: new File([b], (file.name || 'photo').replace(/\.[^.]+$/, '') + '.jpg', { type: 'image/jpeg' }), preview: c.toDataURL('image/jpeg', .6) }) : reject(); }, 'image/jpeg', .85);
            };
            img.onerror = function () { URL.revokeObjectURL(url); reject(); };
            img.src = url;
        });
    }
    function attach(input, name, tile, after) {
        input.addEventListener('change', function () {
            var f = input.files && input.files[0]; if (!f) return;
            tile.classList.add('is-busy');
            prepare(f).then(function (res) {
                files[name] = res.file;
                var dt = new DataTransfer(); dt.items.add(res.file); input.files = dt.files;
                setErr(name, '');
                after(res.preview);
            }).catch(function () {
                // Image illisible dans le navigateur : on garde le fichier d'origine, le serveur tranchera.
                if (/^image\/(jpeg|png|webp)$/.test(f.type)) { files[name] = f; after(URL.createObjectURL(f)); }
                else { input.value = ''; setErr(name, T.imgErr); }
            }).finally(function () { tile.classList.remove('is-busy'); });
        });
    }
    form.querySelectorAll('.gr-up').forEach(function (tile) {
        var input = tile.querySelector('input[type="file"]'), name = tile.dataset.up;
        attach(input, name, tile, function (src) {
            var img = tile.querySelector('img') || tile.appendChild(document.createElement('img'));
            img.src = src; img.alt = ''; tile.classList.add('is-filled');
            // Recto puis verso : la bande MRZ des cartes d'identité est souvent au dos.
            if (name === 'document_scan_front' || name === 'document_scan_back') readDocument(files[name]);
        });
    });

    // ── Selfie : caméra frontale en direct, ou import ──
    var frame = document.getElementById('gr-selfie-frame'), selfieIn = document.getElementById('gr-selfie');
    var camStart = document.getElementById('gr-cam-start'), camSnap = document.getElementById('gr-cam-snap'), stream = null, video = null;
    function selfieDone(src) {
        if (video) { video.remove(); video = null; }
        var img = frame.querySelector('img') || frame.appendChild(document.createElement('img'));
        img.src = src; img.alt = ''; frame.classList.add('has-photo');
        camSnap.hidden = true; camStart.hidden = false; camStart.lastChild.textContent = T.retake;
    }
    attach(selfieIn, 'selfie', frame.parentElement, selfieDone);
    camStart.addEventListener('click', async function () {
        if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) { setErr('selfie', T.camErr); return; }
        try {
            stream = await navigator.mediaDevices.getUserMedia({ video: { facingMode: 'user', width: { ideal: 1280 } }, audio: false });
            frame.querySelector('img')?.remove(); frame.classList.remove('has-photo');
            video = document.createElement('video'); video.autoplay = true; video.playsInline = true; video.muted = true; video.srcObject = stream;
            frame.appendChild(video); camStart.hidden = true; camSnap.hidden = false; setErr('selfie', '');
        } catch (e) { setErr('selfie', T.camErr); }
    });
    camSnap.addEventListener('click', function () {
        if (!video || !video.videoWidth) return;
        var c = document.createElement('canvas'), s = Math.min(video.videoWidth, video.videoHeight);
        c.width = c.height = Math.min(s, 1200);
        var ctx = c.getContext('2d'); ctx.translate(c.width, 0); ctx.scale(-1, 1);
        ctx.drawImage(video, (video.videoWidth - s) / 2, (video.videoHeight - s) / 2, s, s, 0, 0, c.width, c.height);
        stream.getTracks().forEach(function (t) { t.stop(); });
        c.toBlob(function (b) {
            var f = new File([b], 'selfie.jpg', { type: 'image/jpeg' }), dt = new DataTransfer(); dt.items.add(f);
            selfieIn.files = dt.files; files.selfie = f; setErr('selfie', '');
            selfieDone(c.toDataURL('image/jpeg', .7));
        }, 'image/jpeg', .88);
    });

@include('reservations.partials.guest-document-reader')

    // ── Récapitulatif ──
    function txt(name) { var el = form.elements[name]; if (!el) return ''; if (el.length && el[0] && el[0].type === 'radio') { var c = form.querySelector('input[name="' + name + '"]:checked'); return c ? c.parentElement.textContent.trim() : ''; } if (el.tagName === 'SELECT') return el.options[el.selectedIndex]?.text || ''; return el.value; }
    function fmtDate(v) { if (!v) return ''; var p = v.split('-'); return p.length === 3 ? p[2] + '/' + p[1] + '/' + p[0] : v; }
    function review() {
        var rows = [
            [{{ \Illuminate\Support\Js::from(__('Voyageur')) }}, txt('first_name') + ' ' + txt('last_name')],
            [{{ \Illuminate\Support\Js::from(__('Nationalité')) }}, txt('nationality')],
            [{{ \Illuminate\Support\Js::from(__('Naissance')) }}, fmtDate(txt('birth_date')) + (txt('birth_place') ? ' · ' + txt('birth_place') : '')],
            [{{ \Illuminate\Support\Js::from(__('Profession')) }}, txt('profession')],
            [{{ \Illuminate\Support\Js::from(__('Pièce')) }}, txt('document_type') + ' · ' + txt('document_number')],
            [{{ \Illuminate\Support\Js::from(__('Valable jusqu\'au')) }}, fmtDate(txt('document_expiry_date'))],
            [{{ \Illuminate\Support\Js::from(__('Arrivée')) }}, fmtDate(txt('hotel_check_in_date')) + ' · ' + txt('hotel_check_in_time')],
            [{{ \Illuminate\Support\Js::from(__('Départ')) }}, fmtDate(txt('hotel_check_out_date')) + ' · ' + txt('hotel_check_out_time')],
            [{{ \Illuminate\Support\Js::from(__('Motif')) }}, txt('travel_reason')],
            [{{ \Illuminate\Support\Js::from(__('Adresse')) }}, txt('home_address')],
        ];
        var dl = document.getElementById('gr-review'); dl.innerHTML = '';
        rows.forEach(function (r) { var d = document.createElement('div'), dt = document.createElement('dt'), dd = document.createElement('dd'); dt.textContent = r[0]; dd.textContent = (r[1] || '').trim() || '—'; d.append(dt, dd); dl.appendChild(d); });
    }

    // ── Signature (pointeur : souris, doigt, stylet), nette sur écran haute densité ──
    var wrap = document.getElementById('gr-sign'), pad = document.getElementById('gr-sign-pad'), pctx = pad.getContext('2d'), drawing = false, signed = false, last = null;
    function sizePad() {
        var r = pad.getBoundingClientRect(); if (!r.width) return;
        var dpr = window.devicePixelRatio || 1, keep = signed ? pad.toDataURL() : null;
        pad.width = Math.round(r.width * dpr); pad.height = Math.round(r.height * dpr);
        pctx.setTransform(dpr, 0, 0, dpr, 0, 0); pctx.lineWidth = 2.4; pctx.lineCap = 'round'; pctx.lineJoin = 'round'; pctx.strokeStyle = '#1d1a16';
        if (keep) { var im = new Image(); im.onload = function () { pctx.drawImage(im, 0, 0, r.width, r.height); }; im.src = keep; }
    }
    function pos(e) { var r = pad.getBoundingClientRect(); return { x: e.clientX - r.left, y: e.clientY - r.top }; }
    pad.addEventListener('pointerdown', function (e) { drawing = true; last = pos(e); pad.setPointerCapture(e.pointerId); pctx.beginPath(); pctx.arc(last.x, last.y, 1.1, 0, Math.PI * 2); pctx.fill(); e.preventDefault(); });
    pad.addEventListener('pointermove', function (e) { if (!drawing) return; var p = pos(e); pctx.beginPath(); pctx.moveTo(last.x, last.y); pctx.lineTo(p.x, p.y); pctx.stroke(); last = p; if (!signed) { signed = true; wrap.classList.add('is-signed'); setErr('signature_image', ''); } });
    ['pointerup', 'pointercancel', 'pointerleave'].forEach(function (ev) { pad.addEventListener(ev, function () { drawing = false; }); });
    document.getElementById('gr-sign-clear').addEventListener('click', function () { pctx.clearRect(0, 0, pad.width, pad.height); signed = false; wrap.classList.remove('is-signed'); });
    window.addEventListener('resize', function () { if (step === 4) sizePad(); });

    // ── Envoi ──
    btnSubmit.addEventListener('click', function () {
        for (var n = 1; n <= 4; n++) { if (!validate(n)) { show(n); validate(n); return; } }
        btnSubmit.disabled = true; btnSubmit.lastChild.textContent = ' ' + T.sending;
        // Signature sur fond blanc (lisible à l'impression).
        var out = document.createElement('canvas'); out.width = pad.width; out.height = pad.height;
        var o = out.getContext('2d'); o.fillStyle = '#fff'; o.fillRect(0, 0, out.width, out.height); o.drawImage(pad, 0, 0);
        out.toBlob(function (b) {
            var dt = new DataTransfer(); dt.items.add(new File([b], 'signature.png', { type: 'image/png' }));
            document.getElementById('gr-signature-input').files = dt.files;
            try { sessionStorage.removeItem(draftKey); } catch (e) {}
            form.submit();
        }, 'image/png');
    });

    syncBack();
    show(step);
})();
</script>
@endunless

@include('partials.homepage-footer')
</body>
</html>
