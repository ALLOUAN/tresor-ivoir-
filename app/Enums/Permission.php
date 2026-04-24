<?php

namespace App\Enums;

enum Permission: string
{
    // ── Articles ──────────────────────────────────────────────────────────
    case ArticlesView = 'articles.view';
    case ArticlesCreate = 'articles.create';
    case ArticlesEditOwn = 'articles.edit-own';
    case ArticlesEditAny = 'articles.edit-any';
    case ArticlesDeleteOwn = 'articles.delete-own';
    case ArticlesDeleteAny = 'articles.delete-any';
    case ArticlesPublish = 'articles.publish';
    case ArticlesReview = 'articles.review';

    // ── Événements ────────────────────────────────────────────────────────
    case EventsView = 'events.view';
    case EventsCreate = 'events.create';
    case EventsEditOwn = 'events.edit-own';
    case EventsEditAny = 'events.edit-any';
    case EventsDeleteAny = 'events.delete-any';
    case EventsPublish = 'events.publish';

    // ── Prestataires ──────────────────────────────────────────────────────
    case ProvidersView = 'providers.view';
    case ProvidersCreate = 'providers.create';
    case ProvidersEditOwn = 'providers.edit-own';
    case ProvidersEditAny = 'providers.edit-any';
    case ProvidersApprove = 'providers.approve';
    case ProvidersDelete = 'providers.delete';

    // ── Utilisateurs ──────────────────────────────────────────────────────
    case UsersView = 'users.view';
    case UsersCreate = 'users.create';
    case UsersEdit = 'users.edit';
    case UsersDelete = 'users.delete';
    case UsersImpersonate = 'users.impersonate';

    // ── Avis ──────────────────────────────────────────────────────────────
    case ReviewsView = 'reviews.view';
    case ReviewsCreate = 'reviews.create';
    case ReviewsModerate = 'reviews.moderate';
    case ReviewsDeleteOwn = 'reviews.delete-own';
    case ReviewsDeleteAny = 'reviews.delete-any';

    // ── Paiements & Abonnements ───────────────────────────────────────────
    case PaymentsViewOwn = 'payments.view-own';
    case PaymentsViewAll = 'payments.view-all';
    case SubscriptionsManageOwn = 'subscriptions.manage-own';
    case SubscriptionsManageAll = 'subscriptions.manage-all';
    case InvoicesViewOwn = 'invoices.view-own';
    case InvoicesViewAll = 'invoices.view-all';

    // ── Newsletter ────────────────────────────────────────────────────────
    case NewsletterSubscribe = 'newsletter.subscribe';
    case NewsletterManage = 'newsletter.manage';

    // ── Médias ────────────────────────────────────────────────────────────
    case MediaUploadOwn = 'media.upload-own';
    case MediaDeleteOwn = 'media.delete-own';
    case MediaManageAll = 'media.manage-all';

    // ── Administration ────────────────────────────────────────────────────
    case AdminDashboard = 'admin.dashboard';
    case AdminSettings = 'admin.settings';
    case PermissionsView = 'permissions.view';

    // ── Helpers ───────────────────────────────────────────────────────────

    public function label(): string
    {
        return match ($this) {
            self::ArticlesView => 'Voir les articles',
            self::ArticlesCreate => 'Créer un article',
            self::ArticlesEditOwn => 'Modifier ses articles',
            self::ArticlesEditAny => 'Modifier tout article',
            self::ArticlesDeleteOwn => 'Supprimer ses articles',
            self::ArticlesDeleteAny => 'Supprimer tout article',
            self::ArticlesPublish => 'Publier un article',
            self::ArticlesReview => 'Réviser les articles',
            self::EventsView => 'Voir les événements',
            self::EventsCreate => 'Créer un événement',
            self::EventsEditOwn => 'Modifier ses événements',
            self::EventsEditAny => 'Modifier tout événement',
            self::EventsDeleteAny => 'Supprimer tout événement',
            self::EventsPublish => 'Publier un événement',
            self::ProvidersView => 'Voir les prestataires',
            self::ProvidersCreate => 'Créer une fiche prestataire',
            self::ProvidersEditOwn => 'Modifier sa fiche',
            self::ProvidersEditAny => 'Modifier toute fiche',
            self::ProvidersApprove => 'Approuver un prestataire',
            self::ProvidersDelete => 'Supprimer un prestataire',
            self::UsersView => 'Voir les utilisateurs',
            self::UsersCreate => 'Créer un utilisateur',
            self::UsersEdit => 'Modifier un utilisateur',
            self::UsersDelete => 'Supprimer un utilisateur',
            self::UsersImpersonate => 'Emprunter une identité',
            self::ReviewsView => 'Voir les avis',
            self::ReviewsCreate => 'Rédiger un avis',
            self::ReviewsModerate => 'Modérer les avis',
            self::ReviewsDeleteOwn => 'Supprimer son avis',
            self::ReviewsDeleteAny => 'Supprimer tout avis',
            self::PaymentsViewOwn => 'Voir ses paiements',
            self::PaymentsViewAll => 'Voir tous les paiements',
            self::SubscriptionsManageOwn => 'Gérer son abonnement',
            self::SubscriptionsManageAll => 'Gérer tous les abonnements',
            self::InvoicesViewOwn => 'Voir ses factures',
            self::InvoicesViewAll => 'Voir toutes les factures',
            self::NewsletterSubscribe => 'S\'abonner à la newsletter',
            self::NewsletterManage => 'Gérer la newsletter',
            self::MediaUploadOwn => 'Uploader des médias',
            self::MediaDeleteOwn => 'Supprimer ses médias',
            self::MediaManageAll => 'Gérer tous les médias',
            self::AdminDashboard => 'Accéder au dashboard admin',
            self::AdminSettings => 'Modifier les paramètres',
            self::PermissionsView => 'Voir la matrice des permissions',
        };
    }

    public function group(): string
    {
        return match (true) {
            str_starts_with($this->value, 'articles') => 'Articles',
            str_starts_with($this->value, 'events') => 'Événements',
            str_starts_with($this->value, 'providers') => 'Prestataires',
            str_starts_with($this->value, 'users') => 'Utilisateurs',
            str_starts_with($this->value, 'reviews') => 'Avis',
            str_starts_with($this->value, 'payments') => 'Paiements',
            str_starts_with($this->value, 'subscriptions') => 'Abonnements',
            str_starts_with($this->value, 'invoices') => 'Factures',
            str_starts_with($this->value, 'newsletter') => 'Newsletter',
            str_starts_with($this->value, 'media') => 'Médias',
            default => 'Administration',
        };
    }
}
