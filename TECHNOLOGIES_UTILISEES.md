# TECHNOLOGIES UTILISÉES - BITCHEST

## 📋 RÉSUMÉ VISUEL

### Backend
```
PHP 8.1+ ──┐
           ├── Laravel 10.10 ──┐
Composer ──┘                   ├── Application Backend
                               │
Laravel Sanctum 3.3 ──────────┤
                               │
MySQL/MariaDB ────────────────┤
                               │
Redis (Predis 3.3) ───────────┤
                               │
Guzzle HTTP 7.2 ──────────────┘
```

### Frontend
```
Vue.js 3.5.26 ──┐
                ├── Application Frontend
TypeScript 5.9.3 ──┤
                │
Vite 5.4.21 ────┤
                │
Vue Router 4.6.3 ──┤
                │
Pinia 2.2.6 ────┤
                │
Tailwind CSS 3.4.18 ──┤
                │
ApexCharts 5.3.6 ────┘
```

---

## 🔧 STACK TECHNIQUE COMPLÈTE

### Langages
- **PHP 8.1+** : Backend
- **JavaScript/TypeScript 5.9.3** : Frontend
- **SQL** : Base de données

### Frameworks
- **Laravel 10.10** : Framework PHP MVC
- **Vue.js 3.5.26** : Framework JavaScript progressif

### Base de données
- **MySQL/MariaDB** : SGBD relationnel
- **Redis** : Cache en mémoire

### Outils de build
- **Composer** : Gestionnaire de dépendances PHP
- **NPM** : Gestionnaire de paquets Node.js
- **Vite 5.4.21** : Build tool frontend

### Authentification & Sécurité
- **Laravel Sanctum 3.3** : Authentification API
- **Hash (bcrypt)** : Hachage des mots de passe
- **Middleware Laravel** : Protection des routes

### UI/UX
- **Tailwind CSS 3.4.18** : Framework CSS utility-first
- **ApexCharts 5.3.6** : Graphiques et visualisations
- **Three.js 0.161.0** : Rendu 3D
- **Lucide Vue Next 0.553.0** : Bibliothèque d'icônes

### Animations
- **@motionone/vue 10.16.4** : Animations performantes
- **@vueuse/motion 3.0.3** : Hooks d'animation

### HTTP & API
- **Axios 1.7.7** : Client HTTP frontend
- **Guzzle HTTP 7.2** : Client HTTP backend
- **Coinbase API** : API externe pour prix crypto

### State Management
- **Pinia 2.2.6** : Gestion d'état Vue.js

### Routing
- **Vue Router 4.6.3** : Routing côté client

### Utilitaires
- **@vueuse/core 14.0.0** : Collection de composables Vue

### Tests
- **PHPUnit 10.1** : Tests unitaires PHP
- **Mockery 1.4.4** : Mocking pour tests

### Outils de développement
- **Laravel Pint 1.0** : Formateur de code PHP
- **ESLint 9.9.1** : Linter JavaScript/TypeScript
- **Laravel Tinker 2.8** : REPL Laravel

### Conteneurisation
- **Docker** : Conteneurisation (docker-compose.yml)
- **Laravel Sail 1.18** : Environnement Docker Laravel

### Tâches planifiées
- **Laravel Scheduler** : Tâches cron automatisées
- **Artisan Commands** : Commandes personnalisées

---

## 📊 MATRICE DES TECHNOLOGIES PAR FONCTIONNALITÉ

| Fonctionnalité | Backend | Frontend | Base de données | Cache |
|----------------|---------|----------|-----------------|-------|
| Authentification | Laravel Sanctum | Vue Router + Pinia | MySQL (users) | Redis (sessions) |
| API REST | Laravel Controllers | Axios | MySQL | Redis |
| Gestion d'état | Services Laravel | Pinia | - | Redis |
| UI/UX | Blade (emails) | Vue.js + Tailwind | - | - |
| Graphiques | - | ApexCharts | MySQL (historique) | Redis |
| 3D/Animations | - | Three.js + Motion | - | - |
| Tâches asynchrones | Laravel Queue | - | MySQL | Redis |
| Tests | PHPUnit | - | MySQL (test) | - |

---

## 🏗️ ARCHITECTURE TECHNIQUE

### Backend Architecture
```
┌─────────────────────────────────────────┐
│         HTTP Request                    │
└─────────────────────────────────────────┘
              │
              ▼
┌─────────────────────────────────────────┐
│      Laravel Middleware                  │
│  (Auth, Role, Account Status)           │
└─────────────────────────────────────────┘
              │
              ▼
┌─────────────────────────────────────────┐
│      Controllers (MVC)                  │
│  (Admin/Client Controllers)             │
└─────────────────────────────────────────┘
              │
              ▼
┌─────────────────────────────────────────┐
│      Services Layer                     │
│  (TransactionService, PortfolioService) │
└─────────────────────────────────────────┘
              │
        ┌─────┴─────┐
        │           │
        ▼           ▼
┌─────────────┐ ┌─────────────┐
│   Models    │ │    Redis    │
│  (Eloquent) │ │   (Cache)   │
└─────────────┘ └─────────────┘
        │
        ▼
┌─────────────┐
│   MySQL     │
│  Database   │
└─────────────┘
```

### Frontend Architecture
```
┌─────────────────────────────────────────┐
│         Vue.js Application              │
└─────────────────────────────────────────┘
              │
        ┌─────┴─────┐
        │           │
        ▼           ▼
┌─────────────┐ ┌─────────────┐
│   Router    │ │    Pinia     │
│ (Vue Router)│ │  (State Mgmt)│
└─────────────┘ └─────────────┘
        │           │
        └─────┬─────┘
              │
              ▼
┌─────────────────────────────────────────┐
│      Components & Pages                  │
│  (Vue Components + Tailwind CSS)        │
└─────────────────────────────────────────┘
              │
              ▼
┌─────────────────────────────────────────┐
│      API Service (Axios)                 │
└─────────────────────────────────────────┘
              │
              ▼
┌─────────────────────────────────────────┐
│      Laravel Backend API                │
└─────────────────────────────────────────┘
```

---

## 🔐 SÉCURITÉ

### Technologies de sécurité utilisées

#### Backend
- **Laravel Sanctum** : Tokens API sécurisés
- **Hash (bcrypt)** : Mots de passe hashés
- **Middleware** : Protection des routes
- **Form Requests** : Validation des entrées
- **Prepared Statements** : Protection SQL injection (Eloquent)
- **CSRF Protection** : Protection contre CSRF
- **Rate Limiting** : Limitation des requêtes

#### Frontend
- **Vue.js Auto-escaping** : Protection XSS
- **Input Validation** : Validation côté client
- **Secure Token Storage** : Stockage sécurisé des tokens
- **HTTPS** : Communication chiffrée

#### Base de données
- **Foreign Keys** : Intégrité référentielle
- **Constraints** : Contraintes d'intégrité
- **Transactions** : Atomicité des opérations
- **Row Locking** : Évite les race conditions

---

## 📈 PERFORMANCE

### Optimisations implémentées

1. **Cache Redis**
   - Cache des prix crypto
   - Cache des portfolios
   - Cache des notifications
   - Cache des transactions

2. **Optimisations base de données**
   - Index sur colonnes critiques
   - Requêtes optimisées
   - Lazy loading des relations
   - Pagination

3. **Optimisations frontend**
   - Code splitting (Vite)
   - Lazy loading des routes
   - Cache des données API
   - Optimisation des images

---

## 🧪 TESTS

### Outils de test

- **PHPUnit 10.1** : Tests unitaires backend
- **Mockery 1.4.4** : Mocking pour tests
- **Factories Eloquent** : Données de test
- **Seeders** : Peuplement de données de test

---

## 📦 DÉPLOIEMENT

### Environnements

- **Développement** : Docker Compose + Laravel Sail
- **Production** : Serveur web (Apache/Nginx) + PHP-FPM

### Scripts d'automatisation

- `start-redis.bat` : Démarrage Redis
- `init-redis-prices.bat` : Initialisation cache
- `update-crypto-prices.bat` : Mise à jour prix
- `refresh-database.bat` : Rafraîchissement DB
- `run-scheduler.bat` : Exécution scheduler

---

## 📚 DOCUMENTATION

### Outils de documentation

- **Markdown** : Documentation technique
- **PHPDoc** : Documentation code PHP
- **TypeScript** : Typage et documentation implicite
- **Postman Collection** : Documentation API

---

## 🔄 VERSIONING

- **Git** : Contrôle de version
- **Composer** : Versioning dépendances PHP
- **NPM** : Versioning dépendances Node.js

---

**Dernière mise à jour** : 2025-01-27
