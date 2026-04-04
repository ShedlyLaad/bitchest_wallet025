# Configuration Redis pour Optimisation des Transactions

## Vue d'ensemble

Ce projet utilise Redis pour optimiser les performances des transactions et afficher les données instantanément. Redis est configuré via Docker Compose.

## Configuration Docker

Redis est déjà configuré dans `docker-compose.yml`. Pour démarrer Redis :

```bash
docker-compose up -d redis
```

Vérifier que Redis fonctionne :
```bash
docker ps | grep redis
redis-cli ping
# Devrait répondre : PONG
```

## Configuration Laravel (.env)

Ajoutez ou modifiez les variables suivantes dans votre fichier `.env` :

```env
# Cache Driver - IMPORTANT : Utiliser Redis
CACHE_DRIVER=redis

# Redis Configuration
REDIS_CLIENT=predis
REDIS_HOST=127.0.0.1
REDIS_PORT=6379
REDIS_PASSWORD=null
REDIS_DB=0
REDIS_CACHE_DB=1
```

## Optimisations Implémentées

### 1. Cache des Quantités de Transactions
- Les quantités totales (buy/sell) sont mises en cache pendant 5 minutes
- Cache automatiquement invalidé lors de nouvelles transactions
- **Gain de performance** : Requêtes SQL lentes remplacées par lecture Redis instantanée

### 2. Cache de l'Historique des Transactions
- L'historique paginé est mis en cache pendant 2 minutes
- Cache par utilisateur, page et nombre d'éléments par page
- **Gain de performance** : Affichage instantané de l'historique

### 3. Cache des Portfolios
- Calculs de coûts totaux et détails d'achat mis en cache
- Cache de 5 minutes
- **Gain de performance** : Évite les recalculs répétés

### 4. Index de Base de Données
- Index composite sur `(portfolio_id, type)` pour optimiser les requêtes
- Index sur `created_at` pour le tri rapide
- Migration disponible : `2026_01_10_222533_add_indexes_to_transactions_table.php`

## Exécution de la Migration

Pour ajouter les index à la table transactions :

```bash
cd bitchest-backend
php artisan migrate
```

## Vérification de la Configuration

Testez la connexion Redis :

```bash
php artisan tinker
>>> Cache::store('redis')->put('test', 'value', 60);
>>> Cache::store('redis')->get('test');
=> "value"
```

## Dépannage

### Redis ne se connecte pas
1. Vérifier que Docker est démarré : `docker ps`
2. Vérifier le port 6379 : `netstat -an | grep 6379`
3. Vérifier les variables REDIS_* dans `.env`
4. Vérifier que `CACHE_DRIVER=redis` dans `.env`

### Le cache ne se met pas à jour
- Le cache est automatiquement invalidé lors de nouvelles transactions
- Si nécessaire, vider le cache manuellement : `php artisan cache:clear`

### Performance toujours lente
1. Vérifier que Redis fonctionne : `redis-cli ping`
2. Vérifier que `CACHE_DRIVER=redis` dans `.env`
3. Vérifier que les index sont créés : `php artisan migrate:status`
4. Vérifier les logs : `storage/logs/laravel.log`

## Commandes Utiles

```bash
# Vider le cache Redis
php artisan cache:clear

# Voir les clés Redis (nécessite redis-cli)
redis-cli
> KEYS transaction:*

# Voir l'état des migrations
php artisan migrate:status

# Redémarrer Redis
docker-compose restart redis
```
