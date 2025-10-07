Documentation Technique - Starkeeper.it
Plateforme de réservation de télescope robotisé

Table des matières

Architecture générale
Stack technique
Flux utilisateur complet
Architecture de connexion Voyager
Service Node.js (Proxy Voyager)
Intégration Laravel
Base de données
Monitoring temps réel
Gestion des crédits
Déploiement
Sécurité


Architecture générale
┌─────────────────────────────────────────────────────────────┐
│                        NAVIGATEUR                            │
│  (Interface utilisateur - Livewire/Alpine.js/Tailwind)      │
└──────────────────────────┬──────────────────────────────────┘
                           │ HTTP/WebSocket
                           ↓
┌─────────────────────────────────────────────────────────────┐
│                    SERVEUR APPLICATION                       │
│                                                              │
│  ┌──────────────────┐         ┌──────────────────┐         │
│  │  Laravel App     │◄───────►│  Node.js Proxy   │         │
│  │  (Port 80/443)   │  HTTP   │  (Port 3000)     │         │
│  │                  │ local   │                  │         │
│  │  - Controllers   │         │  - Socket TCP    │         │
│  │  - Models        │         │  - Event Handler │         │
│  │  - Jobs/Queue    │         │  - REST API      │         │
│  │  - Broadcasting  │         │                  │         │
│  └──────────────────┘         └──────────┬───────┘         │
│           │                               │                 │
│           ↓                               │                 │
│  ┌──────────────────┐                    │                 │
│  │   MySQL/Postgres │                    │                 │
│  │   + Redis Cache  │                    │                 │
│  └──────────────────┘                    │                 │
└───────────────────────────────────────────┼─────────────────┘
                                            │ JSON-RPC TCP
                                            ↓
                              ┌──────────────────────┐
                              │   Voyager Server     │
                              │   (RoboTarget API)   │
                              │                      │
                              │   - Scheduler        │
                              │   - Sequence Engine  │
                              └──────────┬───────────┘
                                         │
                                         ↓
                              ┌──────────────────────┐
                              │   Matériel           │
                              │   - Télescope        │
                              │   - Caméra           │
                              │   - Roue à filtres   │
                              │   - Focuser          │
                              └──────────────────────┘

Stack technique
Backend

Laravel 12 - Framework PHP
MySQL/PostgreSQL - Base de données principale
Redis - Cache et queues
Node.js 20+ - Service proxy Voyager

Frontend

Livewire 3 - Composants réactifs
Alpine.js - Interactions légères
Tailwind CSS - Styling

Infrastructure

PM2 - Process manager pour Node.js
Supervisor - Gestion des queues Laravel
Nginx - Reverse proxy
Certbot - Certificats SSL


Flux utilisateur complet
1. Achat de crédits
php// app/Models/User.php
class User extends Authenticatable
{
    public function purchaseCredits(int $amount, string $paymentMethod)
    {
        // Traitement paiement (Stripe/PayPal)
        $payment = Payment::create([
            'user_id' => $this->id,
            'amount' => $amount * 10, // 1 crédit = 10€
            'credits' => $amount,
            'status' => 'pending'
        ]);
        
        // Webhook de confirmation ajoutera les crédits
        return $payment;
    }
    
    public function addCredits(int $amount)
    {
        $this->increment('credits', $amount);
        
        CreditTransaction::create([
            'user_id' => $this->id,
            'amount' => $amount,
            'type' => 'purchase',
            'balance_after' => $this->credits
        ]);
    }
}
2. Réservation d'un créneau
php// app/Http/Controllers/ReservationController.php
class ReservationController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'observation_date' => 'required|date|after:today',
            'start_time' => 'required|date_format:H:i',
            'duration' => 'required|integer|min:1|max:8', // heures
            'target_name' => 'required|string',
            'target_ra' => 'required|numeric',
            'target_dec' => 'required|numeric',
            'shots' => 'required|array',
            'shots.*.filter' => 'required|string',
            'shots.*.exposure' => 'required|integer',
            'shots.*.quantity' => 'required|integer',
        ]);
        
        // Calcul du coût
        $cost = $this->calculateCost($validated['duration']);
        
        if (auth()->user()->credits < $cost) {
            return back()->withErrors(['credits' => 'Crédits insuffisants']);
        }
        
        // Vérifier disponibilité
        $isAvailable = $this->checkAvailability(
            $validated['observation_date'],
            $validated['start_time'],
            $validated['duration']
        );
        
        if (!$isAvailable) {
            return back()->withErrors(['slot' => 'Créneau non disponible']);
        }
        
        // Créer la réservation
        $reservation = Reservation::create([
            'user_id' => auth()->id(),
            'observation_date' => $validated['observation_date'],
            'start_time' => $validated['start_time'],
            'duration' => $validated['duration'],
            'target_name' => $validated['target_name'],
            'target_ra' => $validated['target_ra'],
            'target_dec' => $validated['target_dec'],
            'cost' => $cost,
            'status' => 'confirmed'
        ]);
        
        // Enregistrer les shots
        foreach ($validated['shots'] as $shot) {
            $reservation->shots()->create($shot);
        }
        
        // Déduire les crédits
        auth()->user()->deductCredits($cost, $reservation);
        
        // Programmer la préparation automatique
        PrepareObservation::dispatch($reservation)
            ->delay($reservation->observation_date->subDay());
        
        return redirect()->route('reservations.show', $reservation);
    }
    
    private function calculateCost(int $duration): int
    {
        // 1 heure = 10 crédits
        return $duration * 10;
    }
}
3. Préparation automatique (J-1)
php// app/Jobs/PrepareObservation.php
class PrepareObservation implements ShouldQueue
{
    public function __construct(
        public Reservation $reservation
    ) {}
    
    public function handle(VoyagerService $voyager)
    {
        // 1. Créer le Set dans Voyager
        $setGuid = Str::uuid();
        $voyager->addSet([
            'Guid' => $setGuid,
            'Name' => "User_{$this->reservation->user_id}_Session_{$this->reservation->id}",
            'ProfileName' => config('voyager.profile'),
            'Status' => 0, // Enabled
            'Tag' => "user_{$this->reservation->user_id}",
        ]);
        
        // 2. Créer le Target
        $targetGuid = Str::uuid();
        $voyager->addTarget([
            'GuidTarget' => $targetGuid,
            'RefGuidSet' => $setGuid,
            'RefGuidBaseSequence' => config('voyager.default_sequence'),
            'TargetName' => $this->reservation->target_name,
            'RAJ2000' => $this->reservation->target_ra,
            'DECJ2000' => $this->reservation->target_dec,
            'PA' => 0,
            'DateCreation' => now()->timestamp,
            'Status' => 0,
            'Priority' => 2,
            'IsRepeat' => true,
            'Repeat' => 1,
            // Contraintes
            'C_Mask' => 'BDE', // Altitude min, HA start/end
            'C_AltMin' => 30,
            'C_HAStart' => -3,
            'C_HAEnd' => 3,
            'C_DateStart' => $this->reservation->observation_date->timestamp,
            'C_DateEnd' => $this->reservation->observation_date->addDay()->timestamp,
        ]);
        
        // 3. Créer les Shots
        $order = 1;
        foreach ($this->reservation->shots as $shot) {
            $shotGuid = Str::uuid();
            $voyager->addShot([
                'GuidShot' => $shotGuid,
                'RefGuidTarget' => $targetGuid,
                'FilterIndex' => $this->getFilterIndex($shot->filter),
                'Num' => $shot->quantity,
                'Bin' => 1,
                'ReadoutMode' => 0,
                'Type' => 0, // Light
                'Speed' => 0,
                'Gain' => $shot->gain ?? 0,
                'Offset' => $shot->offset ?? 0,
                'Exposure' => $shot->exposure,
                'Order' => $order++,
                'Enabled' => true,
            ]);
        }
        
        // 4. Sauvegarder les GUIDs
        $this->reservation->update([
            'voyager_set_guid' => $setGuid,
            'voyager_target_guid' => $targetGuid,
            'status' => 'prepared'
        ]);
        
        // 5. Notifier l'utilisateur
        $this->reservation->user->notify(
            new ObservationPrepared($this->reservation)
        );
    }
    
    private function getFilterIndex(string $filterName): int
    {
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
4. Jour J - Monitoring
php// app/Http/Livewire/ObservationMonitor.php
namespace App\Http\Livewire;

use Livewire\Component;
use App\Services\VoyagerService;

class ObservationMonitor extends Component
{
    public $reservationId;
    public $reservation;
    public $session;
    public $latestImages = [];
    public $stats = [];
    
    protected $listeners = ['imageCapture' => 'refresh'];
    
    public function mount($reservationId)
    {
        $this->reservationId = $reservationId;
        $this->refresh();
    }
    
    public function refresh()
    {
        $this->reservation = Reservation::with('shots')->find($this->reservationId);
        
        // Récupérer la session en cours
        $this->session = Session::where('reservation_id', $this->reservationId)
            ->latest()
            ->first();
        
        if ($this->session) {
            // Images récentes
            $this->latestImages = $this->session->images()
                ->latest()
                ->take(6)
                ->get()
                ->map(fn($img) => [
                    'url' => Storage::url($img->thumbnail_path),
                    'filter' => $img->filter,
                    'exposure' => $img->exposure,
                    'hfd' => round($img->hfd, 2),
                    'stars' => $img->star_count,
                    'timestamp' => $img->created_at->format('H:i:s'),
                ]);
            
            // Statistiques
            $this->stats = [
                'progress' => $this->session->progress,
                'shots_done' => $this->session->shots_done,
                'shots_total' => $this->session->shots_total,
                'avg_hfd' => round($this->session->images()->avg('hfd'), 2),
                'integration_time' => $this->formatDuration($this->session->integration_time),
                'status' => $this->session->status,
            ];
        }
    }
    
    public function abort()
    {
        if (!$this->session || $this->session->status !== 'running') {
            return;
        }
        
        // Appeler l'API Voyager
        app(VoyagerService::class)->abortTarget(
            $this->reservation->voyager_target_guid
        );
        
        // Mettre à jour le statut
        $this->session->update(['status' => 'aborted']);
        
        // Rembourser les crédits proportionnellement
        $unusedCredits = $this->calculateUnusedCredits();
        $this->reservation->user->addCredits($unusedCredits);
        
        session()->flash('message', 'Observation arrêtée avec succès.');
        $this->refresh();
    }
    
    private function calculateUnusedCredits(): int
    {
        $plannedDuration = $this->reservation->duration * 60; // minutes
        $actualDuration = $this->session->integration_time; // minutes
        $unusedRatio = ($plannedDuration - $actualDuration) / $plannedDuration;
        
        return (int) ($this->reservation->cost * $unusedRatio);
    }
    
    private function formatDuration(int $minutes): string
    {
        $hours = floor($minutes / 60);
        $mins = $minutes % 60;
        return sprintf('%dh%02d', $hours, $mins);
    }
    
    public function render()
    {
        return view('livewire.observation-monitor');
    }
}
blade{{-- resources/views/livewire/observation-monitor.blade.php --}}
<div wire:poll.10s="refresh" class="space-y-6">
    {{-- En-tête --}}
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold">{{ $reservation->target_name }}</h2>
                <p class="text-gray-600">
                    {{ $reservation->observation_date->format('d/m/Y') }} 
                    - {{ $reservation->start_time }}
                </p>
            </div>
            
            @if($session && $session->status === 'running')
                <span class="px-4 py-2 bg-green-100 text-green-800 rounded-full font-semibold">
                    🔴 EN DIRECT
                </span>
            @elseif($session && $session->status === 'completed')
                <span class="px-4 py-2 bg-blue-100 text-blue-800 rounded-full">
                    ✓ Terminée
                </span>
            @else
                <span class="px-4 py-2 bg-gray-100 text-gray-800 rounded-full">
                    ⏳ En attente
                </span>
            @endif
        </div>
    </div>
    
    @if($session)
        {{-- Progression --}}
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold mb-4">Progression</h3>
            
            <div class="mb-4">
                <div class="flex justify-between mb-2">
                    <span class="text-sm font-medium">Images capturées</span>
                    <span class="text-sm font-medium">
                        {{ $stats['shots_done'] }} / {{ $stats['shots_total'] }}
                    </span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-4">
                    <div 
                        class="bg-blue-600 h-4 rounded-full transition-all duration-500"
                        style="width: {{ $stats['progress'] }}%"
                    ></div>
                </div>
            </div>
            
            <div class="grid grid-cols-3 gap-4 mt-6">
                <div class="text-center">
                    <div class="text-2xl font-bold text-blue-600">
                        {{ $stats['integration_time'] }}
                    </div>
                    <div class="text-sm text-gray-600">Temps d'intégration</div>
                </div>
                
                <div class="text-center">
                    <div class="text-2xl font-bold text-green-600">
                        {{ $stats['avg_hfd'] }}
                    </div>
                    <div class="text-sm text-gray-600">HFD moyen</div>
                </div>
                
                <div class="text-center">
                    <div class="text-2xl font-bold text-purple-600">
                        {{ $stats['progress'] }}%
                    </div>
                    <div class="text-sm text-gray-600">Complet</div>
                </div>
            </div>
            
            @if($session->status === 'running')
                <div class="mt-6">
                    <button 
                        wire:click="abort"
                        wire:confirm="Êtes-vous sûr de vouloir arrêter l'observation ?"
                        class="w-full bg-red-600 text-white py-3 rounded-lg hover:bg-red-700 transition"
                    >
                        ⏹ Arrêter l'observation
                    </button>
                </div>
            @endif
        </div>
        
        {{-- Images récentes --}}
        @if(count($latestImages) > 0)
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold mb-4">Dernières images capturées</h3>
                
                <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                    @foreach($latestImages as $image)
                        <div class="relative group">
                            <img 
                                src="{{ $image['url'] }}" 
                                alt="Shot {{ $loop->iteration }}"
                                class="w-full h-48 object-cover rounded-lg"
                            >
                            <div class="absolute bottom-0 left-0 right-0 bg-black bg-opacity-75 text-white p-2 rounded-b-lg">
                                <div class="text-sm">
                                    <strong>{{ $image['filter'] }}</strong> - {{ $image['exposure'] }}s
                                </div>
                                <div class="text-xs">
                                    HFD: {{ $image['hfd'] }} | ⭐ {{ $image['stars'] }}
                                </div>
                                <div class="text-xs text-gray-400">
                                    {{ $image['timestamp'] }}
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    @else
        {{-- En attente de démarrage --}}
        <div class="bg-white rounded-lg shadow p-12 text-center">
            <div class="text-6xl mb-4">🌙</div>
            <h3 class="text-xl font-semibold mb-2">En attente du créneau optimal</h3>
            <p class="text-gray-600">
                L'observation démarrera automatiquement selon les conditions météo
                et l'éphéméride de votre cible.
            </p>
            <p class="text-sm text-gray-500 mt-4">
                Prochaine vérification dans 10 minutes
            </p>
        </div>
    @endif
</div>

Architecture de connexion Voyager
Problématique Socket TCP
Voyager nécessite une connexion socket TCP persistante alors que Laravel fonctionne en HTTP stateless.
HTTP (Laravel classique)
┌───────┐     ┌───────┐     ┌───────┐
│Request│────►│Process│────►│Response│
└───────┘     └───────┘     └───────┘
              ⚠️ Connexion fermée après chaque requête

Socket TCP (Voyager)
┌────────┐──────────────────────────┐
│Connect │ Auth │ Cmd1 │ Cmd2 │ ... │
└────────┴──────┴──────┴──────┴─────┘
✓ Connexion reste ouverte
Solution : Service Node.js
Node.js maintient la connexion TCP en permanence et expose une API REST pour Laravel.
Laravel → HTTP local → Node.js → Socket TCP → Voyager

Service Node.js (Proxy Voyager)
Installation
bashmkdir /var/www/voyager-proxy
cd /var/www/voyager-proxy
npm init -y
npm install express
Structure du projet
voyager-proxy/
├── server.js
├── config.js
├── voyager-client.js
├── routes/
│   ├── targets.js
│   ├── sessions.js
│   └── control.js
├── utils/
│   └── mac-calculator.js
└── package.json
server.js
javascriptconst express = require('express');
const VoyagerClient = require('./voyager-client');
const config = require('./config');

const app = express();
app.use(express.json());

// Instance du client Voyager
const voyager = new VoyagerClient(config.voyager);

// Routes
app.use('/api/targets', require('./routes/targets')(voyager));
app.use('/api/sessions', require('./routes/sessions')(voyager));
app.use('/api/control', require('./routes/control')(voyager));

// Health check
app.get('/health', (req, res) => {
    res.json({
        status: 'ok',
        connected: voyager.isConnected(),
        uptime: process.uptime()
    });
});

// Gestion des erreurs
app.use((err, req, res, next) => {
    console.error('Error:', err);
    res.status(500).json({ error: err.message });
});

// Démarrage
const PORT = config.port || 3000;
app.listen(PORT, () => {
    console.log(`Voyager Proxy running on port ${PORT}`);
    voyager.connect();
});

// Graceful shutdown
process.on('SIGTERM', () => {
    console.log('SIGTERM received, closing connections...');
    voyager.disconnect();
    process.exit(0);
});
voyager-client.js
javascriptconst net = require('net');
const crypto = require('crypto');
const EventEmitter = require('events');

class VoyagerClient extends EventEmitter {
    constructor(config) {
        super();
        this.config = config;
        this.socket = null;
        this.sessionKey = null;
        this.commandId = 0;
        this.pendingCommands = new Map();
        this.reconnectAttempts = 0;
        this.maxReconnectAttempts = 10;
    }
    
    connect() {
        console.log(`Connecting to Voyager at ${this.config.host}:${this.config.port}...`);
        
        this.socket = net.connect(this.config.port, this.config.host);
        
        this.socket.on('connect', () => {
            console.log('Connected to Voyager');
            this.reconnectAttempts = 0;
        });
        
        this.socket.on('data', (data) => {
            this.handleData(data);
        });
        
        this.socket.on('error', (err) => {
            console.error('Socket error:', err);
            this.handleReconnect();
        });
        
        this.socket.on('close', () => {
            console.log('Connection closed');
            this.handleReconnect();
        });
    }
    
    handleData(data) {
        const lines = data.toString().split('\r\n').filter(l => l.trim());
        
        lines.forEach(line => {
            try {
                const message = JSON.parse(line);
                
                if (message.Event === 'Version') {
                    this.sessionKey = message.Timestamp.toString();
                    this.authenticate();
                }
                else if (message.Event === 'RemoteActionResult') {
                    this.handleActionResult(message);
                }
                else if (message.jsonrpc) {
                    // Réponse à une commande
                    const pending = this.pendingCommands.get(message.id);
                    if (pending) {
                        pending.resolve(message);
                        this.pendingCommands.delete(message.id);
                    }
                }
                
                // Émettre l'événement pour Laravel
                this.emit('message', message);
                
            } catch (e) {
                console.error('Parse error:', e, line);
            }
        });
    }
    
    async authenticate() {
        console.log('Authenticating...');
        
        // 1. AuthenticateUserBase
        await this.sendCommand({
            method: 'AuthenticateUserBase',
            params: {
                UID: this.generateUID(),
                Base: this.config.authBase
            }
        });
        
        // 2. RemoteSetRoboTargetManagerMode
        const uid = this.generateUID();
        const hash = this.calculateInitialMAC();
        
        await this.sendCommand({
            method: 'RemoteSetRoboTargetManagerMode',
            params: {
                UID: uid,
                MACKey: this.config.macKey,
                Hash: hash
            }
        });
        
        console.log('Authenticated successfully');
        this.emit('authenticated');
    }
    
    sendCommand(command) {
        return new Promise((resolve, reject) => {
            command.id = ++this.commandId;
            
            const timeout = setTimeout(() => {
                this.pendingCommands.delete(command.id);
                reject(new Error('Command timeout'));
            }, 30000);
            
            this.pendingCommands.set(command.id, {
                resolve: (response) => {
                    clearTimeout(timeout);
                    resolve(response);
                },
                reject
            });
            
            this.socket.write(JSON.stringify(command) + '\r\n');
        });
    }
    
    async executeCommand(method, params = {}) {
        if (!this.sessionKey) {
            throw new Error('Not authenticated');
        }
        
        const uid = this.generateUID();
        const mac = this.calculateMAC(uid);
        
        const command = {
            method,
            params: {
                ...params,
                UID: uid,
                MAC: mac
            }
        };
        
        return this.sendCommand(command);
    }
    
    handleActionResult(message) {
        // Stocker le résultat pour Laravel
        this.emit('actionResult', {
            uid: message.UID,
            result: message.ActionResultInt,
            params: message.ParamRet,
            reason: message.Motivo
        });
    }
    
    calculateInitialMAC() {
        const str = this.config.macWords.join('') + 
                   '||:||' + this.sessionKey + 
                   '||:||' + (this.commandId + 1) + 
                   '||:||' + this.generateUID();
        return crypto.createHash('sha1').update(str).digest('base64');
    }
    
    calculateMAC(uid) {
        const str = this.config.macWords.join('') + 
                   '||:||' + this.sessionKey + 
                   '||:||' + this.commandId + 
                   '||:||' + uid;
        return crypto.createHash('sha1').update(str).digest('base64');
    }
    
    generateUID() {
        return crypto.randomUUID();
    }
    
    handleReconnect() {
        if (this.reconnectAttempts >= this.maxReconnectAttempts) {
            console.error('Max reconnection attempts reached');
            return;
        }
        
        this.reconnectAttempts++;
        const delay = Math.min(1000 * Math.pow(2, this.reconnectAttempts), 30000);
        
        console.log(`Reconnecting in ${delay}ms (attempt ${this.reconnectAttempts})`);
        setTimeout(() => this.connect(), delay);
    }
    
    disconnect() {
        if (this.socket) {
            this.socket.destroy();
        }
    }
    
    isConnected() {
        return this.socket && !this.socket.destroyed && this.sessionKey !== null;
    }
}

module.exports = VoyagerClient;
routes/targets.js
javascriptmodule.exports = (voyager) => {
    const router = require('express').Router();
    
    // Lister les targets
    router.get('/', async (req, res) => {
        try {
            const response = await voyager.executeCommand('RemoteRoboTargetGetTarget', {
                RefGuidSet: req.query.setGuid || ''
            });
            res.json(response);
        } catch (error) {
            res.status(500).json({ error: error.message });
        }
    });
    
    // Ajouter un target
    router.post('/', async (req, res) => {
        try {
            const response = await voyager.executeCommand('RemoteRoboTargetAddTarget', req.body);
            res.json(response);
        } catch (error) {
            res.status(500).json({ error: error.message });
        }
    });
    
    // Mettre à jour un target
    router.put('/:guid', async (req, res) => {
        try {
            const response = await voyager.executeCommand('RemoteRoboTargetUpdateTarget', {
                RefGuidTarget: req.params.guid,
                ...req.body
            });
            res.json(response);
        } catch (error) {
            res.status(500).json({ error: error.message });
        }
    });
    
    //RéessayerCContinuerModifierDocumentation Technique - Starkeeper.it (Suite)
Service Node.js - Routes (suite)
routes/targets.js (suite)
javascript    // Supprimer un target
    router.delete('/:guid', async (req, res) => {
        try {
            const response = await voyager.executeCommand('RemoteRoboTargetRemoveTarget', {
                RefGuidTarget: req.params.guid
            });
            res.json(response);
        } catch (error) {
            res.status(500).json({ error: error.message });
        }
    });
    
    // Récupérer les shots d'un target
    router.get('/:guid/shots', async (req, res) => {
        try {
            const response = await voyager.executeCommand('RemoteRoboTargetGetShot', {
                RefGuidTarget: req.params.guid
            });
            res.json(response);
        } catch (error) {
            res.status(500).json({ error: error.message });
        }
    });
    
    return router;
};
routes/sessions.js
javascriptmodule.exports = (voyager) => {
    const router = require('express').Router();
    
    // Récupérer les runs
    router.get('/runs', async (req, res) => {
        try {
            const response = await voyager.executeCommand('RemoteRoboTargetGetRunList', {
                ProfileName: req.query.profile || '',
                Days: parseInt(req.query.days) || 30
            });
            res.json(response);
        } catch (error) {
            res.status(500).json({ error: error.message });
        }
    });
    
    // Récupérer les sessions d'un run
    router.get('/runs/:runGuid/sessions', async (req, res) => {
        try {
            const response = await voyager.executeCommand('RemoteRoboTargetGetSessionListByRun', {
                RefGuidRun: req.params.runGuid
            });
            res.json(response);
        } catch (error) {
            res.status(500).json({ error: error.message });
        }
    });
    
    // Récupérer les shots d'une session
    router.get('/sessions/:sessionGuid/shots', async (req, res) => {
        try {
            const response = await voyager.executeCommand('RemoteRoboTargetGetShotDoneBySessionList', {
                RefGuidSession: req.params.sessionGuid
            });
            res.json(response);
        } catch (error) {
            res.status(500).json({ error: error.message });
        }
    });
    
    // Récupérer une image JPG
    router.get('/shots/:shotGuid/image', async (req, res) => {
        try {
            const response = await voyager.executeCommand('RemoteRoboTargetGetShotJpg', {
                RefGuidShotDone: req.params.shotGuid,
                FITFileName: ''
            });
            
            if (response.result === 0 && response.ParamRet?.Base64Data) {
                const buffer = Buffer.from(response.ParamRet.Base64Data, 'base64');
                res.set('Content-Type', 'image/jpeg');
                res.send(buffer);
            } else {
                res.status(404).json({ error: 'Image not found' });
            }
        } catch (error) {
            res.status(500).json({ error: error.message });
        }
    });
    
    return router;
};
routes/control.js
javascriptmodule.exports = (voyager) => {
    const router = require('express').Router();
    
    // Abort
    router.post('/abort', async (req, res) => {
        try {
            const response = await voyager.executeCommand('RemoteRoboTargetAbort', {
                RefGuidTarget: req.body.targetGuid || '',
                RefGuidSet: req.body.setGuid || '',
                RefGuidTargetTag: req.body.targetTag || '',
                RefGuidSetTag: req.body.setTag || ''
            });
            res.json(response);
        } catch (error) {
            res.status(500).json({ error: error.message });
        }
    });
    
    // Enable/Disable object
    router.post('/toggle', async (req, res) => {
        try {
            const response = await voyager.executeCommand('RemoteRoboTargetEnableDisableObject', {
                RefGuidObject: req.body.objectGuid,
                ObjectType: req.body.objectType, // 0=Shot, 1=Target, 2=Set
                OperationType: req.body.operation // 0=Enable, 1=Disable
            });
            res.json(response);
        } catch (error) {
            res.status(500).json({ error: error.message });
        }
    });
    
    return router;
};
config.js
javascriptmodule.exports = {
    port: process.env.PORT || 3000,
    voyager: {
        host: process.env.VOYAGER_HOST || 'localhost',
        port: process.env.VOYAGER_PORT || 5950,
        authBase: process.env.VOYAGER_AUTH_BASE,
        macKey: process.env.VOYAGER_MAC_KEY,
        macWords: [
            process.env.VOYAGER_MAC_WORD1,
            process.env.VOYAGER_MAC_WORD2,
            process.env.VOYAGER_MAC_WORD3,
            process.env.VOYAGER_MAC_WORD4
        ]
    }
};
.env (exemple)
bashPORT=3000
VOYAGER_HOST=192.168.1.100
VOYAGER_PORT=5950
VOYAGER_AUTH_BASE=VotreBase64Authentication
VOYAGER_MAC_KEY=votre-cle-mac-nda
VOYAGER_MAC_WORD1=mot1
VOYAGER_MAC_WORD2=mot2
VOYAGER_MAC_WORD3=mot3
VOYAGER_MAC_WORD4=mot4
package.json
json{
  "name": "voyager-proxy",
  "version": "1.0.0",
  "description": "Proxy service for Voyager RoboTarget API",
  "main": "server.js",
  "scripts": {
    "start": "node server.js",
    "dev": "nodemon server.js"
  },
  "dependencies": {
    "express": "^4.18.2"
  },
  "devDependencies": {
    "nodemon": "^3.0.1"
  }
}

Intégration Laravel
Service Voyager
php// app/Services/VoyagerService.php
<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class VoyagerService
{
    private string $baseUrl;
    private int $timeout;
    
    public function __construct()
    {
        $this->baseUrl = config('services.voyager.proxy_url', 'http://localhost:3000');
        $this->timeout = 30;
    }
    
    /**
     * Créer un Set
     */
    public function addSet(array $data): array
    {
        return $this->post('/api/sets', [
            'Guid' => $data['guid'] ?? Str::uuid(),
            'Name' => $data['name'],
            'ProfileName' => config('services.voyager.profile'),
            'IsDefault' => false,
            'Status' => $data['status'] ?? 0,
            'Tag' => $data['tag'] ?? '',
            'Note' => $data['note'] ?? '',
        ]);
    }
    
    /**
     * Créer un Target
     */
    public function addTarget(array $data): array
    {
        return $this->post('/api/targets', [
            'GuidTarget' => $data['guid'],
            'RefGuidSet' => $data['set_guid'],
            'RefGuidBaseSequence' => $data['sequence_guid'] ?? config('services.voyager.default_sequence'),
            'TargetName' => $data['name'],
            'Tag' => $data['tag'] ?? '',
            'RAJ2000' => $data['ra'],
            'DECJ2000' => $data['dec'],
            'PA' => $data['pa'] ?? 0,
            'DateCreation' => now()->timestamp,
            'Status' => $data['status'] ?? 0,
            'StatusOp' => 0,
            'Note' => $data['note'] ?? '',
            'IsRepeat' => $data['repeat'] ?? true,
            'Repeat' => $data['repeat_count'] ?? 1,
            'Priority' => $data['priority'] ?? 2,
            'IsFinishActualExposure' => true,
            'IsCoolSetPoint' => false,
            'CoolSetPoint' => 0,
            'IsWaitShot' => false,
            'WaitShot' => 0,
            'IsGuideTime' => false,
            'GuideTime' => 0,
            'C_ID' => '',
            'C_Mask' => $data['constraints_mask'] ?? 'BDE',
            'C_AltMin' => $data['altitude_min'] ?? 30,
            'C_SqmMin' => 0,
            'C_HAStart' => $data['ha_start'] ?? -3,
            'C_HAEnd' => $data['ha_end'] ?? 3,
            'C_DateStart' => $data['date_start'] ?? 0,
            'C_DateEnd' => $data['date_end'] ?? 0,
            'C_TimeStart' => 0,
            'C_TimeEnd' => 0,
            'C_MoonDown' => $data['moon_down'] ?? false,
            'C_MoonPhaseMin' => 0,
            'C_MoonPhaseMax' => 100,
            'C_MoonDistanceDegree' => $data['moon_distance'] ?? 0,
            'C_HFDMeanLimit' => 0,
            'C_MaxTimeForDay' => 0,
            'C_AirmassMin' => 0,
            'C_AirmassMax' => 0,
            'C_MoonDistanceLorentzian' => 0,
            'C_MaxTime' => $data['max_time'] ?? 0,
            'C_OSDateStart' => 0,
            'C_OSTimeStart' => 0,
            'C_OSEarly' => 0,
            'C_PINTEarly' => 0,
            'C_PINTReset' => false,
            'C_PINTIntervals' => [],
            'C_Mask2' => '',
            'C_L01' => false,
            'C_M01' => false,
            'C_N01' => false,
            'C_S01' => false,
            'Token' => '',
            'TKey' => '',
            'TName' => '',
            'TType' => 0,
            'IsDynamicPointingOverride' => false,
            'DynamicPointingOverride' => 0,
            'DynEachX_Seconds' => 0,
            'DynEachX_Realign' => false,
            'DynEachX_NoPlateSolve' => false,
            'IsOffsetRF' => false,
            'OffsetRF' => 0,
        ]);
    }
    
    /**
     * Créer un Shot
     */
    public function addShot(array $data): array
    {
        return $this->post('/api/targets/' . $data['target_guid'] . '/shots', [
            'GuidShot' => $data['guid'],
            'RefGuidTarget' => $data['target_guid'],
            'Label' => $data['label'] ?? '',
            'FilterIndex' => $data['filter_index'],
            'Num' => $data['quantity'],
            'Bin' => $data['bin'] ?? 1,
            'ReadoutMode' => 0,
            'Type' => 0, // Light
            'Speed' => 0,
            'Gain' => $data['gain'] ?? 0,
            'Offset' => $data['offset'] ?? 0,
            'Exposure' => $data['exposure'],
            'Order' => $data['order'],
            'Done' => false,
            'Enabled' => true,
        ]);
    }
    
    /**
     * Récupérer les targets
     */
    public function getTargets(?string $setGuid = null): array
    {
        $url = '/api/targets';
        if ($setGuid) {
            $url .= '?setGuid=' . $setGuid;
        }
        return $this->get($url);
    }
    
    /**
     * Récupérer les sessions d'un run
     */
    public function getSessionsByRun(string $runGuid): array
    {
        return $this->get("/api/sessions/runs/{$runGuid}/sessions");
    }
    
    /**
     * Récupérer les shots d'une session
     */
    public function getShotsBySession(string $sessionGuid): array
    {
        return $this->get("/api/sessions/sessions/{$sessionGuid}/shots");
    }
    
    /**
     * Télécharger une image JPG
     */
    public function getShotImage(string $shotGuid): ?string
    {
        try {
            $response = Http::timeout($this->timeout)
                ->get("{$this->baseUrl}/api/sessions/shots/{$shotGuid}/image");
            
            if ($response->successful()) {
                return $response->body();
            }
            
            return null;
        } catch (\Exception $e) {
            Log::error('Failed to get shot image', [
                'shot_guid' => $shotGuid,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }
    
    /**
     * Arrêter une observation
     */
    public function abortTarget(?string $targetGuid = null, ?string $setGuid = null): array
    {
        return $this->post('/api/control/abort', [
            'targetGuid' => $targetGuid,
            'setGuid' => $setGuid,
            'targetTag' => '',
            'setTag' => ''
        ]);
    }
    
    /**
     * Activer/Désactiver un objet
     */
    public function toggleObject(string $objectGuid, int $objectType, int $operation): array
    {
        return $this->post('/api/control/toggle', [
            'objectGuid' => $objectGuid,
            'objectType' => $objectType, // 0=Shot, 1=Target, 2=Set
            'operation' => $operation // 0=Enable, 1=Disable
        ]);
    }
    
    /**
     * GET request helper
     */
    private function get(string $url): array
    {
        try {
            $response = Http::timeout($this->timeout)
                ->get($this->baseUrl . $url);
            
            if ($response->successful()) {
                return $response->json();
            }
            
            throw new \Exception('Voyager API error: ' . $response->body());
        } catch (\Exception $e) {
            Log::error('Voyager GET request failed', [
                'url' => $url,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }
    
    /**
     * POST request helper
     */
    private function post(string $url, array $data): array
    {
        try {
            $response = Http::timeout($this->timeout)
                ->post($this->baseUrl . $url, $data);
            
            if ($response->successful()) {
                return $response->json();
            }
            
            throw new \Exception('Voyager API error: ' . $response->body());
        } catch (\Exception $e) {
            Log::error('Voyager POST request failed', [
                'url' => $url,
                'data' => $data,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }
}
Configuration
php// config/services.php
return [
    // ... autres services
    
    'voyager' => [
        'proxy_url' => env('VOYAGER_PROXY_URL', 'http://localhost:3000'),
        'profile' => env('VOYAGER_PROFILE', 'Default.v2y'),
        'default_sequence' => env('VOYAGER_DEFAULT_SEQUENCE_GUID'),
    ],
];
bash# .env
VOYAGER_PROXY_URL=http://localhost:3000
VOYAGER_PROFILE=Production.v2y
VOYAGER_DEFAULT_SEQUENCE_GUID=ae4df8c6-41ca-4a3e-bdf5-594bbab7881a

Base de données
Migrations
php// database/migrations/xxxx_create_reservations_table.php
Schema::create('reservations', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained()->onDelete('cascade');
    $table->date('observation_date');
    $table->time('start_time');
    $table->integer('duration'); // heures
    $table->string('target_name');
    $table->decimal('target_ra', 12, 8); // RA J2000 en heures
    $table->decimal('target_dec', 12, 8); // DEC J2000 en degrés
    $table->decimal('target_pa', 5, 2)->default(0); // Position Angle
    $table->integer('cost'); // Crédits
    $table->enum('status', [
        'pending', 'confirmed', 'prepared', 
        'running', 'completed', 'aborted', 'cancelled_weather'
    ])->default('pending');
    $table->uuid('voyager_set_guid')->nullable();
    $table->uuid('voyager_target_guid')->nullable();
    $table->text('notes')->nullable();
    $table->timestamps();
    $table->softDeletes();
    
    $table->index(['user_id', 'observation_date']);
    $table->index('status');
});

// database/migrations/xxxx_create_reservation_shots_table.php
Schema::create('reservation_shots', function (Blueprint $table) {
    $table->id();
    $table->foreignId('reservation_id')->constrained()->onDelete('cascade');
    $table->uuid('voyager_shot_guid')->nullable();
    $table->string('filter'); // L, R, G, B, Ha, OIII, SII
    $table->integer('exposure'); // secondes
    $table->integer('quantity'); // nombre de poses
    $table->integer('bin')->default(1);
    $table->integer('gain')->default(0);
    $table->integer('offset')->default(0);
    $table->integer('order');
    $table->timestamps();
});

// database/migrations/xxxx_create_sessions_table.php
Schema::create('sessions', function (Blueprint $table) {
    $table->id();
    $table->foreignId('reservation_id')->constrained()->onDelete('cascade');
    $table->uuid('voyager_session_guid')->nullable();
    $table->uuid('voyager_run_guid')->nullable();
    $table->timestamp('started_at')->nullable();
    $table->timestamp('ended_at')->nullable();
    $table->enum('status', ['running', 'completed', 'aborted', 'error'])->default('running');
    $table->integer('shots_total')->default(0);
    $table->integer('shots_done')->default(0);
    $table->integer('shots_deleted')->default(0);
    $table->integer('integration_time')->default(0); // minutes
    $table->decimal('average_hfd', 5, 2)->nullable();
    $table->integer('progress')->default(0); // pourcentage
    $table->text('exit_text')->nullable();
    $table->timestamps();
    
    $table->index(['reservation_id', 'status']);
});

// database/migrations/xxxx_create_captured_images_table.php
Schema::create('captured_images', function (Blueprint $table) {
    $table->id();
    $table->foreignId('session_id')->constrained()->onDelete('cascade');
    $table->foreignId('reservation_shot_id')->constrained()->onDelete('cascade');
    $table->uuid('voyager_shot_done_guid');
    $table->string('filename');
    $table->string('fit_path'); // Chemin du fichier FIT
    $table->string('thumbnail_path'); // Chemin du JPG
    $table->string('filter');
    $table->integer('exposure');
    $table->integer('bin');
    $table->decimal('hfd', 5, 2);
    $table->integer('star_count');
    $table->integer('max_adu');
    $table->integer('mean_adu');
    $table->integer('min_adu');
    $table->integer('rating')->default(0);
    $table->boolean('deleted')->default(false);
    $table->timestamp('captured_at');
    $table->timestamps();
    
    $table->index(['session_id', 'deleted']);
    $table->index('voyager_shot_done_guid');
});

// database/migrations/xxxx_create_credit_transactions_table.php
Schema::create('credit_transactions', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained()->onDelete('cascade');
    $table->integer('amount'); // Positif = ajout, Négatif = déduction
    $table->enum('type', ['purchase', 'reservation', 'refund', 'admin']);
    $table->integer('balance_after');
    $table->morphs('transactionable'); // Reservation, Payment, etc.
    $table->text('description')->nullable();
    $table->timestamps();
    
    $table->index(['user_id', 'created_at']);
});
Modèles
php// app/Models/Reservation.php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Reservation extends Model
{
    use SoftDeletes;
    
    protected $fillable = [
        'user_id', 'observation_date', 'start_time', 'duration',
        'target_name', 'target_ra', 'target_dec', 'target_pa',
        'cost', 'status', 'voyager_set_guid', 'voyager_target_guid', 'notes'
    ];
    
    protected $casts = [
        'observation_date' => 'date',
        'start_time' => 'datetime:H:i',
        'target_ra' => 'decimal:8',
        'target_dec' => 'decimal:8',
        'target_pa' => 'decimal:2',
    ];
    
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    
    public function shots()
    {
        return $this->hasMany(ReservationShot::class)->orderBy('order');
    }
    
    public function sessions()
    {
        return $this->hasMany(Session::class);
    }
    
    public function currentSession()
    {
        return $this->hasOne(Session::class)->latest();
    }
    
    public function isUpcoming(): bool
    {
        return $this->observation_date->isFuture();
    }
    
    public function isToday(): bool
    {
        return $this->observation_date->isToday();
    }
    
    public function canBeAborted(): bool
    {
        return in_array($this->status, ['prepared', 'running']);
    }
}

// app/Models/Session.php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Session extends Model
{
    protected $fillable = [
        'reservation_id', 'voyager_session_guid', 'voyager_run_guid',
        'started_at', 'ended_at', 'status', 'shots_total', 'shots_done',
        'shots_deleted', 'integration_time', 'average_hfd', 'progress', 'exit_text'
    ];
    
    protected $casts = [
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
        'average_hfd' => 'decimal:2',
    ];
    
    public function reservation()
    {
        return $this->belongsTo(Reservation::class);
    }
    
    public function images()
    {
        return $this->hasMany(CapturedImage::class);
    }
    
    public function activeImages()
    {
        return $this->images()->where('deleted', false);
    }
    
    public function isRunning(): bool
    {
        return $this->status === 'running';
    }
    
    public function duration(): int
    {
        if (!$this->ended_at) {
            return $this->started_at->diffInMinutes(now());
        }
        return $this->started_at->diffInMinutes($this->ended_at);
    }
}

// app/Models/CapturedImage.php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class CapturedImage extends Model
{
    protected $fillable = [
        'session_id', 'reservation_shot_id', 'voyager_shot_done_guid',
        'filename', 'fit_path', 'thumbnail_path', 'filter', 'exposure',
        'bin', 'hfd', 'star_count', 'max_adu', 'mean_adu', 'min_adu',
        'rating', 'deleted', 'captured_at'
    ];
    
    protected $casts = [
        'hfd' => 'decimal:2',
        'deleted' => 'boolean',
        'captured_at' => 'datetime',
    ];
    
    public function session()
    {
        return $this->belongsTo(Session::class);
    }
    
    public function reservationShot()
    {
        return $this->belongsTo(ReservationShot::class);
    }
    
    public function getThumbnailUrlAttribute(): string
    {
        return Storage::url($this->thumbnail_path);
    }
    
    public function getFitDownloadUrlAttribute(): string
    {
        return route('images.download', $this);
    }
    
    public function getQualityScoreAttribute(): string
    {
        if ($this->hfd < 2) return 'Excellent';
        if ($this->hfd < 3) return 'Bon';
        if ($this->hfd < 4) return 'Moyen';
        return 'Faible';
    }
}

Monitoring temps réel
Job de synchronisation
php// app/Jobs/SyncVoyagerSession.php
<?php

namespace App\Jobs;

use App\Models\Session;
use App\Services\VoyagerService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class SyncVoyagerSession implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    
    public function __construct(
        public Session $session
    ) {}
    
    public function handle(VoyagerService $voyager)
    {
        if (!$this->session->voyager_session_guid) {
            return;
        }
        
        // Récupérer les shots de Voyager
        $response = $voyager->getShotsBySession($this->session->voyager_session_guid);
        
        if (!isset($response['ParamRet']['list']['done'])) {
            return;
        }
        
        $voyagerShots = $response['ParamRet']['list']['done'];
        
        foreach ($voyagerShots as $shotData) {
            // Vérifier si le shot existe déjà
            $exists = $this->session->images()
                ->where('voyager_shot_done_guid', $shotData['guid'])
                ->exists();
            
            if ($exists) {
                continue;
            }
            
            // Télécharger l'image JPG
            $jpgData = $voyager->getShotImage($shotData['guid']);
            
            if (!$jpgData) {
                continue;
            }
            
            // Sauvegarder le thumbnail
            $thumbnailPath = $this->saveThumbnail($jpgData, $shotData['filename']);
            
            // Créer l'enregistrement
            $this->session->images()->create([
                'voyager_shot_done_guid' => $shotData['guid'],
                'filename' => $shotData['filename'],
                'fit_path' => $shotData['path'] . '/' . $shotData['filename'],
                'thumbnail_path' => $thumbnailPath,
                'filter' => $this->extractFilter($shotData['filename']),
                'exposure' => $shotData['exposure'],
                'bin' => $shotData['bin'],
                'hfd' => $shotData['hfd'],
                'star_count' => (int)$shotData['starindex'],
                'max_adu' => $shotData['max'],
                'mean_adu' => $shotData['mean'],
                'min_adu' => $shotData['min'],
                'captured_at' => \Carbon\Carbon::createFromTimestamp($shotData['datetimeshot']),
            ]);
            
            // Mettre à jour les stats de la session
            $this->updateSessionStats();
            
            // Notifier l'utilisateur
            broadcast(new \App\Events\ImageCaptured($this->session));
        }
    }
    
    private function saveThumbnail(string $jpgData, string $filename): string
    {
        $path = sprintf(
            'sessions/%d/thumbnails/%s.jpg',
            $this->session->id,
            pathinfo($filename, PATHINFO_FILENAME)
        );
        
        Storage::put($path, $jpgData);
        
        return $path;
    }
    
    private function extractFilter(string $filename): string
    {
        // Exemple: M31_Ha_300s_001.fit
        preg_match('/_([A-Z]+)_\d+s_/', $filename, $matches);
        return $matches[1] ?? 'L';
    }
    
    private function updateSessionStats()
    {
        $this->session->update([
            'shots_done' => $this->session->activeImages()->count(),
            'average_hfd' => $this->session->activeImages()->avg('hfd'),
            'integration_time' => $this->session->activeImages()->sum('exposure') / 60,
            'progress' => $this->calculateProgress(),
        ]);
    }
    
    private function calculateProgress(): int
    {
        if ($this->session->shots_total == 0) {
            return 0;
        }
        
        return min(100, (int)(($this->session->shots_done / $this->session->shots_total) * 100));
    }
}RéessayerCContinuerModifierDocumentation Technique - Starkeeper.it (Suite)
Monitoring temps réel (suite)
Commande planifiée
php// app/Console/Kernel.php
<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    protected function schedule(Schedule $schedule)
    {
        // Synchroniser les sessions actives toutes les 30 secondes
        $schedule->call(function () {
            \App\Models\Session::where('status', 'running')
                ->get()
                ->each(function ($session) {
                    \App\Jobs\SyncVoyagerSession::dispatch($session);
                });
        })->everyThirtySeconds();
        
        // Préparer les observations du lendemain
        $schedule->call(function () {
            \App\Models\Reservation::where('observation_date', today()->addDay())
                ->where('status', 'confirmed')
                ->get()
                ->each(function ($reservation) {
                    \App\Jobs\PrepareObservation::dispatch($reservation);
                });
        })->dailyAt('12:00');
        
        // Vérifier les sessions terminées
        $schedule->call(function () {
            \App\Models\Session::where('status', 'running')
                ->where('started_at', '<', now()->subHours(12))
                ->get()
                ->each(function ($session) {
                    \App\Jobs\FinalizeSession::dispatch($session);
                });
        })->everyFiveMinutes();
        
        // Nettoyer les anciennes images (après 30 jours)
        $schedule->call(function () {
            \App\Jobs\CleanupOldImages::dispatch();
        })->daily();
    }
}
Job de finalisation
php// app/Jobs/FinalizeSession.php
<?php

namespace App\Jobs;

use App\Models\Session;
use App\Services\VoyagerService;
use App\Notifications\ObservationCompleted;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class FinalizeSession implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    
    public function __construct(
        public Session $session
    ) {}
    
    public function handle(VoyagerService $voyager)
    {
        // Dernière synchronisation
        SyncVoyagerSession::dispatch($this->session);
        
        // Récupérer les infos finales de Voyager
        if ($this->session->voyager_session_guid) {
            $sessionInfo = $voyager->getSessionsByRun($this->session->voyager_run_guid);
            
            foreach ($sessionInfo['ParamRet']['list'] ?? [] as $vSession) {
                if ($vSession['guid'] === $this->session->voyager_session_guid) {
                    $this->session->update([
                        'ended_at' => \Carbon\Carbon::createFromTimestamp($vSession['datetimeend']),
                        'exit_text' => $vSession['sessionexittext'] ?? '',
                    ]);
                    break;
                }
            }
        }
        
        // Déterminer le statut final
        $finalStatus = $this->determineFinalStatus();
        
        // Mettre à jour
        $this->session->update([
            'status' => $finalStatus,
            'ended_at' => $this->session->ended_at ?? now(),
        ]);
        
        // Mise à jour de la réservation
        $this->session->reservation->update([
            'status' => $finalStatus === 'completed' ? 'completed' : 'aborted'
        ]);
        
        // Désactiver le target dans Voyager
        $voyager->toggleObject(
            $this->session->reservation->voyager_target_guid,
            1, // Target
            1  // Disable
        );
        
        // Générer le rapport
        $this->generateReport();
        
        // Notifier l'utilisateur
        $this->session->reservation->user->notify(
            new ObservationCompleted($this->session)
        );
    }
    
    private function determineFinalStatus(): string
    {
        // Si plus de 80% des shots sont faits
        if ($this->session->progress >= 80) {
            return 'completed';
        }
        
        // Si moins de 20% fait
        if ($this->session->progress < 20) {
            return 'aborted';
        }
        
        // Entre les deux, on considère comme completé mais partiel
        return 'completed';
    }
    
    private function generateReport()
    {
        $stats = [
            'total_images' => $this->session->shots_done,
            'deleted_images' => $this->session->shots_deleted,
            'integration_time' => $this->session->integration_time,
            'average_hfd' => $this->session->average_hfd,
            'progress' => $this->session->progress,
            'duration' => $this->session->duration(),
            'by_filter' => $this->getStatsByFilter(),
        ];
        
        $this->session->update([
            'report_data' => json_encode($stats)
        ]);
    }
    
    private function getStatsByFilter(): array
    {
        return $this->session->activeImages()
            ->select('filter')
            ->selectRaw('COUNT(*) as count')
            ->selectRaw('SUM(exposure) as total_exposure')
            ->selectRaw('AVG(hfd) as avg_hfd')
            ->groupBy('filter')
            ->get()
            ->map(function ($item) {
                return [
                    'filter' => $item->filter,
                    'count' => $item->count,
                    'integration' => round($item->total_exposure / 60, 1), // minutes
                    'avg_hfd' => round($item->avg_hfd, 2),
                ];
            })
            ->toArray();
    }
}
Événements Broadcasting
php// app/Events/ImageCaptured.php
<?php

namespace App\Events;

use App\Models\Session;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ImageCaptured implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;
    
    public function __construct(
        public Session $session
    ) {}
    
    public function broadcastOn()
    {
        return new Channel('session.' . $this->session->id);
    }
    
    public function broadcastAs()
    {
        return 'image.captured';
    }
    
    public function broadcastWith()
    {
        return [
            'session_id' => $this->session->id,
            'shots_done' => $this->session->shots_done,
            'progress' => $this->session->progress,
            'latest_image' => $this->session->images()->latest()->first(),
        ];
    }
}

// app/Events/SessionStatusChanged.php
<?php

namespace App\Events;

use App\Models\Session;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SessionStatusChanged implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;
    
    public function __construct(
        public Session $session,
        public string $oldStatus,
        public string $newStatus
    ) {}
    
    public function broadcastOn()
    {
        return [
            new Channel('session.' . $this->session->id),
            new Channel('user.' . $this->session->reservation->user_id),
        ];
    }
    
    public function broadcastAs()
    {
        return 'session.status.changed';
    }
    
    public function broadcastWith()
    {
        return [
            'session_id' => $this->session->id,
            'old_status' => $this->oldStatus,
            'new_status' => $this->newStatus,
            'message' => $this->getStatusMessage(),
        ];
    }
    
    private function getStatusMessage(): string
    {
        return match($this->newStatus) {
            'running' => 'Votre observation a démarré !',
            'completed' => 'Votre observation est terminée !',
            'aborted' => 'Votre observation a été arrêtée.',
            'error' => 'Une erreur est survenue.',
            default => 'Statut mis à jour.',
        };
    }
}
Configuration Broadcasting
php// config/broadcasting.php
'connections' => [
    'pusher' => [
        'driver' => 'pusher',
        'key' => env('PUSHER_APP_KEY'),
        'secret' => env('PUSHER_APP_SECRET'),
        'app_id' => env('PUSHER_APP_ID'),
        'options' => [
            'cluster' => env('PUSHER_APP_CLUSTER'),
            'useTLS' => true,
        ],
    ],
    
    // Ou utiliser Soketi (alternative gratuite à Pusher)
    'soketi' => [
        'driver' => 'pusher',
        'key' => env('PUSHER_APP_KEY', 'app-key'),
        'secret' => env('PUSHER_APP_SECRET', 'app-secret'),
        'app_id' => env('PUSHER_APP_ID', 'app-id'),
        'options' => [
            'host' => env('PUSHER_HOST', '127.0.0.1'),
            'port' => env('PUSHER_PORT', 6001),
            'scheme' => env('PUSHER_SCHEME', 'http'),
            'useTLS' => env('PUSHER_SCHEME') === 'https',
        ],
    ],
],
Frontend JavaScript (Alpine.js)
javascript// resources/js/observation-monitor.js
import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;

window.Echo = new Echo({
    broadcaster: 'pusher',
    key: import.meta.env.VITE_PUSHER_APP_KEY,
    cluster: import.meta.env.VITE_PUSHER_APP_CLUSTER,
    forceTLS: true
});

// Composant Alpine.js
document.addEventListener('alpine:init', () => {
    Alpine.data('observationMonitor', (sessionId) => ({
        session: null,
        latestImages: [],
        isLive: false,
        
        init() {
            this.subscribeToSession();
            this.loadInitialData();
        },
        
        subscribeToSession() {
            window.Echo.channel(`session.${sessionId}`)
                .listen('.image.captured', (e) => {
                    console.log('New image captured!', e);
                    this.handleNewImage(e);
                })
                .listen('.session.status.changed', (e) => {
                    console.log('Session status changed', e);
                    this.handleStatusChange(e);
                });
        },
        
        loadInitialData() {
            fetch(`/api/sessions/${sessionId}`)
                .then(r => r.json())
                .then(data => {
                    this.session = data.session;
                    this.latestImages = data.latest_images;
                    this.isLive = data.session.status === 'running';
                });
        },
        
        handleNewImage(event) {
            // Ajouter la nouvelle image
            this.latestImages.unshift(event.latest_image);
            
            // Garder seulement les 6 dernières
            if (this.latestImages.length > 6) {
                this.latestImages.pop();
            }
            
            // Mettre à jour les stats
            this.session.shots_done = event.shots_done;
            this.session.progress = event.progress;
            
            // Notification visuelle
            this.showNotification('Nouvelle image capturée !');
            
            // Son (optionnel)
            this.playNotificationSound();
        },
        
        handleStatusChange(event) {
            this.session.status = event.new_status;
            this.isLive = event.new_status === 'running';
            
            this.showNotification(event.message);
            
            if (event.new_status === 'completed') {
                this.playCompletionSound();
            }
        },
        
        showNotification(message) {
            // Notification navigateur
            if ('Notification' in window && Notification.permission === 'granted') {
                new Notification('Starkeeper.it', {
                    body: message,
                    icon: '/images/logo.png'
                });
            }
            
            // Toast dans l'interface
            this.$dispatch('toast', { message, type: 'success' });
        },
        
        playNotificationSound() {
            const audio = new Audio('/sounds/notification.mp3');
            audio.volume = 0.3;
            audio.play().catch(() => {});
        },
        
        playCompletionSound() {
            const audio = new Audio('/sounds/completion.mp3');
            audio.volume = 0.5;
            audio.play().catch(() => {});
        }
    }));
});
Blade avec Alpine.js
blade{{-- resources/views/reservations/monitor.blade.php --}}
@extends('layouts.app')

@section('content')
<div 
    x-data="observationMonitor({{ $reservation->currentSession->id }})"
    class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8"
>
    {{-- En-tête avec statut live --}}
    <div class="bg-white rounded-lg shadow-lg p-6 mb-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold" x-text="session?.target_name || '{{ $reservation->target_name }}'"></h1>
                <p class="text-gray-600 mt-1">
                    {{ $reservation->observation_date->format('d/m/Y') }} 
                    - {{ $reservation->start_time }}
                </p>
            </div>
            
            <div>
                <span 
                    x-show="isLive"
                    class="flex items-center px-4 py-2 bg-red-100 text-red-800 rounded-full font-semibold animate-pulse"
                >
                    <span class="w-3 h-3 bg-red-600 rounded-full mr-2"></span>
                    EN DIRECT
                </span>
                
                <span 
                    x-show="!isLive && session?.status === 'completed'"
                    class="px-4 py-2 bg-green-100 text-green-800 rounded-full font-semibold"
                >
                    ✓ Terminée
                </span>
            </div>
        </div>
    </div>
    
    {{-- Barre de progression --}}
    <div class="bg-white rounded-lg shadow-lg p-6 mb-6">
        <div class="flex justify-between items-center mb-4">
            <h2 class="text-xl font-semibold">Progression</h2>
            <span 
                class="text-2xl font-bold"
                x-text="(session?.progress || 0) + '%'"
            ></span>
        </div>
        
        <div class="relative w-full h-6 bg-gray-200 rounded-full overflow-hidden">
            <div 
                class="absolute top-0 left-0 h-full bg-gradient-to-r from-blue-500 to-blue-600 transition-all duration-500 ease-out"
                :style="`width: ${session?.progress || 0}%`"
            >
                <div class="absolute inset-0 bg-white opacity-20 animate-pulse"></div>
            </div>
        </div>
        
        <div class="flex justify-between text-sm text-gray-600 mt-2">
            <span x-text="`${session?.shots_done || 0} / ${session?.shots_total || 0} images`"></span>
            <span x-text="`${session?.integration_time || 0} min d'intégration`"></span>
        </div>
    </div>
    
    {{-- Statistiques --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
        <div class="bg-white rounded-lg shadow-lg p-6">
            <div class="text-sm text-gray-600 mb-2">Temps d'intégration</div>
            <div class="text-3xl font-bold text-blue-600">
                <span x-text="Math.floor((session?.integration_time || 0) / 60)"></span>h
                <span x-text="(session?.integration_time || 0) % 60"></span>m
            </div>
        </div>
        
        <div class="bg-white rounded-lg shadow-lg p-6">
            <div class="text-sm text-gray-600 mb-2">HFD moyen</div>
            <div class="text-3xl font-bold text-green-600">
                <span x-text="(session?.average_hfd || 0).toFixed(2)"></span>
                <span class="text-base text-gray-500">pixels</span>
            </div>
        </div>
        
        <div class="bg-white rounded-lg shadow-lg p-6">
            <div class="text-sm text-gray-600 mb-2">Qualité moyenne</div>
            <div class="text-3xl font-bold text-purple-600">
                <span x-text="getQualityLabel(session?.average_hfd)"></span>
            </div>
        </div>
    </div>
    
    {{-- Galerie d'images en temps réel --}}
    <div class="bg-white rounded-lg shadow-lg p-6 mb-6">
        <h2 class="text-xl font-semibold mb-4">Dernières images capturées</h2>
        
        <div 
            x-show="latestImages.length === 0"
            class="text-center py-12 text-gray-500"
        >
            <div class="text-4xl mb-4">📷</div>
            <p>En attente des premières images...</p>
        </div>
        
        <div 
            x-show="latestImages.length > 0"
            class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4"
        >
            <template x-for="(image, index) in latestImages" :key="image.id">
                <div 
                    class="relative group cursor-pointer rounded-lg overflow-hidden shadow-md hover:shadow-xl transition-shadow"
                    :class="index === 0 ? 'ring-4 ring-blue-500' : ''"
                    @click="openLightbox(image)"
                >
                    <img 
                        :src="image.thumbnail_url" 
                        :alt="image.filename"
                        class="w-full h-48 object-cover"
                    >
                    
                    {{-- Badge "NEW" sur la dernière image --}}
                    <div 
                        x-show="index === 0"
                        class="absolute top-2 right-2 bg-red-500 text-white text-xs px-2 py-1 rounded-full font-bold animate-bounce"
                    >
                        NEW
                    </div>
                    
                    {{-- Info overlay --}}
                    <div class="absolute bottom-0 left-0 right-0 bg-gradient-to-t from-black to-transparent p-3 text-white text-sm opacity-0 group-hover:opacity-100 transition-opacity">
                        <div class="font-bold" x-text="`${image.filter} - ${image.exposure}s`"></div>
                        <div class="text-xs">
                            <span x-text="`HFD: ${image.hfd.toFixed(2)}`"></span>
                            <span class="mx-1">•</span>
                            <span x-text="`⭐ ${image.star_count}`"></span>
                        </div>
                        <div class="text-xs text-gray-300" x-text="formatTime(image.captured_at)"></div>
                    </div>
                </div>
            </template>
        </div>
    </div>
    
    {{-- Contrôles --}}
    <div class="bg-white rounded-lg shadow-lg p-6" x-show="isLive">
        <div class="flex justify-between items-center">
            <div>
                <h3 class="text-lg font-semibold mb-1">Contrôles</h3>
                <p class="text-sm text-gray-600">
                    L'observation est en cours. Vous pouvez l'arrêter si nécessaire.
                </p>
            </div>
            
            <button
                @click="abortObservation"
                class="px-6 py-3 bg-red-600 text-white rounded-lg hover:bg-red-700 transition font-semibold"
            >
                ⏹ Arrêter l'observation
            </button>
        </div>
    </div>
    
    {{-- Toast notifications --}}
    <div 
        x-data="{ show: false, message: '' }"
        @toast.window="show = true; message = $event.detail.message; setTimeout(() => show = false, 3000)"
        x-show="show"
        x-transition
        class="fixed bottom-4 right-4 bg-green-500 text-white px-6 py-4 rounded-lg shadow-lg"
    >
        <p x-text="message"></p>
    </div>
</div>

<script>
    // Fonctions helper pour Alpine
    function getQualityLabel(hfd) {
        if (!hfd) return '-';
        if (hfd < 2) return 'Excellent';
        if (hfd < 3) return 'Bon';
        if (hfd < 4) return 'Moyen';
        return 'Faible';
    }
    
    function formatTime(timestamp) {
        return new Date(timestamp).toLocaleTimeString('fr-FR');
    }
    
    function abortObservation() {
        if (!confirm('Êtes-vous sûr de vouloir arrêter cette observation ?')) {
            return;
        }
        
        fetch('/api/sessions/{{ $reservation->currentSession->id }}/abort', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        })
        .then(r => r.json())
        .then(data => {
            alert('Observation arrêtée avec succès');
            window.location.reload();
        })
        .catch(err => {
            alert('Erreur lors de l\'arrêt de l\'observation');
        });
    }
    
    // Demander la permission pour les notifications
    if ('Notification' in window && Notification.permission === 'default') {
        Notification.requestPermission();
    }
</script>
@endsection

Gestion des crédits
Modèle User étendu
php// app/Models/User.php
<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    protected $fillable = [
        'name', 'email', 'password', 'credits'
    ];
    
    protected $casts = [
        'credits' => 'integer',
    ];
    
    /**
     * Acheter des crédits
     */
    public function purchaseCredits(int $amount, string $paymentId): void
    {
        $this->increment('credits', $amount);
        
        $this->creditTransactions()->create([
            'amount' => $amount,
            'type' => 'purchase',
            'balance_after' => $this->credits,
            'transactionable_type' => 'App\Models\Payment',
            'transactionable_id' => $paymentId,
            'description' => "Achat de {$amount} crédits"
        ]);
    }
    
    /**
     * Déduire des crédits
     */
    public function deductCredits(int $amount, $relatedModel): void
    {
        if ($this->credits < $amount) {
            throw new \Exception('Crédits insuffisants');
        }
        
        $this->decrement('credits', $amount);
        
        $this->creditTransactions()->create([
            'amount' => -$amount,
            'type' => 'reservation',
            'balance_after' => $this->credits,
            'transactionable_type' => get_class($relatedModel),
            'transactionable_id' => $relatedModel->id,
            'description' => "Réservation observation"
        ]);
    }
    
    /**
     * Rembourser des crédits
     */
    public function refundCredits(int $amount, $relatedModel, string $reason): void
    {
        $this->increment('credits', $amount);
        
        $this->creditTransactions()->create([
            'amount' => $amount,
            'type' => 'refund',
            'balance_after' => $this->credits,
            'transactionable_type' => get_class($relatedModel),
            'transactionable_id' => $relatedModel->id,
            'description' => $reason
        ]);
    }
    
    /**
     * Relations
     */
    public function reservations()
    {
        return $this->hasMany(Reservation::class);
    }
    
    public function creditTransactions()
    {
        return $this->hasMany(CreditTransaction::class);
    }
    
    /**
     * Vérifier si l'utilisateur a assez de crédits
     */
    public function hasEnoughCredits(int $amount): bool
    {
        return $this->credits >= $amount;
    }
}
Contrôleur de paiement
php// app/Http/Controllers/PaymentController.php
<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use Illuminate\Http\Request;
use Stripe\Stripe;
use Stripe\Checkout\Session as StripeSession;

class PaymentController extends Controller
{
    public function __construct()
    {
        Stripe::setApiKey(config('services.stripe.secret'));
    }
    
    /**
     * Page d'achat de crédits
     */
    public function index()
    {
        $packages = [
            ['credits' => 10, 'price' => 100, 'bonus' => 0],
            ['credits' => 50, 'price' => 450, 'bonus' => 5],
            ['credits' => 100, 'price' => 850, 'bonus' => 15],
            ['credits' => 200, 'price' => 1600, 'bonus' => 40],
        ];
        
        return view('credits.purchase', compact('packages'));
    }
    
    /**
     * Créer une session Stripe
     */
    public function createCheckoutSession(Request $request)
    {
        $validated = $request->validate([
            'credits' => 'required|integer|in:10,50,100,200'
        ]);
        
        $packages = [
            10 => ['price' => 100, 'bonus' => 0],
            50 => ['price' => 450, 'bonus' => 5],
            100 => ['price' => 850, 'bonus' => 15],
            200 => ['price' => 1600, 'bonus' => 40],
        ];
        
        $package = $packages[$validated['credits']];
        $totalCredits = $validated['credits'] + $package['bonus'];
        
        // Créer le paiement en base
        $payment = Payment::create([
            'user_id' => auth()->id(),
            'amount' => $package['price'],
            'credits' => $totalCredits,
            'status' => 'pending'
        ]);
        
        // Créer la session Stripe
        $session = StripeSession::create([
            'payment_method_types' => ['card'],
            'line_items' => [[
                'price_data' => [
                    'currency' => 'eur',
                    'unit_amount' => $package['price'] * 100, // centimes
                    'product_data' => [
                        'name' => "{$validated['credits']} crédits Starkeeper",
                        'description' => $package['bonus'] > 0 
                            ? "+{$package['bonus']} crédits bonus inclus !" 
                            : null,
                    ],
                ],
                'quantity' => 1,
            ]],
            'mode' => 'payment',
            'success_url' => route('payment.success', ['payment' => $payment->id]),
            'cancel_url' => route('payment.cancel'),
            'client_reference_id' => $payment->id,
            'customer_email' => auth()->user()->email,
        ]);
        
        $payment->update(['stripe_session_id' => $session->id]);
        
        return redirect($session->url);
    }
    
    /**
     * Page de succès
     */
    public function success(Payment $payment)
    {
        if ($payment->user_id !== auth()->id()) {
            abort(403);
        }
        
        if ($payment->status === 'pending') {
            // Le webhook n'a pas encore été reçu
            return view('credits.processing', compact('payment'));
        }
        
        return view('credits.success', compact('payment'));
    }
    
    /**
     * Webhook Stripe
     */
    public function webhook(Request $request)
    {
        $endpoint_secret = config('services.stripe.webhook_secret');
        
        $payload = $request->getContent();
        $sig_header = $request->header('Stripe-Signature');
        
        try {
            $event = \Stripe\Webhook::constructEvent(
                $payload, $sig_header, $endpoint_secret
            );
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
        
        // Gérer l'événement
        if ($event->type === 'checkout.session.completed') {
            $session = $event->data->object;
            
            $payment = Payment::where('stripe_session_id', $session->id)->first();
            
            if ($payment && $payment->status === 'pending') {RéessayerCContinuerModifierDocumentation Technique - Starkeeper.it (Suite)
Gestion des crédits (suite)
Contrôleur de paiement (suite)
php                // Marquer le paiement comme complété
                $payment->update([
                    'status' => 'completed',
                    'completed_at' => now()
                ]);
                
                // Ajouter les crédits à l'utilisateur
                $payment->user->purchaseCredits($payment->credits, $payment->id);
                
                // Envoyer une notification
                $payment->user->notify(new \App\Notifications\CreditsAdded($payment));
            }
        }
        
        return response()->json(['success' => true]);
    }
}
Modèle Payment
php// app/Models/Payment.php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $fillable = [
        'user_id', 'amount', 'credits', 'status', 
        'stripe_session_id', 'completed_at'
    ];
    
    protected $casts = [
        'amount' => 'integer',
        'credits' => 'integer',
        'completed_at' => 'datetime',
    ];
    
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    
    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }
}
Migration Payment
php// database/migrations/xxxx_create_payments_table.php
Schema::create('payments', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained()->onDelete('cascade');
    $table->integer('amount'); // en centimes d'euro
    $table->integer('credits');
    $table->enum('status', ['pending', 'completed', 'failed', 'refunded'])->default('pending');
    $table->string('stripe_session_id')->nullable();
    $table->timestamp('completed_at')->nullable();
    $table->timestamps();
    
    $table->index(['user_id', 'status']);
});
Notification
php// app/Notifications/CreditsAdded.php
<?php

namespace App\Notifications;

use App\Models\Payment;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CreditsAdded extends Notification
{
    use Queueable;
    
    public function __construct(
        public Payment $payment
    ) {}
    
    public function via($notifiable)
    {
        return ['mail', 'database'];
    }
    
    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Crédits ajoutés à votre compte')
            ->greeting('Bonjour ' . $notifiable->name . ' !')
            ->line("{$this->payment->credits} crédits ont été ajoutés à votre compte.")
            ->line("Nouveau solde : {$notifiable->credits} crédits")
            ->action('Réserver une observation', route('reservations.create'))
            ->line('Merci d\'utiliser Starkeeper.it !');
    }
    
    public function toArray($notifiable)
    {
        return [
            'type' => 'credits_added',
            'credits' => $this->payment->credits,
            'new_balance' => $notifiable->credits,
        ];
    }
}
Vue d'achat de crédits
blade{{-- resources/views/credits/purchase.blade.php --}}
@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <div class="text-center mb-12">
        <h1 class="text-4xl font-bold mb-4">Acheter des crédits</h1>
        <p class="text-xl text-gray-600">
            1 crédit = 1 heure de télescope
        </p>
        <p class="text-gray-500 mt-2">
            Votre solde actuel : <strong>{{ auth()->user()->credits }} crédits</strong>
        </p>
    </div>
    
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        @foreach($packages as $package)
            <div class="bg-white rounded-2xl shadow-xl p-8 relative hover:scale-105 transition-transform {{ $loop->index === 2 ? 'ring-4 ring-blue-500' : '' }}">
                @if($loop->index === 2)
                    <div class="absolute -top-4 left-1/2 transform -translate-x-1/2 bg-blue-500 text-white px-4 py-1 rounded-full text-sm font-bold">
                        POPULAIRE
                    </div>
                @endif
                
                @if($package['bonus'] > 0)
                    <div class="absolute top-4 right-4 bg-green-500 text-white px-3 py-1 rounded-full text-xs font-bold">
                        +{{ $package['bonus'] }} BONUS
                    </div>
                @endif
                
                <div class="text-center mb-6">
                    <div class="text-5xl font-bold text-blue-600 mb-2">
                        {{ $package['credits'] }}
                    </div>
                    <div class="text-gray-600">crédits</div>
                    
                    @if($package['bonus'] > 0)
                        <div class="text-sm text-green-600 font-semibold mt-2">
                            + {{ $package['bonus'] }} crédits bonus
                        </div>
                        <div class="text-xs text-gray-500">
                            = {{ $package['credits'] + $package['bonus'] }} crédits au total
                        </div>
                    @endif
                </div>
                
                <div class="text-center mb-6">
                    <div class="text-3xl font-bold">
                        {{ $package['price'] / 100 }}€
                    </div>
                    <div class="text-sm text-gray-500 mt-1">
                        {{ number_format($package['price'] / $package['credits'] / 100, 2) }}€ / crédit
                    </div>
                </div>
                
                <form action="{{ route('payment.checkout') }}" method="POST">
                    @csrf
                    <input type="hidden" name="credits" value="{{ $package['credits'] }}">
                    <button 
                        type="submit"
                        class="w-full bg-blue-600 text-white py-3 rounded-lg hover:bg-blue-700 transition font-semibold"
                    >
                        Acheter
                    </button>
                </form>
            </div>
        @endforeach
    </div>
    
    <div class="mt-12 bg-blue-50 rounded-lg p-6">
        <h2 class="text-xl font-bold mb-4">💡 Comment ça marche ?</h2>
        <div class="grid md:grid-cols-3 gap-6">
            <div>
                <div class="text-3xl mb-2">1️⃣</div>
                <h3 class="font-semibold mb-2">Achetez des crédits</h3>
                <p class="text-sm text-gray-600">
                    Choisissez votre pack et payez en toute sécurité avec Stripe
                </p>
            </div>
            <div>
                <div class="text-3xl mb-2">2️⃣</div>
                <h3 class="font-semibold mb-2">Réservez votre observation</h3>
                <p class="text-sm text-gray-600">
                    Configurez votre cible et vos paramètres d'acquisition
                </p>
            </div>
            <div>
                <div class="text-3xl mb-2">3️⃣</div>
                <h3 class="font-semibold mb-2">Recevez vos images</h3>
                <p class="text-sm text-gray-600">
                    Téléchargez vos images FITS et traitez-les comme vous le souhaitez
                </p>
            </div>
        </div>
    </div>
    
    <div class="mt-8 text-center text-sm text-gray-500">
        <p>Paiement sécurisé par Stripe • Remboursement automatique en cas de météo défavorable</p>
    </div>
</div>
@endsection

Déploiement
Structure serveur
/var/www/
├── starkeeper/              # Application Laravel
│   ├── app/
│   ├── config/
│   ├── public/             # Document root Nginx
│   ├── storage/
│   │   ├── app/
│   │   │   └── sessions/   # Images capturées
│   │   └── logs/
│   └── .env
│
└── voyager-proxy/          # Service Node.js
    ├── server.js
    ├── voyager-client.js
    ├── config.js
    └── .env
Configuration Nginx
nginx# /etc/nginx/sites-available/starkeeper.it
server {
    listen 80;
    server_name starkeeper.it www.starkeeper.it;
    return 301 https://$server_name$request_uri;
}

server {
    listen 443 ssl http2;
    server_name starkeeper.it www.starkeeper.it;
    
    root /var/www/starkeeper/public;
    index index.php;
    
    # SSL Certificates
    ssl_certificate /etc/letsencrypt/live/starkeeper.it/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/starkeeper.it/privkey.pem;
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_ciphers HIGH:!aNULL:!MD5;
    
    # Logs
    access_log /var/log/nginx/starkeeper-access.log;
    error_log /var/log/nginx/starkeeper-error.log;
    
    # Max upload size (pour les futures features)
    client_max_body_size 100M;
    
    # Security headers
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header X-XSS-Protection "1; mode=block" always;
    
    # Laravel
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }
    
    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_read_timeout 300;
    }
    
    # Cache static assets
    location ~* \.(jpg|jpeg|png|gif|ico|css|js|svg|woff|woff2|ttf|eot)$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
    }
    
    # Deny access to hidden files
    location ~ /\. {
        deny all;
    }
}
Service systemd pour Node.js
ini# /etc/systemd/system/voyager-proxy.service
[Unit]
Description=Voyager Proxy Service
After=network.target

[Service]
Type=simple
User=www-data
WorkingDirectory=/var/www/voyager-proxy
Environment=NODE_ENV=production
ExecStart=/usr/bin/node server.js
Restart=always
RestartSec=10
StandardOutput=journal
StandardError=journal
SyslogIdentifier=voyager-proxy

# Limites de ressources
LimitNOFILE=65536
MemoryMax=512M

[Install]
WantedBy=multi-user.target
Configuration Supervisor (Laravel Queue)
ini# /etc/supervisor/conf.d/starkeeper-worker.conf
[program:starkeeper-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/starkeeper/artisan queue:work --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=4
redirect_stderr=true
stdout_logfile=/var/www/starkeeper/storage/logs/worker.log
stopwaitsecs=3600
Script de déploiement
bash#!/bin/bash
# deploy.sh

set -e

echo "🚀 Déploiement Starkeeper.it"

# Variables
APP_DIR="/var/www/starkeeper"
PROXY_DIR="/var/www/voyager-proxy"
BRANCH="main"

# 1. Mise à jour Laravel
echo "📦 Mise à jour de l'application Laravel..."
cd $APP_DIR
git pull origin $BRANCH

# 2. Dépendances
echo "📚 Installation des dépendances..."
composer install --no-dev --optimize-autoloader
npm ci
npm run build

# 3. Optimisations
echo "⚡ Optimisations..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 4. Migrations
echo "🗄️ Migrations de base de données..."
php artisan migrate --force

# 5. Permissions
echo "🔐 Configuration des permissions..."
chown -R www-data:www-data $APP_DIR/storage
chown -R www-data:www-data $APP_DIR/bootstrap/cache
chmod -R 775 $APP_DIR/storage
chmod -R 775 $APP_DIR/bootstrap/cache

# 6. Redémarrage des services
echo "🔄 Redémarrage des services..."
systemctl restart php8.2-fpm
systemctl restart nginx
supervisorctl restart starkeeper-worker:*

# 7. Mise à jour du proxy Node.js
echo "🔧 Mise à jour du proxy Voyager..."
cd $PROXY_DIR
git pull origin $BRANCH
npm ci --production
systemctl restart voyager-proxy

# 8. Vérification
echo "✅ Vérification des services..."
systemctl status php8.2-fpm --no-pager
systemctl status nginx --no-pager
systemctl status voyager-proxy --no-pager
supervisorctl status

echo "✨ Déploiement terminé !"
Script de sauvegarde
bash#!/bin/bash
# backup.sh

set -e

BACKUP_DIR="/var/backups/starkeeper"
DATE=$(date +%Y%m%d_%H%M%S)
APP_DIR="/var/www/starkeeper"

mkdir -p $BACKUP_DIR

echo "💾 Sauvegarde Starkeeper - $DATE"

# 1. Base de données
echo "🗄️ Sauvegarde base de données..."
mysqldump -u starkeeper -p"$DB_PASSWORD" starkeeper_db | gzip > "$BACKUP_DIR/db_$DATE.sql.gz"

# 2. Images et fichiers
echo "📁 Sauvegarde des fichiers..."
tar -czf "$BACKUP_DIR/storage_$DATE.tar.gz" -C "$APP_DIR" storage/app/sessions

# 3. Configuration
echo "⚙️ Sauvegarde configuration..."
cp "$APP_DIR/.env" "$BACKUP_DIR/env_$DATE"
cp "/var/www/voyager-proxy/.env" "$BACKUP_DIR/proxy_env_$DATE"

# 4. Nettoyage (garder 30 jours)
echo "🧹 Nettoyage anciennes sauvegardes..."
find $BACKUP_DIR -name "*.gz" -mtime +30 -delete
find $BACKUP_DIR -name "env_*" -mtime +30 -delete

# 5. Sync vers stockage distant (optionnel)
# rclone copy $BACKUP_DIR remote:starkeeper-backups/

echo "✅ Sauvegarde terminée : $BACKUP_DIR"
Crontab
cron# crontab -e

# Laravel Scheduler
* * * * * cd /var/www/starkeeper && php artisan schedule:run >> /dev/null 2>&1

# Sauvegarde quotidienne à 3h du matin
0 3 * * * /var/www/scripts/backup.sh >> /var/log/starkeeper-backup.log 2>&1

# Nettoyage des logs tous les lundis
0 0 * * 1 cd /var/www/starkeeper && php artisan log:clear >> /dev/null 2>&1

Sécurité
Variables d'environnement
bash# .env (Laravel)
APP_NAME=Starkeeper
APP_ENV=production
APP_KEY=base64:...
APP_DEBUG=false
APP_URL=https://starkeeper.it

# Base de données
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=starkeeper_db
DB_USERNAME=starkeeper_user
DB_PASSWORD=***STRONG_PASSWORD***

# Redis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=***REDIS_PASSWORD***
REDIS_PORT=6379

# Mail
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=***
MAIL_PASSWORD=***
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@starkeeper.it
MAIL_FROM_NAME="${APP_NAME}"

# Stripe
STRIPE_KEY=pk_live_***
STRIPE_SECRET=sk_live_***
STRIPE_WEBHOOK_SECRET=whsec_***

# Pusher/Broadcasting
PUSHER_APP_ID=***
PUSHER_APP_KEY=***
PUSHER_APP_SECRET=***
PUSHER_APP_CLUSTER=eu

# Voyager
VOYAGER_PROXY_URL=http://localhost:3000
VOYAGER_PROFILE=Production.v2y
VOYAGER_DEFAULT_SEQUENCE_GUID=ae4df8c6-41ca-4a3e-bdf5-594bbab7881a
bash# .env (Node.js Proxy)
PORT=3000
NODE_ENV=production

# Voyager Connection
VOYAGER_HOST=192.168.1.100
VOYAGER_PORT=5950
VOYAGER_AUTH_BASE=***BASE64_AUTH***
VOYAGER_MAC_KEY=***MAC_KEY***
VOYAGER_MAC_WORD1=***
VOYAGER_MAC_WORD2=***
VOYAGER_MAC_WORD3=***
VOYAGER_MAC_WORD4=***
Permissions fichiers
bash# Propriétaire
chown -R www-data:www-data /var/www/starkeeper
chown -R www-data:www-data /var/www/voyager-proxy

# Permissions générales
find /var/www/starkeeper -type f -exec chmod 644 {} \;
find /var/www/starkeeper -type d -exec chmod 755 {} \;

# Storage et cache
chmod -R 775 /var/www/starkeeper/storage
chmod -R 775 /var/www/starkeeper/bootstrap/cache

# Fichiers sensibles
chmod 600 /var/www/starkeeper/.env
chmod 600 /var/www/voyager-proxy/.env
Pare-feu (UFW)
bash# Autoriser SSH
ufw allow 22/tcp

# Autoriser HTTP/HTTPS
ufw allow 80/tcp
ufw allow 443/tcp

# Bloquer l'accès direct au proxy Node.js depuis l'extérieur
ufw deny 3000/tcp

# Activer le pare-feu
ufw enable
Fail2ban
ini# /etc/fail2ban/jail.local
[nginx-limit-req]
enabled = true
filter = nginx-limit-req
logpath = /var/log/nginx/*error.log
maxretry = 5
bantime = 3600

[nginx-noscript]
enabled = true
filter = nginx-noscript
logpath = /var/log/nginx/*access.log
maxretry = 6
bantime = 86400

[nginx-badbots]
enabled = true
filter = nginx-badbots
logpath = /var/log/nginx/*access.log
maxretry = 2
bantime = 86400
Middleware de rate limiting
php// app/Http/Kernel.php
protected $middlewareGroups = [
    'web' => [
        // ...
        \Illuminate\Routing\Middleware\ThrottleRequests::class.':web',
    ],
    
    'api' => [
        \Illuminate\Routing\Middleware\ThrottleRequests::class.':api',
    ],
];

protected $middlewareAliases = [
    'throttle' => \Illuminate\Routing\Middleware\ThrottleRequests::class,
];

// routes/web.php
Route::middleware(['auth', 'throttle:60,1'])->group(function () {
    Route::post('/reservations', [ReservationController::class, 'store']);
});

// API interne Voyager (encore plus restrictif)
Route::middleware(['throttle:30,1'])->group(function () {
    Route::post('/api/sessions/{session}/abort', [SessionController::class, 'abort']);
});

Monitoring et logs
Logging structuré
php// config/logging.php
'channels' => [
    'stack' => [
        'driver' => 'stack',
        'channels' => ['single', 'slack'],
        'ignore_exceptions' => false,
    ],
    
    'voyager' => [
        'driver' => 'daily',
        'path' => storage_path('logs/voyager.log'),
        'level' => 'debug',
        'days' => 14,
    ],
    
    'reservations' => [
        'driver' => 'daily',
        'path' => storage_path('logs/reservations.log'),
        'level' => 'info',
        'days' => 30,
    ],
    
    'payments' => [
        'driver' => 'daily',
        'path' => storage_path('logs/payments.log'),
        'level' => 'info',
        'days' => 90,
    ],
];
Utilisation des logs
php// Dans vos classes
use Illuminate\Support\Facades\Log;

// Log Voyager
Log::channel('voyager')->info('Target created', [
    'target_guid' => $targetGuid,
    'user_id' => $userId,
    'target_name' => $targetName
]);

// Log réservation
Log::channel('reservations')->info('Reservation created', [
    'reservation_id' => $reservation->id,
    'user_id' => $reservation->user_id,
    'cost' => $reservation->cost,
    'observation_date' => $reservation->observation_date
]);

// Log paiement
Log::channel('payments')->info('Payment completed', [
    'payment_id' => $payment->id,
    'user_id' => $payment->user_id,
    'amount' => $payment->amount,
    'credits' => $payment->credits
]);
Health checks
php// routes/api.php
Route::get('/health', function () {
    $checks = [
        'app' => true,
        'database' => false,
        'redis' => false,
        'voyager_proxy' => false,
    ];
    
    // Check database
    try {
        DB::connection()->getPdo();
        $checks['database'] = true;
    } catch (\Exception $e) {
        Log::error('Database health check failed', ['error' => $e->getMessage()]);
    }
    
    // Check Redis
    try {
        Redis::ping();
        $checks['redis'] = true;
    } catch (\Exception $e) {
        Log::error('Redis health check failed', ['error' => $e->getMessage()]);
    }
    
    // Check Voyager Proxy
    try {
        $response = Http::timeout(5)->get(config('services.voyager.proxy_url') . '/health');
        $checks['voyager_proxy'] = $response->successful();
    } catch (\Exception $e) {
        Log::error('Voyager proxy health check failed', ['error' => $e->getMessage()]);
    }
    
    $allHealthy = !in_array(false, $checks, true);
    
    return response()->json([
        'status' => $allHealthy ? 'healthy' : 'unhealthy',
        'checks' => $checks,
        'timestamp' => now()->toIso8601String()
    ], $allHealthy ? 200 : 503);
});
Monitoring externe (UptimeRobot, etc.)
Endpoint à surveiller :
- https://starkeeper.it/health (toutes les 5 minutes)
- https://starkeeper.it (toutes les 5 minutes)

Alertes :
- Email admin si down > 5 minutes
- SMS si down > 15 minutes

Optimisations
Cache Redis
php// config/cache.php
'default' => env('CACHE_DRIVER', 'redis'),

// Utilisation
use Illuminate\Support\Facades\Cache;

// Cache la liste des targets
$targets = Cache::remember('voyager.targets.' . $setGuid, 300, function () use ($setGuid) {
    return app(VoyagerService::class)->getTargets($setGuid);
});

// Invalider le cache après modification
Cache::forget('voyager.targets.' . $setGuid);
Queue asynchrone
php// Tous les jobs longs doivent être en queue
SyncVoyagerSession::dispatch($session)->onQueue('voyager');
PrepareObservation::dispatch($reservation)->onQueue('high');
FinalizeSession::dispatch($session)->onQueue('low');
Index base de données
php// Les indexes sont déjà définis dans les migrations, mais vérifier :
Schema::table('captured_images', function (Blueprint $table) {
    $table->index(['session_id', 'deleted']); // ✅
    $table->index('voyager_shot_done_guid'); // ✅
});

Schema::table('reservations', function (Blueprint $table) {
    $table->index(['user_id', 'observation_date']); // ✅
    $table->index('status'); // ✅
});
Eager Loading
php// Éviter les requêtes N+1
$reservations = Reservation::with(['user', 'shots', 'sessions.images'])
    ->where('status', 'running')
    ->get();

Commandes artisan utiles
php// app/Console/Commands/TestVoyagerConnection.php
<?php

namespace App\Console\Commands;

use App\Services\VoyagerService;
use Illuminate\Console\Command;

class TestVoyagerConnection extends Command
{
    protected $signature = 'voyager:test';
    protected $description = 'Test Voyager connection';
    
    public function handle(VoyagerService $voyager)
    {
        $this->info('Testing Voyager connection...');
        
        try {
            $targets = $voyager->getTargets();
            $this->info('✅ Connection successful!');
            $this->info('Targets found: ' . count($targets['ParamRet']['list'] ?? []));
        } catch (\Exception $e) {
            $this->error('❌ Connection failed: ' . $e->getMessage());
            return 1;
        }
        
        return 0;
    }
}
bash# Commandes disponibles
php artisan voyager:test                    # Tester la connexion
php artisan reservations:prepare-tomorrow   # Préparer les observations
php artisan sessions:finalize-old           # Finaliser les sessions anciennes
php artisan cache:clear                     # Vider le cache
php artisan queue:work                      # Démarrer un worker
php artisan queue:restart                   # Redémarrer les workers
php artisan telescope:prune                 # Nettoyer Telescope (si installé)

Checklist de mise en production
Avant le déploiement

 Tests unitaires passent
 Tests d'intégration passent
 Connexion Voyager testée
 Paiements Stripe en mode test validés
 Variables d'environnement production configurées
 Certificats SSL installés
 Sauvegardes automatiques configurées
 Monitoring externe configuré

Après le déploiement

 Health check répond 200
 Page d'accueil accessible
 Inscription utilisateur fonctionne
 Achat de crédits fonctionne (test réel)
 Création de réservation fonctionne
 Service Node.js actif et connecté
 Queues Laravel actives
 Logs ne montrent pas d'erreurs critiques
 Emails envoyés correctement

Tests end-to-end

Créer un compte utilisateur
Acheter 10 crédits
Créer une réservation de test
Vérifier que le target est créé dans Voyager
Simuler une session (ou attendre une vraie)
Vérifier le monitoring temps réel
Vérifier la réception des images
Tester l'abort
Vérifier la finalisation
Vérifier l'email de fin


Support et maintenance
Documentation utilisateur
Créer une documentation séparée pour les utilisateurs finaux couvrant :

Comment acheter des crédits
Comment créer une réservation
Comment configurer ses targets
Comment suivre une observation en direct
Comment télécharger ses images
FAQ commune

Monitoring quotidien

Vérifier les logs d'erreur chaque matin
Surveiller l'utilisation des crédits
Vérifier que les observations se déroulent bien
Surveiller l'espace disque (images)
Vérifier la santé du proxy Node.js

Mises à jour

Sauvegardes avant chaque mise à jour
Tests en staging d'abord
Déploiements le matin (jamais la nuit pendant les observations)
Maintenir un changelog


Conclusion
Cette architecture permet :
✅ Connexion stable à Voyager via Node.js
✅ Monitoring temps réel avec WebSocket/Pusher
✅ Gestion automatisée des observations
✅ Scalabilité avec queues et cache Redis
✅ Sécurité avec authentification, paiements Stripe, rate limiting
✅ Maintenance facilitée avec logs structurés et health checks
