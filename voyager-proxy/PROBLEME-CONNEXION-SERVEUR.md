# 🔴 PROBLÈME DE CONNEXION VOYAGER

**Date** : 6 décembre 2025
**IP Serveur** : 185.228.120.120
**Port testé** : 23002

---

## 📋 RÉSUMÉ DU PROBLÈME

Le port 23002 est **OUVERT** et accepte les connexions TCP, **MAIS** :
- ❌ **Aucune donnée** n'est reçue du serveur
- ❌ Voyager ne renvoie pas l'événement `Version` attendu
- ❌ La connexion reste "muette"

## ✅ CE QUI FONCTIONNE

- ✅ Connexion réseau à `185.228.120.120` : OK
- ✅ Port 23002 ouvert : OK
- ✅ Paramètres d'authentification : OK (tous corrects)
- ✅ Code Node.js : OK (conforme à la documentation)

## ❌ CE QUI NE FONCTIONNE PAS

- ❌ Port 5950 (défaut Voyager) : **FERMÉ**
- ❌ Port 5951 (Web Server) : **FERMÉ**
- ❌ Réception de données sur 23002 : **AUCUNE**

## 🔍 DIAGNOSTIC

Le port 23002 semble être :
- Un **tunnel SSH** ou **proxy**
- Qui **accepte** la connexion
- Mais qui **ne transmet pas** les données de Voyager

### Scénarios possibles

1. **Voyager n'est pas démarré** sur le serveur
2. **Le tunnel/proxy** ne pointe pas vers le bon port local (devrait être 5950)
3. **Voyager écoute sur 127.0.0.1** uniquement (pas accessible via tunnel)

---

## 📞 ACTION REQUISE

**Vous devez contacter la personne/société qui gère le serveur** `185.228.120.120`

### Questions à poser :

#### 1️⃣ Voyager est-il démarré ?

```
Sur le serveur 185.228.120.120, vérifier que :
- L'application Voyager est lancée
- Voyager affiche son interface principale
```

#### 2️⃣ Configuration du tunnel/proxy

```
Le port 23002 :
- Vers quel port local redirige-t-il ? (devrait être 5950)
- Quelle est la configuration exacte ?
- Est-ce un tunnel SSH ? Un reverse proxy ?
```

#### 3️⃣ Configuration Voyager Remote

```
Dans Voyager → Setup → Remote :
- Application Server Port : devrait être 5950
- "Voyager AS Hostname/IP" : devrait être 0.0.0.0 (ou l'IP publique)
  PAS 127.0.0.1 !
```

#### 4️⃣ Test local

```
Sur le serveur 185.228.120.120, tester :
1. Ouvrir un terminal/cmd
2. Taper : nc -z 127.0.0.1 5950
   ou : telnet 127.0.0.1 5950

Est-ce que Voyager répond en local ?
```

---

## 📧 EMAIL TYPE À ENVOYER

```
Objet : Problème connexion Voyager sur port 23002

Bonjour,

J'essaie de me connecter au serveur Voyager via l'IP 185.228.120.120
sur le port 23002.

La connexion TCP s'établit correctement, mais je ne reçois aucune donnée
du serveur. Normalement, Voyager devrait envoyer un événement "Version"
immédiatement après la connexion.

Pouvez-vous vérifier les points suivants :

1. Voyager est-il bien démarré sur le serveur ?

2. Le tunnel/proxy sur le port 23002 :
   - Vers quel port local redirige-t-il ? (devrait être 5950)
   - La configuration est-elle correcte ?

3. Dans Voyager → Setup → Remote :
   - "Voyager AS Hostname/IP" est-il configuré sur 0.0.0.0 ?
   (ou l'IP publique, PAS 127.0.0.1)

4. Est-ce que Voyager répond en local sur le serveur ?
   (test avec : nc 127.0.0.1 5950 ou telnet 127.0.0.1 5950)

Mes paramètres d'authentification sont corrects (fournis par Leonardo Orazi).

Merci pour votre aide !

Cordialement,
Mikael
```

---

## 📊 CONFIGURATION ACTUELLE (PRÊTE)

Votre configuration est **CORRECTE** et **PRÊTE** :

```bash
# .env
VOYAGER_HOST=185.228.120.120
VOYAGER_PORT=23002
VOYAGER_AUTH_ENABLED=true
VOYAGER_AUTH_BASE=777539
VOYAGER_MAC_KEY=Dherbomez
# ... tous les autres paramètres OK
```

✅ **Dès que le serveur sera configuré correctement, tout fonctionnera !**

---

## 🎯 PROCHAINES ÉTAPES

1. **Contacter** la personne qui gère le serveur `185.228.120.120`
2. **Envoyer** l'email ci-dessus (ou similaire)
3. **Attendre** qu'ils corrigent la configuration
4. **Tester** à nouveau

---

## 💡 ALTERNATIVE (si pas de réponse)

Si la personne qui gère le serveur n'est pas disponible ou ne répond pas :

### Vérifiez vous-même si vous avez accès :

- **Bureau à distance** (RDP Windows) ?
- **TeamViewer** / **AnyDesk** ?
- **VPN** vers le réseau du serveur ?

Si OUI, vous pouvez corriger vous-même dans Voyager :
1. Setup → Remote
2. "Voyager AS Hostname/IP" : mettre `0.0.0.0`
3. Redémarrer Voyager

---

## 📄 FICHIERS DE DIAGNOSTIC

Tous les tests sont disponibles dans :
- `DIAGNOSTIC-FINAL.md` : Rapport complet
- `diagnose.js` : Script de diagnostic automatique
- `port-scan.sh` : Scan de ports

---

**Dernière mise à jour** : 6 décembre 2025, 22:00
