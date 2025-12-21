STARKEEPER.IT
# Voyager RoboTarget Reserved API - Commands Reference (Part 5)
Voyager Starkeeper Software RoboTarget NDA Protocol Ver.1.0

## 6. RoboTarget Commands (Monitoring and Global Control)

### kk) RemoteRoboTargetGetSessionListByTarget
**Method:** `RemoteRoboTargetGetSessionListByTarget`
**Description:** Return list of RoboTarget Session done for the Target. [1]
**Params:**
* **UID** (String): Unique identifier of the Action. [1]
* **RefGuidTarget** (String): UID of Target. [2]
* **MAC**: Concatenated string (Secret + SessionKey + ID + UID) -> SHA1 -> Base64. [2]
**Remote Action Result Parameters (ParamRet Object):**
* **List** (Array): Array of Session Objects including `guid`, `datetimestart`, `datetimeend`, `result` (0=UNDEF, 1=OK, 2=ABORTED, 3=FINISHED_ERROR, 4=TIMEOUT), and `sessionexittext`. [2-4]

---

### ll) RemoteRoboTargetGetSessionContainerCountByTarget
**Method:** `RemoteRoboTargetGetSessionContainerCountByTarget`
**Description:** Return summary data about sessions done for a specific Target. [5]
**Params:**
* **RefGuidTarget** (String): UID of Target. [5]
* **MAC**: Concatenated string (Secret + SessionKey + ID + UID) -> SHA1 -> Base64. [5]
**Remote Action Result Parameters (ParamRet Object):**
* **Duration** (Integer): Total duration of all sessions in seconds. [6]
* **Progress** (DateTime/Numeric): Percentage of progress for the target. [6]
* **ShotDoneCount** (String): Count of shots done and not removed. [7]
* **ShotRequested** (String): Total shots requested for this target. [7]

---

### mm) RemoteRoboTargetGetRuns
**Method:** `RemoteRoboTargetGetRuns`
**Description:** Return list of Runs done for a Profile (all or last X days). [8]
**Params:**
* **ProfileName** (String): Name of profile file. [8]
* **ListDays** (Array): Array of integers (e.g., 0 for all, 30 for last 30 days). [8]

---

### nn) RemoteRoboTargetGetShotJpg
**Method:** `RemoteRoboTargetGetShotJpg`
**Description:** Return the base64 Jpeg image if in cache or available on disk. [9]
**Params:**
* **RefGuidShotDone** (String): UID of the completed shot (leave empty if searching by filename). [9]
* **FITFileName** (String): FIT file name without path (leave empty if searching by UID). [10]
**Remote Action Result Parameters (ParamRet Object):**
* **Base64Data** (String): The compressed JPG image data in Base64. [11]
* **Metadata**: Includes `HFD`, `StarIndex`, `PixelDimX`, `PixelDimY`, `Min`, `Max`, and `Mean` ADU values. [11]

---

### oo) RemoteRoboTargetAbort
**Method:** `RemoteRoboTargetAbort`
**Description:** Abort Remote RoboTarget Sequence based on Target UID, Set UID, or Tags. [12]
**Important:** If all parameters are empty strings (""), the entire RoboTarget Action will be aborted. [12]
**Params:**
* **RefGuidTarget** (String): UID of the target to abort. [13]
* **RegGuidSet** (String): UID of the set to abort. [13]
* **RegGuidTargetTag / RegGuidSetTag** (String): Tags to identify sequences to abort. [13]
**Result:** Integer(0). ParamRet: `{"ret":"DONE"}`. [14, 15]
Analyse pour votre projet Laravel :
1. Suivi de progression : La méthode ll) RemoteRoboTargetGetSessionContainerCountByTarget est la plus efficace pour afficher une barre de progression dans votre interface web, car elle calcule directement le ratio entre les clichés effectués (ShotDoneCount) et ceux demandés (ShotRequested).
2. Visualisation : Contrairement aux commandes standards qui envoient des flux FIT lourds, la méthode nn) RemoteRoboTargetGetShotJpg est optimisée pour le web, fournissant une image JPG déjà compressée en base64, accompagnée de statistiques comme l'index d'étoiles et le HFD pour juger de la qualité à distance.
3. Sécurité d'arrêt : La commande oo) RemoteRoboTargetAbort est l'interrupteur d'urgence. Elle permet une granularité totale : vous pouvez arrêter une seule cible récalcitrante sans stopper tout le programme de la nuit.
