# Rapport de Résolution MAC - RoboTarget Reserved API

**Date:** 22 décembre 2025
**Problème initial:** MAC Error sur toutes les commandes Reserved API
**Statut final:** ✅ Résolu pour les commandes de lecture, ❌ Investigation en cours pour l'écriture

---

## 🎉 Problème Résolu

### Formule MAC Correcte Identifiée

**Formule finale qui fonctionne:**
```
Format: SharedSecret||:||SessionKey||:||ID||:||UID
Conversion: SHA1 → Hexadécimal → Base64
Séparateur: ||:|| (UNIFORME sur les 3 positions)
```

### Erreur Initiale

**Ce qui ne fonctionnait PAS:**
```
Format: SharedSecret|| |SessionKey||  |ID|| |UID
Séparateurs: || | (1 espace), ||  | (2 espaces), || | (1 espace)
```

**Pourquoi c'était faux:**
- La documentation NDA mentionnait des séparateurs asymétriques
- Mais en réalité, TOUTES les commandes Reserved API utilisent le même séparateur que l'activation Manager Mode: `||:||`

---

## Tests Effectués

### ✅ Tests Réussis

1. **Open API (MD5)**
   ```bash
   curl http://localhost:3003/api/robotarget/test-open-api \
     -X POST \
     -H "Content-Type: application/json" \
     -d '{"uid":"test-001"}'
   ```
   **Résultat:** ✅ Liste complète des 17 targets retournée

2. **Reserved API - Lecture Sets**
   ```bash
   curl http://localhost:3003/api/robotarget/sets
   ```
   **Résultat:** ✅ 3 Sets retournés (Comets, Galaxy, Nebuleuse)

3. **Reserved API - Lecture Base Sequences**
   ```bash
   curl http://localhost:3003/api/robotarget/base-sequences
   ```
   **Résultat:** ✅ 2 Base Sequences retournées

### ❌ Test en Échec

4. **Reserved API - Création Set**
   ```bash
   curl http://localhost:3003/api/robotarget/sets \
     -X POST \
     -H "Content-Type: application/json" \
     -d '{
       "guid_set":"12345678-1234-1234-1234-123456789abc",
       "set_name":"Test Set",
       "profile_name":"2025-04-27_EEYE_TOA150_F1100_GM2000HPS_ASI6200mm_v10.v2y"
     }'
   ```
   **Résultat:** ❌ Timeout 30s - Aucune réponse de Voyager

---

## Analyse du Problème AddSet

### Ce qui a été envoyé
```json
{
  "method": "RemoteRoboTargetAddSet",
  "params": {
    "UID": "383b19b2-2749-41f9-902c-ad90b5342530",
    "Guid": "12345678-1234-1234-1234-123456789abc",
    "Name": "Test Set",
    "ProfileName": "2025-04-27_EEYE_TOA150_F1100_GM2000HPS_ASI6200mm_v10.v2y",
    "IsDefault": false,
    "Status": 0,
    "Note": "",
    "MAC": "NzA2ZWM3Zjk1MzljOTZiMmYyZDdlN2M4NjQ4Y2U5ODNkNmJkYjRlOQ=="
  },
  "id": 3
}
```

### Comportement de Voyager
- ❌ Aucune réponse (ni succès, ni erreur)
- ❌ Pas de RemoteActionResult
- ❌ Timeout après 30 secondes
- ✅ Le MAC est correct (même formule que GetSet qui fonctionne)

### Hypothèses

1. **Paramètre manquant**
   - Peut-être qu'un champ obligatoire n'est pas documenté
   - Vérifier l'exemple exact de la doc NDA ligne 1010

2. **Conflit de GUID**
   - Le GUID fourni existe peut-être déjà
   - Tester avec un GUID complètement unique

3. **Permission insuffisante**
   - Manager Mode activé ✅
   - Mais peut-être qu'il faut un niveau d'auth supérieur?

4. **Format de paramètre incorrect**
   - `IsDefault` en boolean vs int?
   - `Status` en int vs string?

---

## Code Modifié

### 1. auth.js - Formule MAC corrigée

**Avant:**
```javascript
const sep1 = '|| |';   // 1 espace
const sep2 = '||  |';  // 2 espaces
const sep3 = '|| |';   // 1 espace
const macString = sharedSecret + sep1 + sessionKeyStr + sep2 + jsonRpcIdStr + sep3 + commandUidStr;
const mac = crypto.createHash('sha1').update(macString).digest('base64');
```

**Après:**
```javascript
const separator = '||:||';  // UNIFORME
const macString = sharedSecret + separator + sessionKeyStr + separator + jsonRpcIdStr + separator + commandUidStr;

// SHA1 → Hex → Base64
const hexHash = crypto.createHash('sha1').update(macString).digest('hex');
const mac = Buffer.from(hexHash, 'utf8').toString('base64');
```

### 2. Logs de débogage ajoutés

```javascript
logger.info(`🔐 MAC generation for RoboTarget command:`);
logger.info(`   Separator: "${separator}" (uniform, like Manager Mode)`);
logger.info(`   MAC string: ${macString}`);
logger.info(`   SHA1 (hex): ${hexHash}`);
logger.info(`   MAC (Hex→Base64): ${mac}`);
```

---

## Prochaines Étapes Recommandées

### Investigation AddSet

1. **Vérifier l'exemple exact NDA**
   ```
   Ligne 1010 du document NDA:
   {"Guid":"...","Name":"Pippolo","ProfileName":"TestFlatNoMount.v2y","IsDefault":false,"Status":0,"Note":"","UID":"...","MAC":"..."}
   ```
   - Comparer EXACTEMENT avec notre requête
   - Vérifier les types (boolean vs int)
   - Vérifier l'ordre des clés

2. **Tester avec un Set minimal**
   ```json
   {
     "Guid": "nouveau-guid-unique",
     "Name": "Test",
     "ProfileName": "existant.v2y",
     "IsDefault": false,
     "Status": 0,
     "Note": ""
   }
   ```

3. **Activer logs Voyager**
   - Vérifier les logs côté serveur Voyager
   - Voir si la commande est reçue/rejetée

4. **Tester autres commandes d'écriture**
   - `RemoteRoboTargetUpdateSet` (modifier un Set existant)
   - `RemoteRoboTargetAddTarget` (créer une Target)
   - Voir si le problème est spécifique à AddSet

### Tests de Validation

1. **Tester UpdateSet** sur un Set existant (GUID connu)
2. **Tester RemoveSet** sur un Set de test
3. **Comparer le comportement** entre lecture (fonctionne) et écriture (timeout)

---

## Commandes Fonctionnelles

### Open API (MD5)

```javascript
// Formule
const macString = sharedSecret + uid;
const mac = md5(macString).toHex();

// Exemple
{
  "method": "RemoteOpenRoboTargetGetTargetList",
  "params": {
    "UID": "test-001",
    "MAC": "92b3304de90518d049012bd8d6580346"
  },
  "id": 100
}
```

### Reserved API (SHA1→Hex→Base64)

```javascript
// Formule
const separator = '||:||';
const macString = sharedSecret + separator + sessionKey + separator + id + separator + uid;
const hexHash = sha1(macString).toHex();
const mac = base64Encode(hexHash);

// Exemple - GetSet
{
  "method": "RemoteRoboTargetGetSet",
  "params": {
    "ProfileName": "",
    "RefGuidSet": "",
    "UID": "test-001",
    "MAC": "N2MwY2JkNjVkMzZkNDQzNjdkN2JjZWNhMDU5ZDMwMGEwNTAzZGYwYQ=="
  },
  "id": 2
}
```

---

## Fichiers de Test Créés

1. `voyager-proxy/test-mac-hex.js` - Démonstrateur des deux méthodes de conversion
2. `voyager-proxy/test-open-api.js` - Test MD5 Open API
3. `voyager-proxy/debug-separators.js` - Analyse byte-par-byte des séparateurs
4. `docs/robotarget/api-comparison.md` - Comparaison complète des deux APIs
5. `docs/robotarget/mac-resolution-report.md` - Ce document

---

## Leçons Apprises

### ❌ Pièges à Éviter

1. **Ne pas se fier aveuglément à la doc**
   - La doc NDA mentionnait `|| |` / `||  |` / `|| |`
   - En réalité c'est `||:||` partout

2. **Tester la conversion byte-par-byte**
   - `7c7c207c` vs `7c7c3a7c7c` fait toute la différence

3. **SHA1 → Hex → Base64 n'est PAS la même chose que SHA1 → Base64**
   - Hex intermediate step obligatoire

### ✅ Méthodes qui ont Fonctionné

1. **Tests incrémentaux**
   - Open API d'abord (simple)
   - Puis Reserved API lecture
   - Puis Reserved API écriture

2. **Logging exhaustif**
   - Voir EXACTEMENT ce qui est envoyé
   - Comparer byte-par-byte

3. **Script de test autonomes**
   - `debug-separators.js` a permis de confirmer les bytes exacts

---

## Conclusion

**✅ Résolution:** La formule MAC Reserved API est maintenant correcte et fonctionne pour toutes les commandes de **lecture**.

**⚠️ En cours:** Les commandes d'**écriture** (AddSet, UpdateSet, etc.) nécessitent une investigation supplémentaire pour identifier pourquoi Voyager ne répond pas.

**📝 Recommandation:** Contacter le support PrimaLuce Lab avec les logs exacts de la tentative AddSet pour confirmer le format attendu des paramètres.

---

**Dernière mise à jour:** 22 décembre 2025 - 16:10 UTC
**Testé avec:** Voyager 2.x, Node.js v25.2.1
**Environnement:** Windows 10, localhost
