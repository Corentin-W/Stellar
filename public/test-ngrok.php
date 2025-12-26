<?php
// Test de connexion au proxy Voyager via ngrok

echo "<h1>Test de connexion ngrok → Voyager Proxy</h1>";
echo "<hr>";

$url = 'https://warningly-unvacuous-rosa.ngrok-free.dev/api/dashboard/state';
$apiKey = 'sk_live_VoyagerProxy2025_SecureKey_YourRandomString123456789';

echo "<h2>Configuration:</h2>";
echo "<pre>";
echo "URL: " . $url . "\n";
echo "API Key: " . substr($apiKey, 0, 20) . "..." . substr($apiKey, -10) . "\n";
echo "</pre>";

echo "<h2>Test de connexion...</h2>";

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'X-API-Key: ' . $apiKey,
    'Accept: application/json'
]);

// Capturer les erreurs curl
curl_setopt($ch, CURLOPT_VERBOSE, true);
$verbose = fopen('php://temp', 'w+');
curl_setopt($ch, CURLOPT_STDERR, $verbose);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlError = curl_error($ch);
$curlInfo = curl_getinfo($ch);

rewind($verbose);
$verboseLog = stream_get_contents($verbose);
fclose($verbose);

curl_close($ch);

echo "<h2>Résultat:</h2>";
echo "<pre>";

if ($curlError) {
    echo "❌ ERREUR CURL: " . $curlError . "\n\n";
} else {
    echo "✅ Pas d'erreur CURL\n\n";
}

echo "HTTP Code: " . $httpCode . "\n";

if ($httpCode == 200) {
    echo "✅ Connexion réussie!\n\n";
    echo "Réponse:\n";
    $data = json_decode($response, true);
    if ($data) {
        echo json_encode($data, JSON_PRETTY_PRINT);
    } else {
        echo $response;
    }
} elseif ($httpCode == 401 || $httpCode == 403) {
    echo "❌ Erreur d'authentification (clé API incorrecte)\n\n";
    echo "Réponse:\n" . $response;
} elseif ($httpCode == 0) {
    echo "❌ Impossible de se connecter au serveur\n";
    echo "Vérifiez:\n";
    echo "- ngrok est-il démarré?\n";
    echo "- L'URL est-elle correcte?\n";
    echo "- Le serveur peut-il accéder à Internet?\n";
} else {
    echo "❌ Erreur HTTP: " . $httpCode . "\n\n";
    echo "Réponse:\n" . $response;
}

echo "\n\n";
echo "Informations de connexion:\n";
echo "- Temps de connexion: " . round($curlInfo['connect_time'] * 1000) . "ms\n";
echo "- Temps total: " . round($curlInfo['total_time'] * 1000) . "ms\n";
echo "- SSL vérifié: " . ($curlInfo['ssl_verify_result'] == 0 ? 'Oui' : 'Non') . "\n";

echo "</pre>";

// Test SANS clé API (doit échouer)
echo "<h2>Test SANS clé API (doit échouer):</h2>";
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
$response2 = curl_exec($ch);
$httpCode2 = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "<pre>";
echo "HTTP Code: " . $httpCode2 . "\n";
echo "Réponse: " . $response2 . "\n";

if ($httpCode2 == 401) {
    echo "\n✅ Sécurité OK: Sans clé API, l'accès est refusé";
} else {
    echo "\n⚠️ ATTENTION: L'accès fonctionne sans clé API!";
}
echo "</pre>";

echo "<hr>";
echo "<p><strong>Instructions:</strong></p>";
echo "<ul>";
echo "<li>Si vous voyez 'Connexion réussie', tout fonctionne!</li>";
echo "<li>Si erreur d'authentification: vérifiez que VOYAGER_PROXY_API_KEY est correct dans .env</li>";
echo "<li>Si impossible de se connecter: vérifiez que ngrok tourne sur votre PC local</li>";
echo "</ul>";
