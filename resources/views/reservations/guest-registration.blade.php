<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Fiche d'enregistrement — {{ $reservation->accommodation_name }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/tesseract.js/5.1.1/tesseract.min.js"></script>
    <style>
        * { box-sizing: border-box; }
        body { margin: 0; font-family: 'Inter', system-ui, sans-serif; background: #f6f3ed; color: #1c1915; }
        h1, h2, .font-serif { font-family: 'Playfair Display', Georgia, serif; }
        .sheet { max-width: 820px; margin: 0 auto; }
        .brand-badge {
            display: inline-flex; align-items: center; gap: 8px; padding: 6px 14px; border-radius: 999px;
            background: linear-gradient(135deg, #fb923c, #f2790f); color: #2a1200;
            font-size: 11px; font-weight: 700; letter-spacing: .16em; text-transform: uppercase;
            box-shadow: 0 8px 24px rgba(242,121,15,0.22);
        }
        .glass-panel {
            background: #ffffff; border: 1px solid rgba(20,18,12,0.08);
            border-radius: 18px; box-shadow: 0 10px 30px rgba(20,18,12,0.06);
        }
        .card {
            position: relative; background: #ffffff; border: 1px solid rgba(20,18,12,0.06);
            border-radius: 26px; box-shadow: 0 30px 70px -18px rgba(0,0,0,0.45), 0 8px 24px rgba(0,0,0,0.12);
            overflow: hidden;
        }
        .card::before {
            content: ''; position: absolute; top: 0; left: 0; right: 0; height: 5px;
            background: #f2790f;
        }
        .field-input {
            width: 100%; background: #f7f7f5; border: 1.5px solid rgba(20,18,12,0.10); border-radius: 12px;
            padding: 10px 12px; font-size: 14px; color: #1c1915; transition: border-color .15s ease, box-shadow .15s ease, background .15s ease;
        }
        .field-input:focus { outline: none; background: #fff; border-color: #f2790f; box-shadow: 0 0 0 4px rgba(242,121,15,0.14); }
        .field-label { display: block; font-size: 12px; font-weight: 600; color: #6b6355; margin-bottom: 5px; }
        .step-panel { transition: opacity .3s ease, transform .3s ease; }
        .step-panel.is-hidden { opacity: 0; transform: translateX(16px); pointer-events: none; position: absolute; }
        .step-panel.is-active { opacity: 1; transform: translateX(0); position: relative; }
        .step-dot {
            width: 34px; height: 34px; border-radius: 999px; display: flex; align-items: center; justify-content: center;
            font-size: 13px; font-weight: 700; background: #efe9da; color: #a89f8f;
            border: 1.5px solid rgba(20,18,12,0.06); transition: all .25s ease;
        }
        .step-dot.is-current {
            background: linear-gradient(135deg, #fb923c, #f2790f); color: #fff; border-color: transparent;
            box-shadow: 0 0 0 5px rgba(242,121,15,0.16);
        }
        .step-dot.is-done { background: linear-gradient(135deg, #22c55e, #15803d); color: #fff; border-color: transparent; }
        .step-line { flex: 1; height: 3px; border-radius: 999px; background: #efe9da; }
        .step-line.is-done { background: linear-gradient(90deg, #f2790f, #22c55e); }
        .step-label { font-size: 10px; font-weight: 600; color: #a89f8f; text-align: center; margin-top: 6px; letter-spacing: .02em; transition: color .25s ease; }
        .step-label.is-active { color: #c25e0a; }
        .upload-btn {
            display: inline-flex; align-items: center; gap: 8px; background: #171512; color: #fff; font-weight: 600;
            font-size: 13px; padding: 10px 16px; border-radius: 12px; cursor: pointer; transition: background .15s ease, transform .1s ease;
        }
        .upload-btn:hover { background: #2a2620; }
        .upload-btn.is-filled { background: #16a34a; }
        .btn-primary {
            background: #f2790f; color: #fff; font-weight: 700;
            box-shadow: 0 12px 28px -8px rgba(242,121,15,0.45);
        }
        .btn-primary:hover { background: #d4630a; }
        #signature-pad { touch-action: none; border: 1.5px dashed rgba(20,18,12,0.25); border-radius: 14px; background: #f7f7f5; width: 100%; height: 180px; }
        .step-icon {
            width: 34px; height: 34px; border-radius: 11px; display: flex; align-items: center; justify-content: center;
            flex-shrink: 0; font-size: 14px; color: #fff; background: linear-gradient(135deg, #fb923c, #f2790f);
            box-shadow: 0 8px 18px -4px rgba(242,121,15,0.4);
        }
        select.field-input {
            appearance: none; -webkit-appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='14' height='14' viewBox='0 0 24 24' fill='none' stroke='%238a7f6b' stroke-width='2.5' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E");
            background-repeat: no-repeat; background-position: right 12px center; padding-right: 34px;
        }
        .section-box { background: #f9f6ee; border: 1px solid rgba(20,18,12,0.06); border-radius: 14px; padding: 14px 16px; }
        .section-box-title { display: flex; align-items: center; gap: 7px; font-size: 13px; font-weight: 700; color: #1c1915; margin-bottom: 12px; }
        .section-box-title i { color: #f2790f; font-size: 12px; }
    </style>
</head>
<body>
    @include('partials.page-background')
@include('partials.public-top-nav')
<div class="pt-20 sm:pt-24"></div>

<div class="py-10 px-4">
<div class="sheet">

    <div class="text-center mb-7">
        <span class="brand-badge">
            <i class="fas fa-passport"></i> Enregistrement voyageur
        </span>
        <h1 class="text-2xl sm:text-3xl font-bold text-[#1c1915] mt-3">{{ $reservation->accommodation_name }}</h1>
        @if($reservation->accommodation?->city)
            <p class="text-[#8a7f6b] text-sm mt-1"><i class="fas fa-location-dot text-[11px] mr-1"></i>{{ $reservation->accommodation->city->name }}, Côte d'Ivoire</p>
        @endif
        <p class="text-[#a89f8f] text-xs mt-3 max-w-md mx-auto leading-relaxed">Conformément à la réglementation du Ministère du Tourisme et des Loisirs, merci de compléter cette fiche pour finaliser votre enregistrement.</p>
    </div>

    @if($registration)
        {{-- ── Déjà soumise : récapitulatif en lecture seule ──────────────────── --}}
        <div class="card p-6 sm:p-8">
            @if(session('success') || session('info'))
                <div class="mb-5 px-4 py-3 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-700 text-sm flex items-center gap-2">
                    <i class="fas fa-circle-check"></i> {{ session('success') ?? session('info') }}
                </div>
            @endif
            @if(session('error'))
                <div class="mb-5 px-4 py-3 rounded-xl bg-rose-50 border border-rose-200 text-rose-700 text-sm flex items-center gap-2">
                    <i class="fas fa-circle-exclamation"></i> {{ session('error') }}
                </div>
            @endif
            <div class="flex items-center gap-3 mb-6">
                <div class="w-11 h-11 rounded-full bg-emerald-100 flex items-center justify-center shrink-0">
                    <i class="fas fa-check text-emerald-600"></i>
                </div>
                <div>
                    <p class="font-semibold text-[#1c1915]">Fiche complétée</p>
                    <p class="text-[#8a7f6b] text-xs">Envoyée le {{ $registration->submitted_at?->format('d/m/Y à H:i') }}</p>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                <div><p class="field-label">Voyageur</p><p class="font-medium">{{ $registration->full_name }}</p></div>
                <div><p class="field-label">Type de voyage</p><p class="font-medium">{{ $registration->labelForTravelType() }}</p></div>
                <div><p class="field-label">Document</p><p class="font-medium">{{ $registration->labelForDocumentType() }} — {{ $registration->document_number }}</p></div>
                <div><p class="field-label">Motif du voyage</p><p class="font-medium">{{ $registration->labelForTravelReason() }}</p></div>
                <div><p class="field-label">Arrivée</p><p class="font-medium">{{ $registration->hotel_check_in_date->format('d/m/Y') }} à {{ $registration->hotel_check_in_time }}</p></div>
                <div><p class="field-label">Départ</p><p class="font-medium">{{ $registration->hotel_check_out_date->format('d/m/Y') }} à {{ $registration->hotel_check_out_time }}</p></div>
                <div><p class="field-label">Téléphone</p><p class="font-medium">{{ $registration->phone_country_code }} {{ $registration->phone_number }}</p></div>
                <div><p class="field-label">Enfants (&lt;15 ans)</p><p class="font-medium">{{ $registration->children_count }}</p></div>
            </div>

            <div class="grid grid-cols-3 gap-3 mt-6">
                <a href="{{ $registration->document_scan_front_url }}" target="_blank" class="block rounded-xl overflow-hidden border border-black/10 aspect-square bg-[#fbf9f5]">
                    <img src="{{ $registration->document_scan_front_url }}" class="w-full h-full object-cover" alt="Pièce recto">
                </a>
                <a href="{{ $registration->document_scan_back_url }}" target="_blank" class="block rounded-xl overflow-hidden border border-black/10 aspect-square bg-[#fbf9f5]">
                    <img src="{{ $registration->document_scan_back_url }}" class="w-full h-full object-cover" alt="Pièce verso">
                </a>
                <a href="{{ $registration->selfie_url }}" target="_blank" class="block rounded-xl overflow-hidden border border-black/10 aspect-square bg-[#fbf9f5]">
                    <img src="{{ $registration->selfie_url }}" class="w-full h-full object-cover" alt="Selfie">
                </a>
            </div>
            <p class="text-[#a89f8f] text-[11px] text-center mt-2">Pièce d'identité (recto/verso) · Selfie</p>

            @if($reservation->payment_status !== \App\Models\Reservation::PAYMENT_DEPOSIT_PAID)
                <div class="mt-6 pt-6 border-t border-black/10 text-center">
                    <p class="text-[#8a7f6b] text-sm mb-3">Il ne reste plus qu'à régler l'acompte pour finaliser votre réservation.</p>
                    <a href="{{ $reservation->paymentUrl() }}" class="btn-primary inline-flex items-center gap-2 px-6 py-3 rounded-xl transition text-sm">
                        <i class="fas fa-credit-card text-xs"></i> Procéder au paiement de l'acompte
                    </a>
                </div>
            @else
                <div class="mt-6 pt-6 border-t border-black/10 text-center">
                    <a href="{{ $reservation->confirmationUrl() }}" class="inline-flex items-center gap-2 text-[#f2790f] hover:text-[#9f4709] font-semibold text-sm">
                        Voir ma confirmation <i class="fas fa-arrow-right text-xs"></i>
                    </a>
                </div>
            @endif
        </div>
    @else
        {{-- ── Formulaire 4 étapes ─────────────────────────────────────────────── --}}
        @if($errors->any())
            <div class="mb-4 px-4 py-3 rounded-xl bg-rose-50 border border-rose-200 text-rose-700 text-sm">
                <ul class="list-disc pl-4 space-y-0.5">
                    @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
                </ul>
            </div>
        @endif

        <div class="glass-panel px-4 py-3.5 mb-6">
            <div class="flex items-center gap-2">
                @for($i = 1; $i <= 4; $i++)
                    <div class="step-dot" data-step-dot="{{ $i }}">{{ $i }}</div>
                    @if($i < 4)<div class="step-line" data-step-line="{{ $i }}"></div>@endif
                @endfor
            </div>
            <div class="flex items-center gap-2 mt-1.5">
                @foreach(['Voyage', 'Identité', 'Contact', 'Résumé'] as $i => $label)
                    <div class="step-label{{ $i === 0 ? ' is-active' : '' }}" data-step-label="{{ $i + 1 }}" style="width:34px;">{{ $label }}</div>
                    @if($i < 3)<div style="flex:1;"></div>@endif
                @endforeach
            </div>
        </div>

        <form id="registration-form" method="POST" action="{{ $reservation->guestRegistrationUrl() }}" enctype="multipart/form-data" class="card p-6 sm:p-8 relative" style="min-height: 520px;">
            @csrf
            <input type="file" name="signature" id="signature-input" class="hidden">

            {{-- Étape 1 : Voyage & documents --}}
            <div class="step-panel is-active" data-step="1">
                <div class="flex items-center gap-2.5 mb-5">
                    <span class="step-icon"><i class="fas fa-plane-departure"></i></span>
                    <h2 class="text-lg font-bold">Voyage & documents</h2>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="field-label">Type de voyage <span class="text-rose-500">*</span></label>
                        <select name="travel_type" required class="field-input">
                            @foreach(\App\Models\GuestRegistration::TRAVEL_TYPE_LABELS as $value => $label)
                                <option value="{{ $value }}" @selected(old('travel_type', 'domestique') === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="field-label">Type de document <span class="text-rose-500">*</span></label>
                        <select name="document_type" required class="field-input">
                            @foreach(\App\Models\GuestRegistration::DOCUMENT_TYPE_LABELS as $value => $label)
                                <option value="{{ $value }}" @selected(old('document_type') === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="field-label">Numéro de document <span class="text-rose-500">*</span></label>
                        <input type="text" name="document_number" id="document-number-input" required maxlength="100" value="{{ old('document_number') }}" placeholder="Inscrivez votre numéro d'identité" class="field-input">
                        <p id="document-number-ocr-status" class="hidden text-[11px] mt-1"></p>
                    </div>
                    <div>
                        <label class="field-label">Motif du voyage <span class="text-rose-500">*</span></label>
                        <select name="travel_reason" required class="field-input">
                            <option value="" disabled selected>Sélectionnez le motif de votre voyage</option>
                            @foreach(\App\Models\GuestRegistration::REASON_LABELS as $value => $label)
                                <option value="{{ $value }}" @selected(old('travel_reason') === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="section-box mt-4">
                    <p class="section-box-title"><i class="fas fa-id-card"></i> Pièces justificatives</p>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="field-label">Scan du document (recto) <span class="text-rose-500">*</span></label>
                        <label class="upload-btn" data-upload-label>
                            <i class="fas fa-camera"></i> <span>Télécharger le recto</span>
                            <input type="file" name="document_scan_front" id="document-scan-front-input" accept="image/*" required class="hidden" data-upload-input>
                        </label>
                    </div>
                    <div>
                        <label class="field-label">Scan du document (verso) <span class="text-rose-500">*</span></label>
                        <label class="upload-btn" data-upload-label>
                            <i class="fas fa-camera"></i> <span>Télécharger le verso</span>
                            <input type="file" name="document_scan_back" accept="image/*" required class="hidden" data-upload-input>
                        </label>
                    </div>
                    <div>
                        <label class="field-label">Selfie <span class="text-rose-500">*</span></label>
                        <div id="selfie-widget" class="rounded-xl border border-black/10 bg-white p-2.5">
                            <div id="selfie-start-view">
                                <button type="button" id="selfie-start-btn" class="upload-btn w-full justify-center">
                                    <i class="fas fa-camera-retro"></i> <span>Activer la caméra</span>
                                </button>
                            </div>

                            <div id="selfie-camera-view" class="hidden">
                                <video id="selfie-video" autoplay playsinline muted class="w-full rounded-lg bg-black" style="max-height:200px; object-fit:cover; transform:scaleX(-1);"></video>
                                <button type="button" id="selfie-snap-btn" class="upload-btn mt-2 w-full justify-center">
                                    <i class="fas fa-camera"></i> Capturer le selfie
                                </button>
                            </div>

                            <div id="selfie-preview-view" class="hidden text-center">
                                <img id="selfie-preview-img" class="mx-auto rounded-lg mb-2" style="max-height:200px;" alt="Aperçu du selfie">
                                <button type="button" id="selfie-retake-btn" class="upload-btn">
                                    <i class="fas fa-rotate"></i> Reprendre le selfie
                                </button>
                            </div>

                            <label id="selfie-fallback-view" class="upload-btn w-full justify-center hidden" data-upload-label>
                                <i class="fas fa-camera-retro"></i> <span>Importer une photo</span>
                                <input type="file" name="selfie" id="selfie-input" accept="image/*" capture="user" class="hidden" data-upload-input>
                            </label>
                            <p id="selfie-error" class="hidden text-rose-600 text-[11px] mt-1.5"></p>
                        </div>
                    </div>
                    </div>
                    <p class="text-[#a89f8f] text-[11px] mt-3">Vous devez téléverser les deux côtés de votre pièce d'identité, ainsi qu'un selfie.</p>
                </div>

                <div class="section-box mt-4">
                    <p class="section-box-title"><i class="fas fa-calendar-days"></i> Séjour à l'hôtel</p>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="field-label">Arrivée à l'hôtel <span class="text-rose-500">*</span></label>
                        <div class="grid grid-cols-2 gap-2">
                            <input type="date" name="hotel_check_in_date" required value="{{ old('hotel_check_in_date', $reservation->check_in?->format('Y-m-d')) }}" class="field-input">
                            <input type="time" name="hotel_check_in_time" required value="{{ old('hotel_check_in_time', '15:00') }}" class="field-input">
                        </div>
                    </div>
                    <div>
                        <label class="field-label">Départ de l'hôtel <span class="text-rose-500">*</span></label>
                        <div class="grid grid-cols-2 gap-2">
                            <input type="date" name="hotel_check_out_date" required value="{{ old('hotel_check_out_date', $reservation->check_out?->format('Y-m-d')) }}" class="field-input">
                            <input type="time" name="hotel_check_out_time" required value="{{ old('hotel_check_out_time', '11:00') }}" class="field-input">
                        </div>
                    </div>
                    </div>
                </div>

                <div class="flex justify-end mt-6">
                    <button type="button" class="step-next btn-primary inline-flex items-center gap-2 text-sm px-5 py-2.5 rounded-xl transition">
                        Suivant <i class="fas fa-arrow-right text-xs"></i>
                    </button>
                </div>
            </div>

            {{-- Étape 2 : Identité --}}
            <div class="step-panel is-hidden" data-step="2">
                <div class="flex items-center gap-2.5 mb-5">
                    <span class="step-icon"><i class="fas fa-id-card-clip"></i></span>
                    <h2 class="text-lg font-bold">Identité</h2>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div><label class="field-label">Prénom(s) <span class="text-rose-500">*</span></label><input type="text" name="first_name" required maxlength="150" value="{{ old('first_name') }}" class="field-input"></div>
                    <div><label class="field-label">Nom de famille <span class="text-rose-500">*</span></label><input type="text" name="last_name" required maxlength="150" value="{{ old('last_name') }}" class="field-input"></div>
                    <div><label class="field-label">Date de naissance <span class="text-rose-500">*</span></label><input type="date" name="birth_date" required value="{{ old('birth_date') }}" class="field-input"></div>
                    <div><label class="field-label">Lieu de naissance <span class="text-rose-500">*</span></label><input type="text" name="birth_place" required maxlength="150" value="{{ old('birth_place') }}" class="field-input"></div>
                    <div><label class="field-label">Nom du père <span class="text-rose-500">*</span></label><input type="text" name="father_name" required maxlength="150" value="{{ old('father_name') }}" class="field-input"></div>
                    <div><label class="field-label">Nom de la mère <span class="text-rose-500">*</span></label><input type="text" name="mother_name" required maxlength="150" value="{{ old('mother_name') }}" class="field-input"></div>
                    <div><label class="field-label">Profession <span class="text-rose-500">*</span></label><input type="text" name="profession" required maxlength="150" value="{{ old('profession') }}" placeholder="Inscrivez votre profession" class="field-input"></div>
                    <div><label class="field-label">Adresse du domicile <span class="text-rose-500">*</span></label><input type="text" name="home_address" required maxlength="255" value="{{ old('home_address') }}" placeholder="Inscrivez votre adresse" class="field-input"></div>
                    <div><label class="field-label">Nombre d'enfants (&lt;15 ans) avec vous</label><input type="number" name="children_count" min="0" max="20" value="{{ old('children_count', 0) }}" class="field-input"></div>
                </div>

                <div class="section-box mt-5">
                    <p class="section-box-title"><i class="fas fa-user-shield"></i> Personne à prévenir en cas d'urgence</p>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div><label class="field-label">Nom</label><input type="text" name="emergency_contact_name" maxlength="150" value="{{ old('emergency_contact_name') }}" placeholder="Inscrivez un nom" class="field-input"></div>
                        <div><label class="field-label">Numéro de téléphone</label><input type="tel" name="emergency_contact_phone" maxlength="30" value="{{ old('emergency_contact_phone') }}" placeholder="Inscrivez un numéro de téléphone" class="field-input"></div>
                    </div>
                </div>

                <div class="flex justify-between mt-6">
                    <button type="button" class="step-back inline-flex items-center gap-2 bg-white border border-black/10 hover:border-black/25 text-[#1c1915] font-semibold text-sm px-5 py-2.5 rounded-xl transition">
                        <i class="fas fa-arrow-left text-xs"></i> Retour
                    </button>
                    <button type="button" class="step-next btn-primary inline-flex items-center gap-2 text-sm px-5 py-2.5 rounded-xl transition">
                        Suivant <i class="fas fa-arrow-right text-xs"></i>
                    </button>
                </div>
            </div>

            {{-- Étape 3 : Contact & validation --}}
            <div class="step-panel is-hidden" data-step="3">
                <div class="flex items-center gap-2.5 mb-5">
                    <span class="step-icon"><i class="fas fa-phone-volume"></i></span>
                    <h2 class="text-lg font-bold">Contact & validation</h2>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="field-label">Numéro de téléphone <span class="text-rose-500">*</span></label>
                        <div class="grid grid-cols-[auto_1fr] gap-2">
                            <select name="phone_country_code" required class="field-input" style="width:auto;">
                                <option value="+225" selected>Côte-d'Ivoire +225</option>
                                <option value="+33">France +33</option>
                                <option value="+1">USA/Canada +1</option>
                                <option value="+44">Royaume-Uni +44</option>
                                <option value="+223">Mali +223</option>
                                <option value="+226">Burkina Faso +226</option>
                                <option value="+228">Togo +228</option>
                                <option value="+229">Bénin +229</option>
                                <option value="+233">Ghana +233</option>
                            </select>
                            <input type="tel" name="phone_number" required maxlength="30" value="{{ old('phone_number') }}" class="field-input">
                        </div>
                    </div>
                    <div><label class="field-label">E-mail</label><input type="email" name="email" maxlength="255" value="{{ old('email') }}" placeholder="Inscrivez votre Email" class="field-input"></div>
                </div>

                <div class="section-box mt-5">
                    <label class="flex items-center gap-2 text-sm">
                        <input type="checkbox" id="data-confirmed-checkbox" name="data_confirmed" value="1" required class="w-4 h-4 rounded" style="accent-color:#f2790f;">
                        Je confirme que toutes les données sont correctes.
                    </label>

                    <div class="mt-4">
                        <label class="field-label">Signature <span class="text-rose-500">*</span></label>
                        <canvas id="signature-pad" width="700" height="180"></canvas>
                        <button type="button" id="signature-clear" class="text-xs text-[#8a7f6b] hover:text-[#f2790f] mt-1.5"><i class="fas fa-eraser mr-1"></i>Effacer</button>
                    </div>
                </div>

                <div class="flex justify-between mt-6">
                    <button type="button" class="step-back inline-flex items-center gap-2 bg-white border border-black/10 hover:border-black/25 text-[#1c1915] font-semibold text-sm px-5 py-2.5 rounded-xl transition">
                        <i class="fas fa-arrow-left text-xs"></i> Retour
                    </button>
                    <button type="button" class="step-next btn-primary inline-flex items-center gap-2 text-sm px-5 py-2.5 rounded-xl transition">
                        Suivant <i class="fas fa-arrow-right text-xs"></i>
                    </button>
                </div>
            </div>

            {{-- Étape 4 : Résumé --}}
            <div class="step-panel is-hidden" data-step="4">
                <div class="flex items-center gap-2.5 mb-1">
                    <span class="step-icon"><i class="fas fa-clipboard-check"></i></span>
                    <h2 class="text-lg font-bold">Résumé de l'enregistrement</h2>
                </div>
                <p class="text-[#8a7f6b] text-xs mb-4 ml-11">Vérifiez vos informations avant d'envoyer votre fiche.</p>
                <div id="summary-grid" class="section-box grid grid-cols-1 sm:grid-cols-2 gap-3 text-sm"></div>

                <div class="mt-4 rounded-xl border border-[#f2790f]/25 bg-[#fdf3e7] px-4 py-3.5 flex items-center justify-between gap-3">
                    <div class="flex items-center gap-2.5">
                        <span class="step-icon" style="width:30px;height:30px;font-size:12px;"><i class="fas fa-coins"></i></span>
                        <div>
                            <p class="text-[11px] font-semibold uppercase tracking-wide text-[#8a7f6b]">Acompte à régler après l'envoi</p>
                            <p class="text-[#1c1915] font-bold text-base">{{ number_format((int) $reservation->deposit_amount_xof, 0, ',', ' ') }} XOF</p>
                        </div>
                    </div>
                </div>

                <div class="flex justify-between mt-6">
                    <button type="button" class="step-back inline-flex items-center gap-2 bg-white border border-black/10 hover:border-black/25 text-[#1c1915] font-semibold text-sm px-5 py-2.5 rounded-xl transition">
                        <i class="fas fa-arrow-left text-xs"></i> Retour
                    </button>
                    <button type="button" id="final-submit" class="btn-primary inline-flex items-center gap-2 text-sm px-6 py-2.5 rounded-xl transition">
                        <i class="fas fa-paper-plane text-xs"></i> Confirmer et envoyer
                    </button>
                </div>
            </div>
        </form>
    @endif

    <p class="text-center text-[#a89f8f] text-[11px] mt-6">Vos données sont conservées par l'établissement conformément à ses obligations réglementaires.</p>
</div>
</div>

<script>
(function () {
    const form = document.getElementById('registration-form');
    if (!form) return;

    const panels = Array.from(form.querySelectorAll('.step-panel'));
    let current = 1;

    function fieldsOf(panel) {
        return Array.from(panel.querySelectorAll('input, select, textarea')).filter((el) => !el.disabled && el.type !== 'hidden');
    }

    function updateDots() {
        document.querySelectorAll('[data-step-dot]').forEach((dot) => {
            const n = parseInt(dot.dataset.stepDot, 10);
            dot.classList.toggle('is-done', n < current);
            dot.classList.toggle('is-current', n === current);
        });
        document.querySelectorAll('[data-step-line]').forEach((line) => {
            const n = parseInt(line.dataset.stepLine, 10);
            line.classList.toggle('is-done', n < current);
        });
        document.querySelectorAll('[data-step-label]').forEach((label) => {
            const n = parseInt(label.dataset.stepLabel, 10);
            label.classList.toggle('is-active', n === current);
        });
    }

    function goTo(step) {
        panels.forEach((panel) => {
            const isTarget = parseInt(panel.dataset.step, 10) === step;
            panel.classList.toggle('is-active', isTarget);
            panel.classList.toggle('is-hidden', !isTarget);
        });
        current = step;
        updateDots();
        if (step === 4) buildSummary();
        window.scrollTo({ top: form.offsetTop - 24, behavior: 'smooth' });
    }

    form.querySelectorAll('.step-next').forEach((btn) => {
        btn.addEventListener('click', () => {
            const panel = btn.closest('.step-panel');
            const invalid = fieldsOf(panel).find((f) => !f.checkValidity());
            if (invalid) {
                invalid.reportValidity();
                return;
            }
            if (parseInt(panel.dataset.step, 10) === 1 && !hasSelfie) {
                selfieError.textContent = 'Merci de prendre (ou d\'importer) un selfie avant de continuer.';
                selfieError.classList.remove('hidden');
                selfieWidget.scrollIntoView({ behavior: 'smooth', block: 'center' });
                return;
            }
            goTo(current + 1);
        });
    });
    form.querySelectorAll('.step-back').forEach((btn) => {
        btn.addEventListener('click', () => goTo(current - 1));
    });

    // Upload buttons : libellé + couleur au choix d'un fichier
    form.querySelectorAll('[data-upload-input]').forEach((input) => {
        input.addEventListener('change', () => {
            const label = input.closest('[data-upload-label]');
            const span = label.querySelector('span');
            if (input.files && input.files[0]) {
                label.classList.add('is-filled');
                span.textContent = input.files[0].name;
            } else {
                label.classList.remove('is-filled');
            }
        });
    });

    // ── Selfie : capture caméra live (secours : import de fichier) ─────────
    const selfieWidget = document.getElementById('selfie-widget');
    const selfieInput = document.getElementById('selfie-input');
    const selfieStartView = document.getElementById('selfie-start-view');
    const selfieCameraView = document.getElementById('selfie-camera-view');
    const selfiePreviewView = document.getElementById('selfie-preview-view');
    const selfieFallbackView = document.getElementById('selfie-fallback-view');
    const selfieVideo = document.getElementById('selfie-video');
    const selfiePreviewImg = document.getElementById('selfie-preview-img');
    const selfieError = document.getElementById('selfie-error');
    let selfieStream = null;
    let hasSelfie = false;

    function showSelfieFallback(message) {
        selfieError.textContent = message;
        selfieError.classList.remove('hidden');
        selfieStartView.classList.add('hidden');
        selfieCameraView.classList.add('hidden');
        selfieFallbackView.classList.remove('hidden');
    }

    function stopSelfieStream() {
        if (selfieStream) {
            selfieStream.getTracks().forEach((t) => t.stop());
            selfieStream = null;
        }
    }

    async function startSelfieCamera() {
        selfieError.classList.add('hidden');
        if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
            showSelfieFallback('Caméra non disponible sur ce navigateur. Importez une photo.');
            return;
        }
        try {
            selfieStream = await navigator.mediaDevices.getUserMedia({ video: { facingMode: 'user' }, audio: false });
            selfieVideo.srcObject = selfieStream;
            selfieStartView.classList.add('hidden');
            selfiePreviewView.classList.add('hidden');
            selfieCameraView.classList.remove('hidden');
        } catch (e) {
            showSelfieFallback('Accès à la caméra refusé ou indisponible. Importez une photo.');
        }
    }

    document.getElementById('selfie-start-btn')?.addEventListener('click', startSelfieCamera);

    document.getElementById('selfie-snap-btn')?.addEventListener('click', () => {
        if (!selfieVideo.videoWidth) return;
        const canvas = document.createElement('canvas');
        canvas.width = selfieVideo.videoWidth;
        canvas.height = selfieVideo.videoHeight;
        const snapCtx = canvas.getContext('2d');
        snapCtx.translate(canvas.width, 0);
        snapCtx.scale(-1, 1);
        snapCtx.drawImage(selfieVideo, 0, 0, canvas.width, canvas.height);
        canvas.toBlob((blob) => {
            const file = new File([blob], 'selfie.png', { type: 'image/png' });
            const dt = new DataTransfer();
            dt.items.add(file);
            selfieInput.files = dt.files;
            selfiePreviewImg.src = canvas.toDataURL('image/png');
            hasSelfie = true;
            selfieError.classList.add('hidden');
            stopSelfieStream();
            selfieCameraView.classList.add('hidden');
            selfiePreviewView.classList.remove('hidden');
        }, 'image/png');
    });

    document.getElementById('selfie-retake-btn')?.addEventListener('click', () => {
        hasSelfie = false;
        selfieInput.value = '';
        selfiePreviewView.classList.add('hidden');
        startSelfieCamera();
    });

    selfieInput.addEventListener('change', () => {
        if (selfieInput.files && selfieInput.files[0]) {
            hasSelfie = true;
            selfieError.classList.add('hidden');
        }
    });

    // ── Lecture automatique du numéro de document (OCR local, Tesseract.js) ──
    const documentNumberInput = document.getElementById('document-number-input');
    const documentScanFrontInput = document.getElementById('document-scan-front-input');
    const ocrStatus = document.getElementById('document-number-ocr-status');

    function setOcrStatus(message, tone) {
        if (!message) {
            ocrStatus.classList.add('hidden');
            return;
        }
        ocrStatus.textContent = message;
        ocrStatus.className = 'text-[11px] mt-1 ' + (tone === 'warn' ? 'text-amber-600' : tone === 'error' ? 'text-rose-500' : 'text-[#8a7f6b]');
    }

    function extractDocumentNumber(rawText) {
        // Plage 5-15 : couvre CNI/permis/carte consulaire ivoiriens et le numéro de
        // passeport (9 caractères) sans jamais retenir une ligne MRZ entière (44
        // caractères, quasi illisible telle quelle) — mieux vaut ne rien détecter que
        // proposer une valeur manifestement fausse.
        const tokens = (rawText || '')
            .split(/[^A-Za-z0-9]+/)
            .map((t) => t.trim())
            .filter((t) => t.length >= 5 && t.length <= 15);

        let best = null;
        let bestScore = -1;
        tokens.forEach((tok) => {
            const digitCount = (tok.match(/[0-9]/g) || []).length;
            if (digitCount < 2) return;
            const digitRatio = digitCount / tok.length;
            const score = digitCount + digitRatio * 3;
            if (score > bestScore) {
                bestScore = score;
                best = tok.toUpperCase();
            }
        });
        return best;
    }

    // Charge le fichier dans un canvas, redimensionné pour limiter le temps de
    // traitement (une photo de téléphone fait souvent 8-12 Mpx, inutile pour lire
    // un simple numéro).
    function loadImageToCanvas(file, maxDim) {
        return new Promise((resolve, reject) => {
            const img = new Image();
            const url = URL.createObjectURL(file);
            img.onload = () => {
                let { width, height } = img;
                if (width > maxDim || height > maxDim) {
                    const ratio = Math.min(maxDim / width, maxDim / height);
                    width = Math.round(width * ratio);
                    height = Math.round(height * ratio);
                }
                const canvas = document.createElement('canvas');
                canvas.width = width;
                canvas.height = height;
                canvas.getContext('2d').drawImage(img, 0, 0, width, height);
                URL.revokeObjectURL(url);
                resolve(canvas);
            };
            img.onerror = reject;
            img.src = url;
        });
    }

    // Seuil d'Otsu : calcule automatiquement le seuil noir/blanc optimal à partir de
    // l'histogramme de l'image, plutôt qu'un seuil fixe qui échouerait selon la
    // luminosité de la photo (éclairage variable d'un téléphone à l'autre).
    function otsuThreshold(histogram, total) {
        let sum = 0;
        for (let t = 0; t < 256; t++) sum += t * histogram[t];
        let sumB = 0, wB = 0, maxVar = 0, threshold = 127;
        for (let t = 0; t < 256; t++) {
            wB += histogram[t];
            if (wB === 0) continue;
            const wF = total - wB;
            if (wF === 0) break;
            sumB += t * histogram[t];
            const mB = sumB / wB;
            const mF = (sum - sumB) / wF;
            const varBetween = wB * wF * (mB - mF) * (mB - mF);
            if (varBetween > maxVar) {
                maxVar = varBetween;
                threshold = t;
            }
        }
        return threshold;
    }

    // Niveaux de gris + binarisation noir/blanc : améliore nettement la fiabilité de
    // Tesseract sur une photo de carte d'identité (contraste texte/fond souvent
    // faible, motifs de sécurité en arrière-plan).
    function preprocessForOcr(canvas) {
        const ctx2 = canvas.getContext('2d');
        const imageData = ctx2.getImageData(0, 0, canvas.width, canvas.height);
        const data = imageData.data;
        const pixelCount = data.length / 4;
        const gray = new Uint8ClampedArray(pixelCount);
        const histogram = new Array(256).fill(0);
        for (let i = 0, p = 0; i < data.length; i += 4, p++) {
            const g = Math.round(0.299 * data[i] + 0.587 * data[i + 1] + 0.114 * data[i + 2]);
            gray[p] = g;
            histogram[g]++;
        }
        const threshold = otsuThreshold(histogram, pixelCount);
        for (let i = 0, p = 0; i < data.length; i += 4, p++) {
            const value = gray[p] > threshold ? 255 : 0;
            data[i] = data[i + 1] = data[i + 2] = value;
        }
        ctx2.putImageData(imageData, 0, 0);
    }

    function canvasToBlob(canvas) {
        return new Promise((resolve, reject) => {
            canvas.toBlob((blob) => (blob ? resolve(blob) : reject(new Error('toBlob a échoué'))), 'image/jpeg', 0.92);
        });
    }

    // Worker Tesseract préchauffé dès le chargement de la page (au lieu d'attendre
    // la sélection du fichier) : le téléchargement du moteur WASM + des données de
    // langue se fait pendant que le voyageur remplit les champs précédents. PSM 11
    // (« sparse text ») convient mieux qu'un mode « page complète » à une carte
    // d'identité, où les champs sont dispersés plutôt qu'en un seul bloc de texte.
    let ocrWorkerPromise = null;
    function getOcrWorker() {
        if (!ocrWorkerPromise) {
            ocrWorkerPromise = (async () => {
                const worker = await Tesseract.createWorker('eng');
                await worker.setParameters({
                    tessedit_char_whitelist: 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789',
                    tessedit_pageseg_mode: '11',
                });
                return worker;
            })();
        }
        return ocrWorkerPromise;
    }
    if (typeof Tesseract !== 'undefined') {
        getOcrWorker();
    }

    documentScanFrontInput?.addEventListener('change', async () => {
        const file = documentScanFrontInput.files && documentScanFrontInput.files[0];
        if (!file || typeof Tesseract === 'undefined') return;

        setOcrStatus('Lecture automatique du numéro en cours…', 'info');
        try {
            const [worker, canvas] = await Promise.all([getOcrWorker(), loadImageToCanvas(file, 2000)]);
            preprocessForOcr(canvas);
            const prepared = await canvasToBlob(canvas);
            const result = await worker.recognize(prepared);
            const rawText = (result.data && result.data.text) || '';
            const detected = extractDocumentNumber(rawText);
            if (detected && !documentNumberInput.value.trim()) {
                documentNumberInput.value = detected;
                setOcrStatus('Numéro détecté automatiquement — vérifiez qu\'il est correct et corrigez si besoin.', 'warn');
            } else if (!documentNumberInput.value.trim()) {
                setOcrStatus('Numéro non détecté automatiquement, merci de le saisir manuellement.', 'warn');
                console.warn('OCR : aucun numéro plausible dans le texte reconnu ->', rawText);
            } else {
                setOcrStatus(null);
            }
        } catch (e) {
            setOcrStatus(null);
            console.warn('OCR : échec de la reconnaissance ->', e);
        }
    });

    function buildSummary() {
        const grid = document.getElementById('summary-grid');
        const rows = [
            ['Nom de l\'hôtel', {{ Js::from($reservation->accommodation_name) }}],
            ['Prénom(s)', form.first_name.value],
            ['Nom de famille', form.last_name.value],
            ['Date de naissance', form.birth_date.value],
            ['Lieu de naissance', form.birth_place.value],
            ['Nom du père', form.father_name.value],
            ['Nom de la mère', form.mother_name.value],
            ['Profession', form.profession.value],
            ['Adresse du domicile', form.home_address.value],
            ['Numéro de téléphone', form.phone_country_code.value + ' ' + form.phone_number.value],
            ['E-mail', form.email.value || '—'],
            ['Type de document', form.document_type.options[form.document_type.selectedIndex]?.text || ''],
            ['Numéro de document', form.document_number.value],
            ['Arrivée à l\'hôtel', form.hotel_check_in_date.value + ' ' + form.hotel_check_in_time.value],
            ['Départ de l\'hôtel', form.hotel_check_out_date.value + ' ' + form.hotel_check_out_time.value],
            ['Nombre d\'enfants (<15 ans)', form.children_count.value || '0'],
            ['Motif du voyage', form.travel_reason.options[form.travel_reason.selectedIndex]?.text || ''],
        ];
        grid.innerHTML = rows.map(function (r) {
            return '<div><p class="field-label">' + r[0] + '</p><p class="font-medium">' + (r[1] || '—') + '</p></div>';
        }).join('');
    }

    // ── Pad de signature (vanille JS, souris + tactile) ─────────────────────
    const canvas = document.getElementById('signature-pad');
    const ctx = canvas.getContext('2d');
    ctx.lineWidth = 2.2;
    ctx.lineCap = 'round';
    ctx.strokeStyle = '#1c1915';
    let drawing = false;
    let hasSignature = false;

    function pointerPos(e) {
        const rect = canvas.getBoundingClientRect();
        const point = e.touches ? e.touches[0] : e;
        return {
            x: (point.clientX - rect.left) * (canvas.width / rect.width),
            y: (point.clientY - rect.top) * (canvas.height / rect.height),
        };
    }
    function startDraw(e) {
        drawing = true;
        hasSignature = true;
        const p = pointerPos(e);
        ctx.beginPath();
        ctx.moveTo(p.x, p.y);
        e.preventDefault();
    }
    function moveDraw(e) {
        if (!drawing) return;
        const p = pointerPos(e);
        ctx.lineTo(p.x, p.y);
        ctx.stroke();
        e.preventDefault();
    }
    function endDraw() { drawing = false; }

    canvas.addEventListener('mousedown', startDraw);
    canvas.addEventListener('mousemove', moveDraw);
    window.addEventListener('mouseup', endDraw);
    canvas.addEventListener('touchstart', startDraw, { passive: false });
    canvas.addEventListener('touchmove', moveDraw, { passive: false });
    canvas.addEventListener('touchend', endDraw);

    document.getElementById('signature-clear')?.addEventListener('click', function () {
        ctx.clearRect(0, 0, canvas.width, canvas.height);
        hasSignature = false;
    });

    document.getElementById('final-submit')?.addEventListener('click', function () {
        if (!hasSignature) {
            alert('Merci de signer avant de valider votre fiche.');
            return;
        }
        if (!document.getElementById('data-confirmed-checkbox').checked) {
            alert('Merci de confirmer que les données saisies sont correctes.');
            goTo(3);
            return;
        }
        canvas.toBlob(function (blob) {
            const file = new File([blob], 'signature.png', { type: 'image/png' });
            const dt = new DataTransfer();
            dt.items.add(file);
            document.getElementById('signature-input').files = dt.files;
            form.submit();
        }, 'image/png');
    });

    updateDots();
})();
</script>

@include('partials.homepage-footer')
</body>
</html>
