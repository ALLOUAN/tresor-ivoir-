<?php

use App\Http\Controllers\Admin\AdministrationController;
use App\Http\Controllers\Admin\ArticleManagementController;
use App\Http\Controllers\Admin\ContactMessageController;
use App\Http\Controllers\Admin\EventManagementController;
use App\Http\Controllers\Admin\FinanceManagementController;
use App\Http\Controllers\Admin\PermissionManagementController;
use App\Http\Controllers\Admin\PlanManagementController;
use App\Http\Controllers\Admin\ProviderManagementController;
use App\Http\Controllers\Admin\ReviewManagementController;
use App\Http\Controllers\Admin\UserRoleManagementController;
use App\Http\Controllers\ArticleController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Dashboard\AdminDashboardController;
use App\Http\Controllers\Dashboard\EditorDashboardController;
use App\Http\Controllers\Dashboard\ProviderDashboardController;
use App\Http\Controllers\Dashboard\VisitorDashboardController;
use App\Http\Controllers\Editor\ArticleController as EditorArticleController;
use App\Http\Controllers\Editor\EventController as EditorEventController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\Provider\BillingController;
use App\Http\Controllers\Provider\ProfileController as ProviderProfileController;
use App\Http\Controllers\Provider\ReviewController as ProviderReviewController;
use App\Http\Controllers\ProviderController;
use App\Http\Controllers\PublicContactController;
use App\Http\Controllers\PublicNewsletterController;
use App\Http\Controllers\ReviewController;
use App\Models\AppearanceSlide;
use App\Models\Event;
use App\Models\Provider;
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
            ->whereNotNull('desktop_image_url')
            ->orderBy('display_order')
            ->orderByDesc('id')
            ->get()
        : collect();

    return view('welcome', compact('homeEvents', 'homeProviders', 'heroSlides'));
})->name('home');

Route::post('/contact', [PublicContactController::class, 'store'])
    ->name('contact.store')
    ->middleware('throttle:8,1');

Route::post('/newsletter/subscribe', [PublicNewsletterController::class, 'subscribe'])
    ->name('newsletter.subscribe')
    ->middleware('throttle:10,1');

// ── ARTICLES PUBLICS ──────────────────────────────────────────────────────
Route::get('/articles', [ArticleController::class, 'index'])->name('articles.index');
Route::get('/articles/{slug}', [ArticleController::class, 'show'])->name('articles.show');

// ── ÉVÉNEMENTS PUBLICS ────────────────────────────────────────────────────
Route::get('/evenements', [EventController::class, 'index'])->name('events.index');
Route::get('/evenements/{slug}', [EventController::class, 'show'])->name('events.show');

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
});

Route::get('/billing/callback', [BillingController::class, 'callback'])->name('provider.billing.callback');
Route::post('/billing/webhook/{gateway}', [BillingController::class, 'webhook'])->name('provider.billing.webhook');

Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

// ── DASHBOARD REDIRECT ────────────────────────────────────────────────────
Route::get('/dashboard', function () {
    $user = Auth::user();
    if (! $user) {
        abort(403);
    }

    return redirect()->route(match ($user->role) {
        'admin' => 'admin.dashboard',
        'editor' => 'editor.dashboard',
        'provider' => 'provider.dashboard',
        default => 'visitor.dashboard',
    });
})->middleware('auth')->name('dashboard');

// ── ADMIN ─────────────────────────────────────────────────────────────────
Route::middleware(['auth', 'role:admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
        Route::get('/permissions', [PermissionManagementController::class, 'index'])->name('permissions');
        Route::get('/administration/maintenance', [AdministrationController::class, 'maintenance'])->name('administration.maintenance');
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
        Route::get('/administration/reseaux-sociaux', [AdministrationController::class, 'social'])->name('administration.social');
        Route::put('/administration/reseaux-sociaux', [AdministrationController::class, 'updateSocialSettings'])->name('administration.social.update');
        Route::get('/administration/medias', [AdministrationController::class, 'media'])->name('administration.media');
        Route::post('/administration/medias', [AdministrationController::class, 'storeSiteMedia'])->name('administration.media.store');
        Route::delete('/administration/medias/{siteMediaItem}', [AdministrationController::class, 'destroySiteMedia'])->name('administration.media.destroy');
        Route::get('/administration/parametres', [AdministrationController::class, 'settings'])->name('administration.settings');
        Route::put('/administration/parametres', [AdministrationController::class, 'updateSiteSettings'])->name('administration.settings.update');
        Route::get('/administration/partenaires', [AdministrationController::class, 'partners'])->name('administration.partners');
        Route::get('/administration/centre-information', [AdministrationController::class, 'infoCenter'])->name('administration.info-center');

        // Articles
        Route::get('/articles', [ArticleManagementController::class, 'index'])->name('articles.index');
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
        Route::patch('/prestataires/{provider}/validate', [ProviderManagementController::class, 'validateProvider'])->name('providers.validate');
        Route::patch('/prestataires/{provider}/suspend', [ProviderManagementController::class, 'suspend'])->name('providers.suspend');

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

        // Événements
        Route::get('/evenements', [EditorEventController::class, 'index'])->name('events.index');
        Route::get('/evenements/create', [EditorEventController::class, 'create'])->name('events.create');
        Route::post('/evenements', [EditorEventController::class, 'store'])->name('events.store');
        Route::get('/evenements/{event}/edit', [EditorEventController::class, 'edit'])->name('events.edit');
        Route::put('/evenements/{event}', [EditorEventController::class, 'update'])->name('events.update');
        Route::delete('/evenements/{event}', [EditorEventController::class, 'destroy'])->name('events.destroy');
        Route::patch('/evenements/{event}/status', [EditorEventController::class, 'updateStatus'])->name('events.status');
    });

// ── PRESTATAIRE ───────────────────────────────────────────────────────────
Route::middleware(['auth', 'role:provider'])
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
        Route::delete('/avis/{review}/reponse', [ProviderReviewController::class, 'destroyReply'])->name('reviews.reply.destroy');

        // Billing
        Route::get('/billing/plans', [BillingController::class, 'plans'])->name('billing.plans');
        Route::get('/billing/checkout/{plan}', [BillingController::class, 'checkout'])->name('billing.checkout');
        Route::post('/billing/checkout/{plan}/pay', [BillingController::class, 'initiate'])->name('billing.pay');
        Route::get('/billing/confirmation/{payment}', [BillingController::class, 'confirmation'])->name('billing.confirmation');
        Route::get('/billing/factures', [BillingController::class, 'invoices'])->name('billing.invoices');
        Route::get('/premium-content', fn () => view('provider.premium-content'))
            ->middleware('subscription.active')
            ->name('premium-content');
    });

// ── VISITEUR ──────────────────────────────────────────────────────────────
Route::middleware(['auth', 'role:visitor'])
    ->prefix('visitor')
    ->name('visitor.')
    ->group(function () {
        Route::get('/dashboard', [VisitorDashboardController::class, 'index'])->name('dashboard');
    });
