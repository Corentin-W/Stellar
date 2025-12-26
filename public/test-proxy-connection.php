<?php
// Test rapide de connexion au proxy

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->bootstrap();

echo "<h1>Test de connexion au Proxy Voyager</h1>";
echo "<hr>";

$proxyUrl = config('services.voyager.proxy_url');
$apiKey = config('services.voyager.proxy_api_key');

echo "<h2>Configuration:</h2>";
echo "<pre>";
echo "VOYAGER_PROXY_URL: " . $proxyUrl . "\n";
echo "API Key configurée: " . ($apiKey ? 'Oui' : 'Non') . "\n";
echo "</pre>";

echo "<h2>Test 1: Health check du proxy</h2>";
echo "<pre>";
$start = microtime(true);

try {
    $response = \Illuminate\Support\Facades\Http::timeout(5)
        ->withHeaders($apiKey ? ['X-API-Key' => $apiKey] : [])
        ->get("{$proxyUrl}/health");

    $elapsed = round((microtime(true) - $start) * 1000);

    if ($response->successful()) {
        echo "✅ Proxy accessible! (temps: {$elapsed}ms)\n";
        echo "Réponse: " . $response->body() . "\n";
    } else {
        echo "❌ Erreur HTTP " . $response->status() . "\n";
        echo "Réponse: " . $response->body() . "\n";
    }
} catch (\Exception $e) {
    $elapsed = round((microtime(true) - $start) * 1000);
    echo "❌ Exception après {$elapsed}ms\n";
    echo "Erreur: " . $e->getMessage() . "\n";
}
echo "</pre>";

echo "<h2>Test 2: État du Dashboard</h2>";
echo "<pre>";
$start = microtime(true);

try {
    $response = \Illuminate\Support\Facades\Http::timeout(5)
        ->withHeaders($apiKey ? ['X-API-Key' => $apiKey] : [])
        ->get("{$proxyUrl}/api/dashboard/state");

    $elapsed = round((microtime(true) - $start) * 1000);

    if ($response->successful()) {
        echo "✅ Dashboard accessible! (temps: {$elapsed}ms)\n";
        $data = $response->json();
        echo "Voyager connecté: " . ($data['connected'] ? 'Oui' : 'Non') . "\n";
        echo "Manager Mode: " . ($data['managerModeActive'] ? 'Actif' : 'Inactif') . "\n";
    } else {
        echo "❌ Erreur HTTP " . $response->status() . "\n";
    }
} catch (\Exception $e) {
    $elapsed = round((microtime(true) - $start) * 1000);
    echo "❌ Exception après {$elapsed}ms\n";
    echo "Erreur: " . $e->getMessage() . "\n";
}
echo "</pre>";

echo "<h2>Test 3: Commande simple GetSet (avec timeout court)</h2>";
echo "<pre>";
$start = microtime(true);

try {
    $response = \Illuminate\Support\Facades\Http::timeout(10)
        ->withHeaders($apiKey ? ['X-API-Key' => $apiKey] : [])
        ->post("{$proxyUrl}/api/robotarget/test-mac", [
            'method' => 'RemoteRoboTargetGetSet',
            'params' => [
                'ProfileName' => ''
            ],
            'macFormula' => [
                'sep1' => '||:||',
                'sep2' => '||:||',
                'sep3' => '||:||'
            ]
        ]);

    $elapsed = round((microtime(true) - $start) * 1000);

    $data = $response->json();

    if ($data['success'] ?? false) {
        $setsCount = count($data['result']['ParamRet']['list'] ?? []);
        echo "✅ GetSet fonctionne! (temps: {$elapsed}ms)\n";
        echo "Sets trouvés: {$setsCount}\n";
    } else {
        echo "❌ Erreur: " . ($data['error'] ?? 'Inconnue') . " (temps: {$elapsed}ms)\n";
    }
} catch (\Exception $e) {
    $elapsed = round((microtime(true) - $start) * 1000);
    echo "❌ Exception après {$elapsed}ms\n";
    echo "Erreur: " . $e->getMessage() . "\n";
}
echo "</pre>";

echo "<hr>";
echo "<p><strong>Diagnostic:</strong></p>";
echo "<ul>";
echo "<li>Si le proxy n'est pas accessible → Vérifiez qu'il tourne (npm run dev)</li>";
echo "<li>Si GetSet fonctionne mais GetShot timeout → Le problème vient de la commande GetShot</li>";
echo "<li>Si tout timeout → Vérifiez que Voyager est bien connecté au proxy</li>";
echo "</ul>";
