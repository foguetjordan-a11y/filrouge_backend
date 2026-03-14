<?php

/**
 * Script pour exécuter les tests Laravel
 * Usage: php run-tests.php [options]
 */

echo "🧪 Exécution des tests Laravel\n";
echo "================================\n\n";

// Vérifier que nous sommes dans le bon répertoire
if (!file_exists('artisan')) {
    echo "❌ Erreur: Ce script doit être exécuté depuis le répertoire racine de Laravel\n";
    exit(1);
}

// Options par défaut
$options = [
    'feature' => false,
    'unit' => false,
    'coverage' => false,
    'verbose' => false,
    'filter' => null,
];

// Parser les arguments de ligne de commande
$args = array_slice($argv, 1);
foreach ($args as $arg) {
    switch ($arg) {
        case '--feature':
            $options['feature'] = true;
            break;
        case '--unit':
            $options['unit'] = true;
            break;
        case '--coverage':
            $options['coverage'] = true;
            break;
        case '--verbose':
        case '-v':
            $options['verbose'] = true;
            break;
        default:
            if (strpos($arg, '--filter=') === 0) {
                $options['filter'] = substr($arg, 9);
            }
            break;
    }
}

// Construire la commande
$command = 'php artisan test';

// Ajouter les options
if ($options['feature'] && !$options['unit']) {
    $command .= ' tests/Feature';
} elseif ($options['unit'] && !$options['feature']) {
    $command .= ' tests/Unit';
}

if ($options['filter']) {
    $command .= ' --filter=' . escapeshellarg($options['filter']);
}

if ($options['verbose']) {
    $command .= ' --verbose';
}

if ($options['coverage']) {
    $command .= ' --coverage';
}

// Afficher la commande qui va être exécutée
echo "📋 Commande: {$command}\n\n";

// Exécuter les tests
echo "🚀 Démarrage des tests...\n";
echo str_repeat('-', 50) . "\n";

$startTime = microtime(true);
$output = [];
$returnCode = 0;

exec($command . ' 2>&1', $output, $returnCode);

$endTime = microtime(true);
$duration = round($endTime - $startTime, 2);

// Afficher les résultats
foreach ($output as $line) {
    echo $line . "\n";
}

echo str_repeat('-', 50) . "\n";

// Résumé
if ($returnCode === 0) {
    echo "✅ Tous les tests sont passés avec succès!\n";
} else {
    echo "❌ Certains tests ont échoué.\n";
}

echo "⏱️  Durée d'exécution: {$duration} secondes\n";

// Afficher l'aide si aucun argument
if (empty($args)) {
    echo "\n📖 Options disponibles:\n";
    echo "  --feature     Exécuter seulement les tests Feature\n";
    echo "  --unit        Exécuter seulement les tests Unit\n";
    echo "  --coverage    Afficher la couverture de code\n";
    echo "  --verbose     Mode verbeux\n";
    echo "  --filter=X    Filtrer les tests par nom\n";
    echo "\n📝 Exemples:\n";
    echo "  php run-tests.php --feature\n";
    echo "  php run-tests.php --unit --verbose\n";
    echo "  php run-tests.php --filter=EnrollementTest\n";
    echo "  php run-tests.php --coverage\n";
}

exit($returnCode);