# GUIDE ACADÉMIQUE DÉTAILLÉ - BITCHEST
## Actions Concrètes pour Valider Chaque Compétence

Ce guide vous indique **exactement** ce que vous devez faire dans votre projet pour valider chaque compétence et créer votre rapport académique.

---

## STRUCTURE DU RAPPORT ACADÉMIQUE

Votre rapport doit contenir :

1. **Page de garde**
2. **Table des matières**
3. **Introduction** (contexte, objectifs)
4. **Partie 1 : Analyse et Conception** (C1, C6, C10)
5. **Partie 2 : Développement Backend** (C3, C5, C7, C8)
6. **Partie 3 : Développement Frontend** (C4)
7. **Partie 4 : Gestion de Projet** (C9)
8. **Conclusion**
9. **Annexes** (diagrammes UML, captures d'écran, code)

---

## C1. MAQUETTER UNE APPLICATION

### ✅ Actions à réaliser dans votre projet :

#### 1. Créer un document de maquettage

**Fichier à créer** : `DOCUMENTATION/MAQUETTE.md`

**Contenu à inclure** :

```markdown
# MAQUETTES FONCTIONNELLES - BITCHEST

## 1. Page Landing
- Capture d'écran de la page d'accueil
- Description des sections (Hero, Features, Stats, etc.)
- Justification des choix de design

## 2. Page Connexion
- Capture d'écran du formulaire
- Validation des champs
- Messages d'erreur

## 3. Page Inscription
- Capture d'écran du formulaire
- Processus d'inscription
- Envoi du mot de passe temporaire

## 4. Dashboard Utilisateur
- Capture d'écran complète
- Éléments affichés (solde, portfolio, graphiques)
- Navigation

## 5. Page Trading
- Capture d'écran de l'interface de trading
- Formulaire d'achat/vente
- Affichage des prix en temps réel

## 6. Page Portfolio
- Capture d'écran du portfolio
- Détails des cryptos possédées
- Calcul des plus-values

## 7. Page Profil
- Capture d'écran du profil utilisateur
- Formulaire de modification
- Upload de photos

## 8. Interface Admin
- Captures d'écran de toutes les pages admin
- Dashboard admin
- Gestion des utilisateurs
```

#### 2. Créer le schéma de navigation

**Fichier à créer** : `DOCUMENTATION/NAVIGATION.md`

**Contenu à inclure** :

```markdown
# SCHÉMA DE NAVIGATION - BITCHEST

## Flux Principal Utilisateur

1. Landing Page
   ↓
2. Inscription / Connexion
   ↓
3. Dashboard (si actif)
   ↓
4. Trading / Portfolio / Profil

## Flux Inscription

1. Formulaire inscription
   ↓
2. Email avec mot de passe temporaire
   ↓
3. Connexion avec mot de passe temporaire
   ↓
4. Changement de mot de passe obligatoire
   ↓
5. Attente validation admin (statut: pending_validation)
   ↓
6. Validation admin → Statut: active
   ↓
7. Accès complet à l'application

## Flux Achat Crypto

1. Page Trading
   ↓
2. Sélection crypto
   ↓
3. Saisie quantité
   ↓
4. Validation solde
   ↓
5. Confirmation transaction
   ↓
6. Mise à jour portfolio
   ↓
7. Notification de transaction

## Flux Admin

1. Connexion admin
   ↓
2. Dashboard admin
   ↓
3. Gestion utilisateurs / Transactions / Market
```

**Action** : Créer un diagramme de flux (peut être fait avec draw.io ou directement dans le document)

#### 3. Documenter la charte graphique

**Fichier à créer** : `DOCUMENTATION/CHARTE_GRAPHIQUE.md`

**Contenu à inclure** :

```markdown
# CHARTE GRAPHIQUE - BITCHEST

## Palette de Couleurs

### Couleurs Principales
- Bleu foncé (Primary) : #1e3a8a
- Bleu (Secondary) : #3b82f6
- Vert (Success) : #10b981
- Rouge (Danger) : #ef4444

### Couleurs de Fond
- Fond clair : #ffffff
- Fond sombre : #1f2937
- Fond secondaire : #f3f4f6

## Typographie

- Police principale : Inter, system-ui, sans-serif
- Taille des titres : 
  - H1 : 3rem (48px)
  - H2 : 2.25rem (36px)
  - H3 : 1.875rem (30px)

## Composants Réutilisables

- Button : Composant Button.vue
- Card : Composant SkeletonCard.vue
- Navbar : Composant Navbar.vue
- Footer : Composant UserFooter.vue
```

**Action** : Extraire les couleurs de `bitchest-frontend/src/index.css` et `bitchest-frontend/src/theme/colors.ts`

#### 4. Documenter la sécurisation de l'interface

**Fichier à créer** : `DOCUMENTATION/SECURITE_UI.md`

**Contenu à inclure** :

```markdown
# SÉCURISATION DE L'INTERFACE - BITCHEST

## Validations Frontend

### Formulaire de Connexion
- Validation email (format)
- Validation password (non vide)
- Messages d'erreur clairs

### Formulaire d'Achat
- Validation quantité (nombre positif)
- Vérification solde avant envoi
- Confirmation visuelle

## Protection CSRF
- Middleware Laravel VerifyCsrfToken
- Tokens générés automatiquement

## Protection XSS
- Vue.js auto-escaping des données
- Sanitization des entrées utilisateur

## Gestion des Erreurs
- Messages d'erreur utilisateur-friendly
- Codes d'erreur HTTP appropriés
- Logs côté serveur pour debugging
```

**Action** : Analyser le code frontend et backend pour documenter les mesures de sécurité

### 📝 Section C1 dans votre rapport académique :

```
CHAPITRE 1 : MAQUETTAGE DE L'APPLICATION

1.1 Maquettes fonctionnelles
- Présentation des maquettes avec captures d'écran
- Justification des choix de design
- Référence : DOCUMENTATION/MAQUETTE.md

1.2 Enchaînement des écrans
- Schéma de navigation
- Flux utilisateur détaillés
- Justification du parcours utilisateur
- Référence : DOCUMENTATION/NAVIGATION.md

1.3 Charte graphique
- Palette de couleurs
- Typographie
- Composants réutilisables
- Référence : DOCUMENTATION/CHARTE_GRAPHIQUE.md

1.4 Sécurisation de l'interface
- Validations frontend
- Protection CSRF et XSS
- Gestion des erreurs
- Référence : DOCUMENTATION/SECURITE_UI.md
```

---

## C3. DÉVELOPPER DES COMPOSANTS D'ACCÈS AUX DONNÉES

### ✅ Actions à réaliser dans votre projet :

#### 1. Documenter les services d'accès aux données

**Fichier à créer** : `DOCUMENTATION/SERVICES_ACCES_DONNEES.md`

**Contenu à inclure** :

```markdown
# SERVICES D'ACCÈS AUX DONNÉES - BITCHEST

## TransactionService

### Méthode : processTransaction()
- **Rôle** : Traiter une transaction d'achat ou de vente
- **Paramètres** : User, CryptoCurrency, quantity, price, type
- **Retour** : Transaction
- **Sécurité** : 
  - Transaction DB pour atomicité
  - Row locking (lockForUpdate) pour éviter race conditions
  - Validation du solde/quantité

### Exemple de code :
```php
// Fichier : app/Services/TransactionService.php
// Ligne 21-96
```

## PortfolioService

### Méthode : getUserPortfolio()
- **Rôle** : Récupérer le portfolio d'un utilisateur avec calculs
- **Paramètres** : User
- **Retour** : Collection de portfolios enrichis
- **Optimisations** :
  - Cache Redis pour les quantités
  - Requêtes optimisées avec index

### Méthode : updatePortfolio()
- **Rôle** : Mettre à jour le portfolio après une transaction
- **Paramètres** : Portfolio, Transaction, quantity, price, type
- **Logique** : Calcul de total_crypto_value selon type (buy/sell)

## CryptoService

### Méthode : getCurrentPrices()
- **Rôle** : Récupérer les prix actuels de toutes les cryptos
- **Optimisations** : Cache Redis
- **Fallback** : Base de données si Redis indisponible
```

**Action** : Analyser chaque service et documenter ses méthodes principales

#### 2. Créer des tests unitaires

**Fichier à créer** : `bitchest-backend/tests/Feature/TransactionServiceTest.php`

**Contenu à inclure** :

```php
<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\CryptoCurrency;
use App\Services\TransactionService;
use Illuminate\Foundation\Testing\RefreshDatabase;

class TransactionServiceTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function test_achat_crypto_solde_suffisant()
    {
        // Arrange
        $user = User::factory()->create(['euro_balance' => 1000]);
        $crypto = CryptoCurrency::factory()->create();
        $service = app(TransactionService::class);
        
        // Act
        $transaction = $service->processTransaction(
            $user, 
            $crypto, 
            0.5, 
            1000, 
            'buy'
        );
        
        // Assert
        $this->assertNotNull($transaction);
        $this->assertEquals('buy', $transaction->type);
        $this->assertEquals(500, $user->fresh()->euro_balance);
    }
    
    /** @test */
    public function test_achat_crypto_solde_insuffisant()
    {
        // Arrange
        $user = User::factory()->create(['euro_balance' => 100]);
        $crypto = CryptoCurrency::factory()->create();
        $service = app(TransactionService::class);
        
        // Act & Assert
        $this->expectException(\InvalidArgumentException::class);
        $service->processTransaction($user, $crypto, 1, 1000, 'buy');
    }
    
    /** @test */
    public function test_vente_crypto_quantite_suffisante()
    {
        // Test de vente avec quantité suffisante
        // À compléter
    }
}
```

**Action** : Écrire au moins 5 tests pour TransactionService et 3 pour PortfolioService

#### 3. Documenter la sécurité des accès

**Fichier à créer** : `DOCUMENTATION/SECURITE_ACCES_DONNEES.md`

**Contenu à inclure** :

```markdown
# SÉCURITÉ DES ACCÈS AUX DONNÉES - BITCHEST

## Prepared Statements

Laravel Eloquent utilise automatiquement les prepared statements :
```php
// Exemple dans TransactionService.php ligne 31
$portfolio = Portfolio::firstOrCreate([...]);
// Génère : SELECT * FROM portfolios WHERE user_id = ? AND crypto_currency_id = ?
```

## Validation des Entrées

### Form Requests Laravel
- Fichier : app/Http/Requests/Client/BuyCryptoRequest.php
- Validation : symbol (required), quantity (required|numeric|min:0.00000001)

## Transactions Base de Données

### Exemple dans TransactionService
```php
return DB::transaction(function () use (...) {
    // Toutes les opérations sont atomiques
    // En cas d'erreur, rollback automatique
});
```

## Row Locking

### Protection contre les race conditions
```php
// TransactionService.php ligne 26
$user = User::where('id', $user->id)->lockForUpdate()->first();
// Verrouille la ligne jusqu'à la fin de la transaction
```
```

**Action** : Analyser le code et documenter chaque mesure de sécurité

### 📝 Section C3 dans votre rapport académique :

```
CHAPITRE 3 : COMPOSANTS D'ACCÈS AUX DONNÉES

3.1 Architecture des composants
- Présentation des services métier
- Diagramme de classes des services
- Justification de l'architecture
- Référence : DOCUMENTATION/SERVICES_ACCES_DONNEES.md

3.2 Traitements de données
- Exemples de requêtes complexes
- Utilisation du cache Redis
- Optimisations (index, requêtes)
- Code source commenté

3.3 Sécurité
- Prepared statements (Eloquent)
- Validation des entrées (Form Requests)
- Gestion des transactions DB
- Row locking pour concurrence
- Référence : DOCUMENTATION/SECURITE_ACCES_DONNEES.md

3.4 Tests
- Tests unitaires créés
- Couverture des tests
- Résultats des tests
- Référence : tests/Feature/TransactionServiceTest.php
```

---

## C4. DÉVELOPPER LA PARTIE FRONT-END

### ✅ Actions à réaliser dans votre projet :

#### 1. Documenter le responsive design

**Fichier à créer** : `DOCUMENTATION/RESPONSIVE_DESIGN.md`

**Contenu à inclure** :

```markdown
# RESPONSIVE DESIGN - BITCHEST

## Breakpoints Tailwind CSS

- sm: 640px (petits écrans)
- md: 768px (tablettes)
- lg: 1024px (desktop)
- xl: 1280px (grand desktop)

## Exemples d'Adaptation

### Navbar
- Desktop : Menu horizontal complet
- Mobile : Menu hamburger

### Dashboard
- Desktop : 3 colonnes
- Tablette : 2 colonnes
- Mobile : 1 colonne

## Tests Responsive

### Appareils testés
- iPhone 12 (390x844)
- iPad (768x1024)
- Desktop 1920x1080

### Captures d'écran
- [Inclure captures pour chaque breakpoint]
```

**Action** : Prendre des captures d'écran sur différents appareils/tailles

#### 2. Documenter l'architecture frontend

**Fichier à créer** : `DOCUMENTATION/ARCHITECTURE_FRONTEND.md`

**Contenu à inclure** :

```markdown
# ARCHITECTURE FRONTEND - BITCHEST

## Structure des Composants

### Composants Réutilisables
- Button.vue : Bouton stylisé
- Navbar.vue : Barre de navigation
- NotificationDropdown.vue : Dropdown notifications
- ProfessionalTradingChart.vue : Graphique de trading

### Pages
- LandingPage.vue : Page d'accueil
- UserDashboard.vue : Dashboard utilisateur
- TradePage.vue : Page de trading
- Portfolio.vue : Page portfolio

## Gestion d'État (Pinia)

### Store Auth
- Fichier : src/stores/auth.ts
- État : user, token, isAuthenticated
- Actions : login, logout, fetchUser

## Routing

### Configuration
- Fichier : src/router/index.ts
- Routes protégées : middleware requiresAuth
- Routes par rôle : middleware roles

## Services API

### Service API Principal
- Fichier : src/services/api.ts
- Gestion des tokens
- Intercepteurs axios
- Gestion des erreurs
```

**Action** : Analyser la structure frontend et documenter

#### 3. Créer des tests fonctionnels

**Fichier à créer** : `DOCUMENTATION/TESTS_FRONTEND.md`

**Contenu à inclure** :

```markdown
# TESTS FONCTIONNELS FRONTEND - BITCHEST

## Scénarios de Test

### Test 1 : Connexion Utilisateur
1. Ouvrir la page de connexion
2. Saisir email et mot de passe
3. Cliquer sur "Se connecter"
4. Vérifier redirection vers dashboard
5. Vérifier affichage du nom utilisateur

### Test 2 : Achat de Crypto
1. Se connecter
2. Aller sur la page Trading
3. Sélectionner une crypto (ex: BTC)
4. Saisir une quantité
5. Cliquer sur "Acheter"
6. Vérifier message de succès
7. Vérifier mise à jour du solde
8. Vérifier mise à jour du portfolio

### Test 3 : Validation de Formulaire
1. Tenter d'acheter avec solde insuffisant
2. Vérifier message d'erreur affiché
3. Vérifier que la transaction n'est pas créée

## Tests de Navigation
- [Liste des tests de navigation entre les pages]
```

**Action** : Créer un plan de test et l'exécuter manuellement

### 📝 Section C4 dans votre rapport académique :

```
CHAPITRE 4 : DÉVELOPPEMENT FRONTEND

4.1 Architecture frontend
- Structure Vue.js
- Composants réutilisables
- Gestion d'état (Pinia)
- Routing
- Référence : DOCUMENTATION/ARCHITECTURE_FRONTEND.md

4.2 Responsive design
- Breakpoints utilisés
- Adaptations mobiles/tablettes
- Tests sur différents appareils
- Captures d'écran
- Référence : DOCUMENTATION/RESPONSIVE_DESIGN.md

4.3 Sécurité frontend
- Gestion des tokens
- Validation des entrées
- Protection XSS
- Référence : DOCUMENTATION/SECURITE_FRONTEND.md

4.4 Tests
- Tests fonctionnels
- Validation des formulaires
- Gestion des erreurs
- Référence : DOCUMENTATION/TESTS_FRONTEND.md
```

---

## C5. DÉVELOPPER LA PARTIE BACK-END

### ✅ Actions à réaliser dans votre projet :

#### 1. Documenter l'architecture backend

**Fichier à créer** : `DOCUMENTATION/ARCHITECTURE_BACKEND.md`

**Contenu à inclure** :

```markdown
# ARCHITECTURE BACKEND - BITCHEST

## Structure MVC Laravel

### Modèles (Models)
- User.php : Modèle utilisateur
- Transaction.php : Modèle transaction
- Portfolio.php : Modèle portfolio
- CryptoCurrency.php : Modèle cryptomonnaie

### Vues (Views)
- Emails : resources/views/emails/
- Blade templates pour emails

### Contrôleurs (Controllers)
- AuthController.php : Authentification
- TransactionController.php : Transactions client
- PortfolioController.php : Portfolio client
- Admin/* : Contrôleurs admin

## Services Métier

### TransactionService
- Logique métier des transactions
- Gestion des transactions DB
- Validation des règles métier

### PortfolioService
- Calculs de portfolio
- Mise à jour des valeurs
- Récupération des données

## Injection de Dépendances

### Exemple
```php
public function __construct(
    PortfolioService $portfolioService,
    NotificationService $notificationService
) {
    $this->portfolioService = $portfolioService;
    $this->notificationService = $notificationService;
}
```

## Principes SOLID

### Single Responsibility
- Chaque service a une responsabilité unique
- TransactionService : Gestion transactions
- PortfolioService : Gestion portfolios

### Dependency Injection
- Toutes les dépendances injectées via constructeur
- Facilite les tests et la maintenance
```

**Action** : Analyser l'architecture et documenter

#### 2. Documenter les bonnes pratiques OOP

**Fichier à créer** : `DOCUMENTATION/PRATIQUES_OOP.md`

**Contenu à inclure** :

```markdown
# BONNES PRATIQUES OOP - BITCHEST

## Principes SOLID Appliqués

### Single Responsibility Principle
- TransactionService : Uniquement la logique de transaction
- PortfolioService : Uniquement la logique de portfolio
- CryptoService : Uniquement la logique crypto

### Open/Closed Principle
- Services extensibles via injection de dépendances
- Pas de modification du code existant pour ajouter des fonctionnalités

### Dependency Inversion Principle
- Dépendances injectées, pas créées dans les classes
- Facilite les tests avec mocks

## Design Patterns

### Service Layer Pattern
- Séparation logique métier / contrôleurs
- Services réutilisables

### Repository Pattern (via Eloquent)
- Abstraction de l'accès aux données
- Modèles Eloquent comme repositories

### DTO Pattern
- TradeOrderData.php : Transfert de données typées
```

**Action** : Identifier et documenter les patterns utilisés

### 📝 Section C5 dans votre rapport académique :

```
CHAPITRE 5 : DÉVELOPPEMENT BACKEND

5.1 Architecture backend
- Structure MVC Laravel
- Services métier
- Injection de dépendances
- Référence : DOCUMENTATION/ARCHITECTURE_BACKEND.md

5.2 Bonnes pratiques
- Principes SOLID
- Design patterns
- Code propre et maintenable
- Référence : DOCUMENTATION/PRATIQUES_OOP.md

5.3 Sécurité
- Authentification (Sanctum)
- Autorisation (rôles)
- Validation (Form Requests)
- Protection CSRF
- Référence : DOCUMENTATION/SECURITE_BACKEND.md

5.4 Tests
- Tests unitaires
- Tests d'intégration
- Tests de sécurité
- Référence : tests/Feature/
```

---

## C6. CONCEVOIR UNE BASE DE DONNÉES

### ✅ Actions à réaliser dans votre projet :

#### 1. Créer le MCD (Modèle Conceptuel de Données)

**Fichier à créer** : `DOCUMENTATION/MCD_BITCHEST.md`

**Contenu à inclure** :

```markdown
# MODÈLE CONCEPTUEL DE DONNÉES (MCD) - BITCHEST

## Entités

### UTILISATEUR
- id (PK)
- name
- email (UNIQUE)
- password
- role
- status
- euro_balance
- level
- experience_points

### PORTFOLIO
- id (PK)
- user_id (FK, UNIQUE - relation 1:1)
- crypto_currency_id (FK)
- total_crypto_value

### TRANSACTION
- id (PK)
- portfolio_id (FK)
- type
- quantity
- price_at_transaction
- euro_amount

### CRYPTOMONNAIE
- id (PK)
- name
- symbol (UNIQUE)
- is_active

### ENREGISTREMENT_PRIX
- id (PK)
- crypto_currency_id (FK)
- price
- recorded_at

### NOTIFICATION
- id (PK)
- user_id (FK)
- portfolio_id (FK, nullable)
- crypto_currency_id (FK, nullable)
- type
- title
- message
- is_read

## Relations

- UTILISATEUR (1) ──── (1) PORTFOLIO
- UTILISATEUR (1) ──── (N) NOTIFICATION
- PORTFOLIO (N) ──── (1) CRYPTOMONNAIE
- PORTFOLIO (1) ──── (N) TRANSACTION
- PORTFOLIO (1) ──── (N) NOTIFICATION
- CRYPTOMONNAIE (1) ──── (N) ENREGISTREMENT_PRIX
- CRYPTOMONNAIE (1) ──── (N) NOTIFICATION
```

**Action** : Créer un diagramme entité-association (utiliser ChatUML avec le prompt fourni)

#### 2. Créer le MLD (Modèle Logique de Données)

**Fichier à créer** : `DOCUMENTATION/MLD_BITCHEST.md`

**Contenu à inclure** :

```markdown
# MODÈLE LOGIQUE DE DONNÉES (MLD) - BITCHEST

## Transformation MCD → MLD

### Règle 1 : Entité → Table
Chaque entité devient une table.

### Règle 2 : Attribut → Colonne
Chaque attribut devient une colonne.

### Règle 3 : Relation 1:1
- Clé étrangère dans une des deux tables
- Contrainte UNIQUE sur la clé étrangère

### Règle 4 : Relation 1:N
- Clé étrangère dans la table du côté N

### Règle 5 : Relation N:N
- Table de liaison créée

## Tables Créées

### users
- id (PK, INT, AUTO_INCREMENT)
- name (VARCHAR(255))
- email (VARCHAR(255), UNIQUE)
- ...

### portfolios
- id (PK, INT, AUTO_INCREMENT)
- user_id (FK, INT, UNIQUE) ← Relation 1:1
- crypto_currency_id (FK, INT)
- ...

### transactions
- id (PK, INT, AUTO_INCREMENT)
- portfolio_id (FK, INT)
- ...

[Continuer pour toutes les tables]
```

**Action** : Documenter la transformation MCD → MLD

#### 3. Documenter la normalisation

**Fichier à créer** : `DOCUMENTATION/NORMALISATION.md`

**Contenu à inclure** :

```markdown
# NORMALISATION - BITCHEST

## Forme Normale 1 (1NF)

### Règle : Pas de groupes répétitifs
✅ Respectée : Chaque colonne contient une valeur atomique

### Exemple
- Table transactions : Chaque transaction est une ligne séparée
- Pas de colonne "transactions_list" avec plusieurs valeurs

## Forme Normale 2 (2NF)

### Règle : 1NF + Dépendances fonctionnelles complètes
✅ Respectée : Toutes les colonnes dépendent de la clé primaire

### Exemple
- Table transactions : Toutes les colonnes dépendent de id
- Pas de dépendance partielle

## Forme Normale 3 (3NF)

### Règle : 2NF + Pas de dépendances transitives
✅ Respectée : Pas de dépendances indirectes

### Exemple
- Table users : email dépend directement de id
- Pas de dépendance email → autre_colonne → id

## Justification

La base de données respecte la 3NF, ce qui garantit :
- Pas de redondance
- Intégrité des données
- Facilité de maintenance
```

**Action** : Analyser chaque table et justifier la normalisation

### 📝 Section C6 dans votre rapport académique :

```
CHAPITRE 6 : CONCEPTION DE LA BASE DE DONNÉES

6.1 Modèle Conceptuel de Données (MCD)
- Diagramme entité-association
- Entités et attributs
- Relations et cardinalités
- Référence : DOCUMENTATION/MCD_BITCHEST.md
- Diagramme : [Inclure diagramme ChatUML]

6.2 Modèle Logique de Données (MLD)
- Transformation MCD → MLD
- Tables créées
- Clés primaires et étrangères
- Référence : DOCUMENTATION/MLD_BITCHEST.md

6.3 Normalisation
- Analyse de la normalisation
- Formes normales respectées (1NF, 2NF, 3NF)
- Justification
- Référence : DOCUMENTATION/NORMALISATION.md

6.4 Règles de nommage
- Conventions utilisées
- Cohérence du schéma
- Référence : DOCUMENTATION/NOMENCLATURE.md
```

---

## C7. METTRE EN PLACE UNE BASE DE DONNÉES

### ✅ Actions à réaliser dans votre projet :

#### 1. Documenter les migrations

**Fichier à créer** : `DOCUMENTATION/MIGRATIONS.md`

**Contenu à inclure** :

```markdown
# MIGRATIONS LARAVEL - BITCHEST

## Liste des Migrations

### 2014_10_12_000000_create_users_table.php
- Création de la table users
- Colonnes : id, name, email, password, role, status, etc.
- Index : email, role, status

### 2025_12_11_205634_create_crypto_currencies_table.php
- Création de la table crypto_currencies
- Colonnes : id, name, symbol, is_active
- Contrainte : symbol UNIQUE

### 2025_12_11_205639_create_portfolios_table.php
- Création de la table portfolios
- Relation 1:1 avec users (user_id UNIQUE)
- Relation N:1 avec crypto_currencies
- Index composites

### 2025_12_11_205639_create_transactions_table.php
- Création de la table transactions
- Relation N:1 avec portfolios
- Index pour optimiser les requêtes

### 2025_12_11_205640_create_notifications_table.php
- Création de la table notifications
- Relations avec users, portfolios, crypto_currencies
- Index composites pour performances

### 2025_12_11_205641_create_crypto_price_records_table.php
- Création de la table crypto_price_records
- Historique des prix
- Index pour requêtes temporelles

### 2026_01_10_222533_add_indexes_to_transactions_table.php
- Ajout d'index supplémentaires
- Optimisation des requêtes de calcul
```

**Action** : Lister toutes les migrations avec leur rôle

#### 2. Documenter l'intégrité des données

**Fichier à créer** : `DOCUMENTATION/INTEGRITE_DONNEES.md`

**Contenu à inclure** :

```markdown
# INTÉGRITÉ DES DONNÉES - BITCHEST

## Foreign Keys

### Table portfolios
- user_id → users.id (CASCADE DELETE, CASCADE UPDATE)
- crypto_currency_id → crypto_currencies.id (CASCADE DELETE)

### Table transactions
- portfolio_id → portfolios.id (CASCADE DELETE)

### Table notifications
- user_id → users.id (CASCADE DELETE)
- portfolio_id → portfolios.id (CASCADE DELETE, nullable)
- crypto_currency_id → crypto_currencies.id (SET NULL, nullable)

## Contraintes

### UNIQUE
- users.email : Un email par utilisateur
- portfolios.user_id : Un portfolio par utilisateur (relation 1:1)
- crypto_currencies.symbol : Un symbole par crypto

### NOT NULL
- Toutes les clés primaires
- Toutes les clés étrangères (sauf nullable)
- Colonnes critiques (name, email, password)

## Cascades

### CASCADE DELETE
- Suppression d'un user → Suppression de son portfolio
- Suppression d'un portfolio → Suppression de ses transactions
- Suppression d'une crypto → Suppression de ses portfolios

### SET NULL
- Suppression d'une crypto → crypto_currency_id dans notifications = NULL
```

**Action** : Analyser toutes les contraintes et les documenter

### 📝 Section C7 dans votre rapport académique :

```
CHAPITRE 7 : MISE EN PLACE DE LA BASE DE DONNÉES

7.1 Création de la base de données
- Migrations Laravel
- Schéma physique
- Contraintes d'intégrité
- Référence : DOCUMENTATION/MIGRATIONS.md

7.2 Intégrité des données
- Foreign keys
- Contraintes (UNIQUE, NOT NULL)
- Cascades (DELETE, UPDATE)
- Référence : DOCUMENTATION/INTEGRITE_DONNEES.md

7.3 Sécurité
- Utilisateurs et droits d'accès
- Chiffrement des données sensibles
- Authentification
- Référence : DOCUMENTATION/SECURITE_BDD.md

7.4 Maintenance
- Procédures de backup
- Procédures de restauration
- Scripts d'automatisation
- Référence : DOCUMENTATION/PROCEDURES_RESTAURATION.md
```

---

## C8. DÉVELOPPER DES COMPOSANTS DANS LE LANGAGE D'UNE BASE DE DONNÉES

### ✅ Actions à réaliser dans votre projet :

#### 1. Documenter les requêtes complexes

**Fichier à créer** : `DOCUMENTATION/REQUETES_COMPLEXES.md`

**Contenu à inclure** :

```markdown
# REQUÊTES COMPLEXES - BITCHEST

## Requête 1 : Calcul de la quantité totale possédée

### Contexte
Dans PortfolioService::getUserPortfolio()

### Requête
```php
$totalBuyQuantity = Transaction::where('portfolio_id', $portfolio->id)
    ->where('type', 'buy')
    ->sum('quantity');

$totalSellQuantity = Transaction::where('portfolio_id', $portfolio->id)
    ->where('type', 'sell')
    ->sum('quantity');

$quantity = $totalBuyQuantity - $totalSellQuantity;
```

### SQL Généré
```sql
SELECT SUM(quantity) FROM transactions 
WHERE portfolio_id = ? AND type = 'buy';

SELECT SUM(quantity) FROM transactions 
WHERE portfolio_id = ? AND type = 'sell';
```

### Optimisation
- Index sur (portfolio_id, type)
- Cache Redis pour éviter requêtes répétées

## Requête 2 : Calcul du coût total investi

### Contexte
Dans PortfolioService::getUserPortfolio()

### Requête
```php
$buyTransactions = Transaction::where('portfolio_id', $portfolio->id)
    ->where('type', 'buy')
    ->get();

$totalCost = $buyTransactions->sum(function ($tx) {
    return $tx->quantity * $tx->price_at_transaction;
});
```

### SQL Généré
```sql
SELECT * FROM transactions 
WHERE portfolio_id = ? AND type = 'buy';
```

### Optimisation
- Cache avec TTL 5 minutes
- Index sur portfolio_id et type

## Requête 3 : Historique des prix avec fenêtre temporelle

### Contexte
Dans CryptoService::getHistoricalPrices()

### Requête
```php
CryptoPriceRecord::where('crypto_currency_id', $cryptoId)
    ->where('recorded_at', '>=', Carbon::now()->subDays($days))
    ->orderBy('recorded_at')
    ->get(['recorded_at', 'price']);
```

### SQL Généré
```sql
SELECT recorded_at, price FROM crypto_price_records
WHERE crypto_currency_id = ? 
AND recorded_at >= ?
ORDER BY recorded_at ASC;
```

### Optimisation
- Index composite sur (crypto_currency_id, recorded_at)
```

**Action** : Identifier et documenter au moins 5 requêtes complexes

### 📝 Section C8 dans votre rapport académique :

```
CHAPITRE 8 : COMPOSANTS LANGAGE BASE DE DONNÉES

8.1 Requêtes complexes
- Exemples de requêtes avec jointures
- Requêtes avec agrégations
- Optimisations (index, cache)
- Référence : DOCUMENTATION/REQUETES_COMPLEXES.md

8.2 Gestion des exceptions
- Try-catch dans les services
- Handler global
- Messages d'erreur appropriés
- Référence : DOCUMENTATION/GESTION_EXCEPTIONS.md

8.3 Gestion des conflits
- Row locking (lockForUpdate)
- Transactions DB
- Gestion de la concurrence
- Référence : DOCUMENTATION/GESTION_CONFLITS.md

8.4 Validation
- Validation des entrées (Form Requests)
- Sanitization
- Contrôles de sécurité
- Référence : DOCUMENTATION/VALIDATION_ENTREES.md
```

---

## C9. COLLABORER À LA GESTION D'UN PROJET INFORMATIQUE

### ✅ Actions à réaliser dans votre projet :

#### 1. Documenter la gestion de projet

**Fichier à créer** : `DOCUMENTATION/GESTION_PROJET.md`

**Contenu à inclure** :

```markdown
# GESTION DE PROJET - BITCHEST

## Planning

### Phase 1 : Conception (Semaine 1-2)
- Analyse des besoins
- Création des maquettes
- Conception de la base de données

### Phase 2 : Développement Backend (Semaine 3-6)
- Configuration Laravel
- Création des migrations
- Développement des services
- API REST

### Phase 3 : Développement Frontend (Semaine 7-10)
- Configuration Vue.js
- Développement des composants
- Intégration API

### Phase 4 : Tests et Optimisations (Semaine 11-12)
- Tests unitaires
- Tests fonctionnels
- Optimisations performance

## Outils Utilisés

### Versioning
- Git
- Repository : [URL si disponible]

### Gestion des tâches
- [Outils utilisés : Trello, Jira, etc.]

### Communication
- [Méthodes de communication]
```

**Action** : Documenter votre processus de développement

### 📝 Section C9 dans votre rapport académique :

```
CHAPITRE 9 : GESTION DE PROJET

9.1 Organisation du projet
- Structure des dossiers
- Gestion des versions (Git)
- Documentation
- Référence : DOCUMENTATION/GESTION_PROJET.md

9.2 Procédures qualité
- Standards de code
- Tests
- Code review
- Référence : DOCUMENTATION/PROCEDURES_QUALITE.md

9.3 Environnement de développement
- Configuration
- Scripts d'automatisation
- Procédures de déploiement
- Référence : DOCUMENTATION/ENVIRONNEMENT_DEV.md

9.4 Collaboration
- Outils utilisés
- Communication
- Partage de connaissances
```

---

## C10. CONCEVOIR UNE APPLICATION

### ✅ Actions à réaliser dans votre projet :

#### 1. Documenter les cas d'utilisation

**Fichier à créer** : `DOCUMENTATION/CAS_UTILISATION.md`

**Contenu à inclure** :

```markdown
# CAS D'UTILISATION - BITCHEST

## Acteur : CLIENT

### CU1 : S'inscrire
**Description** : Un utilisateur crée un compte
**Préconditions** : Aucune
**Scénario principal** :
1. Utilisateur accède à la page d'inscription
2. Saisit nom, prénom, email
3. Système crée le compte
4. Système envoie mot de passe temporaire par email
5. Compte créé avec statut "pending"

**Postconditions** : Compte créé, email envoyé

### CU2 : Acheter une cryptomonnaie
**Description** : Un client achète des cryptos
**Préconditions** : Compte actif, solde suffisant
**Scénario principal** :
1. Client sélectionne une crypto
2. Saisit la quantité
3. Système vérifie le solde
4. Système crée la transaction
5. Système met à jour le portfolio
6. Système envoie une notification

**Postconditions** : Transaction créée, solde débité, portfolio mis à jour

[Continuer pour tous les cas d'utilisation]
```

**Action** : Documenter au moins 10 cas d'utilisation pour Client et 8 pour Admin

#### 2. Documenter l'architecture technique

**Fichier à créer** : `DOCUMENTATION/ARCHITECTURE_TECHNIQUE.md`

**Contenu à inclure** :

```markdown
# ARCHITECTURE TECHNIQUE - BITCHEST

## Architecture Globale

### Pattern : MVC (Model-View-Controller)
- **Modèle** : Eloquent ORM (Laravel)
- **Vue** : Vue.js 3 (Frontend)
- **Contrôleur** : Laravel Controllers

## Séparation des Couches

### Couche Présentation
- Vue.js 3 + TypeScript
- Composants réutilisables
- Gestion d'état (Pinia)

### Couche Application
- Laravel Controllers
- API REST
- Authentification (Sanctum)

### Couche Métier
- Services Laravel
- Logique métier
- Validation

### Couche Données
- Eloquent ORM
- MySQL
- Redis (Cache)

## Justification des Choix

### Laravel
- Framework mature et documenté
- Écosystème riche
- Sécurité intégrée

### Vue.js
- Framework progressif
- Courbe d'apprentissage douce
- Performance optimale

### MySQL
- SGBD relationnel robuste
- Support Laravel natif
- Performance éprouvée

### Redis
- Cache haute performance
- Réduction charge DB
- Temps de réponse < 5ms
```

**Action** : Justifier chaque choix architectural

### 📝 Section C10 dans votre rapport académique :

```
CHAPITRE 10 : CONCEPTION DE L'APPLICATION

10.1 Analyse des besoins
- Cas d'utilisation
- Scénarios utilisateur
- Exigences fonctionnelles
- Référence : DOCUMENTATION/CAS_UTILISATION.md
- Diagrammes : [Inclure diagrammes ChatUML]

10.2 Conception
- Architecture technique
- Diagrammes UML (classes, séquences, déploiement)
- Justification des choix
- Référence : DOCUMENTATION/ARCHITECTURE_TECHNIQUE.md

10.3 Sécurité
- Identification des risques
- Mesures de sécurité par couche
- Stratégie de sécurité
- Référence : DOCUMENTATION/BESOINS_SECURITE.md

10.4 Documentation
- Dossier de conception
- Spécifications techniques
- Diagrammes UML
- Référence : Dossier DOCUMENTATION/
```

---

## CHECKLIST FINALE AVANT RENDU

### Documentation à créer :

- [ ] DOCUMENTATION/MAQUETTE.md
- [ ] DOCUMENTATION/NAVIGATION.md
- [ ] DOCUMENTATION/CHARTE_GRAPHIQUE.md
- [ ] DOCUMENTATION/SECURITE_UI.md
- [ ] DOCUMENTATION/SERVICES_ACCES_DONNEES.md
- [ ] DOCUMENTATION/SECURITE_ACCES_DONNEES.md
- [ ] DOCUMENTATION/RESPONSIVE_DESIGN.md
- [ ] DOCUMENTATION/ARCHITECTURE_FRONTEND.md
- [ ] DOCUMENTATION/TESTS_FRONTEND.md
- [ ] DOCUMENTATION/ARCHITECTURE_BACKEND.md
- [ ] DOCUMENTATION/PRATIQUES_OOP.md
- [ ] DOCUMENTATION/MCD_BITCHEST.md
- [ ] DOCUMENTATION/MLD_BITCHEST.md
- [ ] DOCUMENTATION/NORMALISATION.md
- [ ] DOCUMENTATION/MIGRATIONS.md
- [ ] DOCUMENTATION/INTEGRITE_DONNEES.md
- [ ] DOCUMENTATION/REQUETES_COMPLEXES.md
- [ ] DOCUMENTATION/GESTION_PROJET.md
- [ ] DOCUMENTATION/CAS_UTILISATION.md
- [ ] DOCUMENTATION/ARCHITECTURE_TECHNIQUE.md

### Tests à créer :

- [ ] tests/Feature/TransactionServiceTest.php (5 tests minimum)
- [ ] tests/Feature/PortfolioServiceTest.php (3 tests minimum)
- [ ] tests/Feature/AuthTest.php (3 tests minimum)

### Diagrammes UML à créer :

- [ ] Diagramme de classes (modèles)
- [ ] Diagramme de classes (services)
- [ ] Diagramme de séquence (achat crypto)
- [ ] Diagramme de séquence (connexion)
- [ ] Diagramme de cas d'utilisation (client)
- [ ] Diagramme de cas d'utilisation (admin)
- [ ] Diagramme de déploiement

---

**Document créé le** : 2025-01-27  
**Version** : 1.0  
**Pour** : Rapport académique BitChest
