# Comment récupérer le Base Sequence GUID dans Voyager

Le champ **RefGuidBaseSequence** est **obligatoire** pour créer une target RoboTarget. Il doit référencer une séquence template existante dans Voyager.

## Étapes pour obtenir le GUID

### Option 1: Via l'interface Voyager (Recommandé)

1. **Ouvrez Voyager** sur votre serveur (185.228.120.120)

2. **Accédez à RoboTarget Manager**:
   - Menu principal → RoboTarget
   - Ou utilisez le raccourci clavier approprié

3. **Créez ou sélectionnez une séquence de base**:
   - Dans l'onglet "Sequences" ou "Base Sequences"
   - Créez une nouvelle séquence avec vos paramètres par défaut (exposition, filtres, binning, etc.)
   - Ou sélectionnez une séquence existante

4. **Récupérez le GUID**:
   - Clic droit sur la séquence → Propriétés
   - Ou consultez les logs/fichiers de configuration de Voyager
   - Le GUID sera au format: `xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx`

### Option 2: Via l'API Voyager

Utilisez la commande **RemoteRoboTargetGetBaseSequence** pour lister toutes les séquences disponibles:

```json
{
  "method": "RemoteRoboTargetGetBaseSequence",
  "params": {
    "UID": "nouveau-guid-unique",
    "RefGuidBaseSequence": "",
    "MAC": "votre-mac-calculé"
  },
  "id": 1
}
```

La réponse contiendra la liste des séquences avec leurs GUIDs.

### Option 3: Fichiers de configuration Voyager

Les séquences sont stockées dans les fichiers de configuration Voyager (généralement en XML):
- Chemin: `C:\ProgramData\StarKeeper\Voyager\` (Windows)
- Fichier: `RoboTarget.config` ou similaire
- Recherchez les balises `<BaseSequence>` avec l'attribut `Guid="..."`

## Exemple de GUID valide

```
90ae5721-a248-4159-ad74-56e13cf26141
```

## Configuration dans Stellar

Une fois que vous avez le GUID:

1. **Via l'interface de test** (`https://stellar.test/test/robotarget`):
   - Sélectionnez un preset (M42, M31, etc.)
   - Collez le GUID dans le champ "Base Sequence GUID"
   - Cliquez sur "Envoyer à Voyager"

2. **En dur dans le code** (si vous utilisez toujours la même séquence):
   - Modifiez `app/Http/Controllers/RoboTargetTestController.php` ligne 265
   - Remplacez `'00000000-0000-0000-0000-000000000000'` par votre GUID réel

## Création d'une séquence de base dans Voyager

Si vous n'avez pas encore de séquence de base:

1. Ouvrez RoboTarget Manager dans Voyager
2. Créez une nouvelle "Base Sequence" avec:
   - Nom: "Default Test Sequence"
   - Paramètres caméra (cooling, gain, offset)
   - Configuration de guidage
   - Paramètres de dithering
3. Sauvegardez et récupérez son GUID

## Vérification

Pour vérifier que le GUID est correct:
- La création de target ne doit **pas** échouer avec une erreur MAC
- Voyager doit retourner `ParamRet.ret = "DONE"`
- Consultez les logs du proxy Node.js pour voir la réponse complète

## Troubleshooting

### Erreur "Invalid RefGuidBaseSequence"
- Le GUID n'existe pas dans Voyager
- Vérifiez l'orthographe du GUID
- Assurez-vous que la séquence n'a pas été supprimée

### Erreur MAC
- Si l'erreur persiste même avec un GUID valide
- Vérifiez que le RoboTarget Manager Mode est actif
- Vérifiez les logs d'authentification du proxy

## Notes importantes

⚠️ **Le GUID ne peut PAS être vide** selon la documentation NDA Section 6.u

⚠️ **Le GUID doit être au format UUID valide** (8-4-4-4-12 caractères hexadécimaux)

⚠️ **La séquence doit exister dans Voyager** avant de créer la target
