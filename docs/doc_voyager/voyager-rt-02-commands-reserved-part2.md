STARKEEPER.IT
# Voyager RoboTarget Reserved API - Commands Reference (Part 2)
Voyager Starkeeper Software RoboTarget NDA Protocol Ver.1.0

### e) RemoteRoboTargetGetRunList
**Method:** `RemoteRoboTargetGetRunList`
**Description:** Return list of RoboTarget Run for the Profile defined in Profile Parameter from Voyager ordered by datetime.

**Params:**
* **UID** (String): Unique identifier of the Action to abort. Use a Guide Window identifier or a unique key string generated.
* **ProfileName** (String): Profile name used for search about Runs. If empty will be answered the Runs for all profile configured in Voyager.
* **Days** (Numeric): Number of days backward to today to search. If 0 days are used all the runs will be listed.
* **MAC** (String): Create a concatenated string with RoboTarget Shared secret + SessionKey (the Timestamp string received in the Event Version sent by the Server as is)) + ID of JSON-RPC command + UID of Voyager Command. Finally make an SHA1 hash and convert to base 64 string.

**Remote Action Result Parameters (ParamRet Object):**
* **List** (Array): Array of Run Objects (guid, profilename, datetimestart, datetimeend, isrunning, seqcount, errcount, note).

---

### f) RemoteRoboTargetGetShot
**Method:** `RemoteRoboTargetGetShot`
**Description:** Return list of RoboTarget Shot associated to the Target.

**Params:**
* **UID** (String): Unique identifier of the Action to abort.
* **RefGuidTarget** (String): UID of Target used for search.
* **MAC**: Same logic as RemoteRoboTargetGetSet.

**Remote Action Result Parameters (ParamRet Object):**
* **List** (Array): Array of Shot Objects (guid, label, refguidtarget, filterindex, num, bin, readoutmode, type, speed, gain, offset, exposure, order, done, enabled, auxtotshot, auxshotdone, auxshotdonedeleted).

---

### g) RemoteRoboTargetGetSequenceListByProfile
**Method:** `RemoteRoboTargetGetSequenceListByProfile`
**Description:** Return list of Sequence available for a profile in default Voyager Sequence configuration folder.

**Params:**
* **UID, ProfileName, MAC**: Same logic as RemoteRoboTargetGetSet.

---

### h) RemoteRoboTargetGetSessionListByRun
**Method:** `RemoteRoboTargetGetSessionListByRun`
**Description:** Return list of RoboTarget Session done during the Run.

**Params:**
* **UID** (String): Unique identifier of the Action to abort.
* **RefGuidRun** (String): UID of Run.
* **MAC**: Same logic as RemoteRoboTargetGetSet.

**Session Result Codes:**
* 0 = UNDEF (Undefined)
* 1 = OK (Session finished without error)
* 2 = ABORTED (Session aborted)
* 3 = FINISHED_ERROR (Session finished with error)
* 4 = TIMEOUT (Session finished for timeout)

---

### i) RemoteRoboTargetGetShotDoneBySessionList
**Method:** `RemoteRoboTargetGetShotDoneBySessionList`
**Description:** Return list of RoboTarget Shot done during the Session.

**Params:**
* **RefGuidSession** (String): UID of Session.
* **UID, MAC**: Same logic as RemoteRoboTargetGetSet.

---

### m) RemoteRoboTargetGetErrorListByRun
**Method:** `RemoteRoboTargetGetErrorListByRun`
**Description:** Return list of RoboTarget Error done for a Run.

**Params:**
* **RefGuidRun** (String): UID of Run.
* **UID, MAC**: Same logic as RemoteRoboTargetGetSet.

**Error Code Description (Selected):**
* 0: No error
* 1: Unknow error
* 100: No shot configured for target during scheduling process
* 1000: Error during Scheduling Apply
* 1001: Error during Base Sequence loading
* 1006: Exit for Error
* 1007: Wrong Voyager profile loaded
* 1010: Cannot start sequence because Target is in no goto zone
* 1011: Emergency Suspend happens
* 1012: Emergency Exit happens
* 1013: The target is an orphan
* 2000: The Sequence end with an error
* 2001: The Sequence end for timeout
