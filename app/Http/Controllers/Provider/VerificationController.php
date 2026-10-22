<?php

namespace App\Http\Controllers\Provider;

use App\Http\Controllers\Controller;
use App\Models\Provider;
use App\Models\ProviderDocument;
use App\Models\User;
use App\Notifications\VisitorSystemNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;
use Illuminate\View\View;

/**
 * Vérification du compte prestataire : envoi des justificatifs exigés avant tout retrait
 * d'argent (cf. WalletService::requestPayout).
 */
class VerificationController extends Controller
{
    private function provider(): Provider
    {
        $provider = Provider::forUserQuery(Auth::id())->first();
        abort_unless($provider, 404, 'Aucune fiche prestataire trouvée.');

        return $provider;
    }

    public function index(): View
    {
        $provider = $this->provider();
        $documents = ProviderDocument::where('provider_id', $provider->id)->latest()->get();

        return view('provider.verification.index', compact('provider', 'documents'));
    }

    public function store(Request $request): RedirectResponse
    {
        $provider = $this->provider();

        $validated = $request->validate([
            'type' => ['required', 'in:'.implode(',', array_keys(ProviderDocument::TYPE_LABELS))],
            'document' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png,webp', 'max:8192'],
        ], [], ['type' => 'type de document', 'document' => 'fichier']);

        $file = $request->file('document');
        $path = $file->store('provider-documents/'.$provider->id, 'local');

        ProviderDocument::create([
            'provider_id' => $provider->id,
            'type' => $validated['type'],
            'file_path' => $path,
            'original_name' => mb_substr($file->getClientOriginalName(), 0, 255),
            'status' => ProviderDocument::STATUS_PENDING,
        ]);

        $admins = User::query()->where('role', 'admin')->where('is_active', true)->get();
        Notification::send($admins, new VisitorSystemNotification(
            'Justificatif prestataire à vérifier',
            $provider->name.' a envoyé : '.ProviderDocument::TYPE_LABELS[$validated['type']].'.',
            route('admin.provider-verifications.index'),
        ));

        return back()->with('success', 'Document envoyé. Notre équipe le vérifie sous 48 h ouvrées.');
    }
}
