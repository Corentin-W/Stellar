(Cette section détaille les notifications JSON)
## 5. Events
Event Notification messages are formatted as JSON objects [8]. Each message is a single line of text terminated by CR LF [8].

### Common attributes
All messages contain the following attributes in common [8]:
* **Event** (String): the name of the event
* **Timestamp** (number): the timestamp of the event in seconds from the epoch, including fractional seconds
* **Host** (String): the hostname of the machine running VOYAGER
* **Inst** (Integer): the VOYAGER instance number (1-based)

### a) Version
Contains info about Voyager version [9].
* **VOYVersion** (String): the version of Voyager
* **VOYSubver** (String): the subversion of Voyager if present
* **MsgVersion** (Integer): The numeric version of protocol implemented in this version of Voyager

Example:
{"Event":"Version","Timestamp":1550018143.66187,"Host":"hal9000","Inst":1,"VOYVersion":"Release 2.0.14f - Built 2019-02-11","VOYSubver":"","MsgVersion":1}

### b) Polling
Protocol Heartbeat. Send according HeartBeat paragraph [10].
Example: {"Event":"Polling","Timestamp":1548806904.00159,"Host":"hal9000","Inst":1}

### c) Signal
Used from server to send signal about something happen in Voyager, status changed, action started, error raised etc etc [10]. Signals are sended in realtime [10].
* **Code** (Integer): The numeric index of Signal happen [10].

**Selected Codes Table:**
* 1: Autofocus Error
* 15: Remote Action RUN - Setup Connect
* 18: Remote Action RUN - Camera Shot
* 501: VOYAGER General STATUS - Idle
* 502: VOYAGER General STATUS - Action Running [10, 11]
