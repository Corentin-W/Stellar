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
}
