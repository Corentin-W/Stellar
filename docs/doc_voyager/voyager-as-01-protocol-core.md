STARKEEPER.IT
# Voyager Application Server Protocol
#### Events, Methods and Workflow (TCP-IP)
Leonardo Orazi
December 27, 2024
Voyager Starkeeper Software Application Server Protocol Ver.1.0 [1]

## 1. Introduction
VOYAGER have an internal Application Server that allow external application to interact with it : [2]
* receiving events [2]
    o setup events o action events o error events [2]
* send commands [2]
    o setup cmd o action run o profile management o environment manage [3]

## 2. Connection
Clients connect to Voyager on TCP-IP port 5950. [3] When multiple Voyager instances are running, each instance listens on successive port numbers (5951, 5952, ...). [3] Max instance in the same PC is 3. [3] Firewall must be opened to allow communications in the O.S. [3]
VOYAGER allows multiple clients to establish connections simultaneously. [3]
When a client establishes a connection, VOYAGER sends a version event messages to the client (see the events section). [3] Notification messages are sent to all connected clients, answer to command only to relate client. [3]

## 3. HeartBeat
Communication between Server/Client is under HeartBeat keep-alive system. [4] If 15s passed without receiving valid data from client the server close the connection for inactivity. [4] If you want to leave connection opened with server but you don’t have data or command to send you must send a polling event each 5s to avoid connection closing, using a polling timer. [4] Also if the server don’t have valid data to send will use polling event each 5s to send to the client , in this way client know that server is running and connected and can manage (if needed) then closing itself. [4, 5]
Each communications valid received reset the inactivity timeout client side and server side, in this case the polling timer will be (must be) cleared and restarted. [5] You must implements this polling procedure in your client. [5]

## 4. Authentication
Authentication level between server/client is defined in Voyager -> Setup -> Remote Tab. [5] Possible is none (no authentication required), Username and Password (basic authentication needed with dedicated command from client), Ticket (for renting system, info only under NDA, contact Voyager support). [5, 6]
If the authorization level is not equal to NONE, server will wait for 5s after connection to receive the authentication request otherwise will close the connection. [6] If the authentication fail the connection will be closed immediatly. [6]
If the client is local and authorization is needed or the client will do authentication or the connection will be leave opened until the first command that need authentication will be asked and in this connection will be closed. [6, 7] Some commands and the events not need authentication and in this case a local client can run forever. [7]
