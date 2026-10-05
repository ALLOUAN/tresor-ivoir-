<?php

namespace App\Http\Controllers;

use App\Models\ArtworkOrder;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class VisitorArtOrderController extends Controller
{
    public function index(): View
    {
        $orders = ArtworkOrder::with(['artwork', 'provider'])
            ->where('buyer_user_id', Auth::id())
            ->latest()
            ->paginate(20);

        return view('visitor.art-orders.index', compact('orders'));
    }
}
