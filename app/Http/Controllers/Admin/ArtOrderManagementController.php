<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ArtworkOrder;
use App\Models\User;
use App\Notifications\VisitorSystemNotification;
use App\Services\WalletService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ArtOrderManagementController extends Controller
{
    public function index(Request $request): View
    {
        $status = $request->get('status');

        $query = ArtworkOrder::query()
            ->with(['artwork', 'provider', 'buyer'])
            ->latest();

        if ($status) {
            $query->where('status', $status);
        }

        $orders = $query->paginate(20)->withQueryString();

        $stats = [
            'total_orders' => ArtworkOrder::count(),
            'paid_orders' => ArtworkOrder::where('status', ArtworkOrder::STATUS_PAID)->count(),
            'total_sales_xof' => (int) ArtworkOrder::whereIn('status', [ArtworkOrder::STATUS_PAID, ArtworkOrder::STATUS_SHIPPED, ArtworkOrder::STATUS_DELIVERED])->sum('amount_total_xof'),
            'total_commission_xof' => (int) ArtworkOrder::whereIn('status', [ArtworkOrder::STATUS_PAID, ArtworkOrder::STATUS_SHIPPED, ArtworkOrder::STATUS_DELIVERED])->sum('commission_amount_xof'),
        ];

        return view('admin.art-orders.index', compact('orders', 'stats', 'status'));
    }

    public function show(ArtworkOrder $artworkOrder): View
    {
        $artworkOrder->load(['artwork', 'provider', 'buyer']);

        return view('admin.art-orders.show', ['order' => $artworkOrder]);
    }

    public function refund(Request $request, ArtworkOrder $artworkOrder, WalletService $walletService): RedirectResponse
    {
        $validated = $request->validate([
            'note' => ['nullable', 'string', 'max:500'],
        ]);

        if (! in_array($artworkOrder->status, [ArtworkOrder::STATUS_PAID, ArtworkOrder::STATUS_SHIPPED, ArtworkOrder::STATUS_DELIVERED, ArtworkOrder::STATUS_OVERSOLD], true)) {
            return back()->with('error', 'Cette commande n\'a pas de paiement à rembourser.');
        }

        $walletService->recordManualRefundForArtworkOrder($artworkOrder, Auth::user(), $validated['note'] ?? null);

        if ($artworkOrder->buyer_user_id && ($buyer = User::find($artworkOrder->buyer_user_id))) {
            $artworkOrder->loadMissing('artwork');
            $buyer->notify(new VisitorSystemNotification(
                'Commande remboursée',
                'Votre commande "'.$artworkOrder->artwork?->title.'" a été remboursée.',
                route('visitor.art-orders.index'),
            ));
        }

        return back()->with('success', 'Remboursement enregistré.');
    }
}
