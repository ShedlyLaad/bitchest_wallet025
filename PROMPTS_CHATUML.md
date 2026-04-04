# PROMPTS CHATUML - BITCHEST
## Prêts à copier-coller dans ChatUML

---

## PROMPT 1 : DIAGRAMME DE CLASSES - MODÈLES DE DONNÉES

Copiez-collez ce prompt dans ChatUML :

```
Crée un diagramme de classes UML complet en français pour une application de trading de cryptomonnaies.

CLASSE UTILISATEUR (users)
Attributs privés :
- id: int (clé primaire)
- name: string
- first_name: string
- last_name: string
- email: string (unique, indexé)
- phone: string
- password: string (hashé avec bcrypt)
- must_change_password: boolean
- role: enum('admin', 'client') (indexé)
- status: enum('pending', 'pending_validation', 'active', 'blocked') (indexé)
- email_verified_at: timestamp
- euro_balance: decimal(12,2)
- level: int (gamification)
- experience_points: int (gamification)
- profile_picture: string
- profile_banner: string
- remember_token: string
- created_at: timestamp
- updated_at: timestamp

Méthodes publiques :
+ portfolio(): Portfolio (relation 1:1)
+ notifications(): Collection<Notification> (relation 1:N)
+ isAdmin(): boolean
+ isClient(): boolean
+ isActive(): boolean
+ isBlocked(): boolean
+ mustChangePassword(): boolean

CLASSE PORTFOLIO (portfolios)
Attributs privés :
- id: int (clé primaire)
- user_id: int (clé étrangère, UNIQUE, relation 1:1 avec User, indexé)
- crypto_currency_id: int (clé étrangère, indexé)
- total_crypto_value: decimal(18,8)
- created_at: timestamp
- updated_at: timestamp

Méthodes publiques :
+ user(): User (relation 1:1)
+ crypto(): CryptoCurrency (relation N:1)
+ transactions(): Collection<Transaction> (relation 1:N)
+ notifications(): Collection<Notification> (relation 1:N)

CLASSE TRANSACTION (transactions)
Attributs privés :
- id: int (clé primaire)
- portfolio_id: int (clé étrangère, indexé)
- type: enum('buy', 'sell') (indexé)
- quantity: decimal(18,8)
- price_at_transaction: decimal(18,8)
- euro_amount: decimal(18,2)
- created_at: timestamp (indexé)
- updated_at: timestamp

Méthodes publiques :
+ portfolio(): Portfolio (relation N:1)
+ getCachedQuantity(portfolioId: int, type: string): float
+ invalidatePortfolioCache(portfolioId: int): void

CLASSE CRYPTOMONNAIE (crypto_currencies)
Attributs privés :
- id: int (clé primaire)
- name: string
- symbol: string (unique, indexé)
- is_active: boolean (indexé)
- created_at: timestamp
- updated_at: timestamp

Méthodes publiques :
+ portfolios(): Collection<Portfolio> (relation 1:N)
+ priceRecords(): Collection<CryptoPriceRecord> (relation 1:N)
+ notifications(): Collection<Notification> (relation 1:N)

CLASSE ENREGISTREMENT_PRIX (crypto_price_records)
Attributs privés :
- id: int (clé primaire)
- crypto_currency_id: int (clé étrangère, indexé)
- price: decimal(18,8)
- recorded_at: timestamp (indexé)
- created_at: timestamp
- updated_at: timestamp

Méthodes publiques :
+ crypto(): CryptoCurrency (relation N:1)

CLASSE NOTIFICATION (notifications)
Attributs privés :
- id: int (clé primaire)
- user_id: int (clé étrangère, indexé)
- portfolio_id: int (clé étrangère, nullable, indexé)
- crypto_currency_id: int (clé étrangère, nullable, indexé)
- type: enum('profit', 'loss', 'price_alert', 'portfolio_update', 'level_up') (indexé)
- title: string
- message: text
- crypto_symbol: string
- gain_loss: decimal(18,8)
- gain_loss_percent: decimal(10,2)
- current_price: decimal(18,8)
- previous_price: decimal(18,8)
- level: int
- level_name: string
- is_read: boolean (indexé)
- read_at: timestamp
- created_at: timestamp (indexé)
- updated_at: timestamp

Méthodes publiques :
+ user(): User (relation N:1)
+ portfolio(): Portfolio (relation N:1, nullable)
+ crypto(): CryptoCurrency (relation N:1, nullable)
+ markAsRead(): void

RELATIONS UML :
- UTILISATEUR "1" ──────── "1" PORTFOLIO (composition, un utilisateur possède un seul portfolio)
- UTILISATEUR "1" ──────── "*" NOTIFICATION (association, un utilisateur reçoit plusieurs notifications)
- PORTFOLIO "*" ──────── "1" CRYPTOMONNAIE (association, plusieurs portfolios pour une cryptomonnaie)
- PORTFOLIO "1" ──────── "*" TRANSACTION (composition, un portfolio contient plusieurs transactions)
- PORTFOLIO "1" ──────── "*" NOTIFICATION (association, un portfolio peut générer plusieurs notifications)
- CRYPTOMONNAIE "1" ──────── "*" ENREGISTREMENT_PRIX (composition, une crypto a plusieurs prix historiques)
- CRYPTOMONNAIE "1" ──────── "*" NOTIFICATION (association, une crypto peut être dans plusieurs notifications)
- NOTIFICATION "*" ──────── "0..1" PORTFOLIO (association optionnelle)
- NOTIFICATION "*" ──────── "0..1" CRYPTOMONNAIE (association optionnelle)

Utilisez la notation UML standard avec :
- Stéréotypes : <<table>> pour les classes de modèle
- Multiplicités : 1, *, 0..1
- Visibilité : + (public), - (private)
- Notes pour expliquer les relations importantes
- Couleurs différentes pour chaque entité
```

---

## PROMPT 2 : DIAGRAMME DE SÉQUENCE - ACHAT DE CRYPTO

```
Crée un diagramme de séquence UML détaillé en français pour le processus d'achat de cryptomonnaie.

SCÉNARIO : Un client achète 0.5 BTC à 45000€ par unité

Acteurs et objets :
- :Client (Frontend Vue.js)
- :ContrôleurTransaction (Laravel Controller)
- :ServiceTransaction (Laravel Service)
- :ServicePortfolio (Laravel Service)
- :ServiceRedisPrix (Laravel Service)
- :ModèleUtilisateur (Eloquent Model)
- :ModèlePortfolio (Eloquent Model)
- :ModèleTransaction (Eloquent Model)
- :BaseDeDonnées (MySQL)
- :Redis (Cache)

Séquence d'interactions :

1. :Client -> :ContrôleurTransaction : POST /api/transaction/buy {symbol: "BTC", quantity: 0.5}
   activate :ContrôleurTransaction

2. :ContrôleurTransaction -> :ContrôleurTransaction : Valider la requête (FormRequest)
   note right : Validation des champs symbol et quantity

3. :ContrôleurTransaction -> :ContrôleurTransaction : Vérifier authentification (middleware auth:sanctum)
   note right : Token Bearer vérifié

4. :ContrôleurTransaction -> :ServiceRedisPrix : getPrice("BTC")
   activate :ServiceRedisPrix
   :ServiceRedisPrix -> :Redis : GET crypto:BTC
   :Redis --> :ServiceRedisPrix : {price: 45000, change24h: 2.5}
   :ServiceRedisPrix --> :ContrôleurTransaction : {price: 45000}
   deactivate :ServiceRedisPrix

5. :ContrôleurTransaction -> :ServiceTransaction : processTransaction(user, crypto, 0.5, 45000, "buy")
   activate :ServiceTransaction

6. :ServiceTransaction -> :BaseDeDonnées : BEGIN TRANSACTION
   activate :BaseDeDonnées

7. :ServiceTransaction -> :ModèleUtilisateur : lockForUpdate() WHERE id = user_id
   activate :ModèleUtilisateur
   :ModèleUtilisateur -> :BaseDeDonnées : SELECT * FROM users WHERE id = ? FOR UPDATE
   :BaseDeDonnées --> :ModèleUtilisateur : User {euro_balance: 1000}
   deactivate :ModèleUtilisateur

8. :ServiceTransaction -> :ServiceTransaction : Vérifier solde (1000 >= 22500 ?)
   note right : Solde insuffisant si false

9. :ServiceTransaction -> :ModèlePortfolio : firstOrCreate(user_id, crypto_id)
   activate :ModèlePortfolio
   :ModèlePortfolio -> :BaseDeDonnées : SELECT * FROM portfolios WHERE user_id = ? AND crypto_currency_id = ?
   :BaseDeDonnées --> :ModèlePortfolio : null (pas de portfolio)
   :ModèlePortfolio -> :BaseDeDonnées : INSERT INTO portfolios (user_id, crypto_currency_id, total_crypto_value) VALUES (?, ?, 0)
   :BaseDeDonnées --> :ModèlePortfolio : Portfolio {id: 123}
   deactivate :ModèlePortfolio

10. :ServiceTransaction -> :ModèleUtilisateur : UPDATE users SET euro_balance = 7750 WHERE id = ?
    activate :ModèleUtilisateur
    :ModèleUtilisateur -> :BaseDeDonnées : UPDATE users SET euro_balance = 7750 WHERE id = ?
    :BaseDeDonnées --> :ModèleUtilisateur : OK
    deactivate :ModèleUtilisateur

11. :ServiceTransaction -> :ModèleTransaction : create({portfolio_id, type: "buy", quantity: 0.5, price: 45000, euro_amount: 22500})
    activate :ModèleTransaction
    :ModèleTransaction -> :BaseDeDonnées : INSERT INTO transactions (...)
    :BaseDeDonnées --> :ModèleTransaction : Transaction {id: 456}
    deactivate :ModèleTransaction

12. :ServiceTransaction -> :ServicePortfolio : updatePortfolio(portfolio, transaction, 0.5, 45000, "buy")
    activate :ServicePortfolio
    :ServicePortfolio -> :ModèlePortfolio : UPDATE portfolios SET total_crypto_value = total_crypto_value + 22500
    :ModèlePortfolio -> :BaseDeDonnées : UPDATE portfolios SET total_crypto_value = 22500 WHERE id = 123
    :BaseDeDonnées --> :ModèlePortfolio : OK
    deactivate :ModèlePortfolio
    deactivate :ServicePortfolio

13. :ServiceTransaction -> :BaseDeDonnées : COMMIT TRANSACTION
    :BaseDeDonnées --> :ServiceTransaction : OK
    deactivate :BaseDeDonnées

14. :ServiceTransaction -> :ServiceTransaction : Déclencher événement TransactionCreated
    note right : Event-driven architecture

15. :ServiceTransaction --> :ContrôleurTransaction : Transaction {id: 456, ...}
    deactivate :ServiceTransaction

16. :ContrôleurTransaction -> :Redis : SET user:1:balance 7750
    activate :Redis
    :Redis --> :ContrôleurTransaction : OK
    deactivate :Redis

17. :ContrôleurTransaction --> :Client : JSON {transaction: {...}, balance: 7750, message: "Achat effectué"}
    deactivate :ContrôleurTransaction

Utilisez :
- Messages synchrones (flèches pleines)
- Messages de retour (flèches en pointillés)
- Activations (rectangles sur la ligne de vie)
- Notes pour expliquer les étapes importantes
- Couleurs pour différencier les couches (Frontend, Controller, Service, Model, Database)
```

---

## PROMPT 3 : DIAGRAMME DE CAS D'UTILISATION - CLIENT

```
Crée un diagramme de cas d'utilisation UML en français pour un utilisateur CLIENT d'une application de trading de cryptomonnaies.

Acteur principal : CLIENT

Cas d'utilisation principaux :

1. S'inscrire
   - Description : Créer un nouveau compte utilisateur
   - Précondition : Aucune
   - Postcondition : Compte créé avec statut "pending"

2. Se connecter
   - Description : Authentification avec email et mot de passe
   - Précondition : Compte existant
   - Postcondition : Session active, token généré

3. Changer le mot de passe
   - Description : Modification du mot de passe temporaire
   - Précondition : must_change_password = true
   - Postcondition : Mot de passe changé, statut peut passer à "pending_validation"

4. Consulter le marché crypto
   - Description : Voir la liste des cryptomonnaies avec prix en temps réel
   - Précondition : Utilisateur connecté ou non (route publique)
   - Postcondition : Affichage des cryptos avec prix et variations

5. Acheter une cryptomonnaie
   - Description : Effectuer un achat de crypto avec euros
   - Précondition : Solde suffisant, utilisateur actif
   - Postcondition : Transaction créée, solde débité, portfolio mis à jour

6. Vendre une cryptomonnaie
   - Description : Vendre des cryptos possédées
   - Précondition : Quantité suffisante en portfolio, utilisateur actif
   - Postcondition : Transaction créée, solde crédité, portfolio mis à jour

7. Consulter le portfolio
   - Description : Voir les détails des investissements
   - Précondition : Utilisateur connecté
   - Postcondition : Affichage des cryptos possédées avec plus-values

8. Consulter l'historique des transactions
   - Description : Voir toutes les transactions passées
   - Précondition : Utilisateur connecté
   - Postcondition : Liste paginée des transactions

9. Consulter les notifications
   - Description : Voir les notifications (profit, loss, level_up)
   - Précondition : Utilisateur connecté
   - Postcondition : Liste des notifications avec compteur non lues

10. Gérer le profil
    - Description : Modifier nom, prénom, téléphone
    - Précondition : Utilisateur connecté
    - Postcondition : Profil mis à jour

11. Uploader photo de profil
    - Description : Télécharger une image de profil
    - Précondition : Utilisateur connecté
    - Postcondition : Photo sauvegardée et affichée

12. Uploader bannière de profil
    - Description : Télécharger une bannière de profil
    - Précondition : Utilisateur connecté
    - Postcondition : Bannière sauvegardée et affichée

Relations :
- "S'inscrire" <<include>> "Recevoir mot de passe temporaire par email"
- "Se connecter" <<extend>> "Vérifier statut du compte"
- "Acheter" <<include>> "Vérifier solde disponible"
- "Acheter" <<include>> "Mettre à jour le portfolio"
- "Vendre" <<include>> "Vérifier quantité disponible"
- "Vendre" <<include>> "Mettre à jour le portfolio"
- "Consulter portfolio" <<include>> "Calculer plus-value actuelle"
- "Consulter notifications" <<include>> "Marquer comme lues"

Utilisez :
- Acteur CLIENT à gauche
- Cas d'utilisation dans une ellipse
- Relations <<include>> (flèche pointillée avec stéréotype include)
- Relations <<extend>> (flèche pointillée avec stéréotype extend)
- Notes pour expliquer les relations complexes
```

---

## PROMPT 4 : DIAGRAMME DE CAS D'UTILISATION - ADMIN

```
Crée un diagramme de cas d'utilisation UML en français pour un utilisateur ADMIN d'une application de trading de cryptomonnaies.

Acteur principal : ADMIN

Cas d'utilisation principaux :

1. Se connecter
   - Description : Authentification admin
   - Précondition : Compte admin existant
   - Postcondition : Session admin active

2. Consulter le dashboard
   - Description : Voir les statistiques globales de l'application
   - Précondition : Admin connecté
   - Postcondition : Affichage des statistiques (users, transactions, revenus)

3. Créer un utilisateur client
   - Description : Créer un nouveau compte client depuis l'interface admin
   - Précondition : Admin connecté
   - Postcondition : Compte client créé avec solde initial 500€

4. Approuver un utilisateur
   - Description : Changer le statut d'un utilisateur de "pending_validation" à "active"
   - Précondition : Utilisateur en attente de validation
   - Postcondition : Compte activé, solde crédité si nécessaire

5. Bloquer un utilisateur
   - Description : Changer le statut d'un utilisateur à "blocked"
   - Précondition : Utilisateur actif
   - Postcondition : Compte bloqué, accès refusé

6. Débloquer un utilisateur
   - Description : Réactiver un compte bloqué
   - Précondition : Utilisateur bloqué
   - Postcondition : Compte réactivé

7. Modifier les informations d'un utilisateur
   - Description : Modifier nom, prénom d'un utilisateur
   - Précondition : Admin connecté
   - Postcondition : Informations mises à jour

8. Supprimer un utilisateur
   - Description : Supprimer définitivement un compte
   - Précondition : Admin connecté
   - Postcondition : Compte et données associées supprimés

9. Consulter la liste des utilisateurs
   - Description : Voir tous les utilisateurs avec filtres
   - Précondition : Admin connecté
   - Postcondition : Liste paginée des utilisateurs

10. Consulter les détails d'un utilisateur
    - Description : Voir portfolio, transactions, statistiques d'un utilisateur
    - Précondition : Admin connecté
    - Postcondition : Détails complets affichés

11. Consulter toutes les transactions
    - Description : Voir toutes les transactions avec filtres (user, symbol, type)
    - Précondition : Admin connecté
    - Postcondition : Liste paginée des transactions

12. Consulter le marché crypto (admin)
    - Description : Voir les cryptomonnaies avec possibilité de générer les prix
    - Précondition : Admin connecté
    - Postcondition : Liste des cryptos affichée

13. Générer les prix initiaux
    - Description : Exécuter la commande artisan pour générer les prix
    - Précondition : Admin connecté
    - Postcondition : Prix générés pour toutes les cryptos

14. Gérer le profil admin
    - Description : Modifier nom, email, mot de passe admin
    - Précondition : Admin connecté
    - Postcondition : Profil admin mis à jour

Relations :
- "Créer utilisateur" <<include>> "Envoyer mot de passe temporaire par email"
- "Créer utilisateur" <<include>> "Créditer 500€ de solde initial"
- "Approuver utilisateur" <<include>> "Créditer 500€ de solde initial"
- "Approuver utilisateur" <<include>> "Activer le compte"
- "Consulter dashboard" <<include>> "Calculer statistiques en temps réel"
- "Consulter transactions" <<extend>> "Filtrer par critères (user, symbol, type, date)"
- "Consulter utilisateurs" <<extend>> "Filtrer par statut ou rôle"
- "Supprimer utilisateur" <<include>> "Supprimer portfolio et transactions associées"

Utilisez :
- Acteur ADMIN à gauche
- Cas d'utilisation dans une ellipse
- Relations <<include>> et <<extend>>
- Notes explicatives
- Couleur différente pour l'acteur ADMIN
```

---

## PROMPT 5 : DIAGRAMME DE DÉPLOIEMENT

```
Crée un diagramme de déploiement UML en français pour l'application BitChest de trading de cryptomonnaies.

Nœuds de déploiement :

1. NŒUD : CLIENT_NAVIGATEUR
   Artéfacts :
   - Application Vue.js 3 (Frontend)
   - TypeScript
   - Tailwind CSS
   - ApexCharts
   - Three.js
   
   Spécifications :
   - Navigateurs : Chrome, Firefox, Edge, Safari
   - Protocole : HTTPS
   - Port : 443 (production) ou 5173 (développement)

2. NŒUD : SERVEUR_WEB
   Artéfacts :
   - Apache 2.4 / Nginx 1.20+
   - PHP-FPM 8.1+
   - Modules : mod_rewrite, mod_ssl
   
   Spécifications :
   - Protocole : HTTP/HTTPS
   - Port : 80/443
   - SSL/TLS : Oui

3. NŒUD : SERVEUR_APPLICATION
   Artéfacts :
   - Laravel 10.10 (Backend)
   - PHP 8.1+
   - Composer
   - Artisan (CLI)
   
   Spécifications :
   - Framework : Laravel MVC
   - API : REST
   - Authentification : Laravel Sanctum
   - Cache : Redis

4. NŒUD : SERVEUR_BASE_DONNEES
   Artéfacts :
   - MySQL 8.0+ / MariaDB 10.6+
   - Base de données : bitchest_db
   
   Spécifications :
   - Port : 3306
   - Charset : utf8mb4
   - Engine : InnoDB
   - Backup : Automatique quotidien

5. NŒUD : SERVEUR_CACHE
   Artéfacts :
   - Redis 7.0+
   - Persistence : RDB + AOF
   
   Spécifications :
   - Port : 6379
   - Mémoire : Configurable
   - TTL : Variable selon les données

6. NŒUD : SERVEUR_EXTERNE
   Artéfacts :
   - Coinbase API
   
   Spécifications :
   - URL : https://api.coinbase.com/v2
   - Protocole : HTTPS
   - Rate Limit : Respecté

Connexions (Communication) :

1. CLIENT_NAVIGATEUR <--HTTPS--> SERVEUR_WEB
   - Requêtes HTTP/HTTPS
   - Authentification Bearer Token
   - CORS configuré

2. SERVEUR_WEB <--HTTP interne--> SERVEUR_APPLICATION
   - FastCGI (PHP-FPM)
   - Port interne : 9000

3. SERVEUR_APPLICATION <--MySQL Protocol--> SERVEUR_BASE_DONNEES
   - Port : 3306
   - Connexion : PDO/Eloquent
   - Pool de connexions

4. SERVEUR_APPLICATION <--Redis Protocol--> SERVEUR_CACHE
   - Port : 6379
   - Connexion : Predis
   - Cache des prix crypto
   - Cache des portfolios
   - Cache des notifications

5. SERVEUR_APPLICATION <--HTTPS--> SERVEUR_EXTERNE
   - API REST Coinbase
   - Récupération des prix
   - Rate limiting respecté

Stéréotypes :
- <<web server>> pour SERVEUR_WEB
- <<application server>> pour SERVEUR_APPLICATION
- <<database>> pour SERVEUR_BASE_DONNEES
- <<cache>> pour SERVEUR_CACHE
- <<external>> pour SERVEUR_EXTERNE

Utilisez :
- Nœuds en 3D (cubes)
- Artéfacts dans les nœuds
- Connexions avec protocoles
- Couleurs différentes par type de nœud
- Notes pour expliquer les connexions importantes
```

---

## PROMPT 6 : DIAGRAMME DE SÉQUENCE - CONNEXION CLIENT

```
Crée un diagramme de séquence UML en français pour le processus de connexion d'un utilisateur CLIENT.

SCÉNARIO : Un client se connecte avec son email et mot de passe

Acteurs et objets :
- :Client (Frontend Vue.js)
- :ContrôleurAuth (Laravel AuthController)
- :ModèleUtilisateur (Eloquent User Model)
- :BaseDeDonnées (MySQL)
- :ServiceMail (Laravel Mail Service)

Séquence :

1. :Client -> :ContrôleurAuth : POST /api/login {email: "client@example.com", password: "password123"}
   activate :ContrôleurAuth

2. :ContrôleurAuth -> :ContrôleurAuth : Valider la requête (LoginRequest)
   note right : Validation email et password requis

3. :ContrôleurAuth -> :ModèleUtilisateur : attempt(['email', 'password'])
   activate :ModèleUtilisateur
   :ModèleUtilisateur -> :BaseDeDonnées : SELECT * FROM users WHERE email = ?
   :BaseDeDonnées --> :ModèleUtilisateur : User {id: 1, email: "...", password: "$2y$...", role: "client", status: "active"}
   :ModèleUtilisateur -> :ModèleUtilisateur : Hash::check(password, user.password)
   note right : Vérification du hash bcrypt
   :ModèleUtilisateur --> :ContrôleurAuth : User authentifié
   deactivate :ModèleUtilisateur

4. :ContrôleurAuth -> :ContrôleurAuth : Vérifier role === "client"
   note right : Vérification du rôle utilisateur

5. alt must_change_password === true
   :ContrôleurAuth -> :ModèleUtilisateur : createToken('auth-token')
   activate :ModèleUtilisateur
   :ModèleUtilisateur -> :BaseDeDonnées : INSERT INTO personal_access_tokens
   :BaseDeDonnées --> :ModèleUtilisateur : Token créé
   :ModèleUtilisateur --> :ContrôleurAuth : Token
   deactivate :ModèleUtilisateur
   :ContrôleurAuth --> :Client : {user, token, must_change_password: true}
   note right : Redirection vers page changement mot de passe
   
6. else Vérifier statut
   :ContrôleurAuth -> :ContrôleurAuth : Vérifier status
   alt status === "pending_validation"
     :ContrôleurAuth -> :ContrôleurAuth : logout()
     :ContrôleurAuth --> :Client : 403 "Account awaiting admin validation"
     note right : Compte en attente d'approbation admin
   else status === "blocked"
     :ContrôleurAuth -> :ContrôleurAuth : logout()
     :ContrôleurAuth --> :Client : 403 "Account blocked"
     note right : Compte bloqué par l'admin
   else status === "active"
     :ContrôleurAuth -> :ModèleUtilisateur : createToken('auth-token')
     activate :ModèleUtilisateur
     :ModèleUtilisateur -> :BaseDeDonnées : INSERT INTO personal_access_tokens
     :BaseDeDonnées --> :ModèleUtilisateur : Token créé
     :ModèleUtilisateur --> :ContrôleurAuth : Token
     deactivate :ModèleUtilisateur
     :ContrôleurAuth --> :Client : {user, token, must_change_password: false}
     note right : Connexion réussie, accès au dashboard
   end
   end

7. :Client -> :Client : Stocker token dans Pinia store
   note right : Token stocké pour requêtes futures avec header Authorization: Bearer {token}

deactivate :ContrôleurAuth

Utilisez :
- Messages synchrones
- Blocs alt/else pour les conditions
- Notes explicatives
- Activations sur les lignes de vie
- Couleurs pour différencier les chemins (succès en vert, erreur en rouge)
```

---

## PROMPT 7 : DIAGRAMME DE SÉQUENCE - VENTE DE CRYPTO

```
Crée un diagramme de séquence UML détaillé en français pour le processus de VENTE de cryptomonnaie.

SCÉNARIO : Un client vend 0.2 BTC qu'il possède dans son portfolio

Acteurs et objets :
- :Client (Frontend Vue.js)
- :ContrôleurTransaction (Laravel TransactionController)
- :ServiceTransaction (Laravel TransactionService)
- :ServicePortfolio (Laravel PortfolioService)
- :ServiceRedisPrix (Laravel RedisPriceService)
- :ModèleUtilisateur (Eloquent User Model)
- :ModèlePortfolio (Eloquent Model)
- :ModèleTransaction (Eloquent Model)
- :BaseDeDonnées (MySQL)
- :Redis (Cache)

Séquence d'interactions :

1. :Client -> :ContrôleurTransaction : POST /api/transaction/sell {symbol: "BTC", quantity: 0.2}
   activate :ContrôleurTransaction

2. :ContrôleurTransaction -> :ContrôleurTransaction : Valider la requête (SellCryptoRequest)
   note right : Validation des champs symbol et quantity

3. :ContrôleurTransaction -> :ContrôleurTransaction : Vérifier authentification (middleware auth:sanctum)
   note right : Token Bearer vérifié, utilisateur actif

4. :ContrôleurTransaction -> :ContrôleurTransaction : Vérifier que crypto existe et est active
   :ContrôleurTransaction -> :BaseDeDonnées : SELECT * FROM crypto_currencies WHERE symbol = "BTC" AND is_active = true
   :BaseDeDonnées --> :ContrôleurTransaction : CryptoCurrency {id: 1, symbol: "BTC", name: "Bitcoin"}

5. :ContrôleurTransaction -> :ServiceRedisPrix : getPrice("BTC")
   activate :ServiceRedisPrix
   :ServiceRedisPrix -> :Redis : GET crypto:BTC
   :Redis --> :ServiceRedisPrix : {price: 45000, change24h: 2.5}
   :ServiceRedisPrix --> :ContrôleurTransaction : {price: 45000}
   deactivate :ServiceRedisPrix

6. :ContrôleurTransaction -> :ServiceTransaction : processTransaction(user, crypto, 0.2, 45000, "sell")
   activate :ServiceTransaction

7. :ServiceTransaction -> :BaseDeDonnées : BEGIN TRANSACTION
   activate :BaseDeDonnées

8. :ServiceTransaction -> :ModèleUtilisateur : lockForUpdate() WHERE id = user_id
   activate :ModèleUtilisateur
   :ModèleUtilisateur -> :BaseDeDonnées : SELECT * FROM users WHERE id = ? FOR UPDATE
   :BaseDeDonnées --> :ModèleUtilisateur : User {id: 1, euro_balance: 1000}
   deactivate :ModèleUtilisateur

9. :ServiceTransaction -> :ModèlePortfolio : firstOrCreate(user_id, crypto_id)
   activate :ModèlePortfolio
   :ModèlePortfolio -> :BaseDeDonnées : SELECT * FROM portfolios WHERE user_id = ? AND crypto_currency_id = ?
   :BaseDeDonnées --> :ModèlePortfolio : Portfolio {id: 123, total_crypto_value: 22500}
   deactivate :ModèlePortfolio

10. :ServiceTransaction -> :ServiceTransaction : Vérifier quantité disponible
    :ServiceTransaction -> :Redis : GET portfolio:123:quantity:buy
    :Redis --> :ServiceTransaction : 0.5 (quantité totale achetée)
    :ServiceTransaction -> :Redis : GET portfolio:123:quantity:sell
    :Redis --> :ServiceTransaction : 0.1 (quantité déjà vendue)
    :ServiceTransaction -> :ServiceTransaction : availableQuantity = 0.5 - 0.1 = 0.4
    note right : Quantité disponible = 0.4 BTC
    :ServiceTransaction -> :ServiceTransaction : Vérifier 0.2 <= 0.4
    note right : Quantité suffisante pour la vente

11. :ServiceTransaction -> :ModèleUtilisateur : UPDATE users SET euro_balance = 10000 WHERE id = ?
    activate :ModèleUtilisateur
    note right : Créditer le solde : 1000 + (0.2 * 45000) = 10000 EUR
    :ModèleUtilisateur -> :BaseDeDonnées : UPDATE users SET euro_balance = 10000 WHERE id = ?
    :BaseDeDonnées --> :ModèleUtilisateur : OK
    deactivate :ModèleUtilisateur

12. :ServiceTransaction -> :ModèleTransaction : create({portfolio_id: 123, type: "sell", quantity: 0.2, price: 45000, euro_amount: 9000})
    activate :ModèleTransaction
    :ModèleTransaction -> :BaseDeDonnées : INSERT INTO transactions (...)
    :BaseDeDonnées --> :ModèleTransaction : Transaction {id: 789}
    deactivate :ModèleTransaction

13. :ServiceTransaction -> :ServicePortfolio : updatePortfolio(portfolio, transaction, 0.2, 45000, "sell")
    activate :ServicePortfolio
    :ServicePortfolio -> :ServicePortfolio : Calculer quantité avant vente
    :ServicePortfolio -> :ServicePortfolio : averageInvestedValue = total_crypto_value / totalQuantityBefore
    note right : Calcul de la valeur moyenne investie par unité
    :ServicePortfolio -> :ModèlePortfolio : UPDATE portfolios SET total_crypto_value = total_crypto_value - (0.2 * averageInvestedValue)
    :ModèlePortfolio -> :BaseDeDonnées : UPDATE portfolios SET total_crypto_value = 13500 WHERE id = 123
    :BaseDeDonnées --> :ModèlePortfolio : OK
    deactivate :ModèlePortfolio
    :ServicePortfolio -> :ServicePortfolio : Invalider cache portfolio
    deactivate :ServicePortfolio

14. :ServiceTransaction -> :ServiceTransaction : Déclencher événement TransactionCreated
    note right : Event-driven architecture pour notifications

15. :ServiceTransaction -> :ServiceTransaction : checkAndCreatePortfolioNotifications(user)
    note right : Vérification des notifications profit/loss

16. :ServiceTransaction -> :BaseDeDonnées : COMMIT TRANSACTION
    :BaseDeDonnées --> :ServiceTransaction : OK
    deactivate :BaseDeDonnées

17. :ServiceTransaction --> :ContrôleurTransaction : Transaction {id: 789, ...}
    deactivate :ServiceTransaction

18. :ContrôleurTransaction -> :Redis : SET user:1:balance 10000
    activate :Redis
    :Redis --> :ContrôleurTransaction : OK
    deactivate :Redis

19. :ContrôleurTransaction -> :Redis : INCR portfolio:123:quantity:sell
    activate :Redis
    :Redis --> :ContrôleurTransaction : OK
    deactivate :Redis

20. :ContrôleurTransaction --> :Client : JSON {transaction: {...}, balance: 10000, message: "Vente effectuée"}
    deactivate :ContrôleurTransaction

Utilisez :
- Messages synchrones (flèches pleines)
- Messages de retour (flèches en pointillés)
- Activations (rectangles sur la ligne de vie)
- Notes pour expliquer les calculs importants
- Couleurs pour différencier les couches (Frontend, Controller, Service, Model, Database)
```

---

## PROMPT 8 : DIAGRAMME DE SÉQUENCE - INSCRIPTION CLIENT JUSQU'À CONNEXION

```
Crée un diagramme de séquence UML complet en français pour le processus d'inscription d'un client jusqu'à sa première connexion.

SCÉNARIO COMPLET : Un nouveau client s'inscrit, reçoit un mot de passe temporaire, change son mot de passe, puis se connecte

Acteurs et objets :
- :Client (Frontend Vue.js)
- :ContrôleurAuth (Laravel AuthController)
- :ModèleUtilisateur (Eloquent User Model)
- :ServiceMail (Laravel UniversalMailService)
- :BaseDeDonnées (MySQL)
- :ServeurEmail (SMTP Server)

PARTIE 1 : INSCRIPTION

1. :Client -> :ContrôleurAuth : POST /api/register {first_name: "Jean", last_name: "Dupont", email: "jean@example.com", email_confirmation: "jean@example.com"}
   activate :ContrôleurAuth

2. :ContrôleurAuth -> :ContrôleurAuth : Valider la requête
   note right : Validation : first_name, last_name, email unique, email_confirmation

3. :ContrôleurAuth -> :ContrôleurAuth : Générer mot de passe temporaire (8 chiffres)
   note right : random_int(10000000, 99999999)

4. :ContrôleurAuth -> :ModèleUtilisateur : create({name: "Jean Dupont", email: "jean@example.com", password: Hash::make(tempPassword), role: "client", status: "pending", must_change_password: true, euro_balance: 0})
   activate :ModèleUtilisateur
   :ModèleUtilisateur -> :BaseDeDonnées : INSERT INTO users (...)
   :BaseDeDonnées --> :ModèleUtilisateur : User {id: 5, status: "pending"}
   deactivate :ModèleUtilisateur

5. :ContrôleurAuth -> :ServiceMail : send(TemporaryPasswordMailable, "jean@example.com")
   activate :ServiceMail
   :ServiceMail -> :ServeurEmail : Envoyer email avec mot de passe temporaire
   :ServeurEmail --> :ServiceMail : Email envoyé
   deactivate :ServiceMail

6. :ContrôleurAuth --> :Client : {message: "Account created. A temporary password has been sent to your email.", status: "pending", must_change_password: true}
   deactivate :ContrôleurAuth

PARTIE 2 : CHANGEMENT DE MOT DE PASSE

7. :Client -> :Client : Lire email et récupérer mot de passe temporaire
   note right : Exemple : 87654321

8. :Client -> :ContrôleurAuth : POST /api/login {email: "jean@example.com", password: "87654321"}
   activate :ContrôleurAuth

9. :ContrôleurAuth -> :ModèleUtilisateur : attempt(['email', 'password'])
   activate :ModèleUtilisateur
   :ModèleUtilisateur -> :BaseDeDonnées : SELECT * FROM users WHERE email = ?
   :BaseDeDonnées --> :ModèleUtilisateur : User {must_change_password: true}
   :ModèleUtilisateur -> :ModèleUtilisateur : Hash::check(password, user.password)
   :ModèleUtilisateur --> :ContrôleurAuth : User authentifié
   deactivate :ModèleUtilisateur

10. :ContrôleurAuth -> :ContrôleurAuth : Vérifier must_change_password === true
    :ContrôleurAuth -> :ModèleUtilisateur : createToken('auth-token')
    :ModèleUtilisateur --> :ContrôleurAuth : Token
    :ContrôleurAuth --> :Client : {user, token, must_change_password: true}
    note right : Redirection automatique vers page changement mot de passe

11. :Client -> :ContrôleurAuth : POST /api/change-password {current_password: "87654321", password: "newPassword123", password_confirmation: "newPassword123"}
    activate :ContrôleurAuth

12. :ContrôleurAuth -> :ContrôleurAuth : Valider la requête
    note right : Validation : current_password correct, password confirmé, min 6 caractères

13. :ContrôleurAuth -> :ModèleUtilisateur : UPDATE users SET password = Hash::make("newPassword123"), must_change_password = false, status = "pending_validation"
    activate :ModèleUtilisateur
    :ModèleUtilisateur -> :BaseDeDonnées : UPDATE users SET password = ?, must_change_password = false, status = "pending_validation"
    :BaseDeDonnées --> :ModèleUtilisateur : OK
    deactivate :ModèleUtilisateur

14. :ContrôleurAuth --> :Client : {message: "Password changed successfully. Your account is now awaiting admin validation."}
    deactivate :ContrôleurAuth

PARTIE 3 : ATTENTE APPROBATION ADMIN

15. note over :Client : L'utilisateur attend l'approbation de l'admin
    note right : Statut : "pending_validation"

PARTIE 4 : CONNEXION APRÈS APPROBATION

16. :Client -> :ContrôleurAuth : POST /api/login {email: "jean@example.com", password: "newPassword123"}
    activate :ContrôleurAuth

17. :ContrôleurAuth -> :ModèleUtilisateur : attempt(['email', 'password'])
    activate :ModèleUtilisateur
    :ModèleUtilisateur -> :BaseDeDonnées : SELECT * FROM users WHERE email = ?
    :BaseDeDonnées --> :ModèleUtilisateur : User {status: "active", must_change_password: false, euro_balance: 500}
    note right : Admin a approuvé et crédité 500€
    :ModèleUtilisateur --> :ContrôleurAuth : User authentifié
    deactivate :ModèleUtilisateur

18. :ContrôleurAuth -> :ContrôleurAuth : Vérifier status === "active"
    :ContrôleurAuth -> :ModèleUtilisateur : createToken('auth-token')
    :ModèleUtilisateur -> :BaseDeDonnées : INSERT INTO personal_access_tokens
    :BaseDeDonnées --> :ModèleUtilisateur : Token créé
    :ModèleUtilisateur --> :ContrôleurAuth : Token

19. :ContrôleurAuth --> :Client : {user, token, must_change_password: false}
    note right : Connexion réussie, accès complet au dashboard

20. :Client -> :Client : Stocker token dans Pinia store
    :Client -> :Client : Rediriger vers dashboard
    deactivate :ContrôleurAuth

Utilisez :
- Messages synchrones
- Notes pour séparer les parties (Inscription, Changement mot de passe, Attente, Connexion)
- Activations sur les lignes de vie
- Couleurs différentes pour chaque partie du processus
- Diagramme horizontal pour montrer la chronologie complète
```

---

## PROMPT 9 : DIAGRAMME DE SÉQUENCE - AUTHENTIFICATION ADMIN

```
Crée un diagramme de séquence UML en français pour le processus d'authentification d'un ADMIN.

SCÉNARIO : Un administrateur se connecte à l'interface d'administration

Acteurs et objets :
- :Admin (Frontend Vue.js - Interface Admin)
- :ContrôleurAuth (Laravel AuthController)
- :ModèleUtilisateur (Eloquent User Model)
- :BaseDeDonnées (MySQL)
- :MiddlewareRole (Laravel CheckUserRole Middleware)

Séquence :

1. :Admin -> :ContrôleurAuth : POST /api/login {email: "admin@bitchest.com", password: "admin123"}
   activate :ContrôleurAuth

2. :ContrôleurAuth -> :ContrôleurAuth : Valider la requête (LoginRequest)
   note right : Validation email et password requis

3. :ContrôleurAuth -> :ModèleUtilisateur : attempt(['email', 'password'])
   activate :ModèleUtilisateur
   :ModèleUtilisateur -> :BaseDeDonnées : SELECT * FROM users WHERE email = ?
   :BaseDeDonnées --> :ModèleUtilisateur : User {id: 1, email: "admin@bitchest.com", password: "$2y$...", role: "admin", status: "active"}
   :ModèleUtilisateur -> :ModèleUtilisateur : Hash::check(password, user.password)
   note right : Vérification du hash bcrypt
   :ModèleUtilisateur --> :ContrôleurAuth : User authentifié
   deactivate :ModèleUtilisateur

4. :ContrôleurAuth -> :ContrôleurAuth : Vérifier role === "admin"
   note right : Vérification du rôle administrateur

5. :ContrôleurAuth -> :ModèleUtilisateur : createToken('auth-token')
   activate :ModèleUtilisateur
   :ModèleUtilisateur -> :BaseDeDonnées : INSERT INTO personal_access_tokens (tokenable_type, tokenable_id, name, token, abilities)
   note right : Token créé avec abilities pour admin
   :BaseDeDonnées --> :ModèleUtilisateur : Token créé
   :ModèleUtilisateur --> :ContrôleurAuth : Token
   deactivate :ModèleUtilisateur

6. :ContrôleurAuth --> :Admin : {user: {id: 1, name: "Super Admin", role: "admin", ...}, token: "1|abc123...", must_change_password: false}
   note right : Token admin avec accès complet

7. :Admin -> :Admin : Stocker token dans Pinia store
   note right : Token stocké pour requêtes futures

8. :Admin -> :Admin : Rediriger vers /admin/dashboard
   note right : Accès à l'interface d'administration

9. :Admin -> :ContrôleurAuth : GET /api/admin/dashboard (Header: Authorization: Bearer {token})
   activate :ContrôleurAuth

10. :ContrôleurAuth -> :MiddlewareRole : Vérifier role === "admin"
    activate :MiddlewareRole
    :MiddlewareRole -> :BaseDeDonnées : Vérifier token et rôle
    :BaseDeDonnées --> :MiddlewareRole : User {role: "admin"}
    :MiddlewareRole --> :ContrôleurAuth : Accès autorisé
    deactivate :MiddlewareRole

11. :ContrôleurAuth -> :ContrôleurAuth : Récupérer statistiques dashboard
    :ContrôleurAuth -> :BaseDeDonnées : Requêtes pour statistiques (users, transactions, etc.)
    :BaseDeDonnées --> :ContrôleurAuth : Données statistiques

12. :ContrôleurAuth --> :Admin : {statistics: {...}, users_count: 50, transactions_count: 1200, ...}
    deactivate :ContrôleurAuth

Utilisez :
- Messages synchrones
- Notes explicatives
- Activations sur les lignes de vie
- Couleur différente pour l'acteur Admin (rouge ou violet)
- Montrer le flux complet de connexion jusqu'à l'accès au dashboard
```

---

## PROMPT 10 : DIAGRAMME DE SÉQUENCE - CRÉATION D'UTILISATEUR PAR ADMIN

```
Crée un diagramme de séquence UML en français pour le processus de création d'un utilisateur client par un administrateur.

SCÉNARIO : Un admin crée un nouveau compte client avec nom et email, le système génère un mot de passe temporaire et crédite 500€

Acteurs et objets :
- :Admin (Frontend Vue.js - Interface Admin)
- :ContrôleurAdminUser (Laravel Admin\UserController)
- :ServiceUser (Laravel UserService)
- :ModèleUtilisateur (Eloquent User Model)
- :ServiceMail (Laravel UniversalMailService)
- :BaseDeDonnées (MySQL)
- :ServeurEmail (SMTP Server)

Séquence :

1. :Admin -> :ContrôleurAdminUser : POST /api/admin/users {name: "Marie Martin", email: "marie@example.com"}
   activate :ContrôleurAdminUser

2. :ContrôleurAdminUser -> :ContrôleurAdminUser : Valider la requête (StoreUserRequest)
   note right : Validation : name et email requis, email unique

3. :ContrôleurAdminUser -> :ContrôleurAdminUser : Vérifier authentification admin (middleware auth:sanctum + role:admin)
   note right : Token Bearer vérifié, rôle admin confirmé

4. :ContrôleurAdminUser -> :ServiceUser : createClient("Marie Martin", "marie@example.com")
   activate :ServiceUser

5. :ServiceUser -> :ServiceUser : Générer mot de passe temporaire (12 caractères aléatoires)
   note right : Str::random(12) - Exemple : "aB3xY9mK2pQ7"

6. :ServiceUser -> :ServiceUser : Séparer prénom et nom
   note right : first_name = "Marie", last_name = "Martin"

7. :ServiceUser -> :ModèleUtilisateur : create({name: "Marie Martin", first_name: "Marie", last_name: "Martin", email: "marie@example.com", password: Hash::make("aB3xY9mK2pQ7"), role: "client", status: "pending", must_change_password: true, euro_balance: 500.00})
   activate :ModèleUtilisateur
   :ModèleUtilisateur -> :BaseDeDonnées : INSERT INTO users (name, first_name, last_name, email, password, role, status, must_change_password, euro_balance, created_at, updated_at) VALUES (...)
   :BaseDeDonnées --> :ModèleUtilisateur : User {id: 10, status: "pending", euro_balance: 500.00}
   deactivate :ModèleUtilisateur

8. :ServiceUser -> :ServiceMail : send(TemporaryPasswordMailable("aB3xY9mK2pQ7", "Marie Martin"), "marie@example.com")
   activate :ServiceMail
   :ServiceMail -> :ServeurEmail : Envoyer email avec mot de passe temporaire
   note right : Email avec template temp_password.blade.php
   :ServeurEmail --> :ServiceMail : Email envoyé
   deactivate :ServiceMail

9. :ServiceUser --> :ContrôleurAdminUser : {user: User{id: 10, ...}, password: "aB3xY9mK2pQ7"}
   deactivate :ServiceUser

10. :ContrôleurAdminUser --> :Admin : {message: "User created successfully. A temporary password has been sent to the user's email. They must change it in their private area. For the prototype phase, the account is credited with €500.", user: {id: 10, name: "Marie Martin", email: "marie@example.com", status: "pending", euro_balance: 500.00}}
    note right : Compte créé avec solde initial de 500€
    deactivate :ContrôleurAdminUser

11. :Admin -> :Admin : Afficher message de succès et mettre à jour la liste des utilisateurs
    note right : Interface admin mise à jour

Utilisez :
- Messages synchrones
- Notes explicatives pour chaque étape
- Activations sur les lignes de vie
- Couleur différente pour l'acteur Admin
- Montrer le flux complet de création jusqu'à l'envoi de l'email
```

---

## PROMPT 11 : DIAGRAMME DE SÉQUENCE - APPROBATION D'UTILISATEUR PAR ADMIN

```
Crée un diagramme de séquence UML en français pour le processus d'approbation d'un utilisateur client par un administrateur.

SCÉNARIO : Un admin approuve un utilisateur en attente de validation, le compte est activé et crédité de 500€

Acteurs et objets :
- :Admin (Frontend Vue.js - Interface Admin)
- :ContrôleurAdminUser (Laravel Admin\UserController)
- :ModèleUtilisateur (Eloquent User Model)
- :BaseDeDonnées (MySQL)

Séquence :

1. :Admin -> :Admin : Consulter la liste des utilisateurs en attente
   note right : Interface admin - Liste des utilisateurs avec status "pending_validation"

2. :Admin -> :ContrôleurAdminUser : POST /api/admin/users/10/approve
   activate :ContrôleurAdminUser
   note right : ID 10 = utilisateur à approuver

3. :ContrôleurAdminUser -> :ContrôleurAdminUser : Vérifier authentification admin (middleware auth:sanctum + role:admin)
   note right : Token Bearer vérifié, rôle admin confirmé

4. :ContrôleurAdminUser -> :ModèleUtilisateur : findOrFail(10)
   activate :ModèleUtilisateur
   :ModèleUtilisateur -> :BaseDeDonnées : SELECT * FROM users WHERE id = 10
   :BaseDeDonnées --> :ModèleUtilisateur : User {id: 10, name: "Marie Martin", status: "pending_validation", euro_balance: 500.00}
   :ModèleUtilisateur --> :ContrôleurAdminUser : User trouvé
   deactivate :ModèleUtilisateur

5. :ContrôleurAdminUser -> :ContrôleurAdminUser : Vérifier si user.isActive()
   note right : Vérification : status === "active" ?

6. alt Utilisateur déjà actif
   :ContrôleurAdminUser --> :Admin : 400 {message: "User already active"}
   note right : Erreur si déjà approuvé
   
7. else Utilisateur en attente
   :ContrôleurAdminUser -> :ModèleUtilisateur : update({status: "active", must_change_password: false, euro_balance: 500.00, email_verified_at: now()})
   activate :ModèleUtilisateur
   note right : Activation du compte avec solde de 500€
   :ModèleUtilisateur -> :BaseDeDonnées : UPDATE users SET status = "active", must_change_password = false, euro_balance = 500.00, email_verified_at = NOW() WHERE id = 10
   :BaseDeDonnées --> :ModèleUtilisateur : OK
   deactivate :ModèleUtilisateur

8. :ContrôleurAdminUser -> :ModèleUtilisateur : fresh()
   activate :ModèleUtilisateur
   :ModèleUtilisateur -> :BaseDeDonnées : SELECT * FROM users WHERE id = 10
   :BaseDeDonnées --> :ModèleUtilisateur : User {id: 10, status: "active", euro_balance: 500.00, email_verified_at: "2025-01-27 10:30:00"}
   :ModèleUtilisateur --> :ContrôleurAdminUser : User mis à jour
   deactivate :ModèleUtilisateur

9. :ContrôleurAdminUser --> :Admin : {message: "User approved and account activated", user: {id: 10, name: "Marie Martin", email: "marie@example.com", status: "active", euro_balance: 500.00, email_verified_at: "2025-01-27 10:30:00"}}
   note right : Compte activé avec succès
   end

10. :Admin -> :Admin : Afficher message de succès et mettre à jour la liste
    note right : Interface admin mise à jour, utilisateur maintenant "active"

11. note over :Admin : L'utilisateur peut maintenant se connecter et utiliser l'application
    note right : Statut passé de "pending_validation" à "active"

deactivate :ContrôleurAdminUser

Utilisez :
- Messages synchrones
- Blocs alt/else pour les conditions
- Notes explicatives
- Activations sur les lignes de vie
- Couleur différente pour l'acteur Admin
- Montrer le changement de statut avant/après
```

---

---

## RÉCAPITULATIF DES DIAGRAMMES

### Diagrammes de Classes
- **PROMPT 1** : Diagramme de classes - Modèles de données (Users, Portfolios, Transactions, Cryptos, Notifications)

### Diagrammes de Séquence
- **PROMPT 2** : Achat de cryptomonnaie (Buy)
- **PROMPT 6** : Connexion client
- **PROMPT 7** : Vente de cryptomonnaie (Sell)
- **PROMPT 8** : Inscription client jusqu'à connexion (Register → Change Password → Login)
- **PROMPT 9** : Authentification admin
- **PROMPT 10** : Création d'utilisateur par admin
- **PROMPT 11** : Approbation d'utilisateur par admin

### Diagrammes de Cas d'Utilisation
- **PROMPT 3** : Cas d'utilisation - Client
- **PROMPT 4** : Cas d'utilisation - Admin

### Diagrammes de Déploiement
- **PROMPT 5** : Architecture de déploiement (Frontend, Backend, Database, Redis, API externe)

---

## SCÉNARIOS DÉTAILLÉS PAR DIAGRAMME

### PROMPT 2 - Achat de Crypto
**Scénario** : Client achète 0.5 BTC à 45000€
- Vérification solde
- Création portfolio si nécessaire
- Débit solde utilisateur
- Création transaction
- Mise à jour portfolio
- Notification

### PROMPT 7 - Vente de Crypto
**Scénario** : Client vend 0.2 BTC qu'il possède
- Vérification quantité disponible
- Calcul valeur moyenne investie
- Crédit solde utilisateur
- Création transaction
- Mise à jour portfolio (réduction proportionnelle)
- Notification

### PROMPT 8 - Inscription jusqu'à Connexion
**Scénario complet** :
1. Inscription → Génération mot de passe temporaire → Email envoyé
2. Connexion avec mot de passe temporaire → Redirection changement mot de passe
3. Changement mot de passe → Statut passe à "pending_validation"
4. Attente approbation admin
5. Connexion après approbation → Accès dashboard

### PROMPT 9 - Authentification Admin
**Scénario** : Admin se connecte et accède au dashboard
- Vérification rôle admin
- Génération token avec permissions admin
- Accès interface d'administration
- Récupération statistiques

### PROMPT 10 - Création Utilisateur par Admin
**Scénario** : Admin crée un compte client
- Génération mot de passe temporaire (12 caractères)
- Création compte avec solde initial 500€
- Envoi email avec mot de passe temporaire
- Statut initial : "pending"

### PROMPT 11 - Approbation Utilisateur par Admin
**Scénario** : Admin approuve un utilisateur en attente
- Vérification statut actuel
- Activation compte (status → "active")
- Crédit 500€ si nécessaire
- Email vérifié
- Utilisateur peut maintenant se connecter

---

## INSTRUCTIONS D'UTILISATION

### Étape 1 : Choisir le diagramme
Consultez le récapitulatif ci-dessus pour trouver le diagramme qui correspond à votre besoin.

### Étape 2 : Copier le prompt
1. Ouvrez le fichier `PROMPTS_CHATUML.md`
2. Trouvez le prompt correspondant (PROMPT 1 à 11)
3. Copiez tout le contenu entre les triple backticks (```)

### Étape 3 : Utiliser dans ChatUML
1. Allez sur https://www.chatuml.com/
2. Collez le prompt dans la zone de texte
3. Cliquez sur "Generate" ou "Create Diagram"
4. ChatUML va générer le diagramme UML automatiquement

### Étape 4 : Personnaliser (optionnel)
- Ajustez les couleurs si nécessaire
- Modifiez les noms d'acteurs/objets selon vos préférences
- Ajoutez des notes supplémentaires

### Étape 5 : Exporter
1. Cliquez sur "Export" dans ChatUML
2. Choisissez le format : PNG (pour rapport), SVG (pour qualité), ou PDF
3. Téléchargez le fichier

### Étape 6 : Intégrer dans votre rapport
- Insérez le diagramme dans votre document académique
- Ajoutez une légende et une description
- Référencez le diagramme dans votre texte

---

## CONSEILS POUR VOTRE RAPPORT ACADÉMIQUE

### Ordre recommandé des diagrammes :
1. **Diagramme de déploiement** (PROMPT 5) - Vue d'ensemble de l'architecture
2. **Diagramme de classes** (PROMPT 1) - Structure des données
3. **Diagrammes de cas d'utilisation** (PROMPT 3 et 4) - Fonctionnalités
4. **Diagrammes de séquence** (PROMPT 2, 6, 7, 8, 9, 10, 11) - Processus détaillés

### Pour chaque diagramme, incluez :
- **Titre** : "Diagramme de [type] - [nom du processus]"
- **Description** : 2-3 phrases expliquant ce que montre le diagramme
- **Légende** : Explication des symboles utilisés (si nécessaire)
- **Analyse** : 1-2 paragraphes expliquant le processus représenté

### Exemple de description pour un diagramme de séquence :
```
Ce diagramme de séquence illustre le processus complet d'achat de cryptomonnaie 
par un utilisateur client. Le processus commence par la validation de la requête 
et la vérification de l'authentification, puis récupère le prix actuel depuis 
Redis. Une transaction de base de données est ouverte pour garantir l'intégrité 
des données, le solde utilisateur est vérifié et débité, et le portfolio est 
mis à jour. Le processus se termine par la création d'une notification et 
l'invalidation du cache pour garantir la cohérence des données.
```

---

## NOTES IMPORTANTES

### Relations dans le diagramme de classes :
- **User ↔ Portfolio** : Relation 1:N (un utilisateur peut avoir plusieurs portfolios, un par crypto)
- **Portfolio ↔ Transaction** : Relation 1:N (un portfolio contient plusieurs transactions)
- **Portfolio ↔ Notification** : Relation 1:N (un portfolio peut générer plusieurs notifications)
- **User ↔ Notification** : Relation 1:N (un utilisateur reçoit plusieurs notifications)

### Statuts utilisateur :
- **pending** : Compte créé, en attente de changement de mot de passe
- **pending_validation** : Mot de passe changé, en attente d'approbation admin
- **active** : Compte approuvé par admin, peut utiliser l'application
- **blocked** : Compte bloqué par admin

### Types de transactions :
- **buy** : Achat de cryptomonnaie (débit solde EUR, crédit crypto)
- **sell** : Vente de cryptomonnaie (crédit solde EUR, débit crypto)

---

**Document créé le** : 2025-01-27  
**Version** : 2.0  
**Dernière mise à jour** : Ajout des diagrammes de séquence manquants (Vente, Inscription, Auth Admin, Création User, Approbation User)
