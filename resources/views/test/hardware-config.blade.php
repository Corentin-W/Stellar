<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Configuration Matérielle Voyager</title>
    <style>
        body {
            font-family: monospace;
            background: #1a1a1a;
            color: #00ff00;
            padding: 20px;
        }
        h1 { color: #00ffff; }
        h2 { color: #ffff00; margin-top: 30px; }
        pre {
            background: #000;
            border: 1px solid #333;
            padding: 15px;
            border-radius: 5px;
            overflow-x: auto;
        }
        .success { color: #00ff00; }
        .error { color: #ff0000; }
        hr { border-color: #333; }
        ul { list-style-type: none; padding-left: 0; }
        li { padding: 5px 0; }
        a { color: #00aaff; }
    </style>
</head>
<body>
    <h1>🔧 Test de Configuration Matérielle Voyager</h1>
    <hr>

    @php
        use App\Services\RoboTargetShotService;
        $service = app(RoboTargetShotService::class);
    @endphp

    <!-- Test 1: Configuration complète -->
    <h2>Test 1: Configuration matérielle complète</h2>
    <pre>@php
        try {
            $config = $service->getHardwareConfiguration();

            if ($config['success']) {
                echo "<span class='success'>✅ Configuration récupérée avec succès!</span>\n\n";
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
                echo "<span class='error'>❌ Erreur: " . ($config['error'] ?? 'Inconnue') . "</span>\n";
            }
        } catch (\Exception $e) {
            echo "<span class='error'>❌ Exception: " . $e->getMessage() . "</span>\n";
        }
    @endphp</pre>

    <!-- Test 2: Configuration simple des filtres -->
    <h2>Test 2: Configuration des filtres (API simple)</h2>
    <pre>@php
        try {
            $config = $service->getFilterConfiguration();

            if ($config['success']) {
                echo "<span class='success'>✅ Filtres récupérés avec succès!</span>\n\n";
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
                echo "<span class='error'>❌ Erreur: " . ($config['error'] ?? 'Inconnue') . "</span>\n";
            }
        } catch (\Exception $e) {
            echo "<span class='error'>❌ Exception: " . $e->getMessage() . "</span>\n";
        }
    @endphp</pre>

    <!-- Test 3: Détails d'un filtre -->
    <h2>Test 3: Détails du filtre 0</h2>
    <pre>@php
        try {
            $filter = $service->getFilterDetails(0);

            if ($filter) {
                echo "<span class='success'>✅ Filtre trouvé!</span>\n\n";
                echo "Index: " . $filter['index'] . "\n";
                echo "Nom: " . $filter['name'] . "\n";
                echo "Offset: " . $filter['offset'] . "\n";
                echo "Mag Min: " . ($filter['magMin'] ?? 'N/A') . "\n";
                echo "Mag Max: " . ($filter['magMax'] ?? 'N/A') . "\n";
            } else {
                echo "<span class='error'>❌ Filtre non trouvé</span>\n";
            }
        } catch (\Exception $e) {
            echo "<span class='error'>❌ Exception: " . $e->getMessage() . "</span>\n";
        }
    @endphp</pre>

    <!-- Test 4: Tous les profils -->
    <h2>Test 4: Liste de tous les profils</h2>
    <pre>@php
        try {
            $result = $service->getAllProfiles();

            if ($result['success']) {
                echo "<span class='success'>✅ Profils récupérés avec succès!</span>\n\n";

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
                echo "<span class='error'>❌ Erreur: " . ($result['error'] ?? 'Inconnue') . "</span>\n";
            }
        } catch (\Exception $e) {
            echo "<span class='error'>❌ Exception: " . $e->getMessage() . "</span>\n";
        }
    @endphp</pre>

    <hr>
    <h2>🌐 Routes API disponibles</h2>
    <ul>
        <li>GET <a href="/admin/robotarget/api/config/hardware">/admin/robotarget/api/config/hardware</a> - Config complète</li>
        <li>GET <a href="/admin/robotarget/api/config/filters">/admin/robotarget/api/config/filters</a> - Filtres du profil actif</li>
        <li>GET <a href="/admin/robotarget/api/config/filters/0">/admin/robotarget/api/config/filters/0</a> - Détails d'un filtre</li>
        <li>GET <a href="/admin/robotarget/api/config/profiles">/admin/robotarget/api/config/profiles</a> - Tous les profils</li>
    </ul>
</body>
</html>
