# GUIDE COMPLET DE VALIDATION - BITCHEST
## Rapport Académique et Validation des Compétences

---

## TABLE DES MATIÈRES

1. [Architecture de la Base de Données](#architecture-de-la-base-de-données)
2. [Diagrammes UML](#diagrammes-uml)
3. [Prompts ChatUML](#prompts-chatuml)
4. [Guide par Compétence](#guide-par-compétence)
5. [Optimisations des Migrations](#optimisations-des-migrations)

---

## ARCHITECTURE DE LA BASE DE DONNÉES

### Schéma Entité-Association (MCD)

#### Entités Principales

**1. UTILISATEUR (users)**
- Un utilisateur possède UN SEUL portfolio (relation 1:1)
- Un utilisateur reçoit plusieurs notifications (relation 1:N)
- Un utilisateur peut avoir plusieurs tokens d'authentification (relation 1:N)

**2. PORTFOLIO (portfolios)**
- Un portfolio appartient à UN utilisateur (relation 1:1)
- Un portfolio concerne UNE cryptomonnaie (relation N:1)
- Un portfolio contient plusieurs transactions (relation 1:N)
- Un portfolio peut générer plusieurs notifications (relation 1:N)

**3. CRYPTOMONNAIE (crypto_currencies)**
- Une cryptomonnaie peut être dans plusieurs portfolios (relation 1:N)
- Une cryptomonnaie a plusieurs enregistrements de prix (relation 1:N)
- Une cryptomonnaie peut être référencée dans plusieurs notifications (relation 1:N)

**4. TRANSACTION (transactions)**
- Une transaction appartient à UN portfolio (relation N:1)

**5. ENREGISTREMENT_PRIX (crypto_price_records)**
- Un enregistrement de prix concerne UNE cryptomonnaie (relation N:1)

**6. NOTIFICATION (notifications)**
- Une notification est destinée à UN utilisateur (relation N:1)
- Une notification peut concerner UN portfolio (relation N:1, optionnelle)
- Une notification peut concerner UNE cryptomonnaie (relation N:1, optionnelle)

### Relations Détaillées

```
UTILISATEUR (1) ────────< (1) PORTFOLIO
     │                          │
     │                          │
     │ (1)                      │ (N)
     │                          │
     ▼                          ▼
NOTIFICATION              TRANSACTION
     │
     │ (N)
     │
     ▼
CRYPTOMONNAIE

CRYPTOMONNAIE (1) ────────< (N) ENREGISTREMENT_PRIX
```

---

## DIAGRAMMES UML

### 1. Diagramme de Classes - Modèles de Données

```
┌─────────────────────────────────────────────────────────────┐
│                        UTILISATEUR                           │
│                         (users)                              │
├─────────────────────────────────────────────────────────────┤
│ + id: int (PK)                                               │
│ + name: string                                               │
│ + first_name: string                                         │
│ + last_name: string                                          │
│ + email: string (UNIQUE)                                      │
│ + phone: string                                              │
│ + password: string (hashé)                                    │
│ + must_change_password: boolean                              │
│ + role: enum('admin', 'client')                              │
│ + status: enum('pending', 'pending_validation', 'active',    │
│           'blocked')                                          │
│ + email_verified_at: timestamp                               │
│ + euro_balance: decimal(12,2)                                │
│ + level: int                                                 │
│ + experience_points: int                                     │
│ + profile_picture: string                                    │
│ + profile_banner: string                                    │
│ + remember_token: string                                     │
│ + created_at: timestamp                                      │
│ + updated_at: timestamp                                      │
├─────────────────────────────────────────────────────────────┤
│ + portfolio(): Portfolio (1:1)                               │
│ + notifications(): Notification[] (1:N)                       │
│ + isAdmin(): boolean                                         │
│ + isClient(): boolean                                         │
│ + isActive(): boolean                                        │
│ + isBlocked(): boolean                                        │
│ + mustChangePassword(): boolean                              │
└─────────────────────────────────────────────────────────────┘
                            │
                            │ 1
                            │
                            │ 1
┌─────────────────────────────────────────────────────────────┐
│                        PORTFOLIO                              │
│                      (portfolios)                            │
├─────────────────────────────────────────────────────────────┤
│ + id: int (PK)                                               │
│ + user_id: int (FK, UNIQUE)                                  │
│ + crypto_currency_id: int (FK)                               │
│ + total_crypto_value: decimal(18,8)                          │
│ + created_at: timestamp                                      │
│ + updated_at: timestamp                                      │
├─────────────────────────────────────────────────────────────┤
│ + user(): User (1:1)                                         │
│ + crypto(): CryptoCurrency (N:1)                             │
│ + transactions(): Transaction[] (1:N)                        │
│ + notifications(): Notification[] (1:N)                      │
└─────────────────────────────────────────────────────────────┘
                            │
                            │ 1
                            │
                            │ N
┌─────────────────────────────────────────────────────────────┐
│                      TRANSACTION                              │
│                    (transactions)                            │
├─────────────────────────────────────────────────────────────┤
│ + id: int (PK)                                               │
│ + portfolio_id: int (FK)                                     │
│ + type: enum('buy', 'sell')                                  │
│ + quantity: decimal(18,8)                                     │
│ + price_at_transaction: decimal(18,8)                        │
│ + euro_amount: decimal(18,2)                                 │
│ + created_at: timestamp                                      │
│ + updated_at: timestamp                                      │
├─────────────────────────────────────────────────────────────┤
│ + portfolio(): Portfolio (N:1)                                │
│ + getCachedQuantity(portfolioId, type): float                │
│ + invalidatePortfolioCache(portfolioId): void                │
└─────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────┐
│                    CRYPTOMONNAIE                             │
│                (crypto_currencies)                           │
├─────────────────────────────────────────────────────────────┤
│ + id: int (PK)                                               │
│ + name: string                                               │
│ + symbol: string (UNIQUE)                                    │
│ + is_active: boolean                                         │
│ + created_at: timestamp                                      │
│ + updated_at: timestamp                                      │
├─────────────────────────────────────────────────────────────┤
│ + portfolios(): Portfolio[] (1:N)                            │
│ + priceRecords(): CryptoPriceRecord[] (1:N)                  │
│ + notifications(): Notification[] (1:N)                      │
└─────────────────────────────────────────────────────────────┘
                            │
                            │ 1
                            │
                            │ N
┌─────────────────────────────────────────────────────────────┐
│                  ENREGISTREMENT_PRIX                         │
│              (crypto_price_records)                          │
├─────────────────────────────────────────────────────────────┤
│ + id: int (PK)                                               │
│ + crypto_currency_id: int (FK)                               │
│ + price: decimal(18,8)                                       │
│ + recorded_at: timestamp                                     │
│ + created_at: timestamp                                      │
│ + updated_at: timestamp                                      │
├─────────────────────────────────────────────────────────────┤
│ + crypto(): CryptoCurrency (N:1)                            │
└─────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────┐
│                      NOTIFICATION                            │
│                   (notifications)                            │
├─────────────────────────────────────────────────────────────┤
│ + id: int (PK)                                               │
│ + user_id: int (FK)                                          │
│ + portfolio_id: int (FK, nullable)                          │
│ + crypto_currency_id: int (FK, nullable)                     │
│ + type: enum('profit', 'loss', 'price_alert',                │
│          'portfolio_update', 'level_up')                     │
│ + title: string                                              │
│ + message: text                                              │
│ + crypto_symbol: string                                      │
│ + gain_loss: decimal(18,8)                                   │
│ + gain_loss_percent: decimal(10,2)                           │
│ + current_price: decimal(18,8)                               │
│ + previous_price: decimal(18,8)                              │
│ + level: int                                                 │
│ + level_name: string                                         │
│ + is_read: boolean                                           │
│ + read_at: timestamp                                         │
│ + created_at: timestamp                                      │
│ + updated_at: timestamp                                      │
├─────────────────────────────────────────────────────────────┤
│ + user(): User (N:1)                                         │
│ + portfolio(): Portfolio (N:1, nullable)                     │
│ + crypto(): CryptoCurrency (N:1, nullable)                    │
│ + markAsRead(): void                                         │
└─────────────────────────────────────────────────────────────┘
```

### 2. Diagramme de Classes - Services Métier

```
┌─────────────────────────────────────────────────────────────┐
│                  SERVICE_TRANSACTION                         │
├─────────────────────────────────────────────────────────────┤
│ - portfolioService: PortfolioService                         │
│ - notificationService: NotificationService                  │
├─────────────────────────────────────────────────────────────┤
│ + processTransaction(user, crypto, quantity, price, type)    │
│   : Transaction                                              │
└─────────────────────────────────────────────────────────────┘
                            │ uses
                            │
                            ▼
┌─────────────────────────────────────────────────────────────┐
│                  SERVICE_PORTFOLIO                           │
├─────────────────────────────────────────────────────────────┤
│ - cryptoService: CryptoService                               │
├─────────────────────────────────────────────────────────────┤
│ + updatePortfolio(portfolio, transaction, quantity, price)   │
│ + getUserPortfolio(user): Collection                        │
│ + getPurchaseDetails(user, cryptoId): Collection            │
└─────────────────────────────────────────────────────────────┘
                            │ uses
                            │
                            ▼
┌─────────────────────────────────────────────────────────────┐
│                   SERVICE_CRYPTO                             │
├─────────────────────────────────────────────────────────────┤
│ - coinbaseAPIService: CoinbaseAPIService                    │
│ - compressionService: CryptoDataCompressionService          │
│ - redisPriceService: RedisPriceService                      │
├─────────────────────────────────────────────────────────────┤
│ + getCurrentPrices(forceRefresh): Collection                │
│ + getCurrentPrice(symbol): float                            │
│ + getHistoricalPrices(symbol, days): Collection             │
└─────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────┐
│                SERVICE_NOTIFICATION                          │
├─────────────────────────────────────────────────────────────┤
│ - portfolioService: PortfolioService                         │
│ - levelService: LevelService                                 │
│ - notificationCacheService: NotificationCacheService        │
├─────────────────────────────────────────────────────────────┤
│ + checkAndCreatePortfolioNotifications(user): void           │
│ + createTransactionNotification(...): void                  │
│ + markAsRead(notificationId, userId): boolean                │
│ + markAllAsRead(userId): int                                │
└─────────────────────────────────────────────────────────────┘
```

---

## PROMPTS CHATUML

### Prompt 1 : Diagramme de Classes - Modèles de Données

```
Crée un diagramme de classes UML en français avec les classes suivantes :

CLASSE UTILISATEUR (users)
Attributs :
- id: int (clé primaire)
- name: string
- first_name: string
- last_name: string
- email: string (unique)
- phone: string
- password: string (hashé)
- must_change_password: boolean
- role: enum('admin', 'client')
- status: enum('pending', 'pending_validation', 'active', 'blocked')
- email_verified_at: timestamp
- euro_balance: decimal(12,2)
- level: int
- experience_points: int
- profile_picture: string
- profile_banner: string
- remember_token: string
- created_at: timestamp
- updated_at: timestamp

Méthodes :
+ portfolio(): Portfolio (relation 1:1)
+ notifications(): Notification[] (relation 1:N)
+ isAdmin(): boolean
+ isClient(): boolean
+ isActive(): boolean
+ isBlocked(): boolean
+ mustChangePassword(): boolean

CLASSE PORTFOLIO (portfolios)
Attributs :
- id: int (clé primaire)
- user_id: int (clé étrangère, UNIQUE - relation 1:1 avec User)
- crypto_currency_id: int (clé étrangère)
- total_crypto_value: decimal(18,8)
- created_at: timestamp
- updated_at: timestamp

Méthodes :
+ user(): User (relation 1:1)
+ crypto(): CryptoCurrency (relation N:1)
+ transactions(): Transaction[] (relation 1:N)
+ notifications(): Notification[] (relation 1:N)

CLASSE TRANSACTION (transactions)
Attributs :
- id: int (clé primaire)
- portfolio_id: int (clé étrangère)
- type: enum('buy', 'sell')
- quantity: decimal(18,8)
- price_at_transaction: decimal(18,8)
- euro_amount: decimal(18,2)
- created_at: timestamp
- updated_at: timestamp

Méthodes :
+ portfolio(): Portfolio (relation N:1)
+ getCachedQuantity(portfolioId, type): float
+ invalidatePortfolioCache(portfolioId): void

CLASSE CRYPTOMONNAIE (crypto_currencies)
Attributs :
- id: int (clé primaire)
- name: string
- symbol: string (unique)
- is_active: boolean
- created_at: timestamp
- updated_at: timestamp

Méthodes :
+ portfolios(): Portfolio[] (relation 1:N)
+ priceRecords(): CryptoPriceRecord[] (relation 1:N)
+ notifications(): Notification[] (relation 1:N)

CLASSE ENREGISTREMENT_PRIX (crypto_price_records)
Attributs :
- id: int (clé primaire)
- crypto_currency_id: int (clé étrangère)
- price: decimal(18,8)
- recorded_at: timestamp
- created_at: timestamp
- updated_at: timestamp

Méthodes :
+ crypto(): CryptoCurrency (relation N:1)

CLASSE NOTIFICATION (notifications)
Attributs :
- id: int (clé primaire)
- user_id: int (clé étrangère)
- portfolio_id: int (clé étrangère, nullable)
- crypto_currency_id: int (clé étrangère, nullable)
- type: enum('profit', 'loss', 'price_alert', 'portfolio_update', 'level_up')
- title: string
- message: text
- crypto_symbol: string
- gain_loss: decimal(18,8)
- gain_loss_percent: decimal(10,2)
- current_price: decimal(18,8)
- previous_price: decimal(18,8)
- level: int
- level_name: string
- is_read: boolean
- read_at: timestamp
- created_at: timestamp
- updated_at: timestamp

Méthodes :
+ user(): User (relation N:1)
+ portfolio(): Portfolio (relation N:1, nullable)
+ crypto(): CryptoCurrency (relation N:1, nullable)
+ markAsRead(): void

RELATIONS :
- UTILISATEUR 1 ──── 1 PORTFOLIO (un utilisateur a un seul portfolio)
- UTILISATEUR 1 ──── N NOTIFICATION (un utilisateur a plusieurs notifications)
- PORTFOLIO N ──── 1 CRYPTOMONNAIE (plusieurs portfolios pour une crypto)
- PORTFOLIO 1 ──── N TRANSACTION (un portfolio a plusieurs transactions)
- PORTFOLIO 1 ──── N NOTIFICATION (un portfolio peut générer plusieurs notifications)
- CRYPTOMONNAIE 1 ──── N ENREGISTREMENT_PRIX (une crypto a plusieurs prix)
- CRYPTOMONNAIE 1 ──── N NOTIFICATION (une crypto peut être dans plusieurs notifications)
- NOTIFICATION N ──── 1 PORTFOLIO (optionnel, nullable)
- NOTIFICATION N ──── 1 CRYPTOMONNAIE (optionnel, nullable)

Utilise la notation UML standard avec les cardinalités et les stéréotypes appropriés.
```

### Prompt 2 : Diagramme de Séquence - Achat de Crypto

```
Crée un diagramme de séquence UML en français pour le processus d'achat de cryptomonnaie :

Acteurs :
- Client (Frontend Vue.js)
- ContrôleurTransaction (Laravel Controller)
- ServiceTransaction (Laravel Service)
- ServicePortfolio (Laravel Service)
- ServiceRedisPrix (Laravel Service)
- ModèleUtilisateur (Eloquent Model)
- ModèlePortfolio (Eloquent Model)
- ModèleTransaction (Eloquent Model)
- BaseDeDonnées (MySQL)
- Redis (Cache)

Séquence :
1. Client envoie requête POST /api/transaction/buy avec {symbol, quantity}
2. ContrôleurTransaction valide la requête
3. ContrôleurTransaction vérifie l'authentification (middleware)
4. ContrôleurTransaction récupère le prix depuis ServiceRedisPrix
5. ServiceRedisPrix retourne le prix actuel
6. ContrôleurTransaction appelle ServiceTransaction.processTransaction()
7. ServiceTransaction commence une transaction base de données
8. ServiceTransaction verrouille la ligne utilisateur (lockForUpdate)
9. ServiceTransaction vérifie le solde utilisateur
10. ServiceTransaction trouve ou crée le portfolio (relation 1:1)
11. ServiceTransaction débite le solde utilisateur
12. ServiceTransaction crée l'enregistrement Transaction
13. ServiceTransaction appelle ServicePortfolio.updatePortfolio()
14. ServicePortfolio met à jour total_crypto_value
15. ServiceTransaction valide la transaction (commit)
16. ServiceTransaction déclenche l'événement TransactionCreated
17. ContrôleurTransaction retourne la réponse JSON au Client

Utilise les notations UML standard avec les messages synchrones/asynchrones.
```

### Prompt 3 : Diagramme de Cas d'Utilisation - Client

```
Crée un diagramme de cas d'utilisation UML en français pour un utilisateur CLIENT :

Acteur : CLIENT

Cas d'utilisation :
1. S'inscrire (créer un compte)
2. Se connecter (authentification)
3. Changer le mot de passe (obligatoire à la première connexion)
4. Consulter le marché crypto (liste des cryptomonnaies avec prix)
5. Acheter une cryptomonnaie (transaction d'achat)
6. Vendre une cryptomonnaie (transaction de vente)
7. Consulter le portfolio (détails des investissements)
8. Consulter l'historique des transactions
9. Consulter les notifications (profit, loss, level_up)
10. Gérer le profil (modifier nom, téléphone)
11. Uploader photo de profil
12. Uploader bannière de profil
13. Supprimer photo de profil
14. Supprimer bannière de profil

Relations :
- "S'inscrire" inclut "Recevoir mot de passe temporaire"
- "Se connecter" étend "Vérifier statut compte"
- "Acheter" inclut "Vérifier solde"
- "Vendre" inclut "Vérifier quantité disponible"
- "Consulter portfolio" inclut "Calculer plus-value"

Utilise la notation UML standard avec les relations include/extend.
```

### Prompt 4 : Diagramme de Cas d'Utilisation - Admin

```
Crée un diagramme de cas d'utilisation UML en français pour un utilisateur ADMIN :

Acteur : ADMIN

Cas d'utilisation :
1. Se connecter (authentification admin)
2. Consulter le dashboard (statistiques globales)
3. Créer un utilisateur client
4. Approuver un utilisateur (changer statut pending_validation → active)
5. Bloquer un utilisateur (changer statut → blocked)
6. Débloquer un utilisateur
7. Modifier les informations d'un utilisateur
8. Supprimer un utilisateur
9. Consulter la liste des utilisateurs
10. Consulter les détails d'un utilisateur (portfolio, transactions)
11. Consulter toutes les transactions (filtres par user, symbol, type)
12. Consulter le marché crypto (admin)
13. Générer les prix initiaux (commande artisan)
14. Gérer le profil admin (modifier nom, email, mot de passe)

Relations :
- "Créer utilisateur" inclut "Envoyer mot de passe temporaire"
- "Approuver utilisateur" inclut "Créditer 500€ de solde initial"
- "Consulter dashboard" inclut "Calculer statistiques"
- "Consulter transactions" étend "Filtrer par critères"

Utilise la notation UML standard.
```

### Prompt 5 : Diagramme de Déploiement

```
Crée un diagramme de déploiement UML en français pour l'application BitChest :

Nœuds :
1. CLIENT_NAVIGATEUR (Chrome, Firefox, Edge)
2. SERVEUR_WEB (Apache/Nginx + PHP-FPM)
3. SERVEUR_APPLICATION (Laravel Backend)
4. SERVEUR_BASE_DONNEES (MySQL/MariaDB)
5. SERVEUR_CACHE (Redis)
6. SERVEUR_EXTERNE (Coinbase API)

Artéfacts :
- Application Vue.js (Frontend)
- Application Laravel (Backend)
- Base de données MySQL
- Cache Redis

Connexions :
- CLIENT_NAVIGATEUR ←→ SERVEUR_WEB (HTTPS)
- SERVEUR_WEB ←→ SERVEUR_APPLICATION (HTTP interne)
- SERVEUR_APPLICATION ←→ SERVEUR_BASE_DONNEES (MySQL Protocol)
- SERVEUR_APPLICATION ←→ SERVEUR_CACHE (Redis Protocol)
- SERVEUR_APPLICATION ←→ SERVEUR_EXTERNE (HTTPS - Coinbase API)

Utilise la notation UML standard pour les diagrammes de déploiement.
```

---

## GUIDE PAR COMPÉTENCE

### C1. MAQUETTER UNE APPLICATION

#### Ce que vous devez faire dans votre projet :

1. **Créer un document de maquettage** :
   - [ ] Captures d'écran de toutes les pages (Landing, Dashboard, Trade, Portfolio, Profile, Admin)
   - [ ] Schéma de navigation entre les pages
   - [ ] Spécification des couleurs et typographie (documenter dans un fichier `MAQUETTE.md`)

2. **Documenter l'enchaînement des écrans** :
   - [ ] Créer un fichier `NAVIGATION.md` avec :
     - Flux de connexion/inscription
     - Flux d'achat/vente
     - Flux de consultation portfolio
     - Flux admin (gestion utilisateurs)

3. **Valider avec utilisateurs** :
   - [ ] Tester l'application avec des utilisateurs réels
   - [ ] Documenter les retours dans `RETOURS_UTILISATEURS.md`

4. **Sécurisation interface** :
   - [ ] Documenter les validations frontend dans `SECURITE_UI.md`
   - [ ] Lister les protections CSRF, XSS
   - [ ] Expliquer la gestion des erreurs utilisateur

#### Rapport académique à produire :

**Section C1 dans votre rapport** :
```
1.1 Maquettes fonctionnelles
- Présentation des maquettes avec captures d'écran
- Justification des choix de design

1.2 Enchaînement des écrans
- Schéma de navigation
- Justification du flux utilisateur

1.3 Charte graphique
- Palette de couleurs utilisée
- Typographie
- Composants réutilisables

1.4 Sécurisation interface
- Mesures de sécurité implémentées
- Validation des entrées
- Gestion des erreurs
```

---

### C3. DÉVELOPPER DES COMPOSANTS D'ACCÈS AUX DONNÉES

#### Ce que vous devez faire dans votre projet :

1. **Documenter les services d'accès aux données** :
   - [ ] Créer `DOCUMENTATION_SERVICES.md` avec :
     - Description de chaque service (TransactionService, PortfolioService, etc.)
     - Méthodes principales et leur rôle
     - Exemples d'utilisation

2. **Créer des tests unitaires** :
   - [ ] Écrire au moins 3 tests pour `TransactionService`
   - [ ] Écrire au moins 2 tests pour `PortfolioService`
   - [ ] Documenter dans `tests/Feature/TransactionServiceTest.php`

3. **Documenter la sécurité** :
   - [ ] Créer `SECURITE_ACCES_DONNEES.md` expliquant :
     - Utilisation des prepared statements (Eloquent)
     - Validation des entrées
     - Transactions DB
     - Row locking

4. **Optimisations** :
   - [ ] Documenter l'utilisation du cache Redis
   - [ ] Expliquer les index sur les tables
   - [ ] Créer `OPTIMISATIONS_BDD.md`

#### Rapport académique à produire :

**Section C3 dans votre rapport** :
```
3.1 Architecture des composants d'accès
- Présentation des services métier
- Diagramme de classes des services
- Justification de l'architecture

3.2 Traitements de données
- Exemples de requêtes complexes
- Utilisation du cache
- Optimisations

3.3 Sécurité
- Prepared statements
- Validation des entrées
- Gestion des transactions

3.4 Tests
- Tests unitaires créés
- Couverture des tests
- Résultats des tests
```

---

### C4. DÉVELOPPER LA PARTIE FRONT-END

#### Ce que vous devez faire dans votre projet :

1. **Documenter le responsive design** :
   - [ ] Créer `RESPONSIVE_DESIGN.md` avec :
     - Breakpoints utilisés
     - Tests sur différents appareils (mobile, tablette, desktop)
     - Captures d'écran responsive

2. **Documenter le code** :
   - [ ] Ajouter des commentaires JSDoc dans les composants principaux
   - [ ] Créer `ARCHITECTURE_FRONTEND.md` expliquant :
     - Structure des composants
     - Utilisation de Pinia
     - Routing

3. **Tests fonctionnels** :
   - [ ] Créer un fichier `TESTS_FRONTEND.md` avec :
     - Scénarios de test manuels
     - Tests de validation de formulaires
     - Tests de navigation

4. **Sécurité frontend** :
   - [ ] Documenter dans `SECURITE_FRONTEND.md` :
     - Gestion des tokens
     - Validation côté client
     - Protection XSS

#### Rapport académique à produire :

**Section C4 dans votre rapport** :
```
4.1 Architecture frontend
- Structure Vue.js
- Composants réutilisables
- Gestion d'état (Pinia)

4.2 Responsive design
- Breakpoints
- Adaptations mobiles
- Tests sur différents appareils

4.3 Sécurité frontend
- Gestion des tokens
- Validation des entrées
- Protection XSS

4.4 Tests
- Tests fonctionnels
- Validation des formulaires
- Gestion des erreurs
```

---

### C5. DÉVELOPPER LA PARTIE BACK-END

#### Ce que vous devez faire dans votre projet :

1. **Documenter l'architecture backend** :
   - [ ] Créer `ARCHITECTURE_BACKEND.md` avec :
     - Structure MVC
     - Services métier
     - Injection de dépendances
     - Diagramme de classes backend

2. **Bonnes pratiques OOP** :
   - [ ] Documenter dans `PRATIQUES_OOP.md` :
     - Principes SOLID appliqués
     - Design patterns utilisés
     - Exemples de code

3. **Sécurité backend** :
   - [ ] Créer `SECURITE_BACKEND.md` avec :
     - Authentification Sanctum
     - Middleware de sécurité
     - Validation des entrées
     - Rate limiting

4. **Tests backend** :
   - [ ] Écrire des tests pour les contrôleurs principaux
   - [ ] Tests de sécurité
   - [ ] Documenter dans `TESTS_BACKEND.md`

#### Rapport académique à produire :

**Section C5 dans votre rapport** :
```
5.1 Architecture backend
- Structure MVC Laravel
- Services métier
- Injection de dépendances

5.2 Bonnes pratiques
- Principes SOLID
- Design patterns
- Code propre

5.3 Sécurité
- Authentification
- Autorisation
- Validation
- Protection CSRF

5.4 Tests
- Tests unitaires
- Tests d'intégration
- Tests de sécurité
```

---

### C6. CONCEVOIR UNE BASE DE DONNÉES

#### Ce que vous devez faire dans votre projet :

1. **Créer le MCD (Modèle Conceptuel de Données)** :
   - [ ] Dessiner le diagramme entité-association
   - [ ] Documenter dans `MCD_BITCHEST.md` :
     - Toutes les entités
     - Toutes les relations
     - Cardinalités
     - Contraintes

2. **Créer le MLD (Modèle Logique de Données)** :
   - [ ] Documenter dans `MLD_BITCHEST.md` :
     - Transformation MCD → MLD
     - Tables créées
     - Clés primaires et étrangères

3. **Normalisation** :
   - [ ] Créer `NORMALISATION.md` expliquant :
     - Forme normale 1NF
     - Forme normale 2NF
     - Forme normale 3NF
     - Justification de la normalisation

4. **Règles de nommage** :
   - [ ] Documenter dans `NOMENCLATURE.md` :
     - Conventions utilisées
     - Justification des choix

#### Rapport académique à produire :

**Section C6 dans votre rapport** :
```
6.1 Modèle Conceptuel de Données (MCD)
- Diagramme entité-association
- Entités et attributs
- Relations et cardinalités

6.2 Modèle Logique de Données (MLD)
- Transformation MCD → MLD
- Tables créées
- Clés primaires et étrangères

6.3 Normalisation
- Analyse de la normalisation
- Formes normales respectées
- Justification

6.4 Règles de nommage
- Conventions utilisées
- Cohérence du schéma
```

---

### C7. METTRE EN PLACE UNE BASE DE DONNÉES

#### Ce que vous devez faire dans votre projet :

1. **Documenter les migrations** :
   - [ ] Créer `MIGRATIONS.md` avec :
     - Liste de toutes les migrations
     - Ordre d'exécution
     - Description de chaque migration

2. **Intégrité des données** :
   - [ ] Créer `INTEGRITE_DONNEES.md` expliquant :
     - Foreign keys
     - Contraintes
     - Cascades
     - Index

3. **Sécurité base de données** :
   - [ ] Documenter dans `SECURITE_BDD.md` :
     - Utilisateurs DB
     - Droits d'accès
     - Chiffrement des données sensibles

4. **Procédures de restauration** :
   - [ ] Créer `PROCEDURES_RESTAURATION.md` avec :
     - Scripts de backup
     - Scripts de restauration
     - Procédures de test

#### Rapport académique à produire :

**Section C7 dans votre rapport** :
```
7.1 Création de la base de données
- Migrations Laravel
- Schéma physique
- Contraintes d'intégrité

7.2 Intégrité des données
- Foreign keys
- Contraintes
- Cascades

7.3 Sécurité
- Utilisateurs et droits
- Chiffrement
- Authentification

7.4 Maintenance
- Procédures de backup
- Procédures de restauration
- Monitoring
```

---

### C8. DÉVELOPPER DES COMPOSANTS DANS LE LANGAGE D'UNE BASE DE DONNÉES

#### Ce que vous devez faire dans votre projet :

1. **Documenter les requêtes complexes** :
   - [ ] Créer `REQUETES_COMPLEXES.md` avec :
     - Requêtes avec jointures
     - Requêtes avec agrégations
     - Requêtes optimisées
     - Explications de chaque requête

2. **Gestion des exceptions** :
   - [ ] Documenter dans `GESTION_EXCEPTIONS.md` :
     - Try-catch dans les services
     - Handler global
     - Messages d'erreur appropriés

3. **Gestion des conflits** :
   - [ ] Créer `GESTION_CONFLITS.md` expliquant :
     - Row locking (lockForUpdate)
     - Transactions DB
     - Gestion de la concurrence

4. **Validation des entrées** :
   - [ ] Documenter dans `VALIDATION_ENTREES.md` :
     - Form Requests Laravel
     - Validation côté serveur
     - Sanitization

#### Rapport académique à produire :

**Section C8 dans votre rapport** :
```
8.1 Requêtes complexes
- Exemples de requêtes
- Optimisations
- Utilisation des index

8.2 Gestion des exceptions
- Try-catch
- Handler global
- Messages d'erreur

8.3 Gestion des conflits
- Row locking
- Transactions
- Concurrence

8.4 Validation
- Validation des entrées
- Sanitization
- Contrôles de sécurité
```

---

### C9. COLLABORER À LA GESTION D'UN PROJET INFORMATIQUE

#### Ce que vous devez faire dans votre projet :

1. **Documenter la gestion de projet** :
   - [ ] Créer `GESTION_PROJET.md` avec :
     - Planning du projet
     - Tâches réalisées
     - Outils utilisés (Git, etc.)

2. **Procédures qualité** :
   - [ ] Créer `PROCEDURES_QUALITE.md` :
     - Standards de code
     - Code review
     - Tests

3. **Environnement de développement** :
   - [ ] Documenter dans `ENVIRONNEMENT_DEV.md` :
     - Configuration Docker
     - Scripts d'automatisation
     - Procédures de setup

4. **Outils collaboratifs** :
   - [ ] Créer `OUTILS_COLLABORATIFS.md` :
     - Utilisation de Git
     - Branches
     - Commits
     - Documentation

#### Rapport académique à produire :

**Section C9 dans votre rapport** :
```
9.1 Organisation du projet
- Structure des dossiers
- Gestion des versions (Git)
- Documentation

9.2 Procédures qualité
- Standards de code
- Tests
- Code review

9.3 Environnement de développement
- Configuration
- Scripts d'automatisation
- Procédures de déploiement

9.4 Collaboration
- Outils utilisés
- Communication
- Partage de connaissances
```

---

### C10. CONCEVOIR UNE APPLICATION

#### Ce que vous devez faire dans votre projet :

1. **Documenter les cas d'utilisation** :
   - [ ] Créer `CAS_UTILISATION.md` avec :
     - Diagrammes de cas d'utilisation (Client et Admin)
     - Description détaillée de chaque cas
     - Scénarios

2. **Besoins de sécurité** :
   - [ ] Créer `BESOINS_SECURITE.md` :
     - Identification des risques
     - Mesures de sécurité par couche
     - Justification

3. **Architecture technique** :
   - [ ] Documenter dans `ARCHITECTURE_TECHNIQUE.md` :
     - Diagramme de déploiement
     - Architecture MVC
     - Séparation des couches
     - Justification des choix

4. **Dossier de conception** :
   - [ ] Créer un dossier `CONCEPTION/` avec :
     - Tous les diagrammes UML
     - Spécifications techniques
     - Justifications architecturales

#### Rapport académique à produire :

**Section C10 dans votre rapport** :
```
10.1 Analyse des besoins
- Cas d'utilisation
- Scénarios utilisateur
- Exigences fonctionnelles

10.2 Conception
- Architecture technique
- Diagrammes UML
- Justification des choix

10.3 Sécurité
- Identification des risques
- Mesures de sécurité
- Stratégie par couche

10.4 Documentation
- Dossier de conception
- Spécifications techniques
- Diagrammes
```

---

## OPTIMISATIONS DES MIGRATIONS

Voir le fichier `MIGRATIONS_OPTIMISEES.md` pour les migrations optimisées avec la relation 1:1 User-Portfolio.

---

**Document créé le** : 2025-01-27  
**Version** : 2.0  
**Langue** : Français
