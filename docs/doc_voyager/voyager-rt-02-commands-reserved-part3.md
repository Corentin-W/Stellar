STARKEEPER.IT
# Voyager RoboTarget Reserved API - Commands Reference (Part 3)
Voyager Starkeeper Software RoboTarget NDA Protocol Ver.1.0

## 6. RoboTarget Commands (Modification Methods)

### o) RemoteRoboTargetAddBaseSequence
**Method:** `RemoteRoboTargetAddBaseSequence`
**Description:** Add a Base Sequence (the sequence must exists in the default folder for Sequence Config in Voyager Folders) to a Profile.
**Params:**
* **UID** (String): Unique identifier of the Action.
* **Guid** (String): New UID to associate to the Base Sequence.
* **Name** (String): Base Sequence Name (use the same Sequence filename with extension .s2q).
* **FileName** (String): Path and file name with extension of the Sequence.
* **ProfileName** (String): Profile name within the sequence is associated (with extension .v2y).
* **IsDefault** (Boolean): True if is the default Base sequence for the Profile.
* **Status** (Integer): 0=Enabled, 1=Disabled.
* **Note** (String): Text note associated to the Base Sequence.
* **MAC**: Concatenated string (Secret + SessionKey + ID + UID) -> SHA1 -> Base64.
**Result:** Integer(0). ParamRet: `{"ret":"DONE"}`.

### p) RemoteRoboTargetUpdateBaseSequence
**Method:** `RemoteRoboTargetUpdateBaseSequence`
**Description:** Update a Base Sequence already stored.
**Params:**
* **RefGuidBaseSequence** (String): UID of Base Sequence.
* **IsDefault** (Boolean), **Status** (Integer), **Note** (String), **UID**, **MAC**.

### q) RemoteRoboTargetRemoveBaseSequence
**Method:** `RemoteRoboTargetRemoveBaseSequence`
**Description:** Remove a Base Sequence already stored. Attention: all the Target referring to this Base Sequence will become Orphan and cannot be used for scheduling until fixed.

---

### r) RemoteRoboTargetAddSet
**Method:** `RemoteRoboTargetAddSet`
**Description:** Add a Set to a Profile.
**Params:**
* **Guid** (String): New UID to associate to the Set.
* **Name** (String): Set Name.
* **ProfileName** (String): Profile name with extension .v2y.
* **IsDefault** (Boolean), **Tag** (String), **Status** (Integer), **Note** (String).

### s) RemoteRoboTargetUpdateSet
**Method:** `RemoteRoboTargetUpdateSet`
**Description:** Update a Set.
**Params:**
* **RefGuidSet** (String): UID of the Set to Update.
* **Name**, **Status**, **Tag**, **Note**, **UID**, **MAC**.

---

### u) RemoteRoboTargetAddTarget
**Method:** `RemoteRoboTargetAddTarget`
**Description:** Add a Target to a Set.
**Params (Key Data):**
* **GuidTarget** (String): New UID to associate to the Target.
* **RefGuidSet** (String): UID of Set.
* **RefGuidBaseSequence** (String): UID of Base Sequence.
* **RAJ2000 / DECJ2000** (Numeric): RA in decimal hours, DEC in decimal degrees.
* **PA** (Numeric): Position Angle in degrees.
* **Priority** (Integer): 0=Very Low to 4=First.

**Constraints Parameters (C_ prefix):**
* **C_Mask** (String): List of chars for enabled constraints (A=PA, B=Alt, C=SQM, D/E=HA, F/G=Date, H/J=Time, K=MoonDown, L/M=MoonPhase, N=MoonDist, O=HFD, P/T=MaxTime, Q/R=AirMass, U=OneShot).
* **C_AltMin**, **C_SqmMin**, **C_HAStart**, **C_HAEnd**, **C_MoonDown** (Boolean), **C_MoonPhaseMin/Max**.
* **C_MoonDistanceLorentzian** (Integer): 0=Broad Band, 1=Narrow Band, 2=Free.

**Dynamic Target Data:**
* **TKey** (String): Search Key (Voyager CTNAME).
* **TType** (Integer): 0=DSO, 1=Comet, 2=Asteroid, 3=Planet, 4=DynaSearch.
* **IsDynamicPointingOverride** (Boolean): True to calculate RA/DEC with RoboOrbits during sequence.

### v) RemoteRoboTargetUpdateTarget
**Method:** `RemoteRoboTargetUpdateTarget`
**Description:** Update an existing Target.
**Params:** Same structure as `AddTarget`, using `RefGuidTarget` to identify the object.

### w) RemoteRoboTargetRemoveTarget
**Method:** `RemoteRoboTargetRemoveTarget`
**Description:** Remove a Target already stored. Attention: all shots and data referring to this Target will be deleted.
Ce que votre IA doit savoir :
Ce fichier contient les commandes de "structure" les plus lourdes. Notez bien que pour AddTarget et UpdateTarget, le paramètre TType est obligatoire (MUST VALORIZED ever !). De plus, le retour ActionResultInt: 4 avec un champ ret: "DONE" est la seule preuve de succès ; toute autre valeur doit être traitée comme une erreur par votre application Laravel.
