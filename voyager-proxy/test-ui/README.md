# 🧪 Interface de Test - Voyager Proxy

Interface web simple pour tester toutes les fonctionnalités du proxy Voyager.

## 🚀 Utilisation

### Méthode 1 : Servir avec Python (recommandé)

```bash
cd /Users/w/Herd/Stellar/voyager-proxy/test-ui

# Python 3
python3 -m http.server 8080

# Ou Python 2
python -m SimpleHTTPServer 8080
```

Puis ouvrir : **http://localhost:8080**

### Méthode 2 : Servir avec Node.js

```bash
npm install -g http-server
cd /Users/w/Herd/Stellar/voyager-proxy/test-ui
http-server -p 8080
```

Puis ouvrir : **http://localhost:8080**

### Méthode 3 : Servir avec PHP

```bash
cd /Users/w/Herd/Stellar/voyager-proxy/test-ui
php -S localhost:8080
```

Puis ouvrir : **http://localhost:8080**

### Méthode 4 : Ouvrir directement le fichier

**Double-cliquer** sur `index.html` (fonctionne mais WebSocket peut avoir des limitations CORS)

---

## ⚙️ Configuration

1. **Démarrer le proxy Voyager** d'abord :
   ```bash
   cd /Users/w/Herd/Stellar/voyager-proxy
   npm run dev
   ```

2. **Ouvrir l'interface de test** : http://localhost:8080

3. **Configurer** :
   - URL du Proxy : `http://localhost:3000` (par défaut)
   - API Key : (si configurée dans le proxy)

4. **Tester la connexion** : Cliquer sur "🔌 Tester Connexion"

---

## 🎯 Fonctionnalités

### ✅ Tests API REST

- **Health Check** - Vérifier que le proxy fonctionne
- **Connection Status** - État de la connexion à Voyager
- **Dashboard State** - Récupérer l'état complet du système
- **Enable Dashboard Mode** - Activer le mode Dashboard dans Voyager

### 🎮 Commandes de Contrôle

- **Abort Session** - Arrêter immédiatement la session en cours
- **Toggle Target** - Activer/désactiver une cible RoboTarget
- **Take Shot** - Prendre une photo (exposition, binning, filtre)
- **Telescope Control** - Park, Unpark, Start/Stop Tracking

### 📊 Dashboard Temps Réel

Affichage en temps réel via WebSocket de :

- **Voyager** - Statut global, setup connecté
- **Caméra** - Température, puissance, cooling
- **Monture** - Position (RA/DEC), tracking, park
- **Focuser** - Position, température
- **Séquence** - Nom, temps restant
- **Guidage** - Statut, RMS X/Y

### 📡 Événements WebSocket

Console en temps réel des événements :

- `controlData` - État système (toutes les 2s)
- `newJPG` - Aperçu caméra Base64
- `shotRunning` - Progression exposition
- `signal` - Changements d'état
- `newFITReady` - Nouvelle image FITS
- `remoteActionResult` - Résultats commandes
- `connectionState` - État connexion Voyager

### 📝 Console de Logs

Tous les logs de l'interface avec horodatage.

---

## 🔍 Scénario de Test Complet

### 1. Vérification de Base

```
1. Tester Connexion
2. Connection Status
3. Enable Dashboard Mode
4. Dashboard State
```

**Résultats attendus :**
- ✅ API Status : Connecté
- ✅ Voyager Status : Connecté
- ✅ Dashboard data visible

### 2. Connexion WebSocket

```
1. Cliquer "Connecter WebSocket"
2. Vérifier WebSocket Status : Connecté
3. Observer les événements dans la console
```

**Résultats attendus :**
- ✅ WebSocket Status : Connecté
- ✅ Événement `initialState` reçu
- ✅ Dashboard se met à jour automatiquement
- ✅ Événements `controlData` toutes les 2s

### 3. Tests de Commandes

**Test Telescope :**
```
1. Start Tracking → OK
2. Stop Tracking → OK
3. Park → OK
4. Unpark → OK
```

**Test Shot :**
```
1. Remplir : Exposure = 1, Binning = 1, Filter = 0
2. Cliquer "Prendre Photo"
3. Observer événement "shotRunning" dans console WebSocket
4. Observer événement "newFITReady" quand terminé
```

**Test Abort :**
```
1. Pendant une exposition
2. Cliquer "Arrêter"
3. Observer Signal 503 (Action Stopped)
```

### 4. Vérification Dashboard Temps Réel

**Observer pendant 30 secondes :**
- Température caméra se met à jour
- Position RA/DEC change (si tracking)
- RMS guidage fluctue
- Temps restant séquence décrémente

---

## 🐛 Troubleshooting

### Problème : CORS Error

**Solution :** Utiliser un serveur HTTP (Python/Node/PHP) au lieu d'ouvrir directement le fichier.

### Problème : WebSocket ne se connecte pas

**Vérifications :**
1. Le proxy tourne bien ? `http://localhost:3000/health`
2. URL correcte dans config ?
3. Console navigateur pour erreurs JS

### Problème : 401 Unauthorized

**Solution :**
- Vérifier API Key dans configuration
- Ou désactiver API_KEY dans `.env` du proxy pour les tests

### Problème : Pas de données Dashboard

**Solution :**
1. Cliquer "Enable Dashboard Mode"
2. Attendre 2-3 secondes
3. Cliquer "Dashboard State"

---

## 📊 Indicateurs de Santé

### Statuts Attendus (si tout fonctionne)

- 🟢 **API Status** : Connecté
- 🟢 **WebSocket Status** : Connecté
- 🟢 **Voyager Status** : Connecté

### Dashboard Voyager (valeurs normales)

- **Voyager Status** : `IDLE` ou `RUN`
- **Setup** : `✅ Oui`
- **Caméra Connectée** : `✅ Oui`
- **Monture Connectée** : `✅ Oui`
- **Focuser Connecté** : `✅ Oui`

---

## 🎨 Personnalisation

L'interface utilise un thème sombre spatial. Pour modifier :

**Couleurs** : Éditer `style.css` section `:root`

```css
:root {
    --bg-dark: #0a0c0f;
    --accent-blue: #4FC3F7;
    --accent-purple: #9C27B0;
    /* etc. */
}
```

---

## 📝 Notes

- L'interface **sauvegarde automatiquement** l'URL et l'API Key dans localStorage
- Les logs et événements sont **limités aux 100 derniers**
- L'auto-scroll est **activable/désactivable**
- Les événements `controlData` sont **masquables** (très verbeux toutes les 2s)

---

## 🚀 Après validation

Une fois que tous les tests passent, vous êtes prêt pour :

1. **Déployer le proxy** sur votre serveur cloud
2. **Intégrer avec Laravel** (modifier VoyagerService)
3. **Créer l'interface utilisateur** finale dans Laravel

---

**Interface créée avec ❤️ pour tester Stellar Voyager Proxy**
