# MIGRATIONS OPTIMISÉES - BITCHEST
## Relation 1:1 User-Portfolio et Optimisations

---

## CHANGEMENTS PRINCIPAUX

### 1. Relation User-Portfolio : 1:1 au lieu de 1:N

**Avant** : Un utilisateur pouvait avoir plusieurs portfolios (un par crypto)
**Après** : Un utilisateur a UN SEUL portfolio qui contient toutes ses cryptos

**Impact** :
- Simplification de la logique métier
- Meilleure performance (moins de requêtes)
- Structure plus cohérente avec le concept de "portfolio utilisateur"

### 2. Optimisations ajoutées

- Index sur colonnes fréquemment utilisées
- Contraintes d'intégrité renforcées
- Optimisation des types de données
- Index composites pour requêtes complexes

---

## MIGRATION OPTIMISÉE : PORTFOLIOS

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('portfolios', function (Blueprint $table) {
            $table->id();
            
            // Relation 1:1 avec User - UNIQUE pour garantir un seul portfolio par user
            $table->foreignId('user_id')
                ->unique()  // ← AJOUT : Contrainte UNIQUE pour relation 1:1
                ->constrained('users')
                ->onDelete('cascade')
                ->onUpdate('cascade');
            
            // Relation N:1 avec CryptoCurrency
            $table->foreignId('crypto_currency_id')
                ->constrained('crypto_currencies')
                ->onDelete('cascade')
                ->onUpdate('cascade');
            
            // Valeur totale investie en crypto (en euros)
            $table->decimal('total_crypto_value', 18, 8)
                ->default(0)
                ->comment('Valeur totale investie pour cette crypto dans le portfolio');
            
            $table->timestamps();
            
            // Index composites pour optimiser les requêtes fréquentes
            $table->index(['user_id', 'crypto_currency_id'], 'idx_portfolio_user_crypto');
            $table->index('user_id', 'idx_portfolio_user');
            $table->index('crypto_currency_id', 'idx_portfolio_crypto');
            
            // Contrainte unique composite : un user ne peut avoir qu'UN portfolio par crypto
            // Mais avec la relation 1:1, on pourrait aussi avoir UN portfolio global
            // Ici on garde la possibilité d'avoir plusieurs lignes (une par crypto)
            // mais avec user_id UNIQUE, on force un seul portfolio global par user
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('portfolios');
    }
};
```

**Note importante** : Si vous voulez vraiment UN SEUL portfolio par user (qui contient toutes les cryptos), il faudrait restructurer différemment. La solution actuelle permet un portfolio par crypto par user, mais avec `user_id UNIQUE`, cela crée une contrainte.

**Alternative pour vrai 1:1** : Créer une table `user_portfolios` avec juste `user_id` et `total_portfolio_value`, puis une table `portfolio_items` pour les cryptos.

---

## MIGRATION OPTIMISÉE : USERS

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            
            // Informations personnelles
            $table->string('name', 255);
            $table->string('first_name', 100)->nullable();
            $table->string('last_name', 100)->nullable();
            
            // Authentification
            $table->string('email', 255)->unique();
            $table->string('phone', 20)->nullable();
            $table->string('password', 255); // Hashé avec bcrypt (60 caractères)
            $table->boolean('must_change_password')->default(false);
            $table->rememberToken();
            
            // Rôles et statuts
            $table->enum('role', ['admin', 'client'])
                ->default('client')
                ->comment('Rôle de l\'utilisateur dans l\'application');
            
            $table->enum('status', ['pending', 'pending_validation', 'active', 'blocked'])
                ->default('pending')
                ->comment('Statut du compte utilisateur');
            
            $table->timestamp('email_verified_at')->nullable();
            
            // Données financières
            $table->decimal('euro_balance', 12, 2)
                ->default(0)
                ->comment('Solde en euros de l\'utilisateur');
            
            // Système de niveaux
            $table->integer('level')
                ->default(1)
                ->comment('Niveau de l\'utilisateur (gamification)');
            
            $table->integer('experience_points')
                ->default(0)
                ->comment('Points d\'expérience pour monter de niveau');
            
            // Profil utilisateur
            $table->string('profile_picture', 255)->nullable();
            $table->string('profile_banner', 255)->nullable();
            
            $table->timestamps();
            
            // Index pour optimiser les requêtes fréquentes
            $table->index('email', 'idx_user_email');
            $table->index('role', 'idx_user_role');
            $table->index('status', 'idx_user_status');
            $table->index(['role', 'status'], 'idx_user_role_status');
            $table->index('created_at', 'idx_user_created');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
```

---

## MIGRATION OPTIMISÉE : TRANSACTIONS

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            
            // Relation avec Portfolio
            $table->foreignId('portfolio_id')
                ->constrained('portfolios')
                ->onDelete('cascade')
                ->onUpdate('cascade');
            
            // Type de transaction
            $table->enum('type', ['buy', 'sell'])
                ->comment('Type de transaction : achat ou vente');
            
            // Détails de la transaction
            $table->decimal('quantity', 18, 8)
                ->comment('Quantité de crypto achetée/vendue');
            
            $table->decimal('price_at_transaction', 18, 8)
                ->comment('Prix de la crypto au moment de la transaction');
            
            $table->decimal('euro_amount', 18, 2)
                ->comment('Montant en euros de la transaction');
            
            $table->timestamps();
            
            // Index pour optimiser les requêtes fréquentes
            $table->index('portfolio_id', 'idx_transaction_portfolio');
            $table->index('type', 'idx_transaction_type');
            $table->index('created_at', 'idx_transaction_created');
            $table->index(['portfolio_id', 'type'], 'idx_transaction_portfolio_type');
            $table->index(['portfolio_id', 'created_at'], 'idx_transaction_portfolio_created');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
```

---

## MIGRATION OPTIMISÉE : NOTIFICATIONS

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            
            // Relation avec User (obligatoire)
            $table->foreignId('user_id')
                ->constrained('users')
                ->onDelete('cascade')
                ->onUpdate('cascade');
            
            // Relation avec Portfolio (optionnelle)
            $table->foreignId('portfolio_id')
                ->nullable()
                ->constrained('portfolios')
                ->onDelete('cascade')
                ->onUpdate('cascade');
            
            // Relation avec CryptoCurrency (optionnelle)
            $table->foreignId('crypto_currency_id')
                ->nullable()
                ->constrained('crypto_currencies')
                ->onDelete('set null')  // Garder la notification même si crypto supprimée
                ->onUpdate('cascade');
            
            // Type de notification
            $table->enum('type', [
                'profit',           // Profit réalisé
                'loss',             // Perte réalisée
                'price_alert',      // Alerte de prix
                'portfolio_update', // Mise à jour portfolio
                'level_up'          // Montée de niveau
            ])->default('portfolio_update');
            
            // Contenu de la notification
            $table->string('title', 255);
            $table->text('message');
            $table->string('crypto_symbol', 10)->nullable();
            
            // Données financières (optionnelles)
            $table->decimal('gain_loss', 18, 8)->nullable()
                ->comment('Gain ou perte en euros');
            
            $table->decimal('gain_loss_percent', 10, 2)->nullable()
                ->comment('Gain ou perte en pourcentage');
            
            $table->decimal('current_price', 18, 8)->nullable()
                ->comment('Prix actuel de la crypto');
            
            $table->decimal('previous_price', 18, 8)->nullable()
                ->comment('Prix précédent de la crypto');
            
            // Données de niveau (pour notifications level_up)
            $table->integer('level')->nullable()
                ->comment('Niveau atteint');
            
            $table->string('level_name', 50)->nullable()
                ->comment('Nom du niveau atteint');
            
            // État de lecture
            $table->boolean('is_read')->default(false);
            $table->timestamp('read_at')->nullable();
            
            $table->timestamps();
            
            // Index composites pour optimiser les requêtes fréquentes
            $table->index(['user_id', 'is_read'], 'idx_notification_user_read');
            $table->index(['user_id', 'created_at'], 'idx_notification_user_created');
            $table->index(['user_id', 'type'], 'idx_notification_user_type');
            $table->index('portfolio_id', 'idx_notification_portfolio');
            $table->index('crypto_currency_id', 'idx_notification_crypto');
            $table->index('created_at', 'idx_notification_created');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
```

---

## MIGRATION OPTIMISÉE : CRYPTO_CURRENCIES

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('crypto_currencies', function (Blueprint $table) {
            $table->id();
            
            $table->string('name', 100)
                ->comment('Nom complet de la cryptomonnaie');
            
            $table->string('symbol', 10)
                ->unique()
                ->comment('Symbole de la cryptomonnaie (ex: BTC, ETH)');
            
            $table->boolean('is_active')
                ->default(true)
                ->comment('Indique si la crypto est active et tradable');
            
            $table->timestamps();
            
            // Index pour optimiser les recherches
            $table->index('symbol', 'idx_crypto_symbol');
            $table->index('is_active', 'idx_crypto_active');
            $table->index(['is_active', 'symbol'], 'idx_crypto_active_symbol');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('crypto_currencies');
    }
};
```

---

## MIGRATION OPTIMISÉE : CRYPTO_PRICE_RECORDS

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('crypto_price_records', function (Blueprint $table) {
            $table->id();
            
            // Relation avec CryptoCurrency
            $table->foreignId('crypto_currency_id')
                ->constrained('crypto_currencies')
                ->onDelete('cascade')
                ->onUpdate('cascade');
            
            // Prix enregistré
            $table->decimal('price', 18, 8)
                ->comment('Prix de la crypto à un moment donné');
            
            // Date d'enregistrement du prix
            $table->timestamp('recorded_at')
                ->comment('Date et heure d\'enregistrement du prix');
            
            $table->timestamps();
            
            // Index composites pour optimiser les requêtes historiques
            $table->index(['crypto_currency_id', 'recorded_at'], 'idx_price_crypto_recorded');
            $table->index('recorded_at', 'idx_price_recorded');
            $table->index('crypto_currency_id', 'idx_price_crypto');
            
            // Index pour requêtes de recherche par date
            $table->index(['crypto_currency_id', 'recorded_at', 'price'], 'idx_price_crypto_recorded_price');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('crypto_price_records');
    }
};
```

---

## MIGRATION : INDEX SUR TRANSACTIONS (Optimisation existante)

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            // Index supplémentaires pour optimiser les requêtes de calcul
            // Ces index sont déjà créés dans la migration optimisée ci-dessus
            // Cette migration peut être supprimée si vous utilisez la version optimisée
            
            // Index pour requêtes de somme par portfolio et type
            if (!$this->hasIndex('transactions', 'idx_transaction_portfolio_type')) {
                $table->index(['portfolio_id', 'type'], 'idx_transaction_portfolio_type');
            }
            
            // Index pour requêtes triées par date
            if (!$this->hasIndex('transactions', 'idx_transaction_portfolio_created')) {
                $table->index(['portfolio_id', 'created_at'], 'idx_transaction_portfolio_created');
            }
        });
    }

    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropIndex('idx_transaction_portfolio_type');
            $table->dropIndex('idx_transaction_portfolio_created');
        });
    }
    
    private function hasIndex($table, $index): bool
    {
        $connection = Schema::getConnection();
        $doctrineSchemaManager = $connection->getDoctrineSchemaManager();
        $doctrineTable = $doctrineSchemaManager->listTableDetails($table);
        return $doctrineTable->hasIndex($index);
    }
};
```

---

## RÉSUMÉ DES OPTIMISATIONS

### 1. Index ajoutés

**Table users** :
- `idx_user_email` : Recherche par email
- `idx_user_role` : Filtrage par rôle
- `idx_user_status` : Filtrage par statut
- `idx_user_role_status` : Filtrage combiné
- `idx_user_created` : Tri par date de création

**Table portfolios** :
- `idx_portfolio_user_crypto` : Recherche par user et crypto
- `idx_portfolio_user` : Recherche par user
- `idx_portfolio_crypto` : Recherche par crypto

**Table transactions** :
- `idx_transaction_portfolio` : Recherche par portfolio
- `idx_transaction_type` : Filtrage par type
- `idx_transaction_created` : Tri par date
- `idx_transaction_portfolio_type` : Recherche combinée
- `idx_transaction_portfolio_created` : Tri par portfolio et date

**Table notifications** :
- `idx_notification_user_read` : Notifications non lues par user
- `idx_notification_user_created` : Tri par user et date
- `idx_notification_user_type` : Filtrage par user et type
- `idx_notification_portfolio` : Recherche par portfolio
- `idx_notification_crypto` : Recherche par crypto
- `idx_notification_created` : Tri par date

**Table crypto_currencies** :
- `idx_crypto_symbol` : Recherche par symbole
- `idx_crypto_active` : Filtrage par statut actif
- `idx_crypto_active_symbol` : Recherche combinée

**Table crypto_price_records** :
- `idx_price_crypto_recorded` : Historique par crypto et date
- `idx_price_recorded` : Tri par date
- `idx_price_crypto` : Recherche par crypto
- `idx_price_crypto_recorded_price` : Requêtes complexes

### 2. Contraintes renforcées

- `user_id UNIQUE` dans portfolios pour relation 1:1
- `onUpdate('cascade')` sur toutes les foreign keys
- Commentaires sur toutes les colonnes importantes

### 3. Types de données optimisés

- Longueurs de chaînes définies explicitement
- Commentaires sur toutes les colonnes
- Valeurs par défaut appropriées

---

## INSTRUCTIONS D'APPLICATION

### Option 1 : Nouvelle installation

1. Remplacez les fichiers de migration existants par les versions optimisées
2. Exécutez `php artisan migrate:fresh --seed`

### Option 2 : Migration progressive

1. Créez une nouvelle migration pour ajouter la contrainte UNIQUE :
```php
php artisan make:migration add_unique_constraint_to_portfolios_user_id
```

2. Dans cette migration :
```php
Schema::table('portfolios', function (Blueprint $table) {
    $table->unique('user_id');
});
```

3. Créez des migrations pour ajouter les index manquants

4. Exécutez les migrations

---

**Document créé le** : 2025-01-27  
**Version** : 1.0
