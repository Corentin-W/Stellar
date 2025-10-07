# Documentation Technique - Voyager Application Server Protocol

**Version du protocole:** 1.0  
**Date:** Décembre 2024  
**Éditeur:** Starkeeper Software

---

## Table des matières

1. [Introduction](#introduction)
2. [Architecture de connexion](#architecture-de-connexion)
3. [Système d'authentification](#système-dauthentification)
4. [Heartbeat et Keep-Alive](#heartbeat-et-keep-alive)
5. [Événements](#événements)
6. [Commandes](#commandes)
7. [Workflow d'intégration](#workflow-dintégration)
8. [Gestion des erreurs](#gestion-des-erreurs)
9. [Exemples pratiques](#exemples-pratiques)

---

## Introduction

Le protocole Voyager Application Server permet le contrôle à distance d'un observatoire astronomique via une API JSON-RPC 2.0 sur TCP/IP.

### Fonctionnalités principales

- Contrôle d'équipements (caméra, monture, focuser, rotateur, dôme)
- Automatisation d'observations (séquences, mosaïques)
- Plate solving et autofocus automatique
- Récupération d'images FITS en temps réel
- Monitoring des conditions météo et de sécurité
- Gestion de base de données de cibles (RoboTarget)

### Licences disponibles

| Licence | Fonctionnalités |
|---------|-----------------|
| **Base** | Commandes essentielles, contrôle manuel |
| **Advanced** | RoboTarget, séquences avancées, mosaïques |
| **Full** | Toutes fonctionnalités incluses |
| **Custom** | Configuration sur mesure |

---

## Architecture de connexion

### Paramètres réseau
Protocol: TCP/IP
Port: 5950 (première instance)
Ports additionnels: 5951, 5952 (instances multiples)
Max instances: 3 par machine
Format: JSON-RPC 2.0
Encodage: UTF-8

### Établissement de connexion
```javascript
// 1. Connexion TCP
socket.connect('192.168.1.100', 5950)

// 2. Réception automatique du Version Event
{
  "Event": "Version",
  "Timestamp": 1550096193.55834,
  "Host": "hal9000",
  "Inst": 1,
  "VOYVersion": "Release 2.0.14f",
  "MsgVersion": 1
}

// 3. Authentification (si requise)
// 4. Activation mode Dashboard (optionnel)
// 5. Début des opérations
Multi-clients
Le serveur supporte plusieurs connexions simultanées. Chaque client reçoit :

Tous les événements de notification
Uniquement les réponses à ses propres commandes


Système d'authentification
Niveaux d'authentification
1. None (Aucune)

Pas d'authentification requise
Connexion directe

2. Base (Username/Password)
javascript// Créer la chaîne d'authentification
const credentials = "username:password";
const base64 = btoa(credentials); // "dXNlcm5hbWU6cGFzc3dvcmQ="

// Commande
{
  "method": "AuthenticateUserBase",
  "params": {
    "UID": "37f4962a-73c5-44f5-80e1-d29f029f49a9",
    "Base": "dXNlcm5hbWU6cGFzc3dvcmQ="
  },
  "id": 1
}

// Réponse succès
{
  "jsonrpc": "2.0",
  "authbase": {
    "Username": "admin",
    "FirstName": "Mario",
    "LastName": "Rossi",
    "Mail": "mario.rossi@mail.com",
    "Permissions": 934838,
    "Note": "Remote User"
  },
  "id": 1
}
3. Ticket (Système de location)

Réservé aux partenaires (NDA requis)
Validation par code d'activation

Timeout d'authentification
⚠️ Important: Si l'authentification est requise, le client dispose de 5 secondes après connexion pour s'authentifier, sinon la connexion est fermée.

Heartbeat et Keep-Alive
Principe
Le serveur et le client doivent échanger des données régulièrement pour maintenir la connexion active.
Règles du Heartbeat
Timeout inactivité: 15 secondes
Polling recommandé: Toutes les 5 secondes
Implémentation côté client
javascript// Timer de polling
let pollingTimer;

function resetPollingTimer() {
  clearTimeout(pollingTimer);
  pollingTimer = setTimeout(sendPolling, 5000);
}

function sendPolling() {
  socket.send(JSON.stringify({
    "Event": "Polling",
    "Timestamp": Date.now() / 1000
  }));
  resetPollingTimer();
}

// Réinitialiser à chaque envoi/réception
socket.on('data', (data) => {
  resetPollingTimer();
  processData(data);
});

socket.on('send', () => {
  resetPollingTimer();
});
Format du Polling Event
json{
  "Event": "Polling",
  "Timestamp": 1548806904.00159,
  "Host": "hal9000",
  "Inst": 1
}

Événements
Les événements sont des notifications envoyées automatiquement par le serveur. Format: une ligne JSON terminée par CR LF.
Attributs communs
Tous les événements contiennent:
javascript{
  "Event": "NomEvenement",      // Type d'événement
  "Timestamp": 1550018143.66187, // Epoch timestamp
  "Host": "hal9000",             // Nom machine serveur
  "Inst": 1                      // Numéro d'instance Voyager
}
Événements essentiels
Version
Premier événement reçu à la connexion.
json{
  "Event": "Version",
  "Timestamp": 1550018143.66187,
  "Host": "hal9000",
  "Inst": 1,
  "VOYVersion": "Release 2.0.14f - Built 2019-02-11",
  "VOYSubver": "",
  "MsgVersion": 1
}
Polling
Heartbeat keep-alive (toutes les 5s).
json{
  "Event": "Polling",
  "Timestamp": 1548806904.00159,
  "Host": "hal9000",
  "Inst": 1
}
Signal
Notifications de changements d'état.
json{
  "Event": "Signal",
  "Timestamp": 1550018150.45152,
  "Host": "hal9000",
  "Inst": 1,
  "Code": 18  // Voir table des codes
}
Codes Signal importants:
CodeDescription1Erreur Autofocus2Queue d'actions vide5Autofocus en cours18Prise de vue en cours500Erreur générale501IDLE (prêt)502Action en cours503Action arrêtée
NewFITReady
Nouveau fichier FITS sauvegardé.
json{
  "Event": "NewFITReady",
  "Timestamp": 1550018163.09996,
  "Host": "hal9000",
  "Inst": 1,
  "File": "C:\\Users\\leonardo\\Documents\\Voyager\\FIT\\M81_20190213_003550.fit",
  "Type": 0,        // 0=LIGHT, 1=BIAS, 2=DARK, 3=FLAT
  "VoyType": "SHOT", // TEST, SHOT, SYNC
  "SeqTarget": "M81"
}
⚠️ Note: Les chemins Windows utilisent \\ (caractère d'échappement).
RemoteActionResult
Résultat d'une commande exécutée.
json{
  "Event": "RemoteActionResult",
  "Timestamp": 1556621977.1658,
  "Host": "hal9000",
  "Inst": 1,
  "UID": "eaea5429-f5a9-4012-bc9f-f109e605f5d8",
  "ActionResultInt": 4,
  "Motivo": "",
  "ParamRet": {
    "DownloadAndSaveTime": 3.0700113
  }
}
Codes ActionResultInt:
CodeÉtatDescription0NEED_INITEn attente d'initialisation1READYPrêt à exécuter2RUNNINGEn cours d'exécution4OKSuccès ✓5ERRORErreur ✗6ABORTINGAnnulation en cours7ABORTEDAnnulé8TIMEOUTTimeout10OK_PARTIALSuccès partiel
Shutdown
Le serveur Voyager va se fermer.
json{
  "Event": "ShutDown",
  "Timestamp": 1548806904.00159,
  "Host": "hal9000",
  "Inst": 1
}
⚠️ Action: Fermer immédiatement le client.
Événements Dashboard
Activés uniquement si le client utilise RemoteSetDashboardMode.
ControlData
État complet du système (toutes les 2s).
json{
  "Event": "ControlData",
  "Timestamp": 1564675036.22405,
  "Host": "hal9000",
  "Inst": 1,
  "TI": "2019-08-02 19:24:32",
  "TIUTC": "2019-08-02 17:24:32",
  "VOYSTAT": 2,           // 0=STOPPED, 1=IDLE, 2=RUN, 3=ERROR
  "SETUPCONN": true,
  "CCDCONN": true,
  "CCDTEMP": 10,
  "CCDPOW": 45,
  "CCDSETP": -15,
  "CCDCOOL": true,
  "MNTCONN": true,
  "MNTPARK": false,
  "MNTRA": "02:49:50",
  "MNTDEC": "47° 20' 07\"",
  "MNTTRACK": true,
  "AFCONN": true,
  "AFPOS": 5234,
  "AFTEMP": 12.5,
  "SEQNAME": "M31_LRGB",
  "SEQREMAIN": "02:45:30",
  "GUIDESTAT": 2,         // 0=STOPPED, 1=SETTLING, 2=RUNNING
  "GUIDEX": -0.259,
  "GUIDEY": 0.039
  // ... (voir doc complète pour tous les champs)
}
Valeurs spéciales:
-123456789 = OFF (contrôle désactivé)
+123456789 = ERROR (erreur ou non disponible)
NewJPGReady
Image preview Base64 (uniquement Dashboard).
json{
  "Event": "NewJPGReady",
  "Timestamp": 1564313171.92553,
  "Host": "hal9000",
  "Inst": 1,
  "File": "C:\\...\\TestShot_20190728_112558.fit",
  "SequenceTarget": "",
  "TimeInfo": 1564313170.52465,
  "Expo": 1,
  "Bin": 2,
  "Filter": "** BayerMatrix **",
  "HFD": 4.53,
  "StarIndex": 8.21,
  "PixelDimX": 2048,
  "PixelDimY": 1024,
  "Base64Data": "/9j/4AAQSkZJRgABAQEAYABgAAD/..."
}
ShotRunning
Progression de l'exposition en cours (toutes les 1s).
json{
  "Event": "ShotRunning",
  "Timestamp": 1564498706.03752,
  "Host": "hal9000",
  "Inst": 1,
  "File": "TestShot_20190730_145825.fit",
  "Expo": 300,      // Durée totale
  "Elapsed": 127,   // Temps écoulé
  "ElapsedPerc": 42, // Pourcentage
  "Status": 1       // 0=IDLE, 1=EXPOSE, 2=DOWNLOAD, 3=WAIT_JPG, 4=ERROR
}

Commandes
Les commandes utilisent le format JSON-RPC 2.0. Format: une ligne JSON terminée par CR LF.
Structure d'une commande
json{
  "method": "NomMethode",
  "params": {
    "UID": "guid-unique-obligatoire",
    "Param1": "valeur1",
    "Param2": 123
  },
  "id": 1
}
Éléments obligatoires

method: Nom de la commande
params.UID: GUID unique (traçabilité)
id: Compteur séquentiel (entier)

Réponses
Réponse immédiate (synchrone):
json{"jsonrpc": "2.0", "result": 0, "id": 1}
Réponse d'erreur:
json{
  "jsonrpc": "2.0",
  "error": {
    "code": 1,
    "message": "Description de l'erreur"
  },
  "id": 1
}
Résultat final (asynchrone):
Event RemoteActionResult avec le même UID.
Commandes Setup
RemoteSetupConnect
Connecte tous les équipements du profil actif.
json{
  "method": "RemoteSetupConnect",
  "params": {
    "UID": "69e329c8-c80d-416e-94f5-5862399446b6",
    "TimeoutConnect": 90
  },
  "id": 22
}
Paramètres:

TimeoutConnect (integer): Timeout en secondes

Workflow:
1. Réponse immédiate: {"result": 0}
2. Signal Code 15 (Setup Connect running)
3. RemoteActionResult avec ActionResultInt = 4 (succès)
RemoteSetupDisconnect
Déconnecte tous les équipements.
json{
  "method": "RemoteSetupDisconnect",
  "params": {
    "UID": "d4522a50-bf00-4bdd-acaa-19082578b9a0",
    "TimeoutDisconnect": 90
  },
  "id": 9384
}
RemoteGetStatus
Obtient le statut de Voyager.
json{
  "method": "RemoteGetStatus",
  "params": {
    "UID": "47a439a9-6453-477c-b5c4-529a93605867"
  },
  "id": 369
}
Retour dans ParamRet:
json{
  "VoyagerStatus": "RUN"
  // STOPPED, IDLE, RUN, ERRORE, UNDEFINED, WARNING
}
Commandes Caméra
RemoteCameraShot
Prend une photo.
json{
  "method": "RemoteCameraShot",
  "params": {
    "UID": "eaea5429-f5a9-4012-bc9b-f109e605f5d8",
    "Expo": 300,              // Exposition en secondes
    "Bin": 1,                 // Binning
    "IsROI": false,           // Utiliser ROI?
    "ROITYPE": 0,             // 0=Full, 1=Half, 2=Quarter, etc.
    "ROIX": 0,
    "ROIY": 0,
    "ROIDX": 0,
    "ROIDY": 0,
    "FilterIndex": 0,         // Index du filtre (0-based)
    "ExpoType": 0,            // 0=LIGHT, 1=BIAS, 2=DARK, 3=FLAT
    "SpeedIndex": 0,          // Vitesse ISO/Gain preset
    "ReadoutIndex": 0,        // Mode de lecture
    "IsSaveFile": true,
    "FitFileName": "%%fitdir%%\\TestShot_20190130_001330.fit",
    "Gain": 78,               // Pour CMOS (-2147483648 = NULL)
    "Offset": 22,             // Pour CMOS
    "Parallelized": false
  },
  "id": 306
}
Chemins spéciaux:

%%fitdir%%: Répertoire FIT par défaut
%%sequencedir%%: Répertoire séquences

Valeurs spéciales Gain/Offset:
-2147483648 : NULL (ne pas modifier)
-900000     : Utiliser valeur preset du profil
-800000     : Utiliser valeur actuelle
Workflow:
1. Réponse: {"result": 0}
2. Signal Code 18 (Shot running)
3. ShotRunning events (progression)
4. NewFITReady event
5. Signal Code 2 (Queue vide)
6. RemoteActionResult
RemoteGetCCDTemperature
Lit la température du capteur.
json{
  "method": "RemoteGetCCDTemperature",
  "params": {
    "UID": "24a92e1e-7383-4854-9c36-dbc77351836f"
  },
  "id": 173
}
Retour:
json{
  "ParamRet": {
    "CCDTemp": -15.2  // En °C
  }
}
RemoteCooling
Contrôle le refroidissement.
json{
  "method": "RemoteCooling",
  "params": {
    "UID": "37f4962a-73c5-44f5-80e1-d29f029f49a9",
    "IsSetPoint": true,      // SetPoint firmware
    "IsCoolDown": false,     // CoolDown Voyager
    "IsASync": true,         // Attendre la fin?
    "IsWarmup": false,       // Réchauffement?
    "IsCoolerOFF": false,    // Éteindre?
    "Temperature": -25       // °C cible
  },
  "id": 84
}
Commandes Monture
RemotePrecisePointTarget
Pointage précis avec plate solving.
json{
  "method": "RemotePrecisePointTarget",
  "params": {
    "UID": "eaea5429-f5a9-4012-bc9b-f109e605f5d8",
    "IsText": false,         // Coords en texte ou double?
    "RA": 12.5,              // RA en heures (si IsText=false)
    "DEC": 45.3,             // DEC en degrés
    "RAText": "",            // "HH MM SS.SSS" (si IsText=true)
    "DECText": "",           // "DD MM SS.SSS"
    "Parallelized": false
  },
  "id": 2
}
Retour ParamRet:
json{
  "ActionResult": 1
  // 0=FAILED, 1=OK_IN_RANGE, 2=OK_OUT_RANGE, 3=OK_NO_SOLVE
}
RemotePrecisePointTargetAndPA
Pointage avec angle de position rotateur.
json{
  "method": "RemotePrecisePointTargetAndPA",
  "params": {
    "UID": "eaea5429-f5a9-4012-bc9b-f109e605f5d8",
    "IsText": false,
    "RA": 12.5,
    "DEC": 45.3,
    "RAText": "",
    "DECText": "",
    "PA": 123.5,                      // Angle position en degrés
    "PATollerance": 3.0,              // Tolérance +/-
    "IsSkyPA": true,                  // Utiliser Sky PA?
    "MantainImageOrientation": true   // Maintenir orientation?
  },
  "id": 2
}
RemoteMountFastCommand
Commandes rapides monture.
json{
  "method": "RemoteMountFastCommand",
  "params": {
    "UID": "eaea5429-f5a9-4012-bc9b-f109e605f5d8",
    "CommandType": 1
    // 1=TrackOn, 2=TrackOff, 3=Park, 4=Unpark, 5=GotoZenith, 6=Home
  },
  "id": 2
}
RemotePulseGuide
Guidage par impulsions.
json{
  "method": "RemotePulseGuide",
  "params": {
    "UID": "eaea5429-f5a9-4012-bc9b-f109e605f5d8",
    "Direction": 0,        // 0=North, 1=SouthRéessayerCfais moi tout dans un artifactModifier <artifact identifier="voyager-protocol-doc" type="text/markdown" title="Documentation Technique Voyager Application Server">
# Documentation Technique - Voyager Application Server Protocol
Version du protocole: 1.0
Date: Décembre 2024
Éditeur: Starkeeper Software

Table des matières

Introduction
Architecture de connexion
Système d'authentification
Heartbeat et Keep-Alive
Événements
Commandes
RoboTarget - Gestion avancée
Workflow d'intégration
Gestion des erreurs
Exemples pratiques


Introduction
Le protocole Voyager Application Server permet le contrôle à distance d'un observatoire astronomique via une API JSON-RPC 2.0 sur TCP/IP.
Fonctionnalités principales

Contrôle d'équipements (caméra, monture, focuser, rotateur, dôme)
Automatisation d'observations (séquences, mosaïques)
Plate solving et autofocus automatique
Récupération d'images FITS en temps réel
Monitoring des conditions météo et de sécurité
Gestion de base de données de cibles (RoboTarget)

Licences disponibles
LicenceFonctionnalitésBaseCommandes essentielles, contrôle manuelAdvancedRoboTarget, séquences avancées, mosaïquesFullToutes fonctionnalités inclusesCustomConfiguration sur mesure

Architecture de connexion
Paramètres réseau
Protocol: TCP/IP
Port: 5950 (première instance)
Ports additionnels: 5951, 5952 (instances multiples)
Max instances: 3 par machine
Format: JSON-RPC 2.0
Encodage: UTF-8
Terminaison: CR LF (\r\n)
Établissement de connexion
javascript// 1. Connexion TCP
socket.connect('192.168.1.100', 5950)

// 2. Réception automatique du Version Event
{
  "Event": "Version",
  "Timestamp": 1550096193.55834,
  "Host": "hal9000",
  "Inst": 1,
  "VOYVersion": "Release 2.0.14f",
  "MsgVersion": 1
}

// 3. Authentification (si requise)
// 4. Activation mode Dashboard (optionnel)
// 5. Début des opérations
Multi-clients
Le serveur supporte plusieurs connexions simultanées. Chaque client reçoit :

Tous les événements de notification
Uniquement les réponses à ses propres commandes

Firewall
⚠️ Important: Ouvrir le port 5950 (et suivants si multi-instances) dans le pare-feu Windows/Linux.

Système d'authentification
Niveaux d'authentification
1. None (Aucune)

Pas d'authentification requise
Connexion directe

2. Base (Username/Password)
javascript// Créer la chaîne d'authentification
const credentials = "username:password";
const base64 = btoa(credentials); // "dXNlcm5hbWU6cGFzc3dvcmQ="

// Commande
{
  "method": "AuthenticateUserBase",
  "params": {
    "UID": "37f4962a-73c5-44f5-80e1-d29f029f49a9",
    "Base": "dXNlcm5hbWU6cGFzc3dvcmQ="
  },
  "id": 1
}

// Réponse succès
{
  "jsonrpc": "2.0",
  "authbase": {
    "Username": "admin",
    "FirstName": "Mario",
    "LastName": "Rossi",
    "Mail": "mario.rossi@mail.com",
    "Permissions": 934838,
    "Note": "Remote User"
  },
  "id": 1
}

// Réponse erreur
{
  "jsonrpc": "2.0",
  "error": {
    "code": 1,
    "message": "Authentication Rejected"
  },
  "id": 1
}
3. Ticket (Système de location)

Réservé aux partenaires (NDA requis)
Validation par code d'activation et période de réservation

javascript{
  "method": "AuthenticateUserTicket",
  "params": {
    "UID": "37f4962a-73c5-44f5-80e1-d29f029f49a9",
    "Ticket": "xxxxxxxxxxxxxxxxxxxxxxx"
  },
  "id": 84
}
Timeout d'authentification
⚠️ Important: Si l'authentification est requise, le client dispose de 5 secondes après connexion pour s'authentifier, sinon la connexion est fermée.

Heartbeat et Keep-Alive
Principe
Le serveur et le client doivent échanger des données régulièrement pour maintenir la connexion active.
Règles du Heartbeat
Timeout inactivité: 15 secondes
Polling recommandé: Toutes les 5 secondes
Reset automatique: À chaque envoi/réception de données valides
Implémentation côté client
javascript// Timer de polling
let pollingTimer;

function resetPollingTimer() {
  clearTimeout(pollingTimer);
  pollingTimer = setTimeout(sendPolling, 5000);
}

function sendPolling() {
  const polling = {
    "Event": "Polling",
    "Timestamp": Date.now() / 1000,
    "Host": "client-host",
    "Inst": 1
  };
  socket.send(JSON.stringify(polling) + "\r\n");
  resetPollingTimer();
}

// Réinitialiser à chaque envoi/réception
socket.on('data', (data) => {
  resetPollingTimer();
  processData(data);
});

socket.on('send', () => {
  resetPollingTimer();
});

// Démarrer le polling
resetPollingTimer();
Format du Polling Event
json{
  "Event": "Polling",
  "Timestamp": 1548806904.00159,
  "Host": "hal9000",
  "Inst": 1
}

Événements
Les événements sont des notifications envoyées automatiquement par le serveur. Format: une ligne JSON terminée par CR LF.
Attributs communs
Tous les événements contiennent:
javascript{
  "Event": "NomEvenement",      // Type d'événement
  "Timestamp": 1550018143.66187, // Epoch timestamp
  "Host": "hal9000",             // Nom machine serveur
  "Inst": 1                      // Numéro d'instance Voyager
}
Événements essentiels
Version
Premier événement reçu à la connexion.
json{
  "Event": "Version",
  "Timestamp": 1550018143.66187,
  "Host": "hal9000",
  "Inst": 1,
  "VOYVersion": "Release 2.0.14f - Built 2019-02-11",
  "VOYSubver": "",
  "MsgVersion": 1
}
Polling
Heartbeat keep-alive (toutes les 5s).
json{
  "Event": "Polling",
  "Timestamp": 1548806904.00159,
  "Host": "hal9000",
  "Inst": 1
}
Signal
Notifications de changements d'état.
json{
  "Event": "Signal",
  "Timestamp": 1550018150.45152,
  "Host": "hal9000",
  "Inst": 1,
  "Code": 18
}
Codes Signal importants:
CodeDescriptionUsage1Erreur AutofocusAutofocus a échoué2Queue videToutes actions terminées5Autofocus runningMise au point en cours18Camera Shot runningExposition en cours31Telescope GotoPointage en cours500ERREUR généraleErreur système501IDLEPrêt, en attente502Action RunningAction en exécution503Action StoppedAction arrêtée504UNDEFINEDÉtat indéfini505WARNINGAvertissement
NewFITReady
Nouveau fichier FITS sauvegardé.
json{
  "Event": "NewFITReady",
  "Timestamp": 1550018163.09996,
  "Host": "hal9000",
  "Inst": 1,
  "File": "C:\\Users\\leonardo\\Documents\\Voyager\\FIT\\M81_20190213_003550.fit",
  "Type": 0,
  "VoyType": "SHOT",
  "SeqTarget": "M81"
}
Attributs:

File: Chemin complet du fichier (échappement \\)
Type: 0=LIGHT, 1=BIAS, 2=DARK, 3=FLAT
VoyType: TEST, SHOT, SYNC
SeqTarget: Nom de la cible (si séquence)

⚠️ Note: Les chemins Windows utilisent \\ (caractère d'échappement).
RemoteActionResult
Résultat d'une commande exécutée.
json{
  "Event": "RemoteActionResult",
  "Timestamp": 1556621977.1658,
  "Host": "hal9000",
  "Inst": 1,
  "UID": "eaea5429-f5a9-4012-bc9f-f109e605f5d8",
  "ActionResultInt": 4,
  "Motivo": "",
  "ParamRet": {
    "DownloadAndSaveTime": 3.0700113
  }
}
Codes ActionResultInt:
CodeÉtatDescription0NEED_INITEn attente d'initialisation1READYPrêt à exécuter2RUNNINGEn cours d'exécution4OKSuccès ✓5ERRORErreur ✗6ABORTINGAnnulation en cours7ABORTEDAnnulé8TIMEOUTTimeout9TIME_ENDFin par timer10OK_PARTIALSuccès partiel
Attributs:

UID: Identifiant unique de la commande
ActionResultInt: Code de résultat
Motivo: Description de l'erreur (si échec)
ParamRet: Paramètres de retour (variables)

Shutdown
Le serveur Voyager va se fermer.
json{
  "Event": "ShutDown",
  "Timestamp": 1548806904.00159,
  "Host": "hal9000",
  "Inst": 1
}
⚠️ Action: Fermer immédiatement le client et gérer la déconnexion proprement.
Événements Dashboard
Activés uniquement si le client utilise RemoteSetDashboardMode.
ControlData
État complet du système (toutes les 2s).
json{
  "Event": "ControlData",
  "Timestamp": 1564675036.22405,
  "Host": "hal9000",
  "Inst": 1,
  "TI": "2019-08-02 19:24:32",
  "TIUTC": "2019-08-02 17:24:32",
  "VOYSTAT": 2,
  "SETUPCONN": true,
  "CCDCONN": true,
  "CCDTEMP": -15.2,
  "CCDPOW": 45,
  "CCDSETP": -15,
  "CCDCOOL": true,
  "CCDSTAT": 5,
  "MNTCONN": true,
  "MNTPARK": false,
  "MNTRA": "02:49:50",
  "MNTDEC": "47° 20' 07\"",
  "MNTRAJ2000": "02:33:44",
  "MNTDECJ2000": "47° 31' 17\"",
  "MNTAZ": "331° 23' 32\"",
  "MNTALT": "-16° 09' 55\"",
  "MNTPIER": "pierEast",
  "MNTTFLIP": "09:08:40",
  "MNTSFLIP": 3,
  "MNTTRACK": true,
  "MNTSLEW": false,
  "AFCONN": true,
  "AFTEMP": 12.5,
  "AFPOS": 5234,
  "SEQTOT": 7200,
  "SEQPARZ": 3600,
  "GUIDECONN": true,
  "GUIDESTAT": 2,
  "DITHSTAT": 0,
  "GUIDEX": -0.259,
  "GUIDEY": 0.039,
  "PLACONN": false,
  "SEQNAME": "M31_LRGB",
  "SEQSTART": "20:30:00",
  "SEQREMAIN": "02:45:30",
  "SEQEND": "23:15:30",
  "RUNSEQ": "M31_LRGB.s2q",
  "RUNDS": "",
  "ROTCONN": true,
  "ROTPA": 123.5,
  "ROTSKYPA": 123.8,
  "ROTISROT": false,
  "DOMECONN": true,
  "DOMEPA": 185.3,
  "DOMEISMOV": false,
  "DOMESHUTTER": "shutterOpen"
}
Champs principaux:
ChampTypeDescriptionVOYSTATint0=STOPPED, 1=IDLE, 2=RUN, 3=ERROR, 4=UNDEFINED, 5=WARNINGCCDTEMPfloatTempérature capteur (°C)CCDPOWintPuissance Peltier (%)CCDSTATintÉtat refroidissement (voir tableau)MNTPIERstringpierWest (avant méridien), pierEast (après)MNTSFLIPint0=NotNeeded, 1=ToDo, 2=Running, 3=Done, 4=Fork, 5=ErrorGUIDESTATint0=STOPPED, 1=SETTLING, 2=RUNNING, 3=TIMEOUTAFPOSintPosition focuser (steps)ROTPAfloatPosition angle rotateur (degrés)
Valeurs spéciales:
-123456789 = OFF (contrôle désactivé ou non présent)
+123456789 = ERROR (erreur ou valeur non disponible)
-1         = UNKNOW (valeur inconnue pour ce contrôle)
États de refroidissement (CCDSTAT):
CodeÉtatDescription0INITInitialisation1UNDEFIndéfini2NO_COOLERPas de refroidissement3OFFÉteint4COOLINGRefroidissement en cours5COOLEDTempérature atteinte6TIMEOUTTimeout7WARMUP_RUNNINGRéchauffement en cours8WARMUP_ENDRéchauffement terminé9ERRORErreur
NewJPGReady
Image preview Base64 (uniquement Dashboard).
json{
  "Event": "NewJPGReady",
  "Timestamp": 1564313171.92553,
  "Host": "hal9000",
  "Inst": 1,
  "File": "C:\\...\\TestShot_20190728_112558.fit",
  "SequenceTarget": "M31",
  "TimeInfo": "2019-07-28 11:25:58",
  "TimeInfoUTC": "2019-07-28 09:25:58",
  "Expo": 300,
  "Bin": 1,
  "Filter": "Luminance",
  "HFD": 4.53,
  "StarIndex": 8.21,
  "PixelDimX": 2048,
  "PixelDimY": 1024,
  "Base64Data": "/9j/4AAQSkZJRgABAQEAYABgAAD/..."
}
Utilisation Base64:
html<img src="data:image/jpeg;base64,/9j/4AAQSkZJRgABAQEAYABgAAD/...">
ShotRunning
Progression de l'exposition en cours (toutes les 1s).
json{
  "Event": "ShotRunning",
  "Timestamp": 1564498706.03752,
  "Host": "hal9000",
  "Inst": 1,
  "File": "TestShot_20190730_145825.fit",
  "Expo": 300,
  "Elapsed": 127,
  "ElapsedPerc": 42,
  "Status": 1
}
Status:

0 = IDLE (pas d'exposition)
1 = EXPOSE (exposition en cours)
2 = DOWNLOAD (téléchargement capteur → PC)
3 = WAIT_JPG (génération preview JPG)
4 = ERROR (erreur caméra)

WeatherAndSafetyMonitorData
Conditions météo et sécurité (toutes les 30s, Dashboard uniquement).
json{
  "Event": "WeatherAndSafetyMonitorData",
  "Timestamp": 1653781759.49165,
  "Host": "ORIONE",
  "Inst": 1,
  "WSConnected": true,
  "SMConnected": true,
  "SMStatus": "SAFE",
  "WSCloud": "CLEAR",
  "WSRain": "DRY",
  "WSWind": "CALM",
  "WSLight": "DARK"
}
Valeurs possibles:

SMStatus: SAFE, UNSAFE, ""
WSCloud: UNKNOW, CLEAR, CLOUDY, VERY_CLOUDY
WSRain: UNKNOW, DRY, WET, RAIN
WSWind: UNKNOW, CALM, WINDY, VERY_WINDY
WSLight: UNKNOW, DARK, LIGHT, VERY_LIGHT

AutoFocusResult
Résultat d'un autofocus (Dashboard uniquement).
json{
  "Event": "AutoFocusResult",
  "Timestamp": 1580817847.51588,
  "Host": "hal9000",
  "Inst": 1,
  "IsEmpty": false,
  "Done": true,
  "Position": 53149,
  "HFD": 5.00713,
  "StarPosition": {"X": 421, "Y": 796},
  "DoneTime": 1580817847.49987,
  "Duration": "00:00:07",
  "FocusTemp": 0.96,
  "PercDev": 0,
  "LastError": "",
  "FilterIndex": 0,
  "FilterColor": "#FFFFFF"
}
LogEvent
Ligne de log Voyager (si activé).
json{
  "Event": "LogEvent",
  "Timestamp": 1564498706.03752,
  "Host": "hal9000",
  "Inst": 1,
  "TimeInfo": 1564498706.03752,
  "Type": 2,
  "Text": "Camera connected successfully"
}
Type:

1 = DEBUG
2 = INFO
3 = WARNING
4 = CRITICAL
5 = TITLE
6 = SUBTITLE
7 = EVENT
8 = REQUEST
9 = EMERGENCY


Commandes
Les commandes utilisent le format JSON-RPC 2.0. Format: une ligne JSON terminée par CR LF.
Structure d'une commande
json{
  "method": "NomMethode",
  "params": {
    "UID": "guid-unique-obligatoire",
    "Param1": "valeur1",
    "Param2": 123
  },
  "id": 1
}
Éléments obligatoires

method: Nom de la commande (case-sensitive)
params.UID: GUID unique pour traçabilité
id: Compteur séquentiel (entier)

Générer un UID
javascript// JavaScript/Node.js
const { v4: uuidv4 } = require('uuid');
const uid = uuidv4();

// PHP
$uid = \Ramsey\Uuid\Uuid::uuid4()->toString();

// Python
import uuid
uid = str(uuid.uuid4())
Réponses
Réponse immédiate (synchrone):
json{"jsonrpc": "2.0", "result": 0, "id": 1}
Réponse d'erreur:
json{
  "jsonrpc": "2.0",
  "error": {
    "code": 1,
    "message": "Description de l'erreur"
  },
  "id": 1
}
Résultat final (asynchrone):
Event RemoteActionResult avec le même UID.
Commandes Générales
disconnect
Fermeture propre de la connexion.
json{
  "method": "disconnect",
  "id": 1
}
⚠️ Note: Aucun RemoteActionResult pour cette commande. Réponse immédiate uniquement.
Abort
Annule l'action en cours.
json{
  "method": "Abort",
  "params": {
    "IsHalt": false
  },
  "id": 2
}
Paramètres:

IsHalt: true = HALT ALL (tout arrêter), false = annuler action actuelle

⚠️ Note: Pas de RemoteActionResult, réponse immédiate uniquement.
RemoteActionAbort
Annule une action spécifique par son UID.
json{
  "method": "RemoteActionAbort",
  "params": {
    "UID": "e3f31937-8cac-4ac4-aad8-a0940f9cb2d4"
  },
  "id": 127
}
RemoteActionAbortAll
Annule toutes les actions en cours.
json{
  "method": "RemoteActionAbortAll",
  "params": {
    "UID": "e3f31937-8cac-4ac4-aad8-a0940f9cb2d4"
  },
  "id": 127
}
RemoteSetDashboardMode
Active le mode Dashboard (événements étendus).
json{
  "method": "RemoteSetDashboardMode",
  "params": {
    "UID": "eaea5429-f5a9-4012-bc9b-f109e605f5d8",
    "IsOn": true
  },
  "id": 2
}
RemoteSetLogEvent
Active/désactive la réception des logs.
json{
  "method": "RemoteSetLogEvent",
  "params": {
    "UID": "eaea5429-f5a9-4012-bc9b-f109e605f5d8",
    "IsOn": true,
    "Level": 0
  },
  "id": 2
}
Level:

0 = All (tous les logs)
1 = Emergency, Critical, Warning, Event, Title, Subtitle (pas Debug/Info)

Commandes Setup
RemoteSetupConnect
Connecte tous les équipements du profil actif.
json{
  "method": "RemoteSetupConnect",
  "params": {
    "UID": "69e329c8-c80d-416e-94f5-5862399446b6",
    "TimeoutConnect": 90
  },
  "id": 22
}
Paramètres:

TimeoutConnect (integer): Timeout en secondes

Workflow:
1. Réponse: {"result": 0}
2. Signal Code 15 (Setup Connect running)
3. ... connexion des équipements ...
4. RemoteActionResult (ActionResultInt: 4=OK, 5=ERROR)
RemoteSetupDisconnect
Déconnecte tous les équipements.
json{
  "method": "RemoteSetupDisconnect",
  "params": {
    "UID": "d4522a50-bf00-4bdd-acaa-19082578b9a0",
    "TimeoutDisconnect": 90
  },
  "id": 9384
}
RemoteGetStatus
Obtient le statut de Voyager.
json{
  "method": "RemoteGetStatus",
  "params": {
    "UID": "47a439a9-6453-477c-b5c4-529a93605867"
  },
  "id": 369
}
Retour dans ParamRet:
json{
  "VoyagerStatus": "RUN"
}
Valeurs possibles:

STOPPED: Application arrêtée, profil déconnecté
IDLE: Connecté, en attente
RUN: Action en cours
ERRORE: Erreur sur dernière action
UNDEFINED: État indéfini
WARNING: Avertissement sur dernière action

RemoteGetEnvironmentData
Récupère les informations du profil actif.
json{
  "method": "RemoteGetEnvironmentData",
  "params": {
    "UID": "eaea5429-f5a9-4012-bc9b-f109e605f5d8"
  },
  "id": 2
}
Retour ParamRet:
json{
  "Profile": "TestASINative",
  "Camera": "ASCOM Camera [ASCOM.Simulator.Camera]",
  "FilterWheel": "ASCOM Filter Wheel [FilterWheelSim.FilterWheel]",
  "Mount": "ASCOM Mount [ScopeSim.Telescope]",
  "Guide": "PHD2 Guide",
  "Planetarium": "",
  "PlateSolve": "PlateSolve2",
  "BlindSolve": "",
  "Focuser": "",
  "AutoFocus": "",
  "Rotator": "",
  "FlatDevice1": "",
  "FlatDevice2": "",
  "Dome": "",
  "ObservingConditions": "",
  "SQM": "",
  "SafetyMonitor": ""
}
RemoteGetVoyagerProfiles
Liste tous les profils disponibles.
json{
  "method": "RemoteGetVoyagerProfiles",
  "params": {
    "UID": "208BBAA7-218D-2B92-B648-B9FFBFCB04F1"
  },
  "id": 6
}
Retour:
json{
  "ParamRet": {
    "list": [
      {
        "guid": "77b88760-c5fe-4a1a-890f-795a0a420124",
        "name": "Default.v2y",
        "isactive": false
      },
      {
        "guid": "aa80a367-bd8f-40af-9e43-43652b8459af",
        "name": "Simulator RC12 Kai4022.v2y",
        "isactive": true
      }
    ]
  }
}
RemoteSetProfile
Charge un profil (si aucun profil actif).
json{
  "method": "RemoteSetProfile",
  "params": {
    "UID": "a53c6e8a-be1d-4c67-8ed7-df41c15d8923",
    "FileName": "SoloCamera.v2y"
  },
  "id": 19423
}
⚠️ Erreur si profil déjà connecté:
json{
  "jsonrpc": "2.0",
  "error": {
    "code": 1,
    "message": "Another Profile is actually connected"
  },
  "id": 19423
}
Commandes Caméra
RemoteCameraShot
Prend une photo.
json{
  "method": "RemoteCameraShot",
  "params": {
    "UID": "eaea5429-f5a9-4012-bc9b-f109e605f5d8",
    "Expo": 300,
    "Bin": 1,
    "IsROI": false,
    "ROITYPE": 0,
    "ROIX": 0,
    "ROIY": 0,
    "ROIDX": 0,
    "ROIDY": 0,
    "FilterIndex": 0,
    "ExpoType": 0,
    "SpeedIndex": 0,
    "ReadoutIndex": 0,
    "IsSaveFile": true,
    "FitFileName": "%%fitdir%%\\TestShot_20190130_001330.fit",
    "Gain": 78,
    "Offset": 22,
    "Parallelized": false
  },
  "id": 306
}
**RéessayerCContinuerModifierDocumentation Technique - Voyager Application Server Protocol (Suite)
Commandes Caméra (Suite)
RemoteCameraShot (détails des paramètres)
Paramètres:
ParamètreTypeDescriptionExponumberTemps d'exposition en secondesBinintegerBinning (1, 2, 3, 4)IsROIbooleanUtiliser une région d'intérêt?ROITYPEintegerType de ROI (voir tableau)ROIXintegerOrigine X en pixels (si ROI custom)ROIYintegerOrigine Y en pixelsROIDXintegerLargeur en pixelsROIDYintegerHauteur en pixelsFilterIndexintegerIndex filtre (0-based, depuis config)ExpoTypeinteger0=LIGHT, 1=BIAS, 2=DARK, 3=FLATSpeedIndexintegerIndex vitesse/ISOReadoutIndexintegerIndex mode lectureIsSaveFilebooleanToujours trueFitFileNamestringNom du fichier (avec chemins spéciaux)GainintegerGain CMOS (voir valeurs spéciales)OffsetintegerOffset CMOSParallelizedbooleanExécution parallèle (déconseillé)
ROITYPE:
CodeTypeDescription-1CustomParamètres ROIX, ROIY, ROIDX, ROIDY utilisés0FullFrameFrame complet (défaut)1HalfFrame1/2 du capteur2QuarterFrame1/4 du capteur31/8 Frame1/8 du capteur41/16 Frame1/16 du capteur5Custom CenteredTaille custom centrée (ROIDX/ROIDY utilisés)
Chemins spéciaux:

%%fitdir%%: Répertoire FIT par défaut de Voyager
%%sequencedir%%: Répertoire séquences

Valeurs spéciales Gain/Offset (CMOS):
ValeurConstanteDescription-2147483648GAIN_OFFSET_NULLNe pas modifier (Integer.MinValue)-900000GAIN_OFFSET_PRESETUtiliser preset du profil-800000GAIN_OFFSET_ACTUALUtiliser valeur actuelle[0-...]-Valeur numérique spécifique
Workflow complet:
1. Envoi commande → {"result": 0}
2. Signal Code 18 (Shot running)
3. ShotRunning events (progression 1/s)
4. NewFITReady event (fichier sauvegardé)
5. Signal Code 2 (Queue vide)
6. RemoteActionResult (avec DownloadAndSaveTime)
Retour ParamRet:
json{
  "DownloadAndSaveTime": 3.0471478
}
RemoteGetCCDTemperature
Lit la température du capteur.
json{
  "method": "RemoteGetCCDTemperature",
  "params": {
    "UID": "24a92e1e-7383-4854-9c36-dbc77351836f"
  },
  "id": 173
}
Retour:
json{
  "ParamRet": {
    "CCDTemp": -15.2
  }
}
RemoteCooling
Contrôle le refroidissement.
json{
  "method": "RemoteCooling",
  "params": {
    "UID": "37f4962a-73c5-44f5-80e1-d29f029f49a9",
    "IsSetPoint": true,
    "IsCoolDown": false,
    "IsASync": true,
    "IsWarmup": false,
    "IsCoolerOFF": false,
    "Temperature": -25
  },
  "id": 84
}
Modes:

IsSetPoint=true: Utilise rampe firmware caméra
IsCoolDown=true: Utilise rampe Voyager (config profil)
IsWarmup=true: Réchauffement selon rampe Voyager
IsCoolerOFF=true: Éteint le Peltier
IsASync=true: Attend la fin (false = retour immédiat)

RemoteGetCCDSizeInfo
Récupère les dimensions du capteur.
json{
  "method": "RemoteGetCCDSizeInfo",
  "params": {
    "UID": "24a92e1e-7383-4854-9c36-dbc77351836f"
  },
  "id": 173
}
Retour:
json{
  "ParamRet": {
    "DX": 2048,
    "DY": 2048,
    "PixelSize": 7.4
  }
}
RemoteGetCCDConfiguration
Configuration caméra (type, capacités).
json{
  "method": "RemoteGetCCDConfiguration",
  "params": {
    "UID": "94ac2036-0e2e-49f4-a56b-268fd43d3072"
  },
  "id": 7304
}
Retour:
json{
  "ParamRet": {
    "IsBayerCamera": true,
    "HaveGainCapability": true,
    "HaveOffsetCapability": true
  }
}
Commandes Filtres
RemoteGetFilterConfiguration
Liste des filtres configurés.
json{
  "method": "RemoteGetFilterConfiguration",
  "params": {
    "UID": "cc7b1c6d-48a6-418f-a02b-2e8f1ece1750"
  },
  "id": 4840
}
Retour:
json{
  "ParamRet": {
    "FilterNum": 5,
    "Filter1_Name": "L",
    "Filter1_MagMin": 4,
    "Filter1_MagMax": 7,
    "Filter1_Offset": 0,
    "Filter2_Name": "R",
    "Filter2_MagMin": 4,
    "Filter2_MagMax": 7,
    "Filter2_Offset": 0,
    "Filter3_Name": "G",
    "Filter3_MagMin": 4,
    "Filter3_MagMax": 7,
    "Filter3_Offset": 0,
    "Filter4_Name": "B",
    "Filter4_MagMin": 4,
    "Filter4_MagMax": 7,
    "Filter4_Offset": 0,
    "Filter5_Name": "HA",
    "Filter5_MagMin": 4,
    "Filter5_MagMax": 7,
    "Filter5_Offset": 0
  }
}
⚠️ IMPORTANT: Les filtres sont retournés en base 1 (Filter1, Filter2...) mais les commandes utilisent des index en base 0 (FilterIndex: 0, 1, 2...).
Correspondance:

Filter1 → FilterIndex: 0
Filter2 → FilterIndex: 1
Filter3 → FilterIndex: 2
etc.

RemoteFilterChangeTo
Change le filtre actif.
json{
  "method": "RemoteFilterChangeTo",
  "params": {
    "UID": "82f79427-d192-4b09-81ed-0d363d96d6de",
    "FilterIndex": 2
  },
  "id": 2607
}
RemoteFilterGetActual
Récupère l'index du filtre actuel.
json{
  "method": "RemoteFilterGetActual",
  "params": {
    "UID": "ffc14de0-feee-4417-bb28-c4410c2c1d0d"
  },
  "id": 3762
}
Retour:
json{
  "ParamRet": {
    "FilterIndex": 2
  }
}
Valeur -1 si pas de roue à filtres.
RemoteGetReadoutConfiguration
Liste des modes de lecture.
json{
  "method": "RemoteGetReadoutConfiguration",
  "params": {
    "UID": "94ac2036-0e2e-49f4-a56b-268fd43d3072"
  },
  "id": 7304
}
Retour:
json{
  "ParamRet": {
    "ReadoutNum": 1,
    "Readout1_Name": "Default",
    "Readout1_Index": 0
  }
}
RemoteGetSpeedConfiguration
Liste des vitesses/ISO.
json{
  "method": "RemoteGetSpeedConfiguration",
  "params": {
    "UID": "c012d391-3a7a-4cc3-9dc6-9593e4812d36"
  },
  "id": 7904
}
Retour:
json{
  "ParamRet": {
    "SpeedNum": 5,
    "Speed1_Name": "ISO 100",
    "Speed1_Index": 0,
    "Speed2_Name": "ISO 200",
    "Speed2_Index": 1,
    "Speed3_Name": "ISO 400",
    "Speed3_Index": 2,
    "Speed4_Name": "ISO 800",
    "Speed4_Index": 3,
    "Speed5_Name": "ISO 1600",
    "Speed5_Index": 4
  }
}
Commandes Monture
RemotePrecisePointTarget
Pointage précis avec plate solving.
json{
  "method": "RemotePrecisePointTarget",
  "params": {
    "UID": "eaea5429-f5a9-4012-bc9b-f109e605f5d8",
    "IsText": false,
    "RA": 12.5,
    "DEC": 45.3,
    "RAText": "",
    "DECText": "",
    "Parallelized": false
  },
  "id": 2
}
Formats de coordonnées:
Mode IsText=false (double):

RA en heures décimales (0-24)
DEC en degrés décimaux (-90 à +90)

Mode IsText=true (string):

RAText: "HH MM SS.SSS" (ex: "12 30 45.123")
DECText: "DD MM SS.SSS" (ex: "45 20 30.500")

Retour ParamRet:
json{
  "ActionResult": 1
}
Codes ActionResult:

0 = FAILED (échec)
1 = OK_IN_RANGE (succès, précision dans limites)
2 = OK_OUT_RANGE (succès, mais hors limites précision)
3 = OK_PLATE_SOLVING_DISABLED (succès, pas de plate solving)

RemotePrecisePointTargetAndPA
Pointage avec angle de position rotateur.
json{
  "method": "RemotePrecisePointTargetAndPA",
  "params": {
    "UID": "eaea5429-f5a9-4012-bc9b-f109e605f5d8",
    "IsText": false,
    "RA": 12.5,
    "DEC": 45.3,
    "RAText": "",
    "DECText": "",
    "PA": 123.5,
    "PATollerance": 3.0,
    "IsSkyPA": true,
    "MantainImageOrientation": true
  },
  "id": 2
}
Paramètres spécifiques:

PA: Angle de position cible (0-360°)
PATollerance: Tolérance acceptable (+/- degrés)
IsSkyPA: true = vérifier PA résolu sur ciel (plate solving)
MantainImageOrientation: true = maintenir orientation après méridien

RemoteGotoAltAz
Pointage en coordonnées Alt/Az.
json{
  "method": "RemoteGotoAltAz",
  "params": {
    "UID": "eaea5429-f5a9-4012-bc9b-f109e605f5d8",
    "ALT": 45.0,
    "AZ": 180.0,
    "SettleTime": 5
  },
  "id": 2
}
Paramètres:

ALT: Altitude en degrés (0-90)
AZ: Azimuth en degrés (0-360)
SettleTime: Temps d'attente après goto (secondes)

RemoteMountFastCommand
Commandes rapides monture.
json{
  "method": "RemoteMountFastCommand",
  "params": {
    "UID": "eaea5429-f5a9-4012-bc9b-f109e605f5d8",
    "CommandType": 1
  },
  "id": 2
}
CommandType:

1 = Track On
2 = Track Off
3 = Park
4 = Unpark
5 = Goto Near Zenith
6 = Home

RemotePulseGuide
Guidage par impulsions.
json{
  "method": "RemotePulseGuide",
  "params": {
    "UID": "eaea5429-f5a9-4012-bc9b-f109e605f5d8",
    "Direction": 0,
    "Duration": 1250,
    "Parallelized": false
  },
  "id": 2
}
Direction:

0 = guideNorth (+ DEC/altitude)
1 = guideSouth (- DEC/altitude)
2 = guideEast (+ RA/azimuth)
3 = guideWest (- RA/azimuth)

Duration: En millisecondes
RemoteMountStatusGetInfo
État détaillé de la monture.
json{
  "method": "RemoteMountStatusGetInfo",
  "params": {
    "UID": "e10eacc4-1e60-44d0-bf4a-eab729cf5d5c"
  },
  "id": 14
}
Retour:
json{
  "ParamRet": {
    "IsMountConnected": true,
    "RA": "06:07:12",
    "DEC": "90° 00' 00\"",
    "RAJ2000": "23:59:24",
    "DECJ2000": "89° 52' 10\"",
    "IsParked": false,
    "Altitude": "45° 00' 49\"",
    "Azimuth": "360° 00' 00\"",
    "Pier": "pierWest",
    "TimeToFlip": "-04:18:36",
    "FlipStatus": 0,
    "IsTracking": true,
    "IsSlewing": false,
    "Latitude": 45.0136111111111,
    "Longitude": 6.93972222222222,
    "Elevation": 1000
  }
}
FlipStatus:

0 = Not needed (pierWest)
1 = To do (méridien à faire)
2 = Running (en cours)
3 = Done (pierEast)
4 = Unmanageable (monture FORK)
5 = ERROR

Commandes Focuser
RemoteFocuserMoveTo
Déplace le focuser en position absolue ou relative.
json{
  "method": "RemoteFocuserMoveTo",
  "params": {
    "UID": "84a92e1e-7383-4854-9c36-dbc77351836f",
    "IsAbsoluteMove": true,
    "NewPosition": 5000,
    "MoveDirection": 0,
    "IsBLCompensation": true,
    "BLCompVersus": 1,
    "BLCompStep": 50,
    "IsFinalPositionCheck": true
  },
  "id": 72
}
Paramètres:

IsAbsoluteMove: true = position absolue, false = offset relatif
NewPosition: Position cible (ou offset si relatif)
MoveDirection: 0=OUT, 1=IN (si offset, ignoré si absolu)
IsBLCompensation: Appliquer compensation backlash?
BLCompVersus: Direction compensation (0=OUT, 1=IN)
BLCompStep: Steps de compensation
IsFinalPositionCheck: Vérifier position finale?

RemoteFocuserOffset
Déplacement relatif simplifié.
json{
  "method": "RemoteFocuserOffset",
  "params": {
    "UID": "84a92e1e-7383-4854-9c36-dbc77351836f",
    "Offset": -200,
    "IsBLCompensation": true,
    "BLCompVersus": 1,
    "BLCompStep": 50,
    "IsFinalPositionCheck": true
  },
  "id": 73
}
Offset: Valeur signée (+ ou -)
RemoteFocusEx
Autofocus avec différentes méthodes.
json{
  "method": "RemoteFocusEx",
  "params": {
    "UID": "dd486bd0-b141-43e8-a401-4871cea992f4",
    "FocusMode": 4,
    "filtroFuocoIndex": 0,
    "IsWDMaxHFD": true,
    "WDMaxHFDLimit": 5.0,
    "IsRetryFocusOnWD": true,
    "PreviousPosition": -1,
    "StarRAJ2000Str": "",
    "StarDECJ2000Str": "",
    "IsGoBack": false,
    "IsOnlyPointingStar": false
  },
  "id": 1792
}
FocusMode:
CodeModeDescription0Focus StarFocus sur étoile spécifiée (coords obligatoires)1AcquireStar FMFocusMax AcquireStar (FocusMax requis)2On PlaceFocus sur place actuelle (pas de goto)3Voyager RoboStarVoyager sélectionne étoile et pointe4Voyager LocalFieldIA sur full frame (recommandé)5Only PointingPointe étoile sans focus
Paramètres:

filtroFuocoIndex: Index filtre pour focus
IsWDMaxHFD: Watchdog sur HFD max?
WDMaxHFDLimit: Limite HFD acceptable (pixels), -1 si inconnu
IsRetryFocusOnWD: Réessayer si watchdog déclenché?
PreviousPosition: Position précédente (-1 si inconnu)
StarRAJ2000Str / StarDECJ2000Str: Coords J2000 format "HH MM SS.SSS"
IsGoBack: Revenir à position initiale après focus?
IsOnlyPointingStar: Juste pointer, pas de focus?

RemoteFocusInject
Injecte un autofocus dans une séquence en cours.
json{
  "method": "RemoteFocusInject",
  "params": {
    "UID": "dd486bd0-b141-43e8-a401-4871cea992f4",
    "filtroFuocoIndex": 0
  },
  "id": 1792
}
⚠️ Fonctionne uniquement si une séquence est active.
Commandes Rotateur
RemoteRotatorMoveTo
Rotation vers angle de position.
json{
  "method": "RemoteRotatorMoveTo",
  "params": {
    "UID": "a53c6e8a-be1d-4c67-8ed7-df41c15d8923",
    "PA": 90.0,
    "IsWaitAfter": true,
    "WaitAfterSeconds": 5
  },
  "id": 9423
}
Paramètres:

PA: Position angle cible (0-360°)
IsWaitAfter: Attendre après fin rotation?
WaitAfterSeconds: Temps d'attente (secondes)

RemoteRotatorSync
Synchronise le rotateur.
json{
  "method": "RemoteRotatorSync",
  "params": {
    "UID": "a53c6e8a-be1d-4c67-8ed7-df41c15d8923",
    "PA": 90.0,
    "IsReset": false
  },
  "id": 9423
}
Paramètres:

PA: Angle à synchroniser
IsReset: true = reset position mécanique, false = sync sur PA

Commandes Plate Solving
RemoteSolveActualPosition
Résout la position actuelle du télescope.
json{
  "method": "RemoteSolveActualPosition",
  "params": {
    "UID": "d4522a50-bf00-4bdd-acaa-19082578b9a0",
    "IsBlind": false,
    "IsSync": true
  },
  "id": 9384
}
Paramètres:

IsBlind: true = blind solving, false = plate solving
IsSync: Synchroniser monture sur coordonnées résolues?

Workflow:
1. Prise shot sync
2. NewFITReady event
3. Signal Code 25
4. Solving...
5. RemoteActionResult avec coords résolues
Retour:
json{
  "ParamRet": {
    "IsSolved": true,
    "LastError": "",
    "RA": 7.291651816591,
    "DEC": 89.7363320162195,
    "PA": 208.428127473733
  }
}
RemoteSolveFITFile
Résout un fichier FITS existant.
json{
  "method": "RemoteSolveFITFile",
  "params": {
    "UID": "d4522a50-bf00-4bdd-acaa-19082578b9a0",
    "FileName": "C:\\path\\to\\file.fit",
    "IsBlind": false
  },
  "id": 9384
}
⚠️ Note: Utiliser \\ pour les chemins Windows.
Commandes Séquences
RemoteSequence
Lance une séquence d'observation.
json{
  "method": "RemoteSequence",
  "params": {
    "UID": "eaea5429-f5a9-4012-bc9b-f109e605f5d8",
    "SequenceFile": "M31_LRGB.s2q",
    "StartFlag": 0
  },
  "id": 2
}
Paramètres:

SequenceFile: Nom du fichier (doit être dans répertoire par défaut)
StartFlag: Flags de démarrage (cumulables par addition)

StartFlag:
CodeDescription0Normal (tout actif)1Supprimer Precise Pointing initial2Supprimer Focus initial4Supprimer calibration guidage8Supprimer Precise Pointing avant 1er shot
Exemple cumulé:
StartFlag = 1 + 2 + 8 = 11
→ Pas de pointing initial, pas de focus initial, pas de pointing avant shot
RemoteGetListAvalaibleSequence
Liste des séquences disponibles.
json{
  "method": "RemoteGetListAvalaibleSequence",
  "params": {
    "UID": "eaea5429-f5a9-4012-bc9b-f109e605f5d8"
  },
  "id": 2
}
Retour:
json{
  "ParamRet": {
    "list": [
      "M31_LRGB.s2q",
      "M33_Mosaic.s2q",
      "Calibration.s2q"
    ]
  }
}
RemoteGetListAvalaibleSequenceEx
Liste avec infos profil associé.
json{
  "method": "RemoteGetListAvalaibleSequenceEx",
  "params": {
    "UID": "98129170-e267-4f8b-9021-4e773b2889de",
    "ProfileName": "TestFlatNoMount.v2y"
  },
  "id": 22
}
Paramètres:

ProfileName: Filtre par profil ("" = toutes)

Retour:
json{
  "ParamRet": {
    "list": [
      {
        "name": "SequenzaBase_TestFlatNoMount.s2q",
        "filename": "C:\\...\\SequenzaBase_TestFlatNoMount.s2q",
        "profilename": "TestFlatNoMount.v2y"
      }
    ]
  }
}
Commandes DragScript
RemoteDragScript
Lance un DragScript (mode asynchrone).
json{
  "method": "RemoteDragScript",
  "params": {
    "UID": "eaea5429-f5a9-4012-bc9b-f109e605f5d8",
    "DragScriptFile": "OpenRoof.vos",
    "StartNodeUID": ""
  },
  "id": 2
}
Paramètres:

DragScriptFile: Nom fichier (répertoire par défaut)
StartNodeUID: UID nœud de départ ("" = premier)

⚠️ Note: Pas de RemoteActionResult, vérifier result immédiat.
RemoteDragScriptSelfContained
Lance un DragScript (mode synchrone, avec résultat).
json{
  "method": "RemoteDragScriptSelfContained",
  "params": {
    "UID": "eaea5429-f5a9-4012-bc9b-f109e605f5d8",
    "DragScriptFile": "OpenRoof.vos"
  },
  "id": 2
}
Différence avec RemoteDragScript:

Attend la fin du script
Retourne RemoteActionResult
Pas d'affichage dans interface DragScript Voyager
Recommandé pour petits scripts utilitaires

RemoteGetListAvalaibleDragScript
Liste des DragScripts disponibles.
json{
  "method": "RemoteGetListAvalaibleDragScript",
  "params": {
    "UID": "eaea5429-f5a9-4012-bc9b-f109e605f5d8"
  },
  "id": 2
}
Retour:
json{
  "ParamRet": {
    "list": [
      "OpenRoof.vos",
      "CloseRoof.vos",
      "EmergencyExit.vos"
    ]
  }
}
Commandes Utilitaires
RemoteCreateDir
Crée un répertoire.
json{
  "method": "RemoteCreateDir",
  "params": {
    "UID": "62967a0f-3076-4b53-bfe2-028b37407075",
    "Dir": "%%sequencedir%%\\M31\\2024-12-25"
  },
  "id": 1544
}
Chemins spéciaux:

%%fitdir%%: Répertoire FIT
%%sequencedir%%: Répertoire séquences

RemoteRunExternal
Exécute script/application externe.
json{
  "method": "RemoteRunExternal",
  "params": {
    "UID": "a53c6e8a-be1d-4c67-8ed7-df41c15d8923",
    "FileName": "C:\\Scripts\\calibrate.bat",
    "Arguments": "--mode=full --target=M31",
    "TimeoutMilliseconds": 300000,
    "WaitFinish": true,
    "TryKillOnTimeout": true
  },
  "id": 19423
}
Paramètres:

FileName: Chemin complet de l'exécutable/script
Arguments: Arguments ligne de commande
TimeoutMilliseconds: Timeout (ms)
WaitFinish: Attendre la fin?
TryKillOnTimeout: Tuer processus si timeout?

RemoteSearchTarget
Recherche cible dans planétarium ou Simbad.
json{
  "method": "RemoteSearchTarget",
  "params": {
    "UID": "eaea5429-f5a9-4012-bc9b-f109e605f5d8",
    "Name": "M31",
    "SearchType": 0
  },
  "id": 2
}
SearchType:

0 = Planetarium connecté
1 = Simbad online

Retour:
json{
  "ParamRet": {
    "Result": 1,
    "LastError": "",
    "Name": "M31",
    "RAJ2000": "00 42 41,582",
    "DECJ2000": "41 15 59,97",
    "Info": [
      {"Key": "Type", "Value": "RéessayerCContinuerModifiermarkdown# Documentation Technique - Voyager Application Server Protocol (Suite 2)

### RemoteSearchTarget (suite)

**Retour:**
```json
{
  "ParamRet": {
    "Result": 1,
    "LastError": "",
    "Name": "M31",
    "RAJ2000": "00 42 41,582",
    "DECJ2000": "41 15 59,97",
    "Info": [
      {"Key": "Type", "Value": "Gx"},
      {"Key": "m", "Value": "3.40"},
      {"Key": "Dim", "Value": "189.0 x 61.0 '"},
      {"Key": "desc", "Value": "Andromeda Galaxy"}
    ]
  }
}
Result codes:

0 = NOT_FOUND
1 = FOUND
2 = ERROR

Commandes Flat Device
RemoteFlatDeviceCMD
Contrôle panneau plat.
json{
  "method": "RemoteFlatDeviceCMD",
  "params": {
    "UID": "e10eacc4-1e60-44d0-bf4a-eab729cf5d5c",
    "FlatDeviceIndex": 1,
    "CommandType": 5,
    "Brightness": 128
  },
  "id": 14
}
FlatDeviceIndex:

1 = Premier flat device
2 = Second flat device

CommandType:
CodeCommandeBrightness requis?1Open CoverNon (0)2Close CoverNon (0)3Light ONNon (0)4Light OFFNon (0)5Set BrightnessOui (0-255)
RemoteFlat
Lance séquence d'acquisition flats.
json{
  "method": "RemoteFlat",
  "params": {
    "UID": "3a7a6e74-5a67-4471-b0c5-1e7199bff755",
    "IsOnlyForRemote": true,
    "RemoteConfigurationFile": "test.s2f",
    "DataBase64": "pFbnZlbG...9wZT4NCg=="
  },
  "id": 161
}
Paramètres:

IsOnlyForRemote: Toujours true
RemoteConfigurationFile: Nom fichier config (.s2f)
DataBase64: Contenu fichier encodé Base64

Commandes Géolocalisation
RemoteRoboDataGetGeoDataCache
Récupère localisation observatoire.
json{
  "method": "RemoteRoboDataGetGeoDataCache",
  "params": {
    "UID": "5f896393-75ad-4ba0-a748-d3d8b7040eb9"
  },
  "id": 12
}
Retour:
json{
  "ParamRet": {
    "data": {
      "Latitudine": 45.0136111111111,
      "Longitudine": 6.93972222222222,
      "Elevation": 1000,
      "RemoteDateTime": 1686418111.16039,
      "TimeZoneHour": 2
    }
  }
}
Champs:

Latitudine: Latitude (degrés décimaux)
Longitudine: Longitude (degrés décimaux)
Elevation: Élévation (mètres)
RemoteDateTime: Heure locale (epoch)
TimeZoneHour: Fuseau horaire (heures depuis UTC)


RoboTarget - Gestion avancée
Licence requise: Advanced, Full
RoboTarget est le système de gestion de base de données de cibles astronomiques de Voyager. Toutes les commandes RoboTarget nécessitent une authentification MAC.
Principe de sécurité MAC
Message Authentication Code avec MD5:
javascript// Exemple en JavaScript
const crypto = require('crypto');

const sharedSecret = "leonardo";  // Secret partagé configuré dans Voyager
const uid = "d4a644d7-10d2-4904-9de4-9c1ec5cf6a6a";
const refGuid = "6c5553ef-3c11-4b40-a3e1-7cd008e08c35";

// Concaténation
const str = sharedSecret + uid + refGuid;
// "leonardod4a644d7-10d2-4904-9de4-9c1ec5cf6a6a6c5553ef-3c11-4b40-a3e1-7cd008e08c35"

// Hash MD5
const mac = crypto.createHash('md5').update(str).digest('hex');
// "0241332cd7da9ec94e5a839fcee41ab4"
python# Exemple en Python
import hashlib

shared_secret = "leonardo"
uid = "d4a644d7-10d2-4904-9de4-9c1ec5cf6a6a"
ref_guid = "6c5553ef-3c11-4b40-a3e1-7cd008e08c35"

str_to_hash = shared_secret + uid + ref_guid
mac = hashlib.md5(str_to_hash.encode()).hexdigest()
# "0241332cd7da9ec94e5a839fcee41ab4"
php// Exemple en PHP
$sharedSecret = "leonardo";
$uid = "d4a644d7-10d2-4904-9de4-9c1ec5cf6a6a";
$refGuid = "6c5553ef-3c11-4b40-a3e1-7cd008e08c35";

$str = $sharedSecret . $uid . $refGuid;
$mac = md5($str);
// "0241332cd7da9ec94e5a839fcee41ab4"
⚠️ Important: Le shared secret est configuré dans Voyager Setup → RoboTarget → Shared Secret.
Commandes RoboTarget
RemoteOpenRoboTargetGetTargetList
Liste toutes les cibles RoboTarget.
json{
  "method": "RemoteOpenRoboTargetGetTargetList",
  "params": {
    "UID": "d4a644d7-10d2-4904-9de4-9c1ec5cf6a6a",
    "MAC": "684660d3045ee9c2bbc626a4e5cc5155"
  },
  "id": 37
}
MAC: MD5(SharedSecret + UID)
Retour:
json{
  "ParamRet": {
    "list": [
      {
        "guid": "2d155808-ee20-4036-b595-8002330be5a0",
        "targetname": "M31",
        "tag": "Galaxies",
        "datecreation": 1644848898,
        "status": 0,
        "statusop": 2,
        "setname": "Autumn Targets",
        "settag": "Season",
        "profilename": "Default.v2y"
      }
    ]
  }
}
Champs:

guid: UID unique de la cible
targetname: Nom
tag: Tag utilisateur
datecreation: Date création (epoch)
status: 0=ENABLED, 1=DISABLED
statusop: -1=UNKNOW, 0=IDLE, 1=RUNNING, 2=FINISHED, 3=NO_EPHEM
setname: Nom du set contenant la cible
settag: Tag du set
profilename: Profil Voyager associé

RemoteOpenRoboTargetGetShotDoneList
Liste les images réalisées pour une cible.
json{
  "method": "RemoteOpenRoboTargetGetShotDoneList",
  "params": {
    "UID": "d4a644d7-10d2-4904-9de4-9c1ec5cf6a6a",
    "RefGuidTarget": "6c5553ef-3c11-4b40-a3e1-7cd008e08c35",
    "IsDeleted": false,
    "MAC": "0241332cd7da9ec94e5a839fcee41ab4"
  },
  "id": 37
}
MAC: MD5(SharedSecret + UID + RefGuidTarget)
Paramètres:

RefGuidTarget: GUID de la cible (depuis GetTargetList)
IsDeleted: true = images supprimées, false = actives

Retour:
json{
  "ParamRet": {
    "list": [
      {
        "guid": "120e5c72-9aae-4363-8fc4-f0105aa4c4b3",
        "datetimeshot": 1640715509,
        "datetimeshotutc": 1640711909,
        "filename": "M31_LRGB_LIGHT_L_300s_BIN1_-12C_001.FIT",
        "hfd": 6.45,
        "max": 65535,
        "mean": 18649,
        "min": 0,
        "path": "C:\\Voyager\\Sequence\\M31\\",
        "refguidsession": "cf996602-8e6b-4461-8cbf-81d813e9893f",
        "refguidshot": "73cead8d-4f75-4a15-8db3-bea3d0281343",
        "starindex": 20.65,
        "bin": 1,
        "filterindex": 0,
        "exposure": 300,
        "rating": 14,
        "isdeleted": false
      }
    ]
  }
}
Champs importants:

guid: UID du shot done
datetimeshot / datetimeshotutc: Timestamps
filename: Nom du fichier
path: Chemin (peut être vide)
hfd: Half Flux Diameter (qualité focus)
starindex: Index présence étoiles
rating: Note qualité (≤0 = non évalué, >0 = évalué)
isdeleted: Suppression logique

RemoteOpenRoboTargetSetShotDoneRating
Attribue une note de qualité.
json{
  "method": "RemoteOpenRoboTargetSetShotDoneRating",
  "params": {
    "UID": "d4a644d7-10d2-4904-9de4-9c1ec5cf6a6a",
    "ObjUID": "120e5c72-9aae-4363-8fc4-f0105aa4c4b3",
    "Mode": 0,
    "Rating": 85,
    "IsDeleted": false,
    "MAC": "644a08429b66cecfafa4d0251f576639"
  },
  "id": 37
}
MAC: MD5(SharedSecret + UID + ObjUID)
Paramètres:

ObjUID: GUID du shot / session / target / slot
Mode: Portée de l'action

0 = By Shot (un seul shot)
1 = By Session (toute la session)
2 = By Target (toute la cible)
3 = By Slot (slot de la cible)


Rating: Note (entier, >0 = meilleur)
IsDeleted: Appliquer aux shots supprimés?

RemoteOpenRoboTargetRemoveShotDone
Suppression logique de shots.
json{
  "method": "RemoteOpenRoboTargetRemoveShotDone",
  "params": {
    "UID": "d4a644d7-10d2-4904-9de4-9c1ec5cf6a6a",
    "ObjUID": "120e5c72-9aae-4363-8fc4-f0105aa4c4b3",
    "Mode": 0,
    "RatingMode": 1,
    "RatingLimit": 50,
    "MAC": "644a08429b66cecfafa4d0251f576639"
  },
  "id": 37
}
Paramètres:

ObjUID: GUID shot/session/target/slot/set
Mode: 0=Shot, 1=Session, 2=Target, 3=Slot, 4=Set
RatingMode:

0 = None (tous)
1 = Lower Limit (rating < RatingLimit)
2 = Greater Limit (rating > RatingLimit)


RatingLimit: Seuil de rating

⚠️ Note: Ne supprime PAS physiquement le fichier, juste flag logique.
RemoteOpenRoboTargetRestoreShotDone
Restauration de shots supprimés.
json{
  "method": "RemoteOpenRoboTargetRestoreShotDone",
  "params": {
    "UID": "d4a644d7-10d2-4904-9de4-9c1ec5cf6a6a",
    "ObjUID": "120e5c72-9aae-4363-8fc4-f0105aa4c4b3",
    "Mode": 0,
    "RatingMode": 0,
    "RatingLimit": 0,
    "MAC": "644a08429b66cecfafa4d0251f576639"
  },
  "id": 37
}
Mêmes paramètres que RemoveShotDone.
RemoteOpenRoboTargetUpdateBulkShotDone
Mise à jour en masse.
json{
  "method": "RemoteOpenRoboTargetUpdateBulkShotDone",
  "params": {
    "UID": "88588e1b-bd6e-4008-a27e-9c0be2abd242",
    "SrcList": [
      {
        "RefGuidShotDone": "13c32f52-1649-4184-82fe-3eebb25005d5",
        "Rating": 75,
        "IsToDelete": false
      },
      {
        "RefGuidShotDone": "271a053e-04e7-4747-b1b1-b0ab20351c55",
        "Rating": 20,
        "IsToDelete": true
      }
    ],
    "IsDeleted": false,
    "MAC": "5f98c3681a26bb2c1415e3342d46014c"
  },
  "id": 31
}
MAC: MD5(SharedSecret + UID)
SrcList: Array d'objets

RefGuidShotDone: GUID du shot
Rating: Nouvelle note
IsToDelete: Marquer comme supprimé?

RemoteOpenRoboTargetRemoveShotDoneByFileName
Suppression par nom de fichier.
json{
  "method": "RemoteOpenRoboTargetRemoveShotDoneByFileName",
  "params": {
    "UID": "d4a644d7-10d2-4904-9de4-9c1ec5cf6a6a",
    "FileNameFIT": "M31_LRGB_LIGHT_L_300s_BIN1_-12C_003.FIT",
    "MAC": "f6b655c1e990f321c1b2238efe70a971"
  },
  "id": 37
}
MAC: MD5(SharedSecret + UID + FileNameFIT)
RemoteOpenRoboTargetRestoreShotDoneByFileName
Restauration par nom de fichier.
json{
  "method": "RemoteOpenRoboTargetRestoreShotDoneByFileName",
  "params": {
    "UID": "d4a644d7-10d2-4904-9de4-9c1ec5cf6a6a",
    "FileNameFIT": "M31_LRGB_LIGHT_L_300s_BIN1_-12C_003.FIT",
    "MAC": "f6b655c1e990f321c1b2238efe70a971"
  },
  "id": 37
}
RemoteOpenRoboTargetSetShotDoneRatingByFileName
Rating par nom de fichier.
json{
  "method": "RemoteOpenRoboTargetSetShotDoneRatingByFileName",
  "params": {
    "UID": "d4a644d7-10d2-4904-9de4-9c1ec5cf6a6a",
    "FileNameFIT": "M31_LRGB_LIGHT_L_300s_BIN1_-12C_003.FIT",
    "Rating": 90,
    "MAC": "f6b655c1e990f321c1b2238efe70a971"
  },
  "id": 37
}

Workflow d'intégration
Workflow complet type
1. CONNEXION
   └─> Ouverture socket TCP port 5950
   └─> Démarrer thread lecture socket
   └─> Démarrer timer polling (5s)
   └─> Recevoir Version event

2. AUTHENTIFICATION (si requise)
   └─> AuthenticateUserBase ou AuthenticateUserTicket
   └─> Vérifier réponse (authbase/authticket ou error)

3. INITIALISATION
   └─> RemoteSetDashboardMode (si monitoring)
   └─> RemoteSetLogEvent (si logs)
   └─> RemoteGetStatus
   └─> RemoteGetEnvironmentData
   └─> RemoteSetProfile (si besoin)

4. CONNEXION SETUP
   └─> RemoteSetupConnect
   └─> Attendre RemoteActionResult (ActionResultInt=4)
   └─> Vérifier ControlData.SETUPCONN=true

5. OBSERVATION
   Option A: Shot simple
   └─> RemoteCameraShot
   └─> Suivre ShotRunning events
   └─> Recevoir NewFITReady
   └─> Recevoir RemoteActionResult

   Option B: Séquence
   └─> RemotePrecisePointTarget
   └─> RemoteFocusEx
   └─> RemoteSequence
   └─> Suivre progression via ControlData
   └─> Recevoir NewFITReady pour chaque shot

6. FIN SESSION
   └─> RemoteSetupDisconnect
   └─> Attendre RemoteActionResult
   └─> disconnect
   └─> Fermer socket
Exemple session complète
javascript// Pseudo-code simplifié

// 1. Connexion
const socket = new TcpSocket('192.168.1.100', 5950);
const polling = setInterval(() => sendPolling(), 5000);

socket.on('data', (data) => {
  clearInterval(polling);
  const event = JSON.parse(data);
  handleEvent(event);
  polling = setInterval(() => sendPolling(), 5000);
});

// 2. Version reçue
function handleVersion(event) {
  console.log(`Voyager ${event.VOYVersion} connecté`);
  authenticate();
}

// 3. Authentification
function authenticate() {
  const cmd = {
    method: "AuthenticateUserBase",
    params: {
      UID: generateUID(),
      Base: btoa("admin:password")
    },
    id: commandId++
  };
  sendCommand(cmd);
}

// 4. Setup
function setupConnection() {
  sendCommand({
    method: "RemoteSetupConnect",
    params: {
      UID: generateUID(),
      TimeoutConnect: 90
    },
    id: commandId++
  });
}

// 5. Observation
function takePhoto() {
  sendCommand({
    method: "RemoteCameraShot",
    params: {
      UID: generateUID(),
      Expo: 300,
      Bin: 1,
      IsROI: false,
      ROITYPE: 0,
      FilterIndex: 0,
      ExpoType: 0,
      FitFileName: "%%fitdir%%\\obs_" + Date.now() + ".fit",
      Gain: -900000,
      Offset: -900000
    },
    id: commandId++
  });
}

// 6. Handler événements
function handleEvent(event) {
  switch(event.Event) {
    case 'Version':
      handleVersion(event);
      break;
    case 'Signal':
      console.log(`Signal: ${event.Code}`);
      break;
    case 'NewFITReady':
      console.log(`Image prête: ${event.File}`);
      downloadFile(event.File);
      break;
    case 'RemoteActionResult':
      handleActionResult(event);
      break;
    case 'ShotRunning':
      updateProgress(event.ElapsedPerc);
      break;
  }
}
Gestion du polling
javascriptclass VoyagerClient {
  constructor(host, port) {
    this.socket = new TcpSocket(host, port);
    this.pollingTimer = null;
    this.setupPolling();
  }

  setupPolling() {
    this.resetPollingTimer();
  }

  resetPollingTimer() {
    if (this.pollingTimer) {
      clearTimeout(this.pollingTimer);
    }
    this.pollingTimer = setTimeout(() => {
      this.sendPolling();
    }, 5000);
  }

  sendPolling() {
    const polling = {
      Event: "Polling",
      Timestamp: Date.now() / 1000,
      Host: "client-host",
      Inst: 1
    };
    this.send(JSON.stringify(polling) + "\r\n");
    this.resetPollingTimer();
  }

  send(data) {
    this.socket.write(data);
    this.resetPollingTimer(); // Reset à chaque envoi
  }

  onData(callback) {
    this.socket.on('data', (data) => {
      this.resetPollingTimer(); // Reset à chaque réception
      callback(data);
    });
  }
}

Gestion des erreurs
Types d'erreurs
1. Erreur de connexion
javascript{
  "jsonrpc": "2.0",
  "error": {
    "code": 1,
    "message": "could not connect all controls : Camera Error"
  },
  "id": 3
}
2. Erreur d'action
javascript{
  "Event": "RemoteActionResult",
  "ActionResultInt": 5,
  "Motivo": "Focus Async Error (Error executing VCurve AutoFocus)",
  "ParamRet": {}
}
3. Timeout

Pas de réponse après 15s
Client doit détecter et reconnecter

4. Shutdown inattendu
javascript{
  "Event": "ShutDown"
}
Gestion recommandée
javascriptclass ErrorHandler {
  handleError(error) {
    if (error.jsonrpc && error.error) {
      // Erreur JSON-RPC immédiate
      console.error(`Erreur commande: ${error.error.message}`);
      this.notifyUser(error.error.message);
      return 'command_error';
    }
    
    if (error.Event === 'RemoteActionResult' && error.ActionResultInt === 5) {
      // Erreur d'action
      console.error(`Action échouée: ${error.Motivo}`);
      this.logError(error.UID, error.Motivo);
      return 'action_error';
    }
    
    if (error.Event === 'ShutDown') {
      // Serveur fermé
      console.warn('Serveur Voyager fermé');
      this.disconnect();
      return 'shutdown';
    }
    
    return 'unknown_error';
  }

  async retryWithBackoff(operation, maxRetries = 3) {
    for (let i = 0; i < maxRetries; i++) {
      try {
        return await operation();
      } catch (error) {
        if (i === maxRetries - 1) throw error;
        const delay = Math.pow(2, i) * 1000; // Backoff exponentiel
        await this.sleep(delay);
      }
    }
  }
}

Exemples pratiques
Exemple 1: Session d'observation simple
javascriptasync function simpleObservationSession() {
  // 1. Connexion
  const client = new VoyagerClient('192.168.1.100', 5950);
  await client.waitForVersion();
  
  // 2. Authentification
  await client.authenticate('admin', 'password');
  
  // 3. Activer Dashboard
  await client.setDashboardMode(true);
  
  // 4. Connecter Setup
  await client.setupConnect(90);
  
  // 5. Pointer cible
  await client.precisePointTarget({
    RA: 0.711, // M31 en heures
    DEC: 41.269 // en degrés
  });
  
  // 6. Autofocus
  await client.autoFocus({
    mode: 4, // LocalField
    filterIndex: 0
  });
  
  // 7. Prendre 10 photos de 300s
  for (let i = 0; i < 10; i++) {
    await client.takePhoto({
      exposure: 300,
      bin: 1,
      filterIndex: 0,
      filename: `%%fitdir%%\\M31_L_${i+1}.fit`
    });
    
    // Attendre la fin
    await client.waitForNewFIT();
  }
  
  // 8. Déconnecter
  await client.setupDisconnect();
  client.close();
}
Exemple 2: Monitoring temps réel
javascriptclass VoyagerMonitor {
  constructor(host, port) {
    this.client = new VoyagerClient(host, port);
    this.setupListeners();
  }

  setupListeners() {
    this.client.on('ControlData', (data) => {
      this.updateDashboard(data);
    });

    this.client.on('ShotRunning', (data) => {
      this.updateProgress(data);
    });

    this.client.on('NewFITReady', (data) => {
      this.notifyNewImage(data);
    });

    this.client.on('WeatherAndSafetyMonitorData', (data) => {
      this.checkSafety(data);
    });
  }

  updateDashboard(data) {
    // Mise à jour UI
    $('#temperature').text(`${data.CCDTEMP}°C`);
    $('#ra').text(data.MNTRA);
    $('#dec').text(data.MNTDEC);
    $('#hfd').text(data.FOCHFD);
    
    // Status
    const statusText = {
      0: 'STOPPED',
      1: 'IDLE',
      2: 'RUNNING',
      3: 'ERROR'
    };
    $('#status').text(statusText[data.VOYSTAT]);
  }

  updateProgress(data) {
    const percent = data.ElapsedPerc;
    const remaining = data.Expo - data.Elapsed;
    
    $('#progress').val(percent);
    $('#remaining').text(`${remaining}s restantes`);
  }

  checkSafety(data) {
    if (data.SMStatus === 'UNSAFE' || 
        data.WSRain !== 'DRY' || 
        data.WSCloud === 'VERY_CLOUDY') {
      this.emergencyStop();
    }
  }

  async emergencyStop() {
    alert('Conditions dangereuses détectées!');
    await this.client.abort(true); // HALT ALL
    await this.client.setupDisconnect();
  }
}
Exemple 3: Intégration Laravel
php<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

class VoyagerService
{
    private $socket;
    private $host;
    private $port;
    private $connected = false;
    
    public function __construct(string $host = '192.168.1.100', int $port = 5950)
    {
        $this->host = $host;
        $this->port = $port;
    }
    
    public function connect(): bool
    {
        $this->socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
        
        if (!socket_connect($this->socket, $this->host, $this->port)) {
            Log::error('Voyager connection failed: ' . socket_strerror(socket_last_error()));
            return false;
        }
        
        // Attendre Version event
        $version = $this->readEvent();
        if ($version['Event'] !== 'Version') {
            return false;
        }
        
        Log::info('Connected to Voyager: ' . $version['VOYVersion']);
        $this->connected = true;
        
        return true;
    }
    
    public function authenticate(string $username, string $password): bool
    {
        $base64 = base64_encode("{$username}:{$password}");
        
        $command = [
            'method' => 'AuthenticateUserBase',
            'params' => [
                'UID' => \Ramsey\Uuid\Uuid::uuid4()->toString(),
                'Base' => $base64
            ],
            'id' => 1
        ];
        
        $this->sendCommand($command);
        $response = $this->readResponse();
        
        if (isset($response['error'])) {
            Log::error('Authentication failed: ' . $response['error']['message']);
            return false;
        }
        
        return true;
    }
    
    public function takePhoto(array $params): ?string
    {
        $uid = \Ramsey\Uuid\Uuid::uuid4()->toString();
        
        $command = [
            'method' => 'RemoteCameraShot',
            'params' => array_merge([
                'UID' => $uid,
                'Expo' => 300,
                'Bin' => 1,
                'IsROI' => false,
                'ROITYPE' => 0,
                'FilterIndex' => 0,
                'ExpoType' => 0,
                'FitFileName' => '%%fitdir%%\\obs_' . time() . '.fit',
                'Gain' => -900000,
                'Offset' => -900000
            ], $params),
            'id' => 2
        ];
        
        $this->sendCommand($command);
        
        // Attendre NewFITReadyRéessayerCContinuerModifiermarkdown# Documentation Technique - Voyager Application Server Protocol (Suite 3)

### Exemple 3: Intégration Laravel (suite)
```php
<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

class VoyagerService
{
    private $socket;
    private $host;
    private $port;
    private $connected = false;
    private $commandId = 1;
    
    // ... (constructeur et méthodes précédentes)
    
    public function takePhoto(array $params): ?string
    {
        $uid = \Ramsey\Uuid\Uuid::uuid4()->toString();
        
        $command = [
            'method' => 'RemoteCameraShot',
            'params' => array_merge([
                'UID' => $uid,
                'Expo' => 300,
                'Bin' => 1,
                'IsROI' => false,
                'ROITYPE' => 0,
                'FilterIndex' => 0,
                'ExpoType' => 0,
                'IsSaveFile' => true,
                'FitFileName' => '%%fitdir%%\\obs_' . time() . '.fit',
                'Gain' => -900000,
                'Offset' => -900000,
                'Parallelized' => false
            ], $params),
            'id' => $this->commandId++
        ];
        
        $this->sendCommand($command);
        
        // Attendre NewFITReady
        $filename = null;
        $timeout = time() + $params['Expo'] + 60; // Expo + marge 60s
        
        while (time() < $timeout) {
            $event = $this->readEvent();
            
            if ($event['Event'] === 'NewFITReady') {
                $filename = $event['File'];
                break;
            }
            
            if ($event['Event'] === 'RemoteActionResult' && 
                $event['UID'] === $uid && 
                $event['ActionResultInt'] === 5) {
                Log::error('Shot failed: ' . $event['Motivo']);
                return null;
            }
        }
        
        return $filename;
    }
    
    public function runSequence(string $sequenceFile, int $startFlag = 0): bool
    {
        $uid = \Ramsey\Uuid\Uuid::uuid4()->toString();
        
        $command = [
            'method' => 'RemoteSequence',
            'params' => [
                'UID' => $uid,
                'SequenceFile' => $sequenceFile,
                'StartFlag' => $startFlag
            ],
            'id' => $this->commandId++
        ];
        
        $this->sendCommand($command);
        
        // Attendre résultat
        $result = $this->waitForActionResult($uid, 7200); // timeout 2h
        
        return $result && $result['ActionResultInt'] === 4;
    }
    
    public function setupConnect(int $timeout = 90): bool
    {
        $uid = \Ramsey\Uuid\Uuid::uuid4()->toString();
        
        $command = [
            'method' => 'RemoteSetupConnect',
            'params' => [
                'UID' => $uid,
                'TimeoutConnect' => $timeout
            ],
            'id' => $this->commandId++
        ];
        
        $this->sendCommand($command);
        
        $result = $this->waitForActionResult($uid, $timeout + 10);
        
        return $result && $result['ActionResultInt'] === 4;
    }
    
    public function setupDisconnect(int $timeout = 90): bool
    {
        $uid = \Ramsey\Uuid\Uuid::uuid4()->toString();
        
        $command = [
            'method' => 'RemoteSetupDisconnect',
            'params' => [
                'UID' => $uid,
                'TimeoutDisconnect' => $timeout
            ],
            'id' => $this->commandId++
        ];
        
        $this->sendCommand($command);
        
        $result = $this->waitForActionResult($uid, $timeout + 10);
        
        return $result && $result['ActionResultInt'] === 4;
    }
    
    public function getStatus(): ?string
    {
        $uid = \Ramsey\Uuid\Uuid::uuid4()->toString();
        
        $command = [
            'method' => 'RemoteGetStatus',
            'params' => ['UID' => $uid],
            'id' => $this->commandId++
        ];
        
        $this->sendCommand($command);
        
        $result = $this->waitForActionResult($uid, 10);
        
        return $result['ParamRet']['VoyagerStatus'] ?? null;
    }
    
    private function sendCommand(array $command): void
    {
        $json = json_encode($command) . "\r\n";
        socket_write($this->socket, $json, strlen($json));
        Log::debug('Voyager command sent: ' . $command['method']);
    }
    
    private function readEvent(): ?array
    {
        $buffer = '';
        
        while (true) {
            $char = socket_read($this->socket, 1);
            
            if ($char === false) {
                Log::error('Socket read error');
                return null;
            }
            
            $buffer .= $char;
            
            // Vérifier fin de ligne
            if (substr($buffer, -2) === "\r\n") {
                $json = trim($buffer);
                $data = json_decode($json, true);
                
                if ($data) {
                    Log::debug('Voyager event received: ' . ($data['Event'] ?? 'response'));
                    return $data;
                }
            }
        }
    }
    
    private function readResponse(): ?array
    {
        return $this->readEvent();
    }
    
    private function waitForActionResult(string $uid, int $timeout = 300): ?array
    {
        $endTime = time() + $timeout;
        
        while (time() < $endTime) {
            $event = $this->readEvent();
            
            if (!$event) continue;
            
            if ($event['Event'] === 'RemoteActionResult' && 
                $event['UID'] === $uid) {
                return $event;
            }
        }
        
        Log::error('Timeout waiting for action result: ' . $uid);
        return null;
    }
    
    public function disconnect(): void
    {
        if ($this->connected && $this->socket) {
            $command = [
                'method' => 'disconnect',
                'id' => $this->commandId++
            ];
            
            $this->sendCommand($command);
            socket_close($this->socket);
            $this->connected = false;
            
            Log::info('Disconnected from Voyager');
        }
    }
    
    public function __destruct()
    {
        $this->disconnect();
    }
}
Contrôleur Laravel pour réservations
php<?php

namespace App\Http\Controllers;

use App\Models\Reservation;
use App\Services\VoyagerService;
use Illuminate\Http\Request;

class ObservationController extends Controller
{
    private VoyagerService $voyager;
    
    public function __construct(VoyagerService $voyager)
    {
        $this->voyager = $voyager;
    }
    
    public function startObservation(Request $request, Reservation $reservation)
    {
        // Vérifier que la réservation est active
        if (!$reservation->isActive()) {
            return response()->json([
                'error' => 'Reservation not active'
            ], 403);
        }
        
        // Connexion à Voyager
        if (!$this->voyager->connect()) {
            return response()->json([
                'error' => 'Cannot connect to Voyager'
            ], 500);
        }
        
        // Authentification
        if (!$this->voyager->authenticate(
            config('voyager.username'),
            config('voyager.password')
        )) {
            return response()->json([
                'error' => 'Authentication failed'
            ], 500);
        }
        
        // Connecter le setup
        if (!$this->voyager->setupConnect()) {
            return response()->json([
                'error' => 'Setup connection failed'
            ], 500);
        }
        
        // Lancer la séquence
        $sequenceFile = $reservation->sequence_file;
        $success = $this->voyager->runSequence($sequenceFile);
        
        if ($success) {
            $reservation->update([
                'status' => 'running',
                'started_at' => now()
            ]);
            
            return response()->json([
                'message' => 'Observation started',
                'reservation_id' => $reservation->id
            ]);
        }
        
        return response()->json([
            'error' => 'Failed to start observation'
        ], 500);
    }
    
    public function getStatus(Reservation $reservation)
    {
        if (!$this->voyager->connect()) {
            return response()->json(['error' => 'Connection failed'], 500);
        }
        
        $status = $this->voyager->getStatus();
        
        return response()->json([
            'status' => $status,
            'reservation' => $reservation
        ]);
    }
    
    public function stopObservation(Reservation $reservation)
    {
        if (!$this->voyager->connect()) {
            return response()->json(['error' => 'Connection failed'], 500);
        }
        
        $this->voyager->setupDisconnect();
        
        $reservation->update([
            'status' => 'completed',
            'completed_at' => now()
        ]);
        
        return response()->json([
            'message' => 'Observation stopped'
        ]);
    }
}
Job Laravel pour monitoring continu
php<?php

namespace App\Jobs;

use App\Models\Reservation;
use App\Services\VoyagerService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class MonitorObservation implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    
    public $timeout = 7200; // 2 heures
    
    private Reservation $reservation;
    
    public function __construct(Reservation $reservation)
    {
        $this->reservation = $reservation;
    }
    
    public function handle(VoyagerService $voyager): void
    {
        if (!$voyager->connect()) {
            Log::error('Monitoring: Cannot connect to Voyager');
            return;
        }
        
        $voyager->authenticate(
            config('voyager.username'),
            config('voyager.password')
        );
        
        // Activer mode Dashboard
        $uid = \Ramsey\Uuid\Uuid::uuid4()->toString();
        $command = [
            'method' => 'RemoteSetDashboardMode',
            'params' => [
                'UID' => $uid,
                'IsOn' => true
            ],
            'id' => 1
        ];
        
        $voyager->sendCommand($command);
        
        // Boucle de monitoring
        $startTime = time();
        $endTime = $startTime + $this->timeout;
        
        while (time() < $endTime) {
            $event = $voyager->readEvent();
            
            if (!$event) continue;
            
            switch ($event['Event']) {
                case 'ControlData':
                    $this->updateReservationStatus($event);
                    break;
                    
                case 'NewFITReady':
                    $this->handleNewImage($event);
                    break;
                    
                case 'WeatherAndSafetyMonitorData':
                    $this->checkSafety($event, $voyager);
                    break;
                    
                case 'RemoteActionResult':
                    if ($event['ActionResultInt'] === 4) {
                        // Séquence terminée
                        $this->completeReservation();
                        return;
                    }
                    break;
            }
            
            // Vérifier si réservation toujours active
            $this->reservation->refresh();
            if ($this->reservation->status === 'cancelled') {
                $voyager->setupDisconnect();
                return;
            }
        }
    }
    
    private function updateReservationStatus(array $data): void
    {
        $this->reservation->update([
            'voyager_status' => $data['VOYSTAT'],
            'ccd_temperature' => $data['CCDTEMP'],
            'mount_ra' => $data['MNTRA'],
            'mount_dec' => $data['MNTDEC'],
            'sequence_progress' => $data['SEQPARZ'],
            'sequence_total' => $data['SEQTOT']
        ]);
    }
    
    private function handleNewImage(array $event): void
    {
        Log::info('New image ready: ' . $event['File']);
        
        // Enregistrer l'image
        \App\Models\ObservationImage::create([
            'reservation_id' => $this->reservation->id,
            'filename' => $event['File'],
            'type' => $event['Type'],
            'target' => $event['SeqTarget']
        ]);
        
        // Notifier l'utilisateur
        $this->reservation->user->notify(
            new \App\Notifications\NewImageAvailable($event['File'])
        );
    }
    
    private function checkSafety(array $data, VoyagerService $voyager): void
    {
        $unsafe = false;
        $reasons = [];
        
        if ($data['SMStatus'] === 'UNSAFE') {
            $unsafe = true;
            $reasons[] = 'Safety monitor unsafe';
        }
        
        if ($data['WSRain'] === 'RAIN' || $data['WSRain'] === 'WET') {
            $unsafe = true;
            $reasons[] = 'Rain detected';
        }
        
        if ($data['WSCloud'] === 'VERY_CLOUDY') {
            $unsafe = true;
            $reasons[] = 'Very cloudy';
        }
        
        if ($unsafe) {
            Log::warning('Unsafe conditions: ' . implode(', ', $reasons));
            
            // Arrêt d'urgence
            $voyager->sendCommand([
                'method' => 'Abort',
                'params' => ['IsHalt' => true],
                'id' => 999
            ]);
            
            $this->reservation->update([
                'status' => 'aborted',
                'abort_reason' => implode(', ', $reasons)
            ]);
            
            // Notifier l'utilisateur
            $this->reservation->user->notify(
                new \App\Notifications\ObservationAborted($reasons)
            );
        }
    }
    
    private function completeReservation(): void
    {
        $this->reservation->update([
            'status' => 'completed',
            'completed_at' => now()
        ]);
        
        $this->reservation->user->notify(
            new \App\Notifications\ObservationCompleted()
        );
    }
}
Exemple 4: Client Python
pythonimport socket
import json
import time
import hashlib
import uuid
from typing import Dict, Optional, Callable

class VoyagerClient:
    def __init__(self, host: str = '192.168.1.100', port: int = 5950):
        self.host = host
        self.port = port
        self.socket: Optional[socket.socket] = None
        self.command_id = 1
        self.connected = False
        self.event_handlers: Dict[str, Callable] = {}
        
    def connect(self) -> bool:
        """Établit la connexion avec Voyager"""
        try:
            self.socket = socket.socket(socket.AF_INET, socket.SOCK_STREAM)
            self.socket.connect((self.host, self.port))
            
            # Attendre Version event
            version = self.read_event()
            if version.get('Event') != 'Version':
                return False
            
            print(f"Connected to Voyager {version['VOYVersion']}")
            self.connected = True
            return True
            
        except Exception as e:
            print(f"Connection error: {e}")
            return False
    
    def authenticate(self, username: str, password: str) -> bool:
        """Authentification Base"""
        import base64
        
        credentials = f"{username}:{password}"
        base64_creds = base64.b64encode(credentials.encode()).decode()
        
        command = {
            'method': 'AuthenticateUserBase',
            'params': {
                'UID': str(uuid.uuid4()),
                'Base': base64_creds
            },
            'id': self.command_id
        }
        self.command_id += 1
        
        self.send_command(command)
        response = self.read_event()
        
        if 'error' in response:
            print(f"Authentication failed: {response['error']['message']}")
            return False
        
        return 'authbase' in response
    
    def send_command(self, command: dict) -> None:
        """Envoie une commande"""
        json_str = json.dumps(command) + "\r\n"
        self.socket.send(json_str.encode())
    
    def read_event(self, timeout: int = 30) -> Optional[dict]:
        """Lit un événement"""
        self.socket.settimeout(timeout)
        buffer = ""
        
        try:
            while True:
                char = self.socket.recv(1).decode()
                buffer += char
                
                if buffer.endswith("\r\n"):
                    json_str = buffer.strip()
                    data = json.loads(json_str)
                    return data
                    
        except socket.timeout:
            print("Read timeout")
            return None
        except Exception as e:
            print(f"Read error: {e}")
            return None
    
    def on(self, event_name: str, handler: Callable) -> None:
        """Enregistre un handler d'événement"""
        self.event_handlers[event_name] = handler
    
    def listen(self, duration: int = 3600) -> None:
        """Écoute les événements pendant une durée"""
        start_time = time.time()
        
        while time.time() - start_time < duration:
            event = self.read_event(timeout=5)
            
            if not event:
                continue
            
            event_name = event.get('Event')
            if event_name and event_name in self.event_handlers:
                self.event_handlers[event_name](event)
    
    def setup_connect(self, timeout: int = 90) -> bool:
        """Connecte le setup"""
        uid = str(uuid.uuid4())
        
        command = {
            'method': 'RemoteSetupConnect',
            'params': {
                'UID': uid,
                'TimeoutConnect': timeout
            },
            'id': self.command_id
        }
        self.command_id += 1
        
        self.send_command(command)
        
        # Attendre résultat
        result = self.wait_for_action_result(uid, timeout + 10)
        return result and result['ActionResultInt'] == 4
    
    def take_photo(self, **kwargs) -> Optional[str]:
        """Prend une photo"""
        uid = str(uuid.uuid4())
        
        params = {
            'UID': uid,
            'Expo': 300,
            'Bin': 1,
            'IsROI': False,
            'ROITYPE': 0,
            'FilterIndex': 0,
            'ExpoType': 0,
            'IsSaveFile': True,
            'FitFileName': f'%%fitdir%%\\obs_{int(time.time())}.fit',
            'Gain': -900000,
            'Offset': -900000,
            'Parallelized': False
        }
        params.update(kwargs)
        
        command = {
            'method': 'RemoteCameraShot',
            'params': params,
            'id': self.command_id
        }
        self.command_id += 1
        
        self.send_command(command)
        
        # Attendre NewFITReady
        timeout_time = time.time() + params['Expo'] + 60
        
        while time.time() < timeout_time:
            event = self.read_event(timeout=5)
            
            if not event:
                continue
            
            if event.get('Event') == 'NewFITReady':
                return event['File']
            
            if (event.get('Event') == 'RemoteActionResult' and
                event.get('UID') == uid and
                event.get('ActionResultInt') == 5):
                print(f"Shot failed: {event['Motivo']}")
                return None
        
        return None
    
    def wait_for_action_result(self, uid: str, timeout: int = 300) -> Optional[dict]:
        """Attend le résultat d'une action"""
        end_time = time.time() + timeout
        
        while time.time() < end_time:
            event = self.read_event(timeout=5)
            
            if not event:
                continue
            
            if (event.get('Event') == 'RemoteActionResult' and
                event.get('UID') == uid):
                return event
        
        print(f"Timeout waiting for action result: {uid}")
        return None
    
    def disconnect(self) -> None:
        """Ferme la connexion"""
        if self.connected and self.socket:
            command = {
                'method': 'disconnect',
                'id': self.command_id
            }
            self.send_command(command)
            self.socket.close()
            self.connected = False
            print("Disconnected from Voyager")

# Utilisation
if __name__ == '__main__':
    client = VoyagerClient('192.168.1.100', 5950)
    
    if client.connect():
        if client.authenticate('admin', 'password'):
            # Enregistrer handlers
            client.on('NewFITReady', lambda e: print(f"New image: {e['File']}"))
            client.on('Signal', lambda e: print(f"Signal: {e['Code']}"))
            
            # Connecter setup
            if client.setup_connect():
                # Prendre une photo
                filename = client.take_photo(Expo=60, Bin=2)
                print(f"Photo saved: {filename}")
            
            client.disconnect()

Bonnes pratiques
Sécurité

Ne jamais exposer les credentials

php   // ✓ Bon
   $username = config('voyager.username');
   $password = config('voyager.password');
   
   // ✗ Mauvais
   $username = "admin";
   $password = "password123";

Valider les entrées utilisateur

php   $validated = $request->validate([
       'exposure' => 'required|numeric|min:0.001|max:3600',
       'bin' => 'required|integer|in:1,2,3,4',
       'filter_index' => 'required|integer|min:0|max:10'
   ]);

Utiliser HTTPS pour l'interface web

Voyager → TCP non chiffré
Interface Laravel → HTTPS obligatoire


Firewall strict

Port 5950 accessible uniquement depuis serveur Laravel
Pas d'exposition publique



Performance

Utiliser des queues pour actions longues

php   // Dans le contrôleur
   MonitorObservation::dispatch($reservation);
   
   return response()->json(['message' => 'Started']);

Connection pooling

php   // Réutiliser connexions au lieu de connect/disconnect
   class VoyagerConnectionPool {
       private static $connections = [];
       
       public static function get(): VoyagerService {
           // Logique de pool
       }
   }

Cache des configurations

php   Cache::remember('voyager_filters', 3600, function() {
       return $voyager->getFilterConfiguration();
   });
Robustesse

Retry automatique

php   public function robustConnect(int $maxRetries = 3): bool {
       for ($i = 0; $i < $maxRetries; $i++) {
           if ($this->connect()) {
               return true;
           }
           sleep(pow(2, $i)); // Backoff exponentiel
       }
       return false;
   }

Logging exhaustif

php   Log::channel('voyager')->info('Command sent', [
       'method' => $command['method'],
       'uid' => $command['params']['UID'],
       'timestamp' => now()
   ]);

Health checks

php   // Vérifier connexion régulièrement
   if (!$this->voyager->getStatus()) {
       $this->voyager->reconnect();
   }
Monitoring

Métriques clés

Temps de réponse commandes
Taux d'erreur
Durée expositions
Qualité images (HFD)


Alertes

Conditions météo dangereuses
Échecs répétés
Timeouts
Température hors limites


Dashboard temps réel

WebSockets pour updates live
Graphiques tendances
État équipements




Ressources supplémentaires
Configuration Voyager
Setup → Remote:

Authorization Level: Base/Ticket
Username/Password
RoboTarget Shared Secret

Setup → Network:

Firewall rules
Port 5950 ouvert

Outils de développement
Test connexion:
bash# Telnet
telnet 192.168.1.100 5950

# Netcat
nc 192.168.1.100 5950

# Python simple
python -c "import socket; s=socket.socket(); s.connect(('192.168.1.100',5950)); print(s.recv(1024))"
MD5 online:

https://www.md5hashgenerator.com/
https://md5calc.com/

Base64 encode:
bashecho -n "username:password" | base64
Support
Documentation officielle:

https://voyager.starkeeper.it
PDF Protocol complet
Forums utilisateurs

Contact:

voyagerastro@gmail.com
Support tickets via site


Annexes
Tableau récapitulatif des commandes
CommandeLicenceAsyncUtilisation principaleRemoteSetupConnectBaseOuiConnexion équipementsRemoteSetupDisconnectBaseOuiDéconnexionRemoteCameraShotBaseOuiPrise de vueRemoteSequenceBaseOuiLancer séquenceRemotePrecisePointTargetBaseOuiPointage précisRemoteFocusExBaseOuiAutofocusRemoteGetStatusBaseNonÉtat VoyagerRemoteOpenRoboTargetGetTargetListAdvancedNonListe ciblesRemoteOpenRoboTargetGetShotDoneListAdvancedNonImages cible
Codes d'erreur courants
CodeMessageSolution1Connection failedVérifier réseau/firewall1Authentication rejectedVérifier credentials5Action errorVoir Motivo pour détails8TimeoutAugmenter timeout
Checklist pré-production

 Credentials sécurisés (env variables)
 Firewall configuré
 Logs activés
 Monitoring en place
 Gestion erreurs robuste
 Tests end-to-end
 Documentation déploiement
 Plan de backup
 Procédures urgence
 Formation utilisateurs


Fin de la documentation

