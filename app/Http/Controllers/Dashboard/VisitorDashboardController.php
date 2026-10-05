<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\Event;
use App\Models\MediaPurchase;
use App\Models\NewsletterSubscriber;
use App\Models\Reservation;
use App\Models\Review;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class VisitorDashboardController extends Controller
{
    public function index(): View
    {
        $featured_articles = Article::where('status', 'published')
            ->where('is_featured', true)
            ->with('category', 'author')
            ->latest('published_at')
            ->take(4)
            ->get();

        $upcoming_events = Event::where('status', 'published')
            ->where('starts_at', '>', now())
            ->with('category')
            ->orderBy('starts_at')
            ->take(4)
            ->get();

        $user = Auth::user();
        $newsletter = NewsletterSubscriber::query()
            ->where('status', 'active')
            ->where(function ($q) use ($user) {
                $q->where('user_id', $user->id)
                    ->orWhereRaw('LOWER(email) = ?', [mb_strtolower((string) $user->email)]);
            })
            ->first();

        $my_reviews = Review::where('user_id', Auth::id())
            ->with('provider')
            ->latest()
            ->take(3)
            ->get();

        $favorites_count = $user->favorites()->count();
        $unread_notifications = $user->unreadNotifications()->count();
        $purchases_count = MediaPurchase::where('user_id', $user->id)
            ->where('status', 'completed')
            ->count();
        $recent_purchases = MediaPurchase::with('media')
            ->where('user_id', $user->id)
            ->where('status', 'completed')
            ->latest('paid_at')
            ->take(3)
            ->get();

        // Rattachement par user_id désormais (compte obligatoire pour réserver), avec
        // repli par email pour les réservations invité créées avant ce champ.
        $all_reservations = Reservation::ownedBy($user)->get();

        $reservation_stats = [
            'total' => $all_reservations->count(),
            'upcoming' => $all_reservations->where('computed_status', Reservation::COMPUTED_STATUS_UPCOMING)->count(),
            'ongoing' => $all_reservations->where('computed_status', Reservation::COMPUTED_STATUS_ONGOING)->count(),
            'completed' => $all_reservations->where('computed_status', Reservation::COMPUTED_STATUS_COMPLETED)->count(),
            'cancelled' => $all_reservations->where('computed_status', Reservation::COMPUTED_STATUS_CANCELLED)->count(),
        ];

        $my_reservations = Reservation::ownedBy($user)
            ->with('accommodation')
            ->latest()
            ->take(5)
            ->get();

        $latest_receipts = Reservation::ownedBy($user)
            ->where('payment_status', Reservation::PAYMENT_DEPOSIT_PAID)
            ->latest()
            ->take(3)
            ->get();

        return view('dashboards.visitor', compact(
            'featured_articles',
            'upcoming_events',
            'newsletter',
            'my_reviews',
            'favorites_count',
            'unread_notifications',
            'purchases_count',
            'recent_purchases',
            'my_reservations',
            'reservation_stats',
            'latest_receipts',
        ));
    }
}
