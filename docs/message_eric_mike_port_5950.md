# 📨 MESSAGE POUR ERIC/MIKE - OUVERTURE PORT 5950

---

## Message à envoyer

```
Bonjour Eric et Mike,

J'ai terminé la configuration de mon proxy pour me connecter à l'API Voyager RoboTarget.

Tout est prêt de mon côté, mais j'ai besoin de votre aide pour un dernier point critique :

🚨 LE PORT TCP 5950 N'EST PAS ACCESSIBLE

Test effectué :
  nc -z -w 1 185.228.120.120 5950
  Résultat : Connection timeout

Ce port est requis par Voyager pour l'API RoboTarget (selon la documentation officielle
"Clients connect to Voyager on TCP-IP port 5950").

Sans ce port ouvert, aucune connexion n'est possible.

ACTIONS REQUISES DE VOTRE PART :

1. Ouvrir le port TCP 5950 dans le pare-feu Windows (instructions détaillées ci-dessous)
2. Vérifier la version de Voyager installée (doit être ≥ 2.3.13)
3. Me communiquer le numéro de série de Voyager (pour la licence NDA)

Merci beaucoup !
Mikael
```

---

## 📋 INSTRUCTIONS DÉTAILLÉES POUR ERIC/MIKE

### ÉTAPE 1 : Ouvrir le port 5950 dans Windows Firewall

#### Méthode A : Via l'interface graphique (Recommandée)

1. **Ouvrir le Pare-feu Windows**
   - Appuyez sur `Windows + R`
   - Tapez : `wf.msc`
   - Appuyez sur `Entrée`

2. **Créer une règle entrante**
   - Dans le panneau de gauche, cliquez sur `Inbound Rules` (Règles de trafic entrant)
   - Dans le panneau de droite, cliquez sur `New Rule...` (Nouvelle règle...)

3. **Configurer la règle - Type**
   - Sélectionnez : `Port`
   - Cliquez sur `Next`

4. **Configurer la règle - Protocole et ports**
   - Sélectionnez : `TCP`
   - Sélectionnez : `Specific local ports` (Ports locaux spécifiques)
   - Entrez : `5950`
   - Cliquez sur `Next`

5. **Configurer la règle - Action**
   - Sélectionnez : `Allow the connection` (Autoriser la connexion)
   - Cliquez sur `Next`

6. **Configurer la règle - Profil**
   - Cochez TOUTES les cases :
     - ✅ Domain
     - ✅ Private
     - ✅ Public
   - Cliquez sur `Next`

7. **Configurer la règle - Nom**
   - Name (Nom) : `Voyager API RoboTarget - Port 5950`
   - Description : `Allow incoming connections to Voyager Application Server on TCP port 5950`
   - Cliquez sur `Finish`

✅ **Le port 5950 est maintenant ouvert !**

---

#### Méthode B : Via PowerShell (Alternative rapide)

Si vous préférez une commande rapide, ouvrez PowerShell **en tant qu'administrateur** et exécutez :

```powershell
New-NetFirewallRule -DisplayName "Voyager API RoboTarget - Port 5950" -Direction Inbound -Protocol TCP -LocalPort 5950 -Action Allow -Profile Any
```

✅ **Le port 5950 est maintenant ouvert !**

---

#### Méthode C : Via l'invite de commandes (Alternative)

Ouvrez l'invite de commandes (CMD) **en tant qu'administrateur** et exécutez :

```cmd
netsh advfirewall firewall add rule name="Voyager API RoboTarget - Port 5950" dir=in action=allow protocol=TCP localport=5950
```

✅ **Le port 5950 est maintenant ouvert !**

---

### ÉTAPE 2 : Vérifier que Voyager écoute sur le port 5950

1. **Ouvrir Voyager**
   - Démarrez l'application Voyager sur le serveur 185.228.120.120

2. **Vérifier la configuration du port**
   - Dans Voyager, allez dans : `Setup` → `Voyager` → `Application Server`
   - Vérifiez que le port configuré est bien : **5950**
   - Si ce n'est pas le cas, changez-le pour **5950** et redémarrez Voyager

3. **Vérifier que le service écoute**
   - Ouvrez PowerShell ou CMD
   - Exécutez : `netstat -an | findstr :5950`
   - Vous devriez voir une ligne comme :
     ```
     TCP    0.0.0.0:5950           0.0.0.0:0              LISTENING
     ```
   - Si vous ne voyez rien, Voyager n'écoute pas sur ce port → vérifiez la configuration

---

### ÉTAPE 3 : Tester la connectivité (optionnel)

Depuis votre machine (185.228.120.120), testez localement :

```cmd
telnet localhost 5950
```

Ou avec PowerShell :

```powershell
Test-NetConnection -ComputerName localhost -Port 5950
```

**Résultat attendu** : La connexion doit s'établir (vous verrez peut-être du JSON avec un événement "Version")

---

### ÉTAPE 4 : Vérifier la version de Voyager

1. **Ouvrir Voyager**
2. Allez dans : `Help` → `About` (ou `License`)
3. Notez la version affichée (exemple : `2.3.13` ou supérieur)

**IMPORTANT** : La version doit être **≥ 2.3.13** pour que les clés MAC fonctionnent.

Si la version est inférieure, installez la version 2.3.13 ou supérieure :
- 64 bits : https://www.starkeeper.it/voyager/Voyager_Setup_2.3.13_64bit.zip
- 32 bits : https://www.starkeeper.it/voyager/Voyager_Setup_2.3.13.zip

---

### ÉTAPE 5 : Récupérer le numéro de série Voyager

1. **Ouvrir Voyager**
2. Allez dans : `Help` → `About` ou `License`
3. **Copiez le numéro de série complet** (Serial Number)
   - Format attendu : `XXXX-XXXX-XXXX-XXXX-XXXX...`

Ce numéro est nécessaire pour que Leonardo Orazi génère la licence NDA.

---

## 🔒 VÉRIFICATIONS DE SÉCURITÉ (Optionnel mais recommandé)

### Option 1 : Restreindre l'accès par IP (Recommandé)

Si vous voulez limiter l'accès au port 5950 uniquement à l'IP de Mikael, modifiez la règle :

**Via PowerShell** :
```powershell
New-NetFirewallRule -DisplayName "Voyager API RoboTarget - Port 5950" -Direction Inbound -Protocol TCP -LocalPort 5950 -Action Allow -RemoteAddress [IP_DE_MIKAEL] -Profile Any
```

Remplacez `[IP_DE_MIKAEL]` par son adresse IP publique.

**Via l'interface graphique** :
1. Ouvrez `wf.msc`
2. Double-cliquez sur la règle "Voyager API RoboTarget - Port 5950"
3. Allez dans l'onglet `Scope` (Étendue)
4. Dans `Remote IP address`, sélectionnez `These IP addresses`
5. Cliquez sur `Add...` et ajoutez l'IP de Mikael
6. Cliquez sur `OK`

---

### Option 2 : Configuration VPN (Alternative)

Si vous préférez ne pas exposer le port 5950 sur Internet, vous pouvez :
1. Configurer un VPN (OpenVPN, WireGuard, etc.)
2. Donner accès VPN à Mikael
3. Mikael se connectera via le VPN sur l'IP locale du serveur

---

## ✅ CHECKLIST FINALE

Avant de confirmer à Mikael que tout est prêt, vérifiez :

- [ ] Port 5950 ouvert dans Windows Firewall
- [ ] Voyager configuré pour écouter sur le port 5950
- [ ] `netstat -an | findstr :5950` montre `LISTENING`
- [ ] Test local : `Test-NetConnection -ComputerName localhost -Port 5950` → Success
- [ ] Version Voyager notée (≥ 2.3.13 requis)
- [ ] Numéro de série Voyager récupéré

**Message à envoyer à Mikael** :
```
✅ Port 5950 ouvert et accessible
✅ Voyager version : [VOTRE_VERSION]
✅ Serial Voyager : [VOTRE_SERIAL]

Vous pouvez tester la connexion !
```

---

## 🆘 DÉPANNAGE

### Problème : Le port ne semble pas accessible même après ouverture

**Causes possibles** :
1. **Routeur/Box** : Si le serveur est derrière un routeur, vous devez configurer le **Port Forwarding** (redirection de port) sur le routeur
   - Redirigez le port externe 5950 vers l'IP locale du serveur (port 5950)
2. **Antivirus tiers** : Certains antivirus (Norton, McAfee, etc.) ont leur propre pare-feu
   - Ajoutez une exception pour le port 5950
3. **Voyager non démarré** : Vérifiez que Voyager est bien en cours d'exécution

### Problème : `netstat` ne montre pas le port 5950

**Solutions** :
1. Vérifiez la configuration dans Voyager (Setup → Voyager → Application Server)
2. Redémarrez Voyager
3. Vérifiez les logs de Voyager pour voir s'il y a des erreurs au démarrage

---

## 📞 CONTACT

Si vous rencontrez des difficultés, envoyez-moi :
1. **Capture d'écran** de la configuration Voyager (Setup → Application Server)
2. **Résultat** de la commande : `netstat -an | findstr :5950`
3. **Version** de Voyager (Help → About)
4. **Message d'erreur** éventuel

Je vous aiderai à résoudre le problème !

---

**Document créé le** : 7 décembre 2025
**Objectif** : Permettre la connexion à l'API Voyager RoboTarget depuis le proxy Node.js
