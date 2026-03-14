<?php

/**
 * Script final pour exécuter tous les tests Laravel
 */

echo "🧪 Exécution de la suite de tests Laravel\n";
echo "=========================================\n\n";

$testFiles = [
    "tests/Feature/ExampleTest.php" => "Test de base Laravel",
    "tests/Feature/AuthTest.php" => "Tests d'authentification",
    "tests/Feature/ApiTest.php" => "Tests d'API",
    "tests/Feature/ValidationTest.php" => "Tests de validation",
    "tests/Unit/ExampleTest.php" => "Test unitaire de base",
    "tests/Unit/UserModelTest.php" => "Tests du modèle User"
];

$passed = 0;
$failed = 0;
$total = 0;

foreach ($testFiles as $file => $description) {
    if (!file_exists($file)) {
        echo "⚠️  {$description}: Fichier non trouvé\n";
        continue;
    }
    
    echo "🔍 {$description}...\n";
    $command = "php artisan test {$file} 2>&1";
    $output = shell_exec($command);
    
    if (strpos($output, "PASS") !== false && strpos($output, "FAIL") === false) {
        echo "   ✅ PASS\n";
        $passed++;
    } else {
        echo "   ❌ FAIL\n";
        $failed++;
    }
    $total++;
    echo "\n";
}

echo "📊 RÉSUMÉ:\n";
echo "Total: {$total}\n";
echo "✅ Réussis: {$passed}\n";
echo "❌ Échoués: {$failed}\n";
echo "📈 Taux de réussite: " . round(($passed / $total) * 100, 1) . "%\n";

if ($failed === 0) {
    echo "\n🎉 Tous les tests passent!\n";
    exit(0);
} else {
    echo "\n⚠️  Certains tests échouent.\n";
    exit(1);
}