# GUIDE PRATIQUE DE VALIDATION DES COMPÉTENCES - BITCHEST

Ce guide vous accompagne dans la validation de chaque compétence lors de votre présentation.

---

## CHECKLIST DE VALIDATION PAR COMPÉTENCE

### ✅ C1. Maquetter une application

#### Éléments à présenter :

- [ ] **Maquettes fonctionnelles**
  - 📁 `bitchest-frontend/src/pages/` - Toutes les pages
  - 📁 `bitchest-frontend/src/admin/pages/` - Pages admin
  - 📁 `bitchest-frontend/src/components/sectionsLanding/` - Sections landing page
  - **Démontrer** : Ouvrir chaque page et expliquer sa fonction

- [ ] **Enchaînement des écrans**
  - 📁 `bitchest-frontend/src/router/index.ts` - Configuration routing
  - **Démontrer** : Parcourir l'application et expliquer le flux de navigation
  - **Points clés** :
    - Routes protégées par authentification
    - Redirection selon rôle (admin/client)
    - Gestion des états (pending, active, blocked)

- [ ] **Charte graphique**
  - 📁 `bitchest-frontend/src/index.css` - Variables CSS et styles globaux
  - 📁 `bitchest-frontend/src/theme/colors.ts` - Palette de couleurs
  - 📁 `bitchest-frontend/tailwind.config.js` - Configuration Tailwind
  - **Démontrer** : Montrer la cohérence visuelle entre les pages

- [ ] **Sécurisation interface**
  - 📁 `bitchest-backend/app/Http/Middleware/` - Middleware de sécurité
  - 📁 `bitchest-backend/app/Http/Requests/` - Validation des formulaires
  - **Démontrer** :
    - Tentative d'accès non autorisé
    - Validation des formulaires
    - Gestion des erreurs utilisateur

- [ ] **Exigences sécurité spécifiques**
  - 📁 `bitchest-backend/app/Http/Controllers/AuthController.php` - Gestion auth
  - **Démontrer** :
    - Changement de mot de passe obligatoire
    - Validation admin pour nouveaux comptes
    - Gestion des statuts utilisateur

#### 💬 Phrases clés à dire :
- "Les maquettes respectent la charte graphique définie avec Tailwind CSS"
- "L'enchaînement des écrans est géré par Vue Router avec protection des routes"
- "La sécurité de l'interface est assurée par la validation côté client et serveur"

---

### ✅ C3. Développer des composants d'accès aux données

#### Éléments à présenter :

- [ ] **Traitements de données**
  - 📁 `bitchest-backend/app/Services/TransactionService.php` - Service transactions
  - 📁 `bitchest-backend/app/Services/PortfolioService.php` - Service portfolio
  - 📁 `bitchest-backend/app/Services/CryptoService.php` - Service crypto
  - 📁 `bitchest-backend/app/Models/` - Tous les modèles Eloquent
  - **Démontrer** :
    - Expliquer une méthode de service (ex: `processTransaction`)
    - Montrer les relations Eloquent
    - Expliquer l'utilisation du cache Redis

- [ ] **Tests unitaires**
  - 📁 `bitchest-backend/tests/` - Structure de tests
  - 📁 `bitchest-backend/database/factories/` - Factories pour données de test
  - **Démontrer** :
    - Expliquer la structure de tests
    - Montrer comment créer des données de test avec factories
    - Exécuter un test si disponible

- [ ] **Documentation code**
  - Ouvrir n'importe quel service et montrer les commentaires
  - Expliquer la structure des méthodes
  - **Exemple** : `TransactionService::processTransaction()`

- [ ] **Sécurisation accès données**
  - 📁 `bitchest-backend/app/Http/Requests/` - Form Requests
  - 📁 `bitchest-backend/app/Services/TransactionService.php` - Ligne 26 : `lockForUpdate()`
  - **Démontrer** :
    - Expliquer les prepared statements (Eloquent)
    - Montrer la validation des entrées
    - Expliquer les transactions DB (ligne 23 TransactionService)

- [ ] **Sécurité SGBD**
  - 📁 `bitchest-backend/database/migrations/` - Toutes les migrations
  - **Démontrer** :
    - Montrer les foreign keys dans les migrations
    - Expliquer les index (migration `2026_01_10_222533_add_indexes_to_transactions_table.php`)
    - Montrer les contraintes (cascade delete)

#### 💬 Phrases clés à dire :
- "Les composants d'accès aux données utilisent Eloquent ORM avec prepared statements"
- "La sécurité est assurée par la validation des entrées et les transactions DB"
- "Les performances sont optimisées grâce au cache Redis et aux index DB"

---

### ✅ C4. Développer la partie front-end

#### Éléments à présenter :

- [ ] **Responsive design**
  - Ouvrir l'application dans différentes tailles d'écran
  - 📁 `bitchest-frontend/src/index.css` - Classes responsive
  - **Démontrer** : Redimensionner la fenêtre et montrer l'adaptation

- [ ] **Code documenté**
  - 📁 `bitchest-frontend/src/components/` - Composants Vue
  - 📁 `bitchest-frontend/src/services/api.ts` - Services API avec commentaires
  - **Démontrer** : Ouvrir un composant et expliquer sa structure

- [ ] **Tests fonctionnels**
  - Expliquer comment tester manuellement
  - Montrer la validation des formulaires
  - **Démontrer** : Tester un formulaire avec données invalides

- [ ] **Sécurité frontend**
  - 📁 `bitchest-frontend/src/services/api.ts` - Gestion des tokens
  - 📁 `bitchest-frontend/src/stores/auth.ts` - Store d'authentification
  - **Démontrer** :
    - Montrer comment les tokens sont stockés
    - Expliquer la validation côté client
    - Montrer la gestion des erreurs API

- [ ] **Veille sécurité**
  - 📁 `bitchest-frontend/package.json` - Dépendances à jour
  - **Démontrer** : Expliquer comment maintenir les dépendances à jour

#### 💬 Phrases clés à dire :
- "Le frontend est entièrement responsive grâce à Tailwind CSS"
- "Le code est typé avec TypeScript pour une meilleure maintenabilité"
- "La sécurité est assurée par la validation côté client et la gestion sécurisée des tokens"

---

### ✅ C5. Développer la partie back-end

#### Éléments à présenter :

- [ ] **Bonnes pratiques OOP**
  - 📁 `bitchest-backend/app/Services/` - Services métier
  - 📁 `bitchest-backend/app/DTOs/` - Data Transfer Objects
  - **Démontrer** :
    - Expliquer l'injection de dépendances
    - Montrer la séparation des responsabilités
    - Expliquer l'utilisation des DTOs

- [ ] **Sécurité composants serveur**
  - 📁 `bitchest-backend/app/Http/Middleware/` - Middleware
  - 📁 `bitchest-backend/app/Http/Kernel.php` - Configuration middleware
  - **Démontrer** :
    - Expliquer l'authentification Sanctum
    - Montrer la validation des entrées
    - Expliquer le rate limiting

- [ ] **Documentation code**
  - Ouvrir un service et montrer les commentaires PHPDoc
  - Expliquer la structure MVC
  - **Exemple** : `TransactionService.php`

- [ ] **Tests serveur**
  - 📁 `bitchest-backend/tests/` - Structure de tests
  - **Démontrer** : Expliquer comment écrire des tests

- [ ] **Veille sécurité**
  - 📁 `bitchest-backend/composer.json` - Dépendances
  - Expliquer comment suivre les advisories Laravel

#### 💬 Phrases clés à dire :
- "L'architecture backend suit les principes SOLID avec injection de dépendances"
- "La sécurité est multi-niveaux : middleware, validation, transactions DB"
- "Le code est documenté avec PHPDoc et suit les conventions Laravel"

---

### ✅ C6. Concevoir une base de données

#### Éléments à présenter :

- [ ] **Schéma entité-association**
  - 📁 `bitchest-backend/database/migrations/` - Toutes les migrations
  - 📁 `ANALYSE_TECHNIQUE_BITCHEST.md` - Section 3.1 Diagramme de Classes
  - **Démontrer** :
    - Présenter le diagramme de classes
    - Expliquer les relations entre tables
    - Montrer les migrations

- [ ] **Formalisme E-A**
  - Expliquer la normalisation
  - Montrer les clés primaires et étrangères
  - **Démontrer** : Ouvrir une migration et expliquer la structure

- [ ] **Règles de nommage**
  - Montrer la cohérence des noms de tables
  - Expliquer les conventions Laravel
  - **Exemple** : `users`, `transactions`, `crypto_currencies`

- [ ] **Normalisation**
  - Expliquer pourquoi chaque table existe
  - Montrer qu'il n'y a pas de redondance
  - **Démontrer** : Analyser une table et expliquer sa structure

#### 💬 Phrases clés à dire :
- "Le schéma respecte la 3NF avec séparation des domaines métier"
- "Les relations sont définies avec des foreign keys et contraintes"
- "La normalisation évite la redondance et assure la cohérence"

---

### ✅ C7. Mettre en place une base de données

#### Éléments à présenter :

- [ ] **Conformité schéma physique**
  - 📁 `bitchest-backend/database/migrations/` - Migrations
  - **Démontrer** : Exécuter `php artisan migrate:status` et expliquer

- [ ] **Règles de nommage**
  - Montrer la cohérence dans toutes les migrations
  - Expliquer les conventions

- [ ] **Intégrité données**
  - 📁 `bitchest-backend/database/migrations/2025_12_11_205639_create_transactions_table.php`
  - Ligne 13 : `->constrained()->onDelete('cascade')`
  - **Démontrer** : Expliquer les contraintes

- [ ] **Disponibilité et droits**
  - 📁 `bitchest-backend/config/database.php` - Configuration
  - Expliquer la gestion des environnements (.env)

- [ ] **Confidentialité**
  - Expliquer le hachage des mots de passe
  - Montrer les données sensibles protégées

- [ ] **Authentification et traçabilité**
  - Montrer les timestamps sur toutes les tables
  - Expliquer les logs

- [ ] **Restauration**
  - 📁 `bitchest-backend/database/seeders/` - Seeders
  - 📁 `bitchest-backend/refresh-database.bat` - Script de refresh
  - **Démontrer** : Expliquer comment restaurer la DB

#### 💬 Phrases clés à dire :
- "La base de données est versionnée avec les migrations Laravel"
- "L'intégrité est assurée par les foreign keys et contraintes"
- "Les procédures de restauration sont documentées et automatisées"

---

### ✅ C8. Développer des composants dans le langage d'une base de données

#### Éléments à présenter :

- [ ] **Traitements manipulations données**
  - 📁 `bitchest-backend/app/Services/PortfolioService.php` - Ligne 97-105
  - Requêtes Eloquent complexes avec cache
  - **Démontrer** : Expliquer une requête complexe

- [ ] **Gestion exceptions**
  - 📁 `bitchest-backend/app/Exceptions/Handler.php` - Handler global
  - 📁 `bitchest-backend/app/Services/TransactionService.php` - Try-catch
  - **Démontrer** : Expliquer la gestion des erreurs

- [ ] **Intégrité et confidentialité**
  - Transactions DB (ligne 23 TransactionService)
  - Validation des entrées
  - **Démontrer** : Expliquer une transaction

- [ ] **Gestion conflits accès**
  - 📁 `bitchest-backend/app/Services/TransactionService.php` - Ligne 26
  - `lockForUpdate()` pour éviter les race conditions
  - **Démontrer** : Expliquer le row locking

- [ ] **Contrôle et validation entrées**
  - 📁 `bitchest-backend/app/Http/Requests/` - Form Requests
  - **Démontrer** : Montrer une validation

- [ ] **Tests unitaires**
  - Structure de tests
  - Expliquer comment tester les composants

#### 💬 Phrases clés à dire :
- "Les requêtes utilisent Eloquent avec prepared statements pour la sécurité"
- "Les transactions DB assurent l'atomicité des opérations"
- "Le row locking évite les conflits d'accès concurrents"

---

### ✅ C9. Collaborer à la gestion d'un projet informatique

#### Éléments à présenter :

- [ ] **Suivi activités**
  - Structure de projet organisée
  - Documentation technique
  - **Démontrer** : Parcourir la structure de dossiers

- [ ] **Procédures qualité**
  - 📁 `bitchest-backend/.editorconfig` - Standards de code
  - Laravel Pint pour formatage
  - **Démontrer** : Expliquer les standards

- [ ] **Environnement développement**
  - 📁 `bitchest-backend/docker-compose.yml` - Docker
  - 📁 `bitchest-backend/.env.example` - Configuration
  - Scripts batch pour Windows
  - **Démontrer** : Expliquer le setup

- [ ] **Outils collaboratifs**
  - Git pour versioning
  - Structure de dossiers claire
  - Documentation Markdown
  - **Démontrer** : Montrer l'historique Git si disponible

- [ ] **Communication**
  - Documentation technique
  - Commentaires code
  - README files

#### 💬 Phrases clés à dire :
- "Le projet est organisé selon les conventions Laravel et Vue.js"
- "L'environnement de développement est documenté et automatisé"
- "La collaboration est facilitée par Git et la documentation"

---

### ✅ C10. Concevoir une application

#### Éléments à présenter :

- [ ] **Cas d'utilisation**
  - 📁 `bitchest-backend/routes/api.php` - Routes API
  - 📁 `ANALYSE_TECHNIQUE_BITCHEST.md` - Sections 3.6 et 3.7
  - **Démontrer** : Présenter les diagrammes de cas d'utilisation

- [ ] **Besoins sécurité**
  - Authentification Sanctum
  - Autorisation par rôles
  - Validation multi-niveaux
  - **Démontrer** : Expliquer chaque couche de sécurité

- [ ] **Besoins éco-conception**
  - Cache Redis pour réduire les appels DB
  - Requêtes optimisées
  - Lazy loading
  - **Démontrer** : Montrer l'utilisation du cache

- [ ] **Classes analyse/conception**
  - 📁 `ANALYSE_TECHNIQUE_BITCHEST.md` - Section 3.1 et 3.2
  - **Démontrer** : Présenter les diagrammes de classes

- [ ] **Architecture technique**
  - Architecture MVC
  - Séparation des couches
  - API REST
  - **Démontrer** : Expliquer l'architecture globale

- [ ] **Dossier conception**
  - Ce document d'analyse
  - Diagrammes UML
  - Documentation code
  - **Démontrer** : Présenter la documentation

- [ ] **Stratégie sécurité par couche**
  - Frontend : validation, sanitization
  - API : authentification, autorisation
  - Service : logique métier
  - DB : contraintes, transactions
  - **Démontrer** : Expliquer chaque couche

#### 💬 Phrases clés à dire :
- "L'architecture suit le pattern MVC avec séparation des responsabilités"
- "La sécurité est multi-niveaux : frontend, API, service, base de données"
- "L'éco-conception est assurée par le cache et les optimisations"

---

## SCRIPT DE PRÉSENTATION RECOMMANDÉ

### Introduction (2 minutes)
1. Présenter le projet BitChest
2. Expliquer le contexte (plateforme de trading crypto)
3. Présenter l'architecture globale (Laravel + Vue.js)

### Démonstration fonctionnelle (5 minutes)
1. Connexion utilisateur
2. Achat de crypto
3. Consultation du portfolio
4. Interface admin (si temps)

### Présentation technique par compétence (15 minutes)
1. **Architecture et conception** (C10, C6)
   - Diagrammes UML
   - Schéma de base de données
   - Architecture MVC

2. **Backend** (C5, C3, C8)
   - Services métier
   - Accès aux données
   - Sécurité

3. **Frontend** (C4, C1)
   - Composants Vue.js
   - Responsive design
   - Sécurité UI

4. **Base de données** (C7)
   - Migrations
   - Intégrité
   - Restauration

5. **Gestion de projet** (C9)
   - Organisation
   - Documentation
   - Outils collaboratifs

### Questions et réponses (5 minutes)
- Répondre aux questions du jury
- Démontrer des points spécifiques si demandé

---

## POINTS D'ATTENTION

### ✅ À faire absolument :
- [ ] Tester l'application avant la présentation
- [ ] Préparer des données de test réalistes
- [ ] Avoir les diagrammes imprimés ou sur écran
- [ ] Préparer des réponses aux questions courantes
- [ ] Tester la connexion à la base de données
- [ ] Vérifier que Redis fonctionne

### ❌ À éviter :
- Ne pas modifier le code pendant la présentation
- Ne pas improviser sur des points techniques non maîtrisés
- Ne pas négliger la démonstration fonctionnelle
- Ne pas oublier de mentionner les aspects sécurité
- Ne pas sauter les explications d'architecture

---

## RESSOURCES COMPLÉMENTAIRES

### Fichiers clés à avoir sous la main :
1. `ANALYSE_TECHNIQUE_BITCHEST.md` - Ce document principal
2. `bitchest-backend/routes/api.php` - Routes API
3. `bitchest-backend/database/migrations/` - Migrations
4. `bitchest-backend/app/Services/` - Services métier
5. `bitchest-frontend/src/router/index.ts` - Routing frontend

### Commandes utiles :
```bash
# Backend
php artisan migrate:status
php artisan route:list
php artisan db:check

# Frontend
npm run dev
npm run build
```

---

**Bon courage pour votre présentation ! 🚀**
