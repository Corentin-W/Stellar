# RoboTarget Live Monitoring Implementation Plan

> **For Claude:** REQUIRED SUB-SKILL: Use superpowers:executing-plans to implement this plan task-by-task.

**Goal:** Create a real-time monitoring dashboard to track active RoboTarget telescope observations with live updates of equipment status, image progress, and quality metrics.

**Architecture:** WebSocket-based real-time system using Laravel Broadcasting (Pusher/Socket.io). Voyager proxy sends events (ControlData 2s, ShotRunning 1s, NewJPGReady) to Laravel webhooks which broadcast to private channels. Alpine.js frontend consumes WebSocket stream and updates UI components reactively. Dashboard displays telescope state, current exposure progress, last captured images with HFD/StarIndex quality metrics, and session timeline.

**Tech Stack:** Laravel 11, Alpine.js, Tailwind CSS, Laravel Broadcasting, WebSockets (Pusher/Socket.io), Blade Templates

---

## Task 1: Add ShotRunning Webhook Endpoint

**Files:**
- Modify: `app/Http/Controllers/Api/VoyagerEventController.php` (add method after imageReady)
- Modify: `routes/api.php:31` (add route after image-ready)
- Create: `app/Events/RoboTargetShotRunning.php`

**Step 1: Create RoboTargetShotRunning Event**

```bash
# Create event class
mkdir -p app/Events
```

Create `app/Events/RoboTargetShotRunning.php`:

```php
<?php

namespace App\Events;

use App\Models\RoboTargetSession;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class RoboTargetShotRunning implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public RoboTargetSession $session,
        public array $shotData = []
    ) {}

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('user.' . $this->session->roboTarget->user_id),
            new PrivateChannel('robotarget.session.' . $this->session->id),
        ];
    }

    public function broadcastAs(): string
    {
        return 'shot.running';
    }

    public function broadcastWith(): array
    {
        return [
            'session_id' => $this->session->id,
            'shot' => [
                'file' => $this->shotData['File'] ?? null,
                'exposure' => $this->shotData['Expo'] ?? 0,
                'elapsed' => $this->shotData['Elapsed'] ?? 0,
                'elapsed_perc' => $this->shotData['ElapsedPerc'] ?? 0,
                'status' => $this->shotData['Status'] ?? 0, // 1=Exposure, 2=Download, 3=JPG
            ],
            'timestamp' => now()->toISOString(),
        ];
    }
}
```

**Step 2: Add shotRunning method to VoyagerEventController**

In `app/Http/Controllers/Api/VoyagerEventController.php`, add after `imageReady()`:

```php
    /**
     * Handle shot running event from Voyager Proxy (sent every second during exposure)
     */
    public function shotRunning(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'session_guid' => 'required|string',
            'File' => 'nullable|string',
            'Expo' => 'nullable|numeric',
            'Elapsed' => 'nullable|numeric',
            'ElapsedPerc' => 'nullable|numeric',
            'Status' => 'nullable|integer',
        ]);

        try {
            $session = RoboTargetSession::where('session_guid', $validated['session_guid'])
                ->with('roboTarget')
                ->firstOrFail();

            // Broadcast shot running event
            broadcast(new \App\Events\RoboTargetShotRunning(
                $session,
                $validated
            ));

            return response()->json(['success' => true]);

        } catch (\Exception $e) {
            Log::error('Failed to handle shot running event', [
                'error' => $e->getMessage(),
                'session_guid' => $validated['session_guid'],
            ]);

            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }
```

**Step 3: Add route for shot-running webhook**

In `routes/api.php`, add after line 31:

```php
Route::prefix('voyager/events')->group(function () {
    Route::post('/session-started', [VoyagerEventController::class, 'sessionStarted']);
    Route::post('/progress', [VoyagerEventController::class, 'progress']);
    Route::post('/shot-running', [VoyagerEventController::class, 'shotRunning']); // NEW
    Route::post('/image-ready', [VoyagerEventController::class, 'imageReady']);
    Route::post('/session-completed', [VoyagerEventController::class, 'sessionCompleted']);
});
```

**Step 4: Test webhook endpoint**

```bash
# Test with curl
curl -X POST http://stellar.test/api/voyager/events/shot-running \
  -H "Content-Type: application/json" \
  -d '{
    "session_guid": "test-guid",
    "File": "M42_L_001.fit",
    "Expo": 300,
    "Elapsed": 45,
    "ElapsedPerc": 15,
    "Status": 1
  }'
```

Expected: 404 (session not found) or 200 (success)

**Step 5: Commit**

```bash
git add app/Events/RoboTargetShotRunning.php app/Http/Controllers/Api/VoyagerEventController.php routes/api.php
git commit -m "feat: add shot-running webhook endpoint for real-time exposure tracking"
```

---

## Task 2: Create Live Monitoring Page Route & Controller

**Files:**
- Create: `app/Http/Controllers/RoboTargetMonitorController.php`
- Modify: `routes/web.php` (add monitor route)

**Step 1: Create RoboTargetMonitorController**

Create `app/Http/Controllers/RoboTargetMonitorController.php`:

```php
<?php

namespace App\Http\Controllers;

use App\Models\RoboTarget;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RoboTargetMonitorController extends Controller
{
    /**
     * Display live monitoring page for a specific target
     */
    public function show(Request $request, string $guid): View
    {
        $target = RoboTarget::where('guid', $guid)
            ->with(['user', 'shots', 'sessions' => function($query) {
                $query->latest()->limit(1);
            }])
            ->firstOrFail();

        // Verify user owns this target
        if ($target->user_id !== auth()->id() && !auth()->user()->admin) {
            abort(403, 'Unauthorized');
        }

        $activeSession = $target->sessions()
            ->whereNull('result')
            ->latest()
            ->first();

        return view('robotarget.monitor', [
            'target' => $target,
            'session' => $activeSession,
            'locale' => app()->getLocale(),
        ]);
    }
}
```

**Step 2: Add route in routes/web.php**

Find the RoboTarget routes group and add:

```php
Route::middleware(['auth:web', 'locale'])->prefix('{locale?}')->group(function () {
    // ... existing routes ...

    Route::prefix('robotarget')->name('robotarget.')->group(function () {
        // ... existing routes ...
        Route::get('/{guid}/monitor', [RoboTargetMonitorController::class, 'show'])->name('monitor');
    });
});
```

**Step 3: Test route is accessible**

```bash
# Check route exists
php artisan route:list | grep "robotarget.monitor"
```

Expected: Route listed with GET method

**Step 4: Commit**

```bash
git add app/Http/Controllers/RoboTargetMonitorController.php routes/web.php
git commit -m "feat: add live monitoring page route and controller"
```

---

## Task 3: Create Base Monitoring View with Layout

**Files:**
- Create: `resources/views/robotarget/monitor.blade.php`

**Step 1: Create monitor.blade.php**

Create `resources/views/robotarget/monitor.blade.php`:

```blade
@extends('layouts.astral-app')

@section('title', 'Live Monitoring - ' . $target->target_name)

@section('content')
<div class="min-h-screen p-4 lg:p-6"
     x-data="monitoringDashboard({{ $session?->id ?? 'null' }}, '{{ $target->guid }}')"
     x-init="init()">

    {{-- Header --}}
    <div class="mb-6">
        <div class="flex items-center justify-between">
            <div>
                <a href="{{ route('robotarget.index', ['locale' => $locale]) }}"
                   class="inline-flex items-center text-sm text-white/60 hover:text-white mb-2 transition-colors">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                    Retour aux targets
                </a>
                <h1 class="text-2xl font-bold text-white mb-1">🔭 {{ $target->target_name }}</h1>
                <p class="text-sm text-white/60">
                    RA: {{ $target->ra_j2000 }} • DEC: {{ $target->dec_j2000 }}
                </p>
            </div>

            {{-- Status Badge --}}
            <div class="flex items-center gap-3">
                <div x-show="isConnected" class="flex items-center gap-2 px-3 py-1.5 bg-green-500/20 border border-green-500/30 rounded-md">
                    <div class="w-2 h-2 bg-green-400 rounded-full animate-pulse"></div>
                    <span class="text-xs font-medium text-green-400 uppercase tracking-wide">Live</span>
                </div>
                <div x-show="!isConnected" class="flex items-center gap-2 px-3 py-1.5 bg-red-500/20 border border-red-500/30 rounded-md">
                    <div class="w-2 h-2 bg-red-400 rounded-full"></div>
                    <span class="text-xs font-medium text-red-400 uppercase tracking-wide">Disconnected</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Main Grid will be added in next tasks --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
        <div class="lg:col-span-2">
            <div class="bg-white/5 backdrop-blur-sm border border-white/10 rounded-md p-4">
                <p class="text-white/60">Monitoring dashboard loading...</p>
            </div>
        </div>
        <div>
            <div class="bg-white/5 backdrop-blur-sm border border-white/10 rounded-md p-4">
                <p class="text-white/60">Controls loading...</p>
            </div>
        </div>
    </div>
</div>

<script>
function monitoringDashboard(sessionId, targetGuid) {
    return {
        sessionId: sessionId,
        targetGuid: targetGuid,
        isConnected: false,

        init() {
            console.log('Monitoring dashboard initialized', { sessionId, targetGuid });
            // WebSocket connection will be added in next task
        }
    }
}
</script>
@endsection
```

**Step 2: Test page renders**

Visit: `http://stellar.test/{locale}/robotarget/{guid}/monitor`

Expected: Page loads with header and placeholder content

**Step 3: Commit**

```bash
git add resources/views/robotarget/monitor.blade.php
git commit -m "feat: create base monitoring view with header and layout"
```

---

## Task 4: Add WebSocket Connection & Real-Time Updates

**Files:**
- Modify: `resources/views/robotarget/monitor.blade.php` (update script section)

**Step 1: Add WebSocket connection logic**

Replace the script section in `monitor.blade.php`:

```javascript
<script>
function monitoringDashboard(sessionId, targetGuid) {
    return {
        sessionId: sessionId,
        targetGuid: targetGuid,
        isConnected: false,
        currentShot: {
            file: null,
            exposure: 0,
            elapsed: 0,
            percentage: 0,
            status: 0
        },
        lastImage: {
            thumbnail: null,
            filename: null,
            hfd: null,
            filter: null,
            timestamp: null
        },
        progress: {
            percentage: 0,
            current_shot: 0,
            total_shots: 0,
            camera: {},
            mount: {}
        },

        init() {
            console.log('Monitoring dashboard initialized', { sessionId, targetGuid });
            this.connectWebSocket();
        },

        connectWebSocket() {
            if (!sessionId || typeof window.Echo === 'undefined') {
                console.warn('WebSocket not available or no session');
                return;
            }

            // Subscribe to user's private channel for this session
            window.Echo.private(`robotarget.session.${sessionId}`)
                .listen('.shot.running', (e) => {
                    this.updateShot(e.shot);
                })
                .listen('.session.progress', (e) => {
                    this.updateProgress(e.progress);
                })
                .listen('.image.ready', (e) => {
                    this.updateImage(e.image);
                })
                .listen('.session.completed', (e) => {
                    this.handleCompletion(e.completion_data);
                });

            // Connection status
            window.Echo.connector.pusher.connection.bind('connected', () => {
                this.isConnected = true;
                console.log('WebSocket connected');
            });

            window.Echo.connector.pusher.connection.bind('disconnected', () => {
                this.isConnected = false;
                console.log('WebSocket disconnected');
            });
        },

        updateShot(shot) {
            this.currentShot = {
                file: shot.file,
                exposure: shot.exposure,
                elapsed: shot.elapsed,
                percentage: shot.elapsed_perc,
                status: shot.status
            };
        },

        updateProgress(progress) {
            this.progress = progress;
        },

        updateImage(image) {
            this.lastImage = {
                thumbnail: image.thumbnail,
                filename: image.filename,
                hfd: image.hfd,
                filter: image.filter,
                timestamp: image.timestamp
            };
        },

        handleCompletion(data) {
            console.log('Session completed', data);
            // Show completion notification
            window.showNotification(
                'Session Terminée',
                `La session d'observation est terminée.`,
                'success',
                10000
            );
        },

        getShotStatusLabel() {
            const labels = {
                0: 'Idle',
                1: 'Exposure',
                2: 'Download',
                3: 'Processing'
            };
            return labels[this.currentShot.status] || 'Unknown';
        },

        formatTime(seconds) {
            const mins = Math.floor(seconds / 60);
            const secs = Math.floor(seconds % 60);
            return `${mins}:${secs.toString().padStart(2, '0')}`;
        }
    }
}
</script>
```

**Step 2: Verify WebSocket library is loaded**

Check that `resources/views/layouts/astral-app.blade.php` includes Echo/Pusher:

```blade
{{-- Should have something like: --}}
<script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>
<script src="{{ asset('js/echo.js') }}"></script>
```

If not present, add before closing `</body>` tag.

**Step 3: Test WebSocket connection**

Open browser console on monitor page.

Expected: "WebSocket connected" message

**Step 4: Commit**

```bash
git add resources/views/robotarget/monitor.blade.php
git commit -m "feat: add WebSocket connection for real-time updates"
```

---

## Task 5: Create Current Exposure Progress Component

**Files:**
- Modify: `resources/views/robotarget/monitor.blade.php` (add exposure component)

**Step 1: Add exposure progress card**

Replace the first grid column placeholder with:

```blade
<div class="lg:col-span-2 space-y-4">

    {{-- Current Exposure Card --}}
    <div class="bg-white/5 backdrop-blur-sm border border-white/10 rounded-md p-4">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-base font-semibold text-white flex items-center gap-2">
                <svg class="w-5 h-5 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
                Exposition en cours
            </h2>
            <span x-text="getShotStatusLabel()"
                  class="px-2 py-1 text-xs font-medium rounded"
                  :class="{
                      'bg-blue-500/20 text-blue-400': currentShot.status === 1,
                      'bg-yellow-500/20 text-yellow-400': currentShot.status === 2,
                      'bg-purple-500/20 text-purple-400': currentShot.status === 3,
                      'bg-gray-500/20 text-gray-400': currentShot.status === 0
                  }">
            </span>
        </div>

        <template x-if="currentShot.file">
            <div>
                {{-- File Info --}}
                <div class="flex items-center justify-between mb-3">
                    <div class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-white/60" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                        </svg>
                        <span class="text-sm font-mono text-white" x-text="currentShot.file"></span>
                    </div>
                    <div class="text-sm text-white/60">
                        <span x-text="formatTime(currentShot.elapsed)"></span> /
                        <span x-text="formatTime(currentShot.exposure)"></span>
                    </div>
                </div>

                {{-- Progress Bar --}}
                <div class="relative h-3 bg-white/10 rounded-full overflow-hidden mb-2">
                    <div class="absolute inset-0 bg-gradient-to-r from-blue-500 via-purple-500 to-pink-500 transition-all duration-300"
                         :style="`width: ${currentShot.percentage}%`">
                    </div>
                    <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white/20 to-transparent animate-pulse"></div>
                </div>

                {{-- Percentage --}}
                <div class="text-right">
                    <span class="text-2xl font-bold text-white" x-text="currentShot.percentage.toFixed(1)"></span>
                    <span class="text-sm text-white/60">%</span>
                </div>
            </div>
        </template>

        <template x-if="!currentShot.file">
            <div class="text-center py-8">
                <div class="text-4xl mb-2">⏳</div>
                <p class="text-sm text-white/60">En attente du prochain shoot...</p>
            </div>
        </template>
    </div>

    {{-- Rest of components will be added in next tasks --}}
</div>
```

**Step 2: Test exposure progress display**

Simulate shot running event in browser console:

```javascript
Alpine.store('monitor').updateShot({
    file: 'M42_L_001.fit',
    exposure: 300,
    elapsed: 45,
    elapsed_perc: 15,
    status: 1
});
```

Expected: Progress bar animates to 15%, time displays correctly

**Step 3: Commit**

```bash
git add resources/views/robotarget/monitor.blade.php
git commit -m "feat: add current exposure progress component with animated progress bar"
```

---

## Task 6: Create Last Image Preview Component

**Files:**
- Modify: `resources/views/robotarget/monitor.blade.php` (add image preview)

**Step 1: Add image preview card after exposure card**

```blade
{{-- Last Captured Image --}}
<div class="bg-white/5 backdrop-blur-sm border border-white/10 rounded-md p-4">
    <h2 class="text-base font-semibold text-white mb-4 flex items-center gap-2">
        <svg class="w-5 h-5 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        Dernière image capturée
    </h2>

    <template x-if="lastImage.thumbnail">
        <div>
            {{-- Image --}}
            <div class="relative mb-3 rounded-md overflow-hidden bg-black aspect-video">
                <img :src="'data:image/jpeg;base64,' + lastImage.thumbnail"
                     :alt="lastImage.filename"
                     class="w-full h-full object-contain">
                <div class="absolute top-2 right-2 px-2 py-1 bg-black/60 backdrop-blur-sm rounded text-xs font-mono text-white">
                    <span x-text="lastImage.filter || 'L'"></span>
                </div>
            </div>

            {{-- Image Metadata --}}
            <div class="grid grid-cols-2 gap-3">
                <div class="bg-white/5 rounded-md p-2">
                    <div class="text-[10px] text-white/50 uppercase tracking-wide mb-0.5">HFD</div>
                    <div class="text-lg font-bold text-white" x-text="lastImage.hfd?.toFixed(2) || 'N/A'"></div>
                    <div class="text-[10px] text-white/60">Focus quality</div>
                </div>
                <div class="bg-white/5 rounded-md p-2">
                    <div class="text-[10px] text-white/50 uppercase tracking-wide mb-0.5">Time</div>
                    <div class="text-sm font-mono text-white" x-text="lastImage.timestamp || 'N/A'"></div>
                    <div class="text-[10px] text-white/60" x-text="lastImage.filename || ''"></div>
                </div>
            </div>
        </div>
    </template>

    <template x-if="!lastImage.thumbnail">
        <div class="text-center py-12 bg-white/5 rounded-md">
            <div class="text-4xl mb-2">📷</div>
            <p class="text-sm text-white/60">Aucune image reçue pour le moment</p>
        </div>
    </template>
</div>
```

**Step 2: Test image preview**

Simulate image ready event:

```javascript
Alpine.store('monitor').updateImage({
    thumbnail: 'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==',
    filename: 'M42_L_001.fit',
    hfd: 2.34,
    filter: 'L',
    timestamp: '23:45:12'
});
```

Expected: Image displays with metadata

**Step 3: Commit**

```bash
git add resources/views/robotarget/monitor.blade.php
git commit -m "feat: add last captured image preview with HFD metrics"
```

---

## Task 7: Create Session Progress & Stats Sidebar

**Files:**
- Modify: `resources/views/robotarget/monitor.blade.php` (add sidebar)

**Step 1: Replace sidebar placeholder**

In the second grid column, replace placeholder with:

```blade
<div class="space-y-4">

    {{-- Session Progress --}}
    <div class="bg-white/5 backdrop-blur-sm border border-white/10 rounded-md p-4">
        <h3 class="text-sm font-semibold text-white mb-3 uppercase tracking-wide">Progression Session</h3>

        {{-- Progress Circle --}}
        <div class="flex items-center justify-center mb-4">
            <div class="relative w-32 h-32">
                <svg class="transform -rotate-90 w-32 h-32">
                    <circle cx="64" cy="64" r="56" stroke="currentColor" stroke-width="8" fill="none" class="text-white/10"/>
                    <circle cx="64" cy="64" r="56" stroke="currentColor" stroke-width="8" fill="none"
                            class="text-purple-500 transition-all duration-500"
                            :stroke-dasharray="352"
                            :stroke-dashoffset="352 - (352 * progress.percentage / 100)"/>
                </svg>
                <div class="absolute inset-0 flex flex-col items-center justify-center">
                    <span class="text-2xl font-bold text-white" x-text="progress.percentage.toFixed(0)"></span>
                    <span class="text-xs text-white/60">%</span>
                </div>
            </div>
        </div>

        {{-- Stats --}}
        <div class="space-y-2">
            <div class="flex items-center justify-between text-sm">
                <span class="text-white/60">Images</span>
                <span class="font-medium text-white">
                    <span x-text="progress.current_shot"></span> / <span x-text="progress.total_shots"></span>
                </span>
            </div>
            <div class="flex items-center justify-between text-sm">
                <span class="text-white/60">Restantes</span>
                <span class="font-medium text-white" x-text="progress.total_shots - progress.current_shot"></span>
            </div>
        </div>
    </div>

    {{-- Telescope Status --}}
    <div class="bg-white/5 backdrop-blur-sm border border-white/10 rounded-md p-4">
        <h3 class="text-sm font-semibold text-white mb-3 uppercase tracking-wide">État Télescope</h3>

        <div class="space-y-3">
            {{-- Camera --}}
            <template x-if="progress.camera">
                <div class="bg-white/5 rounded-md p-2">
                    <div class="flex items-center gap-2 mb-1">
                        <svg class="w-4 h-4 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
                        </svg>
                        <span class="text-xs font-medium text-white">Caméra</span>
                    </div>
                    <div class="text-xs text-white/60" x-text="progress.camera.status || 'Idle'"></div>
                </div>
            </template>

            {{-- Mount --}}
            <template x-if="progress.mount">
                <div class="bg-white/5 rounded-md p-2">
                    <div class="flex items-center gap-2 mb-1">
                        <svg class="w-4 h-4 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064"/>
                        </svg>
                        <span class="text-xs font-medium text-white">Monture</span>
                    </div>
                    <div class="text-xs text-white/60" x-text="progress.mount.status || 'Tracking'"></div>
                </div>
            </template>
        </div>
    </div>

    {{-- Quick Actions --}}
    <div class="bg-white/5 backdrop-blur-sm border border-white/10 rounded-md p-4">
        <h3 class="text-sm font-semibold text-white mb-3 uppercase tracking-wide">Actions</h3>

        <div class="space-y-2">
            <button class="w-full px-3 py-2 bg-red-500/20 hover:bg-red-500/30 border border-red-500/30 text-red-400 rounded-md text-sm font-medium transition-all">
                ⏸️ Pause Session
            </button>
            <button class="w-full px-3 py-2 bg-white/5 hover:bg-white/10 border border-white/10 text-white rounded-md text-sm font-medium transition-all">
                📊 Statistiques Détaillées
            </button>
        </div>
    </div>

</div>
```

**Step 2: Test sidebar components**

Simulate progress update:

```javascript
Alpine.store('monitor').updateProgress({
    percentage: 45,
    current_shot: 12,
    total_shots: 30,
    camera: { status: 'Exposing' },
    mount: { status: 'Tracking' }
});
```

Expected: Progress circle animates to 45%, stats update

**Step 3: Commit**

```bash
git add resources/views/robotarget/monitor.blade.php
git commit -m "feat: add session progress sidebar with circular progress and telescope status"
```

---

## Task 8: Add Auto-Refresh Fallback & Polish

**Files:**
- Modify: `resources/views/robotarget/monitor.blade.php` (add polling fallback)

**Step 1: Add API polling fallback**

Update the `init()` method in the Alpine component:

```javascript
init() {
    console.log('Monitoring dashboard initialized', { sessionId, targetGuid });
    this.connectWebSocket();

    // Fallback: Poll API every 5 seconds if WebSocket not available
    if (!sessionId || typeof window.Echo === 'undefined') {
        this.startPolling();
    }
},

startPolling() {
    setInterval(async () => {
        try {
            const response = await fetch(`/api/robotarget/targets/${this.targetGuid}/progress`, {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                credentials: 'same-origin'
            });

            if (response.ok) {
                const data = await response.json();
                this.updateProgress(data.progress || {});
            }
        } catch (error) {
            console.error('Failed to fetch progress', error);
        }
    }, 5000);
},
```

**Step 2: Add loading states**

Add at the top of the Alpine component data:

```javascript
isLoading: true,

// In init(), after connectWebSocket():
setTimeout(() => {
    this.isLoading = false;
}, 1000);
```

Wrap main content:

```blade
<div x-show="!isLoading">
    {{-- Existing grid content --}}
</div>

<div x-show="isLoading" class="flex items-center justify-center min-h-screen">
    <div class="text-center">
        <div class="inline-block animate-spin rounded-full h-12 w-12 border-4 border-purple-500 border-t-transparent"></div>
        <p class="mt-4 text-white/60">Connexion au télescope...</p>
    </div>
</div>
```

**Step 3: Test loading and polling**

Open page without WebSocket configured.

Expected: Shows loading spinner, then polls API every 5s

**Step 4: Commit**

```bash
git add resources/views/robotarget/monitor.blade.php
git commit -m "feat: add API polling fallback and loading states for monitoring page"
```

---

## Task 9: Create Documentation

**Files:**
- Create: `docs/monitoring-system.md`
- Create: `docs/webhook-events.md`

**Step 1: Create monitoring system documentation**

Create `docs/monitoring-system.md`:

```markdown
# RoboTarget Live Monitoring System

## Overview

The live monitoring system provides real-time tracking of active telescope observations. It displays current exposure progress, last captured images with quality metrics, session statistics, and telescope equipment status.

## Architecture

### Data Flow

```
Voyager Application Server
    ↓ (TCP Socket port 5950)
Node.js Proxy
    ↓ (HTTP Webhooks)
Laravel API Endpoints
    ↓ (Laravel Broadcasting)
WebSocket Server (Pusher/Socket.io)
    ↓ (Private Channels)
Frontend (Alpine.js)
```

### Components

#### Backend

1. **VoyagerEventController** (`app/Http/Controllers/Api/VoyagerEventController.php`)
   - Receives webhooks from proxy
   - Validates payloads
   - Broadcasts events to WebSocket channels

2. **Broadcasting Events**
   - `RoboTargetShotRunning` - Exposure progress (1s interval)
   - `RoboTargetProgress` - Session progress (2s interval)
   - `RoboTargetImageReady` - New image available
   - `RoboTargetSessionCompleted` - Session finished

3. **Channels**
   - `robotarget.session.{id}` - Session-specific updates
   - `user.{id}` - User notifications

#### Frontend

1. **MonitoringDashboard** (`resources/views/robotarget/monitor.blade.php`)
   - Alpine.js reactive component
   - WebSocket subscription
   - Real-time UI updates

2. **UI Components**
   - Current Exposure Progress
   - Last Captured Image Preview
   - Session Progress Circle
   - Telescope Status Cards
   - Quick Actions

### WebSocket Events

#### shot.running
Sent every second during exposure.

**Payload:**
```json
{
  "session_id": 123,
  "shot": {
    "file": "M42_L_001.fit",
    "exposure": 300,
    "elapsed": 45,
    "elapsed_perc": 15.0,
    "status": 1
  },
  "timestamp": "2026-01-13T23:45:12.000Z"
}
```

**Status codes:**
- `0` - Idle
- `1` - Exposure
- `2` - Download
- `3` - Processing

#### session.progress
Sent every 2 seconds during session.

**Payload:**
```json
{
  "session_id": 123,
  "progress": {
    "percentage": 45.5,
    "current_shot": 12,
    "total_shots": 30,
    "camera": {
      "status": "Exposing",
      "temp": -10.5
    },
    "mount": {
      "status": "Tracking",
      "ra": "05:35:17",
      "dec": "-05:23:28"
    }
  }
}
```

#### image.ready
Sent when new image is captured.

**Payload:**
```json
{
  "session_id": 123,
  "image": {
    "thumbnail": "base64_string_here",
    "filename": "M42_L_001.fit",
    "filter": "L",
    "exposure": 300,
    "hfd": 2.34,
    "timestamp": "23:45:12"
  }
}
```

## Usage

### Accessing the Monitor

Navigate to: `/robotarget/{guid}/monitor`

Requirements:
- User must be authenticated
- User must own the target (or be admin)
- Target must have an active session

### Testing

#### Simulate Events via Console

```javascript
// Test shot running
Alpine.store('monitor').updateShot({
    file: 'TEST_001.fit',
    exposure: 300,
    elapsed: 45,
    elapsed_perc: 15,
    status: 1
});

// Test progress
Alpine.store('monitor').updateProgress({
    percentage: 45,
    current_shot: 12,
    total_shots: 30,
    camera: { status: 'Exposing' },
    mount: { status: 'Tracking' }
});

// Test image
Alpine.store('monitor').updateImage({
    thumbnail: 'base64_encoded_image',
    filename: 'TEST_001.fit',
    hfd: 2.34,
    filter: 'L',
    timestamp: new Date().toISOString()
});
```

#### Simulate Webhooks via curl

```bash
# Shot running event
curl -X POST http://stellar.test/api/voyager/events/shot-running \
  -H "Content-Type: application/json" \
  -d '{
    "session_guid": "your-session-guid",
    "File": "M42_L_001.fit",
    "Expo": 300,
    "Elapsed": 45,
    "ElapsedPerc": 15,
    "Status": 1
  }'
```

## Troubleshooting

### WebSocket Not Connecting

1. Check `.env` has broadcasting configured:
   ```
   BROADCAST_DRIVER=pusher
   PUSHER_APP_ID=your_app_id
   PUSHER_APP_KEY=your_key
   PUSHER_APP_SECRET=your_secret
   ```

2. Verify Echo is initialized in layout:
   ```blade
   <script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>
   <script>
       window.Echo = new Echo({
           broadcaster: 'pusher',
           key: '{{ config('broadcasting.connections.pusher.key') }}',
           cluster: '{{ config('broadcasting.connections.pusher.options.cluster') }}',
           encrypted: true
       });
   </script>
   ```

3. Check browser console for connection errors

### Events Not Updating UI

1. Verify session_guid matches in webhook and database
2. Check Laravel logs: `tail -f storage/logs/laravel.log`
3. Verify broadcasting queues are running: `php artisan queue:work`
4. Test webhook endpoints manually with curl

### Performance Issues

1. Reduce polling frequency if WebSocket unavailable
2. Implement debouncing for rapid updates
3. Limit image thumbnail size from proxy
4. Use CDN for static assets

## Future Enhancements

- [ ] Session history timeline
- [ ] HFD/StarIndex trend charts
- [ ] Multiple session monitoring
- [ ] Alert thresholds (HFD spike, tracking issues)
- [ ] Mobile-optimized view
- [ ] Screenshot/recording capability
```

**Step 2: Create webhook events documentation**

Create `docs/webhook-events.md`:

```markdown
# Voyager Proxy Webhook Events

## Overview

This document describes all webhook events sent from the Voyager proxy to the Laravel application.

## Authentication

All webhooks should include the proxy API key:

```http
POST /api/voyager/events/{endpoint}
Content-Type: application/json
X-API-Key: {proxy_api_key}
```

## Event Endpoints

### 1. Session Started

**Endpoint:** `POST /api/voyager/events/session-started`

**Triggered by:** `RoboTargetRunningTargetEphemerisPNG` Voyager event

**Payload:**
```json
{
  "TargetUID": "uuid-string",
  "TargetName": "M42 Orion Nebula",
  "session_guid": "session-uuid",
  "Base64Data": "base64_ephemeris_image",
  "StartDateTime": "2026-01-13T20:00:00Z",
  "EndDateTime": "2026-01-14T04:00:00Z",
  "voyager_data": {}
}
```

**Response:**
```json
{
  "success": true
}
```

**Actions:**
- Updates target status to 'executing'
- Creates/updates RoboTargetSession
- Dispatches RoboTargetSessionStarted event
- Sends email/in-app notifications

---

### 2. Shot Running

**Endpoint:** `POST /api/voyager/events/shot-running`

**Triggered by:** `ShotRunning` Voyager event (every 1 second during exposure)

**Payload:**
```json
{
  "session_guid": "session-uuid",
  "File": "M42_L_001.fit",
  "Expo": 300,
  "Elapsed": 45.5,
  "ElapsedPerc": 15.16,
  "Status": 1
}
```

**Status Values:**
- `0` - Idle
- `1` - Exposure
- `2` - Download
- `3` - JPG Conversion

**Response:**
```json
{
  "success": true
}
```

**Actions:**
- Broadcasts to `robotarget.session.{id}` channel
- Updates real-time progress bar
- No database writes (performance)

---

### 3. Progress Update

**Endpoint:** `POST /api/voyager/events/progress`

**Triggered by:** `ControlData` Voyager event (every 2 seconds)

**Payload:**
```json
{
  "session_guid": "session-uuid",
  "progress": {
    "percentage": 45.5,
    "current_shot": 12,
    "total_shots": 30,
    "remaining": 18,
    "camera": {
      "status": "Exposing",
      "temp": -10.5,
      "cooling_power": 75
    },
    "mount": {
      "status": "Tracking",
      "ra": "05:35:17.23",
      "dec": "-05:23:28.1",
      "alt": 45.6,
      "az": 123.4,
      "time_to_flip": 7200
    }
  }
}
```

**Response:**
```json
{
  "success": true
}
```

**Actions:**
- Broadcasts to session channel
- Updates dashboard metrics
- Updates session progress in database (debounced)

---

### 4. Image Ready

**Endpoint:** `POST /api/voyager/events/image-ready`

**Triggered by:** `NewJPGReady` Voyager event

**Payload:**
```json
{
  "session_guid": "session-uuid",
  "image": {
    "filename": "M42_L_001.fit",
    "thumbnail": "base64_jpeg_string",
    "filter": "L",
    "exposure": 300,
    "hfd": 2.34,
    "star_index": 95,
    "timestamp": "2026-01-13T23:45:12Z"
  }
}
```

**Response:**
```json
{
  "success": true
}
```

**Actions:**
- Broadcasts to session channel
- Stores thumbnail in database/storage
- Updates session image counter
- Triggers quality alerts if HFD threshold exceeded

---

### 5. Session Completed

**Endpoint:** `POST /api/voyager/events/session-completed`

**Triggered by:** Session end from Voyager

**Payload:**
```json
{
  "session_guid": "session-uuid",
  "completion_data": {
    "result": 1,
    "result_text": "Success",
    "hfd_mean": 2.45,
    "hfd_stdev": 0.12,
    "images_captured": 30,
    "images_accepted": 28,
    "images_rejected": 2,
    "total_duration": 9000
  }
}
```

**Result Codes:**
- `1` - Success (RESULT_OK)
- `2` - Aborted (RESULT_ABORTED)
- `3` - Error (RESULT_ERROR)

**Response:**
```json
{
  "success": true
}
```

**Actions:**
- Updates session in database
- Updates target status to 'completed'
- Handles credits (capture or refund)
- Sends completion notification
- Broadcasts to session channel

---

## Error Handling

All endpoints return standard error format:

```json
{
  "success": false,
  "error": "Error message here"
}
```

**HTTP Status Codes:**
- `200` - Success
- `400` - Bad request (validation failed)
- `404` - Resource not found (session/target)
- `500` - Server error

## Rate Limiting

**Shot Running:** No rate limit (high frequency expected)

**Other Endpoints:** 100 requests/minute per IP

## Testing

Use curl to test webhooks:

```bash
# Test shot-running
curl -X POST http://stellar.test/api/voyager/events/shot-running \
  -H "Content-Type: application/json" \
  -H "X-API-Key: your-key-here" \
  -d @test-payloads/shot-running.json

# Test with invalid session
curl -X POST http://stellar.test/api/voyager/events/shot-running \
  -H "Content-Type: application/json" \
  -d '{"session_guid": "invalid-guid"}'
```

Expected: 404 error

## Proxy Configuration

Configure proxy to send these events:

```javascript
// Node.js proxy example
const LARAVEL_API = 'http://stellar.test/api/voyager/events';

// Map Voyager events to Laravel endpoints
const EVENT_MAP = {
  'RoboTargetRunningTargetEphemerisPNG': '/session-started',
  'ShotRunning': '/shot-running',
  'ControlData': '/progress',
  'NewJPGReady': '/image-ready',
  'SessionCompleted': '/session-completed'
};

// Forward event to Laravel
async function forwardToLaravel(voyagerEvent, payload) {
  const endpoint = EVENT_MAP[voyagerEvent];
  if (!endpoint) return;

  await fetch(LARAVEL_API + endpoint, {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'X-API-Key': process.env.PROXY_API_KEY
    },
    body: JSON.stringify(payload)
  });
}
```

## Monitoring

Check webhook health:

```bash
# View recent webhook logs
tail -f storage/logs/laravel.log | grep "Voyager event"

# Monitor broadcasting
php artisan horizon:status

# Check queue workers
php artisan queue:work --once --verbose
```
```

**Step 3: Commit documentation**

```bash
git add docs/monitoring-system.md docs/webhook-events.md
git commit -m "docs: add comprehensive monitoring system and webhook events documentation"
```

---

## Task 10: Final Testing & Polish

**Step 1: Create test data seeder (optional)**

```bash
# Create test session with sample data
php artisan tinker
```

```php
$target = RoboTarget::first();
$session = RoboTargetSession::create([
    'robo_target_id' => $target->id,
    'session_guid' => 'test-session-' . time(),
    'session_start' => now(),
]);
```

**Step 2: Manual testing checklist**

- [ ] WebSocket connects successfully
- [ ] Shot running updates progress bar smoothly
- [ ] Image preview displays correctly with HFD
- [ ] Progress circle animates
- [ ] Telescope status cards update
- [ ] Loading state shows/hides correctly
- [ ] API polling works when WebSocket unavailable
- [ ] Responsive design works on mobile
- [ ] Page accessible only to target owner

**Step 3: Performance testing**

Open browser DevTools:
- Network tab: Check WebSocket connection stable
- Performance tab: Record 30s session, check for memory leaks
- Console: No errors during normal operation

**Step 4: Final commit**

```bash
git add .
git commit -m "feat: complete real-time monitoring system with comprehensive documentation

- Add shot-running webhook endpoint
- Create live monitoring dashboard page
- Implement WebSocket real-time updates
- Add exposure progress component
- Add image preview with HFD metrics
- Add session progress sidebar
- Add API polling fallback
- Add comprehensive documentation"
```

---

## Summary

This plan implements a complete real-time monitoring system with:

✅ **Backend:**
- ShotRunning webhook endpoint
- Real-time broadcasting via WebSocket
- API polling fallback

✅ **Frontend:**
- Live monitoring dashboard page
- Current exposure progress with animated bar
- Last captured image preview with quality metrics
- Session progress circular indicator
- Telescope status cards
- Responsive Astral design

✅ **Documentation:**
- Architecture overview
- WebSocket event specifications
- Webhook API reference
- Testing procedures
- Troubleshooting guide

**Total Estimated Time:** 2-3 hours for experienced Laravel developer

**Files Modified:** 4
**Files Created:** 8
**Commits:** 10

---

Plan complete and saved to `docs/plans/2026-01-13-robotarget-live-monitoring.md`.

**Two execution options:**

**1. Subagent-Driven (this session)** - I dispatch fresh subagent per task, review between tasks, fast iteration

**2. Parallel Session (separate)** - Open new session with executing-plans, batch execution with checkpoints

**Which approach?**
