STARKEEPER.IT
# Voyager Application Server - Signals and Extended Events
Voyager Starkeeper Software Application Server Protocol Ver.1.0

### c) Signal (Continued)
Used from server to send signal about something happen in Voyager, status changed, action started, error raised etc etc. Signals are sended in realtime.

**Code Table:**
| Code | Description |
| :--- | :--- |
| 1 | Autofocus Error |
| 2 | Remote Action RUN - Running Queue is empty |
| 4 | Remote Action RUN - Precise Pointing |
| 5 | Remote Action RUN - Autofocus |
| 15 | Remote Action RUN - Setup Connect |
| 16 | Remote Action RUN - Setup Disconnect |
| 18 | Remote Action RUN - Camera Shot |
| 19 | Remote Action RUN - CCD Cooling |
| 20 | Remote Action RUN - Focuser Move To |
| 21 | Remote Action RUN - Focuser OffSet |
| 22 | Remote Action RUN - Rotator Goto |
| 23 | Remote Action RUN - AutoFlat |
| 24 | Remote Action RUN - Filter Change To |
| 25 | Remote Action RUN - Plate Solving Actual Location |
| 31 | Remote Action RUN - Telescope Goto |
| 32 | Remote Action RUN - Run External Script/Application |
| 500 | VOYAGER General STATUS - Error (some error from action or thread raised) |
| 501 | VOYAGER General STATUS - Idle (nothing to do ready to work) |
| 502 | VOYAGER General STATUS - Action Running |
| 503 | VOYAGER General STATUS - Action Stopped |
| 505 | VOYAGER General STATUS - Warning |

### d) NewFITReady
New FIT file just saved from Voyager to the O.S. filesystem.
* **File** (String): Path and name with extension of the file saved.
* **Type** (Integer): 0=LIGHT, 1=BIAS, 2=DARK, 3=FLAT.
* **VoyType** (String): TEST (Simple Test Shot), SHOT (Sequence/DragScript), SYNC (Solving actions).
* **SeqTarget** (String): Target Name if FIT was shot in a Sequence Running.

### g) RemoteActionResult
A remote action was ended in the server. You could check if you have task waiting for it matching the UID inside the event. Result of action is inside the event.
* **ActionResultInt** (Integer):
    * 4: OK - Finished
    * 5: FINISHED ERROR
    * 7: ABORTED
    * 8: TIMEOUT
* **Motivo** (String): If the ActionResultInt correspond to error in this field you’ll find the description of the error.

### i) ControlData
Contains data about status and controls from remote server. Sended only to Dashboard client (each 2s).
* **VOYSTAT** (Integer): 0=STOPPED, 1=IDLE, 2=RUN, 3=ERRORE, 5=WARNING.
* **MNTRA / MNTDEC** (String): Actual RA/DEC of Mount JNow.
* **MNTTFLIP** (String): Time to Meridian Cross in HH:mm:SS (negative if before).
* **MNTSFLIP** (Integer): 0=Not needed, 1=To do, 2=Running, 3=Done.
* **GUIDESTAT** (Integer): 0=STOPPED, 1=WAITING_SETTLE, 2=RUNNING, 3=TIMEOUT_SETTLE.

### j) WeatherAndSafetyMonitorData
* **SMStatus** (String): SAFE or UNSAFE.
* **WSCloud / WSRain / WSWind** (String): CLEAR, CLOUDY, DRY, RAIN, CALM, WINDY, etc.

--------------------------------------------------------------------------------
