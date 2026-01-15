<?php
/**
 * Script de diagnostic de la base de données
 * Usage: php check-database.php
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== DIAGNOSTIC BASE DE DONNÉES ===\n\n";

// 1. Vérifier la connexion
echo "1. Test de connexion à la base de données...\n";
try {
    \DB::connection()->getPdo();
    echo "   ✓ Connexion réussie\n";
    echo "   Base de données: " . \DB::connection()->getDatabaseName() . "\n";
} catch (\Exception $e) {
    echo "   ✗ ERREUR: " . $e->getMessage() . "\n";
    echo "\nVérifiez votre fichier .env:\n";
    echo "   DB_CONNECTION=mysql\n";
    echo "   DB_HOST=127.0.0.1\n";
    echo "   DB_PORT=3306\n";
    echo "   DB_DATABASE=votre_base\n";
    echo "   DB_USERNAME=votre_user\n";
    echo "   DB_PASSWORD=votre_password\n";
    exit(1);
}

// 2. Vérifier les tables
echo "\n2. Vérification des tables...\n";
$requiredTables = ['users', 'crypto_currencies', 'portfolios', 'transactions', 'personal_access_tokens', 'notifications'];
$missingTables = [];

foreach ($requiredTables as $table) {
    try {
        if (\Schema::hasTable($table)) {
            $count = \DB::table($table)->count();
            echo "   ✓ Table '{$table}' existe ({$count} enregistrements)\n";
        } else {
            echo "   ✗ Table '{$table}' MANQUANTE\n";
            $missingTables[] = $table;
        }
    } catch (\Exception $e) {
        echo "   ✗ Erreur lors de la vérification de '{$table}': " . $e->getMessage() . "\n";
        $missingTables[] = $table;
    }
}

if (!empty($missingTables)) {
    echo "\n⚠️  Tables manquantes détectées. Exécutez les migrations:\n";
    echo "   php artisan migrate\n";
    echo "   php artisan migrate:fresh --seed (pour réinitialiser)\n";
}

// 3. Vérifier les cryptos
echo "\n3. Vérification des cryptomonnaies...\n";
try {
    $cryptoCount = \App\Models\CryptoCurrency::count();
    if ($cryptoCount > 0) {
        echo "   ✓ {$cryptoCount} cryptomonnaies trouvées\n";
        $activeCount = \App\Models\CryptoCurrency::where('is_active', true)->count();
        echo "   ✓ {$activeCount} cryptomonnaies actives\n";
    } else {
        echo "   ⚠️  Aucune cryptomonnaie trouvée. Exécutez le seeder:\n";
        echo "      php artisan db:seed --class=CryptoAndPricesSeeder\n";
    }
} catch (\Exception $e) {
    echo "   ✗ Erreur: " . $e->getMessage() . "\n";
}

// 4. Vérifier les utilisateurs
echo "\n4. Vérification des utilisateurs...\n";
try {
    $userCount = \App\Models\User::count();
    echo "   ✓ {$userCount} utilisateurs trouvés\n";
} catch (\Exception $e) {
    echo "   ✗ Erreur: " . $e->getMessage() . "\n";
}

// 5. Vérifier Redis
echo "\n5. Test de connexion Redis...\n";
try {
    \Illuminate\Support\Facades\Redis::connection()->ping();
    echo "   ✓ Redis connecté\n";
} catch (\Exception $e) {
    echo "   ⚠️  Redis non disponible: " . $e->getMessage() . "\n";
    echo "   Le cache fonctionnera avec le driver 'file' par défaut\n";
}

echo "\n=== DIAGNOSTIC TERMINÉ ===\n";

if (empty($missingTables)) {
    echo "\n✓ Toutes les tables sont présentes. La base de données semble correcte.\n";
} else {
    echo "\n⚠️  Des problèmes ont été détectés. Corrigez-les avant de continuer.\n";
    exit(1);
}
