<div align="center">

<img src="bitchest-frontend/src/assets/bitchest_logo.png" alt="Logo BitChest" width="420" />

# BitChest — Plateforme de simulation d'achat et de vente de cryptomonnaies

**Projet de fin de formation — Développeur Web & Web Mobile**

Application web complète (SPA + API REST + micro-service d'assistance IA) permettant à des
utilisateurs d'acheter, de conserver et de revendre des cryptomonnaies avec un portefeuille
virtuel en euros, et à des administrateurs de piloter la plateforme.

![Vue d'ensemble de l'application BitChest sur ordinateur et mobile](Readme.png)

</div>

---

## Table des matières

1. [Contexte et objectifs pédagogiques](#1-contexte-et-objectifs-pédagogiques)
2. [Cahier des charges fonctionnel](#2-cahier-des-charges-fonctionnel)
3. [Architecture générale](#3-architecture-générale)
4. [Pile technologique](#4-pile-technologique)
5. [Organisation du dépôt (monorepo)](#5-organisation-du-dépôt-monorepo)
6. [Modèle de données](#6-modèle-de-données)
7. [API REST](#7-api-rest)
8. [Assistant de support IA](#8-assistant-de-support-ia)
9. [Sécurité](#9-sécurité)
10. [Installation et démarrage](#10-installation-et-démarrage)
11. [Comptes de démonstration](#11-comptes-de-démonstration)
12. [Tâches planifiées](#12-tâches-planifiées)
13. [Tests](#13-tests)
14. [Choix techniques et justifications](#14-choix-techniques-et-justifications)
15. [Limites connues et perspectives](#15-limites-connues-et-perspectives)
16. [Auteur](#16-auteur)

---

## 1. Contexte et objectifs pédagogiques

BitChest est une plateforme fictive de courtage en cryptomonnaies. Le sujet impose de gérer
**dix cryptomonnaies** (Bitcoin, Ethereum, Ripple, Bitcoin Cash, Cardano, Litecoin, NEM,
Stellar, IOTA, Dash), un **portefeuille virtuel en euros** crédité à l'inscription, un
**historique de cotations** et deux profils d'utilisateurs distincts (client et
administrateur).

Le projet vise à démontrer la maîtrise des compétences suivantes :

| Domaine | Mise en œuvre dans BitChest |
|---|---|
| Conception d'une base de données relationnelle | Schéma normalisé, migrations versionnées, index composites orientés requêtes |
| Développement d'une API REST sécurisée | Laravel 10 + Sanctum, séparation des rôles par middleware |
| Développement d'un front-end monopage (SPA) | Vue 3 + TypeScript, Pinia, Vue Router, gardes de navigation |
| Consommation d'une API tierce | Récupération des cours via l'API publique Coinbase avec cache et repli |
| Architecture orientée services | Logique métier isolée dans des classes `Service` testables |
| Intégration d'un service externe (IA) | Micro-service FastAPI d'assistance, appelé en proxy par le backend |
| Qualité logicielle | Tests unitaires PHPUnit, typage strict TypeScript, ESLint, Laravel Pint |

---

## 2. Cahier des charges fonctionnel

### 2.1 Visiteur (non authentifié)

- Page d'accueil de présentation (landing) avec cours du marché en temps quasi réel.
- Inscription (`/signup`) et connexion (`/signin`).

### 2.2 Client authentifié

- **Tableau de bord** : solde en euros, valeur totale du portefeuille, plus/moins-value
  latente, nombre de transactions, répartition des avoirs.
- **Marché** : liste des dix cryptomonnaies, cours actuel, variation sur 24 h, graphique
  d'historique (1 j / 7 j / 30 j / 90 j) en chandeliers japonais.
- **Achat / vente** : acquisition d'une quantité de crypto contre des euros (débit du solde),
  revente au cours courant (crédit du solde). Contrôle de solvabilité et de quantité détenue.
- **Portefeuille** : détail par crypto, prix de revient moyen, quantité, valorisation, détail
  des lots d'achat.
- **Historique** : liste paginée de toutes les transactions du client.
- **Profil** : informations personnelles, photo de profil et bannière, changement de mot de
  passe avec contrôle de robustesse.
- **Support** : widget de discussion et page dédiée, assistés par une IA bilingue FR/EN.
- **Notifications** : alertes de portefeuille (variations significatives), montée de niveau,
  validation de compte.

### 2.3 Administrateur

- **Tableau de bord** : nombre de clients, comptes actifs / en attente de validation, somme
  des soldes, chiffre d'affaires (ventes), volume de transactions, séries temporelles,
  activités récentes, répartition des cryptos détenues.
- **Gestion des clients** : CRUD complet, validation (`approve`) ou blocage (`block`) d'un
  compte, création d'un client avec mot de passe provisoire (obligation de changement à la
  première connexion).
- **Gestion du marché** : consultation de l'historique des cours, génération de cotations,
  **prévisualisation** d'une mise à jour des prix (lecture seule) puis **approbation**
  explicite avant persistance.
- **Transactions** : consultation de l'historique global de la plateforme.

### 2.4 Cycle de vie d'un compte

```
pending  ──► pending_validation ──► active ──► blocked
   │                                   ▲          │
   └── création par inscription        └──────────┘
       ou par l'administrateur          (approve / block par l'admin)
```

Un compte non `active` ne peut pas accéder aux routes protégées (middleware
`EnsureAccountStatus`). Un compte marqué `must_change_password` est redirigé vers
`/change-password` tant qu'il n'a pas défini un nouveau mot de passe.

---

## 3. Architecture générale

BitChest suit une architecture **découplée en trois services** communiquant par HTTP :

```
                          ┌─────────────────────────────────────────┐
                          │   API publique Coinbase (cours crypto)   │
                          └───────────────────┬─────────────────────┘
                                              │  HTTPS (cache + repli BDD)
                                              ▼
┌──────────────────┐   REST/JSON   ┌───────────────────────┐   SQL   ┌──────────────┐
│  bitchest-       │  Bearer token │  bitchest-backend     │────────►│   MySQL      │
│  frontend        │◄─────────────►│  Laravel 10 + Sanctum │         │  bd_bitchest │
│  Vue 3 / TS SPA  │               │  API REST + Scheduler │◄────────│              │
└──────────────────┘               └───────────┬───────────┘   SQL   └──────────────┘
                                               │ proxy authentifié        ▲
                                               ▼                          │ SELECT lecture seule
                                   ┌───────────────────────┐              │
                                   │  bot (support IA)     │──────────────┘
                                   │  FastAPI + Groq API   │
                                   └───────────────────────┘
```

Points structurants :

- **Le frontend ne parle jamais directement au bot ni à la base.** Toute donnée transite par
  l'API Laravel, qui applique l'authentification et le contrôle des rôles.
- **Le bot n'est jamais exposé au navigateur.** Laravel agit en proxy
  (`POST /api/support/chat`) et substitue systématiquement l'`user_id` de la session
  authentifiée : un client ne peut pas demander les données d'un autre utilisateur.
- **Les cours des cryptos** sont récupérés depuis Coinbase, mis en cache, et repliés sur la
  base de données en cas d'indisponibilité de l'API tierce.
- **Redis** (optionnel, via `docker-compose`) accélère la diffusion des prix ; en son absence
  le cache de fichiers Laravel prend le relais.

---

## 4. Pile technologique

### Backend — `bitchest-backend/`

| Composant | Version | Rôle |
|---|---|---|
| PHP | 8.1+ | Langage |
| Laravel | 10.x | Framework API |
| Laravel Sanctum | 3.x | Authentification par jetons (Bearer) |
| MySQL / MariaDB | 5.7+ / 10.x | Persistance (`bd_bitchest`) |
| Guzzle | 7.x | Client HTTP (API Coinbase) |
| Predis | 3.x | Client Redis (cache de prix, optionnel) |
| PHPUnit | 10.x | Tests |
| Laravel Pint | 1.x | Formatage PSR-12 |

### Frontend — `bitchest-frontend/`

| Composant | Version | Rôle |
|---|---|---|
| Vue | 3.5 | Framework SPA |
| TypeScript | 5.9 | Typage statique |
| Vite | 5.4 | Bundler / serveur de dev |
| Pinia | 2.2 | Gestion d'état (store `auth`) |
| Vue Router | 4.6 | Routage + gardes de navigation |
| Axios | 1.7 | Client HTTP |
| ApexCharts | 5.3 | Graphiques de cours (chandeliers) |
| Tailwind CSS | 3.4 | Styles utilitaires |
| Three.js | 0.161 | Arrière-plans animés de la landing |
| Lucide | — | Jeu d'icônes |

### Micro-service de support — `bot/`

| Composant | Version | Rôle |
|---|---|---|
| Python | 3.12 | Langage |
| FastAPI | 0.111 | Serveur HTTP du bot |
| Uvicorn | 0.30 | Serveur ASGI |
| httpx | 0.27 | Appels vers l'API Groq |
| mysql-connector-python | 9.0 | Lecture seule de la base BitChest |
| Groq API | `openai/gpt-oss-120b` | Modèle de langage |

---

## 5. Organisation du dépôt (monorepo)

```
Bitchest_Full/
├── bitchest-backend/            API REST Laravel 10
│   ├── app/
│   │   ├── Console/Commands/     Commandes Artisan (mise à jour des prix, notifications…)
│   │   ├── Http/
│   │   │   ├── Controllers/
│   │   │   │   ├── Admin/        UserController, CryptoController, DashboardController, TransactionController
│   │   │   │   ├── Client/       Portfolio, Transaction, CryptoMarket, Profile, Notification
│   │   │   │   ├── AuthController.php
│   │   │   │   └── SupportChatController.php   Proxy vers le micro-service bot
│   │   │   └── Middleware/       CheckUserRole, EnsureAccountStatus, …
│   │   ├── Models/               User, CryptoCurrency, CryptoPriceRecord, Portfolio, Transaction, Notification
│   │   └── Services/             Logique métier isolée et testable
│   │       ├── CoinbaseAPIService.php          Consommation de l'API tierce
│   │       ├── CryptoService.php               Cours courants + agrégations
│   │       ├── CryptoPriceUpdateService.php    Mise à jour verrouillée (cache lock)
│   │       ├── CotationGeneratorService.php    Génération de l'historique de cotations
│   │       ├── PortfolioService.php / TransactionService.php
│   │       ├── LevelService.php                Gamification (niveaux / XP)
│   │       ├── NotificationService.php
│   │       └── RedisPriceService.php
│   ├── database/
│   │   ├── migrations/           7 migrations versionnées
│   │   └── seeders/              Admin, clients, cryptos, portefeuilles, transactions de démo
│   ├── routes/api.php            Déclaration de toutes les routes de l'API
│   ├── tests/                    PHPUnit (Unit + Feature)
│   ├── docker-compose.yml        Service Redis
│   └── BitChest_API.postman_collection.json   Collection Postman de l'API
│
├── bitchest-frontend/           SPA Vue 3 + TypeScript
│   └── src/
│       ├── pages/               Landing, Dashboard, Trade, Portfolio, Profile, Support, Sign-in/up…
│       ├── admin/               Layout, sidebar et pages du back-office
│       ├── components/          Navbar, graphique de trading, notifications, widget de support, sections landing
│       ├── stores/auth.ts       État d'authentification (Pinia)
│       ├── services/api.ts      Couche d'accès à l'API + cache client
│       ├── router/index.ts      Routes + gardes (auth, rôle, statut de compte)
│       └── assets/              Logo, icônes des dix cryptos, visuels
│
└── bot/                         Micro-service d'assistance IA (FastAPI)
    ├── bot_server.py            Points d'entrée /health et /chat
    ├── app/
    │   ├── prompts.py           Règles de comportement centralisées (langue, périmètre, confidentialité)
    │   └── services/user_service.py   SELECT lecture seule filtrés par user_id
    ├── requirements.txt
    └── serve.ps1                Démarrage local (port 8001)
```

---

## 6. Modèle de données

Sept tables, migrations versionnées et commentées, index composites choisis en fonction des
requêtes réelles (calcul de quantité détenue, historique trié, filtrage par rôle/statut).

### 6.1 Schéma relationnel

```
users 1 ───< portfolios >─── 1 crypto_currencies
                  │ 1                    │ 1
                  │                      │
                  ˅ n                    ˅ n
             transactions        crypto_price_records

users 1 ───< notifications
```

### 6.2 Description des tables

| Table | Colonnes principales | Rôle |
|---|---|---|
| `users` | `name`, `first_name`, `last_name`, `email` (unique), `password` (bcrypt), `role` (`admin`/`client`), `status` (`pending`/`pending_validation`/`active`/`blocked`), `euro_balance` (decimal 12,2), `level`, `experience_points`, `must_change_password`, `profile_picture`, `profile_banner` | Comptes et solde virtuel |
| `crypto_currencies` | `name`, `symbol`, prix courant, variation 24 h | Les dix cryptomonnaies gérées |
| `portfolios` | `user_id`, `crypto_currency_id` | Ligne d'avoir : lien entre un utilisateur et une crypto |
| `transactions` | `portfolio_id`, `type` (`buy`/`sell`), `quantity` (decimal 18,8), `price_at_transaction` (decimal 18,8), `euro_amount` (decimal 18,2) | Journal des achats et ventes ; la quantité détenue = Σ achats − Σ ventes |
| `crypto_price_records` | `crypto_currency_id`, prix, horodatage | Historique des cotations (graphiques) |
| `notifications` | `user_id`, type, contenu, état lu/non lu | Notifications applicatives |
| `personal_access_tokens` | — | Jetons Sanctum |

> **Précision financière** : tous les montants monétaires et quantités sont stockés en
> `DECIMAL` (jamais en flottant) afin d'éviter les erreurs d'arrondi ; les euros à
> 2 décimales, les quantités de crypto à 8 décimales.

---

## 7. API REST

Base : `http://localhost:8000/api` — Authentification : `Authorization: Bearer <token>` (Sanctum).
Une collection Postman prête à l'emploi est fournie :
[`bitchest-backend/BitChest_API.postman_collection.json`](bitchest-backend/BitChest_API.postman_collection.json).

### 7.1 Routes publiques

| Méthode | Route | Description |
|---|---|---|
| `POST` | `/register` | Inscription d'un client |
| `POST` | `/login` | Connexion, renvoie le jeton Bearer |
| `GET` | `/public/market` | Cours du marché pour la landing (sans authentification) |

### 7.2 Routes authentifiées (client ou admin)

| Méthode | Route | Description |
|---|---|---|
| `GET` | `/user` | Utilisateur courant + progression de niveau |
| `POST` | `/change-password` | Changement de mot de passe (contrôle de robustesse) |
| `POST` | `/logout` | Révocation du jeton |
| `POST` | `/support/chat` | Proxy authentifié vers l'assistant IA |
| `GET` | `/notifications`, `/notifications/unread-count` | Liste et compteur |
| `POST` | `/notifications/{id}/read`, `/notifications/read-all` | Marquage comme lu |
| `DELETE` | `/notifications/{id}` | Suppression |

### 7.3 Routes client (`role:client`)

| Méthode | Route | Description |
|---|---|---|
| `GET` | `/portfolio` | Portefeuille consolidé |
| `GET` | `/portfolio/crypto/{id}/purchases` | Détail des lots d'achat d'une crypto |
| `POST` | `/transaction/buy` | Achat de crypto |
| `POST` | `/transaction/sell` | Vente de crypto |
| `GET` | `/transaction/history` | Historique des transactions du client |
| `GET` | `/market` | Marché (dix cryptos, cours, variation) |
| `GET` | `/market/history/{crypto_currency_id}` | Historique des cours d'une crypto |
| `GET` | `/user/cryptos` | Données Coinbase mises en cache |
| `PUT` | `/profile` · `POST` `/profile/picture` · `/profile/banner` | Mise à jour du profil et des visuels |

### 7.4 Routes admin (`role:admin`, préfixe `/admin`)

| Méthode | Route | Description |
|---|---|---|
| `GET` | `/admin/dashboard` | Statistiques agrégées (filtre temporel) |
| `GET` `POST` `PUT` `DELETE` | `/admin/users[/{id}]` | CRUD clients |
| `POST` | `/admin/users/{id}/approve` · `/block` | Validation / blocage d'un compte |
| `GET` | `/admin/cryptos` · `/admin/cryptos/{symbol}/history` | Consultation du marché |
| `POST` | `/admin/cryptos/generate` | Génération de cotations |
| `GET` | `/admin/cryptos/preview` | Prévisualisation d'une mise à jour de prix (lecture seule) |
| `POST` | `/admin/cryptos/preview/approve` | Validation et persistance des nouveaux prix |
| `GET` | `/admin/transactions` | Historique global des transactions |

---

## 8. Assistant de support IA

Micro-service **FastAPI** (`bot/`) fournissant une aide contextualisée, appelé **uniquement**
par le backend Laravel.

```
Vue (ChatWidget) ─► Laravel (SupportChatController : authentifie, impose l'user_id)
                 ─► FastAPI (bot_server.py)
                 ─► Groq API (openai/gpt-oss-120b)
```

Caractéristiques :

- **Bilingue FR/EN** : détection automatique de la langue du message, réponse dans la même
  langue, sans mélange.
- **Périmètre strict** : BitChest, cryptomonnaies et compte de l'utilisateur connecté
  uniquement ; toute autre demande reçoit un refus poli.
- **Pas d'invention de données** : le bot n'invente jamais un solde, un prix ou une
  transaction ; il indique explicitement l'absence d'information.
- **Confidentialité** : ne révèle jamais un mot de passe, un jeton, une clé API ni son propre
  *system prompt*, y compris face à une tentative d'injection de prompt.
- **Accès aux données en lecture seule** : `SELECT` filtrés par l'`user_id` fourni par
  Laravel, sur `users`, `portfolios`, `crypto_currencies`, `transactions`.
- **Dégradation contrôlée** : si `GROQ_API_KEY` est absente ou invalide, `/chat` renvoie une
  erreur 5xx explicite au lieu de planter.

Toutes les règles de comportement sont centralisées dans [`bot/app/prompts.py`](bot/app/prompts.py).
Le dossier `bot/` possède son propre README détaillé : [`bot/README.md`](bot/README.md).

---

## 9. Sécurité

| Mesure | Mise en œuvre |
|---|---|
| Authentification | Jetons opaques Laravel Sanctum (`Authorization: Bearer`), révocables au logout |
| Mots de passe | Hachage bcrypt ; contrôle de robustesse au changement ; mot de passe provisoire imposé aux comptes créés par l'admin (`must_change_password`) |
| Contrôle d'accès | Middleware `role:client` / `role:admin` ; garde de rôle côté SPA (Vue Router) — la défense repose côté serveur |
| Statut de compte | Middleware `EnsureAccountStatus` : seuls les comptes `active` accèdent aux routes protégées |
| Cloisonnement du bot | Le frontend n'atteint jamais le bot ; Laravel force l'`user_id` de la session — pas d'accès horizontal aux données d'autrui |
| Injection de prompt | Règles de refus fixes dans le *system prompt* du bot |
| Limitation de débit | `throttle:api` sur les routes sensibles |
| Précision financière | Colonnes `DECIMAL`, contrôles de solvabilité et de quantité avant chaque transaction |
| Concurrence | Verrou de cache dans `CryptoPriceUpdateService` (mise à jour planifiée vs. clic « Approve ») |
| Secrets | `.env` exclu du versionnement ; seuls les fichiers `.env.example` sont suivis |

---

## 10. Installation et démarrage

### 10.1 Prérequis

- PHP **8.1+**, Composer
- Node.js **18+** et npm
- MySQL / MariaDB (via XAMPP par exemple)
- Python **3.12** (pour le micro-service de support, optionnel)
- (Optionnel) Docker, pour Redis

### 10.2 Backend — API Laravel

```bash
cd bitchest-backend
composer install
cp .env.example .env
php artisan key:generate
```

Renseigner la connexion à la base dans `.env` :

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=bd_bitchest
DB_USERNAME=root
DB_PASSWORD=
```

Créer la base puis exécuter les migrations et les seeders :

```bash
php artisan migrate --seed
php artisan storage:link          # accès public aux images de profil
php artisan serve                 # http://localhost:8000
```

(Optionnel) Redis pour le cache de prix :

```bash
docker compose up -d              # service redis sur le port 6379
```

(Optionnel) Planificateur de tâches en local :

```bash
php artisan schedule:work
```

### 10.3 Frontend — SPA Vue

```bash
cd bitchest-frontend
npm install
```

Fichier `.env.local` :

```env
VITE_API_BASE_URL=http://localhost:8000
VITE_BOT_API_URL=http://localhost:8000/api/support
```

```bash
npm run dev                       # http://localhost:5173
npm run build                     # build de production (dist/)
```

### 10.4 Micro-service de support (optionnel)

```bash
cd bot
py -3.12 -m venv venv
.\venv\Scripts\pip install -r requirements.txt
cp .env.example .env              # renseigner GROQ_API_KEY
.\serve.ps1                       # http://127.0.0.1:8001  (vérifier GET /health)
```

> Sans ce service, l'application reste pleinement fonctionnelle ; seule la page de support
> renvoie une erreur contrôlée.

---

## 11. Comptes de démonstration

Créés par les seeders (`php artisan migrate --seed`) :

| Rôle | Email | Mot de passe | Remarques |
|---|---|---|---|
| Administrateur | `admin@bitchest.com` | `admin123` | Accès complet au back-office |
| Client | voir `database/seeders/UserSeeder.php` | — | Chaque client est crédité de **500 €** virtuels |

> Les identifiants de démonstration sont destinés à un environnement local uniquement.

---

## 12. Tâches planifiées

Définies dans `app/Console/Kernel.php` :

| Fréquence | Tâche | Effet |
|---|---|---|
| Quotidienne (00 h 00) | `crypto:update-prices` | Récupère les cours depuis Coinbase et met à jour `crypto_currencies` (verrou de cache) |
| Horaire | `CotationGeneratorService::generateDaily()` | Alimente `crypto_price_records` pour les graphiques d'historique |
| Toutes les 5 min | `notifications:check-portfolio` | Génère les alertes de portefeuille (`withoutOverlapping`) |

---

## 13. Tests

```bash
cd bitchest-backend
php artisan test                  # ou: ./vendor/bin/phpunit
```

Couverture actuelle (dossier `tests/`) :

| Suite | Contenu |
|---|---|
| `Unit/PortfolioServiceTest` | Calcul de la quantité détenue, valorisation, prix de revient |
| `Unit/TransactionServiceTest` | Règles d'achat / vente, contrôle de solvabilité et de quantité |
| `Unit/ModelTest` | Relations Eloquent et accesseurs |
| `Unit/FormRequestTest` | Règles de validation des requêtes |

Qualité de code :

```bash
./vendor/bin/pint               # formatage PSR-12 (backend)
cd ../bitchest-frontend && npx eslint src && npm run build   # lint + vérification de types
```

---

## 14. Choix techniques et justifications

| Décision | Justification |
|---|---|
| **API REST découplée + SPA** plutôt qu'application Blade monolithique | Séparation nette des responsabilités, réutilisabilité de l'API (mobile, Postman), expérience utilisateur fluide |
| **Sanctum en mode jeton** (et non cookies/session) | SPA et API sur des origines distinctes en développement ; authentification stateless simple à raisonner ; pas de CSRF sur l'API |
| **Logique métier dans des `Service`** | Contrôleurs minces, code testable unitairement, réutilisable entre routes web / commandes Artisan / planificateur |
| **`DECIMAL` pour la monnaie** | Élimination des erreurs d'arrondi propres aux flottants dans un contexte financier |
| **Prévisualisation puis approbation des prix (admin)** | Le sujet exige un contrôle humain avant modification du marché ; séparation lecture / écriture |
| **Cache + repli BDD pour les cours** | Résilience face à l'indisponibilité ou au *rate limiting* de l'API Coinbase |
| **Bot en micro-service séparé** | Isolation de la clé Groq, montée en charge indépendante, langage adapté (Python) sans polluer le backend PHP |
| **Proxy Laravel imposant l'`user_id`** | Empêche tout accès horizontal : le client ne choisit jamais l'identité interrogée |
| **Redis optionnel** | Performance en production sans imposer une dépendance lourde en développement |
| **TypeScript strict + Pinia** | Fiabilité du front, état d'authentification centralisé et prévisible |

---

## 15. Limites connues et perspectives

- **Cours simulés / dépendants d'une API publique** : pas de flux temps réel WebSocket ;
  rafraîchissement par cache et planificateur.
- **Couverture de tests** concentrée sur la logique métier critique (portefeuille,
  transactions) ; les contrôleurs admin gagneraient à être couverts par des tests *feature*.
- **Pas d'envoi réel d'e-mails** garanti hors configuration SMTP valide.
- **Pistes d'amélioration** : pagination et tri avancés côté admin, export CSV de
  l'historique, mise en place d'une CI (GitHub Actions) exécutant Pint + PHPUnit + ESLint,
  conteneurisation complète (`Dockerfile` backend et frontend), internationalisation de
  l'interface.

---

## 16. Auteur

Projet réalisé par **Shedly Laadhiby** dans le cadre de la formation
**Développeur Web & Web Mobile**.

- Backend : Laravel 10 · API REST · Sanctum · MySQL
- Frontend : Vue 3 · TypeScript · Vite · Tailwind CSS
- Micro-service IA : FastAPI · Groq

<div align="center">

<img src="bitchest-frontend/src/assets/bitchest_logo.png" alt="BitChest" width="260" />

</div>
