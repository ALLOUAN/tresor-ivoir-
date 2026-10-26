<?php

use App\Http\Controllers\Admin\AiInsightsController as AdminAiInsightsController;
use App\Http\Controllers\Admin\AiModerationController as AdminAiModerationController;
use App\Http\Controllers\DirectionsController;
use App\Http\Controllers\ChatAssistantController;
use App\Http\Controllers\RecommendationController;
use App\Http\Controllers\SmartSearchController;
use App\Http\Controllers\TripPlannerController;
use App\Http\Controllers\WritingAssistantController;
use App\Http\Controllers\AccommodationController;
use App\Http\Controllers\LeisureVenueController;
use App\Http\Controllers\RestaurantController;
use App\Http\Controllers\TouristExperienceController;
use App\Http\Controllers\TouristVisitController;
use App\Http\Controllers\TravelAgencyController;
use App\Http\Controllers\TransportCompanyController;
use App\Http\Controllers\Admin\ConversationReportController as AdminConversationReportController;
use App\Http\Controllers\Provider\ClientConversationController as ProviderClientConversationController;
use App\Http\Controllers\VisitorConversationController;
use App\Http\Controllers\ArtworkPublicController;
use App\Http\Controllers\ArtworkPurchaseController;
use App\Http\Controllers\Provider\ArtworkController as ProviderArtworkController;
use App\Http\Controllers\Provider\ArtworkOrderController as ProviderArtworkOrderController;
use App\Http\Controllers\Provider\MenuController as ProviderMenuController;
use App\Http\Controllers\Provider\TourController as ProviderTourController;
use App\Http\Controllers\Provider\ActivityController as ProviderActivityController;
use App\Http\Controllers\Provider\TransportController as ProviderTransportController;
use App\Http\Controllers\Admin\ArtworkManagementController;
use App\Http\Controllers\Admin\MenuManagementController;
use App\Http\Controllers\Admin\TourManagementController;
use App\Http\Controllers\Admin\ActivityManagementController;
use App\Http\Controllers\Admin\TransportManagementController;
use App\Http\Controllers\Admin\ArtOrderManagementController;
use App\Http\Controllers\Admin\TouristVisitManagementController;
use App\Http\Controllers\Admin\AdminAnalyticsController;
use App\Http\Controllers\Admin\AdminAuditLogController;
use App\Http\Controllers\Admin\AdministrationController;
use App\Http\Controllers\Admin\ArticleManagementController;
use App\Http\Controllers\Admin\ContactMessageController;
use App\Http\Controllers\Admin\ConversationController as AdminConversationController;
use App\Http\Controllers\Admin\EventManagementController;
use App\Http\Controllers\Admin\FinanceManagementController;
use App\Http\Controllers\Admin\InformationCenterController;
use App\Http\Controllers\Admin\NewsletterManagementController;
use App\Http\Controllers\Admin\PartnerController;
use App\Http\Controllers\Admin\PermissionManagementController;
use App\Http\Controllers\Admin\PlanManagementController;
use App\Http\Controllers\Admin\ProviderManagementController;
use App\Http\Controllers\Admin\ProviderVerificationController as AdminProviderVerificationController;
use App\Http\Controllers\Admin\ReservationController as AdminReservationController;
use App\Http\Controllers\Admin\WalletController as AdminWalletController;
use App\Http\Controllers\Admin\ReviewManagementController;
use App\Http\Controllers\Admin\BookingRequestController as AdminBookingRequestController;
use App\Http\Controllers\BookingRequestController;
use App\Http\Controllers\Provider\BookingRequestController as ProviderBookingRequestController;
use App\Http\Controllers\Admin\UserRoleManagementController;
use App\Http\Controllers\ArticleCommentController;
use App\Http\Controllers\ArticleController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\Dashboard\AdminDashboardController;
use App\Http\Controllers\Dashboard\EditorDashboardController;
use App\Http\Controllers\Dashboard\ProviderDashboardController;
use App\Http\Controllers\Dashboard\VisitorDashboardController;
use App\Http\Controllers\Editor\ArticleController as EditorArticleController;
use App\Http\Controllers\Editor\EventController as EditorEventController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\GuestRegistrationController;
use App\Http\Controllers\InformationPageController;
use App\Http\Controllers\Provider\AccommodationController as ProviderAccommodationController;
use App\Http\Controllers\Provider\LeisureVenueController as ProviderLeisureVenueController;
use App\Http\Controllers\Provider\RestaurantController as ProviderRestaurantController;
use App\Http\Controllers\Provider\TouristExperienceController as ProviderTouristExperienceController;
use App\Http\Controllers\Provider\TouristVisitController as ProviderTouristVisitController;
use App\Http\Controllers\Provider\TouristVisitSessionController as ProviderTouristVisitSessionController;
use App\Http\Controllers\Provider\TouristVisitTypeController as ProviderTouristVisitTypeController;
use App\Http\Controllers\Admin\TouristVisitTypeController as AdminTouristVisitTypeController;
use App\Http\Controllers\Provider\TravelAgencyController as ProviderTravelAgencyController;
use App\Http\Controllers\Provider\TransportCompanyController as ProviderTransportCompanyController;
use App\Http\Controllers\Provider\BillingController;
use App\Http\Controllers\Provider\ConversationController as ProviderConversationController;
use App\Http\Controllers\Provider\MediaController as ProviderMediaController;
use App\Http\Controllers\Provider\NotificationController as ProviderNotificationController;
use App\Http\Controllers\Provider\PaymentController;
use App\Http\Controllers\Provider\ProfileController as ProviderProfileController;
use App\Http\Controllers\Provider\ProviderAnalyticsController;
use App\Http\Controllers\Provider\ReservationController as ProviderReservationController;
use App\Http\Controllers\Provider\WalletController as ProviderWalletController;
use App\Http\Controllers\Provider\VerificationController as ProviderVerificationController;
use App\Http\Controllers\Provider\ReviewController as ProviderReviewController;
use App\Http\Controllers\ProviderController;
use App\Http\Controllers\EstablishmentController;
use App\Http\Controllers\PublicContactController;
use App\Http\Controllers\ReservationController;
use App\Http\Controllers\ReservationPaymentController;
use App\Http\Controllers\ReservationReceiptController;
use App\Http\Controllers\MediaPurchaseController;
use App\Http\Controllers\PublicHomeGalleryController;
use App\Http\Controllers\GalleryLikeController;
use App\Http\Controllers\GalleryDownloadController;
use App\Http\Controllers\PublicNewsletterController;
use App\Http\Controllers\PublicSubscriptionController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\Admin\NotificationController as AdminNotificationController;
use App\Http\Controllers\VisitorFavoriteController;
use App\Http\Controllers\VisitorNotificationController;
use App\Http\Controllers\VisitorProfileController;
use App\Http\Controllers\VisitorReservationController;
use App\Http\Controllers\VisitorWalletController;
use App\Http\Controllers\VisitorPurchaseController;
use App\Http\Controllers\VisitorArtOrderController;
use App\Http\Controllers\TouristController;
use App\Http\Controllers\CulturalController;
use App\Http\Controllers\Admin\TouristManagementController;
use App\Http\Controllers\Admin\CulturalManagementController;
use App\Http\Controllers\Admin\AccommodationManagementController;
use App\Http\Controllers\Admin\LeisureVenueManagementController;
use App\Http\Controllers\Admin\RestaurantManagementController;
use App\Http\Controllers\Admin\TouristExperienceManagementController;
use App\Http\Controllers\Admin\TravelAgencyManagementController;
use App\Http\Controllers\Admin\TransportCompanyManagementController;
use App\Http\Controllers\Admin\PrestationManagementController;
use App\Http\Controllers\Admin\HomepageBubbleManagementController;
use App\Http\Controllers\Admin\FooterImageController;
use App\Http\Controllers\Admin\RegionsSectionImageController;
use App\Http\Controllers\Admin\AnnuaireSectionImageController;
use App\Http\Controllers\Admin\CulturesSectionImageController;
use App\Http\Controllers\Admin\HeaderImageController;
use App\Http\Controllers\Admin\EvenementsSectionImageController;
use App\Http\Controllers\Admin\PartenairesSectionImageController;
use App\Http\Controllers\Admin\ArticlesSectionImageController;
use App\Http\Controllers\Admin\TouristHeroImageController;
use App\Http\Controllers\Admin\CulturalHeroImageController;
use App\Http\Controllers\Admin\ArticlesHeroImageController;
use App\Http\Controllers\Admin\ProvidersHeroImageController;
use App\Http\Controllers\Admin\EventsHeroImageController;
use App\Http\Controllers\Admin\GalleryHeroImageController;
use App\Http\Controllers\Admin\LoginBackgroundImageController;
use App\Http\Controllers\Admin\PlansSectionImageController;
use App\Http\Controllers\Admin\RecoSectionImageController;
use App\Http\Controllers\Admin\ArtSectionImageController;
use App\Http\Controllers\Admin\SearchPageImageController;
use App\Http\Controllers\PrestationController;
use App\Http\Middleware\LogAdminActions;
use App\Models\AppearanceSlide;
use App\Models\Article;
use App\Models\ArticleCategory;
use App\Models\Artwork;
use App\Models\Event;
use App\Models\InformationPage;
use App\Models\Partner;
use App\Models\RegionsSectionImage;
use App\Models\AnnuaireSectionImage;
use App\Models\CulturesSectionImage;
use App\Models\EvenementsSectionImage;
use App\Models\PartenairesSectionImage;
use App\Models\ArticlesSectionImage;
use App\Models\Provider;
use App\Models\ProviderCategory;
use App\Models\TouristCity;
use App\Models\CulturalPeople;
use App\Models\CulturalDomain;
use App\Models\PaymentSetting;
use App\Models\SiteSetting;
use App\Models\SubscriptionPlan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;

// ── PAGE D'ACCUEIL ────────────────────────────────────────────────────────
Route::get('/', function () {
    $homeEvents = Event::query()
        ->with('category')
        ->where('status', 'published')
        ->where('starts_at', '>=', now())
        ->orderBy('starts_at')
        ->limit(3)
        ->get();

    $homeProviders = Provider::query()
        ->with('category', 'media', 'accommodation')
        ->where('status', 'active')
        ->orderByDesc('is_featured')
        ->orderByDesc('rating_avg')
        ->limit(4)
        ->get();

    $homeArtworks = Artwork::published()
        ->with(['provider', 'category'])
        ->orderByDesc('is_featured')
        ->latest()
        ->limit(6)
        ->get();

    $heroSlides = Schema::hasTable('appearance_slides')
        ? AppearanceSlide::query()
            ->where('is_active', true)
            ->where(function ($q) {
                // Slide image avec visuel desktop OU slide vidéo avec vidéo desktop
                $q->where(function ($q2) {
                    $q2->where('media_type', 'image')->whereNotNull('desktop_image_url');
                })->orWhere(function ($q2) {
                    $q2->where('media_type', 'video')->whereNotNull('video_desktop_url');
                });
            })
            ->orderBy('display_order')
            ->orderByDesc('id')
            ->get()
        : collect();

    $homePartners = Schema::hasTable('partners')
        ? Partner::query()
            ->where('is_active', true)
            ->orderByDesc('is_featured')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->limit(12)
            ->get()
        : collect();

    $informationPages = Schema::hasTable('information_pages')
        ? InformationPage::query()->orderBy('sort_order')->orderBy('id')->get()
        : collect();

    $homeDestinationArticleId = null;
    $hideHomeHeroArticle = false;
    $articleWith = ['category', 'author'];
    if (Schema::hasTable('article_uploader')) {
        $articleWith[] = 'uploaders';
    }
    if (Schema::hasTable('site_settings') && Schema::hasColumn('site_settings', 'home_destination_article_id')) {
        $homeDestinationArticleId = SiteSetting::query()->value('home_destination_article_id');
        $hideHomeHeroArticle = $homeDestinationArticleId !== null && (int) $homeDestinationArticleId === 0;
    }

    // Les 5 emplacements « à la une » (1 principal + 4 secondaires, voir la
    // section Articles de welcome.blade.php) respectent la position choisie
    // par l'éditeur sur chaque article (featured_position, 1 à 5) quand elle
    // est renseignée ; les emplacements restants et le reste du pool (jusqu'à
    // 15, comme avant) se remplissent ensuite par date de publication.
    $homeArticles = Schema::hasTable('articles')
        ? (function () use ($articleWith) {
            $featured = Article::where('status', 'published')
                ->where('published_at', '<=', now())
                ->where('is_featured', true)
                ->whereNotNull('featured_position')
                ->with($articleWith)
                ->orderBy('featured_position')
                ->limit(5)
                ->get();

            $others = Article::where('status', 'published')
                ->where('published_at', '<=', now())
                ->whereNotIn('id', $featured->pluck('id'))
                ->with($articleWith)
                ->latest('published_at')
                ->limit(max(0, 15 - $featured->count()))
                ->get();

            return $featured->concat($others)->values();
        })()
        : collect();

    $homeDestinationArticle = null;
    if (! $hideHomeHeroArticle && $homeDestinationArticleId && Schema::hasTable('articles')) {
        $homeDestinationArticle = Article::query()
            ->whereKey($homeDestinationArticleId)
            ->where('status', 'published')
            ->where('published_at', '<=', now())
            ->with($articleWith)
            ->first();

        if ($homeDestinationArticle && ! $homeArticles->contains('id', $homeDestinationArticle->id)) {
            $homeArticles = $homeArticles->prepend($homeDestinationArticle)->take(8)->values();
        }
    }

    $homeCategories = Schema::hasTable('article_categories')
        ? ArticleCategory::where('is_active', true)
            ->withCount(['articles as articles_count' => fn ($q) => $q
                ->where('status', 'published')
                ->where('published_at', '<=', now())])
            ->orderBy('sort_order')
            ->get()
        : collect();

    $homeProviderCategories = Schema::hasTable('provider_categories')
        ? ProviderCategory::where('is_active', true)
            ->whereNull('parent_id')
            ->orderBy('sort_order')
            ->limit(6)
            ->get()
        : collect();

    // Onglets « secteur » de la section Annuaire : les établissements de chaque secteur
    // tels qu'ils apparaissent sur la page de listing du secteur (hôtels, restaurants…).
    $homeSectorShowcase = \App\Support\HomeSectorShowcase::build($homeProviderCategories);
    $homeSectorFeatured = \App\Support\HomeSectorShowcase::featured($homeSectorShowcase);

    // Repli pour un secteur sans page dédiée : ses prestataires actifs (annuaire).
    $homeProvidersBySector = $homeProviderCategories->reject(fn ($root) => isset($homeSectorShowcase[$root->slug]))->mapWithKeys(function ($root) {
        $categoryIds = ProviderCategory::where('id', $root->id)
            ->orWhere('parent_id', $root->id)
            ->pluck('id');

        return [$root->slug => Provider::query()
            ->with('category', 'media', 'accommodation')
            ->where('status', 'active')
            ->whereIn('category_id', $categoryIds)
            ->orderByDesc('is_featured')
            ->orderByDesc('rating_avg')
            ->limit(4)
            ->get()];
    });

    $homeTouristCities = Schema::hasTable('tourist_cities')
        ? TouristCity::where('is_active', 1)
            ->withCount(['sites as sites_count' => fn ($q) => $q->where('is_active', 1)])
            ->orderBy('is_featured', 'desc')
            ->orderBy('sort_order')
            ->limit(8)
            ->get()
        : collect();

    $homeCulturalPeoples = Schema::hasTable('cultural_peoples')
        ? CulturalPeople::where('is_active', 1)
            ->orderBy('is_featured', 'desc')
            ->orderBy('sort_order')
            ->limit(8)
            ->get()
        : collect();

    $homeCulturalDomains = Schema::hasTable('cultural_domains')
        ? CulturalDomain::whereNull('parent_id')
            ->where('is_active', 1)
            ->orderBy('sort_order')
            ->limit(8)
            ->get()
        : collect();

    $regionsImage = Schema::hasTable('regions_section_images')
        ? RegionsSectionImage::query()->find(1)
        : null;

    $annuaireImage = Schema::hasTable('annuaire_section_images')
        ? AnnuaireSectionImage::query()->find(1)
        : null;

    $culturesImage = Schema::hasTable('cultures_section_images')
        ? CulturesSectionImage::query()->find(1)
        : null;

    $evenementsImage = Schema::hasTable('evenements_section_images')
        ? EvenementsSectionImage::query()->find(1)
        : null;

    $partenairesImage = Schema::hasTable('partenaires_section_images')
        ? PartenairesSectionImage::query()->find(1)
        : null;

    $articlesImage = Schema::hasTable('articles_section_images')
        ? ArticlesSectionImage::query()->find(1)
        : null;

    return view('welcome', compact(
        'homeEvents', 'homeProviders', 'homeArtworks', 'heroSlides', 'homePartners',
        'informationPages', 'homeArticles', 'homeCategories', 'homeProviderCategories',
        'homeSectorShowcase', 'homeSectorFeatured', 'homeProvidersBySector', 'homeDestinationArticle', 'hideHomeHeroArticle', 'homeTouristCities',
        'homeCulturalPeoples', 'homeCulturalDomains', 'regionsImage',
        'annuaireImage', 'culturesImage', 'evenementsImage', 'partenairesImage', 'articlesImage'
    ));
})->name('home');

Route::get('/galerie-tresors-ivoire/visuelle/{uuid}', [PublicHomeGalleryController::class, 'show'])
    ->where('uuid', '[0-9a-fA-F]{8}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{12}')
    ->name('gallery.public.show');
Route::post('/galerie-tresors-ivoire/visuelle/{uuid}/jaime', [GalleryLikeController::class, 'toggle'])
    ->where('uuid', '[0-9a-fA-F]{8}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{12}')
    ->middleware('throttle:30,1')
    ->name('gallery.like.toggle');
Route::post('/galerie-tresors-ivoire/visuelle/{uuid}/telechargement', [GalleryDownloadController::class, 'track'])
    ->where('uuid', '[0-9a-fA-F]{8}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{12}')
    ->middleware('throttle:30,1')
    ->name('gallery.download.track');
Route::get('/galerie-tresors-ivoire', [PublicHomeGalleryController::class, 'index'])->name('gallery.public');
// Médiathèque — vidéos (lecteur plein écran, vues, j'aime, partages, clics « Voir et réserver »).
Route::get('/mediatheque/videos', [\App\Http\Controllers\VideoController::class, 'index'])->name('videos.index');
Route::get('/mediatheque/videos/{video}', [\App\Http\Controllers\VideoController::class, 'show'])->name('videos.show');
Route::post('/mediatheque/videos/{video}/vue', [\App\Http\Controllers\VideoController::class, 'view'])->middleware('throttle:60,1')->name('videos.view');
Route::post('/mediatheque/videos/{video}/jaime', [\App\Http\Controllers\VideoController::class, 'like'])->middleware('throttle:30,1')->name('videos.like');
Route::post('/mediatheque/videos/{video}/partage', [\App\Http\Controllers\VideoController::class, 'share'])->middleware('throttle:30,1')->name('videos.share');
Route::post('/mediatheque/videos/{video}/clic', [\App\Http\Controllers\VideoController::class, 'click'])->middleware('throttle:60,1')->name('videos.click');
// « Cinéma d'Ivoire » (Médiathèque) — films documentaires et de fiction.
Route::get('/cinema-d-ivoire', [\App\Http\Controllers\FilmController::class, 'index'])->name('films.index');
Route::get('/cinema-d-ivoire/proposer', [\App\Http\Controllers\FilmController::class, 'create'])->name('films.submit');
Route::post('/cinema-d-ivoire/proposer', [\App\Http\Controllers\FilmController::class, 'store'])->middleware('throttle:5,10')->name('films.submit.store');
Route::get('/cinema-d-ivoire/{kindSlug}', [\App\Http\Controllers\FilmController::class, 'index'])->whereIn('kindSlug', ['documentaires', 'fictions'])->name('films.kind');
Route::get('/cinema-d-ivoire/film/{film}', [\App\Http\Controllers\FilmController::class, 'show'])->name('films.show');
Route::get('/cinema-d-ivoire/film/{film}/regarder', [\App\Http\Controllers\FilmController::class, 'watch'])->middleware('throttle:60,1')->name('films.watch');
// « Les Astuces de Mian » — bandes dessinées (planches en images).
Route::get('/astuces-de-mian', [\App\Http\Controllers\ComicController::class, 'index'])->name('comics.index');
Route::get('/astuces-de-mian/{comic}', [\App\Http\Controllers\ComicController::class, 'show'])->name('comics.show');

// ── ACHAT MÉDIA ───────────────────────────────────────────────────────────────
Route::get('/galerie/achat/retour', [MediaPurchaseController::class, 'handleReturn'])->name('gallery.purchase.return');
Route::post('/galerie/achat/webhook', [MediaPurchaseController::class, 'webhook'])->name('gallery.purchase.webhook');
Route::get('/galerie/achat/{media:uuid}/init', [MediaPurchaseController::class, 'init'])->name('gallery.purchase.init');
Route::post('/galerie/achat/{media:uuid}/creer-et-payer', [MediaPurchaseController::class, 'registerAndPay'])->name('gallery.purchase.register')->middleware('throttle:10,1');
Route::post('/galerie/achat/{media:uuid}/payer', [MediaPurchaseController::class, 'pay'])->name('gallery.purchase.pay')->middleware(['auth', 'throttle:10,1']);

// ── ART & CRÉATIONS ────────────────────────────────────────────────────────────
Route::get('/art-creations', [ArtworkPublicController::class, 'index'])->name('art.index');
// Art & Créations : page artiste, devis d'achat, offres, sur mesure.
Route::get('/artistes/{slug}', [\App\Http\Controllers\ArtMarketController::class, 'artist'])->name('artist.show');
Route::post('/artistes/{slug}/suivre', [\App\Http\Controllers\ArtMarketController::class, 'follow'])->name('artist.follow')->middleware(['auth', 'throttle:20,1']);
Route::get('/artistes/{slug}/sur-mesure', [\App\Http\Controllers\ArtMarketController::class, 'commissionForm'])->name('artist.commission');
Route::post('/artistes/{slug}/sur-mesure', [\App\Http\Controllers\ArtMarketController::class, 'requestCommission'])->name('artist.commission.store')->middleware(['auth', 'throttle:5,1']);
Route::get('/art-creations/devis/{artwork:uuid}', [\App\Http\Controllers\ArtMarketController::class, 'quote'])->name('art.quote')->middleware('throttle:60,1');
Route::post('/art-creations/commander/{artwork:uuid}', [ArtworkPurchaseController::class, 'checkout'])->name('art.checkout')->middleware(['auth', 'throttle:10,1']);
Route::post('/art-creations/offre/{artwork:uuid}', [\App\Http\Controllers\ArtMarketController::class, 'makeOffer'])->name('art.offer')->middleware(['auth', 'throttle:10,1']);
Route::get('/sur-mesure/paiement/retour', [\App\Http\Controllers\ArtMarketController::class, 'commissionReturn'])->name('art-commission.payment.return');
Route::post('/sur-mesure/paiement/webhook', [\App\Http\Controllers\ArtMarketController::class, 'commissionWebhook'])->name('art-commission.payment.webhook');
Route::get('/art-creations/{artwork:slug}', [ArtworkPublicController::class, 'show'])->name('art.show');
Route::get('/art-creations/achat/retour', [ArtworkPurchaseController::class, 'handleReturn'])->name('art.purchase.return');
Route::post('/art-creations/achat/webhook', [ArtworkPurchaseController::class, 'webhook'])->name('art.purchase.webhook');
Route::get('/art-creations/achat/{artwork:uuid}/init', [ArtworkPurchaseController::class, 'init'])->name('art.purchase.init');
Route::post('/art-creations/achat/{artwork:uuid}/creer-et-payer', [ArtworkPurchaseController::class, 'registerAndPay'])->name('art.purchase.register')->middleware('throttle:10,1');
Route::post('/art-creations/achat/{artwork:uuid}/payer', [ArtworkPurchaseController::class, 'pay'])->name('art.purchase.pay')->middleware(['auth', 'throttle:10,1']);

Route::post('/contact', [PublicContactController::class, 'store'])
    ->name('contact.store')
    ->middleware('throttle:8,1');

Route::get('/reservations/disponibilite', [ReservationController::class, 'availability'])
    ->name('reservations.availability')
    ->middleware('throttle:60,1');

Route::get('/reservations/devis', [ReservationController::class, 'quote'])->name('reservations.quote')->middleware('throttle:60,1');
Route::post('/reservations', [ReservationController::class, 'store'])
    ->name('reservations.store')
    ->middleware(['auth', 'verified', 'throttle:10,1']);

Route::post('/reservations/paiement/initier', [ReservationPaymentController::class, 'initiate'])
    ->name('reservations.payment.initiate')
    ->middleware(['auth', 'verified', 'throttle:10,1']);
Route::get('/reservations/paiement/retour', [ReservationPaymentController::class, 'returnFromGateway'])
    ->name('reservations.payment.return');
Route::post('/reservations/paiement/webhook', [ReservationPaymentController::class, 'webhook'])
    ->name('reservations.payment.webhook');
Route::get('/reservations/{reservation}/confirmation', [ReservationPaymentController::class, 'confirmation'])
    ->name('reservations.payment.confirmation')
    ->middleware('signed');
Route::get('/reservations/{reservation}/paiement', [ReservationPaymentController::class, 'pay'])
    ->name('reservations.payment.pay')
    ->middleware('signed');
Route::get('/reservations/{reservation}/recu', [ReservationReceiptController::class, 'show'])
    ->name('reservations.receipt')
    ->middleware('signed');
Route::get('/reservations/{reservation}/recu/pdf', [ReservationReceiptController::class, 'pdf'])
    ->name('reservations.receipt.pdf')
    ->middleware('signed');
Route::get('/reservations/{reservation}/enregistrement', [GuestRegistrationController::class, 'create'])
    ->name('reservations.guest-registration')
    ->middleware('signed');
Route::post('/reservations/{reservation}/enregistrement', [GuestRegistrationController::class, 'store'])
    ->name('reservations.guest-registration.store')
    ->middleware(['signed', 'throttle:10,1']);
// Pièces de la fiche (disque privé) : uniquement par lien signé de courte durée.
Route::get('/enregistrements/{registration}/piece/{kind}', [GuestRegistrationController::class, 'file'])
    ->name('guest-registrations.file')
    ->middleware(['signed', 'throttle:60,1']);

Route::get('/wallet/recharge/retour', [VisitorWalletController::class, 'returnFromGateway'])
    ->name('visitor.wallet.topup.return');
Route::post('/wallet/recharge/webhook', [VisitorWalletController::class, 'webhook'])
    ->name('visitor.wallet.topup.webhook');

Route::post('/newsletter/subscribe', [PublicNewsletterController::class, 'subscribe'])
    ->name('newsletter.subscribe')
    ->middleware('throttle:10,1');

Route::get('/newsletter/desabonnement/{subscriber}', [PublicNewsletterController::class, 'unsubscribe'])
    ->middleware(['signed', 'throttle:20,1'])
    ->name('newsletter.unsubscribe');

// ── CENTRE D'INFORMATION (pages publiques) ────────────────────────────────
Route::get('/information/{informationPage}', [InformationPageController::class, 'show'])
    ->name('information.show');

// ── RECHERCHE GLOBALE ─────────────────────────────────────────────────────
Route::get('/recherche', [SearchController::class, 'index'])->name('search');
Route::get('/recherche/suggestions', [SearchController::class, 'suggestions'])
    ->name('search.suggestions')
    ->middleware('throttle:30,1');

// ── SITEMAP & RSS ─────────────────────────────────────────────────────────
Route::get('/sitemap.xml', function () {
    $articles  = \App\Models\Article::where('status', 'published')->where('published_at', '<=', now())
        ->select('slug_fr', 'published_at', 'updated_at')->latest('published_at')->limit(1000)->get();
    $events    = \App\Models\Event::where('status', 'published')
        ->select('slug', 'updated_at')->limit(500)->get();
    $providers = \App\Models\Provider::where('status', 'active')
        ->select('slug', 'updated_at')->limit(500)->get();
    $pages     = \App\Models\InformationPage::select('id', 'slug', 'updated_at')->get();
    // Fiches détaillées du tourisme et de la culture : [nom de route, lignes actives].
    $active = fn (string $model) => $model::where('is_active', true)->select('slug', 'updated_at')->limit(1000)->get();
    $listings = [
        ['accommodations.show', $active(\App\Models\Accommodation::class), 0.7],
        ['restaurant.show', $active(\App\Models\Restaurant::class), 0.6],
        ['leisure.show', $active(\App\Models\LeisureVenue::class), 0.6],
        ['tourist-experience.show', $active(\App\Models\TouristExperience::class), 0.7],
        ['tourist.city', $active(\App\Models\TouristCity::class), 0.7],
        ['cultural.people', $active(\App\Models\CulturalPeople::class), 0.6],
        ['bundles.show', $active(\App\Models\Bundle::class), 0.7],
    ];

    return response()->view('sitemap', compact('articles', 'events', 'providers', 'pages', 'listings'))
        ->header('Content-Type', 'application/xml');
})->name('sitemap');

Route::get('/rss.xml', function () {
    $articles = \App\Models\Article::where('status', 'published')
        ->where('published_at', '<=', now())
        ->with(['category', 'author'])
        ->latest('published_at')
        ->limit(30)
        ->get();

    return response()->view('rss', compact('articles'))
        ->header('Content-Type', 'application/rss+xml; charset=UTF-8');
})->name('rss');

// ── ARTICLES PUBLICS ──────────────────────────────────────────────────────
Route::get('/articles', [ArticleController::class, 'index'])->name('articles.index');
Route::get('/articles/{slug}/photos.zip', [\App\Http\Controllers\ArticlePhotoController::class, 'zip'])->name('articles.photos.zip')->middleware('throttle:10,1');
Route::get('/articles/{slug}/photos/couverture', [\App\Http\Controllers\ArticlePhotoController::class, 'cover'])->name('articles.photos.cover')->middleware('throttle:60,1');
Route::get('/articles/{slug}/photos/{media}', [\App\Http\Controllers\ArticlePhotoController::class, 'download'])->name('articles.photos.download')->whereNumber('media')->middleware('throttle:60,1');
Route::get('/articles/{slug}', [ArticleController::class, 'show'])->name('articles.show');
Route::post('/articles/{article}/commentaires', [ArticleCommentController::class, 'store'])
    ->name('articles.comments.store')
    ->middleware('throttle:5,1');

// ── DÉCOUVERTES PUBLIQUES ─────────────────────────────────────────────────
Route::get('/decouvertes', function () {
    $discoverCategories = Schema::hasTable('article_categories')
        ? ArticleCategory::where('is_active', true)
            ->withCount(['articles as articles_count' => fn ($q) => $q
                ->where('status', 'published')
                ->where('published_at', '<=', now())])
            ->orderBy('sort_order')
            ->get()
        : collect();

    $discoverArticles = Schema::hasTable('articles')
        ? Article::where('status', 'published')
            ->where('published_at', '<=', now())
            ->with(['category', 'author'])
            ->latest('published_at')
            ->limit(9)
            ->get()
        : collect();

    return view('discoveries.index', compact('discoverCategories', 'discoverArticles'));
})->name('discoveries.index');

// ── ÉVÉNEMENTS PUBLICS ────────────────────────────────────────────────────
Route::get('/evenements', [EventController::class, 'index'])->name('events.index');
Route::get('/evenements/{slug}', [EventController::class, 'show'])->name('events.show');
Route::get('/evenements/{slug}/calendrier.ics', [EventController::class, 'downloadIcs'])->name('events.ics');

// ── PLANS D'ABONNEMENT PUBLICS ────────────────────────────────────────────
Route::get('/abonnements', [PublicSubscriptionController::class, 'index'])->name('plans.public');

Route::get('/abonnements/{plan}/paiement', [PublicSubscriptionController::class, 'checkout'])
    ->name('subscriptions.checkout');
Route::post('/abonnements/{plan}/traiter', [PublicSubscriptionController::class, 'processOffline'])
    ->middleware('auth')
    ->name('subscriptions.process-offline');

// ── ANNUAIRE PRESTATAIRES PUBLIC ──────────────────────────────────────────
Route::get('/annuaire', [ProviderController::class, 'index'])->name('providers.index');
Route::get('/annuaire/{slug}', [ProviderController::class, 'show'])->name('providers.show');

// ── IMAGES REDIMENSIONNÉES (variantes WebP en cache, sans session ni cookie) ─
Route::get('/img/{width}/{path}', [\App\Http\Controllers\ImageController::class, 'show'])
    ->where('width', '[0-9]+')
    ->where('path', '.+')
    ->withoutMiddleware([
        \Illuminate\Cookie\Middleware\EncryptCookies::class,
        \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
        \Illuminate\Session\Middleware\StartSession::class,
        \Illuminate\View\Middleware\ShareErrorsFromSession::class,
        \Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class,
        \Illuminate\Foundation\Http\Middleware\PreventRequestForgery::class,
        \App\Http\Middleware\SetLocaleMiddleware::class,
        \App\Http\Middleware\SiteMaintenanceMiddleware::class,
        \App\Http\Middleware\OptimizeImages::class,
    ])
    ->name('img');

// Image de partage (aperçu des liens sur les réseaux sociaux) : JPEG 1200 × 630, mêmes exclusions que /img
Route::get('/img/og/{path}', [\App\Http\Controllers\ImageController::class, 'share'])
    ->where('path', '.+\.jpg')
    ->withoutMiddleware([
        \Illuminate\Cookie\Middleware\EncryptCookies::class,
        \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
        \Illuminate\Session\Middleware\StartSession::class,
        \Illuminate\View\Middleware\ShareErrorsFromSession::class,
        \Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class,
        \Illuminate\Foundation\Http\Middleware\PreventRequestForgery::class,
        \App\Http\Middleware\SetLocaleMiddleware::class,
        \App\Http\Middleware\SiteMaintenanceMiddleware::class,
        \App\Http\Middleware\OptimizeImages::class,
    ])
    ->name('img.og');

// ── INTELLIGENCE ARTIFICIELLE : recommandation hybride + planificateur « Mon séjour » ──
Route::get('/pour-vous', [RecommendationController::class, 'index'])->name('ai.for-you');
Route::post('/pour-vous/profil', [RecommendationController::class, 'saveProfile'])->middleware('throttle:20,1')->name('ai.profile.save');
Route::post('/pour-vous/effacer', [RecommendationController::class, 'clear'])->middleware('throttle:10,1')->name('ai.data.clear');
Route::get('/ia/recommandations', [RecommendationController::class, 'json'])->middleware('throttle:60,1')->name('ai.recommendations.json');
Route::get('/ia/recherche', [SmartSearchController::class, 'json'])->middleware('throttle:60,1')->name('ai.search.json');
Route::post('/ia/assistant', [ChatAssistantController::class, 'ask'])->middleware('throttle:20,1')->name('ai.chat.ask');
Route::post('/ia/assistant/avis', [ChatAssistantController::class, 'feedback'])->middleware('throttle:30,1')->name('ai.chat.feedback');
Route::middleware(['auth', 'role:admin,editor,provider'])->group(function () {
    // « Mon compte » du back-office (menu utilisateur de la barre du haut).
    Route::get('/espace/mon-compte', [\App\Http\Controllers\AccountController::class, 'edit'])->name('account.edit');
    Route::put('/espace/mon-compte', [\App\Http\Controllers\AccountController::class, 'updateProfile'])->middleware('throttle:10,1')->name('account.profile.update');
    Route::put('/espace/mon-compte/mot-de-passe', [\App\Http\Controllers\AccountController::class, 'updatePassword'])->middleware('throttle:6,1')->name('account.password.update');
    Route::post('/ia/redaction',[WritingAssistantController::class, 'run'])->middleware('throttle:15,1')->name('ai.writing.run');
    Route::post('/ia/redaction/{log}/appliquee', [WritingAssistantController::class, 'applied'])->whereNumber('log')->name('ai.writing.applied');
});
Route::get('/mon-sejour', [TripPlannerController::class, 'create'])->name('trip.create');
Route::post('/mon-sejour', [TripPlannerController::class, 'store'])->middleware('throttle:10,1')->name('trip.store');
Route::get('/mon-sejour/{uuid}', [TripPlannerController::class, 'show'])->whereUuid('uuid')->name('trip.show');
Route::get('/mon-sejour/{uuid}/jour/{day}/trajet', [TripPlannerController::class, 'dayRoute'])->whereUuid('uuid')->whereNumber('day')->name('trip.day-route')->middleware('throttle:30,1');

// ── ITINÉRAIRE GOOGLE MAPS (compte le clic puis redirige) ─────────────────
Route::get('/itineraire/{type}/{slug}', [DirectionsController::class, 'go'])
    ->middleware('throttle:60,1')
    ->name('directions.go');
Route::post('/itineraire/{type}/{slug}/trajet', [DirectionsController::class, 'route'])
    ->middleware('throttle:20,1')
    ->name('directions.route');

// ── TOUS NOS ÉTABLISSEMENTS (regroupe les 6 pages de listing par secteur) ──
Route::get('/nos-etablissements', [EstablishmentController::class, 'index'])->name('establishments.index');

// ── RÉSIDENCES & HÔTELS PUBLIC ─────────────────────────────────────────────
Route::get('/residences-hotels', [AccommodationController::class, 'index'])->name('accommodations.index');
Route::get('/residences-hotels/villes', [AccommodationController::class, 'citiesForRegion'])
    ->name('accommodations.cities')
    ->middleware('throttle:30,1');
Route::get('/residences-hotels/{accommodation:slug}', [AccommodationController::class, 'show'])->name('accommodations.show');
Route::get('/residences-hotels/{accommodation:slug}/chambres', [AccommodationController::class, 'rooms'])->name('accommodations.rooms');

// ── LOISIRS & CULTURE PUBLIC (pilote du système riche par secteur) ────────
Route::get('/loisirs-culture', [LeisureVenueController::class, 'index'])->name('leisure.index');
Route::get('/loisirs-culture/{slug}', [LeisureVenueController::class, 'show'])->name('leisure.show');
Route::get('/loisirs-culture/{slug}/activites', [LeisureVenueController::class, 'activities'])->name('leisure.activities');
// Activités : créneaux en temps réel, réservation immédiate, formules, cartes multi-séances, liste d'attente.
Route::get('/activites/{slug}', [\App\Http\Controllers\LeisureBookingController::class, 'show'])->name('activity.show');
Route::get('/activites/{slug}/creneaux', [\App\Http\Controllers\LeisureBookingController::class, 'slots'])->name('activity.slots')->middleware('throttle:60,1');
Route::post('/activites/{slug}/reserver', [\App\Http\Controllers\LeisureBookingController::class, 'book'])->name('activity.book')->middleware(['auth', 'throttle:10,1']);
Route::post('/activites/{slug}/liste-attente', [\App\Http\Controllers\LeisureBookingController::class, 'joinWaitlist'])->name('activity.waitlist')->middleware(['auth', 'throttle:10,1']);
Route::get('/formules/{slug}', [\App\Http\Controllers\LeisureBookingController::class, 'package'])->name('leisure-package.show');
Route::get('/formules/{slug}/creneaux', [\App\Http\Controllers\LeisureBookingController::class, 'packageSlots'])->name('leisure-package.slots')->middleware('throttle:60,1');
Route::post('/formules/{slug}/reserver', [\App\Http\Controllers\LeisureBookingController::class, 'bookPackage'])->name('leisure-package.book')->middleware(['auth', 'throttle:10,1']);
Route::post('/cartes/{leisurePass}/acheter', [\App\Http\Controllers\LeisureBookingController::class, 'buyPass'])->name('leisure-pass.buy')->middleware(['auth', 'throttle:10,1']);
Route::get('/loisirs/paiement/retour', [\App\Http\Controllers\LeisureBookingController::class, 'handleReturn'])->name('leisure.payment.return');
Route::post('/loisirs/paiement/webhook', [\App\Http\Controllers\LeisureBookingController::class, 'webhook'])->name('leisure.payment.webhook');
Route::get('/loisirs/reservations/{activityBooking}/billet', [\App\Http\Controllers\LeisureBookingController::class, 'ticket'])->name('activity-booking.ticket')->middleware('signed');

// ── RESTAURANTS & GASTRONOMIE PUBLIC ───────────────────────────────────────
Route::get('/restaurants', [RestaurantController::class, 'index'])->name('restaurant.index');
Route::get('/restaurants/{slug}', [RestaurantController::class, 'show'])->name('restaurant.show');
Route::get('/restaurants/{slug}/carte', [RestaurantController::class, 'menu'])->name('restaurant.menu');

// ── SITES TOURISTIQUES (PRESTATAIRES) PUBLIC ───────────────────────────────
Route::get('/experiences-touristiques', [TouristExperienceController::class, 'index'])->name('tourist-experience.index');
Route::get('/experiences-touristiques/{slug}', [TouristExperienceController::class, 'show'])->name('tourist-experience.show');
Route::get('/experiences-touristiques/{slug}/activites', [TouristExperienceController::class, 'activities'])->name('tourist-experience.activities');

// Réservation de visites (individuelle, guidée, groupée)
Route::get('/experiences-touristiques/{slug}/visite/init', [TouristVisitController::class, 'init'])->name('tourist-visit.init');
Route::get('/experiences-touristiques/{slug}/visite/sessions', [TouristVisitController::class, 'sessions'])->name('tourist-visit.sessions');
Route::get('/experiences-touristiques/{slug}/visite/creneaux', [TouristVisitController::class, 'slots'])->name('tourist-visit.slots')->middleware('throttle:60,1');
Route::post('/experiences-touristiques/{slug}/visite/creer-et-payer', [TouristVisitController::class, 'registerAndPay'])->name('tourist-visit.register')->middleware('throttle:10,1');
Route::post('/experiences-touristiques/{slug}/visite/payer', [TouristVisitController::class, 'pay'])->name('tourist-visit.pay')->middleware(['auth', 'throttle:10,1']);
Route::get('/visites/retour', [TouristVisitController::class, 'handleReturn'])->name('tourist-visit.payment.return');
Route::post('/visites/webhook', [TouristVisitController::class, 'webhook'])->name('tourist-visit.payment.webhook');
Route::get('/visites/{touristVisit}/confirmation', [TouristVisitController::class, 'confirmation'])->name('tourist-visit.confirmation')->middleware('signed');

// ── AGENCES DE VOYAGES & TOURS PUBLIC ───────────────────────────────────────
Route::get('/agences-voyages', [TravelAgencyController::class, 'index'])->name('travel-agency.index');
Route::get('/agences-voyages/{slug}', [TravelAgencyController::class, 'show'])->name('travel-agency.show');
Route::get('/ical/{accommodation}/{room}/{token}.ics', [\App\Http\Controllers\IcalController::class, 'export'])
    ->whereNumber('accommodation')->where('token', '[a-f0-9]{32}')->name('ical.export')->middleware('throttle:60,1');
Route::get('/restaurants/{restaurant}/creneaux', \App\Http\Controllers\TableSlotController::class)->whereNumber('restaurant')->name('restaurant.slots')->middleware('throttle:60,1');
Route::get('/restaurants/{restaurant}/plan-de-salle', \App\Http\Controllers\TablePlanController::class)->whereNumber('restaurant')->name('restaurant.table-plan')->middleware('throttle:60,1');
Route::get('/bons-cadeaux/paiement/retour', [\App\Http\Controllers\GiftCardController::class, 'handleReturn'])->name('gift-cards.payment.return');
Route::post('/bons-cadeaux/paiement/webhook', [\App\Http\Controllers\GiftCardController::class, 'webhook'])->name('gift-cards.payment.webhook');
Route::get('/bons-cadeaux/{slug}', [\App\Http\Controllers\GiftCardController::class, 'show'])->name('gift-cards.show');
Route::post('/bons-cadeaux/{slug}', [\App\Http\Controllers\GiftCardController::class, 'store'])->name('gift-cards.store')->middleware(['auth', 'throttle:10,1']);
Route::get('/certificats/{order}/{code}.pdf', [\App\Http\Controllers\ArtCertificateController::class, 'pdf'])->whereNumber('order')->where('code', '[A-F0-9]{12}')->name('art.certificate.pdf');
Route::get('/certificats/verifier/{order}/{code}', [\App\Http\Controllers\ArtCertificateController::class, 'verify'])->whereNumber('order')->where('code', '[A-F0-9]{12}')->name('art.certificate.verify');
Route::get('/autour-de-moi', [\App\Http\Controllers\NearbyController::class, 'index'])->name('nearby.index');
Route::get('/autour-de-moi/lieux', [\App\Http\Controllers\NearbyController::class, 'places'])->name('nearby.places')->middleware('throttle:60,1');
Route::get('/offres-groupees', [\App\Http\Controllers\BundleController::class, 'index'])->name('bundles.index');
Route::get('/offres-groupees/{bundle}', [\App\Http\Controllers\BundleController::class, 'show'])->name('bundles.show');
Route::get('/agences-voyages/{slug}/circuits', [TravelAgencyController::class, 'tours'])->name('travel-agency.tours');
// Circuits : page détaillée, devis en direct, réservation immédiate d'un départ (acompte ou totalité).
Route::get('/agences-voyages/{agency}/circuits/{tour}', [\App\Http\Controllers\TourBookingController::class, 'show'])->name('travel-agency.tour');
Route::get('/agences-voyages/{agency}/circuits/{tour}/devis', [\App\Http\Controllers\TourBookingController::class, 'quote'])->name('travel-agency.tour.quote')->middleware('throttle:60,1');
Route::post('/agences-voyages/{agency}/circuits/{tour}/reserver', [\App\Http\Controllers\TourBookingController::class, 'store'])->name('travel-agency.tour.book')->middleware(['auth', 'throttle:10,1']);
Route::get('/circuits/paiement/retour', [\App\Http\Controllers\TourBookingController::class, 'handleReturn'])->name('tour-booking.payment.return');
Route::post('/circuits/paiement/webhook', [\App\Http\Controllers\TourBookingController::class, 'webhook'])->name('tour-booking.payment.webhook');
Route::get('/circuits/reservations/{tourBooking}/feuille-de-route', [\App\Http\Controllers\TourBookingController::class, 'confirmation'])->name('tour-booking.confirmation')->middleware('signed');

// ── TRANSPORTS & MOBILITÉ PUBLIC ────────────────────────────────────────────
// Transports : page d'offre, devis instantané, sièges d'une ligne, réservation immédiate, confirmation.
Route::get('/transports/offre/{slug}', [\App\Http\Controllers\TransportBookingController::class, 'show'])->name('transport-offer.show');
Route::get('/transports/offre/{slug}/devis', [\App\Http\Controllers\TransportBookingController::class, 'quote'])->name('transport-offer.quote')->middleware('throttle:60,1');
Route::get('/transports/offre/{slug}/departs/{trip}/sieges', [\App\Http\Controllers\TransportBookingController::class, 'seats'])->name('transport-offer.seats')->middleware('throttle:60,1');
Route::post('/transports/offre/{slug}/reserver', [\App\Http\Controllers\TransportBookingController::class, 'book'])->name('transport-offer.book')->middleware(['auth', 'throttle:10,1']);
Route::get('/trajets/paiement/retour', [\App\Http\Controllers\TransportBookingController::class, 'handleReturn'])->name('transport-booking.payment.return');
Route::post('/trajets/paiement/webhook', [\App\Http\Controllers\TransportBookingController::class, 'webhook'])->name('transport-booking.payment.webhook');
Route::get('/trajets/{transportBooking}', [\App\Http\Controllers\TransportBookingController::class, 'confirmation'])->name('transport-booking.show')->middleware('signed');
Route::get('/transports', [TransportCompanyController::class, 'index'])->name('transport-company.index');
Route::get('/transports/{slug}', [TransportCompanyController::class, 'show'])->name('transport-company.show');
Route::get('/transports/{slug}/offres', [TransportCompanyController::class, 'offers'])->name('transport-company.offers');

// ── NOS PRESTATIONS PUBLIC ─────────────────────────────────────────────────
Route::get('/nos-prestations', [PrestationController::class, 'show'])->name('prestations.public');

// ── TOURISME PUBLIC ────────────────────────────────────────────────────────
Route::get('/tourisme', [TouristController::class, 'cities'])->name('tourist.cities');
Route::get('/tourisme/{citySlug}', [TouristController::class, 'city'])->name('tourist.city');
Route::get('/tourisme/{citySlug}/{categorySlug}', [TouristController::class, 'category'])->name('tourist.category');
Route::get('/sites-touristiques/{slug}', [TouristController::class, 'site'])->name('tourist.site');

// ── CULTURES IVOIRIENNES PUBLIC ────────────────────────────────────────────
Route::get('/cultures', [CulturalController::class, 'peoples'])->name('cultural.peoples');
Route::get('/cultures/{slug}', [CulturalController::class, 'people'])->name('cultural.people');
Route::get('/elements-culturels/{slug}', [CulturalController::class, 'element'])->name('cultural.element');

// ── DEMANDES DE RÉSERVATION (restaurants, loisirs, circuits) ──
Route::get('/demandes-reservation/paiement/retour', [\App\Http\Controllers\BookingPaymentController::class, 'handleReturn'])->name('booking-requests.payment.return');
Route::post('/demandes-reservation/paiement/webhook', [\App\Http\Controllers\BookingPaymentController::class, 'webhook'])->name('booking-requests.payment.webhook');
Route::post('/demandes-reservation', [BookingRequestController::class, 'store'])
    ->name('booking-requests.store')
    ->middleware(['auth', 'throttle:6,60']);

// ── AVIS (POST — auth optionnel) ──────────────────────────────────────────
Route::post('/annuaire/{provider}/avis', [ReviewController::class, 'store'])
    ->name('reviews.store')
    ->middleware(['auth', 'throttle:5,60']);

// ── AUTH ──────────────────────────────────────────────────────────────────
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.post');
    Route::get('/connexion/verification', [\App\Http\Controllers\AdminTwoFactorController::class, 'show'])->name('admin.two-factor.show');
    Route::post('/connexion/verification', [\App\Http\Controllers\AdminTwoFactorController::class, 'verify'])->name('admin.two-factor.verify')->middleware('throttle:10,1');
    Route::post('/connexion/verification/renvoyer', [\App\Http\Controllers\AdminTwoFactorController::class, 'resend'])->name('admin.two-factor.resend')->middleware('throttle:3,10');
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->name('register.post')->middleware('throttle:5,1');

    // Mot de passe oublié / réinitialisation
    Route::get('/mot-de-passe-oublie', [AuthController::class, 'showForgotPassword'])->name('password.request');
    Route::post('/mot-de-passe-oublie', [AuthController::class, 'sendResetLink'])->name('password.email');
    Route::get('/reinitialiser/{token}', [AuthController::class, 'showResetPassword'])->name('password.reset');
    Route::post('/reinitialiser', [AuthController::class, 'resetPassword'])->name('password.update');
});

// Compte express pendant une réservation : code envoyé par e-mail et saisi sur place (invité ou compte non vérifié).
Route::post('/compte-express/code', [\App\Http\Controllers\ExpressAccountController::class, 'start'])
    ->name('account.express.start')->middleware('throttle:6,1');
Route::post('/compte-express/verifier', [\App\Http\Controllers\ExpressAccountController::class, 'verify'])
    ->name('account.express.verify')->middleware('throttle:10,1');

// Vérification e-mail (utilisateur connecté)
Route::middleware('auth')->group(function () {
    Route::get('/verification-email', [AuthController::class, 'showVerifyEmail'])->name('verification.notice');
    Route::post('/verification-email/code', [AuthController::class, 'verifyEmailCode'])
        ->name('verification.code')
        ->middleware('throttle:10,1');
    Route::post('/verification-email/renvoyer', [AuthController::class, 'resendVerification'])
        ->name('verification.send')
        ->middleware('throttle:6,1');
});

// ── CHANGEMENT DE LANGUE ──────────────────────────────────────────────────
Route::get('/lang/{locale}', function (string $locale) {
    if (in_array($locale, ['fr', 'en'])) {
        session(['locale' => $locale]);
    }

    return redirect()->back();
})->name('lang.switch');

Route::get('/billing/callback', [BillingController::class, 'callback'])->name('provider.billing.callback');
Route::post('/billing/webhook/{gateway}', [BillingController::class, 'webhook'])->name('provider.billing.webhook');

// ── CYNETPAY — RETOUR NAVIGATEUR (public, sans auth) ─────────────────────
Route::get('/paiement/cynetpay/retour', [PaymentController::class, 'cynetPayReturn'])
    ->name('payment.cynetpay.return');

// ── CYNETPAY — WEBHOOK SERVEUR (public, sans CSRF) ───────────────────────
Route::post('/webhook/cynetpay', [PaymentController::class, 'webhook'])
    ->name('webhook.cynetpay');

Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

// ── DASHBOARD REDIRECT ────────────────────────────────────────────────────
Route::get('/dashboard', function () {
    /** @var \App\Models\User|null $user */
    $user = Auth::user();
    if (! $user) {
        abort(403);
    }

    if (! $user->hasVerifiedEmail()) {
        return redirect()->route('verification.notice')
            ->with('status', 'Validez votre e-mail pour finaliser votre accès au tableau de bord.');
    }

    if ($user->role === 'provider') {
        $provider = Provider::forUser($user->id);
        $hasActiveSubscription = $provider
            ? $provider->subscriptions()
                ->where('status', 'active')
                ->where('ends_at', '>', now())
                ->exists()
            : false;

        if (! $hasActiveSubscription && ! \App\Models\PaymentSetting::providerFreeAccess()) {
            return redirect()->route('provider.billing.plans')
                ->with('status', 'Finalisez votre abonnement pour accéder au tableau de bord.');
        }
    }

    return redirect()->route(match ($user->role) {
        'admin' => 'admin.dashboard',
        'editor' => 'editor.dashboard',
        'provider' => 'provider.dashboard',
        default => 'visitor.dashboard',
    });
})->middleware('auth')->name('dashboard');

// ── ADMIN ─────────────────────────────────────────────────────────────────
Route::middleware(['auth', 'role:admin', LogAdminActions::class])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
        Route::get('/dashboard/audience', [AdminDashboardController::class, 'audience'])->name('dashboard.audience');
        // « Cinéma d'Ivoire » — films
        Route::get('/cinema', [\App\Http\Controllers\Admin\FilmManagementController::class, 'index'])->name('films.index');
        Route::get('/cinema/creer', [\App\Http\Controllers\Admin\FilmManagementController::class, 'create'])->name('films.create');
        Route::post('/cinema', [\App\Http\Controllers\Admin\FilmManagementController::class, 'store'])->name('films.store');
        Route::get('/cinema/{film:id}/modifier', [\App\Http\Controllers\Admin\FilmManagementController::class, 'edit'])->name('films.edit');
        Route::put('/cinema/{film:id}', [\App\Http\Controllers\Admin\FilmManagementController::class, 'update'])->name('films.update');
        Route::patch('/cinema/{film:id}/statut', [\App\Http\Controllers\Admin\FilmManagementController::class, 'status'])->name('films.status');
        Route::delete('/cinema/{film:id}', [\App\Http\Controllers\Admin\FilmManagementController::class, 'destroy'])->name('films.destroy');
        // « Les Astuces de Mian » — bandes dessinées
        Route::get('/astuces-de-mian',[\App\Http\Controllers\Admin\ComicManagementController::class, 'index'])->name('comics.index');
        Route::get('/astuces-de-mian/creer', [\App\Http\Controllers\Admin\ComicManagementController::class, 'create'])->name('comics.create');
        Route::post('/astuces-de-mian', [\App\Http\Controllers\Admin\ComicManagementController::class, 'store'])->name('comics.store');
        Route::get('/astuces-de-mian/{comic}/modifier', [\App\Http\Controllers\Admin\ComicManagementController::class, 'edit'])->name('comics.edit');
        Route::put('/astuces-de-mian/{comic}', [\App\Http\Controllers\Admin\ComicManagementController::class, 'update'])->name('comics.update');
        Route::delete('/astuces-de-mian/{comic}', [\App\Http\Controllers\Admin\ComicManagementController::class, 'destroy'])->name('comics.destroy');
        // Médiathèque — vidéos
        Route::get('/videos', [\App\Http\Controllers\Admin\VideoManagementController::class, 'index'])->name('videos.index');
        Route::get('/videos/creer', [\App\Http\Controllers\Admin\VideoManagementController::class, 'create'])->name('videos.create');
        Route::post('/videos', [\App\Http\Controllers\Admin\VideoManagementController::class, 'store'])->name('videos.store');
        Route::get('/videos/fiches', [\App\Http\Controllers\Admin\VideoManagementController::class, 'searchListings'])->name('videos.listings');
        Route::get('/videos/{video}/modifier', [\App\Http\Controllers\Admin\VideoManagementController::class, 'edit'])->name('videos.edit');
        Route::put('/videos/{video}', [\App\Http\Controllers\Admin\VideoManagementController::class, 'update'])->name('videos.update');
        Route::patch('/videos/{video}/publier', [\App\Http\Controllers\Admin\VideoManagementController::class, 'approve'])->name('videos.approve');
        Route::patch('/videos/{video}/refuser', [\App\Http\Controllers\Admin\VideoManagementController::class, 'reject'])->name('videos.reject');
        Route::delete('/videos/{video}', [\App\Http\Controllers\Admin\VideoManagementController::class, 'destroy'])->name('videos.destroy');
        Route::get('/analytics', [AdminAnalyticsController::class, 'index'])->name('analytics.index');
        Route::get('/ia', [AdminAiInsightsController::class, 'index'])->name('ai.index');
        Route::get('/ia/prestataires/{provider}', [AdminAiInsightsController::class, 'provider'])->whereNumber('provider')->name('ai.provider');
        Route::get('/moderation-ia', [AdminAiModerationController::class, 'index'])->name('ai-moderation.index');
        Route::post('/moderation-ia/analyser', [AdminAiModerationController::class, 'scan'])->name('ai-moderation.scan');
        Route::patch('/moderation-ia/{report}', [AdminAiModerationController::class, 'decide'])->whereNumber('report')->name('ai-moderation.decide');
        Route::get('/audit', [AdminAuditLogController::class, 'index'])->name('audit.index');
        Route::get('/permissions', [PermissionManagementController::class, 'index'])->name('permissions');
        Route::get('/administration/maintenance', [AdministrationController::class, 'maintenance'])->name('administration.maintenance');
        Route::get('/administration/maintenance/preview', [AdministrationController::class, 'maintenancePreview'])->name('administration.maintenance.preview');
        Route::put('/administration/maintenance', [AdministrationController::class, 'updateMaintenance'])->name('administration.maintenance.update');
        Route::patch('/administration/maintenance/toggle', [AdministrationController::class, 'toggleMaintenance'])->name('administration.maintenance.toggle');
        Route::get('/administration/apparence', [AdministrationController::class, 'appearance'])->name('administration.appearance');
        Route::post('/administration/apparence/slides', [AdministrationController::class, 'storeSlide'])->name('administration.appearance.slides.store');
        Route::patch('/administration/apparence/slides/{slide}', [AdministrationController::class, 'updateSlide'])->name('administration.appearance.slides.update');
        Route::patch('/administration/apparence/slides/{slide}/toggle', [AdministrationController::class, 'toggleSlide'])->name('administration.appearance.slides.toggle');
        Route::delete('/administration/apparence/slides/{slide}', [AdministrationController::class, 'destroySlide'])->name('administration.appearance.slides.destroy');
        Route::get('/administration/flash-info', [AdministrationController::class, 'flashInfo'])->name('administration.flash-info');
        Route::post('/administration/flash-info', [AdministrationController::class, 'storeFlashInfo'])->name('administration.flash-info.store');
        Route::patch('/administration/flash-info/{flashInfo}', [AdministrationController::class, 'updateFlashInfo'])->name('administration.flash-info.update');
        Route::patch('/administration/flash-info/{flashInfo}/toggle', [AdministrationController::class, 'toggleFlashInfo'])->name('administration.flash-info.toggle');
        Route::delete('/administration/flash-info/{flashInfo}', [AdministrationController::class, 'destroyFlashInfo'])->name('administration.flash-info.destroy');
        Route::get('/administration/contacts', [AdministrationController::class, 'contacts'])->name('administration.contacts');
        Route::put('/administration/contacts', [AdministrationController::class, 'updateContactSettings'])->name('administration.contacts.update');
        Route::get('/administration/messages-contact/export', [ContactMessageController::class, 'export'])->name('administration.contact-messages.export');
        Route::get('/administration/messages-contact', [ContactMessageController::class, 'index'])->name('administration.contact-messages.index');
        Route::get('/administration/messages-contact/{contactMessage}', [ContactMessageController::class, 'show'])->name('administration.contact-messages.show');
        Route::patch('/administration/messages-contact/{contactMessage}', [ContactMessageController::class, 'update'])->name('administration.contact-messages.update');
        Route::delete('/administration/messages-contact/{contactMessage}', [ContactMessageController::class, 'destroy'])->name('administration.contact-messages.destroy');
        Route::get('/messagerie', [AdminConversationController::class, 'index'])->name('conversations.index');
        Route::get('/messagerie/poll', [AdminConversationController::class, 'poll'])->name('conversations.poll');
        Route::post('/messagerie/start', [AdminConversationController::class, 'startDirectConversation'])->name('conversations.start');
        Route::post('/messagerie/broadcast', [AdminConversationController::class, 'broadcastToAllProviders'])->name('conversations.broadcast');
        Route::get('/messagerie/{conversation}', [AdminConversationController::class, 'show'])->name('conversations.show');
        Route::post('/messagerie/{conversation}/reply', [AdminConversationController::class, 'reply'])->name('conversations.reply');
        Route::patch('/messagerie/{conversation}/messages/{message}', [AdminConversationController::class, 'updateMessage'])->name('conversations.messages.update');
        Route::delete('/messagerie/{conversation}/messages/{message}', [AdminConversationController::class, 'deleteMessage'])->name('conversations.messages.delete');
        Route::patch('/messagerie/{conversation}/status', [AdminConversationController::class, 'updateStatus'])->name('conversations.status');
        Route::get('/messagerie/{conversation}/attachments/{attachment}/download', [AdminConversationController::class, 'downloadAttachment'])->name('conversations.attachments.download');
        Route::get('/messagerie/{conversation}/attachments/{attachment}/preview', [AdminConversationController::class, 'previewAttachment'])->name('conversations.attachments.preview');
        Route::get('/signalements-messagerie', [AdminConversationReportController::class, 'index'])->name('conversation-reports.index');
        Route::get('/signalements-messagerie/{report}', [AdminConversationReportController::class, 'show'])->name('conversation-reports.show');
        Route::patch('/signalements-messagerie/{report}/statut', [AdminConversationReportController::class, 'updateStatus'])->name('conversation-reports.update-status');
        Route::get('/notifications', [AdminNotificationController::class, 'index'])->name('notifications.index');
        Route::patch('/notifications/read-all', [AdminNotificationController::class, 'markAllRead'])->name('notifications.read-all');
        Route::patch('/notifications/{notification}/read', [AdminNotificationController::class, 'markRead'])->name('notifications.read');
        Route::get('/administration/reseaux-sociaux', [AdministrationController::class, 'social'])->name('administration.social');
        Route::put('/administration/reseaux-sociaux', [AdministrationController::class, 'updateSocialSettings'])->name('administration.social.update');
        Route::get('/administration/medias', [AdministrationController::class, 'media'])->name('administration.media');
        Route::post('/administration/medias', [AdministrationController::class, 'storeSiteMedia'])->name('administration.media.store');
        Route::delete('/administration/medias/{siteMediaItem}', [AdministrationController::class, 'destroySiteMedia'])->name('administration.media.destroy');
        Route::get('/administration/parametres', [AdministrationController::class, 'settings'])->name('administration.settings');
        Route::put('/administration/parametres', [AdministrationController::class, 'updateSiteSettings'])->name('administration.settings.update');
        Route::get('/administration/accueil', [AdministrationController::class, 'homepage'])->name('administration.homepage');
        Route::put('/administration/accueil', [AdministrationController::class, 'updateHomepage'])->name('administration.homepage.update');
        Route::get('/administration/footer', [FooterImageController::class, 'edit'])->name('administration.footer');
        Route::put('/administration/footer', [FooterImageController::class, 'update'])->name('administration.footer.update');
        Route::patch('/administration/footer/toggle', [FooterImageController::class, 'toggle'])->name('administration.footer.toggle');
        Route::delete('/administration/footer', [FooterImageController::class, 'destroy'])->name('administration.footer.destroy');
        Route::get('/administration/regions-image', [RegionsSectionImageController::class, 'edit'])->name('administration.regions-image');
        Route::put('/administration/regions-image', [RegionsSectionImageController::class, 'update'])->name('administration.regions-image.update');
        Route::patch('/administration/regions-image/toggle', [RegionsSectionImageController::class, 'toggle'])->name('administration.regions-image.toggle');
        Route::delete('/administration/regions-image', [RegionsSectionImageController::class, 'destroy'])->name('administration.regions-image.destroy');
        Route::get('/administration/annuaire-image', [AnnuaireSectionImageController::class, 'edit'])->name('administration.annuaire-image');
        Route::put('/administration/annuaire-image', [AnnuaireSectionImageController::class, 'update'])->name('administration.annuaire-image.update');
        Route::patch('/administration/annuaire-image/toggle', [AnnuaireSectionImageController::class, 'toggle'])->name('administration.annuaire-image.toggle');
        Route::delete('/administration/annuaire-image', [AnnuaireSectionImageController::class, 'destroy'])->name('administration.annuaire-image.destroy');
        Route::get('/administration/cultures-image', [CulturesSectionImageController::class, 'edit'])->name('administration.cultures-image');
        Route::put('/administration/cultures-image', [CulturesSectionImageController::class, 'update'])->name('administration.cultures-image.update');
        Route::patch('/administration/cultures-image/toggle', [CulturesSectionImageController::class, 'toggle'])->name('administration.cultures-image.toggle');
        Route::delete('/administration/cultures-image', [CulturesSectionImageController::class, 'destroy'])->name('administration.cultures-image.destroy');
        Route::get('/administration/header-image', [HeaderImageController::class, 'edit'])->name('administration.header-image');
        Route::put('/administration/header-image', [HeaderImageController::class, 'update'])->name('administration.header-image.update');
        Route::patch('/administration/header-image/toggle', [HeaderImageController::class, 'toggle'])->name('administration.header-image.toggle');
        Route::delete('/administration/header-image', [HeaderImageController::class, 'destroy'])->name('administration.header-image.destroy');
        Route::get('/administration/evenements-image', [EvenementsSectionImageController::class, 'edit'])->name('administration.evenements-image');
        Route::put('/administration/evenements-image', [EvenementsSectionImageController::class, 'update'])->name('administration.evenements-image.update');
        Route::patch('/administration/evenements-image/toggle', [EvenementsSectionImageController::class, 'toggle'])->name('administration.evenements-image.toggle');
        Route::delete('/administration/evenements-image', [EvenementsSectionImageController::class, 'destroy'])->name('administration.evenements-image.destroy');
        Route::get('/administration/partenaires-image', [PartenairesSectionImageController::class, 'edit'])->name('administration.partenaires-image');
        Route::put('/administration/partenaires-image', [PartenairesSectionImageController::class, 'update'])->name('administration.partenaires-image.update');
        Route::patch('/administration/partenaires-image/toggle', [PartenairesSectionImageController::class, 'toggle'])->name('administration.partenaires-image.toggle');
        Route::delete('/administration/partenaires-image', [PartenairesSectionImageController::class, 'destroy'])->name('administration.partenaires-image.destroy');
        Route::get('/administration/articles-image', [ArticlesSectionImageController::class, 'edit'])->name('administration.articles-image');
        Route::put('/administration/articles-image', [ArticlesSectionImageController::class, 'update'])->name('administration.articles-image.update');
        Route::patch('/administration/articles-image/toggle', [ArticlesSectionImageController::class, 'toggle'])->name('administration.articles-image.toggle');
        Route::delete('/administration/articles-image', [ArticlesSectionImageController::class, 'destroy'])->name('administration.articles-image.destroy');
        Route::get('/administration/tourist-hero-image', [TouristHeroImageController::class, 'edit'])->name('administration.tourist-hero-image');
        Route::put('/administration/tourist-hero-image', [TouristHeroImageController::class, 'update'])->name('administration.tourist-hero-image.update');
        Route::patch('/administration/tourist-hero-image/toggle', [TouristHeroImageController::class, 'toggle'])->name('administration.tourist-hero-image.toggle');
        Route::delete('/administration/tourist-hero-image', [TouristHeroImageController::class, 'destroy'])->name('administration.tourist-hero-image.destroy');
        Route::get('/administration/cultural-hero-image', [CulturalHeroImageController::class, 'edit'])->name('administration.cultural-hero-image');
        Route::put('/administration/cultural-hero-image', [CulturalHeroImageController::class, 'update'])->name('administration.cultural-hero-image.update');
        Route::patch('/administration/cultural-hero-image/toggle', [CulturalHeroImageController::class, 'toggle'])->name('administration.cultural-hero-image.toggle');
        Route::delete('/administration/cultural-hero-image', [CulturalHeroImageController::class, 'destroy'])->name('administration.cultural-hero-image.destroy');
        Route::get('/administration/articles-hero-image', [ArticlesHeroImageController::class, 'edit'])->name('administration.articles-hero-image');
        Route::put('/administration/articles-hero-image', [ArticlesHeroImageController::class, 'update'])->name('administration.articles-hero-image.update');
        Route::patch('/administration/articles-hero-image/toggle', [ArticlesHeroImageController::class, 'toggle'])->name('administration.articles-hero-image.toggle');
        Route::delete('/administration/articles-hero-image', [ArticlesHeroImageController::class, 'destroy'])->name('administration.articles-hero-image.destroy');
        Route::get('/administration/providers-hero-image', [ProvidersHeroImageController::class, 'edit'])->name('administration.providers-hero-image');
        Route::put('/administration/providers-hero-image', [ProvidersHeroImageController::class, 'update'])->name('administration.providers-hero-image.update');
        Route::patch('/administration/providers-hero-image/toggle', [ProvidersHeroImageController::class, 'toggle'])->name('administration.providers-hero-image.toggle');
        Route::delete('/administration/providers-hero-image', [ProvidersHeroImageController::class, 'destroy'])->name('administration.providers-hero-image.destroy');
        Route::get('/administration/events-hero-image', [EventsHeroImageController::class, 'edit'])->name('administration.events-hero-image');
        Route::put('/administration/events-hero-image', [EventsHeroImageController::class, 'update'])->name('administration.events-hero-image.update');
        Route::patch('/administration/events-hero-image/toggle', [EventsHeroImageController::class, 'toggle'])->name('administration.events-hero-image.toggle');
        Route::delete('/administration/events-hero-image', [EventsHeroImageController::class, 'destroy'])->name('administration.events-hero-image.destroy');
        Route::get('/administration/gallery-hero-image', [GalleryHeroImageController::class, 'edit'])->name('administration.gallery-hero-image');
        Route::put('/administration/gallery-hero-image', [GalleryHeroImageController::class, 'update'])->name('administration.gallery-hero-image.update');
        Route::patch('/administration/gallery-hero-image/toggle', [GalleryHeroImageController::class, 'toggle'])->name('administration.gallery-hero-image.toggle');
        Route::delete('/administration/gallery-hero-image', [GalleryHeroImageController::class, 'destroy'])->name('administration.gallery-hero-image.destroy');
        Route::get('/administration/login-background-image', [LoginBackgroundImageController::class, 'edit'])->name('administration.login-background-image');
        Route::put('/administration/login-background-image', [LoginBackgroundImageController::class, 'update'])->name('administration.login-background-image.update');
        Route::patch('/administration/login-background-image/toggle', [LoginBackgroundImageController::class, 'toggle'])->name('administration.login-background-image.toggle');
        Route::delete('/administration/login-background-image', [LoginBackgroundImageController::class, 'destroy'])->name('administration.login-background-image.destroy');
        Route::get('/administration/plans-image', [PlansSectionImageController::class, 'edit'])->name('administration.plans-image');
        Route::put('/administration/plans-image', [PlansSectionImageController::class, 'update'])->name('administration.plans-image.update');
        Route::patch('/administration/plans-image/toggle', [PlansSectionImageController::class, 'toggle'])->name('administration.plans-image.toggle');
        Route::delete('/administration/plans-image', [PlansSectionImageController::class, 'destroy'])->name('administration.plans-image.destroy');
        Route::get('/administration/reco-image', [RecoSectionImageController::class, 'edit'])->name('administration.reco-image');
        Route::put('/administration/reco-image', [RecoSectionImageController::class, 'update'])->name('administration.reco-image.update');
        Route::patch('/administration/reco-image/toggle', [RecoSectionImageController::class, 'toggle'])->name('administration.reco-image.toggle');
        Route::delete('/administration/reco-image', [RecoSectionImageController::class, 'destroy'])->name('administration.reco-image.destroy');
        Route::get('/administration/art-image', [ArtSectionImageController::class, 'edit'])->name('administration.art-image');
        Route::put('/administration/art-image', [ArtSectionImageController::class, 'update'])->name('administration.art-image.update');
        Route::patch('/administration/art-image/toggle', [ArtSectionImageController::class, 'toggle'])->name('administration.art-image.toggle');
        Route::delete('/administration/art-image', [ArtSectionImageController::class, 'destroy'])->name('administration.art-image.destroy');
        Route::get('/administration/search-image', [SearchPageImageController::class, 'edit'])->name('administration.search-image');
        Route::put('/administration/search-image', [SearchPageImageController::class, 'update'])->name('administration.search-image.update');
        Route::patch('/administration/search-image/toggle', [SearchPageImageController::class, 'toggle'])->name('administration.search-image.toggle');
        Route::delete('/administration/search-image', [SearchPageImageController::class, 'destroy'])->name('administration.search-image.destroy');
        Route::get('/administration/partenaires', [PartnerController::class, 'index'])->name('administration.partners');
        Route::get('/administration/partenaires/creer', [PartnerController::class, 'create'])->name('administration.partners.create');
        Route::post('/administration/partenaires', [PartnerController::class, 'store'])->name('administration.partners.store');
        Route::get('/administration/partenaires/{partner}/modifier', [PartnerController::class, 'edit'])->name('administration.partners.edit');
        Route::put('/administration/partenaires/{partner}', [PartnerController::class, 'update'])->name('administration.partners.update');
        Route::delete('/administration/partenaires/{partner}', [PartnerController::class, 'destroy'])->name('administration.partners.destroy');
        Route::patch('/administration/partenaires/{partner}/vedette', [PartnerController::class, 'toggleFeatured'])->name('administration.partners.toggle-featured');
        Route::patch('/administration/partenaires/{partner}/actif', [PartnerController::class, 'toggleActive'])->name('administration.partners.toggle-active');
        Route::get('/administration/centre-information', [InformationCenterController::class, 'index'])->name('administration.info-center');
        Route::get('/administration/centre-information/{informationPage}/modifier', [InformationCenterController::class, 'edit'])->name('administration.info-center.edit');
        Route::put('/administration/centre-information/{informationPage}', [InformationCenterController::class, 'update'])->name('administration.info-center.update');

        Route::get('/newsletter', [NewsletterManagementController::class, 'index'])->name('newsletter.index');
        Route::get('/newsletter/abonnes/export', [NewsletterManagementController::class, 'exportSubscribers'])
            ->name('newsletter.subscribers.export');
        Route::get('/newsletter/abonnes/{subscriber}/message', [NewsletterManagementController::class, 'individualMessageForm'])
            ->name('newsletter.subscribers.message');
        Route::post('/newsletter/abonnes/{subscriber}/message', [NewsletterManagementController::class, 'sendIndividual'])
            ->name('newsletter.subscribers.message.send')
            ->middleware('throttle:30,60');
        Route::post('/newsletter/envoyer', [NewsletterManagementController::class, 'send'])
            ->name('newsletter.send')
            ->middleware('throttle:6,60');

        // Articles
        Route::get('/articles', [ArticleManagementController::class, 'index'])->name('articles.index');
        Route::put('/articles/{article}', [ArticleManagementController::class, 'update'])->name('articles.update');
        Route::patch('/articles/{article}/publish', [ArticleManagementController::class, 'publish'])->name('articles.publish');
        Route::patch('/articles/{article}/reject', [ArticleManagementController::class, 'reject'])->name('articles.reject');
        Route::patch('/articles/{article}/archive', [ArticleManagementController::class, 'archive'])->name('articles.archive');
        Route::delete('/articles/{article}', [ArticleManagementController::class, 'destroy'])->name('articles.destroy');
        Route::get('/categories/articles', [ArticleManagementController::class, 'categories'])->name('categories.articles');
        Route::post('/categories/articles', [ArticleManagementController::class, 'storeCategory'])->name('categories.articles.store');
        Route::patch('/categories/articles/{category}', [ArticleManagementController::class, 'updateCategory'])->name('categories.articles.update');

        // Événements
        Route::get('/evenements', [EventManagementController::class, 'index'])->name('events.index');
        Route::patch('/evenements/{event}/publish', [EventManagementController::class, 'publish'])->name('events.publish');
        Route::patch('/evenements/{event}/cancel', [EventManagementController::class, 'cancel'])->name('events.cancel');
        Route::delete('/evenements/{event}', [EventManagementController::class, 'destroy'])->name('events.destroy');

        // Catégories d'événements
        Route::get('/evenements/categories', [EventManagementController::class, 'categories'])->name('events.categories.index');
        Route::post('/evenements/categories', [EventManagementController::class, 'storeCategory'])->name('events.categories.store');
        Route::put('/evenements/categories/{category}', [EventManagementController::class, 'updateCategory'])->name('events.categories.update');
        Route::delete('/evenements/categories/{category}', [EventManagementController::class, 'destroyCategory'])->name('events.categories.destroy');

        // Prestataires
        Route::get('/prestataires/categories', [ProviderManagementController::class, 'categories'])->name('providers.categories.index');
        Route::post('/prestataires/categories', [ProviderManagementController::class, 'storeCategory'])->name('providers.categories.store');
        Route::put('/prestataires/categories/{providerCategory}', [ProviderManagementController::class, 'updateCategory'])->name('providers.categories.update');
        Route::delete('/prestataires/categories/{providerCategory}', [ProviderManagementController::class, 'destroyCategory'])->name('providers.categories.destroy');
        Route::get('/prestataires/verifications', [AdminProviderVerificationController::class, 'index'])->name('provider-verifications.index');
        Route::get('/prestataires/verifications/{document}/fichier', [AdminProviderVerificationController::class, 'download'])->name('provider-verifications.download');
        Route::post('/prestataires/verifications/{document}/valider', [AdminProviderVerificationController::class, 'approve'])->name('provider-verifications.approve');
        Route::post('/prestataires/verifications/{document}/refuser', [AdminProviderVerificationController::class, 'reject'])->name('provider-verifications.reject');
        Route::get('/prestataires', [ProviderManagementController::class, 'index'])->name('providers.index');
        Route::post('/prestataires', [ProviderManagementController::class, 'store'])->name('providers.store');
        Route::patch('/prestataires/{provider}', [ProviderManagementController::class, 'update'])->name('providers.update');
        Route::delete('/prestataires/{provider}', [ProviderManagementController::class, 'destroy'])->name('providers.destroy');
        Route::patch('/prestataires/{provider}/validate', [ProviderManagementController::class, 'validateProvider'])->name('providers.validate');
        Route::patch('/prestataires/{provider}/suspend', [ProviderManagementController::class, 'suspend'])->name('providers.suspend');
        Route::get('/prestataires/{provider}/contenus', [ProviderManagementController::class, 'content'])->name('providers.content');
        Route::patch('/prestataires/{provider}/contenus/articles/reassign-bulk', [ProviderManagementController::class, 'reassignArticlesBulk'])->name('providers.content.articles.reassign-bulk');
        Route::patch('/prestataires/{provider}/contenus/articles/{article}', [ProviderManagementController::class, 'reassignArticle'])->name('providers.content.articles.reassign');
        Route::patch('/prestataires/{provider}/contenus/evenements/reassign-bulk', [ProviderManagementController::class, 'reassignEventsBulk'])->name('providers.content.events.reassign-bulk');
        Route::patch('/prestataires/{provider}/contenus/evenements/{event}', [ProviderManagementController::class, 'reassignEvent'])->name('providers.content.events.reassign');
        Route::patch('/prestataires/{provider}/contenus/medias/reassign-bulk', [ProviderManagementController::class, 'reassignMediaBulk'])->name('providers.content.media.reassign-bulk');
        Route::patch('/prestataires/{provider}/contenus/medias/{media}', [ProviderManagementController::class, 'reassignMedia'])->name('providers.content.media.reassign');
        Route::post('/prestataires/{provider}/contenus/medias', [ProviderManagementController::class, 'storeMedia'])->name('providers.content.media.store');
        Route::delete('/prestataires/{provider}/contenus/medias/{media}', [ProviderManagementController::class, 'destroyMedia'])->name('providers.content.media.destroy');

        // Art & Créations — modération des œuvres
        Route::get('/oeuvres', [ArtworkManagementController::class, 'index'])->name('artworks.index');
        Route::get('/oeuvres/creer', [ArtworkManagementController::class, 'create'])->name('artworks.create');
        Route::post('/oeuvres', [ArtworkManagementController::class, 'store'])->name('artworks.store');
        Route::get('/oeuvres/categories', [ArtworkManagementController::class, 'categories'])->name('artworks.categories.index');
        Route::post('/oeuvres/categories', [ArtworkManagementController::class, 'storeCategory'])->name('artworks.categories.store');
        Route::put('/oeuvres/categories/{artworkCategory}', [ArtworkManagementController::class, 'updateCategory'])->name('artworks.categories.update');
        Route::delete('/oeuvres/categories/{artworkCategory}', [ArtworkManagementController::class, 'destroyCategory'])->name('artworks.categories.destroy');
        Route::get('/oeuvres/{artwork}/modifier', [ArtworkManagementController::class, 'edit'])->name('artworks.edit');
        Route::put('/oeuvres/{artwork}', [ArtworkManagementController::class, 'update'])->name('artworks.update');
        Route::patch('/oeuvres/{artwork}/approuver', [ArtworkManagementController::class, 'approve'])->name('artworks.approve');
        Route::patch('/oeuvres/{artwork}/rejeter', [ArtworkManagementController::class, 'reject'])->name('artworks.reject');
        Route::patch('/oeuvres/{artwork}/suspendre', [ArtworkManagementController::class, 'suspend'])->name('artworks.suspend');
        Route::delete('/oeuvres/{artwork}', [ArtworkManagementController::class, 'destroy'])->name('artworks.destroy');

        // Restaurants & Gastronomie — supervision de la carte
        Route::get('/carte', [MenuManagementController::class, 'index'])->name('menu.index');
        Route::get('/carte/creer', [MenuManagementController::class, 'create'])->name('menu.create');
        Route::post('/carte', [MenuManagementController::class, 'store'])->name('menu.store');
        Route::get('/carte/categories', [MenuManagementController::class, 'categories'])->name('menu.categories.index');
        Route::post('/carte/categories', [MenuManagementController::class, 'storeCategory'])->name('menu.categories.store');
        Route::put('/carte/categories/{menuCategory}', [MenuManagementController::class, 'updateCategory'])->name('menu.categories.update');
        Route::delete('/carte/categories/{menuCategory}', [MenuManagementController::class, 'destroyCategory'])->name('menu.categories.destroy');
        Route::get('/carte/{menuItem}/modifier', [MenuManagementController::class, 'edit'])->name('menu.edit');
        Route::put('/carte/{menuItem}', [MenuManagementController::class, 'update'])->name('menu.update');
        Route::patch('/carte/{menuItem}/toggle', [MenuManagementController::class, 'toggle'])->name('menu.toggle');
        Route::delete('/carte/{menuItem}', [MenuManagementController::class, 'destroy'])->name('menu.destroy');

        // Agences de Voyages & Tours — supervision des circuits
        Route::get('/circuits', [TourManagementController::class, 'index'])->name('tours.index');
        Route::get('/circuits/creer', [TourManagementController::class, 'create'])->name('tours.create');
        Route::post('/circuits', [TourManagementController::class, 'store'])->name('tours.store');
        Route::get('/circuits/categories', [TourManagementController::class, 'categories'])->name('tours.categories.index');
        Route::post('/circuits/categories', [TourManagementController::class, 'storeCategory'])->name('tours.categories.store');
        Route::put('/circuits/categories/{tourCategory}', [TourManagementController::class, 'updateCategory'])->name('tours.categories.update');
        Route::delete('/circuits/categories/{tourCategory}', [TourManagementController::class, 'destroyCategory'])->name('tours.categories.destroy');
        Route::get('/circuits/{tour}/modifier', [TourManagementController::class, 'edit'])->name('tours.edit');
        Route::put('/circuits/{tour}', [TourManagementController::class, 'update'])->name('tours.update');
        Route::patch('/circuits/{tour}/toggle', [TourManagementController::class, 'toggle'])->name('tours.toggle');
        Route::delete('/circuits/{tour}', [TourManagementController::class, 'destroy'])->name('tours.destroy');

        // Loisirs & Culture — supervision des activités
        Route::get('/activites', [ActivityManagementController::class, 'index'])->name('activities.index');
        Route::get('/activites/creer', [ActivityManagementController::class, 'create'])->name('activities.create');
        Route::post('/activites', [ActivityManagementController::class, 'store'])->name('activities.store');
        Route::get('/activites/categories', [ActivityManagementController::class, 'categories'])->name('activities.categories.index');
        Route::post('/activites/categories', [ActivityManagementController::class, 'storeCategory'])->name('activities.categories.store');
        Route::put('/activites/categories/{activityCategory}', [ActivityManagementController::class, 'updateCategory'])->name('activities.categories.update');
        Route::delete('/activites/categories/{activityCategory}', [ActivityManagementController::class, 'destroyCategory'])->name('activities.categories.destroy');
        Route::get('/activites/{activity}/modifier', [ActivityManagementController::class, 'edit'])->name('activities.edit');
        Route::put('/activites/{activity}', [ActivityManagementController::class, 'update'])->name('activities.update');
        Route::patch('/activites/{activity}/toggle', [ActivityManagementController::class, 'toggle'])->name('activities.toggle');
        Route::delete('/activites/{activity}', [ActivityManagementController::class, 'destroy'])->name('activities.destroy');

        // Transports & Mobilité — supervision des offres
        Route::get('/transport', [TransportManagementController::class, 'index'])->name('transport.index');
        Route::get('/transport/creer', [TransportManagementController::class, 'create'])->name('transport.create');
        Route::post('/transport', [TransportManagementController::class, 'store'])->name('transport.store');
        Route::get('/transport/categories', [TransportManagementController::class, 'categories'])->name('transport.categories.index');
        Route::post('/transport/categories', [TransportManagementController::class, 'storeCategory'])->name('transport.categories.store');
        Route::put('/transport/categories/{transportCategory}', [TransportManagementController::class, 'updateCategory'])->name('transport.categories.update');
        Route::delete('/transport/categories/{transportCategory}', [TransportManagementController::class, 'destroyCategory'])->name('transport.categories.destroy');
        Route::get('/transport/{offer}/modifier', [TransportManagementController::class, 'edit'])->name('transport.edit');
        Route::put('/transport/{offer}', [TransportManagementController::class, 'update'])->name('transport.update');
        Route::patch('/transport/{offer}/toggle', [TransportManagementController::class, 'toggle'])->name('transport.toggle');
        Route::delete('/transport/{offer}', [TransportManagementController::class, 'destroy'])->name('transport.destroy');

        // Art & Créations — commandes
        Route::get('/commandes-art', [ArtOrderManagementController::class, 'index'])->name('art-orders.index');
        Route::get('/commandes-art/{artworkOrder}', [ArtOrderManagementController::class, 'show'])->name('art-orders.show');
        Route::post('/commandes-art/{artworkOrder}/remboursement', [ArtOrderManagementController::class, 'refund'])->name('art-orders.refund');

        // Avis
        Route::get('/avis', [ReviewManagementController::class, 'index'])->name('reviews.index');
        Route::patch('/avis/{review}/approve', [ReviewManagementController::class, 'approve'])->name('reviews.approve');
        Route::patch('/avis/{review}/reject', [ReviewManagementController::class, 'reject'])->name('reviews.reject');
        Route::patch('/avis/{review}/flag', [ReviewManagementController::class, 'flag'])->name('reviews.flag');
        Route::delete('/avis/{review}', [ReviewManagementController::class, 'destroy'])->name('reviews.destroy');

        // Finance
        Route::get('/plans', [PlanManagementController::class, 'index'])->name('plans.index');
        Route::post('/plans', [PlanManagementController::class, 'store'])->name('plans.store');
        Route::patch('/plans/{plan}', [PlanManagementController::class, 'update'])->name('plans.update');
        Route::patch('/plans/{plan}/toggle', [PlanManagementController::class, 'toggle'])->name('plans.toggle');
        Route::post('/promo-codes', [PlanManagementController::class, 'storePromo'])->name('promo-codes.store');
        Route::patch('/promo-codes/{promo}/toggle', [PlanManagementController::class, 'togglePromo'])->name('promo-codes.toggle');
        Route::get('/payments', [FinanceManagementController::class, 'payments'])->name('payments.index');
        Route::get('/payments/{payment}', [FinanceManagementController::class, 'paymentShow'])->name('payments.show');
        Route::get('/payment-settings', [FinanceManagementController::class, 'settings'])->name('payments.settings');
        Route::post('/payment-settings', [FinanceManagementController::class, 'saveSettings'])->name('payments.settings.save');
        Route::get('/subscriptions', [FinanceManagementController::class, 'subscriptions'])->name('subscriptions.index');
        Route::post('/subscriptions', [FinanceManagementController::class, 'storeSubscription'])->name('subscriptions.store');
        Route::patch('/subscriptions/{subscription}', [FinanceManagementController::class, 'updateSubscription'])->name('subscriptions.update');
        Route::post('/subscriptions/{subscription}/extend', [FinanceManagementController::class, 'extendSubscription'])->name('subscriptions.extend');

        Route::get('/wallet', [AdminWalletController::class, 'index'])->name('wallet.index');
        Route::get('/wallet/export', [AdminWalletController::class, 'export'])->name('wallet.export');
        Route::get('/wallet/prestataires/{provider}', [AdminWalletController::class, 'providerShow'])->name('wallet.provider-show');
        Route::get('/wallet/clients/{user}', [AdminWalletController::class, 'userShow'])->name('wallet.user-show');
        Route::get('/wallet/retraits', [AdminWalletController::class, 'payouts'])->name('wallet.payouts');
        Route::post('/wallet/retraits/{payoutRequest}/approuver', [AdminWalletController::class, 'payoutApprove'])->name('wallet.payouts.approve');
        Route::post('/wallet/retraits/{payoutRequest}/refuser', [AdminWalletController::class, 'payoutReject'])->name('wallet.payouts.reject');
        Route::post('/wallet/retraits/{payoutRequest}/payer', [AdminWalletController::class, 'payoutMarkPaid'])->name('wallet.payouts.mark-paid');
        Route::post('/reservations/{reservation}/remboursement', [AdminWalletController::class, 'refund'])->name('reservations.refund');

        // Utilisateurs
        Route::get('/users', [UserRoleManagementController::class, 'index'])->name('users.index');
        Route::post('/users', [UserRoleManagementController::class, 'store'])->name('users.store');
        Route::patch('/users/{user}/role', [UserRoleManagementController::class, 'update'])->name('users.role.update');
        Route::patch('/users/{user}/permissions', [UserRoleManagementController::class, 'updatePermissions'])->name('users.permissions.update');
        Route::delete('/users/{user}', [UserRoleManagementController::class, 'destroy'])->name('users.destroy');

        // Tourisme — Villes
        Route::get('/tourisme/villes', [TouristManagementController::class, 'cities'])->name('tourist.cities.index');
        Route::post('/tourisme/villes', [TouristManagementController::class, 'storeCity'])->name('tourist.cities.store');
        Route::put('/tourisme/villes/{city}', [TouristManagementController::class, 'updateCity'])->name('tourist.cities.update');
        Route::delete('/tourisme/villes/{city}', [TouristManagementController::class, 'destroyCity'])->name('tourist.cities.destroy');
        Route::patch('/tourisme/villes/{city}/toggle', [TouristManagementController::class, 'toggleCityActive'])->name('tourist.cities.toggle');
        Route::patch('/tourisme/villes/{city}/vedette', [TouristManagementController::class, 'toggleCityFeatured'])->name('tourist.cities.featured');

        // Tourisme — Catégories
        Route::get('/tourisme/categories', [TouristManagementController::class, 'categories'])->name('tourist.categories.index');
        Route::post('/tourisme/categories', [TouristManagementController::class, 'storeCategory'])->name('tourist.categories.store');
        Route::put('/tourisme/categories/{category}', [TouristManagementController::class, 'updateCategory'])->name('tourist.categories.update');
        Route::delete('/tourisme/categories/{category}', [TouristManagementController::class, 'destroyCategory'])->name('tourist.categories.destroy');

        // Tourisme — Sites
        Route::get('/tourisme/sites', [TouristManagementController::class, 'sites'])->name('tourist.sites.index');
        Route::get('/tourisme/sites/creer', [TouristManagementController::class, 'createSite'])->name('tourist.sites.create');
        Route::post('/tourisme/sites', [TouristManagementController::class, 'storeSite'])->name('tourist.sites.store');
        Route::get('/tourisme/sites/{site}/modifier', [TouristManagementController::class, 'editSite'])->name('tourist.sites.edit');
        Route::put('/tourisme/sites/{site}', [TouristManagementController::class, 'updateSite'])->name('tourist.sites.update');
        Route::delete('/tourisme/sites/{site}', [TouristManagementController::class, 'destroySite'])->name('tourist.sites.destroy');
        Route::patch('/tourisme/sites/{site}/toggle', [TouristManagementController::class, 'toggleSiteActive'])->name('tourist.sites.toggle');
        Route::patch('/tourisme/sites/{site}/vedette', [TouristManagementController::class, 'toggleSiteFeatured'])->name('tourist.sites.featured');

        // Tourisme — Médias
        Route::post('/tourisme/sites/{site}/medias', [TouristManagementController::class, 'storeMedia'])->name('tourist.media.store');
        Route::delete('/tourisme/medias/{media}', [TouristManagementController::class, 'destroyMedia'])->name('tourist.media.destroy');

        // Cultures — Peuples
        Route::get('/cultures/peuples', [CulturalManagementController::class, 'peoples'])->name('cultural.peoples.index');
        Route::post('/cultures/peuples', [CulturalManagementController::class, 'storePeople'])->name('cultural.peoples.store');
        Route::put('/cultures/peuples/{people}', [CulturalManagementController::class, 'updatePeople'])->name('cultural.peoples.update');
        Route::delete('/cultures/peuples/{people}', [CulturalManagementController::class, 'destroyPeople'])->name('cultural.peoples.destroy');
        Route::patch('/cultures/peuples/{people}/toggle', [CulturalManagementController::class, 'togglePeopleActive'])->name('cultural.peoples.toggle');
        Route::patch('/cultures/peuples/{people}/vedette', [CulturalManagementController::class, 'togglePeopleFeatured'])->name('cultural.peoples.featured');

        // Cultures — Domaines
        Route::get('/cultures/domaines', [CulturalManagementController::class, 'domains'])->name('cultural.domains.index');
        Route::post('/cultures/domaines', [CulturalManagementController::class, 'storeDomain'])->name('cultural.domains.store');
        Route::put('/cultures/domaines/{domain}', [CulturalManagementController::class, 'updateDomain'])->name('cultural.domains.update');
        Route::delete('/cultures/domaines/{domain}', [CulturalManagementController::class, 'destroyDomain'])->name('cultural.domains.destroy');

        // Cultures — Éléments
        Route::get('/cultures/elements', [CulturalManagementController::class, 'elements'])->name('cultural.elements.index');
        Route::get('/cultures/elements/creer', [CulturalManagementController::class, 'createElement'])->name('cultural.elements.create');
        Route::post('/cultures/elements', [CulturalManagementController::class, 'storeElement'])->name('cultural.elements.store');
        Route::get('/cultures/elements/{element}/modifier', [CulturalManagementController::class, 'editElement'])->name('cultural.elements.edit');
        Route::put('/cultures/elements/{element}', [CulturalManagementController::class, 'updateElement'])->name('cultural.elements.update');
        Route::delete('/cultures/elements/{element}', [CulturalManagementController::class, 'destroyElement'])->name('cultural.elements.destroy');
        Route::patch('/cultures/elements/{element}/toggle', [CulturalManagementController::class, 'toggleElementActive'])->name('cultural.elements.toggle');
        Route::patch('/cultures/elements/{element}/vedette', [CulturalManagementController::class, 'toggleElementFeatured'])->name('cultural.elements.featured');

        // Cultures — Médias
        Route::delete('/cultures/medias/{media}', [CulturalManagementController::class, 'destroyMedia'])->name('cultural.media.destroy');

        // Hébergements
        Route::get('/hebergements', [AccommodationManagementController::class, 'index'])->name('accommodations.index');
        Route::get('/hebergements/creer', [AccommodationManagementController::class, 'create'])->name('accommodations.create');
        Route::post('/hebergements', [AccommodationManagementController::class, 'store'])->name('accommodations.store');
        Route::get('/hebergements/{accommodation}/modifier', [AccommodationManagementController::class, 'edit'])->name('accommodations.edit');
        Route::put('/hebergements/{accommodation}', [AccommodationManagementController::class, 'update'])->name('accommodations.update');
        Route::delete('/hebergements/{accommodation}', [AccommodationManagementController::class, 'destroy'])->name('accommodations.destroy');
        Route::patch('/hebergements/{accommodation}/toggle-actif', [AccommodationManagementController::class, 'toggleActive'])->name('accommodations.toggle-active');
        Route::patch('/hebergements/{accommodation}/toggle-vedette', [AccommodationManagementController::class, 'toggleFeatured'])->name('accommodations.toggle-featured');
        Route::delete('/hebergements/medias/{media}', [AccommodationManagementController::class, 'destroyMedia'])->name('accommodations.media.destroy');

        // Loisirs & Culture — établissements (pilote du système riche par secteur)
        Route::get('/loisirs-culture', [LeisureVenueManagementController::class, 'index'])->name('leisure-venues.index');
        Route::get('/loisirs-culture/creer', [LeisureVenueManagementController::class, 'create'])->name('leisure-venues.create');
        Route::post('/loisirs-culture', [LeisureVenueManagementController::class, 'store'])->name('leisure-venues.store');
        Route::get('/loisirs-culture/{leisureVenue}/modifier', [LeisureVenueManagementController::class, 'edit'])->name('leisure-venues.edit');
        Route::put('/loisirs-culture/{leisureVenue}', [LeisureVenueManagementController::class, 'update'])->name('leisure-venues.update');
        Route::delete('/loisirs-culture/{leisureVenue}', [LeisureVenueManagementController::class, 'destroy'])->name('leisure-venues.destroy');
        Route::patch('/loisirs-culture/{leisureVenue}/toggle-actif', [LeisureVenueManagementController::class, 'toggleActive'])->name('leisure-venues.toggle-active');
        Route::patch('/loisirs-culture/{leisureVenue}/toggle-vedette', [LeisureVenueManagementController::class, 'toggleFeatured'])->name('leisure-venues.toggle-featured');
        Route::delete('/loisirs-culture/medias/{media}', [LeisureVenueManagementController::class, 'destroyMedia'])->name('leisure-venues.media.destroy');

        // Restaurants & Gastronomie — établissements
        Route::get('/restaurants', [RestaurantManagementController::class, 'index'])->name('restaurants.index');
        Route::get('/restaurants/creer', [RestaurantManagementController::class, 'create'])->name('restaurants.create');
        Route::post('/restaurants', [RestaurantManagementController::class, 'store'])->name('restaurants.store');
        Route::get('/restaurants/{restaurant}/modifier', [RestaurantManagementController::class, 'edit'])->name('restaurants.edit');
        Route::put('/restaurants/{restaurant}', [RestaurantManagementController::class, 'update'])->name('restaurants.update');
        Route::delete('/restaurants/{restaurant}', [RestaurantManagementController::class, 'destroy'])->name('restaurants.destroy');
        Route::patch('/restaurants/{restaurant}/toggle-actif', [RestaurantManagementController::class, 'toggleActive'])->name('restaurants.toggle-active');
        Route::patch('/restaurants/{restaurant}/toggle-vedette', [RestaurantManagementController::class, 'toggleFeatured'])->name('restaurants.toggle-featured');
        Route::delete('/restaurants/medias/{media}', [RestaurantManagementController::class, 'destroyMedia'])->name('restaurants.media.destroy');

        // Sites Touristiques (prestataires) — établissements
        Route::get('/experiences-touristiques', [TouristExperienceManagementController::class, 'index'])->name('tourist-experiences.index');
        Route::get('/experiences-touristiques/creer', [TouristExperienceManagementController::class, 'create'])->name('tourist-experiences.create');
        Route::post('/experiences-touristiques', [TouristExperienceManagementController::class, 'store'])->name('tourist-experiences.store');
        Route::get('/experiences-touristiques/{touristExperience}/modifier', [TouristExperienceManagementController::class, 'edit'])->name('tourist-experiences.edit');
        Route::put('/experiences-touristiques/{touristExperience}', [TouristExperienceManagementController::class, 'update'])->name('tourist-experiences.update');
        Route::delete('/experiences-touristiques/{touristExperience}', [TouristExperienceManagementController::class, 'destroy'])->name('tourist-experiences.destroy');
        Route::patch('/experiences-touristiques/{touristExperience}/toggle-actif', [TouristExperienceManagementController::class, 'toggleActive'])->name('tourist-experiences.toggle-active');
        Route::patch('/experiences-touristiques/{touristExperience}/toggle-vedette', [TouristExperienceManagementController::class, 'toggleFeatured'])->name('tourist-experiences.toggle-featured');
        Route::delete('/experiences-touristiques/medias/{media}', [TouristExperienceManagementController::class, 'destroyMedia'])->name('tourist-experiences.media.destroy');
        // Types de visite d'un site
        Route::get('/experiences-touristiques/{touristExperience}/types-de-visite', [AdminTouristVisitTypeController::class, 'index'])->name('tourist-experiences.visit-types.index');
        Route::get('/experiences-touristiques/{touristExperience}/types-de-visite/creer', [AdminTouristVisitTypeController::class, 'create'])->name('tourist-experiences.visit-types.create');
        Route::post('/experiences-touristiques/{touristExperience}/types-de-visite', [AdminTouristVisitTypeController::class, 'store'])->name('tourist-experiences.visit-types.store');
        Route::get('/experiences-touristiques/{touristExperience}/types-de-visite/{visitType}/modifier', [AdminTouristVisitTypeController::class, 'edit'])->name('tourist-experiences.visit-types.edit');
        Route::put('/experiences-touristiques/{touristExperience}/types-de-visite/{visitType}', [AdminTouristVisitTypeController::class, 'update'])->name('tourist-experiences.visit-types.update');
        Route::patch('/experiences-touristiques/{touristExperience}/types-de-visite/{visitType}/basculer', [AdminTouristVisitTypeController::class, 'toggle'])->name('tourist-experiences.visit-types.toggle');
        Route::delete('/experiences-touristiques/{touristExperience}/types-de-visite/{visitType}', [AdminTouristVisitTypeController::class, 'destroy'])->name('tourist-experiences.visit-types.destroy');

        // Agences de Voyages & Tours — établissements
        Route::get('/agences-voyages', [TravelAgencyManagementController::class, 'index'])->name('travel-agencies.index');
        Route::get('/agences-voyages/creer', [TravelAgencyManagementController::class, 'create'])->name('travel-agencies.create');
        Route::post('/agences-voyages', [TravelAgencyManagementController::class, 'store'])->name('travel-agencies.store');
        Route::get('/agences-voyages/{travelAgency}/modifier', [TravelAgencyManagementController::class, 'edit'])->name('travel-agencies.edit');
        Route::put('/agences-voyages/{travelAgency}', [TravelAgencyManagementController::class, 'update'])->name('travel-agencies.update');
        Route::delete('/agences-voyages/{travelAgency}', [TravelAgencyManagementController::class, 'destroy'])->name('travel-agencies.destroy');
        Route::patch('/agences-voyages/{travelAgency}/toggle-actif', [TravelAgencyManagementController::class, 'toggleActive'])->name('travel-agencies.toggle-active');
        Route::patch('/agences-voyages/{travelAgency}/toggle-vedette', [TravelAgencyManagementController::class, 'toggleFeatured'])->name('travel-agencies.toggle-featured');
        Route::delete('/agences-voyages/medias/{media}', [TravelAgencyManagementController::class, 'destroyMedia'])->name('travel-agencies.media.destroy');

        // Transports & Mobilité — entreprises
        Route::get('/transports', [TransportCompanyManagementController::class, 'index'])->name('transport-companies.index');
        Route::get('/transports/creer', [TransportCompanyManagementController::class, 'create'])->name('transport-companies.create');
        Route::post('/transports', [TransportCompanyManagementController::class, 'store'])->name('transport-companies.store');
        Route::get('/transports/{transportCompany}/modifier', [TransportCompanyManagementController::class, 'edit'])->name('transport-companies.edit');
        Route::put('/transports/{transportCompany}', [TransportCompanyManagementController::class, 'update'])->name('transport-companies.update');
        Route::delete('/transports/{transportCompany}', [TransportCompanyManagementController::class, 'destroy'])->name('transport-companies.destroy');
        Route::patch('/transports/{transportCompany}/toggle-actif', [TransportCompanyManagementController::class, 'toggleActive'])->name('transport-companies.toggle-active');
        Route::patch('/transports/{transportCompany}/toggle-vedette', [TransportCompanyManagementController::class, 'toggleFeatured'])->name('transport-companies.toggle-featured');
        Route::delete('/transports/medias/{media}', [TransportCompanyManagementController::class, 'destroyMedia'])->name('transport-companies.media.destroy');

        // ── Nos Prestations ──────────────────────────────────────────────
        Route::get('/prestations', [PrestationManagementController::class, 'index'])->name('prestations.index');
        Route::post('/prestations/banners', [PrestationManagementController::class, 'storeBanner'])->name('prestations.banners.store');
        Route::patch('/prestations/banners/{banner}', [PrestationManagementController::class, 'updateBanner'])->name('prestations.banners.update');
        Route::patch('/prestations/banners/{banner}/toggle', [PrestationManagementController::class, 'toggleBanner'])->name('prestations.banners.toggle');
        Route::delete('/prestations/banners/{banner}', [PrestationManagementController::class, 'destroyBanner'])->name('prestations.banners.destroy');
        Route::put('/prestations/parametres', [PrestationManagementController::class, 'updateSettings'])->name('prestations.settings.update');
        Route::post('/prestations/items', [PrestationManagementController::class, 'storeItem'])->name('prestations.items.store');
        Route::patch('/prestations/items/{item}', [PrestationManagementController::class, 'updateItem'])->name('prestations.items.update');
        Route::patch('/prestations/items/{item}/toggle', [PrestationManagementController::class, 'toggleItem'])->name('prestations.items.toggle');
        Route::delete('/prestations/items/{item}', [PrestationManagementController::class, 'destroyItem'])->name('prestations.items.destroy');

        // ── Bannières publicitaires (page d'accueil) ───────────────────────
        Route::get('/bannieres-accueil', [\App\Http\Controllers\Admin\HomePromoBannerController::class, 'index'])->name('home-banners.index');
        Route::get('/bannieres-accueil/creer', [\App\Http\Controllers\Admin\HomePromoBannerController::class, 'create'])->name('home-banners.create');
        Route::post('/bannieres-accueil', [\App\Http\Controllers\Admin\HomePromoBannerController::class, 'store'])->name('home-banners.store');
        Route::get('/bannieres-accueil/{banner}/modifier', [\App\Http\Controllers\Admin\HomePromoBannerController::class, 'edit'])->name('home-banners.edit');
        Route::put('/bannieres-accueil/{banner}', [\App\Http\Controllers\Admin\HomePromoBannerController::class, 'update'])->name('home-banners.update');
        Route::patch('/bannieres-accueil/{banner}/toggle', [\App\Http\Controllers\Admin\HomePromoBannerController::class, 'toggle'])->name('home-banners.toggle');
        Route::delete('/bannieres-accueil/{banner}', [\App\Http\Controllers\Admin\HomePromoBannerController::class, 'destroy'])->name('home-banners.destroy');

        // ── Bulles interactives (page d'accueil) ───────────────────────────
        Route::get('/bulles-accueil',[HomepageBubbleManagementController::class, 'index'])->name('homepage-bubbles.index');
        Route::post('/bulles-accueil', [HomepageBubbleManagementController::class, 'store'])->name('homepage-bubbles.store');
        Route::put('/bulles-accueil/{bubble}', [HomepageBubbleManagementController::class, 'update'])->name('homepage-bubbles.update');
        Route::patch('/bulles-accueil/{bubble}/toggle', [HomepageBubbleManagementController::class, 'toggle'])->name('homepage-bubbles.toggle');
        Route::delete('/bulles-accueil/{bubble}', [HomepageBubbleManagementController::class, 'destroy'])->name('homepage-bubbles.destroy');
        Route::post('/bulles-accueil/{bubble}/images', [HomepageBubbleManagementController::class, 'storeImage'])->name('homepage-bubbles.images.store');
        Route::delete('/bulles-accueil/images/{image}', [HomepageBubbleManagementController::class, 'destroyImage'])->name('homepage-bubbles.images.destroy');

        // Réservations
        Route::get('/reservations/export', [AdminReservationController::class, 'export'])->name('reservations.export');
        Route::get('/demandes-reservation', [AdminBookingRequestController::class, 'index'])->name('booking-requests.index');
        Route::get('/offres-groupees', [\App\Http\Controllers\Admin\BundleManagementController::class, 'index'])->name('bundles.index');
        Route::get('/offres-groupees/creer', [\App\Http\Controllers\Admin\BundleManagementController::class, 'create'])->name('bundles.create');
        Route::post('/offres-groupees', [\App\Http\Controllers\Admin\BundleManagementController::class, 'store'])->name('bundles.store');
        Route::get('/offres-groupees/{bundle}/modifier', [\App\Http\Controllers\Admin\BundleManagementController::class, 'edit'])->name('bundles.edit');
        Route::put('/offres-groupees/{bundle}', [\App\Http\Controllers\Admin\BundleManagementController::class, 'update'])->name('bundles.update');
        Route::patch('/offres-groupees/{bundle}/visibilite', [\App\Http\Controllers\Admin\BundleManagementController::class, 'toggle'])->name('bundles.toggle');
        Route::delete('/offres-groupees/{bundle}', [\App\Http\Controllers\Admin\BundleManagementController::class, 'destroy'])->name('bundles.destroy');
        Route::post('/demandes-reservation/{bookingRequest}/rembourser', [\App\Http\Controllers\BookingPaymentController::class, 'adminRefund'])->name('booking-requests.refund');
        Route::get('/litiges', [\App\Http\Controllers\DisputeController::class, 'adminIndex'])->name('disputes.index');
        Route::post('/litiges/{dispute}/decision', [\App\Http\Controllers\DisputeController::class, 'adminResolve'])->name('disputes.resolve');
        Route::get('/reservations', [AdminReservationController::class, 'index'])->name('reservations.index');
        Route::get('/reservations/{reservation}', [AdminReservationController::class, 'show'])->name('reservations.show');
        Route::patch('/reservations/{reservation}', [AdminReservationController::class, 'update'])->name('reservations.update');
        Route::delete('/reservations/{reservation}', [AdminReservationController::class, 'destroy'])->name('reservations.destroy');

        // Visites de sites touristiques
        Route::get('/visites/export', [TouristVisitManagementController::class, 'export'])->name('tourist-visits.export');
        Route::get('/visites', [TouristVisitManagementController::class, 'index'])->name('tourist-visits.index');
        Route::get('/visites/{touristVisit}', [TouristVisitManagementController::class, 'show'])->name('tourist-visits.show');
        Route::patch('/visites/{touristVisit}', [TouristVisitManagementController::class, 'update'])->name('tourist-visits.update');
        Route::post('/visites/{touristVisit}/remboursement', [TouristVisitManagementController::class, 'refund'])->name('tourist-visits.refund');
        Route::delete('/visites/{touristVisit}', [TouristVisitManagementController::class, 'destroy'])->name('tourist-visits.destroy');
    });

// ── ÉDITEUR ───────────────────────────────────────────────────────────────
Route::middleware(['auth', 'role:admin,editor'])
    ->prefix('editor')
    ->name('editor.')
    ->group(function () {
        Route::get('/dashboard', [EditorDashboardController::class, 'index'])->name('dashboard');

        // Articles
        Route::get('/articles', [EditorArticleController::class, 'index'])->name('articles.index');
        Route::get('/articles/create', [EditorArticleController::class, 'create'])->name('articles.create');
        Route::post('/articles', [EditorArticleController::class, 'store'])->name('articles.store');
        Route::get('/articles/{article}/edit', [EditorArticleController::class, 'edit'])->name('articles.edit');
        Route::put('/articles/{article}', [EditorArticleController::class, 'update'])->name('articles.update');
        Route::delete('/articles/{article}', [EditorArticleController::class, 'destroy'])->name('articles.destroy');
        Route::patch('/articles/{article}/status', [EditorArticleController::class, 'updateStatus'])->name('articles.status');
        Route::get('/articles/{article}/preview', [EditorArticleController::class, 'preview'])->name('articles.preview');
        Route::patch('/articles/{article}/autosave', [EditorArticleController::class, 'autosave'])
            ->name('articles.autosave')
            ->middleware('throttle:45,1');

        // Événements
        Route::get('/evenements', [EditorEventController::class, 'index'])->name('events.index');
        Route::get('/evenements/create', [EditorEventController::class, 'create'])->name('events.create');
        Route::post('/evenements', [EditorEventController::class, 'store'])->name('events.store');
        Route::get('/evenements/{event}/edit', [EditorEventController::class, 'edit'])->name('events.edit');
        Route::get('/evenements/{event}/preview', [EditorEventController::class, 'preview'])->name('events.preview');
        Route::patch('/evenements/{event}/autosave', [EditorEventController::class, 'autosave'])
            ->name('events.autosave')
            ->middleware('throttle:45,1');
        Route::put('/evenements/{event}', [EditorEventController::class, 'update'])->name('events.update');
        Route::delete('/evenements/{event}', [EditorEventController::class, 'destroy'])->name('events.destroy');
        Route::patch('/evenements/{event}/status', [EditorEventController::class, 'updateStatus'])->name('events.status');
        Route::post('/evenements/{event}/medias', [EditorEventController::class, 'storeMedia'])->name('events.media.store');
        Route::delete('/evenements/{event}/medias/{media}', [EditorEventController::class, 'destroyMedia'])->name('events.media.destroy');
        Route::post('/evenements/description-image', [EditorEventController::class, 'uploadDescriptionImage'])
            ->name('events.description-image')
            ->middleware('throttle:30,1');
    });

// ── PRESTATAIRE ───────────────────────────────────────────────────────────
Route::middleware(['auth', 'verified', 'role:provider', \App\Http\Middleware\ProviderStaffGate::class])
    ->prefix('provider')
    ->name('provider.')
    ->group(function () {
        Route::get('/dashboard', [ProviderDashboardController::class, 'index'])->name('dashboard');
        // Mes vidéos (médiathèque, publiées après validation)
        Route::get('/videos', [\App\Http\Controllers\Provider\VideoController::class, 'index'])->name('videos.index');
        Route::get('/videos/creer', [\App\Http\Controllers\Provider\VideoController::class, 'create'])->name('videos.create');
        Route::post('/videos', [\App\Http\Controllers\Provider\VideoController::class, 'store'])->name('videos.store');
        Route::delete('/videos/{video}', [\App\Http\Controllers\Provider\VideoController::class, 'destroy'])->name('videos.destroy');

        // Profil
        Route::get('/profil', [ProviderProfileController::class, 'edit'])->name('profile.edit');
        Route::put('/profil', [ProviderProfileController::class, 'update'])->name('profile.update');
        Route::put('/profil/horaires', [ProviderProfileController::class, 'updateHours'])->name('profile.hours');

        // Fiche hébergement (chambres, tarifs, équipements, liens de réservation) — prestataires "Hôtels"
        Route::get('/hebergement', [ProviderAccommodationController::class, 'dashboard'])->name('accommodation.dashboard');
        Route::get('/hebergement/fiche', [ProviderAccommodationController::class, 'editProfile'])->name('accommodation.profile.edit');
        Route::put('/hebergement/fiche', [ProviderAccommodationController::class, 'updateProfile'])->name('accommodation.profile.update');
        Route::get('/hebergement/chambres', [ProviderAccommodationController::class, 'roomTypes'])->name('accommodation.rooms.index');
        Route::get('/hebergement/chambres/creer', [ProviderAccommodationController::class, 'createRoomType'])->name('accommodation.rooms.create');
        Route::post('/hebergement/chambres', [ProviderAccommodationController::class, 'storeRoomType'])->name('accommodation.rooms.store');
        Route::get('/hebergement/chambres/{room}/modifier', [ProviderAccommodationController::class, 'editRoomType'])->name('accommodation.rooms.edit');
        Route::put('/hebergement/chambres/{room}', [ProviderAccommodationController::class, 'updateRoomType'])->name('accommodation.rooms.update');
        Route::delete('/hebergement/chambres/{room}', [ProviderAccommodationController::class, 'destroyRoomType'])->name('accommodation.rooms.destroy');
        Route::get('/hebergement/galerie', [ProviderAccommodationController::class, 'gallery'])->name('accommodation.gallery.index');
        Route::post('/hebergement/galerie', [ProviderAccommodationController::class, 'storeGalleryMedia'])->name('accommodation.gallery.store');
        Route::delete('/hebergement/galerie/{media}', [ProviderAccommodationController::class, 'destroyMedia'])->name('accommodation.gallery.destroy');

        // Fiche établissement Loisirs & Culture — prestataires "Loisirs & Culture"
        Route::get('/loisirs-culture', [ProviderLeisureVenueController::class, 'dashboard'])->name('leisure-venue.dashboard');
        Route::get('/loisirs-culture/fiche', [ProviderLeisureVenueController::class, 'editProfile'])->name('leisure-venue.profile.edit');
        Route::put('/loisirs-culture/fiche', [ProviderLeisureVenueController::class, 'updateProfile'])->name('leisure-venue.profile.update');
        Route::get('/loisirs-culture/galerie', [ProviderLeisureVenueController::class, 'gallery'])->name('leisure-venue.gallery.index');
        Route::post('/loisirs-culture/galerie', [ProviderLeisureVenueController::class, 'storeGalleryMedia'])->name('leisure-venue.gallery.store');
        Route::delete('/loisirs-culture/galerie/{media}', [ProviderLeisureVenueController::class, 'destroyMedia'])->name('leisure-venue.gallery.destroy');

        // Fiche établissement Restaurant — prestataires "Restaurants & Gastronomie"
        Route::get('/restaurants', [ProviderRestaurantController::class, 'dashboard'])->name('restaurant.dashboard');
        Route::get('/restaurants/fiche', [ProviderRestaurantController::class, 'editProfile'])->name('restaurant.profile.edit');
        Route::put('/restaurants/fiche', [ProviderRestaurantController::class, 'updateProfile'])->name('restaurant.profile.update');
        Route::get('/restaurants/tables', [ProviderRestaurantController::class, 'editTables'])->name('restaurant.tables.edit');
        Route::put('/restaurants/tables', [ProviderRestaurantController::class, 'updateTables'])->name('restaurant.tables.update');
        Route::post('/restaurants/plan-de-salle', [ProviderRestaurantController::class, 'storeTable'])->name('restaurant.floor.store');
        Route::patch('/restaurants/plan-de-salle/{table}', [ProviderRestaurantController::class, 'toggleTable'])->name('restaurant.floor.toggle');
        Route::delete('/restaurants/plan-de-salle/{table}', [ProviderRestaurantController::class, 'destroyTable'])->name('restaurant.floor.destroy');
        Route::get('/restaurants/menu-qr', [ProviderRestaurantController::class, 'menuQr'])->name('restaurant.menu-qr');
        Route::get('/restaurants/galerie', [ProviderRestaurantController::class, 'gallery'])->name('restaurant.gallery.index');
        Route::post('/restaurants/galerie', [ProviderRestaurantController::class, 'storeGalleryMedia'])->name('restaurant.gallery.store');
        Route::delete('/restaurants/galerie/{media}', [ProviderRestaurantController::class, 'destroyMedia'])->name('restaurant.gallery.destroy');

        // Fiche établissement Sites Touristiques — prestataires "Sites Touristiques"
        Route::get('/experiences-touristiques', [ProviderTouristExperienceController::class, 'dashboard'])->name('tourist-experience.dashboard');
        Route::get('/experiences-touristiques/fiche', [ProviderTouristExperienceController::class, 'editProfile'])->name('tourist-experience.profile.edit');
        Route::put('/experiences-touristiques/fiche', [ProviderTouristExperienceController::class, 'updateProfile'])->name('tourist-experience.profile.update');
        Route::get('/experiences-touristiques/galerie', [ProviderTouristExperienceController::class, 'gallery'])->name('tourist-experience.gallery.index');
        Route::post('/experiences-touristiques/galerie', [ProviderTouristExperienceController::class, 'storeGalleryMedia'])->name('tourist-experience.gallery.store');
        Route::delete('/experiences-touristiques/galerie/{media}', [ProviderTouristExperienceController::class, 'destroyMedia'])->name('tourist-experience.gallery.destroy');

        // Sessions de visite groupée — prestataires "Sites Touristiques"
        Route::get('/experiences-touristiques/sessions', [ProviderTouristVisitSessionController::class, 'index'])->name('tourist-experience.sessions.index');
        Route::get('/experiences-touristiques/sessions/creer', [ProviderTouristVisitSessionController::class, 'create'])->name('tourist-experience.sessions.create');
        Route::post('/experiences-touristiques/sessions', [ProviderTouristVisitSessionController::class, 'store'])->name('tourist-experience.sessions.store');
        Route::get('/experiences-touristiques/sessions/{session}/modifier', [ProviderTouristVisitSessionController::class, 'edit'])->name('tourist-experience.sessions.edit');
        Route::put('/experiences-touristiques/sessions/{session}', [ProviderTouristVisitSessionController::class, 'update'])->name('tourist-experience.sessions.update');
        Route::delete('/experiences-touristiques/sessions/{session}', [ProviderTouristVisitSessionController::class, 'destroy'])->name('tourist-experience.sessions.destroy');

        // Types de visite personnalisés — prestataires "Sites Touristiques"
        Route::get('/experiences-touristiques/types-de-visite', [ProviderTouristVisitTypeController::class, 'index'])->name('tourist-experience.visit-types.index');
        Route::get('/experiences-touristiques/types-de-visite/creer', [ProviderTouristVisitTypeController::class, 'create'])->name('tourist-experience.visit-types.create');
        Route::post('/experiences-touristiques/types-de-visite', [ProviderTouristVisitTypeController::class, 'store'])->name('tourist-experience.visit-types.store');
        Route::get('/experiences-touristiques/types-de-visite/{visitType}/modifier', [ProviderTouristVisitTypeController::class, 'edit'])->name('tourist-experience.visit-types.edit');
        Route::put('/experiences-touristiques/types-de-visite/{visitType}', [ProviderTouristVisitTypeController::class, 'update'])->name('tourist-experience.visit-types.update');
        Route::patch('/experiences-touristiques/types-de-visite/{visitType}/basculer', [ProviderTouristVisitTypeController::class, 'toggle'])->name('tourist-experience.visit-types.toggle');
        Route::delete('/experiences-touristiques/types-de-visite/{visitType}', [ProviderTouristVisitTypeController::class, 'destroy'])->name('tourist-experience.visit-types.destroy');

        // Fiche établissement Agence de Voyages — prestataires "Agences de Voyages & Tours"
        Route::get('/agences-voyages', [ProviderTravelAgencyController::class, 'dashboard'])->name('travel-agency.dashboard');
        Route::get('/agences-voyages/fiche', [ProviderTravelAgencyController::class, 'editProfile'])->name('travel-agency.profile.edit');
        Route::put('/agences-voyages/fiche', [ProviderTravelAgencyController::class, 'updateProfile'])->name('travel-agency.profile.update');
        Route::get('/agences-voyages/galerie', [ProviderTravelAgencyController::class, 'gallery'])->name('travel-agency.gallery.index');
        Route::post('/agences-voyages/galerie', [ProviderTravelAgencyController::class, 'storeGalleryMedia'])->name('travel-agency.gallery.store');
        Route::delete('/agences-voyages/galerie/{media}', [ProviderTravelAgencyController::class, 'destroyMedia'])->name('travel-agency.gallery.destroy');

        // Fiche établissement Entreprise de Transport — prestataires "Transports & Mobilité"
        Route::get('/transports', [ProviderTransportCompanyController::class, 'dashboard'])->name('transport-company.dashboard');
        Route::get('/transports/fiche', [ProviderTransportCompanyController::class, 'editProfile'])->name('transport-company.profile.edit');
        Route::put('/transports/fiche', [ProviderTransportCompanyController::class, 'updateProfile'])->name('transport-company.profile.update');
        Route::get('/transports/galerie', [ProviderTransportCompanyController::class, 'gallery'])->name('transport-company.gallery.index');
        Route::post('/transports/galerie', [ProviderTransportCompanyController::class, 'storeGalleryMedia'])->name('transport-company.gallery.store');
        Route::delete('/transports/galerie/{media}', [ProviderTransportCompanyController::class, 'destroyMedia'])->name('transport-company.gallery.destroy');

        // Œuvres (Art & Créations) — publication soumise à un abonnement Art & Créations actif
        Route::middleware('subscription.active:art-creations')->group(function () {
            Route::get('/espace-art', [ProviderArtworkController::class, 'dashboard'])->name('artworks.dashboard');
            Route::get('/oeuvres', [ProviderArtworkController::class, 'index'])->name('artworks.index');
            Route::get('/oeuvres/creer', [ProviderArtworkController::class, 'create'])->name('artworks.create');
            Route::post('/oeuvres', [ProviderArtworkController::class, 'store'])->name('artworks.store');
            Route::get('/oeuvres/{artwork}/modifier', [ProviderArtworkController::class, 'edit'])->name('artworks.edit');
            Route::put('/oeuvres/{artwork}', [ProviderArtworkController::class, 'update'])->name('artworks.update');
            Route::delete('/oeuvres/{artwork}', [ProviderArtworkController::class, 'destroy'])->name('artworks.destroy');
        });

        // Carte / menu (Restaurants & Gastronomie)
        Route::get('/carte', [ProviderMenuController::class, 'dashboard'])->name('menu.dashboard');
        Route::get('/carte/plats', [ProviderMenuController::class, 'index'])->name('menu.index');
        Route::get('/carte/plats/creer', [ProviderMenuController::class, 'create'])->name('menu.create');
        Route::post('/carte/plats', [ProviderMenuController::class, 'store'])->name('menu.store');
        Route::get('/carte/plats/{menuItem}/modifier', [ProviderMenuController::class, 'edit'])->name('menu.edit');
        Route::put('/carte/plats/{menuItem}', [ProviderMenuController::class, 'update'])->name('menu.update');
        Route::delete('/carte/plats/{menuItem}', [ProviderMenuController::class, 'destroy'])->name('menu.destroy');

        // Circuits (Agences de Voyages & Tours)
        Route::get('/circuits', [ProviderTourController::class, 'dashboard'])->name('tours.dashboard');
        Route::get('/circuits/liste', [ProviderTourController::class, 'index'])->name('tours.index');
        Route::get('/circuits/creer', [ProviderTourController::class, 'create'])->name('tours.create');
        Route::post('/circuits', [ProviderTourController::class, 'store'])->name('tours.store');
        Route::get('/circuits/{tour}/modifier', [ProviderTourController::class, 'edit'])->name('tours.edit');
        Route::put('/circuits/{tour}', [ProviderTourController::class, 'update'])->name('tours.update');
        Route::delete('/circuits/{tour}', [ProviderTourController::class, 'destroy'])->name('tours.destroy');
        Route::get('/circuits/{tour}/departs', [ProviderTourController::class, 'departures'])->name('tours.departures');
        Route::post('/circuits/{tour}/departs', [ProviderTourController::class, 'storeDeparture'])->name('tours.departures.store');
        Route::delete('/circuits/{tour}/departs/{departure}', [ProviderTourController::class, 'destroyDeparture'])->name('tours.departures.destroy');
        Route::patch('/circuits/{tour}/departs/{departure}', [ProviderTourController::class, 'updateDeparture'])->name('tours.departures.update');
        Route::post('/circuits/{tour}/departs/{departure}/garantir', [ProviderTourController::class, 'guaranteeDeparture'])->name('tours.departures.guarantee');
        Route::post('/circuits/{tour}/departs/{departure}/annuler', [ProviderTourController::class, 'cancelDeparture'])->name('tours.departures.cancel');

        // Activités (Loisirs & Culture)
        Route::get('/activites', [ProviderActivityController::class, 'dashboard'])->name('activities.dashboard');
        Route::get('/loisirs/reservations', [\App\Http\Controllers\Provider\LeisureOfferController::class, 'bookings'])->name('activity-bookings.index');
        Route::get('/loisirs/ressources', [\App\Http\Controllers\Provider\LeisureOfferController::class, 'resources'])->name('leisure-resources.index');
        Route::post('/loisirs/ressources', [\App\Http\Controllers\Provider\LeisureOfferController::class, 'storeResource'])->name('leisure-resources.store');
        Route::put('/loisirs/ressources/{resource}', [\App\Http\Controllers\Provider\LeisureOfferController::class, 'updateResource'])->name('leisure-resources.update');
        Route::delete('/loisirs/ressources/{resource}', [\App\Http\Controllers\Provider\LeisureOfferController::class, 'destroyResource'])->name('leisure-resources.destroy');
        Route::get('/loisirs/formules', [\App\Http\Controllers\Provider\LeisureOfferController::class, 'packages'])->name('leisure-packages.index');
        Route::post('/loisirs/formules', [\App\Http\Controllers\Provider\LeisureOfferController::class, 'storePackage'])->name('leisure-packages.store');
        Route::put('/loisirs/formules/{package}', [\App\Http\Controllers\Provider\LeisureOfferController::class, 'updatePackage'])->name('leisure-packages.update');
        Route::delete('/loisirs/formules/{package}', [\App\Http\Controllers\Provider\LeisureOfferController::class, 'destroyPackage'])->name('leisure-packages.destroy');
        Route::get('/loisirs/cartes', [\App\Http\Controllers\Provider\LeisureOfferController::class, 'passes'])->name('leisure-passes.index');
        Route::post('/loisirs/cartes', [\App\Http\Controllers\Provider\LeisureOfferController::class, 'storePass'])->name('leisure-passes.store');
        Route::put('/loisirs/cartes/{pass}', [\App\Http\Controllers\Provider\LeisureOfferController::class, 'updatePass'])->name('leisure-passes.update');
        Route::get('/activites/liste', [ProviderActivityController::class, 'index'])->name('activities.index');
        Route::get('/activites/creer', [ProviderActivityController::class, 'create'])->name('activities.create');
        Route::post('/activites', [ProviderActivityController::class, 'store'])->name('activities.store');
        Route::get('/activites/{activity}/modifier', [ProviderActivityController::class, 'edit'])->name('activities.edit');
        Route::put('/activites/{activity}', [ProviderActivityController::class, 'update'])->name('activities.update');
        Route::delete('/activites/{activity}', [ProviderActivityController::class, 'destroy'])->name('activities.destroy');

        // Offres de transport (Transports & Mobilité)
        Route::get('/transport', [ProviderTransportController::class, 'dashboard'])->name('transport.dashboard');
        Route::get('/transport/planning', [\App\Http\Controllers\Provider\TransportOpsController::class, 'bookings'])->name('transport-bookings.index');
        Route::post('/transport/planning/{booking}/affecter', [\App\Http\Controllers\Provider\TransportOpsController::class, 'assign'])->name('transport-bookings.assign');
        Route::post('/transport/planning/{booking}/statut', [\App\Http\Controllers\Provider\TransportOpsController::class, 'tripStatus'])->name('transport-bookings.status');
        Route::post('/transport/planning/{booking}/etat-des-lieux', [\App\Http\Controllers\Provider\TransportOpsController::class, 'inspection'])->name('transport-bookings.inspection');
        Route::get('/transport/planning/{booking}/fichier/{kind}/{index?}', [\App\Http\Controllers\Provider\TransportOpsController::class, 'file'])->name('transport-bookings.file')->whereIn('kind', ['license', 'id_card', 'out', 'in'])->whereNumber('index');
        Route::get('/transport/flotte', [\App\Http\Controllers\Provider\TransportOpsController::class, 'fleet'])->name('transport-fleet.index');
        Route::post('/transport/flotte/vehicules/{vehicle?}', [\App\Http\Controllers\Provider\TransportOpsController::class, 'saveVehicle'])->name('transport-fleet.vehicle');
        Route::post('/transport/flotte/chauffeurs/{driver?}', [\App\Http\Controllers\Provider\TransportOpsController::class, 'saveDriver'])->name('transport-fleet.driver');
        Route::get('/transport/lignes', [\App\Http\Controllers\Provider\TransportOpsController::class, 'trips'])->name('transport-trips.index');
        Route::post('/transport/lignes', [\App\Http\Controllers\Provider\TransportOpsController::class, 'storeTrips'])->name('transport-trips.store');
        Route::patch('/transport/lignes/{trip}', [\App\Http\Controllers\Provider\TransportOpsController::class, 'toggleTrip'])->name('transport-trips.toggle');
        Route::get('/transport/liste', [ProviderTransportController::class, 'index'])->name('transport.index');
        Route::get('/transport/creer', [ProviderTransportController::class, 'create'])->name('transport.create');
        Route::post('/transport', [ProviderTransportController::class, 'store'])->name('transport.store');
        Route::get('/transport/{offer}/modifier', [ProviderTransportController::class, 'edit'])->name('transport.edit');
        Route::put('/transport/{offer}', [ProviderTransportController::class, 'update'])->name('transport.update');
        Route::delete('/transport/{offer}', [ProviderTransportController::class, 'destroy'])->name('transport.destroy');

        // Commandes reçues sur les œuvres — pas de gate abonnement : une commande déjà
        // payée doit pouvoir être honorée même si l'abonnement a expiré entre-temps.
        Route::get('/commandes-art', [ProviderArtworkOrderController::class, 'index'])->name('art-orders.index');
        Route::get('/commandes-art/{artworkOrder}', [ProviderArtworkOrderController::class, 'show'])->name('art-orders.show');
        Route::patch('/commandes-art/{artworkOrder}/statut', [ProviderArtworkOrderController::class, 'updateStatus'])->name('art-orders.status');
        Route::post('/commandes-art/{artworkOrder}/expedier', [\App\Http\Controllers\Provider\ArtStudioController::class, 'ship'])->name('art-orders.ship');
        Route::post('/commandes-art/{artworkOrder}/retour', [\App\Http\Controllers\Provider\ArtStudioController::class, 'returnDecision'])->name('art-orders.return');
        Route::get('/page-artiste', [\App\Http\Controllers\Provider\ArtStudioController::class, 'editProfile'])->name('artist-profile.edit');
        Route::put('/page-artiste', [\App\Http\Controllers\Provider\ArtStudioController::class, 'updateProfile'])->name('artist-profile.update');
        Route::get('/offres-oeuvres', [\App\Http\Controllers\Provider\ArtStudioController::class, 'offers'])->name('art-offers.index');
        Route::post('/offres-oeuvres/{offer}', [\App\Http\Controllers\Provider\ArtStudioController::class, 'respondOffer'])->name('art-offers.respond');
        Route::get('/sur-mesure', [\App\Http\Controllers\Provider\ArtStudioController::class, 'commissions'])->name('art-commissions.index');
        Route::post('/sur-mesure/{artCommission}', [\App\Http\Controllers\Provider\ArtStudioController::class, 'commissionAction'])->name('art-commissions.action');

        // Litiges sur les réservations
        Route::get('/litiges', [\App\Http\Controllers\DisputeController::class, 'providerIndex'])->name('disputes.index');
        Route::post('/litiges/{dispute}/repondre', [\App\Http\Controllers\DisputeController::class, 'providerRespond'])->name('disputes.respond');
        Route::get('/portefeuille/export', [ProviderWalletController::class, 'export'])->name('wallet.export');

        // Demandes de réservation (restaurant, loisir, circuit)
        Route::get('/demandes-reservation', [ProviderBookingRequestController::class, 'index'])->name('booking-requests.index');
        Route::post('/demandes-reservation/{bookingRequest}/repondre', [ProviderBookingRequestController::class, 'respond'])->name('booking-requests.respond');

        // Réservations reçues
        Route::get('/reservations', [ProviderReservationController::class, 'index'])->name('reservations.index');
        Route::get('/reservations/{reservation}', [ProviderReservationController::class, 'show'])->name('reservations.show');
        Route::patch('/reservations/{reservation}/status', [ProviderReservationController::class, 'updateStatus'])->name('reservations.status');

        // Bons cadeaux
        Route::get('/planning', [\App\Http\Controllers\Provider\AgendaController::class, 'index'])->name('agenda');
        Route::get('/codes-promo', [\App\Http\Controllers\Provider\PromoCodeController::class, 'index'])->name('promo-codes.index');
        Route::post('/codes-promo', [\App\Http\Controllers\Provider\PromoCodeController::class, 'store'])->name('promo-codes.store');
        Route::post('/codes-promo/{promoCode}/activation', [\App\Http\Controllers\Provider\PromoCodeController::class, 'toggle'])->name('promo-codes.toggle');
        Route::get('/bons-cadeaux', [\App\Http\Controllers\Provider\GiftCardController::class, 'index'])->name('gift-cards.index');
        Route::post('/bons-cadeaux/activation', [\App\Http\Controllers\Provider\GiftCardController::class, 'toggle'])->name('gift-cards.toggle');
        Route::post('/bons-cadeaux/{giftCard}/valider', [\App\Http\Controllers\Provider\GiftCardController::class, 'redeem'])->name('gift-cards.redeem')->middleware('throttle:30,1');

        // Comptes équipe (réservé au propriétaire)
        Route::get('/equipe', [\App\Http\Controllers\Provider\TeamController::class, 'index'])->name('team.index');
        Route::post('/equipe', [\App\Http\Controllers\Provider\TeamController::class, 'store'])->name('team.store')->middleware('throttle:10,1');
        Route::delete('/equipe/{member}', [\App\Http\Controllers\Provider\TeamController::class, 'destroy'])->name('team.destroy');

        // Contrôle des billets à l'entrée des sites (QR)
        Route::get('/billets', [\App\Http\Controllers\Provider\TicketController::class, 'index'])->name('tickets.index');
        Route::get('/billets/{code}', [\App\Http\Controllers\Provider\TicketController::class, 'show'])->where('code', '[A-Za-z0-9]{6,16}')->name('tickets.show');
        Route::post('/billets/{code}/entree', [\App\Http\Controllers\Provider\TicketController::class, 'checkIn'])->where('code', '[A-Za-z0-9]{6,16}')->name('tickets.check-in')->middleware('throttle:60,1');

        // Visites reçues (sites touristiques) — volontairement séparé des réservations hôtelières ci-dessus
        Route::get('/visites', [ProviderTouristVisitController::class, 'index'])->name('tourist-visits.index');
        Route::get('/visites/{touristVisit}', [ProviderTouristVisitController::class, 'show'])->name('tourist-visits.show')
            ->missing(fn () => redirect()->route('provider.tourist-visits.index')->with('error', "Cette visite n'existe pas ou a été supprimée."));
        Route::patch('/visites/{touristVisit}/statut', [ProviderTouristVisitController::class, 'updateStatus'])->name('tourist-visits.status');

        // Vérification du compte (justificatifs exigés avant retrait)
        Route::get('/verification', [ProviderVerificationController::class, 'index'])->name('verification.index');
        Route::post('/verification', [ProviderVerificationController::class, 'store'])->name('verification.store')->middleware('throttle:10,1');

        // Portefeuille (solde acomptes, retraits)
        Route::get('/portefeuille', [ProviderWalletController::class, 'index'])->name('wallet.index');
        Route::post('/portefeuille/retrait', [ProviderWalletController::class, 'requestPayout'])->name('wallet.request-payout')->middleware('throttle:5,1');
        Route::post('/portefeuille/retrait/{payoutRequest}/verifier', [ProviderWalletController::class, 'verifyPayoutOtp'])->name('wallet.payout.verify')->middleware('throttle:10,1');
        Route::post('/portefeuille/retrait/{payoutRequest}/renvoyer-code', [ProviderWalletController::class, 'resendPayoutOtp'])->name('wallet.payout.resend')->middleware('throttle:6,1');

        // Avis (réponses)
        Route::get('/avis', [ProviderReviewController::class, 'index'])->name('reviews.index');
        Route::post('/avis/{review}/repondre', [ProviderReviewController::class, 'reply'])->name('reviews.reply');
        Route::delete('/avis/{review}/reponses/{reply}', [ProviderReviewController::class, 'destroyReply'])->name('reviews.reply.destroy');
        Route::delete('/avis/{review}', [ProviderReviewController::class, 'destroy'])->name('reviews.destroy');

        // Analytics
        Route::get('/analytics', [ProviderAnalyticsController::class, 'index'])->name('analytics');
        Route::get('/analyse-avancee', [\App\Http\Controllers\Provider\ProviderInsightsController::class, 'show'])->name('insights');
        Route::get('/analyse-avancee/export', [\App\Http\Controllers\Provider\ProviderInsightsController::class, 'export'])->name('insights.export');

        // Médias
        Route::get('/medias', [ProviderMediaController::class, 'index'])->name('media.index');
        Route::post('/medias', [ProviderMediaController::class, 'store'])->name('media.store');
        Route::delete('/medias/{media}', [ProviderMediaController::class, 'destroy'])->name('media.destroy');
        Route::get('/messagerie', [ProviderConversationController::class, 'index'])->name('conversations.index');
        Route::get('/messagerie/poll', [ProviderConversationController::class, 'poll'])->name('conversations.poll');
        Route::post('/messagerie', [ProviderConversationController::class, 'store'])->name('conversations.store');
        Route::get('/messagerie/{conversation}', [ProviderConversationController::class, 'show'])->name('conversations.show');
        Route::post('/messagerie/{conversation}/reply', [ProviderConversationController::class, 'reply'])->name('conversations.reply');
        Route::patch('/messagerie/{conversation}/messages/{message}', [ProviderConversationController::class, 'updateMessage'])->name('conversations.messages.update');
        Route::delete('/messagerie/{conversation}/messages/{message}', [ProviderConversationController::class, 'deleteMessage'])->name('conversations.messages.delete');
        Route::get('/messagerie/{conversation}/attachments/{attachment}/download', [ProviderConversationController::class, 'downloadAttachment'])->name('conversations.attachments.download');
        Route::get('/messagerie/{conversation}/attachments/{attachment}/preview', [ProviderConversationController::class, 'previewAttachment'])->name('conversations.attachments.preview');

        // Messagerie avec les clients (distincte de la messagerie support admin ci-dessus)
        Route::get('/messages-clients', [ProviderClientConversationController::class, 'index'])->name('client-conversations.index');
        Route::get('/messages-clients/poll', [ProviderClientConversationController::class, 'poll'])->name('client-conversations.poll');
        Route::post('/messages-clients/reponses-rapides', [ProviderClientConversationController::class, 'updateQuickReplies'])->name('client-conversations.quick-replies');
        Route::get('/messages-clients/{conversation}', [ProviderClientConversationController::class, 'show'])->name('client-conversations.show');
        Route::post('/messages-clients/{conversation}/reply', [ProviderClientConversationController::class, 'reply'])->name('client-conversations.reply')->middleware('throttle:20,1');
        Route::post('/messages-clients/{conversation}/signaler', [ProviderClientConversationController::class, 'report'])->name('client-conversations.report');
        Route::post('/messages-clients/{conversation}/bloquer', [ProviderClientConversationController::class, 'block'])->name('client-conversations.block');
        Route::get('/messages-clients/{conversation}/pieces-jointes/{attachment}/telecharger', [ProviderClientConversationController::class, 'downloadAttachment'])->name('client-conversations.attachments.download');
        Route::get('/messages-clients/{conversation}/pieces-jointes/{attachment}/apercu', [ProviderClientConversationController::class, 'previewAttachment'])->name('client-conversations.attachments.preview');

        Route::get('/notifications', [ProviderNotificationController::class, 'index'])->name('notifications.index');
        Route::patch('/notifications/read-all', [ProviderNotificationController::class, 'markAllRead'])->name('notifications.read-all');
        Route::patch('/notifications/{notification}/read', [ProviderNotificationController::class, 'markRead'])->name('notifications.read');

        // Billing
        Route::get('/billing/plans', [BillingController::class, 'plans'])->name('billing.plans');
        Route::get('/billing/checkout/{plan}', [BillingController::class, 'checkout'])->name('billing.checkout');
        Route::post('/billing/checkout/{plan}/pay', [BillingController::class, 'initiate'])->name('billing.pay');
        Route::post('/billing/promo/validate', [BillingController::class, 'validatePromo'])->name('billing.promo.validate')->middleware('throttle:20,1');
        Route::get('/billing/confirmation/{payment}', [BillingController::class, 'confirmation'])->name('billing.confirmation');
        Route::get('/billing/factures', [BillingController::class, 'invoices'])->name('billing.invoices');

        // CynetPay AJAX initiation + status check
        Route::post('/billing/cynetpay/initier', [PaymentController::class, 'initiateCynetPayPayment'])
            ->name('payment.cynetpay.initiate')
            ->middleware('throttle:10,1');
        Route::post('/paiements/{payment}/verifier-statut', [PaymentController::class, 'checkStatus'])
            ->name('payment.check-status')
            ->middleware('throttle:30,1');
        Route::get('/premium-content', fn () => view('provider.premium-content'))
            ->middleware('subscription.active')
            ->name('premium-content');
    });

// ── VISITEUR ──────────────────────────────────────────────────────────────
Route::middleware(['auth', 'verified', 'role:visitor'])
    ->prefix('visitor')
    ->name('visitor.')
    ->group(function () {
        Route::get('/dashboard', [VisitorDashboardController::class, 'index'])->name('dashboard');
        Route::get('/profil', [VisitorProfileController::class, 'edit'])->name('profile.edit');
        Route::put('/profil', [VisitorProfileController::class, 'update'])->name('profile.update');

        Route::get('/reservations', [VisitorReservationController::class, 'index'])->name('reservations.index');
        Route::get('/reservations/{reservation}', [VisitorReservationController::class, 'show'])->name('reservations.show');
        Route::post('/reservations/{reservation}/litige', [\App\Http\Controllers\DisputeController::class, 'store'])->name('reservations.dispute')->middleware('throttle:5,60');
        Route::post('/reservations/{reservation}/annuler', [VisitorReservationController::class, 'cancel'])->name('reservations.cancel')->middleware('throttle:10,1');
        Route::get('/demandes', [BookingRequestController::class, 'index'])->name('booking-requests.index');
        Route::post('/demandes/{bookingRequest}/payer', [\App\Http\Controllers\BookingPaymentController::class, 'pay'])->name('booking-requests.pay')->middleware('throttle:10,1');
        Route::post('/demandes/{bookingRequest}/annuler', [BookingRequestController::class, 'cancel'])->name('booking-requests.cancel')->middleware('throttle:10,1');
        Route::get('/visites', [TouristVisitController::class, 'mine'])->name('tourist-visits.index');
        Route::get('/voyages', [\App\Http\Controllers\TourBookingController::class, 'mine'])->name('tour-bookings.index');
        Route::get('/trajets', [\App\Http\Controllers\TransportBookingController::class, 'mine'])->name('transport-bookings.index');
        Route::post('/trajets/{transportBooking}/annuler', [\App\Http\Controllers\TransportBookingController::class, 'cancel'])->name('transport-bookings.cancel')->middleware('throttle:10,1');
        Route::post('/trajets/{transportBooking}/modifier', [\App\Http\Controllers\TransportBookingController::class, 'modify'])->name('transport-bookings.modify')->middleware('throttle:10,1');
        Route::post('/trajets/{transportBooking}/documents', [\App\Http\Controllers\TransportBookingController::class, 'documents'])->name('transport-bookings.documents')->middleware('throttle:10,1');
        Route::get('/activites', [\App\Http\Controllers\LeisureBookingController::class, 'mine'])->name('activity-bookings.index');
        Route::post('/activites/{activityBooking}/annuler', [\App\Http\Controllers\LeisureBookingController::class, 'cancel'])->name('activity-bookings.cancel')->middleware('throttle:10,1');
        Route::post('/activites/{activityBooking}/reporter', [\App\Http\Controllers\LeisureBookingController::class, 'reschedule'])->name('activity-bookings.reschedule')->middleware('throttle:10,1');
        Route::delete('/activites/liste-attente/{waitlist}', [\App\Http\Controllers\LeisureBookingController::class, 'leaveWaitlist'])->name('activity-waitlist.leave');
        Route::post('/voyages/{tourBooking}/payer', [\App\Http\Controllers\TourBookingController::class, 'pay'])->name('tour-bookings.pay')->middleware('throttle:10,1');
        Route::post('/voyages/{tourBooking}/annuler', [\App\Http\Controllers\TourBookingController::class, 'cancel'])->name('tour-bookings.cancel')->middleware('throttle:10,1');
        Route::put('/voyages/{tourBooking}/voyageurs', [\App\Http\Controllers\TourBookingController::class, 'updateTravelers'])->name('tour-bookings.travelers')->middleware('throttle:20,1');
        Route::post('/visites/{touristVisit}/annuler', [TouristVisitController::class, 'cancel'])->name('tourist-visits.cancel')->middleware('throttle:10,1');
        Route::get('/mes-recus', [VisitorReservationController::class, 'receipts'])->name('receipts.index');

        Route::get('/messages', [VisitorConversationController::class, 'index'])->name('conversations.index');
        Route::get('/messages/poll', [VisitorConversationController::class, 'poll'])->name('conversations.poll');
        Route::post('/messages', [VisitorConversationController::class, 'store'])->name('conversations.store')->middleware('throttle:5,1');
        Route::get('/messages/{conversation}', [VisitorConversationController::class, 'show'])->name('conversations.show');
        Route::post('/messages/{conversation}/reply', [VisitorConversationController::class, 'reply'])->name('conversations.reply')->middleware('throttle:20,1');
        Route::post('/messages/{conversation}/signaler', [VisitorConversationController::class, 'report'])->name('conversations.report');
        Route::post('/messages/{conversation}/bloquer', [VisitorConversationController::class, 'block'])->name('conversations.block');
        Route::get('/messages/{conversation}/pieces-jointes/{attachment}/telecharger', [VisitorConversationController::class, 'downloadAttachment'])->name('conversations.attachments.download');
        Route::get('/messages/{conversation}/pieces-jointes/{attachment}/apercu', [VisitorConversationController::class, 'previewAttachment'])->name('conversations.attachments.preview');

        Route::get('/portefeuille', [VisitorWalletController::class, 'index'])->name('wallet.index');
        Route::post('/portefeuille/recharger', [VisitorWalletController::class, 'initiateTopup'])
            ->name('wallet.topup.initiate')
            ->middleware('throttle:10,1');
        Route::post('/portefeuille/retrait', [VisitorWalletController::class, 'requestPayout'])->name('wallet.request-payout')->middleware('throttle:5,1');
        Route::post('/portefeuille/retrait/{payoutRequest}/verifier', [VisitorWalletController::class, 'verifyPayoutOtp'])->name('wallet.payout.verify')->middleware('throttle:10,1');
        Route::post('/portefeuille/retrait/{payoutRequest}/renvoyer-code', [VisitorWalletController::class, 'resendPayoutOtp'])->name('wallet.payout.resend')->middleware('throttle:6,1');

        Route::get('/favoris', [VisitorFavoriteController::class, 'index'])->name('favorites.index');
        Route::post('/favoris', [VisitorFavoriteController::class, 'store'])->name('favorites.store');
        Route::post('/favoris/basculer', [VisitorFavoriteController::class, 'toggle'])->name('favorites.toggle')->middleware('throttle:60,1');
        Route::delete('/favoris/{favorite}', [VisitorFavoriteController::class, 'destroy'])->name('favorites.destroy');

        Route::get('/notifications', [VisitorNotificationController::class, 'index'])->name('notifications.index');
        Route::patch('/notifications/read-all', [VisitorNotificationController::class, 'markAllRead'])->name('notifications.read-all');

        Route::get('/mes-achats', [VisitorPurchaseController::class, 'index'])->name('purchases.index');
        Route::get('/mes-achats/{purchase:uuid}/telecharger', [VisitorPurchaseController::class, 'download'])->name('purchases.download');

        Route::get('/mes-commandes-art', [VisitorArtOrderController::class, 'index'])->name('art-orders.index');
        Route::post('/mes-commandes-art/{artworkOrder}/echeance', [ArtworkPurchaseController::class, 'payInstallment'])->name('art-orders.installment')->middleware('throttle:10,1');
        Route::post('/mes-commandes-art/{artworkOrder}/reception', [\App\Http\Controllers\ArtMarketController::class, 'confirmReceipt'])->name('art-orders.receipt');
        Route::post('/mes-commandes-art/{artworkOrder}/retour', [\App\Http\Controllers\ArtMarketController::class, 'requestReturn'])->name('art-orders.return')->middleware('throttle:5,1');
        Route::get('/sur-mesure', [\App\Http\Controllers\ArtMarketController::class, 'myCommissions'])->name('art-commissions.index');
        Route::post('/sur-mesure/{artCommission}/payer', [\App\Http\Controllers\ArtMarketController::class, 'payCommission'])->name('art-commissions.pay')->middleware('throttle:10,1');
        Route::post('/sur-mesure/{artCommission}/esquisse', [\App\Http\Controllers\ArtMarketController::class, 'sketchDecision'])->name('art-commissions.sketch');
        Route::post('/sur-mesure/{artCommission}/annuler', [\App\Http\Controllers\ArtMarketController::class, 'cancelCommission'])->name('art-commissions.cancel');
        Route::post('/offres/{offer}/accepter', [\App\Http\Controllers\ArtMarketController::class, 'acceptCounter'])->name('art-offers.accept');
        Route::post('/offres/{offer}/retirer', [\App\Http\Controllers\ArtMarketController::class, 'withdrawOffer'])->name('art-offers.withdraw');
    });
