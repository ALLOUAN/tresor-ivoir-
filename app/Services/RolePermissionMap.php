<?php

namespace App\Services;

use App\Enums\Permission;

class RolePermissionMap
{
    private static array $map = [];

    public static function get(): array
    {
        if (! empty(self::$map)) {
            return self::$map;
        }

        $all = array_column(Permission::cases(), 'value');

        self::$map = [

            'admin' => $all,

            'editor' => [
                Permission::ArticlesView->value,
                Permission::ArticlesCreate->value,
                Permission::ArticlesEditOwn->value,
                Permission::ArticlesDeleteOwn->value,
                Permission::ArticlesPublish->value,
                Permission::ArticlesReview->value,
                Permission::EventsView->value,
                Permission::EventsCreate->value,
                Permission::EventsEditOwn->value,
                Permission::EventsPublish->value,
                Permission::ProvidersView->value,
                Permission::ReviewsView->value,
                Permission::MediaUploadOwn->value,
                Permission::MediaDeleteOwn->value,
                Permission::NewsletterSubscribe->value,
            ],

            'provider' => [
                Permission::ArticlesView->value,
                Permission::EventsView->value,
                Permission::ProvidersView->value,
                Permission::ProvidersCreate->value,
                Permission::ProvidersEditOwn->value,
                Permission::ReviewsView->value,
                Permission::PaymentsViewOwn->value,
                Permission::SubscriptionsManageOwn->value,
                Permission::InvoicesViewOwn->value,
                Permission::MediaUploadOwn->value,
                Permission::MediaDeleteOwn->value,
                Permission::NewsletterSubscribe->value,
            ],

            'visitor' => [
                Permission::ArticlesView->value,
                Permission::EventsView->value,
                Permission::ProvidersView->value,
                Permission::ReviewsView->value,
                Permission::ReviewsCreate->value,
                Permission::ReviewsDeleteOwn->value,
                Permission::NewsletterSubscribe->value,
            ],
        ];

        return self::$map;
    }

    public static function forRole(string $role): array
    {
        return self::get()[$role] ?? [];
    }

    public static function roleHas(string $role, string $permission): bool
    {
        return in_array($permission, self::forRole($role), true);
    }

    public static function roles(): array
    {
        return array_keys(self::get());
    }
}
