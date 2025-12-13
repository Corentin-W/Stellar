🔌 Protocole de Connexion et Maintien (Heartbeat) Voyager
Ce document détaille les spécifications techniques pour établir, sécuriser et maintenir la connexion TCP/IP persistante entre le Proxy (Node.js) et le serveur d'application Voyager.
1. Vue d'ensemble de l'Architecture
Conformément à l'architecture technique définie, la connexion directe au matériel n'est pas gérée par Laravel, mais par un Proxy Node.js intermédiaire.
• Laravel : Envoie des requêtes HTTP ponctuelles au Proxy.
• Proxy Node.js : Maintient le tunnel TCP ouvert, gère le flux JSON-RPC et le Heartbeat [1, 2].
• Voyager : Serveur TCP écoutant par défaut sur le port 5950 [3].
2. Le Protocole de Communication
Le serveur Voyager utilise un protocole strict qu'il faut respecter à la lettre pour éviter les déconnexions immédiates.
• Transport : Socket TCP brute.
• Format : JSON-RPC 2.0.
• Terminaison : Chaque paquet (commande ou événement) doit impérativement se terminer par CR LF (\r\n) [4, 5].
• Encodage : Texte (ASCII/UTF-8).
3. Workflow de Connexion (Handshake)
La séquence de connexion doit suivre cet ordre précis. Tout écart peut entraîner un rejet par le serveur.
Étape A : Ouverture et Événement Version
Dès l'ouverture de la socket TCP, Voyager envoie spontanément un événement Version.
{"Event":"Version","Timestamp":1652231344.88438,"Host":"RC16","Inst":1,...}

> CRITIQUE : Vous devez capturer la valeur Timestamp de cet événement. Elle sert de SessionKey pour le calcul des hashs de sécurité RoboTarget plus tard [6, 7].
Étape B : Authentification (Time-sensitive)
Si l'authentification est activée dans Voyager, vous disposez de 5 secondes après la connexion pour envoyer la commande AuthenticateUserBase. Sinon, le serveur coupe la connexion [8].
• Méthode : AuthenticateUserBase
• Encodage : Le paramètre Base est une chaîne user:password encodée en Base64 [9].
{"method": "AuthenticateUserBase", "params": {"UID":"[UUID]","Base":"[BASE64_STRING]"}, "id": 1}

Étape C : Initialisation des modes
Une fois authentifié, activez les modes nécessaires pour recevoir les données :
1. Mode Dashboard : Pour recevoir les images JPG et le statut détaillé (ControlData) toutes les 2 secondes [10].
2. Mode RoboTarget : Pour piloter l'automate via RemoteSetRoboTargetManagerMode. C'est ici que le Timestamp (récupéré à l'étape A) est utilisé pour générer le Hash de sécurité [6, 11].
--------------------------------------------------------------------------------
4. Maintien de la Connexion (Heartbeat / Polling)
C'est la partie la plus critique pour la stabilité du Proxy. Voyager intègre un mécanisme de "Watchdog" strict.
La Règle des 15 secondes
Si le serveur ne reçoit aucune donnée valide du client pendant 15 secondes, il considère le client comme "mort" et ferme la socket TCP [12].
Implémentation du Heartbeat
Le client (Proxy) doit envoyer un événement de Polling régulièrement si aucune autre commande n'est envoyée.
• Fréquence recommandée : Toutes les 5 secondes [12].
• Format du paquet :
{"Event":"Polling","Timestamp":1652231350.000,"Host":"ProxyClient","Inst":1}  
(Note: Les champs Timestamp, Host et Inst sont informatifs, l'essentiel est l'envoi du JSON).
Logique de Timer (Algorithme)
Pour une robustesse maximale, implémentez la logique suivante dans le Proxy :
1. Initialiser un timer KeepAlive de 5 secondes.
2. À chaque envoi de commande (ex: RemoteCameraShot), réinitialiser ce timer à 0.
3. Si le timer atteint 5 secondes (aucune commande envoyée), envoyer le paquet Polling.
4. Réception : Le serveur envoie aussi des événements Polling. Le Proxy doit les traiter silencieusement pour confirmer que le serveur est en vie [13].
--------------------------------------------------------------------------------
5. Gestion des Pannes et Reconnexion
Le Proxy doit être capable de survivre à un redémarrage de Voyager ou une coupure réseau sans intervention humaine [14].
Détection de la déconnexion
1. Événement ShutDown : Voyager prévient qu'il va fermer [15].
2. Erreur Socket / Timeout : Si aucune donnée n'est reçue pendant >15s.
Stratégie de Reconnexion (Backoff)
Si la connexion est perdue, le Proxy doit :
1. Marquer le statut interne comme disconnected.
2. Broadcaster l'état via WebSocket au frontend Laravel (connectionState: false) [14].
3. Tenter une reconnexion immédiate après 5s.
4. Si échec, augmenter le délai (Backoff exponentiel : 10s, 20s, jusqu'à 5min) jusqu'à ce que le port 5950 réponde à nouveau [16].
--------------------------------------------------------------------------------
6. Résumé des Commandes Clés pour la Connexion
Action
Commande JSON-RPC
Condition
Authentifier
AuthenticateUserBase
< 5s après connexion [8]
Keep-Alive
{"Event":"Polling",...}
Tous les 5s (si inactif) [12]
Mode API
RemoteSetRoboTargetManagerMode
Requis pour RoboTarget [11]
Mode UI
RemoteSetDashboardMode
Requis pour flux JPG/Status [10]
Déconnecter
disconnect
Fermeture propre [17]
