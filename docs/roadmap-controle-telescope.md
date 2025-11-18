# 🔭 Roadmap - Contrôle Télescope en Session

> Focus sur l'expérience utilisateur pendant la session d'observation

**Dernière mise à jour** : 18 novembre 2024
**Objectif** : Permettre aux utilisateurs de contrôler le télescope en temps réel pendant leur créneau réservé

---

## 📊 État actuel du projet

### ✅ Ce qui est déjà en place

#### 1. Système de réservation
- [x] Réservation de créneaux horaires
- [x] Calcul du coût en crédits
- [x] Déduction automatique des crédits
- [x] Validation des conflits de réservation
- [x] États : pending, upcoming, active, finished, cancelled

#### 2. Contrôle d'accès temporel
- [x] Page d'accès dédiée (`/bookings/{id}/access`)
- [x] Compte à rebours avant démarrage
- [x] Minuterie pendant la session
- [x] Déverrouillage/verrouillage automatique
- [x] Vérification ownership (sécurité)

#### 3. Interface de base
- [x] Bloc sidebar avec prochaine réservation
- [x] Page d'accès avec informations session
- [x] Messages selon l'état de la réservation
- [x] Étapes recommandées affichées

#### 4. Structure backend
- [x] Modèle `EquipmentBooking` avec états
- [x] Contrôleur `BookingControlController`
- [x] Service `VoyagerService` (avec mode mock)
- [x] Routes API pour le contrôle
- [x] Configuration Voyager dans `.env`

#### 5. Frontend Alpine.js
- [x] Composant `bookingControlPanel`
- [x] Polling toutes les 15 secondes
- [x] Gestion visibilité onglet
- [x] Notifications basiques

---

## 🚧 Ce qu'il reste à faire

### Phase 1 : Proxy Voyager Node.js (CRITIQUE) 🔴

> **Sans ce proxy, aucun contrôle réel n'est possible**

#### 1.1 Serveur TCP/IP
- [ ] Connexion TCP persistante à Voyager (port 5950)
- [ ] Gestion de la reconnexion automatique
- [ ] Heartbeat automatique (polling toutes les 5s)
- [ ] Timeout et gestion déconnexion (15s)
- [ ] Support multi-instances (5950, 5951, 5952)

#### 1.2 Authentification
- [ ] Système d'authentification Base64
- [ ] Stockage sécurisé credentials
- [ ] Gestion du timeout (5s après connexion)
- [ ] Retry en cas d'échec

#### 1.3 Gestion des événements
- [ ] Écoute `Version` event à la connexion
- [ ] Traitement `ControlData` (état système toutes les 2s)
- [ ] Traitement `Signal` (changements d'état)
- [ ] Traitement `NewFITReady` (nouvelles images)
- [ ] Traitement `NewJPGReady` (aperçus Base64)
- [ ] Traitement `ShotRunning` (progression toutes les 1s)
- [ ] Traitement `RemoteActionResult` (résultats commandes)
- [ ] Gestion `ShutDown` event (fermeture propre)

#### 1.4 API REST exposée à Laravel
```javascript
GET  /api/dashboard/state         // État complet système
POST /api/control/abort            // Arrêt immédiat
POST /api/control/toggle           // Toggle cible RoboTarget
GET  /api/camera/preview           // Aperçu caméra Base64
GET  /api/status/connection        // État connexion Voyager
GET  /api/sequences/current        // Séquence en cours
POST /api/sequences/pause          // Pause séquence
POST /api/sequences/resume         // Reprise séquence
```

#### 1.5 WebSocket pour temps réel
- [ ] Serveur WebSocket (Socket.IO)
- [ ] Broadcast `ControlData` aux clients connectés
- [ ] Broadcast `NewJPGReady` pour preview live
- [ ] Broadcast `ShotRunning` pour progression
- [ ] Gestion des rooms par réservation
- [ ] Authentification WebSocket

#### 1.6 Infrastructure
- [ ] Configuration PM2 pour persistance
- [ ] Logs structurés (Winston)
- [ ] Health check endpoint
- [ ] Métriques et monitoring
- [ ] Variables d'environnement
- [ ] Documentation API (Swagger)

**Fichiers à créer :**
```
voyager-proxy/
├── package.json
├── .env.example
├── ecosystem.config.js          # PM2
├── src/
│   ├── index.js                 # Point d'entrée
│   ├── voyager/
│   │   ├── connection.js        # TCP client
│   │   ├── auth.js              # Authentification
│   │   ├── events.js            # Event handlers
│   │   └── commands.js          # Commandes RPC
│   ├── api/
│   │   ├── server.js            # Express server
│   │   ├── routes.js            # Routes REST
│   │   └── middleware.js        # Auth, CORS
│   ├── websocket/
│   │   └── server.js            # Socket.IO
│   └── utils/
│       ├── logger.js            # Winston
│       └── metrics.js           # Monitoring
└── README.md
```

---

### Phase 2 : Intégration Laravel complète 🟠

#### 2.1 Service VoyagerService amélioré
- [ ] Supprimer le mode mock
- [ ] Vraies requêtes HTTP vers proxy Node.js
- [ ] Gestion timeout et retry
- [ ] Circuit breaker (si proxy down)
- [ ] Cache des états (Redis)
- [ ] Logs détaillés des appels

#### 2.2 Broadcasting Laravel
- [ ] Configuration Laravel Echo
- [ ] Channel privé par réservation
- [ ] Broadcast événements Voyager
- [ ] Pusher ou Redis backend
- [ ] Autorisation des channels

#### 2.3 Jobs et Queue
```php
// Jobs à créer
PrepareObservationJob          // J-1 : Création Set/Target/Shots
StartObservationJob            // Heure H : Activation RoboTarget
EndObservationJob              // Fin session : Désactivation + archivage
CheckObservationHealthJob      // Toutes les 5min : Vérif état
ProcessNewImageJob             // À chaque FITS : Traitement + stockage
```

#### 2.4 Modèles et relations
- [ ] Ajouter champs à `equipment_bookings` :
  ```php
  $table->uuid('voyager_set_guid')->nullable();
  $table->uuid('voyager_target_guid')->nullable();
  $table->json('voyager_shots')->nullable();
  $table->timestamp('session_started_at')->nullable();
  $table->timestamp('session_ended_at')->nullable();
  $table->integer('images_captured')->default(0);
  $table->json('session_stats')->nullable();
  ```
- [ ] Table `observation_images` pour stocker les FITS/JPG
- [ ] Table `observation_logs` pour l'historique des événements

#### 2.5 API Controllers
```php
BookingControlController::
├── status()              // État temps réel (existant)
├── abort()               // Arrêt (existant)
├── toggle()              // Toggle (existant)
├── preview()             // Aperçu (existant)
├── pause()               // ➕ Pause séquence
├── resume()              // ➕ Reprise séquence
├── images()              // ➕ Liste images capturées
├── downloadImage($id)    // ➕ Télécharger FITS
├── stats()               // ➕ Statistiques session
└── logs()                // ➕ Logs temps réel
```

---

### Phase 3 : Interface utilisateur avancée 🟡

#### 3.1 Dashboard de contrôle temps réel

**Composant Alpine.js à améliorer**

```javascript
// resources/js/components/telescope-control.js
Alpine.data('telescopeControl', () => ({
    // État actuel
    connection: 'disconnected',
    voyagerStatus: null,
    sequence: null,
    equipment: {},
    currentShot: null,
    progress: 0,

    // WebSocket
    socket: null,

    // Données
    images: [],
    logs: [],
    stats: {},

    init() {
        this.connectWebSocket();
        this.loadInitialData();
        this.startPolling();
    },

    // Méthodes à implémenter
    connectWebSocket() {},
    handleRealtimeData(data) {},
    sendCommand(command, params) {},
    refreshPreview() {},
    pauseSequence() {},
    resumeSequence() {},
    abortSession() {},
}));
```

**Sections de la page de contrôle :**

1. **En-tête de session**
   - [ ] Nom de l'équipement + statut connexion
   - [ ] Temps écoulé / temps restant
   - [ ] Indicateur crédits consommés
   - [ ] Bouton urgence (abort)

2. **État du télescope** (temps réel)
   - [ ] Statut global (IDLE/RUN/ERROR)
   - [ ] Position (RA/DEC)
   - [ ] Tracking ON/OFF
   - [ ] Parked/Unparked
   - [ ] Icônes animées

3. **État de la caméra**
   - [ ] Température actuelle vs consigne
   - [ ] Puissance refroidissement (%)
   - [ ] Statut cooling
   - [ ] Binning actuel

4. **État du focuser**
   - [ ] Position actuelle
   - [ ] Température
   - [ ] Graphique position dans le temps

5. **Séquence en cours**
   - [ ] Nom de la séquence
   - [ ] Target actuelle
   - [ ] Filtre en cours
   - [ ] Exposition actuelle (progress bar animée)
   - [ ] Nombre de prises (5/20)
   - [ ] Temps restant séquence

6. **Guidage**
   - [ ] Statut (STOPPED/SETTLING/RUNNING)
   - [ ] RMS X/Y
   - [ ] Graphique guidage en temps réel

7. **Aperçu caméra**
   - [ ] Image preview live (WebSocket)
   - [ ] HFD (qualité focus)
   - [ ] Star Index
   - [ ] Histogramme
   - [ ] Bouton rafraîchir manuel
   - [ ] Bouton plein écran
   - [ ] Stats image (expo, filtre, bin)

8. **Images capturées**
   - [ ] Galerie miniatures (dernières 20)
   - [ ] Nom fichier + timestamp
   - [ ] Icône type (LIGHT/DARK/FLAT)
   - [ ] Bouton télécharger FITS
   - [ ] Compteur total images

9. **Contrôles utilisateur**
   - [ ] Bouton Pause séquence
   - [ ] Bouton Resume séquence
   - [ ] Bouton Arrêt session (avec confirmation)
   - [ ] Bouton Rafraîchir état
   - [ ] Toggle notifications sonores

10. **Logs temps réel**
    - [ ] Console scrollable
    - [ ] Filtres (INFO/WARNING/ERROR)
    - [ ] Timestamps
    - [ ] Auto-scroll
    - [ ] Export logs

11. **Statistiques session**
    - [ ] Durée écoulée
    - [ ] Images capturées (par filtre)
    - [ ] Temps d'exposition total
    - [ ] Qualité moyenne (HFD)
    - [ ] Taux de réussite guidage

12. **Météo et conditions**
    - [ ] Température extérieure
    - [ ] Humidité
    - [ ] Vent
    - [ ] Seeing
    - [ ] Couverture nuageuse
    - [ ] Sécurité (safe/unsafe)

#### 3.2 Responsive et mobile
- [ ] Layout adapté mobile
- [ ] Swipe entre sections
- [ ] Notifications push mobile
- [ ] Mode portrait optimisé
- [ ] Touch gestures

#### 3.3 Thème astral adapté
- [ ] Animations cosmiques sur les indicateurs
- [ ] Glow effects sur équipements actifs
- [ ] Progress bars stellaires
- [ ] Notifications avec effet nébuleuse
- [ ] Dark theme optimisé pour la nuit

---

### Phase 4 : Préparation automatique des observations 🟡

#### 4.1 Formulaire de réservation enrichi

**Actuellement manquant :** Configuration des prises de vue

```php
// À ajouter dans le formulaire de réservation
- Sélection de la cible (nom, RA, DEC)
- Plan de prise de vue :
  * Filtre (L, R, G, B, Ha, OIII, SII)
  * Durée d'exposition (secondes)
  * Nombre de prises
  * Binning
  * Gain (optionnel)
  * Offset (optionnel)
- Contraintes :
  * Altitude minimale
  * Heure angle début/fin
  * Priorité
```

**Composant Livewire à créer :**
```php
// app/Http/Livewire/BookingForm.php
class BookingForm extends Component
{
    public $equipmentId;
    public $date;
    public $startTime;
    public $duration;

    // Target
    public $targetName;
    public $targetRA;
    public $targetDEC;

    // Shots plan
    public $shots = [];

    // Constraints
    public $minAltitude = 30;
    public $haStart = -3;
    public $haEnd = 3;

    public function addShot() {}
    public function removeShot($index) {}
    public function calculateCost() {}
    public function submit() {}
}
```

**Vue Livewire :**
```blade
<div wire:loading.class="opacity-50">
    <!-- Target selection -->
    <!-- Shots table (dynamic) -->
    <!-- Constraints -->
    <!-- Cost calculator -->
    <!-- Submit button -->
</div>
```

#### 4.2 Job de préparation (J-1)

```php
// app/Jobs/PrepareObservationJob.php
class PrepareObservationJob implements ShouldQueue
{
    public function handle(VoyagerService $voyager)
    {
        // 1. Créer Set dans Voyager
        $setGuid = $this->createSet();

        // 2. Créer Target
        $targetGuid = $this->createTarget($setGuid);

        // 3. Créer Shots
        $this->createShots($targetGuid);

        // 4. Activer Set (Status = 0)
        $voyager->updateSetStatus($setGuid, 0);

        // 5. Notifier utilisateur
        $this->notifyUser();
    }

    private function createSet() {
        return $this->voyager->addSet([
            'Guid' => Str::uuid(),
            'Name' => "User_{$this->booking->user_id}_Booking_{$this->booking->id}",
            'ProfileName' => config('voyager.profile'),
            'Status' => 0, // Enabled
            'Tag' => "stellar_booking_{$this->booking->id}",
        ]);
    }

    private function createTarget($setGuid) {
        return $this->voyager->addTarget([
            'GuidTarget' => Str::uuid(),
            'RefGuidSet' => $setGuid,
            'RefGuidBaseSequence' => config('voyager.default_sequence'),
            'TargetName' => $this->booking->target_name,
            'RAJ2000' => $this->booking->target_ra,
            'DECJ2000' => $this->booking->target_dec,
            'PA' => 0,
            'DateCreation' => now()->timestamp,
            'Status' => 0,
            'Priority' => 2,
            'IsRepeat' => true,
            'Repeat' => 1,
            // Contraintes temporelles
            'C_Mask' => 'BDE',
            'C_AltMin' => $this->booking->min_altitude,
            'C_HAStart' => $this->booking->ha_start,
            'C_HAEnd' => $this->booking->ha_end,
            'C_DateStart' => $this->booking->start_datetime->timestamp,
            'C_DateEnd' => $this->booking->end_datetime->timestamp,
        ]);
    }

    private function createShots($targetGuid) {
        foreach ($this->booking->shots as $index => $shot) {
            $this->voyager->addShot([
                'GuidShot' => Str::uuid(),
                'RefGuidTarget' => $targetGuid,
                'FilterIndex' => $this->getFilterIndex($shot['filter']),
                'Num' => $shot['quantity'],
                'Bin' => $shot['binning'] ?? 1,
                'ReadoutMode' => 0,
                'Type' => 0, // LIGHT
                'Speed' => 0,
                'Gain' => $shot['gain'] ?? 0,
                'Offset' => $shot['offset'] ?? 0,
                'Exposure' => $shot['exposure'],
                'Order' => $index + 1,
                'Enabled' => true,
            ]);
        }
    }

    private function getFilterIndex($filterName) {
        return match($filterName) {
            'L' => 0,
            'R' => 1,
            'G' => 2,
            'B' => 3,
            'Ha' => 4,
            'OIII' => 5,
            'SII' => 6,
            default => 0
        };
    }
}
```

#### 4.3 API RoboTarget à implémenter

**Dans VoyagerService :**
```php
// Gestion des Sets
public function addSet(array $data): string
public function updateSet(string $guid, array $data): bool
public function deleteSet(string $guid): bool
public function getSet(string $guid): array

// Gestion des Targets
public function addTarget(array $data): string
public function updateTarget(string $guid, array $data): bool
public function deleteTarget(string $guid): bool
public function activateTarget(string $guid): bool
public function deactivateTarget(string $guid): bool

// Gestion des Shots
public function addShot(array $data): string
public function updateShot(string $guid, array $data): bool
public function deleteShot(string $guid): bool

// Queries
public function listSets(): array
public function listTargetsForSet(string $setGuid): array
public function listShotsForTarget(string $targetGuid): array
```

**Endpoints proxy Node.js correspondants :**
```javascript
POST /api/robotarget/sets
GET  /api/robotarget/sets/:guid
PUT  /api/robotarget/sets/:guid
DELETE /api/robotarget/sets/:guid

POST /api/robotarget/targets
GET  /api/robotarget/targets/:guid
PUT  /api/robotarget/targets/:guid
DELETE /api/robotarget/targets/:guid
POST /api/robotarget/targets/:guid/activate
POST /api/robotarget/targets/:guid/deactivate

POST /api/robotarget/shots
PUT  /api/robotarget/shots/:guid
DELETE /api/robotarget/shots/:guid
```

---

### Phase 5 : Automation et lifecycle session 🟢

#### 5.1 Démarrage automatique (Heure H)

```php
// app/Jobs/StartObservationJob.php
class StartObservationJob implements ShouldQueue
{
    public function handle()
    {
        // 1. Vérifier conditions météo
        if (!$this->checkWeatherConditions()) {
            $this->notifyBadWeather();
            $this->reschedule();
            return;
        }

        // 2. Activer le Target dans RoboTarget
        $this->voyager->activateTarget($this->booking->voyager_target_guid);

        // 3. Marquer session comme started
        $this->booking->update([
            'session_started_at' => now(),
            'status' => 'running'
        ]);

        // 4. Lancer monitoring
        MonitorObservationJob::dispatch($this->booking)
            ->delay(now()->addMinutes(1));

        // 5. Notifier utilisateur
        $this->booking->user->notify(
            new SessionStarted($this->booking)
        );
    }
}
```

#### 5.2 Monitoring continu

```php
// app/Jobs/MonitorObservationJob.php
class MonitorObservationJob implements ShouldQueue
{
    public function handle()
    {
        // Récupérer état Voyager
        $state = $this->voyager->getControlOverview();

        // Vérifier erreurs
        if ($state['VOYSTAT'] === 3) { // ERROR
            $this->handleError($state);
        }

        // Vérifier météo
        if (!$this->checkWeatherSafe($state)) {
            $this->pauseForWeather();
        }

        // Mettre à jour stats
        $this->updateStats($state);

        // Continuer si session pas terminée
        if ($this->booking->end_datetime->isFuture()) {
            self::dispatch($this->booking)
                ->delay(now()->addMinutes(5));
        }
    }
}
```

#### 5.3 Fin automatique (Fin de créneau)

```php
// app/Jobs/EndObservationJob.php
class EndObservationJob implements ShouldQueue
{
    public function handle()
    {
        // 1. Désactiver Target
        $this->voyager->deactivateTarget($this->booking->voyager_target_guid);

        // 2. Récupérer statistiques finales
        $stats = $this->collectFinalStats();

        // 3. Archiver images
        $images = $this->archiveImages();

        // 4. Marquer session terminée
        $this->booking->update([
            'session_ended_at' => now(),
            'status' => 'completed',
            'images_captured' => count($images),
            'session_stats' => $stats
        ]);

        // 5. Générer rapport
        $report = $this->generateReport();

        // 6. Notifier utilisateur avec rapport
        $this->booking->user->notify(
            new SessionCompleted($this->booking, $report)
        );
    }

    private function archiveImages()
    {
        // Copier FITS depuis dossier Voyager vers storage
        // Générer previews JPG
        // Enregistrer en base
    }
}
```

#### 5.4 Gestion des images FITS

```php
// app/Jobs/ProcessNewImageJob.php
class ProcessNewImageJob implements ShouldQueue
{
    public function handle($fitPath)
    {
        // 1. Copier FITS vers storage
        $destination = storage_path("app/observations/{$this->booking->id}/");
        File::copy($fitPath, $destination);

        // 2. Générer preview JPG (via ImageMagick ou Python)
        $preview = $this->generatePreview($fitPath);

        // 3. Extraire métadonnées FITS
        $metadata = $this->extractFitsMetadata($fitPath);

        // 4. Enregistrer en base
        ObservationImage::create([
            'booking_id' => $this->booking->id,
            'filename' => basename($fitPath),
            'path' => $destination,
            'preview_path' => $preview,
            'type' => $metadata['IMAGETYP'], // LIGHT/DARK/FLAT
            'filter' => $metadata['FILTER'],
            'exposure' => $metadata['EXPTIME'],
            'temperature' => $metadata['CCD-TEMP'],
            'binning' => $metadata['XBINNING'],
            'hfd' => $metadata['HFD'] ?? null,
            'metadata' => $metadata,
            'captured_at' => Carbon::parse($metadata['DATE-OBS']),
        ]);

        // 5. Broadcaster aux clients connectés
        broadcast(new NewImageCaptured($this->booking, $image));
    }
}
```

#### 5.5 Scheduler Laravel

```php
// app/Console/Kernel.php
protected function schedule(Schedule $schedule)
{
    // Préparer observations J-1
    $schedule->call(function () {
        $tomorrow = now()->addDay();
        EquipmentBooking::where('start_datetime', '>=', $tomorrow->startOfDay())
            ->where('start_datetime', '<', $tomorrow->endOfDay())
            ->where('status', 'confirmed')
            ->whereNull('voyager_set_guid')
            ->each(function ($booking) {
                PrepareObservationJob::dispatch($booking);
            });
    })->dailyAt('12:00'); // Midi, 12h avant

    // Démarrer sessions à l'heure H
    $schedule->call(function () {
        EquipmentBooking::where('start_datetime', '<=', now())
            ->where('start_datetime', '>=', now()->subMinutes(5))
            ->where('status', 'confirmed')
            ->whereNull('session_started_at')
            ->each(function ($booking) {
                StartObservationJob::dispatch($booking);
            });
    })->everyMinute();

    // Terminer sessions expirées
    $schedule->call(function () {
        EquipmentBooking::where('end_datetime', '<=', now())
            ->where('status', 'running')
            ->each(function ($booking) {
                EndObservationJob::dispatch($booking);
            });
    })->everyMinute();
}
```

---

### Phase 6 : Notifications et communication 🟢

#### 6.1 Notifications email

```php
// À créer dans app/Notifications/
ObservationPrepared          // J-1 : "Votre observation est prête"
SessionStarting              // H-1 : "Votre session démarre dans 1h"
SessionStarted               // H : "Votre session a démarré"
SessionPaused                // Si pause : "Session en pause (météo)"
SessionResumed               // Reprise : "Session reprise"
SessionCompleted             // Fin : "Session terminée + rapport"
ErrorOccurred                // Erreur : "Problème détecté"
ImagesCaptured               // Périodique : "15 images capturées"
```

#### 6.2 Notifications in-app

```php
// Utiliser le store Alpine.js notifications existant
// Broadcaster via Laravel Echo

// Événements à broadcaster
broadcast(new ObservationStateChanged($booking));
broadcast(new NewImageCaptured($booking, $image));
broadcast(new SequenceProgress($booking, $progress));
broadcast(new WeatherAlert($booking, $conditions));
broadcast(new ErrorAlert($booking, $error));
```

#### 6.3 Notifications push (optionnel)

```php
// Via Laravel WebPush ou OneSignal
- Démarrage session
- Nouvelle image capturée
- Problème détecté
- Session terminée
```

---

### Phase 7 : Rapports et analytics 🟢

#### 7.1 Rapport de session

**Généré automatiquement à la fin :**

```php
// app/Services/SessionReportService.php
class SessionReportService
{
    public function generate(EquipmentBooking $booking)
    {
        return [
            'summary' => [
                'booking_id' => $booking->id,
                'user' => $booking->user->name,
                'equipment' => $booking->equipment->name,
                'target' => $booking->target_name,
                'date' => $booking->start_datetime,
                'duration_planned' => $booking->duration,
                'duration_actual' => $this->calculateActualDuration($booking),
            ],
            'weather' => [
                'average_temperature' => $this->getAverageTemp($booking),
                'average_seeing' => $this->getAverageSeeing($booking),
                'cloud_coverage' => $this->getCloudCoverage($booking),
            ],
            'images' => [
                'total_captured' => $booking->images()->count(),
                'by_filter' => $booking->images()->groupBy('filter'),
                'total_exposure_time' => $booking->images()->sum('exposure'),
                'average_hfd' => $booking->images()->avg('hfd'),
            ],
            'tracking' => [
                'average_rms' => $this->getAverageRMS($booking),
                'guiding_uptime' => $this->getGuidingUptime($booking),
            ],
            'issues' => [
                'errors_count' => $this->getErrorsCount($booking),
                'pauses_count' => $this->getPausesCount($booking),
                'pauses_duration' => $this->getPausesDuration($booking),
            ],
            'files' => [
                'download_link' => $this->generateDownloadLink($booking),
                'total_size' => $this->getTotalFilesSize($booking),
            ],
        ];
    }
}
```

**Vue du rapport :**
```blade
<!-- resources/views/bookings/report.blade.php -->
- Résumé session
- Graphiques (images par filtre, HFD dans le temps, guidage)
- Galerie preview images
- Bouton télécharger toutes les images (ZIP)
- Statistiques détaillées
- Timeline des événements
```

#### 7.2 Page "Mes observations"

```php
// Route : /my-observations
- Liste toutes les sessions terminées
- Filtres (date, équipement, cible)
- Tri (récent, ancien, plus d'images)
- Vignettes preview
- Lien vers rapport détaillé
- Bouton télécharger images
```

#### 7.3 Analytics admin

```php
// Dashboard admin
- Taux d'utilisation par équipement
- Heures totales d'observation
- Images totales capturées
- Revenus par mois
- Taux de succès sessions
- Temps moyen par session
- Problèmes fréquents
```

---

### Phase 8 : Améliorations UX 🔵

#### 8.1 Tutoriel interactif

```php
// Premier utilisateur
- Guide pas à pas
- Tour de l'interface de contrôle
- Explication des indicateurs
- Bonnes pratiques
```

#### 8.2 Mode démo

```php
// Sans réservation active
- Simulateur de contrôle
- Données d'exemple
- Permet de découvrir l'interface
- Bouton "Réserver pour de vrai"
```

#### 8.3 Aide contextuelle

```php
// Sur chaque section
- Tooltips explicatifs
- Icônes d'aide
- Liens vers documentation
- FAQ intégrée
```

#### 8.4 Raccourcis clavier

```php
Space  : Pause/Resume
R      : Refresh
A      : Abort (avec confirmation)
P      : Toggle preview
L      : Toggle logs
F      : Fullscreen preview
```

---

## 📅 Planning suggéré

### Sprint 1 (2-3 semaines) - CRITIQUE
- [x] Proxy Voyager Node.js complet
- [x] WebSocket temps réel
- [x] Intégration Laravel basique
- [x] Test connexion Voyager réelle

### Sprint 2 (2 semaines)
- [x] Interface de contrôle complète
- [x] Preview caméra live
- [x] Logs temps réel
- [x] Stats basiques

### Sprint 3 (1-2 semaines)
- [x] Formulaire réservation enrichi
- [x] API RoboTarget (Sets/Targets/Shots)
- [x] Job préparation J-1
- [x] Tests création observations

### Sprint 4 (1-2 semaines)
- [x] Jobs automation (start/monitor/end)
- [x] Gestion images FITS
- [x] Scheduler Laravel
- [x] Notifications email

### Sprint 5 (1 semaine)
- [x] Broadcasting Laravel Echo
- [x] Notifications in-app
- [x] WebSocket frontend

### Sprint 6 (1 semaine)
- [x] Rapports de session
- [x] Page "Mes observations"
- [x] Téléchargement images

### Sprint 7 (1 semaine)
- [x] Analytics admin
- [x] Optimisations performance
- [x] Tests E2E

### Sprint 8 (1 semaine)
- [x] Tutoriel + aide
- [x] Documentation utilisateur
- [x] Polish UI/UX

---

## 🎯 MVP (Minimum Viable Product)

**Pour une première version fonctionnelle :**

### Obligatoire
1. ✅ Proxy Voyager Node.js opérationnel
2. ✅ Connexion TCP/IP stable avec heartbeat
3. ✅ Interface de contrôle avec état temps réel
4. ✅ Preview caméra
5. ✅ Boutons Pause/Resume/Abort
6. ✅ Création automatique Set/Target/Shots
7. ✅ Démarrage/arrêt auto session

### Nice to have (v1.1)
- WebSocket push (peut commencer par polling)
- Galerie images (peut être simple)
- Rapports détaillés
- Graphiques analytics

### Peut attendre (v1.2+)
- Notifications push mobile
- Mode démo
- Tutoriel interactif
- Raccourcis clavier

---

## 🔧 Configuration requise

### Serveur
```bash
# Node.js
Node.js 20+ LTS
npm ou yarn
PM2 global

# Laravel
PHP 8.2+
Composer
Redis (pour cache + queues)
Supervisor (pour queues Laravel)

# Système
Port 5950 ouvert vers Voyager
Port 3000 pour proxy (interne)
Port 6001 pour WebSocket (ou via nginx)
```

### Variables d'environnement
```env
# Voyager
VOYAGER_HOST=192.168.1.100
VOYAGER_PORT=5950
VOYAGER_USERNAME=admin
VOYAGER_PASSWORD=secret
VOYAGER_PROFILE=Default.v2y
VOYAGER_DEFAULT_SEQUENCE_GUID=xxxxx-xxxx-xxxx

# Proxy
VOYAGER_PROXY_URL=http://localhost:3000
VOYAGER_PROXY_API_KEY=secret_key

# WebSocket
BROADCAST_DRIVER=redis
QUEUE_CONNECTION=redis

# Storage
OBSERVATIONS_STORAGE_PATH=/mnt/observations
MAX_IMAGE_SIZE_MB=50
```

---

## 🧪 Tests critiques

### Tests de connexion
- [ ] Connexion TCP Voyager établie
- [ ] Heartbeat maintenu pendant 1h
- [ ] Reconnexion après déconnexion
- [ ] Authentification réussie
- [ ] Timeout géré correctement

### Tests fonctionnels
- [ ] Création Set/Target/Shots
- [ ] Activation/désactivation Target
- [ ] Pause/resume séquence
- [ ] Abort session
- [ ] Réception événements temps réel

### Tests E2E
- [ ] Parcours complet : réservation → session → images
- [ ] Session de 2h avec surveillance
- [ ] Gestion erreur météo
- [ ] Téléchargement images finales

---

## 📞 Support utilisateur

### Documentation utilisateur
- [ ] Guide de réservation
- [ ] Guide de contrôle pendant session
- [ ] FAQ "Que faire si..."
- [ ] Tutoriel vidéo

### Monitoring admin
- [ ] Dashboard santé système
- [ ] Logs centralisés
- [ ] Alertes erreurs critiques
- [ ] Métriques Voyager

---

## 🚀 Go-Live Checklist

Avant mise en production :

- [ ] Proxy Voyager testé en conditions réelles (48h+)
- [ ] Au moins 5 sessions complètes simulées
- [ ] Backup automatique configuré
- [ ] Monitoring en place
- [ ] Documentation complète
- [ ] Support utilisateur prêt
- [ ] Plan de rollback défini
- [ ] Tests charge (10 users simultanés)

---

**Prochaine étape critique** : Développement du proxy Voyager Node.js

Veux-tu que je commence par créer la structure complète du projet Node.js avec tous les fichiers nécessaires ?
