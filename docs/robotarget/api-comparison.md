# Comparaison des APIs RoboTarget Voyager

## Vue d'ensemble

Voyager propose deux APIs pour interagir avec RoboTarget:

1. **Open API** - Consultation en lecture seule (MD5)
2. **Reserved API (NDA)** - Gestion complète en lecture/écriture (SHA1)

---

## 1. Open API (Publique)

### Caractéristiques
- ✅ **Accès:** Consultation des données
- ✅ **Complexité:** Faible
- ✅ **Activation:** Aucune activation préalable requise

### Formule MAC
```
MAC = MD5(SharedSecret + UID)
Résultat: Hexadécimal (32 caractères)
```

### Exemple concret
```javascript
SharedSecret: "Dherbomez"
UID: "test-001"

// Calcul
macString = "Dherbomeztest-001"
mac = md5(macString) = "92b3304de90518d049012bd8d6580346"

// Commande JSON-RPC
{
  "method": "RemoteOpenRoboTargetGetTargetList",
  "params": {
    "UID": "test-001",
    "MAC": "92b3304de90518d049012bd8d6580346"
  },
  "id": 100
}
```

### Commandes disponibles
- `RemoteOpenRoboTargetGetTargetList` - Lister toutes les targets
- `RemoteOpenRoboTargetGetShotDoneList` - Lister les shots complétés
- `RemoteOpenRoboTargetSetShotDoneRating` - Noter un shot

---

## 2. Reserved API (NDA)

### Caractéristiques
- ✅ **Accès:** Gestion complète (lecture + écriture)
- ⚠️ **Complexité:** Élevée
- ⚠️ **Activation:** Nécessite activation du **Manager Mode**

### Prérequis: Activation Manager Mode

Avant d'utiliser les commandes Reserved API, vous devez activer le Manager Mode:

```javascript
// Formule spéciale pour l'activation
// Format: MACKey||:||SessionKey||:||Word1||:||Word2||:||Word3||:||Word4
const hashString = `${macKey}||:||${sessionKey}||:||${word1}||:||${word2}||:||${word3}||:||${word4}`;
const hash = sha1(hashString).toHex().toBase64();

// Commande d'activation
{
  "method": "RemoteSetRoboTargetManagerMode",
  "params": {
    "UID": "activation-uid",
    "MACKey": "Dherbomez",
    "Hash": hash
  },
  "id": 1
}
```

### Formule MAC (Commandes standard)

**⚠️ IMPORTANT:** La formule MAC utilise le **même séparateur** que l'activation Manager Mode!

```
Format: SharedSecret||:||SessionKey||:||ID||:||UID
Hachage: SHA1 → Hexadécimal → Base64
```

**Étapes détaillées:**
1. Construire la chaîne: `SharedSecret||:||SessionKey||:||ID||:||UID`
2. Calculer le SHA1 en hexadécimal (40 caractères)
3. Encoder le hex en Base64 (60 caractères)

### Exemple concret

```javascript
SharedSecret: "Dherbomez"
SessionKey: "1766412413.48277"  // Reçu à la connexion (Event: Version)
ID: "2"                          // JSON-RPC ID de la commande
UID: "test-reserved-001"

// Étape 1: Construction de la chaîne
macString = "Dherbomez||:||1766412413.48277||:||2||:||test-reserved-001"

// Étape 2: SHA1 → Hex
hexHash = sha1(macString).toHex()
// Résultat: "5c176e7f9d8330757dbe6061ae27eb1454fb0ea4"

// Étape 3: Hex → Base64
mac = base64Encode(hexHash)
// Résultat: "NWMxNzZlN2Y5ZDgzMzA3NTdkYmU2MDYxYWUyN2ViMTQ1NGZiMGVhNA=="

// Commande JSON-RPC
{
  "method": "RemoteRoboTargetGetSet",
  "params": {
    "ProfileName": "",
    "RefGuidSet": "",
    "UID": "test-reserved-001",
    "MAC": "NWMxNzZlN2Y5ZDgzMzA3NTdkYmU2MDYxYWUyN2ViMTQ1NGZiMGVhNA=="
  },
  "id": 2
}
```

### Commandes disponibles

#### Sets
- `RemoteRoboTargetGetSet` - Lister/récupérer les sets
- `RemoteRoboTargetAddSet` - Créer un nouveau set
- `RemoteRoboTargetUpdateSet` - Modifier un set existant
- `RemoteRoboTargetRemoveSet` - Supprimer un set

#### Targets
- `RemoteRoboTargetGetTarget` - Récupérer une target
- `RemoteRoboTargetAddTarget` - Créer une nouvelle target
- `RemoteRoboTargetUpdateTarget` - Modifier une target
- `RemoteRoboTargetRemoveTarget` - Supprimer une target

#### Shots
- `RemoteRoboTargetAddShot` - Ajouter un shot à une target
- `RemoteRoboTargetGetShotJpg` - Récupérer l'image d'un shot
- `RemoteRoboTargetGetShotDoneBySessionList` - Lister les shots d'une session
- `RemoteRoboTargetGetShotDoneSinceList` - Lister les shots depuis un timestamp

#### Séquences de base
- `RemoteRoboTargetGetBaseSequence` - Lister les séquences disponibles

---

## Tableau comparatif

| Caractéristique | Open API | Reserved API (NDA) |
|-----------------|----------|-------------------|
| **Algorithme** | MD5 | SHA1 |
| **Format final** | Hexadécimal | Base64 (du hex) |
| **Séparateur** | Aucun | `||:||` |
| **Variables** | Secret + UID | Secret + SessionKey + ID + UID |
| **Manager Mode** | Non requis | **OBLIGATOIRE** |
| **Droits** | Lecture seule | Lecture + Écriture |
| **Complexité** | ⭐ Faible | ⭐⭐⭐⭐⭐ Très élevée |

---

## Erreurs courantes

### ❌ MAC Error (Reserved API)

**Causes possibles:**

1. **Mauvais séparateur** ⚠️ Le plus fréquent!
   - ❌ FAUX: `|| |` / `||  |` / `|| |` (avec espaces)
   - ✅ CORRECT: `||:||` (avec deux-points)

2. **Mauvaise conversion SHA1**
   - ❌ FAUX: SHA1 binaire → Base64 directement
   - ✅ CORRECT: SHA1 → Hex string → Base64 encode du hex

3. **Manager Mode non activé**
   - Les commandes Reserved nécessitent l'activation préalable

4. **SessionKey incorrecte**
   - Doit être la chaîne exacte reçue dans l'événement `Version`
   - Format: `"1766412413.48277"` (string, pas float!)

5. **ID incorrect**
   - Doit correspondre exactement à l'ID JSON-RPC de la commande

---

## Implémentation Node.js

### Open API (Simple)
```javascript
import crypto from 'crypto';

function generateOpenApiMAC(sharedSecret, uid) {
  const macString = sharedSecret + uid;
  return crypto.createHash('md5').update(macString).digest('hex');
}
```

### Reserved API (Complexe)
```javascript
import crypto from 'crypto';

function generateReservedApiMAC(sharedSecret, sessionKey, jsonRpcId, uid) {
  const separator = '||:||';
  const macString = sharedSecret + separator +
                    String(sessionKey) + separator +
                    String(jsonRpcId) + separator +
                    String(uid);

  // SHA1 → Hex → Base64
  const hexHash = crypto.createHash('sha1').update(macString).digest('hex');
  const mac = Buffer.from(hexHash, 'utf8').toString('base64');

  return mac;
}
```

---

## Analogie pour comprendre

### Open API
C'est comme utiliser un **badge magnétique** pour entrer dans un bâtiment:
- Simple et direct
- Accès limité aux zones publiques
- Une seule clé (Secret + UID)

### Reserved API
C'est comme un **coffre de banque** où vous devez:
1. D'abord obtenir l'autorisation du manager (Manager Mode)
2. Tourner plusieurs cadrans dans un ordre précis (Secret, SessionKey, ID, UID)
3. Utiliser une clé complexe avec une précision au millimètre (SHA1→Hex→Base64)
4. Respecter exactement les intervalles entre les cadrans (`||:||`)

---

## Recommandations

### Utiliser Open API quand:
- ✅ Vous avez besoin de **consulter** uniquement
- ✅ Vous voulez une intégration **simple et rapide**
- ✅ Vous développez un **dashboard de visualisation**

### Utiliser Reserved API quand:
- ✅ Vous devez **créer/modifier/supprimer** des targets
- ✅ Vous construisez un **outil d'automatisation**
- ✅ Vous avez accès aux **credentials NDA**

---

## Ressources

- Documentation officielle Voyager
- NDA Reserved API Specification
- [Code source du proxy](../../voyager-proxy/)

---

**Dernière mise à jour:** 22 décembre 2025
**Version:** 1.0
**Testé avec:** Voyager 2.x
