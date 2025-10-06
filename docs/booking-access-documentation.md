# Système de déverrouillage d'accès aux réservations

## Vue d'ensemble
Ce module permet d'ouvrir automatiquement l'accès au matériel réservé lorsqu'une session confirmée démarre et de le refermer à la fin du créneau. Les utilisateurs disposent d'une page dédiée qui affiche l'état de la réservation, des compteurs synchronisés et des consignes d'utilisation.

Principaux objectifs :
- prévenir l'accès prématuré au matériel ;
- informer l'utilisateur en temps réel sur l'état de sa session ;
- centraliser les informations utiles (description, coût, localisation) pendant l'accès ;
- proposer une expérience cohérente dans « Mes réservations ».

## Architecture
| Élément | Localisation | Rôle |
| --- | --- | --- |
| `EquipmentBooking::getAccessState()` | `app/Models/EquipmentBooking.php` | Calcule l'état courant d'accès pour une réservation (pending, upcoming, active, finished, etc.). |
| `BookingController::access()` | `app/Http/Controllers/BookingController.php` | Point d'entrée sécurisé de la page d'accès. Prépare les données et vérifie les droits. |
| Route `bookings.access` | `routes/web.php` | `GET /{locale}/bookings/{booking}/access` sous middleware `auth`. |
| Vue Blade `bookings/access.blade.php` | `resources/views/bookings/access.blade.php` | IHM d'accès : compteurs, messages d'état, recommandations. |
| Mise à jour de `my-bookings.blade.php` | `resources/views/bookings/my-bookings.blade.php` | Boutons contextuels (Accéder, Page d'accès, etc.) selon l'état de la réservation. |

## États d'accès
`getAccessState()` renvoie une chaîne de contrôle utilisée partout dans l'UI :

| État | Condition | UX |
| --- | --- | --- |
| `pending` | Réservation non validée | Message d'attente, aucun bouton d'accès. |
| `upcoming` | Confirmée mais avant l'heure de début | Affiche un compte à rebours et rafraîchit automatiquement la page d'accès à l'issue. |
| `active` | Entre `start_datetime` et `end_datetime` | Bouton « Accéder au matériel », minuterie du temps restant. |
| `finished` | Après la fin de session (ou statut `completed`) | Résumé et possibilité de revoir la page d'accès. |
| `cancelled` | Statut annulé | Indique que l'accès est impossible. |
| `blocked` | Rejetée ou statut non géré | Retourne un 403 depuis le contrôleur. |

Les helpers `secondsUntilStart()` et `secondsUntilEnd()` fournissent les compteurs utilisés par les scripts JavaScript pour animer les décomptes.

## Parcours utilisateur
1. L'utilisateur consulte « Mes réservations » (`/bookings/my-bookings`).
2. Chaque carte de réservation calcule l'état via `getAccessState()` :
   - « Accéder au matériel » si la session est active.
   - « Page d'accès » + message « Débute le … » si la session est à venir.
   - Autres messages pour pending, annulée ou terminée.
3. En cliquant, l'utilisateur arrive sur `bookings.access` :
   - le compte à rebours affiche le temps restant avant l'ouverture et rafraîchit la page.
   - pendant la session, une minuterie descendante et des consignes sont visibles.
   - après la session, un panneau de fin de session est présenté.

## Sécurité & règles
- Le contrôleur vérifie que l'utilisateur est propriétaire de la réservation (`$booking->user_id === auth()->id()`).
- Statut `blocked` (réservation rejetée ou inconnue) renvoie un `403` avec message expliquant que l'accès n'est pas autorisé.
- Toute réservation hors statut `confirmed` est verrouillée via les états décrits ci-dessus.

## Personnalisation
- Fuseau horaire : `BookingController::bookingTimezone()` (configuré via `app.booking_timezone`).
- Contenu de la section « Étapes recommandées » : modifiable dans `resources/views/bookings/access.blade.php`.
- Texte et styles : les classes Tailwind utilisées respectent les conventions de l'UI astrale.

## Scripts front-end
Un script inline gère les compteurs :
- `tickCountdown()` met à jour l'affichage toutes les secondes.
- À la fin d'un compte à rebours marqué `data-refresh="true"`, la page se recharge automatiquement.

## Tests et validation
- Tests existants : `php artisan test` (Attention : `Tests/Feature/ExampleTest` échoue déjà car la route `/` redirige vers `/fr`).
- Points à tester manuellement :
  1. Réservation confirmée future ⇒ compte à rebours puis ouverture auto.
  2. Réservation confirmée en cours ⇒ bouton d'accès et minuterie active.
  3. Réservation annulée/rejetée ⇒ message d'accès refusé.
  4. Utilisateur non propriétaire ⇒ 403.

## Évolutions possibles
- Ajouter des webhooks ou tâches planifiées pour passer automatiquement les réservations `active` en `completed`.
- Intégrer un lien direct vers les outils de contrôle distant spécifiques à chaque équipement.
- Envoyer des notifications push/e-mail lors de l'ouverture prochaine de la fenêtre d'accès.

