# GUIDE DES MIGRATIONS - SÉCURITÉ DES DONNÉES

## ⚠️ RÉPONSE RAPIDE

### ✅ `php artisan migrate` → **NE SUPPRIME PAS** vos données
Cette commande exécute **uniquement** les migrations qui n'ont pas encore été exécutées. Vos données existantes sont **PRÉSERVÉES**.

### ❌ `php artisan migrate:fresh` → **SUPPRIME TOUT**
Cette commande **supprime toutes les tables** puis recrée tout. **TOUTES VOS DONNÉES SERONT PERDUES**.

### ⚠️ `php artisan migrate:refresh` → **PEUT SUPPRIMER** des données
Cette commande fait un rollback puis re-migrate. Si vos migrations `down()` suppriment des tables, vous perdrez des données.

---

## 📋 DÉTAILS DES COMMANDES

### 1. `php artisan migrate` ✅ SÉCURISÉ

**Comportement :**
- ✅ Exécute **uniquement** les migrations non encore exécutées
- ✅ **Préserve** toutes vos données existantes
- ✅ Ajoute seulement les nouvelles tables/colonnes/index
- ✅ Met à jour la table `migrations` pour suivre l'état

**Quand utiliser :**
- Après avoir ajouté de nouvelles migrations
- Pour appliquer les optimisations (index, nouvelles colonnes)
- En production (sans risque de perte de données)

**Exemple :**
```bash
# Si vous avez déjà exécuté les migrations précédentes
# et que vous ajoutez seulement des index ou nouvelles colonnes
php artisan migrate
# → Vos données sont SÉCURISÉES ✅
```

---

### 2. `php artisan migrate:fresh` ❌ DANGEREUX

**Comportement :**
- ❌ **Supprime TOUTES les tables** de la base de données
- ❌ **Supprime TOUTES les données** (utilisateurs, transactions, portfolios, etc.)
- ✅ Recrée toutes les tables depuis zéro
- ⚠️ **TOUTES VOS DONNÉES SERONT PERDUES**

**Quand utiliser :**
- ✅ En développement uniquement
- ✅ Pour repartir de zéro
- ❌ **JAMAIS en production** (sauf si vous avez une sauvegarde)

**Exemple :**
```bash
php artisan migrate:fresh
# ⚠️ ATTENTION : Toutes vos données seront supprimées !
```

---

### 3. `php artisan migrate:refresh` ⚠️ ATTENTION

**Comportement :**
- ⚠️ Fait un **rollback** de toutes les migrations (exécute les méthodes `down()`)
- ⚠️ Puis **re-migrate** tout (exécute les méthodes `up()`)
- ⚠️ Si vos migrations `down()` suppriment des tables, **vous perdrez des données**

**Quand utiliser :**
- ⚠️ En développement uniquement
- ⚠️ Si vous avez modifié des migrations existantes
- ❌ **JAMAIS en production** sans sauvegarde

**Exemple :**
```bash
php artisan migrate:refresh
# ⚠️ Peut supprimer des données selon les méthodes down()
```

---

### 4. `php artisan migrate:rollback` ⚠️ ATTENTION

**Comportement :**
- ⚠️ Annule la **dernière** migration (exécute la méthode `down()`)
- ⚠️ Si `down()` supprime une table, **vous perdrez les données** de cette table

**Quand utiliser :**
- ⚠️ Pour annuler une migration récente en développement
- ❌ **JAMAIS en production** sans sauvegarde

---

## 🔍 VÉRIFIER L'ÉTAT ACTUEL

### Voir quelles migrations ont été exécutées

```bash
php artisan migrate:status
```

**Résultat attendu :**
```
+------+----------------------------------------------------+-------+
| Ran? | Migration                                          | Batch |
+------+----------------------------------------------------+-------+
| Yes  | 2014_10_12_000000_create_users_table              | 1     |
| Yes  | 2025_12_11_205634_create_crypto_currencies_table  | 1     |
| Yes  | 2025_12_11_205639_create_portfolios_table          | 1     |
| ...  | ...                                                | ...   |
+------+----------------------------------------------------+-------+
```

### Voir les migrations en attente

```bash
php artisan migrate:status
# Les migrations avec "No" n'ont pas encore été exécutées
```

---

## ✅ APPLIQUER LES OPTIMISATIONS SANS PERDRE DE DONNÉES

### Scénario : Vous avez déjà une base de données avec des données

**Étape 1 : Sauvegarder votre base de données**
```bash
# Windows (XAMPP)
mysqldump -u root -p bitchest_db > backup_$(date +%Y%m%d_%H%M%S).sql

# Ou utiliser phpMyAdmin pour exporter
```

**Étape 2 : Vérifier l'état actuel**
```bash
php artisan migrate:status
```

**Étape 3 : Appliquer les nouvelles migrations**
```bash
# Cette commande est SÉCURISÉE - elle n'efface rien
php artisan migrate
```

**Ce qui va se passer :**
- ✅ Si une table existe déjà → **Rien ne se passe** (pas d'erreur)
- ✅ Si une table n'existe pas → **Elle sera créée**
- ✅ Si des index manquent → **Ils seront ajoutés**
- ✅ **Vos données existantes sont préservées**

---

## 🔧 CAS SPÉCIFIQUES

### Cas 1 : Vous avez déjà exécuté les migrations de base

**Situation :**
- Vous avez déjà `users`, `portfolios`, `transactions`, etc.
- Vous voulez appliquer les optimisations (index, commentaires)

**Solution :**
```bash
# Les migrations vérifient si les tables existent
# Si elles existent déjà, elles ne seront pas recréées
php artisan migrate

# Si vous avez des erreurs "Table already exists"
# C'est normal, ignorez-les ou supprimez les migrations déjà exécutées
```

### Cas 2 : Vous voulez ajouter seulement des index

**Situation :**
- Vos tables existent déjà
- Vous voulez ajouter les index optimisés

**Solution :**
```bash
# Créer une nouvelle migration pour ajouter les index
php artisan make:migration add_optimized_indexes_to_existing_tables

# Puis exécuter
php artisan migrate
# → Vos données sont SÉCURISÉES ✅
```

### Cas 3 : Vous voulez repartir de zéro (DÉVELOPPEMENT)

**Situation :**
- Vous êtes en développement
- Vous voulez tout réinitialiser

**Solution :**
```bash
# ⚠️ ATTENTION : Supprime TOUT
php artisan migrate:fresh --seed

# Ou avec vos seeders personnalisés
php artisan migrate:fresh
php artisan db:seed --class=UserSeeder
php artisan db:seed --class=CryptoAndPricesSeeder
```

---

## 📊 TABLEAU RÉCAPITULATIF

| Commande | Supprime les données ? | Quand utiliser |
|----------|------------------------|----------------|
| `php artisan migrate` | ❌ **NON** | ✅ Production, développement |
| `php artisan migrate:fresh` | ✅ **OUI** (tout) | ⚠️ Développement uniquement |
| `php artisan migrate:refresh` | ⚠️ **PEUT** (selon down()) | ⚠️ Développement uniquement |
| `php artisan migrate:rollback` | ⚠️ **PEUT** (selon down()) | ⚠️ Développement uniquement |
| `php artisan migrate:status` | ❌ **NON** | ✅ Vérification |

---

## 🛡️ PROTECTION DES DONNÉES

### Vérifications avant migration

**1. Vérifier l'état actuel :**
```bash
php artisan migrate:status
```

**2. Sauvegarder (recommandé) :**
```bash
mysqldump -u root -p bitchest_db > backup.sql
```

**3. Tester sur une base de test :**
```bash
# Créer une base de test
mysql -u root -p -e "CREATE DATABASE bitchest_test;"

# Modifier .env temporairement pour pointer vers bitchest_test
# Puis tester
php artisan migrate
```

**4. Appliquer en production :**
```bash
# Une fois testé, appliquer sur la vraie base
php artisan migrate
```

---

## ⚠️ ATTENTION : MIGRATIONS MODIFIÉES

### Si vous modifiez une migration déjà exécutée

**Problème :**
- Si vous modifiez `2014_10_12_000000_create_users_table.php`
- Et que cette migration a déjà été exécutée
- Laravel ne la réexécutera pas automatiquement

**Solutions :**

**Option 1 : Créer une nouvelle migration**
```bash
# Créer une migration pour ajouter les index
php artisan make:migration add_indexes_to_users_table

# Puis dans cette migration, ajouter les index
# Puis exécuter
php artisan migrate
```

**Option 2 : Rollback puis re-migrate (DÉVELOPPEMENT UNIQUEMENT)**
```bash
# ⚠️ ATTENTION : Peut supprimer des données
php artisan migrate:rollback --step=1
php artisan migrate
```

---

## ✅ RECOMMANDATION POUR VOTRE CAS

### Vous avez déjà une base de données avec des données

**Action recommandée :**

1. **Sauvegarder** (sécurité) :
```bash
mysqldump -u root -p bitchest_db > backup_avant_optimisation.sql
```

2. **Vérifier l'état** :
```bash
php artisan migrate:status
```

3. **Appliquer les migrations** :
```bash
php artisan migrate
```

**Résultat attendu :**
- ✅ Si les tables existent déjà → **Rien ne se passe** (pas d'erreur)
- ✅ Si des index manquent → **Ils seront ajoutés**
- ✅ **Vos données sont préservées**

---

## 🚨 EN CAS D'ERREUR

### Erreur : "Table already exists"

**Cause :** La table existe déjà mais Laravel pense qu'elle n'existe pas.

**Solution :**
```bash
# Marquer la migration comme exécutée sans l'exécuter
php artisan migrate --pretend

# Ou modifier manuellement la table migrations dans la base de données
# pour indiquer que la migration a été exécutée
```

### Erreur : "Index already exists"

**Cause :** L'index existe déjà.

**Solution :**
```bash
# Modifier la migration pour vérifier si l'index existe avant de le créer
# Ou ignorer l'erreur si elle n'est pas bloquante
```

---

## 📝 RÉSUMÉ FINAL

### ✅ Pour appliquer les optimisations SANS perdre de données :

```bash
# 1. Sauvegarder (recommandé)
mysqldump -u root -p bitchest_db > backup.sql

# 2. Vérifier l'état
php artisan migrate:status

# 3. Appliquer (SÉCURISÉ)
php artisan migrate

# ✅ Vos données sont préservées !
```

### ❌ À NE JAMAIS FAIRE en production :

```bash
# ❌ NE PAS UTILISER ces commandes en production sans sauvegarde
php artisan migrate:fresh      # Supprime TOUT
php artisan migrate:refresh    # Peut supprimer des données
php artisan migrate:rollback   # Peut supprimer des données
```

---

**Date de création :** 2025-01-27  
**Version :** 1.0
