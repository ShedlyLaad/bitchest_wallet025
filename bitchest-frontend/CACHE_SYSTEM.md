# Système de Cache Intelligent

## Vue d'ensemble

Le système de cache a été implémenté pour **accélérer considérablement l'affichage des données** sur toutes les pages (Trade, Portfolio, Dashboard, etc.). Les données sont maintenant affichées **instantanément** depuis le cache local, puis mises à jour en arrière-plan.

## Fonctionnalités

### ✅ Cache Intelligent
- **localStorage** : Pour les données persistantes (portfolio, transactions, etc.)
- **sessionStorage** : Pour les données temporaires et sensibles
- **TTL automatique** : Chaque type de données a un temps de vie optimal
- **Nettoyage automatique** : Les entrées expirées sont supprimées automatiquement

### ✅ Optimistic UI
- Les données en cache sont affichées **immédiatement**
- Les données fraîches sont chargées en **arrière-plan**
- L'utilisateur voit les données **instantanément** sans attendre l'API

### ✅ Préchargement
- Les données critiques sont préchargées au démarrage de l'application
- Préchargement automatique après connexion
- Préchargement lors de la navigation vers les pages importantes

### ✅ Invalidation Intelligente
- Les caches sont invalidés automatiquement après les actions (buy/sell)
- Mise à jour en temps réel des données affectées

## Temps de Cache par Type de Données

| Type de Données | TTL | Raison |
|----------------|-----|--------|
| Market / Cryptos | 30 secondes | Données très dynamiques |
| Portfolio | 1 minute | Change modérément |
| Transactions | 2 minutes | Change peu souvent |
| Notifications | 10 secondes | Très dynamique |
| Dashboard | 1 minute | Statistiques |
| User / Profile | 5 minutes | Données stables |

## Utilisation

### Dans les Composants Vue

#### Option 1 : Utiliser directement les fonctions API (Recommandé)

Les fonctions API utilisent automatiquement le cache :

```typescript
import { getPortfolio, getUserCryptos } from '@/services/api';

// Utilise automatiquement le cache
const portfolio = await getPortfolio();

// Forcer le rafraîchissement
const freshPortfolio = await getPortfolio(false);
```

#### Option 2 : Utiliser le Composable `useCachedData`

Pour plus de contrôle :

```vue
<script setup lang="ts">
import { useCachedData } from '@/composables/useCachedData';
import { getPortfolio } from '@/services/api';

const { data, loading, error, fromCache, refresh } = useCachedData(
  'portfolio',
  () => getPortfolio(false),
  { ttl: 60 * 1000 } // 1 minute
);
</script>

<template>
  <div v-if="loading && !data">Chargement...</div>
  <div v-else-if="data">
    <span v-if="fromCache" class="text-xs text-gray-400">(depuis le cache)</span>
    <!-- Afficher les données -->
  </div>
</template>
```

## Fonctions API avec Cache

Toutes ces fonctions utilisent automatiquement le cache :

- ✅ `getPortfolio()` - Portfolio de l'utilisateur
- ✅ `getMarket()` - Marché des cryptos
- ✅ `getUserCryptos()` - Cryptos disponibles pour trading
- ✅ `getMarketHistory(symbol)` - Historique d'une crypto
- ✅ `getTransactionHistory()` - Historique des transactions
- ✅ `getNotifications()` - Notifications
- ✅ `getUnreadNotificationsCount()` - Nombre de notifications non lues
- ✅ `getAdminDashboard()` - Dashboard admin

## Invalidation du Cache

Le cache est automatiquement invalidé après :

- ✅ Achat de crypto (`buyCrypto`)
- ✅ Vente de crypto (`sellCrypto`)
- ✅ Actions qui modifient les données

Pour invalider manuellement :

```typescript
import { cacheService } from '@/services/cacheService';

// Supprimer une clé spécifique
cacheService.remove('portfolio');

// Vider tout le cache
cacheService.clear();
```

## Préchargement

Le préchargement se fait automatiquement :

1. **Au démarrage** : Si l'utilisateur est déjà connecté
2. **Après connexion** : Les données critiques sont préchargées
3. **Lors de la navigation** : Vers les pages importantes (dashboard, trade, portfolio)

## Performance

### Avant
- ⏱️ Temps d'affichage : **2-5 secondes** (attente de l'API)
- 🔄 Appels API : **À chaque chargement de page**
- 😞 Expérience utilisateur : **Lente et frustrante**

### Après
- ⚡ Temps d'affichage : **< 100ms** (depuis le cache)
- 🔄 Appels API : **Seulement si nécessaire** (cache expiré ou données manquantes)
- 😊 Expérience utilisateur : **Instantanée et fluide**

## Dépannage

### Le cache ne fonctionne pas
1. Vérifier que `localStorage` est disponible (navigateur moderne)
2. Vérifier la console pour les erreurs
3. Vérifier que les données ne sont pas expirées

### Forcer le rafraîchissement
```typescript
// Passer `false` comme deuxième paramètre
const freshData = await getPortfolio(false);
```

### Vider le cache
```typescript
import { cacheService } from '@/services/cacheService';
cacheService.clear();
```

## Notes Techniques

- Le cache utilise `JSON.stringify/parse` pour la sérialisation
- Les erreurs de cache sont gérées gracieusement (fallback sur API)
- Le nettoyage automatique se fait toutes les 10 minutes
- Le cache est limité par la taille du localStorage (généralement 5-10MB)

