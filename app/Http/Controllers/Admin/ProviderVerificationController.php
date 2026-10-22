<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ProviderDocument;
use App\Notifications\VisitorSystemNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * File d'examen des justificatifs prestataires. Quand toutes les pièces exigées sont
 * validées, le prestataire passe « vérifié » et peut demander des retraits.
 */
class ProviderVerificationController extends Controller
{
    public function index(Request $request): View
    {
        $status = in_array($request->query('statut'), array_keys(ProviderDocument::STATUS_LABELS), true)
            ? $request->query('statut')
            : ProviderDocument::STATUS_PENDING;

        $documents = ProviderDocument::with(['provider', 'reviewer'])
            ->where('status', $status)
            ->orderBy($status === ProviderDocument::STATUS_PENDING ? 'created_at' : 'reviewed_at', $status === ProviderDocument::STATUS_PENDING ? 'asc' : 'desc')
            ->paginate(20)
            ->withQueryString();

        $counts = ProviderDocument::selectRaw('status, count(*) as n')->groupBy('status')->pluck('n', 'status');

        return view('admin.provider-verifications.index', compact('documents', 'status', 'counts'));
    }

    public function download(ProviderDocument $document): StreamedResponse
    {
        abort_unless(Storage::disk('local')->exists($document->file_path), 404);

        return Storage::disk('local')->response($document->file_path, $document->original_name ?: basename($document->file_path));
    }

    public function approve(ProviderDocument $document): RedirectResponse
    {
        $document->update([
            'status' => ProviderDocument::STATUS_APPROVED,
            'rejection_reason' => null,
            'reviewed_by' => Auth::id(),
            'reviewed_at' => now(),
        ]);

        $provider = $document->provider;
        $nowVerified = ! $provider->is_verified && ProviderDocument::providerHasAllRequired($provider);
        if ($nowVerified) {
            $provider->update(['is_verified' => true]);
        }

        $provider->user?->notify(new VisitorSystemNotification(
            $nowVerified ? 'Compte vérifié' : 'Justificatif validé',
            $nowVerified
                ? 'Toutes vos pièces sont validées : votre établissement porte désormais le badge « vérifié » et vous pouvez retirer vos gains.'
                : $document->typeLabel().' a été validé.',
            route('provider.verification.index'),
        ));

        return back()->with('success', $nowVerified ? 'Document validé — le prestataire est maintenant vérifié.' : 'Document validé.');
    }

    public function reject(Request $request, ProviderDocument $document): RedirectResponse
    {
        $validated = $request->validate(['reason' => ['required', 'string', 'max:500']], [], ['reason' => 'motif']);

        $document->update([
            'status' => ProviderDocument::STATUS_REJECTED,
            'rejection_reason' => $validated['reason'],
            'reviewed_by' => Auth::id(),
            'reviewed_at' => now(),
        ]);

        $document->provider->user?->notify(new VisitorSystemNotification(
            'Justificatif refusé',
            $document->typeLabel().' a été refusé : '.$validated['reason'].' Merci d\'envoyer un nouveau document.',
            route('provider.verification.index'),
        ));

        return back()->with('success', 'Document refusé, le prestataire a été prévenu.');
    }
}
