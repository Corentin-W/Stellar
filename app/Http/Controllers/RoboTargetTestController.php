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
            'Status' => 0,
            'Tag' => 'test_stellar',
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
        $result = $this->voyager->addTarget([
            'GuidTarget' => $targetGuid,
            'RefGuidSet' => $request->set_guid,
            'TargetName' => $request->name,
            'RAJ2000' => $request->ra,
            'DECJ2000' => $request->dec,
            'PA' => 0,
            'DateCreation' => now()->timestamp,
            'Status' => 0,
            'Priority' => 2,
            'IsRepeat' => true,
            'Repeat' => 1,
            'C_Mask' => 'BDE',
            'C_AltMin' => (float) ($request->alt_min ?? 30),
            'C_HAStart' => (float) ($request->ha_start ?? -3),
            'C_HAEnd' => (float) ($request->ha_end ?? 3),
        ]);

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
