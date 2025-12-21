STARKEEPER.IT
# Voyager Application Server - Commands Reference
Voyager Starkeeper Software Application Server Protocol Ver.1.0

## 6. Commands
VOYAGER provides an RPC (remote procedure call) interface for clients. [3] The message protocol is JSON RPC 2.0. [3] Requests are sent as a single line of text, terminated by CR LF. [3] Responses from the server are also a single line of text terminated by CR LF. [3] Parameters name and parameters value are case sensitive, please for Boolean value use true or false lower case. [3]

All the commands (exceptions you’ll find in a single command description) return an async jsonrpc result or jsonrpc error. [4] You can refer to jsonrpc protocol or see the example below. [4] Remember that ID is a integer counter sequential of the command in the client scope. [4] All the commands (exceptions you’ll find in a single command description) return when finished an RemoteActionResult event. [4]

All Command (exceptions you’ll find in a single command description) have like params a string unique identifier UID, usually used is a windows guide identifier. [4] This string must identify univoque the command. [4] Some commands can generate dedicated signal events before to send the RemoteActionResult final event. [5]

### a) Disconnect
**Method:** `disconnect`
**Description:** Disconnect the Client from the Server. [6] Necessary when you want to close the communication with server in a clean way. [6] Just closing the socket without disconnect command force the server to wait heartbeat timeout to declare closed the communication and release the client thread. [6] Using this command close immediately the connection and the thread. [6] No RemoteActionResult will be received about this command. [6]
**Params:** None. [7]
**Result:** Integer(0). [7]

### b) GetArrayElementData
**Method:** `GetArrayElementData`
**Description:** Ask to the Server to send the common data for Array Custom Management System. [7] Status, CCD temperature, Rotator PA, Mount position, etc.etc. [7] Data arrive like event. [7] See the relative event ArrayElementData. [7]
**Params:** None. [7]
**Result:** Integer(0). [7]

### c) RemoteActionAbort
**Method:** `RemoteActionAbort`
**Description:** Ask to the Server to abort the action running. [8]
**Params:**
* **UID** (String): Unique identifier of the Action to abort. [9]
**Result:** Integer(0). [9]

### e) RemoteCameraShot
**Method:** `RemoteCameraShot`
**Description:** Ask to the Server to do an exposure with the parameters send. [10] This method is ASync, a JSonRPC result will be send from server immediately with the answer to command. [10] A RemoteActionResult event with the final result of the remote action will be send. [10]
**Params:**
* **UID** (String): Unique identifier of the Action to abort. [11]
* **Expo** (Number): Time of exposure expressed in seconds. [11]
* **Bin** (Integer): Binning value for x and y. [11]
* **FilterIndex** (Integer): Index of filter to user for exposure like received in RemoteGetFiltersConfiguration. [11]
* **IsSaveFile** (Boolean): true always. [12]
* **FitFileName** (String): Name of File to save. [12] You can use a special symbols to identify the location where to save file in the directory default of server, use `%%fitdir%%`. [12]
* **Gain / Offset** (Integer): For CMOS camera. [13] SPECIAL VALUES: -2147483648 for NULL (no change), -900000 for Preset value, -800000 for Actual value. [14]

--------------------------------------------------------------------------------
