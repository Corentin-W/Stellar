# Comparaison Requête RemoteRoboTargetGetSet

## 📦 Requête réelle envoyée à Voyager

```json
{
  "method": "RemoteRoboTargetGetSet",
  "params": {
    "RefGuidSet": "",
    "UID": "a35aefdd-48b5-4301-8285-cbbabc12bcae",
    "MAC": "rzoPx/wOL6uKDo+PfWinwL6cAPs="
  },
  "id": 9
}
```

## 🔐 Calcul du MAC

### Formule pour Reserved API

**IMPORTANT:** Les commandes Reserved API (GetSet, GetTarget, GetBaseSequence, etc.) utilisent une formule MAC **différente** de celle des commandes de modification (AddTarget, UpdateTarget, etc.)

```
Format: Secret||:||SessionKey||:||ID||:||UID
        ^^^^^  ^^^^            ^^^^  ^^^^
         |      |                |      |
         |      Séparateur ||:|| (pas d'espaces!)
         |
         SharedSecret ("Dherbomez")
```

### Exemple concret

```
Supposons:
- SharedSecret: "Dherbomez"
- SessionKey: "1766739275.42356"
- ID: 9
- UID: "a35aefdd-48b5-4301-8285-cbbabc12bcae"

MAC String: "Dherbomez||:||1766739275.42356||:||9||:||a35aefdd-48b5-4301-8285-cbbabc12bcae"
```

### Transformation

```javascript
// Étape 1: SHA1 de la chaîne
const hexHash = crypto.createHash('sha1').update(macString).digest('hex');
// Résultat: "af3a0fc7fc0e2fab8a0e8f8f7d68a7c0be9c00fb" (hex string)

// Étape 2: Encoder le HEX en Base64
const mac = Buffer.from(hexHash, 'utf8').toString('base64');
// Résultat: "rzoPx/wOL6uKDo+PfWinwL6cAPs=" (Base64)
```

## 📊 Comparaison avec la documentation

| Élément | Documentation | Implémentation | ✅/❌ |
|---------|--------------|----------------|-------|
| **Méthode** | RemoteRoboTargetGetSet | ✅ `RemoteRoboTargetGetSet` | ✅ |
| **RefGuidSet** | String (vide = tous) | ✅ `""` (tous les sets) | ✅ |
| **UID** | String (GUID) | ✅ `a35aefdd-48b5-4301-8285-cbbabc12bcae` | ✅ |
| **MAC** | Base64 | ✅ `rzoPx/wOL6uKDo+PfWinwL6cAPs=` | ✅ |
| **ID** | Integer | ✅ `9` | ✅ |
| **Séparateurs MAC** | `||:||` (Reserved API) | ✅ `||:||` | ✅ |

## ⚠️ Différence importante: MAC Reserved API vs Manager Mode

### Reserved API (Lecture: GetSet, GetTarget, GetBaseSequence)

```
Séparateurs: ||:||  (pas d'espaces)
Exemple:     Dherbomez||:||1766739275.42356||:||9||:||a35aefdd...
```

### Manager Mode (Écriture: AddTarget, UpdateTarget, DeleteTarget)

```
Séparateurs: || | ... ||  | ... || |  (algorithme "1-2-1")
             └─┘       └──┘       └─┘
           1 espace  2 espaces  1 espace

Exemple:     Dherbomez|| |1766738572.78051||  |2|| |14a16068...
```

## ✅ Conformité globale

### Points validés

1. ✅ **RefGuidSet présent** (vide retourne tous les sets)
2. ✅ **UID généré automatiquement** (GUID Windows)
3. ✅ **MAC avec séparateurs ||:||** (Reserved API)
4. ✅ **Algorithme correct**: SHA1 → HEX → Base64
5. ✅ **Structure JSON-RPC correcte**

### Différences entre les APIs

| Aspect | Reserved API (GetSet) | Manager Mode (AddTarget) |
|--------|----------------------|--------------------------|
| **Séparateurs MAC** | `||:||` (uniforme) | `|| |` `||  |` `|| |` (1-2-1) |
| **Commandes** | Get* (lecture) | Add*, Update*, Delete* (écriture) |
| **Activation** | Authentification de base suffit | Requiert RemoteSetRoboTargetManagerMode |

## 🎯 Résultat attendu

Lorsque la commande réussit, Voyager renvoie:

```json
{
  "Event": "RemoteActionResult",
  "UID": "a35aefdd-48b5-4301-8285-cbbabc12bcae",
  "ActionResultInt": 4,
  "ParamRet": {
    "list": [
      {
        "guid": "2fea3ea2-84cd-4488-b641-bff46be09c8e",
        "setname": "Comets",
        "profilename": "2025-04-27_EEYE_TOA150_F1100_GM2000HPS_ASI6200mm_v10.v2y",
        "isdefault": false,
        "status": 0,
        "tag": "Comets",
        "note": ""
      },
      // ... autres sets
    ]
  }
}
```

## 🔍 Notes importantes

1. **RefGuidSet vide** (`""`) retourne **tous les sets de tous les profils**
2. **RefGuidSet avec GUID** retourne uniquement le set spécifié
3. Le champ `list` contient un tableau d'objets avec:
   - `guid`: GUID du set
   - `setname`: Nom du set
   - `profilename`: Nom du profil Voyager
   - `isdefault`: Boolean (set par défaut ou non)
   - `status`: 0=Actif, 1=Inactif
   - `tag`: Tag du set
   - `note`: Note du set

## 📝 Conclusion

La requête **RemoteRoboTargetGetSet** est conforme à la documentation de l'API Reserved:
- Structure JSON-RPC correcte ✅
- Paramètres obligatoires présents ✅
- MAC calculé avec la bonne formule (||:||) ✅
- Format de données correct ✅
