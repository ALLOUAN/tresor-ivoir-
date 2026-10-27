# Architecture — Trésors d'Ivoire

## Vue d'ensemble

| Projet | Dossier | Rôle |
|---|---|---|
| Site + API (Laravel 13) | `tresorsIvoire/` (ce dépôt) | Site public, espaces client / prestataire / éditeur / admin, API mobile `/api/v1` |
| Application mobile (Flutter) | `tresorsIvoire-app/` (dépôt séparé) | Application Android (puis iOS), ne parle au serveur que par l'API |

Le site et l'application partagent **la même logique métier** : modèles, scopes et services de `app/`.
L'API n'est qu'une autre « porte d'entrée » vers cette logique.

## Organisation du code Laravel

```
app/
  Http/
    Controllers/            site web (pages Blade)
    Controllers/Api/V1/     API mobile, un dossier par module (Auth, Events, …)
    Resources/Api/V1/       format JSON de l'API (liste + fiche détaillée)
    Requests/Api/V1/        validation des données envoyées par l'application
    Middleware/Api/         JSON forcé, langue du téléphone
  Exceptions/ApiExceptionRenderer.php   erreurs JSON de l'API
  Models/                   modèles + scopes partagés (ex. Event::forPeriod, ::notFinished, ::search)
  Services/                 logique partagée (ex. EmailVerificationCodeService, ClientAccountService)
routes/
  web.php                   site
  api.php                   charge routes/api/v1/*.php sous /api/v1
config/mobile.php           versions minimales de l'application, durée des jetons
docs/api/README.md          documentation de l'API
tests/Feature/Api/V1/       tests de l'API, un fichier par module
```

## Règles

1. **Pas de logique en double** : une règle métier (ex. « un événement en cours reste affiché ») vit dans le
   modèle ou un service, et le site comme l'API l'utilisent.
2. **API versionnée** : `/api/v1`. Ajouter un champ = permis ; supprimer / renommer / changer le sens = `v2`.
3. **Un module = routes + contrôleur + ressources + requêtes + tests + documentation.**
4. **Réponses** : JSON, messages traduits (fr/en), dates ISO 8601, URL absolues, prix en XOF.
5. **Sécurité** : jetons Sanctum par appareil avec expiration, limites de requêtes, comptes admin exclus de l'API.

## Configuration (.env)

| Variable | Défaut | Rôle |
|---|---|---|
| `MOBILE_ANDROID_MIN_VERSION` | 1.0.0 | En dessous, l'application impose une mise à jour |
| `MOBILE_ANDROID_LATEST_VERSION` | 1.0.0 | Dernière version publiée (mise à jour proposée) |
| `MOBILE_ANDROID_STORE_URL` | fiche Google Play | Lien du bouton « Mettre à jour » |
| `MOBILE_TOKEN_TTL_DAYS` | 90 | Durée d'un jeton de connexion |

## Déploiement d'une évolution de l'API

1. Envoyer les fichiers modifiés (jamais `bootstrap/cache/`, ni `.env`).
2. `composer install --no-dev` si `composer.json` a changé.
3. `php artisan migrate --force`
4. `php artisan config:clear && php artisan route:clear`
