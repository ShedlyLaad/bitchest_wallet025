# QUESTIONS POSSIBLES DU JURY - BITCHEST

Ce document contient les questions fréquemment posées par les jurys lors des présentations de projets, avec leurs réponses détaillées.

---

## C1. MAQUETTER UNE APPLICATION

### Q1 : Comment avez-vous conçu vos maquettes fonctionnelles ?

**Réponse :**
"J'ai conçu les maquettes en respectant les principes UX/UI modernes. Les pages principales sont dans `bitchest-frontend/src/pages/` et les pages admin dans `bitchest-frontend/src/admin/pages/`. Chaque page a été pensée pour une navigation intuitive avec une charte graphique cohérente utilisant Tailwind CSS. Les maquettes respectent le responsive design pour s'adapter à tous les écrans."

**Points à montrer :**
- Ouvrir les fichiers de pages dans le projet
- Montrer la cohérence visuelle entre les pages
- Démontrer le responsive design

---

### Q2 : Comment gérez-vous l'enchaînement des écrans ?

**Réponse :**
"L'enchaînement des écrans est géré par Vue Router dans `bitchest-frontend/src/router/index.ts`. J'ai implémenté des routes protégées qui vérifient l'authentification et le rôle de l'utilisateur. Par exemple, un utilisateur non authentifié est redirigé vers la page de connexion, et un utilisateur avec le rôle 'client' ne peut pas accéder aux pages admin."

**Points à montrer :**
- Ouvrir le fichier router
- Expliquer les guards de routes
- Montrer les redirections selon les états

---

### Q3 : Comment avez-vous sécurisé l'interface utilisateur ?

**Réponse :**
"La sécurité de l'interface est assurée à plusieurs niveaux :
1. **Validation côté client** : Tous les formulaires utilisent la validation Vue.js
2. **Validation côté serveur** : Les Form Requests Laravel valident toutes les entrées
3. **Protection des routes** : Les routes sensibles nécessitent une authentification
4. **Gestion des tokens** : Les tokens JWT sont stockés de manière sécurisée et gérés par le store Pinia"

**Points à montrer :**
- Ouvrir un composant avec validation
- Montrer un Form Request
- Expliquer la gestion des tokens

---

## C3. DÉVELOPPER DES COMPOSANTS D'ACCÈS AUX DONNÉES

### Q4 : Comment fonctionnent vos services de traitement de données ?

**Réponse :**
"J'ai créé des services métier qui encapsulent la logique d'accès aux données. Par exemple, `TransactionService` gère toutes les opérations de transaction en utilisant Eloquent ORM avec des prepared statements pour éviter les injections SQL. Le service utilise également des transactions DB pour garantir l'atomicité des opérations."

**Points à montrer :**
- Ouvrir `TransactionService.php`
- Expliquer la méthode `processTransaction()`
- Montrer l'utilisation de `lockForUpdate()` pour éviter les race conditions

---

### Q5 : Où sont vos tests unitaires ?

**Réponse :**
"J'ai créé une suite complète de tests unitaires dans `bitchest-backend/tests/Unit/`. Les tests couvrent les services principaux comme `TransactionService` et `PortfolioService`, ainsi que les modèles et les Form Requests. J'utilise PHPUnit avec des factories pour générer des données de test réalistes."

**Points à montrer :**
- Ouvrir les fichiers de tests
- Expliquer la structure des tests
- Montrer comment exécuter les tests : `php artisan test`

---

### Q6 : Comment documentez-vous votre code ?

**Réponse :**
"Tous les services et méthodes importantes sont documentés avec PHPDoc. Chaque service a une description de ses responsabilités, et chaque méthode publique a des annotations `@param` et `@return`. Par exemple, dans `TransactionService`, chaque méthode explique clairement son rôle et ses paramètres."

**Points à montrer :**
- Ouvrir un service avec PHPDoc
- Expliquer la structure des commentaires
- Montrer les annotations

---

### Q7 : Comment sécurisez-vous l'accès aux données ?

**Réponse :**
"La sécurité des données est assurée à plusieurs niveaux :
1. **Prepared statements** : Eloquent utilise automatiquement des prepared statements
2. **Validation des entrées** : Les Form Requests valident toutes les données avant traitement
3. **Transactions DB** : Les opérations critiques utilisent des transactions pour garantir l'intégrité
4. **Row locking** : J'utilise `lockForUpdate()` pour éviter les conflits d'accès concurrents"

**Points à montrer :**
- Montrer une transaction DB dans `TransactionService`
- Expliquer `lockForUpdate()`
- Montrer un Form Request avec validation

---

## C4. DÉVELOPPER LA PARTIE FRONT-END

### Q8 : Comment avez-vous géré le responsive design ?

**Réponse :**
"Le responsive design est géré entièrement avec Tailwind CSS. J'utilise les classes responsive comme `md:`, `lg:`, `xl:` pour adapter l'interface selon la taille de l'écran. Par exemple, sur mobile, les tableaux deviennent des cartes empilées, et sur desktop, ils s'affichent en tableau complet."

**Points à montrer :**
- Redimensionner la fenêtre du navigateur
- Montrer l'adaptation de l'interface
- Ouvrir le fichier `tailwind.config.js`

---

### Q9 : Comment testez-vous votre frontend ?

**Réponse :**
"Je teste le frontend de manière fonctionnelle en vérifiant :
1. La validation des formulaires avec des données invalides
2. La gestion des erreurs API
3. Les interactions utilisateur (clics, navigation)
4. La gestion des états (loading, erreur, succès)

J'ai également typé le code avec TypeScript pour détecter les erreurs à la compilation."

**Points à montrer :**
- Tester un formulaire avec données invalides
- Montrer la gestion des erreurs
- Expliquer l'utilisation de TypeScript

---

### Q10 : Comment sécurisez-vous le frontend ?

**Réponse :**
"La sécurité frontend est assurée par :
1. **Validation côté client** : Tous les formulaires valident les données avant envoi
2. **Gestion sécurisée des tokens** : Les tokens JWT sont stockés dans le store Pinia et envoyés dans les headers HTTP
3. **Sanitization** : Les données utilisateur sont échappées avant affichage
4. **Gestion des erreurs** : Les erreurs API sont gérées de manière sécurisée sans exposer d'informations sensibles"

**Points à montrer :**
- Ouvrir `bitchest-frontend/src/services/api.ts`
- Montrer la gestion des tokens
- Expliquer la validation des formulaires

---

## C5. DÉVELOPPER LA PARTIE BACK-END

### Q11 : Quelles bonnes pratiques OOP avez-vous appliquées ?

**Réponse :**
"J'ai appliqué plusieurs principes OOP :
1. **Séparation des responsabilités** : Chaque service a une responsabilité unique (SRP)
2. **Injection de dépendances** : Les services sont injectés via le constructeur
3. **DTOs** : J'utilise des Data Transfer Objects pour structurer les données
4. **Interfaces** : Les services peuvent être facilement mockés pour les tests

Par exemple, `TransactionService` dépend de `PortfolioService` et `NotificationService`, mais ces dépendances sont injectées, ce qui facilite les tests."

**Points à montrer :**
- Ouvrir un service avec injection de dépendances
- Montrer les DTOs
- Expliquer la structure MVC

---

### Q12 : Comment sécurisez-vous les composants serveur ?

**Réponse :**
"La sécurité serveur est multi-niveaux :
1. **Authentification Sanctum** : Gestion des tokens d'authentification
2. **Middleware** : Vérification de l'authentification et des autorisations
3. **Validation** : Form Requests pour valider toutes les entrées
4. **Rate limiting** : Protection contre les attaques par force brute
5. **Transactions DB** : Garantie de l'intégrité des données"

**Points à montrer :**
- Ouvrir `app/Http/Middleware/`
- Montrer la configuration dans `Kernel.php`
- Expliquer Sanctum

---

### Q13 : Comment testez-vous votre backend ?

**Réponse :**
"J'ai créé des tests unitaires pour les services principaux et des tests de fonctionnalité pour les endpoints API. Les tests utilisent des factories pour générer des données de test et des mocks pour isoler les dépendances. Par exemple, `TransactionServiceTest` teste tous les cas d'usage : achat réussi, solde insuffisant, vente, etc."

**Points à montrer :**
- Ouvrir les fichiers de tests
- Expliquer la structure
- Montrer comment exécuter : `php artisan test`

---

## C6. CONCEVOIR UNE BASE DE DONNÉES

### Q14 : Comment avez-vous conçu votre schéma de base de données ?

**Réponse :**
"J'ai conçu le schéma en respectant la normalisation 3NF. Les principales entités sont :
- `users` : Utilisateurs de la plateforme
- `crypto_currencies` : Cryptomonnaies disponibles
- `portfolios` : Portfolios des utilisateurs (relation many-to-many entre users et crypto_currencies)
- `transactions` : Historique des transactions
- `notifications` : Notifications utilisateurs
- `crypto_price_records` : Historique des prix

Les relations sont définies avec des foreign keys et des contraintes de cascade pour garantir l'intégrité référentielle."

**Points à montrer :**
- Ouvrir les migrations
- Expliquer les relations
- Montrer les foreign keys

---

### Q15 : Comment avez-vous normalisé votre base de données ?

**Réponse :**
"La base de données est normalisée en 3NF :
- Chaque table a une responsabilité unique
- Pas de redondance de données
- Les relations sont gérées par des tables de liaison (comme `portfolios` qui lie users et crypto_currencies)
- Les données calculées (comme les plus-values) sont calculées dynamiquement, pas stockées

Par exemple, au lieu de stocker le prix moyen d'achat, je le calcule à la volée depuis les transactions."

**Points à montrer :**
- Analyser une table et expliquer sa structure
- Montrer qu'il n'y a pas de redondance
- Expliquer les calculs dynamiques

---

## C7. METTRE EN PLACE UNE BASE DE DONNÉES

### Q16 : Comment gérez-vous les migrations ?

**Réponse :**
"J'utilise le système de migrations Laravel pour versionner la base de données. Chaque migration est nommée de manière descriptive et contient les instructions pour créer ou modifier les tables. J'ai également créé des scripts batch pour faciliter l'exécution des migrations en toute sécurité."

**Points à montrer :**
- Exécuter `php artisan migrate:status`
- Ouvrir une migration
- Expliquer le processus de migration

---

### Q17 : Comment assurez-vous l'intégrité des données ?

**Réponse :**
"L'intégrité est assurée par :
1. **Foreign keys** : Toutes les relations ont des foreign keys avec contraintes
2. **Cascade delete** : Les suppressions en cascade sont configurées (ex: supprimer un utilisateur supprime ses portfolios)
3. **Transactions DB** : Les opérations critiques utilisent des transactions
4. **Validation** : Toutes les entrées sont validées avant insertion"

**Points à montrer :**
- Ouvrir une migration avec foreign key
- Montrer `onDelete('cascade')`
- Expliquer les transactions

---

### Q18 : Comment restaurez-vous la base de données ?

**Réponse :**
"J'ai créé des seeders pour initialiser la base de données avec des données de test. Le script `refresh-database.bat` permet de réinitialiser complètement la base. Les seeders créent des utilisateurs, des cryptomonnaies, et des données de test réalistes."

**Points à montrer :**
- Ouvrir les seeders
- Expliquer le script de refresh
- Montrer comment restaurer

---

## C8. DÉVELOPPER DES COMPOSANTS DANS LE LANGAGE D'UNE BASE DE DONNÉES

### Q19 : Comment gérez-vous les exceptions dans vos requêtes ?

**Réponse :**
"Les exceptions sont gérées à plusieurs niveaux :
1. **Try-catch dans les services** : Chaque service gère ses propres exceptions
2. **Handler global** : `app/Exceptions/Handler.php` gère les exceptions non capturées
3. **Logging** : Toutes les erreurs sont loggées pour le débogage
4. **Messages utilisateur** : Les erreurs sont transformées en messages compréhensibles pour l'utilisateur"

**Points à montrer :**
- Ouvrir un service avec try-catch
- Montrer le Handler
- Expliquer la gestion des erreurs

---

### Q20 : Comment gérez-vous les conflits d'accès concurrents ?

**Réponse :**
"Je gère les conflits d'accès avec `lockForUpdate()` dans les transactions critiques. Par exemple, dans `TransactionService::processTransaction()`, je verrouille la ligne utilisateur avant de modifier son solde pour éviter les race conditions. Cela garantit qu'une seule transaction peut modifier le solde à la fois."

**Points à montrer :**
- Ouvrir `TransactionService.php` ligne 53-55
- Expliquer `lockForUpdate()`
- Donner un exemple de race condition évitée

---

### Q21 : Comment validez-vous les entrées utilisateur ?

**Réponse :**
"La validation est faite via les Form Requests Laravel. Chaque endpoint a son propre Form Request qui définit les règles de validation. Par exemple, `BuyCryptoRequest` valide que le symbole existe et que la quantité est positive. Les messages d'erreur sont personnalisés en français."

**Points à montrer :**
- Ouvrir un Form Request
- Expliquer les règles
- Montrer les messages personnalisés

---

## C9. COLLABORER À LA GESTION D'UN PROJET INFORMATIQUE

### Q22 : Comment organisez-vous votre projet ?

**Réponse :**
"Le projet suit les conventions Laravel et Vue.js :
- **Backend** : Structure MVC avec services, DTOs, et middleware
- **Frontend** : Structure par composants avec stores Pinia
- **Documentation** : README et guides techniques
- **Versioning** : Git avec commits descriptifs
- **Standards** : Laravel Pint pour le formatage du code"

**Points à montrer :**
- Parcourir la structure de dossiers
- Montrer la documentation
- Expliquer l'organisation

---

### Q23 : Quels outils collaboratifs utilisez-vous ?

**Réponse :**
"J'utilise :
1. **Git** : Pour le versioning et la collaboration
2. **Documentation Markdown** : Pour documenter le projet
3. **Standards de code** : Laravel Pint et ESLint pour maintenir la cohérence
4. **Scripts d'automatisation** : Scripts batch pour faciliter le développement"

**Points à montrer :**
- Montrer l'historique Git (si disponible)
- Ouvrir la documentation
- Expliquer les standards

---

## C10. CONCEVOIR UNE APPLICATION

### Q24 : Quels sont vos cas d'utilisation principaux ?

**Réponse :**
"Les principaux cas d'utilisation sont :
1. **Authentification** : Connexion, inscription, changement de mot de passe
2. **Trading** : Achat et vente de cryptomonnaies
3. **Portfolio** : Consultation du portfolio avec calcul des plus-values
4. **Notifications** : Alertes de profit/perte et montées de niveau
5. **Administration** : Gestion des utilisateurs et des cryptomonnaies

Ces cas d'utilisation sont documentés dans `ANALYSE_TECHNIQUE_BITCHEST.md` avec des diagrammes UML."

**Points à montrer :**
- Ouvrir `routes/api.php`
- Montrer les diagrammes UML
- Expliquer les cas d'utilisation

---

### Q25 : Comment avez-vous géré les besoins de sécurité ?

**Réponse :**
"La sécurité est gérée par couches :
1. **Frontend** : Validation, sanitization, gestion sécurisée des tokens
2. **API** : Authentification Sanctum, autorisation par rôles, rate limiting
3. **Service** : Validation métier, transactions DB
4. **Base de données** : Contraintes, foreign keys, hachage des mots de passe

Chaque couche ajoute une protection supplémentaire."

**Points à montrer :**
- Expliquer chaque couche
- Montrer les middleware
- Expliquer Sanctum

---

### Q26 : Quelles sont vos optimisations pour l'éco-conception ?

**Réponse :**
"J'ai implémenté plusieurs optimisations :
1. **Cache Redis** : Les prix crypto sont mis en cache pour réduire les appels DB
2. **Requêtes optimisées** : Utilisation d'index et de requêtes Eloquent optimisées
3. **Lazy loading** : Chargement à la demande des relations
4. **Compression des données** : Service de compression pour réduire la taille des réponses API

Ces optimisations réduisent la charge serveur et améliorent les performances."

**Points à montrer :**
- Ouvrir `RedisPriceService.php`
- Expliquer le cache
- Montrer les index dans les migrations

---

### Q27 : Quelle est votre architecture technique ?

**Réponse :**
"L'architecture suit le pattern MVC :
- **Modèles** : Représentent les entités de la base de données
- **Vues** : Composants Vue.js pour l'interface utilisateur
- **Contrôleurs** : Gèrent les requêtes HTTP et appellent les services
- **Services** : Contiennent la logique métier
- **API REST** : Communication entre frontend et backend

Cette architecture permet une séparation claire des responsabilités et facilite la maintenance."

**Points à montrer :**
- Expliquer le flux de données
- Montrer un contrôleur
- Expliquer l'architecture globale

---

## QUESTIONS TECHNIQUES SPÉCIFIQUES

### Q28 : Pourquoi utilisez-vous Redis ?

**Réponse :**
"Redis est utilisé pour le cache des prix crypto car :
1. **Performance** : Accès ultra-rapide (< 5ms) comparé à la base de données
2. **Réduction de charge** : Évite les requêtes DB répétées
3. **Fallback automatique** : Si Redis est vide, on récupère depuis la DB
4. **Scalabilité** : Permet de gérer un grand nombre de requêtes simultanées"

**Points à montrer :**
- Ouvrir `RedisPriceService.php`
- Expliquer le mécanisme de cache
- Montrer le fallback

---

### Q29 : Comment calculez-vous les plus-values ?

**Réponse :**
"Le calcul des plus-values suit le cahier des charges :
1. **Coût total** : Somme de tous les achats
2. **Quantité possédée** : Quantité achetée - quantité vendue
3. **Prix moyen d'achat** : Coût total / Quantité totale achetée
4. **Plus-value** : (Quantité possédée × Prix actuel) - (Quantité possédée × Prix moyen d'achat)

Ce calcul est fait dynamiquement dans `PortfolioService::enrichPortfolio()`."

**Points à montrer :**
- Ouvrir `PortfolioService.php` ligne 128-168
- Expliquer le calcul
- Montrer un exemple

---

### Q30 : Comment gérez-vous les notifications ?

**Réponse :**
"Le système de notifications :
1. **Vérifie automatiquement** après chaque transaction
2. **Détecte les profits/pertes** selon des seuils (0.01€ ou 0.1%)
3. **Gère un cooldown** de 15 minutes entre notifications similaires
4. **Nettoie automatiquement** les anciennes notifications (garde les 50 plus récentes)
5. **Utilise le cache Redis** pour optimiser les performances"

**Points à montrer :**
- Ouvrir `NotificationService.php`
- Expliquer la logique
- Montrer les seuils

---

## CONSEILS POUR LA PRÉSENTATION

### ✅ À faire :
- **Préparer des données de test** : Avoir des utilisateurs, transactions, et portfolios de test
- **Tester l'application** : Vérifier que tout fonctionne avant la présentation
- **Avoir les diagrammes** : Avoir les diagrammes UML imprimés ou sur écran
- **Connaître le code** : Être capable d'expliquer n'importe quelle partie du code
- **Démontrer** : Montrer le code, pas seulement en parler

### ❌ À éviter :
- **Modifier le code** pendant la présentation
- **Improviser** sur des points techniques non maîtrisés
- **Négliger la démo fonctionnelle** : C'est souvent la première impression
- **Oublier la sécurité** : Toujours mentionner les aspects sécurité
- **Sauter les explications d'architecture** : C'est crucial pour C10

---

**Bon courage pour votre présentation ! 🚀**
