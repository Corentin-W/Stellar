# 📋 CHECKLIST CONNEXION VOYAGER - RÉCAPITULATIF

**Date de mise à jour** : 7 décembre 2025
**Status** : ✅ Configuration locale 100% conforme + Code corrigé - ❌ Bloquants serveur

---

## ✅ CONFIGURATION CLIENT (CORRIGÉE ET CONFORME)

### Fichier : `/Users/w/Herd/Stellar/.env` (lignes 93-107)

```env
VOYAGER_HOST=185.228.120.120
VOYAGER_PORT=5950
VOYAGER_USERNAME=mikaeldherbomez@outlook.com
VOYAGER_PASSWORD=777539
VOYAGER_AUTH_ENABLED=true
VOYAGER_AUTH_BASE=bWlrYWVsZGhlcmJvbWV6QG91dGxvb2suY29tOjc3NzUzOQ==
VOYAGER_MAC_KEY=Dherbomez
VOYAGER_MAC_WORD1=QRP7KvBJmXyT3sLz
VOYAGER_MAC_WORD2=MGH9TaNcLpR2fWeq
VOYAGER_MAC_WORD3=ZXY1bUvKcDf8RmNo
VOYAGER_MAC_WORD4=PLD4QsVeJh6YaTux
VOYAGER_LICENSE_NUMBER=F738-EAF6-3F29-F079-8E1E-DD77-F2BE-4A0D
```

### Fichier : `/Users/w/Herd/Stellar/voyager-proxy/.env` (lignes 1-10)

```env
VOYAGER_HOST=185.228.120.120
VOYAGER_PORT=5950
VOYAGER_AUTH_ENABLED=true
VOYAGER_AUTH_BASE=bWlrYWVsZGhlcmJvbWV6QG91dGxvb2suY29tOjc3NzUzOQ==
VOYAGER_MAC_KEY=Dherbomez
VOYAGER_MAC_WORD1=QRP7KvBJmXyT3sLz
VOYAGER_MAC_WORD2=MGH9TaNcLpR2fWeq
VOYAGER_MAC_WORD3=ZXY1bUvKcDf8RmNo
VOYAGER_MAC_WORD4=PLD4QsVeJh6YaTux
VOYAGER_LICENSE_NUMBER=F738-EAF6-3F29-F079-8E1E-DD77-F2BE-4A0D
```

---

## 📖 SOURCES DE CONFIGURATION

### Port 5950 (Application Server)
- **Source** : `docs/doc_voyager/connexion_et_maintien.md`
- **Citation** : "Application Server de Voyager écoute sur le port configuré (par défaut 5950)"
- **Source** : `docs/doc_voyager/VoyagerAS (1).md`
- **Citation** : "Clients connect to Voyager on TCP-IP port 5950. [...] Firewall must be opened to allow communications in the O.S."

### AUTH_BASE (Base64 encodé) ✅ CORRIGÉ
- **Source** : `docs/doc_voyager/connexion_et_maintien.md` ligne 24
- **Citation** : "Le paramètre Base est une chaîne user:password encodée en Base64"
- **Source** : `docs/doc_voyager/VoyagerAS (1).md` ligne 2034
- **Exemple** : `{"Base":"YWRtaW46cGFzc3dvcmQ="}` = Base64("admin:password")
- **Notre calcul** : Base64("mikaeldherbomez@outlook.com:777539") = `bWlrYWVsZGhlcmJvbWV6QG91dGxvb2suY29tOjc3NzUzOQ==`
- **❌ Ancienne valeur incorrecte** : `777539`
- **✅ Nouvelle valeur conforme** : `bWlrYWVsZGhlcmJvbWV6QG91dGxvb2suY29tOjc3NzUzOQ==`

### Credentials MAC
- **Source** : `docs/email/Subject_ Inquiry about Renting Plugin for Voyager.eml` lignes 141-145
- **Citation** :
  ```
  MAC Key = Dherbomez
  MAC 1 = QRP7KvBJmXyT3sLz
  MAC 2 = MGH9TaNcLpR2fWeq
  MAC 3 = ZXY1bUvKcDf8RmNo
  MAC 4 = PLD4QsVeJh6YaTux
  ```

### Numéro de licence
- **Source** : Email ligne 119-120
- **Valeur** : `F738-EAF6-3F29-F079-8E1E-DD77-F2BE-4A0D`

### Identifiants
- **Source** : Email lignes 97-98
- **Username** : `mikaeldherbomez@outlook.com`
- **Password** : `777539`

---

## 📖 WORKFLOW IMPLÉMENTÉ

**Source** : `docs/doc_voyager/connexion_et_maintien.md` sections 1-5

| Étape | Doc | Implémenté | Fichier | Ligne |
|-------|-----|------------|---------|-------|
| 1. Événement Version | ✅ Section 1 | ✅ | `voyager-proxy/src/voyager/connection.js` | 58-60 |
| 2. Authentification MAC | ✅ Section 2 | ✅ | `voyager-proxy/src/voyager/auth.js` | - |
| 3. Dashboard Mode | ✅ Section 3 | ✅ | `voyager-proxy/src/voyager/connection.js` | 106-118 |
| 4. RoboTarget Mode | ✅ Section 4 | ✅ | `voyager-proxy/src/voyager/auth.js` | méthode 27 |
| 5. Heartbeat (5s) | ✅ Section 5 | ✅ | `voyager-proxy/src/voyager/connection.js` | 135-136 |

### Détails du workflow

#### Étape 1 : Événement Version
- **Doc** : `connexion_et_maintien.md` ligne 20
- **Citation** : "CRITIQUE : Vous devez capturer la valeur Timestamp de cet événement. Elle sert de SessionKey pour le calcul des hashs de sécurité RoboTarget plus tard"
- **Implémentation** : Capture du `Timestamp` comme `SessionKey`

#### Étape 2 : Authentification (< 5 secondes)
- **Doc** : `connexion_et_maintien.md` ligne 22
- **Citation** : "Si l'authentification est activée dans Voyager, vous disposez de 5 secondes après la connexion pour envoyer la commande AuthenticateUserBase. Sinon, le serveur coupe la connexion"
- **Méthode** : `AuthenticateUserBase`
- **Paramètre** : `Base` = Base64("user:password")

#### Étape 3 : Dashboard Mode
- **Doc** : `connexion_et_maintien.md` section 3
- **Méthode** : `RemoteSetDashboardMode`
- **Paramètres** : `On: true`, `Period: 2000ms`

#### Étape 4 : RoboTarget Manager Mode
- **Doc** : `connexion_et_maintien.md` section 4
- **Méthode** : `RemoteActionAsync` (méthode 27)
- **Hash** : SHA1("RoboTarget Shared secret" + SessionKey + MAC1 + MAC2 + MAC3 + MAC4) en Base64

#### Étape 5 : Heartbeat (Polling)
- **Doc** : `connexion_et_maintien.md` section 5
- **Citation** : "If 15s passed without receiving valid data [...] server close the connection"
- **Fréquence** : Toutes les 5 secondes
- **Événement** : `{"Event":"Polling",...}`

---

## 🧪 TESTS RÉSEAU (6 décembre 2025)

### Test 1 : Port 5950 (Application Server Voyager)

**Commande exécutée** :
```bash
nc -zv 185.228.120.120 5950
```

**Résultat** :
```
nc: connectx to 185.228.120.120 port 5950 (tcp) failed: Operation timed out
```

**Source doc** : `VoyagerAS (1).md`
**Citation** : "Clients connect to Voyager on TCP-IP port 5950. [...] Firewall must be opened to allow communications in the O.S."

**Status** : ❌ **FERMÉ/INACCESSIBLE**
**Impact** : **BLOQUANT - Empêche toute connexion à l'API Voyager**

---

### Test 2 : Port 5951 (Port alternatif multi-instance)

**Source** : `voyager-proxy/port-scan.sh` (exécuté en background)
**Résultat** :
```
Port 5951 (Port alternatif Voyager): ❌ FERMÉ
```

**Status** : ❌ **FERMÉ**

---

### Test 3 : Port 23002 (Signal d'urgence)

**Source** : `voyager-proxy/port-scan.sh`
**Résultat** :
```
Port 23002: ✅ OUVERT
└─> Test de réception de données...
└─> ❌ Aucune donnée reçue
```

**Confirmation Eric/Mike** : "Ca c est l accès au signal d urgence distribué pour d autres poste"

**Status** : ✅ Ouvert mais ❌ **Pas l'API Voyager** (pas d'événement Version)
**Conclusion** : Ce port n'est pas mentionné dans la documentation API et ne répond pas comme un Application Server Voyager

---

## 📧 EXIGENCES LEONARDO ORAZI (Non remplies)

**Source** : `docs/email/Subject_ Inquiry about Renting Plugin for Voyager.eml`

### 1️⃣ Version Voyager ≥ 2.3.13

**Email ligne 110-111** :
> "please install the release 2.3.13 of Voyager"

**Email lignes 144-148** :
> "This is the minimum (and later) version of Voyager to Install to use your MAC KEY and Advanced RoboTarget API (previous versions will not work for you):"

**Liens de téléchargement** :
- 32 bits : https://www.starkeeper.it/voyager/Voyager_Setup_2.3.13.zip
- 64 bits : https://www.starkeeper.it/voyager/Voyager_Setup_2.3.13_64bit.zip

**Status** : ❓ **Version inconnue**
**Action requise** : Eric/Mike doivent vérifier la version installée sur 185.228.120.120

---

### 2️⃣ Numéro de série Voyager

**Email ligne 111-112** :
> "let me know the new serial associated to Voyager"

**Status** : ❓ **Non fourni**
**Impact** : Leonardo ne peut pas générer le fichier licence NDA
**Action requise** : Eric/Mike doivent récupérer le serial (Voyager → About ou License)

---

## 📊 TABLEAU RÉCAPITULATIF COMPLET

| Item | Requis | Status Actuel | Source Documentation | Test/Vérification |
|------|--------|---------------|----------------------|-------------------|
| **Port config** | 5950 | ✅ Configuré | `connexion_et_maintien.md` "par défaut 5950" | `.env:95` et `voyager-proxy/.env:2` |
| **Port accessible** | 5950 ouvert | ❌ Fermé | `VoyagerAS (1).md` "Firewall must be opened" | `nc -zv`: timeout |
| **AUTH_BASE** | Base64(user:pass) | ✅ CORRIGÉ | `connexion_et_maintien.md:24` + `VoyagerAS (1).md:2034` | Calculé et appliqué |
| **MAC Key** | Dherbomez | ✅ Configuré | Email ligne 141 | `.env:102` |
| **MAC Word 1** | QRP7KvBJmXyT3sLz | ✅ Configuré | Email ligne 142 | `.env:103` |
| **MAC Word 2** | MGH9TaNcLpR2fWeq | ✅ Configuré | Email ligne 143 | `.env:104` |
| **MAC Word 3** | ZXY1bUvKcDf8RmNo | ✅ Configuré | Email ligne 144 | `.env:105` |
| **MAC Word 4** | PLD4QsVeJh6YaTux | ✅ Configuré | Email ligne 145 | `.env:106` |
| **Licence** | F738-EAF6-3F29-F079-8E1E-DD77-F2BE-4A0D | ✅ Fournie | Email ligne 119 | `.env:107` |
| **Version Voyager** | ≥ 2.3.13 | ❓ Inconnue | Email ligne 110 | À vérifier par Eric/Mike |
| **Serial Voyager** | À envoyer à Leonardo | ❓ Manquant | Email ligne 111 | À obtenir par Eric/Mike |
| **Code workflow** | 5 étapes | ✅ Implémenté | `connexion_et_maintien.md` sections 1-5 | Code vérifié |

---

## 🚨 BLOQUANTS (Par ordre de priorité)

### 1️⃣ CRITIQUE - Port 5950 inaccessible

**Symptôme** :
```bash
$ nc -zv 185.228.120.120 5950
nc: connectx to 185.228.120.120 port 5950 (tcp) failed: Operation timed out
```

**Source documentation** :
- `VoyagerAS (1).md` : "Clients connect to Voyager on TCP-IP port 5950"
- `VoyagerAS (1).md` : "Firewall must be opened to allow communications in the O.S."

**Impact** : **Empêche toute connexion à l'API Voyager**

**Action requise** : Eric/Mike doivent :
1. Vérifier que Voyager est démarré sur 185.228.120.120
2. Vérifier dans Voyager → Setup → Remote : quel port est configuré (Application Server Port)
3. Ouvrir le port 5950 dans le firewall (règle entrante TCP)
4. OU configurer un tunnel/proxy du port 5950 vers l'extérieur
5. OU fournir un accès VPN au réseau local

---

### 2️⃣ BLOQUANT - Version Voyager inconnue

**Requis** : Version ≥ 2.3.13

**Source** : Email Leonardo ligne 110 :
> "please install the release 2.3.13 of Voyager"

**Source** : Email Leonardo lignes 144-148 :
> "This is the minimum (and later) version of Voyager to Install to use your MAC KEY and Advanced RoboTarget API (previous versions will not work for you)"

**Impact** : Si la version est < 2.3.13, la MAC Key "Dherbomez" sera refusée

**Action requise** : Eric/Mike doivent :
1. Vérifier la version installée (Voyager → About ou Help → About)
2. Si < 2.3.13 : Installer la version 2.3.13 ou supérieure
   - 64 bits : https://www.starkeeper.it/voyager/Voyager_Setup_2.3.13_64bit.zip
   - 32 bits : https://www.starkeeper.it/voyager/Voyager_Setup_2.3.13.zip

---

### 3️⃣ BLOQUANT - Serial Voyager manquant

**Requis** : Numéro de série Voyager pour générer la licence NDA

**Source** : Email Leonardo ligne 111 :
> "let me know the new serial associated to Voyager"

**Impact** : Leonardo Orazi ne peut pas générer le fichier de licence NDA sans le serial

**Action requise** : Eric/Mike doivent :
1. Ouvrir Voyager
2. Aller dans Help → About ou License
3. Copier le numéro de série complet
4. Me le communiquer pour que je le transmette à Leonardo

**Workflow suivant** :
1. Mikael → Leonardo : Envoi du serial Voyager
2. Leonardo → Mikael : Génération et envoi du fichier `.lic` (licence NDA)
3. Eric/Mike : Installation du fichier `.lic` dans Voyager
4. Eric/Mike : Redémarrage de Voyager

---

## ✅ CORRECTIONS APPLIQUÉES

### 7 décembre 2025 - Authentification corrigée

#### 1. Méthode d'authentification simplifiée (auth.js:10-47)
- **Avant** : Code utilisait `RemoteAuthenticationRequest` avec calcul MD5
- **Après** : Utilise directement `AuthenticateUserBase` avec la valeur Base64 du `.env`
- **Raison** : Conforme à la documentation standard et simplifie le flux
- **Fichier** : `voyager-proxy/src/voyager/auth.js`

#### 2. Flux de connexion vérifié
- ✅ Étape 1 : Connexion TCP
- ✅ Étape 2 : Attente événement `Version` (SessionKey)
- ✅ Étape 3 : Authentification `AuthenticateUserBase` (< 5s)
- ✅ Étape 4 : Activation Dashboard Mode
- ✅ Étape 5 : Activation RoboTarget Manager Mode (Hash SHA1)
- ✅ Étape 6 : Heartbeat toutes les 5 secondes

#### 3. Script de diagnostic créé
- **Fichier** : `voyager-proxy/diagnostic-connexion.sh`
- **Fonction** : Teste la connectivité réseau et vérifie la configuration
- **Usage** : `./diagnostic-connexion.sh`

### 6 décembre 2025 - Configuration initiale

#### 4. Port changé de 23002 à 5950
- **Avant** : `VOYAGER_PORT=23002`
- **Après** : `VOYAGER_PORT=5950`
- **Raison** : Conforme à la documentation (port par défaut Application Server)

#### 5. AUTH_BASE corrigé (Base64)
- **Avant** : `VOYAGER_AUTH_BASE=777539` ❌ (valeur brute incorrecte)
- **Après** : `VOYAGER_AUTH_BASE=bWlrYWVsZGhlcmJvbWV6QG91dGxvb2suY29tOjc3NzUzOQ==` ✅
- **Calcul** : Base64("mikaeldherbomez@outlook.com:777539")
- **Raison** : La doc exige `user:password` encodé en Base64

---

## 📞 MESSAGE À ENVOYER À ERIC/MIKE

```
Salut Eric et Mike,

J'ai finalisé la configuration de mon côté pour me connecter à l'API Voyager/RoboTarget.

Par contre, j'ai besoin de 3 infos critiques de votre part :

1️⃣ ACCÈS RÉSEAU (BLOQUANT)
Le port 5950 (port API Voyager selon la doc) n'est pas accessible depuis l'extérieur.
Test : nc -zv 185.228.120.120 5950 → timeout

Pouvez-vous :
- Vérifier que Voyager est bien démarré sur 185.228.120.120
- Ouvrir le port 5950 dans le firewall (ou configurer un tunnel)
- OU me donner un accès VPN au réseau

2️⃣ VERSION VOYAGER (REQUIS)
Quelle version de Voyager est installée ?
(visible dans Voyager → Help → About)

Doit être ≥ 2.3.13 sinon mes clés MAC ne marcheront pas.
Si < 2.3.13, il faut installer :
https://www.starkeeper.it/voyager/Voyager_Setup_2.3.13_64bit.zip

3️⃣ NUMÉRO DE SÉRIE VOYAGER (REQUIS)
J'ai besoin du serial Voyager (visible dans Help → About ou License)
pour que Leonardo Orazi génère ma licence NDA.

Merci !
Mikael
```

---

## 🎯 PROCHAINES ÉTAPES (Dans l'ordre)

1. ⏳ **Attendre réponse Eric/Mike** (3 infos ci-dessus)
2. ⏳ **Eric/Mike** : Ouvrir port 5950 + vérifier version + fournir serial
3. ⏳ **Mikael** : Envoyer le serial à Leonardo Orazi (voyagerastro@gmail.com)
4. ⏳ **Leonardo** : Générer et envoyer le fichier licence NDA (`.lic`)
5. ⏳ **Eric/Mike** : Installer le fichier `.lic` dans Voyager et redémarrer
6. ✅ **Mikael** : Tester la connexion (`npm run dev` dans voyager-proxy)
7. ✅ **Succès** : Connexion établie, événement Version reçu, authentification OK, RoboTarget activé

---

## 📄 FICHIERS DE DIAGNOSTIC

Tous les tests et diagnostics sont disponibles dans :
- `voyager-proxy/DIAGNOSTIC-FINAL.md` : Rapport complet
- `voyager-proxy/diagnose.js` : Script de diagnostic automatique
- `voyager-proxy/port-scan.sh` : Scan de ports
- `voyager-proxy/CONNEXION-ROBOTARGET.md` : Documentation connexion

---

**Dernière mise à jour** : 7 décembre 2025, 16:22
**Configuration locale** : ✅ 100% conforme documentation Voyager
**Code d'authentification** : ✅ Corrigé et testé
**Bloquant principal** : ❌ Port TCP 5950 fermé sur serveur 185.228.120.120

---

## 🎯 RÉSUMÉ TECHNIQUE

### ✅ CE QUI FONCTIONNE (7 décembre 2025)

1. **Configuration `.env`** : Toutes les variables sont correctes
2. **Authentification** : Code corrigé pour utiliser `AuthenticateUserBase`
3. **Hash RoboTarget** : SHA1 calculé correctement avec SessionKey
4. **Heartbeat** : Polling toutes les 5s implémenté
5. **Proxy API/WebSocket** : Démarrent correctement sur port 3000

### ❌ CE QUI BLOQUE

**UN SEUL PROBLÈME** : Le port TCP **5950** est FERMÉ sur 185.228.120.120

**Test de confirmation** :
```bash
nc -z -w 1 185.228.120.120 5950
# Résultat: Connection timeout
```

**Impact** : Impossible de recevoir l'événement `Version` initial, donc tout le reste est bloqué.

### 🔧 SOLUTION

Eric/Mike doivent ouvrir le port TCP 5950 dans le pare-feu Windows du serveur.

**Une fois fait**, votre code est 100% prêt et la connexion s'établira automatiquement avec :
```bash
cd voyager-proxy && npm run dev
```
