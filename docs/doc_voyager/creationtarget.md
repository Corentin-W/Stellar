Voici la documentation technique exhaustive pour la création d'une cible (**Target**) via l'**API Réservée (NDA)** de Voyager RoboTarget, incluant l'intégralité des paramètres et protocoles de sécurité décrits dans la documentation officielle.

---

# Documentation Technique : RemoteRoboTargetAddTarget

Cette commande permet d'ajouter une cible complète à un Set (groupe) spécifique dans la base de données RoboTarget.

## 1. Protocole de Connexion et Session
*   **Port TCP** : `5950` par défaut.
*   **Événement Version** : Dès la connexion, le serveur envoie un objet `Version`. Vous devez impérativement récupérer le champ **`Timestamp`** (ex: `1652231344.88438`). Il servira de **`SessionKey`**.
*   **Maintien de session (Heartbeat)** : Vous devez envoyer un événement `Polling` toutes les **5 secondes**. Une inactivité de 15 secondes entraîne la fermeture de la socket.

## 2. Authentification et Élévation de Privilèges
Avant toute commande `AddTarget`, deux étapes sont obligatoires :

### A. Authentification de base (`AuthenticateUserBase`)
Envoyez la chaîne `username:password` encodée en **Base64**.
*   *Exemple pour admin:6383* : `YWRtaW46NjM4Mw==`.

### B. Activation du Mode Manager (`RemoteSetRoboTargetManagerMode`)
Cette commande débloque les fonctions d'écriture.
*   **MAC Key** : `Dherbomez` (votre Custom Key).
*   **Calcul du Hash** : `SharedSecret + "||:||" + SessionKey + "||:||" + MAC_Words(1+2+3+4)`.
*   **Algorithme** : SHA1 puis conversion en **Base64**.

## 3. Paramètres de la commande `RemoteRoboTargetAddTarget`
La requête doit être envoyée sur une **seule ligne** terminée par **CR LF** (`\r\n`).

### Paramètres d'identification et de structure
| Paramètre | Type | Description |
| :--- | :--- | :--- |
| **`UID`** | String | Identifiant unique de l'action (GUID Windows recommandé). |
| **`GuidTarget`** | String | Nouvel UID unique à associer à la cible. |
| **`RefGuidSet`** | String | UID du Set (groupe) auquel appartient la cible. |
| **`RefGuidBaseSequence`** | String | UID de la séquence de base utilisée comme modèle. |
| **`TargetName`** | String | Nom de la cible. |
| **`Tag`** | String | Tag de la cible. |
| **`DateCreation`** | Datetime | Date de création (format Epoch) pour l'ordre du scheduler. |

### Paramètres de position et statut
| Paramètre | Type | Description |
| :--- | :--- | :--- |
| **`RAJ2000`** | Numeric | Coordonnée RA J2000 exprimée en **Heures**. |
| **`DECJ2000`** | Numeric | Coordonnée DEC J2000 exprimée en **Degrés**. |
| **`PA`** | Numeric | Position Angle en degrés (Sky PA ou Mechanical PA selon la séquence). |
| **`Status`** | Integer | 0 = Activé, 1 = Désactivé. |
| **`StatusOp`** | Integer | État opérationnel : -1 (Inconnu), 0 (Idle), 1 (Running), 2 (Finished), 3 (Éphém. non calculées), 4 (Expiré). |
| **`Priority`** | Integer | 0 (Très basse) à 4 (Première). |
| **`Note`** | String | Note texte libre. |

### Paramètres d'acquisition (Overrides)
| Paramètre | Type | Description |
| :--- | :--- | :--- |
| **`IsRepeat`** | Boolean | `true` pour répéter le groupe de shots. |
| **`Repeat`** | Integer | Nombre de répétitions. |
| **`IsFinishActualExposure`** | Boolean | `true` pour finir l'exposition en cours si le temps est écoulé. |
| **`IsCoolSetPoint`** | Boolean | Override de la température de refroidissement. |
| **`CoolSetPoint`** | Integer | Température cible du capteur. |
| **`IsWaitShot`** | Boolean | Override du temps d'attente entre shots. |
| **`WaitShot`** | Integer | Temps d'attente en secondes. |
| **`IsGuideTime`** | Boolean | Override du temps d'exposition du guidage. |
| **`GuideTime`** | Numeric | Temps d'exposition de guidage en secondes. |
| **`IsOffsetRF`** | Boolean | `true` pour activer un offset de focus (RoboFire). |
| **`OffsetRF`** | Integer | Nombre de pas à ajouter au focus final. |

### Paramètres des Contraintes (Constraints)
*   **`C_ID`** : UID de l'ensemble de contraintes.
*   **`C_Mask`** : Chaîne de caractères définissant les contraintes actives.
    *   *A=PA, B=Alt min, C=SQM, D/E=HA, F/G=Date start/end, H/J=Time start/end, K=Moon Down, L/M=Moon Phase, N=Moon Dist, O=HFD Max, P/T=Max Time, U=OneShot, V=Interval*.

| Paramètre | Type | Description |
| :--- | :--- | :--- |
| **`C_AltMin`** | Numeric | Altitude minimale en degrés. |
| **`C_SqmMin`** | Numeric | SQM minimal requis. |
| **`C_HAStart` / `C_HAEnd`** | Numeric | Intervalle d'angle horaire (HA) autorisé. |
| **`C_MoonDown`** | Boolean | `true` pour exiger que la lune soit couchée. |
| **`C_MoonPhaseMin/Max`**| Numeric | Phase lunaire autorisée (0-100). |
| **`C_MoonDistanceDegree`**| Numeric | Distance minimale de la lune en degrés. |
| **`C_MoonDistanceLorentzian`**| Integer | Profil d'évitement : 0 (Large), 1 (Étroit), 2 (Libre). |
| **`C_HFDMeanLimit`** | Numeric | HFD maximum pour continuer la session. |
| **`C_MaxTimeForDay`** | Numeric | Minutes max autorisées par 24h. |
| **`C_AirMassMin/Max`** | Numeric | Masse d'air autorisée. |

### Paramètres Dynamiques et Objets (Comètes/Astéroïdes)
| Paramètre | Type | Description |
| :--- | :--- | :--- |
| **`TType`** | Integer | **OBLIGATOIRE.** 0=DSO, 1=Comète, 2=Astéroïde, 3=Planète, 4=DynaSearch. |
| **`TKey`** | String | Clé de recherche pour objets dynamiques (champ CTNAME). |
| **`TName`** | String | Nom de désignation de l'objet dynamique. |
| **`IsDynamicPointingOverride`**| Boolean | `true` pour outrepasser le mode de pointage dynamique de la séquence. |
| **`DynamicPointingOverride`**| Integer | 0=Début de séquence, 1=Chaque GOTO, 2=Chaque X secondes. |
| **`DynEachX_Seconds`** | Integer | Intervalle en secondes pour le recalcul. |
| **`DynEachX_Realign`** | Boolean | `true` pour réalaligner dès que possible après l'intervalle. |

## 4. Sécurité MAC (Algorithme "1-2-1")
Le paramètre **`MAC`** est crucial. Sa construction est rigide sur les espaces :
1.  **Chaîne source** : `Secret|| |SessionKey||  |ID|| |UID`
    *   `|| |` : 1 espace après le 1er bloc de pipes.
    *   `||  |` : **2 espaces** après le 2ème bloc.
    *   `|| |` : 1 espace après le 3ème bloc.
    *   `ID` : L'ID JSON-RPC (entier).
    *   `UID` : L'UID de la commande (string).
2.  **Transformation** : SHA1 → Résultat hexadécimal (minuscules) → Encodage **Base64**.

## 5. Validation du résultat
Le succès n'est confirmé que si :
1.  La réponse JSON-RPC immédiate renvoie `result: 0`.
2.  L'événement asynchrone **`RemoteActionResult`** reçu plus tard contient `ParamRet: {"ret": "DONE"}`. Une valeur différente de `DONE` constitue une erreur.