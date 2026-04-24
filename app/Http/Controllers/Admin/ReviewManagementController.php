<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Provider;
use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReviewManagementController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->get('status', 'pending');
        $search = $request->get('q');

        $query = Review::with(['provider', 'user'])->latest();

        if ($status && $status !== 'all') {
            $query->where('status', $status);
        }

        if ($search) {
            $query->where(fn ($q) => $q
                ->where('comment', 'like', "%{$search}%")
                ->orWhere('author_name', 'like', "%{$search}%")
                ->orWhereHas('provider', fn ($q2) => $q2->where('name', 'like', "%{$search}%"))
            );
        }

        $reviews = $query->paginate(20)->withQueryString();

        $counts = [
            'all' => Review::count(),
            'pending' => Review::where('status', 'pending')->count(),
            'approved' => Review::where('status', 'approved')->count(),
            'rejected' => Review::where('status', 'rejected')->count(),
            'flagged' => Review::where('status', 'flagged')->count(),
        ];

        return view('admin.reviews.index', compact('reviews', 'counts', 'status', 'search'));
    }

    public function approve(Review $review)
    {
        $review->update([
            'status' => 'approved',
            'moderated_by' => Auth::id(),
            'moderated_at' => now(),
        ]);

        $this->recalcRating($review->provider_id);

        return back()->with('success', 'Avis approuvé.');
    }

    public function reject(Request $request, Review $review)
    {
        $request->validate(['reason' => 'nullable|string|max:255']);

        $review->update([
            'status' => 'rejected',
            'moderated_by' => Auth::id(),
            'moderated_at' => now(),
            'rejection_reason' => $request->reason,
        ]);

        $this->recalcRating($review->provider_id);

        return back()->with('success', 'Avis rejeté.');
    }

    public function flag(Review $review)
    {
        $review->update(['status' => 'flagged']);

        return back()->with('success', 'Avis signalé.');
    }

    public function destroy(Review $review)
    {
        $providerId = $review->provider_id;
        $review->delete();
        $this->recalcRating($providerId);

        return back()->with('success', 'Avis supprimé.');
    }

    private function recalcRating(int $providerId): void
    {
        $approved = Review::where('provider_id', $providerId)->where('status', 'approved');
        $avg = $approved->avg('rating');
        $count = $approved->count();

        Provider::where('id', $providerId)->update([
            'rating_avg' => $avg ? round($avg, 2) : null,
            'rating_count' => $count,
        ]);
    }
}
