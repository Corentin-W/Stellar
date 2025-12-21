STARKEEPER.IT
# Voyager RoboTarget Reserved API - Commands Reference (Part 1)
Voyager Starkeeper Software RoboTarget NDA Protocol Ver.1.0 [1]

## 6. RoboTarget Commands
VOYAGER provides an RPC (remote procedure call) interface for clients. The message protocol is JSON RPC 2.0. [2]
Requests are sent as a single line of text, terminated by CR LF. Responses from the server are also a single line of text terminated by CR LF. Pamaters name and parameters value are case sensitive, please for Boolean value use true or false lower case. [2]

All the commands (exceptions you’ll find in a single command description) return an async jsonrpc result or jsonrpc error. [3] Remember that ID is a integer counter sequential of the command in the client scope. [3]
All the commands (exceptions you’ll find in a single command description) return when finished an RemoteActionResult event. [3]
All Command (exceptions you’ll find in a single command description) have like params a string unique identifier UID, usually used is a windows guide identifier. This string must identify univoque the command. [3]
Some commands can generate dedicated signal events before to send the RemoteActionResult final event. [4]

---

### a) RemoteSetRoboTargetManagerMode
**Method:** `RemoteSetRoboTargetManagerMode` [4]
**Description:** Declare to the Server to considering this Client like a RoboTarget Manager. This command must be used for first after user authentication to allow use of all the others API included in this document. [4]

**Params:**
* **UID** (String): Unique identifier of the Action to abort. [4]
* **MACKey** (String): The MAC Key string received with the NDA. [5]
* **Hash** (String): Create a concatenated string with “||:||” string separator of RoboTarget Shared secret + SessionKey (the Timestamp string received in the Event Version sent by the Server as is) + the 4 MAC strings in order (1 to 4). Finally make an SHA1 hash and convert to base 64 string. [5]

**Result:** Integer(0). [5]
**License Required:** Advanced, Full with NDA. [5]

**Hash creation example (VALID ONLY FOR THIS COMMAND):**
* RoboTarget Shared Secret = “pippo”
* SessionKey= “1652231344.88438”
* MAC 1 = “12345678”, MAC 2 = ”abcdefg”, MAC 3 = ”pluto”, MAC 4 = “paperino” [6]
* **String concatenated:** “pippo||:||1652231344.88438||:||12345678abcdefgplutopaperino” [6]
* **SHA1 hashing:** “69efafc940cabd1797da7dc57a1452cdaae6d0ff” [6]
* **After Base64 conversion:** `Hash=”NjllZmFmYzk0MGNhYmQxNzk3ZGE3ZGM1N2ExNDUyY2RhYWU2ZDBmZg==”` [7]

---

### b) RemoteRoboTargetGetSet
**Method:** `RemoteRoboTargetGetSet` [7]
**Description:** Return list of RoboTarget Set for the Profile defined in Profile Parameter from Voyager ordered by Set Name. [7]

**Params:**
* **UID** (String): Unique identifier of the Action to abort. [7]
* **ProfileName** (String): Profile name used for search about Set. If empty will be answered all the set for all profile configured in Voyager. [8]
* **MAC** (String): Create a concatenated string with RoboTarget Shared secret + SessionKey (the Timestamp string received in the Event Version sent by the Server as is)) + ID of JSON-RPC command + UID of Voyager Command. Finally make an SHA1 hash and convert to base 64 string. [8]

**Remote Action Result Parameters (ParamRet Object):**
* **List** (Array): Array of Set Objects (guid, setname, profilename, Isdefault, status, tag, note). [9]

---

### c) RemoteRoboTargetGetBaseSequence
**Method:** `RemoteRoboTargetGetBaseSequence` [10]
**Description:** Return list of RoboTarget Base Sequence for the Profile defined in Profile Parameter from Voyager ordered by Base Sequence Name. [10]

**Params:**
* **UID, ProfileName, MAC**: Same logic as RemoteRoboTargetGetSet. [11]

**Remote Action Result Parameters (ParamRet Object):**
* **List** (Array): Array of Base Sequence Objects (guid, basesequencename, filename, profilename, isdefault, Status, note). [12]

---

### d) RemoteRoboTargetGetTarget
**Method:** `RemoteRoboTargetGetTarget` [13]
**Description:** Return list of RoboTarget Target for the Set defined in RefGuid Parameter from Voyager ordered by Base Target Name. [13]

**Params:**
* **RefGuidSet** (String): UID of Set which Target Belong. If empty will be return all the Target. [13]
* **UID, MAC**: Same logic as RemoteRoboTargetGetSet. [13]

**Key Attributes (Target Object):**
* **raj2000 / decj2000** (Numeric): RA in Hours / DEC in Degree. [14]
* **pa** (Numeric): Position Angle. [15]
* **priority** (Integer): 0=Very Low to 4=First. [15]
* **isrepeat / repeat** (Boolean/Integer): Repeating shot groups. [15, 16]
* **cmask** (String): Mask for enabled constraints. [17, 18]

**Constraints Mask 1 (cmask):**
A=Position Angle, B=Min Altitude, C=Min SQM, D=HA Start, E=HA End, F=Date Start, G=End Date, H=Time Start, J=Time End, K=Moon Down, L=Moon Phase Min, M=Moon Phase Max, N=Moon Distance, O=HFD Mean Max, P=Max Shot Time For Day, Q=Airmass Min, R=Airmass Max, S=Max Time for sequence, T=OneShot Target. [18, 19]

**Constraints Mask 2 (cmask2):**
L01=Moon Phase Min And Moon Up, M01=Moon Phase Max Or Moon Down, N01=Moon Distance or Moon Down, S01=Moon Lorentzian Avoidance or Moon Down. [19]

**Dynamic Target Support:**
* **TType** (Integer): 0=DSO, 1=Comet, 2=Asteroid, 3=Planet, 4=DynaSearch. [20]
* **TKey** (String): Search Key for Dynamic Target (CTNAME Field). [21]
