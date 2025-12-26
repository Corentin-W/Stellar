# 🎯 Guide complet : RoboTarget Sets en Production

## 📋 Table des matières

1. [Vue d'ensemble](#vue-densemble)
2. [Architecture du système](#architecture-du-système)
3. [Prérequis](#prérequis)
4. [Installation et configuration](#installation-et-configuration)
5. [Démarrage quotidien](#démarrage-quotidien)
6. [Utilisation](#utilisation)
7. [Dépannage](#dépannage)
8. [Sécurité](#sécurité)

---

## 🌟 Vue d'ensemble

Le système **RoboTarget Sets Manager** permet de gérer vos Sets Voyager depuis n'importe où dans le monde via une interface web moderne.

### Ce qui est possible

- ✅ **Consulter** tous vos Sets Voyager depuis Internet
- ✅ **Créer** de nouveaux Sets à distance
- ✅ **Modifier** les Sets existants
- ✅ **Activer/Désactiver** des Sets
- ✅ **Supprimer** des Sets
- ✅ **Rechercher et filtrer** par nom, tag, profil ou statut

### Fonctionnalités

- 🔐 Sécurisé avec authentification admin et clé API
- 🌍 Accessible depuis n'importe où (téléphone, tablette, ordinateur)
- ⚡ Interface réactive temps réel
- 🎨 Design moderne dark theme
- 📊 Statistiques en direct

---

## 🏗️ Architecture du système

```
┌─────────────────────────────────────────────────┐
│  Serveur de Production (Cloud)                  │
│  https://stellarloc.com                         │
│  ├─ Laravel (interface web)                     │
│  └─ Page admin: /admin/robotarget/sets          │
└──────────────┬──────────────────────────────────┘
               │
               │ Internet (HTTPS + Clé API)
               ↓
┌─────────────────────────────────────────────────┐
│  ngrok - Tunnel sécurisé                        │
│  URL: warningly-unvacuous-rosa.ngrok-free.dev   │
│  - Tunnel HTTPS chiffré                         │
│  - URL fixe (ne change pas)                     │
└──────────────┬──────────────────────────────────┘
               │
               │ Localhost (via tunnel)
               ↓
┌─────────────────────────────────────────────────┐
│  VOTRE PC LOCAL (doit être allumé)              │
│                                                  │
│  ┌────────────────────────────────────────┐    │
│  │ 1. Voyager (port 5950)                 │    │
│  │    - Logiciel d'astronomie             │    │
│  │    - Base de données RoboTarget        │    │
│  └────────────────────────────────────────┘    │
│                                                  │
│  ┌────────────────────────────────────────┐    │
│  │ 2. voyager-proxy (port 3003)           │    │
│  │    - API REST                          │    │
│  │    - Calcul automatique MAC            │    │
│  │    - Authentification par clé API      │    │
│  └────────────────────────────────────────┘    │
│                                                  │
│  ┌────────────────────────────────────────┐    │
│  │ 3. ngrok (tunnel)                      │    │
│  │    - Expose le port 3003 sur Internet  │    │
│  └────────────────────────────────────────┘    │
└─────────────────────────────────────────────────┘
```

### Comment ça fonctionne

1. **Vous accédez** à `https://stellarloc.com/admin/robotarget/sets` depuis n'importe où
2. **Le serveur de production** envoie une requête HTTPS à ngrok avec la clé API
3. **ngrok** transfère la requête via le tunnel vers votre PC local
4. **voyager-proxy** vérifie la clé API, calcule le MAC et communique avec Voyager
5. **Voyager** retourne les données RoboTarget
6. **La réponse remonte** jusqu'à votre navigateur

---

## 📦 Prérequis

### Sur votre PC local

- ✅ Windows (votre PC actuel)
- ✅ Voyager installé et fonctionnel
- ✅ Node.js v20+ installé
- ✅ ngrok installé (Microsoft Store ou ngrok.com)
- ✅ Connexion Internet stable

### Sur le serveur de production

- ✅ Laravel déployé sur https://stellarloc.com
- ✅ Fichier `.env` configuré
- ✅ Accès admin au site

---

## ⚙️ Installation et configuration

### 1️⃣ Configuration du PC local

#### A. Installer voyager-proxy

Le proxy est déjà installé dans :
```
C:\Users\PrimaLuceLab\Desktop\Code\voyager-proxy\
```

#### B. Configurer le proxy

Fichier : `voyager-proxy/.env`

```env
# Port du proxy
PORT=3003

# Connexion à Voyager
VOYAGER_HOST=127.0.0.1
VOYAGER_PORT=5950

# Authentification Voyager
VOYAGER_AUTH_ENABLED=true
VOYAGER_USERNAME=admin
VOYAGER_PASSWORD=6383

# RoboTarget Shared Secret (DOIT correspondre au champ "Secret" dans Voyager)
VOYAGER_SHARED_SECRET=Dherbomez

# Clé API pour sécuriser l'accès distant
API_KEY=sk_live_VoyagerProxy2025_SecureKey_YourRandomString123456789

# CORS - Autoriser les requêtes
CORS_ORIGIN=http://localhost,https://stellarloc.com

# Logging
LOG_LEVEL=info
```

**⚠️ Important** : La clé `API_KEY` doit être **identique** à celle du serveur de production.

#### C. Installer ngrok

Si ce n'est pas déjà fait :
1. Microsoft Store : Recherchez "ngrok" et installez
2. OU téléchargez depuis https://ngrok.com/download

#### D. Configurer ngrok

Créez un compte gratuit sur https://dashboard.ngrok.com/signup

Récupérez votre authtoken et configurez-le :
```bash
ngrok config add-authtoken VOTRE_TOKEN_ICI
```

### 2️⃣ Configuration du serveur de production

#### Fichier : `.env` (sur le serveur)

Ajoutez ces deux lignes :

```env
VOYAGER_PROXY_URL=https://warningly-unvacuous-rosa.ngrok-free.dev
VOYAGER_PROXY_API_KEY=sk_live_VoyagerProxy2025_SecureKey_YourRandomString123456789
```

**⚠️ Attention** :
- Pas d'espace avant/après
- **Pas de slash `/` à la fin** de l'URL
- La clé API doit être **identique** à celle du proxy local

#### Vider le cache Laravel

Après modification du `.env` :

```bash
php artisan config:clear
```

---

## 🚀 Démarrage quotidien

### Option 1 : Script automatique (Recommandé)

Double-cliquez sur le fichier sur votre bureau :
```
start-robotarget.bat
```

Ce script démarre automatiquement :
- ✅ voyager-proxy
- ✅ ngrok

### Option 2 : Démarrage manuel

#### Étape 1 : Démarrer Voyager

Lancez normalement le logiciel Voyager.

#### Étape 2 : Démarrer le proxy

Ouvrez un terminal (PowerShell ou CMD) :

```bash
cd C:\Users\PrimaLuceLab\Desktop\Code\voyager-proxy
npm run dev
```

Vous devriez voir :
```
✅ Stellar Voyager Proxy is ready!
✅ Connection fully established!
✅ RoboTarget Manager Mode ACTIVE
```

**⚠️ Laissez ce terminal ouvert**

#### Étape 3 : Démarrer ngrok

Ouvrez un **nouveau terminal** :

```bash
ngrok http 3003
```

Vous verrez :
```
Forwarding    https://warningly-unvacuous-rosa.ngrok-free.dev -> http://localhost:3003
```

**⚠️ Laissez ce terminal ouvert aussi**

### Vérification

Testez que tout fonctionne :

1. **Local** : http://localhost:3003/health
   - Doit retourner `{"status":"ok"}`

2. **Via ngrok** : https://warningly-unvacuous-rosa.ngrok-free.dev/health
   - Doit retourner `{"status":"ok"}`

3. **Production** : https://stellarloc.com/admin/robotarget/sets
   - Doit afficher vos Sets

---

## 💻 Utilisation

### Accéder à l'interface

**URL** : https://stellarloc.com/admin/robotarget/sets

**Prérequis** :
- Être connecté en tant qu'admin
- Voyager + proxy + ngrok doivent tourner sur votre PC local

### Interface principale

L'interface affiche :

#### 📊 Statistiques (en haut)
- **Total Sets** : Nombre total de Sets
- **Sets actifs** : Sets avec status = 0 (vert)
- **Sets inactifs** : Sets avec status = 1 (rouge)
- **Profils** : Nombre de profils Voyager différents

#### 🔍 Recherche et filtres
- **Barre de recherche** : Cherchez par nom, tag ou profil
- **Filtre statut** : Tous / Actifs / Inactifs
- **Filtre profil** : Liste déroulante des profils disponibles

#### 📋 Tableau des Sets

Pour chaque Set :
- **Nom** avec GUID
- **Profil Voyager**
- **Tag** (si défini)
- **Statut** (badge vert/rouge)
- **Défaut** (⭐ si Set par défaut)
- **Actions** disponibles

### Actions disponibles

#### 👁️ Voir
Affiche tous les détails du Set dans une modal :
- Nom complet
- GUID
- Profil
- Statut
- Set par défaut
- Tag
- Note

#### ✏️ Modifier
Ouvre un formulaire pour modifier :
- Nom du Set
- Profil Voyager
- Tag
- Statut (Actif/Inactif)
- Note

#### 🔒 Activer / 🔓 Désactiver
Bascule le statut entre actif (0) et inactif (1).

#### 🗑️ Supprimer
Supprime le Set **et toutes ses Targets associées**.

**⚠️ Action irréversible** - Une confirmation est demandée.

#### ➕ Créer un nouveau Set

Bouton "➕ Nouveau Set" en haut à droite.

Formulaire :
- **Nom du Set** * (obligatoire)
- **Profil Voyager** * (obligatoire - liste déroulante)
- **Tag** (optionnel)
- **Statut** : Actif / Inactif
- **Note** (optionnel)

#### 🔄 Rafraîchir

Bouton "🔄 Rafraîchir" pour recharger les Sets depuis Voyager.

Utile si vous avez modifié des Sets directement dans Voyager.

### Indicateur de connexion

En haut à droite :
- **● Connecté** (vert) : Tout fonctionne
- **● Déconnecté** (rouge) : Problème de connexion

Si déconnecté, vérifiez que :
1. Voyager tourne
2. voyager-proxy tourne
3. ngrok tourne

---

## 🐛 Dépannage

### Page vide / Aucun Set affiché

#### Cause possible 1 : Proxy non démarré

**Symptôme** : Indicateur "Déconnecté" en rouge

**Solution** :
```bash
cd C:\Users\PrimaLuceLab\Desktop\Code\voyager-proxy
npm run dev
```

#### Cause possible 2 : ngrok non démarré

**Symptôme** : Erreur de connexion dans la console

**Solution** :
```bash
ngrok http 3003
```

#### Cause possible 3 : Slash final dans l'URL

**Symptôme** : Erreur "Route not found"

**Solution** : Dans `.env` de production, vérifiez qu'il n'y a **PAS** de `/` à la fin :
```env
# ❌ Incorrect
VOYAGER_PROXY_URL=https://warningly-unvacuous-rosa.ngrok-free.dev/

# ✅ Correct
VOYAGER_PROXY_URL=https://warningly-unvacuous-rosa.ngrok-free.dev
```

Puis :
```bash
php artisan config:clear
```

### Erreur "Unauthorized" ou "Forbidden"

**Cause** : Clé API incorrecte ou manquante

**Solution** : Vérifiez que la clé est **identique** dans :

1. **Proxy local** (`voyager-proxy/.env`) :
   ```env
   API_KEY=sk_live_VoyagerProxy2025_SecureKey_YourRandomString123456789
   ```

2. **Serveur de production** (`.env`) :
   ```env
   VOYAGER_PROXY_API_KEY=sk_live_VoyagerProxy2025_SecureKey_YourRandomString123456789
   ```

Puis redémarrez le proxy.

### Erreur "MAC Error" dans les logs

**Cause** : Shared Secret incorrect

**Solution** : Vérifiez dans `voyager-proxy/.env` :
```env
VOYAGER_SHARED_SECRET=Dherbomez
```

Ce secret doit correspondre au champ **"Secret"** dans l'onglet **COMMON** de Voyager.

### Timeout lors de la récupération des Sets

**Cause** : Voyager ne répond pas ou n'est pas démarré

**Solution** :
1. Vérifiez que Voyager tourne
2. Vérifiez les logs du proxy pour voir les messages d'erreur
3. Redémarrez Voyager si nécessaire

### Port 3003 déjà utilisé

**Symptôme** : `EADDRINUSE: address already in use 0.0.0.0:3003`

**Solution** : Tuez l'ancien processus :

```bash
# Trouver le processus
netstat -ano | findstr ":3003"

# Tuer le processus (remplacez PID par le numéro affiché)
powershell "Stop-Process -Id PID -Force"
```

Puis redémarrez le proxy.

---

## 🔒 Sécurité

### Authentification multi-niveaux

Le système utilise **3 niveaux de sécurité** :

1. **Authentification Laravel** : Vous devez être connecté
2. **Middleware admin** : Vous devez être administrateur
3. **Clé API ngrok** : Protège l'accès au proxy depuis Internet

### Clé API

La clé API :
- ✅ Protège le proxy contre les accès non autorisés
- ✅ Est transmise via HTTPS (chiffré)
- ✅ Est vérifiée pour chaque requête

**Bonne pratique** : Changez régulièrement la clé API.

Pour changer la clé :
1. Générez une nouvelle clé aléatoire (minimum 32 caractères)
2. Mettez-la dans `voyager-proxy/.env` (ligne `API_KEY=`)
3. Mettez la même dans `.env` production (ligne `VOYAGER_PROXY_API_KEY=`)
4. Redémarrez le proxy
5. Videz le cache Laravel : `php artisan config:clear`

### HTTPS

Toutes les communications entre le serveur de production et votre PC passent par **HTTPS** grâce à ngrok.

Les données sont **chiffrées** en transit.

### Rate Limiting

Le proxy limite les requêtes à **100 par 15 minutes** par IP pour éviter les abus.

### Logs

Le proxy enregistre toutes les requêtes dans :
```
voyager-proxy/logs/
```

Consultez les logs en cas d'activité suspecte.

---

## 📊 Monitoring

### Interface de monitoring ngrok

Pendant que ngrok tourne, accédez à :
```
http://localhost:4040
```

Vous verrez :
- 📊 Toutes les requêtes HTTP en temps réel
- 🔍 Headers, body, réponses
- ⏱️ Temps de réponse
- 🐛 Erreurs éventuelles

Très utile pour déboguer !

### Logs du proxy

Le proxy affiche dans le terminal :
- ✅ Connexion à Voyager
- ✅ Activation du Manager Mode
- ✅ Requêtes RoboTarget
- ❌ Erreurs éventuelles

Niveau de log configurable dans `voyager-proxy/.env` :
```env
LOG_LEVEL=info  # debug | info | warn | error
```

---

## 🎯 Conseils et bonnes pratiques

### Pour un fonctionnement optimal

1. **Gardez votre PC allumé** quand vous voulez accéder aux Sets à distance
2. **Désactivez la mise en veille** si vous voulez un accès 24/7
3. **Utilisez le script de démarrage** pour gagner du temps
4. **Surveillez les logs** du proxy en cas de problème
5. **Rafraîchissez régulièrement** si vous modifiez des Sets dans Voyager

### Organisation des Sets

1. **Utilisez des tags** pour catégoriser vos Sets (galaxies, nébuleuses, comètes, etc.)
2. **Noms explicites** : Préférez "Galaxies d'hiver 2025" à "Set1"
3. **Notes détaillées** : Documentez le contenu et l'objectif de chaque Set
4. **Désactivez plutôt que supprimer** si vous n'êtes pas sûr

### Sauvegarde

Les Sets sont stockés dans Voyager. Pensez à :
1. **Sauvegarder régulièrement** votre base Voyager
2. **Exporter vos Sets** importants (fonctionnalité future possible)

---

## 🔄 Mise à jour

### Mise à jour du proxy

Pour mettre à jour le voyager-proxy :

```bash
cd C:\Users\PrimaLuceLab\Desktop\Code\voyager-proxy
git pull
npm install
```

Redémarrez le proxy après la mise à jour.

### Mise à jour de ngrok

ngrok se met à jour automatiquement via le Microsoft Store.

---

## 📞 Support

### Fichiers de test

Deux fichiers de test sont disponibles en production :

1. **Test de connexion ngrok** : https://stellarloc.com/test-ngrok.php
   - Vérifie que le serveur peut joindre ngrok
   - Teste la clé API

2. **Test du service Sets** : https://stellarloc.com/test-sets-prod.php
   - Teste directement le service RoboTargetSetService
   - Affiche les Sets récupérés

Utilisez-les pour diagnostiquer les problèmes.

### Documentation supplémentaire

- `ROBOTARGET-SETS-API.md` - Documentation API REST
- `SETS-API-RECAP.md` - Récapitulatif du service
- `ADMIN-SETS-GUIDE.md` - Guide d'utilisation de l'interface
- `SESSION-RECAP-COMPLETE.md` - Récapitulatif complet de la session

---

## ✅ Checklist de déploiement

### Configuration initiale (une seule fois)

- [ ] voyager-proxy installé et configuré
- [ ] ngrok installé et authtoken configuré
- [ ] `.env` de production configuré avec URL ngrok et clé API
- [ ] Cache Laravel vidé sur le serveur de production
- [ ] Test de connexion réussi (test-ngrok.php)
- [ ] Test du service réussi (test-sets-prod.php)

### Démarrage quotidien

- [ ] Voyager démarré
- [ ] voyager-proxy démarré (`npm run dev`)
- [ ] ngrok démarré (`ngrok http 3003`)
- [ ] Indicateur "Connecté" en vert sur stellarloc.com

### Vérification

- [ ] Page admin accessible : https://stellarloc.com/admin/robotarget/sets
- [ ] Sets affichés correctement
- [ ] Actions (créer, modifier, supprimer) fonctionnent
- [ ] Recherche et filtres opérationnels

---

## 🎉 Conclusion

Vous disposez maintenant d'un système complet pour gérer vos Sets RoboTarget depuis n'importe où dans le monde !

**Avantages** :
- ✅ Accès distant sécurisé
- ✅ Interface moderne et intuitive
- ✅ Données restent sur votre PC (sécurité)
- ✅ Temps réel via tunnel ngrok
- ✅ Aucune modification de Voyager nécessaire

**Profitez-en bien !** 🚀

---

*Documentation créée le 26 décembre 2025*
*Version 1.0*
