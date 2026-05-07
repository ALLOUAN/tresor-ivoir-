<?php

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
use App\Http\Controllers\Admin\ReviewManagementController;
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
use App\Http\Controllers\InformationPageController;
use App\Http\Controllers\Provider\BillingController;
use App\Http\Controllers\Provider\ConversationController as ProviderConversationController;
use App\Http\Controllers\Provider\MediaController as ProviderMediaController;
use App\Http\Controllers\Provider\NotificationController as ProviderNotificationController;
use App\Http\Controllers\Provider\PaymentController;
use App\Http\Controllers\Provider\ProfileController as ProviderProfileController;
use App\Http\Controllers\Provider\ProviderAnalyticsController;
use App\Http\Controllers\Provider\ReviewController as ProviderReviewController;
use App\Http\Controllers\ProviderController;
use App\Http\Controllers\PublicContactController;
use App\Http\Controllers\MediaPurchaseController;
use App\Http\Controllers\PublicHomeGalleryController;
use App\Http\Controllers\PublicNewsletterController;
use App\Http\Controllers\PublicSubscriptionController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\Admin\NotificationController as AdminNotificationController;
use App\Http\Controllers\VisitorFavoriteController;
use App\Http\Controllers\VisitorNotificationController;
use App\Http\Controllers\VisitorProfileController;
use App\Http\Controllers\VisitorPurchaseController;
use App\Http\Middleware\LogAdminActions;
use App\Models\AppearanceSlide;
use App\Models\Article;
use App\Models\ArticleCategory;
use App\Models\Event;
use App\Models\InformationPage;
use App\Models\Partner;
use App\Models\Provider;
use App\Models\ProviderCategory;
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
        ->with('category')
        ->where('status', 'active')
        ->orderByDesc('is_featured')
        ->orderByDesc('rating_avg')
        ->limit(4)
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

    $homeArticles = Schema::hasTable('articles')
        ? Article::where('status', 'published')
            ->where('published_at', '<=', now())
            ->with($articleWith)
            ->latest('published_at')
            ->limit(15)
            ->get()
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

    return view('welcome', compact(
        'homeEvents', 'homeProviders', 'heroSlides', 'homePartners',
        'informationPages', 'homeArticles', 'homeCategories', 'homeProviderCategories', 'homeDestinationArticle', 'hideHomeHeroArticle'
    ));
})->name('home');

Route::get('/galerie-tresors-ivoire/visuelle/{uuid}', [PublicHomeGalleryController::class, 'show'])
    ->where('uuid', '[0-9a-fA-F]{8}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{12}')
    ->name('gallery.public.show');
Route::get('/galerie-tresors-ivoire', [PublicHomeGalleryController::class, 'index'])->name('gallery.public');

// ── ACHAT MÉDIA ───────────────────────────────────────────────────────────────
Route::get('/galerie/achat/retour', [MediaPurchaseController::class, 'handleReturn'])->name('gallery.purchase.return');
Route::post('/galerie/achat/webhook', [MediaPurchaseController::class, 'webhook'])->name('gallery.purchase.webhook');
Route::get('/galerie/achat/{media:uuid}/init', [MediaPurchaseController::class, 'init'])->name('gallery.purchase.init');
Route::post('/galerie/achat/{media:uuid}/creer-et-payer', [MediaPurchaseController::class, 'registerAndPay'])->name('gallery.purchase.register')->middleware('throttle:10,1');
Route::post('/galerie/achat/{media:uuid}/payer', [MediaPurchaseController::class, 'pay'])->name('gallery.purchase.pay')->middleware(['auth', 'throttle:10,1']);

Route::post('/contact', [PublicContactController::class, 'store'])
    ->name('contact.store')
    ->middleware('throttle:8,1');

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

// ── SITEMAP & RSS ─────────────────────────────────────────────────────────
Route::get('/sitemap.xml', function () {
    $articles  = \App\Models\Article::where('status', 'published')->where('published_at', '<=', now())
        ->select('slug_fr', 'published_at', 'updated_at')->latest('published_at')->limit(1000)->get();
    $events    = \App\Models\Event::where('status', 'published')
        ->select('slug', 'updated_at')->limit(500)->get();
    $providers = \App\Models\Provider::where('status', 'active')
        ->select('slug', 'updated_at')->limit(500)->get();
    $pages     = \App\Models\InformationPage::select('id', 'updated_at')->get();

    return response()->view('sitemap', compact('articles', 'events', 'providers', 'pages'))
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

// ── PLANS D'ABONNEMENT PUBLICS ────────────────────────────────────────────
Route::get('/abonnements', function () {
    $plans = SubscriptionPlan::where('is_active', true)
        ->orderBy('sort_order')
        ->get();

    $cycleSettings = PaymentSetting::query()->pluck('value', 'key');
    $showMonthly = ($cycleSettings['cycle_monthly_active'] ?? '1') === '1';
    $showYearly  = ($cycleSettings['cycle_yearly_active']  ?? '1') === '1';
    $yearlySavingsLabel = $cycleSettings['cycle_yearly_savings_label'] ?? '-20%';

    return view('public.plans', compact('plans', 'showMonthly', 'showYearly', 'yearlySavingsLabel'));
})->name('plans.public');

Route::get('/abonnements/{plan}/paiement', [PublicSubscriptionController::class, 'checkout'])
    ->name('subscriptions.checkout');
Route::post('/abonnements/{plan}/traiter', [PublicSubscriptionController::class, 'processOffline'])
    ->middleware('auth')
    ->name('subscriptions.process-offline');

// ── ANNUAIRE PRESTATAIRES PUBLIC ──────────────────────────────────────────
Route::get('/annuaire', [ProviderController::class, 'index'])->name('providers.index');
Route::get('/annuaire/{slug}', [ProviderController::class, 'show'])->name('providers.show');

// ── AVIS (POST — auth optionnel) ──────────────────────────────────────────
Route::post('/annuaire/{provider}/avis', [ReviewController::class, 'store'])
    ->name('reviews.store')
    ->middleware('auth');

// ── AUTH ──────────────────────────────────────────────────────────────────
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.post');
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->name('register.post');

    // Mot de passe oublié / réinitialisation
    Route::get('/mot-de-passe-oublie', [AuthController::class, 'showForgotPassword'])->name('password.request');
    Route::post('/mot-de-passe-oublie', [AuthController::class, 'sendResetLink'])->name('password.email');
    Route::get('/reinitialiser/{token}', [AuthController::class, 'showResetPassword'])->name('password.reset');
    Route::post('/reinitialiser', [AuthController::class, 'resetPassword'])->name('password.update');
});

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
        $provider = Provider::query()->where('user_id', $user->id)->first();
        $hasActiveSubscription = $provider
            ? $provider->subscriptions()
                ->where('status', 'active')
                ->where('ends_at', '>', now())
                ->exists()
            : false;

        if (! $hasActiveSubscription) {
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
        Route::get('/analytics', [AdminAnalyticsController::class, 'index'])->name('analytics.index');
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

        // Prestataires
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

        // Utilisateurs
        Route::get('/users', [UserRoleManagementController::class, 'index'])->name('users.index');
        Route::post('/users', [UserRoleManagementController::class, 'store'])->name('users.store');
        Route::patch('/users/{user}/role', [UserRoleManagementController::class, 'update'])->name('users.role.update');
        Route::patch('/users/{user}/permissions', [UserRoleManagementController::class, 'updatePermissions'])->name('users.permissions.update');
        Route::delete('/users/{user}', [UserRoleManagementController::class, 'destroy'])->name('users.destroy');
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
    });

// ── PRESTATAIRE ───────────────────────────────────────────────────────────
Route::middleware(['auth', 'verified', 'role:provider'])
    ->prefix('provider')
    ->name('provider.')
    ->group(function () {
        Route::get('/dashboard', [ProviderDashboardController::class, 'index'])->name('dashboard');

        // Profil
        Route::get('/profil', [ProviderProfileController::class, 'edit'])->name('profile.edit');
        Route::put('/profil', [ProviderProfileController::class, 'update'])->name('profile.update');
        Route::put('/profil/horaires', [ProviderProfileController::class, 'updateHours'])->name('profile.hours');

        // Avis (réponses)
        Route::get('/avis', [ProviderReviewController::class, 'index'])->name('reviews.index');
        Route::post('/avis/{review}/repondre', [ProviderReviewController::class, 'reply'])->name('reviews.reply');
        Route::delete('/avis/{review}/reponses/{reply}', [ProviderReviewController::class, 'destroyReply'])->name('reviews.reply.destroy');
        Route::delete('/avis/{review}', [ProviderReviewController::class, 'destroy'])->name('reviews.destroy');

        // Analytics
        Route::get('/analytics', [ProviderAnalyticsController::class, 'index'])->name('analytics');

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

        Route::get('/favoris', [VisitorFavoriteController::class, 'index'])->name('favorites.index');
        Route::post('/favoris', [VisitorFavoriteController::class, 'store'])->name('favorites.store');
        Route::delete('/favoris/{favorite}', [VisitorFavoriteController::class, 'destroy'])->name('favorites.destroy');

        Route::get('/notifications', [VisitorNotificationController::class, 'index'])->name('notifications.index');
        Route::patch('/notifications/read-all', [VisitorNotificationController::class, 'markAllRead'])->name('notifications.read-all');

        Route::get('/mes-achats', [VisitorPurchaseController::class, 'index'])->name('purchases.index');
        Route::get('/mes-achats/{purchase:uuid}/telecharger', [VisitorPurchaseController::class, 'download'])->name('purchases.download');
    });
