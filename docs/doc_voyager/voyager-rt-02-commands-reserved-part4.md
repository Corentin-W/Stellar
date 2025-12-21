STARKEEPER.IT
# Voyager RoboTarget Reserved API - Commands Reference (Part 4)
Voyager Starkeeper Software RoboTarget NDA Protocol Ver.1.0

## 6. RoboTarget Commands (Shots and Management)

### x) RemoteRoboTargetGetConfigDataShot
**Method:** `RemoteRoboTargetGetConfigDataShot`
**Description:** Return information about Shot configuration usable for the profile in parameter.
**Params:**
* **UID** (String): Unique identifier of the Action.
* **ProfileName** (String): Name of profile file with extension .v2y. Empty to retrieve data for all profile available.
* **MAC**: Concatenated string (Secret + SessionKey + ID + UID) -> SHA1 -> Base64.
**Remote Action Result Parameters (ParamRet Object):**
* **List** (Array): Array of Shot configuration objects including: `sensortype` (0=Mono, 1=Color, 2=DSLR), `iscmos` (Boolean), `filters` (List of names and magnitudes), `readoutmode`, and `speed`.

---

### y) RemoteRoboTargetAddShot
**Method:** `RemoteRoboTargetAddShot`
**Description:** Add a Shot configuration (Slot) to a Target.
**Params:**
* **GuidShot** (String): New UID to associate to the Shot.
* **RefGuidTarget** (String): UID of Target.
* **FilterIndex** (Integer): Index of filter (0 for default).
* **Num** (Integer): How many shots for this slot.
* **Bin** (Integer): Binning to use.
* **Type** (Integer): Image type: 0=Light, 1=Bias, 2=Dark, 3=Flat.
* **Gain / Offset** (Integer): Dedicated to CMOS (use 0 otherwise).
* **Exposure** (Numeric): Time exposure in seconds.
* **Order** (Integer): Execution order in the sequence.
* **Enabled** (Boolean): True if the Slot is enabled.

### z) RemoteRoboTargetUpdateShot
**Method:** `RemoteRoboTargetUpdateShot`
**Description:** Update a Shot (Slot) configuration. Use `RefGuidShot` to identify the object.

### aa) RemoteRoboTargetRemoveShot
**Method:** `RemoteRoboTargetRemoveShot`
**Description:** Remove a Shot (Slot) already stored. All data referring to this will be removed.

---

### bb) RemoteRoboTargetMoveShot
**Method:** `RemoteRoboTargetMoveShot`
**Description:** Change order of a Shot (Slot).
**Params:**
* **RefGuidShot** (String): UID of Shot.
* **MoveType** (Integer): 0=First, 1=Up, 2=Down, 3=Last.

### ee) RemoteRoboTargetMoveCopyTarget
**Method:** `RemoteRoboTargetMoveCopyTarget`
**Description:** Move or Copy Target to another Set/Profile.
**Params:**
* **RefGuidTarget** (String): UID of actual Target.
* **RefGuidTargetNew** (String): New UID of destination Target.
* **RefGuidSetDestination** (String): UID of destination Set.
* **IsShot** (Boolean): True to copy Shot configuration with the Target.
* **IsCut** (Boolean): True to move, False to copy.

### ff) RemoteRoboTargetMoveSet
**Method:** `RemoteRoboTargetMoveSet`
**Description:** Move Set to another profile. Attention: targets will become orphan and must be fixed.
**Params:**
* **DestinationProfile** (String): File name with extension .v2y.
* **IsSequenceBlank / IsSequenceDefault / IsSequenceFixed** (Boolean): Flags to handle base sequence replacement.

### gg) RemoteRoboTargetCopyShot
**Method:** `RemoteRoboTargetCopyShot`
**Description:** Copy Shot (Slot) configuration in another Target.

### hh) RemoteRoboTargetCopyTargetShot
**Method:** `RemoteRoboTargetCopyTargetShot`
**Description:** Copy all Shot (Slot) configuration of one Target to another Target.
Insights pour votre IA Laravel :
1. Gestion des profils : La méthode RemoteRoboTargetGetConfigDataShot est indispensable avant d'ajouter un cliché (AddShot), car elle permet de récupérer les index réels des filtres et des modes de lecture propres à un profil matériel spécifique.
2. Validation impérative : Comme pour les cibles, chaque commande de modification de cliché doit être validée en vérifiant que le champ ret dans ParamRet est égal à "DONE".
3. Hachage MAC : Notez que le calcul du MAC pour ces commandes utilise toujours l'ID de la commande JSON-RPC et l'UID de l'action Voyager, concaténés avec le secret et la clé de session
