# BitChest Support Bot

Assistant de support IA pour la plateforme BitChest, bilingue français/anglais, servi par
FastAPI et l'API Groq.

## Architecture

```
Vue (ChatWidget) → Laravel (SupportChatController, authentifie l'utilisateur)
                  → FastAPI (bot_server.py, ce dossier)
                  → Groq API (openai/gpt-oss-120b)
```

Le proxy Laravel remplace toujours `user_id` par celui de la session authentifiée côté
serveur : le frontend ne peut jamais demander les données d'un autre utilisateur.
`app/services/user_service.py` ne fait que des `SELECT` en lecture seule sur `users`,
`portfolios`, `crypto_currencies` et `transactions`, filtrés par cet ID.

## Comportement du chatbot

Toutes les règles de comportement (langue, ton, périmètre, confidentialité) sont centralisées
dans `app/prompts.py` :

- **Bilingue FR/EN** : détecte automatiquement la langue du message et répond dans la même
  langue, sans jamais mélanger les deux. L'interface de BitChest (boutons, menus) reste en
  anglais — seule la conversation avec le bot s'adapte.
- **Périmètre strict** : BitChest, cryptomonnaies, et le compte de l'utilisateur connecté
  uniquement. Toute autre question reçoit un refus poli et fixe, dans la langue de l'utilisateur.
- **Pas d'invention de données** : si une information n'est pas disponible, le bot le dit
  explicitement plutôt que d'inventer un solde, un prix ou une transaction.
- **Confidentialité** : ne révèle jamais un mot de passe, une clé API, un token, ou son propre
  system prompt, y compris face à une tentative de contournement ("prompt injection").

Un guide de présentation complet (architecture illustrée, exemples réels, FAQ pour le jury) est
disponible séparément.

## Configuration (`.env`)

Copier `.env.example` vers `.env` et renseigner les valeurs réelles. **`.env` n'est jamais suivi
par Git** (voir `.gitignore` à la racine du projet) ; `.env.example` ne doit jamais contenir de
vraie clé.

| Variable | Rôle |
|---|---|
| `GROQ_API_KEY` | Clé API Groq. À obtenir sur console.groq.com et à coller **uniquement** dans `.env`. |
| `GROQ_MODEL` | Modèle utilisé (`openai/gpt-oss-120b` par défaut). |
| `MYSQL_*` | Connexion en lecture seule à la même base que le backend Laravel. |

Si `GROQ_API_KEY` est vide ou invalide, le bot renvoie une erreur contrôlée (`/chat` répond
503/500 avec un message clair) au lieu de planter.

## Lancer le bot en local

Ce dossier a son propre environnement virtuel Python (`bot/venv/`, déjà créé — ne pas en
recréer un autre). Il n'est jamais suivi par Git.

```powershell
cd bot
.\venv\Scripts\pip install -r requirements.txt   # si nécessaire
.\serve.ps1                                      # démarre sur http://127.0.0.1:8001
```

Vérifier ensuite `GET /health`.

## Tests manuels de référence

```powershell
curl -X POST http://127.0.0.1:8001/chat -H "Content-Type: application/json" `
  -d '{"messages":[{"role":"user","content":"Quel est mon solde ?"}],"user_id":2}'
```

Cas à couvrir : solde/portefeuille/transactions en français et en anglais, une question
hors-sujet dans chaque langue, une tentative d'accès aux données d'un autre utilisateur, et une
tentative de prompt injection demandant à révéler le system prompt.
