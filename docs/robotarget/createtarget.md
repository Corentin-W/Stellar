Voici une documentation technique complète au format Markdown pour la création d'une cible via l'API **RemoteRoboTargetAddTarget**, basée sur la section **6.u** du protocole NDA Voyager.

---

# Spécification Technique : RemoteRoboTargetAddTarget

## 1. Description de la méthode
La commande **`RemoteRoboTargetAddTarget`** permet d'injecter une nouvelle cible (Target) dans un ensemble (Set) spécifique de la base de données RoboTarget de Voyager.

*   **Type :** Appel de procédure distante (JSON-RPC 2.0).
*   **Mode requis :** Le client doit préalablement être authentifié et déclaré en mode `RoboTarget Manager` via la commande `RemoteSetRoboTargetManagerMode`.
*   **Licence :** Requiert une licence Voyager Advanced ou Full avec l'extension NDA activée.

## 2. Paramètres de l'objet `params`
L'objet `params` est extrêmement riche. Tous les noms de paramètres sont **sensibles à la casse**.

### A. Identifiants et Configuration de base
| Paramètre | Type | Description |
| :--- | :--- | :--- |
| **`UID`** | String | Identifiant unique de la commande (GUID recommandé). |
| **`GuidTarget`** | String | Nouvel UID unique généré par le client pour identifier cette cible. |
| **`RefGuidSet`** | String | UID de l'ensemble (Set) auquel la cible doit appartenir. |
| ****`RefGuidBaseSequence`**** | String | UID de la séquence de base (Template) servant de modèle à l'acquisition. |
| **`TargetName`** | String | Nom de l'objet céleste. |
| **`Tag`** | String | Étiquette personnalisée pour le tri. |
| **`Status`** | Integer | **0** = Activée, **1** = Désactivée (ignorée par le planificateur). |
| **`Priority`** | Integer | **0**=Très Basse, **1**=Basse, **2**=Normale, **3**=Haute, **4**=Première. |

### B. Coordonnées et Rotation
| Paramètre | Type | Description |
| :--- | :--- | :--- |
| **`RAJ2000`** | Numeric | Ascension Droite J2000 exprimée en **heures** (ex: 0.1766). |
| **`DECJ2000`** | Numeric | Déclinaison J2000 exprimée en **degrés** (ex: 58.766). |
| **`PA`** | Numeric | Position Angle en degrés (Position mécanique ou Sky PA selon la séquence). |

### C. Paramètres d'Acquisition (Overrides)
Ces paramètres permettent de modifier les réglages par défaut de la séquence de base :
*   **`IsRepeat`** (Boolean) : `true` pour répéter le groupe de poses défini.
*   **`Repeat`** (Integer) : Nombre de répétitions.
*   **`IsCoolSetPoint`** (Boolean) : Force une température de refroidissement spécifique.
*   **`CoolSetPoint`** (Integer) : Température cible en degrés Celsius.
*   **`IsWaitShot`** / **`WaitShot`** : Délai d'attente entre les poses (en secondes).
*   **`IsGuideTime`** / **`GuideTime`** : Temps d'exposition pour le guidage.

### D. Le Masque de Contraintes (`C_Mask`)
Le champ **`C_Mask`** est une chaîne de caractères où chaque lettre active une contrainte spécifique :
*   **A** : Position Angle | **B** : Altitude Min | **C** : SQM Min | **D/E** : HA Start/End
*   **F/G** : Date Start/End | **H/J** : Heure Start/End | **K** : Lune Couchée (Moon Down)
*   **L/M** : Phase Lunaire Min/Max | **N** : Distance Lunaire | **O** : Limite HFD
*   **Q/R** : Air Mass Min/Max | **T** : Durée Max Séquence | **U** : One Shot

Chaque contrainte activée dans le masque doit avoir sa valeur correspondante définie (ex: `C_AltMin`, `C_MoonPhaseMax`, etc.).

## 3. Sécurité et MAC
Le champ **`MAC`** est obligatoire pour valider l'intégrité de la commande.

*   **Formule :** `SHA1_Binaire(SharedSecret + "|||" + SessionKey + "|||" + ID_JSON + "|||" + UID_Commande)`.
*   **Séparateur :** `|||` (TROIS barres verticales) entre tous les éléments.
*   **Encodage :** Le résultat SHA1 binaire doit être converti en **Base64** (28 caractères).
*   **SessionKey :** Doit être la chaîne exacte reçue dans l'événement `Version` à la connexion.

**Exemple de calcul :**
```
Chaîne: "Dherbomez|||1766334167.23676|||2|||14a27ee3-43c1-4f01-9e7f-86a4e6ebb74e"
SHA1 → Base64 → MAC final
```

## 4. Exemple de Requête (TX)
```json
{
  "method": "RemoteRoboTargetAddTarget",
  "params": {
    "GuidTarget": "76878ebc-55d0-4ffd-8298-a726d1625c2d",
    "RefGuidSet": "5482d20e-2304-41d1-8d2b-32adc2c314bc",
    "RefGuidBaseSequence": "90ae5721-a248-4159-ad74-56e13cf26141",
    "TargetName": "vdB 1",
    "RAJ2000": 0.176666666666667,
    "DECJ2000": 58.7666666666667,
    "PA": 120,
    "Status": 0,
    "Priority": 2,
    "C_Mask": "ABK",
    "C_AltMin": 30,
    "C_MoonDown": true,
    "UID": "8766074d-f415-4cad-826b-b0e2a5ae40b7",
    "MAC": "yW3Y8aRExk3Yf2qA/JTAMruu4Dc="
  },
  "id": 12
}
```
*[Note : Le MAC fourni est illustratif et doit être recalculé]*.

## 5. Flux de Réponses (RX)
1.  **Réponse immédiate :** Voyager confirme la réception de la commande JSON-RPC.
    *   `{"jsonrpc": "2.0", "result": 0, "id": 12}`.
2.  **Événement final :** Voyager envoie l'événement `RemoteActionResult` une fois l'insertion terminée.
    *   Vérifiez que **`ParamRet.ret`** est égal à **`"DONE"`**. Toute autre valeur indique une erreur (ex: Set inexistant, MAC invalide).

---

