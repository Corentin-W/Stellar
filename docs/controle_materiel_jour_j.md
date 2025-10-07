# Controle du materiel Jour J

Ce document decrit l'ensemble du fonctionnement mis en place pour permettre aux utilisateurs de prendre la main sur le materiel le jour de leur reservation, incluant le panneau de controle, l'apercu camera et l'integration du flux webcam externe.

## 1. Vue d'ensemble

| Element                    | Description                                                                 |
|---------------------------|-----------------------------------------------------------------------------|
| Declenchement             | Des qu'un utilisateur possede une reservation confirmee dont la fenetre d'acces est ouverte (ou imminente). |
| Cote interface            | Un bloc *Controle du materiel* s'affiche automatiquement (sidebar + page d'acces). |
| Donnees temps reel        | Recuperees via le service `VoyagerService` (proxy Node.js) avec repli *mock* si le proxy est indisponible. |
| Actions utilisateur       | Rafraichir l'etat, arreter la session, activer/desactiver la cible, consulter l'apercu camera, ouvrir la webcam live. |

## 2. Sidebar utilisateur

Fichier : `resources/views/layouts/partials/astral-sidebar.blade.php`

- A l'authentification, on recherche la prochaine reservation confirmee sur la journee (`EquipmentBooking`).
- Si la fenetre est *upcoming* ou *active*, un bloc dedie apparait :
  - Nom de l'equipement.
  - Plage horaire localisee.
- Compteur (decompte avant demarrage ou temps restant).
- Bouton vers la page d'acces (`bookings.access`).
- Le compteur est anime cote client via un script `@once` qui rafraichit la page automatiquement a la fin du decompte.

## 3. Page d'acces au materiel (`resources/views/bookings/access.blade.php`)

### 3.1 Informations generales

- Statut de la reservation, horaires, fuseau horaire.
- Messages dedies pour chaque etat (`pending`, `upcoming`, `active`, `finished`, `cancelled`).
- Pour l'etat `active`, affichage des etapes recommandees et des infos tarifaires.

### 3.2 Panneau de controle (Alpine.js)

Composant `bookingControlPanel` :

| Fonction                               | Détails |
|----------------------------------------|---------|
| Polling etat Voyager                   | 15 secondes (`/bookings/{booking}/control/status`). |
| Gestion commandes                      | `abort` (arret immediate), `toggle` (activation/desactivation de la cible). |
| Affichage sequence                     | Progression, exposition courante, nombre de prises. |
| Equipements                            | Statut de la monture, camera, focuser (donnees proxy ou mock). |
| Notifications                          | Utilise `window.showNotification` quand disponible. |
| Visibilite onglet                      | Suspend/reprend le polling quand la fenetre est masquee. |

### 3.3 Bloc Caméra

- Apercu `preview` via `/bookings/{booking}/control/preview` (Base64/JPG) affiche dans une carte dediee.
- Bouton *Actualiser l'apercu* (desactive si l'URL de preview n'est pas configuree).
- Lien *Webcam live* vers `VOYAGER_WEBCAM_URL` ouvrant la page publique `http://185.228.120.120:23003/public.php`.
- Si la preview est indisponible, un SVG de secours est affiche avec mention *mode demo*.

## 4. API Laravel

Routes déclarées dans `routes/web.php` (zone réservations) :

| Route | Méthode | Contrôleur | Rôle |
|-------|---------|------------|------|
| `/bookings/{booking}/control/status` | GET | `BookingControlController@status` | Statut temps reel (sequence, equipements). |
| `/bookings/{booking}/control/abort`  | POST| `BookingControlController@abort`  | Commande d'arret de la cible. |
| `/bookings/{booking}/control/toggle` | POST| `BookingControlController@toggle` | Activation/Desactivation RoboTarget. |
| `/bookings/{booking}/control/preview`| GET | `BookingControlController@preview` | Apercu camera (Base64). |

Sécurité :

- Toutes les routes sont protegees par `auth` et verifient que la reservation appartient a l'utilisateur (`assertOwnership`).
- Les actions sont autorisees uniquement si l'etat est `active` ou `upcoming`.

## 5. Service `VoyagerService`

Fichier : `app/Services/VoyagerService.php`

| Méthode                | Description |
|------------------------|-------------|
| `getControlOverview()` | Appelle `/api/dashboard/state` sur le proxy. Fournit un mock détaillé si indisponible. |
| `abortTarget()`        | POST `/api/control/abort` (mock si proxy indisponible). |
| `toggleObject()`       | POST `/api/control/toggle` (mock si proxy indisponible). |
| `getCameraPreview()`   | GET `/api/camera/preview` avec repli sur un SVG informatif. |

> **Requis côté proxy Node.js** : implémenter les endpoints REST correspondants (`/api/dashboard/state`, `/api/control/abort`, `/api/control/toggle`, `/api/camera/preview`). Sans cela, le système bascule en mode démo.

## 6. Configuration

`config/services.php` :

```php
'voyager' => [
    'proxy_url' => env('VOYAGER_PROXY_URL', 'http://localhost:3000'),
    'profile' => env('VOYAGER_PROFILE', 'Default.v2y'),
    'default_sequence' => env('VOYAGER_DEFAULT_SEQUENCE_GUID'),
    'webcam_url' => env('VOYAGER_WEBCAM_URL'),
],
```

`.env` :

```dotenv
VOYAGER_PROXY_URL=http://localhost:3000
VOYAGER_PROFILE=Default.v2y
VOYAGER_DEFAULT_SEQUENCE_GUID=
VOYAGER_WEBCAM_URL=http://185.228.120.120:23003/public.php
```

## 7. Modèle `EquipmentBooking`

- Champs ajoutes : `voyager_set_guid`, `voyager_target_guid` (UUID, `nullable`).
- `getAccessState()` et les helpers (`secondsUntilStart`, `secondsUntilEnd`, `isAccessWindowOpen`) pilotent la visibilite des commandes.

## 8. Scénario utilisateur

1. **Avant le créneau** : l'utilisateur voit depuis la sidebar l'équipement réservé avec un décompte.
2. **Accès à la page de contrôle** :
   - Vue d'état (statut de la réservation, timers).
   - Panneau de contrôle (statut Voyager, séquence, actions).
   - Carte caméra avec aperçu + lien live.
3. **Pendant la session** :
   - Mise à jour automatique toutes les 15 s.
   - Possibilité d'interrompre la session ou désactiver la cible.
4. **Après la session** :
   - Le panneau bascule sur les états *terminé* ou *annulé* et les boutons sont désactivés.

## 9. Tests recommandés

| Test | Étapes |
|------|--------|
| Scénario `upcoming` | Simuler une réservation confirmée qui débute dans quelques minutes. Vérifier le bloc sidebar + compte à rebours. |
| Scénario `active`   | Simuler un créneau en cours. Vérifier l'affichage du panneau, le rafraîchissement, l'appel aux routes `/control/*`. |
| Proxy offline       | Arrêter le proxy Node.js. Confirmer que le mode démo s'affiche (données mock + SVG). |
| Webcam live         | Cliquer sur le bouton *Webcam live* et vérifier l'ouverture du flux `http://185.228.120.120:23003/public.php`. |

## 10. Points d'intégration restants

- Déployer ou configurer le proxy Voyager afin qu'il expose les routes REST attendues.
- Sécuriser le flux webcam si besoin (authentification, HTTPS).
- Étendre le service Laravel si de nouvelles commandes Voyager doivent être supportées (focus, changement de filtre, etc.).

---

*Derniere mise a jour : 2025-02-15*
