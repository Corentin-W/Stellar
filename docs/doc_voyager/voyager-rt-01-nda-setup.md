STARKEEPER.IT
# Voyager RoboTarget JSON-RPC API
#### Definitions, Events and Methods under NDA
Leonardo Orazi - June 25, 2024
Voyager Starkeeper Software RoboTarget NDA Protocol Ver.1.0

## 1. Introduction
VOYAGER have an internal Application Server that allow external application to interact with it:
* receiving events (setup, action, error events)
* send commands (setup cmd, action run, profile management, environment manage)

This document are reserved to the RoboTarget JSON-RPC API. The API about RoboTarget working only in a Voyager installation having the Advanced/Full license with RoboTarget NDA Plug in activated.
##### What are inside this document are totally under NDA, is not possible disclose it to public or other entities outside of what/who are reported in the NDA you have signed.

## 2. RoboTarget Data Objects
* **Profile**: All the settings that belong to a complete astrophotography setup configured in Voyager.
* **Base Sequence**: Sequence file to use like template for inherits the final Sequence to use for shot the Target.
* **Set**: Group the target for some kind of logic. If the Set is disabled all the Target inside will not be considered by the RoboTarget Scheduler.
* **Target**: Deep sky object to shot with all information about constraints, pointing and so.
* **Shot**: Shot information by filter with exposure and shot settings.
* **Session**: Set of Run done for the specified target.
* **Run**: Sequence runned on a Target.
* **Orphan Set/Target**: Set or Target belonging to a profile or base sequence that no longer exists.

## 3. Setup the MAC key related to NDA
To access to this reserved API you need a MAC hashing with dedicated key and words released to you with the License.
1. Go to **Setup Section** -> **Common Tab**.
2. Locate the **RoboTarget MAC** box.
3. Select **"Use Custom"** or **"Use RoboTargetManager or Custom"**.
4. Edit the **Custom Key** with the MAC key you have received.
5. Press **Apply** and **Restart Voyager**.

**MACID modes:**
* **Use RoboTarget Manager**: Default for NOT NDA Server.
* **Use Custom**: Reserved to NDA Server, allows access only using the Custom Key and MAC system.
* **User RoboTarget Manager or Custom**: Allows access from both systems.

## 4. Activate the RoboTarget Client Mode
At each connection, you must set the RoboTarget mode in your client first after user authentication using the command `RemoteSetRoboTargetManagerMode`.

**Example (TX):**
{"method": "RemoteSetRoboTargetManagerMode", "params": {"UID":"4ba87f87-b3e8-4bf5-9314-287ebc1c70f7","MACKey":"xxxxxx","Hash":"mQw/4x7qn09944Ndj5ne9/Z+b0="}, "id": 3}
