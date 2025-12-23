# Formules MAC pour RoboTarget - Documentation Complète

Ce document récapitule les formules exactes de hachage MAC pour l'API RoboTarget de Voyager.

## 📋 Paramètres de Sécurité

D'après votre configuration :

```
Custom Key / MAC Key: Dherbomez
RoboTarget Shared Secret: Dherbomez
MAC Word 1: QRP7KvBJmXyT3sLz
MAC Word 2: MGH9TaNcLpR2fWeq
MAC Word 3: ZXY1bUvKcDf8RmNo
MAC Word 4: PLD4QsVeJh6YaTux
```

## 🔐 Formule 1: Activation du Mode RoboTarget Manager

**Commande**: `RemoteSetRoboTargetManagerMode`

**Quand l'utiliser**: Une seule fois après l'authentification utilisateur, pour débloquer les API RoboTarget.

### Construction du Hash

```
Chaîne = SharedSecret + "||:||" + SessionKey + "||:||" + MAC1 + MAC2 + MAC3 + MAC4
```

**Séparateur**: `||:||` (2 barres, 2 points, 2 barres)

**Exemple**:
```
Dherbomez||:||1766334167.23676||:||QRP7KvBJmXyT3sLzMGH9TaNcLpR2fWeqZXY1bUvKcDf8RmNoPLD4QsVeJh6YaTux
```

**Algorithme**:
1. SHA1 hash de la chaîne → résultat en hexadécimal (40 caractères)
2. Convertir le hex en Base64

**Paramètres de la commande**:
```json
{
  "method": "RemoteSetRoboTargetManagerMode",
  "params": {
    "UID": "nouveau-guid",
    "MACKey": "Dherbomez",
    "Hash": "base64-du-sha1-hex"
  },
  "id": 3
}
```

## 🎯 Formule 2: Commandes RoboTarget Standards

**Commandes concernées**: Toutes les commandes RoboTarget après activation du mode Manager :
- `RemoteRoboTargetGetSet`
- `RemoteRoboTargetGetBaseSequence`
- `RemoteRoboTargetAddSet`
- `RemoteRoboTargetAddTarget`
- `RemoteRoboTargetAddShot`
- etc.

### Construction du MAC

```
Chaîne = SharedSecret + "|||" + SessionKey + "|||" + ID + "|||" + UID
```

**Séparateur**: `|||` (TROIS barres verticales)

**Exemple**:
```
Dherbomez|||1766334167.23676|||2|||14a27ee3-43c1-4f01-9e7f-86a4e6ebb74e
```

**Algorithme**:
1. SHA1 hash de la chaîne → résultat binaire
2. Convertir directement le binaire en Base64 (28 caractères)

**⚠️ IMPORTANT**:
- **NE PAS** convertir en hexadécimal puis en Base64 (c'est seulement pour l'activation)
- Convertir **directement** le SHA1 binaire en Base64

**Paramètres de la commande**:
```json
{
  "method": "RemoteRoboTargetAddSet",
  "params": {
    "UID": "guid-de-la-commande",
    "Guid": "guid-du-set",
    "Name": "Nom du Set",
    "ProfileName": "Default.v2y",
    "IsDefault": 0,
    "Status": 0,
    "Note": "Note optionnelle",
    "MAC": "base64-du-sha1-binaire"
  },
  "id": 2
}
```

## 📊 Différences Clés

| Aspect | Activation Mode Manager | Commandes RoboTarget |
|--------|------------------------|---------------------|
| **Séparateur** | `\|\|:\|\|` (2 barres, 2 points, 2 barres) | `\|\|\|` (3 barres) |
| **Formule** | Secret + Sep + SessionKey + Sep + Words | Secret + Sep + SessionKey + Sep + ID + Sep + UID |
| **SHA1 → Base64** | Hex → Base64 | Direct binaire → Base64 |
| **Résultat** | Variable (54+ chars) | 28 caractères |
| **Paramètre** | `Hash` | `MAC` |

## ✅ Validation

Voyager confirmera le succès via un événement `RemoteActionResult`:

```json
{
  "Event": "RemoteActionResult",
  "UID": "guid-de-la-commande",
  "ActionResultInt": 4,
  "ParamRet": {
    "ret": "DONE"
  }
}
```

**IMPORTANT**: Le champ `ParamRet.ret` doit être **exactement** `"DONE"`. Toute autre valeur indique :
- `"MAC Error"` → MAC invalide
- Autre → Erreur de validation ou de privilèges

## 🧪 Test de la Formule

**Node.js (pour tester)**:
```javascript
const crypto = require('crypto');

// Pour les commandes RoboTarget
const sharedSecret = 'Dherbomez';
const sessionKey = '1766334167.23676';
const id = '2';
const uid = '14a27ee3-43c1-4f01-9e7f-86a4e6ebb74e';

const macString = sharedSecret + '|||' + sessionKey + '|||' + id + '|||' + uid;
const mac = crypto.createHash('sha1').update(macString).digest('base64');

console.log('MAC String:', macString);
console.log('MAC (Base64):', mac);
```

## 📝 Notes

1. **SessionKey** : Toujours utiliser la valeur exacte reçue dans l'événement `Version` (avec les décimales)
2. **ID** : L'ID séquentiel de la commande JSON-RPC
3. **UID** : Le GUID unique de la commande Voyager (différent pour chaque commande)
4. **Sensibilité à la casse** : Tous les paramètres sont sensibles à la casse
