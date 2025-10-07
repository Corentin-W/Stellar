<?php

namespace App\Services;

use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class VoyagerService
{
    private string $baseUrl;
    private int $timeout;

    public function __construct(?string $baseUrl = null, ?int $timeout = null)
    {
        $this->baseUrl = rtrim($baseUrl ?? config('services.voyager.proxy_url', ''), '/');
        $this->timeout = $timeout ?? 20;
    }

    /**
     * Retourne un instantané du statut matériel.
     *
     * @param  array<string, mixed>  $context
     * @return array<string, mixed>
     */
    public function getControlOverview(array $context = []): array
    {
        $query = array_filter([
            'setGuid' => $context['set_guid'] ?? null,
            'targetGuid' => $context['target_guid'] ?? null,
        ]);

        $response = $this->performRequest('get', '/api/dashboard/state', $query);

        if ($response['successful'] && $response['source'] === 'proxy') {
            return [
                'source' => 'proxy',
                'timestamp' => $response['timestamp'],
                'data' => $response['payload'],
            ];
        }

        return [
            'source' => 'mock',
            'timestamp' => now()->toIso8601String(),
            'data' => $this->makeMockControlOverview($context),
        ];
    }

    /**
     * Envoie une commande d'arrêt de la cible courante.
     *
     * @return array<string, mixed>
     */
    public function abortTarget(?string $targetGuid = null, ?string $setGuid = null): array
    {
        $response = $this->performRequest('post', '/api/control/abort', [
            'targetGuid' => $targetGuid,
            'setGuid' => $setGuid,
        ]);

        if ($response['successful']) {
            return [
                'successful' => true,
                'source' => $response['source'],
                'payload' => $response['payload'],
                'timestamp' => $response['timestamp'],
                'message' => 'Commande d\'arrêt envoyée',
            ];
        }

        return [
            'successful' => true,
            'source' => 'mock',
            'payload' => [
                'result' => 0,
                'message' => 'Commande simulée: arrêt du target demandé.',
            ],
            'timestamp' => now()->toIso8601String(),
            'message' => 'Proxy indisponible, exécution simulée.',
        ];
    }

    /**
     * Active ou désactive un objet RoboTarget.
     *
     * @param  array{object_guid:string|null, object_type:int, operation:int}  $payload
     * @return array<string, mixed>
     */
    public function toggleObject(array $payload): array
    {
        $response = $this->performRequest('post', '/api/control/toggle', [
            'objectGuid' => $payload['object_guid'] ?? null,
            'objectType' => $payload['object_type'],
            'operation' => $payload['operation'],
        ]);

        if ($response['successful']) {
            return [
                'successful' => true,
                'source' => $response['source'],
                'payload' => $response['payload'],
                'timestamp' => $response['timestamp'],
                'message' => 'Commande envoyée',
            ];
        }

        $operationLabel = $payload['operation'] === 0 ? 'activation' : 'désactivation';

        return [
            'successful' => true,
            'source' => 'mock',
            'payload' => [
                'result' => 0,
                'message' => sprintf('Commande simulée: %s effectuée.', $operationLabel),
            ],
            'timestamp' => now()->toIso8601String(),
            'message' => 'Proxy indisponible, exécution simulée.',
        ];
    }

    /**
     * Retourne une prévisualisation caméra (Base64).
     *
     * @param  array<string, mixed>  $context
     * @return array<string, mixed>
     */
    public function getCameraPreview(array $context = []): array
    {
        $query = array_filter([
            'targetGuid' => $context['target_guid'] ?? null,
            'setGuid' => $context['set_guid'] ?? null,
        ]);

        $response = $this->performRequest('get', '/api/camera/preview', $query);

        if ($response['successful'] && $response['source'] === 'proxy') {
            $payload = (array) ($response['payload'] ?? []);

            return [
                'source' => 'proxy',
                'image' => $payload['image'] ?? null,
                'timestamp' => $payload['timestamp'] ?? $response['timestamp'],
                'meta' => $payload['meta'] ?? [],
            ];
        }

        return [
            'source' => 'mock',
            'image' => $this->makeMockPreviewImage($context),
            'timestamp' => now()->toIso8601String(),
            'meta' => [
                'message' => 'Flux caméra simulé (proxy hors ligne).',
            ],
        ];
    }

    /**
     * @param  array<string, mixed>  $context
     * @return array<string, mixed>
     */
    private function makeMockControlOverview(array $context = []): array
    {
        $state = $context['state'] ?? 'upcoming';
        $secondsToEnd = Arr::get($context, 'seconds_to_end', 0);
        $secondsToStart = Arr::get($context, 'seconds_to_start', 0);
        $progress = $state === 'active' && $secondsToEnd > 0
            ? max(0, 100 - intval(($secondsToEnd / max(1, ($secondsToEnd + $secondsToStart))) * 100))
            : ($state === 'active' ? 40 : 0);

        $now = now();

        return [
            'mode' => 'demo',
            'message' => 'Voyager proxy indisponible, données simulées.',
            'voyager_status' => $state === 'active' ? 'RUN' : 'IDLE',
            'target_name' => $context['target_name'] ?? 'Session de démonstration',
            'target_guid' => $context['target_guid'] ?? 'mock-target-guid',
            'set_guid' => $context['set_guid'] ?? 'mock-set-guid',
            'target_enabled' => true,
            'updated_at' => $now->toIso8601String(),
            'equipment' => [
                [
                    'key' => 'mount',
                    'label' => 'Monture',
                    'connected' => true,
                    'status' => $state === 'active' ? 'Suivi actif' : 'Parquée',
                    'detail' => $state === 'active' ? 'Tracking ON' : 'Ready',
                ],
                [
                    'key' => 'camera',
                    'label' => 'Caméra',
                    'connected' => true,
                    'status' => 'Refroidissement stable',
                    'detail' => '-10°C',
                ],
                [
                    'key' => 'focuser',
                    'label' => 'Focuser',
                    'connected' => true,
                    'status' => 'Position nominale',
                    'detail' => '52340',
                ],
            ],
            'sequence' => [
                'progress' => $progress,
                'shots_total' => 12,
                'shots_done' => $state === 'active' ? max(1, intval(12 * ($progress / 100))) : 0,
                'integration_minutes' => $state === 'active' ? 24 : 0,
            ],
            'exposure' => $state === 'active'
                ? [
                    'filter' => 'L',
                    'elapsed' => 120,
                    'remaining' => 180,
                    'progress' => 40,
                ]
                : null,
            'warnings' => $state === 'upcoming'
                ? ['La fenêtre d’accès est ouverte, la séquence démarrera à l’heure prévue.']
                : [],
        ];
    }

    /**
     * @param  array<string, mixed>  $context
     */
    private function makeMockPreviewImage(array $context = []): string
    {
        $target = htmlspecialchars($context['target_name'] ?? 'Session de démonstration', ENT_QUOTES, 'UTF-8');
        $state = htmlspecialchars(strtoupper($context['state'] ?? 'IDLE'), ENT_QUOTES, 'UTF-8');
        $timestamp = now()->format('H:i:s');

        $svg = <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" width="800" height="450" viewBox="0 0 800 450">
    <defs>
        <linearGradient id="bg" x1="0%" y1="0%" x2="100%" y2="100%">
            <stop offset="0%" stop-color="#0f172a"/>
            <stop offset="100%" stop-color="#1e293b"/>
        </linearGradient>
    </defs>
    <rect width="800" height="450" fill="url(#bg)"/>
    <g fill="#334155">
        <circle cx="130" cy="120" r="60" opacity="0.4"/>
        <circle cx="380" cy="80" r="40" opacity="0.2"/>
        <circle cx="640" cy="140" r="70" opacity="0.25"/>
        <circle cx="540" cy="360" r="50" opacity="0.3"/>
        <circle cx="220" cy="320" r="55" opacity="0.18"/>
    </g>
    <text x="50%" y="45%" text-anchor="middle" fill="#e2e8f0" font-family="Arial, Helvetica, sans-serif" font-size="32">
        Aperçu caméra indisponible
    </text>
    <text x="50%" y="58%" text-anchor="middle" fill="#94a3b8" font-family="Arial, Helvetica, sans-serif" font-size="20">
        $target • État: $state
    </text>
    <text x="50%" y="70%" text-anchor="middle" fill="#64748b" font-family="Arial, Helvetica, sans-serif" font-size="16">
        Obtenu le $timestamp — mode démo activé
    </text>
</svg>
SVG;

        return 'data:image/svg+xml;base64,' . base64_encode($svg);
    }

    /**
     * @param  'get'|'post'  $method
     * @param  array<string, mixed>  $payload
     * @return array{
     *     successful: bool,
     *     source: string,
     *     payload: mixed,
     *     timestamp: string,
     *     status?: int,
     *     error?: string
     * }
     */
    private function performRequest(string $method, string $uri, array $payload = []): array
    {
        $timestamp = now()->toIso8601String();

        if ($this->baseUrl === '') {
            return [
                'successful' => false,
                'source' => 'mock',
                'payload' => null,
                'timestamp' => $timestamp,
                'error' => 'Proxy URL non configurée.',
            ];
        }

        try {
            $url = $this->baseUrl . $uri;

            if ($method === 'get') {
                $response = Http::timeout($this->timeout)
                    ->acceptJson()
                    ->get($url, $payload);
            } else {
                $response = Http::timeout($this->timeout)
                    ->acceptJson()
                    ->post($url, $payload);
            }

            return [
                'successful' => $response->successful(),
                'source' => 'proxy',
                'payload' => $response->json(),
                'timestamp' => Carbon::now()->toIso8601String(),
                'status' => $response->status(),
            ];
        } catch (\Throwable $exception) {
            Log::warning('Voyager proxy request failed', [
                'uri' => $uri,
                'method' => $method,
                'error' => $exception->getMessage(),
            ]);

            return [
                'successful' => false,
                'source' => 'mock',
                'payload' => null,
                'timestamp' => $timestamp,
                'error' => $exception->getMessage(),
            ];
        }
    }
}
