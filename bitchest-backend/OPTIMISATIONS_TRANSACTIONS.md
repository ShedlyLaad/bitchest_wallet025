# Optimisations des Transactions avec Redis

## Résumé des Optimisations

Ce document décrit les optimisations apportées au système de transactions pour améliorer les performances et permettre un affichage instantané des données.

## Problèmes Résolus

1. **Requêtes SQL lentes** sur les calculs de quantité (sum sur transactions)
2. **Affichage lent de l'historique** des transactions
3. **Recalculs répétés** des portfolios et coûts totaux
4. **Absence d'index** sur les colonnes fréquemment utilisées

## Solutions Implémentées

### 1. Migration de Base de Données : Index
**Fichier** : `database/migrations/2026_01_10_222533_add_indexes_to_transactions_table.php`

Ajout de 3 index pour optimiser les requêtes :
- Index composite sur `(portfolio_id, type)` pour les calculs de quantité
- Index sur `created_at` pour le tri rapide
- Index sur `portfolio_id` pour les jointures

**À exécuter** :
```bash
php artisan migrate
```

### 2. Modèle Transaction : Méthodes de Cache
**Fichier** : `app/Models/Transaction.php`

Nouvelles méthodes :
- `getCachedQuantity(int $portfolioId, string $type): float` - Récupère la quantité avec cache Redis (5 min TTL)
- `invalidatePortfolioCache(int $portfolioId): void` - Invalide le cache d'un portfolio
- `invalidateUserCache(int $userId): void` - Invalide le cache d'un utilisateur

Le hook `booted()` invalide automatiquement le cache lors de nouvelles transactions.

### 3. TransactionService : Optimisation avec Redis
**Fichier** : `app/Services/TransactionService.php`

**Avant** :
```php
$totalBuyQuantity = Transaction::where('portfolio_id', $portfolio->id)
    ->where('type', 'buy')
    ->sum('quantity'); // Requête SQL lente
```

**Après** :
```php
$totalBuyQuantity = Transaction::getCachedQuantity($portfolio->id, 'buy');
// Cache Redis instantané, invalidé automatiquement lors de nouvelles transactions
```

### 4. TransactionController : Cache de l'Historique
**Fichier** : `app/Http/Controllers/Client/TransactionController.php`

L'historique des transactions est maintenant mis en cache pendant 2 minutes :
- Cache par utilisateur, page et nombre d'éléments par page
- Invalidation automatique lors de nouvelles transactions
- Affichage instantané pour les utilisateurs

### 5. PortfolioService : Cache des Calculs
**Fichier** : `app/Services/PortfolioService.php`

Optimisations :
- Cache des quantités (buy/sell) avec `getCachedQuantity()`
- Cache du coût total des achats (5 min TTL)
- Cache du nombre de transactions d'achat
- Cache des détails d'achat pour les portfolios

**Invalidation automatique** lors de :
- Nouvelles transactions
- Mises à jour de portfolio

## Configuration Requise

### 1. Docker Redis
Redis est configuré dans `docker-compose.yml`. Démarrer avec :
```bash
docker-compose up -d redis
```

### 2. Variables d'Environnement (.env)
```env
CACHE_DRIVER=redis
REDIS_CLIENT=predis
REDIS_HOST=127.0.0.1
REDIS_PORT=6379
REDIS_DB=0
REDIS_CACHE_DB=1
```

### 3. Migration
```bash
php artisan migrate
```

## Performances Attendues

### Avant Optimisation
- Calcul de quantité : ~200-500ms (requête SQL avec sum)
- Affichage historique : ~300-800ms (requête avec jointures)
- Calcul portfolio : ~400-1000ms (multiples requêtes)

### Après Optimisation
- Calcul de quantité : **<5ms** (lecture Redis)
- Affichage historique : **<10ms** (cache Redis)
- Calcul portfolio : **<50ms** (cache Redis + requêtes optimisées)

**Gain de performance** : **10x à 100x plus rapide** selon les cas

## Gestion du Cache

### Invalidation Automatique
Le cache est automatiquement invalidé lors de :
- Création d'une nouvelle transaction
- Modification d'un portfolio
- Tout changement affectant les calculs

### Invalidation Manuelle
Si nécessaire, vider le cache :
```bash
php artisan cache:clear
```

## Vérification

### Tester Redis
```bash
redis-cli ping
# Devrait répondre : PONG
```

### Tester le Cache dans Laravel
```bash
php artisan tinker
>>> Cache::store('redis')->put('test', 'value', 60);
>>> Cache::store('redis')->get('test');
=> "value"
```

### Vérifier les Index
```sql
SHOW INDEX FROM transactions;
-- Devrait afficher les nouveaux index
```

## Fichiers Modifiés

1. ✅ `database/migrations/2026_01_10_222533_add_indexes_to_transactions_table.php` (nouveau)
2. ✅ `app/Models/Transaction.php` (méthodes de cache ajoutées)
3. ✅ `app/Services/TransactionService.php` (utilisation du cache)
4. ✅ `app/Http/Controllers/Client/TransactionController.php` (cache historique)
5. ✅ `app/Services/PortfolioService.php` (cache des calculs)

## Prochaines Étapes

1. **Exécuter la migration** pour ajouter les index
2. **Configurer Redis** dans `.env` si pas déjà fait
3. **Démarrer Redis** via Docker
4. **Tester** les performances sur l'environnement de développement

## Support

Pour plus d'informations, voir :
- `REDIS_SETUP.md` - Guide de configuration Redis
- Logs Laravel : `storage/logs/laravel.log`
- Documentation Laravel Cache : https://laravel.com/docs/cache
