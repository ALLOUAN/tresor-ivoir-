<?php

namespace App\Http\Controllers\Provider;

use App\Http\Controllers\Controller;
use App\Models\Review;
use App\Models\ReviewReply;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReviewController extends Controller
{
    public function index()
    {
        /** @var User $user */
        $user = Auth::user();
        $provider = $user->providers()->first();

        if (! $provider) {
            return redirect()->route('provider.dashboard')
                ->with('error', 'Créez d\'abord votre fiche prestataire.');
        }

        $reviews = Review::query()
            ->where('provider_id', $provider->id)
            ->with(['user', 'reply'])
            ->latest()
            ->paginate(20);

        $counts = [
            'all' => Review::where('provider_id', $provider->id)->count(),
            'pending' => Review::where('provider_id', $provider->id)->where('status', 'pending')->count(),
            'approved' => Review::where('provider_id', $provider->id)->where('status', 'approved')->count(),
            'rejected' => Review::where('provider_id', $provider->id)->where('status', 'rejected')->count(),
        ];

        return view('provider.reviews.index', compact('reviews', 'counts'));
    }

    public function reply(Request $request, Review $review)
    {
        /** @var User $user */
        $user = Auth::user();
        $provider = $user->providers()->first();

        if (! $provider || $review->provider_id !== $provider->id) {
            abort(403, 'Accès refusé.');
        }

        $request->validate(['reply_text' => 'required|string|min:10|max:1000']);

        ReviewReply::updateOrCreate(
            ['review_id' => $review->id],
            [
                'provider_id' => $provider->id,
                'replied_by' => Auth::id(),
                'reply_text' => $request->reply_text,
                'is_visible' => true,
            ]
        );

        return back()->with('success', 'Votre réponse a été publiée.');
    }

    public function destroyReply(Review $review)
    {
        /** @var User $user */
        $user = Auth::user();
        $provider = $user->providers()->first();

        if (! $provider || $review->provider_id !== $provider->id) {
            abort(403);
        }

        $review->reply?->delete();

        return back()->with('success', 'Réponse supprimée.');
    }
}
