<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Base Sequences Voyager</title>
    <style>
        body {
            font-family: monospace;
            background: #1a1a1a;
            color: #00ff00;
            padding: 20px;
        }
        h1 { color: #00ffff; }
        h2 { color: #ffff00; margin-top: 30px; }
        h3 { color: #ff00ff; margin-top: 20px; }
        pre {
            background: #000;
            border: 1px solid #333;
            padding: 15px;
            border-radius: 5px;
            overflow-x: auto;
        }
        .success { color: #00ff00; }
        .error { color: #ff0000; }
        .warning { color: #ffaa00; }
        hr { border-color: #333; }
        ul { list-style-type: none; padding-left: 0; }
        li { padding: 5px 0; }
        a { color: #00aaff; }
        .sequence-card {
            background: #222;
            border: 1px solid #444;
            padding: 10px;
            margin: 5px 0;
            border-radius: 5px;
        }
        .default { border-color: #ffaa00; background: #332200; }
    </style>
</head>
<body>
    <h1>📋 Test Base Sequences Voyager</h1>
    <hr>

    @php
        use App\Services\RoboTargetSetService;
        $service = app(RoboTargetSetService::class);
    @endphp

    <!-- Test 1: Toutes les séquences -->
    <h2>Test 1: Récupération de toutes les Base Sequences</h2>
    <pre>@php
        try {
            $result = $service->getBaseSequences();

            if ($result['success']) {
                echo "<span class='success'>✅ Séquences récupérées avec succès!</span>\n\n";
                echo "Nombre total de séquences: " . $result['count'] . "\n\n";

                if (!empty($result['sequences'])) {
                    foreach ($result['sequences'] as $seq) {
                        $default = $seq['isdefault'] ? ' ⭐ PAR DÉFAUT' : '';
                        echo "• {$seq['basesequencename']}{$default}\n";
                        echo "  Fichier: {$seq['filename']}\n";
                        echo "  Profil: {$seq['profilename']}\n";
                        echo "  GUID: {$seq['guid']}\n\n";
                    }
                } else {
                    echo "<span class='warning'>⚠️ Aucune séquence trouvée</span>\n";
                }
            } else {
                echo "<span class='error'>❌ Erreur: " . ($result['error'] ?? 'Inconnue') . "</span>\n";
            }
        } catch (\Exception $e) {
            echo "<span class='error'>❌ Exception: " . $e->getMessage() . "</span>\n";
        }
    @endphp</pre>

    <!-- Test 2: Groupement par profil -->
    <h2>Test 2: Séquences groupées par profil</h2>
    <pre>@php
        try {
            $result = $service->getBaseSequences();

            if ($result['success'] && !empty($result['byProfile'])) {
                echo "<span class='success'>✅ Séquences groupées par profil:</span>\n\n";

                foreach ($result['byProfile'] as $profileName => $group) {
                    echo "📁 <span class='success'>{$profileName}</span> ({$group['profileName']})\n";
                    echo "   Nombre de séquences: " . count($group['sequences']) . "\n";

                    if ($group['defaultSequence']) {
                        echo "   ⭐ Séquence par défaut: {$group['defaultSequence']['basesequencename']}\n";
                    }

                    echo "\n   Séquences:\n";
                    foreach ($group['sequences'] as $seq) {
                        $marker = $seq['isdefault'] ? ' ⭐' : '  ';
                        echo "   {$marker} {$seq['basesequencename']} ({$seq['filename']})\n";
                        echo "      GUID: {$seq['guid']}\n";
                    }
                    echo "\n";
                }
            } else {
                echo "<span class='warning'>⚠️ Aucune séquence trouvée ou erreur</span>\n";
            }
        } catch (\Exception $e) {
            echo "<span class='error'>❌ Exception: " . $e->getMessage() . "</span>\n";
        }
    @endphp</pre>

    <!-- Test 3: Séquence d'un profil spécifique -->
    <h2>Test 3: Séquences d'un profil spécifique</h2>
    <pre>@php
        try {
            // Récupérer d'abord la liste de tous les profils
            $allResult = $service->getBaseSequences();

            if ($allResult['success'] && !empty($allResult['byProfile'])) {
                // Prendre le premier profil disponible
                $firstProfile = array_key_first($allResult['byProfile']);

                echo "🔍 Test avec le profil: <span class='success'>{$firstProfile}</span>\n\n";

                $result = $service->getBaseSequences($firstProfile);

                if ($result['success']) {
                    echo "<span class='success'>✅ {$result['count']} séquence(s) trouvée(s) pour ce profil</span>\n\n";

                    foreach ($result['sequences'] as $seq) {
                        $default = $seq['isdefault'] ? ' [PAR DÉFAUT]' : '';
                        echo "• {$seq['basesequencename']}{$default}\n";
                        echo "  📄 {$seq['filename']}\n";
                        echo "  🆔 {$seq['guid']}\n\n";
                    }
                } else {
                    echo "<span class='error'>❌ Erreur: " . ($result['error'] ?? 'Inconnue') . "</span>\n";
                }
            } else {
                echo "<span class='warning'>⚠️ Aucun profil disponible pour le test</span>\n";
            }
        } catch (\Exception $e) {
            echo "<span class='error'>❌ Exception: " . $e->getMessage() . "</span>\n";
        }
    @endphp</pre>

    <!-- Test 4: Statistiques -->
    <h2>Test 4: Statistiques des Base Sequences</h2>
    <pre>@php
        try {
            $result = $service->getBaseSequences();

            if ($result['success']) {
                echo "<span class='success'>✅ Statistiques:</span>\n\n";

                echo "Nombre total de séquences: {$result['count']}\n";
                echo "Nombre de profils: " . count($result['byProfile']) . "\n\n";

                $totalDefault = 0;
                foreach ($result['sequences'] as $seq) {
                    if ($seq['isdefault']) $totalDefault++;
                }
                echo "Séquences par défaut: {$totalDefault}\n";
                echo "Séquences non-défaut: " . ($result['count'] - $totalDefault) . "\n\n";

                echo "Répartition par profil:\n";
                foreach ($result['byProfile'] as $profileName => $group) {
                    echo "  • {$profileName}: " . count($group['sequences']) . " séquence(s)\n";
                }
            } else {
                echo "<span class='error'>❌ Erreur: " . ($result['error'] ?? 'Inconnue') . "</span>\n";
            }
        } catch (\Exception $e) {
            echo "<span class='error'>❌ Exception: " . $e->getMessage() . "</span>\n";
        }
    @endphp</pre>

    <hr>
    <h2>🌐 API Disponible</h2>
    <ul>
        <li>GET <a href="/admin/robotarget/api/base-sequences">/admin/robotarget/api/base-sequences</a> - Toutes les séquences</li>
        <li>GET <a href="/admin/robotarget/api/base-sequences?profile=Default.v2y">/admin/robotarget/api/base-sequences?profile=Default.v2y</a> - Profil spécifique</li>
    </ul>

    <h2>💡 Utilisation</h2>
    <pre>
Les Base Sequences sont des templates (.s2q) utilisés pour créer des cibles.

Pour ajouter une Target, vous devez:
1. Récupérer le GUID de la Base Sequence désirée
2. Utiliser ce GUID dans la commande RemoteRoboTargetAddTarget

Exemple:
- Séquence: "Deep Sky LRGB"
- GUID: "abc-def-123-456"
- Utiliser ce GUID lors de AddTarget
    </pre>

    <h2>🔗 Voyager Control Panel</h2>
    <p><a href="/fr/admin/robotarget/sets">👉 Ouvrir le Control Panel</a> puis cliquer sur le bouton <strong>📋 Templates</strong></p>
</body>
</html>
