@php
    // E-mail transactionnel : tableaux + styles en ligne (Gmail, Outlook, Apple Mail), pas d'image de fond ni de SVG.
    $site = $branding['site_name'] ?? config('app.name');
    $logo = ! empty($branding['logo_url']) ? url($branding['logo_url']) : null;
    $phone = $branding['contact']['phone_1'] ?? null;
    $mail = $branding['contact']['email_primary'] ?? null;
    $spaced = trim(chunk_split($code, 3, ' '));
@endphp
<!DOCTYPE html>
<html lang="fr" xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="color-scheme" content="light">
    <meta name="supported-color-schemes" content="light">
    <title>{{ __('Votre code de vérification') }}</title>
    <style>
        @media (max-width: 600px) {
            .tiv-pad { padding-left: 20px !important; padding-right: 20px !important; }
            .tiv-h1 { font-size: 24px !important; }
            .tiv-code { font-size: 34px !important; letter-spacing: 6px !important; }
        }
    </style>
</head>
<body style="margin:0; padding:0; background:#f6f3ed; -webkit-text-size-adjust:100%;">
    {{-- Aperçu affiché dans la liste des e-mails --}}
    <div style="display:none; max-height:0; overflow:hidden; mso-hide:all; font-size:1px; line-height:1px; color:#f6f3ed;">
        {{ __('Votre code : :code — valable :n minutes.', ['code' => $code, 'n' => $minutes]) }}&#8204;&nbsp;&#8204;&nbsp;&#8204;&nbsp;&#8204;&nbsp;&#8204;&nbsp;&#8204;&nbsp;
    </div>

    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background:#f6f3ed;">
        <tr>
            <td align="center" style="padding:28px 12px 36px;">
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="max-width:560px;">

                    {{-- Marque --}}
                    <tr>
                        <td align="center" style="padding:0 0 18px;">
                            <table role="presentation" cellpadding="0" cellspacing="0" border="0">
                                <tr>
                                    @if($logo)
                                        <td style="padding-right:10px;"><img src="{{ $logo }}" width="44" height="44" alt="" style="display:block; width:44px; height:44px; border-radius:22px; border:0;"></td>
                                    @endif
                                    <td style="font-family:Georgia,'Times New Roman',serif; font-size:20px; font-weight:bold; color:#1d1a16;">{{ $site }}</td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    {{-- Carte --}}
                    <tr>
                        <td style="background:#ffffff; border:1px solid #e8e1d3; border-radius:24px; overflow:hidden;">
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">

                                {{-- En-tête vert --}}
                                <tr>
                                    <td class="tiv-pad" align="center" style="background:#123a25; border-radius:24px 24px 0 0; padding:30px 36px 26px;">
                                        <p style="margin:0; font-family:Arial,Helvetica,sans-serif; font-size:12px; font-weight:bold; letter-spacing:2px; text-transform:uppercase; color:#f6c38f;">{{ __('Vérification de votre e-mail') }}</p>
                                        <h1 class="tiv-h1" style="margin:12px 0 0; font-family:Georgia,'Times New Roman',serif; font-size:28px; line-height:1.2; font-weight:bold; color:#ffffff;">
                                            {{ __('Bonjour :name,', ['name' => $name ?: __('et bienvenue')]) }}
                                        </h1>
                                        <p style="margin:10px 0 0; font-family:Arial,Helvetica,sans-serif; font-size:15px; line-height:1.55; color:#cfe6d7;">{{ __('Plus qu\'une étape pour activer votre compte :site.', ['site' => $site]) }}</p>
                                    </td>
                                </tr>
                                {{-- Lisière kita (aplats de couleurs) --}}
                                <tr>
                                    <td style="font-size:0; line-height:0;">
                                        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0"><tr>
                                            @foreach(['#d9650f', '#e0a12e', '#1b6b3a', '#f3ead8', '#1f8079', '#d9650f', '#e0a12e', '#1b6b3a', '#f3ead8', '#1f8079'] as $kc)
                                                <td height="6" style="height:6px; background:{{ $kc }}; font-size:0; line-height:0;">&nbsp;</td>
                                            @endforeach
                                        </tr></table>
                                    </td>
                                </tr>

                                {{-- Code --}}
                                <tr>
                                    <td class="tiv-pad" style="padding:30px 36px 8px; font-family:Arial,Helvetica,sans-serif;">
                                        <p style="margin:0; text-align:center; font-size:15px; line-height:1.55; color:#4a443a;">{{ __('Saisissez ce code sur la page de vérification :') }}</p>
                                        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="margin:18px 0 0;">
                                            <tr>
                                                <td align="center" style="background:#fdf4ec; border:1.5px dashed #e8b98d; border-radius:18px; padding:22px 10px;">
                                                    <p class="tiv-code" style="margin:0; font-family:'Courier New',Courier,monospace; font-size:40px; line-height:1; font-weight:bold; letter-spacing:10px; color:#9a4308;">{{ $spaced }}</p>
                                                    <p style="margin:12px 0 0; font-size:13px; color:#8a7f6b;">{{ __('Valable :n minutes', ['n' => $minutes]) }}</p>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>

                                {{-- Bouton --}}
                                <tr>
                                    <td align="center" class="tiv-pad" style="padding:22px 36px 8px;">
                                        <table role="presentation" cellpadding="0" cellspacing="0" border="0">
                                            <tr>
                                                <td align="center" bgcolor="#b9500a" style="border-radius:999px;">
                                                    <a href="{{ $verifyUrl }}" target="_blank" style="display:inline-block; padding:15px 30px; font-family:Arial,Helvetica,sans-serif; font-size:15px; font-weight:bold; color:#ffffff; text-decoration:none; border-radius:999px;">{{ __('Ouvrir la page de vérification') }}</a>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>

                                {{-- Sécurité --}}
                                <tr>
                                    <td class="tiv-pad" style="padding:22px 36px 30px; font-family:Arial,Helvetica,sans-serif;">
                                        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
                                            <tr>
                                                <td style="background:#fbf9f5; border:1px solid #ece5d6; border-radius:16px; padding:14px 16px; font-size:13.5px; line-height:1.55; color:#4a443a;">
                                                    <strong style="color:#1d1a16;">{{ __('Pour votre sécurité') }}</strong><br>
                                                    {{ __('Ne communiquez jamais ce code : notre équipe ne vous le demandera pas.') }}
                                                    {{ __('Si vous n\'êtes pas à l\'origine de cette inscription, ignorez simplement cet e-mail.') }}
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    {{-- Pied --}}
                    <tr>
                        <td align="center" style="padding:22px 20px 0; font-family:Arial,Helvetica,sans-serif; font-size:12.5px; line-height:1.6; color:#8a7f6b;">
                            <strong style="color:#4a443a;">{{ $site }}</strong>@if(! empty($branding['site_slogan'])) · {{ $branding['site_slogan'] }}@endif<br>
                            @if($phone){{ $phone }}@endif @if($phone && $mail) · @endif @if($mail)<a href="mailto:{{ $mail }}" style="color:#9a4308; text-decoration:none;">{{ $mail }}</a>@endif
                            @if($phone || $mail)<br>@endif
                            <a href="{{ url('/') }}" style="color:#9a4308; text-decoration:none;">{{ preg_replace('#^https?://#', '', rtrim(url('/'), '/')) }}</a><br>
                            <span style="color:#a89e8b;">{{ __('E-mail automatique envoyé suite à la création de votre compte.') }}</span>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
