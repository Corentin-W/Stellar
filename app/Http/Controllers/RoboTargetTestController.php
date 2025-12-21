<?php

namespace App\Http\Controllers;

use App\Services\VoyagerService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class RoboTargetTestController extends Controller
{
    private VoyagerService $voyager;

    public function __construct(VoyagerService $voyager)
    {
        $this->voyager = $voyager;
    }

    public function index()
    {
        return view('test.robotarget');
    }

    /**
     * Convert RA from HH:MM:SS format to decimal hours
     * Example: "05:35:17" -> 5.5881
     */
    private function raToDecimal(string $ra): float
    {
        // Handle already decimal values
        if (is_numeric($ra)) {
            return (float) $ra;
        }

        // Parse HH:MM:SS format
        $parts = explode(':', $ra);
        if (count($parts) !== 3) {
            throw new \InvalidArgumentException("Invalid RA format. Expected HH:MM:SS, got: {$ra}");
        }

        [$hours, $minutes, $seconds] = array_map('floatval', $parts);
        return $hours + ($minutes / 60) + ($seconds / 3600);
    }

    /**
     * Convert DEC from ±DD:MM:SS format to decimal degrees
     * Example: "-05:23:28" -> -5.3911
     */
    private function decToDecimal(string $dec): float
    {
        // Handle already decimal values
        if (is_numeric($dec)) {
            return (float) $dec;
        }

        // Parse ±DD:MM:SS format
        $sign = 1;
        if (str_starts_with($dec, '-')) {
            $sign = -1;
            $dec = substr($dec, 1);
        } elseif (str_starts_with($dec, '+')) {
            $dec = substr($dec, 1);
        }

        $parts = explode(':', $dec);
        if (count($parts) !== 3) {
            throw new \InvalidArgumentException("Invalid DEC format. Expected ±DD:MM:SS, got: {$dec}");
        }

        [$degrees, $minutes, $seconds] = array_map('floatval', $parts);
        return $sign * ($degrees + ($minutes / 60) + ($seconds / 3600));
    }

    public function createSet(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $setGuid = (string) Str::uuid();
        $result = $this->voyager->addSet([
            'Guid' => $setGuid,
            'Name' => $request->name,
            'ProfileName' => 'Default.v2y',
            'IsDefault' => false,
            // Tag field removed - not supported by Voyager RoboTarget API
            'Status' => 0,
            'Note' => $request->input('note', ''),
        ]);

        return response()->json([
            'success' => true,
            'guid' => $setGuid,
            'result' => $result,
        ]);
    }

    public function listSets()
    {
        $result = $this->voyager->listSets();

        return response()->json([
            'success' => true,
            'sets' => $result,
        ]);
    }

    public function createTarget(Request $request)
    {
        $request->validate([
            'set_guid' => 'required|string',
            'name' => 'required|string|max:255',
            'ra' => 'required|string',
            'dec' => 'required|string',
        ]);

        $targetGuid = (string) Str::uuid();

        // Convert coordinates from HH:MM:SS and ±DD:MM:SS to decimal
        $raDecimal = $this->raToDecimal($request->ra);
        $decDecimal = $this->decToDecimal($request->dec);

        // Validate base sequence GUID
        $baseSequenceGuid = $request->input('base_sequence_guid');
        if (empty($baseSequenceGuid)) {
            $baseSequenceGuid = '00000000-0000-0000-0000-000000000000';
            logger()->warning('Using placeholder base_sequence_guid. Create a real sequence template in Voyager.');
        }

        // Generate C_Mask based on actual constraints provided
        $cMask = 'B';  // AltMin is always required
        if ($request->has('ha_start') && $request->has('ha_end')) {
            $cMask .= 'DE';
        }

        // Build target parameters
        $targetParams = [
            'GuidTarget' => $targetGuid,
            'RefGuidSet' => $request->set_guid,
            'RefGuidBaseSequence' => $baseSequenceGuid,
            'TargetName' => $request->name,
            'RAJ2000' => $raDecimal,
            'DECJ2000' => $decDecimal,
            'PA' => (float) ($request->pa ?? 0),
            'Status' => 0,
            'Priority' => 2,
            'IsRepeat' => true,
            'Repeat' => 1,
            'C_Mask' => $cMask,
            'C_AltMin' => (float) ($request->alt_min ?? 30),
        ];

        // Add optional constraints only if they are provided
        if ($request->has('ha_start')) {
            $targetParams['C_HAStart'] = (float) $request->ha_start;
        }
        if ($request->has('ha_end')) {
            $targetParams['C_HAEnd'] = (float) $request->ha_end;
        }

        $result = $this->voyager->addTarget($targetParams);

        return response()->json([
            'success' => true,
            'guid' => $targetGuid,
            'result' => $result,
        ]);
    }

    public function listTargets(Request $request)
    {
        $setGuid = $request->query('set_guid');

        if (!$setGuid) {
            return response()->json(['error' => 'set_guid required'], 400);
        }

        $result = $this->voyager->listTargetsForSet($setGuid);

        return response()->json([
            'success' => true,
            'targets' => $result,
        ]);
    }

    public function createShot(Request $request)
    {
        $request->validate([
            'target_guid' => 'required|string',
            'filter' => 'required|string',
            'exposure' => 'required|numeric|min:0.1',
            'quantity' => 'required|integer|min:1',
        ]);

        $shotGuid = (string) Str::uuid();

        $filterMap = [
            'L' => 0, 'R' => 1, 'G' => 2, 'B' => 3,
            'Ha' => 4, 'OIII' => 5, 'SII' => 6,
        ];

        $result = $this->voyager->addShot([
            'GuidShot' => $shotGuid,
            'RefGuidTarget' => $request->target_guid,
            'FilterIndex' => $filterMap[$request->filter] ?? 0,
            'Num' => (int) $request->quantity,
            'Bin' => (int) ($request->binning ?? 1),
            'ReadoutMode' => 0,
            'Type' => 0, // LIGHT
            'Speed' => 0,
            'Gain' => (int) ($request->gain ?? 100),
            'Offset' => (int) ($request->offset ?? 10),
            'Exposure' => (float) $request->exposure,
            'Order' => 1,
            'Enabled' => true,
        ]);

        return response()->json([
            'success' => true,
            'guid' => $shotGuid,
            'result' => $result,
        ]);
    }

    public function activateTarget(Request $request, string $guid)
    {
        $result = $this->voyager->activateTarget($guid);

        return response()->json([
            'success' => true,
            'result' => $result,
        ]);
    }

    public function deactivateTarget(Request $request, string $guid)
    {
        $result = $this->voyager->deactivateTarget($guid);

        return response()->json([
            'success' => true,
            'result' => $result,
        ]);
    }

    /**
     * Create a complete target with set and shots in one call (for testing)
     */
    public function createComplete(Request $request)
    {
        $request->validate([
            'target_name' => 'required|string|max:255',
            'ra_j2000' => 'required|string',
            'dec_j2000' => 'required|string',
            'shots' => 'required|array',
            'shots.*.filter_name' => 'required|string',
            'shots.*.exposure' => 'required|numeric|min:0.1',
            'shots.*.num' => 'required|integer|min:1',
        ]);

        try {
            // 1. Create Set
            $setGuid = $request->input('guid_set', (string) Str::uuid());
            $setResult = $this->voyager->addSet([
                'Guid' => $setGuid,
                'Name' => 'Test Set - ' . $request->target_name,
                'ProfileName' => 'Default.v2y',
                'IsDefault' => 0, // Convert boolean to int (Voyager expects 0/1, not true/false)
                'Status' => 0,
                'Note' => 'Created via Stellar test interface',
            ]);

            // 2. Create Target
            $targetGuid = $request->input('guid_target', (string) Str::uuid());

            // Convert coordinates from HH:MM:SS and ±DD:MM:SS to decimal
            $raDecimal = $this->raToDecimal($request->ra_j2000);
            $decDecimal = $this->decToDecimal($request->dec_j2000);

            // CRITICAL: RefGuidBaseSequence MUST reference an existing sequence template in Voyager
            // According to the documentation (docs/robotarget/createtarget.md:23), this field cannot be empty.
            // For testing purposes, we'll use a default GUID, but you should replace this with an actual
            // sequence template GUID from Voyager.
            $baseSequenceGuid = $request->input('base_sequence_guid');

            if (empty($baseSequenceGuid)) {
                // Use a placeholder GUID - this will likely fail until you create a real sequence in Voyager
                // TODO: Create a base sequence template in Voyager and use its GUID here
                $baseSequenceGuid = '00000000-0000-0000-0000-000000000000';
                logger()->warning('Using placeholder base_sequence_guid. Create a real sequence template in Voyager.');
            }

            // Generate C_Mask based on actual constraints provided
            $cMask = 'B';  // AltMin is always required
            if ($request->has('c_ha_start') && $request->has('c_ha_end')) {
                $cMask .= 'DE';
            }
            if ($request->input('c_moon_down', false)) {
                $cMask .= 'K';
            }

            // Build target parameters
            $targetParams = [
                'GuidTarget' => $targetGuid,
                'RefGuidSet' => $setGuid,
                'RefGuidBaseSequence' => $baseSequenceGuid,
                'TargetName' => $request->target_name,
                'RAJ2000' => $raDecimal,
                'DECJ2000' => $decDecimal,
                'PA' => (float) ($request->input('pa', 0)),
                'Status' => 0,
                'Priority' => $request->input('priority', 0),
                'IsRepeat' => true,
                'Repeat' => 1,
                'C_Mask' => $cMask,
                'C_AltMin' => (float) $request->input('c_alt_min', 30),
            ];

            // Add optional constraints only if they are provided
            if ($request->has('c_ha_start')) {
                $targetParams['C_HAStart'] = (float) $request->c_ha_start;
            }
            if ($request->has('c_ha_end')) {
                $targetParams['C_HAEnd'] = (float) $request->c_ha_end;
            }
            if ($request->input('c_moon_down', false)) {
                $targetParams['C_MoonDown'] = true;
            }

            $targetResult = $this->voyager->addTarget($targetParams);

            // 3. Create Shots
            $shotResults = [];
            foreach ($request->shots as $shot) {
                $shotGuid = (string) Str::uuid();
                $shotResult = $this->voyager->addShot([
                    'GuidShot' => $shotGuid,
                    'RefGuidTarget' => $targetGuid,
                    'FilterIndex' => $shot['filter_index'] ?? 0,
                    'Num' => (int) $shot['num'],
                    'Bin' => (int) ($shot['bin'] ?? 1),
                    'ReadoutMode' => 0,
                    'Type' => 0, // LIGHT
                    'Speed' => 0,
                    'Gain' => (int) ($shot['gain'] ?? 100),
                    'Offset' => (int) ($shot['offset'] ?? 10),
                    'Exposure' => (float) $shot['exposure'],
                    'Order' => 1,
                    'Enabled' => true,
                ]);
                $shotResults[] = ['guid' => $shotGuid, 'result' => $shotResult];
            }

            // 4. Optionally activate the target
            if ($request->input('activate', false)) {
                $this->voyager->activateTarget($targetGuid);
            }

            return response()->json([
                'success' => true,
                'message' => 'Target created successfully',
                'target' => [
                    'guid' => $targetGuid,
                    'set_guid' => $setGuid,
                    'name' => $request->target_name,
                    'status' => 'pending',
                    'shots_count' => count($shotResults),
                ],
                'details' => [
                    'set' => $setResult,
                    'target' => $targetResult,
                    'shots' => $shotResults,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create target: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function diagnostics()
    {
        $diagnostics = [
            'timestamp' => now()->toIso8601String(),
            'tests' => [],
        ];

        // Test 1: Connexion au Proxy Node.js
        $proxyUrl = config('services.voyager.proxy_url', 'http://localhost:3000');
        $proxyTest = [
            'name' => 'Proxy Node.js (localhost:3000)',
            'status' => 'error',
            'message' => '',
            'details' => [],
        ];

        try {
            $response = \Illuminate\Support\Facades\Http::timeout(5)->get($proxyUrl . '/health');

            if ($response->successful()) {
                $data = $response->json();
                $proxyTest['status'] = 'success';
                $proxyTest['message'] = 'Le proxy Node.js répond correctement';
                $proxyTest['details'] = [
                    'uptime' => isset($data['uptime']) ? round($data['uptime']) . ' secondes' : 'N/A',
                    'voyager_connected' => $data['voyager']['connected'] ?? false ? 'OUI ✅' : 'NON ❌',
                    'voyager_authenticated' => $data['voyager']['authenticated'] ?? false ? 'OUI ✅' : 'NON ❌',
                ];

                // Test 2: État de la connexion Voyager
                if (!($data['voyager']['connected'] ?? false)) {
                    $diagnostics['tests'][] = [
                        'name' => 'Connexion TCP à Voyager (185.228.120.120:23002)',
                        'status' => 'warning',
                        'message' => 'Le proxy tourne mais Voyager ne répond pas',
                        'details' => [
                            'explication' => 'Le proxy arrive à se connecter en TCP mais ne reçoit pas l\'événement "Version"',
                            'raisons_possibles' => [
                                '1. Voyager n\'est pas démarré sur le serveur distant',
                                '2. Le port 23002 est mal configuré (proxy/tunnel)',
                                '3. Firewall bloque les données (mais pas la connexion)',
                            ],
                            'solution' => 'Vérifiez que Voyager tourne sur 185.228.120.120 et répond sur le port 23002',
                        ],
                    ];
                }
            } else {
                $proxyTest['status'] = 'error';
                $proxyTest['message'] = 'Le proxy répond mais avec une erreur HTTP ' . $response->status();
            }
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            $proxyTest['status'] = 'error';
            $proxyTest['message'] = 'Impossible de se connecter au proxy Node.js';
            $proxyTest['details'] = [
                'url_testée' => $proxyUrl . '/health',
                'erreur' => $e->getMessage(),
                'solution' => 'Démarrez le proxy avec: cd voyager-proxy && npm run dev',
            ];
        } catch (\Exception $e) {
            $proxyTest['status'] = 'error';
            $proxyTest['message'] = 'Erreur inattendue: ' . $e->getMessage();
        }

        $diagnostics['tests'][] = $proxyTest;

        // Test 3: Logs Laravel récents
        $laravelLogsTest = [
            'name' => 'Logs Laravel récents',
            'status' => 'info',
            'message' => 'Dernières erreurs de connexion',
            'details' => [],
        ];

        try {
            $logFile = storage_path('logs/laravel-' . now()->format('Y-m-d') . '.log');
            if (file_exists($logFile)) {
                $logs = file($logFile);
                $voyagerErrors = array_filter($logs, function ($line) {
                    return strpos($line, 'Voyager proxy request failed') !== false;
                });

                $lastErrors = array_slice($voyagerErrors, -3);
                if (count($lastErrors) > 0) {
                    $laravelLogsTest['status'] = 'warning';
                    $laravelLogsTest['message'] = count($lastErrors) . ' erreur(s) récente(s) détectée(s)';
                    $laravelLogsTest['details']['dernières_erreurs'] = array_map('trim', $lastErrors);
                } else {
                    $laravelLogsTest['message'] = 'Aucune erreur récente';
                }
            }
        } catch (\Exception $e) {
            $laravelLogsTest['message'] = 'Impossible de lire les logs';
        }

        $diagnostics['tests'][] = $laravelLogsTest;

        // Test 4: Configuration
        $configTest = [
            'name' => 'Configuration',
            'status' => 'info',
            'message' => 'Paramètres actuels',
            'details' => [
                'proxy_url' => config('services.voyager.proxy_url', 'NON CONFIGURÉ'),
                'timeout' => config('services.voyager.timeout', 20) . ' secondes',
            ],
        ];

        $diagnostics['tests'][] = $configTest;

        return response()->json($diagnostics);
    }
}
