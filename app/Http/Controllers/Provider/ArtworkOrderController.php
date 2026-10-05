<?php

namespace App\Http\Controllers\Provider;

use App\Http\Controllers\Controller;
use App\Models\ArtworkOrder;
use App\Models\Provider;
use App\Models\User;
use App\Notifications\VisitorSystemNotification;
use App\Services\WalletService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ArtworkOrderController extends Controller
{
    private function getProvider(): Provider
    {
        $provider = Provider::query()->where('user_id', Auth::id())->first();
        if (! $provider) {
            abort(404, 'Aucune fiche prestataire trouvée.');
        }

        return $provider;
    }

    public function index(Request $request): View
    {
        $provider = $this->getProvider();

        $status = (string) $request->query('status', '');
        $allowedStatuses = array_keys(ArtworkOrder::STATUS_LABELS);
        if ($status !== '' && ! in_array($status, $allowedStatuses, true)) {
            $status = '';
        }

        $base = ArtworkOrder::where('provider_id', $provider->id);

        $stats = [
            'total' => (clone $base)->count(),
            'paid' => (clone $base)->where('status', ArtworkOrder::STATUS_PAID)->count(),
            'shipped' => (clone $base)->where('status', ArtworkOrder::STATUS_SHIPPED)->count(),
            'delivered' => (clone $base)->where('status', ArtworkOrder::STATUS_DELIVERED)->count(),
        ];

        $orders = (clone $base)
            ->with('artwork')
            ->when($status !== '', fn ($q) => $q->where('status', $status))
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        return view('provider.art-orders.index', compact('orders', 'stats', 'status'));
    }

    public function show(ArtworkOrder $artworkOrder): View
    {
        $provider = $this->getProvider();
        abort_unless((int) $artworkOrder->provider_id === (int) $provider->id, 403);

        $artworkOrder->load('artwork');

        return view('provider.art-orders.show', ['order' => $artworkOrder]);
    }

    public function updateStatus(Request $request, ArtworkOrder $artworkOrder, WalletService $walletService): RedirectResponse
    {
        $provider = $this->getProvider();
        abort_unless((int) $artworkOrder->provider_id === (int) $provider->id, 403);

        $validated = $request->validate([
            'status' => ['required', 'string', 'in:'.ArtworkOrder::STATUS_SHIPPED.','.ArtworkOrder::STATUS_DELIVERED],
        ]);

        // Transitions strictement séquentielles : payé -> expédiée -> livrée, jamais de saut
        // ni de retour en arrière depuis cette action.
        $allowedFrom = [
            ArtworkOrder::STATUS_SHIPPED => ArtworkOrder::STATUS_PAID,
            ArtworkOrder::STATUS_DELIVERED => ArtworkOrder::STATUS_SHIPPED,
        ];

        if ($artworkOrder->status !== $allowedFrom[$validated['status']]) {
            return back()->with('error', 'Transition de statut invalide.');
        }

        $artworkOrder->loadMissing('artwork');
        $buyer = $artworkOrder->buyer_user_id ? User::find($artworkOrder->buyer_user_id) : null;

        if ($validated['status'] === ArtworkOrder::STATUS_SHIPPED) {
            $artworkOrder->update(['status' => ArtworkOrder::STATUS_SHIPPED, 'shipped_at' => now()]);

            $buyer?->notify(new VisitorSystemNotification(
                'Commande expédiée',
                'Votre commande "'.$artworkOrder->artwork?->title.'" a été expédiée.',
                route('visitor.art-orders.index'),
            ));

            return back()->with('success', 'Commande marquée comme expédiée.');
        }

        $artworkOrder->update(['status' => ArtworkOrder::STATUS_DELIVERED, 'delivered_at' => now()]);
        $walletService->releaseArtworkOrderCredit($artworkOrder);

        $buyer?->notify(new VisitorSystemNotification(
            'Commande livrée',
            'Votre commande "'.$artworkOrder->artwork?->title.'" a été marquée comme livrée.',
            route('visitor.art-orders.index'),
        ));

        return back()->with('success', 'Commande marquée comme livrée — votre solde a été crédité.');
    }
}
