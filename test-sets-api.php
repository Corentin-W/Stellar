<?php

/**
 * Script de test pour l'API RoboTarget Sets
 *
 * Usage: php test-sets-api.php
 */

require __DIR__.'/vendor/autoload.php';

use Illuminate\Support\Facades\Artisan;

// Bootstrap Laravel
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Services\RoboTargetSetService;

echo "🧪 TEST DE L'API ROBOTARGET SETS\n";
echo str_repeat('=', 80) . "\n\n";

$service = new RoboTargetSetService();

// Test 1: Vérifier le statut de connexion
echo "📊 Test 1: Vérification du statut de connexion\n";
echo str_repeat('-', 80) . "\n";
$status = $service->getConnectionStatus();
if ($status['success'] ?? false) {
    echo "✅ Voyager connecté\n";
    echo "   Host: " . ($status['data']['Host'] ?? 'N/A') . "\n";
    echo "   Status: " . ($status['data']['VOYSTAT'] ?? 'N/A') . "\n";
} else {
    echo "❌ Voyager non connecté\n";
    echo "   Erreur: " . ($status['error'] ?? 'Unknown') . "\n";
    exit(1);
}
echo "\n";

// Test 2: Récupérer tous les Sets
echo "📋 Test 2: Récupération de tous les Sets\n";
echo str_repeat('-', 80) . "\n";
$result = $service->getSets();
if ($result['success']) {
    echo "✅ Sets récupérés: {$result['count']}\n";
    foreach ($result['sets'] as $index => $set) {
        echo "   " . ($index + 1) . ". {$set['setname']} ({$set['guid']})\n";
        echo "      Profile: {$set['profilename']}\n";
        echo "      Status: " . ($set['status'] === 0 ? 'Actif' : 'Inactif') . "\n";
        if (!empty($set['tag'])) {
            echo "      Tag: {$set['tag']}\n";
        }
    }
} else {
    echo "❌ Erreur: {$result['error']}\n";
}
echo "\n";

// Test 3: Récupérer un Set spécifique par GUID
if (!empty($result['sets'])) {
    $firstSet = $result['sets'][0];
    echo "🔍 Test 3: Récupération d'un Set par GUID\n";
    echo str_repeat('-', 80) . "\n";
    echo "GUID: {$firstSet['guid']}\n";

    $set = $service->getSetByGuid($firstSet['guid']);
    if ($set) {
        echo "✅ Set trouvé: {$set['setname']}\n";
        echo "   Profile: {$set['profilename']}\n";
        echo "   Default: " . ($set['isdefault'] ? 'Oui' : 'Non') . "\n";
    } else {
        echo "❌ Set non trouvé\n";
    }
    echo "\n";
}

// Test 4: Créer un nouveau Set (optionnel - commenté pour éviter de créer des Sets à chaque test)
/*
echo "➕ Test 4: Création d'un nouveau Set\n";
echo str_repeat('-', 80) . "\n";
$createResult = $service->addSet([
    'name' => 'Test API ' . date('Y-m-d H:i:s'),
    'profile_name' => '2025-04-27_EEYE_TOA150_F1100_GM2000HPS_ASI6200mm_v10.v2y', // Utilisez un profil existant
    'is_default' => false,
    'status' => 0,
    'tag' => 'test-api',
    'note' => 'Créé automatiquement par le script de test'
]);

if ($createResult['success']) {
    echo "✅ Set créé avec succès\n";
    echo "   GUID: {$createResult['guid']}\n";
    echo "   Résultat: " . ($createResult['result']['ret'] ?? 'N/A') . "\n";

    // Test 5: Mettre à jour le Set
    echo "\n";
    echo "✏️  Test 5: Mise à jour du Set\n";
    echo str_repeat('-', 80) . "\n";
    $updateResult = $service->updateSet($createResult['guid'], [
        'name' => 'Test API Updated',
        'status' => 0,
        'tag' => 'test-api-updated',
        'note' => 'Mis à jour par le script de test'
    ]);

    if ($updateResult['success']) {
        echo "✅ Set mis à jour avec succès\n";
    } else {
        echo "❌ Erreur mise à jour: {$updateResult['error']}\n";
    }

    // Test 6: Désactiver le Set
    echo "\n";
    echo "🔒 Test 6: Désactivation du Set\n";
    echo str_repeat('-', 80) . "\n";
    $disableResult = $service->toggleSetStatus($createResult['guid'], false);

    if ($disableResult['success']) {
        echo "✅ Set désactivé avec succès\n";
    } else {
        echo "❌ Erreur désactivation: {$disableResult['error']}\n";
    }

    // Test 7: Réactiver le Set
    echo "\n";
    echo "🔓 Test 7: Réactivation du Set\n";
    echo str_repeat('-', 80) . "\n";
    $enableResult = $service->toggleSetStatus($createResult['guid'], true);

    if ($enableResult['success']) {
        echo "✅ Set activé avec succès\n";
    } else {
        echo "❌ Erreur activation: {$enableResult['error']}\n";
    }

    // Test 8: Supprimer le Set (décommentez si vous voulez nettoyer)
    // echo "\n";
    // echo "🗑️  Test 8: Suppression du Set\n";
    // echo str_repeat('-', 80) . "\n";
    // $deleteResult = $service->deleteSet($createResult['guid']);
    //
    // if ($deleteResult['success']) {
    //     echo "✅ Set supprimé avec succès\n";
    // } else {
    //     echo "❌ Erreur suppression: {$deleteResult['error']}\n";
    // }

} else {
    echo "❌ Erreur création: {$createResult['error']}\n";
}
*/

echo "\n";
echo str_repeat('=', 80) . "\n";
echo "✅ TESTS TERMINÉS\n";
echo str_repeat('=', 80) . "\n";
