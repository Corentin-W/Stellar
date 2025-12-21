STARKEEPER.IT
# Voyager Application Server - Events Reference
Voyager Starkeeper Software Application Server Protocol Ver.1.0

## 5. Events
Event Notification messages are formatted as JSON objects. Each message is a single line of text terminated by CR LF.

### Common attributes
All messages contain the following attributes in common:
* **Event** (String): the name of the event
* **Timestamp** (number): the timestamp of the event in seconds from the epoch, including fractional seconds
* **Host** (String): the hostname of the machine running VOYAGER
* **Inst** (Integer): the VOYAGER instance number (1-based)

### a) Version
Contains info about Voyager version
* **VOYVersion** (String): the version of Voyager
* **VOYSubver** (String): the subversion of Voyager if present
* **MsgVersion** (Integer): The numeric version of protocol implemented in this version of Voyager

Example:
{"Event":"Version","Timestamp":1550018143.66187,"Host":"hal9000","Inst":1,"VOYVersion":"Release 2.0.14f - Built 2019-02-11","VOYSubver":"","MsgVersion":1}

### b) Polling
Protocol Heartbeat. Send according HeartBeat paragraph.
Example:
{"Event":"Polling","Timestamp":1548806904.00159,"Host":"hal9000","Inst":1}

### c) Signal
Used from server to send signal about something happen in Voyager, status changed, action started, error raised etc etc. Signals are sended in realtime.
* **Code** (Integer): The numeric index of Signal happen.

**Selected Signal Codes Table:**
1: Autofocus Error
2: Remote Action RUN - Running Queue is empty
4: Remote Action RUN - Precise Pointing
5: Remote Action RUN - Autofocus
15: Remote Action RUN - Setup Connect
16: Remote Action RUN - Setup Disconnect
18: Remote Action RUN - Camera Shot
19: Remote Action RUN - CCD Cooling
20: Remote Action RUN - Focuser Move To
21: Remote Action RUN - Focuser OffSet
22: Remote Action RUN - Rotator Goto
23: Remote Action RUN - AutoFlat
24: Remote Action RUN - Filter Change To
25: Remote Action RUN - Plate Solving Actual Location
31: Remote Action RUN - Telescope Goto
32: Remote Action RUN - Run External Script/Application
500: VOYAGER General STATUS - Error
501: VOYAGER General STATUS - Idle
502: VOYAGER General STATUS - Action Running
503: VOYAGER General STATUS - Action Stopped
505: VOYAGER General STATUS - Warning

### d) NewFITReady
New FIT file just saved from Voyager to the O.S. filesystem.
* **File** (String): Path and name with extension of the file saved.
* **Type** (Integer): 0=LIGHT, 1=BIAS, 2=DARK, 3=FLAT.
* **VoyType** (String): TEST, SHOT, SYNC.
* **SeqTarget** (String): Target Name if FIT was shot in a Sequence Running.

### g) RemoteActionResult
A remote action was ended in the server. Result of action is inside the event.
* **UID** (String): Unique string that identify in univocal way the action.
* **ActionResultInt** (Integer): 4=OK, 5=FINISHED ERROR, 7=ABORTED, 8=TIMEOUT.
* **Motivo** (String): Description of the error if present.
* **ParamRet** (Array): Return parameters if the action related return parameters.

### i) ControlData
Contains data about status and controls from remote server. Sended only to Dashboard client (each 2s).
* **VOYSTAT** (Integer): 0=STOPPED, 1=IDLE, 2=RUN, 3=ERRORE, 5=WARNING.
* **SETUPCONN** (Boolean): Indicate if all setup controls are connected.
* **MNTRA / MNTDEC** (String): Actual RA/DEC of Mount JNow.
* **MNTTFLIP** (String): Time to Meridian Cross in HH:mm:SS (negative if before).
* **MNTSFLIP** (Integer): 0=Not needed, 1=To do, 2=Running, 3=Done.
* **GUIDESTAT** (Integer): 0=STOPPED, 1=WAITING_SETTLE, 2=RUNNING, 3=TIMEOUT_SETTLE.

--------------------------------------------------------------------------------
