<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class RoboTargetShotService
{
    private string $proxyUrl;

    public function __construct()
    {
        $this->proxyUrl = config('services.voyager.proxy_url', 'http://localhost:3003');
    }

    /**
     * Get HTTP headers including API key if configured
     *
     * @return array
     */
    private function getHeaders(): array
    {
        $headers = [];
        if ($apiKey = config('services.voyager.proxy_api_key')) {
            $headers['X-API-Key'] = $apiKey;
        }
        return $headers;
    }

    /**
     * Récupérer la configuration complète du matériel (filtres, readout modes, etc.)
     * pour un profil spécifique ou tous les profils
     *
     * @param string|null $profileName Nom du profil (ex: "Default.v2y") ou null pour tous
     * @return array
     */
    public function getHardwareConfiguration(?string $profileName = null): array
    {
        try {
            Log::info('GetConfigDataShot Request Start', [
                'profileName' => $profileName,
                'proxyUrl' => $this->proxyUrl
            ]);

            $startTime = microtime(true);

            $response = Http::timeout(30)
                ->withHeaders($this->getHeaders())
                ->post("{$this->proxyUrl}/api/robotarget/test-mac", [
                'method' => 'RemoteRoboTargetGetConfigDataShot',
                'params' => [
                    'ProfileName' => $profileName ?? ''
                ],
                'macFormula' => [
                    'sep1' => '|| |',   // 1 espace
                    'sep2' => '||  |',  // 2 espaces
                    'sep3' => '|| |'    // 1 espace
                ]
            ]);

            $elapsed = round((microtime(true) - $startTime) * 1000);
            $result = $response->json();

            Log::info('GetConfigDataShot Response', [
                'profileName' => $profileName,
                'status' => $response->status(),
                'elapsed_ms' => $elapsed,
                'success' => $result['success'] ?? false
            ]);

            if ($result['success'] ?? false) {
                $profiles = $result['result']['ParamRet']['list'] ?? [];

                return [
                    'success' => true,
                    'profiles' => $profiles,
                    'count' => count($profiles),
                    'parsed' => $this->parseConfigurationData($profiles)
                ];
            }

            return [
                'success' => false,
                'error' => $result['error'] ?? 'Failed to get config data'
            ];

        } catch (\Exception $e) {
            Log::error('GetConfigDataShot Exception', [
                'profileName' => $profileName,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Récupérer uniquement la configuration des filtres (rétrocompatibilité)
     *
     * @return array
     */
    public function getFilterConfiguration(): array
    {
        $config = $this->getHardwareConfiguration();

        if (!$config['success']) {
            return $config;
        }

        // Extraire uniquement les filtres du profil actif
        $activeProfile = null;
        foreach ($config['profiles'] as $profile) {
            if ($profile['isactive'] ?? false) {
                $activeProfile = $profile;
                break;
            }
        }

        if (!$activeProfile) {
            // Si aucun profil actif, prendre le premier
            $activeProfile = $config['profiles'][0] ?? null;
        }

        if (!$activeProfile) {
            return [
                'success' => false,
                'error' => 'No profile configuration found'
            ];
        }

        // Parser les filtres
        $filters = $this->parseFilters($activeProfile['filters'] ?? []);

        return [
            'success' => true,
            'filters' => $filters,
            'profileName' => $activeProfile['name'] ?? 'Unknown',
            'sensorType' => $activeProfile['sensortype'] ?? 0,
            'isCmos' => $activeProfile['iscmos'] ?? false
        ];
    }

    /**
     * Parser les données de configuration complètes
     *
     * @param array $profiles
     * @return array
     */
    private function parseConfigurationData(array $profiles): array
    {
        $parsed = [
            'activeProfile' => null,
            'allProfiles' => []
        ];

        foreach ($profiles as $profile) {
            $profileData = [
                'guid' => $profile['guid'] ?? null,
                'name' => $profile['name'] ?? 'Unknown',
                'isActive' => $profile['isactive'] ?? false,
                'sensorType' => $this->getSensorTypeName($profile['sensortype'] ?? 0),
                'sensorTypeCode' => $profile['sensortype'] ?? 0,
                'isCmos' => $profile['iscmos'] ?? false,
                'filters' => $this->parseFilters($profile['filters'] ?? []),
                'readoutModes' => $this->parseReadoutModes($profile['readoutmode'] ?? []),
                'speeds' => $this->parseSpeeds($profile['speed'] ?? [])
            ];

            $parsed['allProfiles'][] = $profileData;

            if ($profileData['isActive']) {
                $parsed['activeProfile'] = $profileData;
            }
        }

        return $parsed;
    }

    /**
     * Parser les filtres depuis la configuration
     *
     * @param array $filterConfig
     * @return array
     */
    private function parseFilters(array $filterConfig): array
    {
        $filters = [];
        $filterNum = $filterConfig['FilterNum'] ?? 0;

        for ($i = 1; $i <= $filterNum; $i++) {
            $nameKey = "Filter{$i}_Name";
            $offsetKey = "Filter{$i}_Offset";
            $magMinKey = "Filter{$i}_MagMin";
            $magMaxKey = "Filter{$i}_MagMax";

            if (isset($filterConfig[$nameKey])) {
                $filters[$i - 1] = [
                    'index' => $i - 1,
                    'name' => $filterConfig[$nameKey],
                    'offset' => $filterConfig[$offsetKey] ?? 0,
                    'magMin' => $filterConfig[$magMinKey] ?? null,
                    'magMax' => $filterConfig[$magMaxKey] ?? null
                ];
            }
        }

        return $filters;
    }

    /**
     * Parser les modes de lecture
     *
     * @param array $readoutConfig
     * @return array
     */
    private function parseReadoutModes(array $readoutConfig): array
    {
        $modes = [];
        $readoutNum = $readoutConfig['ReadoutNum'] ?? 0;

        for ($i = 1; $i <= $readoutNum; $i++) {
            $nameKey = "Readout{$i}_Name";
            $indexKey = "Readout{$i}_Index";

            if (isset($readoutConfig[$nameKey])) {
                $modes[] = [
                    'name' => $readoutConfig[$nameKey],
                    'index' => $readoutConfig[$indexKey] ?? ($i - 1)
                ];
            }
        }

        return $modes;
    }

    /**
     * Parser les vitesses disponibles
     *
     * @param array $speedConfig
     * @return array
     */
    private function parseSpeeds(array $speedConfig): array
    {
        $speeds = [];
        $speedNum = $speedConfig['SpeedNum'] ?? 0;

        for ($i = 1; $i <= $speedNum; $i++) {
            $nameKey = "Speed{$i}_Name";
            $indexKey = "Speed{$i}_Index";

            if (isset($speedConfig[$nameKey])) {
                $speeds[] = [
                    'name' => $speedConfig[$nameKey],
                    'index' => $speedConfig[$indexKey] ?? ($i - 1)
                ];
            }
        }

        return $speeds;
    }

    /**
     * Obtenir le nom du type de capteur
     *
     * @param int $sensorType
     * @return string
     */
    private function getSensorTypeName(int $sensorType): string
    {
        return match($sensorType) {
            0 => 'Monochrome',
            1 => 'Color',
            2 => 'DSLR',
            default => 'Unknown'
        };
    }

    /**
     * Récupérer les Shots planifiés (configurations d'exposition) d'une Target
     *
     * @param string $targetGuid GUID de la Target
     * @return array
     */
    public function getPlannedShots(string $targetGuid): array
    {
        try {
            Log::info('GetShots Request Start', [
                'targetGuid' => $targetGuid,
                'proxyUrl' => $this->proxyUrl
            ]);

            $startTime = microtime(true);

            $response = Http::timeout(60)
                ->withHeaders($this->getHeaders())
                ->post("{$this->proxyUrl}/api/robotarget/test-mac", [
                'method' => 'RemoteRoboTargetGetShot',
                'params' => [
                    'RefGuidTarget' => $targetGuid
                ],
                'macFormula' => [
                    'sep1' => '||:||',
                    'sep2' => '||:||',
                    'sep3' => '||:||'
                ]
            ]);

            $elapsed = round((microtime(true) - $startTime) * 1000);
            $result = $response->json();

            Log::info('GetShots Response', [
                'targetGuid' => $targetGuid,
                'status' => $response->status(),
                'elapsed_ms' => $elapsed,
                'success' => $result['success'] ?? false
            ]);

            if ($result['success'] ?? false) {
                $shots = $result['result']['ParamRet']['list'] ?? [];

                return [
                    'success' => true,
                    'shots' => $shots,
                    'count' => count($shots),
                    'stats' => $this->calculateShotStats($shots)
                ];
            }

            return [
                'success' => false,
                'error' => $result['error'] ?? 'Unknown error',
                'shots' => []
            ];

        } catch (\Exception $e) {
            Log::error('GetShots Exception', [
                'targetGuid' => $targetGuid,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
                'shots' => []
            ];
        }
    }

    /**
     * Récupérer les images réellement capturées pour une Target
     * (Open API - utilise MD5 pour le MAC)
     *
     * @param string $targetGuid GUID de la Target
     * @param bool $isDeleted false = fichiers valides, true = fichiers écartés
     * @return array
     */
    public function getCapturedShots(string $targetGuid, bool $isDeleted = false): array
    {
        try {
            $response = Http::timeout(30)
                ->withHeaders($this->getHeaders())
                ->post("{$this->proxyUrl}/api/robotarget/open-api", [
                'method' => 'RemoteOpenRoboTargetGetShotDoneList',
                'params' => [
                    'RefGuidTarget' => $targetGuid,
                    'IsDeleted' => $isDeleted
                ]
            ]);

            $result = $response->json();

            if ($result['success'] ?? false) {
                $shots = $result['result']['ParamRet']['list'] ?? [];

                return [
                    'success' => true,
                    'shots' => $shots,
                    'count' => count($shots),
                    'stats' => $this->calculateCapturedShotStats($shots)
                ];
            }

            return [
                'success' => false,
                'error' => $result['error'] ?? 'Unknown error',
                'shots' => []
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'shots' => []
            ];
        }
    }

    /**
     * Récupérer tous les shots d'une Target (planifiés + capturés)
     *
     * @param string $targetGuid
     * @return array
     */
    public function getAllShotsForTarget(string $targetGuid): array
    {
        $planned = $this->getPlannedShots($targetGuid);
        $captured = $this->getCapturedShots($targetGuid);
        $filters = $this->getFilterConfiguration();

        return [
            'success' => $planned['success'] && $captured['success'] && $filters['success'],
            'planned' => [
                'shots' => $planned['shots'] ?? [],
                'count' => $planned['count'] ?? 0,
                'stats' => $planned['stats'] ?? []
            ],
            'captured' => [
                'shots' => $captured['shots'] ?? [],
                'count' => $captured['count'] ?? 0,
                'stats' => $captured['stats'] ?? []
            ],
            'filters' => $filters['filters'] ?? [],
            'errors' => array_filter([
                'planned' => $planned['error'] ?? null,
                'captured' => $captured['error'] ?? null,
                'filters' => $filters['error'] ?? null
            ])
        ];
    }

    /**
     * Calculer les statistiques pour les shots planifiés
     *
     * @param array $shots
     * @return array
     */
    private function calculateShotStats(array $shots): array
    {
        if (empty($shots)) {
            return [
                'totalPlanned' => 0,
                'totalCompleted' => 0,
                'progressPercent' => 0,
                'totalExposureTime' => 0,
                'completedExposureTime' => 0
            ];
        }

        $totalPlanned = 0;
        $totalCompleted = 0;
        $totalExposureTime = 0;
        $completedExposureTime = 0;

        foreach ($shots as $shot) {
            $planned = $shot['auxtotshot'] ?? $shot['num'] ?? 0;
            $completed = $shot['auxshotdone'] ?? 0;
            $exposure = $shot['exposure'] ?? 0;

            $totalPlanned += $planned;
            $totalCompleted += $completed;
            $totalExposureTime += ($planned * $exposure);
            $completedExposureTime += ($completed * $exposure);
        }

        return [
            'totalPlanned' => $totalPlanned,
            'totalCompleted' => $totalCompleted,
            'progressPercent' => $totalPlanned > 0 ? round(($totalCompleted / $totalPlanned) * 100, 1) : 0,
            'totalExposureTime' => $totalExposureTime,
            'completedExposureTime' => $completedExposureTime,
            'totalExposureFormatted' => $this->formatExposureTime($totalExposureTime),
            'completedExposureFormatted' => $this->formatExposureTime($completedExposureTime)
        ];
    }

    /**
     * Calculer les statistiques pour les shots capturés
     *
     * @param array $shots
     * @return array
     */
    private function calculateCapturedShotStats(array $shots): array
    {
        if (empty($shots)) {
            return [
                'total' => 0,
                'avgHfd' => 0,
                'avgStarIndex' => 0
            ];
        }

        $totalHfd = 0;
        $totalStarIndex = 0;

        foreach ($shots as $shot) {
            $totalHfd += $shot['hfd'] ?? 0;
            $totalStarIndex += $shot['starindex'] ?? 0;
        }

        return [
            'total' => count($shots),
            'avgHfd' => round($totalHfd / count($shots), 2),
            'avgStarIndex' => round($totalStarIndex / count($shots), 2)
        ];
    }

    /**
     * Formater un temps d'exposition en secondes vers un format lisible
     *
     * @param int $seconds
     * @return string
     */
    public function formatExposureTime(int $seconds): string
    {
        if ($seconds >= 3600) {
            $hours = floor($seconds / 3600);
            $minutes = floor(($seconds % 3600) / 60);
            $secs = $seconds % 60;

            $parts = [];
            if ($hours > 0) $parts[] = "{$hours}h";
            if ($minutes > 0) $parts[] = "{$minutes}m";
            if ($secs > 0) $parts[] = "{$secs}s";

            return implode(' ', $parts);
        } elseif ($seconds >= 60) {
            $minutes = floor($seconds / 60);
            $secs = $seconds % 60;
            return $secs > 0 ? "{$minutes}m {$secs}s" : "{$minutes}m";
        }

        return "{$seconds}s";
    }

    /**
     * Obtenir le nom d'un filtre à partir de son index
     *
     * @param int $filterIndex
     * @param array|null $filterConfig
     * @return string
     */
    public function getFilterName(int $filterIndex, ?array $filterConfig = null): string
    {
        if ($filterConfig === null) {
            $config = $this->getFilterConfiguration();
            $filterConfig = $config['filters'] ?? [];
        }

        // Si $filterConfig est un tableau associatif avec des données parsées
        if (isset($filterConfig[$filterIndex]['name'])) {
            return $filterConfig[$filterIndex]['name'];
        }

        // Si $filterConfig est un simple tableau index => name
        if (isset($filterConfig[$filterIndex]) && is_string($filterConfig[$filterIndex])) {
            return $filterConfig[$filterIndex];
        }

        return "Filter {$filterIndex}";
    }

    /**
     * Obtenir les détails complets d'un filtre
     *
     * @param int $filterIndex
     * @return array|null
     */
    public function getFilterDetails(int $filterIndex): ?array
    {
        $config = $this->getFilterConfiguration();
        $filters = $config['filters'] ?? [];

        return $filters[$filterIndex] ?? null;
    }

    /**
     * Obtenir tous les profils disponibles
     *
     * @return array
     */
    public function getAllProfiles(): array
    {
        $config = $this->getHardwareConfiguration();

        if (!$config['success']) {
            return [
                'success' => false,
                'error' => $config['error']
            ];
        }

        return [
            'success' => true,
            'profiles' => $config['parsed']['allProfiles'] ?? [],
            'activeProfile' => $config['parsed']['activeProfile'] ?? null
        ];
    }

    /**
     * Obtenir la configuration d'un profil spécifique
     *
     * @param string $profileName
     * @return array
     */
    public function getProfileConfiguration(string $profileName): array
    {
        $config = $this->getHardwareConfiguration($profileName);

        if (!$config['success']) {
            return [
                'success' => false,
                'error' => $config['error']
            ];
        }

        $profile = $config['profiles'][0] ?? null;

        if (!$profile) {
            return [
                'success' => false,
                'error' => 'Profile not found'
            ];
        }

        return [
            'success' => true,
            'profile' => $profile,
            'parsed' => [
                'filters' => $this->parseFilters($profile['filters'] ?? []),
                'readoutModes' => $this->parseReadoutModes($profile['readoutmode'] ?? []),
                'speeds' => $this->parseSpeeds($profile['speed'] ?? [])
            ]
        ];
    }
}
