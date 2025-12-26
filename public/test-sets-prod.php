<?php
// Test direct du service RoboTargetSetService en production

require __DIR__ . '/../vendor/autoload.php';

// Charger Laravel
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->bootstrap();

echo "<h1>Test du service RoboTargetSetService</h1>";
echo "<hr>";

// Instancier le service
$service = new App\Services\RoboTargetSetService();

echo "<h2>Configuration:</h2>";
echo "<pre>";
echo "VOYAGER_PROXY_URL: " . config('services.voyager.proxy_url') . "\n";
echo "VOYAGER_PROXY_API_KEY: " . (config('services.voyager.proxy_api_key') ? substr(config('services.voyager.proxy_api_key'), 0, 20) . '...' : 'NON CONFIGURÉE') . "\n";
echo "</pre>";

echo "<h2>Test 1: Statut de connexion</h2>";
echo "<pre>";
$status = $service->getConnectionStatus();
echo "Résultat:\n";
print_r($status);
echo "</pre>";

echo "<h2>Test 2: Récupération des Sets</h2>";
echo "<pre>";
echo "Appel de getSets()...\n\n";
$result = $service->getSets();
echo "Résultat:\n";
print_r($result);

if (isset($result['success']) && $result['success']) {
    echo "\n\n✅ Succès!\n";
    echo "Nombre de Sets: " . count($result['sets']) . "\n\n";

    if (!empty($result['sets'])) {
        echo "Liste des Sets:\n";
        foreach ($result['sets'] as $set) {
            echo "  - " . $set['setname'] . " (GUID: " . $set['guid'] . ")\n";
        }
    } else {
        echo "⚠️ Aucun Set trouvé\n";
    }
} else {
    echo "\n\n❌ Échec!\n";
    echo "Erreur: " . ($result['error'] ?? 'Inconnue') . "\n";
}
echo "</pre>";

echo "<h2>Test 3: Debug complet</h2>";
echo "<pre>";
echo "Headers envoyés par le service:\n";

// On va tracer les headers
$reflection = new ReflectionClass($service);
$method = $reflection->getMethod('getHeaders');
$method->setAccessible(true);
$headers = $method->invoke($service);

echo "Headers:\n";
print_r($headers);
echo "</pre>";

echo "<hr>";
echo "<p><strong>Analyse:</strong></p>";
echo "<ul>";

if (isset($result['success']) && $result['success'] && !empty($result['sets'])) {
    echo "<li>✅ <strong>Tout fonctionne!</strong> Les Sets sont récupérés correctement.</li>";
    echo "<li>Le problème vient probablement de la vue ou du JavaScript Alpine.js</li>";
} elseif (isset($result['success']) && $result['success'] && empty($result['sets'])) {
    echo "<li>✅ Connexion OK mais aucun Set dans Voyager</li>";
    echo "<li>Vérifiez que Voyager a bien des Sets configurés</li>";
} else {
    echo "<li>❌ Le service ne fonctionne pas</li>";
    echo "<li>Erreur: " . ($result['error'] ?? 'Inconnue') . "</li>";
}

echo "</ul>";
