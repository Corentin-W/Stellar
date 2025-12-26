<?php
// Test de récupération des Shots d'une Target

require __DIR__ . '/../vendor/autoload.php';

// Charger Laravel
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->bootstrap();

echo "<h1>Test de récupération des Shots</h1>";
echo "<hr>";

// Instancier le service
$service = new App\Services\RoboTargetSetService();

echo "<h2>Étape 1: Récupération de la configuration des filtres</h2>";
echo "<pre>";
$configResult = $service->getConfigDataShot();
echo "Résultat:\n";
print_r($configResult);
echo "</pre>";

echo "<h2>Étape 2: Récupération des Sets</h2>";
echo "<pre>";
$setsResult = $service->getSets();
if ($setsResult['success'] && !empty($setsResult['sets'])) {
    $firstSet = $setsResult['sets'][0];
    echo "✅ Premier Set trouvé: " . $firstSet['setname'] . "\n";
    echo "   GUID: " . $firstSet['guid'] . "\n\n";

    echo "<h2>Étape 3: Récupération des Targets du Set</h2>";
    $targetsResult = $service->getTargets($firstSet['guid']);
    echo "Résultat:\n";
    print_r($targetsResult);

    if ($targetsResult['success'] && !empty($targetsResult['targets'])) {
        $firstTarget = $targetsResult['targets'][0];
        echo "\n✅ Première Target trouvée: " . $firstTarget['targetname'] . "\n";
        echo "   GUID: " . $firstTarget['guid'] . "\n\n";

        echo "<h2>Étape 4: Récupération des Shots de la Target</h2>";
        $shotsResult = $service->getShots($firstTarget['guid']);
        echo "Résultat:\n";
        print_r($shotsResult);

        if ($shotsResult['success']) {
            echo "\n✅ Succès! " . count($shotsResult['shots']) . " Shots récupérés\n";

            if (!empty($shotsResult['shots'])) {
                echo "\nDétails des Shots:\n";
                foreach ($shotsResult['shots'] as $i => $shot) {
                    echo "  Shot " . ($i + 1) . ":\n";
                    echo "    - Filtre index: " . ($shot['filterindex'] ?? 'N/A') . "\n";
                    echo "    - Exposition: " . ($shot['exposure'] ?? 'N/A') . "s\n";
                    echo "    - Nombre: " . ($shot['num'] ?? 'N/A') . "\n";
                    echo "    - Binning: " . ($shot['bin'] ?? 'N/A') . "\n";
                    echo "    - Gain: " . ($shot['gain'] ?? 'N/A') . "\n";
                    echo "    - Total shots: " . ($shot['auxtotshot'] ?? 'N/A') . "\n";
                    echo "    - Shots done: " . ($shot['auxshotdone'] ?? 'N/A') . "\n";
                    echo "\n";
                }
            }
        } else {
            echo "\n❌ Erreur lors de la récupération des Shots\n";
            echo "Erreur: " . ($shotsResult['error'] ?? 'Inconnue') . "\n";
        }
    } else {
        echo "\n⚠️ Aucune Target trouvée dans ce Set\n";
    }
} else {
    echo "❌ Aucun Set trouvé ou erreur\n";
}
echo "</pre>";
