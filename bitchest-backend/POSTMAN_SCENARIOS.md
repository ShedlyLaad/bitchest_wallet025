# 📬 Collection Postman BitChest - Scénarios Complets

## 🔧 Configuration Initiale

### Variables d'Environnement Postman

Créer un environnement avec ces variables :

```
base_url = http://localhost:8000/api
admin_token = (sera rempli automatiquement)
client_token = (sera rempli automatiquement)
client_id = (sera rempli automatiquement)
client_email = (sera rempli automatiquement)
temp_password = (sera rempli automatiquement)
```

### Compte Admin par défaut (depuis UserSeeder)
- **Email:** `admin@bitchest.com`
- **Password:** `admin123`
- **Role:** `admin`

---

## 📋 SCÉNARIO 1 : AUTHENTIFICATION (Routes Publiques)

### 1.1 Inscription d'un nouveau client
**Méthode:** `POST`  
**URL:** `{{base_url}}/register`  
**Headers:**
```
Content-Type: application/json
Accept: application/json
```

**Body (raw JSON):**
```json
{
    "name": "Jean Dupont",
    "email": "jean.dupont@example.com",
    "password": "password123",
    "password_confirmation": "password123"
}
```

**Réponse attendue (201):**
```json
{
    "message": "Account created. Waiting admin approval.",
    "status": "pending"
}
```

**Tests Postman (Tests tab):**
```javascript
pm.test("Status code is 201", function () {
    pm.response.to.have.status(201);
});

pm.test("Response has pending status", function () {
    const jsonData = pm.response.json();
    pm.expect(jsonData.status).to.eql("pending");
});
```

---

### 1.2 Login Admin
**Méthode:** `POST`  
**URL:** `{{base_url}}/login`  
**Headers:**
```
Content-Type: application/json
Accept: application/json
```

**Body (raw JSON):**
```json
{
    "email": "admin@bitchest.com",
    "password": "admin123"
}
```

**Réponse attendue (200):**
```json
{
    "user": {
        "id": 1,
        "name": "Super Admin",
        "email": "admin@bitchest.com",
        "role": "admin"
    },
    "token": "1|xxxxxxxxxxxxx"
}
```

**Tests Postman:**
```javascript
pm.test("Status code is 200", function () {
    pm.response.to.have.status(200);
});

pm.test("Response has token", function () {
    const jsonData = pm.response.json();
    pm.expect(jsonData.token).to.exist;
    pm.environment.set("admin_token", jsonData.token);
});
```

---

### 1.3 Login Client (après approbation)
**Méthode:** `POST`  
**URL:** `{{base_url}}/login`  
**Headers:**
```
Content-Type: application/json
Accept: application/json
```

**Body (raw JSON):**
```json
{
    "email": "{{client_email}}",
    "password": "{{temp_password}}"
}
```

**Réponse attendue (200):**
```json
{
    "user": {
        "id": 2,
        "name": "Jean Dupont",
        "email": "jean.dupont@example.com",
        "role": "client",
        "status": "active",
        "euro_balance": "500.00"
    },
    "token": "2|xxxxxxxxxxxxx"
}
```

**Tests Postman:**
```javascript
pm.test("Status code is 200", function () {
    pm.response.to.have.status(200);
});

pm.test("Response has token", function () {
    const jsonData = pm.response.json();
    pm.expect(jsonData.token).to.exist;
    pm.environment.set("client_token", jsonData.token);
});
```

---

### 1.4 Login Client avec compte pending (devrait échouer)
**Méthode:** `POST`  
**URL:** `{{base_url}}/login`  
**Headers:**
```
Content-Type: application/json
Accept: application/json
```

**Body (raw JSON):**
```json
{
    "email": "pending.user@example.com",
    "password": "password123"
}
```

**Réponse attendue (403):**
```json
{
    "message": "Account pending approval"
}
```

---

### 1.5 Login avec mauvais identifiants (devrait échouer)
**Méthode:** `POST`  
**URL:** `{{base_url}}/login`  
**Headers:**
```
Content-Type: application/json
Accept: application/json
```

**Body (raw JSON):**
```json
{
    "email": "wrong@example.com",
    "password": "wrongpassword"
}
```

**Réponse attendue (401):**
```json
{
    "message": "Invalid credentials"
}
```

---

## 📋 SCÉNARIO 2 : ADMIN - GESTION DES CRYPTOS

### 2.1 Générer les prix initiaux des cryptos
**Méthode:** `POST`  
**URL:** `{{base_url}}/admin/cryptos/generate`  
**Headers:**
```
Authorization: Bearer {{admin_token}}
Content-Type: application/json
Accept: application/json
```

**Body:** (vide)

**Réponse attendue (200):**
```json
{
    "message": "Initial prices generated."
}
```

**Tests Postman:**
```javascript
pm.test("Status code is 200", function () {
    pm.response.to.have.status(200);
});
```

---

### 2.2 Voir les prix actuels des cryptos (Admin)
**Méthode:** `GET`  
**URL:** `{{base_url}}/admin/cryptos`  
**Headers:**
```
Authorization: Bearer {{admin_token}}
Accept: application/json
```

**Réponse attendue (200):**
```json
[
    {
        "symbol": "BTC",
        "name": "Bitcoin",
        "price": "30000.12345678"
    },
    {
        "symbol": "ETH",
        "name": "Ethereum",
        "price": "2000.12345678"
    }
]
```

---

## 📋 SCÉNARIO 3 : ADMIN - GESTION DES CLIENTS

### 3.1 Créer un nouveau client
**Méthode:** `POST`  
**URL:** `{{base_url}}/admin/users`  
**Headers:**
```
Authorization: Bearer {{admin_token}}
Content-Type: application/json
Accept: application/json
```

**Body (raw JSON):**
```json
{
    "name": "Marie Martin",
    "email": "marie.martin@example.com"
}
```

**Réponse attendue (201):**
```json
{
    "user": {
        "id": 3,
        "name": "Marie Martin",
        "email": "marie.martin@example.com",
        "role": "client",
        "status": "pending",
        "euro_balance": "500.00"
    },
    "temporary_password": "Abc123XyZ789"
}
```

**Tests Postman:**
```javascript
pm.test("Status code is 201", function () {
    pm.response.to.have.status(201);
});

pm.test("Save client data", function () {
    const jsonData = pm.response.json();
    pm.environment.set("client_id", jsonData.user.id);
    pm.environment.set("client_email", jsonData.user.email);
    pm.environment.set("temp_password", jsonData.temporary_password);
});
```

---

### 3.2 Lister tous les clients
**Méthode:** `GET`  
**URL:** `{{base_url}}/admin/users`  
**Headers:**
```
Authorization: Bearer {{admin_token}}
Accept: application/json
```

**Réponse attendue (200):**
```json
[
    {
        "id": 2,
        "name": "Jean Dupont",
        "email": "jean.dupont@example.com",
        "role": "client",
        "status": "active"
    },
    {
        "id": 3,
        "name": "Marie Martin",
        "email": "marie.martin@example.com",
        "role": "client",
        "status": "pending"
    }
]
```

---

### 3.3 Voir un client spécifique
**Méthode:** `GET`  
**URL:** `{{base_url}}/admin/users/{{client_id}}`  
**Headers:**
```
Authorization: Bearer {{admin_token}}
Accept: application/json
```

**Réponse attendue (200):**
```json
{
    "id": 3,
    "name": "Marie Martin",
    "email": "marie.martin@example.com",
    "role": "client",
    "status": "pending",
    "euro_balance": "500.00"
}
```

---

### 3.4 Approuver un client (activer son compte)
**Méthode:** `POST`  
**URL:** `{{base_url}}/admin/users/{{client_id}}/approve`  
**Headers:**
```
Authorization: Bearer {{admin_token}}
Content-Type: application/json
Accept: application/json
```

**Body:** (vide)

**Réponse attendue (200):**
```json
{
    "message": "User approved + wallet initialized",
    "user": {
        "id": 3,
        "name": "Marie Martin",
        "email": "marie.martin@example.com",
        "role": "client",
        "status": "active",
        "euro_balance": "500.00"
    }
}
```

**Tests Postman:**
```javascript
pm.test("Status code is 200", function () {
    pm.response.to.have.status(200);
});

pm.test("User is now active", function () {
    const jsonData = pm.response.json();
    pm.expect(jsonData.user.status).to.eql("active");
});
```

---

### 3.5 Approuver un client déjà actif (devrait échouer)
**Méthode:** `POST`  
**URL:** `{{base_url}}/admin/users/{{client_id}}/approve`  
**Headers:**
```
Authorization: Bearer {{admin_token}}
Content-Type: application/json
Accept: application/json
```

**Réponse attendue (400):**
```json
{
    "message": "User already active"
}
```

---

### 3.6 Supprimer un client
**Méthode:** `DELETE`  
**URL:** `{{base_url}}/admin/users/{{client_id}}`  
**Headers:**
```
Authorization: Bearer {{admin_token}}
Accept: application/json
```

**Réponse attendue (200):**
```json
{
    "message": "Deleted"
}
```

---

## 📋 SCÉNARIO 4 : ADMIN - GESTION DU PROFIL

### 4.1 Mettre à jour le profil admin (nom et email)
**Méthode:** `PUT`  
**URL:** `{{base_url}}/admin/profile`  
**Headers:**
```
Authorization: Bearer {{admin_token}}
Content-Type: application/json
Accept: application/json
```

**Body (raw JSON):**
```json
{
    "name": "Super Admin Updated",
    "email": "admin.updated@bitchest.com"
}
```

**Réponse attendue (200):**
```json
{
    "message": "Profil mis à jour",
    "admin": {
        "id": 1,
        "name": "Super Admin Updated",
        "email": "admin.updated@bitchest.com"
    }
}
```

---

### 4.2 Mettre à jour le mot de passe admin
**Méthode:** `PUT`  
**URL:** `{{base_url}}/admin/profile`  
**Headers:**
```
Authorization: Bearer {{admin_token}}
Content-Type: application/json
Accept: application/json
```

**Body (raw JSON):**
```json
{
    "password": "newpassword123",
    "password_confirmation": "newpassword123"
}
```

**Réponse attendue (200):**
```json
{
    "message": "Profil mis à jour",
    "admin": {
        "id": 1,
        "name": "Super Admin Updated",
        "email": "admin.updated@bitchest.com"
    }
}
```

---

## 📋 SCÉNARIO 5 : CLIENT - CONSULTATION DU MARCHÉ

### 5.1 Voir les prix actuels du marché
**Méthode:** `GET`  
**URL:** `{{base_url}}/market`  
**Headers:**
```
Authorization: Bearer {{client_token}}
Accept: application/json
```

**Réponse attendue (200):**
```json
[
    {
        "symbol": "BTC",
        "name": "Bitcoin",
        "price": "30000.12345678"
    },
    {
        "symbol": "ETH",
        "name": "Ethereum",
        "price": "2000.12345678"
    },
    {
        "symbol": "XRP",
        "name": "Ripple",
        "price": "0.50000000"
    }
]
```

---

### 5.2 Voir l'historique d'une crypto (BTC)
**Méthode:** `GET`  
**URL:** `{{base_url}}/market/history/BTC`  
**Headers:**
```
Authorization: Bearer {{client_token}}
Accept: application/json
```

**Réponse attendue (200):**
```json
[
    {
        "id": 1,
        "crypto_currency_id": 1,
        "price": "30000.12345678",
        "recorded_at": "2025-01-15T10:00:00.000000Z"
    },
    {
        "id": 2,
        "crypto_currency_id": 1,
        "price": "30100.12345678",
        "recorded_at": "2025-01-15T11:00:00.000000Z"
    }
]
```

---

### 5.3 Voir l'historique d'autres cryptos
**Méthode:** `GET`  
**URL:** `{{base_url}}/market/history/ETH`  
**Headers:**
```
Authorization: Bearer {{client_token}}
Accept: application/json
```

**Symboles disponibles:** BTC, ETH, XRP, BCH, ADA, LTC, XEM, XLM, MIOTA, DASH

---

## 📋 SCÉNARIO 6 : CLIENT - GESTION DU PORTEFEUILLE

### 6.1 Voir mon portefeuille (vide au début)
**Méthode:** `GET`  
**URL:** `{{base_url}}/portfolio`  
**Headers:**
```
Authorization: Bearer {{client_token}}
Accept: application/json
```

**Réponse attendue (200):**
```json
{
    "balance": "500.00",
    "portfolio": []
}
```

---

### 6.2 Voir mon portefeuille (après achats)
**Méthode:** `GET`  
**URL:** `{{base_url}}/portfolio`  
**Headers:**
```
Authorization: Bearer {{client_token}}
Accept: application/json
```

**Réponse attendue (200):**
```json
{
    "balance": "450.00",
    "portfolio": [
        {
            "id": 1,
            "user_id": 3,
            "crypto_currency_id": 1,
            "total_quantity": "0.00100000",
            "pamu": "30000.00000000",
            "current_value": "30.12345678",
            "unrealized_profit": "0.12345678",
            "crypto": {
                "id": 1,
                "name": "Bitcoin",
                "symbol": "BTC"
            }
        }
    ]
}
```

---

## 📋 SCÉNARIO 7 : CLIENT - TRANSACTIONS D'ACHAT

### 7.1 Acheter du Bitcoin (BTC)
**Méthode:** `POST`  
**URL:** `{{base_url}}/transaction/buy`  
**Headers:**
```
Authorization: Bearer {{client_token}}
Content-Type: application/json
Accept: application/json
```

**Body (raw JSON):**
```json
{
    "symbol": "BTC",
    "quantity": 0.001
}
```

**Réponse attendue (201):**
```json
{
    "transaction": {
        "id": 1,
        "user_id": 3,
        "crypto_currency_id": 1,
        "type": "buy",
        "quantity": "0.00100000",
        "price_at_transaction": "30000.12345678",
        "euro_amount": "30.00",
        "created_at": "2025-01-15T10:00:00.000000Z"
    }
}
```

**Tests Postman:**
```javascript
pm.test("Status code is 201", function () {
    pm.response.to.have.status(201);
});

pm.test("Transaction is buy type", function () {
    const jsonData = pm.response.json();
    pm.expect(jsonData.transaction.type).to.eql("buy");
});
```

---

### 7.2 Acheter de l'Ethereum (ETH)
**Méthode:** `POST`  
**URL:** `{{base_url}}/transaction/buy`  
**Headers:**
```
Authorization: Bearer {{client_token}}
Content-Type: application/json
Accept: application/json
```

**Body (raw JSON):**
```json
{
    "symbol": "ETH",
    "quantity": 0.1
}
```

**Réponse attendue (201):**
```json
{
    "transaction": {
        "id": 2,
        "user_id": 3,
        "crypto_currency_id": 2,
        "type": "buy",
        "quantity": "0.10000000",
        "price_at_transaction": "2000.12345678",
        "euro_amount": "200.00",
        "created_at": "2025-01-15T10:05:00.000000Z"
    }
}
```

---

### 7.3 Acheter avec solde insuffisant (devrait échouer)
**Méthode:** `POST`  
**URL:** `{{base_url}}/transaction/buy`  
**Headers:**
```
Authorization: Bearer {{client_token}}
Content-Type: application/json
Accept: application/json
```

**Body (raw JSON):**
```json
{
    "symbol": "BTC",
    "quantity": 10
}
```

**Réponse attendue (500 ou 400):**
```json
{
    "message": "Solde insuffisant."
}
```

---

### 7.4 Acheter avec symbole invalide (devrait échouer)
**Méthode:** `POST`  
**URL:** `{{base_url}}/transaction/buy`  
**Headers:**
```
Authorization: Bearer {{client_token}}
Content-Type: application/json
Accept: application/json
```

**Body (raw JSON):**
```json
{
    "symbol": "INVALID",
    "quantity": 0.001
}
```

**Réponse attendue (422):**
```json
{
    "message": "The selected symbol is invalid.",
    "errors": {
        "symbol": ["The selected symbol is invalid."]
    }
}
```

---

### 7.5 Acheter avec quantité négative (devrait échouer)
**Méthode:** `POST`  
**URL:** `{{base_url}}/transaction/buy`  
**Headers:**
```
Authorization: Bearer {{client_token}}
Content-Type: application/json
Accept: application/json
```

**Body (raw JSON):**
```json
{
    "symbol": "BTC",
    "quantity": -0.001
}
```

**Réponse attendue (422):**
```json
{
    "message": "The quantity must be greater than 0.",
    "errors": {
        "quantity": ["The quantity must be greater than 0."]
    }
}
```

---

## 📋 SCÉNARIO 8 : CLIENT - TRANSACTIONS DE VENTE

### 8.1 Vendre partiellement du Bitcoin
**Méthode:** `POST`  
**URL:** `{{base_url}}/transaction/sell`  
**Headers:**
```
Authorization: Bearer {{client_token}}
Content-Type: application/json
Accept: application/json
```

**Body (raw JSON):**
```json
{
    "symbol": "BTC",
    "quantity": 0.0005
}
```

**Réponse attendue (201):**
```json
{
    "transaction": {
        "id": 3,
        "user_id": 3,
        "crypto_currency_id": 1,
        "type": "sell",
        "quantity": "0.00050000",
        "price_at_transaction": "30100.12345678",
        "euro_amount": "15.05",
        "created_at": "2025-01-15T10:10:00.000000Z"
    }
}
```

---

### 8.2 Vendre complètement une crypto
**Méthode:** `POST`  
**URL:** `{{base_url}}/transaction/sell`  
**Headers:**
```
Authorization: Bearer {{client_token}}
Content-Type: application/json
Accept: application/json
```

**Body (raw JSON):**
```json
{
    "symbol": "ETH",
    "quantity": 0.1
}
```

**Réponse attendue (201):**
```json
{
    "transaction": {
        "id": 4,
        "user_id": 3,
        "crypto_currency_id": 2,
        "type": "sell",
        "quantity": "0.10000000",
        "price_at_transaction": "2010.12345678",
        "euro_amount": "201.01",
        "created_at": "2025-01-15T10:15:00.000000Z"
    }
}
```

**Note:** Après cette vente complète, l'entrée du portfolio pour ETH sera supprimée.

---

### 8.3 Vendre avec quantité insuffisante (devrait échouer)
**Méthode:** `POST`  
**URL:** `{{base_url}}/transaction/sell`  
**Headers:**
```
Authorization: Bearer {{client_token}}
Content-Type: application/json
Accept: application/json
```

**Body (raw JSON):**
```json
{
    "symbol": "BTC",
    "quantity": 10
}
```

**Réponse attendue (500 ou 400):**
```json
{
    "message": "Quantité insuffisante dans le portefeuille"
}
```

---

### 8.4 Vendre une crypto non possédée (devrait échouer)
**Méthode:** `POST`  
**URL:** `{{base_url}}/transaction/sell`  
**Headers:**
```
Authorization: Bearer {{client_token}}
Content-Type: application/json
Accept: application/json
```

**Body (raw JSON):**
```json
{
    "symbol": "XRP",
    "quantity": 0.1
}
```

**Réponse attendue (500 ou 400):**
```json
{
    "message": "Vous ne possédez pas cette crypto"
}
```

---

## 📋 SCÉNARIO 9 : DÉCONNEXION

### 9.1 Logout Admin
**Méthode:** `POST`  
**URL:** `{{base_url}}/logout`  
**Headers:**
```
Authorization: Bearer {{admin_token}}
Content-Type: application/json
Accept: application/json
```

**Body:** (vide)

**Réponse attendue (200):**
```json
{
    "message": "Logged out successfully"
}
```

**Tests Postman:**
```javascript
pm.test("Status code is 200", function () {
    pm.response.to.have.status(200);
});

pm.environment.set("admin_token", "");
```

---

### 9.2 Logout Client
**Méthode:** `POST`  
**URL:** `{{base_url}}/logout`  
**Headers:**
```
Authorization: Bearer {{client_token}}
Content-Type: application/json
Accept: application/json
```

**Body:** (vide)

**Réponse attendue (200):**
```json
{
    "message": "Logged out successfully"
}
```

---

## 📋 SCÉNARIO 10 : TESTS DE SÉCURITÉ

### 10.1 Accès sans authentification (devrait échouer)
**Méthode:** `GET`  
**URL:** `{{base_url}}/portfolio`  
**Headers:**
```
Accept: application/json
```

**Réponse attendue (401):**
```json
{
    "message": "Unauthenticated."
}
```

---

### 10.2 Accès client aux routes admin (devrait échouer)
**Méthode:** `POST`  
**URL:** `{{base_url}}/admin/users`  
**Headers:**
```
Authorization: Bearer {{client_token}}
Content-Type: application/json
Accept: application/json
```

**Body (raw JSON):**
```json
{
    "name": "Test",
    "email": "test@example.com"
}
```

**Réponse attendue (403):**
```json
{
    "message": "Unauthorized"
}
```

---

### 10.3 Accès admin aux routes client (devrait échouer)
**Méthode:** `GET`  
**URL:** `{{base_url}}/portfolio`  
**Headers:**
```
Authorization: Bearer {{admin_token}}
Accept: application/json
```

**Réponse attendue (403):**
```json
{
    "message": "Unauthorized"
}
```

---

### 10.4 Client avec status "pending" (devrait échouer)
**Méthode:** `GET`  
**URL:** `{{base_url}}/portfolio`  
**Headers:**
```
Authorization: Bearer {{pending_client_token}}
Accept: application/json
```

**Réponse attendue (403):**
```json
{
    "message": "Account pending approval"
}
```

---

## 📋 SCÉNARIO 11 : FLUX COMPLET END-TO-END

### Ordre d'exécution recommandé :

1. **Setup Initial**
   - 1.1 Login Admin → `{{admin_token}}`
   - 2.1 Générer les prix → `POST /admin/cryptos/generate`

2. **Création et Activation Client**
   - 3.1 Créer client → `POST /admin/users`
   - 3.4 Approuver client → `POST /admin/users/{id}/approve`
   - 1.3 Login client → `{{client_token}}`

3. **Consultation et Trading**
   - 5.1 Voir le marché → `GET /market`
   - 6.1 Voir le portefeuille (vide) → `GET /portfolio`
   - 7.1 Acheter BTC → `POST /transaction/buy`
   - 6.2 Voir le portefeuille (avec BTC) → `GET /portfolio`
   - 7.2 Acheter ETH → `POST /transaction/buy`
   - 5.2 Voir l'historique BTC → `GET /market/history/BTC`
   - 8.1 Vendre partiellement BTC → `POST /transaction/sell`
   - 6.2 Voir le portefeuille mis à jour → `GET /portfolio`
   - 8.2 Vendre complètement ETH → `POST /transaction/sell`
   - 6.1 Vérifier le solde final → `GET /portfolio`

4. **Gestion Admin**
   - 3.2 Lister tous les clients → `GET /admin/users`
   - 3.3 Voir un client spécifique → `GET /admin/users/{id}`
   - 4.1 Mettre à jour le profil admin → `PUT /admin/profile`

5. **Nettoyage**
   - 9.1 Logout Admin → `POST /logout`
   - 9.2 Logout Client → `POST /logout`

---

## 🔄 Scripts Postman Automatiques

### Script Pre-request (Collection Level)

Créer un script au niveau de la collection pour vérifier les variables :

```javascript
// Vérifier que base_url est défini
if (!pm.environment.get("base_url")) {
    console.log("⚠️ base_url n'est pas défini dans l'environnement");
}
```

### Scripts de Tests Communs

#### Pour les requêtes de login (sauvegarder le token) :
```javascript
if (pm.response.code === 200) {
    const jsonData = pm.response.json();
    if (jsonData.token) {
        const userRole = jsonData.user.role;
        if (userRole === 'admin') {
            pm.environment.set("admin_token", jsonData.token);
            console.log("✅ Token admin sauvegardé");
        } else if (userRole === 'client') {
            pm.environment.set("client_token", jsonData.token);
            console.log("✅ Token client sauvegardé");
        }
    }
}
```

#### Pour la création de client (sauvegarder les données) :
```javascript
if (pm.response.code === 201) {
    const jsonData = pm.response.json();
    if (jsonData.user) {
        pm.environment.set("client_id", jsonData.user.id);
        pm.environment.set("client_email", jsonData.user.email);
        if (jsonData.temporary_password) {
            pm.environment.set("temp_password", jsonData.temporary_password);
            console.log("✅ Données client sauvegardées");
        }
    }
}
```

#### Pour vérifier le statut de réponse :
```javascript
pm.test("Response time is less than 2000ms", function () {
    pm.expect(pm.response.responseTime).to.be.below(2000);
});

pm.test("Response has JSON body", function () {
    pm.response.to.be.json;
});
```

---

## 📊 Statistiques et Métriques

### Variables à suivre dans les tests :

- **Temps de réponse** : < 2000ms
- **Taux de succès** : 100% pour les requêtes valides
- **Codes de statut** : 200, 201, 400, 401, 403, 422, 500

---

## 🎯 Points d'Attention

1. **Ordre d'exécution** : Respecter l'ordre des scénarios pour éviter les erreurs
2. **Tokens** : Les tokens Sanctum expirent, il faut se reconnecter si nécessaire
3. **Données de test** : Utiliser des emails uniques à chaque test
4. **Nettoyage** : Supprimer les clients de test après les tests
5. **Cryptos** : S'assurer que les cryptos existent en base avant les transactions
6. **Solde** : Vérifier le solde avant chaque transaction d'achat
7. **Portfolio** : Vérifier que le portfolio contient la crypto avant une vente

---

## 📝 Notes Importantes

- Toutes les routes nécessitent le header `Accept: application/json`
- Les routes protégées nécessitent `Authorization: Bearer {token}`
- Les routes admin nécessitent le rôle `admin`
- Les routes client nécessitent le rôle `client` ET le statut `active`
- Le format des montants est en `decimal:2` pour les euros et `decimal:8` pour les cryptos
- Le PAMU (Prix d'Achat Moyen Unitaire) est calculé automatiquement lors des achats

---

**Fin du document - Bonne chance avec vos tests ! 🚀**

