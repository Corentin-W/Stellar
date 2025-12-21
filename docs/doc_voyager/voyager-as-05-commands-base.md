STARKEEPER.IT
# Voyager Application Server - Standard Commands Reference
#### Events, Methods and Workflow (TCP-IP)
Leonardo Orazi - December 27, 2024
Voyager Starkeeper Software Application Server Protocol Ver.1.0

## 6. Commands
VOYAGER provides an RPC (remote procedure call) interface for clients. The message protocol is JSON RPC 2.0. Requests are sent as a single line of text, terminated by CR LF. Responses from the server are also a single line of text terminated by CR LF. Parameters name and parameters value are case sensitive, please for Boolean value use true or false lower case. [2, 3]

All the commands (exceptions you’ll find in a single command description) return an async jsonrpc result or jsonrpc error. You can refer to jsonrpc protocol or see the example below. Remember that ID is a integer counter sequential of the command in the client scope. All the commands (exceptions you’ll find in a single command description) return when finished an RemoteActionResult event. [4, 5]

All Command (exceptions you’ll find in a single command description) have like params a string unique identifier UID, usually used is a windows guide identifier. This string must identify univocue the command. Some commands can generate dedicated signal events before to send the RemoteActionResult final event. [4, 5]

### Example Exchange
**Remote Setup Connect:**
➔{"method": "RemoteSetupConnect", "params": {"UID":"69e329c8-c80d-416e-94f5-5862399446b6","TimeoutConnect":90}, "id": 22}
{"jsonrpc": "2.0", "result": 0, "id": 22}
{"Event":"Signal","Timestamp":1556983812.21223,"Host":"hal9000","Inst":1,"Code":15}
{"Event":"RemoteActionResult","Timestamp":1556983826.98443,"Host":"hal9000","Inst":1,"UID":"69e329c8-c80d-416e-94f5-5862399446b6","ActionResultInt":4,"Motivo":"","ParamRet":{}} [6, 7]

---

### a) Disconnect
**Method:** `disconnect`
**Description:** Disconnect the Client from the Server. Necessary when you want to close the communication with server in a clean way. Just closing the socket without disconnect command force the server to wait heartbeat timeout to declare closed the communication and release the client thread. Using this command close immediately the connection and the thread. No RemoteActionResult will be received about this command. [8, 9]
**Params:** None.
**Result:** Integer(0). [8, 9]

### b) GetArrayElementData
**Method:** `GetArrayElementData`
**Description:** Ask to the Server to send the common data for Array Custom Management System. Status, CCD temperature, Rotator PA, Mount position, etc.etc. Data arrive like event. See the relative event ArrayElementData. [10, 11]
**Params:** None.
**Result:** Integer(0). [10, 11]

### c) RemoteActionAbort
**Method:** `RemoteActionAbort`
**Description:** Ask to the Server to abort the action running. [11, 12]
**Params:**
* **UID** (String): Unique identifier of the Action to abort. [11, 13]
**Result:** Integer(0). [11, 13]

### e) RemoteCameraShot
**Method:** `RemoteCameraShot`
**Description:** Ask to the Server to do an exposure with the parameters send. This method is ASync, a JSonRPC result will be send from server immediately with the answer to command. A RemoteActionResult event with the final result of the remote action will be send. Referring to the original command will be done with the UID. Setup must be connected to get a shot. [14, 15]
**Params:**
* **UID** (String): Unique identifier of the Action.
* **Expo** (Number): Time of exposure expressed in seconds.
* **Bin** (Integer): Binning value for x and y.
* **FilterIndex** (Integer): Index of filter to user for exposure (base 0).
* **ExpoType** (Integer): See table of types in NewFITReady event.
* **IsSaveFile** (Boolean): true always.
* **FitFileName** (String): Name of File to save. Use `%%fitdir%%` for default directory.
* **Gain / Offset** (Integer): For CMOS camera. SPECIAL VALUES: -2147483648 for NULL, -900000 for Preset, -800000 for Actual. [15-18]

### f) RemoteCooling
**Method:** `RemoteCooling`
**Description:** Activate or Deactivate Camera Cooling. It’s possible to do SetPoint, cooling down, warmup. Sync or ASync. [19]
**Params:**
* **IsSetPoint** (Boolean): true for Cooling camera using internal firmware ramp.
* **IsCoolDown** (Boolean): true for Cooling camera using Voyager ramp.
* **IsASync** (Boolean): If true action finish when cooling or warmup action is finished.
* **IsWarmup** (Boolean): true for Warmup camera.
* **IsCoolerOFF** (Boolean): true for Switch off cooling.
* **Temperature** (Number): Temperature to reach. [20]

### h) RemoteFilterChangeTo
**Method:** `RemoteFilterChangeTo`
**Description:** Change actual filter in the filter wheel. [21]
**Params:**
* **FilterIndex** (Integer): Index of filter (base 0). [21]

### l) RemoteFocusEx
**Method:** `RemoteFocusEx`
**Description:** Execute AutoFocus Action in Remote Voyager Server. [22]
**Params:**
* **FocusMode** (Integer): 0=Focus Star, 2=On Place, 3=Voyager RoboStar, 4=Voyager LocalField.
* **filtroFuocoIndex** (Integer): Index of filter to use.
* **StarRAJ2000Str / StarDECJ2000Str** (String): Coordinates in J2000 string format. [22-24]

### p) RemoteGetStatus
**Method:** `RemoteGetStatus`
**Description:** Return Operative Status of Voyager Application. [25]
**Result Parameters:**
* **VoyagerStatus** (String): STOPPED, IDLE, RUN, ERRORE, UNDEFINED, WARNING. [25]

### x) RemoteSetupConnect
**Method:** `RemoteSetupConnect`
**Description:** Connect all controls Setup in Remote Voyager Server. [26, 27]
**Params:**
* **UID** (String): Unique identifier of the Action.
* **TimeoutConnect** (Integer): Seconds to wait before declaring connection timeout. [27, 28]

### fff) RemoteMountStatusGetInfo
**Method:** `RemoteMountStatusGetInfo`
**Description:** Send request for info about mount status. [29]
**Result Parameters:**
* **IsMountConnected** (Boolean), **RA/DEC** (String JNow), **RAJ2000/DECJ2000** (String), **IsParked** (Boolean), **Pier** (String), **TimeToFlip** (String), **FlipStatus** (Integer). [29-31]
