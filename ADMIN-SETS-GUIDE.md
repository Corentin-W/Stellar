# 🎯 Guide - Page Admin RoboTarget Sets

## 📍 Accès à la page

URL: **http://localhost:8000/admin/robotarget/sets**

⚠️ **Authentification requise** : Vous devez être connecté en tant qu'administrateur

## ✨ Fonctionnalités

### 1. 📊 Vue d'ensemble

La page affiche:
- **Total des Sets** : Nombre total de Sets dans Voyager
- **Sets actifs** : Nombre de Sets avec status = 0
- **Sets inactifs** : Nombre de Sets avec status = 1
- **Nombre de profils** : Profils Voyager différents utilisés

### 2. 🔍 Recherche et filtres

#### Barre de recherche
- Recherche par **nom du Set**
- Recherche par **tag**
- Recherche par **nom du profil**

#### Filtres
- **Par statut** : Tous / Actifs uniquement / Inactifs uniquement
- **Par profil** : Sélectionnez un profil spécifique

### 3. 📋 Tableau des Sets

Affiche pour chaque Set:
- **Nom** avec GUID
- **Profil Voyager**
- **Tag** (si défini)
- **Statut** (Actif/Inactif)
- **Défaut** (⭐ si c'est le Set par défaut)
- **Actions** disponibles

### 4. ⚡ Actions rapides

#### 👁️ Voir
Affiche les détails complets du Set dans une modal:
- Nom
- GUID
- Profil
- Statut
- Set par défaut (Oui/Non)
- Tag
- Note

#### ✏️ Modifier
Ouvre un formulaire pour modifier:
- Nom du Set
- Profil Voyager
- Tag
- Statut (Actif/Inactif)
- Note

#### 🔒 Désactiver / 🔓 Activer
Bascule entre actif (status=0) et inactif (status=1)

#### 🗑️ Supprimer
Supprime le Set et **toutes ses Targets associées**
⚠️ **Action irréversible!** Une confirmation est demandée.

### 5. ➕ Créer un nouveau Set

Bouton **"➕ Nouveau Set"** en haut à droite

Formulaire de création:
- **Nom du Set** * (obligatoire)
- **Profil Voyager** * (obligatoire - liste déroulante)
- **Tag** (optionnel)
- **Statut** (Actif/Inactif)
- **Note** (optionnel)

### 6. 🔄 Rafraîchir

Bouton **"🔄 Rafraîchir"** pour recharger les Sets depuis Voyager

## 🎨 Interface

### En-tête
- 🎯 **Titre** : RoboTarget Sets Manager
- **Statut de connexion** : Indicateur vert (connecté) / rouge (déconnecté)
- **Actions** : Rafraîchir, Nouveau Set

### Statistiques
4 cartes affichant les métriques clés

### Barre de recherche et filtres
Pour affiner la liste

### Tableau responsive
Liste de tous les Sets avec actions

### Modals
- **Modal création/édition** : Formulaire complet
- **Modal détails** : Vue complète d'un Set

## 🔧 Technologie

- **Backend** : Laravel + Service RoboTargetSetService
- **Frontend** : Alpine.js + Tailwind CSS
- **API** : Routes AJAX pour toutes les opérations
- **MAC** : Calcul automatique avec formule `||:||`

## 📝 Exemples d'utilisation

### Créer un Set de galaxies

1. Cliquez sur **"➕ Nouveau Set"**
2. Remplissez:
   - Nom: `Galaxies d'hiver`
   - Profil: Sélectionnez votre profil
   - Tag: `galaxies`
   - Statut: `Actif`
   - Note: `Collection de galaxies pour l'hiver 2025`
3. Cliquez sur **"➕ Créer"**

### Rechercher tous les Sets d'un profil

1. Dans le filtre **"Tous les profils"**, sélectionnez votre profil
2. La liste est automatiquement filtrée

### Désactiver temporairement un Set

1. Trouvez le Set dans la liste
2. Cliquez sur **"🔒 Désactiver"**
3. Confirmez l'action
4. Le Set passe en status `Inactif` (rouge)

### Voir tous les Sets inactifs

1. Dans le filtre **"Tous les statuts"**, sélectionnez **"Inactifs uniquement"**
2. Seuls les Sets avec status=1 sont affichés

## 🔐 Sécurité

- ✅ Middleware **auth** : Connexion requise
- ✅ Middleware **admin** : Droits admin requis
- ✅ Token CSRF : Protection contre les attaques CSRF
- ✅ Validation Laravel : Toutes les entrées sont validées

## 🐛 Dépannage

### La page ne charge pas
```
Erreur: Class 'App\Http\Controllers\Admin\RoboTargetAdminController' not found
```
**Solution**: Videz le cache Laravel
```bash
php artisan route:clear
php artisan cache:clear
php artisan config:clear
```

### Les Sets ne s'affichent pas
**Vérifiez**:
1. Voyager-proxy est démarré: `cd voyager-proxy && npm run dev`
2. Voyager est connecté (indicateur en haut à droite)
3. Manager Mode est activé (logs du proxy)

### Erreur "MAC Error"
**Vérifiez**:
1. SharedSecret dans `.env` correspond à Voyager
2. Manager Mode est bien activé au démarrage du proxy

### Timeout lors de la création
**Vérifiez**:
1. Le profil sélectionné existe dans Voyager
2. Voyager répond correctement (regardez les logs du proxy)

## 📊 Routes disponibles

```
GET    /admin/robotarget/sets              - Page principale
GET    /admin/robotarget/api/sets          - Liste AJAX
POST   /admin/robotarget/api/sets          - Créer AJAX
PUT    /admin/robotarget/api/sets/{guid}   - Modifier AJAX
DELETE /admin/robotarget/api/sets/{guid}   - Supprimer AJAX
POST   /admin/robotarget/api/sets/{guid}/toggle - Activer/Désactiver AJAX
```

## 🎯 Prochaines fonctionnalités possibles

- [ ] Gestion des **Targets** (liste, création, modification)
- [ ] Gestion des **BaseSequences**
- [ ] Import/Export de Sets en JSON
- [ ] Duplication de Sets
- [ ] Statistiques avancées
- [ ] Historique des modifications

## 💡 Conseils

1. **Rafraîchissez régulièrement** si vous modifiez des Sets directement dans Voyager
2. **Utilisez les tags** pour organiser vos Sets par catégories
3. **Désactivez plutôt que supprimer** si vous n'êtes pas sûr
4. **Vérifiez le profil** avant de créer un Set (il doit exister dans Voyager)

## ✅ Checklist avant utilisation

- [ ] Voyager est démarré
- [ ] Voyager-proxy tourne (`npm run dev`)
- [ ] Manager Mode est activé (vérifiez les logs)
- [ ] Vous êtes connecté en tant qu'admin
- [ ] Le port 3003 est accessible

---

**Page créée avec ❤️ pour gérer vos Sets RoboTarget facilement!**
