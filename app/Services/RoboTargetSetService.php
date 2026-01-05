<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class RoboTargetSetService
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
     * Récupérer tous les Sets ou les Sets d'un profil spécifique
     *
     * @param string|null $profileName Nom du profil (vide = tous les profils)
     * @return array
     */
    public function getSets(?string $profileName = null): array
    {
        try {
            $response = Http::timeout(30)
                ->withHeaders($this->getHeaders())
                ->post("{$this->proxyUrl}/api/robotarget/test-mac", [
                'method' => 'RemoteRoboTargetGetSet',
                'params' => [
                    'ProfileName' => $profileName ?? ''
                ],
                'macFormula' => [
                    'sep1' => '||:||',
                    'sep2' => '||:||',
                    'sep3' => '||:||'
                ]
            ]);

            $result = $response->json();

            if ($result['success'] ?? false) {
                return [
                    'success' => true,
                    'sets' => $result['result']['ParamRet']['list'] ?? [],
                    'count' => count($result['result']['ParamRet']['list'] ?? []),
                    'macInfo' => $result['macInfo'] ?? null
                ];
            }

            return [
                'success' => false,
                'error' => $result['error'] ?? 'Unknown error',
                'sets' => []
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'sets' => []
            ];
        }
    }

    /**
     * Récupérer un Set spécifique par son GUID
     *
     * @param string $guid
     * @return array|null
     */
    public function getSetByGuid(string $guid): ?array
    {
        $result = $this->getSets();

        if (!$result['success']) {
            return null;
        }

        foreach ($result['sets'] as $set) {
            if ($set['guid'] === $guid) {
                return $set;
            }
        }

        return null;
    }

    /**
     * Récupérer les Sets d'un profil spécifique
     *
     * @param string $profileName
     * @return array
     */
    public function getSetsByProfile(string $profileName): array
    {
        $result = $this->getSets($profileName);

        return $result;
    }

    /**
     * Ajouter un nouveau Set
     *
     * @param array $data
     * @return array
     */
    public function addSet(array $data): array
    {
        try {
            $guid = $data['guid'] ?? Str::uuid()->toString();

            $response = Http::timeout(30)
                ->withHeaders($this->getHeaders())
                ->post("{$this->proxyUrl}/api/robotarget/test-mac", [
                'method' => 'RemoteRoboTargetAddSet',
                'params' => [
                    'Guid' => $guid,
                    'Name' => $data['name'],
                    'ProfileName' => $data['profile_name'],
                    'IsDefault' => $data['is_default'] ?? false,
                    'Status' => $data['status'] ?? 0,
                    'Tag' => $data['tag'] ?? '',
                    'Note' => $data['note'] ?? ''
                ],
                'macFormula' => [
                    'sep1' => '||:||',
                    'sep2' => '||:||',
                    'sep3' => '||:||'
                ]
            ]);

            $result = $response->json();

            if ($result['success'] ?? false) {
                return [
                    'success' => true,
                    'guid' => $guid,
                    'result' => $result['result']['ParamRet'] ?? null
                ];
            }

            return [
                'success' => false,
                'error' => $result['error'] ?? 'Failed to add set'
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Mettre à jour un Set existant
     *
     * @param string $guid
     * @param array $data
     * @return array
     */
    public function updateSet(string $guid, array $data): array
    {
        try {
            $response = Http::timeout(30)
                ->withHeaders($this->getHeaders())
                ->post("{$this->proxyUrl}/api/robotarget/test-mac", [
                'method' => 'RemoteRoboTargetUpdateSet',
                'params' => [
                    'RefGuidSet' => $guid,
                    'Name' => $data['name'],
                    'Status' => $data['status'] ?? 0,
                    'Tag' => $data['tag'] ?? '',
                    'Note' => $data['note'] ?? ''
                ],
                'macFormula' => [
                    'sep1' => '||:||',
                    'sep2' => '||:||',
                    'sep3' => '||:||'
                ]
            ]);

            $result = $response->json();

            if ($result['success'] ?? false) {
                return [
                    'success' => true,
                    'result' => $result['result']['ParamRet'] ?? null
                ];
            }

            return [
                'success' => false,
                'error' => $result['error'] ?? 'Failed to update set'
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Supprimer un Set
     *
     * @param string $guid
     * @return array
     */
    public function deleteSet(string $guid): array
    {
        try {
            $response = Http::timeout(30)
                ->withHeaders($this->getHeaders())
                ->post("{$this->proxyUrl}/api/robotarget/test-mac", [
                'method' => 'RemoteRoboTargetRemoveSet',
                'params' => [
                    'RefGuidSet' => $guid
                ],
                'macFormula' => [
                    'sep1' => '||:||',
                    'sep2' => '||:||',
                    'sep3' => '||:||'
                ]
            ]);

            $result = $response->json();

            if ($result['success'] ?? false) {
                return [
                    'success' => true,
                    'result' => $result['result']['ParamRet'] ?? null
                ];
            }

            return [
                'success' => false,
                'error' => $result['error'] ?? 'Failed to delete set'
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Activer/Désactiver un Set
     *
     * @param string $guid
     * @param bool $enable
     * @return array
     */
    public function toggleSetStatus(string $guid, bool $enable): array
    {
        try {
            $response = Http::timeout(30)
                ->withHeaders($this->getHeaders())
                ->post("{$this->proxyUrl}/api/robotarget/test-mac", [
                'method' => 'RemoteRoboTargetEnableDisableObject',
                'params' => [
                    'RefGuidObject' => $guid,
                    'ObjectType' => 2, // 2 = Set
                    'OperationType' => $enable ? 0 : 1 // 0 = Enable, 1 = Disable
                ],
                'macFormula' => [
                    'sep1' => '||:||',
                    'sep2' => '||:||',
                    'sep3' => '||:||'
                ]
            ]);

            $result = $response->json();

            if ($result['success'] ?? false) {
                return [
                    'success' => true,
                    'enabled' => $enable,
                    'result' => $result['result']['ParamRet'] ?? null
                ];
            }

            return [
                'success' => false,
                'error' => $result['error'] ?? 'Failed to toggle set status'
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Récupérer les Base Sequences (templates .s2q) disponibles
     *
     * @param string|null $profileName Nom du profil (ex: "Default.v2y") ou null pour tous
     * @return array
     */
    public function getBaseSequences(?string $profileName = null): array
    {
        try {
            \Log::info('GetBaseSequence Request Start', [
                'profileName' => $profileName,
                'proxyUrl' => $this->proxyUrl
            ]);

            $startTime = microtime(true);

            // Augmenter le timeout à 120 secondes (GetBaseSequence peut être très lent lors du scan de tous les profils)
            $response = Http::timeout(120)
                ->withHeaders($this->getHeaders())
                ->post("{$this->proxyUrl}/api/robotarget/test-mac", [
                'method' => 'RemoteRoboTargetGetBaseSequence',
                'params' => [
                    'ProfileName' => $profileName ?? ''
                ],
                'macFormula' => [
                    'sep1' => '||:||',   // Formule colon (comme GetSet)
                    'sep2' => '||:||',
                    'sep3' => '||:||'
                ]
            ]);

            $elapsed = round((microtime(true) - $startTime) * 1000);
            $result = $response->json();

            \Log::info('GetBaseSequence Response', [
                'profileName' => $profileName,
                'status' => $response->status(),
                'elapsed_ms' => $elapsed,
                'success' => $result['success'] ?? false
            ]);

            if ($result['success'] ?? false) {
                $sequences = $result['result']['ParamRet']['list'] ?? [];

                return [
                    'success' => true,
                    'sequences' => $sequences,
                    'count' => count($sequences),
                    'byProfile' => $this->groupSequencesByProfile($sequences)
                ];
            }

            return [
                'success' => false,
                'error' => $result['error'] ?? 'Unknown error',
                'sequences' => []
            ];

        } catch (\Exception $e) {
            \Log::error('GetBaseSequence Exception', [
                'profileName' => $profileName,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
                'sequences' => []
            ];
        }
    }

    /**
     * Grouper les séquences par profil
     *
     * @param array $sequences
     * @return array
     */
    private function groupSequencesByProfile(array $sequences): array
    {
        $grouped = [];

        foreach ($sequences as $sequence) {
            $profileName = $sequence['profilename'] ?? 'Unknown';

            if (!isset($grouped[$profileName])) {
                $grouped[$profileName] = [
                    'profileName' => $profileName,
                    'sequences' => [],
                    'defaultSequence' => null
                ];
            }

            $grouped[$profileName]['sequences'][] = $sequence;

            if ($sequence['isdefault'] ?? false) {
                $grouped[$profileName]['defaultSequence'] = $sequence;
            }
        }

        return $grouped;
    }

    /**
     * Récupérer les informations de connexion Voyager
     *
     * @return array
     */
    public function getConnectionStatus(): array
    {
        try {
            $response = Http::timeout(5)
                ->withHeaders($this->getHeaders())
                ->get("{$this->proxyUrl}/api/dashboard/state");

            $data = $response->json();

            // Si json() retourne null, retourner un array d'erreur
            if ($data === null) {
                return [
                    'success' => false,
                    'error' => 'Invalid response from proxy',
                    'status_code' => $response->status()
                ];
            }

            return $data;
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Récupérer les Targets d'un Set spécifique ou de tous les Sets
     *
     * @param string|null $setGuid GUID du Set (null ou vide = toutes les targets)
     * @return array
     */
    public function getTargets(?string $setGuid = null): array
    {
        try {
            \Log::info('GetTarget Request Start', [
                'setGuid' => $setGuid,
                'proxyUrl' => $this->proxyUrl
            ]);

            $startTime = microtime(true);

            $response = Http::timeout(60)
                ->withHeaders($this->getHeaders())
                ->post("{$this->proxyUrl}/api/robotarget/test-mac", [
                'method' => 'RemoteRoboTargetGetTarget',
                'params' => [
                    'RefGuidSet' => $setGuid ?? ''
                ],
                'macFormula' => [
                    'sep1' => '||:||',
                    'sep2' => '||:||',
                    'sep3' => '||:||'
                ]
            ]);

            $elapsed = round((microtime(true) - $startTime) * 1000);
            $result = $response->json();

            \Log::info('GetTarget Response', [
                'setGuid' => $setGuid,
                'status' => $response->status(),
                'elapsed_ms' => $elapsed,
                'success' => $result['success'] ?? false
            ]);

            if ($result['success'] ?? false) {
                $targets = $result['result']['ParamRet']['list'] ?? [];

                return [
                    'success' => true,
                    'targets' => $targets,
                    'count' => count($targets),
                    'bySet' => $this->groupTargetsBySet($targets)
                ];
            }

            return [
                'success' => false,
                'error' => $result['error'] ?? 'Unknown error',
                'targets' => []
            ];

        } catch (\Exception $e) {
            \Log::error('GetTarget Exception', [
                'setGuid' => $setGuid,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
                'targets' => []
            ];
        }
    }

    /**
     * Grouper les targets par Set
     *
     * @param array $targets
     * @return array
     */
    private function groupTargetsBySet(array $targets): array
    {
        $grouped = [];

        foreach ($targets as $target) {
            $setGuid = $target['refguidset'] ?? 'unknown';

            if (!isset($grouped[$setGuid])) {
                $grouped[$setGuid] = [
                    'setGuid' => $setGuid,
                    'targets' => [],
                    'count' => 0
                ];
            }

            $grouped[$setGuid]['targets'][] = $target;
            $grouped[$setGuid]['count']++;
        }

        return $grouped;
    }

    /**
     * Récupérer la configuration des filtres (mapping filterindex -> nom)
     * À appeler AVANT de récupérer les shots pour interpréter les filterindex
     *
     * @return array
     */
    public function getConfigDataShot(): array
    {
        try {
            $response = Http::timeout(30)
                ->withHeaders($this->getHeaders())
                ->post("{$this->proxyUrl}/api/robotarget/test-mac", [
                'method' => 'RemoteRoboTargetGetConfigDataShot',
                'params' => [],
                'macFormula' => [
                    'sep1' => '|| |',  // 1 espace
                    'sep2' => '||  |', // 2 espaces
                    'sep3' => '|| |'   // 1 espace
                ]
            ]);

            $result = $response->json();

            if ($result['success'] ?? false) {
                $config = $result['result']['ParamRet'] ?? [];

                // Extraire la liste des filtres
                $filters = [];
                if (isset($config['filterwheellist'])) {
                    foreach ($config['filterwheellist'] as $index => $filterName) {
                        $filters[$index] = $filterName;
                    }
                }

                return [
                    'success' => true,
                    'filters' => $filters,
                    'binning' => $config['binning'] ?? [],
                    'config' => $config
                ];
            }

            return [
                'success' => false,
                'error' => $result['error'] ?? 'Failed to get config data'
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Récupérer les Shots planifiés (configurations d'exposition) d'une Target
     *
     * @param string $targetGuid GUID de la Target
     * @return array
     */
    public function getShots(string $targetGuid): array
    {
        try {
            \Log::info('GetShots Request Start', [
                'targetGuid' => $targetGuid,
                'proxyUrl' => $this->proxyUrl
            ]);

            $startTime = microtime(true);

            // Augmenter le timeout à 60 secondes
            $response = Http::timeout(60)
                ->withHeaders($this->getHeaders())
                ->post("{$this->proxyUrl}/api/robotarget/test-mac", [
                'method' => 'RemoteRoboTargetGetShot',
                'params' => [
                    'RefGuidTarget' => $targetGuid
                ],
                'macFormula' => [
                    'sep1' => '||:||',  // Même formule que GetSet/GetTarget
                    'sep2' => '||:||',
                    'sep3' => '||:||'
                ]
            ]);

            $elapsed = round((microtime(true) - $startTime) * 1000);
            $result = $response->json();

            // Log pour debug
            \Log::info('GetShots Response', [
                'targetGuid' => $targetGuid,
                'status' => $response->status(),
                'elapsed_ms' => $elapsed,
                'result' => $result
            ]);

            if ($result['success'] ?? false) {
                return [
                    'success' => true,
                    'shots' => $result['result']['ParamRet']['list'] ?? [],
                    'count' => count($result['result']['ParamRet']['list'] ?? []),
                    'debug' => $result // Pour debug
                ];
            }

            return [
                'success' => false,
                'error' => $result['error'] ?? 'Unknown error',
                'shots' => [],
                'debug' => $result // Pour debug
            ];

        } catch (\Exception $e) {
            \Log::error('GetShots Exception', [
                'targetGuid' => $targetGuid,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
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
    public function getShotDoneList(string $targetGuid, bool $isDeleted = false): array
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

                // Enrichir avec des statistiques
                $stats = [
                    'total' => count($shots),
                    'avgHfd' => 0,
                    'avgStarIndex' => 0
                ];

                if (count($shots) > 0) {
                    $totalHfd = 0;
                    $totalStarIndex = 0;
                    foreach ($shots as $shot) {
                        $totalHfd += $shot['hfd'] ?? 0;
                        $totalStarIndex += $shot['starindex'] ?? 0;
                    }
                    $stats['avgHfd'] = round($totalHfd / count($shots), 2);
                    $stats['avgStarIndex'] = round($totalStarIndex / count($shots), 2);
                }

                return [
                    'success' => true,
                    'shots' => $shots,
                    'stats' => $stats,
                    'count' => count($shots)
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
}
