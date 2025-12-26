<?php
// Test de la nouvelle API de configuration matérielle

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->bootstrap();

echo "<h1>🔧 Test de Configuration Matérielle Voyager</h1>";
echo "<hr>";

// Test 1: Configuration complète
echo "<h2>Test 1: Configuration matérielle complète</h2>";
echo "<pre>";
try {
    $service = app(App\Services\RoboTargetShotService::class);
    $config = $service->getHardwareConfiguration();

    if ($config['success']) {
        echo "✅ Configuration récupérée avec succès!\n\n";

        echo "Nombre de profils: " . $config['count'] . "\n\n";

        $parsed = $config['parsed'];

        if ($parsed['activeProfile']) {
            echo "📋 Profil Actif:\n";
            echo "  - Nom: " . $parsed['activeProfile']['name'] . "\n";
            echo "  - Type capteur: " . $parsed['activeProfile']['sensorType'] . "\n";
            echo "  - CMOS: " . ($parsed['activeProfile']['isCmos'] ? 'Oui' : 'Non') . "\n\n";

            echo "  🎨 Filtres:\n";
            foreach ($parsed['activeProfile']['filters'] as $filter) {
                echo "    [{$filter['index']}] {$filter['name']} (Offset: {$filter['offset']})\n";
            }

            echo "\n  📖 Modes de lecture:\n";
            foreach ($parsed['activeProfile']['readoutModes'] as $mode) {
                echo "    [{$mode['index']}] {$mode['name']}\n";
            }

            if (!empty($parsed['activeProfile']['speeds'])) {
                echo "\n  ⚡ Vitesses:\n";
                foreach ($parsed['activeProfile']['speeds'] as $speed) {
                    echo "    [{$speed['index']}] {$speed['name']}\n";
                }
            }
        }

        echo "\n📚 Tous les profils:\n";
        foreach ($parsed['allProfiles'] as $profile) {
            $active = $profile['isActive'] ? ' ✅' : '';
            echo "  - {$profile['name']}{$active}\n";
        }

    } else {
        echo "❌ Erreur: " . ($config['error'] ?? 'Inconnue') . "\n";
    }
} catch (\Exception $e) {
    echo "❌ Exception: " . $e->getMessage() . "\n";
}
echo "</pre>";

// Test 2: Configuration simple des filtres
echo "<h2>Test 2: Configuration des filtres (API simple)</h2>";
echo "<pre>";
try {
    $service = app(App\Services\RoboTargetShotService::class);
    $config = $service->getFilterConfiguration();

    if ($config['success']) {
        echo "✅ Filtres récupérés avec succès!\n\n";
        echo "Profil: " . $config['profileName'] . "\n";
        echo "Type capteur: " . $config['sensorType'] . "\n";
        echo "CMOS: " . ($config['isCmos'] ? 'Oui' : 'Non') . "\n\n";

        echo "Filtres disponibles:\n";
        foreach ($config['filters'] as $filter) {
            echo "  [{$filter['index']}] {$filter['name']} (Offset: {$filter['offset']}";
            if ($filter['magMin'] !== null) {
                echo ", Mag: {$filter['magMin']}-{$filter['magMax']}";
            }
            echo ")\n";
        }
    } else {
        echo "❌ Erreur: " . ($config['error'] ?? 'Inconnue') . "\n";
    }
} catch (\Exception $e) {
    echo "❌ Exception: " . $e->getMessage() . "\n";
}
echo "</pre>";

// Test 3: Détails d'un filtre
echo "<h2>Test 3: Détails du filtre 0</h2>";
echo "<pre>";
try {
    $service = app(App\Services\RoboTargetShotService::class);
    $filter = $service->getFilterDetails(0);

    if ($filter) {
        echo "✅ Filtre trouvé!\n\n";
        echo "Index: " . $filter['index'] . "\n";
        echo "Nom: " . $filter['name'] . "\n";
        echo "Offset: " . $filter['offset'] . "\n";
        echo "Mag Min: " . ($filter['magMin'] ?? 'N/A') . "\n";
        echo "Mag Max: " . ($filter['magMax'] ?? 'N/A') . "\n";
    } else {
        echo "❌ Filtre non trouvé\n";
    }
} catch (\Exception $e) {
    echo "❌ Exception: " . $e->getMessage() . "\n";
}
echo "</pre>";

// Test 4: Tous les profils
echo "<h2>Test 4: Liste de tous les profils</h2>";
echo "<pre>";
try {
    $service = app(App\Services\RoboTargetShotService::class);
    $result = $service->getAllProfiles();

    if ($result['success']) {
        echo "✅ Profils récupérés avec succès!\n\n";

        if ($result['activeProfile']) {
            echo "Profil actif: " . $result['activeProfile']['name'] . "\n\n";
        }

        echo "Tous les profils:\n";
        foreach ($result['profiles'] as $profile) {
            $active = $profile['isActive'] ? ' [ACTIF]' : '';
            echo "  - {$profile['name']}{$active}\n";
            echo "    Type: {$profile['sensorType']}\n";
            echo "    Filtres: " . count($profile['filters']) . "\n";
            echo "    Modes lecture: " . count($profile['readoutModes']) . "\n";
        }
    } else {
        echo "❌ Erreur: " . ($result['error'] ?? 'Inconnue') . "\n";
    }
} catch (\Exception $e) {
    echo "❌ Exception: " . $e->getMessage() . "\n";
}
echo "</pre>";

echo "<hr>";
echo "<h2>🌐 Routes API disponibles</h2>";
echo "<ul>";
echo "<li>GET <a href='/admin/robotarget/api/config/hardware'>/admin/robotarget/api/config/hardware</a> - Config complète</li>";
echo "<li>GET <a href='/admin/robotarget/api/config/filters'>/admin/robotarget/api/config/filters</a> - Filtres du profil actif</li>";
echo "<li>GET <a href='/admin/robotarget/api/config/filters/0'>/admin/robotarget/api/config/filters/0</a> - Détails d'un filtre</li>";
echo "<li>GET <a href='/admin/robotarget/api/config/profiles'>/admin/robotarget/api/config/profiles</a> - Tous les profils</li>";
echo "</ul>";
