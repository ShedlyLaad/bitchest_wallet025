# SCÉNARIOS DÉTAILLÉS POUR DIAGRAMMES UML - BITCHEST

## 📋 TABLE DES MATIÈRES

1. [Scénario 1 : Achat de Cryptomonnaie](#scénario-1-achat-de-cryptomonnaie)
2. [Scénario 2 : Vente de Cryptomonnaie](#scénario-2-vente-de-cryptomonnaie)
3. [Scénario 3 : Inscription Client jusqu'à Connexion](#scénario-3-inscription-client-jusquà-connexion)
4. [Scénario 4 : Connexion Client](#scénario-4-connexion-client)
5. [Scénario 5 : Authentification Admin](#scénario-5-authentification-admin)
6. [Scénario 6 : Création d'Utilisateur par Admin](#scénario-6-création-dutilisateur-par-admin)
7. [Scénario 7 : Approbation d'Utilisateur par Admin](#scénario-7-approbation-dutilisateur-par-admin)

---

## SCÉNARIO 1 : ACHAT DE CRYPTOMONNAIE

### 📝 Description Générale

Ce scénario décrit le processus complet d'achat de cryptomonnaie par un utilisateur client. Le processus garantit la sécurité des transactions, la cohérence des données et la mise à jour en temps réel du portfolio de l'utilisateur.

### 🎯 Objectif

Permettre à un client authentifié d'acheter une quantité déterminée de cryptomonnaie en utilisant son solde en euros, tout en garantissant l'intégrité transactionnelle et la cohérence des données.

### 👤 Acteurs Principaux

- **Client** : Utilisateur authentifié souhaitant effectuer un achat
- **Système Backend** : Application Laravel gérant la logique métier
- **Base de Données** : MySQL stockant les données utilisateurs et transactions
- **Cache Redis** : Système de cache pour les prix et les données fréquemment consultées

### 📊 Étapes du Processus

#### **Étape 1 : Initiation de la Requête**

Le client, depuis l'interface web, sélectionne une cryptomonnaie et indique la quantité qu'il souhaite acheter. L'application frontend envoie une requête HTTP POST vers l'API backend avec les informations nécessaires : le symbole de la cryptomonnaie et la quantité désirée.

#### **Étape 2 : Validation et Authentification**

Le contrôleur backend reçoit la requête et procède à plusieurs vérifications :
- **Validation des données** : Vérification que les champs requis sont présents et valides
- **Authentification** : Vérification du token d'authentification Bearer pour confirmer l'identité de l'utilisateur
- **Vérification du statut** : Confirmation que le compte utilisateur est actif et autorisé à effectuer des transactions

#### **Étape 3 : Récupération du Prix Actuel**

Le système interroge le cache Redis pour obtenir le prix actuel de la cryptomonnaie. Si le prix n'est pas disponible dans le cache, le système peut faire appel à l'API externe Coinbase pour récupérer le prix en temps réel. Cette approche optimise les performances en évitant des appels API inutiles.

#### **Étape 4 : Ouverture de Transaction de Base de Données**

Pour garantir l'intégrité des données et éviter les conditions de course (race conditions), le système ouvre une transaction de base de données. Cette transaction assure que toutes les opérations suivantes seront exécutées de manière atomique : soit toutes réussissent, soit toutes échouent.

#### **Étape 5 : Vérification et Verrouillage du Solde**

Le système verrouille la ligne de l'utilisateur dans la base de données pour empêcher d'autres transactions simultanées de modifier le solde. Il vérifie ensuite que le solde disponible est suffisant pour effectuer l'achat. Si le solde est insuffisant, la transaction est annulée et un message d'erreur est retourné au client.

#### **Étape 6 : Gestion du Portfolio**

Le système vérifie si l'utilisateur possède déjà un portfolio pour cette cryptomonnaie. Si aucun portfolio n'existe, il en crée un nouveau avec une valeur initiale de zéro. Cette approche permet à chaque utilisateur d'avoir un portfolio distinct pour chaque type de cryptomonnaie.

#### **Étape 7 : Débit du Solde Utilisateur**

Le montant total de l'achat est calculé (quantité × prix unitaire) et débité du solde en euros de l'utilisateur. Cette opération est effectuée de manière atomique dans le cadre de la transaction de base de données.

#### **Étape 8 : Création de l'Enregistrement de Transaction**

Un nouvel enregistrement est créé dans la table des transactions, contenant toutes les informations pertinentes : l'identifiant du portfolio, le type de transaction (achat), la quantité, le prix au moment de la transaction, et le montant total en euros.

#### **Étape 9 : Mise à Jour du Portfolio**

Le portfolio de l'utilisateur est mis à jour pour refléter le nouvel achat. La valeur totale investie dans cette cryptomonnaie est augmentée du montant de l'achat. Cette valeur sera utilisée ultérieurement pour calculer les plus-values et les pertes.

#### **Étape 10 : Validation et Finalisation**

La transaction de base de données est validée (commit), ce qui rend toutes les modifications permanentes. Le système invalide les caches concernés pour garantir que les données affichées à l'utilisateur seront à jour lors des prochaines requêtes.

#### **Étape 11 : Notification et Réponse**

Le système déclenche un événement pour notifier l'utilisateur de la transaction réussie. Une réponse JSON est envoyée au client contenant les détails de la transaction et le nouveau solde de l'utilisateur.

### 🔒 Sécurité et Intégrité

- **Verrouillage de ligne** : Empêche les modifications concurrentes du solde
- **Transaction atomique** : Garantit la cohérence des données
- **Validation stricte** : Vérifie toutes les conditions avant d'exécuter l'opération
- **Authentification requise** : Seuls les utilisateurs authentifiés peuvent effectuer des achats

### 📈 Optimisations

- **Cache Redis** : Réduction des appels API externes
- **Index de base de données** : Accélération des requêtes
- **Invalidation de cache** : Garantit la cohérence des données affichées

---

## SCÉNARIO 2 : VENTE DE CRYPTOMONNAIE

### 📝 Description Générale

Ce scénario décrit le processus de vente de cryptomonnaie par un utilisateur client. Contrairement à l'achat, la vente nécessite de vérifier que l'utilisateur possède suffisamment de cryptomonnaie dans son portfolio avant d'autoriser la transaction.

### 🎯 Objectif

Permettre à un client authentifié de vendre une quantité déterminée de cryptomonnaie qu'il possède, en créditant son solde en euros et en mettant à jour son portfolio de manière proportionnelle.

### 👤 Acteurs Principaux

- **Client** : Utilisateur authentifié possédant de la cryptomonnaie
- **Système Backend** : Application Laravel gérant la logique métier
- **Base de Données** : MySQL stockant les données utilisateurs et transactions
- **Cache Redis** : Système de cache pour les quantités et les prix

### 📊 Étapes du Processus

#### **Étape 1 : Initiation de la Requête**

Le client sélectionne une cryptomonnaie qu'il possède et indique la quantité qu'il souhaite vendre. L'application frontend envoie une requête HTTP POST vers l'API backend avec le symbole de la cryptomonnaie et la quantité à vendre.

#### **Étape 2 : Validation et Authentification**

Comme pour l'achat, le système valide les données, vérifie l'authentification et confirme que le compte utilisateur est actif et autorisé.

#### **Étape 3 : Vérification de l'Existence de la Cryptomonnaie**

Le système vérifie que la cryptomonnaie demandée existe dans la base de données et qu'elle est active. Si la cryptomonnaie n'existe pas ou est inactive, la transaction est refusée.

#### **Étape 4 : Récupération du Prix Actuel**

Le système récupère le prix actuel de la cryptomonnaie depuis le cache Redis ou l'API externe, de la même manière que pour un achat.

#### **Étape 5 : Ouverture de Transaction de Base de Données**

Une transaction de base de données est ouverte pour garantir l'intégrité des opérations.

#### **Étape 6 : Vérification de la Quantité Disponible**

Cette étape est cruciale pour une vente. Le système calcule la quantité totale de cryptomonnaie que l'utilisateur possède en :
- Additionnant toutes les transactions d'achat pour cette cryptomonnaie
- Soustrayant toutes les transactions de vente déjà effectuées
- Comparant le résultat avec la quantité demandée

Si la quantité disponible est insuffisante, la transaction est refusée avec un message d'erreur explicite.

#### **Étape 7 : Calcul de la Valeur Moyenne Investie**

Avant de mettre à jour le portfolio, le système calcule la valeur moyenne investie par unité de cryptomonnaie. Ce calcul est nécessaire pour déterminer correctement la valeur restante dans le portfolio après la vente.

#### **Étape 8 : Crédit du Solde Utilisateur**

Le montant total de la vente est calculé (quantité × prix unitaire) et crédité au solde en euros de l'utilisateur. Cette opération augmente le solde disponible de l'utilisateur.

#### **Étape 9 : Création de l'Enregistrement de Transaction**

Un nouvel enregistrement de transaction de type "vente" est créé avec toutes les informations pertinentes.

#### **Étape 10 : Mise à Jour Proportionnelle du Portfolio**

Le portfolio est mis à jour de manière proportionnelle. La valeur totale investie est réduite en fonction de la quantité vendue et de la valeur moyenne investie par unité. Cette méthode garantit que le calcul des plus-values reste cohérent même après plusieurs ventes partielles.

#### **Étape 11 : Validation et Finalisation**

La transaction de base de données est validée, les caches sont invalidés, et le système prépare la réponse pour le client.

#### **Étape 12 : Notification et Réponse**

Le système envoie une réponse JSON au client contenant les détails de la transaction de vente et le nouveau solde en euros.

### 🔒 Sécurité et Intégrité

- **Vérification de quantité** : Empêche la vente de cryptomonnaies non possédées
- **Calcul proportionnel** : Garantit la cohérence des valeurs du portfolio
- **Transaction atomique** : Assure l'intégrité des données
- **Cache des quantités** : Optimise les calculs de disponibilité

### 📈 Points Techniques Importants

- **Calcul de la valeur moyenne** : Nécessaire pour maintenir la cohérence du portfolio après plusieurs transactions
- **Réduction proportionnelle** : La valeur investie est réduite proportionnellement à la quantité vendue
- **Gestion des quantités** : Utilisation du cache pour accélérer les calculs de disponibilité

---

## SCÉNARIO 3 : INSCRIPTION CLIENT JUSQU'À CONNEXION

### 📝 Description Générale

Ce scénario décrit le parcours complet d'un nouvel utilisateur depuis son inscription jusqu'à sa première connexion réussie. Ce processus comprend plusieurs étapes de validation et de sécurité pour garantir la qualité des comptes créés.

### 🎯 Objectif

Permettre à un nouvel utilisateur de créer un compte, de recevoir un mot de passe temporaire sécurisé, de le changer, et d'attendre l'approbation administrative avant de pouvoir utiliser pleinement l'application.

### 👤 Acteurs Principaux

- **Nouvel Utilisateur** : Personne souhaitant créer un compte
- **Système Backend** : Application Laravel gérant l'inscription
- **Service Email** : Système d'envoi d'emails
- **Administrateur** : Personne approuvant les comptes (scénario séparé)

### 📊 Étapes du Processus

#### **PARTIE 1 : INSCRIPTION**

##### **Étape 1 : Saisie des Informations**

Le nouvel utilisateur accède à la page d'inscription et saisit ses informations personnelles : prénom, nom de famille, et adresse email. Il doit également confirmer son adresse email pour éviter les erreurs de saisie.

##### **Étape 2 : Validation des Données**

Le système valide que :
- Le prénom et le nom sont fournis et valides
- L'adresse email est au format correct
- L'adresse email n'est pas déjà utilisée dans le système
- La confirmation de l'email correspond à l'email saisi

##### **Étape 3 : Génération du Mot de Passe Temporaire**

Le système génère automatiquement un mot de passe temporaire sécurisé de huit chiffres. Ce mot de passe est généré de manière aléatoire pour garantir sa sécurité.

##### **Étape 4 : Création du Compte Utilisateur**

Un nouvel enregistrement est créé dans la base de données avec les caractéristiques suivantes :
- Le nom complet est construit à partir du prénom et du nom
- Le mot de passe temporaire est haché de manière sécurisée
- Le rôle est défini comme "client"
- Le statut initial est "pending" (en attente)
- Le flag "must_change_password" est activé
- Le solde initial est de zéro euro

##### **Étape 5 : Envoi de l'Email avec Mot de Passe Temporaire**

Le système envoie un email à l'utilisateur contenant son mot de passe temporaire. Cet email utilise un template professionnel et contient des instructions claires pour la prochaine étape.

##### **Étape 6 : Confirmation d'Inscription**

Le système retourne une réponse confirmant que le compte a été créé et qu'un email a été envoyé. L'utilisateur est informé qu'il doit changer son mot de passe et attendre l'approbation administrative.

#### **PARTIE 2 : PREMIÈRE CONNEXION ET CHANGEMENT DE MOT DE PASSE**

##### **Étape 7 : Connexion avec Mot de Passe Temporaire**

L'utilisateur reçoit l'email et utilise le mot de passe temporaire pour se connecter. Le système authentifie l'utilisateur et détecte que le flag "must_change_password" est activé.

##### **Étape 8 : Redirection vers la Page de Changement de Mot de Passe**

Le système génère un token d'authentification temporaire et redirige l'utilisateur vers la page de changement de mot de passe. L'utilisateur ne peut pas accéder aux autres fonctionnalités tant que le mot de passe n'a pas été changé.

##### **Étape 9 : Saisie du Nouveau Mot de Passe**

L'utilisateur saisit son mot de passe temporaire actuel et choisit un nouveau mot de passe sécurisé. Il doit confirmer le nouveau mot de passe pour éviter les erreurs de saisie.

##### **Étape 10 : Validation et Mise à Jour**

Le système valide que :
- Le mot de passe temporaire actuel est correct
- Le nouveau mot de passe respecte les critères de sécurité (minimum 6 caractères)
- La confirmation du mot de passe correspond au nouveau mot de passe

Si toutes les validations passent, le système :
- Met à jour le mot de passe avec le nouveau hash
- Désactive le flag "must_change_password"
- Change le statut du compte à "pending_validation" (en attente de validation)

##### **Étape 11 : Confirmation du Changement**

Le système confirme que le mot de passe a été changé avec succès et informe l'utilisateur que son compte est maintenant en attente d'approbation par un administrateur.

#### **PARTIE 3 : ATTENTE D'APPROBATION**

##### **Étape 12 : Période d'Attente**

L'utilisateur doit attendre qu'un administrateur approuve son compte. Pendant cette période, l'utilisateur peut se connecter mais son accès est limité. Le statut "pending_validation" empêche l'utilisateur d'effectuer des transactions.

#### **PARTIE 4 : CONNEXION APRÈS APPROBATION**

##### **Étape 13 : Approbation par l'Administrateur**

Un administrateur approuve le compte (voir Scénario 7). Le statut passe à "active" et le solde initial de 500 euros est crédité.

##### **Étape 14 : Connexion avec Nouveau Mot de Passe**

L'utilisateur se connecte avec son nouveau mot de passe. Le système vérifie que le compte est maintenant actif.

##### **Étape 15 : Accès Complet à l'Application**

Le système génère un token d'authentification complet et l'utilisateur peut maintenant accéder à toutes les fonctionnalités de l'application, y compris l'achat et la vente de cryptomonnaies.

### 🔒 Sécurité et Validation

- **Mot de passe temporaire sécurisé** : Génération aléatoire de 8 chiffres
- **Hachage des mots de passe** : Utilisation de bcrypt pour le stockage sécurisé
- **Validation en deux étapes** : Inscription puis changement de mot de passe obligatoire
- **Approbation administrative** : Contrôle qualité des comptes créés
- **Statuts progressifs** : pending → pending_validation → active

### 📈 Points Techniques Importants

- **Génération automatique** : Le système génère le mot de passe, l'utilisateur n'a pas à le choisir initialement
- **Obligation de changement** : Le flag "must_change_password" force le changement lors de la première connexion
- **Workflow en étapes** : Chaque étape doit être complétée avant de passer à la suivante
- **Communication par email** : Notification automatique à chaque étape importante

---

## SCÉNARIO 4 : CONNEXION CLIENT

### 📝 Description Générale

Ce scénario décrit le processus d'authentification d'un utilisateur client existant. Le système vérifie les identifiants, le statut du compte, et génère un token d'authentification pour permettre l'accès aux fonctionnalités de l'application.

### 🎯 Objectif

Permettre à un utilisateur client de s'authentifier de manière sécurisée et d'obtenir un token d'accès pour utiliser l'application, tout en vérifiant que son compte est dans un état valide.

### 👤 Acteurs Principaux

- **Client** : Utilisateur possédant un compte
- **Système Backend** : Application Laravel gérant l'authentification
- **Base de Données** : MySQL stockant les informations utilisateurs

### 📊 Étapes du Processus

#### **Étape 1 : Saisie des Identifiants**

Le client accède à la page de connexion et saisit son adresse email et son mot de passe. Ces informations sont envoyées au backend via une requête HTTP POST sécurisée.

#### **Étape 2 : Validation des Données**

Le système valide que les champs email et mot de passe sont présents et correctement formatés. Cette validation initiale évite des requêtes inutiles à la base de données.

#### **Étape 3 : Recherche de l'Utilisateur**

Le système interroge la base de données pour trouver l'utilisateur correspondant à l'adresse email fournie. Si aucun utilisateur n'est trouvé, le processus s'arrête avec un message d'erreur.

#### **Étape 4 : Vérification du Mot de Passe**

Le système compare le mot de passe fourni avec le hash stocké dans la base de données en utilisant l'algorithme bcrypt. Cette vérification est sécurisée et ne permet pas de récupérer le mot de passe en clair.

#### **Étape 5 : Vérification du Rôle**

Le système vérifie que l'utilisateur a le rôle "client". Si l'utilisateur est un administrateur, un processus différent est suivi (voir Scénario 5).

#### **Étape 6 : Vérification du Flag de Changement de Mot de Passe**

Le système vérifie si l'utilisateur doit changer son mot de passe. Si le flag "must_change_password" est activé, le système génère un token d'authentification temporaire et informe le client qu'il doit changer son mot de passe avant de pouvoir utiliser l'application.

#### **Étape 7 : Vérification du Statut du Compte**

Si le mot de passe n'a pas besoin d'être changé, le système vérifie le statut du compte :
- **pending_validation** : Le compte est en attente d'approbation administrative. L'authentification est refusée avec un message explicite.
- **blocked** : Le compte a été bloqué par un administrateur. L'authentification est refusée.
- **active** : Le compte est actif et approuvé. L'authentification peut continuer.

#### **Étape 8 : Génération du Token d'Authentification**

Si toutes les vérifications passent, le système génère un token d'authentification sécurisé en utilisant Laravel Sanctum. Ce token est stocké dans la base de données et associé à l'utilisateur.

#### **Étape 9 : Stockage du Token dans le Frontend**

Le token est retourné au client dans la réponse JSON. Le frontend stocke ce token (généralement dans le store Pinia ou le localStorage) pour l'utiliser dans les requêtes suivantes.

#### **Étape 10 : Accès à l'Application**

Le client est maintenant authentifié et peut accéder aux fonctionnalités de l'application. Le token sera inclus dans l'en-tête "Authorization" de toutes les requêtes suivantes.

### 🔒 Sécurité et Validation

- **Hachage sécurisé** : Utilisation de bcrypt pour les mots de passe
- **Vérification du statut** : Empêche l'accès aux comptes non approuvés ou bloqués
- **Token sécurisé** : Génération de tokens uniques et sécurisés
- **Validation stricte** : Vérification de toutes les conditions avant d'autoriser l'accès

### 📈 Cas d'Erreur Gérés

- **Email inexistant** : Message d'erreur générique pour ne pas révéler qu'un email existe
- **Mot de passe incorrect** : Message d'erreur générique
- **Compte en attente** : Message explicite indiquant l'attente d'approbation
- **Compte bloqué** : Message explicite indiquant le blocage

---

## SCÉNARIO 5 : AUTHENTIFICATION ADMIN

### 📝 Description Générale

Ce scénario décrit le processus d'authentification d'un administrateur. Les administrateurs ont un processus d'authentification simplifié car ils n'ont pas besoin de changer leur mot de passe et leur compte est toujours actif.

### 🎯 Objectif

Permettre à un administrateur de s'authentifier et d'accéder à l'interface d'administration avec les permissions appropriées.

### 👤 Acteurs Principaux

- **Administrateur** : Utilisateur avec rôle admin
- **Système Backend** : Application Laravel gérant l'authentification
- **Base de Données** : MySQL stockant les informations utilisateurs
- **Middleware de Rôle** : Système vérifiant les permissions

### 📊 Étapes du Processus

#### **Étape 1 : Saisie des Identifiants Admin**

L'administrateur accède à la page de connexion et saisit son adresse email et son mot de passe. Ces informations sont envoyées au backend.

#### **Étape 2 : Validation des Données**

Le système valide que les champs email et mot de passe sont présents et correctement formatés.

#### **Étape 3 : Recherche et Vérification**

Le système recherche l'utilisateur dans la base de données et vérifie le mot de passe de la même manière que pour un client.

#### **Étape 4 : Vérification du Rôle Administrateur**

Le système vérifie que l'utilisateur a le rôle "admin". Cette vérification est cruciale car elle détermine les permissions et l'accès aux fonctionnalités.

#### **Étape 5 : Génération du Token Admin**

Le système génère un token d'authentification avec les permissions d'administrateur. Ce token contient des informations supplémentaires permettant l'accès aux fonctionnalités administratives.

#### **Étape 6 : Stockage du Token**

Le token est retourné au client et stocké dans le frontend pour les requêtes suivantes.

#### **Étape 7 : Redirection vers l'Interface Admin**

Le frontend redirige automatiquement l'administrateur vers l'interface d'administration, qui est séparée de l'interface client.

#### **Étape 8 : Accès au Dashboard Admin**

L'administrateur peut maintenant accéder au dashboard administratif. Le système vérifie le token et le rôle à chaque requête pour garantir la sécurité.

#### **Étape 9 : Vérification des Permissions**

À chaque requête administrative, le middleware vérifie que :
- Le token est valide
- L'utilisateur a toujours le rôle admin
- Le compte est toujours actif

### 🔒 Sécurité et Permissions

- **Vérification de rôle stricte** : Seuls les utilisateurs avec rôle "admin" peuvent accéder
- **Token avec permissions** : Le token contient des informations de permissions
- **Middleware de protection** : Vérification à chaque requête administrative
- **Séparation des interfaces** : Interface admin séparée de l'interface client

### 📈 Différences avec la Connexion Client

- **Pas de vérification de statut** : Les comptes admin sont toujours actifs
- **Pas de changement de mot de passe obligatoire** : Les admins peuvent utiliser leur mot de passe directement
- **Permissions étendues** : Accès à toutes les fonctionnalités administratives
- **Interface dédiée** : Accès à une interface spécifique pour la gestion

---

## SCÉNARIO 6 : CRÉATION D'UTILISATEUR PAR ADMIN

### 📝 Description Générale

Ce scénario décrit le processus de création d'un compte utilisateur client par un administrateur. Cette fonctionnalité permet aux administrateurs de créer des comptes manuellement, par exemple pour des utilisateurs ayant des difficultés avec le processus d'inscription standard.

### 🎯 Objectif

Permettre à un administrateur de créer un compte client avec un solde initial, de générer un mot de passe temporaire sécurisé, et d'envoyer les informations de connexion à l'utilisateur par email.

### 👤 Acteurs Principaux

- **Administrateur** : Utilisateur avec permissions admin
- **Système Backend** : Application Laravel gérant la création
- **Service Email** : Système d'envoi d'emails
- **Nouvel Utilisateur** : Personne pour qui le compte est créé

### 📊 Étapes du Processus

#### **Étape 1 : Accès à l'Interface de Création**

L'administrateur accède à l'interface de gestion des utilisateurs et clique sur le bouton pour créer un nouvel utilisateur.

#### **Étape 2 : Saisie des Informations**

L'administrateur saisit les informations de base :
- Le nom complet de l'utilisateur
- L'adresse email de l'utilisateur

Ces informations sont envoyées au backend via une requête HTTP POST.

#### **Étape 3 : Vérification des Permissions Admin**

Le système vérifie que la requête provient bien d'un administrateur authentifié. Cette vérification est effectuée par le middleware de rôle.

#### **Étape 4 : Validation des Données**

Le système valide que :
- Le nom est fourni et valide
- L'adresse email est au format correct
- L'adresse email n'est pas déjà utilisée dans le système

#### **Étape 5 : Génération du Mot de Passe Temporaire**

Le système génère automatiquement un mot de passe temporaire sécurisé de douze caractères aléatoires. Ce mot de passe est plus long que celui généré lors de l'inscription standard pour une sécurité accrue.

#### **Étape 6 : Séparation du Nom**

Le système sépare automatiquement le nom complet en prénom et nom de famille. Cette séparation est effectuée intelligemment pour gérer différents formats de noms.

#### **Étape 7 : Création du Compte Utilisateur**

Un nouvel enregistrement est créé dans la base de données avec les caractéristiques suivantes :
- Le nom complet, prénom et nom de famille
- L'adresse email
- Le mot de passe temporaire haché
- Le rôle défini comme "client"
- Le statut initial "pending" (en attente)
- Le flag "must_change_password" activé
- Le solde initial de 500 euros (crédité immédiatement)

#### **Étape 8 : Envoi de l'Email avec Mot de Passe Temporaire**

Le système envoie un email professionnel à l'utilisateur contenant :
- Son mot de passe temporaire
- Des instructions pour se connecter
- Des instructions pour changer le mot de passe
- Des informations sur le solde initial

#### **Étape 9 : Confirmation de Création**

Le système retourne une confirmation à l'administrateur indiquant que :
- Le compte a été créé avec succès
- Un email a été envoyé à l'utilisateur
- Le compte a été crédité de 500 euros
- L'utilisateur devra changer son mot de passe lors de sa première connexion

#### **Étape 10 : Mise à Jour de l'Interface Admin**

L'interface d'administration est mise à jour pour afficher le nouvel utilisateur dans la liste. Le statut "pending" est visible, indiquant que l'utilisateur doit encore changer son mot de passe.

### 🔒 Sécurité et Validation

- **Permissions strictes** : Seuls les administrateurs peuvent créer des comptes
- **Mot de passe sécurisé** : Génération aléatoire de 12 caractères
- **Validation de l'email** : Vérification de l'unicité de l'adresse email
- **Solde initial garanti** : Crédit automatique de 500 euros

### 📈 Points Techniques Importants

- **Génération automatique** : Le système gère tout le processus de création
- **Email automatique** : Notification immédiate à l'utilisateur
- **Solde initial** : Crédit immédiat contrairement à l'inscription standard
- **Workflow simplifié** : L'administrateur n'a besoin que du nom et de l'email

### 🔄 Différences avec l'Inscription Standard

- **Solde initial** : 500 euros crédités immédiatement (vs 0 pour l'inscription standard)
- **Mot de passe plus long** : 12 caractères vs 8 chiffres
- **Création par admin** : Pas besoin que l'utilisateur accède au site
- **Statut initial** : Toujours "pending" (doit changer le mot de passe)

---

## SCÉNARIO 7 : APPROBATION D'UTILISATEUR PAR ADMIN

### 📝 Description Générale

Ce scénario décrit le processus d'approbation d'un compte utilisateur client par un administrateur. Cette étape est cruciale dans le workflow de validation des comptes et permet aux administrateurs de contrôler la qualité des utilisateurs de la plateforme.

### 🎯 Objectif

Permettre à un administrateur d'approuver un compte utilisateur en attente de validation, d'activer le compte, et de créditer le solde initial si nécessaire, permettant ainsi à l'utilisateur d'utiliser pleinement l'application.

### 👤 Acteurs Principaux

- **Administrateur** : Utilisateur avec permissions admin
- **Système Backend** : Application Laravel gérant l'approbation
- **Utilisateur Client** : Compte en attente d'approbation
- **Base de Données** : MySQL stockant les informations utilisateurs

### 📊 Étapes du Processus

#### **Étape 1 : Consultation de la Liste des Utilisateurs**

L'administrateur accède à l'interface de gestion des utilisateurs et consulte la liste des comptes. Les comptes en attente de validation sont clairement identifiés avec leur statut "pending_validation".

#### **Étape 2 : Sélection de l'Utilisateur à Approuver**

L'administrateur sélectionne un utilisateur spécifique dans la liste. Il peut consulter les détails du compte avant de prendre la décision d'approbation.

#### **Étape 3 : Envoi de la Requête d'Approbation**

L'administrateur clique sur le bouton d'approbation, ce qui envoie une requête HTTP POST au backend avec l'identifiant de l'utilisateur à approuver.

#### **Étape 4 : Vérification des Permissions Admin**

Le système vérifie que la requête provient bien d'un administrateur authentifié et autorisé. Cette vérification est effectuée par le middleware de rôle.

#### **Étape 5 : Récupération de l'Utilisateur**

Le système récupère l'utilisateur depuis la base de données en utilisant l'identifiant fourni. Si l'utilisateur n'existe pas, une erreur est retournée.

#### **Étape 6 : Vérification du Statut Actuel**

Le système vérifie le statut actuel de l'utilisateur. Si l'utilisateur est déjà actif, le système retourne une erreur indiquant que l'utilisateur est déjà approuvé. Cette vérification évite les opérations inutiles.

#### **Étape 7 : Mise à Jour du Statut et des Informations**

Si l'utilisateur est en attente de validation, le système met à jour plusieurs champs :
- **Statut** : Changé de "pending_validation" à "active"
- **Flag de changement de mot de passe** : Désactivé (l'utilisateur a déjà changé son mot de passe)
- **Solde** : Crédité de 500 euros si nécessaire (pour les comptes créés par inscription standard)
- **Email vérifié** : Date de vérification enregistrée

#### **Étape 8 : Validation de la Mise à Jour**

La mise à jour est effectuée dans la base de données. Le système s'assure que toutes les modifications sont appliquées de manière atomique.

#### **Étape 9 : Récupération des Données Mises à Jour**

Le système récupère les données mises à jour de l'utilisateur pour confirmer que toutes les modifications ont été appliquées correctement.

#### **Étape 10 : Confirmation à l'Administrateur**

Le système retourne une confirmation à l'administrateur contenant :
- Un message de succès
- Les informations mises à jour de l'utilisateur
- Le nouveau statut (active)
- Le solde crédité

#### **Étape 11 : Mise à Jour de l'Interface Admin**

L'interface d'administration est mise à jour pour refléter le changement de statut. L'utilisateur apparaît maintenant comme "active" dans la liste.

#### **Étape 12 : Notification Implicite à l'Utilisateur**

Bien que le système n'envoie pas d'email explicite, l'utilisateur découvrira que son compte est approuvé lors de sa prochaine tentative de connexion. Il pourra alors accéder à toutes les fonctionnalités de l'application.

### 🔒 Sécurité et Validation

- **Permissions strictes** : Seuls les administrateurs peuvent approuver des comptes
- **Vérification de statut** : Empêche l'approbation multiple ou inutile
- **Opération atomique** : Toutes les mises à jour sont effectuées ensemble
- **Contrôle qualité** : Permet aux administrateurs de vérifier les comptes avant approbation

### 📈 Points Techniques Importants

- **Changement de statut** : Transition de "pending_validation" à "active"
- **Crédit conditionnel** : Le solde est crédité si nécessaire (déjà fait pour les comptes créés par admin)
- **Désactivation du flag** : Le flag "must_change_password" est désactivé car l'utilisateur a déjà changé son mot de passe
- **Vérification d'email** : La date de vérification est enregistrée pour traçabilité

### 🔄 Workflow Complet

1. **Inscription standard** : pending → (changement mot de passe) → pending_validation → (approbation admin) → active
2. **Création par admin** : pending → (changement mot de passe) → pending_validation → (approbation admin) → active

Dans les deux cas, l'approbation administrative est nécessaire pour activer le compte.

### 💡 Cas d'Usage

- **Contrôle qualité** : Les administrateurs peuvent vérifier les informations avant d'approuver
- **Sécurité** : Empêche la création de comptes frauduleux
- **Gestion** : Permet de gérer le nombre d'utilisateurs actifs
- **Support** : Les administrateurs peuvent aider les utilisateurs ayant des problèmes

---

## 📚 GUIDE D'UTILISATION POUR RAPPORT ACADÉMIQUE

### Comment Utiliser Ces Scénarios

Ces scénarios sont conçus pour être utilisés dans votre rapport académique. Voici comment les intégrer :

#### **1. Pour Chaque Diagramme de Séquence**

- **Introduction** : Utilisez la "Description Générale" pour introduire le diagramme
- **Objectif** : Expliquez l'objectif du processus
- **Acteurs** : Listez les acteurs principaux
- **Étapes** : Décrivez chaque étape en détail
- **Sécurité** : Mentionnez les aspects de sécurité
- **Conclusion** : Résumez le processus et ses bénéfices

#### **2. Structure Recommandée pour Votre Rapport**

```
1. Introduction du chapitre
2. Description générale du processus
3. Objectif et contexte
4. Acteurs impliqués
5. Étapes détaillées (avec références au diagramme)
6. Aspects de sécurité
7. Optimisations techniques
8. Conclusion
```

#### **3. Exemple d'Intégration**

**Dans votre rapport, vous pourriez écrire :**

> "Le processus d'achat de cryptomonnaie (voir Scénario 1) garantit l'intégrité transactionnelle grâce à l'utilisation de transactions de base de données atomiques. Chaque étape, depuis la validation de la requête jusqu'à la mise à jour du portfolio, est exécutée de manière sécurisée et cohérente. Le système utilise un verrouillage de ligne pour empêcher les conditions de course lors de modifications concurrentes du solde utilisateur."

#### **4. Citations et Références**

- Citez le numéro du scénario : "Comme décrit dans le Scénario 3..."
- Référencez les étapes spécifiques : "L'étape 5 du Scénario 1 montre..."
- Utilisez les sections "Points Techniques Importants" pour approfondir

### 🎓 Conseils pour l'Explication Orale

Si vous devez présenter ces scénarios oralement :

1. **Commencez par le contexte** : Expliquez pourquoi ce processus existe
2. **Suivez l'ordre chronologique** : Présentez les étapes dans l'ordre
3. **Soulignez les points clés** : Mettez en évidence les aspects de sécurité et d'intégrité
4. **Utilisez des exemples concrets** : "Imaginez qu'un utilisateur veut acheter 0.5 BTC..."
5. **Expliquez les choix techniques** : Pourquoi utiliser une transaction de base de données ? Pourquoi verrouiller la ligne ?

---

**Document créé le** : 2025-01-27  
**Version** : 1.0  
**Pour utilisation avec** : PROMPTS_CHATUML.md
