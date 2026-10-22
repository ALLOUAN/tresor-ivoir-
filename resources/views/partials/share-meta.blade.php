{{--
    Aperçu de lien partagé (Facebook, WhatsApp, LinkedIn, X…) + URL canonique.
    Paramètres : title, description, image (URL /storage/…, chemin du disque public ou URL absolue),
    url (défaut : URL courante sans paramètres), type (défaut : website), alt.
--}}
@php
    $smBrand = $siteBrand ?? \App\Models\SiteSetting::branding();
    $smCover = is_array($image ?? null) ? (collect($image)->filter()->first() ?? null) : ($image ?? null);
    $smCover = is_string($smCover) ? trim($smCover) : null;
    $smImage = null;
    if ($smCover && preg_match('#^https?://#i', $smCover)) {
        $smImage = $smCover;
    } elseif ($smCover) {
        $smRel = str_starts_with($smCover, '/storage/')
            ? ltrim(substr(parse_url($smCover, PHP_URL_PATH) ?: '', strlen('/storage/')), '/')
            : ltrim($smCover, '/');
        $smAbs = \Illuminate\Support\Facades\Storage::disk('public')->path($smRel);
        if (is_file($smAbs)) {
            $smImage = url(route('img.og', ['path' => $smRel.'.jpg'], false)).'?v='.filemtime($smAbs);
        } elseif (is_file(public_path($smRel))) {
            $smImage = url($smRel);
        }
    }
    if (! $smImage && ! empty($smBrand['logo_url'])) {
        $smImage = url($smBrand['logo_url']);
    }
    $smTitle = trim((string) ($title ?? $smBrand['site_name']));
    $smDesc = \Illuminate\Support\Str::limit(trim(preg_replace('/\s+/', ' ', strip_tags((string) ($description ?? '')))), 200);
    $smUrl = $url ?? url()->current();
@endphp
<link rel="canonical" href="{{ $smUrl }}">
@if($smDesc !== '')
<meta name="description" content="{{ $smDesc }}">
@endif
<meta property="og:type" content="{{ $type ?? 'website' }}">
<meta property="og:site_name" content="{{ $smBrand['site_name'] }}">
<meta property="og:locale" content="fr_FR">
<meta property="og:url" content="{{ $smUrl }}">
<meta property="og:title" content="{{ $smTitle }}">
@if($smDesc !== '')
<meta property="og:description" content="{{ $smDesc }}">
@endif
@if($smImage)
<meta property="og:image" content="{{ $smImage }}">
<meta property="og:image:secure_url" content="{{ $smImage }}">
@if(str_contains($smImage, '/img/og/'))
<meta property="og:image:type" content="image/jpeg">
<meta property="og:image:width" content="1200">
<meta property="og:image:height" content="630">
@endif
<meta property="og:image:alt" content="{{ $alt ?? $smTitle }}">
@endif
<meta name="twitter:card" content="{{ $smImage ? 'summary_large_image' : 'summary' }}">
<meta name="twitter:title" content="{{ $smTitle }}">
@if($smDesc !== '')
<meta name="twitter:description" content="{{ $smDesc }}">
@endif
@if($smImage)
<meta name="twitter:image" content="{{ $smImage }}">
@endif
