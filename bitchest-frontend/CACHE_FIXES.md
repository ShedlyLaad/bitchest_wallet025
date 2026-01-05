# Corrections du Cache et des Prix Crypto

## Modifications Apportées

### 1. TTL du Cache pour les Prix Crypto
- **Avant** : 30 secondes
- **Après** : **1 heure** (3600000 ms)
- Les prix sont maintenant mis en cache pendant 1 heure et rafraîchis automatiquement

### 2. Validation et Normalisation des Prix
- Les prix sont maintenant arrondis à **8 décimales** pour éviter les erreurs de précision
- Les valeurs `change24h` sont :
  - Arrondies à **2 décimales**
  - Limitées entre **-99% et +200%** pour éviter les valeurs aberrantes
  - Validées avant l'affichage

### 3. Rafraîchissement Automatique
- Le marché se rafraîchit automatiquement **toutes les heures**
- Le cache est invalidé après chaque transaction (buy/sell)
- Les données sont toujours à jour

### 4. Correction de l'Affichage des Pourcentages
- Les pourcentages sont maintenant correctement formatés
- Utilisation de `Math.abs()` pour éviter les doubles signes négatifs
- Validation des valeurs avant affichage

## Fonctionnement

### Cycle de Vie du Cache
1. **Premier chargement** : Les données sont récupérées depuis l'API et mises en cache
2. **Chargements suivants** : Les données sont affichées depuis le cache (< 100ms)
3. **Mise à jour en arrière-plan** : Les données fraîches sont chargées et le cache est mis à jour
4. **Après 1 heure** : Le cache expire et les nouvelles données sont récupérées depuis l'API
5. **Après transaction** : Le cache est invalidé pour forcer le rafraîchissement

### Invalidation du Cache
Le cache est automatiquement invalidé après :
- ✅ Achat de crypto (`buyCrypto`)
- ✅ Vente de crypto (`sellCrypto`)
- ✅ Expiration du TTL (1 heure)

## Correction des Pourcentages

### Problème Identifié
Les pourcentages affichés étaient parfois plus élevés que prévu (+5% et 11% au lieu des valeurs attendues).

### Solutions Appliquées
1. **Validation des valeurs** : Les valeurs sont limitées entre -99% et +200%
2. **Arrondi correct** : Tous les pourcentages sont arrondis à 2 décimales
3. **Normalisation** : Les valeurs sont normalisées avant l'affichage
4. **Cache propre** : Le cache est invalidé après les transactions pour éviter les données obsolètes

## Tests Recommandés

1. **Vérifier les prix** : Les prix doivent être cohérents avec Coinbase API
2. **Vérifier les pourcentages** : Les pourcentages doivent être réalistes (-99% à +200%)
3. **Vérifier le cache** : Les données doivent se rafraîchir après 1 heure
4. **Vérifier après transaction** : Les données doivent se mettre à jour immédiatement après buy/sell

## Notes Techniques

- Le backend calcule `change24h` en comparant le prix actuel avec le prix d'il y a 24h
- Les valeurs sont validées côté backend (limites -99% à +200%)
- Le frontend normalise et arrondit les valeurs avant l'affichage
- Le cache utilise localStorage pour persistance entre les sessions

