<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Accommodation;
use App\Models\AccommodationMedia;
use App\Models\Article;
use App\Models\Event;
use App\Models\Media;
use App\Models\Payment;
use App\Models\Provider;
use App\Models\Reservation;
use App\Models\Review;
use App\Models\ReviewReply;
use App\Models\Subscription;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class AdminDashboardController extends Controller
{
    private const MOIS_FR = ['Jan', 'Fév', 'Mar', 'Avr', 'Mai', 'Jun', 'Jul', 'Aoû', 'Sep', 'Oct', 'Nov', 'Déc'];

    public function index()
    {
        $stats = [
            'total_users' => User::count(),
            'users_today' => User::whereDate('created_at', today())->count(),
            'active_providers' => Provider::where('status', 'active')->count(),
            'pending_providers' => Provider::where('status', 'pending')->count(),
            'published_articles' => Article::where('status', 'published')->count(),
            'articles_review' => Article::where('status', 'review')->count(),
            'pending_reviews' => Review::where('status', 'pending')->count(),
            'active_subscriptions' => Subscription::where('status', 'active')->count(),
            'upcoming_events' => Event::where('status', 'published')->where('starts_at', '>', now())->count(),
            'monthly_revenue' => Payment::where('status', 'completed')
                ->whereNotNull('paid_at')
                ->whereMonth('paid_at', now()->month)
                ->whereYear('paid_at', now()->year)
                ->sum('amount'),
        ];

        $recent_users = User::latest()->take(6)->get();

        $pending_reviews = Review::with('provider', 'user')
            ->where('status', 'pending')
            ->latest()
            ->take(5)
            ->get();

        $providers_by_status = Provider::selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $subscriptions_by_plan = Subscription::with('plan')
            ->where('status', 'active')
            ->get()
            ->groupBy('plan.code')
            ->map->count();

        $revenue_by_month = $this->revenueByMonth();
        $signups_by_day = $this->signupsByDay();
        $recent_provider_activity = $this->recentProviderActivity();

        return view('dashboards.admin', compact(
            'stats',
            'recent_users',
            'pending_reviews',
            'providers_by_status',
            'subscriptions_by_plan',
            'revenue_by_month',
            'signups_by_day',
            'recent_provider_activity',
        ));
    }

    /**
     * Agrège les dernières actions des prestataires (auto-gestion de leur espace) en un
     * flux unique, tous types de contenu confondus, pour supervision par l'administrateur.
     */
    private function recentProviderActivity(int $limit = 12): Collection
    {
        $items = collect();

        Accommodation::query()
            ->whereNotNull('provider_id')
            ->with('provider')
            ->latest('updated_at')
            ->take($limit)
            ->get()
            ->each(function (Accommodation $a) use ($items) {
                $items->push([
                    'icon' => 'fa-bed',
                    'color' => 'orange',
                    'label' => 'Fiche hébergement mise à jour',
                    'title' => $a->name,
                    'provider' => $a->provider?->name,
                    'date' => $a->updated_at,
                    'url' => route('admin.accommodations.edit', $a),
                ]);
            });

        Reservation::query()
            ->whereNotNull('provider_id')
            ->with('provider')
            ->latest('created_at')
            ->take($limit)
            ->get()
            ->each(function (Reservation $r) use ($items) {
                $items->push([
                    'icon' => 'fa-calendar-check',
                    'color' => 'emerald',
                    'label' => 'Nouvelle réservation reçue',
                    'title' => $r->full_name.' — '.$r->room_name,
                    'provider' => $r->provider?->name,
                    'date' => $r->created_at,
                    'url' => route('admin.reservations.show', $r),
                ]);
            });

        Media::query()
            ->where('mediable_type', Provider::class)
            ->with('mediable')
            ->latest('created_at')
            ->take($limit)
            ->get()
            ->each(function (Media $m) use ($items) {
                $provider = $m->mediable;
                $items->push([
                    'icon' => 'fa-image',
                    'color' => 'sky',
                    'label' => 'Photo ajoutée à la fiche',
                    'title' => $provider?->name ?? 'Prestataire',
                    'provider' => $provider?->name,
                    'date' => $m->created_at,
                    'url' => $provider ? route('admin.providers.content', $provider) : route('admin.providers.index'),
                ]);
            });

        AccommodationMedia::query()
            ->whereHas('accommodation', fn ($q) => $q->whereNotNull('provider_id'))
            ->with('accommodation.provider')
            ->latest('created_at')
            ->take($limit)
            ->get()
            ->each(function (AccommodationMedia $m) use ($items) {
                $items->push([
                    'icon' => 'fa-images',
                    'color' => 'sky',
                    'label' => 'Photo ajoutée à l\'hébergement',
                    'title' => $m->accommodation?->name ?? 'Hébergement',
                    'provider' => $m->accommodation?->provider?->name,
                    'date' => $m->created_at,
                    'url' => $m->accommodation ? route('admin.accommodations.edit', $m->accommodation) : route('admin.accommodations.index'),
                ]);
            });

        Event::query()
            ->whereNotNull('provider_id')
            ->with('provider')
            ->latest('created_at')
            ->take($limit)
            ->get()
            ->each(function (Event $e) use ($items) {
                $items->push([
                    'icon' => 'fa-calendar-days',
                    'color' => 'violet',
                    'label' => 'Événement ajouté',
                    'title' => $e->title_fr,
                    'provider' => $e->provider?->name,
                    'date' => $e->created_at,
                    'url' => $e->provider ? route('admin.providers.content', $e->provider) : route('admin.events.index'),
                ]);
            });

        Article::query()
            ->whereNotNull('sponsor_id')
            ->with('sponsor')
            ->latest('created_at')
            ->take($limit)
            ->get()
            ->each(function (Article $a) use ($items) {
                $items->push([
                    'icon' => 'fa-newspaper',
                    'color' => 'orange',
                    'label' => 'Article sponsorisé ajouté',
                    'title' => $a->title_fr,
                    'provider' => $a->sponsor?->name,
                    'date' => $a->created_at,
                    'url' => $a->sponsor ? route('admin.providers.content', $a->sponsor) : route('admin.articles.index'),
                ]);
            });

        ReviewReply::query()
            ->with('provider')
            ->latest('created_at')
            ->take($limit)
            ->get()
            ->each(function (ReviewReply $r) use ($items) {
                $items->push([
                    'icon' => 'fa-reply',
                    'color' => 'rose',
                    'label' => 'Réponse à un avis',
                    'title' => mb_strimwidth($r->reply_text, 0, 80, '…'),
                    'provider' => $r->provider?->name,
                    'date' => $r->created_at,
                    'url' => route('admin.providers.index'),
                ]);
            });

        return $items
            ->filter(fn (array $item) => $item['date'] !== null)
            ->sortByDesc(fn (array $item) => $item['date']->timestamp)
            ->take($limit)
            ->values();
    }

    private function revenueByMonth(): array
    {
        $labels = [];
        $values = [];

        for ($i = 11; $i >= 0; $i--) {
            $month = Carbon::now()->subMonths($i);
            $labels[] = self::MOIS_FR[$month->month - 1];
            $values[] = (float) Payment::where('status', 'completed')
                ->whereNotNull('paid_at')
                ->whereMonth('paid_at', $month->month)
                ->whereYear('paid_at', $month->year)
                ->sum('amount');
        }

        return ['labels' => $labels, 'values' => $values];
    }

    private function signupsByDay(): array
    {
        $labels = [];
        $values = [];

        for ($i = 13; $i >= 0; $i--) {
            $day = Carbon::now()->subDays($i);
            $labels[] = $day->isoFormat('D/M');
            $values[] = User::whereDate('created_at', $day->toDateString())->count();
        }

        return ['labels' => $labels, 'values' => $values];
    }
}
