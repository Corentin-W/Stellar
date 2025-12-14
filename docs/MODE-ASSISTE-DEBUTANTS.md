# 🌌 Mode Assisté pour Débutants - Guide Complet

## 🎯 Objectif

Le **Mode Assisté** a été créé pour permettre aux débutants en astrophotographie de créer facilement leurs premières cibles d'observation sans avoir à maîtriser tous les paramètres techniques.

## ✨ Nouvelles Fonctionnalités

### 1. **Page d'accueil avec choix du mode**

Quand vous créez une nouvelle target sur `/robotarget/create`, vous voyez maintenant :

#### Mode Assisté 🌱 (Recommandé pour débutants)
- **Catalogue d'objets populaires** préconfigurés
- **Paramètres recommandés** automatiquement appliqués
- **Explications détaillées** pour chaque objet
- **Filtrés par difficulté** (débutant, intermédiaire, avancé)

#### Mode Manuel ⚙️ (Pour experts)
- **Contrôle total** de tous les paramètres
- **Coordonnées personnalisées**
- **Configuration avancée**

### 2. **Catalogue de 8 objets populaires**

Le catalogue inclut des cibles soigneusement sélectionnées :

#### 🌱 Débutant
1. **M42 - Grande Nébuleuse d'Orion**
   - La plus belle nébuleuse! Parfaite pour débuter
   - Temps estimé: 2h20min
   - Visible: Nov-Mar

2. **M31 - Galaxie d'Andromède**
   - Notre voisine galactique, immense et spectaculaire
   - Temps estimé: 3h45min
   - Visible: Sep-Déc

3. **M13 - Grand Amas d'Hercule**
   - Amas globulaire spectaculaire
   - Temps estimé: 2h
   - Visible: Avr-Aoû

4. **M27 - Nébuleuse de l'Haltère**
   - Nébuleuse planétaire brillante et facile
   - Temps estimé: 3h3min
   - Visible: Juin-Sep

#### ⭐ Intermédiaire
5. **M51 - Galaxie du Tourbillon**
   - Galaxie spirale magnifique avec compagnon
   - Temps estimé: 6h40min
   - Visible: Mar-Juin

6. **NGC 7000 - Nébuleuse North America**
   - Grande nébuleuse diffuse
   - Temps estimé: 7h15min
   - Visible: Juin-Sep

7. **M57 - Nébuleuse de l'Anneau**
   - Nébuleuse planétaire iconique
   - Temps estimé: 5h30min
   - Visible: Mai-Sep

#### 🚀 Avancé
8. **IC 1396 - Nébuleuse de la Trompe d'Éléphant**
   - Région HII complexe, projet ambitieux
   - Temps estimé: 9h10min
   - Visible: Jui-Oct

## 🎬 Comment utiliser le Mode Assisté

### Étape 1 : Sélection du mode
1. Allez sur `/robotarget/create`
2. Cliquez sur **"Mode Assisté"**

### Étape 2 : Filtrer le catalogue
Choisissez un niveau de difficulté :
- **🌱 Débutant** : Objets brillants, faciles à imager
- **⭐ Intermédiaire** : Requiert plus de temps et nuit noire
- **🚀 Avancé** : Filtres à bande étroite, longue exposition

### Étape 3 : Sélectionner un objet
Parcourez le catalogue et cliquez sur l'objet qui vous intéresse.

Chaque objet affiche :
- ✅ **Nom et type** (Galaxie, Nébuleuse, Amas)
- ✅ **Description** courte
- ✅ **Temps estimé** d'acquisition
- ✅ **Meilleures périodes** d'observation
- ✅ **Difficulté**

### Étape 4 : Personnalisation (optionnelle)
Une fois l'objet chargé :
- ✅ Les **coordonnées** sont pré-remplies
- ✅ Les **filtres et expositions** sont recommandés
- ✅ Les **contraintes** sont adaptées à votre abonnement

**Vous pouvez modifier ces paramètres** si vous le souhaitez !

### Étape 5 : Validation
Vérifiez l'estimation de crédits et créez votre target.

## 📊 Ce qui est automatiquement configuré

### Pour chaque objet, nous pré-remplissons :

1. **Coordonnées J2000**
   - RA (Ascension Droite)
   - DEC (Déclinaison)

2. **Plan d'acquisition recommandé**
   - Filtres (L, R, G, B, Ha, OIII, SII)
   - Nombre de poses
   - Temps d'exposition
   - Binning

3. **Contraintes selon difficulté**
   - **Débutant** : Priorité basse, pas de nuit noire
   - **Intermédiaire** : Priorité normale
   - **Avancé** : Priorité élevée + nuit noire + HFD

## 💡 Conseils pour les Débutants

### ✅ Commencez par M42 ou M31
Ces objets sont très lumineux et tolèrent les erreurs de configuration.

### ✅ Respectez les périodes d'observation
Chaque objet a des "meilleures périodes" indiquées. Hors de ces périodes, l'objet sera trop bas sur l'horizon.

### ✅ Vérifiez votre solde de crédits
L'estimation est affichée avant de valider. Assurez-vous d'avoir assez de crédits.

### ✅ Commencez avec votre plan Stardust
Les objets "débutant" sont optimisés pour le plan Stardust (Priority 0-1).

### ✅ Comprenez les multiplicateurs
- **Priorité** : Plus haute = plus cher mais traité en premier
- **Nuit noire** : ×2 crédits mais qualité maximale
- **Garantie HFD** : ×1.5 crédits pour images nettes garanties

## 🎓 Progression suggérée

### Semaine 1-2 : Débutant
1. M42 (Orion) - Apprenez les bases
2. M31 (Andromède) - Découvrez les galaxies
3. M13 (Hercule) - Testez les amas

### Mois 1-3 : Intermédiaire
4. M51 (Tourbillon) - Galaxies plus faibles
5. NGC 7000 - Nébuleuse étendue
6. M57 (Anneau) - Nébuleuse planétaire

### Mois 3+ : Avancé
7. IC 1396 - Filtres à bande étroite
8. Projets personnalisés en mode manuel

## 🔄 Passer en Mode Manuel

Une fois à l'aise avec le Mode Assisté :

1. Créez une nouvelle target
2. Choisissez **"Mode Manuel"**
3. Saisissez vos propres coordonnées
4. Configurez librement tous les paramètres

## ❓ FAQ

### **Q : Puis-je modifier les paramètres suggérés ?**
**R :** Oui ! Les templates sont un point de départ. Vous pouvez tout personnaliser.

### **Q : Pourquoi certains objets nécessitent-ils plus de crédits ?**
**R :** Les objets avancés utilisent :
- Plus de poses (temps d'occupation du télescope)
- Filtres à bande étroite (expositions plus longues)
- Option nuit noire (×2 multiplicateur)

### **Q : Que signifie "overhead technique" ?**
**R :** Le temps entre chaque pose pour :
- Lire le capteur (~5-10s)
- Sauvegarder le fichier FITS (~5s)
- Vérifier le guidage (~5-10s)
Environ **30 secondes par pose**.

### **Q : Puis-je créer plusieurs targets en même temps ?**
**R :** Oui ! Créez autant de targets que vous voulez. Le système Voyager les gérera selon leur priorité.

### **Q : Comment savoir si j'ai assez de crédits ?**
**R :** L'estimation apparaît à l'étape 3. Un indicateur vous dit si votre solde est suffisant.

---

## 🎉 Bonne observation !

Le Mode Assisté est conçu pour vous faire gagner du temps et éviter les erreurs. Profitez de l'astrophotographie sans vous soucier des détails techniques !

**Besoin d'aide ?** Contactez le support via la page Support dans le menu.
