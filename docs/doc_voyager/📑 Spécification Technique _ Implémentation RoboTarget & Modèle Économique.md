Voici le **Plan Directeur de Mise en Œuvre Technique et Commerciale** pour le projet *Astral Stellar*.

Ce document est conçu pour être remis directement à l'équipe de développement (Laravel/Node.js) et à Mike (Product Owner). Il fait le lien entre vos choix commerciaux (Stardust/Nebula/Quasar) et les contraintes techniques strictes de l'API Voyager RoboTarget.

---

# **📑 Spécification Technique : Implémentation RoboTarget & Modèle Économique**

**Projet :** Astral Stellar **Version API Voyager :** RoboTarget NDA Protocol Ver 1.0 **Date :** 12 Septembre 2025

---

## **1\. Architecture & Flux de Données**

Pour garantir la stabilité (Heartbeat) et la sécurité, nous maintenons l'architecture en deux couches définie dans le *Protocole de Connexion*.

* **Front-End (Astral Theme) :** Interface utilisateur pour la configuration des cibles (Target Planner).  
* **Back-End (Laravel 12\) :**  
  * Gère la logique métier (Abonnements, Crédits, Calcul des coûts).  
  * Gère la base de données utilisateurs et transactions.  
  * **Ne communique jamais directement** avec Voyager via TCP (trop lent/stateless).  
* **Proxy (Node.js) :**  
  * Maintient le tunnel TCP (Port 5950\) ouvert avec Voyager.  
  * Gère le Heartbeat (toutes les 5s).  
  * Authentifie la connexion et active le mode `RemoteSetRoboTargetManagerMode`.

---

## **2\. Implémentation des Offres Commerciales (Business Logic)**

Cette section définit comment traduire les abonnements choisis en paramètres JSON pour la commande `RemoteRoboTargetAddTarget`.

### **2.1 Abonnement STARDUST (29€ \- 20 Crédits)**

*Cible : Débutant / Eco*

* **Restrictions API (Laravel Middleware) :**  
  * **Priority :** Forcée à `0` (Very Low) ou `1` (Low).  
  * **Nuit Noire (`C_MoonDown`) :** Forcée à `false`.  
  * **Garantie Netteté (`C_HFDMeanLimit`) :** Désactivée (valeur `0`).  
  * **Mode :** "One Shot" uniquement (paramètre `IsRepeat` \= `false`).  
* **Comportement :** L'utilisateur remplit les trous du planning. Si la lune est là, l'image est prise quand même.

### **2.2 Abonnement NEBULA (59€ \- 60 Crédits)**

*Cible : Amateur Confirmé*

* **Permissions API :**  
  * **Priority :** Accès autorisé jusqu'à `2` (Normal).  
  * **Nuit Noire (`C_MoonDown`) :** Option débloquée (cochant cette case coûte x2 crédits).  
  * **Dashboard :** Accès temps réel activé via `RemoteSetDashboardMode`.  
  * **Garantie Netteté :** Standard uniquement (valeur forcée à `4.0` pixels, pas de remboursement strict).

### **2.3 Abonnement QUASAR (119€ \- 150 Crédits)**

*Cible : Expert / VIP*

* **Privilèges API :**  
  * **Priority :** Accès à `3` (High) et `4` (First). *Le coupe-file.*  
  * **Garantie Netteté :** Accès complet au curseur `C_HFDMeanLimit` (ex: \< 2.5 px). *Si l'image est floue, elle est rejetée.*  
  * **Projets Multi-nuits :** Accès au paramètre `IsRepeat` à `true` et utilisation des "Sets" (`RemoteRoboTargetAddSet`) pour grouper les mosaïques.

---

## **3\. Le Moteur de Crédits (Pricing Engine)**

Le coût en crédits est calculé par Laravel **avant** l'envoi de la commande.

### **Formule de Calcul**

Coût\_Final \= (Durée\_Estimee \* Coût\_Base\_Horaire) \* Multiplicateurs

### **Tableau des Multiplicateurs**

| Option | Paramètre API | Condition | Multiplicateur |
| ----- | ----- | ----- | ----- |
| **Priorité Eco** | `Priority` \= 0/1 | Stardust | **x 1.0** |
| **Priorité Standard** | `Priority` \= 2 | Nebula | **x 1.2** |
| **Priorité High** | `Priority` \= 3 | Quasar | **x 2.0** |
| **Priorité VIP (First)** | `Priority` \= 4 | Quasar | **x 3.0** |
| **Nuit Noire** | `C_MoonDown` \= true | Nebula/Quasar | **x 2.0** |
| **Garantie HFD** | `C_HFDMeanLimit` \> 0 | Quasar | **x 1.5** |

### **Gestion de la Facturation (Cycle de vie)**

1. **Hold (Réservation) :** À la commande, les crédits sont "gelés" mais pas détruits.  
2. **Exécution :** Voyager traite la cible.  
3. **Capture ou Remboursement :**  
   * Interrogation de `RemoteRoboTargetGetSessionListByTarget`.  
   * Si `Result` \== `1` (OK) ➔ **Débit définitif.**  
   * Si `Result` \== `2` (Aborted) ou `3` (Error) ➔ **Remboursement automatique** (Déblocage des crédits).  
   * *Note :* C'est l'argument commercial "Satisfait ou Remboursé" techniquement viable grâce au retour d'état du robot.

---

## **4\. Documentation Technique de Mise en Place**

### **Étape 1 : Préparation du Payload JSON (Laravel)**

Lorsqu'un utilisateur crée une cible, Laravel doit construire l'objet JSON complexe requis par `RemoteRoboTargetAddTarget`.

**Attention au Paramètre `C_Mask` :** Vous devez générer dynamiquement la chaîne de caractères `C_Mask` pour indiquer à Voyager quelles contraintes respecter.

* Si Nuit Noire est cochée ➔ Ajouter "K" (Moon Down).  
* Si Netteté est activée ➔ Ajouter "O" (HFD Sub Max).  
* *Exemple :* `C_Mask: "ABK"` signifie "Angle, Altitude, MoonDown".

**Exemple de Payload JSON généré par Laravel (pour un user Nebula) :**

{  
  "method": "RemoteRoboTargetAddTarget",  
  "params": {  
    "UID": "UUID\_GENERATED\_BY\_LARAVEL",  
    "TargetName": "M42 Nebula",  
    "Priority": 2,            // Niveau Nebula  
    "C\_MoonDown": true,       // Option Nuit Noire activée  
    "C\_Mask": "BK",           // B=Altitude, K=MoonDown  
    "C\_AltMin": 30,  
    "RefGuidSet": "USER\_SET\_UUID", // ID du dossier utilisateur  
    // ... autres coordonnées RA/DEC ...  
    "MAC": "HASH\_DE\_SECURITE" // Calculé avec le Timestamp SessionKey  
  },  
  "id": 1  
}

### **Étape 2 : Intégration UX (Thème Astral)**

Utilisez les composants du *Design System Astral* pour refléter les abonnements :

1. **Sidebar Astrale \- Target Planner :**

   * Ajouter un indicateur de quota (ex: "40/60 Crédits").  
   * Si l'utilisateur est **Stardust**, les toggles "Nuit Noire" et "Priorité" doivent apparaître avec un cadenas 🔒 (débloquable par upgrade).  
2. **Dashboard Cosmique :**

   * Utiliser la commande `RemoteRoboTargetGetSessionContainerCountByTarget` pour récupérer le champ `Progress`.  
   * Afficher ce pourcentage dans les cartes de cibles anamorphiques.  
3. **Galerie et Preuve de Qualité :**

   * Pour les utilisateurs **Quasar** qui paient la garantie netteté, récupérez les métadonnées de l'image via `RemoteRoboTargetGetShotJpg`.  
   * Affichez fièrement la valeur `HFD` et `StarIndex` à côté de l'image pour prouver que la garantie a été respectée.

### **Étape 3 : Gestion des Erreurs et Robustesse**

Selon la documentation, Voyager peut renvoyer des erreurs si la cible est impossible (ex: sous l'horizon).

* Surveillez le retour `ParamRet` : s'il contient "ERROR", affichez une notification "Toast" rouge du thème Astral à l'utilisateur : *"Cible impossible : Vérifiez vos coordonnées"*.  
* Vérifiez que le mode `RemoteSetRoboTargetManagerMode` est bien activé au démarrage du Proxy, sinon toutes les créations de cibles échoueront.

---

## **5\. Résumé des Tarifs pour Configuration Stripe**

| Plan | ID Stripe (Sug.) | Prix | Crédits mensuels | Coût unitaire du crédit (implied) |
| ----- | ----- | ----- | ----- | ----- |
| **Stardust** | `sub_stardust` | 29 € | 20 | 1.45 € |
| **Nebula** | `sub_nebula` | 59 € | 60 | 0.98 € |
| **Quasar** | `sub_quasar` | 119 € | 150 | 0.79 € |

| Pack Crédits | ID Stripe (Sug.) | Prix | Quantité |
| ----- | ----- | ----- | ----- |
| **Pack Small** | `pack_20` | 10 € | 20 |
| **Pack Medium** | `pack_100` | 45 € | 100 |
| **Pack Large** | `pack_250` | 99 € | 250 |

