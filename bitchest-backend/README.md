# BitChest Backend

Backend Laravel pour la plateforme de trading de cryptomonnaies BitChest.

## Architecture

### Performance et Cache
- **Redis** : Cache ultra-rapide pour les prix crypto (< 5ms)
- **Fallback automatique** : Si Redis est vide, récupération depuis la DB
- **Coinbase API** : Prix en temps réel avec calcul dynamique de `change24h`

### Calcul dynamique du change24h

Le pourcentage de changement sur 24h (`change24h`) est calculé dynamiquement :

- ✅ **Peut être 0%** si :
  - Pas encore de données historiques (base récemment créée)
  - Le prix n'a pas changé depuis 24h
  - Moins de 6h d'historique disponible

- ✅ **S'ajuste automatiquement** avec :
  - Chaque rafraîchissement des prix
  - Le temps qui passe (cherche le prix le plus proche de 24h dans une fenêtre 12h-48h)
  - Les nouvelles données enregistrées dans l'historique

- ✅ **Valeurs limitées** entre -99% et +200% pour éviter les valeurs extrêmes

### Commandes principales

```bash
# Mettre à jour les prix depuis Coinbase API
php artisan crypto:update-prices

# Initialiser Redis depuis la DB
php artisan redis:init-prices

# Démarrer le worker de queue
php artisan queue:work redis --tries=3

# Démarrer le scheduler
php artisan schedule:work
```

### Configuration

Assurez-vous que votre `.env` contient :
```env
REDIS_CLIENT=predis
QUEUE_CONNECTION=redis
BROADCAST_DRIVER=redis
```

## Structure

- `app/Services/RedisPriceService.php` : Gestion du cache Redis avec fallback DB
- `app/Console/Commands/UpdateCryptoPricesCommand.php` : Mise à jour des prix depuis Coinbase API
- `app/Http/Controllers/Client/CryptoMarketController.php` : API publique pour le marché
- `app/Http/Controllers/Admin/CryptoController.php` : API admin pour le marché

## Logique Redis ↔ Database

1. **Lecture** : Redis (ultra-rapide) → Fallback DB si Redis vide
2. **Écriture** : Redis + DB (historique) lors de la mise à jour des prix
3. **Calcul change24h** : Depuis l'historique DB, mis à jour à chaque refresh
