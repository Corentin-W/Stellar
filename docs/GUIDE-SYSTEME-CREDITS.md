# 📚 Guide Utilisateur - Système de Crédits RoboTarget

## 🎯 Introduction

Le système RoboTarget fonctionne avec des **crédits** qui représentent le temps d'occupation du télescope. Ce guide explique comment les crédits sont calculés et comment optimiser vos observations.

---

## 💡 Principe de base : 1 crédit = 1 heure

**IMPORTANT** : 1 crédit = 1 heure d'**occupation totale** du télescope, pas seulement le temps d'exposition.

### Ce qui est inclus dans le calcul :

1. **Temps d'exposition** : La durée totale de vos poses
2. **Overheads techniques** (~30 secondes par pose) :
   - Lecture du capteur CCD/CMOS (~5-10s)
   - Sauvegarde du fichier FITS (~5s)
   - Vérification du guidage (~5-10s)
   - Temps système divers (~5s)

---

## 📊 Calcul détaillé d'une target

### Formule complète :

```
Crédits = (Temps_Exposition + Temps_Overheads) × Multiplicateurs

Où :
  Temps_Exposition = Somme de (durée_pose × nombre_poses)
  Temps_Overheads = Nombre_total_poses × 30 secondes
  Multiplicateurs = Priority × MoonDown × HFD
```

### Exemple concret : Target M31

**Configuration :**
- 10 poses Luminance × 5 minutes
- 10 poses Red × 3 minutes
- 10 poses Green × 3 minutes
- 10 poses Blue × 3 minutes

**Calcul étape par étape :**

```
1. Temps d'exposition :
   • Luminance : 10 × 5min = 50 min
   • Red       : 10 × 3min = 30 min
   • Green     : 10 × 3min = 30 min
   • Blue      : 10 × 3min = 30 min
   → Total exposition : 140 minutes

2. Overheads techniques :
   • Nombre total de poses : 40
   • 40 poses × 30s = 1200s = 20 minutes

3. Temps total d'occupation :
   • 140 min + 20 min = 160 minutes ≈ 2.67 heures

4. Coût de base :
   • 2.67 heures ≈ 3 crédits (arrondi au crédit supérieur)
```

**Sans options :** 3 crédits

**Avec multiplicateurs :**
- Avec priorité normale (×1.2) : 4 crédits
- Avec nuit noire (×2.0) : 6 crédits
- Avec nuit noire + garantie HFD (×2.0 × ×1.5) : 9 crédits

---

## 🎯 Les multiplicateurs

### 1. Priorité (selon votre plan)

| Priority | Label | Multiplicateur | Plans autorisés |
|----------|-------|----------------|-----------------|
| 0-1 | Très basse / Basse | ×1.0 | Tous |
| 2 | Normale | ×1.2 | Nebula, Quasar |
| 3 | Haute | ×2.0 | Quasar |
| 4 | Très haute (Coupe-file) | ×3.0 | Quasar |

**Impact :** Plus la priorité est élevée, plus vite votre target sera traitée dans la file d'attente.

### 2. Nuit noire (MoonDown)

| Option | Multiplicateur | Plans autorisés |
|--------|----------------|-----------------|
| Désactivé | ×1.0 | Tous |
| Activé 🌙 | ×2.0 | Nebula (option), Quasar (inclus) |

**Impact :** Garantit que vos acquisitions se feront **uniquement quand la lune est couchée**, pour minimiser la pollution lumineuse.

**Quand l'utiliser :**
- ✅ Objets faibles (nébuleuses, galaxies lointaines)
- ✅ Imagerie en bande étroite (Ha, OIII, SII)
- ❌ Objets brillants (lune, planètes, amas globulaires)

### 3. Garantie netteté HFD

| Option | Multiplicateur | Plans autorisés |
|--------|----------------|-----------------|
| Désactivé | ×1.0 | Stardust, Nebula |
| HFD fixe 4.0 | ×1.0 | Nebula (inclus) |
| HFD ajustable (1.5-4.0) ⭐⭐⭐ | ×1.5 | Quasar |

**Impact :** Garantit que seules les images avec un HFD (Half Flux Diameter) inférieur au seuil seront conservées.

**HFD c'est quoi ?**
- Indicateur de netteté des étoiles
- Plus c'est bas, plus c'est net
- HFD < 2.5 = excellent seeing
- HFD > 4.0 = seeing médiocre

**Garantie :**
- Si les images dépassent le seuil HFD → Session annulée → **Crédits remboursés**

---

## 💰 Stratégies d'optimisation

### ❌ Stratégie coûteuse

```
Target M42 : 60 poses × 1 minute

Calcul :
  • Exposition : 60 × 1min = 60 min
  • Overheads : 60 × 30s = 30 min
  • Total : 90 min = 2 crédits

Problème : Beaucoup d'overheads (33% du temps !)
```

### ✅ Stratégie optimisée

```
Target M42 : 12 poses × 5 minutes

Calcul :
  • Exposition : 12 × 5min = 60 min
  • Overheads : 12 × 30s = 6 min
  • Total : 66 min = 2 crédits

Avantage :
  • Même temps d'exposition (60min)
  • 24 minutes économisées !
  • Meilleur rapport signal/bruit par pose
```

### 💡 Conseils d'optimisation

1. **Privilégiez les poses longues** (3-10 minutes) plutôt que beaucoup de poses courtes
2. **Groupez vos filtres** : Faites toutes les poses d'un filtre avant de changer
3. **Calculez le bon compromis** :
   - Poses trop courtes → beaucoup d'overheads
   - Poses trop longues → risque de saturation, difficile de rejeter les mauvaises

**Durées recommandées :**
- Luminance : 5-10 minutes
- RGB : 3-5 minutes
- Ha/OIII/SII : 10-20 minutes

---

## 🔄 Remboursement automatique

### Quand êtes-vous remboursé ?

Vos crédits sont **automatiquement remboursés** si :

1. **Météo défavorable** : Ciel couvert, vent fort, humidité élevée
2. **Problème technique** : Panne télescope, guidage défaillant, caméra HS
3. **Images floues** (si garantie HFD activée) : HFD dépasse le seuil choisi
4. **Erreur de configuration** : Cible sous l'horizon, coordonnées invalides

### Processus de remboursement

```
1. Votre target est soumise
   → Crédits "gelés" (hold)

2. Session s'exécute
   → Télescope travaille

3. Session terminée → Analyse automatique :

   ✅ Si Result = 1 (OK)
      → Crédits définitivement débités
      → Vous recevez vos images FITS

   ❌ Si Result = 2 (Aborted) ou 3 (Error)
      → Crédits REMBOURSÉS dans les 24h
      → Email de notification

4. Vous consultez votre historique
   → Détail de chaque transaction
```

---

## 📅 Renouvellement mensuel

### Comment ça marche ?

- **Chaque 1er du mois à 00:00** : Vos crédits sont renouvelés
- **Crédits mensuels** : Selon votre plan (20, 60 ou 150)
- **Crédits non utilisés** : ❌ **NE SONT PAS REPORTÉS**

### Exemple

```
Plan Nebula (60 crédits/mois)

1er janvier :
  • Renouvellement : +60 crédits
  • Solde : 60 crédits

15 janvier :
  • Vous utilisez 35 crédits
  • Solde : 25 crédits

1er février :
  • Renouvellement : +60 crédits
  • Les 25 crédits restants DISPARAISSENT
  • Nouveau solde : 60 crédits (pas 85 !)
```

### 💡 Conseil

Planifiez vos observations pour **maximiser l'utilisation** de vos crédits chaque mois !

**Outil pratique :** Votre dashboard affiche :
- Crédits utilisés ce mois
- Crédits restants
- Jours avant renouvellement

---

## 📊 Comparaison des plans

### Exemple pratique : Projet M31 (Galaxie d'Andromède)

**Configuration identique pour tous :**
- 40 poses totales (10L + 10R + 10G + 10B)
- 160 minutes d'occupation (140 min expo + 20 min overhead)
- 3 crédits de base

| Plan | Priorité max | Options | Coût final | Notes |
|------|--------------|---------|------------|-------|
| **Stardust** | 0-1 | Aucune | **3 crédits** | File normale, avec lune possible |
| **Nebula** | 0-2 | Nuit noire activée | **6 crédits** | Sans lune, meilleure qualité |
| **Quasar** | 0-4 | Nuit noire + HFD 2.0 | **14 crédits** | Coupe-file, qualité maximale garantie |

**Avec un budget de 60 crédits/mois :**
- Stardust → 20 targets (mais qualité variable)
- Nebula → 10 targets nuit noire (bonne qualité)
- Quasar → 4 targets premium (qualité exceptionnelle)

---

## ❓ FAQ

### Q: Pourquoi payer pour les overheads ?

**R:** Les overheads représentent le temps où le télescope est **mobilisé pour vous** mais ne fait pas d'exposition. Ce temps empêche d'autres utilisateurs d'utiliser le télescope. Le modèle est donc **juste pour tous**.

### Q: Puis-je réduire les overheads ?

**R:** Oui ! En faisant **moins de poses mais plus longues**.
- 60 poses × 1min = 30min d'overheads
- 12 poses × 5min = 6min d'overheads
- Économie : **24 minutes** pour la même exposition totale !

### Q: Que se passe-t-il si je n'ai pas assez de crédits ?

**R:** Vous ne pouvez pas soumettre la target. L'interface vous indique combien de crédits manquent et propose de changer de plan.

### Q: Puis-je annuler une target avant qu'elle démarre ?

**R:** Oui ! Si la target est encore en statut "pending" (pas encore soumise à Voyager), vous pouvez l'annuler et récupérer vos crédits immédiatement.

### Q: Combien de temps mes crédits sont-ils "gelés" ?

**R:** Jusqu'à la fin de la session (quelques heures max). Dès que le résultat est connu, les crédits sont soit débités définitivement (succès), soit remboursés (échec).

### Q: Puis-je acheter des crédits supplémentaires ?

**R:** Actuellement, non. Nous recommandons de **passer à un plan supérieur** si vous avez besoin de plus de crédits mensuels.

---

## 📞 Support

Des questions sur le système de crédits ?

- **Email** : support@astral-stellar.com
- **Documentation** : https://docs.astral-stellar.com
- **Discord** : https://discord.gg/astral-stellar

---

**Dernière mise à jour** : 13 décembre 2025
**Version** : 1.0
