# API de l'application mobile — v1

Adresse de base : `https://<domaine>/api/v1` (en local : `https://tresorsivoire.test/api/v1` ;
depuis l'émulateur Android : `http://10.0.2.2/api/v1`).

## Règles communes

| Sujet | Règle |
|---|---|
| Format | Toujours du JSON. Envoyer `Accept: application/json` (l'API le force de toute façon). |
| Langue | En-tête `Accept-Language: fr` ou `en` (français par défaut). La réponse renvoie `Content-Language`. |
| Connexion | Jeton Sanctum : en-tête `Authorization: Bearer <token>`. Un jeton par appareil, valable `MOBILE_TOKEN_TTL_DAYS` jours (90 par défaut). |
| Listes | Paginées : `data`, `links`, `meta` (`current_page`, `last_page`, `per_page`, `total`). Paramètres `page`, `per_page` (max 50). |
| Fiches | `{ "data": { … } }`. |
| Dates | ISO 8601 (`2026-11-02T20:00:00+00:00`, heure d'Abidjan = UTC). |
| Prix | Nombre en francs CFA, `currency: "XOF"`. |
| Images | URL absolues. |
| Limites | 120 requêtes/min par compte (ou adresse IP) ; 10/min pour connexion, inscription, codes. |

## Erreurs

Toujours `{ "message": "…" }`, message traduit, affichable tel quel.

| Code | Cas |
|---|---|
| 401 | Jeton absent, invalide ou expiré → renvoyer vers l'écran de connexion |
| 403 | Compte désactivé, compte administrateur, accès refusé |
| 404 | Fiche introuvable ou non publiée |
| 422 | Données invalides : `errors` = `{ "champ": ["message"] }` |
| 429 | Trop de requêtes (en-tête `Retry-After`) |
| 503 | Service indisponible (ex. envoi d'e-mail en panne) |

## Points d'accès

### Démarrage
| Méthode | Adresse | Description |
|---|---|---|
| GET | `/meta` | Version minimale de l'application (mise à jour obligatoire en dessous), maintenance, contacts, réseaux sociaux, pages légales |

### Compte (`/auth`)
| Méthode | Adresse | Connexion | Corps / description |
|---|---|---|---|
| POST | `/auth/login` | — | `email`, `password`, `device_name` → `token`, `token_type`, `expires_at`, `user` |
| POST | `/auth/register` | — | `first_name`, `last_name`, `email`, `phone?`, `password`, `password_confirmation`, `terms`, `newsletter_opt_in?`, `device_name` → 201 + jeton ; code envoyé par e-mail |
| POST | `/auth/password/forgot` | — | `email` → lien de réinitialisation (page du site) |
| GET | `/auth/me` | oui | Profil |
| POST | `/auth/logout` | oui | Supprime le jeton de cet appareil |
| POST | `/auth/email/resend` | oui | Renvoie le code à 6 chiffres |
| POST | `/auth/email/verify` | oui | `code` → `user` avec `email_verified: true` |

Inscription : comptes voyageurs uniquement ; les prestataires s'inscrivent sur le site (abonnement).
Les administrateurs ne se connectent pas à l'application (double authentification sur le site).

### Événements
| Méthode | Adresse | Description |
|---|---|---|
| GET | `/events` | Liste : `periode` = `upcoming` (défaut : à venir **et en cours**) \| `past` \| `all`, `categorie` (slug), `ville`, `q` |
| GET | `/events/{slug}` | Fiche : description HTML, lieu et coordonnées GPS, organisateur, programme, photos, vidéos, `web_url` (partage) |
| GET | `/event-categories` | Catégories avec `events_count` (à venir ou en cours) |

## Ajouter un module

1. Routes : `routes/api/v1/<module>.php` (chargé automatiquement).
2. Contrôleurs : `app/Http/Controllers/Api/V1/<Module>/`.
3. Format JSON : `app/Http/Resources/Api/V1/` (version courte pour les listes, `…DetailResource` pour la fiche).
4. Validation : `app/Http/Requests/Api/V1/<Module>/`.
5. Logique : réutiliser les modèles, scopes et services du site — jamais de règle métier recopiée.
6. Tests : `tests/Feature/Api/V1/<Module>ApiTest.php`.
7. Documenter ici.

Une modification incompatible (champ supprimé ou renommé, sens changé) va dans une **v2** ;
ajouter un champ reste possible en v1.
