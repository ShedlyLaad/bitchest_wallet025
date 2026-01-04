# Configuration Email Universelle

Le système de mailing a été configuré pour fonctionner avec **tous les fournisseurs d'email** (Gmail, Outlook, Yahoo, AOL, domaines personnalisés, etc.).

## Configuration dans .env

Pour que le système fonctionne avec tous les fournisseurs, configurez les variables suivantes dans votre fichier `.env` :

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=votre-email@gmail.com
MAIL_PASSWORD=votre-mot-de-passe-app
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=votre-email@gmail.com
MAIL_FROM_NAME="BitChest"
```

## Configuration pour différents fournisseurs

### Gmail
```env
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_ENCRYPTION=tls
```
**Important** : Pour Gmail, vous devez utiliser un **mot de passe d'application** (pas votre mot de passe normal). Créez-en un dans les paramètres de sécurité de votre compte Google.

### Outlook / Hotmail / Live
```env
MAIL_HOST=smtp-mail.outlook.com
MAIL_PORT=587
MAIL_ENCRYPTION=tls
```

### Yahoo
```env
MAIL_HOST=smtp.mail.yahoo.com
MAIL_PORT=587
MAIL_ENCRYPTION=tls
```

### AOL
```env
MAIL_HOST=smtp.aol.com
MAIL_PORT=587
MAIL_ENCRYPTION=tls
```

### Domaines personnalisés
Pour les domaines personnalisés (ex: `@votreentreprise.com`), utilisez les paramètres SMTP fournis par votre hébergeur email.

## Fonctionnement automatique

Le système `UniversalMailService` détecte automatiquement le serveur SMTP approprié selon le domaine de l'email de destination **si MAIL_HOST n'est pas défini dans .env**.

Cependant, **la méthode recommandée** est de configurer un serveur SMTP unique dans `.env` qui sera utilisé pour tous les emails envoyés.

## Utilisation

Le service est automatiquement utilisé dans :
- `TemporaryPasswordMailable` - Envoi de mot de passe temporaire
- `UserCreatedMail` - Notification de création de compte
- `VerifyEmailMailable` - Vérification d'email

Tous les emails sont maintenant envoyés via `UniversalMailService` qui garantit la compatibilité avec tous les fournisseurs.

## Test

Pour tester la configuration, vous pouvez :
1. Créer un nouveau compte utilisateur
2. Vérifier que l'email de mot de passe temporaire est bien reçu
3. Tester avec différents fournisseurs d'email (Gmail, Outlook, Yahoo, etc.)

